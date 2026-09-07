<?php
/**
 * Lightweight editorial artwork for MOM taxonomy cards.
 *
 * @package MOM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mom_topic_art_svg( $id ) {
	/* Prefer the canonical HQ category photo everywhere a post/card needs a fallback. */
	$photos = array(
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

	if ( ! empty( $photos[ $id ] ) ) {
		$file = trailingslashit( get_template_directory() ) . 'assets/images/hq/' . $photos[ $id ];
		if ( file_exists( $file ) ) {
			$url = trailingslashit( get_template_directory_uri() ) . 'assets/images/hq/' . $photos[ $id ];
			return '<img class="mom-topic-fallback-image" src="' . esc_url( $url ) . '" width="1448" height="1086" alt="" loading="lazy" decoding="async" style="width:100%;height:100%;object-fit:cover;object-position:center;display:block">';
		}
	}

	$paths = array(
		'sleep' => '<path d="M43 12c-4 2-7 7-7 12 0 10 8 18 18 18 2 0 4 0 6-1-4 8-12 13-22 13-13 0-24-10-24-23S24 8 37 8c2 0 4 0 6 1Z"/><path d="m17 15 2 4 4 2-4 2-2 4-2-4-4-2 4-2 2-4Zm36-2 1.5 3 3.5 1.5-3.5 1.5-1.5 3-1.5-3-3.5-1.5 3.5-1.5 1.5-3Z"/>',
		'parenting-behavior' => '<circle cx="22" cy="20" r="7"/><circle cx="44" cy="25" r="5"/><path d="M9 53c1-13 6-21 14-21 9 0 13 7 14 21M35 53c1-10 4-16 10-16 6 0 9 6 10 16"/><path d="M29 35c5 3 9 6 12 10M31 43c5-1 8-4 10-8"/>',
		'child-feeding' => '<path d="M10 35h38c-2 13-8 20-19 20S12 48 10 35Z"/><path d="M8 35h42M51 11c6 9 6 18-1 27M48 12h7"/><path d="M18 28c2-7 8-11 14-11s12 4 14 11"/><path d="M30 17c-2-5 0-9 4-12M34 11c5-3 9-2 12 1-3 4-7 5-12 4"/>',
		'potty-hygiene-autonomy' => '<path d="M10 31h44v8c0 9-7 16-16 16H26c-9 0-16-7-16-16v-8Z"/><path d="M15 31V18c0-5 4-9 9-9h5"/><path d="M26 12c5 6 7 9 7 13a7 7 0 0 1-14 0c0-4 2-7 7-13Z"/><path d="M22 55v4M42 55v4"/>',
		'routines-family-life' => '<path d="M9 31 32 11l23 20v24H9V31Z"/><circle cx="32" cy="34" r="10"/><path d="M32 28v7l5 3M17 46h7M40 46h7"/>',
		'play-learning-autonomy' => '<rect x="8" y="34" width="19" height="19" rx="2"/><rect x="28" y="22" width="20" height="31" rx="2"/><path d="M15 42h6M18 39v6M33 30h10M33 36h10"/><path d="m52 12 2 5 5 2-5 2-2 5-2-5-5-2 5-2 2-5Z"/>',
		'childcare-school-social' => '<path d="M17 24h30l5 31H12l5-31Z"/><path d="M24 24c0-8 3-12 8-12s8 4 8 12M17 35h30"/><circle cx="25" cy="44" r="3"/><circle cx="39" cy="44" r="3"/><path d="M22 50c3-2 7-2 10 0M32 50c3-2 7-2 10 0"/>',
		'pregnancy-preparation' => '<path d="M33 9c-9 0-15 7-15 17 0 7 3 11 8 15 4 3 6 7 6 13h17c1-9-2-15-8-19-4-3-6-7-6-12 0-5 2-10 6-13-2-1-5-1-8-1Z"/><path d="M32 31c3-5 11-5 14 1 3 7-5 12-9 15-4-3-11-8-8-14 1-1 2-2 3-2Z"/>',
		'postpartum-newborn' => '<path d="M13 26c8-8 18-12 28-8 8 3 13 10 13 19 0 11-9 19-22 19S10 49 10 39c0-5 1-9 3-13Z"/><circle cx="35" cy="31" r="6"/><path d="M30 31c3 2 7 2 10 0M18 42c9-5 19-5 29 0M18 49c9-5 19-5 29 0"/><path d="m15 21 6 5M50 18l-5 6"/>',
		'breastfeeding-baby-feeding' => '<circle cx="24" cy="17" r="6"/><path d="M13 53c0-15 4-27 12-27 8 0 12 7 13 18"/><circle cx="43" cy="36" r="6"/><path d="M32 48c2-9 7-14 13-14 7 0 11 6 12 18M29 34c7 1 12 5 15 12"/><path d="M20 35c4 6 9 9 15 10"/>',
		'couple-coparenting' => '<circle cx="21" cy="20" r="7"/><circle cx="43" cy="20" r="7"/><path d="M8 54c1-14 6-23 14-23 6 0 10 4 12 10M56 54c-1-14-6-23-14-23-6 0-10 4-12 10"/><path d="M25 43c2-6 11-6 14 0 2 5-4 10-7 12-3-2-9-7-7-12Z"/>',
		'motherhood-identity' => '<ellipse cx="32" cy="31" rx="18" ry="23"/><path d="M24 26c2-8 14-8 16 0M25 39c5 3 10 3 15 0"/><path d="M32 8V3M12 18 8 14M52 18l4-4M12 45l-5 4M52 45l5 4"/><path d="M27 53c0 5 2 8 5 8s5-3 5-8"/>',
		'family-siblings-boundaries' => '<path d="M8 31 32 10l24 21v25H8V31Z"/><circle cx="25" cy="34" r="5"/><circle cx="40" cy="37" r="4"/><path d="M16 54c1-9 4-14 10-14 5 0 8 4 9 11M34 54c1-7 3-11 7-11s7 4 8 11"/><path d="M20 18h24"/>',
		'work-balance-life' => '<rect x="8" y="16" width="31" height="24" rx="2"/><path d="M4 45h39M19 40v5M28 40v5"/><circle cx="49" cy="39" r="11"/><path d="M49 33v7l5 3"/><path d="M43 12c2-5 8-7 12-3 4 4 1 9-4 13-5-4-9-7-7-11"/>',
		'travel-outings-celebrations' => '<path d="M11 25h27v28H11zM17 18h15v7M16 53v5M33 53v5"/><path d="M44 17c4-6 10-8 15-5-1 7-6 11-13 12"/><circle cx="50" cy="37" r="10"/><path d="M50 22v-6M50 58v-6M35 37h-6M65 37h-6"/>',
	);
	$path = $paths[ $id ] ?? '<circle cx="32" cy="32" r="21"/><path d="M20 38c6-10 15-15 26-12-3 10-10 16-22 18"/>';
	return '<svg class="mom-topic-svg" viewBox="0 0 64 64" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
}

function mom_hero_art_svg() {
	return '<svg viewBox="0 0 560 560" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"><path d="M184 446c-27-88-10-183 53-248 22-23 54-37 86-37 67 0 113 52 113 119 0 50-19 87-54 119-25 22-36 49-36 82"/><circle cx="280" cy="123" r="54"/><path d="M245 128c20 14 48 13 70-4M217 247c29-14 59-17 88-8 37 12 59 42 62 85M228 291c34 3 60 18 78 45M244 356c22 18 49 27 82 25"/><circle cx="329" cy="294" r="36"/><path d="M294 311c15 9 31 10 47 3M151 193c-42-8-70-37-75-79 43-5 75 15 91 57M407 147c33-35 73-42 119-22-14 45-46 70-96 74M137 346c-40 7-65 30-76 70 40 11 74-2 99-39M417 357c40 3 69 24 85 62-39 16-74 7-105-28"/><path d="M118 110c25 26 38 58 40 96M488 131c-28 23-47 53-55 89M80 412c30-11 61-14 93-7M490 414c-30-10-59-9-89 2"/></svg>';
}
