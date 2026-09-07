<?php
/**
 * Single article template.
 *
 * @package MOM
 */
get_header();
while ( have_posts() ) : the_post();
	$topic_id = mom_primary_topic_id();
	?>
	<article>
		<div class="article-shell">
			<header class="article-header">
				<span class="card-kicker"><?php echo esc_html( mom_topic_label( $topic_id ) ); ?></span>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?><p class="article-deck"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
				<div class="article-meta"><span><?php echo esc_html( get_the_date() ); ?></span> · <span><?php echo esc_html( mom_reading_time() ); ?></span></div>
			</header>
		</div>
		<?php if ( has_post_thumbnail() ) : ?><div class="article-hero"><?php the_post_thumbnail( 'mom-hero' ); ?></div><?php endif; ?>
		<div class="article-shell"><div class="entry-content"><?php the_content(); ?></div></div>
	</article>
	<section class="section">
		<div class="container">
			<header class="latest-head"><div><span class="section-label"><?php echo esc_html( mom_t( 'Sigue leyendo', 'Keep reading' ) ); ?></span><h2><?php echo esc_html( mom_t( 'Más en MOM', 'More from MOM' ) ); ?></h2></div></header>
			<div class="card-grid">
				<?php
				$related = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3, 'post__not_in' => array( get_the_ID() ), 'ignore_sticky_posts' => true ) );
				while ( $related->have_posts() ) : $related->the_post(); mom_render_post_card( get_the_ID() ); endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
	<?php
endwhile;
get_footer();
