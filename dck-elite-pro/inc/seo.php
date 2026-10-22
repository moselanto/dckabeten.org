<?php
/**
 * SEO module (v2.2.0).
 * Every page ships complete SEO by default: title, meta description, keywords,
 * canonical, Open Graph, Twitter cards, and JSON-LD structured data.
 * Editors can override per page via the "SEO (DCK)" box; blank fields auto-fill.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'dck_seo_setup' );
if ( ! function_exists( 'dck_seo_setup' ) ) {
	function dck_seo_setup() {
		add_theme_support( 'title-tag' );
	}
}

/* ---------------- Raw defaults (no recursion) ---------------- */
if ( ! function_exists( 'dck_seo_raw_desc' ) ) {
	function dck_seo_raw_desc() {
		return "Deliverance Church Kabete N Market (Cistern of Hope Center) is a Christ-centered church family in Uthiru, Nairobi, Kenya. Join us this Sunday for joyful worship, prayer, and life-changing ministry.";
	}
}
if ( ! function_exists( 'dck_seo_raw_keywords' ) ) {
	function dck_seo_raw_keywords() {
		return "Deliverance Church Kabete, DCK, deliverance church, churches in Kenya, church in Nairobi, churches near me, church in Uthiru, Cistern of Hope Center, Sunday service Nairobi, born again church Kenya, worship in Nairobi, prayer and revival, Pastor Justus Kubai";
	}
}
if ( ! function_exists( 'dck_seo_desc_default' ) ) {
	function dck_seo_desc_default() {
		return get_theme_mod( 'dck_seo_home_desc', dck_seo_raw_desc() );
	}
}
if ( ! function_exists( 'dck_seo_keywords_default' ) ) {
	function dck_seo_keywords_default() {
		return get_theme_mod( 'dck_seo_keywords', dck_seo_raw_keywords() );
	}
}
if ( ! function_exists( 'dck_seo_image_default' ) ) {
	function dck_seo_image_default() {
		$mod = get_theme_mod( 'dck_seo_image', '' );
		if ( $mod ) {
			return $mod;
		}
		$logo_id = get_theme_mod( 'custom_logo' );
		if ( $logo_id ) {
			$src = wp_get_attachment_image_url( $logo_id, 'large' );
			if ( $src ) {
				return $src;
			}
		}
		return get_template_directory_uri() . '/assets/images/home/church-slide-4.webp';
	}
}

/* ---------------- Context resolver ---------------- */
if ( ! function_exists( 'dck_seo_meta' ) ) {
	function dck_seo_meta() {
		$out = array(
			'desc'     => dck_seo_desc_default(),
			'keywords' => dck_seo_keywords_default(),
			'image'    => dck_seo_image_default(),
			'type'     => 'website',
		);
		if ( is_singular() ) {
			$id      = get_queried_object_id();
			$ov_desc = get_post_meta( $id, '_dck_seo_desc', true );
			$ov_kw   = get_post_meta( $id, '_dck_seo_keywords', true );
			if ( $ov_desc ) {
				$out['desc'] = $ov_desc;
			} else {
				$raw  = wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $id ) ) );
				$auto = trim( wp_trim_words( $raw, 30, '...' ) );
				if ( $auto ) {
					$out['desc'] = $auto;
				}
			}
			if ( $ov_kw ) {
				$out['keywords'] = $ov_kw;
			}
			$img = get_the_post_thumbnail_url( $id, 'large' );
			if ( $img ) {
				$out['image'] = $img;
			}
			$out['type'] = 'article';
		}
		return $out;
	}
}

if ( ! function_exists( 'dck_seo_canonical' ) ) {
	function dck_seo_canonical() {
		$url = home_url( '/' );
		if ( is_singular() ) {
			$p = get_permalink();
			if ( $p ) {
				$url = $p;
			}
		} elseif ( is_post_type_archive() ) {
			$a = get_post_type_archive_link( get_post_type() );
			if ( $a ) {
				$url = $a;
			}
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$t = get_queried_object();
			if ( $t && ! is_wp_error( $t ) ) {
				$tl = get_term_link( $t );
				if ( $tl && ! is_wp_error( $tl ) ) {
					$url = $tl;
				}
			}
		}
		return $url;
	}
}

/* ---------------- JSON-LD: one @graph with stable @ids ---------------- */
if ( ! function_exists( 'dck_seo_id' ) ) {
	function dck_seo_id( $frag ) {
		return home_url( '/' ) . '#' . $frag;
	}
}

/* Official social profiles only - generic homepages (facebook.com/, youtube.com/) are ignored. */
if ( ! function_exists( 'dck_seo_same_as' ) ) {
	function dck_seo_same_as() {
		$out = array();
		foreach ( array( 'dck_fb_url', 'dck_yt_url', 'dck_ig_url', 'dck_tiktok_url', 'dck_x_url' ) as $k ) {
			$u = trim( (string) get_theme_mod( $k, '' ) );
			if ( ! $u || ! wp_http_validate_url( $u ) ) {
				continue;
			}
			$path = trim( (string) wp_parse_url( $u, PHP_URL_PATH ), '/' );
			$q    = (string) wp_parse_url( $u, PHP_URL_QUERY );
			if ( '' === $path && '' === $q ) {
				continue; // Placeholder like https://www.facebook.com/.
			}
			$out[] = esc_url_raw( $u );
		}
		return array_values( array_unique( $out ) );
	}
}

/* "8:00 - 9:00 AM" / "10:40 AM - 1:00 PM" -> array( '08:00', '09:00' ) or null. */
if ( ! function_exists( 'dck_seo_parse_span' ) ) {
	function dck_seo_parse_span( $t ) {
		if ( ! preg_match( '/(\d{1,2})(?::(\d{2}))?\s*(am|pm)?\s*[-\x{2013}to]+\s*(\d{1,2})(?::(\d{2}))?\s*(am|pm)/iu', (string) $t, $m ) ) {
			return null;
		}
		$ap2 = strtolower( $m[6] );
		$ap1 = $m[3] ? strtolower( $m[3] ) : $ap2;
		$h1  = (int) $m[1] % 12 + ( 'pm' === $ap1 ? 12 : 0 );
		$h2  = (int) $m[4] % 12 + ( 'pm' === $ap2 ? 12 : 0 );
		if ( ! $m[3] && $h1 > $h2 ) {
			$h1 -= 12; // "11:00 - 1:00 PM" means 11 AM.
		}
		return array( sprintf( '%02d:%02d', $h1, (int) $m[2] ), sprintf( '%02d:%02d', $h2, (int) $m[5] ) );
	}
}

/* Opening hours: Google Business Profile hours from settings; fallback built from the Gatherings list (weekly/Sunday only; monthly Keshas skipped). */
if ( ! function_exists( 'dck_seo_hours' ) ) {
	function dck_seo_hours() {
		/* Opening hours must match the Google Business Profile: DCK Church > Church & Contact > Opening hours (Google). */
		$dck_oh_default = "Monday|09:00|18:00\nTuesday|09:00|18:00\nWednesday|09:00|18:00\nThursday|09:00|19:00\nFriday|09:00|18:00\nSaturday|09:00|18:00\nSunday|07:00|18:00";
		$dck_oh_raw     = function_exists( 'dck_home_val' ) ? (string) dck_home_val( 'dck_seo_opening_hours', $dck_oh_default ) : $dck_oh_default;
		$dck_oh_days    = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
		$dck_oh_out     = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $dck_oh_raw ) as $dck_oh_line ) {
			$dck_oh_p = array_map( 'trim', explode( '|', $dck_oh_line ) );
			if ( 3 !== count( $dck_oh_p ) || ! in_array( strtolower( $dck_oh_p[0] ), $dck_oh_days, true ) ) {
				continue;
			}
			if ( ! preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $dck_oh_p[1] ) || ! preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $dck_oh_p[2] ) ) {
				continue;
			}
			$dck_oh_out[] = array( '@type' => 'OpeningHoursSpecification', 'dayOfWeek' => 'https://schema.org/' . ucfirst( strtolower( $dck_oh_p[0] ) ), 'opens' => $dck_oh_p[1], 'closes' => $dck_oh_p[2] );
		}
		if ( $dck_oh_out ) {
			return $dck_oh_out;
		}
		if ( ! function_exists( 'dck_gatherings' ) ) {
			return array();
		}
		$days = array( 'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday' );
		$span = array();
		foreach ( (array) dck_gatherings() as $g ) {
			if ( empty( $g['type'] ) || 'monthly' === $g['type'] ) {
				continue;
			}
			$se = dck_seo_parse_span( isset( $g['time'] ) ? $g['time'] : '' );
			if ( ! $se ) {
				continue;
			}
			$when = strtolower( 'sunday' === $g['type'] ? 'sunday' : (string) $g['when'] );
			foreach ( $days as $k => $d ) {
				if ( false !== strpos( $when, $k ) ) {
					if ( ! isset( $span[ $d ] ) ) {
						$span[ $d ] = $se;
					} else {
						$span[ $d ][0] = min( $span[ $d ][0], $se[0] );
						$span[ $d ][1] = max( $span[ $d ][1], $se[1] );
					}
				}
			}
		}
		$out = array();
		foreach ( $span as $d => $se ) {
			$out[] = array( '@type' => 'OpeningHoursSpecification', 'dayOfWeek' => 'https://schema.org/' . $d, 'opens' => $se[0], 'closes' => $se[1] );
		}
		return $out;
	}
}

if ( ! function_exists( 'dck_seo_address' ) ) {
	function dck_seo_address() {
		return array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => get_theme_mod( 'dck_top_address', 'Behind N Market, Uthiru, Kabete' ),
			'addressLocality' => get_theme_mod( 'dck_seo_locality', 'Uthiru' ),
			'addressRegion'   => get_theme_mod( 'dck_seo_region', 'Nairobi' ),
			'addressCountry'  => 'KE',
		);
	}
}

if ( ! function_exists( 'dck_seo_jsonld' ) ) {
	function dck_seo_jsonld( $meta ) {
		$name   = get_bloginfo( 'name' );
		$home   = home_url( '/' );
		$url    = dck_seo_canonical();
		$church = array_filter(
			array(
				'@type'                     => 'Church',
				'@id'                       => dck_seo_id( 'church' ),
				'name'                      => $name,
				'alternateName'             => array( 'Cistern of Hope Center', 'DCK Kabete' ),
				'url'                       => $home,
				'logo'                      => array( '@type' => 'ImageObject', '@id' => dck_seo_id( 'logo' ), 'url' => get_template_directory_uri() . '/assets/images/logo.png' ),
				'image'                     => dck_seo_image_default(),
				'description'               => dck_seo_desc_default(),
				'telephone'                 => get_theme_mod( 'dck_top_phone', '+254 110 304521' ),
				'email'                     => get_theme_mod( 'dck_top_email', 'info@dckabeten.org' ),
				'address'                   => dck_seo_address(),
				'hasMap'                    => get_theme_mod( 'dck_directions_url', '' ),
				'areaServed'                => array( '@type' => 'Country', 'name' => 'Kenya' ),
				'sameAs'                    => dck_seo_same_as(),
				'openingHoursSpecification' => dck_seo_hours(),
			)
		);
		$lat = get_theme_mod( 'dck_seo_lat', '' );
		$lng = get_theme_mod( 'dck_seo_lng', '' );
		if ( is_numeric( $lat ) && is_numeric( $lng ) ) {
			$church['geo'] = array( '@type' => 'GeoCoordinates', 'latitude' => (float) $lat, 'longitude' => (float) $lng );
		}
		$website = array(
			'@type'      => 'WebSite',
			'@id'        => dck_seo_id( 'website' ),
			'name'       => $name,
			'url'        => $home,
			'inLanguage' => 'en-KE',
			'publisher'  => array( '@id' => dck_seo_id( 'church' ) ),
		);
		$page = array(
			'@type'      => is_singular( 'post' ) || is_singular( 'dck_sermon' ) ? 'WebPage' : ( is_page_template( 'template-faq.php' ) ? 'FAQPage' : ( is_page_template( 'template-contact.php' ) ? 'ContactPage' : ( is_page_template( 'template-about.php' ) ? 'AboutPage' : ( is_post_type_archive() || is_home() || is_category() || is_tag() ? 'CollectionPage' : 'WebPage' ) ) ) ),
			'@id'        => $url . '#webpage',
			'url'        => $url,
			'name'       => wp_get_document_title(),
			'description'=> $meta['desc'],
			'isPartOf'   => array( '@id' => dck_seo_id( 'website' ) ),
			'about'      => array( '@id' => dck_seo_id( 'church' ) ),
			'inLanguage' => 'en-KE',
		);
		if ( 'FAQPage' === $page['@type'] ) {
			$page['@type'] = 'WebPage'; // The FAQ entity itself is emitted by inc/faq.php from the visible Q&As.
		}
		$graph = array( $church, $website );
		$nodes = function_exists( 'dck_seo_extra_nodes' ) ? dck_seo_extra_nodes( $url ) : array();
		foreach ( $nodes as $n ) {
			if ( 'BreadcrumbList' === $n['@type'] ) {
				$page['breadcrumb'] = array( '@id' => $n['@id'] );
			}
			if ( isset( $n['mainEntityOfPage'] ) ) {
				$page['mainEntity'] = array( '@id' => $n['@id'] );
			}
		}
		$graph[] = $page;
		$graph   = array_merge( $graph, $nodes );
		$json    = wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG );
		return '<script type="application/ld+json">' . $json . '</script>' . chr( 10 );
	}
}

/* ---------------- One provider rule ---------------- */
if ( ! function_exists( 'dck_seo_external' ) ) {
	/* True when an SEO plugin is active. It then owns meta/canonical/sitemap; the theme keeps the schema graph. */
	function dck_seo_external() {
		return (bool) apply_filters( 'dck_seo_external', defined( 'RANK_MATH_VERSION' ) || defined( 'WPSEO_VERSION' ) );
	}
}
/* Remove the plugin's own JSON-LD so there is exactly one Church/WebSite graph (the theme's, with real hours and address). */
add_filter( 'rank_math/json_ld', function ( $data ) { return apply_filters( 'dck_keep_plugin_schema', false ) ? $data : array(); }, 999 );
add_filter( 'wpseo_json_ld_output', function ( $data ) { return apply_filters( 'dck_keep_plugin_schema', false ) ? $data : false; }, 999 );
add_action(
	'wp',
	function () {
		if ( dck_seo_external() ) {
			remove_filter( 'wp_robots', 'dck_seo_robots' );
			remove_filter( 'document_title_parts', 'dck_seo_title_parts' );
			remove_filter( 'document_title_parts', 'dck_pg_seo_title_filter', 20 );
		}
	}
);

/* ---------------- Head output ---------------- */
remove_action( 'wp_head', 'rel_canonical' );
add_action( 'wp_head', 'dck_seo_head', 1 );
if ( ! function_exists( 'dck_seo_head' ) ) {
	function dck_seo_head() {
		if ( is_admin() ) {
			return;
		}
		$m     = dck_seo_meta();
		$url   = dck_seo_canonical();
		$title = wp_get_document_title();
		$nl    = chr( 10 );
		$img   = $m['image'];

		echo $nl . '<!-- DCK SEO -->' . $nl;
		if ( dck_seo_external() ) {
			// Rank Math / Yoast owns title, description, canonical, robots, OG and sitemap; the theme owns the single JSON-LD graph.
			echo dck_seo_jsonld( $m );
			echo '<!-- /DCK SEO -->' . $nl;
			return;
		}
		echo '<meta name="description" content="' . esc_attr( $m['desc'] ) . '">' . $nl;
		echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . $nl;

		echo '<meta property="og:type" content="' . esc_attr( $m['type'] ) . '">' . $nl;
		echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . $nl;
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . $nl;
		echo '<meta property="og:description" content="' . esc_attr( $m['desc'] ) . '">' . $nl;
		echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . $nl;
		echo '<meta property="og:locale" content="en_KE">' . $nl;
		if ( $img ) {
			echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . $nl;
		}

		echo '<meta name="twitter:card" content="summary_large_image">' . $nl;
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . $nl;
		echo '<meta name="twitter:description" content="' . esc_attr( $m['desc'] ) . '">' . $nl;
		if ( $img ) {
			echo '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . $nl;
		}

		echo dck_seo_jsonld( $m );
		echo '<!-- /DCK SEO -->' . $nl;
	}
}

/* ---------------- Robots (noindex thin pages) ---------------- */
add_filter( 'wp_robots', 'dck_seo_robots' );
if ( ! function_exists( 'dck_seo_robots' ) ) {
	function dck_seo_robots( $robots ) {
		if ( is_search() || is_404() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		} else {
			$robots['index']             = true;
			$robots['follow']            = true;
			$robots['max-image-preview'] = 'large';
		}
		return $robots;
	}
}

/* ---------------- Per-page SEO title override ---------------- */
add_filter( 'document_title_parts', 'dck_seo_title_parts' );
if ( ! function_exists( 'dck_seo_title_parts' ) ) {
	function dck_seo_title_parts( $parts ) {
		if ( is_singular() ) {
			$ttl = get_post_meta( get_queried_object_id(), '_dck_seo_title', true );
			if ( $ttl ) {
				$parts['title'] = $ttl;
			}
		}
		return $parts;
	}
}

/* ---------------- Per-page SEO meta box ---------------- */
add_action( 'add_meta_boxes', 'dck_seo_metabox' );
if ( ! function_exists( 'dck_seo_metabox' ) ) {
	function dck_seo_metabox() {
		$types = get_post_types( array( 'public' => true ), 'names' );
		foreach ( $types as $t ) {
			if ( 'attachment' === $t ) {
				continue;
			}
			add_meta_box( 'dck_seo_box', __( 'SEO (DCK)', 'dck-elite' ), 'dck_seo_metabox_render', $t, 'normal', 'high' );
		}
	}
}
if ( ! function_exists( 'dck_seo_metabox_render' ) ) {
	function dck_seo_metabox_render( $post ) {
		wp_nonce_field( 'dck_seo_save', 'dck_seo_nonce' );
		$ttl  = get_post_meta( $post->ID, '_dck_seo_title', true );
		$desc = get_post_meta( $post->ID, '_dck_seo_desc', true );
		$kw   = get_post_meta( $post->ID, '_dck_seo_keywords', true );
		echo '<p><label style="font-weight:600;display:block;margin-bottom:4px">' . esc_html__( 'SEO title (optional, overrides the browser title)', 'dck-elite' ) . '</label>';
		echo '<input type="text" name="dck_seo_title" value="' . esc_attr( $ttl ) . '" style="width:100%" maxlength="70"></p>';
		echo '<p><label style="font-weight:600;display:block;margin-bottom:4px">' . esc_html__( 'Meta description (aim for 150 to 160 characters)', 'dck-elite' ) . '</label>';
		echo '<textarea name="dck_seo_desc" rows="3" style="width:100%" maxlength="220">' . esc_textarea( $desc ) . '</textarea></p>';
		echo '<p><label style="font-weight:600;display:block;margin-bottom:4px">' . esc_html__( 'Focus keywords (comma separated)', 'dck-elite' ) . '</label>';
		echo '<input type="text" name="dck_seo_keywords" value="' . esc_attr( $kw ) . '" style="width:100%"></p>';
		echo '<p style="color:#666;margin:0">' . esc_html__( 'Leave blank and DCK fills these automatically from the page title and content.', 'dck-elite' ) . '</p>';
	}
}
add_action( 'save_post', 'dck_seo_metabox_save' );
if ( ! function_exists( 'dck_seo_metabox_save' ) ) {
	function dck_seo_metabox_save( $post_id ) {
		if ( ! isset( $_POST['dck_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dck_seo_nonce'] ) ), 'dck_seo_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$map = array(
			'_dck_seo_title'    => 'dck_seo_title',
			'_dck_seo_desc'     => 'dck_seo_desc',
			'_dck_seo_keywords' => 'dck_seo_keywords',
		);
		foreach ( $map as $meta => $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$val = ( 'dck_seo_desc' === $field )
					? sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) )
					: sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
				update_post_meta( $post_id, $meta, $val );
			}
		}
	}
}

/* ---------------- Customizer: SEO and Social ---------------- */
add_action( 'customize_register', 'dck_seo_customize' );
if ( ! function_exists( 'dck_seo_customize' ) ) {
	function dck_seo_customize( $wp ) {
		$wp->add_section( 'dck_seo', array( 'title' => __( 'SEO and Social', 'dck-elite' ), 'priority' => 24 ) );
		$wp->add_setting( 'dck_seo_home_desc', array( 'default' => dck_seo_raw_desc(), 'sanitize_callback' => 'sanitize_textarea_field' ) );
		$wp->add_control( 'dck_seo_home_desc', array( 'label' => __( 'Homepage meta description', 'dck-elite' ), 'section' => 'dck_seo', 'type' => 'textarea' ) );
		$wp->add_setting( 'dck_seo_keywords', array( 'default' => dck_seo_raw_keywords(), 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'dck_seo_keywords', array( 'label' => __( 'Site keywords', 'dck-elite' ), 'section' => 'dck_seo', 'type' => 'textarea' ) );
		$wp->add_setting( 'dck_seo_locality', array( 'default' => 'Uthiru', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'dck_seo_locality', array( 'label' => __( 'Locality (town)', 'dck-elite' ), 'section' => 'dck_seo', 'type' => 'text' ) );
		$wp->add_setting( 'dck_seo_region', array( 'default' => 'Nairobi', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'dck_seo_region', array( 'label' => __( 'Region (county)', 'dck-elite' ), 'section' => 'dck_seo', 'type' => 'text' ) );
		$wp->add_setting( 'dck_seo_image', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
		$wp->add_control( new WP_Customize_Image_Control( $wp, 'dck_seo_image', array( 'label' => __( 'Default social share image', 'dck-elite' ), 'section' => 'dck_seo' ) ) );
	}
}
