<?php
/**
 * Leads: every front-end form posts here and is stored as a "Lead".
 *
 * Forms are cache friendly (no nonce, which would expire in cached pages):
 * spam is stopped with a honeypot field, a minimum fill time and a per-IP rate limit.
 *
 * @package DaraCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hidden fields every lead form needs.
 *
 * @param string $type    Lead type (see dara_lead_types()).
 * @param int    $post_id Related property/project.
 */
function dara_lead_hidden_fields( $type, $post_id = 0 ) {
	$time = time();
	printf( '<input type="hidden" name="action" value="dara_lead">' );
	printf( '<input type="hidden" name="lead_type" value="%s">', esc_attr( $type ) );
	printf( '<input type="hidden" name="lead_post" value="%d">', (int) $post_id );
	printf( '<input type="hidden" name="lead_ts" value="%1$d.%2$s">', (int) $time, esc_attr( substr( wp_hash( 'dara_lead' . $time ), 0, 12 ) ) );
	echo '<div class="hp" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>';
}

/**
 * Form action URL.
 *
 * @return string
 */
function dara_lead_action() {
	return admin_url( 'admin-post.php' );
}

/**
 * Status message after submitting.
 *
 * @return string HTML.
 */
function dara_lead_notice() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	$status = isset( $_GET['lead'] ) ? sanitize_key( $_GET['lead'] ) : '';
	$map    = array(
		'sent'    => array( 'success', __( 'Thank you! We received your request and will contact you shortly.', 'dara-core' ) ),
		'invalid' => array( 'error', __( 'Please enter your name and a valid phone number or email.', 'dara-core' ) ),
		'wait'    => array( 'error', __( 'Please wait a moment before sending again.', 'dara-core' ) ),
	);
	if ( ! isset( $map[ $status ] ) ) {
		return '';
	}
	return sprintf( '<p class="form-notice form-notice--%1$s" role="status">%2$s</p>', esc_attr( $map[ $status ][0] ), esc_html( $map[ $status ][1] ) );
}

/**
 * Handle a submission.
 */
function dara_core_handle_lead() {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$back = remove_query_arg( 'lead', $back );
	$go   = function ( $status ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'lead', $status, $back ) . '#lead-form' );
		exit;
	};

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- see file comment.
	$post = wp_unslash( $_POST );
	// phpcs:enable

	// Honeypot: pretend success.
	if ( ! empty( $post['website'] ) ) {
		$go( 'sent' );
	}

	// Minimum time on form (3s) with a signed timestamp.
	$ts = isset( $post['lead_ts'] ) ? explode( '.', (string) $post['lead_ts'] ) : array();
	if ( 2 !== count( $ts ) || substr( wp_hash( 'dara_lead' . (int) $ts[0] ), 0, 12 ) !== $ts[1] || time() - (int) $ts[0] < 3 ) {
		$go( 'wait' );
	}

	// Rate limit: 1 lead / 20s per IP.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'dara_lead_' . md5( $ip );
	if ( get_transient( $key ) ) {
		$go( 'wait' );
	}

	$types = dara_lead_types();
	$type  = isset( $post['lead_type'] ) && isset( $types[ $post['lead_type'] ] ) ? $post['lead_type'] : 'contact';
	$name  = isset( $post['lead_name'] ) ? sanitize_text_field( $post['lead_name'] ) : '';
	$phone = isset( $post['lead_phone'] ) ? preg_replace( '/[^0-9+ ]/', '', (string) $post['lead_phone'] ) : '';
	$email = isset( $post['lead_email'] ) ? sanitize_email( $post['lead_email'] ) : '';

	if ( 'newsletter' === $type ) {
		if ( ! is_email( $email ) ) {
			$go( 'invalid' );
		}
		$name = $email;
	} elseif ( '' === $name || ( strlen( preg_replace( '/\D/', '', $phone ) ) < 7 && ! is_email( $email ) ) ) {
		$go( 'invalid' );
	}

	$related = isset( $post['lead_post'] ) ? absint( $post['lead_post'] ) : 0;
	if ( $related && ! in_array( get_post_type( $related ), array( 'dara_property', 'dara_project' ), true ) ) {
		$related = 0;
	}

	// Extra structured fields (list-your-property / project unit type...).
	$extra = array();
	$extra_fields = array(
		'lead_city'    => __( 'City', 'dara-core' ),
		'lead_ptype'   => __( 'Property type', 'dara-core' ),
		'lead_purpose' => __( 'Purpose', 'dara-core' ),
		'lead_unit'    => __( 'Unit type', 'dara-core' ),
		'lead_price'   => __( 'Expected price', 'dara-core' ),
	);
	foreach ( $extra_fields as $field => $label ) {
		if ( ! empty( $post[ $field ] ) ) {
			$extra[] = $label . ': ' . sanitize_text_field( $post[ $field ] );
		}
	}

	$data = array(
		'_lead_type'    => $type,
		'_lead_name'    => $name,
		'_lead_phone'   => $phone,
		'_lead_email'   => $email,
		'_lead_date'    => isset( $post['lead_date'] ) ? sanitize_text_field( $post['lead_date'] ) : '',
		'_lead_message' => isset( $post['lead_message'] ) ? sanitize_textarea_field( $post['lead_message'] ) : '',
		'_lead_extra'   => implode( "\n", $extra ),
		'_lead_post'    => $related,
		'_lead_source'  => esc_url_raw( $back ),
	);

	$title   = $types[ $type ] . ' — ' . $name . ( $related ? ' — ' . get_the_title( $related ) : '' );
	$lead_id = wp_insert_post(
		array(
			'post_type'   => 'dara_lead',
			'post_status' => 'publish',
			'post_title'  => wp_strip_all_tags( $title ),
			'meta_input'  => $data,
		)
	);

	set_transient( $key, 1, 20 );

	if ( $lead_id && ! is_wp_error( $lead_id ) ) {
		$to = array( dara_setting( 'lead_email' ) );
		if ( $related ) {
			$agent = dara_get_agent( $related );
			if ( ! empty( $agent['email'] ) ) {
				$to[] = $agent['email'];
			}
		}
		$body = '';
		foreach ( $data as $k => $v ) {
			if ( '' !== $v && '_lead_post' !== $k ) {
				$body .= ucfirst( str_replace( '_lead_', '', $k ) ) . ': ' . $v . "\n";
			}
		}
		if ( $related ) {
			$body .= get_permalink( $related ) . "\n";
		}
		$body .= "\n" . admin_url( 'post.php?post=' . $lead_id . '&action=edit' );
		$headers = is_email( $email ) ? array( 'Reply-To: ' . $name . ' <' . $email . '>' ) : array();
		wp_mail( array_unique( array_filter( $to ) ), '[' . get_bloginfo( 'name' ) . '] ' . $title, $body, $headers );

		do_action( 'dara_lead_created', $lead_id, $data );
	}

	$go( 'sent' );
}
add_action( 'admin_post_nopriv_dara_lead', 'dara_core_handle_lead' );
add_action( 'admin_post_dara_lead', 'dara_core_handle_lead' );

/**
 * Unread leads bubble in the admin menu.
 */
function dara_core_leads_bubble() {
	global $menu;
	$count = (int) wp_count_posts( 'dara_lead' )->publish;
	$seen  = (int) get_user_meta( get_current_user_id(), 'dara_leads_seen', true );
	$new   = max( 0, $count - $seen );
	if ( ! $new ) {
		return;
	}
	foreach ( $menu as $i => $item ) {
		if ( 'edit.php?post_type=dara_lead' === $item[2] ) {
			$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . (int) $new . '</span></span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}
	}
}
add_action( 'admin_menu', 'dara_core_leads_bubble', 99 );

/**
 * Mark leads as seen when the list is opened.
 */
function dara_core_leads_seen() {
	$screen = get_current_screen();
	if ( $screen && 'edit-dara_lead' === $screen->id ) {
		update_user_meta( get_current_user_id(), 'dara_leads_seen', (int) wp_count_posts( 'dara_lead' )->publish );
	}
}
add_action( 'current_screen', 'dara_core_leads_seen' );
