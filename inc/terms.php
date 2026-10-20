<?php
/**
 * DCK Elite - Terms page content + photography opt-out form.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   1. Terms + Privacy + Photography page content
   ============================================================ */
if ( ! function_exists( 'dck_elite_terms_content' ) ) {
	function dck_elite_terms_content() {
		$h  = '<h2>Terms of Use</h2>';
		$h .= '<p>By using this website you agree to these Terms of Use. This site serves Deliverance Church Kabete N Market (Cistern of Hope Center), Uthiru, Nairobi, Kenya.</p>';
		$h .= '<h3>Online tools</h3>';
		$h .= '<p>Our AI assistant and Bible study tools are for encouragement and information only. They are not a substitute for pastoral counsel or professional advice. In an emergency contact local emergency services or call us on +254 110 304 521.</p>';
		$h .= '<h3>Giving</h3>';
		$h .= '<p>Always confirm giving details on our Giving page before sending a gift. Contact us on +254 110 304 521 with any questions.</p>';
		$h .= '<h3>Content</h3>';
		$h .= '<p>Sermons, teachings, and other materials are for personal encouragement. Please do not reproduce or distribute them commercially without our permission.</p>';
		$h .= '<h2>Privacy Notice</h2>';
		$h .= '<p>We only collect information we need to serve you.</p>';
		$h .= '<ul>';
		$h .= '<li><strong>Contact form</strong> — your name, email, phone, and message.</li>';
		$h .= '<li><strong>Prayer request form</strong> — your name, phone, email, and prayer request.</li>';
		$h .= '<li><strong>Photography preference form</strong> — your name, phone, email, and signature.</li>';
		$h .= '</ul>';
		$h .= '<p>We use your details to reply, pray for you, and honour your preferences. We do not sell your information.</p>';
		$h .= '<h2>Photography and Video at Church</h2>';
		$h .= '<p>We sometimes photograph and record video during services and events to share what God is doing. Only authorised media team members may do this on behalf of the church.</p>';
		$h .= '<p>By attending you may appear in photos or video. If you prefer not to be photographed or recorded, please complete the form below or speak to an usher when you arrive.</p>';
		return $h;
	}
}

/* ============================================================
   2. CPT for storing opt-out records
   ============================================================ */
add_action( 'init', 'dck_optout_register_cpt' );

if ( ! function_exists( 'dck_optout_register_cpt' ) ) {
	function dck_optout_register_cpt() {
		if ( post_type_exists( 'dck_photo_optout' ) ) {
			return;
		}
		register_post_type( 'dck_photo_optout', array(
			'labels'   => array(
				'name'          => __( 'Photo Preferences', 'dck-elite' ),
				'singular_name' => __( 'Photo Preference',  'dck-elite' ),
				'menu_name'     => __( 'Photo Preferences', 'dck-elite' ),
			),
			'public'   => false,
			'show_ui'  => true,
			'menu_icon'=> 'dashicons-camera',
			'supports' => array( 'title', 'editor' ),
		) );
	}
}

/* ============================================================
   3. REST endpoint — photography opt-out form submission
   ============================================================ */
add_action( 'rest_api_init', 'dck_optout_rest_init' );

if ( ! function_exists( 'dck_optout_rest_init' ) ) {
	function dck_optout_rest_init() {
		register_rest_route( 'dck/v1', '/photo-optout', array(
			'methods'             => 'POST',
			'callback'            => 'dck_optout_rest_handler',
			'permission_callback' => '__return_true',
		) );
	}
}

if ( ! function_exists( 'dck_optout_rest_handler' ) ) {
	function dck_optout_rest_handler( $request ) {
		$p = $request->get_json_params();
		if ( ! is_array( $p ) ) {
			$p = array();
		}

		/* Anti-spam guard */
		if ( function_exists( 'dck_sec_guard' ) ) {
			$guard = dck_sec_guard( $request, $p, 'optout', 4000, 4, 900 );
			if ( true !== $guard ) {
				return $guard;
			}
		}

		/* Sanitize */
		$name    = isset( $p['name'] )      ? sanitize_text_field( wp_unslash( (string) $p['name'] ) )    : '';
		$phone   = isset( $p['phone'] )     ? sanitize_text_field( wp_unslash( (string) $p['phone'] ) )   : '';
		$email   = isset( $p['email'] )     ? sanitize_email( wp_unslash( (string) $p['email'] ) )        : '';
		$service = isset( $p['service'] )   ? sanitize_text_field( wp_unslash( (string) $p['service'] ) ) : '';
		$sig     = isset( $p['signature'] ) ? (string) $p['signature']                                    : '';

		if ( function_exists( 'dck_sec_cap' ) ) {
			$name    = dck_sec_cap( $name,    120 );
			$phone   = dck_sec_cap( $phone,   30  );
			$email   = dck_sec_cap( $email,   190 );
			$service = dck_sec_cap( $service, 120 );
		}

		/* Validate required fields */
		if ( '' === trim( $name ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => 'Please enter your full name.' ), 200 );
		}
		if ( '' === trim( $phone ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => 'Phone number is required.' ), 200 );
		}
		if ( '' === trim( $email ) || ! is_email( $email ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => 'A valid email address is required.' ), 200 );
		}

		/* Validate signature */
		$sig_ok = function_exists( 'dck_sec_signature_ok' )
			? dck_sec_signature_ok( $sig )
			: ( '' !== $sig && 0 === strpos( $sig, 'data:image/png;base64,' ) && strlen( $sig ) < 700000 );

		if ( ! $sig_ok ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => 'Please draw your signature before submitting.' ), 200 );
		}

		/* Save the record */
		$title = 'Photo Opt-Out: ' . $name . ' (' . current_time( 'Y-m-d H:i' ) . ')';
		$body  = 'Name: '            . $name    . PHP_EOL
		       . 'Phone: '           . $phone   . PHP_EOL
		       . 'Email: '           . $email   . PHP_EOL
		       . 'Usually attends: ' . $service . PHP_EOL . PHP_EOL
		       . 'This person has asked NOT to be photographed or recorded. Signature on file.';

		$post_id = wp_insert_post( array(
			'post_type'    => 'dck_photo_optout',
			'post_title'   => $title,
			'post_content' => $body,
			'post_status'  => 'private',
		) );

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, '_dck_optout_sig', $sig );
		}

		/* Email admin */
		$admin = get_option( 'admin_email' );
		if ( $admin ) {
			wp_mail( $admin, 'Photo Opt-Out: ' . $name, $body );
		}

		return new WP_REST_Response( array(
			'ok'      => true,
			'message' => 'Thank you. We have noted your preference and our media team will be made aware.',
		), 200 );
	}
}
