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
?>

<section class="home-hero">
	<div class="container hero-grid">
		<div class="hero-copy">
			<span class="eyebrow"><?php echo esc_html( mom_t( 'Maternidad y vida familiar', 'Motherhood & family life' ) ); ?></span>
			<h1><?php echo esc_html( mom_t( 'Criar tiene muchas preguntas. Aquí caben todas.', 'Parenting comes with questions. There is room for all of them here.' ) ); ?></h1>
			<p><?php echo esc_html( mom_t( 'MOM reúne respuestas prácticas, contexto y acompañamiento editorial para el embarazo, los primeros años y todo lo que cambia en una familia por el camino.', 'MOM brings together practical answers, context and thoughtful guidance for pregnancy, the early years and everything that changes in a family along the way.' ) ); ?></p>
			<form class="hero-search" role="search" method="get" action="<?php echo esc_url( $home_url ); ?>">
				<label class="screen-reader-text" for="mom-search"><?php echo esc_html( mom_t( 'Buscar', 'Search' ) ); ?></label>
				<input id="mom-search" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( mom_t( 'Sueño, rabietas, lactancia, vuelta al trabajo…', 'Sleep, tantrums, feeding, returning to work…' ) ); ?>">
				<button type="submit"><?php echo esc_html( mom_t( 'Buscar', 'Search' ) ); ?></button>
			</form>
			<div class="hero-shortcuts">
				<span><?php echo esc_html( mom_t( 'Empieza por', 'Start with' ) ); ?></span>
				<a href="#temas"><?php echo esc_html( mom_t( 'un tema', 'a topic' ) ); ?></a>
				<a href="#etapas"><?php echo esc_html( mom_t( 'una etapa', 'a stage' ) ); ?></a>
				<a href="#para-ti"><?php echo esc_html( mom_t( 'tu situación', 'your situation' ) ); ?></a>
			</div>
		</div>
		<div class="hero-art">
			<?php echo mom_hero_art_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<div class="hero-note">
				<strong><?php echo esc_html( mom_t( 'Menos ruido. Más contexto para decidir.', 'Less noise. More context for your decisions.' ) ); ?></strong>
				<span><?php echo esc_html( mom_t( 'Contenido pensado para la vida real, no para la familia perfecta.', 'Content designed for real life, not the perfect family.' ) ); ?></span>
			</div>
		</div>
	</div>
</section>

<section class="section" id="temas">
	<div class="container">
		<header class="section-intro">
			<div><span class="section-label"><?php echo esc_html( mom_t( 'Temas', 'Topics' ) ); ?></span><h2><?php echo esc_html( mom_t( 'Encuentra justo lo que te preocupa hoy', 'Find what is on your mind today' ) ); ?></h2></div>
			<p><?php echo esc_html( mom_t( 'La misma familia cambia de pregunta muchas veces al día. Explora por el tema que tienes delante ahora mismo.', 'Family questions change many times in a single day. Explore the topic that is in front of you right now.' ) ); ?></p>
		</header>
		<div class="topic-grid">
			<?php foreach ( $topics as $topic ) : ?>
				<a class="topic-card" style="--topic-accent:<?php echo esc_attr( mom_topic_accent( $topic['id'] ) ); ?>" href="<?php echo esc_url( mom_term_url( 'topic', $topic['id'], $topic['label'] ) ); ?>">
					<span class="topic-art" aria-hidden="true"><?php echo mom_topic_art_svg( $topic['id'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="topic-copy"><strong><?php echo esc_html( $topic['label'] ); ?></strong><small><?php echo esc_html( mom_topic_description( $topic['id'] ) ); ?></small></span>
					<span class="topic-arrow" aria-hidden="true">↗</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section stage-section" id="etapas">
	<div class="container">
		<header class="compact-intro">
			<span class="section-label"><?php echo esc_html( mom_t( 'Por etapa', 'By stage' ) ); ?></span>
			<h2><?php echo esc_html( mom_t( 'Porque no necesitas lo mismo en cada momento', 'Because every stage asks for something different' ) ); ?></h2>
			<p><?php echo esc_html( mom_t( 'Del embarazo a la edad escolar, entra directamente en la etapa que estás viviendo.', 'From pregnancy to school age, jump directly into the stage you are living now.' ) ); ?></p>
		</header>
		<div class="stage-grid">
			<?php foreach ( $stages as $index => $stage ) : ?>
				<a class="stage-card" href="<?php echo esc_url( mom_term_url( 'stage', $stage['id'], $stage['label'] ) ); ?>">
					<span class="stage-number"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<strong><?php echo esc_html( $stage['label'] ); ?></strong>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section" id="para-ti">
	<div class="container">
		<header class="section-intro">
			<div><span class="section-label"><?php echo esc_html( mom_t( 'Para quién', 'For whom' ) ); ?></span><h2><?php echo esc_html( mom_t( 'La crianza no la vive una sola persona', 'Parenting is never lived by just one person' ) ); ?></h2></div>
			<p><?php echo esc_html( mom_t( 'También puedes filtrar la lectura según quién necesita la respuesta: madres, padres, pareja o familia que acompaña.', 'You can also filter by who needs the answer: moms, dads, partners or the wider family supporting them.' ) ); ?></p>
		</header>
		<div class="audience-grid">
			<?php $symbols = array( '○', '◡', '◇', '∞', '⌂' ); foreach ( $audiences as $index => $audience ) : ?>
				<a class="audience-card" href="<?php echo esc_url( mom_term_url( 'audience', $audience['id'], $audience['label'] ) ); ?>">
					<span class="audience-symbol" aria-hidden="true"><?php echo esc_html( $symbols[ $index ] ?? '○' ); ?></span>
					<span><strong><?php echo esc_html( $audience['label'] ); ?></strong><small><?php echo esc_html( mom_t( 'Ver artículos seleccionados para esta perspectiva.', 'See articles selected for this perspective.' ) ); ?></small></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section" id="ultimos-articulos">
	<div class="container">
		<header class="latest-head">
			<div><span class="section-label"><?php echo esc_html( mom_t( 'Nuevas lecturas', 'New reads' ) ); ?></span><h2><?php echo esc_html( mom_t( 'Últimos artículos', 'Latest articles' ) ); ?></h2></div>
		</header>
		<div class="card-grid">
			<?php
			$latest = new WP_Query(
				array(
					'post_type'           => 'post',
					'post_status'         => 'publish',
					'posts_per_page'      => 6,
					'ignore_sticky_posts' => false,
				)
			);
			if ( $latest->have_posts() ) :
				while ( $latest->have_posts() ) : $latest->the_post();
					mom_render_post_card( get_the_ID() );
				endwhile;
				wp_reset_postdata();
			endif;
			?>
		</div>
	</div>
</section>

<section class="home-manifesto">
	<div class="container">
		<div class="manifesto-card">
			<div><span class="section-label"><?php echo esc_html( mom_t( 'Nuestra idea', 'Our approach' ) ); ?></span><h2><?php echo esc_html( mom_t( 'Información que ayuda, no que añade presión.', 'Information that helps instead of adding pressure.' ) ); ?></h2></div>
			<p><?php echo esc_html( mom_t( 'Queremos que cada artículo responda una pregunta real, explique los matices que importan y te deje con algo útil que puedas aplicar o decidir. Sin convertir la maternidad ni la crianza en una lista infinita de cosas que hacer bien.', 'Every article should answer a real question, explain the nuances that matter and leave you with something useful you can apply or decide. Without turning motherhood or parenting into an endless checklist of things to get right.' ) ); ?></p>
		</div>
	</div>
</section>

<?php get_footer(); ?>
