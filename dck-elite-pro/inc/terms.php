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
	/**
	 * Full legal text as HTML (built from the editable sections).
	 */
	function dck_elite_terms_content() {
		$h = '';
		if ( function_exists( 'dck_legal_sections' ) ) {
			foreach ( dck_legal_sections() as $sec ) {
				$h .= '<h2>' . esc_html( $sec['title'] ) . '</h2>' . $sec['body'];
			}
		}
		return $h;
	}
}

/* Front-end script for the Terms page (signature pad + preference form). */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! is_page_template( 'template-terms.php' ) ) {
			return;
		}
		wp_enqueue_script( 'dck-terms', get_template_directory_uri() . '/assets/js/terms.js', array(), DCK_ELITE_VERSION, true );
		wp_localize_script(
			'dck-terms',
			'DCK_TERMS',
			array(
				'phone'    => function_exists( 'dck_phone_display' ) ? dck_phone_display() : '',
				'sending'  => __( 'Sending...', 'dck-elite' ),
				'thanks'   => __( 'Thank you.', 'dck-elite' ),
				'errName'  => __( 'Please enter your full name.', 'dck-elite' ),
				'errPhone' => __( 'Phone number is required.', 'dck-elite' ),
				'errEmail' => __( 'A valid email address is required.', 'dck-elite' ),
				'errSig'   => __( 'Please draw your signature before submitting.', 'dck-elite' ),
				'errTick'  => __( 'Please tick the confirmation box.', 'dck-elite' ),
				'errNet'   => __( 'Something went wrong. Please speak to an usher or call', 'dck-elite' ),
			)
		);
	}
);

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
		$admin = function_exists( 'dck_notify_email' ) ? dck_notify_email() : get_option( 'admin_email' );
		if ( $admin ) {
			wp_mail( $admin, 'Photo Opt-Out: ' . $name, $body );
		}

		return new WP_REST_Response( array(
			'ok'      => true,
			'message' => 'Thank you. We have noted your preference and our media team will be made aware.',
		), 200 );
	}
}
