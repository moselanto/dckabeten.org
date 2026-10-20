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
		$topic = isset( $p['topic'] ) ? sanitize_text_field( $p['topic'] ) : '';
		$topic = function_exists( 'dck_sec_cap' ) ? dck_sec_cap( $topic, 60 ) : substr( $topic, 0, 60 );
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
		$body .= 'Phone: ' . $phone . PHP_EOL;
		$body .= ( $topic ? 'Topic: ' . $topic . PHP_EOL : '' ) . PHP_EOL . $msg;
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
			$headers = is_email( $email ) ? array( 'Reply-To: ' . $name . ' <' . $email . '>' ) : array();
			wp_mail( $to, 'New Website Message' . ( $topic ? ' (' . $topic . ')' : '' ) . ' - DCK', $body, $headers );
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

/* Front-end script for the Contact page. */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! is_page_template( 'template-contact.php' ) ) {
			return;
		}
		wp_enqueue_script( 'dck-contact', get_template_directory_uri() . '/assets/js/contact.js', array(), DCK_ELITE_VERSION, true );
		wp_localize_script(
			'dck-contact',
			'DCK_CONTACT',
			array(
				'phone'       => function_exists( 'dck_phone_display' ) ? dck_phone_display() : '',
				'sending'     => __( 'Sending...', 'dck-elite' ),
				'thanks'      => __( 'Thank you. Your message has been received.', 'dck-elite' ),
				'alsoWa'      => __( 'Also send on WhatsApp', 'dck-elite' ),
				'errRequired' => __( 'Please enter your name, email and a message.', 'dck-elite' ),
				'errEmail'    => __( 'Please enter a valid email address so we can reply.', 'dck-elite' ),
				'errNet'      => __( 'Something went wrong. Please call us on', 'dck-elite' ),
			)
		);
	}
);
