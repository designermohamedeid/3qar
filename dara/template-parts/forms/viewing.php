<?php
/**
 * Viewing request form (single property).
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;
?>
<form class="form" id="lead-form" method="post" action="<?php echo esc_url( dara_lead_action() ); ?>">
	<h2 class="form__title"><?php esc_html_e( 'Request a viewing', 'dara' ); ?></h2>
	<?php echo dara_lead_notice(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in plugin. ?>
	<?php dara_lead_hidden_fields( 'viewing', get_the_ID() ); ?>
	<label class="form__field"><span><?php esc_html_e( 'Name', 'dara' ); ?></span><input type="text" name="lead_name" required autocomplete="name"></label>
	<label class="form__field"><span><?php esc_html_e( 'Mobile number', 'dara' ); ?></span><input type="tel" name="lead_phone" required dir="ltr" inputmode="tel" autocomplete="tel" placeholder="05xxxxxxxx"></label>
	<label class="form__field"><span><?php esc_html_e( 'Preferred date', 'dara' ); ?></span><input type="date" name="lead_date" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>"></label>
	<label class="form__field"><span><?php esc_html_e( 'Message', 'dara' ); ?></span><textarea name="lead_message" rows="3"><?php esc_html_e( 'I would like to view this property.', 'dara' ); ?></textarea></label>
	<button type="submit" class="btn btn--dark btn--block"><?php esc_html_e( 'Send request', 'dara' ); ?></button>
</form>
