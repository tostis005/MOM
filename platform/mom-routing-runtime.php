<?php
/**
 * Plugin Name: MOM Routing Runtime
 * Description: Runtime resolver for localized MOM routes. Keeps / in Spanish and /en/ in English without relying only on cached rewrite rules.
 * Version: 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'MOM_ROUTING_RUNTIME_VERSION' ) ) {
    define( 'MOM_ROUTING_RUNTIME_VERSION', '2026-09-07-2' );
}

function mom_runtime_request_path() {
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
    $path        = (string) wp_parse_url( $request_uri, PHP_URL_PATH );
    $home_path   = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );

    if ( $home_path && '/' !== $home_path ) {
        $home_path = '/' . trim( $home_path, '/' );
        if ( 0 === strpos( $path, $home_path ) ) {
            $path = substr( $path, strlen( $home_path ) );
        }
    }

    return trim( $path, '/' );
}

function mom_runtime_polylang_active() {
    return function_exists( 'pll_languages_list' ) || function_exists( 'pll_current_language' ) || defined( 'POLYLANG_VERSION' );
}

function mom_runtime_taxonomy_base( $dimension, $language ) {
    $bases = array(
        'topic'        => array( 'es' => 'temas', 'en' => 'topics' ),
        'stage'        => array( 'es' => 'etapas', 'en' => 'stages' ),
        'audience'     => array( 'es' => 'para-quien', 'en' => 'for-whom' ),
        'article_type' => array( 'es' => 'tipo', 'en' => 'article-type' ),
    );

    return isset( $bases[ $dimension ][ $language ] ) ? $bases[ $dimension ][ $language ] : sanitize_title( $dimension );
}

function mom_runtime_localized_value( $value, $language ) {
    if ( function_exists( 'content_platform_localized_value' ) ) {
        return content_platform_localized_value( $value, $language );
    }
    if ( is_array( $value ) && isset( $value[ $language ] ) && is_scalar( $value[ $language ] ) ) {
        return (string) $value[ $language ];
    }
    if ( is_scalar( $value ) ) {
        return (string) $value;
    }
    return '';
}

function mom_runtime_apply_language( &$query_vars, $language ) {
    $query_vars['mom_lang'] = $language;
    if ( mom_runtime_polylang_active() ) {
        $query_vars['lang'] = $language;
    }
}

function mom_runtime_clear_route_vars( &$query_vars ) {
    foreach ( array( 'error', 'name', 'pagename', 'page_id', 'attachment', 'attachment_id', 'category_name', 'tag' ) as $key ) {
        unset( $query_vars[ $key ] );
    }
}

function mom_runtime_find_managed_post( $slug, $language ) {
    $posts = get_posts(
        array(
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => 1,
            'fields'                 => 'ids',
            'name'                   => sanitize_title( $slug ),
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'suppress_filters'       => true,
            'meta_query'             => array(
                array( 'key' => '_content_language', 'value' => $language ),
            ),
        )
    );

    return ! empty( $posts ) ? (int) $posts[0] : 0;
}

function mom_runtime_match_taxonomy_path( $path ) {
    if ( ! function_exists( 'content_platform_taxonomies' ) ) {
        return array();
    }

    foreach ( content_platform_taxonomies() as $definition ) {
        if ( empty( $definition['dimension'] ) || empty( $definition['wp_taxonomy'] ) || empty( $definition['terms'] ) || ! is_array( $definition['terms'] ) ) {
            continue;
        }

        $dimension = (string) $definition['dimension'];
        $taxonomy  = (string) $definition['wp_taxonomy'];

        foreach ( $definition['terms'] as $term ) {
            if ( empty( $term['id'] ) ) {
                continue;
            }

            foreach ( array( 'es', 'en' ) as $language ) {
                $base   = mom_runtime_taxonomy_base( $dimension, $language );
                $slug   = sanitize_title( mom_runtime_localized_value( isset( $term['slug'] ) ? $term['slug'] : $term['id'], $language ) );
                $prefix = 'en' === $language ? 'en/' : '';
                $route  = $prefix . trim( $base, '/' ) . '/' . $slug;

                if ( $path === $route ) {
                    return array(
                        'language' => $language,
                        'taxonomy' => $taxonomy,
                        'term_id'  => sanitize_title( (string) $term['id'] ),
                        'paged'    => 0,
                    );
                }

                if ( 0 === strpos( $path, $route . '/page/' ) ) {
                    $page = substr( $path, strlen( $route . '/page/' ) );
                    if ( ctype_digit( $page ) && (int) $page > 0 ) {
                        return array(
                            'language' => $language,
                            'taxonomy' => $taxonomy,
                            'term_id'  => sanitize_title( (string) $term['id'] ),
                            'paged'    => (int) $page,
                        );
                    }
                }
            }
        }
    }

    return array();
}

function mom_runtime_parse_request( $wp ) {
    if ( is_admin() || ! $wp instanceof WP ) {
        return;
    }

    $path = mom_runtime_request_path();

    if ( 'en' === $path ) {
        mom_runtime_clear_route_vars( $wp->query_vars );
        mom_runtime_apply_language( $wp->query_vars, 'en' );
        $GLOBALS['mom_runtime_routed'] = true;
        $wp->matched_rule              = 'mom-runtime-en-home';
        return;
    }

    $taxonomy_match = mom_runtime_match_taxonomy_path( $path );
    if ( ! empty( $taxonomy_match ) ) {
        mom_runtime_clear_route_vars( $wp->query_vars );
        $wp->query_vars[ $taxonomy_match['taxonomy'] ] = $taxonomy_match['term_id'];
        if ( $taxonomy_match['paged'] ) {
            $wp->query_vars['paged'] = $taxonomy_match['paged'];
        }
        mom_runtime_apply_language( $wp->query_vars, $taxonomy_match['language'] );
        $GLOBALS['mom_runtime_routed'] = true;
        $wp->matched_rule              = 'mom-runtime-taxonomy';
        return;
    }

    $language = 'es';
    $slug     = $path;
    if ( 0 === strpos( $path, 'en/' ) ) {
        $language = 'en';
        $slug     = substr( $path, 3 );
    }

    if ( '' === $slug || false !== strpos( $slug, '/' ) ) {
        return;
    }

    $post_id = mom_runtime_find_managed_post( $slug, $language );
    if ( ! $post_id ) {
        return;
    }

    mom_runtime_clear_route_vars( $wp->query_vars );
    $wp->query_vars['p']         = $post_id;
    $wp->query_vars['post_type'] = 'post';
    mom_runtime_apply_language( $wp->query_vars, $language );
    $GLOBALS['mom_runtime_routed'] = true;
    $wp->matched_rule              = 'mom-runtime-article';
}
add_action( 'parse_request', 'mom_runtime_parse_request', 99 );

function mom_runtime_disable_canonical_for_routed_urls( $redirect_url ) {
    return ! empty( $GLOBALS['mom_runtime_routed'] ) ? false : $redirect_url;
}
add_filter( 'redirect_canonical', 'mom_runtime_disable_canonical_for_routed_urls', 1 );

function mom_runtime_recover_english_home_status() {
    if ( 'en' !== mom_runtime_request_path() ) {
        return;
    }

    global $wp_query;
    if ( $wp_query instanceof WP_Query && $wp_query->is_404() ) {
        $wp_query->is_404  = false;
        $wp_query->is_home = true;
        status_header( 200 );
    }
}
add_action( 'template_redirect', 'mom_runtime_recover_english_home_status', 0 );

function mom_runtime_flush_rewrites_once() {
    if ( MOM_ROUTING_RUNTIME_VERSION === get_option( 'mom_routing_runtime_version' ) ) {
        return;
    }
    flush_rewrite_rules( false );
    update_option( 'mom_routing_runtime_version', MOM_ROUTING_RUNTIME_VERSION, false );
}
add_action( 'wp_loaded', 'mom_runtime_flush_rewrites_once', 99 );
