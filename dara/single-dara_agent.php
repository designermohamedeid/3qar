<?php
/**
 * Agent profile: bio, contact and their listings.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$dara_id    = get_the_ID();
	$dara_phone = get_post_meta( $dara_id, '_dara_phone', true );
	$dara_wa    = get_post_meta( $dara_id, '_dara_whatsapp', true );
	$dara_email = get_post_meta( $dara_id, '_dara_email', true );
	$dara_fal   = get_post_meta( $dara_id, '_dara_fal', true );
	$dara_paged = max( 1, (int) get_query_var( 'paged' ) );
	$dara_props = new WP_Query(
		array(
			'post_type'      => 'dara_property',
			'posts_per_page' => 9,
			'paged'          => $dara_paged,
			'meta_key'       => '_dara_agent', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $dara_id, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	?>
	<div class="page-head page-head--agent">
		<div class="container agent-profile">
			<span class="avatar avatar--xl">
				<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail(
						'medium',
						array(
							'alt'     => '',
							'loading' => 'eager',
						)
					);
				} else {
					echo esc_html( dara_initials( get_the_title() ) );
				}
				?>
			</span>
			<div class="agent-profile__info">
				<?php dara_breadcrumbs(); ?>
				<h1 class="page-head__title"><?php the_title(); ?></h1>
				<p class="agent-profile__role">
					<?php
					echo esc_html( get_the_excerpt() );
					if ( $dara_fal ) {
						echo ' · ' . esc_html( sprintf( /* translators: %s: license. */ __( 'FAL %s', 'dara' ), $dara_fal ) );
					}
					?>
				</p>
				<div class="agent-profile__actions">
					<?php if ( $dara_phone ) : ?>
						<a class="btn btn--primary" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $dara_phone ) ); ?>"><?php dara_the_icon( 'phone', 18 ); ?><span dir="ltr"><?php echo esc_html( $dara_phone ); ?></span></a>
					<?php endif; ?>
					<?php if ( $dara_wa ) : ?>
						<a class="btn btn--outline" href="<?php echo esc_url( dara_whatsapp_url( $dara_wa ) ); ?>" target="_blank" rel="noopener"><?php dara_the_icon( 'whatsapp', 18 ); ?><?php esc_html_e( 'WhatsApp', 'dara' ); ?></a>
					<?php endif; ?>
					<?php if ( $dara_email ) : ?>
						<a class="btn btn--outline" href="mailto:<?php echo esc_attr( antispambot( $dara_email ) ); ?>"><?php dara_the_icon( 'mail', 18 ); ?><?php esc_html_e( 'Email', 'dara' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="container section section--top-0 agent-layout">
		<div class="agent-layout__main">
			<?php if ( '' !== trim( get_the_content() ) ) : ?>
				<section class="agent-bio">
					<h2><?php esc_html_e( 'About the agent', 'dara' ); ?></h2>
					<div class="entry-content"><?php the_content(); ?></div>
				</section>
			<?php endif; ?>

			<section>
				<h2 class="section-title agent-layout__title">
					<?php echo esc_html( sprintf( /* translators: %s: number. */ _n( '%s property', '%s properties', (int) $dara_props->found_posts, 'dara' ), number_format_i18n( (int) $dara_props->found_posts ) ) ); ?>
				</h2>
				<?php if ( $dara_props->have_posts() ) : ?>
					<div class="grid grid--cards">
						<?php
						while ( $dara_props->have_posts() ) :
							$dara_props->the_post();
							get_template_part( 'template-parts/cards/property' );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
					<?php
					$dara_links = paginate_links(
						array(
							'total'   => $dara_props->max_num_pages,
							'current' => $dara_paged,
							'type'    => 'plain',
						)
					);
					if ( $dara_links ) {
						echo '<nav class="navigation pagination" aria-label="' . esc_attr__( 'Pages', 'dara' ) . '"><div class="nav-links">' . wp_kses_post( $dara_links ) . '</div></nav>';
					}
					?>
				<?php else : ?>
					<p class="empty"><?php esc_html_e( 'No properties listed yet.', 'dara' ); ?></p>
				<?php endif; ?>
			</section>
		</div>

		<aside class="agent-layout__side">
			<form class="form form--card" id="lead-form" method="post" action="<?php echo esc_url( dara_lead_action() ); ?>">
				<h2 class="form__title"><?php echo esc_html( sprintf( /* translators: %s: agent name. */ __( 'Message %s', 'dara' ), get_the_title() ) ); ?></h2>
				<?php echo dara_lead_notice(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php dara_lead_hidden_fields( 'contact', $dara_id ); ?>
				<label class="form__field"><span><?php esc_html_e( 'Name', 'dara' ); ?></span><input type="text" name="lead_name" required autocomplete="name"></label>
				<label class="form__field"><span><?php esc_html_e( 'Mobile number', 'dara' ); ?></span><input type="tel" name="lead_phone" required dir="ltr" inputmode="tel" autocomplete="tel"></label>
				<label class="form__field"><span><?php esc_html_e( 'Message', 'dara' ); ?></span><textarea name="lead_message" rows="4"></textarea></label>
				<button type="submit" class="btn btn--primary btn--block"><?php esc_html_e( 'Send message', 'dara' ); ?></button>
			</form>
		</aside>
	</div>
	<?php
endwhile;

get_footer();
