<?php
/**
 * Styles, scripts, font & image preloading.
 *
 * Front end ships: 1 CSS file + 1 small deferred JS file. Leaflet (maps) is
 * loaded on demand by main.js only when a map scrolls into view.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue front-end assets.
 */
function dara_assets() {
	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	// Core styles + only the bundle the current template needs.
	wp_enqueue_style( 'dara', dara_asset_url( "assets/css/core{$min}.css" ), array(), dara_asset_ver( "assets/css/core{$min}.css" ) );
	foreach ( dara_page_bundles() as $bundle ) {
		wp_enqueue_style( 'dara-' . $bundle, dara_asset_url( "assets/css/{$bundle}{$min}.css" ), array( 'dara' ), dara_asset_ver( "assets/css/{$bundle}{$min}.css" ) );
	}
	wp_add_inline_style( 'dara', dara_css_vars() );

	wp_enqueue_script( 'dara', dara_asset_url( "assets/js/main{$min}.js" ), array(), dara_asset_ver( "assets/js/main{$min}.js" ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_localize_script(
		'dara',
		'daraData',
		array(
			'leafletJs'   => DARA_URI . '/assets/vendor/leaflet/leaflet.js',
			'leafletCss'  => DARA_URI . '/assets/vendor/leaflet/leaflet.css',
			'tiles'       => dara_mod( 'map_tiles' ),
			'attribution' => wp_kses_post( dara_mod( 'map_attribution' ) ),
			'restUrl'     => esc_url_raw( rest_url( 'wp/v2/' ) ),
			'i18n'        => array(
				'saved'   => __( 'Saved to favorites', 'dara' ),
				'removed' => __( 'Removed from favorites', 'dara' ),
				'copied'  => __( 'Link copied', 'dara' ),
				'empty'   => __( 'You have no saved properties yet.', 'dara' ),
				'close'   => __( 'Close', 'dara' ),
				'prev'    => __( 'Previous', 'dara' ),
				'next'    => __( 'Next', 'dara' ),
			),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'dara_assets' );

/**
 * CSS bundles needed by the current view (besides core).
 *
 * @return string[]
 */
function dara_page_bundles() {
	$bundles = array();
	if ( is_front_page() || ( is_singular() && dara_has_dara_blocks( get_queried_object_id() ) ) ) {
		$bundles[] = 'home';
	}
	if ( is_singular() && has_block( 'dara/mortgage', get_queried_object_id() ) ) {
		$bundles[] = 'property';
	}
	if ( is_singular() && has_block( 'dara/agents', get_queried_object_id() ) ) {
		$bundles[] = 'agents';
	}
	if ( is_post_type_archive( 'dara_property' ) || is_tax( array( 'property_type', 'property_city' ) ) ) {
		$bundles[] = 'listing';
	}
	if ( is_singular( array( 'dara_property', 'dara_project' ) ) ) {
		$bundles[] = 'property';
	}
	if ( is_singular( 'dara_project' ) ) {
		$bundles[] = 'project';
	}
	if ( is_post_type_archive( 'dara_agent' ) || is_singular( 'dara_agent' ) ) {
		$bundles[] = 'agents';
	}
	if ( ! is_front_page() && ( is_singular() || is_home() || is_archive() || is_search() || is_404() ) && ! is_post_type_archive( array( 'dara_property', 'dara_project', 'dara_agent' ) ) && ! is_tax( array( 'property_type', 'property_city' ) ) ) {
		$bundles[] = 'content';
	} elseif ( is_front_page() && 'page' === get_option( 'show_on_front' ) && '' !== trim( (string) get_post_field( 'post_content', get_option( 'page_on_front' ) ) ) ) {
		$bundles[] = 'content';
	}
	return apply_filters( 'dara_css_bundles', array_unique( $bundles ) );
}

/**
 * Asset URL (falls back to the unminified file when the build was not run).
 *
 * @param string $path Relative path.
 * @return string
 */
function dara_asset_url( $path ) {
	if ( ! file_exists( DARA_DIR . '/' . $path ) ) {
		$path = str_replace( '.min.', '.', $path );
	}
	return DARA_URI . '/' . $path;
}

/**
 * Cache-busting version from the file time.
 *
 * @param string $path Relative path.
 * @return string
 */
function dara_asset_ver( $path ) {
	$file = DARA_DIR . '/' . $path;
	if ( ! file_exists( $file ) ) {
		$file = str_replace( '.min.', '.', $file );
	}
	return DARA_VERSION . '.' . ( file_exists( $file ) ? filemtime( $file ) : '0' );
}

/**
 * Colors from the Customizer as CSS variables.
 *
 * @return string
 */
function dara_css_vars() {
	$primary = sanitize_hex_color( dara_mod( 'primary_color' ) );
	$ink     = sanitize_hex_color( dara_mod( 'ink_color' ) );
	$css     = '';
	if ( $primary && '#0B6E5F' !== strtoupper( $primary ) ) {
		$css .= '--primary:' . $primary . ';--primary-700:color-mix(in srgb,' . $primary . ' 82%,#000);--primary-50:color-mix(in srgb,' . $primary . ' 10%,#fff);';
	}
	if ( $ink && '#0E1A2B' !== strtoupper( $ink ) ) {
		$css .= '--ink:' . $ink . ';';
	}
	return $css ? ':root{' . $css . '}' : '';
}

/**
 * Preload the fonts used above the fold and the hero image (LCP).
 */
function dara_preload() {
	$subset = is_rtl() ? 'arabic' : 'latin';
	foreach ( array( 400, 700 ) as $weight ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( DARA_URI . "/assets/fonts/plex-arabic-{$weight}-{$subset}.woff2" )
		);
	}

	$hero = 0;
	if ( is_singular() && 'dara/hero' === dara_first_block( get_queried_object_id() ) ) {
		$blocks = parse_blocks( get_post_field( 'post_content', get_queried_object_id() ) );
		foreach ( $blocks as $block ) {
			if ( 'dara/hero' === $block['blockName'] ) {
				$hero = ! empty( $block['attrs']['hero_image'] ) ? (int) $block['attrs']['hero_image'] : (int) dara_mod( 'hero_image' );
				break;
			}
		}
	} elseif ( is_front_page() ) {
		$hero = (int) dara_mod( 'hero_image' );
	} elseif ( is_singular( 'dara_project' ) ) {
		$hero = (int) get_post_thumbnail_id();
	}
	$size  = 'dara-hero';
	$sizes = '100vw';
	if ( ! $hero && is_singular( 'dara_property' ) && function_exists( 'dara_gallery_ids' ) ) {
		$ids   = dara_gallery_ids( get_queried_object_id() );
		$hero  = $ids ? (int) $ids[0] : 0;
		$size  = 'dara-wide';
		$sizes = '(max-width: 900px) 100vw, 620px';
	}
	if ( $hero ) {
		$src    = wp_get_attachment_image_url( $hero, $size );
		$srcset = wp_get_attachment_image_srcset( $hero, $size );
		if ( $src ) {
			printf(
				'<link rel="preload" as="image" href="%1$s"%2$s imagesizes="%3$s" fetchpriority="high">' . "\n",
				esc_url( $src ),
				$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : '',
				esc_attr( $sizes )
			);
		}
	}
}
add_action( 'wp_head', 'dara_preload', 1 );
