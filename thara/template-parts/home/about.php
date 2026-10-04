<?php
/**
 * Home: about & counters.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="info-section" id="about">
	<div class="container">
		<div class="row">
			<div class="col-lg-7 col-md-6">
				<h2 class="top-title"><?php echo esc_html( thara_option( 'about_title' ) ); ?></h2>
				<p class="sub-title"><?php echo wp_kses_post( thara_option( 'about_text' ) ); ?></p>
			</div>
			<div class="col-lg-5 col-md-6">
				<div class="count-container">
					<?php foreach ( array( 1, 2 ) as $thara_i ) : ?>
						<?php if ( thara_option( "counter{$thara_i}_label" ) ) : ?>
							<div class="count-box">
								<h3><?php echo esc_html( thara_option( "counter{$thara_i}_label" ) ); ?></h3>
								<div class="count"><span>+</span><span class="timer" data-from="0" data-to="<?php echo absint( thara_option( "counter{$thara_i}_number" ) ); ?>"><?php echo absint( thara_option( "counter{$thara_i}_number" ) ); ?></span></div>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</section>
