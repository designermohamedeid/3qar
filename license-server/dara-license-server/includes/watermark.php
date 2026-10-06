<?php
/**
 * Per-license builds: every Dara Pro download gets a unique build ID hidden in
 * several places, plus a signed manifest of its files. A leaked copy can be traced
 * back to the license that downloaded it (Licenses → Find a leak).
 *
 * @package DaraLicenseServer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Packages built per license.
 *
 * @return array
 */
function dls_watermarked_packages() {
	return (array) apply_filters( 'dls_watermarked_packages', array( 'dara-pro' ) );
}

/**
 * Files that carry the build ID in trailing whitespace.
 *
 * @return array
 */
function dls_stego_files() {
	return array( 'includes/features.php', 'includes/guard.php', 'includes/blocks.php', 'includes/demo.php' );
}

/**
 * Hide a 32-bit hex ID in trailing whitespace: space = 0, tab = 1, on lines ending with ";".
 *
 * @param string $code PHP source.
 * @param string $id   8 hex characters.
 * @return string
 */
function dls_stego_encode( $code, $id ) {
	$bits  = str_pad( base_convert( $id, 16, 2 ), 32, '0', STR_PAD_LEFT );
	$lines = explode( "\n", $code );
	$i     = 0;
	foreach ( $lines as &$line ) {
		if ( $i >= 32 ) {
			break;
		}
		$trim = rtrim( $line, " \t" );
		if ( '' !== $trim && ';' === substr( $trim, -1 ) ) {
			$line = $trim . ( '1' === $bits[ $i ] ? "\t" : ' ' );
			++$i;
		}
	}
	unset( $line );
	return $i >= 32 ? implode( "\n", $lines ) : $code;
}

/**
 * Read a hidden ID back (null when the whitespace was stripped).
 *
 * @param string $code PHP source.
 * @return string|null
 */
function dls_stego_decode( $code ) {
	$bits = '';
	foreach ( explode( "\n", $code ) as $line ) {
		if ( strlen( $bits ) >= 32 ) {
			break;
		}
		if ( preg_match( '/;([ \t])$/', $line, $m ) ) {
			$bits .= "\t" === $m[1] ? '1' : '0';
		} elseif ( ';' === substr( rtrim( $line ), -1 ) ) {
			return null;
		}
	}
	return 32 === strlen( $bits ) ? str_pad( base_convert( $bits, 2, 16 ), 8, '0', STR_PAD_LEFT ) : null;
}

/**
 * Build a per-license copy of a release zip.
 *
 * @param string $source  Release zip path.
 * @param string $package Package slug (the folder inside the zip).
 * @param array  $license License row.
 * @param string $domain  Domain.
 * @param string $version Version.
 * @return string|WP_Error Path of the temporary zip.
 */
function dls_build_watermarked( $source, $package, array $license, $domain, $version ) {
	global $wpdb;
	$id = bin2hex( random_bytes( 4 ) );
	$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		dls_tables()->downloads,
		array(
			'build_id'   => $id,
			'license_id' => (int) $license['id'],
			'package'    => $package,
			'version'    => $version,
			'domain'     => $domain,
			'ip'         => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			'created_at' => current_time( 'mysql', true ),
		)
	);

	require_once ABSPATH . 'wp-admin/includes/file.php';
	$tmp = wp_tempnam( $package . '.zip' );
	if ( ! copy( $source, $tmp ) ) {
		return new WP_Error( 'dls_build', 'Could not copy the release.' );
	}
	$zip = new ZipArchive();
	if ( true !== $zip->open( $tmp ) ) {
		return new WP_Error( 'dls_build', 'Could not open the release.' );
	}
	$root = $package . '/';

	// 1. Build file.
	$zip->addFromString( $root . 'includes/build.php', "<?php\n/**\n * Build information.\n *\n * @package DaraPro\n */\n\ndefined( 'ABSPATH' ) || exit;\n\nreturn array( 'b' => '" . $id . "' );\n" );

	// 2. Trailing whitespace in several PHP files.
	foreach ( dls_stego_files() as $rel ) {
		$code = $zip->getFromName( $root . $rel );
		if ( false !== $code ) {
			$zip->addFromString( $root . $rel, dls_stego_encode( $code, $id ) );
		}
	}

	// 3. Script files.
	foreach ( array( 'assets/js/pro.js', 'assets/js/pro.min.js' ) as $rel ) {
		$js = $zip->getFromName( $root . $rel );
		if ( false !== $js ) {
			$zip->addFromString( $root . $rel, rtrim( $js ) . "\n//# sourceURL=dara-pro-" . $id . ".js\n" );
		}
	}

	// 4. Signed manifest of every file.
	$zip->deleteName( $root . 'manifest.json' );
	$zip->deleteName( $root . 'manifest.sig' );
	$zip->close();
	$zip->open( $tmp );
	$files = array();
	for ( $i = 0; $i < $zip->numFiles; $i++ ) {  // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- ZipArchive property.
		$name = $zip->getNameIndex( $i );
		if ( 0 !== strpos( $name, $root ) || '/' === substr( $name, -1 ) ) {
			continue;
		}
		$files[ substr( $name, strlen( $root ) ) ] = hash( 'sha256', $zip->getFromIndex( $i ) );
	}
	ksort( $files );
	$manifest = wp_json_encode(
		array(
			'b'     => $id,
			'k'     => substr( hash( 'sha256', $license['license_key'] ), 0, 16 ),
			'v'     => $version,
			'files' => $files,
		),
		JSON_UNESCAPED_SLASHES
	);
	$zip->addFromString( $root . 'manifest.json', $manifest );
	$zip->addFromString( $root . 'manifest.sig', dls_sign_raw( $manifest ) );
	$zip->close();
	return $tmp;
}

/**
 * Find build IDs in a leaked file (zip, PHP or JS).
 *
 * @param string $path File path.
 * @param string $name Original file name.
 * @return array Build IDs found, with where they were found.
 */
function dls_find_build_ids( $path, $name ) {
	$found = array();
	$scan  = function ( $rel, $content ) use ( &$found ) {
		if ( preg_match( "/'b'\s*=>\s*'([0-9a-f]{8})'/", $content, $m ) ) {
			$found[ $m[1] ][] = $rel . ' (build file)';
		}
		if ( preg_match( '/sourceURL=dara-pro-([0-9a-f]{8})\.js/', $content, $m ) ) {
			$found[ $m[1] ][] = $rel . ' (script)';
		}
		if ( preg_match( '/"b":"([0-9a-f]{8})"/', $content, $m ) ) {
			$found[ $m[1] ][] = $rel . ' (manifest)';
		}
		if ( '.php' === substr( $rel, -4 ) ) {
			$id = dls_stego_decode( $content );
			if ( $id && '00000000' !== $id ) {
				$found[ $id ][] = $rel . ' (hidden whitespace)';
			}
		}
	};
	if ( '.zip' === strtolower( substr( $name, -4 ) ) ) {
		$zip = new ZipArchive();
		if ( true === $zip->open( $path ) ) {
			for ( $i = 0; $i < $zip->numFiles; $i++ ) {  // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- ZipArchive property.
				$rel = $zip->getNameIndex( $i );
				if ( preg_match( '/\.(php|js|json)$/', $rel ) ) {
					$scan( $rel, (string) $zip->getFromIndex( $i ) );
				}
			}
			$zip->close();
		}
	} else {
		$scan( $name, (string) file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}
	return $found;
}

/**
 * Leak finder page.
 */
function dls_page_leaks() {
	global $wpdb;
	$results = null;
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified below.
	if ( isset( $_FILES['leak'] ) && check_admin_referer( 'dls_leak' ) && current_user_can( 'manage_options' ) ) {
		$tmp     = isset( $_FILES['leak']['tmp_name'] ) ? $_FILES['leak']['tmp_name'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$name    = isset( $_FILES['leak']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['leak']['name'] ) ) : '';
		$results = $tmp && is_uploaded_file( $tmp ) ? dls_find_build_ids( $tmp, $name ) : array();
	}
	?>
	<div class="wrap">
		<h1>Find a leak</h1>
		<p>Found Dara Pro on a nulled site or shared somewhere? Upload the zip (or any PHP / JS file from it). Each download is built for one license, so the hidden build ID shows who downloaded it.</p>
		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'dls_leak' ); ?>
			<input type="file" name="leak" accept=".zip,.php,.js,.json" required>
			<?php submit_button( 'Check file', 'primary', 'submit', false ); ?>
		</form>
		<?php if ( null !== $results ) : ?>
			<h2>Result</h2>
			<?php if ( ! $results ) : ?>
				<p>No build ID found. The file may be a development build or every mark was removed.</p>
			<?php else : ?>
				<table class="widefat striped" style="max-width:1000px">
					<thead><tr><th>Build ID</th><th>Found in</th><th>License</th><th>Customer</th><th>Downloaded by</th><th>Date</th></tr></thead>
					<tbody>
					<?php foreach ( $results as $id => $where ) : ?>
						<?php
						$t = dls_tables();
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						$row = $wpdb->get_row( $wpdb->prepare( "SELECT d.*, l.license_key, l.customer_name, l.customer_email FROM {$t->downloads} d LEFT JOIN {$t->licenses} l ON l.id = d.license_id WHERE d.build_id = %s", $id ), ARRAY_A );
						?>
						<tr>
							<td><code><?php echo esc_html( $id ); ?></code></td>
							<td><?php echo esc_html( implode( ', ', array_unique( $where ) ) ); ?></td>
							<?php if ( $row ) : ?>
								<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=dls&id=' . (int) $row['license_id'] ) ); ?>"><code><?php echo esc_html( $row['license_key'] ); ?></code></a></td>
								<td><?php echo esc_html( $row['customer_name'] . ' <' . $row['customer_email'] . '>' ); ?></td>
								<td><?php echo esc_html( $row['domain'] . ' · ' . $row['ip'] ); ?></td>
								<td><?php echo esc_html( get_date_from_gmt( $row['created_at'], 'Y-m-d H:i' ) ); ?></td>
							<?php else : ?>
								<td colspan="4">Unknown build ID (not from this server).</td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Menu entry.
 */
function dls_leaks_menu() {
	add_submenu_page( 'dls', 'Find a leak', 'Find a leak', 'manage_options', 'dls-leaks', 'dls_page_leaks' );
}
add_action( 'admin_menu', 'dls_leaks_menu', 20 );
