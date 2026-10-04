<?php
/**
 * Home: why us.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

$dara_img    = (int) dara_part_opt( $args, 'why_image' );
$dara_points = dara_part_lines( $args, 'why_points' );
?>
<section class="section section--tight">
	<div class="container why">
		<div class="why__media">
			<?php echo dara_img( $dara_img, 'dara-wide', array( 'sizes' => '(max-width: 900px) 100vw, 600px', 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( dara_part_opt( $args, 'why_badge_title' ) ) : ?>
				<div class="why__badge">
					<span class="why__badge-icon"><?php dara_the_icon( 'shield', 22 ); ?></span>
					<span><strong><?php echo esc_html( dara_part_opt( $args, 'why_badge_title' ) ); ?></strong><small><?php echo esc_html( dara_part_opt( $args, 'why_badge_text' ) ); ?></small></span>
				</div>
			<?php endif; ?>
		</div>
		<div class="why__content">
			<?php dara_section_head( dara_part_opt( $args, 'why_eyebrow' ), dara_part_opt( $args, 'why_title' ), dara_part_opt( $args, 'why_text' ) ); ?>
			<?php if ( $dara_points ) : ?>
				<ul class="checklist">
					<?php foreach ( $dara_points as $dara_point ) : ?>
						<li><span class="checklist__icon"><?php dara_the_icon( 'check', 16 ); ?></span><?php echo esc_html( $dara_point[0] ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ( dara_part_opt( $args, 'why_btn_text' ) ) : ?>
				<a class="btn btn--primary" href="<?php echo esc_url( dara_part_opt( $args, 'why_btn_url' ) ? dara_part_opt( $args, 'why_btn_url' ) : dara_template_url( 'page-templates/contact.php' ) ); ?>"><?php echo esc_html( dara_part_opt( $args, 'why_btn_text' ) ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
