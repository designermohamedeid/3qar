<?php
/**
 * Front page.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Home page built with Dara blocks: render the page content full width.
if ( 'page' === get_option( 'show_on_front' ) && dara_has_dara_blocks( get_queried_object_id() ) ) {
	while ( have_posts() ) {
		the_post();
		echo '<div class="block-page">';
		the_content();
		echo '</div>';
	}
	get_footer();
	return;
}

get_template_part( 'template-parts/home/hero' );

/**
 * Filter the home sections (slug => Customizer toggle). Reorder or add your own.
 */
$dara_sections = apply_filters(
	'dara_home_sections',
	array(
		'featured' => 'show_featured',
		'types'    => 'show_types',
		'projects' => 'show_projects',
		'services' => 'show_services',
		'why'      => 'show_why',
		'blog'     => 'show_blog',
		'cta'      => 'show_cta',
	)
);
$dara_needs_core = array( 'featured', 'types', 'projects' );

foreach ( $dara_sections as $dara_section => $dara_toggle ) {
	if ( ! dara_mod( $dara_toggle ) || ( in_array( $dara_section, $dara_needs_core, true ) && ! dara_has_core() ) ) {
		continue;
	}
	get_template_part( 'template-parts/home/' . $dara_section );
}

if ( 'page' === get_option( 'show_on_front' ) ) {
	while ( have_posts() ) {
		the_post();
		if ( '' !== trim( get_the_content() ) ) {
			echo '<section class="section"><div class="container container--narrow entry-content">';
			the_content();
			echo '</div></section>';
		}
	}
}

get_footer();
