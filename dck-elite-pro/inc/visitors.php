<?php
/**
 * First-time visitor follow-up ("I'm New").
 * Visitors fill a short card (page /new-here/ or the QR code on the seats); the
 * welcome team is emailed, then works through each person: New -> Contacted ->
 * Visited -> Joined, with one-tap WhatsApp messages and a CSV export.
 *
 * Data: private post type dck_visitor (editors and admins).
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
			'dck_visitor',
			array(
				'labels'          => array(
					'name'          => __( 'New Visitors', 'dck-elite' ),
					'singular_name' => __( 'Visitor', 'dck-elite' ),
					'menu_name'     => __( 'New Visitors', 'dck-elite' ),
					'add_new'       => __( 'Add walk-in visitor', 'dck-elite' ),
					'add_new_item'  => __( 'Add walk-in visitor', 'dck-elite' ),
					'edit_item'     => __( 'Visitor follow-up', 'dck-elite' ),
					'search_items'  => __( 'Search visitors', 'dck-elite' ),
					'not_found'     => __( 'No visitors yet', 'dck-elite' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'menu_icon'       => 'dashicons-groups',
				'menu_position'   => 25,
				'supports'        => array( 'title' ),
				'capability_type' => 'page',
				'map_meta_cap'    => true,
			)
		);
	}
);

if ( ! function_exists( 'dck_visitor_statuses' ) ) {
	function dck_visitor_statuses() {
		return array(
			'new'       => array( __( 'New', 'dck-elite' ), 'amber' ),
			'contacted' => array( __( 'Contacted', 'dck-elite' ), 'blue' ),
			'visited'   => array( __( 'Visited again', 'dck-elite' ), 'blue' ),
			'joined'    => array( __( 'Joined', 'dck-elite' ), 'green' ),
			'closed'    => array( __( 'No response', 'dck-elite' ), 'gray' ),
		);
	}
}

if ( ! function_exists( 'dck_visitor_interests' ) ) {
	function dck_visitor_interests() {
		return array(
			'call'       => __( 'A call from a pastor', 'dck-elite' ),
			'fellowship' => __( 'Joining a home fellowship', 'dck-elite' ),
			'membership' => __( 'Membership class', 'dck-elite' ),
			'baptism'    => __( 'Baptism', 'dck-elite' ),
			'serve'      => __( 'Serving in a ministry', 'dck-elite' ),
			'salvation'  => __( 'I gave my life to Christ', 'dck-elite' ),
		);
	}
}

if ( ! function_exists( 'dck_visitor_meta' ) ) {
	function dck_visitor_meta( $id ) {
		$m = array();
		foreach ( array( 'name', 'phone', 'email', 'area', 'heard', 'visit_date', 'interests', 'prayer', 'status', 'notes' ) as $k ) {
			$m[ $k ] = get_post_meta( $id, '_dck_v_' . $k, true );
		}
		$m['interests'] = is_array( $m['interests'] ) ? $m['interests'] : array();
		$m['status']    = $m['status'] ? $m['status'] : 'new';
		return $m;
	}
}

if ( ! function_exists( 'dck_visitor_wa_text' ) ) {
	function dck_visitor_wa_text( $name ) {
		return dck_ct_fill(
			dck_home_val( 'dck_welcome_wa_text', "Hello {name}, thank you for worshipping with us at Deliverance Church Kabete N Market. We are so glad you came! Is there anything we can pray with you about this week? - Welcome Team" ),
			array( 'name' => $name )
		);
	}
}

/* ---------- Public endpoint ---------- */
add_action(
	'rest_api_init',
	function () {
		register_rest_route( 'dck/v1', '/visitor', array( 'methods' => 'POST', 'callback' => 'dck_visitor_rest', 'permission_callback' => '__return_true' ) );
	}
);

if ( ! function_exists( 'dck_visitor_rest' ) ) {
	function dck_visitor_rest( $request ) {
		$p = (array) $request->get_json_params();
		if ( function_exists( 'dck_sec_guard' ) ) {
			$g = dck_sec_guard( $request, $p, 'visitor', 3000, 5, 900 );
			if ( true !== $g ) {
				return $g;
			}
		}
		$name   = dck_sec_cap( sanitize_text_field( isset( $p['name'] ) ? $p['name'] : '' ), 120 );
		$phone  = dck_sec_cap( sanitize_text_field( isset( $p['phone'] ) ? $p['phone'] : '' ), 40 );
		$email  = dck_sec_cap( sanitize_email( isset( $p['email'] ) ? $p['email'] : '' ), 190 );
		$area   = dck_sec_cap( sanitize_text_field( isset( $p['area'] ) ? $p['area'] : '' ), 120 );
		$heard  = dck_sec_cap( sanitize_text_field( isset( $p['heard'] ) ? $p['heard'] : '' ), 80 );
		$prayer = dck_sec_cap( sanitize_textarea_field( isset( $p['prayer'] ) ? $p['prayer'] : '' ), 2000 );
		$date   = preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) ( isset( $p['visit_date'] ) ? $p['visit_date'] : '' ) ) ? $p['visit_date'] : current_time( 'Y-m-d' );
		$ints   = array_values( array_intersect( array_map( 'sanitize_key', (array) ( isset( $p['interests'] ) ? $p['interests'] : array() ) ), array_keys( dck_visitor_interests() ) ) );

		if ( '' === $name || ! dck_ct_wa_number( $phone ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Please enter your name and a valid phone number.', 'dck-elite' ) ), 200 );
		}
		if ( $email && ! is_email( $email ) ) {
			$email = '';
		}
		if ( function_exists( 'dck_sec_looks_spammy' ) && dck_sec_looks_spammy( $name . ' ' . $prayer ) ) {
			return new WP_REST_Response( array( 'ok' => true, 'message' => __( 'Thank you! Our welcome team will be in touch.', 'dck-elite' ) ), 200 );
		}

		$id = wp_insert_post( array( 'post_type' => 'dck_visitor', 'post_status' => 'private', 'post_title' => $name ) );
		if ( ! $id || is_wp_error( $id ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'We could not save your details. Please try again.', 'dck-elite' ) ), 200 );
		}
		foreach ( array( 'name' => $name, 'phone' => $phone, 'email' => $email, 'area' => $area, 'heard' => $heard, 'visit_date' => $date, 'interests' => $ints, 'prayer' => $prayer, 'status' => 'new' ) as $k => $v ) {
			update_post_meta( $id, '_dck_v_' . $k, $v );
		}

		$labels = array();
		foreach ( $ints as $i ) {
			$labels[] = dck_visitor_interests()[ $i ];
		}
		$body  = "A new visitor filled in the I'm New card.\n\nName: $name\nPhone: $phone\nEmail: $email\nArea: $area\nHeard about us: $heard\nVisit date: $date\nInterested in: " . implode( ', ', $labels ) . "\n";
		$body .= $prayer ? "\nPrayer request:\n$prayer\n" : '';
		$wa    = dck_ct_wa_link( $phone, dck_visitor_wa_text( $name ) );
		$body .= $wa ? "\nWelcome them on WhatsApp: $wa\n" : '';
		$body .= "\nFollow-up list: " . admin_url( 'edit.php?post_type=dck_visitor' );
		wp_mail( dck_ct_email_to( 'dck_welcome_email' ), "New visitor: $name", $body );

		if ( $email ) {
			wp_mail(
				$email,
				sprintf( __( 'Welcome to %s', 'dck-elite' ), get_bloginfo( 'name' ) ),
				dck_ct_fill( dck_home_val( 'dck_welcome_email_text', "Dear {name},\n\nThank you for worshipping with us. It was a joy to have you! A member of our welcome team will reach out to you this week.\n\nWe gather every Sunday. You are always welcome home.\n\nWith love,\nThe Welcome Team" ), array( 'name' => $name ) )
			);
		}
		return new WP_REST_Response( array( 'ok' => true, 'message' => __( 'Thank you! We are so glad you came. Someone from our welcome team will reach out to you this week.', 'dck-elite' ) ), 200 );
	}
}

/* ---------- Admin: list ---------- */
add_filter(
	'manage_dck_visitor_posts_columns',
	function () {
		return array(
			'cb'         => '<input type="checkbox">',
			'title'      => __( 'Name', 'dck-elite' ),
			'dck_phone'  => __( 'Phone', 'dck-elite' ),
			'dck_area'   => __( 'Area', 'dck-elite' ),
			'dck_wants'  => __( 'Interested in', 'dck-elite' ),
			'dck_status' => __( 'Follow-up', 'dck-elite' ),
			'dck_wa'     => __( 'WhatsApp', 'dck-elite' ),
			'date'       => __( 'Received', 'dck-elite' ),
		);
	}
);
add_action(
	'manage_dck_visitor_posts_custom_column',
	function ( $col, $id ) {
		$m  = dck_visitor_meta( $id );
		$st = dck_visitor_statuses();
		if ( 'dck_phone' === $col ) {
			echo esc_html( $m['phone'] );
		} elseif ( 'dck_area' === $col ) {
			echo esc_html( $m['area'] );
		} elseif ( 'dck_wants' === $col ) {
			$l = array();
			foreach ( $m['interests'] as $i ) {
				if ( isset( dck_visitor_interests()[ $i ] ) ) {
					$l[] = dck_visitor_interests()[ $i ];
				}
			}
			echo esc_html( implode( ', ', $l ) );
			echo $m['prayer'] ? '<br><em>' . esc_html__( 'Has a prayer request', 'dck-elite' ) . '</em>' : '';
		} elseif ( 'dck_status' === $col ) {
			$s = isset( $st[ $m['status'] ] ) ? $st[ $m['status'] ] : $st['new'];
			echo dck_ct_pill( $s[0], $s[1] ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo '<div style="margin-top:4px;font-size:12px">';
			foreach ( $st as $k => $v ) {
				if ( $k !== $m['status'] ) {
					echo '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dck_visitor_status&id=' . $id . '&to=' . $k ), 'dck_visitor_status_' . $id ) ) . '">' . esc_html( $v[0] ) . '</a> ';
				}
			}
			echo '</div>';
		} elseif ( 'dck_wa' === $col ) {
			$wa = dck_ct_wa_link( $m['phone'], dck_visitor_wa_text( $m['name'] ) );
			if ( $wa ) {
				echo '<a class="button button-small" target="_blank" rel="noopener" href="' . esc_url( $wa ) . '">' . esc_html__( 'Send welcome', 'dck-elite' ) . '</a>';
			}
		}
	},
	10,
	2
);
add_action(
	'restrict_manage_posts',
	function ( $type ) {
		if ( 'dck_visitor' !== $type ) {
			return;
		}
		$cur = isset( $_GET['dck_status'] ) ? sanitize_key( wp_unslash( $_GET['dck_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		echo '<select name="dck_status"><option value="">' . esc_html__( 'All follow-up stages', 'dck-elite' ) . '</option>';
		foreach ( dck_visitor_statuses() as $k => $s ) {
			echo '<option value="' . esc_attr( $k ) . '"' . selected( $cur, $k, false ) . '>' . esc_html( $s[0] ) . '</option>';
		}
		echo '</select>';
	}
);
add_action(
	'manage_posts_extra_tablenav',
	function ( $which ) {
		$screen = get_current_screen();
		if ( 'top' !== $which || ! $screen || 'edit-dck_visitor' !== $screen->id ) {
			return;
		}
		echo '<div class="alignleft actions"><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dck_visitor_csv' ), 'dck_visitor_csv' ) ) . '">' . esc_html__( 'Download CSV', 'dck-elite' ) . '</a> ';
		echo '<a class="button" href="' . esc_url( admin_url( 'edit.php?post_type=dck_visitor&page=dck-visitor-qr' ) ) . '">' . esc_html__( 'QR code for seats', 'dck-elite' ) . '</a></div>';
	}
);
add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( ! is_admin() || ! $q->is_main_query() || 'dck_visitor' !== $q->get( 'post_type' ) ) {
			return;
		}
		$st = isset( $_GET['dck_status'] ) ? sanitize_key( wp_unslash( $_GET['dck_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( $st ) {
			$q->set( 'meta_key', '_dck_v_status' );
			$q->set( 'meta_value', $st );
		}
	}
);
add_action(
	'admin_post_dck_visitor_status',
	function () {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$to = isset( $_GET['to'] ) ? sanitize_key( wp_unslash( $_GET['to'] ) ) : '';
		check_admin_referer( 'dck_visitor_status_' . $id );
		if ( ! $id || 'dck_visitor' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) || ! array_key_exists( $to, dck_visitor_statuses() ) ) {
			wp_die( esc_html__( 'Not allowed.', 'dck-elite' ) );
		}
		update_post_meta( $id, '_dck_v_status', $to );
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=dck_visitor' ) );
		exit;
	}
);
add_action(
	'admin_post_dck_visitor_csv',
	function () {
		check_admin_referer( 'dck_visitor_csv' );
		if ( ! current_user_can( 'edit_others_pages' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'dck-elite' ) );
		}
		$rows = array();
		foreach ( get_posts( array( 'post_type' => 'dck_visitor', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $id ) {
			$m      = dck_visitor_meta( $id );
			$rows[] = array( get_the_date( 'Y-m-d', $id ), $m['name'], $m['phone'], $m['email'], $m['area'], $m['heard'], $m['visit_date'], implode( ', ', $m['interests'] ), $m['status'], $m['prayer'], $m['notes'] );
		}
		dck_ct_csv( 'new-visitors-' . gmdate( 'Y-m-d' ) . '.csv', array( 'Received', 'Name', 'Phone', 'Email', 'Area', 'Heard about us', 'Visit date', 'Interested in', 'Follow-up', 'Prayer request', 'Team notes' ), $rows );
	}
);

/* Details + team notes on the edit screen (also used for walk-ins added by hand). */
add_action(
	'add_meta_boxes_dck_visitor',
	function () {
		add_meta_box(
			'dck_visitor_box',
			__( 'Visitor details & follow-up', 'dck-elite' ),
			function ( $post ) {
				wp_nonce_field( 'dck_visitor_save', 'dck_visitor_nonce' );
				$m = dck_visitor_meta( $post->ID );
				$i = 'width:100%;max-width:520px';
				foreach ( array( 'phone' => __( 'Phone', 'dck-elite' ), 'email' => __( 'Email', 'dck-elite' ), 'area' => __( 'Area', 'dck-elite' ), 'heard' => __( 'How they heard about us', 'dck-elite' ), 'visit_date' => __( 'Visit date (YYYY-MM-DD)', 'dck-elite' ) ) as $k => $l ) {
					echo '<p><label><strong>' . esc_html( $l ) . '</strong><br><input type="text" name="dck_v[' . esc_attr( $k ) . ']" value="' . esc_attr( $m[ $k ] ) . '" style="' . esc_attr( $i ) . '"></label></p>';
				}
				echo '<p><strong>' . esc_html__( 'Interested in', 'dck-elite' ) . '</strong><br>';
				foreach ( dck_visitor_interests() as $k => $l ) {
					echo '<label style="display:inline-block;margin:0 14px 6px 0"><input type="checkbox" name="dck_v_int[]" value="' . esc_attr( $k ) . '"' . checked( in_array( $k, $m['interests'], true ), true, false ) . '> ' . esc_html( $l ) . '</label>';
				}
				echo '</p><p><label><strong>' . esc_html__( 'Follow-up stage', 'dck-elite' ) . '</strong><br><select name="dck_v[status]">';
				foreach ( dck_visitor_statuses() as $k => $s ) {
					echo '<option value="' . esc_attr( $k ) . '"' . selected( $m['status'], $k, false ) . '>' . esc_html( $s[0] ) . '</option>';
				}
				echo '</select></label></p>';
				echo '<p><label><strong>' . esc_html__( 'Prayer request', 'dck-elite' ) . '</strong><br><textarea name="dck_v[prayer]" rows="3" style="' . esc_attr( $i ) . '">' . esc_textarea( $m['prayer'] ) . '</textarea></label></p>';
				echo '<p><label><strong>' . esc_html__( 'Team notes (calls made, who followed up)', 'dck-elite' ) . '</strong><br><textarea name="dck_v[notes]" rows="4" style="' . esc_attr( $i ) . '">' . esc_textarea( $m['notes'] ) . '</textarea></label></p>';
				$wa = dck_ct_wa_link( $m['phone'], dck_visitor_wa_text( $m['name'] ? $m['name'] : $post->post_title ) );
				if ( $wa ) {
					echo '<p><a class="button button-primary" target="_blank" rel="noopener" href="' . esc_url( $wa ) . '">' . esc_html__( 'Send welcome on WhatsApp', 'dck-elite' ) . '</a></p>';
				}
			},
			'dck_visitor',
			'normal',
			'high'
		);
	}
);
add_action(
	'save_post_dck_visitor',
	function ( $post_id ) {
		if ( ! isset( $_POST['dck_visitor_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dck_visitor_nonce'] ) ), 'dck_visitor_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$in = isset( $_POST['dck_v'] ) ? (array) wp_unslash( $_POST['dck_v'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		foreach ( array( 'phone', 'email', 'area', 'heard', 'visit_date' ) as $k ) {
			update_post_meta( $post_id, '_dck_v_' . $k, sanitize_text_field( isset( $in[ $k ] ) ? $in[ $k ] : '' ) );
		}
		foreach ( array( 'prayer', 'notes' ) as $k ) {
			update_post_meta( $post_id, '_dck_v_' . $k, sanitize_textarea_field( isset( $in[ $k ] ) ? $in[ $k ] : '' ) );
		}
		$st = sanitize_key( isset( $in['status'] ) ? $in['status'] : 'new' );
		update_post_meta( $post_id, '_dck_v_status', array_key_exists( $st, dck_visitor_statuses() ) ? $st : 'new' );
		$ints = isset( $_POST['dck_v_int'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['dck_v_int'] ) ) : array();
		update_post_meta( $post_id, '_dck_v_interests', array_values( array_intersect( $ints, array_keys( dck_visitor_interests() ) ) ) );
		update_post_meta( $post_id, '_dck_v_name', get_the_title( $post_id ) );
	}
);

/* QR code page (print and place on seats / welcome desk). */
add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'edit.php?post_type=dck_visitor',
			__( 'QR code for seats', 'dck-elite' ),
			__( 'QR code', 'dck-elite' ),
			'edit_pages',
			'dck-visitor-qr',
			function () {
				$url = dck_elite_page_url( 'new-here' );
				$qr  = get_template_directory_uri() . '/assets/images/app/qr-new-here.svg';
				$png = get_template_directory_uri() . '/assets/images/app/qr-new-here.png';
				echo '<div class="wrap"><h1>' . esc_html__( "I'm New - QR code", 'dck-elite' ) . '</h1>';
				echo '<p>' . esc_html__( 'Print this and place it on seats, the welcome desk, bulletins or the screen during announcements. Scanning opens the I\'m New card on the website. It is also shown on the homepage.', 'dck-elite' ) . '</p>';
				echo '<div style="background:#fff;display:inline-block;padding:28px 32px;border-radius:18px;text-align:center;border:1px solid #ddd;max-width:360px">';
				echo '<p style="font-size:24px;font-weight:700;margin:0 0 4px;color:#0E1C4D">' . esc_html__( 'New here? Karibu!', 'dck-elite' ) . '</p><p style="margin:0 0 14px;color:#555">' . esc_html__( 'Scan to let us know you visited', 'dck-elite' ) . '</p>';
				echo '<img src="' . esc_url( $qr ) . '" width="280" height="280" alt="QR code"><p style="margin:10px 0 0;font-size:13px;color:#555">' . esc_html( $url ) . '</p></div>';
				echo '<p><a class="button button-primary" href="' . esc_url( $png ) . '" download="dck-im-new-qr.png">' . esc_html__( 'Download for printing (PNG)', 'dck-elite' ) . '</a> <a class="button" href="' . esc_url( $qr ) . '" download="dck-im-new-qr.svg">' . esc_html__( 'Download for designers (SVG)', 'dck-elite' ) . '</a> <button class="button" onclick="window.print()">' . esc_html__( 'Print', 'dck-elite' ) . '</button></p>';
				if ( false === strpos( $url, '/new-here' ) ) {
					echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'The I\'m New page address has changed. The QR code points to dckabeten.org/new-here/ - keep that address or ask your developer to regenerate the code.', 'dck-elite' ) . '</p></div>';
				}
				echo '</div>';
			}
		);
	}
);

/* Create the /new-here/ page once. */
add_action(
	'admin_init',
	function () {
		if ( get_option( 'dck_newhere_page_v1' ) || ! current_user_can( 'publish_pages' ) ) {
			return;
		}
		if ( ! get_page_by_path( 'new-here' ) ) {
			$pid = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => "I'm New", 'post_name' => 'new-here', 'post_content' => '' ) );
			if ( $pid && ! is_wp_error( $pid ) ) {
				update_post_meta( $pid, '_wp_page_template', 'template-welcome.php' );
			}
		}
		update_option( 'dck_newhere_page_v1', 1, false );
	}
);

/* "New here?" band with the branded QR code (homepage). QR shows on larger screens; phones get a button. */
if ( ! function_exists( 'dck_newhere_band' ) ) {
	function dck_newhere_band() {
		$url = dck_elite_page_url( 'new-here' );
		$qr  = get_template_directory_uri() . '/assets/images/app/qr-new-here.svg';
		?>
		<section class="dck-newband" aria-labelledby="dck-newband-title">
			<div class="dck-container dck-newband__inner">
				<div class="dck-newband__copy">
					<p class="dck-eyebrow"><?php esc_html_e( 'First Time With Us?', 'dck-elite' ); ?></p>
					<h2 id="dck-newband-title"><?php echo esc_html( dck_home_val( 'dck_newband_title', __( 'Karibu! Let us know you visited', 'dck-elite' ) ) ); ?></h2>
					<p><?php echo esc_html( dck_home_val( 'dck_newband_text', __( 'Fill our one-minute welcome card and someone from our welcome team will reach out to you this week. No pressure, just a warm hello.', 'dck-elite' ) ) ); ?></p>
					<div class="dck-newband__actions">
						<a class="dck-btn dck-btn--gold" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Fill the welcome card', 'dck-elite' ); ?></a>
						<a class="dck-btn dck-btn--ghost" href="<?php echo esc_url( dck_elite_page_url( 'services' ) ); ?>"><?php esc_html_e( 'Service times', 'dck-elite' ); ?></a>
					</div>
				</div>
				<figure class="dck-newband__qr">
					<div class="dck-newband__qrbox"><img src="<?php echo esc_url( $qr ); ?>" width="220" height="220" alt="<?php esc_attr_e( 'QR code that opens the welcome card', 'dck-elite' ); ?>" loading="lazy" decoding="async"></div>
					<figcaption><strong><?php esc_html_e( 'Scan with your phone camera', 'dck-elite' ); ?></strong><span><?php echo esc_html( preg_replace( '#^https?://#', '', untrailingslashit( $url ) ) ); ?></span></figcaption>
				</figure>
			</div>
		</section>
		<?php
	}
}
