<?php
/**
 * Plugin Name: Dara live demo helpers
 * Description: For the public Dara demo only: floating "Buy" and language switch bar, and lead forms that are accepted but not stored or emailed.
 * Author:      Mansoura Host
 * Version:     1.0.0
 *
 * Install: copy to wp-content/mu-plugins/ on each demo site and set the constants in wp-config.php:
 *   define( 'DARA_DEMO_BUY_URL', 'https://themeforest.net/item/...' );
 *   define( 'DARA_DEMO_SWITCH_URL', 'https://dara.mansourahost.com/en' ); // The other language demo.
 *   define( 'DARA_DEMO_SWITCH_LABEL', 'English' );
 *
 * @package DaraDemo
 */

defined( 'ABSPATH' ) || exit;

/**
 * Link to the same page in the other language demo (archives share paths; single items fall back to the home page).
 *
 * @return string
 */
function dara_demo_switch_url() {
	if ( ! defined( 'DARA_DEMO_SWITCH_URL' ) ) {
		return '';
	}
	$base = untrailingslashit( DARA_DEMO_SWITCH_URL );
	if ( is_singular() && ! is_page() ) {
		return $base . '/';
	}
	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '/';
	$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( $home && 0 === strpos( $path, $home ) ) {
		$path = '/' . ltrim( substr( $path, strlen( $home ) ), '/' );
	}
	// Arabic page slugs do not exist on the English demo and the other way round.
	if ( preg_match( '/%[0-9a-f]{2}/i', $path ) || is_page() ) {
		$path = '/';
	}
	return $base . $path;
}

/**
 * Floating demo bar.
 */
function dara_demo_bar() {
	if ( is_admin() ) {
		return;
	}
	$buy    = defined( 'DARA_DEMO_BUY_URL' ) ? DARA_DEMO_BUY_URL : '';
	$switch = dara_demo_switch_url();
	$label  = defined( 'DARA_DEMO_SWITCH_LABEL' ) ? DARA_DEMO_SWITCH_LABEL : ( is_rtl() ? 'English' : 'العربية' );
	if ( ! $buy && ! $switch ) {
		return;
	}
	?>
	<div class="dara-demo-bar" role="complementary" aria-label="Demo">
		<?php if ( $switch ) : ?>
			<a class="dara-demo-bar__lang" href="<?php echo esc_url( $switch ); ?>" lang="<?php echo is_rtl() ? 'en' : 'ar'; ?>"><?php echo esc_html( $label ); ?></a>
		<?php endif; ?>
		<?php if ( $buy ) : ?>
			<a class="dara-demo-bar__buy" href="<?php echo esc_url( $buy ); ?>" target="_blank" rel="noopener"><?php echo esc_html( is_rtl() ? 'اشترِ القالب' : 'Buy Dara' ); ?></a>
		<?php endif; ?>
	</div>
	<style>
		.dara-demo-bar{position:fixed;z-index:9990;inset-inline-start:16px;bottom:16px;display:flex;gap:8px;font:600 14px/1 var(--font,system-ui,sans-serif)}
		.dara-demo-bar a{display:inline-flex;align-items:center;min-height:42px;padding:0 16px;border-radius:999px;text-decoration:none;box-shadow:0 10px 24px rgba(14,26,43,.22)}
		.dara-demo-bar__lang{background:#fff;color:#0E1A22;border:1px solid #E3E8EA}
		.dara-demo-bar__buy{background:#82B541;color:#fff}
		@media (max-width:767px){.dara-demo-bar{bottom:84px}}
		@media print{.dara-demo-bar{display:none}}
	</style>
	<?php
}
add_action( 'wp_footer', 'dara_demo_bar', 99 );

/**
 * Demo forms: accept the submission, then drop it (no stored leads, no email).
 *
 * @param int $lead_id Lead post ID.
 */
function dara_demo_drop_lead( $lead_id ) {
	wp_delete_post( $lead_id, true );
}
add_action( 'dara_lead_created', 'dara_demo_drop_lead' );
add_filter( 'pre_wp_mail', '__return_false' );
