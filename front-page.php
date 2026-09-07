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
	'play-learning-autonomy'      => 'topic-parenting.jpg',
	'childcare-school-social'     => 'topic-parenting.jpg',
	'pregnancy-preparation'       => 'topic-pregnancy.jpg',
	'postpartum-newborn'          => 'topic-postpartum.jpg',
	'breastfeeding-baby-feeding'  => 'hero-mother-baby.jpg',
	'couple-coparenting'          => 'topic-relationships.jpg',
	'motherhood-identity'         => 'topic-wellbeing.jpg',
	'routines-family-life'        => 'topic-relationships.jpg',
	'family-siblings-boundaries'  => 'topic-relationships.jpg',
);

$topic_image_url = static function ( $topic_id ) use ( $topic_images, $ai_assets ) {
	if ( empty( $topic_images[ $topic_id ] ) ) {
		return '';
	}
	return $ai_assets . $topic_images[ $topic_id ];
};
?>

<style>
.mom-ai-hero{width:100%;height:100%;min-height:100%;object-fit:cover;object-position:center;display:block}
.premium-topic-art.mom-ai-topic{overflow:hidden;padding:0;background:#eadfd8;box-shadow:inset 0 0 0 1px rgba(69,47,44,.06)}
.premium-topic-art.mom-ai-topic img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .35s ease,filter .35s ease}
.premium-topic-card:hover .premium-topic-art.mom-ai-topic img{transform:scale(1.045);filter:saturate(.96) contrast(1.02)}
.story-fallback.mom-ai-story{padding:0;overflow:hidden;background:#eadfd8}
.story-fallback.mom-ai-story img{width:100%;height:100%;object-fit:cover;display:block}
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
