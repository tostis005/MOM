<?php
/**
 * Generic taxonomy archive.
 *
 * @package MOM
 */
get_header();
$term = get_queried_object();
?>
<section class="archive-wrap">
	<div class="container">
		<header class="archive-header">
			<span class="section-label"><?php echo esc_html( mom_t( 'Explorar', 'Explore' ) ); ?></span>
			<h1><?php single_term_title(); ?></h1>
			<?php if ( $term instanceof WP_Term && $term->description ) : ?><p><?php echo esc_html( $term->description ); ?></p><?php endif; ?>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="card-grid">
				<?php while ( have_posts() ) : the_post(); mom_render_post_card( get_the_ID() ); endwhile; ?>
			</div>
			<div class="pagination"><?php the_posts_pagination(); ?></div>
		<?php else : ?>
			<p><?php echo esc_html( mom_t( 'Todavía no hay artículos publicados en esta sección.', 'There are no published articles in this section yet.' ) ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
