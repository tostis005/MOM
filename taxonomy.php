<?php
/**
 * Premium taxonomy archive.
 *
 * @package MOM
 */

$taxonomy_premium_css = get_template_directory() . '/assets/css/taxonomy-premium.css';
wp_enqueue_style(
	'mom-taxonomy-premium',
	get_template_directory_uri() . '/assets/css/taxonomy-premium.css',
	array( 'mom-style' ),
	file_exists( $taxonomy_premium_css ) ? (string) filemtime( $taxonomy_premium_css ) : wp_get_theme()->get( 'Version' )
);

get_header();
$term = get_queried_object();

$dimension = 'topic';
if ( $term instanceof WP_Term ) {
	foreach ( array( 'topic', 'stage', 'audience', 'article_type' ) as $candidate_dimension ) {
		if ( $term->taxonomy === mom_taxonomy_name( $candidate_dimension ) ) {
			$dimension = $candidate_dimension;
			break;
		}
	}
}

$term_id = $term instanceof WP_Term ? (string) get_term_meta( $term->term_id, '_content_term_id', true ) : '';
if ( ! $term_id && $term instanceof WP_Term ) {
	$term_id = (string) $term->slug;
}

$term_label = $term instanceof WP_Term ? $term->name : '';
if ( $term_id && function_exists( 'content_platform_term_label' ) ) {
	$localized_label = content_platform_term_label( $dimension, $term_id, mom_is_english() ? 'en' : 'es' );
	if ( $localized_label ) {
		$term_label = $localized_label;
	}
}

$dimension_labels = array(
	'topic'        => mom_t( 'Tema', 'Topic' ),
	'stage'        => mom_t( 'Etapa', 'Stage' ),
	'audience'     => mom_t( 'Para quién', 'For whom' ),
	'article_type' => mom_t( 'Tipo de guía', 'Guide type' ),
);
$dimension_label = isset( $dimension_labels[ $dimension ] ) ? $dimension_labels[ $dimension ] : mom_t( 'Explorar', 'Explore' );

$description = $term instanceof WP_Term ? trim( (string) $term->description ) : '';
if ( ! $description && 'topic' === $dimension && $term_id ) {
	$description = mom_topic_description( $term_id );
}
if ( ! $description ) {
	$fallback_descriptions = array(
		'topic'        => mom_t( 'Ideas prácticas, contexto útil y guías claras para acompañar el día a día en familia.', 'Practical ideas, useful context and clear guides for everyday family life.' ),
		'stage'        => mom_t( 'Una selección de guías pensadas para las preguntas, cambios y decisiones propias de esta etapa.', 'A selection of guides for the questions, changes and decisions that come with this stage.' ),
		'audience'     => mom_t( 'Contenido seleccionado para ayudarte a encontrar antes lo que encaja con tu realidad familiar.', 'Curated content to help you find what fits your family reality more quickly.' ),
		'article_type' => mom_t( 'Consejos claros y aplicables para pasar de la duda a una decisión práctica.', 'Clear, actionable guidance to move from uncertainty to a practical next step.' ),
	);
	$description = isset( $fallback_descriptions[ $dimension ] ) ? $fallback_descriptions[ $dimension ] : '';
}

$hero_url = '';
$prefixes = array( 'topic' => 'topic', 'stage' => 'stage', 'audience' => 'audience' );
if ( isset( $prefixes[ $dimension ] ) && $term_id ) {
	$image_term_id = $term_id;
	$image_file    = get_template_directory() . '/assets/images/hq/' . $prefixes[ $dimension ] . '-' . sanitize_file_name( $image_term_id ) . '.jpg';

	if ( ! file_exists( $image_file ) && $term instanceof WP_Term && $term->parent ) {
		$parent_term = get_term( $term->parent, $term->taxonomy );
		if ( $parent_term instanceof WP_Term ) {
			$parent_content_id = (string) get_term_meta( $parent_term->term_id, '_content_term_id', true );
			$image_term_id     = $parent_content_id ? $parent_content_id : (string) $parent_term->slug;
			$image_file        = get_template_directory() . '/assets/images/hq/' . $prefixes[ $dimension ] . '-' . sanitize_file_name( $image_term_id ) . '.jpg';
		}
	}

	if ( file_exists( $image_file ) ) {
		$hero_url = get_template_directory_uri() . '/assets/images/hq/' . $prefixes[ $dimension ] . '-' . rawurlencode( $image_term_id ) . '.jpg';
	}
}

if ( ! $hero_url ) {
	$fallback_hero = get_template_directory() . '/assets/images/hq/hero-mother-baby.jpg';
	if ( file_exists( $fallback_hero ) ) {
		$hero_url = get_template_directory_uri() . '/assets/images/hq/hero-mother-baby.jpg';
	}
}

$children = array();
if ( $term instanceof WP_Term && is_taxonomy_hierarchical( $term->taxonomy ) ) {
	$children = get_terms(
		array(
			'taxonomy'   => $term->taxonomy,
			'parent'     => $term->term_id,
			'hide_empty' => true,
		)
	);
	if ( is_wp_error( $children ) ) {
		$children = array();
	}
}

$post_count = $term instanceof WP_Term ? (int) $term->count : 0;
?>
<main class="taxonomy-premium">
	<header class="taxonomy-premium-hero">
		<?php if ( $hero_url ) : ?>
			<figure class="taxonomy-premium-hero-media" aria-hidden="true">
				<img src="<?php echo esc_url( $hero_url ); ?>" alt="" width="1448" height="1086" fetchpriority="high" decoding="async">
			</figure>
		<?php endif; ?>
		<div class="taxonomy-premium-hero-overlay" aria-hidden="true"></div>
		<div class="container taxonomy-premium-hero-inner">
			<div class="taxonomy-premium-hero-copy">
				<span class="taxonomy-premium-kicker"><?php echo esc_html( $dimension_label ); ?></span>
				<h1><?php echo esc_html( $term_label ); ?></h1>
				<?php if ( $description ) : ?><p class="taxonomy-premium-description"><?php echo esc_html( $description ); ?></p><?php endif; ?>
				<span class="taxonomy-premium-count">
					<?php
					echo esc_html(
						sprintf(
							mom_is_english() ? _n( '%d article', '%d articles', $post_count, 'mom' ) : _n( '%d artículo', '%d artículos', $post_count, 'mom' ),
							$post_count
						)
					);
					?>
				</span>
			</div>
		</div>
	</header>

	<div class="taxonomy-premium-body">
		<div class="container">
			<?php if ( ! empty( $children ) ) : ?>
				<nav class="taxonomy-premium-subnav" aria-label="<?php echo esc_attr( mom_t( 'Subcategorías', 'Subcategories' ) ); ?>">
					<span class="taxonomy-premium-subnav-label"><?php echo esc_html( mom_t( 'Explora más', 'Explore more' ) ); ?></span>
					<div class="taxonomy-premium-chips">
						<?php foreach ( $children as $child ) : ?>
							<?php
							$child_id    = (string) get_term_meta( $child->term_id, '_content_term_id', true );
							$child_id    = $child_id ? $child_id : (string) $child->slug;
							$child_label = function_exists( 'content_platform_term_label' ) ? content_platform_term_label( $dimension, $child_id, mom_is_english() ? 'en' : 'es' ) : $child->name;
							$child_url   = get_term_link( $child );
							if ( is_wp_error( $child_url ) ) {
								continue;
							}
							?>
							<a class="taxonomy-premium-chip" href="<?php echo esc_url( $child_url ); ?>"><?php echo esc_html( $child_label ); ?></a>
						<?php endforeach; ?>
					</div>
				</nav>
			<?php endif; ?>

			<header class="taxonomy-premium-list-head">
				<div>
					<span class="section-label"><?php echo esc_html( mom_t( 'Lecturas recomendadas', 'Recommended reading' ) ); ?></span>
					<h2><?php echo esc_html( mom_t( 'Artículos para ti', 'Guides for you' ) ); ?></h2>
				</div>
			</header>

			<?php if ( have_posts() ) : ?>
				<div class="taxonomy-premium-grid">
					<?php while ( have_posts() ) : the_post(); mom_render_post_card( get_the_ID() ); endwhile; ?>
				</div>
				<div class="pagination"><?php the_posts_pagination(); ?></div>
			<?php else : ?>
				<div class="taxonomy-premium-empty">
					<h2><?php echo esc_html( mom_t( 'Estamos preparando esta sección', 'We are building this section' ) ); ?></h2>
					<p><?php echo esc_html( mom_t( 'Todavía no hay artículos publicados aquí, pero puedes seguir explorando otras categorías de MOM.', 'There are no published articles here yet, but you can keep exploring other MOM categories.' ) ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php get_footer(); ?>
