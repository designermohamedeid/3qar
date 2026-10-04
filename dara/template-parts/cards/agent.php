<?php
/**
 * Agent card.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

$dara_id    = get_the_ID();
$dara_phone = get_post_meta( $dara_id, '_dara_phone', true );
$dara_wa    = get_post_meta( $dara_id, '_dara_whatsapp', true );
$dara_count = dara_agent_listing_count( $dara_id );
?>
<article class="card card--agent">
	<div class="card--agent__head">
		<span class="avatar avatar--lg">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'thumbnail', array( 'alt' => '', 'loading' => 'lazy' ) );
			} else {
				echo esc_html( dara_initials( get_the_title() ) );
			}
			?>
		</span>
		<div>
			<h2 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<?php if ( has_excerpt() ) : ?>
				<p class="card__loc"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<ul class="card--agent__meta">
		<li><?php dara_the_icon( 'home', 18 ); ?><?php echo esc_html( sprintf( /* translators: %s: number. */ _n( '%s property', '%s properties', $dara_count, 'dara' ), number_format_i18n( $dara_count ) ) ); ?></li>
		<?php if ( get_post_meta( $dara_id, '_dara_fal', true ) ) : ?>
			<li><?php dara_the_icon( 'shield', 18 ); ?><?php echo esc_html( sprintf( /* translators: %s: license. */ __( 'FAL %s', 'dara' ), get_post_meta( $dara_id, '_dara_fal', true ) ) ); ?></li>
		<?php endif; ?>
	</ul>
	<div class="card--agent__actions">
		<?php if ( $dara_phone ) : ?>
			<a class="btn btn--primary btn--sm" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $dara_phone ) ); ?>"><?php dara_the_icon( 'phone', 18 ); ?><?php esc_html_e( 'Call', 'dara' ); ?></a>
		<?php endif; ?>
		<?php if ( $dara_wa ) : ?>
			<a class="btn btn--outline btn--sm" href="<?php echo esc_url( dara_whatsapp_url( $dara_wa ) ); ?>" target="_blank" rel="noopener"><?php dara_the_icon( 'whatsapp', 18 ); ?><?php esc_html_e( 'WhatsApp', 'dara' ); ?></a>
		<?php endif; ?>
	</div>
</article>
