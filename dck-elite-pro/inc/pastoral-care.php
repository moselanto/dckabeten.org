<?php
/**
 * Pastoral care: book time with a pastor and request church services
 * (baptism, baby dedication, wedding, membership class, home/hospital visit, funeral support).
 * Page /pastoral-care/ -> REST dck/v1/care -> wp-admin "Pastoral Requests".
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_care_types' ) ) {
	function dck_care_types() {
		return array(
			'counselling' => __( 'Counselling with a pastor', 'dck-elite' ),
			'prayer'      => __( 'Prayer with a pastor', 'dck-elite' ),
			'home'        => __( 'Home visit', 'dck-elite' ),
			'hospital'    => __( 'Hospital visit', 'dck-elite' ),
			'baptism'     => __( 'Water baptism', 'dck-elite' ),
			'dedication'  => __( 'Baby dedication', 'dck-elite' ),
			'wedding'     => __( 'Wedding / marriage preparation', 'dck-elite' ),
			'membership'  => __( 'Membership class', 'dck-elite' ),
			'funeral'     => __( 'Funeral / bereavement support', 'dck-elite' ),
		);
	}
}
if ( ! function_exists( 'dck_care_times' ) ) {
	function dck_care_times() {
		return array(
			'morning'   => __( 'Morning (9am - 12pm)', 'dck-elite' ),
			'afternoon' => __( 'Afternoon (12pm - 4pm)', 'dck-elite' ),
			'evening'   => __( 'Evening (4pm - 7pm)', 'dck-elite' ),
			'any'       => __( 'Any time', 'dck-elite' ),
		);
	}
}

add_action(
	'init',
	function () {
	dck_rec_register(
		'dck_care',
		array(
			'label'    => __( 'Pastoral Requests', 'dck-elite' ),
			'single'   => __( 'Pastoral request', 'dck-elite' ),
			'icon'     => 'dashicons-heart',
			'position' => 26,
			'csv'      => 'pastoral-requests',
			'csv_skip' => array( 'details' ),
			'statuses' => array(
				'new'       => array( __( 'New', 'dck-elite' ), 'amber' ),
				'scheduled' => array( __( 'Scheduled', 'dck-elite' ), 'blue' ),
				'done'      => array( __( 'Done', 'dck-elite' ), 'green' ),
				'closed'    => array( __( 'Closed', 'dck-elite' ), 'gray' ),
			),
			'columns'  => array( 'type' => __( 'Request', 'dck-elite' ), 'phone' => __( 'Phone', 'dck-elite' ), 'date' => __( 'Preferred day', 'dck-elite' ), 'slot' => __( 'Time', 'dck-elite' ) ),
			'fields'   => array( 'type' => 'Request', 'name' => 'Name', 'phone' => 'Phone', 'email' => 'Email', 'date' => 'Preferred day', 'slot' => 'Preferred time', 'private' => 'Confidential', 'details' => 'Details' ),
			'display'  => array(
				'type'    => function ( $v ) { $t = dck_care_types(); return isset( $t[ $v ] ) ? $t[ $v ] : $v; },
				'slot'    => function ( $v ) { $t = dck_care_times(); return isset( $t[ $v ] ) ? $t[ $v ] : $v; },
				'private' => function ( $v ) { return $v ? 'Yes - pastors only' : 'No'; },
				'date'    => function ( $v ) { return $v ? wp_date( 'D j M Y', strtotime( $v . ' 12:00' ) ) : ''; },
			),
			'filter'         => 'type',
			'filter_label'   => __( 'All request types', 'dck-elite' ),
			'filter_options' => 'dck_care_types',
			'wa'             => function ( $id ) {
				$t = dck_care_types();
				$k = get_post_meta( $id, '_dck_type', true );
				return array(
					get_post_meta( $id, '_dck_phone', true ),
					dck_ct_fill( dck_home_val( 'dck_care_wa_text', 'Hello {name}, thank you for reaching out to Deliverance Church Kabete N about {purpose}. A member of our pastoral team will confirm a time with you shortly. God bless you.' ), array( 'name' => get_post_meta( $id, '_dck_name', true ), 'purpose' => strtolower( isset( $t[ $k ] ) ? $t[ $k ] : $k ) ) ),
					__( 'Reply', 'dck-elite' ),
				);
			},
		)
	);
	},
	1
);

add_action(
	'rest_api_init',
	function () {
		register_rest_route( 'dck/v1', '/care', array( 'methods' => 'POST', 'callback' => 'dck_care_rest', 'permission_callback' => '__return_true' ) );
	}
);
if ( ! function_exists( 'dck_care_rest' ) ) {
	function dck_care_rest( $request ) {
		$p = (array) $request->get_json_params();
		$g = dck_sec_guard( $request, $p, 'care', 3000, 5, 900 );
		if ( true !== $g ) {
			return $g;
		}
		$type  = sanitize_key( isset( $p['type'] ) ? $p['type'] : '' );
		$slot  = sanitize_key( isset( $p['slot'] ) ? $p['slot'] : 'any' );
		$name  = dck_sec_cap( sanitize_text_field( isset( $p['name'] ) ? $p['name'] : '' ), 120 );
		$phone = dck_sec_cap( sanitize_text_field( isset( $p['phone'] ) ? $p['phone'] : '' ), 40 );
		$email = dck_sec_cap( sanitize_email( isset( $p['email'] ) ? $p['email'] : '' ), 190 );
		$det   = dck_sec_cap( sanitize_textarea_field( isset( $p['details'] ) ? $p['details'] : '' ), 3000 );
		$date  = ( isset( $p['date'] ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $p['date'] ) && $p['date'] >= current_time( 'Y-m-d' ) ) ? $p['date'] : '';
		$priv  = empty( $p['private'] ) ? '' : '1';
		if ( ! isset( dck_care_types()[ $type ] ) || '' === $name || ! dck_ct_wa_number( $phone ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Please choose what you need and enter your name and a valid phone number.', 'dck-elite' ) ), 200 );
		}
		if ( ! isset( dck_care_times()[ $slot ] ) ) {
			$slot = 'any';
		}
		if ( $email && ! is_email( $email ) ) {
			$email = '';
		}
		$label = dck_care_types()[ $type ];
		$id    = dck_rec_insert( 'dck_care', $name . ' - ' . $label, array( 'type' => $type, 'name' => $name, 'phone' => $phone, 'email' => $email, 'date' => $date, 'slot' => $slot, 'private' => $priv, 'details' => $det ), 'new' );
		if ( ! $id ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'We could not save your request. Please call us.', 'dck-elite' ) ), 200 );
		}
		$body  = "New pastoral request: $label\n\nName: $name\nPhone: $phone\nEmail: $email\nPreferred day: " . ( $date ? $date : 'Any' ) . "\nPreferred time: " . dck_care_times()[ $slot ] . "\n";
		$body .= $priv ? "\nThis request is CONFIDENTIAL - details are in wp-admin only.\n" : ( $det ? "\nDetails:\n$det\n" : '' );
		$body .= "\nOpen: " . admin_url( 'post.php?post=' . $id . '&action=edit' );
		wp_mail( dck_ct_email_to( 'dck_care_email' ), "Pastoral request: $label - $name", $body );
		return new WP_REST_Response( array( 'ok' => true, 'message' => __( 'Thank you. Your request has been received - a member of our pastoral team will call or WhatsApp you to confirm a time.', 'dck-elite' ) ), 200 );
	}
}
