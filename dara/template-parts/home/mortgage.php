<?php
/**
 * Mortgage calculator section (block).
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="section">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/property/mortgage',
			null,
			array(
				'price'     => isset( $args['mortgage_price'] ) ? (float) $args['mortgage_price'] : 0,
				'title'     => isset( $args['mortgage_title'] ) ? $args['mortgage_title'] : '',
				'apply_url' => dara_template_url( 'page-templates/contact.php' ),
			)
		);
		?>
	</div>
</section>
