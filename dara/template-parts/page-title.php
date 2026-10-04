<?php
/**
 * Inner page title bar.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;
$dara_title = isset( $args['title'] ) ? $args['title'] : dara_page_title();
?>
<div class="page-head">
	<div class="container">
		<?php dara_breadcrumbs(); ?>
		<h1 class="page-head__title"><?php echo esc_html( $dara_title ); ?></h1>
		<?php if ( ! empty( $args['subtitle'] ) ) : ?>
			<p class="page-head__sub"><?php echo esc_html( $args['subtitle'] ); ?></p>
		<?php endif; ?>
	</div>
</div>
