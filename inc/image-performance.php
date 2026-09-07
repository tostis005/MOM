<?php
/**
 * Runtime optimization for static editorial images and language-aware term counts.
 *
 * Static JPGs live inside the theme, so WordPress does not create intermediate
 * sizes for them. This layer creates cached derivatives in uploads and swaps
 * the heavy theme URLs before HTML is sent to the browser.
 *
 * @package MOM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mom_static_image_profiles() {
	return array(
		'thumb'     => array( 'width' => 480, 'quality' => 72 ),
		'card'      => array( 'width' => 760, 'quality' => 76 ),
		'hero'      => array( 'width' => 1200, 'quality' => 80 ),
		'home-hero' => array( 'width' => 1600, 'quality' => 80 ),
	);
}

function mom_static_image_variant( $filename, $profile = 'thumb' ) {
	static $cache = array();

	$filename = sanitize_file_name( wp_basename( (string) $filename ) );
	$profiles = mom_static_image_profiles();
	$profile  = isset( $profiles[ $profile ] ) ? $profile : 'thumb';
	$key      = $profile . ':' . $filename;

	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$source = trailingslashit( get_template_directory() ) . 'assets/images/hq/' . $filename;
	if ( ! $filename || ! is_readable( $source ) ) {
		$cache[ $key ] = array();
		return $cache[ $key ];
	}

	$source_size = @getimagesize( $source );
	$source_w    = ! empty( $source_size[0] ) ? (int) $source_size[0] : 0;
	$source_h    = ! empty( $source_size[1] ) ? (int) $source_size[1] : 0;
	$target_w    = $source_w ? min( $source_w, (int) $profiles[ $profile ]['width'] ) : (int) $profiles[ $profile ]['width'];
	$target_h    = $source_w && $source_h ? max( 1, (int) round( $source_h * ( $target_w / $source_w ) ) ) : 0;
	$quality     = (int) $profiles[ $profile ]['quality'];
	$mtime       = (int) filemtime( $source );

	$uploads = wp_upload_dir();
	if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) || empty( $uploads['baseurl'] ) ) {
		$cache[ $key ] = array(
			'url'    => trailingslashit( get_template_directory_uri() ) . 'assets/images/hq/' . rawurlencode( $filename ),
			'width'  => $source_w,
			'height' => $source_h,
		);
		return $cache[ $key ];
	}

	$can_webp = function_exists( 'wp_image_editor_supports' ) && wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) );
	$ext      = $can_webp ? 'webp' : 'jpg';
	$mime     = $can_webp ? 'image/webp' : 'image/jpeg';
	$base     = pathinfo( $filename, PATHINFO_FILENAME );
	$version  = substr( md5( $mtime . '|' . $target_w . '|' . $quality . '|' . $ext ), 0, 10 );
	$dir_rel  = 'mom-responsive/' . $profile;
	$file_out = sanitize_file_name( $base . '-w' . $target_w . '-' . $version . '.' . $ext );
	$dir_abs  = trailingslashit( $uploads['basedir'] ) . $dir_rel;
	$dest     = trailingslashit( $dir_abs ) . $file_out;
	$url      = trailingslashit( $uploads['baseurl'] ) . $dir_rel . '/' . rawurlencode( $file_out );

	if ( ! file_exists( $dest ) ) {
		if ( ! wp_mkdir_p( $dir_abs ) ) {
			$cache[ $key ] = array(
				'url'    => trailingslashit( get_template_directory_uri() ) . 'assets/images/hq/' . rawurlencode( $filename ),
				'width'  => $source_w,
				'height' => $source_h,
			);
			return $cache[ $key ];
		}

		$editor = wp_get_image_editor( $source );
		if ( is_wp_error( $editor ) ) {
			$cache[ $key ] = array(
				'url'    => trailingslashit( get_template_directory_uri() ) . 'assets/images/hq/' . rawurlencode( $filename ),
				'width'  => $source_w,
				'height' => $source_h,
			);
			return $cache[ $key ];
		}

		if ( $source_w && $target_w < $source_w ) {
			$resized = $editor->resize( $target_w, $target_h, false );
			if ( is_wp_error( $resized ) ) {
				$cache[ $key ] = array();
				return $cache[ $key ];
			}
		}
		$editor->set_quality( $quality );
		$saved = $editor->save( $dest, $mime );

		if ( is_wp_error( $saved ) && $can_webp ) {
			$ext      = 'jpg';
			$mime     = 'image/jpeg';
			$file_out = sanitize_file_name( $base . '-w' . $target_w . '-' . $version . '.jpg' );
			$dest     = trailingslashit( $dir_abs ) . $file_out;
			$url      = trailingslashit( $uploads['baseurl'] ) . $dir_rel . '/' . rawurlencode( $file_out );
			$saved    = $editor->save( $dest, $mime );
		}

		if ( is_wp_error( $saved ) ) {
			$cache[ $key ] = array(
				'url'    => trailingslashit( get_template_directory_uri() ) . 'assets/images/hq/' . rawurlencode( $filename ),
				'width'  => $source_w,
				'height' => $source_h,
			);
			return $cache[ $key ];
		}

		if ( ! empty( $saved['width'] ) ) {
			$target_w = (int) $saved['width'];
		}
		if ( ! empty( $saved['height'] ) ) {
			$target_h = (int) $saved['height'];
		}
	}

	$cache[ $key ] = array(
		'url'    => $url,
		'width'  => $target_w,
		'height' => $target_h,
	);
	return $cache[ $key ];
}

function mom_static_image_url( $filename, $profile = 'thumb' ) {
	$image = mom_static_image_variant( $filename, $profile );
	return ! empty( $image['url'] ) ? (string) $image['url'] : '';
}

function mom_optimize_static_theme_images_html( $html ) {
	if ( ! is_string( $html ) || false === strpos( $html, '/assets/images/hq/' ) ) {
		return $html;
	}

	return preg_replace_callback(
		'#<img\b[^>]*\bsrc=(\"|\')([^\"\']*/assets/images/hq/([^\"\']+\.jpg))\1[^>]*>#i',
		static function ( $matches ) {
			$tag      = $matches[0];
			$filename = rawurldecode( wp_basename( $matches[3] ) );
			$profile  = 'thumb';

			if ( false !== stripos( $tag, 'mom-hq-hero' ) ) {
				$profile = 'home-hero';
			} elseif ( false !== stripos( $tag, 'fetchpriority=\"high\"' ) || false !== stripos( $tag, "fetchpriority='high'" ) || false !== stripos( $tag, 'article-banner-image' ) ) {
				$profile = 'hero';
			}

			$image = mom_static_image_variant( $filename, $profile );
			if ( empty( $image['url'] ) ) {
				return $tag;
			}

			$tag = preg_replace( '#\bsrc=(\"|\')[^\"\']+\1#i', 'src="' . esc_url( $image['url'] ) . '"', $tag, 1 );
			if ( ! empty( $image['width'] ) ) {
				$tag = preg_replace( '#\bwidth=(\"|\')[^\"\']*\1#i', 'width="' . (int) $image['width'] . '"', $tag, 1 );
			}
			if ( ! empty( $image['height'] ) ) {
				$tag = preg_replace( '#\bheight=(\"|\')[^\"\']*\1#i', 'height="' . (int) $image['height'] . '"', $tag, 1 );
			}
			return $tag;
		},
		$html
	);
}

function mom_start_static_image_optimization_buffer() {
	if ( is_admin() || wp_doing_ajax() || wp_is_json_request() || is_feed() ) {
		return;
	}
	ob_start( 'mom_optimize_static_theme_images_html' );
}
add_action( 'template_redirect', 'mom_start_static_image_optimization_buffer', 90 );

function mom_language_term_counts( $taxonomy, $language = '' ) {
	static $cache = array();

	$language = in_array( $language, array( 'es', 'en' ), true ) ? $language : ( mom_is_english() ? 'en' : 'es' );
	$key      = $taxonomy . ':' . $language;
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}
	if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
		$cache[ $key ] = array();
		return $cache[ $key ];
	}

	global $wpdb;
	$sql = $wpdb->prepare(
		"SELECT tt.term_id, COUNT(DISTINCT p.ID) AS article_count
		FROM {$wpdb->term_relationships} tr
		INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
		INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
		INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_content_language'
		WHERE tt.taxonomy = %s
		AND p.post_type = 'post'
		AND p.post_status = 'publish'
		AND pm.meta_value = %s
		GROUP BY tt.term_id",
		$taxonomy,
		$language
	);
	$rows = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$map  = array();
	foreach ( is_array( $rows ) ? $rows : array() as $row ) {
		$map[ (int) $row->term_id ] = (int) $row->article_count;
	}
	$cache[ $key ] = $map;
	return $cache[ $key ];
}

function mom_term_language_count( $term, $language = '' ) {
	if ( ! $term instanceof WP_Term ) {
		return 0;
	}
	$counts = mom_language_term_counts( $term->taxonomy, $language );
	return isset( $counts[ $term->term_id ] ) ? (int) $counts[ $term->term_id ] : 0;
}
