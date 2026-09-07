<?php
/**
 * Site footer.
 *
 * @package MOM
 */
?>
</main>
<footer class="site-footer">
	<div class="container footer-main">
		<div>
			<div class="footer-brand"><?php echo esc_html( get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'MOM' ); ?></div>
			<p class="footer-copy"><?php echo esc_html( mom_t( 'Información práctica para entender mejor la crianza, la maternidad y la vida familiar sin convertir cada día en un examen.', 'Practical information to understand parenting, motherhood and family life without turning every day into a test.' ) ); ?></p>
		</div>
		<div class="footer-links">
			<h3><?php echo esc_html( mom_t( 'Explorar', 'Explore' ) ); ?></h3>
			<ul>
				<li><a href="<?php echo esc_url( mom_language_home_url() . '#temas' ); ?>"><?php echo esc_html( mom_t( 'Por tema', 'By topic' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( mom_language_home_url() . '#etapas' ); ?>"><?php echo esc_html( mom_t( 'Por etapa', 'By stage' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( mom_language_home_url() . '#para-ti' ); ?>"><?php echo esc_html( mom_t( 'Para quién', 'For whom' ) ); ?></a></li>
			</ul>
		</div>
		<div class="footer-links">
			<h3><?php echo esc_html( mom_t( 'MOM', 'MOM' ) ); ?></h3>
			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'depth' => 1 ) ); ?>
			<?php else : ?>
				<ul><li><a href="<?php echo esc_url( mom_language_home_url() ); ?>"><?php echo esc_html( mom_t( 'Inicio', 'Home' ) ); ?></a></li></ul>
			<?php endif; ?>
		</div>
	</div>
	<div class="container footer-bottom">© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'MOM' ); ?></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
