<?php
/**
 * Main archive fallback.
 *
 * @package MOM
 */
get_header();
?>
<section class="archive-wrap">
	<div class="container">
		<header class="archive-header">
			<span class="section-label"><?php echo esc_html( is_search() ? mom_t( 'Resultados', 'Results' ) : mom_t( 'Artículos', 'Articles' ) ); ?></span>
			<h1><?php echo esc_html( is_search() ? sprintf( mom_t( 'Resultados para “%s”', 'Results for “%s”' ), get_search_query() ) : mom_t( 'Todas las lecturas', 'All articles' ) ); ?></h1>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="card-grid">
				<?php while ( have_posts() ) : the_post(); mom_render_post_card( get_the_ID() ); endwhile; ?>
			</div>
			<div class="pagination"><?php the_posts_pagination(); ?></div>
		<?php else : ?>
			<p><?php echo esc_html( mom_t( 'No hemos encontrado artículos con ese criterio.', 'We could not find articles matching that query.' ) ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
