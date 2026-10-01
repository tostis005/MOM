<?php
/**
 * Lightweight native SEO fallbacks for Matternal.
 *
 * The content importer stores SEO titles and descriptions in post meta. This
 * module makes sure WordPress actually renders those values when a dedicated
 * SEO plugin is not doing it, and keeps archive/indexing signals consistent
 * with the site's information architecture.
 *
 * @package MOM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mom_seo_plugin_handles_metadata() {
	return defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' );
}

function mom_seo_language() {
	if ( function_exists( 'mom_current_language' ) ) {
		return mom_current_language();
	}
	return function_exists( 'mom_is_english' ) && mom_is_english() ? 'en' : 'es';
}

function mom_seo_hub_copy( $hub, $language ) {
	$copy = array(
		'topics' => array(
			'es' => array(
				'title'       => 'Maternidad y crianza por temas | Matternal',
				'description' => 'Explora guías prácticas de Matternal sobre sueño, alimentación, crianza, posparto, pareja, rutinas, colegio y vida familiar.',
			),
			'en' => array(
				'title'       => 'Parenting and motherhood topics | Matternal',
				'description' => 'Explore practical Matternal guides on sleep, feeding, parenting, postpartum, relationships, routines, school and family life.',
			),
		),
		'stages' => array(
			'es' => array(
				'title'       => 'Maternidad y crianza por etapas | Matternal',
				'description' => 'Encuentra contenido práctico para embarazo, posparto, recién nacido, bebé, toddler, infantil, edad escolar y vida familiar.',
			),
			'en' => array(
				'title'       => 'Parenting and motherhood by stage | Matternal',
				'description' => 'Find practical content for pregnancy, postpartum, newborns, babies, toddlers, preschool, school age and everyday family life.',
			),
		),
		'audience' => array(
			'es' => array(
				'title'       => 'Contenido para madres, padres y cuidadores | Matternal',
				'description' => 'Explora contenido de Matternal pensado para madres, padres, parejas, familiares y otras personas que cuidan.',
			),
			'en' => array(
				'title'       => 'Content for parents and caregivers | Matternal',
				'description' => 'Explore Matternal content for mothers, fathers, couples, relatives and other caregivers.',
			),
		),
		'latest' => array(
			'es' => array(
				'title'       => 'Últimos artículos | Matternal',
				'description' => 'Los artículos más recientes de Matternal sobre maternidad, crianza y vida familiar.',
			),
			'en' => array(
				'title'       => 'Latest articles | Matternal',
				'description' => 'The latest Matternal articles on motherhood, parenting and family life.',
			),
		),
	);

	return isset( $copy[ $hub ][ $language ] ) ? $copy[ $hub ][ $language ] : array();
}

function mom_seo_home_copy( $language ) {
	if ( 'en' === $language ) {
		return array(
			'title'       => 'Matternal | Practical motherhood and parenting guides',
			'description' => 'Practical, thoughtful guides for motherhood, parenting and family life: sleep, feeding, routines, relationships, school and everyday decisions.',
		);
	}
	return array(
		'title'       => 'Matternal | Guías prácticas de maternidad y crianza',
		'description' => 'Guías prácticas y cuidadas sobre maternidad, crianza y vida familiar: sueño, alimentación, rutinas, pareja, colegio y decisiones cotidianas.',
	);
}

function mom_seo_post_title( $post_id ) {
	return trim( (string) get_post_meta( (int) $post_id, '_content_seo_title', true ) );
}

function mom_seo_post_description( $post_id ) {
	$description = trim( (string) get_post_meta( (int) $post_id, '_content_meta_description', true ) );
	if ( '' !== $description ) {
		return $description;
	}
	$excerpt = trim( wp_strip_all_tags( (string) get_post_field( 'post_excerpt', (int) $post_id ) ) );
	return $excerpt ? wp_trim_words( $excerpt, 30, '' ) : '';
}

function mom_seo_document_title( $title ) {
	if ( mom_seo_plugin_handles_metadata() ) {
		return $title;
	}

	if ( is_singular( 'post' ) ) {
		$seo_title = mom_seo_post_title( get_queried_object_id() );
		return $seo_title ? $seo_title : $title;
	}

	$language = mom_seo_language();
	$hub      = (string) get_query_var( 'mom_hub' );
	if ( $hub ) {
		$copy = mom_seo_hub_copy( $hub, $language );
		if ( ! empty( $copy['title'] ) ) {
			return $copy['title'];
		}
	}

	if ( is_front_page() || is_home() ) {
		$copy = mom_seo_home_copy( $language );
		return $copy['title'];
	}

	return $title;
}
add_filter( 'pre_get_document_title', 'mom_seo_document_title', 30 );

function mom_seo_current_description() {
	if ( is_singular( 'post' ) ) {
		return mom_seo_post_description( get_queried_object_id() );
	}

	$language = mom_seo_language();
	$hub      = (string) get_query_var( 'mom_hub' );
	if ( $hub ) {
		$copy = mom_seo_hub_copy( $hub, $language );
		return isset( $copy['description'] ) ? $copy['description'] : '';
	}

	if ( is_front_page() || is_home() ) {
		$copy = mom_seo_home_copy( $language );
		return $copy['description'];
	}

	if ( is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$description = trim( wp_strip_all_tags( (string) $term->description ) );
			if ( '' === $description && function_exists( 'mom_i18n_dimension_from_taxonomy' ) && function_exists( 'mom_topic_description' ) ) {
				$dimension = mom_i18n_dimension_from_taxonomy( $term->taxonomy );
				$term_id   = (string) get_term_meta( $term->term_id, '_content_term_id', true );
				if ( 'topic' === $dimension && $term_id ) {
					$description = mom_topic_description( $term_id );
				}
			}
			return $description;
		}
	}

	return '';
}

function mom_seo_render_meta_description() {
	if ( mom_seo_plugin_handles_metadata() ) {
		return;
	}
	$description = mom_seo_current_description();
	if ( '' !== $description ) {
		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'mom_seo_render_meta_description', 2 );

function mom_seo_filter_canonical_url( $canonical_url, $post ) {
	if ( $post instanceof WP_Post && 'post' === $post->post_type && function_exists( 'mom_i18n_post_url' ) ) {
		$localized = mom_i18n_post_url( $post->ID );
		if ( $localized ) {
			return $localized;
		}
	}
	return $canonical_url;
}
add_filter( 'get_canonical_url', 'mom_seo_filter_canonical_url', 20, 2 );

function mom_seo_archive_canonical_url() {
	$language = mom_seo_language();
	$paged    = max( 1, (int) get_query_var( 'paged' ) );

	if ( $paged > 1 ) {
		return get_pagenum_link( $paged );
	}

	$hub = (string) get_query_var( 'mom_hub' );
	if ( $hub && function_exists( 'mom_discovery_url' ) ) {
		return mom_discovery_url( $hub, $language );
	}

	if ( is_tax() && function_exists( 'mom_i18n_dimension_from_taxonomy' ) && function_exists( 'mom_i18n_term_url' ) ) {
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

	if ( is_front_page() || is_home() ) {
		return function_exists( 'mom_i18n_home_url' ) ? mom_i18n_home_url( $language ) : home_url( '/' );
	}

	return '';
}

function mom_seo_render_archive_canonical() {
	if ( mom_seo_plugin_handles_metadata() || is_singular() ) {
		return;
	}
	$url = mom_seo_archive_canonical_url();
	if ( $url ) {
		echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'mom_seo_render_archive_canonical', 4 );

function mom_seo_taxonomy_dimension_is_indexable( $dimension ) {
	return in_array( (string) $dimension, array( 'topic', 'stage' ), true );
}

function mom_seo_discovery_hub_is_indexable( $hub ) {
	return in_array( (string) $hub, array( 'topics', 'stages' ), true );
}

function mom_seo_robots( $robots ) {
	$hub = (string) get_query_var( 'mom_hub' );
	if ( $hub && ! mom_seo_discovery_hub_is_indexable( $hub ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'] );
	}

	if ( is_tax() && function_exists( 'mom_i18n_dimension_from_taxonomy' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$dimension = mom_i18n_dimension_from_taxonomy( $term->taxonomy );
			if ( ! mom_seo_taxonomy_dimension_is_indexable( $dimension ) ) {
				$robots['noindex'] = true;
				$robots['follow']  = true;
				unset( $robots['index'] );
			}
		}
	}

	return $robots;
}
add_filter( 'wp_robots', 'mom_seo_robots', 20 );

function mom_seo_term_has_language_content( $dimension, $term_id, $language ) {
	$language = 'en' === $language ? 'en' : 'es';
	$key      = (string) $dimension . ':' . (string) $term_id . ':' . $language;
	static $cache = array();
	if ( array_key_exists( $key, $cache ) ) {
		return $cache[ $key ];
	}

	$taxonomy = function_exists( 'mom_taxonomy_name' ) ? mom_taxonomy_name( $dimension ) : '';
	if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
		$cache[ $key ] = false;
		return false;
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'suppress_filters'       => true,
			'meta_query'             => array(
				array(
					'key'   => '_content_language',
					'value' => $language,
				),
			),
			'tax_query'              => array(
				array(
					'taxonomy'         => $taxonomy,
					'field'            => 'slug',
					'terms'            => sanitize_title( (string) $term_id ),
					'include_children' => true,
				),
			),
		)
	);

	$cache[ $key ] = ! empty( $query->posts );
	return $cache[ $key ];
}

function mom_seo_render_structured_data() {
	if ( mom_seo_plugin_handles_metadata() ) {
		return;
	}

	$language = 'en' === mom_seo_language() ? 'en-US' : 'es-ES';
	$home     = function_exists( 'mom_i18n_home_url' ) ? mom_i18n_home_url( mom_seo_language() ) : home_url( '/' );
	$graph    = array();

	if ( is_front_page() || is_home() ) {
		$copy    = mom_seo_home_copy( mom_seo_language() );
		$graph[] = array(
			'@type'       => 'WebSite',
			'@id'         => trailingslashit( $home ) . '#website',
			'url'         => $home,
			'name'        => 'Matternal',
			'description' => $copy['description'],
			'inLanguage'  => $language,
		);
		$graph[] = array(
			'@type' => 'Organization',
			'@id'   => trailingslashit( home_url( '/' ) ) . '#organization',
			'name'  => 'Matternal',
			'url'   => home_url( '/' ),
		);
	}

	if ( is_singular( 'post' ) ) {
		$post_id = get_queried_object_id();
		$url     = function_exists( 'mom_i18n_post_url' ) ? mom_i18n_post_url( $post_id ) : get_permalink( $post_id );
		$article = array(
			'@type'            => 'Article',
			'@id'              => $url . '#article',
			'mainEntityOfPage' => $url,
			'url'              => $url,
			'headline'         => get_the_title( $post_id ),
			'datePublished'    => get_post_time( 'c', true, $post_id ),
			'dateModified'     => get_post_modified_time( 'c', true, $post_id ),
			'inLanguage'       => $language,
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => 'Matternal',
				'url'   => home_url( '/' ),
			),
		);
		$description = mom_seo_post_description( $post_id );
		if ( $description ) {
			$article['description'] = $description;
		}
		$image = get_the_post_thumbnail_url( $post_id, 'full' );
		if ( $image ) {
			$article['image'] = array( $image );
		}
		$graph[] = $article;
	}

	if ( empty( $graph ) ) {
		return;
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_head', 'mom_seo_render_structured_data', 20 );


function mom_seo_related_post_ids( $post_id, $limit = 3 ) {
	$post_id  = (int) $post_id;
	$limit    = max( 1, (int) $limit );
	$language = (string) get_post_meta( $post_id, '_content_language', true );
	$language = in_array( $language, array( 'es', 'en' ), true ) ? $language : mom_seo_language();
	$topic_id = function_exists( 'mom_primary_topic_id' ) ? mom_primary_topic_id( $post_id ) : '';
	$taxonomy = function_exists( 'mom_taxonomy_name' ) ? mom_taxonomy_name( 'topic' ) : '';
	$ids      = array();

	$base_args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $limit,
		'fields'              => 'ids',
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
		'suppress_filters'    => true,
		'post__not_in'        => array( $post_id ),
		'meta_query'          => array(
			array(
				'key'   => '_content_language',
				'value' => $language,
			),
		),
		'orderby'             => 'date',
		'order'               => 'DESC',
	);

	if ( $taxonomy && $topic_id && taxonomy_exists( $taxonomy ) ) {
		$topic_args              = $base_args;
		$topic_args['tax_query'] = array(
			array(
				'taxonomy'         => $taxonomy,
				'field'            => 'slug',
				'terms'            => sanitize_title( (string) $topic_id ),
				'include_children' => true,
			),
		);
		$topic_query = new WP_Query( $topic_args );
		$ids         = array_values( array_map( 'intval', $topic_query->posts ) );
	}

	if ( count( $ids ) < $limit ) {
		$fallback_args                   = $base_args;
		$fallback_args['posts_per_page'] = $limit - count( $ids );
		$fallback_args['post__not_in']   = array_merge( array( $post_id ), $ids );
		$fallback_query                  = new WP_Query( $fallback_args );
		$ids                             = array_merge( $ids, array_map( 'intval', $fallback_query->posts ) );
	}

	return array_slice( array_values( array_unique( $ids ) ), 0, $limit );
}
