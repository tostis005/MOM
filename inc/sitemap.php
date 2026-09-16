<?php
/**
 * Language-aware XML sitemaps for Matternal.
 *
 * Exposes one sitemap index with separate Spanish and English child sitemaps.
 * The child sitemaps are generated dynamically from published content so they
 * stay current as new posts and pages are published.
 *
 * @package MOM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'MOM_SITEMAP_VERSION' ) ) {
	define( 'MOM_SITEMAP_VERSION', '2026-09-16-1' );
}

function mom_sitemap_register_rewrite_rules() {
	add_rewrite_rule( '^sitemap\.xml$', 'index.php?mom_sitemap=index', 'top' );
	add_rewrite_rule( '^sitemap-es\.xml$', 'index.php?mom_sitemap=es', 'top' );
	add_rewrite_rule( '^sitemap-en\.xml$', 'index.php?mom_sitemap=en', 'top' );
}
add_action( 'init', 'mom_sitemap_register_rewrite_rules', 5 );

function mom_sitemap_query_vars( $vars ) {
	$vars[] = 'mom_sitemap';
	return $vars;
}
add_filter( 'query_vars', 'mom_sitemap_query_vars' );

function mom_sitemap_maybe_flush_rewrites() {
	if ( MOM_SITEMAP_VERSION === get_option( 'mom_sitemap_version' ) ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'mom_sitemap_version', MOM_SITEMAP_VERSION, false );
}
add_action( 'wp_loaded', 'mom_sitemap_maybe_flush_rewrites', 35 );

function mom_sitemap_xml_escape( $value ) {
	return htmlspecialchars( (string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8' );
}

function mom_sitemap_index_url() {
	return home_url( '/sitemap.xml' );
}

function mom_sitemap_language_url( $language ) {
	$language = 'en' === $language ? 'en' : 'es';
	return home_url( '/sitemap-' . $language . '.xml' );
}

function mom_sitemap_content_language( $post ) {
	$post = $post instanceof WP_Post ? $post : get_post( (int) $post );
	if ( ! $post instanceof WP_Post ) {
		return 'es';
	}

	$language = (string) get_post_meta( $post->ID, '_content_language', true );
	if ( in_array( $language, array( 'es', 'en' ), true ) ) {
		return $language;
	}

	if ( function_exists( 'pll_get_post_language' ) ) {
		$language = (string) pll_get_post_language( $post->ID, 'slug' );
		if ( in_array( $language, array( 'es', 'en' ), true ) ) {
			return $language;
		}
	}

	$permalink = get_permalink( $post );
	$path      = $permalink ? (string) wp_parse_url( $permalink, PHP_URL_PATH ) : '';
	return ( '/en' === rtrim( $path, '/' ) || 0 === strpos( $path, '/en/' ) ) ? 'en' : 'es';
}

function mom_sitemap_add_url( &$urls, $loc, $lastmod = '' ) {
	$loc = trim( (string) $loc );
	if ( '' === $loc ) {
		return;
	}

	$urls[ $loc ] = array(
		'loc'     => $loc,
		'lastmod' => (string) $lastmod,
	);
}

function mom_sitemap_home_url( $language ) {
	if ( function_exists( 'mom_i18n_home_url' ) ) {
		return mom_i18n_home_url( $language );
	}
	return 'en' === $language ? home_url( '/en/' ) : home_url( '/' );
}

function mom_sitemap_discovery_url( $hub, $language ) {
	if ( function_exists( 'mom_i18n_discovery_url' ) ) {
		return mom_i18n_discovery_url( $hub, $language );
	}
	if ( function_exists( 'mom_discovery_url' ) ) {
		return mom_discovery_url( $hub, $language );
	}

	$paths = array(
		'es' => array(
			'topics'   => '/temas/',
			'stages'   => '/etapas/',
			'audience' => '/para-quien/',
			'latest'   => '/ultimos-articulos/',
		),
		'en' => array(
			'topics'   => '/en/topics/',
			'stages'   => '/en/stages/',
			'audience' => '/en/for-whom/',
			'latest'   => '/en/latest-articles/',
		),
	);

	return isset( $paths[ $language ][ $hub ] ) ? home_url( $paths[ $language ][ $hub ] ) : '';
}

function mom_sitemap_add_taxonomy_urls( &$urls, $language ) {
	if ( ! function_exists( 'content_platform_taxonomies' ) || ! function_exists( 'mom_i18n_term_url' ) ) {
		return;
	}

	$allowed_dimensions = array( 'topic', 'stage', 'audience', 'article_type' );
	foreach ( content_platform_taxonomies() as $definition ) {
		if ( empty( $definition['dimension'] ) || empty( $definition['terms'] ) || ! is_array( $definition['terms'] ) ) {
			continue;
		}

		$dimension = (string) $definition['dimension'];
		if ( ! in_array( $dimension, $allowed_dimensions, true ) ) {
			continue;
		}

		foreach ( $definition['terms'] as $term ) {
			if ( empty( $term['id'] ) ) {
				continue;
			}
			mom_sitemap_add_url( $urls, mom_i18n_term_url( $dimension, (string) $term['id'], $language ) );
		}
	}
}

function mom_sitemap_language_urls( $language ) {
	$language = 'en' === $language ? 'en' : 'es';
	$urls     = array();

	mom_sitemap_add_url( $urls, mom_sitemap_home_url( $language ) );

	foreach ( array( 'topics', 'stages', 'audience', 'latest' ) as $hub ) {
		mom_sitemap_add_url( $urls, mom_sitemap_discovery_url( $hub, $language ) );
	}
	mom_sitemap_add_taxonomy_urls( $urls, $language );

	$content_ids = get_posts(
		array(
			'post_type'           => array( 'post', 'page' ),
			'post_status'         => 'publish',
			'posts_per_page'      => -1,
			'fields'              => 'ids',
			'orderby'             => 'ID',
			'order'               => 'ASC',
			'no_found_rows'       => true,
			'suppress_filters'    => true,
			'ignore_sticky_posts' => true,
		)
	);

	foreach ( $content_ids as $content_id ) {
		$post = get_post( (int) $content_id );
		if ( ! $post instanceof WP_Post || $language !== mom_sitemap_content_language( $post ) ) {
			continue;
		}

		if ( 'post' === $post->post_type && function_exists( 'mom_i18n_post_url' ) ) {
			$loc = mom_i18n_post_url( $post->ID );
		} else {
			$loc = get_permalink( $post );
		}

		$lastmod = get_post_modified_time( 'c', true, $post );
		mom_sitemap_add_url( $urls, $loc, $lastmod );
	}

	return array_values( $urls );
}

function mom_sitemap_render_index() {
	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
	foreach ( array( 'es', 'en' ) as $language ) {
		echo "  <sitemap>\n";
		echo '    <loc>' . mom_sitemap_xml_escape( mom_sitemap_language_url( $language ) ) . "</loc>\n";
		echo "  </sitemap>\n";
	}
	echo "</sitemapindex>\n";
}

function mom_sitemap_render_language( $language ) {
	$urls = mom_sitemap_language_urls( $language );

	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
	foreach ( $urls as $entry ) {
		echo "  <url>\n";
		echo '    <loc>' . mom_sitemap_xml_escape( $entry['loc'] ) . "</loc>\n";
		if ( ! empty( $entry['lastmod'] ) ) {
			echo '    <lastmod>' . mom_sitemap_xml_escape( $entry['lastmod'] ) . "</lastmod>\n";
		}
		echo "  </url>\n";
	}
	echo "</urlset>\n";
}

function mom_sitemap_template_redirect() {
	$sitemap = (string) get_query_var( 'mom_sitemap' );
	if ( ! in_array( $sitemap, array( 'index', 'es', 'en' ), true ) ) {
		return;
	}

	status_header( 200 );
	nocache_headers();
	header( 'Content-Type: application/xml; charset=UTF-8' );

	if ( 'index' === $sitemap ) {
		mom_sitemap_render_index();
	} else {
		mom_sitemap_render_language( $sitemap );
	}
	exit;
}
add_action( 'template_redirect', 'mom_sitemap_template_redirect', 0 );

function mom_sitemap_robots_txt( $output, $public ) {
	$line = 'Sitemap: ' . mom_sitemap_index_url();
	if ( false !== strpos( (string) $output, $line ) ) {
		return $output;
	}

	$output = rtrim( (string) $output );
	return ( '' !== $output ? $output . "\n" : '' ) . $line . "\n";
}
add_filter( 'robots_txt', 'mom_sitemap_robots_txt', 20, 2 );
