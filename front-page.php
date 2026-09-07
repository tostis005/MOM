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
$ai_assets = trailingslashit( get_template_directory_uri() ) . 'assets/images/ai/';

$topic_images = array(
	'sleep'                       => 'topic-sleep.jpg',
	'parenting-behavior'          => 'topic-parenting.jpg',
	'child-feeding'               => 'topic-feeding.jpg',
	'potty-hygiene-autonomy'      => 'topic-parenting.jpg',
	'routines-family-life'        => 'topic-relationships.jpg',
	'play-learning-autonomy'      => 'topic-parenting.jpg',
	'childcare-school-social'     => 'topic-parenting.jpg',
	'pregnancy-preparation'       => 'topic-pregnancy.jpg',
	'postpartum-newborn'          => 'topic-postpartum.jpg',
	'breastfeeding-baby-feeding'  => 'topic-postpartum.jpg',
	'couple-coparenting'          => 'topic-relationships.jpg',
	'motherhood-identity'         => 'topic-wellbeing.jpg',
	'family-siblings-boundaries'  => 'topic-relationships.jpg',
	'work-balance-life'           => 'topic-wellbeing.jpg',
	'travel-outings-celebrations' => 'topic-travel.jpg',
);

$topic_image_url = static function ( $topic_id ) use ( $topic_images, $ai_assets ) {
	if ( empty( $topic_images[ $topic_id ] ) ) {
		return '';
	}
	return $ai_assets . $topic_images[ $topic_id ];
};
?>

<style>
/* Final production pass: keep the hero in one row on real desktop/laptop sizes and
   never enlarge small AI assets beyond the size where they remain visually crisp. */
.home-hero{padding:22px 0 24px}
.hero-shell{min-height:520px;grid-template-columns:minmax(0,1.08fr) minmax(410px,.92fr);align-items:stretch;border-radius:28px;overflow:hidden}
.hero-copy{padding:clamp(42px,5vw,72px) clamp(34px,4.8vw,70px);justify-content:center}
.hero-copy h1{max-width:10.5ch;margin:14px 0 18px;font-size:clamp(50px,5.25vw,72px);line-height:.99;letter-spacing:-.048em}
.hero-copy>p:not(.hero-signature){max-width:51ch;font-size:clamp(16px,1.35vw,18px);line-height:1.55}
.hero-media{min-height:520px;display:grid;place-items:center;padding:26px;background:linear-gradient(145deg,#eadbd1,#dbc5b8);overflow:hidden}
.mom-ai-hero{width:min(100%,520px);height:auto;aspect-ratio:4/3;object-fit:cover;object-position:center 43%;display:block;border-radius:24px;box-shadow:0 22px 50px rgba(67,47,42,.14)}
.hero-media::after{display:none}
.hero-quote{right:22px;top:auto;bottom:22px;max-width:184px;padding:14px 16px;border:1px solid rgba(124,73,68,.14);border-radius:15px;background:rgba(255,253,251,.9);backdrop-filter:blur(10px);color:#5d4540;text-shadow:none;box-shadow:0 12px 30px rgba(66,45,39,.08)}
.hero-quote span{font-size:18px;line-height:1.22}.hero-quote strong{margin-top:9px;color:#7c4944}
.hero-search-premium{display:flex;align-items:center;gap:8px;max-width:590px;margin-top:27px;padding:7px 7px 7px 18px;border:1px solid #e1d3ca;border-radius:999px;background:#fff;box-shadow:0 10px 30px rgba(63,44,39,.055)}
.hero-search-premium span{font-size:18px;color:#927c73}.hero-search-premium input{min-width:0;flex:1;border:0;outline:0;background:transparent;padding:9px 5px;color:var(--mom-ink);font-size:13px}
.hero-search-premium button{border:0;border-radius:999px;background:var(--mom-rose);color:#fff;min-height:40px;padding:0 21px;font-size:12px;font-weight:800;cursor:pointer}
.hero-trust{display:flex;flex-wrap:wrap;gap:9px 20px;margin-top:20px;color:#776c67;font-size:10.5px}.hero-trust span{display:flex;align-items:center;gap:7px}.hero-trust i{width:22px;height:22px;display:grid;place-items:center;border:1px solid #dbc5bb;border-radius:50%;color:#9b625a;font-style:normal;font-size:10px;background:rgba(255,255,255,.45)}
.topic-section{padding-top:34px;padding-bottom:32px}.topic-rail{grid-auto-columns:160px;gap:14px;padding:2px 0 12px;scroll-snap-type:x proximity}.premium-topic-card{min-width:160px;border:0;border-radius:0;background:transparent;box-shadow:none;scroll-snap-align:start}.premium-topic-card:hover{transform:translateY(-2px);box-shadow:none}.premium-topic-art.mom-ai-topic{height:120px;overflow:hidden;padding:0;border:1px solid #eadfd8;border-radius:14px;background:#eadfd8;box-shadow:0 8px 24px rgba(66,46,40,.055)}
.premium-topic-art.mom-ai-topic img{width:100%;height:100%;object-fit:cover;object-position:center;display:block;filter:none;transform:none;transition:transform .28s ease}.premium-topic-card:hover .premium-topic-art.mom-ai-topic img{transform:scale(1.025);filter:none}.premium-topic-art:not(.mom-ai-topic){height:120px;border-radius:14px}.premium-topic-copy{min-height:0;padding:10px 2px 0;text-align:center}.premium-topic-copy strong{font:500 14px/1.18 Georgia,serif}.premium-topic-copy small{display:none}
.stage-section{padding-top:33px;padding-bottom:34px}.stage-rail{grid-auto-columns:116px;gap:16px;padding:4px 0 10px;scroll-snap-type:x proximity}.premium-stage-card{min-height:0;display:flex;flex-direction:column;justify-content:flex-start;gap:9px;padding:0;border:0;border-radius:0;background:transparent;text-align:center;scroll-snap-align:start}.premium-stage-card:hover{background:transparent;border-color:transparent}.stage-icon{width:62px;height:62px;margin-inline:auto;background:#f1e1da;font-size:10px;border:1px solid #ead6cc}.stage-copy strong{font:500 12px/1.22 Georgia,serif}.stage-copy small,.stage-arrow{display:none}
.audience-section{padding-top:20px;padding-bottom:24px}.audience-shell{box-shadow:0 18px 48px rgba(65,45,39,.055)}.premium-audience-card{transition:transform .2s ease,background .2s ease}.premium-audience-card:hover{transform:translateY(-2px)}
.stories-section{padding-top:38px}.story-grid{gap:18px}.story-card{border-radius:16px;box-shadow:0 10px 28px rgba(61,43,38,.035)}.story-card>a{display:block;min-height:0}.story-media{min-height:0;aspect-ratio:16/10;background:#f1e3dc}.story-media img{width:100%;height:100%;object-fit:cover;object-position:center;display:block}.story-fallback{width:100%;height:100%;display:grid;place-items:center;background:linear-gradient(145deg,#f2e5df,#ead7cd)}.story-fallback svg{width:34%;height:34%}.story-body{min-height:176px;padding:18px 18px 16px}.story-body h3{font-size:20px;line-height:1.12;margin:9px 0 8px}.story-body p{font-size:11.5px;line-height:1.42}.story-meta{font-size:10px;padding-top:12px}
.community-card{box-shadow:0 16px 42px rgba(65,45,39,.05)}
@media (max-width:900px){
  .hero-shell{grid-template-columns:minmax(0,1fr) minmax(330px,.82fr)}
  .hero-copy{padding:40px 32px}.hero-copy h1{font-size:clamp(46px,6vw,60px)}
  .hero-media{min-height:480px;padding:20px}.mom-ai-hero{width:min(100%,440px)}
  .story-grid{grid-template-columns:repeat(2,1fr)}
}
@media (max-width:700px){
  .hero-shell{grid-template-columns:1fr;min-height:0}.hero-copy{padding:38px 24px 30px}.hero-copy h1{font-size:clamp(43px,12vw,58px);max-width:11ch}.hero-media{min-height:0;padding:18px}.mom-ai-hero{width:100%;max-width:520px;aspect-ratio:4/3}.hero-quote{right:30px;bottom:30px}.hero-search-premium{margin-top:22px}.hero-trust{gap:8px 13px}.topic-rail{grid-auto-columns:154px}.premium-topic-card{min-width:154px}.premium-topic-art.mom-ai-topic,.premium-topic-art:not(.mom-ai-topic){height:116px}.story-grid{grid-template-columns:1fr;gap:14px}.story-body{min-height:0}
}
@media (max-width:480px){.hero-search-premium button{padding:0 15px}.hero-trust span:nth-child(3){display:none}.hero-quote{right:27px;bottom:27px;max-width:166px}.hero-quote span{font-size:16px}}
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
				<img class="mom-ai-hero" src="<?php echo esc_url( $ai_assets . 'hero-mother-baby.jpg' ); ?>" alt="<?php echo esc_attr( mom_t( 'Madre abrazando a su bebé en casa', 'Mother holding her baby at home' ) ); ?>" loading="eager" fetchpriority="high" decoding="async">
				<div class="hero-quote">
					<span><?php echo esc_html( mom_t( '“Aquí también importas tú.”', '“You matter here, too.”' ) ); ?></span>
					<strong>MOM.</strong>
				</div>
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
				<a class="premium-topic-card" style="--topic-accent:<?php echo esc_attr( mom_topic_accent( $topic['id'] ) ); ?>" href="<?php echo esc_url( mom_term_url( 'topic', $topic['id'], $topic['label'] ) ); ?>">
					<?php if ( $topic_image ) : ?>
						<span class="premium-topic-art mom-ai-topic" aria-hidden="true"><img src="<?php echo esc_url( $topic_image ); ?>" alt="" loading="lazy" decoding="async"></span>
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
		<header class="home-section-head">
			<div>
				<h2><?php echo esc_html( mom_t( 'Descubre por etapa', 'Browse by stage' ) ); ?></h2>
				<p><?php echo esc_html( mom_t( 'Recursos pensados para el momento en el que estás.', 'Resources for the stage you are in right now.' ) ); ?></p>
			</div>
		</header>

		<div class="stage-rail">
			<?php foreach ( $stages as $index => $stage ) : ?>
				<a class="premium-stage-card" href="<?php echo esc_url( mom_term_url( 'stage', $stage['id'], $stage['label'] ) ); ?>">
					<span class="stage-icon" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<span class="stage-copy"><strong><?php echo esc_html( $stage['label'] ); ?></strong></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="home-section audience-section" id="para-ti">
	<div class="container audience-shell">
		<div class="audience-intro">
			<span class="eyebrow"><?php echo esc_html( mom_t( 'Para quién', 'For whom' ) ); ?></span>
			<h2><?php echo esc_html( mom_t( 'La maternidad también se vive en plural.', 'Motherhood is lived together.' ) ); ?></h2>
			<p><?php echo esc_html( mom_t( 'Contenido pensado para madres, padres, pareja y la red que acompaña.', 'Content for moms, dads, partners and the wider support network.' ) ); ?></p>
		</div>
		<div class="audience-list">
			<?php $symbols = array( '♡', '○', '◇', '∞', '⌂' ); foreach ( $audiences as $index => $audience ) : ?>
				<a class="premium-audience-card" href="<?php echo esc_url( mom_term_url( 'audience', $audience['id'], $audience['label'] ) ); ?>">
					<span class="audience-symbol" aria-hidden="true"><?php echo esc_html( $symbols[ $index ] ?? '○' ); ?></span>
					<strong><?php echo esc_html( $audience['label'] ); ?></strong>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="home-section stories-section" id="ultimos-articulos">
	<div class="container">
		<header class="home-section-head stories-head">
			<div>
				<h2><?php echo esc_html( mom_t( 'Artículos destacados', 'Featured stories' ) ); ?></h2>
				<p><?php echo esc_html( mom_t( 'Historias, guías y reflexiones para una maternidad más real.', 'Stories, guides and perspective for a more real motherhood.' ) ); ?></p>
			</div>
		</header>

		<div class="story-grid">
			<?php
			$featured = new WP_Query(
				array(
					'post_type'           => 'post',
					'post_status'         => 'publish',
					'posts_per_page'      => 4,
					'ignore_sticky_posts' => false,
				)
			);
			if ( $featured->have_posts() ) :
				while ( $featured->have_posts() ) :
					$featured->the_post();
					$post_id  = get_the_ID();
					$topic_id = mom_primary_topic_id( $post_id );
					?>
					<article class="story-card">
						<a href="<?php the_permalink(); ?>">
							<div class="story-media">
								<?php if ( has_post_thumbnail( $post_id ) ) : ?>
									<?php echo get_the_post_thumbnail( $post_id, 'mom-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
								<?php else : ?>
									<div class="story-fallback" style="color:<?php echo esc_attr( mom_topic_accent( $topic_id ) ); ?>"><?php echo mom_topic_art_svg( $topic_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
								<?php endif; ?>
							</div>
							<div class="story-body">
								<span class="story-kicker"><?php echo esc_html( mom_topic_label( $topic_id ) ); ?></span>
								<h3><?php the_title(); ?></h3>
								<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 16 ) ); ?></p>
								<span class="story-meta"><?php echo esc_html( mom_reading_time( $post_id ) ); ?></span>
							</div>
						</a>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
			endif;
			?>
		</div>
	</div>
</section>

<section class="community-section" id="comunidad">
	<div class="container">
		<div class="community-card">
			<div class="community-kicker"><?php echo esc_html( mom_t( 'Una comunidad que te acompaña', 'A community beside you' ) ); ?></div>
			<div class="community-copy">
				<h2><?php echo esc_html( mom_t( 'MOM. para la vida real.', 'MOM. for real life.' ) ); ?></h2>
				<p><?php echo esc_html( mom_t( 'Contenido para decidir con más contexto, vivir cada etapa con menos ruido y recordar que tú también importas.', 'Content to decide with more context, live each stage with less noise and remember that you matter too.' ) ); ?></p>
			</div>
			<a class="button button-primary" href="#temas"><?php echo esc_html( mom_t( 'Empieza a explorar', 'Start exploring' ) ); ?> <span aria-hidden="true">→</span></a>
			<div class="community-signature"><?php echo esc_html( mom_t( 'Mujeres más acompañadas. Familias más tranquilas.', 'More supported women. Calmer families.' ) ); ?></div>
		</div>
	</div>
</section>

<?php get_footer(); ?>