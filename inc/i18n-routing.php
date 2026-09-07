<?php
/**
 * Language routing and localized URLs for MOM.
 *
 * Spanish lives at the site root and English lives below /en/.
 * This works with or without Polylang; when Polylang is present we still use
 * the content translation metadata as the canonical fallback.
 *
 * @package MOM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'MOM_I18N_ROUTING_VERSION' ) ) {
	define( 'MOM_I18N_ROUTING_VERSION', '2026-09-07-1' );
}

function mom_i18n_request_path() {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	$path        = (string) wp_parse_url( $request_uri, PHP_URL_PATH );
	$home_path   = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );

	if ( $home_path && '/' !== $home_path ) {
		$home_path = '/' . trim( $home_path, '/' );
		if ( 0 === strpos( $path, $home_path ) ) {
			$path = substr( $path, strlen( $home_path ) );
		}
	}

	return '/' . trim( $path, '/' );
}

function mom_current_language() {
	$query_language = function_exists( 'get_query_var' ) ? (string) get_query_var( 'mom_lang' ) : '';
	if ( in_array( $query_language, array( 'es', 'en' ), true ) ) {
		return $query_language;
	}

	$path = mom_i18n_request_path();
	if ( '/en' === $path || 0 === strpos( $path, '/en/' ) ) {
		return 'en';
	}

	if ( function_exists( 'pll_current_language' ) ) {
		$language = pll_current_language( 'slug' );
		if ( in_array( $language, array( 'es', 'en' ), true ) ) {
			return $language;
		}
	}

	if ( function_exists( 'get_queried_object_id' ) ) {
		$post_id = (int) get_queried_object_id();
		if ( $post_id ) {
			$language = (string) get_post_meta( $post_id, '_content_language', true );
			if ( in_array( $language, array( 'es', 'en' ), true ) ) {
				return $language;
			}
		}
	}

	return 'es';
}

function mom_i18n_home_url( $language = '' ) {
	$language = in_array( $language, array( 'es', 'en' ), true ) ? $language : mom_current_language();
	return 'en' === $language ? home_url( '/en/' ) : home_url( '/' );
}

function mom_i18n_post_language( $post_id ) {
	$language = (string) get_post_meta( (int) $post_id, '_content_language', true );
	return in_array( $language, array( 'es', 'en' ), true ) ? $language : 'es';
}

function mom_i18n_post_url( $post_id ) {
	$post = get_post( (int) $post_id );
	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
		return '';
	}

	$slug = (string) $post->post_name;
	if ( '' === $slug ) {
		return '';
	}

	$language = mom_i18n_post_language( $post->ID );
	$path     = 'en' === $language ? '/en/' . $slug . '/' : '/' . $slug . '/';
	return home_url( $path );
}

function mom_i18n_filter_post_link( $permalink, $post ) {
	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
		return $permalink;
	}

	$language = (string) get_post_meta( $post->ID, '_content_language', true );
	if ( ! in_array( $language, array( 'es', 'en' ), true ) ) {
		return $permalink;
	}

	$localized = mom_i18n_post_url( $post->ID );
	return $localized ? $localized : $permalink;
}
add_filter( 'post_link', 'mom_i18n_filter_post_link', 20, 2 );

function mom_i18n_dimension_from_taxonomy( $taxonomy ) {
	if ( function_exists( 'content_platform_taxonomies' ) ) {
		foreach ( content_platform_taxonomies() as $definition ) {
			if ( ! empty( $definition['wp_taxonomy'] ) && (string) $definition['wp_taxonomy'] === (string) $taxonomy ) {
				return ! empty( $definition['dimension'] ) ? (string) $definition['dimension'] : '';
			}
		}
	}

	$map = array(
		'content_topic'        => 'topic',
		'content_stage'        => 'stage',
		'content_audience'     => 'audience',
		'content_article_type' => 'article_type',
	);
	return isset( $map[ $taxonomy ] ) ? $map[ $taxonomy ] : '';
}

function mom_i18n_taxonomy_base( $dimension, $language ) {
	$bases = array(
		'topic'        => array( 'es' => 'temas', 'en' => 'topics' ),
		'stage'        => array( 'es' => 'etapas', 'en' => 'stages' ),
		'audience'     => array( 'es' => 'para-quien', 'en' => 'for-whom' ),
		'article_type' => array( 'es' => 'tipo', 'en' => 'article-type' ),
	);

	if ( isset( $bases[ $dimension ][ $language ] ) ) {
		return $bases[ $dimension ][ $language ];
	}
	return sanitize_title( $dimension );
}

function mom_i18n_term_slug( $dimension, $term_id, $language ) {
	if ( function_exists( 'content_platform_term_definition' ) && function_exists( 'content_platform_localized_value' ) ) {
		$definition = content_platform_term_definition( $dimension, $term_id );
		if ( ! empty( $definition ) ) {
			$slug = content_platform_localized_value( isset( $definition['slug'] ) ? $definition['slug'] : $term_id, $language );
			if ( '' !== $slug ) {
				return sanitize_title( $slug );
			}
		}
	}

	$taxonomy = function_exists( 'mom_taxonomy_name' ) ? mom_taxonomy_name( $dimension ) : '';
	if ( $taxonomy && taxonomy_exists( $taxonomy ) ) {
		$term = get_term_by( 'slug', sanitize_title( $term_id ), $taxonomy );
		if ( $term instanceof WP_Term ) {
			$stored = get_term_meta( $term->term_id, '_content_localized_slugs', true );
			$data   = is_string( $stored ) ? json_decode( $stored, true ) : array();
			if ( is_array( $data ) && ! empty( $data[ $language ] ) ) {
				return sanitize_title( (string) $data[ $language ] );
			}
		}
	}

	return sanitize_title( $term_id );
}

function mom_i18n_term_url( $dimension, $term_id, $language = '' ) {
	$language = in_array( $language, array( 'es', 'en' ), true ) ? $language : mom_current_language();
	$base     = mom_i18n_taxonomy_base( $dimension, $language );
	$slug     = mom_i18n_term_slug( $dimension, $term_id, $language );
	$prefix   = 'en' === $language ? '/en/' : '/';
	return home_url( $prefix . $base . '/' . $slug . '/' );
}

function mom_i18n_filter_term_link( $termlink, $term, $taxonomy ) {
	$dimension = mom_i18n_dimension_from_taxonomy( $taxonomy );
	if ( ! $dimension || ! $term instanceof WP_Term ) {
		return $termlink;
	}
	$term_id = (string) get_term_meta( $term->term_id, '_content_term_id', true );
	$term_id = $term_id ? $term_id : (string) $term->slug;
	return mom_i18n_term_url( $dimension, $term_id, mom_current_language() );
}
add_filter( 'term_link', 'mom_i18n_filter_term_link', 20, 3 );

function mom_i18n_localized_term_label( $term, $language = '' ) {
	if ( ! $term instanceof WP_Term ) {
		return '';
	}
	$language  = in_array( $language, array( 'es', 'en' ), true ) ? $language : mom_current_language();
	$dimension = mom_i18n_dimension_from_taxonomy( $term->taxonomy );
	$term_id   = (string) get_term_meta( $term->term_id, '_content_term_id', true );
	$term_id   = $term_id ? $term_id : (string) $term->slug;

	if ( $dimension && function_exists( 'content_platform_term_label' ) ) {
		return content_platform_term_label( $dimension, $term_id, $language );
	}
	return (string) $term->name;
}

function mom_i18n_filter_single_term_title( $title ) {
	$term = get_queried_object();
	if ( $term instanceof WP_Term ) {
		$localized = mom_i18n_localized_term_label( $term );
		return $localized ? $localized : $title;
	}
	return $title;
}
add_filter( 'single_term_title', 'mom_i18n_filter_single_term_title', 20 );

function mom_i18n_register_rewrite_rules() {
	if ( function_exists( 'content_platform_taxonomies' ) ) {
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
				$term_id = sanitize_title( (string) $term['id'] );

				foreach ( array( 'es', 'en' ) as $language ) {
					$base = preg_quote( mom_i18n_taxonomy_base( $dimension, $language ), '#' );
					$slug = preg_quote( mom_i18n_term_slug( $dimension, $term['id'], $language ), '#' );
					$head = 'en' === $language ? 'en/' : '';
					$query = 'index.php?' . $taxonomy . '=' . $term_id . '&mom_lang=' . $language;
					add_rewrite_rule( '^' . $head . $base . '/' . $slug . '/page/([0-9]+)/?$', $query . '&paged=$matches[1]', 'top' );
					add_rewrite_rule( '^' . $head . $base . '/' . $slug . '/?$', $query, 'top' );
				}
			}
		}
	}

	add_rewrite_rule( '^en/?$', 'index.php?mom_lang=en', 'top' );
	add_rewrite_rule( '^en/([^/]+)/?$', 'index.php?name=$matches[1]&mom_lang=en', 'top' );
}
add_action( 'init', 'mom_i18n_register_rewrite_rules', 20 );

function mom_i18n_query_vars( $vars ) {
	$vars[] = 'mom_lang';
	return $vars;
}
add_filter( 'query_vars', 'mom_i18n_query_vars' );

function mom_i18n_maybe_flush_rewrites() {
	if ( MOM_I18N_ROUTING_VERSION === get_option( 'mom_i18n_routing_version' ) ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'mom_i18n_routing_version', MOM_I18N_ROUTING_VERSION, false );
}
add_action( 'wp_loaded', 'mom_i18n_maybe_flush_rewrites', 30 );

function mom_i18n_filter_queries( $query ) {
	if ( is_admin() || 'cli' === PHP_SAPI || $query->get( 'suppress_filters' ) ) {
		return;
	}

	$path = mom_i18n_request_path();
	if ( $query->is_main_query() && ( '/' === $path || '/en' === $path ) ) {
		return;
	}

	if ( $query->is_page() || $query->get( 'page_id' ) || $query->get( 'pagename' ) ) {
		return;
	}

	$post_type = $query->get( 'post_type' );
	if ( $post_type && 'post' !== $post_type && ! ( is_array( $post_type ) && in_array( 'post', $post_type, true ) ) ) {
		return;
	}

	$language = (string) $query->get( 'mom_lang' );
	if ( ! in_array( $language, array( 'es', 'en' ), true ) ) {
		$language = mom_current_language();
	}

	$meta_query = $query->get( 'meta_query' );
	$meta_query = is_array( $meta_query ) ? $meta_query : array();
	$meta_query[] = array(
		'key'   => '_content_language',
		'value' => $language,
	);
	$query->set( 'meta_query', $meta_query );
}
add_action( 'pre_get_posts', 'mom_i18n_filter_queries', 20 );

function mom_i18n_front_page_template( $template ) {
	if ( '/en' !== mom_i18n_request_path() ) {
		return $template;
	}
	$front_page = locate_template( 'front-page.php' );
	return $front_page ? $front_page : $template;
}
add_filter( 'template_include', 'mom_i18n_front_page_template', 99 );

function mom_i18n_find_translation( $post_id, $language ) {
	$post_id   = (int) $post_id;
	$language  = in_array( $language, array( 'es', 'en' ), true ) ? $language : 'es';
	$pll_match = 0;

	if ( function_exists( 'pll_get_post' ) ) {
		$pll_match = (int) pll_get_post( $post_id, $language );
		if ( $pll_match ) {
			return $pll_match;
		}
	}

	$group = (string) get_post_meta( $post_id, '_content_translation_group', true );
	if ( '' === $group ) {
		return 0;
	}

	$posts = get_posts(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'suppress_filters'       => true,
			'ignore_sticky_posts'    => true,
			'meta_query'             => array(
				array( 'key' => '_content_translation_group', 'value' => $group ),
				array( 'key' => '_content_language', 'value' => $language ),
			),
		)
	);
	return ! empty( $posts ) ? (int) $posts[0] : 0;
}

function mom_language_switch_url( $language ) {
	$language = in_array( $language, array( 'es', 'en' ), true ) ? $language : 'es';

	if ( is_singular( 'post' ) ) {
		$translation_id = mom_i18n_find_translation( get_queried_object_id(), $language );
		if ( $translation_id ) {
			$url = mom_i18n_post_url( $translation_id );
			if ( $url ) {
				return $url;
			}
		}
	}

	if ( is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$dimension = mom_i18n_dimension_from_taxonomy( $term->taxonomy );
			$term_id   = (string) get_term_meta( $term->term_id, '_content_term_id', true );
			$term_id   = $term_id ? $term_id : (string) $term->slug;
			if ( $dimension ) {
				return mom_i18n_term_url( $dimension, $term_id, $language );
			}
		}
	}

	if ( is_search() ) {
		return add_query_arg( 's', get_search_query(), mom_i18n_home_url( $language ) );
	}

	return mom_i18n_home_url( $language );
}

function mom_i18n_canonical_article_redirect() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	$post_id = (int) get_queried_object_id();
	if ( ! $post_id || ! get_post_meta( $post_id, '_content_language', true ) ) {
		return;
	}
	$canonical = mom_i18n_post_url( $post_id );
	$current   = home_url( trailingslashit( mom_i18n_request_path() ) );
	if ( $canonical && untrailingslashit( $canonical ) !== untrailingslashit( $current ) ) {
		wp_safe_redirect( $canonical, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'mom_i18n_canonical_article_redirect', 1 );

function mom_i18n_hreflang_links() {
	if ( is_admin() ) {
		return;
	}
	$es = mom_language_switch_url( 'es' );
	$en = mom_language_switch_url( 'en' );
	if ( $es ) {
		echo '<link rel="alternate" hreflang="es-ES" href="' . esc_url( $es ) . '">' . "\n";
		echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $es ) . '">' . "\n";
	}
	if ( $en ) {
		echo '<link rel="alternate" hreflang="en-US" href="' . esc_url( $en ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'mom_i18n_hreflang_links', 3 );

function mom_i18n_language_attributes( $output ) {
	$lang = 'en' === mom_current_language() ? 'en-US' : 'es-ES';
	if ( preg_match( '/lang=("|\')[^"\']+("|\')/i', $output ) ) {
		return preg_replace( '/lang=("|\')[^"\']+("|\')/i', 'lang="' . esc_attr( $lang ) . '"', $output, 1 );
	}
	return trim( $output . ' lang="' . esc_attr( $lang ) . '"' );
}
add_filter( 'language_attributes', 'mom_i18n_language_attributes', 20 );

function mom_i18n_body_classes( $classes ) {
	$classes[] = 'lang-' . mom_current_language();
	return $classes;
}
add_filter( 'body_class', 'mom_i18n_body_classes', 20 );
