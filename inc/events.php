<?php
/**
 * DCK Elite - Events module.
 * Self-contained Events post type: one-time events (with a date) and
 * recurring weekly / monthly gatherings (with a day-or-frequency label),
 * plus a notice flag. Managed entirely from wp-admin.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_events_register' ) ) {
	function dck_events_register() {
		if ( post_type_exists( 'dck_event' ) ) {
			return;
		}
		register_post_type( 'dck_event', array(
			'labels' => array(
				'name'          => 'Events',
				'singular_name' => 'Event',
				'add_new'       => 'Add New Event',
				'add_new_item'  => 'Add New Event',
				'edit_item'     => 'Edit Event',
				'new_item'      => 'New Event',
				'view_item'     => 'View Event',
				'search_items'  => 'Search Events',
				'not_found'     => 'No events found',
				'menu_name'     => 'Events',
			),
			'public'        => true,
			'has_archive'   => false,
			'menu_icon'     => 'dashicons-calendar-alt',
			'menu_position' => 22,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'rewrite'       => array( 'slug' => 'event' ),
			'show_in_rest'  => true,
		) );
	}
}
add_action( 'init', 'dck_events_register' );

if ( ! function_exists( 'dck_events_add_meta' ) ) {
	function dck_events_add_meta() {
		add_meta_box( 'dck_event_details', 'Event Details', 'dck_events_meta_render', 'dck_event', 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes', 'dck_events_add_meta' );

if ( ! function_exists( 'dck_events_meta_render' ) ) {
	function dck_events_meta_render( $post ) {
		wp_nonce_field( 'dck_event_save', 'dck_event_nonce' );
		$rec    = get_post_meta( $post->ID, '_dck_event_recurring', true );
		$dayl   = get_post_meta( $post->ID, '_dck_event_daylabel', true );
		$date   = get_post_meta( $post->ID, '_dck_event_date', true );
		$start  = get_post_meta( $post->ID, '_dck_event_start', true );
		$end    = get_post_meta( $post->ID, '_dck_event_end', true );
		$loc    = get_post_meta( $post->ID, '_dck_event_location', true );
		$notice = get_post_meta( $post->ID, '_dck_event_notice', true );
		$istyle = 'width:100%;max-width:340px;padding:7px 9px';
		echo '<div style="max-width:380px">';
		echo '<p style="padding:10px 12px;background:#eef3ff;border-radius:8px"><label style="font-weight:600"><input type="checkbox" name="dck_event_recurring" value="1" ' . checked( $rec, '1', false ) . '> This is a recurring / weekly gathering</label><br><span style="color:#555;font-size:12px">Tick for regular services (Sunday, midweek, Keshas). These show under "Our Weekly Rhythm". Leave unticked for one-time events with a specific date.</span></p>';
		echo '<p><label for="dck_ev_dayl"><strong>Day or frequency label</strong> (recurring only)</label><br><input type="text" id="dck_ev_dayl" name="dck_event_daylabel" value="' . esc_attr( $dayl ) . '" placeholder="e.g. Sunday, or Fridays 1st &amp; 3rd week" style="' . esc_attr( $istyle ) . '"></p>';
		echo '<hr>';
		echo '<p><label for="dck_ev_date"><strong>Event date</strong> (one-time events only)</label><br><input type="date" id="dck_ev_date" name="dck_event_date" value="' . esc_attr( $date ) . '" style="' . esc_attr( $istyle ) . '"></p>';
		echo '<p><label for="dck_ev_start"><strong>Start time</strong></label><br><input type="time" id="dck_ev_start" name="dck_event_start" value="' . esc_attr( $start ) . '" style="' . esc_attr( $istyle ) . '"></p>';
		echo '<p><label for="dck_ev_end"><strong>End time</strong></label><br><input type="time" id="dck_ev_end" name="dck_event_end" value="' . esc_attr( $end ) . '" style="' . esc_attr( $istyle ) . '"></p>';
		echo '<p><label for="dck_ev_loc"><strong>Location</strong></label><br><input type="text" id="dck_ev_loc" name="dck_event_location" value="' . esc_attr( $loc ) . '" placeholder="e.g. Main Sanctuary, Uthiru" style="' . esc_attr( $istyle ) . '"></p>';
		echo '<p style="margin-top:14px"><label style="font-weight:600"><input type="checkbox" name="dck_event_notice" value="1" ' . checked( $notice, '1', false ) . '> Pin as a notice / announcement</label></p>';
		echo '</div>';
	}
}

if ( ! function_exists( 'dck_events_save' ) ) {
	function dck_events_save( $post_id ) {
		if ( ! isset( $_POST['dck_event_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dck_event_nonce'] ) ), 'dck_event_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$fields = array(
			'_dck_event_daylabel' => 'dck_event_daylabel',
			'_dck_event_date'     => 'dck_event_date',
			'_dck_event_start'    => 'dck_event_start',
			'_dck_event_end'      => 'dck_event_end',
			'_dck_event_location' => 'dck_event_location',
		);
		foreach ( $fields as $meta => $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $meta, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
			}
		}
		update_post_meta( $post_id, '_dck_event_recurring', isset( $_POST['dck_event_recurring'] ) ? '1' : '' );
		update_post_meta( $post_id, '_dck_event_notice', isset( $_POST['dck_event_notice'] ) ? '1' : '' );
	}
}
add_action( 'save_post_dck_event', 'dck_events_save' );

if ( ! function_exists( 'dck_event_format_date' ) ) {
	function dck_event_format_date( $ymd ) {
		if ( ! $ymd ) {
			return array( 'mon' => '', 'day' => '', 'full' => '' );
		}
		$ts = strtotime( $ymd );
		if ( ! $ts ) {
			return array( 'mon' => '', 'day' => '', 'full' => $ymd );
		}
		return array(
			'mon'  => strtoupper( gmdate( 'M', $ts ) ),
			'day'  => gmdate( 'j', $ts ),
			'full' => gmdate( 'l, F j, Y', $ts ),
		);
	}
}

if ( ! function_exists( 'dck_event_time_label' ) ) {
	function dck_event_time_label( $t ) {
		if ( ! $t ) {
			return '';
		}
		$parts = explode( ':', $t );
		if ( count( $parts ) < 2 ) {
			return $t;
		}
		$h    = (int) $parts[0];
		$m    = $parts[1];
		$ampm = ( $h >= 12 ) ? 'PM' : 'AM';
		$h12  = $h % 12;
		if ( 0 === $h12 ) {
			$h12 = 12;
		}
		return $h12 . ':' . $m . ' ' . $ampm;
	}
}

/**
 * Seed the regular weekly / monthly gatherings once, so the Events page is
 * populated on first install. Church can edit or add more from wp-admin.
 */
if ( ! function_exists( 'dck_events_seed' ) ) {
	function dck_events_seed() {
		if ( get_option( 'dck_events_seeded' ) ) {
			return;
		}
		if ( ! post_type_exists( 'dck_event' ) ) {
			return;
		}
		$seeds = array(
			array( 'Intensive Prayers', 'Sunday', '08:00', '09:00', '', 'Start each Sunday seeking His face together.', 10 ),
			array( 'Intensive Bible Study', 'Sunday', '09:30', '10:30', '', 'Grow deeper in the Word before the Glorious Service.', 20 ),
			array( 'Glorious Service', 'Sunday', '10:40', '13:00', '', 'Joyful worship and Bible-based teaching for the whole family.', 30 ),
			array( 'Keshas (Overnight)', 'Fridays, 1st & 3rd week', '', '', '', 'An overnight of prayer and worship, twice every month.', 40 ),
			array( 'Prayers in the Church', 'Tuesday', '18:00', '19:00', '', 'Midweek prayers to strengthen your week.', 50 ),
			array( 'Midweek Service in Church', 'Thursday', '18:00', '19:00', '', 'A midweek encounter in the Word and worship.', 60 ),
			array( 'Home-Based Church Services', 'Friday & Saturday', '18:00', '19:00', 'Neighbourhood homes', 'Fellowship in homes across the community.', 70 ),
			array( 'Worship Team Practice in Church', 'Saturday', '16:00', '', '', 'The worship team prepares to lead us before God.', 80 ),
		);
		foreach ( $seeds as $s ) {
			$pid = wp_insert_post( array(
				'post_type'    => 'dck_event',
				'post_title'   => $s[0],
				'post_excerpt' => $s[5],
				'post_status'  => 'publish',
				'menu_order'   => $s[6],
			) );
			if ( $pid && ! is_wp_error( $pid ) ) {
				update_post_meta( $pid, '_dck_event_recurring', '1' );
				update_post_meta( $pid, '_dck_event_daylabel', $s[1] );
				update_post_meta( $pid, '_dck_event_start', $s[2] );
				update_post_meta( $pid, '_dck_event_end', $s[3] );
				update_post_meta( $pid, '_dck_event_location', $s[4] );
			}
		}
		update_option( 'dck_events_seeded', 1 );
	}
}
add_action( 'admin_init', 'dck_events_seed' );
