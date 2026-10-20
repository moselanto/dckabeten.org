<?php
/**
 * Giving records: members confirm an M-Pesa / bank gift on the Give page
 * (no Daraja needed). Finance team verifies each record against the Equity
 * statement, then sends a thank-you by email and/or one-tap WhatsApp.
 *
 * Data: private post type dck_gift (editors and admins only).
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
			'dck_gift',
			array(
				'labels'          => array(
					'name'          => __( 'Giving Records', 'dck-elite' ),
					'singular_name' => __( 'Giving Record', 'dck-elite' ),
					'menu_name'     => __( 'Giving Records', 'dck-elite' ),
					'search_items'  => __( 'Search giving records', 'dck-elite' ),
					'not_found'     => __( 'No giving records yet', 'dck-elite' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'menu_icon'       => 'dashicons-money-alt',
				'menu_position'   => 24,
				'supports'        => array( 'title' ),
				'capability_type' => 'page',
				'map_meta_cap'    => true,
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			)
		);
	}
);

if ( ! function_exists( 'dck_gift_statuses' ) ) {
	function dck_gift_statuses() {
		return array(
			'pending'  => array( __( 'To verify', 'dck-elite' ), 'amber' ),
			'verified' => array( __( 'Verified', 'dck-elite' ), 'green' ),
			'rejected' => array( __( 'Not found', 'dck-elite' ), 'red' ),
		);
	}
}

if ( ! function_exists( 'dck_gift_purpose_list' ) ) {
	/* Title => description, from DCK Church > Giving > "Where your gift goes". */
	function dck_gift_purpose_list() {
		$out = array();
		foreach ( explode( "\n", (string) dck_home_val( 'dck_give_purposes', "Tithes|Honour God with the first tenth of your increase.\nOfferings|Freewill gifts of thanksgiving and worship.\nChurch Building|Help us keep building our sanctuary in Uthiru.\nMissions and Evangelism|Carry the Gospel to new places.\nWelfare and Care|Support members and neighbours in need." ) ) as $line ) {
			$parts = array_map( 'trim', explode( '|', $line, 2 ) );
			if ( '' !== $parts[0] ) {
				$out[ $parts[0] ] = isset( $parts[1] ) ? $parts[1] : '';
			}
		}
		return $out;
	}
}

if ( ! function_exists( 'dck_gift_purposes' ) ) {
	function dck_gift_purposes() {
		$out = array();
		foreach ( explode( "\n", (string) dck_home_val( 'dck_give_purposes', "Tithes|\nOfferings|\nChurch Building|\nMissions and Evangelism|\nWelfare and Care|" ) ) as $line ) {
			$t = trim( (string) strtok( $line, '|' ) );
			if ( '' !== $t ) {
				$out[] = $t;
			}
		}
		return $out ? $out : array( 'Tithes', 'Offerings' );
	}
}

if ( ! function_exists( 'dck_gift_meta' ) ) {
	function dck_gift_meta( $id ) {
		$k = array( 'name', 'phone', 'email', 'purpose', 'amount', 'code', 'channel', 'status', 'note' );
		$m = array();
		foreach ( $k as $key ) {
			$m[ $key ] = (string) get_post_meta( $id, '_dck_gift_' . $key, true );
		}
		if ( '' === $m['status'] ) {
			$m['status'] = 'pending';
		}
		return $m;
	}
}

/* ---------- Public endpoint ---------- */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'dck/v1',
			'/gift',
			array(
				'methods'             => 'POST',
				'callback'            => 'dck_gift_rest',
				'permission_callback' => '__return_true',
			)
		);
	}
);

if ( ! function_exists( 'dck_gift_rest' ) ) {
	function dck_gift_rest( $request ) {
		$p = (array) $request->get_json_params();
		if ( function_exists( 'dck_sec_guard' ) ) {
			$g = dck_sec_guard( $request, $p, 'gift', 3000, 5, 900 );
			if ( true !== $g ) {
				return $g;
			}
		}
		$name    = dck_sec_cap( sanitize_text_field( isset( $p['name'] ) ? $p['name'] : '' ), 120 );
		$phone   = dck_sec_cap( sanitize_text_field( isset( $p['phone'] ) ? $p['phone'] : '' ), 40 );
		$email   = dck_sec_cap( sanitize_email( isset( $p['email'] ) ? $p['email'] : '' ), 190 );
		$purpose = sanitize_text_field( isset( $p['purpose'] ) ? $p['purpose'] : '' );
		$channel = sanitize_key( isset( $p['channel'] ) ? $p['channel'] : 'mpesa' );
		$code    = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', (string) ( isset( $p['code'] ) ? $p['code'] : '' ) ) );
		$amount  = (float) preg_replace( '/[^0-9.]/', '', (string) ( isset( $p['amount'] ) ? $p['amount'] : '' ) );
		$note    = dck_sec_cap( sanitize_textarea_field( isset( $p['note'] ) ? $p['note'] : '' ), 500 );

		if ( '' === $name || ! dck_ct_wa_number( $phone ) || $amount <= 0 ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Please enter your name, a valid phone number and the amount.', 'dck-elite' ) ), 200 );
		}
		if ( $amount > 10000000 ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Please check the amount.', 'dck-elite' ) ), 200 );
		}
		if ( ! in_array( $channel, array( 'mpesa', 'bank' ), true ) ) {
			$channel = 'mpesa';
		}
		if ( 'mpesa' === $channel && ! preg_match( '/^[A-Z0-9]{10}$/', $code ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Please enter the 10-character M-Pesa code from your confirmation SMS (for example SJK4ABC12D).', 'dck-elite' ) ), 200 );
		}
		if ( $email && ! is_email( $email ) ) {
			$email = '';
		}
		if ( 'other' === $purpose ) {
			$other   = dck_sec_cap( sanitize_text_field( isset( $p['purpose_other'] ) ? $p['purpose_other'] : '' ), 80 );
			$purpose = $other ? 'Other: ' . $other : __( 'General', 'dck-elite' );
		} elseif ( ! in_array( $purpose, dck_gift_purposes(), true ) ) {
			$purpose = __( 'General', 'dck-elite' );
		}
		if ( $code ) {
			$dupe = get_posts(
				array(
					'post_type'      => 'dck_gift',
					'post_status'    => 'any',
					'meta_key'       => '_dck_gift_code', // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_value'     => $code, // phpcs:ignore WordPress.DB.SlowDBQuery
					'fields'         => 'ids',
					'posts_per_page' => 1,
				)
			);
			if ( $dupe ) {
				return new WP_REST_Response( array( 'ok' => true, 'message' => __( 'Thank you. This transaction has already been recorded.', 'dck-elite' ) ), 200 );
			}
		}

		$id = wp_insert_post(
			array(
				'post_type'   => 'dck_gift',
				'post_status' => 'private',
				'post_title'  => sprintf( '%s - KES %s - %s', $name, number_format_i18n( $amount ), $purpose ),
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'We could not save your record. Please try again.', 'dck-elite' ) ), 200 );
		}
		foreach ( array( 'name' => $name, 'phone' => $phone, 'email' => $email, 'purpose' => $purpose, 'amount' => $amount, 'code' => $code, 'channel' => $channel, 'status' => 'pending', 'note' => $note ) as $k => $v ) {
			update_post_meta( $id, '_dck_gift_' . $k, $v );
		}

		$body  = "A new gift was recorded on the website. Please verify it against the Equity statement.\n\n";
		$body .= "Name: $name\nPhone: $phone\nEmail: $email\nPurpose: $purpose\nAmount: KES " . number_format_i18n( $amount ) . "\nChannel: " . strtoupper( $channel ) . "\nM-Pesa code: $code\n" . ( $note ? "Note: $note\n" : '' );
		$body .= "\nReview: " . admin_url( 'edit.php?post_type=dck_gift' );
		wp_mail( dck_ct_email_to( 'dck_give_finance_email' ), 'New giving record - KES ' . number_format_i18n( $amount ) . ' (' . $purpose . ')', $body );

		return new WP_REST_Response(
			array(
				'ok'      => true,
				'message' => __( 'Thank you for giving! Our finance team will confirm your gift and send you a thank-you message.', 'dck-elite' ),
			),
			200
		);
	}
}

/* ---------- Admin list ---------- */
add_filter(
	'manage_dck_gift_posts_columns',
	function () {
		return array(
			'cb'          => '<input type="checkbox">',
			'title'       => __( 'Giver', 'dck-elite' ),
			'dck_amount'  => __( 'Amount (KES)', 'dck-elite' ),
			'dck_purpose' => __( 'Purpose', 'dck-elite' ),
			'dck_code'    => __( 'M-Pesa code', 'dck-elite' ),
			'dck_phone'   => __( 'Phone', 'dck-elite' ),
			'dck_status'  => __( 'Status', 'dck-elite' ),
			'dck_thanks'  => __( 'Thank-you', 'dck-elite' ),
			'date'        => __( 'Received', 'dck-elite' ),
		);
	}
);

add_action(
	'manage_dck_gift_posts_custom_column',
	function ( $col, $id ) {
		$m  = dck_gift_meta( $id );
		$st = dck_gift_statuses();
		switch ( $col ) {
			case 'dck_amount':
				echo '<strong>' . esc_html( number_format_i18n( (float) $m['amount'] ) ) . '</strong>';
				break;
			case 'dck_purpose':
				echo esc_html( $m['purpose'] );
				break;
			case 'dck_code':
				echo '<code>' . esc_html( $m['code'] ? $m['code'] : strtoupper( $m['channel'] ) ) . '</code>';
				break;
			case 'dck_phone':
				echo esc_html( $m['phone'] );
				break;
			case 'dck_status':
				$s = isset( $st[ $m['status'] ] ) ? $st[ $m['status'] ] : $st['pending'];
				echo dck_ct_pill( $s[0], $s[1] ); // phpcs:ignore WordPress.Security.EscapeOutput
				foreach ( array( 'verified', 'rejected' ) as $to ) {
					if ( $to !== $m['status'] ) {
						$u = wp_nonce_url( admin_url( 'admin-post.php?action=dck_gift_status&id=' . $id . '&to=' . $to ), 'dck_gift_status_' . $id );
						echo '<br><a href="' . esc_url( $u ) . '">' . esc_html( 'verified' === $to ? __( 'Mark verified', 'dck-elite' ) : __( 'Not found', 'dck-elite' ) ) . '</a>';
					}
				}
				break;
			case 'dck_thanks':
				if ( 'verified' === $m['status'] ) {
					$txt = dck_ct_fill(
						dck_home_val( 'dck_give_thanks_wa', "Dear {name}, thank you for your gift of KES {amount} towards {purpose}. We have received it with gratitude. God bless you abundantly! - Deliverance Church Kabete N" ),
						array( 'name' => $m['name'], 'amount' => number_format_i18n( (float) $m['amount'] ), 'purpose' => $m['purpose'] )
					);
					$wa = dck_ct_wa_link( $m['phone'], $txt );
					if ( $wa ) {
						echo '<a class="button button-small" target="_blank" rel="noopener" href="' . esc_url( $wa ) . '">' . esc_html__( 'WhatsApp', 'dck-elite' ) . '</a> ';
					}
					echo $m['email'] ? esc_html__( 'Email sent', 'dck-elite' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput
				} else {
					echo '<span style="color:#888">' . esc_html__( 'After verifying', 'dck-elite' ) . '</span>';
				}
				break;
		}
	},
	10,
	2
);

/* Status filter + export button above the list. */
add_action(
	'restrict_manage_posts',
	function ( $type ) {
		if ( 'dck_gift' !== $type ) {
			return;
		}
		$cur = isset( $_GET['dck_status'] ) ? sanitize_key( wp_unslash( $_GET['dck_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		echo '<select name="dck_status"><option value="">' . esc_html__( 'All statuses', 'dck-elite' ) . '</option>';
		foreach ( dck_gift_statuses() as $k => $s ) {
			echo '<option value="' . esc_attr( $k ) . '"' . selected( $cur, $k, false ) . '>' . esc_html( $s[0] ) . '</option>';
		}
		echo '</select>';
	}
);
add_action(
	'manage_posts_extra_tablenav',
	function ( $which ) {
		$screen = get_current_screen();
		if ( 'top' !== $which || ! $screen || 'edit-dck_gift' !== $screen->id ) {
			return;
		}
		$u = wp_nonce_url( admin_url( 'admin-post.php?action=dck_gift_csv' ), 'dck_gift_csv' );
		echo '<div class="alignleft actions"><a class="button" href="' . esc_url( $u ) . '">' . esc_html__( 'Download CSV', 'dck-elite' ) . '</a></div>';
	}
);
add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( ! is_admin() || ! $q->is_main_query() || 'dck_gift' !== $q->get( 'post_type' ) ) {
			return;
		}
		$st = isset( $_GET['dck_status'] ) ? sanitize_key( wp_unslash( $_GET['dck_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( $st ) {
			$q->set( 'meta_key', '_dck_gift_status' );
			$q->set( 'meta_value', $st );
		}
	}
);

/* Mark verified / not found. */
add_action(
	'admin_post_dck_gift_status',
	function () {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$to = isset( $_GET['to'] ) ? sanitize_key( wp_unslash( $_GET['to'] ) ) : '';
		check_admin_referer( 'dck_gift_status_' . $id );
		if ( ! $id || 'dck_gift' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) || ! array_key_exists( $to, dck_gift_statuses() ) ) {
			wp_die( esc_html__( 'Not allowed.', 'dck-elite' ) );
		}
		$m = dck_gift_meta( $id );
		update_post_meta( $id, '_dck_gift_status', $to );
		update_post_meta( $id, '_dck_gift_by', get_current_user_id() );
		if ( 'verified' === $to && $m['email'] && ! get_post_meta( $id, '_dck_gift_thanked', true ) ) {
			$msg = dck_ct_fill(
				dck_home_val( 'dck_give_thanks_wa', "Dear {name}, thank you for your gift of KES {amount} towards {purpose}. We have received it with gratitude. God bless you abundantly! - Deliverance Church Kabete N" ),
				array( 'name' => $m['name'], 'amount' => number_format_i18n( (float) $m['amount'] ), 'purpose' => $m['purpose'] )
			);
			$msg .= "\n\n" . sprintf( 'Receipt reference: %s | Date: %s', $m['code'] ? $m['code'] : '#' . $id, get_the_date( 'j M Y', $id ) );
			wp_mail( $m['email'], __( 'Thank you for your gift', 'dck-elite' ) . ' - ' . get_bloginfo( 'name' ), $msg );
			update_post_meta( $id, '_dck_gift_thanked', time() );
		}
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=dck_gift' ) );
		exit;
	}
);

/* CSV export. */
add_action(
	'admin_post_dck_gift_csv',
	function () {
		check_admin_referer( 'dck_gift_csv' );
		if ( ! current_user_can( 'edit_others_pages' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'dck-elite' ) );
		}
		$ids  = get_posts( array( 'post_type' => 'dck_gift', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) );
		$rows = array();
		foreach ( $ids as $id ) {
			$m      = dck_gift_meta( $id );
			$rows[] = array( get_the_date( 'Y-m-d H:i', $id ), $m['name'], $m['phone'], $m['email'], $m['purpose'], $m['amount'], strtoupper( $m['channel'] ), $m['code'], $m['status'], $m['note'] );
		}
		dck_ct_csv( 'giving-records-' . gmdate( 'Y-m-d' ) . '.csv', array( 'Date', 'Name', 'Phone', 'Email', 'Purpose', 'Amount KES', 'Channel', 'M-Pesa code', 'Status', 'Note' ), $rows );
	}
);

/* Record detail screen: read-only summary. */
add_action(
	'add_meta_boxes_dck_gift',
	function () {
		add_meta_box(
			'dck_gift_details',
			__( 'Gift details', 'dck-elite' ),
			function ( $post ) {
				$m = dck_gift_meta( $post->ID );
				echo '<table class="widefat striped"><tbody>';
				foreach ( array( 'name' => 'Name', 'phone' => 'Phone', 'email' => 'Email', 'purpose' => 'Purpose', 'amount' => 'Amount (KES)', 'channel' => 'Channel', 'code' => 'M-Pesa code', 'status' => 'Status', 'note' => 'Note' ) as $k => $l ) {
					echo '<tr><th style="width:160px">' . esc_html( $l ) . '</th><td>' . esc_html( $m[ $k ] ) . '</td></tr>';
				}
				echo '</tbody></table>';
			},
			'dck_gift',
			'normal',
			'high'
		);
	}
);

/* Front-end: "Tell us you gave" form, rendered on the Give page. */
if ( ! function_exists( 'dck_gift_form' ) ) {
	function dck_gift_form() {
		wp_enqueue_script( 'dck-forms' );
		?>
		<section class="dck-section dck-gift" id="confirm-gift">
			<div class="dck-container dck-gift__grid">
				<div class="dck-gift__intro">
					<p class="dck-eyebrow"><?php esc_html_e( 'After You Give', 'dck-elite' ); ?></p>
					<h2><?php echo esc_html( dck_home_val( 'dck_give_confirm_title', __( 'Let us know so we can thank you', 'dck-elite' ) ) ); ?></h2>
					<p><?php echo esc_html( dck_home_val( 'dck_give_confirm_text', __( 'Paid through M-Pesa or the bank? Share the details below. Our finance team confirms every gift against the church statement and sends you a thank-you receipt.', 'dck-elite' ) ) ); ?></p>
					<ul class="dck-gift__points">
						<li><?php esc_html_e( 'Takes less than a minute', 'dck-elite' ); ?></li>
						<li><?php esc_html_e( 'Your gift is recorded under the right purpose', 'dck-elite' ); ?></li>
						<li><?php esc_html_e( 'Only the finance team sees your details', 'dck-elite' ); ?></li>
					</ul>
				</div>
				<form class="dck-ctform dck-formcard" data-endpoint="<?php echo esc_url( rest_url( 'dck/v1/gift' ) ); ?>" novalidate>
					<div class="dck-cform__field">
						<span><?php esc_html_e( 'How did you give?', 'dck-elite' ); ?></span>
						<div class="dck-seg">
							<label><input type="radio" name="channel" value="mpesa" checked><span><?php esc_html_e( 'M-Pesa', 'dck-elite' ); ?></span></label>
							<label><input type="radio" name="channel" value="bank"><span><?php esc_html_e( 'Bank', 'dck-elite' ); ?></span></label>
						</div>
					</div>
					<div class="dck-cform__field">
						<span><?php esc_html_e( 'What is your gift for?', 'dck-elite' ); ?> <em class="dck-req">*</em></span>
						<div class="dck-purpose" role="radiogroup">
							<?php $dck_first_p = true; foreach ( dck_gift_purpose_list() as $pt => $pd ) : ?>
								<label class="dck-purpose__opt"><input type="radio" name="purpose" value="<?php echo esc_attr( $pt ); ?>"<?php checked( $dck_first_p ); ?>><span class="dck-purpose__card"><strong><?php echo esc_html( $pt ); ?></strong><?php if ( $pd ) : ?><small><?php echo esc_html( wp_trim_words( $pd, 12 ) ); ?></small><?php endif; ?></span></label>
							<?php $dck_first_p = false; endforeach; ?>
							<label class="dck-purpose__opt"><input type="radio" name="purpose" value="other"><span class="dck-purpose__card"><strong><?php esc_html_e( 'Something else', 'dck-elite' ); ?></strong><small><?php esc_html_e( 'A pledge, project or special appeal', 'dck-elite' ); ?></small></span></label>
						</div>
						<input type="text" name="purpose_other" class="dck-purpose__other" maxlength="80" hidden placeholder="<?php esc_attr_e( 'e.g. Building pledge, Youth camp, Thanksgiving', 'dck-elite' ); ?>">
					</div>
					<label class="dck-cform__field"><span><?php esc_html_e( 'Amount (KES)', 'dck-elite' ); ?> <em class="dck-req">*</em></span>
						<input type="number" name="amount" min="1" step="1" inputmode="numeric" required>
						<span class="dck-amounts"><?php foreach ( array( 500, 1000, 2000, 5000, 10000 ) as $amt ) : ?><button type="button" class="dck-chip" data-amt="<?php echo esc_attr( $amt ); ?>"><?php echo esc_html( number_format_i18n( $amt ) ); ?></button><?php endforeach; ?></span>
					</label>
					<label class="dck-cform__field dck-gift__code"><span><?php esc_html_e( 'M-Pesa code', 'dck-elite' ); ?></span><input type="text" name="code" maxlength="12" autocapitalize="characters" placeholder="<?php esc_attr_e( 'From your M-Pesa SMS, e.g. SJK4ABC12D', 'dck-elite' ); ?>"></label>
					<div class="dck-cform__row">
						<label class="dck-cform__field"><span><?php esc_html_e( 'Your name', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="text" name="name" autocomplete="name" required></label>
						<label class="dck-cform__field"><span><?php esc_html_e( 'Phone number', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="tel" name="phone" autocomplete="tel" required placeholder="07XX XXX XXX"></label>
					</div>
					<label class="dck-cform__field"><span><?php esc_html_e( 'Email', 'dck-elite' ); ?> <small class="dck-opt"><?php esc_html_e( 'Optional, for an emailed receipt', 'dck-elite' ); ?></small></span><input type="email" name="email" autocomplete="email"></label>
					<div class="dck-hp" aria-hidden="true"><label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
					<button type="submit" class="dck-btn dck-btn--primary dck-btn--block"><?php esc_html_e( 'Record my gift', 'dck-elite' ); ?></button>
					<p class="dck-ctform__status" role="status" aria-live="polite"></p>
				</form>
			</div>
		</section>
		<script>
		document.addEventListener('change',function(e){var t=e.target;if(!t){return;}
			if(t.name==='channel'){var c=document.querySelector('.dck-gift__code');if(c){c.hidden=t.value!=='mpesa';}}
			if(t.name==='purpose'){var o=document.querySelector('.dck-purpose__other');if(o){o.hidden=t.value!=='other';if(!o.hidden){o.focus();}}}
		});
		document.addEventListener('click',function(e){var c=e.target.closest&&e.target.closest('[data-purpose]');if(!c){return;}var r=document.querySelectorAll('.dck-purpose input[name="purpose"]');for(var i=0;i<r.length;i++){if(r[i].value===c.getAttribute('data-purpose')){r[i].checked=true;r[i].dispatchEvent(new Event('change',{bubbles:true}));}}});
		if(location.hash.indexOf('#give-')===0){var want=decodeURIComponent(location.hash.slice(6)).toLowerCase();var rr=document.querySelectorAll('.dck-purpose input[name="purpose"]');for(var j=0;j<rr.length;j++){if(rr[j].value.toLowerCase()===want){rr[j].checked=true;}}}
		document.addEventListener('click',function(e){var b=e.target.closest&&e.target.closest('[data-amt]');if(!b){return;}var i=document.querySelector('.dck-gift input[name="amount"]');if(i){i.value=b.getAttribute('data-amt');}var all=document.querySelectorAll('.dck-amounts .dck-chip');for(var k=0;k<all.length;k++){all[k].classList.toggle('is-active',all[k]===b);}});
		</script>
		<?php
	}
}
