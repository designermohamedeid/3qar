<?php
/**
 * Front page: sections are controlled from Customize > Thara Theme Options.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

get_header();

/**
 * Filter the list (and order) of front page sections.
 *
 * @param array $sections section slug => Customizer toggle key.
 */
$thara_sections = apply_filters(
	'thara_front_page_sections',
	array(
		'about'      => 'about_show',
		'vision'     => 'vision_show',
		'services'   => 'services_show',
		'properties' => 'properties_show',
		'news'       => 'news_show',
	)
);

foreach ( $thara_sections as $thara_section => $thara_toggle ) {
	if ( thara_option( $thara_toggle ) ) {
		get_template_part( 'template-parts/home/' . $thara_section );
	}
}

// Content of the static front page (if any) is shown after the sections.
if ( 'page' === get_option( 'show_on_front' ) ) {
	while ( have_posts() ) {
		the_post();
		if ( '' !== trim( get_the_content() ) ) {
			echo '<section class="page-section front-page-content"><div class="container"><div class="entry-content">';
			the_content();
			echo '</div></div></section>';
		}
	}
}

get_footer();
