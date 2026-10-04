<?php
/**
 * Header.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;
$dara_overlay = dara_header_is_overlay();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="<?php echo esc_attr( dara_mod( 'ink_color' ) ); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'dara' ); ?></a>

<header class="site-header<?php echo $dara_overlay ? ' site-header--overlay' : ''; ?>" id="site-header">
	<div class="container site-header__inner">
		<?php dara_logo( $dara_overlay ); ?>

		<nav class="main-nav" aria-label="<?php esc_attr_e( 'Main menu', 'dara' ); ?>">
			<?php dara_primary_menu(); ?>
		</nav>

		<div class="site-header__actions">
			<?php foreach ( dara_languages() as $dara_lang ) : ?>
				<a class="header-link" href="<?php echo esc_url( $dara_lang['url'] ); ?>" hreflang="<?php echo esc_attr( strtolower( $dara_lang['label'] ) ); ?>" aria-label="<?php echo esc_attr( $dara_lang['name'] ); ?>"><?php dara_the_icon( 'globe', 18 ); ?><span><?php echo esc_html( $dara_lang['label'] ); ?></span></a>
			<?php endforeach; ?>
			<?php
			$dara_fav_url = dara_template_url( 'page-templates/favorites.php' );
			if ( $dara_fav_url ) :
				?>
				<a class="icon-btn fav-link" href="<?php echo esc_url( $dara_fav_url ); ?>" aria-label="<?php esc_attr_e( 'Favorites', 'dara' ); ?>"><?php dara_the_icon( 'heart', 20 ); ?><span class="fav-count" hidden></span></a>
			<?php endif; ?>
			<?php if ( dara_mod( 'header_cta_text' ) ) : ?>
				<a class="btn btn--primary btn--sm header-cta" href="<?php echo esc_url( dara_list_property_url() ); ?>"><?php dara_the_icon( 'plus', 18 ); ?><?php echo esc_html( dara_mod( 'header_cta_text' ) ); ?></a>
			<?php endif; ?>
			<button type="button" class="icon-btn menu-toggle" aria-controls="drawer" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open menu', 'dara' ); ?>"><?php dara_the_icon( 'menu', 22 ); ?></button>
		</div>
	</div>
</header>

<div class="drawer" id="drawer" hidden>
	<div class="drawer__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'dara' ); ?>">
		<div class="drawer__head">
			<?php dara_logo(); ?>
			<button type="button" class="icon-btn drawer__close" aria-label="<?php esc_attr_e( 'Close menu', 'dara' ); ?>"><?php dara_the_icon( 'close', 22 ); ?></button>
		</div>
		<nav aria-label="<?php esc_attr_e( 'Mobile menu', 'dara' ); ?>"><?php dara_primary_menu( 'drawer-menu' ); ?></nav>
		<div class="drawer__foot">
			<?php if ( dara_mod( 'header_cta_text' ) ) : ?>
				<a class="btn btn--primary btn--block" href="<?php echo esc_url( dara_list_property_url() ); ?>"><?php echo esc_html( dara_mod( 'header_cta_text' ) ); ?></a>
			<?php endif; ?>
			<?php foreach ( dara_languages() as $dara_lang ) : ?>
				<a class="btn btn--outline btn--block" href="<?php echo esc_url( $dara_lang['url'] ); ?>"><?php echo esc_html( $dara_lang['name'] ); ?></a>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<main id="content" class="site-main">
