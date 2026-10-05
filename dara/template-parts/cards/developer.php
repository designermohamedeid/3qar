<?php
/**
 * Developer card.
 *
 * @package Dara
 *
 * @var array $args { term: WP_Term }
 */

defined( 'ABSPATH' ) || exit;

$dara_term    = $args['term'];
$dara_logo    = (int) get_term_meta( $dara_term->term_id, 'dara_logo', true );
$dara_founded = get_term_meta( $dara_term->term_id, 'dara_founded', true );
?>
<article class="card card--developer">
	<a class="card--developer__link" href="<?php echo esc_url( get_term_link( $dara_term ) ); ?>">
		<span class="dev-logo">
			<?php
			echo $dara_logo // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() markup.
				? wp_get_attachment_image( $dara_logo, 'thumbnail', false, array( 'alt' => '' ) )
				: esc_html( dara_initials( $dara_term->name ) );
			?>
		</span>
		<span class="card--developer__body">
			<span class="card--developer__name"><?php echo esc_html( $dara_term->name ); ?></span>
			<span class="card--developer__meta">
				<?php
				echo esc_html( sprintf( /* translators: %s: number of projects. */ _n( '%s project', '%s projects', (int) $dara_term->count, 'dara' ), number_format_i18n( (int) $dara_term->count ) ) );
				if ( $dara_founded ) {
					echo ' · ' . esc_html( sprintf( /* translators: %s: year. */ __( 'Since %s', 'dara' ), $dara_founded ) );
				}
				?>
			</span>
		</span>
		<?php dara_the_icon( 'chevron-r', 20, 'flip-rtl' ); ?>
	</a>
</article>
