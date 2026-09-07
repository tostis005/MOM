<?php
/**
 * MOM theme functions.
 *
 * @package MOM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mom_theme_setup() {
	load_theme_textdomain( 'mom', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'automatic-feed-links' );
	add_image_size( 'mom-card', 760, 500, true );
	add_image_size( 'mom-hero', 1400, 860, true );

	register_nav_menus(
		array(
			'primary' => __( 'Menú principal', 'mom' ),
			'footer'  => __( 'Menú del pie', 'mom' ),
		)
	);
}
add_action( 'after_setup_theme', 'mom_theme_setup' );

$mom_i18n_routing = get_template_directory() . '/inc/i18n-routing.php';
if ( file_exists( $mom_i18n_routing ) ) {
	require_once $mom_i18n_routing;
}

function mom_enqueue_assets() {
	$version = wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'mom-style', get_stylesheet_uri(), array(), $version );
	wp_enqueue_style( 'mom-navigation-overlays', get_template_directory_uri() . '/assets/css/navigation-overlays.css', array( 'mom-style' ), $version );
	wp_enqueue_script( 'mom-navigation-overlays', get_template_directory_uri() . '/assets/js/navigation-overlays.js', array(), $version, true );
}
add_action( 'wp_enqueue_scripts', 'mom_enqueue_assets' );

function mom_is_english() {
	if ( function_exists( 'mom_current_language' ) ) {
		return 'en' === mom_current_language();
	}
	if ( function_exists( 'content_platform_current_language' ) ) {
		return 'en' === content_platform_current_language();
	}
	if ( function_exists( 'pll_current_language' ) ) {
		return 'en' === pll_current_language( 'slug' );
	}
	return 0 === strpos( strtolower( (string) get_locale() ), 'en' );
}

function mom_t( $es, $en ) {
	return mom_is_english() ? $en : $es;
}

function mom_language_home_url( $language = '' ) {
	if ( function_exists( 'mom_i18n_home_url' ) ) {
		return mom_i18n_home_url( $language );
	}
	if ( function_exists( 'pll_home_url' ) ) {
		$language = in_array( $language, array( 'es', 'en' ), true ) ? $language : ( mom_is_english() ? 'en' : 'es' );
		return pll_home_url( $language );
	}
	return home_url( '/' );
}

function mom_fallback_terms( $dimension ) {
	$terms = array(
		'topic' => array(
			array( 'id' => 'sleep', 'label' => array( 'es' => 'Sueño', 'en' => 'Sleep' ) ),
			array( 'id' => 'parenting-behavior', 'label' => array( 'es' => 'Crianza y comportamiento', 'en' => 'Parenting & behavior' ) ),
			array( 'id' => 'child-feeding', 'label' => array( 'es' => 'Alimentación infantil', 'en' => 'Child feeding' ) ),
			array( 'id' => 'potty-hygiene-autonomy', 'label' => array( 'es' => 'Pañal, higiene y autonomía', 'en' => 'Potty, hygiene & autonomy' ) ),
			array( 'id' => 'routines-family-life', 'label' => array( 'es' => 'Rutinas y vida familiar', 'en' => 'Routines & family life' ) ),
			array( 'id' => 'play-learning-autonomy', 'label' => array( 'es' => 'Juego, aprendizaje y autonomía', 'en' => 'Play, learning & autonomy' ) ),
			array( 'id' => 'childcare-school-social', 'label' => array( 'es' => 'Guardería, colegio y vida social', 'en' => 'Childcare, school & social life' ) ),
			array( 'id' => 'pregnancy-preparation', 'label' => array( 'es' => 'Embarazo y preparación', 'en' => 'Pregnancy & preparation' ) ),
			array( 'id' => 'postpartum-newborn', 'label' => array( 'es' => 'Posparto y recién nacido', 'en' => 'Postpartum & newborn' ) ),
			array( 'id' => 'breastfeeding-baby-feeding', 'label' => array( 'es' => 'Lactancia y alimentación del bebé', 'en' => 'Breastfeeding & baby feeding' ) ),
			array( 'id' => 'couple-coparenting', 'label' => array( 'es' => 'Pareja y coparentalidad', 'en' => 'Couple & coparenting' ) ),
			array( 'id' => 'motherhood-identity', 'label' => array( 'es' => 'Maternidad, identidad y bienestar', 'en' => 'Motherhood, identity & wellbeing' ) ),
			array( 'id' => 'family-siblings-boundaries', 'label' => array( 'es' => 'Familia, hermanos y límites', 'en' => 'Family, siblings & boundaries' ) ),
			array( 'id' => 'work-balance-life', 'label' => array( 'es' => 'Trabajo, conciliación y vida propia', 'en' => 'Work, balance & own life' ) ),
			array( 'id' => 'travel-outings-celebrations', 'label' => array( 'es' => 'Viajes, salidas y celebraciones', 'en' => 'Travel, outings & celebrations' ) ),
		),
		'stage' => array(
			array( 'id' => 'pregnancy', 'label' => array( 'es' => 'Embarazo', 'en' => 'Pregnancy' ) ),
			array( 'id' => 'preparing-for-baby', 'label' => array( 'es' => 'Preparación para el bebé', 'en' => 'Preparing for baby' ) ),
			array( 'id' => 'postpartum', 'label' => array( 'es' => 'Posparto', 'en' => 'Postpartum' ) ),
			array( 'id' => 'newborn', 'label' => array( 'es' => 'Recién nacido', 'en' => 'Newborn' ) ),
			array( 'id' => 'baby', 'label' => array( 'es' => 'Bebé', 'en' => 'Baby' ) ),
			array( 'id' => 'toddler', 'label' => array( 'es' => 'Toddler', 'en' => 'Toddler' ) ),
			array( 'id' => 'preschool', 'label' => array( 'es' => 'Preschool / infantil', 'en' => 'Preschool' ) ),
			array( 'id' => 'school-age', 'label' => array( 'es' => 'Edad escolar', 'en' => 'School age' ) ),
			array( 'id' => 'second-child-siblings', 'label' => array( 'es' => 'Segundo hijo y hermanos', 'en' => 'Second child & siblings' ) ),
			array( 'id' => 'parenthood-general', 'label' => array( 'es' => 'Maternidad y paternidad', 'en' => 'Parenthood' ) ),
		),
		'audience' => array(
			array( 'id' => 'parents', 'label' => array( 'es' => 'Para madres y padres', 'en' => 'For parents' ) ),
			array( 'id' => 'mothers', 'label' => array( 'es' => 'Para mamás', 'en' => 'For moms' ) ),
			array( 'id' => 'fathers', 'label' => array( 'es' => 'Para papás', 'en' => 'For dads' ) ),
			array( 'id' => 'couples', 'label' => array( 'es' => 'Para la pareja', 'en' => 'For couples' ) ),
			array( 'id' => 'family-caregivers', 'label' => array( 'es' => 'Familia y cuidadores', 'en' => 'Family & caregivers' ) ),
		),
	);

	$language = mom_is_english() ? 'en' : 'es';
	$result   = array();
	foreach ( isset( $terms[ $dimension ] ) ? $terms[ $dimension ] : array() as $term ) {
		$result[] = array(
			'id'    => $term['id'],
			'label' => $term['label'][ $language ],
			'slug'  => $term['id'],
		);
	}
	return $result;
}

function mom_home_terms( $dimension ) {
	if ( function_exists( 'content_platform_home_terms' ) ) {
		$terms = content_platform_home_terms( $dimension, mom_is_english() ? 'en' : 'es' );
		if ( ! empty( $terms ) ) {
			return $terms;
		}
	}
	return mom_fallback_terms( $dimension );
}

function mom_taxonomy_name( $dimension ) {
	if ( function_exists( 'content_platform_taxonomy_name' ) ) {
		$name = content_platform_taxonomy_name( $dimension );
		if ( $name ) {
			return $name;
		}
	}
	$map = array(
		'topic'        => 'content_topic',
		'stage'        => 'content_stage',
		'audience'     => 'content_audience',
		'article_type' => 'content_article_type',
	);
	return isset( $map[ $dimension ] ) ? $map[ $dimension ] : '';
}

function mom_term_url( $dimension, $term_id, $label = '', $language = '' ) {
	if ( function_exists( 'mom_i18n_term_url' ) ) {
		return mom_i18n_term_url( $dimension, $term_id, $language );
	}
	$taxonomy = mom_taxonomy_name( $dimension );
	if ( $taxonomy && taxonomy_exists( $taxonomy ) ) {
		$term = get_term_by( 'slug', sanitize_title( $term_id ), $taxonomy );
		if ( $term instanceof WP_Term ) {
			$url = get_term_link( $term );
			if ( ! is_wp_error( $url ) ) {
				return $url;
			}
		}
	}
	return mom_language_home_url( $language ) . '?s=' . rawurlencode( $label ? $label : str_replace( '-', ' ', $term_id ) );
}

function mom_topic_description( $id ) {
	$es = array(
		'sleep' => 'Rutinas, despertares, siestas y cambios de sueño sin fórmulas mágicas.',
		'parenting-behavior' => 'Límites, rabietas, cooperación y emociones para el día a día.',
		'child-feeding' => 'Picky eating, comidas, alimentos nuevos y una relación tranquila con la mesa.',
		'potty-hygiene-autonomy' => 'Pañal, baño, vestirse y pequeñas responsabilidades con más autonomía.',
		'routines-family-life' => 'Mañanas, tardes, casa y organización para que la familia respire mejor.',
		'play-learning-autonomy' => 'Juego, aprendizaje y habilidades cotidianas que crecen con ellos.',
		'childcare-school-social' => 'Guardería, colegio, amistades, adaptación y vida fuera de casa.',
		'pregnancy-preparation' => 'Embarazo, decisiones prácticas y preparación realista para la llegada del bebé.',
		'postpartum-newborn' => 'Las primeras semanas: recuperación, vínculo, cuidados y nueva rutina.',
		'breastfeeding-baby-feeding' => 'Lactancia, biberón, tomas y alimentación del bebé con contexto útil.',
		'couple-coparenting' => 'Comunicación, reparto de carga y cómo seguir siendo equipo al criar.',
		'motherhood-identity' => 'Identidad, bienestar, culpa, descanso y espacio propio en la maternidad.',
		'family-siblings-boundaries' => 'Hermanos, abuelos, visitas y límites que protegen la convivencia.',
		'work-balance-life' => 'Vuelta al trabajo, conciliación, tiempo propio y logística familiar.',
		'travel-outings-celebrations' => 'Viajes, planes, restaurantes y celebraciones con niños sin complicarlo todo.',
	);
	$en = array(
		'sleep' => 'Routines, wakings, naps and sleep transitions without magic formulas.',
		'parenting-behavior' => 'Boundaries, tantrums, cooperation and emotions for everyday family life.',
		'child-feeding' => 'Picky eating, meals, new foods and a calmer relationship with the table.',
		'potty-hygiene-autonomy' => 'Potty learning, baths, getting dressed and growing independence.',
		'routines-family-life' => 'Mornings, evenings, home and organization that make family life lighter.',
		'play-learning-autonomy' => 'Play, learning and everyday skills that grow alongside children.',
		'childcare-school-social' => 'Childcare, school, friendships, transitions and life beyond home.',
		'pregnancy-preparation' => 'Pregnancy, practical decisions and realistic preparation for a new baby.',
		'postpartum-newborn' => 'The first weeks: recovery, bonding, newborn care and a new rhythm.',
		'breastfeeding-baby-feeding' => 'Breastfeeding, bottles, feeds and baby nutrition with useful context.',
		'couple-coparenting' => 'Communication, shared load and staying a team while raising children.',
		'motherhood-identity' => 'Identity, wellbeing, guilt, rest and making room for yourself in motherhood.',
		'family-siblings-boundaries' => 'Siblings, grandparents, visitors and boundaries that protect family life.',
		'work-balance-life' => 'Returning to work, balance, personal time and family logistics.',
		'travel-outings-celebrations' => 'Trips, outings, restaurants and celebrations with children, made simpler.',
	);
	$source = mom_is_english() ? $en : $es;
	return isset( $source[ $id ] ) ? $source[ $id ] : '';
}

function mom_topic_accent( $id ) {
	$colors = array(
		'sleep' => '#7f7799', 'parenting-behavior' => '#9a6262', 'child-feeding' => '#9a7448',
		'potty-hygiene-autonomy' => '#668b91', 'routines-family-life' => '#8a7767', 'play-learning-autonomy' => '#aa7f57',
		'childcare-school-social' => '#6f8377', 'pregnancy-preparation' => '#a96d7a', 'postpartum-newborn' => '#9d6f69',
		'breastfeeding-baby-feeding' => '#8f7664', 'couple-coparenting' => '#93677f', 'motherhood-identity' => '#7a736d',
		'family-siblings-boundaries' => '#71816d', 'work-balance-life' => '#6f7286', 'travel-outings-celebrations' => '#a17658',
	);
	return isset( $colors[ $id ] ) ? $colors[ $id ] : '#9b5f60';
}

function mom_primary_topic_id( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	if ( function_exists( 'content_platform_primary_term_id' ) ) {
		$id = content_platform_primary_term_id( $post_id, 'topic' );
		if ( $id ) {
			return $id;
		}
	}
	$id = (string) get_post_meta( $post_id, '_content_primary_topic', true );
	if ( $id ) {
		return $id;
	}
	$taxonomy = mom_taxonomy_name( 'topic' );
	$terms    = $taxonomy ? get_the_terms( $post_id, $taxonomy ) : false;
	if ( is_array( $terms ) && ! empty( $terms ) ) {
		$stored = get_term_meta( $terms[0]->term_id, '_content_term_id', true );
		return $stored ? (string) $stored : (string) $terms[0]->slug;
	}
	return 'motherhood-identity';
}

function mom_topic_label( $id ) {
	if ( function_exists( 'content_platform_term_label' ) ) {
		return content_platform_term_label( 'topic', $id, mom_is_english() ? 'en' : 'es' );
	}
	foreach ( mom_fallback_terms( 'topic' ) as $term ) {
		if ( $term['id'] === $id ) {
			return $term['label'];
		}
	}
	return ucfirst( str_replace( '-', ' ', $id ) );
}

function mom_reading_time( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	$words   = str_word_count( wp_strip_all_tags( $content ) );
	$minutes = max( 1, (int) ceil( $words / 210 ) );
	return sprintf( mom_is_english() ? '%d min read' : '%d min de lectura', $minutes );
}

function mom_render_post_card( $post_id ) {
	$post_id  = (int) $post_id;
	$topic_id = mom_primary_topic_id( $post_id );
	?>
	<article class="post-card">
		<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<div class="card-media">
				<?php if ( has_post_thumbnail( $post_id ) ) : ?>
					<?php echo get_the_post_thumbnail( $post_id, 'mom-card', array( 'loading' => 'lazy' ) ); ?>
				<?php else : ?>
					<div class="card-fallback" style="color:<?php echo esc_attr( mom_topic_accent( $topic_id ) ); ?>"><?php echo mom_topic_art_svg( $topic_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endif; ?>
			</div>
			<div class="card-body">
				<span class="card-kicker"><?php echo esc_html( mom_topic_label( $topic_id ) ); ?></span>
				<h3 class="card-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				<p class="card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 22 ) ); ?></p>
				<div class="card-meta"><span><?php echo esc_html( mom_reading_time( $post_id ) ); ?></span></div>
			</div>
		</a>
	</article>
	<?php
}

function mom_body_classes( $classes ) {
	$classes[] = 'mom-theme';
	return $classes;
}
add_filter( 'body_class', 'mom_body_classes' );

$mom_visual_taxonomy = get_template_directory() . '/inc/visual-taxonomy.php';
if ( file_exists( $mom_visual_taxonomy ) ) {
	require_once $mom_visual_taxonomy;
}

$mom_discovery_routing = get_template_directory() . '/inc/discovery-routing.php';
if ( file_exists( $mom_discovery_routing ) ) {
	require_once $mom_discovery_routing;
}
