<?php
/**
 * MOM home page.
 *
 * @package MOM
 */
get_header();

$topics    = mom_home_terms( 'topic' );
$stages    = mom_home_terms( 'stage' );
$audiences = mom_home_terms( 'audience' );
$home_url  = mom_language_home_url();
$hq_assets = trailingslashit( get_template_directory_uri() ) . 'assets/images/hq/';

/* One unique, high-resolution image per canonical home topic. */
$topic_images = array(
	'sleep'                       => 'topic-sleep.jpg',
	'parenting-behavior'          => 'topic-parenting-behavior.jpg',
	'child-feeding'               => 'topic-child-feeding.jpg',
	'potty-hygiene-autonomy'      => 'topic-potty-hygiene-autonomy.jpg',
	'routines-family-life'        => 'topic-routines-family-life.jpg',
	'play-learning-autonomy'      => 'topic-play-learning-autonomy.jpg',
	'childcare-school-social'     => 'topic-childcare-school-social.jpg',
	'pregnancy-preparation'       => 'topic-pregnancy-preparation.jpg',
	'postpartum-newborn'          => 'topic-postpartum-newborn.jpg',
	'breastfeeding-baby-feeding'  => 'topic-breastfeeding-baby-feeding.jpg',
	'couple-coparenting'          => 'topic-couple-coparenting.jpg',
	'motherhood-identity'         => 'topic-motherhood-identity.jpg',
	'family-siblings-boundaries'  => 'topic-family-siblings-boundaries.jpg',
	'work-balance-life'           => 'topic-work-balance-life.jpg',
	'travel-outings-celebrations' => 'topic-travel-outings-celebrations.jpg',
);

$topic_image_url = static function ( $topic_id ) use ( $topic_images, $hq_assets ) {
	return ! empty( $topic_images[ $topic_id ] ) ? $hq_assets . $topic_images[ $topic_id ] : '';
};
?>

<style>
/* HQ production home: no image upscaling, no repeated topic photography. */
.home-hero{padding:20px 0 28px}
.hero-shell{min-height:560px;display:grid;grid-template-columns:minmax(0,45%) minmax(0,55%);align-items:stretch;overflow:hidden;border:1px solid #eaded6;border-radius:30px;background:#f4e9e2;box-shadow:0 24px 68px rgba(63,46,41,.08)}
.hero-copy{padding:clamp(48px,5vw,76px) clamp(34px,4.7vw,68px);display:flex;flex-direction:column;justify-content:center;position:relative;z-index:2}
.hero-copy h1{margin:14px 0 18px;max-width:10.7ch;font:500 clamp(50px,5.2vw,74px)/.99 Georgia,"Times New Roman",serif;letter-spacing:-.048em}
.hero-copy>p{margin:0;max-width:49ch;color:#655c57;font-size:clamp(16px,1.4vw,19px);line-height:1.55}
.hero-search-premium{display:flex;align-items:center;gap:8px;width:100%;max-width:590px;margin-top:28px;padding:7px 7px 7px 18px;border:1px solid #dfd0c7;border-radius:999px;background:#fff;box-shadow:0 10px 30px rgba(63,44,39,.06)}
.hero-search-premium span{font-size:18px;color:#927c73}.hero-search-premium input{min-width:0;flex:1;border:0;outline:0;background:transparent;padding:9px 5px;color:var(--mom-ink);font-size:13px}.hero-search-premium button{border:0;border-radius:999px;background:var(--mom-rose);color:#fff;min-height:40px;padding:0 22px;font-size:12px;font-weight:800;cursor:pointer}
.hero-trust{display:flex;flex-wrap:wrap;gap:9px 20px;margin-top:20px;color:#776c67;font-size:10.5px}.hero-trust span{display:flex;align-items:center;gap:7px}.hero-trust i{width:22px;height:22px;display:grid;place-items:center;border:1px solid #dbc5bb;border-radius:50%;color:#9b625a;font-style:normal;font-size:10px;background:rgba(255,255,255,.5)}
.hero-media{position:relative;min-height:560px;overflow:hidden;background:#d8c2b6}
.hero-media>img.mom-hq-hero{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center center;display:block;max-width:none}
.hero-media::after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(53,38,34,.03),transparent 28%,rgba(42,30,27,.04));pointer-events:none}
.hero-note{position:absolute;right:24px;bottom:24px;z-index:2;max-width:190px;padding:13px 16px;border:1px solid rgba(124,73,68,.13);border-radius:15px;background:rgba(255,253,251,.9);backdrop-filter:blur(8px);color:#654d47;text-align:center;box-shadow:0 12px 30px rgba(66,45,39,.08);font:italic 17px/1.25 Georgia,serif}
.topic-section{padding:38px 0 34px;background:rgba(255,253,251,.78);border-block:1px solid rgba(233,223,215,.8)}
.topic-rail{display:grid;grid-auto-flow:column;grid-auto-columns:184px;gap:16px;overflow-x:auto;overscroll-behavior-inline:contain;scroll-snap-type:x proximity;scrollbar-width:thin;padding:3px 0 13px}
.premium-topic-card{min-width:184px;display:block;border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important;text-decoration:none;scroll-snap-align:start;transition:transform .2s ease}
.premium-topic-card:hover{transform:translateY(-3px);box-shadow:none!important}
.premium-topic-art.mom-hq-topic{height:auto;aspect-ratio:4/3;display:block;overflow:hidden;padding:0;border:1px solid #e9ddd6;border-radius:16px;background:#e9ddd6;box-shadow:0 9px 25px rgba(65,45,39,.06)}
.premium-topic-art.mom-hq-topic img{width:100%;height:100%;object-fit:cover;object-position:center;display:block;max-width:none;filter:none;transform:none;transition:transform .3s ease}
.premium-topic-card:hover .premium-topic-art.mom-hq-topic img{transform:scale(1.025)}
.premium-topic-copy{min-height:0!important;display:block;padding:11px 3px 0!important;text-align:center}.premium-topic-copy strong{display:block;font:500 15px/1.18 Georgia,serif}.premium-topic-copy small{display:none!important}
.stage-section{padding:36px 0}.stage-rail{display:grid;grid-auto-flow:column;grid-auto-columns:120px;gap:17px;overflow-x:auto;scroll-snap-type:x proximity;scrollbar-width:thin;padding:4px 0 10px}.premium-stage-card{min-height:0!important;display:flex!important;flex-direction:column;gap:10px;padding:0!important;border:0!important;background:transparent!important;text-align:center;scroll-snap-align:start}.stage-icon{width:64px!important;height:64px!important;margin-inline:auto;border:1px solid #ead6cc;background:#f2e2db}.stage-copy strong{display:block;font:500 12px/1.22 Georgia,serif}.stage-copy small,.stage-arrow{display:none!important}
.audience-section{padding:18px 0 26px}.audience-shell{box-shadow:0 18px 48px rgba(65,45,39,.05)}
.stories-section{padding-top:38px}.story-grid{gap:18px}.story-card{border-radius:16px;box-shadow:0 10px 28px rgba(61,43,38,.035)}.story-card>a{display:block;min-height:0}.story-media{min-height:0;aspect-ratio:16/10;background:#f1e3dc}.story-media img{width:100%;height:100%;object-fit:cover;object-position:center;display:block}.story-fallback{width:100%;height:100%;display:grid;place-items:center;background:linear-gradient(145deg,#f2e5df,#ead7cd)}.story-fallback svg{width:34%;height:34%}.story-body{min-height:176px;padding:18px 18px 16px}.story-body h3{font-size:20px;line-height:1.12;margin:9px 0 8px}.story-body p{font-size:11.5px;line-height:1.42}.story-meta{font-size:10px;padding-top:12px}
.community-card{box-shadow:0 16px 42px rgba(65,45,39,.05)}
@media(max-width:900px){.hero-shell{grid-template-columns:minmax(0,47%) minmax(0,53%);min-height:500px}.hero-copy{padding:40px 30px}.hero-copy h1{font-size:clamp(45px,5.9vw,60px)}.hero-media{min-height:500px}.story-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:700px){.hero-shell{grid-template-columns:1fr;min-height:0}.hero-copy{padding:38px 24px 31px}.hero-copy h1{font-size:clamp(43px,12vw,58px);max-width:11ch}.hero-media{min-height:390px}.hero-note{right:18px;bottom:18px}.topic-rail{grid-auto-columns:168px}.premium-topic-card{min-width:168px}.story-grid{grid-template-columns:1fr;gap:14px}.story-body{min-height:0}}
@media(max-width:480px){.hero-search-premium button{padding:0 15px}.hero-trust span:nth-child(3){display:none}.hero-media{min-height:350px}.hero-note{max-width:165px;font-size:15px}}
</style>

<section class="home-hero">
	<div class="container">
		<div class="hero-shell">
			<div class="hero-copy">
				<span class="eyebrow"><?php echo esc_html( mom_t( 'Maternidad con sentido', 'Motherhood with meaning' ) ); ?></span>
				<h1><?php echo esc_html( mom_t( 'Acompañándote en cada etapa', 'With you through every stage' ) ); ?></h1>
				<p><?php echo esc_html( mom_t( 'Información fiable, ideas prácticas y apoyo real para una maternidad más tranquila y consciente.', 'Reliable information, practical ideas and real support for a calmer, more intentional motherhood.' ) ); ?></p>
				<form class="hero-search-premium" role="search" method="get" action="<?php echo esc_url( $home_url ); ?>">
					<span aria-hidden="true">⌕</span>
					<input type="search" name="s" placeholder="<?php echo esc_attr( mom_t( '¿Qué te gustaría saber hoy?', 'What would you like to know today?' ) ); ?>" aria-label="<?php echo esc_attr( mom_t( 'Buscar artículos', 'Search articles' ) ); ?>">
					<button type="submit"><?php echo esc_html( mom_t( 'Buscar', 'Search' ) ); ?></button>
				</form>
				<div class="hero-trust" aria-label="<?php echo esc_attr( mom_t( 'Principios editoriales', 'Editorial principles' ) ); ?>">
					<span><i aria-hidden="true">✓</i><?php echo esc_html( mom_t( 'Contenido con contexto', 'Content with context' ) ); ?></span>
					<span><i aria-hidden="true">♡</i><?php echo esc_html( mom_t( 'Criterio editorial', 'Editorial judgment' ) ); ?></span>
					<span><i aria-hidden="true">○</i><?php echo esc_html( mom_t( 'Para la vida real', 'For real life' ) ); ?></span>
				</div>
			</div>
			<div class="hero-media">
				<img class="mom-hq-hero" src="<?php echo esc_url( $hq_assets . 'hero-mother-baby.jpg' ); ?>" width="2400" height="1350" sizes="(max-width:700px) 100vw, 55vw" alt="<?php echo esc_attr( mom_t( 'Madre abrazando a su bebé en casa', 'Mother holding her baby at home' ) ); ?>" loading="eager" fetchpriority="high" decoding="async">
				<div class="hero-note"><?php echo esc_html( mom_t( 'Pequeños momentos, grandes historias.', 'Small moments, big stories.' ) ); ?></div>
			</div>
		</div>
	</div>
</section>

<section class="home-section topic-section" id="temas">
	<div class="container">
		<header class="home-section-head">
			<div>
				<h2><?php echo esc_html( mom_t( 'Explora por tema', 'Explore by topic' ) ); ?></h2>
				<p><?php echo esc_html( mom_t( 'Encuentra rápido el contenido que necesitas.', 'Find the content you need quickly.' ) ); ?></p>
			</div>
			<span class="section-hint"><?php echo esc_html( mom_t( 'Desliza para ver todos', 'Scroll to see all' ) ); ?> <span aria-hidden="true">→</span></span>
		</header>
		<div class="topic-rail" aria-label="<?php echo esc_attr( mom_t( 'Temas', 'Topics' ) ); ?>">
			<?php foreach ( $topics as $topic ) : ?>
				<?php $topic_image = $topic_image_url( $topic['id'] ); ?>
				<a class="premium-topic-card" href="<?php echo esc_url( mom_term_url( 'topic', $topic['id'], $topic['label'] ) ); ?>">
					<?php if ( $topic_image ) : ?>
						<span class="premium-topic-art mom-hq-topic" aria-hidden="true"><img src="<?php echo esc_url( $topic_image ); ?>" width="1448" height="1086" sizes="(max-width:700px) 168px, 184px" alt="" loading="lazy" decoding="async"></span>
					<?php else : ?>
						<span class="premium-topic-art" aria-hidden="true"><?php echo mom_topic_art_svg( $topic['id'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php endif; ?>
					<span class="premium-topic-copy"><strong><?php echo esc_html( $topic['label'] ); ?></strong></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="home-section stage-section" id="etapas">
	<div class="container">
		<header class="home-section-head"><div><h2><?php echo esc_html( mom_t( 'Descubre por etapa', 'Browse by stage' ) ); ?></h2><p><?php echo esc_html( mom_t( 'Recursos pensados para el momento en el que estás.', 'Resources for the stage you are in right now.' ) ); ?></p></div></header>
		<div class="stage-rail">
			<?php foreach ( $stages as $index => $stage ) : ?>
				<a class="premium-stage-card" href="<?php echo esc_url( mom_term_url( 'stage', $stage['id'], $stage['label'] ) ); ?>"><span class="stage-icon" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><span class="stage-copy"><strong><?php echo esc_html( $stage['label'] ); ?></strong></span></a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="home-section audience-section" id="para-ti">
	<div class="container audience-shell">
		<div class="audience-intro"><span class="eyebrow"><?php echo esc_html( mom_t( 'Para quién', 'For whom' ) ); ?></span><h2><?php echo esc_html( mom_t( 'La maternidad también se vive en plural.', 'Motherhood is lived together.' ) ); ?></h2><p><?php echo esc_html( mom_t( 'Contenido pensado para madres, padres, pareja y la red que acompaña.', 'Content for moms, dads, partners and the wider support network.' ) ); ?></p></div>
		<div class="audience-list">
			<?php $symbols = array( '♡', '○', '◇', '∞', '⌂' ); foreach ( $audiences as $index => $audience ) : ?>
				<a class="premium-audience-card" href="<?php echo esc_url( mom_term_url( 'audience', $audience['id'], $audience['label'] ) ); ?>"><span class="audience-symbol" aria-hidden="true"><?php echo esc_html( $symbols[ $index ] ?? '○' ); ?></span><strong><?php echo esc_html( $audience['label'] ); ?></strong></a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="home-section stories-section" id="ultimos-articulos">
	<div class="container">
		<header class="home-section-head stories-head"><div><h2><?php echo esc_html( mom_t( 'Artículos destacados', 'Featured stories' ) ); ?></h2><p><?php echo esc_html( mom_t( 'Historias, guías y reflexiones para una maternidad más real.', 'Stories, guides and perspective for a more real motherhood.' ) ); ?></p></div></header>
		<div class="story-grid">
			<?php
			$featured = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 4, 'ignore_sticky_posts' => false ) );
			if ( $featured->have_posts() ) :
				while ( $featured->have_posts() ) : $featured->the_post();
					$post_id = get_the_ID(); $topic_id = mom_primary_topic_id( $post_id ); ?>
					<article class="story-card"><a href="<?php the_permalink(); ?>"><div class="story-media">
						<?php if ( has_post_thumbnail( $post_id ) ) : echo get_the_post_thumbnail( $post_id, 'mom-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); else : ?><div class="story-fallback" style="color:<?php echo esc_attr( mom_topic_accent( $topic_id ) ); ?>"><?php echo mom_topic_art_svg( $topic_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
					</div><div class="story-body"><span class="story-kicker"><?php echo esc_html( mom_topic_label( $topic_id ) ); ?></span><h3><?php the_title(); ?></h3><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 16 ) ); ?></p><span class="story-meta"><?php echo esc_html( mom_reading_time( $post_id ) ); ?></span></div></a></article>
				<?php endwhile; wp_reset_postdata();
			endif; ?>
		</div>
	</div>
</section>

<section class="community-section" id="comunidad">
	<div class="container"><div class="community-card"><div class="community-kicker"><?php echo esc_html( mom_t( 'Una comunidad que te acompaña', 'A community beside you' ) ); ?></div><div class="community-copy"><h2><?php echo esc_html( mom_t( 'MOM. para la vida real.', 'MOM. for real life.' ) ); ?></h2><p><?php echo esc_html( mom_t( 'Contenido para decidir con más contexto, vivir cada etapa con menos ruido y recordar que tú también importas.', 'Content to decide with more context, live each stage with less noise and remember that you matter too.' ) ); ?></p></div><a class="button button-primary" href="#temas"><?php echo esc_html( mom_t( 'Empieza a explorar', 'Start exploring' ) ); ?> <span aria-hidden="true">→</span></a><div class="community-signature"><?php echo esc_html( mom_t( 'Mujeres más acompañadas. Familias más tranquilas.', 'More supported women. Calmer families.' ) ); ?></div></div></div>
</section>

<?php get_footer(); ?>