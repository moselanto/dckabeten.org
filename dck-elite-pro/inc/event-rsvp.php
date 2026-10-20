<?php
/**
 * Event registration (RSVP) with check-in and reminders.
 * - Tick "Open registration" on an event; a sign-up form appears on its page.
 * - Registrations: DCK > Registrations (filter by event, check-in, CSV).
 * - The day before a dated event, people who gave an email get a reminder automatically.
 * - WhatsApp reminders: one tap per person (WhatsApp Business app), or copy all numbers
 *   into a WhatsApp Business broadcast list from the event screen.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	function () {
		register_post_type(
			'dck_rsvp',
			array(
				'labels'          => array(
					'name'          => __( 'Registrations', 'dck-elite' ),
					'singular_name' => __( 'Registration', 'dck-elite' ),
					'menu_name'     => __( 'Registrations', 'dck-elite' ),
					'search_items'  => __( 'Search registrations', 'dck-elite' ),
					'not_found'     => __( 'No registrations yet', 'dck-elite' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => 'edit.php?post_type=dck_event',
				'supports'        => array( 'title' ),
				'capability_type' => 'page',
				'map_meta_cap'    => true,
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			)
		);
	}
);

if ( ! function_exists( 'dck_rsvp_open' ) ) {
	function dck_rsvp_open( $event_id ) {
		return '1' === get_post_meta( $event_id, '_dck_event_rsvp', true );
	}
}

if ( ! function_exists( 'dck_rsvp_count' ) ) {
	/* Total people (registrants + their guests) for an event. */
	function dck_rsvp_count( $event_id ) {
		$total = 0;
		foreach ( get_posts( array( 'post_type' => 'dck_rsvp', 'post_status' => 'private', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_dck_r_event', 'meta_value' => (int) $event_id ) ) as $id ) { // phpcs:ignore WordPress.DB.SlowDBQuery
			$total += 1 + (int) get_post_meta( $id, '_dck_r_guests', true );
		}
		return $total;
	}
}

if ( ! function_exists( 'dck_rsvp_event_info' ) ) {
	function dck_rsvp_event_info( $event_id ) {
		$date  = (string) get_post_meta( $event_id, '_dck_event_date', true );
		$start = (string) get_post_meta( $event_id, '_dck_event_start', true );
		return array(
			'event' => get_the_title( $event_id ),
			'ymd'   => $date,
			'date'  => $date ? wp_date( 'l, j F Y', strtotime( $date . ' 12:00:00' ) ) : (string) get_post_meta( $event_id, '_dck_event_daylabel', true ),
			'time'  => $start && function_exists( 'dck_event_time_label' ) ? dck_event_time_label( $start ) : $start,
			'place' => (string) get_post_meta( $event_id, '_dck_event_location', true ),
			'url'   => get_permalink( $event_id ),
		);
	}
}

if ( ! function_exists( 'dck_rsvp_reminder_text' ) ) {
	function dck_rsvp_reminder_text( $name, $event_id ) {
		$i = dck_rsvp_event_info( $event_id );
		return dck_ct_fill(
			dck_home_val( 'dck_rsvp_wa_text', "Hello {name}, a reminder that you registered for {event} on {date} at {time}, {place}. We look forward to seeing you! - Deliverance Church Kabete N" ),
			array( 'name' => $name, 'event' => $i['event'], 'date' => $i['date'], 'time' => $i['time'], 'place' => $i['place'] )
		);
	}
}

/* ---------- Event screen: registration settings ---------- */
add_action(
	'add_meta_boxes_dck_event',
	function () {
		add_meta_box(
			'dck_event_rsvp',
			__( 'Registration', 'dck-elite' ),
			function ( $post ) {
				wp_nonce_field( 'dck_event_rsvp_save', 'dck_event_rsvp_nonce' );
				$cap   = (int) get_post_meta( $post->ID, '_dck_event_capacity', true );
				$count = dck_rsvp_count( $post->ID );
				echo '<p><label style="font-weight:600"><input type="checkbox" name="dck_event_rsvp" value="1"' . checked( dck_rsvp_open( $post->ID ), true, false ) . '> ' . esc_html__( 'Open registration for this event', 'dck-elite' ) . '</label></p>';
				echo '<p><label>' . esc_html__( 'Maximum people (0 = no limit)', 'dck-elite' ) . '<br><input type="number" min="0" name="dck_event_capacity" value="' . esc_attr( $cap ) . '" style="width:100%"></label></p>';
				echo '<p><strong>' . esc_html( sprintf( /* translators: %d people */ _n( '%d person registered', '%d people registered', $count, 'dck-elite' ), $count ) ) . '</strong><br><a href="' . esc_url( admin_url( 'edit.php?post_type=dck_rsvp&dck_event=' . $post->ID ) ) . '">' . esc_html__( 'View list & check in', 'dck-elite' ) . '</a></p>';
				$nums = array();
				foreach ( get_posts( array( 'post_type' => 'dck_rsvp', 'post_status' => 'private', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_dck_r_event', 'meta_value' => $post->ID ) ) as $rid ) { // phpcs:ignore WordPress.DB.SlowDBQuery
					$n = dck_ct_wa_number( get_post_meta( $rid, '_dck_r_phone', true ) );
					if ( $n ) {
						$nums[] = '+' . $n;
					}
				}
				if ( $nums ) {
					echo '<p style="margin-bottom:4px">' . esc_html__( 'All phone numbers (paste into a WhatsApp Business broadcast list):', 'dck-elite' ) . '</p><textarea readonly rows="4" style="width:100%" onclick="this.select()">' . esc_textarea( implode( "\n", array_unique( $nums ) ) ) . '</textarea>';
				}
			},
			'dck_event',
			'side',
			'high'
		);
	}
);
add_action(
	'save_post_dck_event',
	function ( $post_id ) {
		if ( ! isset( $_POST['dck_event_rsvp_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dck_event_rsvp_nonce'] ) ), 'dck_event_rsvp_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		update_post_meta( $post_id, '_dck_event_rsvp', isset( $_POST['dck_event_rsvp'] ) ? '1' : '' );
		update_post_meta( $post_id, '_dck_event_capacity', isset( $_POST['dck_event_capacity'] ) ? absint( $_POST['dck_event_capacity'] ) : 0 );
	}
);

/* ---------- Public endpoint ---------- */
add_action(
	'rest_api_init',
	function () {
		register_rest_route( 'dck/v1', '/rsvp', array( 'methods' => 'POST', 'callback' => 'dck_rsvp_rest', 'permission_callback' => '__return_true' ) );
	}
);

if ( ! function_exists( 'dck_rsvp_rest' ) ) {
	function dck_rsvp_rest( $request ) {
		$p = (array) $request->get_json_params();
		if ( function_exists( 'dck_sec_guard' ) ) {
			$g = dck_sec_guard( $request, $p, 'rsvp', 2500, 6, 900 );
			if ( true !== $g ) {
				return $g;
			}
		}
		$eid    = isset( $p['event'] ) ? absint( $p['event'] ) : 0;
		$name   = dck_sec_cap( sanitize_text_field( isset( $p['name'] ) ? $p['name'] : '' ), 120 );
		$phone  = dck_sec_cap( sanitize_text_field( isset( $p['phone'] ) ? $p['phone'] : '' ), 40 );
		$email  = dck_sec_cap( sanitize_email( isset( $p['email'] ) ? $p['email'] : '' ), 190 );
		$guests = min( 10, isset( $p['guests'] ) ? absint( $p['guests'] ) : 0 );

		if ( ! $eid || 'dck_event' !== get_post_type( $eid ) || 'publish' !== get_post_status( $eid ) || ! dck_rsvp_open( $eid ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Registration for this event is closed.', 'dck-elite' ) ), 200 );
		}
		$info = dck_rsvp_event_info( $eid );
		if ( $info['ymd'] && $info['ymd'] < current_time( 'Y-m-d' ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'This event has already taken place.', 'dck-elite' ) ), 200 );
		}
		if ( '' === $name || ! dck_ct_wa_number( $phone ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Please enter your name and a valid phone number.', 'dck-elite' ) ), 200 );
		}
		if ( $email && ! is_email( $email ) ) {
			$email = '';
		}
		$norm = dck_ct_wa_number( $phone );
		$dupe = get_posts( array( 'post_type' => 'dck_rsvp', 'post_status' => 'private', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_dck_r_event', 'value' => $eid ), array( 'key' => '_dck_r_norm', 'value' => $norm ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( $dupe ) {
			return new WP_REST_Response( array( 'ok' => true, 'message' => __( 'You are already registered for this event. See you there!', 'dck-elite' ) ), 200 );
		}
		$cap = (int) get_post_meta( $eid, '_dck_event_capacity', true );
		if ( $cap > 0 && dck_rsvp_count( $eid ) + 1 + $guests > $cap ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Sorry, this event is fully booked.', 'dck-elite' ) ), 200 );
		}
		$id = wp_insert_post( array( 'post_type' => 'dck_rsvp', 'post_status' => 'private', 'post_title' => $name . ' - ' . $info['event'] ) );
		if ( ! $id || is_wp_error( $id ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'We could not save your registration. Please try again.', 'dck-elite' ) ), 200 );
		}
		foreach ( array( 'event' => $eid, 'name' => $name, 'phone' => $phone, 'norm' => $norm, 'email' => $email, 'guests' => $guests ) as $k => $v ) {
			update_post_meta( $id, '_dck_r_' . $k, $v );
		}
		$when = trim( $info['date'] . ( $info['time'] ? ', ' . $info['time'] : '' ) . ( $info['place'] ? ' - ' . $info['place'] : '' ) );
		if ( $email ) {
			wp_mail( $email, sprintf( __( "You're registered: %s", 'dck-elite' ), $info['event'] ), sprintf( "Dear %s,\n\nYou are registered for %s.\n%s\n%s\n\nWe will send you a reminder the day before. See you there!\n\n%s", $name, $info['event'], $when, $guests ? sprintf( 'Guests: %d', $guests ) : '', get_bloginfo( 'name' ) ) );
		}
		return new WP_REST_Response( array( 'ok' => true, 'message' => sprintf( __( "You're registered for %s. See you there!", 'dck-elite' ), $info['event'] ) ), 200 );
	}
}

/* ---------- Form on the event page ---------- */
add_filter(
	'the_content',
	function ( $content ) {
		if ( ! is_singular( 'dck_event' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$eid  = get_the_ID();
		if ( ! dck_rsvp_open( $eid ) ) {
			return $content;
		}
		$info = dck_rsvp_event_info( $eid );
		$cap  = (int) get_post_meta( $eid, '_dck_event_capacity', true );
		$left = $cap > 0 ? max( 0, $cap - dck_rsvp_count( $eid ) ) : -1;
		wp_enqueue_script( 'dck-forms' );
		ob_start();
		echo '<section class="dck-rsvp" id="register"><div class="dck-rsvp__head"><p class="dck-eyebrow">' . esc_html__( 'Register', 'dck-elite' ) . '</p><h2>' . esc_html__( 'Reserve your place', 'dck-elite' ) . '</h2>';
		echo '<p class="dck-rsvp__when">' . esc_html( trim( $info['date'] . ( $info['time'] ? ' · ' . $info['time'] : '' ) . ( $info['place'] ? ' · ' . $info['place'] : '' ) ) ) . '</p>';
		if ( $left >= 0 ) {
			echo '<p class="dck-rsvp__left">' . esc_html( $left > 0 ? sprintf( _n( '%d place left', '%d places left', $left, 'dck-elite' ), $left ) : __( 'Fully booked', 'dck-elite' ) ) . '</p>';
		}
		echo '</div>';
		if ( 0 !== $left && ! ( $info['ymd'] && $info['ymd'] < current_time( 'Y-m-d' ) ) ) {
			?>
			<form class="dck-ctform dck-formcard" data-endpoint="<?php echo esc_url( rest_url( 'dck/v1/rsvp' ) ); ?>" novalidate>
				<input type="hidden" name="event" value="<?php echo esc_attr( $eid ); ?>">
				<div class="dck-cform__row">
					<label class="dck-cform__field"><span><?php esc_html_e( 'Your name', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="text" name="name" autocomplete="name" required></label>
					<label class="dck-cform__field"><span><?php esc_html_e( 'Phone / WhatsApp', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="tel" name="phone" autocomplete="tel" required placeholder="07XX XXX XXX"></label>
				</div>
				<div class="dck-cform__row">
					<label class="dck-cform__field"><span><?php esc_html_e( 'Email', 'dck-elite' ); ?> <small class="dck-opt"><?php esc_html_e( 'For your reminder', 'dck-elite' ); ?></small></span><input type="email" name="email" autocomplete="email"></label>
					<label class="dck-cform__field"><span><?php esc_html_e( 'Bringing anyone?', 'dck-elite' ); ?></span><select name="guests"><?php for ( $g = 0; $g <= 10; $g++ ) { echo '<option value="' . esc_attr( $g ) . '">' . esc_html( 0 === $g ? __( 'Just me', 'dck-elite' ) : sprintf( '+%d', $g ) ) . '</option>'; } ?></select></label>
				</div>
				<div class="dck-hp" aria-hidden="true"><label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
				<button type="submit" class="dck-btn dck-btn--primary dck-btn--block"><?php esc_html_e( 'Register now', 'dck-elite' ); ?></button>
				<p class="dck-ctform__status" role="status" aria-live="polite"></p>
			</form>
			<?php
		}
		echo '</section>';
		return $content . ob_get_clean();
	},
	20
);

/* ---------- Admin list ---------- */
add_filter(
	'manage_dck_rsvp_posts_columns',
	function () {
		return array(
			'cb'         => '<input type="checkbox">',
			'title'      => __( 'Name', 'dck-elite' ),
			'dck_event'  => __( 'Event', 'dck-elite' ),
			'dck_phone'  => __( 'Phone', 'dck-elite' ),
			'dck_guests' => __( 'Guests', 'dck-elite' ),
			'dck_in'     => __( 'Check-in', 'dck-elite' ),
			'dck_wa'     => __( 'Reminder', 'dck-elite' ),
			'date'       => __( 'Registered', 'dck-elite' ),
		);
	}
);
add_action(
	'manage_dck_rsvp_posts_custom_column',
	function ( $col, $id ) {
		$eid = (int) get_post_meta( $id, '_dck_r_event', true );
		if ( 'dck_event' === $col ) {
			echo esc_html( get_the_title( $eid ) );
		} elseif ( 'dck_phone' === $col ) {
			echo esc_html( get_post_meta( $id, '_dck_r_phone', true ) );
		} elseif ( 'dck_guests' === $col ) {
			echo esc_html( (int) get_post_meta( $id, '_dck_r_guests', true ) );
		} elseif ( 'dck_in' === $col ) {
			$in = (bool) get_post_meta( $id, '_dck_r_in', true );
			echo $in ? dck_ct_pill( __( 'Arrived', 'dck-elite' ), 'green' ) . ' ' : ''; // phpcs:ignore WordPress.Security.EscapeOutput
			echo '<a class="button button-small" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dck_rsvp_in&id=' . $id ), 'dck_rsvp_in_' . $id ) ) . '">' . esc_html( $in ? __( 'Undo', 'dck-elite' ) : __( 'Check in', 'dck-elite' ) ) . '</a>';
		} elseif ( 'dck_wa' === $col ) {
			$wa = dck_ct_wa_link( get_post_meta( $id, '_dck_r_phone', true ), dck_rsvp_reminder_text( get_post_meta( $id, '_dck_r_name', true ), $eid ) );
			if ( $wa ) {
				echo '<a class="button button-small" target="_blank" rel="noopener" href="' . esc_url( $wa ) . '">' . esc_html__( 'WhatsApp', 'dck-elite' ) . '</a> ';
			}
			echo get_post_meta( $id, '_dck_r_reminded', true ) ? '<small>' . esc_html__( 'Email sent', 'dck-elite' ) . '</small>' : '';
		}
	},
	10,
	2
);
add_action(
	'restrict_manage_posts',
	function ( $type ) {
		if ( 'dck_rsvp' !== $type ) {
			return;
		}
		$cur = isset( $_GET['dck_event'] ) ? absint( $_GET['dck_event'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		echo '<select name="dck_event"><option value="0">' . esc_html__( 'All events', 'dck-elite' ) . '</option>';
		foreach ( get_posts( array( 'post_type' => 'dck_event', 'posts_per_page' => 100, 'meta_key' => '_dck_event_rsvp', 'meta_value' => '1' ) ) as $e ) { // phpcs:ignore WordPress.DB.SlowDBQuery
			echo '<option value="' . esc_attr( $e->ID ) . '"' . selected( $cur, $e->ID, false ) . '>' . esc_html( $e->post_title ) . '</option>';
		}
		echo '</select>';
	}
);
add_action(
	'manage_posts_extra_tablenav',
	function ( $which ) {
		$screen = get_current_screen();
		if ( 'top' !== $which || ! $screen || 'edit-dck_rsvp' !== $screen->id ) {
			return;
		}
		$ev = isset( $_GET['dck_event'] ) ? absint( $_GET['dck_event'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		echo '<div class="alignleft actions"><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dck_rsvp_csv&event=' . $ev ), 'dck_rsvp_csv' ) ) . '">' . esc_html__( 'Download CSV', 'dck-elite' ) . '</a></div>';
	}
);
add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( ! is_admin() || ! $q->is_main_query() || 'dck_rsvp' !== $q->get( 'post_type' ) ) {
			return;
		}
		$ev = isset( $_GET['dck_event'] ) ? absint( $_GET['dck_event'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		if ( $ev ) {
			$q->set( 'meta_key', '_dck_r_event' );
			$q->set( 'meta_value', $ev );
		}
	}
);
add_action(
	'admin_post_dck_rsvp_in',
	function () {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		check_admin_referer( 'dck_rsvp_in_' . $id );
		if ( ! $id || 'dck_rsvp' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) ) {
			wp_die( esc_html__( 'Not allowed.', 'dck-elite' ) );
		}
		update_post_meta( $id, '_dck_r_in', get_post_meta( $id, '_dck_r_in', true ) ? '' : time() );
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=dck_rsvp' ) );
		exit;
	}
);
add_action(
	'admin_post_dck_rsvp_csv',
	function () {
		check_admin_referer( 'dck_rsvp_csv' );
		if ( ! current_user_can( 'edit_others_pages' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'dck-elite' ) );
		}
		$ev   = isset( $_GET['event'] ) ? absint( $_GET['event'] ) : 0;
		$args = array( 'post_type' => 'dck_rsvp', 'post_status' => 'private', 'posts_per_page' => -1, 'fields' => 'ids' );
		if ( $ev ) {
			$args['meta_key']   = '_dck_r_event'; // phpcs:ignore WordPress.DB.SlowDBQuery
			$args['meta_value'] = $ev; // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		$rows = array();
		foreach ( get_posts( $args ) as $id ) {
			$rows[] = array( get_the_title( (int) get_post_meta( $id, '_dck_r_event', true ) ), get_post_meta( $id, '_dck_r_name', true ), get_post_meta( $id, '_dck_r_phone', true ), get_post_meta( $id, '_dck_r_email', true ), (int) get_post_meta( $id, '_dck_r_guests', true ), get_post_meta( $id, '_dck_r_in', true ) ? 'Yes' : 'No', get_the_date( 'Y-m-d H:i', $id ) );
		}
		dck_ct_csv( 'registrations-' . gmdate( 'Y-m-d' ) . '.csv', array( 'Event', 'Name', 'Phone', 'Email', 'Guests', 'Checked in', 'Registered' ), $rows );
	}
);

/* "Register" link on the Events page rows. */
if ( ! function_exists( 'dck_rsvp_button' ) ) {
	function dck_rsvp_button( $event_id ) {
		if ( dck_rsvp_open( $event_id ) ) {
			echo '<a class="dck-btn dck-btn--gold dck-btn--sm" href="' . esc_url( get_permalink( $event_id ) . '#register' ) . '">' . esc_html__( 'Register', 'dck-elite' ) . '</a>';
		}
	}
}

/* ---------- Automatic email reminders (day before) ---------- */
add_action(
	'init',
	function () {
		if ( ! wp_next_scheduled( 'dck_rsvp_reminders' ) ) {
			wp_schedule_event( time() + 300, 'hourly', 'dck_rsvp_reminders' );
		}
	}
);
add_action(
	'switch_theme',
	function () {
		wp_clear_scheduled_hook( 'dck_rsvp_reminders' );
	}
);
add_action(
	'dck_rsvp_reminders',
	function () {
		$tomorrow = wp_date( 'Y-m-d', time() + DAY_IN_SECONDS );
		$events   = get_posts( array( 'post_type' => 'dck_event', 'posts_per_page' => 50, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_dck_event_rsvp', 'value' => '1' ), array( 'key' => '_dck_event_date', 'value' => $tomorrow ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		foreach ( $events as $eid ) {
			$regs = get_posts( array( 'post_type' => 'dck_rsvp', 'post_status' => 'private', 'posts_per_page' => 300, 'fields' => 'ids', 'meta_key' => '_dck_r_event', 'meta_value' => $eid ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
			foreach ( $regs as $rid ) {
				$email = get_post_meta( $rid, '_dck_r_email', true );
				if ( ! is_email( $email ) || get_post_meta( $rid, '_dck_r_reminded', true ) ) {
					continue;
				}
				$i = dck_rsvp_event_info( $eid );
				wp_mail( $email, sprintf( __( 'Reminder: %s is tomorrow', 'dck-elite' ), $i['event'] ), dck_rsvp_reminder_text( get_post_meta( $rid, '_dck_r_name', true ), $eid ) . "\n\n" . $i['url'] );
				update_post_meta( $rid, '_dck_r_reminded', time() );
			}
		}
	}
);
