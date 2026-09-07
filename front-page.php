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
);

$topic_image_url = static function ( $topic_id ) use ( $topic_images, $ai_assets ) {
	if ( empty( $topic_images[ $topic_id ] ) ) {
		return '';
	}
	return $ai_assets . $topic_images[ $topic_id ];
};
?>

<style>
/* Production image/layout corrections: preserve photo ratios and avoid stretched AI crops. */
.hero-shell{min-height:530px;grid-template-columns:45% 55%}
.hero-copy{padding:clamp(42px,5vw,72px) clamp(32px,4.4vw,64px)}
.hero-copy h1{max-width:12ch;font-size:clamp(46px,5vw,68px);line-height:1.01}
.hero-media{min-height:530px;background:#e6d8ce}
.mom-ai-hero{width:100%;height:100%;min-height:530px;object-fit:cover;object-position:center center;display:block}
.hero-quote{right:5%;top:10%;max-width:190px;padding:15px 18px;border:1px solid rgba(124,73,68,.14);border-radius:16px;background:rgba(255,253,251,.82);backdrop-filter:blur(10px);color:#5d4540;text-shadow:none;box-shadow:0 12px 30px rgba(66,45,39,.08)}
.hero-quote span{font-size:19px;line-height:1.2}
.hero-quote strong{margin-top:10px;color:#7c4944}
.topic-rail{grid-auto-columns:180px;gap:14px;padding-bottom:12px}
.premium-topic-card{min-width:180px;border-radius:16px}
.premium-topic-art.mom-ai-topic{height:126px;overflow:hidden;padding:0;background:#eadfd8;box-shadow:inset 0 0 0 1px rgba(69,47,44,.06)}
.premium-topic-art.mom-ai-topic img{width:100%;height:100%;object-fit:cover;object-position:center;display:block;transition:transform .35s ease,filter .35s ease}
.premium-topic-card:hover .premium-topic-art.mom-ai-topic img{transform:scale(1.035);filter:saturate(.97) contrast(1.02)}
.premium-topic-copy{min-height:88px;padding:13px 14px 15px}
.premium-topic-copy strong{font-size:16px}
.premium-topic-copy small{font-size:10px;line-height:1.32;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.story-grid{gap:18px}
.story-card{border-radius:16px}
.story-card>a{display:block;min-height:0}
.story-media{min-height:0;aspect-ratio:16/10;background:#eadbd3}
.story-media img,.story-fallback.mom-ai-story img{width:100%;height:100%;object-fit:cover;object-position:center;display:block}
.story-fallback.mom-ai-story{width:100%;height:100%;padding:0;overflow:hidden;background:#eadfd8}
.story-body{min-height:178px;padding:18px 18px 16px}
.story-body h3{font-size:20px;line-height:1.12;margin:9px 0 8px}
.story-body p{font-size:11.5px;line-height:1.42}
.story-meta{font-size:10px;padding-top:12px}
@media (max-width:980px){
  .hero-shell{grid-template-columns:1fr;min-height:0}
  .hero-copy{padding:44px 40px}
  .hero-media,.mom-ai-hero{min-height:440px}
  .story-grid{grid-template-columns:repeat(2,1fr)}
}
@media (max-width:650px){
  .hero-copy{padding:36px 24px}
  .hero-copy h1{font-size:clamp(42px,12vw,56px)}
  .hero-media,.mom-ai-hero{min-height:350px}
  .hero-quote{top:auto;right:16px;bottom:16px;max-width:180px}
  .topic-rail{grid-auto-columns:166px}
  .premium-topic-card{min-width:166px}
  .premium-topic-art.mom-ai-topic{height:118px}
  .story-grid{grid-template-columns:1fr;gap:14px}
  .story-body{min-height:0}
}
</style>

<section class="home-hero">
	<div class="container">
		<div class="hero-shell">
			<div class="hero-copy">
				<span class="eyebrow"><?php echo esc_html( mom_t( 'Una maternidad más consciente', 'A more conscious motherhood' ) ); ?></span>
				<h1><?php echo esc_html( mom_t( 'Acompañar la maternidad con calma, criterio y belleza', 'Motherhood, accompanied with calm, perspective and beauty' ) ); ?></h1>
				<p><?php echo esc_html( mom_t( 'Información confiable, inspiración real y herramientas prácticas para cada etapa. Un espacio para mujeres y familias que crían, cuidan y también se cuidan.', 'Reliable information, real inspiration and practical tools for every stage. A space for women and families who raise, care and care for themselves too.' ) ); ?></p>
				<div class="hero-actions">
					<a class="button button-primary" href="#ultimos-articulos"><?php echo esc_html( mom_t( 'Explora artículos', 'Explore articles' ) ); ?> <span aria-hidden="true">→</span></a>
					<a class="button button-secondary" href="#comunidad"><?php echo esc_html( mom_t( 'Conoce MOM', 'Discover MOM' ) ); ?></a>
				</div>
				<p class="hero-signature"><?php echo esc_html( mom_t( 'Maternar también es una forma de volver a ti.', 'Mothering can also be a way back to yourself.' ) ); ?></p>
			</div>

			<div class="hero-media">
				<img class="mom-ai-hero" src="<?php echo esc_url( $ai_assets . 'hero-mother-baby.jpg' ); ?>" alt="<?php echo esc_attr( mom_t( 'Madre abrazando a su bebé en casa', 'Mother holding her baby at home' ) ); ?>" loading="eager" fetchpriority="high">
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
				<p><?php echo esc_html( mom_t( 'Todo lo que necesitas, en un solo lugar.', 'Everything you need, in one place.' ) ); ?></p>
			</div>
			<span class="section-hint"><?php echo esc_html( mom_t( 'Desliza para ver todos', 'Scroll to see all' ) ); ?> <span aria-hidden="true">→</span></span>
		</header>

		<div class="topic-rail" aria-label="<?php echo esc_attr( mom_t( 'Temas', 'Topics' ) ); ?>">
			<?php foreach ( $topics as $topic ) : ?>
				<?php $topic_image = $topic_image_url( $topic['id'] ); ?>
				<a class="premium-topic-card" style="--topic-accent:<?php echo esc_attr( mom_topic_accent( $topic['id'] ) ); ?>" href="<?php echo esc_url( mom_term_url( 'topic', $topic['id'], $topic['label'] ) ); ?>">
					<?php if ( $topic_image ) : ?>
						<span class="premium-topic-art mom-ai-topic" aria-hidden="true"><img src="<?php echo esc_url( $topic_image ); ?>" alt="" loading="lazy"></span>
					<?php else : ?>
						<span class="premium-topic-art" aria-hidden="true"><?php echo mom_topic_art_svg( $topic['id'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php endif; ?>
					<span class="premium-topic-copy">
						<strong><?php echo esc_html( $topic['label'] ); ?></strong>
						<small><?php echo esc_html( wp_trim_words( mom_topic_description( $topic['id'] ), 10 ) ); ?></small>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="home-section stage-section" id="etapas">
	<div class="container">
		<header class="home-section-head">
			<div>
				<h2><?php echo esc_html( mom_t( 'Explora por etapa', 'Explore by stage' ) ); ?></h2>
				<p><?php echo esc_html( mom_t( 'Cada etapa tiene preguntas distintas. Aquí tienes recursos pensados para el momento en el que estás.', 'Every stage brings different questions. Find resources for the moment you are in.' ) ); ?></p>
			</div>
		</header>

		<div class="stage-rail">
			<?php foreach ( $stages as $index => $stage ) : ?>
				<a class="premium-stage-card" href="<?php echo esc_url( mom_term_url( 'stage', $stage['id'], $stage['label'] ) ); ?>">
					<span class="stage-icon" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<span class="stage-copy"><strong><?php echo esc_html( $stage['label'] ); ?></strong><small><?php echo esc_html( mom_t( 'Recursos para esta etapa', 'Resources for this stage' ) ); ?></small></span>
					<span class="stage-arrow" aria-hidden="true">›</span>
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
			<p><?php echo esc_html( mom_t( 'Encuentra contenido pensado para quien está viviendo la pregunta: madres, padres, pareja y la red que acompaña.', 'Find content for the person living the question: moms, dads, partners and the wider support network.' ) ); ?></p>
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
					$post_id     = get_the_ID();
					$topic_id    = mom_primary_topic_id( $post_id );
					$topic_image = $topic_image_url( $topic_id );
					?>
					<article class="story-card">
						<a href="<?php the_permalink(); ?>">
							<div class="story-media">
								<?php if ( has_post_thumbnail( $post_id ) ) : ?>
									<?php echo get_the_post_thumbnail( $post_id, 'mom-card', array( 'loading' => 'lazy' ) ); ?>
								<?php elseif ( $topic_image ) : ?>
									<div class="story-fallback mom-ai-story" aria-hidden="true"><img src="<?php echo esc_url( $topic_image ); ?>" alt="" loading="lazy"></div>
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