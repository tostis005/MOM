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
$hq_uri    = trailingslashit( get_template_directory_uri() ) . 'assets/images/hq/';
$hq_dir    = trailingslashit( get_template_directory() ) . 'assets/images/hq/';

$asset_url = static function ( $filename ) use ( $hq_uri, $hq_dir ) {
	if ( ! $filename || ! file_exists( $hq_dir . $filename ) ) {
		return '';
	}
	return $hq_uri . $filename;
};

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

$stage_images = array(
	'pregnancy'             => 'stage-pregnancy.jpg',
	'preparing-for-baby'    => 'stage-preparing-for-baby.jpg',
	'postpartum'             => 'stage-postpartum.jpg',
	'newborn'                => 'stage-newborn.jpg',
	'baby'                   => 'stage-baby.jpg',
	'toddler'                => 'stage-toddler.jpg',
	'preschool'              => 'stage-preschool.jpg',
	'school-age'             => 'stage-school-age.jpg',
	'second-child-siblings'  => 'stage-second-child-siblings.jpg',
	'parenthood-general'     => 'stage-parenthood-general.jpg',
);

$audience_images = array(
	'parents'           => 'audience-parents.jpg',
	'mothers'           => 'audience-mothers.jpg',
	'fathers'           => 'audience-fathers.jpg',
	'couples'           => 'audience-couples.jpg',
	'family-caregivers' => 'audience-family-caregivers.jpg',
);

$hero_image = $asset_url( 'hero-mother-baby.jpg' );
?>

<style>
.home-hero{padding:22px 0 28px}.hero-shell{min-height:520px;grid-template-columns:minmax(0,1.04fr) minmax(430px,.96fr);align-items:stretch;border-radius:28px;overflow:hidden}.hero-copy{padding:clamp(42px,5vw,72px) clamp(34px,4.8vw,70px);justify-content:center}.hero-copy h1{max-width:10.5ch;margin:14px 0 18px;font-size:clamp(50px,5.25vw,72px);line-height:.99;letter-spacing:-.048em}.hero-copy>p:not(.hero-signature){max-width:51ch;font-size:clamp(16px,1.35vw,18px);line-height:1.55}.hero-media{min-height:520px;position:relative;overflow:hidden;background:#e4d3c7}.mom-hq-hero{width:100%;height:100%;object-fit:cover;object-position:center 43%;display:block}.hero-media::after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(50,35,30,.06),transparent 28%,rgba(42,30,27,.03));pointer-events:none}.hero-quote{right:24px;top:auto;bottom:24px;max-width:184px;padding:14px 16px;border:1px solid rgba(124,73,68,.14);border-radius:15px;background:rgba(255,253,251,.9);backdrop-filter:blur(10px);color:#5d4540;text-shadow:none;box-shadow:0 12px 30px rgba(66,45,39,.08)}.hero-quote span{font-size:18px;line-height:1.22}.hero-quote strong{margin-top:9px;color:#7c4944}.hero-search-premium{display:flex;align-items:center;gap:8px;max-width:590px;margin-top:27px;padding:7px 7px 7px 18px;border:1px solid #e1d3ca;border-radius:999px;background:#fff;box-shadow:0 10px 30px rgba(63,44,39,.055)}.hero-search-premium span{font-size:18px;color:#927c73}.hero-search-premium input{min-width:0;flex:1;border:0;outline:0;background:transparent;padding:9px 5px;color:var(--mom-ink);font-size:13px}.hero-search-premium button{border:0;border-radius:999px;background:var(--mom-rose);color:#fff;min-height:40px;padding:0 21px;font-size:12px;font-weight:800;cursor:pointer}.hero-trust{display:flex;flex-wrap:wrap;gap:9px 20px;margin-top:20px;color:#776c67;font-size:10.5px}.hero-trust span{display:flex;align-items:center;gap:7px}.hero-trust i{width:22px;height:22px;display:grid;place-items:center;border:1px solid #dbc5bb;border-radius:50%;color:#9b625a;font-style:normal;font-size:10px;background:rgba(255,255,255,.45)}
.discovery-section{padding:48px 0}.discovery-section+.discovery-section{border-top:1px solid rgba(233,223,215,.72)}.discovery-head{display:flex;justify-content:space-between;align-items:end;gap:28px;margin-bottom:24px}.discovery-head h2{margin:0;font:500 clamp(30px,3vw,42px)/1.04 Georgia,serif;letter-spacing:-.04em}.discovery-head p{max-width:580px;margin:7px 0 0;color:var(--mom-muted);font-size:13px}.taxonomy-photo-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:22px 16px}.taxonomy-photo-card{min-width:0;text-decoration:none}.taxonomy-photo-media{position:relative;overflow:hidden;aspect-ratio:4/3;border-radius:18px;background:linear-gradient(145deg,#f2e5df,#ead7cd);box-shadow:0 10px 28px rgba(61,43,38,.055)}.taxonomy-photo-media img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .28s ease}.taxonomy-photo-card:hover .taxonomy-photo-media img{transform:scale(1.025)}.taxonomy-photo-fallback{width:100%;height:100%;display:grid;place-items:center;color:var(--topic-accent,#9b5f60)}.taxonomy-photo-fallback svg{width:34%;height:34%}.taxonomy-photo-copy{padding:11px 4px 0;text-align:center}.taxonomy-photo-copy strong{display:block;font:500 16px/1.2 Georgia,serif}.taxonomy-photo-copy small{display:block;margin-top:5px;color:var(--mom-muted);font-size:10.5px;line-height:1.32}.stage-photo-grid .taxonomy-photo-copy strong{font-size:14px}.audience-section-grid{background:linear-gradient(180deg,#faf5f1 0%,#fffdfb 100%)}.audience-photo-grid{grid-template-columns:repeat(5,minmax(0,1fr))}.audience-photo-grid .taxonomy-photo-media{aspect-ratio:5/4}.audience-photo-grid .taxonomy-photo-copy strong{font-size:17px}
.stories-section{padding:52px 0 44px}.story-grid{gap:18px}.story-card{border-radius:16px;box-shadow:0 10px 28px rgba(61,43,38,.035)}.story-card>a{display:block;min-height:0}.story-media{min-height:0;aspect-ratio:16/10;background:#f1e3dc;overflow:hidden}.story-media img,.story-media .story-fallback img{width:100%;height:100%;object-fit:cover;object-position:center;display:block}.story-fallback{width:100%;height:100%;display:grid;place-items:center;background:linear-gradient(145deg,#f2e5df,#ead7cd);overflow:hidden}.story-fallback svg{width:34%;height:34%}.story-body{min-height:176px;padding:18px 18px 16px}.story-body h3{font-size:20px;line-height:1.12;margin:9px 0 8px}.story-body p{font-size:11.5px;line-height:1.42}.story-meta{font-size:10px;padding-top:12px}
@media(max-width:1180px){.taxonomy-photo-grid{grid-template-columns:repeat(4,minmax(0,1fr))}.audience-photo-grid{grid-template-columns:repeat(5,minmax(0,1fr))}.hero-shell{grid-template-columns:minmax(0,1fr) minmax(380px,.9fr)}}
@media(max-width:900px){.hero-shell{grid-template-columns:minmax(0,1fr) minmax(330px,.82fr)}.hero-copy{padding:40px 32px}.hero-copy h1{font-size:clamp(46px,6vw,60px)}.taxonomy-photo-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.audience-photo-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.story-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:700px){.hero-shell{grid-template-columns:1fr;min-height:0}.hero-copy{padding:38px 24px 30px}.hero-copy h1{font-size:clamp(43px,12vw,58px);max-width:11ch}.hero-media{min-height:390px}.hero-quote{right:18px;bottom:18px}.hero-search-premium{margin-top:22px}.hero-trust{gap:8px 13px}.discovery-section{padding:36px 0}.discovery-head{align-items:flex-start}.taxonomy-photo-grid,.audience-photo-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 12px}.taxonomy-photo-copy strong{font-size:14px}.taxonomy-photo-copy small{display:none}.story-grid{grid-template-columns:1fr;gap:14px}.story-body{min-height:0}}
@media(max-width:480px){.hero-search-premium button{padding:0 15px}.hero-trust span:nth-child(3){display:none}.taxonomy-photo-grid,.audience-photo-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.taxonomy-photo-media{border-radius:14px}}
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
				<?php if ( $hero_image ) : ?>
					<img class="mom-hq-hero" src="<?php echo esc_url( $hero_image ); ?>" width="2400" height="1350" sizes="(max-width:700px) 100vw, 48vw" alt="<?php echo esc_attr( mom_t( 'Madre abrazando a su bebé en casa', 'Mother holding her baby at home' ) ); ?>" loading="eager" fetchpriority="high" decoding="async">
				<?php else : ?>
					<div class="hero-art-fallback"><?php echo mom_hero_art_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endif; ?>
				<div class="hero-quote"><span><?php echo esc_html( mom_t( '“Aquí también importas tú.”', '“You matter here, too.”' ) ); ?></span><strong>MOM.</strong></div>
			</div>
		</div>
	</div>
</section>

<section class="discovery-section topic-section" id="temas">
	<div class="container">
		<header class="discovery-head"><div><h2><?php echo esc_html( mom_t( 'Explora por tema', 'Explore by topic' ) ); ?></h2><p><?php echo esc_html( mom_t( 'Todo el mapa de contenidos, visible de un vistazo.', 'Your full content map, visible at a glance.' ) ); ?></p></div></header>
		<div class="taxonomy-photo-grid topic-photo-grid">
			<?php foreach ( $topics as $topic ) : $topic_image = $asset_url( $topic_images[ $topic['id'] ] ?? '' ); ?>
				<a class="taxonomy-photo-card" style="--topic-accent:<?php echo esc_attr( mom_topic_accent( $topic['id'] ) ); ?>" href="<?php echo esc_url( mom_term_url( 'topic', $topic['id'], $topic['label'] ) ); ?>">
					<div class="taxonomy-photo-media">
						<?php if ( $topic_image ) : ?><img src="<?php echo esc_url( $topic_image ); ?>" width="1448" height="1086" sizes="(max-width:700px) 46vw, (max-width:1180px) 23vw, 18vw" alt="" loading="lazy" decoding="async"><?php else : ?><div class="taxonomy-photo-fallback"><?php echo mom_topic_art_svg( $topic['id'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
					</div>
					<div class="taxonomy-photo-copy"><strong><?php echo esc_html( $topic['label'] ); ?></strong><small><?php echo esc_html( wp_trim_words( mom_topic_description( $topic['id'] ), 9 ) ); ?></small></div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="discovery-section stage-section" id="etapas">
	<div class="container">
		<header class="discovery-head"><div><h2><?php echo esc_html( mom_t( 'Descubre por etapa', 'Browse by stage' ) ); ?></h2><p><?php echo esc_html( mom_t( 'Encuentra directamente el momento vital en el que estás.', 'Go straight to the stage you are living now.' ) ); ?></p></div></header>
		<div class="taxonomy-photo-grid stage-photo-grid">
			<?php foreach ( $stages as $stage ) : $stage_image = $asset_url( $stage_images[ $stage['id'] ] ?? '' ); ?>
				<a class="taxonomy-photo-card" href="<?php echo esc_url( mom_term_url( 'stage', $stage['id'], $stage['label'] ) ); ?>">
					<div class="taxonomy-photo-media">
						<?php if ( $stage_image ) : ?><img src="<?php echo esc_url( $stage_image ); ?>" width="1448" height="1086" sizes="(max-width:700px) 46vw, (max-width:1180px) 23vw, 18vw" alt="" loading="lazy" decoding="async"><?php else : ?><div class="taxonomy-photo-fallback" style="--topic-accent:#9b5f60"><?php echo mom_hero_art_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
					</div>
					<div class="taxonomy-photo-copy"><strong><?php echo esc_html( $stage['label'] ); ?></strong></div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="discovery-section audience-section-grid" id="para-ti">
	<div class="container">
		<header class="discovery-head"><div><h2><?php echo esc_html( mom_t( 'Para ti', 'For you' ) ); ?></h2><p><?php echo esc_html( mom_t( 'Porque la misma pregunta cambia según quién la está viviendo.', 'Because the same question changes depending on who is living it.' ) ); ?></p></div></header>
		<div class="taxonomy-photo-grid audience-photo-grid">
			<?php foreach ( $audiences as $audience ) : $audience_image = $asset_url( $audience_images[ $audience['id'] ] ?? '' ); ?>
				<a class="taxonomy-photo-card" href="<?php echo esc_url( mom_term_url( 'audience', $audience['id'], $audience['label'] ) ); ?>">
					<div class="taxonomy-photo-media">
						<?php if ( $audience_image ) : ?><img src="<?php echo esc_url( $audience_image ); ?>" width="1448" height="1086" sizes="(max-width:700px) 46vw, (max-width:900px) 31vw, 18vw" alt="" loading="lazy" decoding="async"><?php else : ?><div class="taxonomy-photo-fallback" style="--topic-accent:#9b5f60"><?php echo mom_hero_art_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
					</div>
					<div class="taxonomy-photo-copy"><strong><?php echo esc_html( $audience['label'] ); ?></strong></div>
				</a>
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
					$post_id = get_the_ID(); $topic_id = mom_primary_topic_id( $post_id ); $fallback_image = $asset_url( $topic_images[ $topic_id ] ?? '' );
					?>
					<article class="story-card"><a href="<?php the_permalink(); ?>">
						<div class="story-media">
							<?php if ( has_post_thumbnail( $post_id ) ) : echo get_the_post_thumbnail( $post_id, 'mom-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); elseif ( $fallback_image ) : ?><div class="story-fallback"><img src="<?php echo esc_url( $fallback_image ); ?>" width="1448" height="1086" alt="" loading="lazy" decoding="async"></div><?php else : ?><div class="story-fallback" style="color:<?php echo esc_attr( mom_topic_accent( $topic_id ) ); ?>"><?php echo mom_topic_art_svg( $topic_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
						</div>
						<div class="story-body"><span class="story-kicker"><?php echo esc_html( mom_topic_label( $topic_id ) ); ?></span><h3><?php the_title(); ?></h3><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 16 ) ); ?></p><span class="story-meta"><?php echo esc_html( mom_reading_time( $post_id ) ); ?></span></div>
					</a></article>
				<?php endwhile; wp_reset_postdata(); endif; ?>
		</div>
	</div>
</section>

<section class="community-section" id="comunidad"><div class="container"><div class="community-card"><div class="community-kicker"><?php echo esc_html( mom_t( 'Una comunidad que te acompaña', 'A community beside you' ) ); ?></div><div class="community-copy"><h2><?php echo esc_html( mom_t( 'MOM. para la vida real.', 'MOM. for real life.' ) ); ?></h2><p><?php echo esc_html( mom_t( 'Contenido para decidir con más contexto, vivir cada etapa con menos ruido y recordar que tú también importas.', 'Content to decide with more context, live each stage with less noise and remember that you matter too.' ) ); ?></p></div><a class="button button-primary" href="#temas"><?php echo esc_html( mom_t( 'Empieza a explorar', 'Start exploring' ) ); ?> <span aria-hidden="true">→</span></a><div class="community-signature"><?php echo esc_html( mom_t( 'Mujeres más acompañadas. Familias más tranquilas.', 'More supported women. Calmer families.' ) ); ?></div></div></div></section>

<?php get_footer(); ?>