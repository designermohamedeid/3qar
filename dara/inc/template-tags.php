<?php
/**
 * Options and template helpers.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Customizer defaults (translatable, so a fresh Arabic site shows Arabic copy).
 *
 * @return array
 */
function dara_defaults() {
	static $d = null;
	if ( null !== $d ) {
		return $d;
	}
	$d = array(
		// Brand.
		'primary_color'       => '#0B6E5F',
		'ink_color'           => '#0E1A2B',
		'header_cta_text'     => __( 'List your property', 'dara' ),
		'header_cta_url'      => '',
		// Hero.
		'hero_image'          => 0,
		'hero_eyebrow'        => __( 'Properties for sale, rent and new developments', 'dara' ),
		'hero_title'          => __( 'Find your next property with confidence', 'dara' ),
		'hero_text'           => __( 'Verified listings with full details, clear prices and licensed agents who stay with you until the contract is signed.', 'dara' ),
		'hero_popular'        => '',
		// Featured.
		'show_featured'       => true,
		'featured_eyebrow'    => __( 'Hand-picked', 'dara' ),
		'featured_title'      => __( 'Latest featured properties', 'dara' ),
		'featured_count'      => 6,
		'featured_only'       => false,
		// Types.
		'show_types'          => true,
		'types_eyebrow'       => __( 'Browse by type', 'dara' ),
		'types_title'         => __( 'What are you looking for?', 'dara' ),
		// Projects.
		'show_projects'       => true,
		'projects_eyebrow'    => __( 'Developer projects', 'dara' ),
		'projects_title'      => __( 'New off-plan and under-construction projects', 'dara' ),
		'projects_text'       => __( 'Follow construction progress, see available units and payment plans, and register your interest directly.', 'dara' ),
		'projects_count'      => 2,
		// Services.
		'show_services'       => true,
		'services_eyebrow'    => __( 'Our services', 'dara' ),
		'services_title'      => __( 'Everything your property needs, in one place', 'dara' ),
		'services_items'      => implode(
			"\n",
			array(
				'megaphone | ' . __( 'Real estate marketing', 'dara' ) . ' | ' . __( 'Professional photography and digital campaigns that reach the right buyer fast.', 'dara' ) . ' | ',
				'key | ' . __( 'Property management', 'dara' ) . ' | ' . __( 'Rent collection, maintenance and renewals with clear periodic reports for owners.', 'dara' ) . ' | ',
				'chart | ' . __( 'Financing solutions', 'dara' ) . ' | ' . __( 'We compare mortgage offers and guide you through the process until approval.', 'dara' ) . ' | ',
				'clipboard | ' . __( 'Property valuation', 'dara' ) . ' | ' . __( 'Certified valuation of the fair market value before you buy or sell.', 'dara' ) . ' | ',
			)
		),
		// Why us.
		'show_why'            => true,
		'why_image'           => 0,
		'why_eyebrow'         => __( 'Why us?', 'dara' ),
		'why_title'           => __( 'A clear real estate experience from search to handover', 'dara' ),
		'why_text'            => __( 'We show every detail you need to decide with confidence, and arrange the viewing, negotiation and paperwork for you.', 'dara' ),
		'why_points'          => implode(
			"\n",
			array(
				__( 'Listings inspected and verified', 'dara' ),
				__( 'Published prices with no hidden fees', 'dara' ),
				__( 'A dedicated agent on phone and WhatsApp', 'dara' ),
				__( 'Help with financing, documentation and transfer', 'dara' ),
			)
		),
		'why_badge_title'     => __( 'Licensed listings', 'dara' ),
		'why_badge_text'      => __( 'A license number on every ad', 'dara' ),
		'why_btn_text'        => __( 'Talk to an advisor', 'dara' ),
		'why_btn_url'         => '',
		// Blog.
		'show_blog'           => true,
		'blog_eyebrow'        => __( 'Blog', 'dara' ),
		'blog_title'          => __( 'Your guide to the property market', 'dara' ),
		'blog_count'          => 3,
		// CTA.
		'show_cta'            => true,
		'cta_title'           => __( 'Own a property you want to sell or rent?', 'dara' ),
		'cta_text'            => __( 'Send us the details and an agent will contact you within one business day to arrange photos and pricing.', 'dara' ),
		'cta_btn_text'        => __( 'List your property', 'dara' ),
		'cta_btn_url'         => '',
		// Contact.
		'phone'               => '',
		'whatsapp'            => '',
		'email'               => '',
		'address'             => '',
		'map_lat'             => '',
		'map_lng'             => '',
		// Social.
		'social_x'            => '',
		'social_instagram'    => '',
		'social_snapchat'     => '',
		'social_tiktok'       => '',
		'social_youtube'      => '',
		'social_linkedin'     => '',
		'social_facebook'     => '',
		// Footer.
		'footer_about'        => __( 'We help you buy, sell, rent and manage property, and present developer projects transparently from the first visit to the signed contract.', 'dara' ),
		'newsletter_show'     => true,
		'newsletter_text'     => __( 'Get the newest properties and projects first.', 'dara' ),
		/* translators: Keep {year} and {site}. */
		'copyright'           => __( '© {year} {site}. All rights reserved.', 'dara' ),
		// Mortgage calculator.
		'mortgage_show'       => true,
		'mortgage_method'     => 'amortized',
		'mortgage_rate'       => '5.5',
		'mortgage_down'       => 10,
		'mortgage_years'      => 25,
		'mortgage_max_years'  => 30,
		'mortgage_note'       => __( 'Estimate only. The final installment depends on the bank, your salary and your obligations.', 'dara' ),
		// Performance.
		'perf_emoji'          => true,
		'perf_block_css'      => true,
		'map_tiles'           => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
		'map_attribution'     => '&copy; OpenStreetMap',
	);
	return $d;
}

/**
 * Theme option with default.
 *
 * @param string $key Key.
 * @return mixed
 */
function dara_mod( $key ) {
	$d = dara_defaults();
	return get_theme_mod( $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
}

/**
 * Whether the Dara Core plugin is active.
 *
 * @return bool
 */
function dara_has_core() {
	return function_exists( 'dara_price_parts' ) && post_type_exists( 'dara_property' );
}

/**
 * Transparent header over a hero image?
 *
 * @return bool
 */
function dara_header_is_overlay() {
	if ( is_singular( 'dara_project' ) ) {
		return true;
	}
	$id = get_queried_object_id();
	if ( is_front_page() && ! ( 'page' === get_option( 'show_on_front' ) && dara_has_dara_blocks( $id ) ) ) {
		return true; // Customizer home starts with the hero.
	}
	return is_singular( 'page' ) && 'dara/hero' === dara_first_block( $id );
}

/**
 * Lines "a | b | c" from a theme mod.
 *
 * @param string $key     Mod key.
 * @param int    $columns Columns.
 * @return array[]
 */
function dara_mod_lines( $key, $columns = 1 ) {
	$rows = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) dara_mod( $key ) ) as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$rows[] = array_pad( array_map( 'trim', explode( '|', $line ) ), $columns, '' );
		}
	}
	return $rows;
}

/**
 * Site logo (custom logo or text mark).
 *
 * @param bool $light Light version for dark backgrounds.
 */
function dara_logo( $light = false ) {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	printf(
		'<a class="logo%1$s" href="%2$s" rel="home"><span class="logo__mark">%3$s</span><span class="logo__text">%4$s</span></a>',
		$light ? ' logo--light' : '',
		esc_url( home_url( '/' ) ),
		dara_icon( 'logo', 26 ), // phpcs:ignore WordPress.Security.EscapeOutput
		esc_html( get_bloginfo( 'name' ) )
	);
}

/**
 * Responsive image helper.
 *
 * @param int    $id    Attachment ID.
 * @param string $size  Size.
 * @param array  $attr  Attributes.
 * @return string
 */
function dara_img( $id, $size = 'dara-card', $attr = array() ) {
	if ( ! $id ) {
		return '<span class="img-placeholder" aria-hidden="true">' . dara_icon( 'image', 32 ) . '</span>';
	}
	$attr = array_merge(
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
		),
		$attr
	);
	return wp_get_attachment_image( $id, $size, false, $attr );
}

/**
 * Social networks.
 *
 * @return array key => label
 */
function dara_social_networks() {
	return array(
		'x'         => 'X',
		'instagram' => 'Instagram',
		'snapchat'  => 'Snapchat',
		'tiktok'    => 'TikTok',
		'youtube'   => 'YouTube',
		'linkedin'  => 'LinkedIn',
		'facebook'  => 'Facebook',
	);
}

/**
 * Social links list.
 */
function dara_social_links() {
	$out = '';
	foreach ( dara_social_networks() as $key => $label ) {
		$url = dara_mod( 'social_' . $key );
		if ( $url ) {
			$out .= sprintf( '<a href="%1$s" target="_blank" rel="noopener" aria-label="%2$s">%3$s</a>', esc_url( $url ), esc_attr( $label ), dara_icon( $key, 18 ) );
		}
	}
	if ( dara_mod( 'whatsapp' ) ) {
		$out .= sprintf( '<a href="%1$s" target="_blank" rel="noopener" aria-label="WhatsApp">%2$s</a>', esc_url( 'https://wa.me/' . preg_replace( '/\D+/', '', dara_mod( 'whatsapp' ) ) ), dara_icon( 'whatsapp', 18 ) );
	}
	if ( $out ) {
		echo '<div class="social">' . $out . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
	}
}

/**
 * Other language links (Polylang / WPML).
 *
 * @return array
 */
function dara_languages() {
	$langs = array();
	if ( function_exists( 'pll_the_languages' ) ) {
		foreach ( (array) pll_the_languages( array( 'raw' => 1 ) ) as $l ) {
			if ( empty( $l['current_lang'] ) ) {
				$langs[] = array( 'url' => $l['url'], 'label' => strtoupper( $l['slug'] ), 'name' => $l['name'] );
			}
		}
	} elseif ( has_filter( 'wpml_active_languages' ) ) {
		foreach ( (array) apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) ) as $l ) {
			if ( empty( $l['active'] ) ) {
				$langs[] = array( 'url' => $l['url'], 'label' => strtoupper( $l['code'] ), 'name' => $l['native_name'] );
			}
		}
	}
	return $langs;
}

/**
 * Page title for the inner title bar.
 *
 * @return string
 */
function dara_page_title() {
	if ( is_home() ) {
		$id = (int) get_option( 'page_for_posts' );
		return $id ? get_the_title( $id ) : __( 'Blog', 'dara' );
	}
	if ( is_search() ) {
		/* translators: %s: search term. */
		return sprintf( __( 'Search: %s', 'dara' ), get_search_query() );
	}
	if ( is_404() ) {
		return __( 'Page not found', 'dara' );
	}
	if ( is_post_type_archive( 'dara_property' ) ) {
		return dara_listing_title();
	}
	if ( is_post_type_archive() ) {
		return post_type_archive_title( '', false );
	}
	if ( is_archive() ) {
		return wp_strip_all_tags( get_the_archive_title() );
	}
	return single_post_title( '', false );
}

/**
 * Descriptive title for property searches ("Villas for sale in Riyadh").
 *
 * @return string
 */
function dara_listing_title() {
	if ( ! dara_has_core() ) {
		return post_type_archive_title( '', false );
	}
	$f     = dara_get_filters();
	$title = post_type_archive_title( '', false );
	if ( $f['type'] ) {
		$term = get_term_by( 'slug', $f['type'][0], 'property_type' );
		if ( $term ) {
			$title = $term->name;
		}
	}
	if ( $f['purpose'] ) {
		$title .= ' ' . dara_purpose_label( $f['purpose'] );
	}
	if ( $f['location'] ) {
		/* translators: %s: location. */
		$title .= ' ' . sprintf( __( 'in %s', 'dara' ), $f['location'] );
	}
	return $title;
}

/**
 * Breadcrumbs (Yoast / Rank Math when present).
 */
function dara_breadcrumbs() {
	if ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb( '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'dara' ) . '">', '</nav>' );
		return;
	}
	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
		rank_math_the_breadcrumbs();
		return;
	}
	$items = array( array( home_url( '/' ), __( 'Home', 'dara' ) ) );
	if ( is_singular() && ! is_page() ) {
		$type = get_post_type();
		if ( 'post' === $type ) {
			$blog = (int) get_option( 'page_for_posts' );
			if ( $blog ) {
				$items[] = array( get_permalink( $blog ), get_the_title( $blog ) );
			}
		} else {
			$obj = get_post_type_object( $type );
			if ( $obj && $obj->has_archive ) {
				$items[] = array( get_post_type_archive_link( $type ), $obj->labels->name );
			}
		}
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $a ) {
			$items[] = array( get_permalink( $a ), get_the_title( $a ) );
		}
	} elseif ( is_tax( array( 'property_type', 'property_city' ) ) ) {
		$items[] = array( get_post_type_archive_link( 'dara_property' ), get_post_type_object( 'dara_property' )->labels->name );
	}
	$items[] = array( '', is_singular() ? get_the_title() : dara_page_title() );

	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'dara' ) . '"><ol>';
	foreach ( $items as $item ) {
		echo $item[0] ? '<li><a href="' . esc_url( $item[0] ) . '">' . esc_html( $item[1] ) . '</a></li>' : '<li aria-current="page">' . esc_html( $item[1] ) . '</li>';
	}
	echo '</ol></nav>';
}

/**
 * Pagination.
 */
function dara_pagination() {
	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'prev_text'          => dara_icon( 'chevron-r', 18, 'flip-ltr' ) . '<span class="screen-reader-text">' . __( 'Previous page', 'dara' ) . '</span>',
			'next_text'          => dara_icon( 'chevron-r', 18, 'flip-rtl' ) . '<span class="screen-reader-text">' . __( 'Next page', 'dara' ) . '</span>',
			'before_page_number' => '',
		)
	);
}

/**
 * Copyright line.
 *
 * @return string
 */
function dara_copyright() {
	return str_replace( array( '{year}', '{site}' ), array( wp_date( 'Y' ), get_bloginfo( 'name' ) ), dara_mod( 'copyright' ) );
}

/**
 * Archive link for properties, optionally filtered.
 *
 * @param array $args Query args.
 * @return string
 */
function dara_listings_url( $args = array() ) {
	$base = dara_has_core() ? get_post_type_archive_link( 'dara_property' ) : home_url( '/' );
	return $args ? add_query_arg( $args, $base ) : $base;
}

/**
 * Section heading.
 *
 * @param string $eyebrow Eyebrow.
 * @param string $title   Title.
 * @param string $text    Optional text.
 * @param string $class   Extra classes.
 */
function dara_section_head( $eyebrow, $title, $text = '', $class = '' ) {
	echo '<div class="section-head ' . esc_attr( $class ) . '">';
	if ( $eyebrow ) {
		echo '<span class="eyebrow">' . esc_html( $eyebrow ) . '</span>';
	}
	echo '<h2 class="section-title">' . esc_html( $title ) . '</h2>';
	if ( $text ) {
		echo '<p class="section-text">' . esc_html( $text ) . '</p>';
	}
	echo '</div>';
}

/**
 * URL of the first page using a page template (cached).
 *
 * @param string $template e.g. page-templates/favorites.php.
 * @return string
 */
function dara_template_url( $template ) {
	$map = get_transient( 'dara_template_pages' );
	if ( false === $map ) {
		$map = array();
		$ids = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
				'posts_per_page' => 50,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		foreach ( $ids as $id ) {
			$slug = get_page_template_slug( $id );
			if ( $slug && ! isset( $map[ $slug ] ) ) {
				$map[ $slug ] = $id;
			}
		}
		set_transient( 'dara_template_pages', $map, DAY_IN_SECONDS );
	}
	return isset( $map[ $template ] ) ? get_permalink( $map[ $template ] ) : '';
}

/**
 * Reset the template page cache when pages change.
 */
function dara_flush_template_pages() {
	delete_transient( 'dara_template_pages' );
}
add_action( 'save_post_page', 'dara_flush_template_pages' );
add_action( 'deleted_post', 'dara_flush_template_pages' );

/**
 * Header / CTA "List your property" link.
 *
 * @param string $mod Theme mod holding a custom URL.
 * @return string
 */
function dara_list_property_url( $mod = 'header_cta_url' ) {
	if ( dara_mod( $mod ) ) {
		return dara_mod( $mod );
	}
	$url = dara_template_url( 'page-templates/list-property.php' );
	return $url ? $url : dara_listings_url();
}

/**
 * Section option: block attribute when given, Customizer value otherwise.
 *
 * @param array|null $args Template part args.
 * @param string     $key  Option key.
 * @return mixed
 */
function dara_part_opt( $args, $key ) {
	if ( is_array( $args ) && array_key_exists( $key, $args ) && null !== $args[ $key ] ) {
		return $args[ $key ];
	}
	return dara_mod( $key );
}

/**
 * "a | b | c" lines from a block attribute or a theme mod.
 *
 * @param array|null $args    Template part args.
 * @param string     $key     Option key.
 * @param int        $columns Columns.
 * @return array[]
 */
function dara_part_lines( $args, $key, $columns = 1 ) {
	$rows = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) dara_part_opt( $args, $key ) ) as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$rows[] = array_pad( array_map( 'trim', explode( '|', $line ) ), $columns, '' );
		}
	}
	return $rows;
}

/**
 * Whether a post's content uses Dara blocks.
 *
 * @param int|WP_Post|null $post Post.
 * @return bool
 */
function dara_has_dara_blocks( $post = null ) {
	$post = get_post( $post );
	return $post && false !== strpos( $post->post_content, '<!-- wp:dara/' );
}

/**
 * Name of the first block in a post.
 *
 * @param int|WP_Post|null $post Post.
 * @return string
 */
function dara_first_block( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || ! has_blocks( $post ) ) {
		return '';
	}
	foreach ( parse_blocks( $post->post_content ) as $block ) {
		if ( ! empty( $block['blockName'] ) ) {
			return $block['blockName'];
		}
	}
	return '';
}

/**
 * Mortgage calculation (mirrors the JS in main.js).
 *
 * @param float  $price  Property price.
 * @param float  $down   Down payment percent.
 * @param int    $years  Term in years.
 * @param float  $rate   Annual profit rate percent.
 * @param string $method amortized|flat.
 * @return array monthly, loan, down, profit, total
 */
function dara_mortgage_calc( $price, $down, $years, $rate, $method = 'amortized' ) {
	$price  = max( 0, (float) $price );
	$downv  = $price * min( 100, max( 0, (float) $down ) ) / 100;
	$loan   = $price - $downv;
	$n      = max( 1, (int) $years ) * 12;
	$r      = max( 0, (float) $rate ) / 100;
	if ( 'flat' === $method ) {
		$profit  = $loan * $r * ( $n / 12 );
		$monthly = ( $loan + $profit ) / $n;
	} else {
		$m       = $r / 12;
		$monthly = $m > 0 ? $loan * $m / ( 1 - pow( 1 + $m, -$n ) ) : $loan / $n;
		$profit  = $monthly * $n - $loan;
	}
	return array(
		'monthly' => $monthly,
		'loan'    => $loan,
		'down'    => $downv,
		'profit'  => max( 0, $profit ),
		'total'   => $downv + $loan + max( 0, $profit ),
	);
}

/**
 * Compare page URL ('' when no page uses the Compare template).
 *
 * @return string
 */
function dara_compare_url() {
	return dara_template_url( 'page-templates/compare.php' );
}

/**
 * Add-to-compare button.
 *
 * @param int    $post_id Property ID.
 * @param string $variant "card" (round icon) or "inline" (labelled button).
 */
function dara_compare_button( $post_id, $variant = 'card' ) {
	if ( ! dara_compare_url() ) {
		return;
	}
	$title = get_the_title( $post_id );
	$img   = get_the_post_thumbnail_url( $post_id, 'thumbnail' );
	/* translators: %s: property title. */
	$label = sprintf( __( 'Compare %s', 'dara' ), $title );
	printf(
		'<button type="button" class="%1$s" data-compare="%2$d" data-title="%3$s" data-img="%4$s" aria-pressed="false" aria-label="%5$s">%6$s%7$s</button>',
		'inline' === $variant ? 'btn btn--outline btn--sm cmp-btn--inline' : 'cmp-btn',
		(int) $post_id,
		esc_attr( $title ),
		esc_url( $img ? $img : '' ),
		esc_attr( $label ),
		dara_icon( 'compare', 'inline' === $variant ? 18 : 20 ), // phpcs:ignore WordPress.Security.EscapeOutput
		'inline' === $variant ? esc_html__( 'Compare', 'dara' ) : ''
	);
}
