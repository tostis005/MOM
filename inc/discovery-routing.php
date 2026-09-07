<?php
/**
 * Standalone discovery pages for MOM navigation.
 *
 * @package MOM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mom_image_performance = get_template_directory() . '/inc/image-performance.php';
if ( file_exists( $mom_image_performance ) ) {
	require_once $mom_image_performance;
}

if ( ! defined( 'MOM_DISCOVERY_ROUTING_VERSION' ) ) {
	define( 'MOM_DISCOVERY_ROUTING_VERSION', '2026-09-07-1' );
}

function mom_discovery_pages() {
	return array(
		'topics' => array(
			'dimension' => 'topic',
			'es'        => 'temas',
			'en'        => 'topics',
			'label_es'  => 'Temas',
			'label_en'  => 'Topics',
		),
		'stages' => array(
			'dimension' => 'stage',
			'es'        => 'etapas',
			'en'        => 'stages',
			'label_es'  => 'Etapas',
			'label_en'  => 'Stages',
		),
		'audience' => array(
			'dimension' => 'audience',
			'es'        => 'para-quien',
			'en'        => 'for-whom',
			'label_es'  => 'Para quién',
			'label_en'  => 'For whom',
		),
		'latest' => array(
			'dimension' => '',
			'es'        => 'ultimos-articulos',
			'en'        => 'latest-articles',
			'label_es'  => 'Últimos artículos',
			'label_en'  => 'Latest articles',
		),
	);
}

function mom_discovery_url( $hub, $language = '' ) {
	$pages = mom_discovery_pages();
	if ( empty( $pages[ $hub ] ) ) {
		return mom_language_home_url( $language );
	}
	$language = in_array( $language, array( 'es', 'en' ), true ) ? $language : ( mom_is_english() ? 'en' : 'es' );
	$prefix   = 'en' === $language ? '/en/' : '/';
	return home_url( $prefix . $pages[ $hub ][ $language ] . '/' );
}

function mom_context_language_switch_url( $language ) {
	$hub = (string) get_query_var( 'mom_hub' );
	if ( $hub && isset( mom_discovery_pages()[ $hub ] ) ) {
		return mom_discovery_url( $hub, $language );
	}
	return function_exists( 'mom_language_switch_url' ) ? mom_language_switch_url( $language ) : mom_language_home_url( $language );
}

function mom_discovery_register_rewrite_rules() {
	foreach ( mom_discovery_pages() as $hub => $page ) {
		foreach ( array( 'es', 'en' ) as $language ) {
			$head = 'en' === $language ? 'en/' : '';
			$slug = preg_quote( $page[ $language ], '#' );
			$query = 'index.php?mom_hub=' . rawurlencode( $hub ) . '&mom_lang=' . $language;
			add_rewrite_rule( '^' . $head . $slug . '/page/([0-9]+)/?$', $query . '&paged=$matches[1]', 'top' );
			add_rewrite_rule( '^' . $head . $slug . '/?$', $query, 'top' );
		}
	}
}
add_action( 'init', 'mom_discovery_register_rewrite_rules', 35 );

function mom_discovery_query_vars( $vars ) {
	$vars[] = 'mom_hub';
	return $vars;
}
add_filter( 'query_vars', 'mom_discovery_query_vars' );

function mom_discovery_maybe_flush_rewrites() {
	if ( MOM_DISCOVERY_ROUTING_VERSION === get_option( 'mom_discovery_routing_version' ) ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'mom_discovery_routing_version', MOM_DISCOVERY_ROUTING_VERSION, false );
}
add_action( 'wp_loaded', 'mom_discovery_maybe_flush_rewrites', 40 );

function mom_discovery_prepare_virtual_page() {
	$hub = (string) get_query_var( 'mom_hub' );
	if ( ! $hub || ! isset( mom_discovery_pages()[ $hub ] ) ) {
		return;
	}
	global $wp_query;
	if ( $wp_query instanceof WP_Query ) {
		$wp_query->is_404 = false;
		$wp_query->is_home = false;
		$wp_query->is_archive = true;
	}
	status_header( 200 );
}
add_action( 'template_redirect', 'mom_discovery_prepare_virtual_page', 0 );

function mom_discovery_template( $template ) {
	$hub = (string) get_query_var( 'mom_hub' );
	if ( ! $hub || ! isset( mom_discovery_pages()[ $hub ] ) ) {
		return $template;
	}
	$discovery_template = locate_template( 'discovery.php' );
	return $discovery_template ? $discovery_template : $template;
}
add_filter( 'template_include', 'mom_discovery_template', 100 );

function mom_discovery_document_title( $title ) {
	$hub   = (string) get_query_var( 'mom_hub' );
	$pages = mom_discovery_pages();
	if ( ! $hub || empty( $pages[ $hub ] ) ) {
		return $title;
	}
	return mom_is_english() ? $pages[ $hub ]['label_en'] : $pages[ $hub ]['label_es'];
}
add_filter( 'pre_get_document_title', 'mom_discovery_document_title', 20 );

function mom_discovery_hreflang_links() {
	$hub = (string) get_query_var( 'mom_hub' );
	if ( $hub && isset( mom_discovery_pages()[ $hub ] ) ) {
		$es = mom_discovery_url( $hub, 'es' );
		$en = mom_discovery_url( $hub, 'en' );
		echo '<link rel="alternate" hreflang="es-ES" href="' . esc_url( $es ) . '">' . "\n";
		echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $es ) . '">' . "\n";
		echo '<link rel="alternate" hreflang="en-US" href="' . esc_url( $en ) . '">' . "\n";
		return;
	}
	if ( function_exists( 'mom_i18n_hreflang_links' ) ) {
		mom_i18n_hreflang_links();
	}
}

if ( function_exists( 'mom_i18n_hreflang_links' ) ) {
	remove_action( 'wp_head', 'mom_i18n_hreflang_links', 3 );
	add_action( 'wp_head', 'mom_discovery_hreflang_links', 3 );
}

function mom_discovery_image_url( $dimension, $term_id ) {
	$prefixes = array(
		'topic'    => 'topic',
		'stage'    => 'stage',
		'audience' => 'audience',
	);
	if ( empty( $prefixes[ $dimension ] ) || ! $term_id ) {
		return '';
	}
	$filename = $prefixes[ $dimension ] . '-' . sanitize_file_name( $term_id ) . '.jpg';
	if ( function_exists( 'mom_static_image_url' ) ) {
		return mom_static_image_url( $filename, 'thumb' );
	}
	$file = trailingslashit( get_template_directory() ) . 'assets/images/hq/' . $filename;
	if ( ! file_exists( $file ) ) {
		return '';
	}
	return trailingslashit( get_template_directory_uri() ) . 'assets/images/hq/' . rawurlencode( $filename );
}
