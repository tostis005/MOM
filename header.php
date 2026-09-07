<?php
/**
 * Site header.
 *
 * @package MOM
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
	<style id="mom-production-polish">
		img{image-rendering:auto;-webkit-font-smoothing:antialiased}
		.site-header{box-shadow:0 1px 0 rgba(74,52,46,.03)}
		.site-brand strong::after{content:"♥";display:inline-block;margin-left:4px;color:var(--mom-rose);font:400 .34em/1 Georgia,serif;vertical-align:top;transform:translateY(-1px)}
		.header-search svg{width:19px;height:19px;display:block;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round}
		@media (min-width:981px) and (max-width:1180px){
			.header-main{grid-template-columns:minmax(170px,auto) 1fr auto;gap:18px}
			.primary-nav{display:block}
			.primary-nav ul{gap:18px}
			.primary-nav a{font-size:12px}
			.site-brand small{display:none}
			.header-cta{padding-inline:15px}
		}
		@media (max-width:700px){
			.header-main{min-height:66px}
			.site-brand strong{font-size:34px}
			.header-search{width:38px;height:38px}
		}
		@media (prefers-reduced-motion:reduce){*,*::before,*::after{scroll-behavior:auto!important;transition:none!important}}
	</style>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
	<div class="container header-main">
		<a class="site-brand" href="<?php echo esc_url( mom_language_home_url() ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<strong><?php echo esc_html( get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'MOM' ); ?></strong>
			<small><?php echo esc_html( mom_t( 'maternidad con más sentido', 'motherhood with more meaning' ) ); ?></small>
		</a>

		<nav class="primary-nav" aria-label="<?php echo esc_attr( mom_t( 'Navegación principal', 'Primary navigation' ) ); ?>">
			<?php if ( has_nav_menu( 'primary' ) ) : ?>
				<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'depth' => 1 ) ); ?>
			<?php else : ?>
				<ul>
					<li><a href="<?php echo esc_url( mom_language_home_url() . '#temas' ); ?>"><?php echo esc_html( mom_t( 'Temas', 'Topics' ) ); ?></a></li>
					<li><a href="<?php echo esc_url( mom_language_home_url() . '#etapas' ); ?>"><?php echo esc_html( mom_t( 'Etapas', 'Stages' ) ); ?></a></li>
					<li><a href="<?php echo esc_url( mom_language_home_url() . '#para-ti' ); ?>"><?php echo esc_html( mom_t( 'Para quién', 'For whom' ) ); ?></a></li>
					<li><a href="<?php echo esc_url( mom_language_home_url() . '#ultimos-articulos' ); ?>"><?php echo esc_html( mom_t( 'Últimos artículos', 'Latest articles' ) ); ?></a></li>
				</ul>
			<?php endif; ?>
		</nav>

		<div class="header-actions">
			<?php if ( function_exists( 'pll_the_languages' ) ) : ?>
				<div class="language-links" aria-label="<?php echo esc_attr( mom_t( 'Idioma', 'Language' ) ); ?>">
					<?php foreach ( pll_the_languages( array( 'raw' => 1 ) ) as $language ) : ?>
						<a class="<?php echo ! empty( $language['current_lang'] ) ? 'current' : ''; ?>" href="<?php echo esc_url( $language['url'] ); ?>"><?php echo esc_html( strtoupper( $language['slug'] ) ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<a class="header-search" href="<?php echo esc_url( mom_language_home_url() . '?s=' ); ?>" aria-label="<?php echo esc_attr( mom_t( 'Buscar', 'Search' ) ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="M15.5 15.5 21 21"></path></svg>
			</a>
			<a class="header-cta" href="<?php echo esc_url( mom_language_home_url() . '#comunidad' ); ?>"><?php echo esc_html( mom_t( 'Únete a la comunidad', 'Join the community' ) ); ?></a>
		</div>
	</div>
</header>
<main id="main-content">
