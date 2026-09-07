<?php
/**
 * Standalone discovery landing pages.
 *
 * @package MOM
 */

$discovery_css = get_template_directory() . '/assets/css/discovery-pages.css';
wp_enqueue_style(
	'mom-discovery-pages',
	get_template_directory_uri() . '/assets/css/discovery-pages.css',
	array( 'mom-style' ),
	file_exists( $discovery_css ) ? (string) filemtime( $discovery_css ) : wp_get_theme()->get( 'Version' )
);

get_header();

$hub      = (string) get_query_var( 'mom_hub' );
$language = mom_is_english() ? 'en' : 'es';
$pages    = mom_discovery_pages();
$page     = isset( $pages[ $hub ] ) ? $pages[ $hub ] : $pages['topics'];

$config = array(
	'topics' => array(
		'eyebrow' => mom_t( 'Explora MOM', 'Explore MOM' ),
		'title'   => mom_t( 'Temas', 'Topics' ),
		'deck'    => mom_t( 'Todo el contenido organizado por las preguntas y situaciones que aparecen en la vida familiar.', 'All our content organized around the questions and situations that come up in family life.' ),
		'image'   => 'hero-mother-baby.jpg',
	),
	'stages' => array(
		'eyebrow' => mom_t( 'Cada momento cuenta', 'Every stage matters' ),
		'title'   => mom_t( 'Etapas', 'Stages' ),
		'deck'    => mom_t( 'Encuentra guías pensadas para el momento concreto que estás viviendo, desde el embarazo hasta la edad escolar.', 'Find guidance for the exact stage you are living through, from pregnancy to the school years.' ),
		'image'   => 'stage-parenthood-general.jpg',
	),
	'audience' => array(
		'eyebrow' => mom_t( 'Contenido que encaja contigo', 'Content that fits you' ),
		'title'   => mom_t( 'Para quién', 'For whom' ),
		'deck'    => mom_t( 'La misma pregunta cambia según quién la vive. Aquí puedes explorar el contenido desde tu realidad familiar.', 'The same question changes depending on who is living it. Explore content from the perspective that matches your family reality.' ),
		'image'   => 'audience-parents.jpg',
	),
	'latest' => array(
		'eyebrow' => mom_t( 'Recién publicado', 'Recently published' ),
		'title'   => mom_t( 'Últimos artículos', 'Latest articles' ),
		'deck'    => mom_t( 'Las guías, historias y reflexiones más recientes de MOM, reunidas en un solo lugar.', 'The newest guides, stories and perspectives from MOM, all in one place.' ),
		'image'   => 'hero-mother-baby.jpg',
	),
);
$current = isset( $config[ $hub ] ) ? $config[ $hub ] : $config['topics'];

$hero_file = trailingslashit( get_template_directory() ) . 'assets/images/hq/' . $current['image'];
$hero_url  = file_exists( $hero_file ) ? trailingslashit( get_template_directory_uri() ) . 'assets/images/hq/' . rawurlencode( $current['image'] ) : '';
?>

<main class="discovery-page discovery-page--<?php echo esc_attr( $hub ); ?>">
	<header class="discovery-hero">
		<?php if ( $hero_url ) : ?>
			<figure class="discovery-hero-media" aria-hidden="true">
				<img src="<?php echo esc_url( $hero_url ); ?>" alt="" width="1448" height="1086" fetchpriority="high" decoding="async">
			</figure>
		<?php endif; ?>
		<div class="discovery-hero-fade" aria-hidden="true"></div>
		<div class="container discovery-hero-inner">
			<div class="discovery-hero-copy">
				<span class="discovery-hero-kicker"><?php echo esc_html( $current['eyebrow'] ); ?></span>
				<h1><?php echo esc_html( $current['title'] ); ?></h1>
				<p><?php echo esc_html( $current['deck'] ); ?></p>
			</div>
		</div>
	</header>

	<section class="discovery-content">
		<div class="container">
			<?php if ( 'latest' !== $hub ) : ?>
				<?php
				$dimension = $page['dimension'];
				$terms     = mom_home_terms( $dimension );
				?>
				<div class="discovery-card-grid discovery-card-grid--<?php echo esc_attr( $dimension ); ?>">
					<?php foreach ( $terms as $term ) : ?>
						<?php
						$term_id    = (string) $term['id'];
						$term_label = (string) $term['label'];
						$image_url  = mom_discovery_image_url( $dimension, $term_id );
						$term_url   = mom_term_url( $dimension, $term_id, $term_label, $language );
						$term_count = 0;
						$taxonomy   = mom_taxonomy_name( $dimension );
						if ( $taxonomy && taxonomy_exists( $taxonomy ) ) {
							$wp_term = get_term_by( 'slug', sanitize_title( $term_id ), $taxonomy );
							if ( $wp_term instanceof WP_Term ) {
								$term_count = function_exists( 'mom_term_language_count' ) ? mom_term_language_count( $wp_term, $language ) : 0;
							}
						}
						$term_description = 'topic' === $dimension ? mom_topic_description( $term_id ) : '';
						?>
						<a class="discovery-card" href="<?php echo esc_url( $term_url ); ?>">
							<div class="discovery-card-media">
								<?php if ( $image_url ) : ?>
									<img src="<?php echo esc_url( $image_url ); ?>" width="480" height="360" alt="" loading="lazy" decoding="async">
								<?php else : ?>
									<div class="discovery-card-fallback" style="--topic-accent:<?php echo esc_attr( 'topic' === $dimension ? mom_topic_accent( $term_id ) : '#9b5f60' ); ?>"><?php echo 'topic' === $dimension ? mom_topic_art_svg( $term_id ) : mom_hero_art_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
								<?php endif; ?>
							</div>
							<div class="discovery-card-copy">
								<div class="discovery-card-title-row">
									<h2><?php echo esc_html( $term_label ); ?></h2>
									<span aria-hidden="true">→</span>
								</div>
								<?php if ( $term_description ) : ?><p><?php echo esc_html( $term_description ); ?></p><?php endif; ?>
								<?php if ( $term_count ) : ?><small><?php echo esc_html( sprintf( mom_is_english() ? _n( '%d article', '%d articles', $term_count, 'mom' ) : _n( '%d artículo', '%d artículos', $term_count, 'mom' ), $term_count ) ); ?></small><?php endif; ?>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<?php
				$paged = max( 1, (int) get_query_var( 'paged' ) );
				$latest = new WP_Query(
					array(
						'post_type'           => 'post',
						'post_status'         => 'publish',
						'posts_per_page'      => 12,
						'paged'               => $paged,
						'ignore_sticky_posts' => false,
						'meta_query'          => array(
							array( 'key' => '_content_language', 'value' => $language ),
						),
					)
				);
				?>
				<?php if ( $latest->have_posts() ) : ?>
					<div class="discovery-article-grid">
						<?php while ( $latest->have_posts() ) : $latest->the_post(); mom_render_post_card( get_the_ID() ); endwhile; ?>
					</div>
					<?php if ( $latest->max_num_pages > 1 ) : ?>
						<nav class="pagination discovery-pagination" aria-label="<?php echo esc_attr( mom_t( 'Paginación de artículos', 'Article pagination' ) ); ?>">
							<?php
							echo wp_kses_post(
								paginate_links(
									array(
										'total'   => $latest->max_num_pages,
										'current' => $paged,
										'base'    => trailingslashit( mom_discovery_url( 'latest', $language ) ) . 'page/%#%/',
										'format'  => '',
									)
								)
							);
							?>
						</nav>
					<?php endif; ?>
				<?php else : ?>
					<p class="discovery-empty"><?php echo esc_html( mom_t( 'Todavía no hay artículos publicados.', 'There are no published articles yet.' ) ); ?></p>
				<?php endif; wp_reset_postdata(); ?>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php get_footer(); ?>