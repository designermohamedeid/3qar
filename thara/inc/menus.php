<?php
/**
 * Navigation menus: map WordPress menu markup to the template classes.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the menu args belong to one of the theme's styled menus.
 *
 * @param stdClass $args wp_nav_menu() args.
 * @return bool
 */
function thara_is_theme_menu( $args ) {
	return isset( $args->thara_style ) && $args->thara_style;
}

/**
 * Parent items get the "drop" class used by the dropdown animation.
 *
 * @param array    $classes Classes.
 * @param WP_Post  $item    Menu item.
 * @param stdClass $args    Args.
 * @return array
 */
function thara_nav_menu_css_class( $classes, $item, $args ) {
	if ( thara_is_theme_menu( $args ) && in_array( 'menu-item-has-children', $classes, true ) ) {
		$classes[] = 'drop';
	}
	if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-ancestor', $classes, true ) ) {
		$classes[] = 'active';
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'thara_nav_menu_css_class', 10, 3 );

/**
 * Parent links get the chevron icon classes.
 *
 * @param array    $atts  Link attributes.
 * @param WP_Post  $item  Menu item.
 * @param stdClass $args  Args.
 * @return array
 */
function thara_nav_menu_link_attributes( $atts, $item, $args ) {
	if ( thara_is_theme_menu( $args ) && in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		$atts['class']         = trim( ( isset( $atts['class'] ) ? $atts['class'] : '' ) . ' lnr lnr-chevron-down' );
		$atts['aria-haspopup'] = 'true';
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'thara_nav_menu_link_attributes', 10, 3 );

/**
 * Sub menus use the template "dropDown" list.
 *
 * @param array    $classes Classes.
 * @param stdClass $args    Args.
 * @return array
 */
function thara_nav_menu_submenu_css_class( $classes, $args ) {
	if ( thara_is_theme_menu( $args ) ) {
		$classes[] = 'res';
		$classes[] = 'dropDown';
	}
	return $classes;
}
add_filter( 'nav_menu_submenu_css_class', 'thara_nav_menu_submenu_css_class', 10, 2 );

/**
 * Print the primary menu.
 *
 * @param string $context "header" or "side".
 */
function thara_primary_menu( $context = 'header' ) {
	$search = '';
	if ( 'header' === $context ) {
		$search = '<li class="menu-search"><span class="lnr lnr-magnifier search-toggle" role="button" tabindex="0" aria-label="' . esc_attr__( 'Open search', 'thara' ) . '"></span></li>';
	}

	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'side' === $context ? 'res list' : 'res',
				'menu_id'        => 'side' === $context ? 'side-menu' : 'primary-menu',
				'depth'          => 2,
				'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s' . $search . '</ul>',
				'thara_style'    => true,
				'fallback_cb'    => false,
			)
		);
		return;
	}

	// Fallback: list published pages.
	$pages = wp_list_pages(
		array(
			'title_li' => '',
			'depth'    => 1,
			'echo'     => false,
		)
	);
	printf(
		'<ul class="%1$s"><li><a href="%2$s">%3$s</a></li>%4$s%5$s</ul>',
		'side' === $context ? 'res list' : 'res',
		esc_url( home_url( '/' ) ),
		esc_html__( 'Home', 'thara' ),
		$pages, // phpcs:ignore WordPress.Security.EscapeOutput -- core markup.
		$search // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
	);
}

/**
 * Print the footer quick links menu.
 */
function thara_footer_menu() {
	if ( has_nav_menu( 'footer' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'footer',
				'container'      => false,
				'menu_class'     => 'res',
				'depth'          => 1,
				'fallback_cb'    => false,
			)
		);
		return;
	}
	echo '<ul class="res">';
	wp_list_pages(
		array(
			'title_li' => '',
			'depth'    => 1,
			'number'   => 8,
		)
	);
	echo '</ul>';
}
