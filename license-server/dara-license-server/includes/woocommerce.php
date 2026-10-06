<?php
/**
 * Optional WooCommerce integration: a license per purchased item.
 *
 * Mark a product as a license product in its "General" tab (product + sites + months of updates).
 * When the order is completed, keys are created, saved on the order item and shown in emails and My Account.
 *
 * @package DaraLicenseServer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product fields.
 */
function dls_wc_product_fields() {
	echo '<div class="options_group">';
	woocommerce_wp_select(
		array(
			'id'      => '_dls_product',
			'label'   => 'License product',
			'options' => array( '' => '— None —' ) + wp_list_pluck( dls_products(), 'name' ),
		)
	);
	woocommerce_wp_text_input(
		array(
			'id'                => '_dls_sites',
			'label'             => 'Sites per license',
			'type'              => 'number',
			'custom_attributes' => array( 'min' => 1 ),
			'placeholder'       => '1',
		)
	);
	woocommerce_wp_text_input(
		array(
			'id'          => '_dls_months',
			'label'       => 'Months of updates',
			'type'        => 'number',
			'placeholder' => '12 (0 = lifetime)',
		)
	);
	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'dls_wc_product_fields' );

/**
 * Save product fields.
 *
 * @param int $post_id Product ID.
 */
function dls_wc_save_product( $post_id ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the product form.
	foreach ( array( '_dls_product', '_dls_sites', '_dls_months' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
	// phpcs:enable
}
add_action( 'woocommerce_process_product_meta', 'dls_wc_save_product' );

/**
 * Create licenses when an order is completed (once per item and quantity).
 *
 * @param int $order_id Order ID.
 */
function dls_wc_order_completed( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	foreach ( $order->get_items() as $item_id => $item ) {
		$product_id = $item->get_product_id();
		$product    = get_post_meta( $product_id, '_dls_product', true );
		if ( ! $product || $item->get_meta( '_dls_keys' ) ) {
			continue;
		}
		$months = get_post_meta( $product_id, '_dls_months', true );
		$months = '' === $months ? 12 : (int) $months;
		$keys   = array();
		for ( $q = 0; $q < max( 1, (int) $item->get_quantity() ); $q++ ) {
			$row = dls_create_license(
				array(
					'product'        => $product,
					'customer_name'  => $order->get_formatted_billing_full_name(),
					'customer_email' => $order->get_billing_email(),
					'max_sites'      => max( 1, (int) get_post_meta( $product_id, '_dls_sites', true ) ),
					'expires_at'     => $months ? gmdate( 'Y-m-d H:i:s', strtotime( '+' . $months . ' months' ) ) : '',
					'source'         => 'woocommerce',
					'order_ref'      => 'wc-' . $order_id . '-' . $item_id . '-' . $q,
				)
			);
			if ( ! is_wp_error( $row ) ) {
				$keys[] = $row['license_key'];
			}
		}
		if ( $keys ) {
			$item->update_meta_data( '_dls_keys', $keys );
			$item->update_meta_data( 'License key', implode( ', ', $keys ) );
			$item->save();
			$order->add_order_note( 'License key(s): ' . implode( ', ', $keys ) );
		}
	}
}
add_action( 'woocommerce_order_status_completed', 'dls_wc_order_completed' );

/**
 * Suspend licenses when an order is refunded or cancelled.
 *
 * @param int $order_id Order ID.
 */
function dls_wc_order_cancelled( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	foreach ( $order->get_items() as $item ) {
		foreach ( (array) $item->get_meta( '_dls_keys' ) as $key ) {
			$l = dls_get_license( (string) $key );
			if ( $l ) {
				dls_update_license( (int) $l['id'], array( 'status' => 'suspended' ) );
			}
		}
	}
}
add_action( 'woocommerce_order_status_refunded', 'dls_wc_order_cancelled' );
add_action( 'woocommerce_order_status_cancelled', 'dls_wc_order_cancelled' );
