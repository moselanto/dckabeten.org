<?php
/**
 * DCK Elite - Contact form backend.
 * Stores website messages and emails the church, with a WhatsApp handoff.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_contact_register_cpt' ) ) {
	function dck_contact_register_cpt() {
		if ( post_type_exists( 'dck_message' ) ) {
			return;
		}
		register_post_type( 'dck_message', array(
			'labels'   => array(
				'name'          => 'Messages',
				'singular_name' => 'Message',
				'menu_name'     => 'Messages',
			),
			'public'    => false,
			'show_ui'   => true,
			'menu_icon' => 'dashicons-email-alt',
			'supports'  => array( 'title', 'editor' ),
		) );
	}
}
add_action( 'init', 'dck_contact_register_cpt' );

if ( ! function_exists( 'dck_contact_rest_init' ) ) {
	function dck_contact_rest_init() {
		register_rest_route( 'dck/v1', '/contact', array(
			'methods'             => 'POST',
			'callback'            => 'dck_contact_rest',
			'permission_callback' => '__return_true',
		) );
	}
}
add_action( 'rest_api_init', 'dck_contact_rest_init' );

if ( ! function_exists( 'dck_contact_rest' ) ) {
	function dck_contact_rest( $request ) {
		$p     = $request->get_json_params();
		if ( function_exists( 'dck_sec_guard' ) ) {
			$dck_guard = dck_sec_guard( $request, $p, 'contact' );
			if ( true !== $dck_guard ) { return $dck_guard; }
		}
		$name  = isset( $p['name'] ) ? sanitize_text_field( $p['name'] ) : '';
		$email = isset( $p['email'] ) ? sanitize_email( $p['email'] ) : '';
		$phone = isset( $p['phone'] ) ? sanitize_text_field( $p['phone'] ) : '';
		$msg   = isset( $p['message'] ) ? sanitize_textarea_field( $p['message'] ) : '';
		if ( function_exists( 'dck_sec_cap' ) ) {
			$name  = dck_sec_cap( $name, 120 );
			$email = dck_sec_cap( $email, 190 );
			$phone = dck_sec_cap( $phone, 40 );
			$msg   = dck_sec_cap( $msg, 5000 );
		}
		if ( '' === trim( $name ) || '' === trim( $email ) || ! is_email( $email ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => 'Please enter your name and a valid email so we can reply.' ), 200 );
		}
		if ( '' === trim( $msg ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => 'Please write a short message so we can help.' ), 200 );
		}
		if ( ( function_exists( 'dck_sec_looks_spammy' ) && dck_sec_looks_spammy( $msg . ' ' . $name ) ) || ( function_exists( 'dck_sec_email_disposable' ) && dck_sec_email_disposable( $email ) ) ) {
			return new WP_REST_Response( array( 'ok' => true, 'message' => 'Thank you. Your message has been received.' ), 200 );
		}
		$title = ( $name ? $name : 'Website visitor' ) . ' - ' . current_time( 'Y-m-d H:i' );
		$body  = 'From: ' . ( $name ? $name : 'Anonymous' ) . PHP_EOL;
		$body .= 'Email: ' . $email . PHP_EOL;
		$body .= 'Phone: ' . $phone . PHP_EOL . PHP_EOL . $msg;
		wp_insert_post( array(
			'post_type'    => 'dck_message',
			'post_title'   => $title,
			'post_content' => $body,
			'post_status'  => 'private',
		) );
		$to = get_theme_mod( 'dck_top_email', 'info@dckabeten.org' );
		if ( ! $to ) {
			$to = get_option( 'admin_email' );
		}
		if ( $to ) {
			wp_mail( $to, 'New Website Message - DCK', $body );
		}
		$wa     = function_exists( 'dck_prayer_whatsapp' ) ? dck_prayer_whatsapp() : '254110304521';
		$wa_txt = 'Hello DCK, ' . ( $name ? '(' . $name . ') ' : '' ) . $msg;
		$wa_url = 'https://wa.me/' . $wa . '?text=' . rawurlencode( $wa_txt );
		return new WP_REST_Response( array(
			'ok'       => true,
			'message'  => 'Thank you. Your message has been received - we will get back to you soon.',
			'whatsapp' => $wa_url,
		), 200 );
	}
}
