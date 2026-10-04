<?php
/**
 * Inner pages title banner.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="content page-hero">
	<div class="container">
		<h1 class="page-hero__title"><?php echo esc_html( thara_page_title() ); ?></h1>
		<?php thara_breadcrumbs(); ?>
	</div>
</div>
