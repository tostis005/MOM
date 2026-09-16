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
		<div style="grid-column: 1 / -1;">
			<div class="footer-brand"><span class="matternal-wordmark"><span class="matternal-wordmark__matter">Matter</span><span class="matternal-wordmark__nal">nal</span></span></div>
			<p class="footer-copy"><?php echo esc_html( mom_t( 'Información práctica para entender mejor la crianza, la maternidad y la vida familiar sin convertir cada día en un examen.', 'Practical information to understand parenting, motherhood and family life without turning every day into a test.' ) ); ?></p>
		</div>
	</div>
	<div class="container footer-bottom">© <?php echo esc_html( gmdate( 'Y' ) ); ?> Matternal</div>
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
