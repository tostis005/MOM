<?php
/**
 * Plugin Name: Content Platform
 * Description: Generic taxonomy and metadata runtime for JSON-managed editorial sites.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function content_platform_config() {
    $config = get_option( 'content_platform_config', array() );
    return is_array( $config ) ? $config : array();
}

function content_platform_site_config() {
    $config = content_platform_config();
    return isset( $config['site'] ) && is_array( $config['site'] ) ? $config['site'] : array();
}

function content_platform_taxonomies() {
    $config = content_platform_config();
    return isset( $config['taxonomies'] ) && is_array( $config['taxonomies'] ) ? $config['taxonomies'] : array();
}

function content_platform_current_language() {
    if ( function_exists( 'pll_current_language' ) ) {
        $language = pll_current_language( 'slug' );
        if ( is_string( $language ) && '' !== $language ) {
            return $language;
        }
    }

    $site = content_platform_site_config();
    if ( ! empty( $site['default_language'] ) ) {
        return (string) $site['default_language'];
    }

    $locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
    return strtolower( substr( (string) $locale, 0, 2 ) );
}

function content_platform_localized_value( $value, $language = '' ) {
    if ( ! is_array( $value ) ) {
        return is_scalar( $value ) ? (string) $value : '';
    }

    $language = $language ? $language : content_platform_current_language();
    if ( isset( $value[ $language ] ) && is_scalar( $value[ $language ] ) ) {
        return (string) $value[ $language ];
    }

    $site             = content_platform_site_config();
    $default_language = ! empty( $site['default_language'] ) ? (string) $site['default_language'] : '';
    if ( $default_language && isset( $value[ $default_language ] ) && is_scalar( $value[ $default_language ] ) ) {
        return (string) $value[ $default_language ];
    }

    foreach ( $value as $candidate ) {
        if ( is_scalar( $candidate ) ) {
            return (string) $candidate;
        }
    }

    return '';
}

function content_platform_dimension_config( $dimension ) {
    foreach ( content_platform_taxonomies() as $taxonomy ) {
        if ( isset( $taxonomy['dimension'] ) && (string) $taxonomy['dimension'] === (string) $dimension ) {
            return $taxonomy;
        }
    }
    return array();
}

function content_platform_taxonomy_name( $dimension ) {
    $config = content_platform_dimension_config( $dimension );
    return ! empty( $config['wp_taxonomy'] ) ? (string) $config['wp_taxonomy'] : '';
}

function content_platform_term_definition( $dimension, $term_id ) {
    $config = content_platform_dimension_config( $dimension );
    if ( empty( $config['terms'] ) || ! is_array( $config['terms'] ) ) {
        return array();
    }

    foreach ( $config['terms'] as $term ) {
        if ( isset( $term['id'] ) && (string) $term['id'] === (string) $term_id ) {
            return is_array( $term ) ? $term : array();
        }
    }

    return array();
}

function content_platform_term_label( $dimension, $term_id, $language = '' ) {
    $term = content_platform_term_definition( $dimension, $term_id );
    if ( empty( $term ) ) {
        return (string) $term_id;
    }
    return content_platform_localized_value( isset( $term['label'] ) ? $term['label'] : $term_id, $language );
}

function content_platform_home_axes() {
    $site = content_platform_site_config();
    return ! empty( $site['navigation']['home_axes'] ) && is_array( $site['navigation']['home_axes'] )
        ? array_values( $site['navigation']['home_axes'] )
        : array();
}

function content_platform_home_terms( $dimension, $language = '' ) {
    $config = content_platform_dimension_config( $dimension );
    if ( empty( $config['terms'] ) || ! is_array( $config['terms'] ) ) {
        return array();
    }

    $terms = array_filter(
        $config['terms'],
        static function ( $term ) {
            return is_array( $term )
                && empty( $term['parent'] )
                && ( ! isset( $term['home_visible'] ) || (bool) $term['home_visible'] );
        }
    );

    usort(
        $terms,
        static function ( $a, $b ) {
            return (int) ( $a['home_order'] ?? 9999 ) <=> (int) ( $b['home_order'] ?? 9999 );
        }
    );

    return array_map(
        static function ( $term ) use ( $dimension, $language ) {
            return array(
                'id'    => (string) $term['id'],
                'label' => content_platform_term_label( $dimension, $term['id'], $language ),
                'slug'  => content_platform_localized_value( $term['slug'] ?? $term['id'], $language ),
            );
        },
        $terms
    );
}

function content_platform_dimension_terms( $dimension, $parent = null, $language = '' ) {
    $config = content_platform_dimension_config( $dimension );
    if ( empty( $config['terms'] ) || ! is_array( $config['terms'] ) ) {
        return array();
    }

    $terms = array_filter(
        $config['terms'],
        static function ( $term ) use ( $parent ) {
            if ( ! is_array( $term ) || empty( $term['id'] ) ) {
                return false;
            }
            if ( null === $parent ) {
                return true;
            }
            $term_parent = isset( $term['parent'] ) && null !== $term['parent'] ? (string) $term['parent'] : '';
            return (string) $parent === $term_parent;
        }
    );

    usort(
        $terms,
        static function ( $a, $b ) {
            return (int) ( $a['order'] ?? $a['home_order'] ?? 9999 ) <=> (int) ( $b['order'] ?? $b['home_order'] ?? 9999 );
        }
    );

    return array_map(
        static function ( $term ) use ( $dimension, $language ) {
            return array(
                'id'     => (string) $term['id'],
                'parent' => isset( $term['parent'] ) && null !== $term['parent'] ? (string) $term['parent'] : '',
                'label'  => content_platform_term_label( $dimension, $term['id'], $language ),
                'slug'   => content_platform_localized_value( $term['slug'] ?? $term['id'], $language ),
            );
        },
        $terms
    );
}

function content_platform_child_terms( $dimension, $parent_id, $language = '' ) {
    return content_platform_dimension_terms( $dimension, (string) $parent_id, $language );
}

function content_platform_register_taxonomies() {
    $site      = content_platform_site_config();
    $post_type = ! empty( $site['post_type'] ) ? (string) $site['post_type'] : 'post';

    foreach ( content_platform_taxonomies() as $definition ) {
        if ( empty( $definition['wp_taxonomy'] ) || empty( $definition['dimension'] ) ) {
            continue;
        }

        $taxonomy = (string) $definition['wp_taxonomy'];
        if ( 'category' === $taxonomy || taxonomy_exists( $taxonomy ) ) {
            continue;
        }

        $label = content_platform_localized_value( $definition['labels'] ?? $definition['dimension'] );
        if ( '' === $label ) {
            $label = ucfirst( str_replace( '_', ' ', (string) $definition['dimension'] ) );
        }

        register_taxonomy(
            $taxonomy,
            array( $post_type ),
            array(
                'labels' => array(
                    'name'          => $label,
                    'singular_name' => $label,
                    'menu_name'     => $label,
                ),
                'public'            => ! isset( $definition['public'] ) || (bool) $definition['public'],
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'hierarchical'      => ! empty( $definition['hierarchical'] ),
                'query_var'         => true,
                'rewrite'           => array(
                    'slug'       => ! empty( $definition['rewrite_base'] ) ? (string) $definition['rewrite_base'] : $taxonomy,
                    'with_front' => false,
                ),
            )
        );
    }
}
add_action( 'init', 'content_platform_register_taxonomies', 5 );

function content_platform_primary_term_id( $post_id, $dimension ) {
    return (string) get_post_meta( (int) $post_id, '_content_primary_' . sanitize_key( $dimension ), true );
}

function content_platform_article_taxonomy( $post_id ) {
    $raw = get_post_meta( (int) $post_id, '_content_taxonomy', true );
    if ( is_array( $raw ) ) {
        return $raw;
    }
    if ( is_string( $raw ) && '' !== $raw ) {
        $decoded = json_decode( $raw, true );
        return is_array( $decoded ) ? $decoded : array();
    }
    return array();
}
