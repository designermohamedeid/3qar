<?php
/**
 * Menus: dropdown chevrons + accessible sub-menu toggles for the mobile drawer.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add a toggle button after parent items of the main menu.
 *
 * @param string   $output Item output.
 * @param WP_Post  $item   Item.
 * @param int      $depth  Depth.
 * @param stdClass $args   Args.
 * @return string
 */
function dara_menu_toggle( $output, $item, $depth, $args ) {
	if ( isset( $args->theme_location ) && 'primary' === $args->theme_location && in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		$output .= '<button type="button" class="sub-toggle" aria-expanded="false" aria-label="' . esc_attr(
			/* translators: %s: menu item. */
			sprintf( __( 'Show sub menu of %s', 'dara' ), wp_strip_all_tags( $item->title ) )
		) . '">' . dara_icon( 'chevron', 16 ) . '</button>';
	}
	return $output;
}
add_filter( 'walker_nav_menu_start_el', 'dara_menu_toggle', 10, 4 );

/**
 * Print the main menu.
 *
 * @param string $id Menu element id.
 */
function dara_primary_menu( $id = 'primary-menu' ) {
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_id'        => $id,
				'menu_class'     => 'menu',
				'depth'          => 2,
				'fallback_cb'    => false,
			)
		);
		return;
	}
	echo '<ul id="' . esc_attr( $id ) . '" class="menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'dara' ) . '</a></li>';
	if ( dara_has_core() ) {
		echo '<li><a href="' . esc_url( get_post_type_archive_link( 'dara_property' ) ) . '">' . esc_html__( 'Properties', 'dara' ) . '</a></li>';
		echo '<li><a href="' . esc_url( get_post_type_archive_link( 'dara_project' ) ) . '">' . esc_html__( 'Projects', 'dara' ) . '</a></li>';
		if ( post_type_exists( 'dara_agent' ) && get_post_type_object( 'dara_agent' )->has_archive ) {
			echo '<li><a href="' . esc_url( get_post_type_archive_link( 'dara_agent' ) ) . '">' . esc_html__( 'Agents', 'dara' ) . '</a></li>';
		}
	}
	wp_list_pages( array( 'title_li' => '', 'depth' => 1, 'number' => 4 ) );
	echo '</ul>';
}
