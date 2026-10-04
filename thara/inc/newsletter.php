<?php
/**
 * Built-in newsletter form: emails each subscription to the site admin.
 *
 * Used only when no shortcode / external action is configured.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle the newsletter submission.
 */
function thara_handle_newsletter() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$redirect = remove_query_arg( 'thara_newsletter', $redirect );

	if ( ! isset( $_POST['thara_newsletter_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['thara_newsletter_nonce'] ), 'thara_newsletter' ) ) {
		wp_safe_redirect( add_query_arg( 'thara_newsletter', 'error', $redirect ) . '#newsletter' );
		exit;
	}

	// Honeypot: bots fill every field.
	if ( ! empty( $_POST['thara_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'thara_newsletter', 'success', $redirect ) . '#newsletter' );
		exit;
	}

	$email = isset( $_POST['EMAIL'] ) ? sanitize_email( wp_unslash( $_POST['EMAIL'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'thara_newsletter', 'invalid', $redirect ) . '#newsletter' );
		exit;
	}

	$subscribers = (array) get_option( 'thara_newsletter_subscribers', array() );
	if ( ! in_array( $email, $subscribers, true ) ) {
		$subscribers[] = $email;
		update_option( 'thara_newsletter_subscribers', $subscribers, false );

		wp_mail(
			get_option( 'admin_email' ),
			/* translators: %s: site name. */
			sprintf( __( '[%s] New newsletter subscriber', 'thara' ), get_bloginfo( 'name' ) ),
			/* translators: %s: email address. */
			sprintf( __( 'New subscriber: %s', 'thara' ), $email )
		);
	}

	wp_safe_redirect( add_query_arg( 'thara_newsletter', 'success', $redirect ) . '#newsletter' );
	exit;
}
add_action( 'admin_post_nopriv_thara_newsletter', 'thara_handle_newsletter' );
add_action( 'admin_post_thara_newsletter', 'thara_handle_newsletter' );

/**
 * Status message after a submission.
 *
 * @return string
 */
function thara_newsletter_message() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	$status   = isset( $_GET['thara_newsletter'] ) ? sanitize_key( $_GET['thara_newsletter'] ) : '';
	$messages = array(
		'success' => __( 'Thank you! You have been subscribed.', 'thara' ),
		'invalid' => __( 'Please enter a valid email address.', 'thara' ),
		'error'   => __( 'Something went wrong, please try again.', 'thara' ),
	);
	if ( ! isset( $messages[ $status ] ) ) {
		return '';
	}
	return sprintf( '<p class="newsletter-msg newsletter-msg--%1$s" role="status">%2$s</p>', esc_attr( $status ), esc_html( $messages[ $status ] ) );
}

/**
 * Subscribers list in Tools > Newsletter Subscribers.
 */
function thara_newsletter_admin_page() {
	add_management_page(
		__( 'Newsletter Subscribers', 'thara' ),
		__( 'Newsletter Subscribers', 'thara' ),
		'manage_options',
		'thara-newsletter',
		'thara_render_newsletter_admin_page'
	);
}
add_action( 'admin_menu', 'thara_newsletter_admin_page' );

/**
 * Render subscribers page.
 */
function thara_render_newsletter_admin_page() {
	$subscribers = (array) get_option( 'thara_newsletter_subscribers', array() );
	echo '<div class="wrap"><h1>' . esc_html__( 'Newsletter Subscribers', 'thara' ) . '</h1>';
	if ( empty( $subscribers ) ) {
		echo '<p>' . esc_html__( 'No subscribers yet.', 'thara' ) . '</p></div>';
		return;
	}
	echo '<p>' . esc_html__( 'Copy this list into your email marketing tool:', 'thara' ) . '</p>';
	echo '<textarea readonly class="large-text code" rows="15">' . esc_textarea( implode( "\n", $subscribers ) ) . '</textarea></div>';
}
