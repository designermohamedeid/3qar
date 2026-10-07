<?php
/**
 * Admin: Licenses, Releases, Settings.
 *
 * @package DaraLicenseServer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Menus.
 */
function dls_admin_menu() {
	add_menu_page( 'Licenses', 'Licenses', 'manage_options', 'dls', 'dls_page_licenses', 'dashicons-admin-network', 58 );
	add_submenu_page( 'dls', 'Licenses', 'All licenses', 'manage_options', 'dls', 'dls_page_licenses' );
	add_submenu_page( 'dls', 'Add license', 'Add license', 'manage_options', 'dls-new', 'dls_page_new' );
	add_submenu_page( 'dls', 'Releases', 'Releases', 'manage_options', 'dls-releases', 'dls_page_releases' );
	add_submenu_page( 'dls', 'License server settings', 'Settings', 'manage_options', 'dls-settings', 'dls_page_settings' );
}
add_action( 'admin_menu', 'dls_admin_menu' );

/**
 * Admin notice from the "msg" query arg.
 */
function dls_admin_message() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$msg = isset( $_GET['dls_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['dls_msg'] ) ) : '';
	if ( $msg ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
	}
}

/**
 * Redirect back with a message.
 *
 * @param string $page Page slug.
 * @param string $msg  Message.
 * @param array  $args Extra args.
 */
function dls_back( $page, $msg, $args = array() ) {
	wp_safe_redirect(
		add_query_arg(
			array_merge(
				array(
					'page'    => $page,
					'dls_msg' => rawurlencode( $msg ),
				),
				$args
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}

/**
 * Status badge.
 *
 * @param array $l License.
 * @return string
 */
function dls_status_badge( $l ) {
	$problem = dls_license_problem( $l, true );
	$label   = $problem ? ( 'expired' === $problem ? 'Updates expired' : 'Suspended' ) : 'Active';
	$color   = $problem ? '#b32d2e' : '#00a32a';
	return '<span style="display:inline-block;padding:2px 8px;border-radius:10px;background:' . $color . ';color:#fff;font-size:12px">' . esc_html( $label ) . '</span>';
}

/**
 * Licenses list (or one license when &id= is set).
 */
function dls_page_licenses() {
	global $wpdb;
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['id'] ) ) {
		dls_page_edit( (int) $_GET['id'] );
		return;
	}
	$s     = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$paged = max( 1, isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 );
	// phpcs:enable
	$t     = dls_tables();
	$per   = 50;
	$where = '1=1';
	$args  = array();
	if ( $s ) {
		$where = '(l.license_key LIKE %s OR l.customer_email LIKE %s OR l.customer_name LIKE %s OR l.order_ref LIKE %s OR l.id IN (SELECT license_id FROM ' . $t->activations . ' WHERE domain LIKE %s))';
		$like  = '%' . $wpdb->esc_like( $s ) . '%';
		$args  = array( $like, $like, $like, $like, $like );
	}
	$count_sql = "SELECT COUNT(*) FROM {$t->licenses} l WHERE $where";
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
	$total  = (int) ( $args ? $wpdb->get_var( $wpdb->prepare( $count_sql, $args ) ) : $wpdb->get_var( $count_sql ) );
	$args[] = $per;
	$args[] = ( $paged - 1 ) * $per;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
	$rows = $wpdb->get_results( $wpdb->prepare( "SELECT l.*, (SELECT COUNT(*) FROM {$t->activations} a WHERE a.license_id = l.id AND a.is_local = 0) AS used FROM {$t->licenses} l WHERE $where ORDER BY l.id DESC LIMIT %d OFFSET %d", $args ), ARRAY_A );
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline">Licenses</h1>
		<a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=dls-new' ) ); ?>">Add license</a>
		<?php dls_admin_message(); ?>
		<form method="get" style="margin:12px 0">
			<input type="hidden" name="page" value="dls">
			<p class="search-box"><input type="search" name="s" value="<?php echo esc_attr( $s ); ?>" placeholder="Key, email, order, domain"> <button class="button">Search</button></p>
		</form>
		<table class="widefat striped">
			<thead><tr><th>Key</th><th>Customer</th><th>Sites</th><th>Status</th><th>Expires</th><th>Order</th><th>Created</th></tr></thead>
			<tbody>
			<?php if ( ! $rows ) : ?>
				<tr><td colspan="7">No licenses yet.</td></tr>
			<?php endif; ?>
			<?php foreach ( $rows as $l ) : ?>
				<tr>
					<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=dls&id=' . (int) $l['id'] ) ); ?>"><code><?php echo esc_html( $l['license_key'] ); ?></code></a></td>
					<td><?php echo esc_html( $l['customer_name'] ); ?><br><small><?php echo esc_html( $l['customer_email'] ); ?></small></td>
					<td><?php echo (int) $l['used'] . ' / ' . (int) $l['max_sites']; ?></td>
					<td><?php echo dls_status_badge( $l ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
					<td><?php echo $l['expires_at'] ? esc_html( get_date_from_gmt( $l['expires_at'], 'Y-m-d' ) ) : 'Lifetime'; ?></td>
					<td><?php echo esc_html( $l['order_ref'] ); ?></td>
					<td><?php echo esc_html( get_date_from_gmt( $l['created_at'], 'Y-m-d' ) ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		$pages = (int) ceil( $total / $per );
		if ( $pages > 1 ) {
			echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(
				paginate_links(
					array(
						'base'    => add_query_arg( 'paged', '%#%' ),
						'total'   => $pages,
						'current' => $paged,
					)
				)
			) . '</div></div>';
		}
		?>
	</div>
	<?php
}

/**
 * One license: edit fields and activations.
 *
 * @param int $id License ID.
 */
function dls_page_edit( $id ) {
	$l = dls_get_license( $id );
	if ( ! $l ) {
		echo '<div class="wrap"><p>License not found.</p></div>';
		return;
	}
	$acts = dls_activations( $id );
	?>
	<div class="wrap">
		<h1>License <code><?php echo esc_html( $l['license_key'] ); ?></code> <?php echo dls_status_badge( $l ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
		<?php dls_admin_message(); ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'dls_save_' . $id ); ?>
			<input type="hidden" name="action" value="dls_save">
			<input type="hidden" name="id" value="<?php echo (int) $id; ?>">
			<table class="form-table">
				<tr><th>Customer name</th><td><input class="regular-text" name="customer_name" value="<?php echo esc_attr( $l['customer_name'] ); ?>"></td></tr>
				<tr><th>Customer email</th><td><input class="regular-text" type="email" name="customer_email" value="<?php echo esc_attr( $l['customer_email'] ); ?>"></td></tr>
				<tr><th>Sites allowed</th><td><input type="number" min="1" name="max_sites" value="<?php echo (int) $l['max_sites']; ?>"> <p class="description">Production domains. Localhost and staging/dev subdomains are free.</p></td></tr>
				<tr><th>Status</th><td><select name="status"><option value="active" <?php selected( $l['status'], 'active' ); ?>>Active</option><option value="suspended" <?php selected( $l['status'], 'suspended' ); ?>>Suspended</option></select></td></tr>
				<tr><th>Updates &amp; support until</th><td><input type="date" name="expires_at" value="<?php echo $l['expires_at'] ? esc_attr( get_date_from_gmt( $l['expires_at'], 'Y-m-d' ) ) : ''; ?>"> <p class="description">Empty = lifetime.</p></td></tr>
				<tr><th>Notes</th><td><textarea class="large-text" rows="3" name="notes"><?php echo esc_textarea( (string) $l['notes'] ); ?></textarea></td></tr>
				<tr><th>Order</th><td><?php echo esc_html( $l['source'] . ' ' . $l['order_ref'] ); ?></td></tr>
			</table>
			<?php submit_button( 'Save license' ); ?>
		</form>

		<h2>Activated domains</h2>
		<table class="widefat striped" style="max-width:900px">
			<thead><tr><th>Domain</th><th>Type</th><th>Version</th><th>Activated</th><th>Last check</th><th></th></tr></thead>
			<tbody>
			<?php if ( ! $acts ) : ?>
				<tr><td colspan="6">Not activated on any site yet.</td></tr>
			<?php endif; ?>
			<?php foreach ( $acts as $a ) : ?>
				<tr>
					<td><a href="<?php echo esc_url( 'https://' . $a['domain'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $a['domain'] ); ?></a></td>
					<td><?php echo $a['is_local'] ? 'Local / staging' : 'Production'; ?></td>
					<td><?php echo esc_html( $a['version'] ); ?></td>
					<td><?php echo esc_html( get_date_from_gmt( $a['activated_at'], 'Y-m-d' ) ); ?></td>
					<td><?php echo esc_html( get_date_from_gmt( $a['last_check'], 'Y-m-d H:i' ) ); ?></td>
					<td>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Remove this domain?')">
							<?php wp_nonce_field( 'dls_unbind_' . $id ); ?>
							<input type="hidden" name="action" value="dls_unbind">
							<input type="hidden" name="id" value="<?php echo (int) $id; ?>">
							<input type="hidden" name="domain" value="<?php echo esc_attr( $a['domain'] ); ?>">
							<button class="button-link-delete">Remove</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p class="description">A removed domain stops working at its next weekly check, and the slot is free for a new domain.</p>
	</div>
	<?php
}

/**
 * Save license.
 */
function dls_handle_save() {
	$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
	check_admin_referer( 'dls_save_' . $id );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.' );
	}
	$exp = isset( $_POST['expires_at'] ) ? sanitize_text_field( wp_unslash( $_POST['expires_at'] ) ) : '';
	dls_update_license(
		$id,
		array(
			'customer_name'  => isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '',
			'customer_email' => isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '',
			'max_sites'      => isset( $_POST['max_sites'] ) ? max( 1, (int) $_POST['max_sites'] ) : 1,
			'status'         => isset( $_POST['status'] ) && 'suspended' === $_POST['status'] ? 'suspended' : 'active',
			'expires_at'     => $exp ? get_gmt_from_date( $exp . ' 23:59:59' ) : null,
			'notes'          => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
		)
	);
	dls_back( 'dls', 'License saved.', array( 'id' => $id ) );
}
add_action( 'admin_post_dls_save', 'dls_handle_save' );

/**
 * Remove a domain from a license.
 */
function dls_handle_unbind() {
	$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
	check_admin_referer( 'dls_unbind_' . $id );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.' );
	}
	dls_remove_activation( $id, isset( $_POST['domain'] ) ? dls_normalize_domain( wp_unslash( $_POST['domain'] ) ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	dls_back( 'dls', 'Domain removed.', array( 'id' => $id ) );
}
add_action( 'admin_post_dls_unbind', 'dls_handle_unbind' );

/**
 * Add license form.
 */
function dls_page_new() {
	?>
	<div class="wrap">
		<h1>Add license</h1>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'dls_new' ); ?>
			<input type="hidden" name="action" value="dls_new">
			<table class="form-table">
				<tr><th>Product</th><td><select name="product">
					<?php foreach ( dls_products() as $slug => $p ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $p['name'] ); ?></option>
					<?php endforeach; ?>
				</select></td></tr>
				<tr><th>Customer name</th><td><input class="regular-text" name="customer_name"></td></tr>
				<tr><th>Customer email</th><td><input class="regular-text" type="email" name="customer_email"></td></tr>
				<tr><th>Sites allowed</th><td><input type="number" min="1" name="max_sites" value="1"></td></tr>
				<tr><th>Updates &amp; support until</th><td><input type="date" name="expires_at" value="<?php echo esc_attr( gmdate( 'Y-m-d', strtotime( '+1 year' ) ) ); ?>"> <p class="description">Empty = lifetime.</p></td></tr>
				<tr><th>Order reference</th><td><input class="regular-text" name="order_ref"></td></tr>
				<tr><th>Notes</th><td><textarea class="large-text" rows="3" name="notes"></textarea></td></tr>
			</table>
			<?php submit_button( 'Create license' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Create license from the form.
 */
function dls_handle_new() {
	check_admin_referer( 'dls_new' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.' );
	}
	$data               = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized in dls_create_license().
	$data['source']     = 'manual';
	$data['expires_at'] = ! empty( $data['expires_at'] ) ? get_gmt_from_date( $data['expires_at'] . ' 23:59:59' ) : '';
	$row                = dls_create_license( $data );
	if ( is_wp_error( $row ) ) {
		wp_die( esc_html( $row->get_error_message() ) );
	}
	dls_back( 'dls', 'License created: ' . $row['license_key'], array( 'id' => $row['id'] ) );
}
add_action( 'admin_post_dls_new', 'dls_handle_new' );

/**
 * Releases page.
 */
function dls_page_releases() {
	$releases = dls_releases();
	?>
	<div class="wrap">
		<h1>Releases</h1>
		<?php dls_admin_message(); ?>
		<p>Upload a new version. Licensed sites see the update in <em>Dashboard → Updates</em> within 12 hours (or right away after "Check again").</p>
		<table class="widefat striped" style="max-width:900px">
			<thead><tr><th>Package</th><th>Latest version</th><th>Date</th><th>Tested up to</th></tr></thead>
			<tbody>
			<?php foreach ( dls_products() as $product ) : ?>
				<?php foreach ( $product['packages'] as $pkg ) : ?>
					<tr>
						<td><code><?php echo esc_html( $pkg ); ?></code></td>
						<td><?php echo isset( $releases[ $pkg ] ) ? esc_html( $releases[ $pkg ]['version'] ) : '—'; ?></td>
						<td><?php echo isset( $releases[ $pkg ] ) ? esc_html( $releases[ $pkg ]['date'] ) : ''; ?></td>
						<td><?php echo isset( $releases[ $pkg ] ) ? esc_html( $releases[ $pkg ]['tested'] ) : ''; ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endforeach; ?>
			</tbody>
		</table>

		<h2>Upload a release</h2>
		<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'dls_release' ); ?>
			<input type="hidden" name="action" value="dls_release">
			<table class="form-table">
				<tr><th>Package</th><td><select name="package">
					<?php foreach ( dls_products() as $product ) : ?>
						<?php foreach ( $product['packages'] as $pkg ) : ?>
							<option value="<?php echo esc_attr( $pkg ); ?>"><?php echo esc_html( $pkg ); ?></option>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</select> <p class="description">Pick the package that matches the zip (from the "direct" build): <code>dara</code> = dara.zip, <code>dara-core</code> = dara-core.zip, <code>dara-pro</code> = dara-pro.zip. Version = the version inside that zip.</p></td></tr>
				<tr><th>Version</th><td><input name="version" placeholder="1.7.0" required pattern="\d+(\.\d+){1,3}"></td></tr>
				<tr><th>Zip file</th><td><input type="file" name="zip" accept=".zip" required></td></tr>
				<tr><th>Tested up to (WordPress)</th><td><input name="tested" placeholder="6.8"></td></tr>
				<tr><th>Changelog</th><td><textarea class="large-text" rows="5" name="changelog" placeholder="&lt;ul&gt;&lt;li&gt;New: ...&lt;/li&gt;&lt;/ul&gt;"></textarea></td></tr>
			</table>
			<?php submit_button( 'Upload release' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Store an uploaded release.
 */
function dls_handle_release() {
	check_admin_referer( 'dls_release' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.' );
	}
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$file = isset( $_FILES['zip']['tmp_name'] ) ? $_FILES['zip']['tmp_name'] : '';
	if ( ! $file || ! is_uploaded_file( $file ) ) {
		wp_die( 'Upload failed.' );
	}
	$zip = new ZipArchive();
	if ( true !== $zip->open( $file ) ) {
		wp_die( 'Not a valid zip file.' );
	}
	$zip->close();
	$result = dls_add_release(
		isset( $_POST['package'] ) ? sanitize_key( $_POST['package'] ) : '',
		isset( $_POST['version'] ) ? sanitize_text_field( wp_unslash( $_POST['version'] ) ) : '',
		$file,
		array(
			'tested'    => isset( $_POST['tested'] ) ? sanitize_text_field( wp_unslash( $_POST['tested'] ) ) : '',
			'changelog' => isset( $_POST['changelog'] ) ? wp_kses_post( wp_unslash( $_POST['changelog'] ) ) : '',
		)
	);
	if ( is_wp_error( $result ) ) {
		wp_die( esc_html( $result->get_error_message() ) );
	}
	dls_back( 'dls-releases', 'Release uploaded.' );
}
add_action( 'admin_post_dls_release', 'dls_handle_release' );

/**
 * Settings page.
 */
function dls_page_settings() {
	?>
	<div class="wrap">
		<h1>License server settings</h1>
		<?php dls_admin_message(); ?>
		<h2>Public key</h2>
		<p>The Dara build uses this key to verify that license answers really come from this server. It is set in <code>bin/package.sh</code> (<code>DARA_LICENSE_PUBKEY</code>).</p>
		<p><input class="large-text code" readonly onclick="this.select()" value="<?php echo esc_attr( dls_public_key() ); ?>"></p>

		<h2>API endpoint</h2>
		<p><input class="large-text code" readonly onclick="this.select()" value="<?php echo esc_attr( rest_url( 'dls/v1/' ) ); ?>"></p>

		<h2>Shop API secret</h2>
		<p>For WHMCS or another shop to create, renew and suspend licenses. Send it in the <code>X-DLS-Secret</code> header. Keep it private.</p>
		<p><input class="regular-text code" readonly onclick="this.select()" value="<?php echo esc_attr( get_option( 'dls_api_secret' ) ); ?>"></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Your shop integration will stop working until you update the secret there. Continue?')">
			<?php wp_nonce_field( 'dls_regen_api' ); ?>
			<input type="hidden" name="action" value="dls_regen_api">
			<?php submit_button( 'Generate a new API secret', 'secondary', 'submit', false ); ?>
		</form>

		<h2>Signing keys</h2>
		<?php if ( defined( 'DLS_SIGN_SECRET' ) ) : ?>
			<p>The signing key is defined in <code>wp-config.php</code> (<code>DLS_SIGN_SECRET</code>).</p>
		<?php else : ?>
			<p><strong>Back up</strong> the option <code>dls_sign_secret</code> (or move it to <code>wp-config.php</code> as <code>DLS_SIGN_SECRET</code>). If it is lost, every site needs a new Dara build with the new public key.</p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * New API secret.
 */
function dls_handle_regen_api() {
	check_admin_referer( 'dls_regen_api' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.' );
	}
	update_option( 'dls_api_secret', bin2hex( random_bytes( 24 ) ), false );
	dls_back( 'dls-settings', 'New API secret generated.' );
}
add_action( 'admin_post_dls_regen_api', 'dls_handle_regen_api' );
