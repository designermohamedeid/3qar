<?php
/**
 * Developer profile: logo, about, contact and their projects.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();

$dara_term    = get_queried_object();
$dara_logo    = (int) get_term_meta( $dara_term->term_id, 'dara_logo', true );
$dara_founded = get_term_meta( $dara_term->term_id, 'dara_founded', true );
$dara_site    = get_term_meta( $dara_term->term_id, 'dara_website', true );
$dara_phone   = get_term_meta( $dara_term->term_id, 'dara_phone', true );
$dara_email   = get_term_meta( $dara_term->term_id, 'dara_email', true );
?>
<div class="page-head page-head--agent page-head--developer">
	<div class="container agent-profile">
		<span class="dev-logo dev-logo--xl">
			<?php
			echo $dara_logo // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() markup.
				? wp_get_attachment_image(
					$dara_logo,
					'medium',
					false,
					array(
						'alt'     => '',
						'loading' => 'eager',
					)
				)
				: esc_html( dara_initials( $dara_term->name ) );
			?>
		</span>
		<div class="agent-profile__info">
			<?php dara_breadcrumbs(); ?>
			<h1 class="page-head__title"><?php echo esc_html( $dara_term->name ); ?></h1>
			<p class="agent-profile__role">
				<?php
				$dara_meta = array( sprintf( /* translators: %s: number of projects. */ _n( '%s project', '%s projects', (int) $dara_term->count, 'dara' ), number_format_i18n( (int) $dara_term->count ) ) );
				if ( $dara_founded ) {
					/* translators: %s: year. */
					$dara_meta[] = sprintf( __( 'Since %s', 'dara' ), $dara_founded );
				}
				echo esc_html( implode( ' · ', $dara_meta ) );
				?>
			</p>
			<div class="agent-profile__actions">
				<?php if ( $dara_phone ) : ?>
					<a class="btn btn--primary" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $dara_phone ) ); ?>"><?php dara_the_icon( 'phone', 18 ); ?><span dir="ltr"><?php echo esc_html( $dara_phone ); ?></span></a>
				<?php endif; ?>
				<?php if ( $dara_site ) : ?>
					<a class="btn btn--outline" href="<?php echo esc_url( $dara_site ); ?>" target="_blank" rel="noopener"><?php dara_the_icon( 'globe', 18 ); ?><?php esc_html_e( 'Website', 'dara' ); ?></a>
				<?php endif; ?>
				<?php if ( $dara_email ) : ?>
					<a class="btn btn--outline" href="mailto:<?php echo esc_attr( antispambot( $dara_email ) ); ?>"><?php dara_the_icon( 'mail', 18 ); ?><?php esc_html_e( 'Email', 'dara' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<div class="container section section--top-0 developer-layout">
	<?php if ( '' !== trim( $dara_term->description ) ) : ?>
		<section class="agent-bio">
			<h2><?php esc_html_e( 'About the developer', 'dara' ); ?></h2>
			<div class="entry-content"><?php echo wp_kses_post( wpautop( $dara_term->description ) ); ?></div>
		</section>
	<?php endif; ?>

	<section>
		<h2 class="section-title agent-layout__title"><?php esc_html_e( 'Projects', 'dara' ); ?></h2>
		<?php if ( have_posts() ) : ?>
			<div class="grid grid--projects grid--projects-light">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/cards/project' );
				endwhile;
				?>
			</div>
			<?php dara_pagination(); ?>
		<?php else : ?>
			<p class="empty"><?php esc_html_e( 'No projects listed yet.', 'dara' ); ?></p>
		<?php endif; ?>
	</section>
</div>
<?php
get_footer();
