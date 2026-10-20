<?php
/**
 * DCK Elite - Editable images (helpers + Customizer controls).
 * Lets the church change key photos from Appearance > Customize > DCK Images,
 * with graceful monogram placeholders when a pastor photo is not set yet.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_theme_image' ) ) {
	function dck_theme_image( $mod, $rel_default ) {
		$v = trim( (string) get_theme_mod( $mod, '' ) );
		if ( $v ) {
			return $v;
		}
		return get_template_directory_uri() . '/' . ltrim( $rel_default, '/' );
	}
}

if ( ! function_exists( 'dck_pastor_photo_html' ) ) {
	function dck_pastor_photo_html( $mod, $alt, $initials, $default_rel = '' ) {
		$url = trim( (string) get_theme_mod( $mod, '' ) );
		if ( '' === $url && $default_rel ) {
			$url = get_template_directory_uri() . '/' . ltrim( $default_rel, '/' );
		}
		if ( $url ) {
			return '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy">';
		}
		$ini = $initials ? $initials : 'DCK';
		return '<span class="dck-pastor__ph" role="img" aria-label="' . esc_attr( $alt ) . '">' . esc_html( $ini ) . '</span>';
	}
}

if ( ! function_exists( 'dck_media_customize' ) ) {
	function dck_media_customize( $wp_customize ) {
		$wp_customize->add_section( 'dck_media', array(
			'title'       => __( 'DCK Images', 'dck-elite' ),
			'description' => __( 'Change key photos across the site. Leave a pastor photo empty to show a clean monogram placeholder until you upload one.', 'dck-elite' ),
			'priority'    => 35,
		) );
		$imgs = array(
			'dck_about_image'        => __( 'About page - story image', 'dck-elite' ),
			'dck_contact_image'      => __( 'Contact page - image', 'dck-elite' ),
			'dck_pastors_couple_img' => __( 'Our Pastors - lead couple photo', 'dck-elite' ),
			'dck_pastor_justus_img'  => __( 'Pastor Justus Kubai - photo', 'dck-elite' ),
			'dck_pastor_clare_img'   => __( 'Pastor Clare Kubai - photo', 'dck-elite' ),
			'dck_pastor_henry_img'   => __( 'Pastor Henry Onzere - photo', 'dck-elite' ),
			'dck_pastor_nancy_img'   => __( 'Pastor Nancy Onzere - photo', 'dck-elite' ),
		);
		foreach ( $imgs as $id => $label ) {
			$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $id, array(
				'label'   => $label,
				'section' => 'dck_media',
			) ) );
		}
	}
}
add_action( 'customize_register', 'dck_media_customize' );
