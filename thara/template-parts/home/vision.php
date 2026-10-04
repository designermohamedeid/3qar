<?php
/**
 * Home: vision & goals.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="goals-section" id="vision">
	<div class="container">
		<div class="row">
			<div class="col-md-6">
				<?php if ( thara_option( 'vision_image' ) ) : ?>
					<div class="img-box"><img class="img-responsive" src="<?php echo esc_url( thara_option( 'vision_image' ) ); ?>" alt="<?php echo esc_attr( thara_option( 'vision_title' ) ); ?>" loading="lazy"></div>
				<?php endif; ?>
			</div>
			<div class="col-md-6">
				<div class="goals-continer">
					<?php foreach ( array( 'vision', 'goals' ) as $thara_box ) : ?>
						<?php if ( thara_option( "{$thara_box}_title" ) || thara_option( "{$thara_box}_text" ) ) : ?>
							<div class="goal-box">
								<?php if ( thara_option( "{$thara_box}_icon" ) ) : ?>
									<div class="icon-box"><img src="<?php echo esc_url( thara_option( "{$thara_box}_icon" ) ); ?>" alt="" aria-hidden="true"></div>
								<?php endif; ?>
								<div class="caption">
									<h2><?php echo esc_html( thara_option( "{$thara_box}_title" ) ); ?></h2>
									<p><?php echo wp_kses_post( thara_option( "{$thara_box}_text" ) ); ?></p>
								</div>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</section>
