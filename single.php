<?php
/**
 * Single article template.
 *
 * @package MOM
 */

$article_premium_css = get_template_directory() . '/assets/css/article-premium.css';
wp_enqueue_style(
	'mom-article-premium',
	get_template_directory_uri() . '/assets/css/article-premium.css',
	array( 'mom-style' ),
	file_exists( $article_premium_css ) ? (string) filemtime( $article_premium_css ) : wp_get_theme()->get( 'Version' )
);

get_header();
while ( have_posts() ) : the_post();
	$topic_id    = mom_primary_topic_id();
	$topic_label = mom_topic_label( $topic_id );
	$topic_url   = mom_term_url( 'topic', $topic_id, $topic_label );
	$has_hero    = has_post_thumbnail();
	?>
	<article class="single-article-premium">
		<header class="article-banner <?php echo $has_hero ? 'has-image' : 'no-image'; ?>">
			<?php if ( $has_hero ) : ?>
				<figure class="article-banner-media">
					<?php
					the_post_thumbnail(
						'mom-hero',
						array(
							'class'         => 'article-banner-image',
							'fetchpriority' => 'high',
							'decoding'      => 'async',
							'sizes'         => '100vw',
						)
					);
					?>
				</figure>
			<?php endif; ?>
			<div class="article-banner-overlay" aria-hidden="true"></div>

			<div class="container article-banner-inner">
				<div class="article-banner-copy">
					<a class="article-banner-kicker" href="<?php echo esc_url( $topic_url ); ?>"><?php echo esc_html( $topic_label ); ?></a>
					<h1><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="article-banner-deck"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
					<div class="article-banner-meta">
						<span class="article-reading-time"><?php echo esc_html( mom_reading_time() ); ?></span>
					</div>
				</div>
			</div>
		</header>

		<div class="article-shell article-reading-shell">
			<div class="entry-content"><?php the_content(); ?></div>
		</div>
	</article>

	<section class="section">
		<div class="container">
			<header class="latest-head"><div><span class="section-label"><?php echo esc_html( mom_t( 'Sigue leyendo', 'Keep reading' ) ); ?></span><h2><?php echo esc_html( mom_t( 'Más en MOM', 'More from MOM' ) ); ?></h2></div></header>
			<div class="card-grid">
				<?php
				$related = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3, 'post__not_in' => array( get_the_ID() ), 'ignore_sticky_posts' => true ) );
				while ( $related->have_posts() ) :
					$related->the_post();
					mom_render_post_card( get_the_ID() );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
	<?php
endwhile;
get_footer();
