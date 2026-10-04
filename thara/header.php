<?php
/**
 * Site header.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
	<noscript><style>.preloader{display:none!important}</style></noscript>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'thara' ); ?></a>

<?php if ( thara_option( 'preloader' ) && ! is_customize_preview() ) : ?>
	<div class="preloader" aria-hidden="true">
		<div class="cssload-thecube">
			<div class="cssload-cube cssload-c1"></div>
			<div class="cssload-cube cssload-c2"></div>
			<div class="cssload-cube cssload-c4"></div>
			<div class="cssload-cube cssload-c3"></div>
		</div>
	</div>
<?php endif; ?>

<!-- Mobile side navigation -->
<div class="navover"></div>
<div class="sideNav" id="side-nav" aria-hidden="true">
	<div class="opt">
		<div class="lang"><?php thara_language_switcher(); ?></div>
		<button type="button" class="close1" aria-label="<?php esc_attr_e( 'Close menu', 'thara' ); ?>"><span class="lnr lnr-cross" aria-hidden="true"></span></button>
	</div>
	<nav class="links" aria-label="<?php esc_attr_e( 'Mobile menu', 'thara' ); ?>">
		<?php thara_primary_menu( 'side' ); ?>
	</nav>
	<div class="logo">
		<?php
		$thara_side_logo = thara_option( 'side_logo' ) ? thara_option( 'side_logo' ) : thara_asset( 'images/logo-side.png' );
		printf( '<img class="img-responsive" src="%1$s" alt="%2$s">', esc_url( $thara_side_logo ), esc_attr( get_bloginfo( 'name' ) ) );
		?>
	</div>
</div>

<!-- Search overlay -->
<div class="searchh" id="search-overlay" aria-hidden="true">
	<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<span class="lnr lnr-cross search-close" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Close search', 'thara' ); ?>"></span>
		<label class="screen-reader-text" for="overlay-search"><?php esc_html_e( 'Search for:', 'thara' ); ?></label>
		<input type="search" id="overlay-search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search', 'thara' ); ?>">
		<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'thara' ); ?>"><span class="lnr lnr-magnifier" aria-hidden="true"></span></button>
	</form>
</div>

<?php $thara_is_home = is_front_page(); ?>
<header class="site-header <?php echo $thara_is_home ? 'home-header' : 'inner-header'; ?>">
	<?php
	$thara_header_img = $thara_is_home ? thara_option( 'hero_image' ) : thara_inner_header_image();
	if ( $thara_header_img ) {
		printf( '<img class="header-bg" src="%s" alt="" aria-hidden="true">', esc_url( $thara_header_img ) );
	}
	?>
	<nav class="main-nav" aria-label="<?php esc_attr_e( 'Main menu', 'thara' ); ?>">
		<div class="container">
			<?php thara_language_switcher( 'lang' ); ?>
			<div class="content">
				<div class="logo">
					<?php thara_site_logo(); ?>
					<button type="button" class="menuTriger" aria-controls="side-nav" aria-label="<?php esc_attr_e( 'Open menu', 'thara' ); ?>"><span class="lnr lnr-menu" aria-hidden="true"></span></button>
					<button type="button" class="searchTriger" aria-controls="search-overlay" aria-label="<?php esc_attr_e( 'Open search', 'thara' ); ?>"><span class="lnr lnr-magnifier search-toggle" aria-hidden="true"></span></button>
				</div>
				<div class="links" id="links">
					<?php thara_primary_menu( 'header' ); ?>
					<div class="dropDown-bg"></div>
				</div>
			</div>
		</div>
	</nav>

	<?php
	if ( $thara_is_home ) {
		get_template_part( 'template-parts/header/hero' );
	} else {
		get_template_part( 'template-parts/header/page-title' );
	}
	?>
</header>

<main id="main" class="site-main">
