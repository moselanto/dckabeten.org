<?php
/**
 * Home fellowships (finder + join) and volunteer sign-up by ministry.
 * - Fellowship groups: wp-admin "Fellowship Groups" (area, day/time, venue, leader). Page /fellowships/.
 * - Sign-ups (serve in a ministry or join a fellowship): wp-admin "Volunteers". Page /serve/.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- Fellowship groups (edited by staff) ---------- */
add_action(
	'init',
	function () {
		register_post_type(
			'dck_fellowship',
			array(
				'labels'          => array( 'name' => __( 'Fellowship Groups', 'dck-elite' ), 'singular_name' => __( 'Fellowship Group', 'dck-elite' ), 'add_new_item' => __( 'Add fellowship group', 'dck-elite' ), 'edit_item' => __( 'Edit fellowship group', 'dck-elite' ) ),
				'public'          => false,
				'show_ui'         => true,
				'menu_icon'       => 'dashicons-admin-home',
				'menu_position'   => 27,
				'supports'        => array( 'title', 'excerpt', 'thumbnail', 'page-attributes' ),
				'capability_type' => 'page',
				'map_meta_cap'    => true,
			)
		);
	}
);
if ( ! function_exists( 'dck_fs_fields' ) ) {
	function dck_fs_fields() {
		return array(
			'area'     => __( 'Area (e.g. Uthiru, Kabete, Kangemi)', 'dck-elite' ),
			'day'      => __( 'Meeting day (e.g. Wednesdays)', 'dck-elite' ),
			'time'     => __( 'Time (e.g. 6:30pm - 8:00pm)', 'dck-elite' ),
			'venue'    => __( 'Venue / landmark (shown publicly, no house numbers)', 'dck-elite' ),
			'leader'   => __( 'Leader name (shown publicly)', 'dck-elite' ),
			'lphone'   => __( 'Leader phone (staff only)', 'dck-elite' ),
		);
	}
}
add_action(
	'add_meta_boxes_dck_fellowship',
	function () {
		add_meta_box(
			'dck_fs_box',
			__( 'Group details', 'dck-elite' ),
			function ( $post ) {
				wp_nonce_field( 'dck_fs_save', 'dck_fs_nonce' );
				foreach ( dck_fs_fields() as $k => $l ) {
					echo '<p><label><strong>' . esc_html( $l ) . '</strong><br><input type="text" name="dck_fs[' . esc_attr( $k ) . ']" value="' . esc_attr( get_post_meta( $post->ID, '_dck_fs_' . $k, true ) ) . '" style="width:100%;max-width:520px"></label></p>';
				}
				echo '<p class="description">' . esc_html__( 'Use the Excerpt box for a one-line description (e.g. "Young families, Bible study and prayer").', 'dck-elite' ) . '</p>';
			},
			'dck_fellowship',
			'normal',
			'high'
		);
	}
);
add_action(
	'save_post_dck_fellowship',
	function ( $post_id ) {
		if ( ! isset( $_POST['dck_fs_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dck_fs_nonce'] ) ), 'dck_fs_save' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$in = isset( $_POST['dck_fs'] ) ? (array) wp_unslash( $_POST['dck_fs'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		foreach ( array_keys( dck_fs_fields() ) as $k ) {
			update_post_meta( $post_id, '_dck_fs_' . $k, sanitize_text_field( isset( $in[ $k ] ) ? $in[ $k ] : '' ) );
		}
	}
);
if ( ! function_exists( 'dck_fs_groups' ) ) {
	function dck_fs_groups() {
		$out = array();
		foreach ( get_posts( array( 'post_type' => 'dck_fellowship', 'post_status' => 'publish', 'posts_per_page' => 100, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) ) as $p ) {
			$g = array( 'id' => $p->ID, 'name' => $p->post_title, 'desc' => $p->post_excerpt );
			foreach ( array_keys( dck_fs_fields() ) as $k ) {
				$g[ $k ] = (string) get_post_meta( $p->ID, '_dck_fs_' . $k, true );
			}
			$out[] = $g;
		}
		return $out;
	}
}

/* ---------- Sign-ups ---------- */
if ( ! function_exists( 'dck_serve_ministries' ) ) {
	function dck_serve_ministries() {
		$raw = dck_home_val( 'dck_volunteer_ministries', "Praise and Worship\nUshering and Protocol\nMedia and Sound\nChildren's Ministry (Sunday School)\nYouth Ministry\nIntercessory Prayer\nCare and Welfare\nHospitality\nEvangelism and Outreach\nCleaning and Maintenance" );
		return array_values( array_filter( array_map( 'trim', explode( "\n", (string) $raw ) ) ) );
	}
}
if ( ! function_exists( 'dck_serve_targets' ) ) {
	/* Filter options for the admin list: ministries + fellowship groups. */
	function dck_serve_targets() {
		$o = array();
		foreach ( dck_serve_ministries() as $m ) {
			$o[ $m ] = $m;
		}
		foreach ( dck_fs_groups() as $g ) {
			$o[ 'Fellowship: ' . $g['name'] ] = 'Fellowship: ' . $g['name'];
		}
		return $o;
	}
}

dck_rec_register(
	'dck_volunteer',
	array(
		'label'          => __( 'Volunteers', 'dck-elite' ),
		'single'         => __( 'Volunteer / member sign-up', 'dck-elite' ),
		'icon'           => 'dashicons-groups',
		'position'       => 28,
		'csv'            => 'volunteers',
		'statuses'       => array(
			'new'       => array( __( 'New', 'dck-elite' ), 'amber' ),
			'contacted' => array( __( 'Contacted', 'dck-elite' ), 'blue' ),
			'active'    => array( __( 'Serving / joined', 'dck-elite' ), 'green' ),
			'closed'    => array( __( 'Not now', 'dck-elite' ), 'gray' ),
		),
		'columns'        => array( 'target' => __( 'Wants to', 'dck-elite' ), 'phone' => __( 'Phone', 'dck-elite' ), 'avail' => __( 'Available', 'dck-elite' ) ),
		'fields'         => array( 'target' => 'Ministry / fellowship', 'name' => 'Name', 'phone' => 'Phone', 'email' => 'Email', 'area' => 'Area', 'avail' => 'Available', 'note' => 'Note' ),
		'filter'         => 'target',
		'filter_label'   => __( 'All ministries & groups', 'dck-elite' ),
		'filter_options' => 'dck_serve_targets',
		'wa'             => function ( $id ) {
			return array( get_post_meta( $id, '_dck_phone', true ), sprintf( 'Hello %s, thank you for signing up for %s at Deliverance Church Kabete N! We are excited to have you. Here are the next steps: ', get_post_meta( $id, '_dck_name', true ), get_post_meta( $id, '_dck_target', true ) ), __( 'Welcome', 'dck-elite' ) );
		},
	)
);

add_action(
	'rest_api_init',
	function () {
		register_rest_route( 'dck/v1', '/serve', array( 'methods' => 'POST', 'callback' => 'dck_serve_rest', 'permission_callback' => '__return_true' ) );
	}
);
if ( ! function_exists( 'dck_serve_rest' ) ) {
	function dck_serve_rest( $request ) {
		$p = (array) $request->get_json_params();
		$g = dck_sec_guard( $request, $p, 'serve', 3000, 6, 900 );
		if ( true !== $g ) {
			return $g;
		}
		$name  = dck_sec_cap( sanitize_text_field( isset( $p['name'] ) ? $p['name'] : '' ), 120 );
		$phone = dck_sec_cap( sanitize_text_field( isset( $p['phone'] ) ? $p['phone'] : '' ), 40 );
		$email = dck_sec_cap( sanitize_email( isset( $p['email'] ) ? $p['email'] : '' ), 190 );
		$area  = dck_sec_cap( sanitize_text_field( isset( $p['area'] ) ? $p['area'] : '' ), 120 );
		$note  = dck_sec_cap( sanitize_textarea_field( isset( $p['note'] ) ? $p['note'] : '' ), 1000 );
		$avail = array_values( array_intersect( array_map( 'sanitize_text_field', (array) ( isset( $p['avail'] ) ? $p['avail'] : array() ) ), array( 'Sunday mornings', 'Weekday evenings', 'Saturdays', 'Special events' ) ) );
		$group = isset( $p['group'] ) ? absint( $p['group'] ) : 0;
		$min   = sanitize_text_field( isset( $p['ministry'] ) ? $p['ministry'] : '' );
		$lphone = '';
		if ( $group ) {
			if ( 'dck_fellowship' !== get_post_type( $group ) || 'publish' !== get_post_status( $group ) ) {
				return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Please choose a fellowship group.', 'dck-elite' ) ), 200 );
			}
			$target = 'Fellowship: ' . get_the_title( $group );
			$lphone = (string) get_post_meta( $group, '_dck_fs_lphone', true );
		} elseif ( in_array( $min, dck_serve_ministries(), true ) ) {
			$target = $min;
		} else {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Please choose where you would like to serve.', 'dck-elite' ) ), 200 );
		}
		if ( '' === $name || ! dck_ct_wa_number( $phone ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Please enter your name and a valid phone number.', 'dck-elite' ) ), 200 );
		}
		if ( $email && ! is_email( $email ) ) {
			$email = '';
		}
		$id = dck_rec_insert( 'dck_volunteer', $name . ' - ' . $target, array( 'target' => $target, 'name' => $name, 'phone' => $phone, 'email' => $email, 'area' => $area, 'avail' => $avail, 'note' => $note ), 'new' );
		if ( ! $id ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'We could not save your details. Please try again.', 'dck-elite' ) ), 200 );
		}
		$body = "New sign-up: $target\n\nName: $name\nPhone: $phone\nEmail: $email\nArea: $area\nAvailable: " . implode( ', ', $avail ) . "\n" . ( $note ? "Note: $note\n" : '' );
		if ( $lphone ) {
			$wa    = dck_ct_wa_link( $lphone, "Hello, $name ($phone) would like to join your fellowship group. Please reach out to welcome them. - DCK" );
			$body .= $wa ? "\nForward to the group leader on WhatsApp: $wa\n" : '';
		}
		$body .= "\nOpen: " . admin_url( 'edit.php?post_type=dck_volunteer' );
		wp_mail( dck_ct_email_to( 'dck_volunteer_email' ), "New sign-up: $target - $name", $body );
		return new WP_REST_Response( array( 'ok' => true, 'message' => $group ? __( 'Wonderful! We will connect you with the group leader this week.', 'dck-elite' ) : __( 'Thank you for offering to serve! The ministry leader will contact you this week.', 'dck-elite' ) ), 200 );
	}
}
