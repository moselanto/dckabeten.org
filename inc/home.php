<?php
/**
 * Homepage content model + Customizer (v2.1.0).
 * Every homepage element is editable here - nothing is hardcoded.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------- Value + image helpers ---------------- */

if ( ! function_exists( 'dck_home_val' ) ) {
	function dck_home_val( $key, $default = '' ) {
		$v = get_theme_mod( $key, $default );
		return ( '' === $v || null === $v ) ? $default : $v;
	}
}

if ( ! function_exists( 'dck_home_img' ) ) {
	function dck_home_img( $key, $default_file ) {
		$v = get_theme_mod( $key, '' );
		if ( $v ) {
			if ( is_numeric( $v ) ) {
				$src = wp_get_attachment_image_url( (int) $v, 'large' );
				if ( $src ) {
					return $src;
				}
			}
			return $v;
		}
		return get_template_directory_uri() . '/assets/images/home/' . $default_file;
	}
}

if ( ! function_exists( 'dck_home_tel' ) ) {
	function dck_home_tel() {
		return preg_replace( '/[^0-9+]/', '', dck_home_val( 'dck_top_phone', '+254 110 304521' ) );
	}
}

/* ---------------- Ministry data (matches the live site) ---------------- */

if ( ! function_exists( 'dck_home_min_defs' ) ) {
	function dck_home_min_defs() {
		return array(
			array( 'k' => 'pastoral',   'img' => 'pastors.webp',            'name' => 'Pastoral Team',          'desc' => "Lead Pastors Pastor Justus Kubai and Mrs. Clare Kubai, with Assistant Pastors Mr. and Mrs. Henry Onzere." ),
			array( 'k' => 'mof',        'img' => 'mof.webp',                'name' => 'Men of Faith (MOF)',     'desc' => "A fellowship of married men faithfully committed to leading spiritually and mentoring others in Christ." ),
			array( 'k' => 'dof',        'img' => 'women.webp',              'name' => 'Daughters of Faith (DoF)','desc' => "A vibrant women's ministry focused on prayer, empowerment, and fellowship." ),
			array( 'k' => 'yotm',       'img' => 'youth-m.webp',            'name' => 'Youth on the Move (YOTM)','desc' => "Youth ministry passionate about serving God and influencing their generation." ),
			array( 'k' => 'goldeng',    'img' => 'youth-ministry.webp',     'name' => 'Golden Gift (Double G)',  'desc' => "A department for unmarried men and women, focused on purpose and purity." ),
			array( 'k' => 'fof',        'img' => 'frames-of-fire.webp',     'name' => 'Flames of Fire (FoF)',    'desc' => "Anointed worship team passionately leading powerful, deep encounters with God." ),
			array( 'k' => 'voices',     'img' => 'choir.webp',              'name' => 'Cistern of Hope Voices',  'desc' => "Our choir team inspiring worship through unity, faith, and powerful, uplifting voices." ),
			array( 'k' => 'dunamis',    'img' => 'children-praying.webp',   'name' => 'Dunamis (Teenagers)',     'desc' => "A teen ministry for ages 13 to 19, guiding youth spiritually through their formative years." ),
			array( 'k' => 'conquerors', 'img' => 'conquerors.webp',         'name' => 'Conquerors',              'desc' => "Children's ministry for ages 7 to 12, building a godly foundation for the next generation." ),
			array( 'k' => 'champions',  'img' => 'champions-of-christ.webp','name' => 'Champions of Christ',     'desc' => "Children's ministry for ages 3 to 6, building a godly foundation for the next generation." ),
			array( 'k' => 'evangelism', 'img' => 'evangelism.webp',         'name' => 'Missions and Evangelism', 'desc' => "A dedicated team focused on soul winning and outreach." ),
			array( 'k' => 'media',      'img' => 'media-team.webp',         'name' => 'Media Team Crew',         'desc' => "Manages all media and communication needs for the church." ),
			array( 'k' => 'shield',     'img' => 'security.webp',           'name' => 'Shield Crew',             'desc' => "Our security team, ensuring safety and order during services and events." ),
			array( 'k' => 'welfare',    'img' => 'food-donation.webp',      'name' => 'Welfare Department',      'desc' => "Supports member welfare, charity outreach, and compassion." ),
		);
	}
}

if ( ! function_exists( 'dck_home_ministries' ) ) {
	function dck_home_ministries() {
		$out = array();
		foreach ( dck_home_min_defs() as $d ) {
			$out[] = array(
				'name' => dck_home_val( 'dck_min_' . $d['k'] . '_name', $d['name'] ),
				'desc' => dck_home_val( 'dck_min_' . $d['k'] . '_desc', $d['desc'] ),
				'img'  => dck_home_img( 'dck_min_' . $d['k'] . '_img', $d['img'] ),
			);
		}
		return $out;
	}
}

/* ---------------- Hero slide data ---------------- */

if ( ! function_exists( 'dck_home_slides' ) ) {
	function dck_home_slides() {
		$defs = array(
			array( 'k' => '1', 'img' => 'church-slide-4.webp', 'eyebrow' => 'Welcome Home',              'title' => 'Where Faith Grows, Lives Change, and Hope Begins.', 'text' => 'Deliverance Church Kabete N Market, Cistern of Hope Center - uniting hearts in praise, prayer and divine presence.' ),
			array( 'k' => '2', 'img' => 'joy.webp',            'eyebrow' => 'Joyful Sunday Gatherings',   'title' => 'A Joyful Sunday Gathering',                        'text' => 'Filled with faith, community, and celebration - experience a joyful Sunday gathering with us.' ),
			array( 'k' => '3', 'img' => 'church-slide-3.webp', 'eyebrow' => 'Powerful Moments of Worship','title' => 'Powerful Moments of Worship Together',            'text' => 'Uniting hearts in praise, prayer, and divine presence.' ),
		);
		$out = array();
		foreach ( $defs as $d ) {
			$out[] = array(
				'img'     => dck_home_img( 'dck_slide' . $d['k'] . '_img', $d['img'] ),
				'eyebrow' => dck_home_val( 'dck_slide' . $d['k'] . '_eyebrow', $d['eyebrow'] ),
				'title'   => dck_home_val( 'dck_slide' . $d['k'] . '_title', $d['title'] ),
				'text'    => dck_home_val( 'dck_slide' . $d['k'] . '_text', $d['text'] ),
			);
		}
		return $out;
	}
}

/* ---------------- Sunday column photos ---------------- */

if ( ! function_exists( 'dck_home_sunday_imgs' ) ) {
	function dck_home_sunday_imgs() {
		$defs = array( 'children-praying.webp', 'evangelism.webp', 'joy.webp', 'champions-of-christ.webp' );
		$out  = array();
		$i    = 1;
		foreach ( $defs as $f ) {
			$out[] = dck_home_img( 'dck_sunday_img' . $i, $f );
			$i++;
		}
		return $out;
	}
}

/* ---------------- Customizer ---------------- */

if ( ! function_exists( 'dck_home_ctl_text' ) ) {
	function dck_home_ctl_text( $wp, $id, $section, $label, $default = '', $textarea = false ) {
		$wp->add_setting( $id, array(
			'default'           => $default,
			'sanitize_callback' => $textarea ? 'sanitize_textarea_field' : 'sanitize_text_field',
		) );
		$wp->add_control( $id, array(
			'label'   => $label,
			'section' => $section,
			'type'    => $textarea ? 'textarea' : 'text',
		) );
	}
}

if ( ! function_exists( 'dck_home_ctl_img' ) ) {
	function dck_home_ctl_img( $wp, $id, $section, $label ) {
		$wp->add_setting( $id, array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
		$wp->add_control( new WP_Customize_Image_Control( $wp, $id, array( 'label' => $label, 'section' => $section ) ) );
	}
}

if ( ! function_exists( 'dck_home_customize' ) ) {
	function dck_home_customize( $wp_customize ) {
		$panel = 'dck_home_panel';
		$wp_customize->add_panel( $panel, array( 'title' => __( 'DCK Homepage', 'dck-elite' ), 'priority' => 22 ) );

		$wp_customize->add_section( 'dck_home_top', array( 'title' => __( 'Top Bar and Footer', 'dck-elite' ), 'panel' => $panel ) );
		dck_home_ctl_text( $wp_customize, 'dck_top_email', 'dck_home_top', __( 'Email address', 'dck-elite' ), 'info@dckabeten.org' );
		dck_home_ctl_text( $wp_customize, 'dck_top_address', 'dck_home_top', __( 'Location', 'dck-elite' ), 'Kabete Behind N Market' );
		dck_home_ctl_text( $wp_customize, 'dck_top_phone', 'dck_home_top', __( 'Phone', 'dck-elite' ), '+254 110 304521' );
		dck_home_ctl_text( $wp_customize, 'dck_fb_url', 'dck_home_top', __( 'Facebook URL', 'dck-elite' ), 'https://www.facebook.com/' );
		dck_home_ctl_text( $wp_customize, 'dck_yt_url', 'dck_home_top', __( 'YouTube URL', 'dck-elite' ), 'https://www.youtube.com/' );
		dck_home_ctl_text( $wp_customize, 'dck_ig_url', 'dck_home_top', __( 'Instagram URL', 'dck-elite' ), '' );
		dck_home_ctl_text( $wp_customize, 'dck_tiktok_url', 'dck_home_top', __( 'TikTok URL', 'dck-elite' ), '' );
		dck_home_ctl_text( $wp_customize, 'dck_x_url', 'dck_home_top', __( 'X (Twitter) URL', 'dck-elite' ), '' );
		dck_home_ctl_text( $wp_customize, 'dck_copyright', 'dck_home_top', __( 'Footer copyright name', 'dck-elite' ), 'Deliverance Church Kabete N' );

		$wp_customize->add_section( 'dck_home_hero', array( 'title' => __( 'Hero Slides', 'dck-elite' ), 'panel' => $panel ) );
		foreach ( array( '1', '2', '3' ) as $n ) {
			dck_home_ctl_img( $wp_customize, 'dck_slide' . $n . '_img', 'dck_home_hero', sprintf( __( 'Slide %s image', 'dck-elite' ), $n ) );
			dck_home_ctl_text( $wp_customize, 'dck_slide' . $n . '_eyebrow', 'dck_home_hero', sprintf( __( 'Slide %s small label', 'dck-elite' ), $n ) );
			dck_home_ctl_text( $wp_customize, 'dck_slide' . $n . '_title', 'dck_home_hero', sprintf( __( 'Slide %s title', 'dck-elite' ), $n ) );
			dck_home_ctl_text( $wp_customize, 'dck_slide' . $n . '_text', 'dck_home_hero', sprintf( __( 'Slide %s text', 'dck-elite' ), $n ), '', true );
		}

		$wp_customize->add_section( 'dck_home_min', array( 'title' => __( 'Ministry Departments', 'dck-elite' ), 'panel' => $panel ) );
		foreach ( dck_home_min_defs() as $d ) {
			dck_home_ctl_img( $wp_customize, 'dck_min_' . $d['k'] . '_img', 'dck_home_min', $d['name'] . ' - photo' );
			dck_home_ctl_text( $wp_customize, 'dck_min_' . $d['k'] . '_name', 'dck_home_min', $d['name'] . ' - name', $d['name'] );
			dck_home_ctl_text( $wp_customize, 'dck_min_' . $d['k'] . '_desc', 'dck_home_min', $d['name'] . ' - text', $d['desc'], true );
		}

		$wp_customize->add_section( 'dck_home_dept', array( 'title' => __( 'Care and Protocol', 'dck-elite' ), 'panel' => $panel ) );
		dck_home_ctl_img( $wp_customize, 'dck_care_img', 'dck_home_dept', __( 'Care Ministry photo', 'dck-elite' ) );
		dck_home_ctl_text( $wp_customize, 'dck_care_title', 'dck_home_dept', __( 'Care Ministry title', 'dck-elite' ), 'Care Ministry' );
		dck_home_ctl_text( $wp_customize, 'dck_care_text', 'dck_home_dept', __( 'Care Ministry text', 'dck-elite' ), 'Handles ushering and hospitality, ensuring a welcoming experience for all.', true );
		dck_home_ctl_img( $wp_customize, 'dck_protocol_img', 'dck_home_dept', __( 'Protocol Department photo', 'dck-elite' ) );
		dck_home_ctl_text( $wp_customize, 'dck_protocol_title', 'dck_home_dept', __( 'Protocol Department title', 'dck-elite' ), 'Protocol Department' );
		dck_home_ctl_text( $wp_customize, 'dck_protocol_text', 'dck_home_dept', __( 'Protocol Department text', 'dck-elite' ), 'Oversees the welfare and honor of our pastors and visiting gospel ministers.', true );

		$wp_customize->add_section( 'dck_home_sun', array( 'title' => __( 'Join Us Sunday', 'dck-elite' ), 'panel' => $panel ) );
		dck_home_ctl_text( $wp_customize, 'dck_sunday_title', 'dck_home_sun', __( 'Sunday heading', 'dck-elite' ), 'Join Us for a Joyful Sunday Gathering' );
		dck_home_ctl_text( $wp_customize, 'dck_sunday_tagline', 'dck_home_sun', __( 'Sunday tagline', 'dck-elite' ), 'Experience faith, fellowship, and inspiration every Sunday.', true );
		foreach ( array( '1', '2', '3', '4' ) as $n ) {
			dck_home_ctl_img( $wp_customize, 'dck_sunday_img' . $n, 'dck_home_sun', sprintf( __( 'Sunday photo %s', 'dck-elite' ), $n ) );
		}
	}
}
add_action( 'customize_register', 'dck_home_customize' );

/* ---------------- v2.2.2 extras: directions URL + editable Sunday/CTA ---------------- */
if ( ! function_exists( 'dck_home_directions' ) ) {
	function dck_home_directions() {
		return dck_home_val( 'dck_directions_url', 'https://share.google/LJmcXS6D5Nq1aF2R5' );
	}
}
add_action( 'customize_register', 'dck_home_extra_customize', 30 );
if ( ! function_exists( 'dck_home_extra_customize' ) ) {
	function dck_home_extra_customize( $wp ) {
		if ( ! function_exists( 'dck_home_ctl_text' ) ) {
			return;
		}
		dck_home_ctl_text( $wp, 'dck_directions_url', 'dck_home_top', __( 'Plan Your Visit link (Google Maps / directions)', 'dck-elite' ), 'https://share.google/LJmcXS6D5Nq1aF2R5' );
		dck_home_ctl_text( $wp, 'dck_sunday_eyebrow', 'dck_home_sun', __( 'Sunday eyebrow', 'dck-elite' ), 'Join Us This Sunday' );
		dck_home_ctl_text( $wp, 'dck_sunday_heading', 'dck_home_sun', __( 'Sunday heading', 'dck-elite' ), 'A Joyful Sunday Gathering' );
		dck_home_ctl_text( $wp, 'dck_sunday_tag2', 'dck_home_sun', __( 'Sunday supporting text', 'dck-elite' ), 'Experience faith, fellowship, and inspiration every Sunday. Wherever you are on your journey, there is a place for you at the table.', true );
		$wp->add_section( 'dck_home_cta', array( 'title' => __( 'Family Call to Action', 'dck-elite' ), 'panel' => 'dck_home_panel' ) );
		dck_home_ctl_text( $wp, 'dck_cta_eyebrow', 'dck_home_cta', __( 'Eyebrow', 'dck-elite' ), 'Come As You Are' );
		dck_home_ctl_text( $wp, 'dck_cta_title', 'dck_home_cta', __( 'Heading', 'dck-elite' ), 'Become Part of Our Family' );
		dck_home_ctl_text( $wp, 'dck_cta_text', 'dck_home_cta', __( 'Text', 'dck-elite' ), 'Plan your first visit, join a ministry, or give toward the work God is doing at DCK-N. There is a place for you here.', true );
	}
}


/* Shared social icon row (header + footer). Renders only links that are set. */
if ( ! function_exists( 'dck_social_icons' ) ) {
	function dck_social_icons() {
		$items = array(
			array( 'key' => 'dck_fb_url', 'def' => 'https://www.facebook.com/', 'label' => 'Facebook', 'svg' => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true" focusable="false"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.8 3.8-3.8 1.1 0 2.2.2 2.2.2v2.4h-1.2c-1.2 0-1.6.76-1.6 1.5V12h2.7l-.43 2.9h-2.3v7A10 10 0 0 0 22 12Z"/></svg>' ),
			array( 'key' => 'dck_yt_url', 'def' => 'https://www.youtube.com/', 'label' => 'YouTube', 'svg' => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true" focusable="false"><path d="M23 12s0-3.2-.4-4.7a2.5 2.5 0 0 0-1.8-1.8C19.3 5 12 5 12 5s-7.3 0-8.8.5A2.5 2.5 0 0 0 1.4 7.3C1 8.8 1 12 1 12s0 3.2.4 4.7a2.5 2.5 0 0 0 1.8 1.8C4.7 19 12 19 12 19s7.3 0 8.8-.5a2.5 2.5 0 0 0 1.8-1.8C23 15.2 23 12 23 12ZM9.8 15.3V8.7l5.7 3.3Z"/></svg>' ),
			array( 'key' => 'dck_ig_url', 'def' => '', 'label' => 'Instagram', 'svg' => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12 2c2.7 0 3 0 4.1.06 1 .05 1.7.24 2.3.5.6.24 1.1.55 1.6 1.05.5.5.8 1 1.05 1.6.26.6.45 1.3.5 2.3.05 1.1.06 1.4.06 4.1s0 3-.06 4.1c-.05 1-.24 1.7-.5 2.3a4.5 4.5 0 0 1-1.05 1.6 4.5 4.5 0 0 1-1.6 1.05c-.6.26-1.3.45-2.3.5-1.1.05-1.4.06-4.1.06s-3 0-4.1-.06c-1-.05-1.7-.24-2.3-.5a4.5 4.5 0 0 1-1.6-1.05 4.5 4.5 0 0 1-1.05-1.6c-.26-.6-.45-1.3-.5-2.3C2 15 2 14.7 2 12s0-3 .06-4.1c.05-1 .24-1.7.5-2.3A4.5 4.5 0 0 1 3.6 4 4.5 4.5 0 0 1 5.2 2.95c.6-.26 1.3-.45 2.3-.5C8.6 2 8.9 2 12 2Zm0 1.8c-2.7 0-3 0-4 .06-.8.04-1.2.17-1.5.3-.4.14-.6.3-.9.6-.3.3-.46.5-.6.9-.13.3-.26.7-.3 1.5-.06 1-.06 1.3-.06 4s0 3 .06 4c.04.8.17 1.2.3 1.5.14.4.3.6.6.9.3.3.5.46.9.6.3.13.7.26 1.5.3 1 .06 1.3.06 4 .06s3 0 4-.06c.8-.04 1.2-.17 1.5-.3.4-.14.6-.3.9-.6.3-.3.46-.5.6-.9.13-.3.26-.7.3-1.5.06-1 .06-1.3.06-4s0-3-.06-4c-.04-.8-.17-1.2-.3-1.5a2.7 2.7 0 0 0-.6-.9 2.7 2.7 0 0 0-.9-.6c-.3-.13-.7-.26-1.5-.3-1-.06-1.3-.06-4-.06Zm0 3.06a5.14 5.14 0 1 1 0 10.28 5.14 5.14 0 0 1 0-10.28Zm0 1.8a3.34 3.34 0 1 0 0 6.68 3.34 3.34 0 0 0 0-6.68Zm5.34-3.2a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4Z"/></svg>' ),
			array( 'key' => 'dck_tiktok_url', 'def' => '', 'label' => 'TikTok', 'svg' => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true" focusable="false"><path d="M16.5 3c.3 2.1 1.5 3.4 3.5 3.6v2.4c-1.2.1-2.4-.3-3.5-1v5.9c0 3-2.2 5.1-5.1 5.1A5 5 0 0 1 6.3 14a5 5 0 0 1 6.2-4.9v2.5a2.5 2.5 0 0 0-3.3 2.4 2.5 2.5 0 0 0 5 .2V3h2.3Z"/></svg>' ),
			array( 'key' => 'dck_x_url', 'def' => '', 'label' => 'X', 'svg' => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true" focusable="false"><path d="M17.5 3h3l-6.6 7.6L21.8 21h-6l-4.7-6.1L5.6 21H2.5l7-8.1L2.4 3h6.1l4.3 5.7L17.5 3Zm-1 16h1.7L8.1 4.8H6.3L16.5 19Z"/></svg>' ),
		);
		foreach ( $items as $it ) {
			$url = trim( (string) dck_home_val( $it['key'], $it['def'] ) );
			if ( '' === $url ) {
				continue;
			}
			echo '<a class="dck-social" href="' . esc_url( $url ) . '" aria-label="' . esc_attr( $it['label'] ) . '" target="_blank" rel="noopener noreferrer">' . $it['svg'] . '</a>';
		}
	}
}

/* --- v3.6.0: About / Welcome Customizer panel (editable welcome + faith text) --- */
add_action( 'customize_register', 'dck_home_about_customize', 35 );
if ( ! function_exists( 'dck_home_about_customize' ) ) {
	function dck_home_about_customize( $wp ) {
		if ( ! function_exists( 'dck_home_ctl_text' ) ) {
			return;
		}
		$wp->add_section( 'dck_home_about', array(
			'title' => __( 'About / Welcome Text', 'dck-elite' ),
			'panel' => 'dck_home_panel',
		) );
		dck_home_ctl_text( $wp, 'dck_lead_text', 'dck_home_about',
			__( 'Welcome paragraph', 'dck-elite' ),
			'Whether you are visiting for the first time or looking for a church family, there is a place for you at Deliverance Church Kabete N (DCK-N). We are a Christ-centered community founded on prayer, evangelism and revival, believing God for transformed lives and a transformed nation.',
			true
		);
		dck_home_ctl_text( $wp, 'dck_about_faith_text', 'dck_home_about',
			__( 'Built by Faith card text', 'dck-elite' ),
			'From a first prayer meeting on 9 June 2017 and a launch on 18 June 2017, God has grown Deliverance Church Kabete N (DCK-N) from a small sanctuary in N Market into a thriving church family in Uthiru, building faithfully on ground once undeveloped.',
			true
		);
	}
}


/* --- v3.7.0: Full content Customizer — stats, ministry header, global buttons, gallery page --- */
add_action( 'customize_register', 'dck_home_v37_customize', 40 );
if ( ! function_exists( 'dck_home_v37_customize' ) ) {
	function dck_home_v37_customize( $wp ) {
		if ( ! function_exists( 'dck_home_ctl_text' ) ) {
			return;
		}
		/* About / Welcome extras (section created at priority 35) */
		dck_home_ctl_text( $wp, 'dck_welcome_eyebrow',     'dck_home_about', __( 'Welcome eyebrow', 'dck-elite' ),      'A Word of Welcome' );
		dck_home_ctl_text( $wp, 'dck_welcome_heading',     'dck_home_about', __( 'Welcome heading', 'dck-elite' ),      'Karibu Home, You Belong Here' );
		dck_home_ctl_text( $wp, 'dck_welcome_pastors',     'dck_home_about', __( 'Pastors line', 'dck-elite' ),         'Led by Pastor Justus Kubai and Mrs. Clare Kubai, with Assistant Pastors Mr. and Mrs. Henry Onzere.', true );
		dck_home_ctl_text( $wp, 'dck_about_since',         'dck_home_about', __( 'Faith card date tag', 'dck-elite' ),  'Since 2017' );
		dck_home_ctl_text( $wp, 'dck_about_faith_heading', 'dck_home_about', __( 'Faith card heading', 'dck-elite' ),   'Built by Faith' );

		/* Stats counters */
		$wp->add_section( 'dck_home_stats', array( 'title' => __( 'Stats Counters', 'dck-elite' ), 'panel' => 'dck_home_panel' ) );
		dck_home_ctl_text( $wp, 'dck_stat1_num',   'dck_home_stats', __( 'Stat 1 number', 'dck-elite' ), '850' );
		dck_home_ctl_text( $wp, 'dck_stat1_label', 'dck_home_stats', __( 'Stat 1 label',  'dck-elite' ), 'Members and Growing' );
		dck_home_ctl_text( $wp, 'dck_stat2_num',   'dck_home_stats', __( 'Stat 2 number', 'dck-elite' ), '14' );
		dck_home_ctl_text( $wp, 'dck_stat2_label', 'dck_home_stats', __( 'Stat 2 label',  'dck-elite' ), 'Ministry Departments' );
		dck_home_ctl_text( $wp, 'dck_stat3_num',   'dck_home_stats', __( 'Stat 3 number', 'dck-elite' ), '8' );
		dck_home_ctl_text( $wp, 'dck_stat3_label', 'dck_home_stats', __( 'Stat 3 label',  'dck-elite' ), 'Years of Ministry' );
		dck_home_ctl_text( $wp, 'dck_stat4_num',   'dck_home_stats', __( 'Stat 4 number', 'dck-elite' ), '12' );
		dck_home_ctl_text( $wp, 'dck_stat4_label', 'dck_home_stats', __( 'Stat 4 label',  'dck-elite' ), 'Outreach Projects' );

		/* Ministry section heading */
		$wp->add_section( 'dck_home_minsec', array( 'title' => __( 'Ministry Section Header', 'dck-elite' ), 'panel' => 'dck_home_panel' ) );
		dck_home_ctl_text( $wp, 'dck_min_eyebrow', 'dck_home_minsec', __( 'Eyebrow', 'dck-elite' ),       'Get Involved' );
		dck_home_ctl_text( $wp, 'dck_min_heading',  'dck_home_minsec', __( 'Heading', 'dck-elite' ),       'Our Ministry Departments' );
		dck_home_ctl_text( $wp, 'dck_min_lead',     'dck_home_minsec', __( 'Lead paragraph', 'dck-elite' ), 'There is a family for every season of life. These are the departments that drive our growth and impact.', true );

		/* Global button labels */
		$wp->add_section( 'dck_home_btns', array( 'title' => __( 'Global Button Labels', 'dck-elite' ), 'panel' => 'dck_home_panel' ) );
		dck_home_ctl_text( $wp, 'dck_btn_plan_visit',     'dck_home_btns', __( 'Plan Your Visit button',    'dck-elite' ), 'Plan Your Visit' );
		dck_home_ctl_text( $wp, 'dck_btn_watch_live',     'dck_home_btns', __( 'Watch Live button',         'dck-elite' ), 'Watch Live' );
		dck_home_ctl_text( $wp, 'dck_btn_our_story',      'dck_home_btns', __( 'Our Story button',          'dck-elite' ), 'Our Story' );
		dck_home_ctl_text( $wp, 'dck_btn_give_online',    'dck_home_btns', __( 'Give Online button',        'dck-elite' ), 'Give Online' );
		dck_home_ctl_text( $wp, 'dck_btn_all_ministries', 'dck_home_btns', __( 'View All Ministries button','dck-elite' ), 'View All Ministries' );
		dck_home_ctl_text( $wp, 'dck_btn_prayer_request', 'dck_home_btns', __( 'Prayer Request button',     'dck-elite' ), 'Prayer Request' );

		/* Gallery page hero text */
		$wp->add_section( 'dck_home_gallery', array( 'title' => __( 'Gallery Page Text', 'dck-elite' ), 'panel' => 'dck_home_panel' ) );
		dck_home_ctl_text( $wp, 'dck_gallery_eyebrow', 'dck_home_gallery', __( 'Gallery eyebrow',   'dck-elite' ), 'Moments of Grace' );
		dck_home_ctl_text( $wp, 'dck_gallery_title',   'dck_home_gallery', __( 'Gallery page title','dck-elite' ), 'Our Photo Gallery' );
		dck_home_ctl_text( $wp, 'dck_gallery_lead',    'dck_home_gallery', __( 'Gallery lead text', 'dck-elite' ), 'A window into the life of our church family — worship, fellowship, and the goodness of God.', true );
	}
}
