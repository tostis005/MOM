<?php
/**
 * Universal JSON -> WordPress importer for Content Contract v2.
 *
 * Usage:
 *   php import-content.php <wp-root> <package-root>
 *     [--language=es|en|all] [--from=1] [--to=999999]
 *     [--status=publish|draft|review|json] [--force=0|1]
 *     [--successful-sha=<sha>] [--state=import|read]
 *
 * Package root must contain content/site.json, the taxonomy files referenced
 * by that manifest, and content/articles/<language>/*.json.
 */

if ( PHP_SAPI !== 'cli' ) {
    fwrite( STDERR, "CLI only.\n" );
    exit( 1 );
}

if ( $argc < 3 ) {
    fwrite( STDERR, "Usage: php import-content.php <wp-root> <package-root> [options]\n" );
    exit( 1 );
}

$wp_root      = rtrim( $argv[1], '/' );
$package_root = rtrim( $argv[2], '/' );
$wp_load      = $wp_root . '/wp-load.php';

if ( ! is_readable( $wp_load ) ) {
    fwrite( STDERR, "Cannot read {$wp_load}\n" );
    exit( 1 );
}
if ( ! is_dir( $package_root ) ) {
    fwrite( STDERR, "Package directory not found: {$package_root}\n" );
    exit( 1 );
}

$options = array(
    'language'       => 'all',
    'from'           => 1,
    'to'             => PHP_INT_MAX,
    'status'         => 'json',
    'force'          => false,
    'successful_sha' => '',
    'state'          => 'import',
);

foreach ( array_slice( $argv, 3 ) as $argument ) {
    if ( 0 !== strpos( $argument, '--' ) || false === strpos( $argument, '=' ) ) {
        continue;
    }
    list( $key, $value ) = explode( '=', substr( $argument, 2 ), 2 );
    $key = str_replace( '-', '_', $key );
    if ( array_key_exists( $key, $options ) ) {
        $options[ $key ] = $value;
    }
}

$options['from']  = max( 1, (int) $options['from'] );
$options['to']    = max( $options['from'], (int) $options['to'] );
$options['force'] = in_array( strtolower( (string) $options['force'] ), array( '1', 'true', 'yes', 'on' ), true );

if ( ! preg_match( '/^(?:all|[a-z]{2}(?:-[A-Z]{2})?)$/', (string) $options['language'] ) ) {
    throw new RuntimeException( 'Invalid --language.' );
}
if ( ! in_array( $options['status'], array( 'publish', 'draft', 'review', 'json' ), true ) ) {
    throw new RuntimeException( 'Invalid --status.' );
}
if ( ! in_array( $options['state'], array( 'import', 'read' ), true ) ) {
    throw new RuntimeException( 'Invalid --state.' );
}
if ( '' !== $options['successful_sha'] && ! preg_match( '/^[0-9a-f]{40}$/i', (string) $options['successful_sha'] ) ) {
    throw new RuntimeException( 'Invalid --successful-sha.' );
}

require_once $wp_load;

if ( 'read' === $options['state'] ) {
    echo 'IMPORT_STATE_SHA=' . (string) get_option( 'content_platform_last_successful_import_sha', '' ) . "\n";
    exit( 0 );
}

function content_import_json_file( $path ) {
    if ( ! is_readable( $path ) ) {
        throw new RuntimeException( "Cannot read JSON file: {$path}" );
    }
    $raw = file_get_contents( $path );
    $data = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );
    if ( ! is_array( $data ) ) {
        throw new RuntimeException( "JSON root must be an object: {$path}" );
    }
    return array( $data, $raw );
}

function content_import_required_string( $data, $key, $file ) {
    if ( ! isset( $data[ $key ] ) || ! is_string( $data[ $key ] ) || '' === trim( $data[ $key ] ) ) {
        throw new RuntimeException( "Missing or invalid '{$key}' in {$file}" );
    }
    return trim( $data[ $key ] );
}

function content_import_load_platform_config( $package_root ) {
    list( $site ) = content_import_json_file( $package_root . '/content/site.json' );
    if ( empty( $site['site_id'] ) || empty( $site['taxonomies'] ) || ! is_array( $site['taxonomies'] ) ) {
        throw new RuntimeException( 'content/site.json is missing site_id or taxonomies.' );
    }

    $taxonomies = array();
    foreach ( $site['taxonomies'] as $relative_path ) {
        $relative_path = ltrim( (string) $relative_path, '/' );
        list( $definition ) = content_import_json_file( $package_root . '/' . $relative_path );
        if ( empty( $definition['dimension'] ) || empty( $definition['wp_taxonomy'] ) ) {
            throw new RuntimeException( "Invalid taxonomy definition: {$relative_path}" );
        }
        $taxonomies[ (string) $definition['dimension'] ] = $definition;
    }

    return array( 'site' => $site, 'taxonomies' => array_values( $taxonomies ) );
}

function content_import_term_registry( $definition ) {
    $registry = array();
    foreach ( $definition['terms'] ?? array() as $term ) {
        if ( is_array( $term ) && ! empty( $term['id'] ) ) {
            $registry[ (string) $term['id'] ] = $term;
        }
    }
    return $registry;
}

function content_import_localized( $value, $language, $default_language ) {
    if ( ! is_array( $value ) ) {
        return is_scalar( $value ) ? (string) $value : '';
    }
    if ( isset( $value[ $language ] ) && is_scalar( $value[ $language ] ) ) {
        return (string) $value[ $language ];
    }
    if ( isset( $value[ $default_language ] ) && is_scalar( $value[ $default_language ] ) ) {
        return (string) $value[ $default_language ];
    }
    foreach ( $value as $candidate ) {
        if ( is_scalar( $candidate ) ) {
            return (string) $candidate;
        }
    }
    return '';
}

function content_import_humanize_id( $id ) {
    return ucwords( str_replace( array( '-', '_' ), ' ', (string) $id ) );
}

function content_import_ensure_term( $definition, $term_id, $platform, &$cache ) {
    $taxonomy         = (string) $definition['wp_taxonomy'];
    $dimension        = (string) $definition['dimension'];
    $cache_key        = $taxonomy . ':' . $term_id;
    $site             = $platform['site'];
    $default_language = ! empty( $site['default_language'] ) ? (string) $site['default_language'] : array_key_first( $site['languages'] ?? array() );
    $registry         = content_import_term_registry( $definition );
    $strict           = ! empty( $site['content']['strict_taxonomy_terms'] );

    if ( isset( $cache[ $cache_key ] ) ) {
        return $cache[ $cache_key ];
    }

    if ( ! isset( $registry[ $term_id ] ) && $strict ) {
        throw new RuntimeException( "Unknown taxonomy term '{$term_id}' in dimension '{$dimension}'." );
    }

    $term_config = $registry[ $term_id ] ?? array( 'id' => $term_id, 'label' => $term_id );
    $name        = content_import_localized( $term_config['label'] ?? $term_id, $default_language, $default_language );
    $name        = '' !== $name ? $name : content_import_humanize_id( $term_id );
    $slug        = sanitize_title( (string) $term_id );
    $parent_id   = 0;

    if ( ! empty( $definition['hierarchical'] ) && ! empty( $term_config['parent'] ) ) {
        $parent_id = content_import_ensure_term( $definition, (string) $term_config['parent'], $platform, $cache );
    }

    $term = get_term_by( 'slug', $slug, $taxonomy );
    if ( ! $term instanceof WP_Term ) {
        $created = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug, 'parent' => $parent_id ) );
        if ( is_wp_error( $created ) ) {
            throw new RuntimeException( "Could not create {$taxonomy}:{$term_id}: " . $created->get_error_message() );
        }
        $term = get_term( (int) $created['term_id'], $taxonomy );
    } else {
        $updated = wp_update_term( $term->term_id, $taxonomy, array( 'name' => $name, 'parent' => $parent_id ) );
        if ( is_wp_error( $updated ) ) {
            throw new RuntimeException( "Could not update {$taxonomy}:{$term_id}: " . $updated->get_error_message() );
        }
        $term = get_term( $term->term_id, $taxonomy );
    }

    update_term_meta( $term->term_id, '_content_term_id', (string) $term_id );
    update_term_meta( $term->term_id, '_content_dimension', $dimension );
    update_term_meta( $term->term_id, '_content_localized_labels', wp_json_encode( $term_config['label'] ?? array(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
    update_term_meta( $term->term_id, '_content_localized_slugs', wp_json_encode( $term_config['slug'] ?? array(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
    update_term_meta( $term->term_id, '_content_home_order', (int) ( $term_config['home_order'] ?? 9999 ) );
    update_term_meta( $term->term_id, '_content_home_visible', ! isset( $term_config['home_visible'] ) || (bool) $term_config['home_visible'] ? '1' : '0' );

    $cache[ $cache_key ] = (int) $term->term_id;
    return $cache[ $cache_key ];
}

function content_import_normalize_taxonomy( $data, $platform ) {
    $taxonomy = isset( $data['taxonomy'] ) && is_array( $data['taxonomy'] ) ? $data['taxonomy'] : array();
    $version  = isset( $data['schema_version'] ) ? (int) $data['schema_version'] : 1;

    if ( $version >= 2 ) {
        $normalized = array();
        foreach ( $taxonomy as $dimension => $assignment ) {
            if ( ! is_array( $assignment ) ) {
                continue;
            }
            $terms = ! empty( $assignment['terms'] ) && is_array( $assignment['terms'] ) ? $assignment['terms'] : array();
            $terms = array_values( array_unique( array_filter( array_map( 'strval', $terms ) ) ) );
            $primary = isset( $assignment['primary'] ) && null !== $assignment['primary'] ? (string) $assignment['primary'] : '';
            if ( $primary && ! in_array( $primary, $terms, true ) ) {
                array_unshift( $terms, $primary );
            }
            $normalized[ (string) $dimension ] = array( 'primary' => $primary, 'terms' => $terms );
        }
        return $normalized;
    }

    $legacy = $platform['site']['legacy_v1'] ?? array();
    if ( empty( $legacy['enabled'] ) ) {
        throw new RuntimeException( 'Schema v1 article encountered but legacy_v1 is disabled.' );
    }

    $normalized = array();
    foreach ( $legacy['taxonomy_fields'] ?? array() as $field => $mapping ) {
        if ( ! array_key_exists( $field, $taxonomy ) || empty( $mapping['dimension'] ) ) {
            continue;
        }
        $dimension = (string) $mapping['dimension'];
        $mode      = ! empty( $mapping['mode'] ) ? (string) $mapping['mode'] : 'terms';
        $values    = is_array( $taxonomy[ $field ] ) ? $taxonomy[ $field ] : array( $taxonomy[ $field ] );
        $value_map = ! empty( $mapping['value_map'] ) && is_array( $mapping['value_map'] ) ? $mapping['value_map'] : array();

        foreach ( $values as $value ) {
            $value = (string) $value;
            if ( isset( $value_map[ $value ] ) ) {
                $value = (string) $value_map[ $value ];
            }
            if ( '' === $value ) {
                continue;
            }
            if ( ! isset( $normalized[ $dimension ] ) ) {
                $normalized[ $dimension ] = array( 'primary' => '', 'terms' => array() );
            }
            if ( ! in_array( $value, $normalized[ $dimension ]['terms'], true ) ) {
                $normalized[ $dimension ]['terms'][] = $value;
            }
            if ( 'primary' === $mode ) {
                $normalized[ $dimension ]['primary'] = $value;
            }
        }
    }

    return $normalized;
}

function content_import_find_existing( $source_id, $slug, $language, $post_type ) {
    $ids = get_posts( array(
        'post_type' => $post_type, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids',
        'meta_key' => '_content_source_id', 'meta_value' => $source_id,
    ) );
    if ( ! empty( $ids ) ) {
        return get_post( (int) $ids[0] );
    }

    $ids = get_posts( array(
        'post_type' => $post_type, 'post_status' => 'any', 'posts_per_page' => 1, 'name' => $slug,
        'meta_query' => array( array( 'key' => '_content_language', 'value' => $language ) ),
    ) );
    return ! empty( $ids ) ? get_post( (int) $ids[0] ) : null;
}

function content_import_status( $json_status, $override ) {
    if ( 'json' !== $override ) {
        return 'review' === $override ? 'draft' : $override;
    }
    return 'publish' === $json_status ? 'publish' : 'draft';
}

function content_import_sources_html( $sources, $language ) {
    if ( empty( $sources ) || ! is_array( $sources ) ) {
        return '';
    }
    $title = 'en' === $language ? 'Sources' : 'Fuentes';
    $html  = '<h2>' . esc_html( $title ) . '</h2><ul class="content-article-sources">';
    foreach ( $sources as $source ) {
        if ( empty( $source['name'] ) || empty( $source['url'] ) ) {
            continue;
        }
        $name = esc_html( (string) $source['name'] );
        $url  = esc_url( (string) $source['url'] );
        $note = ! empty( $source['note'] ) ? ' — ' . esc_html( (string) $source['note'] ) : '';
        $html .= '<li><a href="' . $url . '" rel="noopener noreferrer">' . $name . '</a>' . $note . '</li>';
    }
    return $html . '</ul>';
}

function content_import_apply_taxonomy( $post_id, $taxonomy, $platform, &$term_cache ) {
    foreach ( $platform['taxonomies'] as $definition ) {
        $dimension = (string) $definition['dimension'];
        $wp_tax    = (string) $definition['wp_taxonomy'];
        $assigned  = $taxonomy[ $dimension ] ?? array( 'primary' => '', 'terms' => array() );
        $term_ids  = array();

        foreach ( $assigned['terms'] ?? array() as $term_id ) {
            $term_ids[] = content_import_ensure_term( $definition, (string) $term_id, $platform, $term_cache );
        }
        wp_set_object_terms( $post_id, array_values( array_unique( $term_ids ) ), $wp_tax, false );
        update_post_meta( $post_id, '_content_primary_' . sanitize_key( $dimension ), (string) ( $assigned['primary'] ?? '' ) );
    }
    update_post_meta( $post_id, '_content_taxonomy', wp_json_encode( $taxonomy, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
}

function content_import_save_seo( $post_id, $seo ) {
    $title       = isset( $seo['title'] ) ? (string) $seo['title'] : '';
    $description = isset( $seo['meta_description'] ) ? (string) $seo['meta_description'] : '';
    $intent      = isset( $seo['search_intent'] ) ? (string) $seo['search_intent'] : '';
    update_post_meta( $post_id, '_content_seo_title', $title );
    update_post_meta( $post_id, '_content_meta_description', $description );
    update_post_meta( $post_id, '_content_search_intent', $intent );
    if ( defined( 'WPSEO_VERSION' ) ) {
        update_post_meta( $post_id, '_yoast_wpseo_title', $title );
        update_post_meta( $post_id, '_yoast_wpseo_metadesc', $description );
    }
    if ( defined( 'RANK_MATH_VERSION' ) ) {
        update_post_meta( $post_id, 'rank_math_title', $title );
        update_post_meta( $post_id, 'rank_math_description', $description );
    }
}

function content_import_link_translations( $groups, $post_type ) {
    if ( ! function_exists( 'pll_save_post_translations' ) ) {
        return;
    }
    foreach ( array_unique( $groups ) as $group ) {
        $posts = get_posts( array(
            'post_type' => $post_type, 'post_status' => 'any', 'posts_per_page' => -1,
            'meta_key' => '_content_translation_group', 'meta_value' => $group,
        ) );
        $translations = array();
        foreach ( $posts as $post ) {
            $lang = (string) get_post_meta( $post->ID, '_content_language', true );
            if ( $lang ) {
                $translations[ $lang ] = (int) $post->ID;
            }
        }
        if ( count( $translations ) > 1 ) {
            pll_save_post_translations( $translations );
        }
    }
}

$platform = content_import_load_platform_config( $package_root );
update_option( 'content_platform_config', $platform, false );
if ( function_exists( 'content_platform_register_taxonomies' ) ) {
    content_platform_register_taxonomies();
}

$post_type = ! empty( $platform['site']['post_type'] ) ? (string) $platform['site']['post_type'] : 'post';
$languages = array_keys( $platform['site']['languages'] ?? array() );
if ( 'all' !== $options['language'] ) {
    $languages = array( (string) $options['language'] );
}

$files = array();
foreach ( $languages as $language ) {
    $directory = $package_root . '/content/articles/' . $language;
    if ( ! is_dir( $directory ) ) {
        continue;
    }
    foreach ( glob( $directory . '/*.json' ) ?: array() as $file ) {
        $files[] = $file;
    }
}
sort( $files, SORT_NATURAL );

$created = $updated = $skipped = $failed = 0;
$groups = array();
$term_cache = array();

foreach ( $files as $file ) {
    try {
        list( $data, $raw ) = content_import_json_file( $file );
        $number = isset( $data['article_number'] ) ? (int) $data['article_number'] : 0;
        if ( $number < $options['from'] || $number > $options['to'] ) {
            continue;
        }

        $source_id         = content_import_required_string( $data, 'id', $file );
        $language          = content_import_required_string( $data, 'language', $file );
        $translation_group = content_import_required_string( $data, 'translation_group', $file );
        $title             = content_import_required_string( $data, 'title', $file );
        $slug              = content_import_required_string( $data, 'slug', $file );
        $content_html      = isset( $data['content_html'] ) && is_string( $data['content_html'] ) ? $data['content_html'] : '';
        $excerpt           = isset( $data['excerpt'] ) && is_string( $data['excerpt'] ) ? $data['excerpt'] : '';
        $seo               = isset( $data['seo'] ) && is_array( $data['seo'] ) ? $data['seo'] : array();
        $faq               = isset( $data['faq'] ) && is_array( $data['faq'] ) ? $data['faq'] : array();
        $sources           = isset( $data['sources'] ) && is_array( $data['sources'] ) ? $data['sources'] : array();
        $image             = isset( $data['image'] ) && is_array( $data['image'] ) ? $data['image'] : array();
        $taxonomy          = content_import_normalize_taxonomy( $data, $platform );
        $post_status       = content_import_status( isset( $data['status'] ) ? (string) $data['status'] : 'draft', $options['status'] );
        $source_hash       = hash( 'sha256', $raw );
        $existing          = content_import_find_existing( $source_id, $slug, $language, $post_type );

        if ( ! $options['force'] && $existing instanceof WP_Post ) {
            $stored_hash = (string) get_post_meta( $existing->ID, '_content_source_hash', true );
            if ( $stored_hash && hash_equals( $stored_hash, $source_hash ) && $existing->post_status === $post_status ) {
                ++$skipped;
                echo "UNCHANGED #{$number} [{$language}] post_id={$existing->ID} slug={$slug}\n";
                $groups[] = $translation_group;
                continue;
            }
        }

        $content = $content_html;
        if ( ! empty( $platform['site']['content']['render_sources_in_body'] ) ) {
            $content .= content_import_sources_html( $sources, $language );
        }

        $postarr = array(
            'post_type' => $post_type, 'post_title' => $title, 'post_name' => $slug,
            'post_content' => $content, 'post_excerpt' => $excerpt, 'post_status' => $post_status,
        );
        if ( $existing instanceof WP_Post ) {
            $postarr['ID'] = $existing->ID;
        }

        $post_id = wp_insert_post( wp_slash( $postarr ), true );
        if ( is_wp_error( $post_id ) ) {
            throw new RuntimeException( $post_id->get_error_message() );
        }

        update_post_meta( $post_id, '_content_source_id', $source_id );
        update_post_meta( $post_id, '_content_source_hash', $source_hash );
        update_post_meta( $post_id, '_content_schema_version', (int) ( $data['schema_version'] ?? 1 ) );
        update_post_meta( $post_id, '_content_article_number', $number );
        update_post_meta( $post_id, '_content_language', $language );
        update_post_meta( $post_id, '_content_translation_group', $translation_group );
        update_post_meta( $post_id, '_content_locale', isset( $data['locale'] ) ? (string) $data['locale'] : $language );
        update_post_meta( $post_id, '_content_market_context', isset( $data['market_context'] ) ? (string) $data['market_context'] : '' );
        update_post_meta( $post_id, '_content_source_json', str_replace( $package_root . '/', '', $file ) );
        update_post_meta( $post_id, '_content_faq', wp_json_encode( $faq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
        update_post_meta( $post_id, '_content_sources', wp_json_encode( $sources, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
        update_post_meta( $post_id, '_content_image_concept', isset( $image['concept'] ) ? (string) $image['concept'] : '' );
        update_post_meta( $post_id, '_content_image_alt', isset( $image['alt'] ) ? (string) $image['alt'] : '' );
        update_post_meta( $post_id, '_content_managed_article', '1' );

        content_import_apply_taxonomy( $post_id, $taxonomy, $platform, $term_cache );
        content_import_save_seo( $post_id, $seo );

        if ( function_exists( 'pll_set_post_language' ) ) {
            pll_set_post_language( $post_id, $language );
        }

        $groups[] = $translation_group;
        if ( $existing instanceof WP_Post ) {
            ++$updated;
            echo "UPDATED #{$number} [{$language}] post_id={$post_id} slug={$slug}\n";
        } else {
            ++$created;
            echo "CREATED #{$number} [{$language}] post_id={$post_id} slug={$slug}\n";
        }
    } catch ( Throwable $e ) {
        ++$failed;
        fwrite( STDERR, 'FAILED ' . basename( $file ) . ': ' . $e->getMessage() . "\n" );
    }
}

content_import_link_translations( $groups, $post_type );

if ( '' !== $options['successful_sha'] && 0 === $failed ) {
    update_option( 'content_platform_last_successful_import_sha', strtolower( (string) $options['successful_sha'] ), false );
}

echo "SUMMARY created={$created} updated={$updated} skipped={$skipped} failed={$failed}\n";
exit( $failed > 0 ? 1 : 0 );
