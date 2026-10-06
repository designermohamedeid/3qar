<?php
/**
 * Mortgage calculator. Args: price (number), title (string), heading (h2|h3), apply_url (string).
 *
 * Results are rendered server-side (no layout shift, works without JS) and
 * updated live by main.js.
 *
 * @package DaraPro
 */

defined( 'ABSPATH' ) || exit;

$dara_price  = isset( $args['price'] ) && (float) $args['price'] > 0 ? (float) $args['price'] : 1000000;
$dara_down   = min( 90, max( 0, (int) dara_mod( 'mortgage_down' ) ) );
$dara_max    = max( 5, min( 40, (int) dara_mod( 'mortgage_max_years' ) ) );
$dara_years  = min( $dara_max, max( 1, (int) dara_mod( 'mortgage_years' ) ) );
$dara_rate   = (float) dara_mod( 'mortgage_rate' );
$dara_method = 'flat' === dara_mod( 'mortgage_method' ) ? 'flat' : 'amortized';
$dara_cur    = function_exists( 'dara_setting' ) ? dara_setting( 'currency' ) : '';
$dara_calc   = dara_mortgage_calc( $dara_price, $dara_down, $dara_years, $dara_rate, $dara_method );
$dara_head   = isset( $args['heading'] ) && 'h3' === $args['heading'] ? 'h3' : 'h2';
$dara_uid    = wp_unique_id( 'mortgage-' );
$dara_fmt    = function ( $n ) {
	return number_format_i18n( round( $n ) );
};
$dara_pct    = function ( $part ) use ( $dara_calc ) {
	return $dara_calc['total'] > 0 ? round( 100 * $part / $dara_calc['total'], 2 ) : 0;
};
?>
<section class="mortgage" data-mortgage data-method="<?php echo esc_attr( $dara_method ); ?>" aria-labelledby="<?php echo esc_attr( $dara_uid ); ?>-title"
	data-msg="<?php echo esc_attr__( 'I would like a financing consultation. Price: {price}, down payment: {down}, term: {years} years, estimated monthly installment: {monthly}.', 'dara-pro' ); ?>">
	<div class="mortgage__head">
		<span class="mortgage__icon"><?php dara_the_icon( 'chart', 22 ); ?></span>
		<div>
			<<?php echo esc_html( $dara_head ); ?> class="mortgage__title" id="<?php echo esc_attr( $dara_uid ); ?>-title"><?php echo esc_html( isset( $args['title'] ) && $args['title'] ? $args['title'] : __( 'Mortgage calculator', 'dara-pro' ) ); ?></<?php echo esc_html( $dara_head ); ?>>
			<p class="mortgage__sub"><?php echo esc_html( 'flat' === $dara_method ? __( 'Estimated monthly installment (flat rate)', 'dara-pro' ) : __( 'Estimated monthly installment (declining balance)', 'dara-pro' ) ); ?></p>
		</div>
	</div>

	<div class="mortgage__body">
		<form class="mortgage__form" novalidate>
			<label class="mortgage__field">
				<span class="mortgage__label"><?php echo esc_html( sprintf( /* translators: %s: currency. */ __( 'Property price (%s)', 'dara-pro' ), $dara_cur ) ); ?></span>
				<input type="number" name="price" inputmode="numeric" min="0" step="1000" value="<?php echo esc_attr( round( $dara_price ) ); ?>">
			</label>

			<label class="mortgage__field">
				<span class="mortgage__label"><?php esc_html_e( 'Down payment', 'dara-pro' ); ?> <output data-out="down-pct"><?php echo esc_html( $dara_down ); ?>%</output></span>
				<input type="range" name="down" min="0" max="90" step="1" value="<?php echo esc_attr( $dara_down ); ?>">
				<span class="mortgage__hint" data-out="down"><?php echo esc_html( $dara_fmt( $dara_calc['down'] ) . ' ' . $dara_cur ); ?></span>
			</label>

			<label class="mortgage__field">
				<span class="mortgage__label"><?php esc_html_e( 'Term', 'dara-pro' ); ?> <output data-out="years"><?php echo esc_html( sprintf( /* translators: %s: years. */ _n( '%s year', '%s years', $dara_years, 'dara-pro' ), number_format_i18n( $dara_years ) ) ); ?></output></span>
				<input type="range" name="years" min="1" max="<?php echo esc_attr( $dara_max ); ?>" step="1" value="<?php echo esc_attr( $dara_years ); ?>" data-unit-one="<?php echo esc_attr( _n( '%s year', '%s years', 1, 'dara-pro' ) ); ?>" data-unit-many="<?php echo esc_attr( _n( '%s year', '%s years', 11, 'dara-pro' ) ); ?>" data-unit-few="<?php echo esc_attr( _n( '%s year', '%s years', 3, 'dara-pro' ) ); ?>" data-unit-two="<?php echo esc_attr( _n( '%s year', '%s years', 2, 'dara-pro' ) ); ?>">
			</label>

			<label class="mortgage__field">
				<span class="mortgage__label"><?php esc_html_e( 'Annual profit rate (%)', 'dara-pro' ); ?></span>
				<input type="number" name="rate" inputmode="decimal" min="0" max="30" step="0.01" value="<?php echo esc_attr( $dara_rate ); ?>">
			</label>
		</form>

		<div class="mortgage__result" aria-live="polite">
			<span class="mortgage__result-label"><?php esc_html_e( 'Monthly installment', 'dara-pro' ); ?></span>
			<p class="mortgage__monthly"><strong data-out="monthly"><?php echo esc_html( $dara_fmt( $dara_calc['monthly'] ) ); ?></strong> <span><?php echo esc_html( $dara_cur ); ?></span></p>

			<div class="mortgage__bar" aria-hidden="true">
				<span class="is-down" data-bar="down" style="width:<?php echo esc_attr( $dara_pct( $dara_calc['down'] ) ); ?>%"></span>
				<span class="is-loan" data-bar="loan" style="width:<?php echo esc_attr( $dara_pct( $dara_calc['loan'] ) ); ?>%"></span>
				<span class="is-profit" data-bar="profit" style="width:<?php echo esc_attr( $dara_pct( $dara_calc['profit'] ) ); ?>%"></span>
			</div>

			<dl class="mortgage__list">
				<div><dt><i class="is-down"></i><?php esc_html_e( 'Down payment', 'dara-pro' ); ?></dt><dd data-out="down"><?php echo esc_html( $dara_fmt( $dara_calc['down'] ) . ' ' . $dara_cur ); ?></dd></div>
				<div><dt><i class="is-loan"></i><?php esc_html_e( 'Financing amount', 'dara-pro' ); ?></dt><dd data-out="loan"><?php echo esc_html( $dara_fmt( $dara_calc['loan'] ) . ' ' . $dara_cur ); ?></dd></div>
				<div><dt><i class="is-profit"></i><?php esc_html_e( 'Total profit', 'dara-pro' ); ?></dt><dd data-out="profit"><?php echo esc_html( $dara_fmt( $dara_calc['profit'] ) . ' ' . $dara_cur ); ?></dd></div>
				<div class="is-total"><dt><?php esc_html_e( 'Total cost', 'dara-pro' ); ?></dt><dd data-out="total"><?php echo esc_html( $dara_fmt( $dara_calc['total'] ) . ' ' . $dara_cur ); ?></dd></div>
			</dl>

			<a class="btn btn--primary btn--block" data-mortgage-apply href="<?php echo esc_url( ! empty( $args['apply_url'] ) ? $args['apply_url'] : '#lead-form' ); ?>"><?php esc_html_e( 'Request a financing consultation', 'dara-pro' ); ?></a>
			<?php if ( dara_mod( 'mortgage_note' ) ) : ?>
				<p class="mortgage__note"><?php echo esc_html( dara_mod( 'mortgage_note' ) ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<span class="screen-reader-text" data-currency><?php echo esc_html( $dara_cur ); ?></span>
</section>
