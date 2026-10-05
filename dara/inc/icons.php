<?php
/**
 * Inline SVG icon set (no icon font = no extra request, crisp at any size).
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Icon paths (24×24, stroke based).
 *
 * @return array
 */
function dara_icon_paths() {
	return array(
		'search'     => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'pin'        => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'bed'        => '<path d="M3 19v-8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8M3 15h18M7 9V6h5v3"/>',
		'bath'       => '<path d="M4 12h16v3a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4zM6 12V6a2 2 0 0 1 4 0"/>',
		'area'       => '<path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/>',
		'heart'      => '<path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/>',
		'phone'      => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
		'whatsapp'   => '<path d="M4 20l1.3-3.9A8 8 0 1 1 8 19z"/><path d="M9 9.5c.3 1.8 2.7 4.2 4.5 4.5l1-1.2 1.8.8c-.2 1.1-1 1.6-2 1.6-2.8 0-6-3.2-6-6 0-1 .5-1.8 1.6-2l.8 1.8z"/>',
		'mail'       => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'arrow'      => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'chevron'    => '<path d="m6 9 6 6 6-6"/>',
		'chevron-r'  => '<path d="m9 6 6 6-6 6"/>',
		'check'      => '<path d="m5 12 4 4 10-10"/>',
		'menu'       => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'      => '<path d="M6 6l12 12M18 6 6 18"/>',
		'plus'       => '<path d="M12 5v14M5 12h14"/>',
		'globe'      => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
		'share'      => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/>',
		'print'      => '<path d="M7 9V3h10v6M7 17H4v-7h16v7h-3M7 14h10v7H7z"/>',
		'camera'     => '<rect x="3" y="6" width="18" height="14" rx="2"/><circle cx="12" cy="13" r="3.5"/><path d="M8 6l1.5-2h5L16 6"/>',
		'image'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5-9 8"/>',
		'grid'       => '<rect x="4" y="4" width="7" height="7" rx="1"/><rect x="13" y="4" width="7" height="7" rx="1"/><rect x="4" y="13" width="7" height="7" rx="1"/><rect x="13" y="13" width="7" height="7" rx="1"/>',
		'map'        => '<path d="M9 4 3 6v14l6-2 6 2 6-2V4l-6 2z"/><path d="M9 4v14M15 6v14"/>',
		'filter'     => '<path d="M4 6h16M7 12h10M10 18h4"/>',
		'calendar'   => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
		'compass'    => '<circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5z"/>',
		'shield'     => '<path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/><path d="m9 12 2 2 4-4"/>',
		'download'   => '<path d="M12 4v12M7 11l5 5 5-5M5 20h14"/>',
		'play'       => '<circle cx="12" cy="12" r="9"/><path d="m10 8.5 5.5 3.5-5.5 3.5z"/>',
		'user'       => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
		'home'       => '<path d="M3 11 12 4l9 7M5 10v10h14V10M10 20v-5h4v5"/>',
		'villa'      => '<path d="M3 11 12 4l9 7M5 10v10h14V10M10 20v-5h4v5"/>',
		'apartment'  => '<rect x="5" y="3" width="14" height="18" rx="1.5"/><path d="M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1"/>',
		'land'       => '<path d="M3 17l6-4 4 3 8-6M3 21h18"/>',
		'office'     => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5h6v2M3 12h18"/>',
		'building'   => '<path d="M3 21V9l6-4v16M9 21V3l12 4v14M13 9h4M13 13h4M13 17h4"/>',
		'rest-house' => '<path d="M4 21V11l8-6 8 6v10M4 21h16M9 21v-4h6v4M12 10v.01"/>',
		'megaphone'  => '<path d="M3 11v3l12 5V6L3 11zM15 9a4 4 0 0 1 0 6M7 15v4"/>',
		'key'        => '<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M17 6l3 3"/>',
		'chart'      => '<path d="M4 20V10M10 20V4M16 20v-7M21 20H3"/>',
		'clipboard'  => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M9 11h6M9 15h4"/>',
		'x'          => '<path d="M5 4l14 16M19 4 5 20"/>',
		'instagram'  => '<rect x="4" y="4" width="16" height="16" rx="5"/><circle cx="12" cy="12" r="3.5"/><path d="M16.5 7.5h0"/>',
		'snapchat'   => '<path d="M12 3c3 0 5 2.2 5 5v3l2 1-2 1.5c.5 1.5 1.8 2.5 3 2.8-.5 1-2 1-3 1.2L16 20c-1.5-.4-2.5-.2-4 .8-1.5-1-2.5-1.2-4-.8l-1-2.5c-1-.2-2.5-.2-3-1.2 1.2-.3 2.5-1.3 3-2.8L5 12l2-1V8c0-2.8 2-5 5-5z"/>',
		'tiktok'     => '<path d="M14 3v11.5a3.5 3.5 0 1 1-3.5-3.5M14 3c.5 2.5 2.3 4 5 4"/>',
		'youtube'    => '<rect x="3" y="6" width="18" height="12" rx="4"/><path d="m10 9.5 4.5 2.5-4.5 2.5z"/>',
		'linkedin'   => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10v7M8 7v.01M12 17v-4a2 2 0 0 1 4 0v4M12 10v7"/>',
		'facebook'   => '<path d="M14 21v-8h3l.5-3.5H14V8c0-1 .5-1.5 1.5-1.5H18V3.3C17.5 3.2 16.3 3 15 3c-2.8 0-4.5 1.7-4.5 4.6v1.9H7.5V13h3v8"/>',
		'compare'    => '<path d="M7 4 3 8l4 4M3 8h13M17 12l4 4-4 4M21 16H8"/>',
		'logo'       => '<path d="M4 20V10l8-6 8 6v10"/><path d="M9 20v-5a3 3 0 0 1 6 0v5"/>',
	);
}

/**
 * Return an inline SVG icon.
 *
 * @param string $name  Icon name.
 * @param int    $size  Pixel size.
 * @param string $class Extra classes ("flip" mirrors it in RTL).
 * @return string
 */
function dara_icon( $name, $size = 20, $class = '' ) {
	$paths = dara_icon_paths();
	if ( ! isset( $paths[ $name ] ) ) {
		$name = 'home';
	}
	return sprintf(
		'<svg class="icon icon-%1$s %2$s" width="%3$d" height="%3$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%4$s</svg>',
		esc_attr( $name ),
		esc_attr( $class ),
		(int) $size,
		$paths[ $name ] // Static, trusted markup.
	);
}

/**
 * Echo an icon.
 *
 * @param string $name  Icon.
 * @param int    $size  Size.
 * @param string $class Class.
 */
function dara_the_icon( $name, $size = 20, $class = '' ) {
	echo dara_icon( $name, $size, $class ); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG.
}
