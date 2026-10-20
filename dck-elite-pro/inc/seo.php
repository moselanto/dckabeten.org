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

/* ---------------- JSON-LD ---------------- */
if ( ! function_exists( 'dck_seo_jsonld' ) ) {
	function dck_seo_jsonld( $meta ) {
		$name = get_bloginfo( 'name' );
		$home = home_url( '/' );
		$same = array();
		$fb   = get_theme_mod( 'dck_fb_url', '' );
		$yt   = get_theme_mod( 'dck_yt_url', '' );
		if ( $fb ) {
			$same[] = $fb;
		}
		if ( $yt ) {
			$same[] = $yt;
		}
		$church = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Church',
			'name'        => $name,
			'url'         => $home,
			'logo'        => dck_seo_image_default(),
			'image'       => $meta['image'],
			'description' => dck_seo_desc_default(),
			'telephone'   => get_theme_mod( 'dck_top_phone', '+254 110 304521' ),
			'email'       => get_theme_mod( 'dck_top_email', 'info@dckabeten.org' ),
			'address'     => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => get_theme_mod( 'dck_top_address', 'Behind N Market, Uthiru, Kabete' ),
				'addressLocality' => get_theme_mod( 'dck_seo_locality', 'Uthiru' ),
				'addressRegion'   => get_theme_mod( 'dck_seo_region', 'Nairobi' ),
				'addressCountry'  => 'KE',
			),
			'sameAs'                    => $same,
			'openingHoursSpecification' => array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => 'Sunday',
				'opens'     => '08:00',
				'closes'    => '13:00',
			),
		);
		$website = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'WebSite',
			'name'            => $name,
			'url'             => $home,
			'inLanguage'      => 'en',
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => $home . '?s={search_term_string}',
				'query-input' => 'required name=search_term_string',
			),
		);
		$graph = array( $church, $website );
		$json  = wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		return '<script type="application/ld+json">' . $json . '</script>' . chr( 10 );
	}
}

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
		echo '<meta name="description" content="' . esc_attr( $m['desc'] ) . '">' . $nl;
		echo '<meta name="keywords" content="' . esc_attr( $m['keywords'] ) . '">' . $nl;
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
