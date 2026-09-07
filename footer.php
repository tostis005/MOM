<?php
/**
 * Site footer.
 *
 * @package MOM
 */
$footer_home = mom_language_home_url();
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
				<li><a href="<?php echo esc_url( $footer_home . '#temas' ); ?>"><?php echo esc_html( mom_t( 'Por tema', 'By topic' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( $footer_home . '#etapas' ); ?>"><?php echo esc_html( mom_t( 'Por etapa', 'By stage' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( $footer_home . '#para-ti' ); ?>"><?php echo esc_html( mom_t( 'Para quién', 'For whom' ) ); ?></a></li>
			</ul>
		</div>
		<div class="footer-links">
			<h3>MOM</h3>
			<ul>
				<li><a href="<?php echo esc_url( $footer_home ); ?>"><?php echo esc_html( mom_t( 'Inicio', 'Home' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( $footer_home . '#ultimos-articulos' ); ?>"><?php echo esc_html( mom_t( 'Últimos artículos', 'Latest articles' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( $footer_home . '#comunidad' ); ?>"><?php echo esc_html( mom_t( 'Comunidad', 'Community' ) ); ?></a></li>
			</ul>
		</div>
	</div>
	<div class="container footer-bottom">© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'MOM' ); ?></div>
</footer>
<?php
$pagination_script = get_template_directory() . '/assets/js/responsive-pagination.js';
if ( file_exists( $pagination_script ) ) :
	?>
	<script src="<?php echo esc_url( get_template_directory_uri() . '/assets/js/responsive-pagination.js' ); ?>?ver=<?php echo esc_attr( (string) filemtime( $pagination_script ) ); ?>" defer></script>
	<?php
endif;
wp_footer();
?>
</body>
</html>
