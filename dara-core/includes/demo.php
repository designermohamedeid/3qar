<?php
/**
 * One-click demo content: Properties > Demo content.
 *
 * Everything created is tagged with the `_dara_demo` meta so it can be removed
 * with one click. Images are original illustrations bundled with the plugin.
 *
 * @package DaraCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin page.
 */
function dara_demo_menu() {
	add_submenu_page(
		'edit.php?post_type=dara_property',
		__( 'Demo content', 'dara-core' ),
		__( 'Demo content', 'dara-core' ),
		'manage_options',
		'dara-demo',
		'dara_demo_page'
	);
}
add_action( 'admin_menu', 'dara_demo_menu' );

/**
 * Render the admin page.
 */
function dara_demo_page() {
	$imported = get_option( 'dara_demo_imported' );
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	$done = isset( $_GET['done'] ) ? sanitize_key( $_GET['done'] ) : '';
	echo '<div class="wrap"><h1>' . esc_html__( 'Demo content', 'dara-core' ) . '</h1>';

	if ( 'imported' === $done ) {
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Demo content imported. Visit your site to see it.', 'dara-core' ) . ' <a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'View site', 'dara-core' ) . '</a></p></div>';
	} elseif ( 'removed' === $done ) {
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Demo content removed.', 'dara-core' ) . '</p></div>';
	}

	echo '<p style="max-width:720px">' . esc_html__( 'Fill your site with sample properties, projects, agents, blog posts, pages, menus and home page settings so it looks like the theme demo. You can remove all of it later with one click; your own content is never touched.', 'dara-core' ) . '</p>';

	if ( $imported ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'dara_demo' );
		echo '<input type="hidden" name="action" value="dara_demo_remove">';
		submit_button( __( 'Remove demo content', 'dara-core' ), 'delete' );
		echo '</form>';
	} else {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'dara_demo' );
		echo '<input type="hidden" name="action" value="dara_demo_import">';
		echo '<p><label for="dara-demo-lang"><strong>' . esc_html__( 'Content language', 'dara-core' ) . '</strong></label><br><select id="dara-demo-lang" name="lang">';
		$is_ar = 0 === strpos( get_locale(), 'ar' );
		echo '<option value="ar"' . selected( $is_ar, true, false ) . '>العربية</option>';
		echo '<option value="en"' . selected( $is_ar, false, false ) . '>English</option>';
		echo '</select></p>';
		echo '<p><label><input type="checkbox" name="set_front" value="1" checked> ' . esc_html__( 'Set the demo home page and blog page in Settings > Reading', 'dara-core' ) . '</label></p>';
		echo '<p><label><input type="checkbox" name="set_menus" value="1" checked> ' . esc_html__( 'Create and assign menus', 'dara-core' ) . '</label></p>';
		submit_button( __( 'Import demo content', 'dara-core' ) );
		echo '</form>';
	}
	echo '</div>';
}

/**
 * Translate a pair.
 *
 * @param string $ar Arabic.
 * @param string $en English.
 * @return string
 */
function dara_demo_t( $ar, $en ) {
	return 'ar' === $GLOBALS['dara_demo_lang'] ? $ar : $en;
}

/**
 * Import a bundled image into the media library (cached per run).
 *
 * @param string $name File name without extension.
 * @return int Attachment ID.
 */
function dara_demo_image( $name ) {
	static $cache = array();
	if ( isset( $cache[ $name ] ) ) {
		return $cache[ $name ];
	}
	$src = DARA_CORE_DIR . 'demo/images/' . $name . '.jpg';
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( $name . '.jpg' );
	copy( $src, $tmp );
	$id = media_handle_sideload(
		array(
			'name'     => 'dara-demo-' . $name . '.jpg',
			'tmp_name' => $tmp,
		),
		0
	);
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return 0;
	}
	update_post_meta( $id, '_dara_demo', 1 );
	$cache[ $name ] = (int) $id;
	return $cache[ $name ];
}

/**
 * Create a post tagged as demo.
 *
 * @param array $args wp_insert_post args.
 * @param array $meta Meta.
 * @return int
 */
function dara_demo_post( $args, $meta = array() ) {
	$args = array_merge( array( 'post_status' => 'publish' ), $args );
	$id   = wp_insert_post( wp_slash( $args ) );
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	update_post_meta( $id, '_dara_demo', 1 );
	foreach ( $meta as $key => $value ) {
		if ( '' !== $value && null !== $value ) {
			update_post_meta( $id, $key, $value );
		}
	}
	return (int) $id;
}

/**
 * Create (or reuse) a term tagged as demo.
 *
 * @param string $name     Name.
 * @param string $taxonomy Taxonomy.
 * @param array  $args     Args.
 * @return int
 */
function dara_demo_term( $name, $taxonomy, $args = array() ) {
	$parent = isset( $args['parent'] ) ? (int) $args['parent'] : 0;
	$exists = term_exists( $name, $taxonomy, $parent );
	if ( $exists ) {
		return (int) $exists['term_id'];
	}
	$t = wp_insert_term( $name, $taxonomy, $args );
	if ( is_wp_error( $t ) ) {
		return 0;
	}
	update_term_meta( $t['term_id'], '_dara_demo', 1 );
	return (int) $t['term_id'];
}

/**
 * Run the import.
 */
function dara_demo_import() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'dara_demo' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'dara-core' ) );
	}
	if ( get_option( 'dara_demo_imported' ) ) {
		wp_safe_redirect( admin_url( 'edit.php?post_type=dara_property&page=dara-demo' ) );
		exit;
	}
	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 300 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions
	}
	$GLOBALS['dara_demo_lang'] = isset( $_POST['lang'] ) && 'en' === $_POST['lang'] ? 'en' : 'ar';
	$t                         = 'dara_demo_t';

	// Taxonomies.
	$types = array();
	foreach ( array(
		'villa'      => array( 'فلل', 'Villas' ),
		'apartment'  => array( 'شقق', 'Apartments' ),
		'land'       => array( 'أراضٍ', 'Land' ),
		'office'     => array( 'مكاتب تجارية', 'Offices' ),
		'building'   => array( 'عمائر', 'Buildings' ),
		'rest-house' => array( 'استراحات', 'Rest houses' ),
	) as $icon => $names ) {
		$types[ $icon ] = dara_demo_term( $t( $names[0], $names[1] ), 'property_type', array( 'slug' => $icon ) );
		update_term_meta( $types[ $icon ], 'dara_icon', $icon );
	}

	$districts = array();
	foreach ( array(
		array( array( 'الرياض', 'Riyadh' ), array( array( 'الملقا', 'Al Malqa' ), array( 'النرجس', 'An Narjis' ), array( 'العليا', 'Al Olaya' ), array( 'الياسمين', 'Al Yasmin' ) ) ),
		array( array( 'جدة', 'Jeddah' ), array( array( 'الشاطئ', 'Ash Shati' ), array( 'أبحر الشمالية', 'North Obhur' ) ) ),
	) as $city ) {
		$city_id = dara_demo_term( $t( $city[0][0], $city[0][1] ), 'property_city' );
		foreach ( $city[1] as $d ) {
			$districts[ $d[1] ] = dara_demo_term(
				$t( $d[0], $d[1] ),
				'property_city',
				array(
					'parent' => $city_id,
					'slug'   => sanitize_title( $city[0][1] . '-' . $d[1] ),
				)
			);
		}
	}

	$features = array();
	foreach ( array( array( 'مسبح خاص', 'Private pool' ), array( 'حديقة', 'Garden' ), array( 'مصعد', 'Elevator' ), array( 'موقف خاص', 'Private parking' ), array( 'تكييف مركزي', 'Central A/C' ), array( 'غرفة خادمة', "Maid's room" ), array( 'مفروش', 'Furnished' ), array( 'نظام منزل ذكي', 'Smart home' ) ) as $f ) {
		$features[] = dara_demo_term( $t( $f[0], $f[1] ), 'property_feature' );
	}

	// Agents.
	$agents = array(
		dara_demo_post(
			array(
				'post_type'    => 'dara_agent',
				'post_title'   => $t( 'سارة القحطاني', 'Sarah Al-Qahtani' ),
				'post_excerpt' => $t( 'مستشارة عقارية — فلل شمال الرياض', 'Property advisor — North Riyadh villas' ),
				'post_content' => $t( '<p>أكثر من 8 سنوات في تسويق الفلل والدوبلكسات شمال الرياض. ترافق عملاءها من أول معاينة حتى نقل الملكية.</p>', '<p>8+ years marketing villas and duplexes in North Riyadh, guiding clients from the first viewing to the transfer of ownership.</p>' ),
			),
			array(
				'_dara_phone'    => '+966 50 000 0001',
				'_dara_whatsapp' => '966500000001',
				'_dara_fal'      => '1100000001',
			)
		),
		dara_demo_post(
			array(
				'post_type'    => 'dara_agent',
				'post_title'   => $t( 'خالد العمري', 'Khalid Al-Omari' ),
				'post_excerpt' => $t( 'مسوّق عقاري — الشقق والتجاري', 'Agent — apartments & commercial' ),
				'post_content' => $t( '<p>متخصص في الشقق السكنية والمكاتب التجارية للبيع والإيجار في الرياض وجدة.</p>', '<p>Specialised in apartments and offices for sale and rent in Riyadh and Jeddah.</p>' ),
			),
			array(
				'_dara_phone'    => '+966 50 000 0002',
				'_dara_whatsapp' => '966500000002',
				'_dara_fal'      => '1100000002',
			)
		),
	);

	// Properties.
	$nearby       = $t( "مدرسة عالمية | 5 دقائق\nطريق الملك سلمان | 3 دقائق\nمركز تسوق | 8 دقائق\nمستشفى | 12 دقيقة", "International school | 5 min\nKing Salman Road | 3 min\nShopping mall | 8 min\nHospital | 12 min" );
	$desc         = $t(
		"<!-- wp:paragraph -->\n<p>عقار مميز بتصميم معاصر وتشطيبات حديثة، بمساحات واسعة ومطبخ مفتوح مجهز بالكامل، وجناح رئيسي بغرفة ملابس. قريب من المدارس والطرق الرئيسية والمراكز التجارية.</p>\n<!-- /wp:paragraph -->",
		"<!-- wp:paragraph -->\n<p>A contemporary property with modern finishes, generous spaces, a fully fitted open kitchen and a master suite with walk-in closet. Close to schools, main roads and shopping.</p>\n<!-- /wp:paragraph -->"
	);
	$props        = array(
		// title ar, title en, type, district, purpose, price, area, beds, baths, image, lat, lng, featured, agent.
		array( 'فيلا مودرن بحديقة ومسبح خاص', 'Modern villa with garden and private pool', 'villa', 'Al Malqa', 'sale', 3450000, 520, 5, 6, 'villa-2', 24.812, 46.612, 1, 0 ),
		array( 'شقة فاخرة بإطلالة مفتوحة', 'Luxury apartment with open views', 'apartment', 'Al Yasmin', 'sale', 1280000, 185, 3, 3, 'tower-1', 24.826, 46.648, 0, 1 ),
		array( 'دوبلكس عائلي جديد', 'New family duplex', 'villa', 'An Narjis', 'sale', 2150000, 360, 4, 5, 'villa-1', 24.857, 46.676, 1, 0 ),
		array( 'شقة مفروشة قريبة من المترو', 'Furnished apartment near the metro', 'apartment', 'Al Olaya', 'rent', 85000, 120, 2, 2, 'tower-2', 24.694, 46.685, 0, 1 ),
		array( 'فيلا درج داخلي بتشطيب حديث', 'Villa with internal staircase', 'villa', 'An Narjis', 'sale', 1950000, 400, 5, 5, 'villa-3', 24.861, 46.69, 0, 0 ),
		array( 'عمارة سكنية مؤجرة بالكامل', 'Fully leased residential building', 'building', 'Al Olaya', 'sale', 6800000, 900, 12, 12, 'building-1', 24.705, 46.672, 0, 1 ),
		array( 'شقة بإطلالة بحرية', 'Sea view apartment', 'apartment', 'Ash Shati', 'rent', 120000, 210, 3, 3, 'tower-3', 21.59, 39.11, 1, 1 ),
		array( 'فيلا زاوية بملحق', 'Corner villa with annex', 'villa', 'Al Malqa', 'sale', 2900000, 480, 6, 6, 'villa-4', 24.818, 46.62, 0, 0 ),
	);
	$gallery_pool = array( 'interior-1', 'villa-1', 'villa-2', 'villa-3', 'villa-4', 'tower-1', 'tower-3' );
	foreach ( $props as $i => $p ) {
		$gallery = array( dara_demo_image( 'interior-1' ) );
		foreach ( array_slice( array_values( array_diff( $gallery_pool, array( $p[9], 'interior-1' ) ) ), $i % 3, 3 ) as $img ) {
			$gallery[] = dara_demo_image( $img );
		}
		$id = dara_demo_post(
			array(
				'post_type'    => 'dara_property',
				'post_title'   => $t( $p[0], $p[1] ),
				'post_content' => $desc,
				'post_excerpt' => $t( 'عقار مميز بتشطيبات حديثة في موقع قريب من الخدمات.', 'Modern finishes in a well-connected location.' ),
				'post_date'    => gmdate( 'Y-m-d H:i:s', time() - $i * DAY_IN_SECONDS ),
			),
			array(
				'_dara_purpose'      => $p[4],
				'_dara_price'        => $p[5],
				'_dara_price_period' => 'rent' === $p[4] ? 'year' : '',
				'_dara_area'         => $p[6],
				'_dara_beds'         => $p[7],
				'_dara_baths'        => $p[8],
				'_dara_age'          => $t( 'جديد', 'New' ),
				'_dara_facing'       => array( 'north', 'east', 'corner', 'west' )[ $i % 4 ],
				'_dara_condition'    => 'ready',
				'_dara_featured'     => $p[12] ? '1' : '',
				'_dara_agent'        => $agents[ $p[13] ],
				'_dara_lat'          => $p[10],
				'_dara_lng'          => $p[11],
				'_dara_nearby'       => $nearby,
				'_dara_ad_license'   => '72000000' . str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ),
				'_dara_gallery'      => implode( ',', array_filter( $gallery ) ),
				'_dara_floorplans'   => 'villa' === $p[2] ? (string) dara_demo_image( 'floorplan-1' ) : '',
			)
		);
		set_post_thumbnail( $id, dara_demo_image( $p[9] ) );
		wp_set_object_terms( $id, $types[ $p[2] ], 'property_type' );
		wp_set_object_terms( $id, $districts[ $p[3] ], 'property_city' );
		wp_set_object_terms( $id, array_slice( $features, $i % 3, 5 ), 'property_feature' );
	}

	// Projects.
	$units      = $t(
		"A1 | شقة | 110 | 2 | 850000 | available\nA2 | شقة | 145 | 3 | 1120000 | available\nB1 | شقة | 165 | 3 | 1290000 | reserved\nC1 | شقة | 210 | 4 | 1640000 | available\nPH | بنتهاوس | 320 | 4 | 2900000 | sold",
		"A1 | Apartment | 110 | 2 | 850000 | available\nA2 | Apartment | 145 | 3 | 1120000 | available\nB1 | Apartment | 165 | 3 | 1290000 | reserved\nC1 | Apartment | 210 | 4 | 1640000 | available\nPH | Penthouse | 320 | 4 | 2900000 | sold"
	);
	$payment    = $t( "10% | عند الحجز | دفعة أولى لتثبيت الوحدة\n40% | أثناء الإنشاء | على دفعات حسب مراحل الإنجاز\n50% | عند الاستلام | نقداً أو عبر التمويل العقاري", "10% | On booking | Down payment to reserve the unit\n40% | During construction | Instalments by milestone\n50% | On handover | Cash or mortgage" );
	$projects   = array(
		array( 'أبراج الواحة', 'Al Waha Towers', 'tower-3', 'construction', 65, 850000, $t( 'الربع الرابع 2027', 'Q4 2027' ), 'Al Yasmin', 24.83, 46.64, $t( 'يناير 2025', 'Jan 2025' ) . ' | ' . $t( 'إطلاق المشروع', 'Launch' ) . " | done\n" . $t( 'يونيو 2025', 'Jun 2025' ) . ' | ' . $t( 'الحفر والأساسات', 'Foundations' ) . " | done\n" . $t( 'مارس 2026', 'Mar 2026' ) . ' | ' . $t( 'الهيكل الإنشائي', 'Structure' ) . " | current\n" . $t( 'مارس 2027', 'Mar 2027' ) . ' | ' . $t( 'التشطيبات والواجهات', 'Finishing & facades' ) . " | upcoming\n" . $t( 'ديسمبر 2027', 'Dec 2027' ) . ' | ' . $t( 'التسليم', 'Handover' ) . ' | upcoming' ),
		array( 'مجمع رُبى السكني', 'Ruba Residences', 'tower-1', 'offplan', 20, 1450000, $t( 'الربع الثاني 2028', 'Q2 2028' ), 'North Obhur', 21.75, 39.12, $t( 'مارس 2026', 'Mar 2026' ) . ' | ' . $t( 'إطلاق المشروع', 'Launch' ) . " | done\n" . $t( 'سبتمبر 2026', 'Sep 2026' ) . ' | ' . $t( 'الأساسات', 'Foundations' ) . " | current\n" . $t( 'يونيو 2027', 'Jun 2027' ) . ' | ' . $t( 'الهيكل الإنشائي', 'Structure' ) . " | upcoming\n" . $t( 'يونيو 2028', 'Jun 2028' ) . ' | ' . $t( 'التسليم', 'Handover' ) . ' | upcoming' ),
	);
	$developers = array();
	foreach ( array(
		array( 'الواحة للتطوير العقاري', 'Al Waha Development', 'developer-1', '2009', 'شركة تطوير سعودية متخصصة في المجمعات السكنية المتكاملة، سلّمت أكثر من 3,000 وحدة في الرياض والمنطقة الشرقية.', 'A Saudi developer of integrated residential communities that has delivered more than 3,000 homes in Riyadh and the Eastern Province.' ),
		array( 'رُبى العقارية', 'Ruba Real Estate', 'developer-2', '2014', 'مطوّر عقاري يركّز على المشاريع السكنية الساحلية في جدة، بتصاميم عصرية ومساحات خضراء واسعة.', 'A developer focused on coastal residential projects in Jeddah, with modern design and generous green spaces.' ),
	) as $i => $d ) {
		$term_id = dara_demo_term( $t( $d[0], $d[1] ), 'project_developer', array( 'description' => $t( $d[4], $d[5] ) ) );
		update_term_meta( $term_id, 'dara_logo', dara_demo_image( $d[2] ) );
		update_term_meta( $term_id, 'dara_founded', $d[3] );
		update_term_meta( $term_id, 'dara_website', 'https://example.com' );
		update_term_meta( $term_id, 'dara_phone', '+966 11 000 000' . ( $i + 1 ) );
		update_term_meta( $term_id, 'dara_email', 'sales' . ( $i + 1 ) . '@example.com' );
		$developers[] = $term_id;
	}

	foreach ( $projects as $pi => $p ) {
		$id = dara_demo_post(
			array(
				'post_type'    => 'dara_project',
				'post_title'   => $t( $p[0], $p[1] ),
				'post_content' => $t( '<p>أبراج سكنية حول ساحة مركزية خضراء، بوحدات من غرفتين إلى أربع غرف، ومرافق مشتركة تشمل نادياً رياضياً ومسبحاً ومنطقة ألعاب للأطفال، مع حراسة على مدار الساعة ومواقف سفلية.</p>', '<p>Residential towers around a central green plaza with 2–4 bedroom units and shared amenities: gym, pool, kids play area, 24/7 security and underground parking.</p>' ),
			),
			array(
				'_dara_status'      => $p[3],
				'_dara_progress'    => $p[4],
				'_dara_price_from'  => $p[5],
				'_dara_units_count' => 240,
				'_dara_unit_types'  => $t( 'شقق 2–4 غرف', 'Apartments 2–4 bedrooms' ),
				'_dara_delivery'    => $p[6],
				'_dara_wafi'        => '1',
				'_dara_lat'         => $p[8],
				'_dara_lng'         => $p[9],
				'_dara_amenities'   => $t( "نادي رياضي ومسبح\nمواقف سفلية\nحراسة 24 ساعة\nمساحات خضراء", "Gym and pool\nUnderground parking\n24/7 security\nGreen spaces" ),
				'_dara_units'       => $units,
				'_dara_payment'     => $payment,
				'_dara_phases'      => $p[10],
				'_dara_gallery'     => implode( ',', array( dara_demo_image( 'tower-2' ), dara_demo_image( 'interior-1' ), dara_demo_image( 'building-1' ) ) ),
			)
		);
		set_post_thumbnail( $id, dara_demo_image( $p[2] ) );
		wp_set_object_terms( $id, $districts[ $p[7] ], 'property_city' );
		wp_set_object_terms( $id, $developers[ $pi ], 'project_developer' );
	}

	// Blog posts.
	$cat = dara_demo_term( $t( 'السوق العقاري', 'Market' ), 'category' );
	foreach ( array(
		array( 'كيف تقرأ مؤشرات السوق العقاري قبل الشراء', 'How to read market indicators before buying', 'blog-1' ),
		array( 'دليلك للتمويل العقاري لأول مرة', 'Your first-time mortgage guide', 'blog-2' ),
		array( '7 أشياء تفحصها في معاينة الفيلا', '7 things to check when viewing a villa', 'blog-3' ),
	) as $i => $b ) {
		$id = dara_demo_post(
			array(
				'post_type'     => 'post',
				'post_title'    => $t( $b[0], $b[1] ),
				'post_content'  => $t( "<!-- wp:paragraph -->\n<p>نستعرض في هذا المقال أهم النقاط العملية التي تساعدك على اتخاذ قرار عقاري واضح ومدروس، مع أمثلة من السوق المحلي.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>ابدأ بتحديد احتياجك</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>حدّد الميزانية والموقع وعدد الغرف قبل البدء بالمعاينات، فذلك يوفّر وقتك ويجعل المقارنة أسهل.</p>\n<!-- /wp:paragraph -->", "<!-- wp:paragraph -->\n<p>This article walks through the practical points that help you make a clear, informed property decision, with examples from the local market.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>Start with your needs</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Set your budget, area and number of rooms before viewings; it saves time and makes comparing easier.</p>\n<!-- /wp:paragraph -->" ),
				'post_category' => array( $cat ),
				'post_date'     => gmdate( 'Y-m-d H:i:s', time() - ( $i + 1 ) * 3 * DAY_IN_SECONDS ),
			)
		);
		set_post_thumbnail( $id, dara_demo_image( $b[2] ) );
	}

	// Pages.
	$pages = array(
		'home'     => dara_demo_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $t( 'الرئيسية', 'Home' ),
				'post_name'    => 'home',
				'post_content' => dara_core_home_pattern_content(),
			)
		),
		'blog'     => dara_demo_post(
			array(
				'post_type'  => 'page',
				'post_title' => $t( 'المدونة', 'Blog' ),
				'post_name'  => 'blog',
			)
		),
		'contact'  => dara_demo_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $t( 'تواصل معنا', 'Contact us' ),
				'post_name'    => 'contact',
				'post_content' => $t( '<p>يسعدنا تواصلكم معنا، فريقنا متاح من الأحد إلى الخميس.</p>', '<p>We would love to hear from you. Our team is available Sunday to Thursday.</p>' ),
			),
			array( '_wp_page_template' => 'page-templates/contact.php' )
		),
		'list'     => dara_demo_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $t( 'أضف عقارك', 'List your property' ),
				'post_name'    => 'list-your-property',
				'post_content' => $t( '<p>أرسل بيانات عقارك وسيتواصل معك مسوّق خلال يوم عمل.</p>', '<p>Send us your property details and an agent will contact you within one business day.</p>' ),
			),
			array( '_wp_page_template' => 'page-templates/list-property.php' )
		),
		'favorite' => dara_demo_post(
			array(
				'post_type'  => 'page',
				'post_title' => $t( 'المفضلة', 'Favorites' ),
				'post_name'  => 'favorites',
			),
			array( '_wp_page_template' => 'page-templates/favorites.php' )
		),
		'compare'  => dara_demo_post(
			array(
				'post_type'  => 'page',
				'post_title' => $t( 'مقارنة العقارات', 'Compare properties' ),
				'post_name'  => 'compare',
			),
			array( '_wp_page_template' => 'page-templates/compare.php' )
		),
		'devs'     => dara_demo_post(
			array(
				'post_type'  => 'page',
				'post_title' => $t( 'المطوّرون العقاريون', 'Developers' ),
				'post_name'  => 'developers',
			),
			array( '_wp_page_template' => 'page-templates/developers.php' )
		),
	);
	delete_transient( 'dara_template_pages' );

	// Move the untouched WordPress sample post/page to the trash (restored on removal).
	$trashed = array();
	foreach ( array(
		'hello-world' => 'post',
		'sample-page' => 'page',
	) as $slug => $type ) {
		$sample = get_page_by_path( $slug, OBJECT, $type );
		if ( $sample && $sample->post_date === $sample->post_modified && wp_trash_post( $sample->ID ) ) {
			$trashed[] = $sample->ID;
		}
	}

	$backup = array(
		'show_on_front'  => get_option( 'show_on_front' ),
		'page_on_front'  => get_option( 'page_on_front' ),
		'page_for_posts' => get_option( 'page_for_posts' ),
		'locations'      => get_theme_mod( 'nav_menu_locations', array() ),
		'settings'       => get_option( 'dara_core_settings' ),
	);

	if ( ! empty( $_POST['set_front'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['home'] );
		update_option( 'page_for_posts', $pages['blog'] );
	}

	// Menus.
	$menus = array();
	if ( ! empty( $_POST['set_menus'] ) ) {
		$main = wp_create_nav_menu( $t( 'القائمة الرئيسية (ديمو)', 'Main menu (demo)' ) . ' ' . wp_rand( 100, 999 ) );
		if ( ! is_wp_error( $main ) ) {
			$menus[] = $main;
			$add     = function ( $menu, $title, $url, $parent = 0 ) {
				return wp_update_nav_menu_item(
					$menu,
					0,
					array(
						'menu-item-title'     => $title,
						'menu-item-url'       => $url,
						'menu-item-parent-id' => $parent,
						'menu-item-status'    => 'publish',
					)
				);
			};
			$archive = get_post_type_archive_link( 'dara_property' );
			$add( $main, $t( 'الرئيسية', 'Home' ), home_url( '/' ) );
			$props_item = $add( $main, $t( 'العقارات', 'Properties' ), $archive );
			$add( $main, $t( 'للبيع', 'For sale' ), add_query_arg( 'purpose', 'sale', $archive ), $props_item );
			$add( $main, $t( 'للإيجار', 'For rent' ), add_query_arg( 'purpose', 'rent', $archive ), $props_item );
			$projects_item = $add( $main, $t( 'المشاريع', 'Projects' ), get_post_type_archive_link( 'dara_project' ) );
			$add( $main, $t( 'كل المشاريع', 'All projects' ), get_post_type_archive_link( 'dara_project' ), $projects_item );
			$add( $main, $t( 'المطوّرون', 'Developers' ), get_permalink( $pages['devs'] ), $projects_item );
			$add( $main, $t( 'المسوّقون', 'Agents' ), get_post_type_archive_link( 'dara_agent' ) );
			$add( $main, $t( 'المدونة', 'Blog' ), get_permalink( $pages['blog'] ) );
			$add( $main, $t( 'تواصل معنا', 'Contact' ), get_permalink( $pages['contact'] ) );

			$foot = wp_create_nav_menu( $t( 'روابط الفوتر (ديمو)', 'Footer links (demo)' ) . ' ' . wp_rand( 100, 999 ) );
			if ( ! is_wp_error( $foot ) ) {
				$menus[] = $foot;
				$add( $foot, $t( 'العقارات', 'Properties' ), $archive );
				$add( $foot, $t( 'المشاريع', 'Projects' ), get_post_type_archive_link( 'dara_project' ) );
				$add( $foot, $t( 'المطوّرون', 'Developers' ), get_permalink( $pages['devs'] ) );
				$add( $foot, $t( 'المسوّقون', 'Agents' ), get_post_type_archive_link( 'dara_agent' ) );
				$add( $foot, $t( 'أضف عقارك', 'List your property' ), get_permalink( $pages['list'] ) );
				$add( $foot, $t( 'تواصل معنا', 'Contact' ), get_permalink( $pages['contact'] ) );
			}
			$locations            = (array) get_theme_mod( 'nav_menu_locations', array() );
			$locations['primary'] = $main;
			if ( ! empty( $foot ) && ! is_wp_error( $foot ) ) {
				$locations['footer'] = $foot;
			}
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	// Theme options (only when they are still empty, so nothing of the user's is overwritten).
	$mods     = array(
		'hero_image' => dara_demo_image( 'hero' ),
		'why_image'  => dara_demo_image( 'interior-1' ),
		'phone'      => '+966 11 000 0000',
		'whatsapp'   => '966500000000',
		'email'      => 'info@example.com',
		'address'    => $t( 'الرياض، طريق الملك فهد', 'King Fahd Road, Riyadh' ),
		'map_lat'    => '24.7136',
		'map_lng'    => '46.6753',
	);
	$set_mods = array();
	foreach ( $mods as $key => $value ) {
		if ( ! get_theme_mod( $key ) ) {
			set_theme_mod( $key, $value );
			$set_mods[] = $key;
		}
	}
	$settings = (array) get_option( 'dara_core_settings', array() );
	if ( empty( $settings['currency'] ) ) {
		$settings['currency'] = $t( 'ر.س', 'SAR' );
	}
	update_option( 'dara_core_settings', $settings );

	update_option(
		'dara_demo_imported',
		array(
			'time'     => time(),
			'menus'    => $menus,
			'backup'   => $backup,
			'set_mods' => $set_mods,
			'trashed'  => $trashed,
		),
		false
	);

	wp_safe_redirect( admin_url( 'edit.php?post_type=dara_property&page=dara-demo&done=imported' ) );
	exit;
}
add_action( 'admin_post_dara_demo_import', 'dara_demo_import' );

/**
 * Remove everything tagged as demo and restore the reading settings.
 */
function dara_demo_remove() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'dara_demo' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'dara-core' ) );
	}
	$info = (array) get_option( 'dara_demo_imported', array() );

	$ids = get_posts(
		array(
			'post_type'      => array( 'dara_property', 'dara_project', 'dara_agent', 'post', 'page', 'attachment' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_dara_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	foreach ( $ids as $id ) {
		if ( 'attachment' === get_post_type( $id ) ) {
			wp_delete_attachment( $id, true );
		} else {
			wp_delete_post( $id, true );
		}
	}

	foreach ( array( 'property_type', 'property_city', 'property_feature', 'project_developer', 'category' ) as $tax ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $tax,
				'hide_empty' => false,
				'fields'     => 'ids',
				'meta_key'   => '_dara_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		foreach ( (array) $terms as $term_id ) {
			wp_delete_term( $term_id, $tax );
		}
	}

	foreach ( (array) ( isset( $info['menus'] ) ? $info['menus'] : array() ) as $menu ) {
		wp_delete_nav_menu( $menu );
	}

	if ( ! empty( $info['backup'] ) ) {
		$b = $info['backup'];
		update_option( 'show_on_front', $b['show_on_front'] );
		update_option( 'page_on_front', $b['page_on_front'] );
		update_option( 'page_for_posts', $b['page_for_posts'] );
		set_theme_mod( 'nav_menu_locations', (array) $b['locations'] );
	}
	foreach ( (array) ( isset( $info['set_mods'] ) ? $info['set_mods'] : array() ) as $key ) {
		remove_theme_mod( $key );
	}

	foreach ( (array) ( isset( $info['trashed'] ) ? $info['trashed'] : array() ) as $sample_id ) {
		wp_untrash_post( $sample_id );
		wp_publish_post( $sample_id );
	}

	delete_option( 'dara_demo_imported' );
	delete_transient( 'dara_template_pages' );

	wp_safe_redirect( admin_url( 'edit.php?post_type=dara_property&page=dara-demo&done=removed' ) );
	exit;
}
add_action( 'admin_post_dara_demo_remove', 'dara_demo_remove' );

/**
 * Point new users to the importer after activating the plugin.
 */
function dara_demo_notice() {
	if ( get_option( 'dara_demo_imported' ) || get_option( 'dara_demo_notice_dismissed' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'plugins', 'themes', 'edit-dara_property' ), true ) ) {
		return;
	}
	if ( wp_count_posts( 'dara_property' )->publish > 0 ) {
		return;
	}
	printf(
		'<div class="notice notice-info"><p>%1$s <a class="button button-primary" href="%2$s">%3$s</a></p></div>',
		esc_html__( 'Start faster: import the demo properties, projects and pages, then replace them with your own.', 'dara-core' ),
		esc_url( admin_url( 'edit.php?post_type=dara_property&page=dara-demo' ) ),
		esc_html__( 'Import demo content', 'dara-core' )
	);
}
add_action( 'admin_notices', 'dara_demo_notice' );
