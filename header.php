<?php
/**
 * Site header.
 *
 * @package MOM
 */
$current_language = mom_is_english() ? 'en' : 'es';
$current_flag     = 'en' === $current_language ? '🇺🇸' : '🇪🇸';
$home_url         = mom_language_home_url();
$site_name        = get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'MOM';
$nav_items        = array(
	array( 'anchor' => 'temas', 'label' => mom_t( 'Temas', 'Topics' ) ),
	array( 'anchor' => 'etapas', 'label' => mom_t( 'Etapas', 'Stages' ) ),
	array( 'anchor' => 'para-ti', 'label' => mom_t( 'Para quién', 'For whom' ) ),
	array( 'anchor' => 'ultimos-articulos', 'label' => mom_t( 'Últimos artículos', 'Latest articles' ) ),
);
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
			.primary-nav{display:none}
			.site-brand small{display:none}
			.header-cta{padding-inline:15px}
		}
		@media (max-width:700px){
			.header-main{min-height:66px}
			.site-brand strong{font-size:34px}
		}
		@media (prefers-reduced-motion:reduce){*,*::before,*::after{scroll-behavior:auto!important;transition:none!important}}
	</style>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
	<div class="container header-main">
		<a class="site-brand" href="<?php echo esc_url( $home_url ); ?>" aria-label="<?php echo esc_attr( $site_name ); ?>">
			<strong><?php echo esc_html( $site_name ); ?></strong>
			<small><?php echo esc_html( mom_t( 'maternidad con más sentido', 'motherhood with more meaning' ) ); ?></small>
		</a>

		<nav class="primary-nav" aria-label="<?php echo esc_attr( mom_t( 'Navegación principal', 'Primary navigation' ) ); ?>">
			<ul>
				<?php foreach ( $nav_items as $item ) : ?>
					<li><a href="<?php echo esc_url( $home_url . '#' . $item['anchor'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<div class="header-actions">
			<button class="language-trigger" type="button" data-open-overlay="mom-language-overlay" aria-label="<?php echo esc_attr( mom_t( 'Cambiar idioma', 'Change language' ) ); ?>" aria-haspopup="dialog">
				<span class="language-flag" aria-hidden="true"><?php echo esc_html( $current_flag ); ?></span>
			</button>
			<a class="header-search" href="<?php echo esc_url( add_query_arg( 's', '', $home_url ) ); ?>" aria-label="<?php echo esc_attr( mom_t( 'Buscar', 'Search' ) ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="M15.5 15.5 21 21"></path></svg>
			</a>
			<a class="header-cta" href="<?php echo esc_url( $home_url . '#comunidad' ); ?>"><?php echo esc_html( mom_t( 'Únete a la comunidad', 'Join the community' ) ); ?></a>
			<button class="menu-trigger" type="button" data-open-overlay="mom-mobile-menu" aria-label="<?php echo esc_attr( mom_t( 'Abrir menú', 'Open menu' ) ); ?>" aria-haspopup="dialog">
				<span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
			</button>
		</div>
	</div>
</header>

<div class="mom-overlay" id="mom-language-overlay" data-mom-overlay role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="mom-language-title">
	<div class="mom-overlay-top">
		<div class="mom-overlay-brand"><?php echo esc_html( $site_name ); ?></div>
		<button class="mom-overlay-close" type="button" data-close-overlay aria-label="<?php echo esc_attr( mom_t( 'Cerrar selector de idioma', 'Close language selector' ) ); ?>">×</button>
	</div>
	<div class="mom-overlay-body">
		<div class="language-panel">
			<header class="language-panel-header">
				<span><?php echo esc_html( mom_t( 'Idioma', 'Language' ) ); ?></span>
				<h2 id="mom-language-title"><?php echo esc_html( mom_t( 'Elige tu idioma', 'Choose your language' ) ); ?></h2>
			</header>
			<div class="language-options">
				<a class="language-option <?php echo 'es' === $current_language ? 'is-current' : ''; ?>" href="<?php echo esc_url( mom_language_switch_url( 'es' ) ); ?>" hreflang="es-ES" lang="es">
					<span class="language-option-flag" aria-hidden="true">🇪🇸</span>
					<span class="language-option-copy"><strong>Español</strong><small>España</small></span>
					<span class="language-option-status" aria-hidden="true"><?php echo 'es' === $current_language ? '✓' : '→'; ?></span>
				</a>
				<a class="language-option <?php echo 'en' === $current_language ? 'is-current' : ''; ?>" href="<?php echo esc_url( mom_language_switch_url( 'en' ) ); ?>" hreflang="en-US" lang="en">
					<span class="language-option-flag" aria-hidden="true">🇺🇸</span>
					<span class="language-option-copy"><strong>English</strong><small>United States</small></span>
					<span class="language-option-status" aria-hidden="true"><?php echo 'en' === $current_language ? '✓' : '→'; ?></span>
				</a>
			</div>
		</div>
	</div>
</div>

<div class="mom-overlay" id="mom-mobile-menu" data-mom-overlay role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="mom-mobile-menu-title">
	<div class="mom-overlay-top">
		<div class="mom-overlay-brand" id="mom-mobile-menu-title"><?php echo esc_html( $site_name ); ?></div>
		<button class="mom-overlay-close" type="button" data-close-overlay aria-label="<?php echo esc_attr( mom_t( 'Cerrar menú', 'Close menu' ) ); ?>">×</button>
	</div>
	<div class="mom-overlay-body">
		<div class="mobile-menu-panel">
			<nav class="mobile-menu-nav" aria-label="<?php echo esc_attr( mom_t( 'Menú móvil', 'Mobile menu' ) ); ?>">
				<ul>
					<?php foreach ( $nav_items as $item ) : ?>
						<li><a href="<?php echo esc_url( $home_url . '#' . $item['anchor'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<div class="mobile-menu-actions">
				<button class="mom-mobile-language" type="button" data-open-overlay="mom-language-overlay">
					<span class="language-flag" aria-hidden="true"><?php echo esc_html( $current_flag ); ?></span>
					<span><?php echo esc_html( mom_t( 'Cambiar idioma', 'Change language' ) ); ?></span>
				</button>
				<a class="mobile-menu-cta" href="<?php echo esc_url( $home_url . '#comunidad' ); ?>"><?php echo esc_html( mom_t( 'Únete a la comunidad', 'Join the community' ) ); ?></a>
			</div>
		</div>
	</div>
</div>

<main id="main-content">
