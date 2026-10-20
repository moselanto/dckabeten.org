<?php
/**
 * DCK Elite - Per-Page SEO titles and meta descriptions.
 *
 * Hooks cleanly into WordPress without touching seo.php internals.
 * - Overrides <title> via document_title_parts filter.
 * - Overrides meta description by filtering the theme_mod that
 *   dck_seo_desc_default() reads — one-directional, zero circular risk.
 * - Adds a Customizer panel so every default can be overridden in-UI.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   1. Data table — slug => title + description
   ============================================================ */
if ( ! function_exists( 'dck_pg_seo_data' ) ) {
	function dck_pg_seo_data() {
		return array(
			'home'            => array(
				'title' => 'Deliverance Church Kabete N Market | Church in Uthiru, Kabete, Nairobi',
				'desc'  => 'Welcome to Deliverance Church Kabete N Market (Cistern of Hope Center), a Christ-centred church in Nairobi. Join us for worship, Bible study, prayer, sermons, and online services.',
			),
			'about'           => array(
				'title' => 'About Deliverance Church Kabete | Our Story, Mission & Leadership',
				'desc'  => 'Learn about Deliverance Church Kabete N Market, our history, mission, beliefs, pastors, and commitment to building a Christ-centred community in Nairobi, Kenya.',
			),
			'pastors'         => array(
				'title' => 'Meet Our Pastors | Deliverance Church Kabete Nairobi',
				'desc'  => 'Meet Pastor Justus Kubai, Pastor Clare Kubai, and the pastoral leadership team serving Deliverance Church Kabete with biblical teaching and community outreach.',
			),
			'services'        => array(
				'title' => 'Sunday Service Times in Uthiru, Kabete | Deliverance Church Kabete',
				'desc'  => 'Sunday at Deliverance Church Kabete N Market, behind N Market, Uthiru: Intensive Prayers 8:00 AM, Bible Study 9:30 AM, Glorious Service 10:40 AM. Midweek prayers and service too.',
			),
			'ministries'      => array(
				'title' => 'Church Ministries | Youth, Children, Men, Women & Worship Teams',
				'desc'  => 'Discover ministries for every stage of life including youth, children, men, women, worship, evangelism, missions, and family ministries at Deliverance Church Kabete.',
			),
			'sermons'         => array(
				'title' => 'Christian Sermons Online | Watch & Listen | Deliverance Church Kabete',
				'desc'  => 'Watch inspiring Christian sermons from Pastor Justus Kubai and the Deliverance Church Kabete team. Grow in faith with biblical teaching available online anytime.',
			),
			'sermon-search'   => array(
				'title' => 'AI Sermon Search | Find Christian Sermons by Topic',
				'desc'  => 'Search Christian sermons using AI. Find biblical messages on faith, prayer, healing, marriage, purpose, salvation, hope, forgiveness, and spiritual growth.',
			),
			'bible-study'     => array(
				'title' => 'AI Bible Study Assistant | Ask Bible Questions Online',
				'desc'  => 'Ask Bible questions and receive Scripture-based answers with cross references, historical context, application, and prayer using our AI Bible Study Assistant.',
			),
			'hope'            => array(
				'title' => 'Christian AI Assistant | Ask Hope | Bible & Prayer Support',
				'desc'  => 'Ask Hope, our Christian AI assistant, Bible questions, prayer requests, church information, discipleship guidance, and Scripture-based encouragement.',
			),
			'prayer-requests' => array(
				'title' => 'Submit a Prayer Request Online | Deliverance Church Kabete',
				'desc'  => 'Need prayer? Submit your confidential prayer request online and let our church family stand with you in faith through prayer and biblical encouragement.',
			),
			'give'            => array(
				'title' => 'Give Online | Tithes, Offerings & Donations | Deliverance Church Kabete',
				'desc'  => 'Support the work of God through secure online giving. Give tithes, offerings, missions, and donations via M-PESA, bank transfer, and other supported methods.',
			),
			'contact'         => array(
				'title' => 'Contact & Directions | Deliverance Church Kabete, Behind N Market Uthiru',
				'desc'  => 'Find Deliverance Church Kabete N Market behind N Market, Uthiru, Kabete. Call +254 110 304521, WhatsApp or email info@dckabeten.org, and get directions on Google Maps.',
			),
			'faq'             => array(
				'title' => 'Church FAQs | Worship, Membership, Giving & Bible Questions',
				'desc'  => 'Find answers about church services, membership, baptism, Bible study, giving, prayer requests, ministries, and visiting Deliverance Church Kabete.',
			),
			'blog'            => array(
				'title' => 'Christian Blog | Faith, Prayer & Bible Teaching in Kenya',
				'desc'  => 'Read biblical articles, devotionals, Christian living tips, prayer guides, marriage, family, youth, and discipleship resources from Deliverance Church Kabete.',
			),
			'gallery'         => array(
				'title' => 'Church Photo Gallery | Worship, Events & Community Life',
				'desc'  => 'View photos from worship services, conferences, youth events, baptisms, missions, outreach, and church life at Deliverance Church Kabete.',
			),
			'live'            => array(
				'title' => 'Watch Church Live Online | Sunday Worship Service Nairobi',
				'desc'  => 'Watch Deliverance Church Kabete live every Sunday. Join worship, biblical preaching, praise, prayer, and fellowship from anywhere in the world.',
			),
			'visit'           => array(
				'title' => 'Plan Your Visit | Deliverance Church Kabete Nairobi',
				'desc'  => 'Visiting Deliverance Church Kabete for the first time? Find service times, directions, parking, and everything you need to feel at home.',
			),
			'events'          => array(
				'title' => 'Upcoming Church Events | Deliverance Church Kabete',
				'desc'  => 'Stay up to date with upcoming worship services, conferences, prayer nights, youth events, and community programmes at Deliverance Church Kabete.',
			),
		);
	}
}

/* ============================================================
   2. Resolve the SEO key for the current page (no DB queries)
   ============================================================ */
if ( ! function_exists( 'dck_pg_seo_key' ) ) {
	function dck_pg_seo_key() {
		if ( is_front_page() ) {
			return 'home';
		}
		if ( is_home() ) {
			return 'blog';
		}
		if ( is_page_template( 'template-gallery.php' ) ) {
			return 'gallery';
		}
		if ( is_page() || is_singular() ) {
			$obj = get_queried_object();
			if ( $obj && isset( $obj->post_name ) ) {
				$slug = (string) $obj->post_name;
				$data = dck_pg_seo_data();
				if ( isset( $data[ $slug ] ) ) {
					return $slug;
				}
			}
		}
		if ( is_post_type_archive( 'dck_sermon' ) ) {
			return 'sermons';
		}
		if ( is_post_type_archive( 'dck_event' ) ) {
			return 'events';
		}
		if ( is_category() || is_tag() ) {
			return 'blog';
		}
		return '';
	}
}

/* ============================================================
   3. Read title/desc — Customizer override wins, else built-in
   ============================================================ */
if ( ! function_exists( 'dck_pg_seo_title' ) ) {
	function dck_pg_seo_title( $key ) {
		$mod = get_theme_mod( 'dck_pgseo_title_' . $key, '' );
		if ( '' !== (string) $mod ) {
			return (string) $mod;
		}
		$data = dck_pg_seo_data();
		return isset( $data[ $key ]['title'] ) ? $data[ $key ]['title'] : '';
	}
}

if ( ! function_exists( 'dck_pg_seo_desc' ) ) {
	function dck_pg_seo_desc( $key ) {
		$mod = get_theme_mod( 'dck_pgseo_desc_' . $key, '' );
		if ( '' !== (string) $mod ) {
			return (string) $mod;
		}
		$data = dck_pg_seo_data();
		return isset( $data[ $key ]['desc'] ) ? $data[ $key ]['desc'] : '';
	}
}

/* ============================================================
   4. Override <title> tag
   ============================================================ */
add_filter( 'document_title_parts', 'dck_pg_seo_title_filter', 20 );

if ( ! function_exists( 'dck_pg_seo_title_filter' ) ) {
	function dck_pg_seo_title_filter( $parts ) {
		/* Respect per-post meta override */
		if ( is_singular() ) {
			$meta = get_post_meta( get_queried_object_id(), '_dck_seo_title', true );
			if ( $meta ) {
				return $parts;
			}
		}
		$key = dck_pg_seo_key();
		if ( '' === $key ) {
			return $parts;
		}
		$ttl = dck_pg_seo_title( $key );
		if ( '' === $ttl ) {
			return $parts;
		}
		$parts['title'] = $ttl;
		unset( $parts['tagline'], $parts['site'] );
		return $parts;
	}
}

/* ============================================================
   5. Override meta description.
   dck_seo_desc_default() in seo.php calls:
     get_theme_mod( 'dck_seo_home_desc', dck_seo_raw_desc() )
   WordPress fires 'theme_mod_dck_seo_home_desc' when that runs.
   We intercept it here — clean, one-directional, no circularity.
   ============================================================ */
add_filter( 'theme_mod_dck_seo_home_desc', 'dck_pg_seo_desc_filter' );

if ( ! function_exists( 'dck_pg_seo_desc_filter' ) ) {
	function dck_pg_seo_desc_filter( $default ) {
		/* Respect per-post meta override */
		if ( is_singular() ) {
			$meta = get_post_meta( get_queried_object_id(), '_dck_seo_desc', true );
			if ( $meta ) {
				return $default;
			}
		}
		$key = dck_pg_seo_key();
		if ( '' === $key ) {
			return $default;
		}
		$desc = dck_pg_seo_desc( $key );
		return '' !== $desc ? $desc : $default;
	}
}

/* ============================================================
   6. Customizer panel — override any default in the UI
   ============================================================ */
add_action( 'customize_register', 'dck_pg_seo_customize', 30 );

if ( ! function_exists( 'dck_pg_seo_customize' ) ) {
	function dck_pg_seo_customize( $wp ) {
		$wp->add_section( 'dck_pgseo', array(
			'title'       => __( 'Page SEO Titles & Descriptions', 'dck-elite' ),
			'priority'    => 165,
			'description' => __( 'Leave blank to use the built-in SEO defaults.', 'dck-elite' ),
		) );

		foreach ( dck_pg_seo_data() as $key => $d ) {
			$label = ucwords( str_replace( '-', ' ', $key ) );

			$id_t = 'dck_pgseo_title_' . $key;
			$wp->add_setting( $id_t, array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			) );
			$wp->add_control( $id_t, array(
				'label'       => $label . ' — SEO title',
				'description' => esc_html( $d['title'] ),
				'section'     => 'dck_pgseo',
				'type'        => 'text',
			) );

			$id_d = 'dck_pgseo_desc_' . $key;
			$wp->add_setting( $id_d, array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_textarea_field',
			) );
			$wp->add_control( $id_d, array(
				'label'       => $label . ' — Meta description',
				'description' => esc_html( $d['desc'] ),
				'section'     => 'dck_pgseo',
				'type'        => 'textarea',
			) );
		}
	}
}
