<?php
/**
 * Template Name: List your property
 *
 * Owners send their property details; it arrives as a Lead.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/page-title' );
$dara_types = dara_has_core() ? get_terms(
	array(
		'taxonomy'   => 'property_type',
		'hide_empty' => false,
	)
) : array();
?>
<div class="container container--mid section section--top-0">
	<?php
	while ( have_posts() ) :
		the_post();
		if ( '' !== trim( get_the_content() ) ) {
			echo '<div class="entry-content">';
			the_content();
			echo '</div>';
		}
	endwhile;
	?>
	<?php if ( dara_has_core() ) : ?>
		<form class="form form--card" id="lead-form" method="post" action="<?php echo esc_url( dara_lead_action() ); ?>">
			<?php echo dara_lead_notice(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php dara_lead_hidden_fields( 'listing' ); ?>
			<div class="form__grid">
				<label class="form__field"><span><?php esc_html_e( 'Name', 'dara' ); ?></span><input type="text" name="lead_name" required autocomplete="name"></label>
				<label class="form__field"><span><?php esc_html_e( 'Mobile number', 'dara' ); ?></span><input type="tel" name="lead_phone" required dir="ltr" inputmode="tel" autocomplete="tel"></label>
				<fieldset class="form__field form__field--full">
					<legend><?php esc_html_e( 'I want to', 'dara' ); ?></legend>
					<div class="seg">
						<label><input type="radio" name="lead_purpose" value="<?php esc_attr_e( 'Sell', 'dara' ); ?>" checked><span><?php esc_html_e( 'Sell', 'dara' ); ?></span></label>
						<label><input type="radio" name="lead_purpose" value="<?php esc_attr_e( 'Rent out', 'dara' ); ?>"><span><?php esc_html_e( 'Rent out', 'dara' ); ?></span></label>
						<label><input type="radio" name="lead_purpose" value="<?php esc_attr_e( 'Management', 'dara' ); ?>"><span><?php esc_html_e( 'Management', 'dara' ); ?></span></label>
					</div>
				</fieldset>
				<label class="form__field"><span><?php esc_html_e( 'Property type', 'dara' ); ?></span>
					<select name="lead_ptype">
						<?php foreach ( (array) $dara_types as $dara_t ) : ?>
							<?php if ( $dara_t instanceof WP_Term ) : ?>
								<option><?php echo esc_html( $dara_t->name ); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
						<option><?php esc_html_e( 'Other', 'dara' ); ?></option>
					</select>
				</label>
				<label class="form__field"><span><?php esc_html_e( 'City / district', 'dara' ); ?></span><input type="text" name="lead_city" required></label>
				<label class="form__field form__field--full"><span><?php esc_html_e( 'Expected price (optional)', 'dara' ); ?></span><input type="text" name="lead_price" inputmode="numeric"></label>
				<label class="form__field form__field--full"><span><?php esc_html_e( 'Details', 'dara' ); ?></span><textarea name="lead_message" rows="5" placeholder="<?php esc_attr_e( 'Area, rooms, age, special features…', 'dara' ); ?>"></textarea></label>
			</div>
			<button type="submit" class="btn btn--primary"><?php esc_html_e( 'Send property details', 'dara' ); ?></button>
		</form>
	<?php endif; ?>
</div>
<?php
get_footer();
