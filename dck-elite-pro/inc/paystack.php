<?php
/**
 * Instant M-Pesa giving through Paystack (STK push to the giver's phone).
 *
 * Flow: giver picks purpose + amount + phone -> REST dck/v1/pay/start calls Paystack
 * /charge (mobile_money, provider mpesa, KES) -> phone shows the M-Pesa PIN prompt ->
 * page polls dck/v1/pay/status (which verifies with Paystack) and the signed webhook
 * dck/v1/pay/webhook confirms charge.success -> Giving Record is marked Verified and a
 * thank-you is sent automatically.
 *
 * Secret key lives ONLY in wp-config.php:  define( 'DCK_PAYSTACK_SECRET', 'sk_live_...' );
 * Paystack dashboard > Settings > API Keys & Webhooks > Webhook URL: {site}/wp-json/dck/v1/pay/webhook
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_pay_secret' ) ) {
	function dck_pay_secret() {
		return ( defined( 'DCK_PAYSTACK_SECRET' ) && is_string( DCK_PAYSTACK_SECRET ) && 0 === strpos( DCK_PAYSTACK_SECRET, 'sk_' ) ) ? DCK_PAYSTACK_SECRET : '';
	}
}
if ( ! function_exists( 'dck_pay_ready' ) ) {
	function dck_pay_ready() {
		return '' !== dck_pay_secret();
	}
}
if ( ! function_exists( 'dck_pay_test_mode' ) ) {
	function dck_pay_test_mode() {
		return 0 === strpos( dck_pay_secret(), 'sk_test_' );
	}
}

/* Call the Paystack API. Returns decoded body array or WP_Error. */
if ( ! function_exists( 'dck_pay_api' ) ) {
	function dck_pay_api( $method, $path, $body = null ) {
		$args = array(
			'method'  => $method,
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . dck_pay_secret(),
				'Content-Type'  => 'application/json',
				'Cache-Control' => 'no-cache',
			),
		);
		if ( null !== $body ) {
			$args['body'] = wp_json_encode( $body );
		}
		$res = wp_remote_request( 'https://api.paystack.co' . $path, $args );
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		$data = json_decode( (string) wp_remote_retrieve_body( $res ), true );
		return is_array( $data ) ? $data : new WP_Error( 'dck_pay_bad', 'Bad response' );
	}
}

/* Find the giving record for a Paystack reference. */
if ( ! function_exists( 'dck_pay_find' ) ) {
	function dck_pay_find( $ref ) {
		$ids = get_posts( array( 'post_type' => 'dck_gift', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_dck_gift_code', 'meta_value' => $ref ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		return $ids ? (int) $ids[0] : 0;
	}
}

/* Mark a record paid (idempotent), after checking amount + currency match. */
if ( ! function_exists( 'dck_pay_mark_paid' ) ) {
	function dck_pay_mark_paid( $id, $tx ) {
		if ( ! $id || 'verified' === get_post_meta( $id, '_dck_gift_status', true ) ) {
			return (bool) $id;
		}
		$expected = (int) round( (float) get_post_meta( $id, '_dck_gift_amount', true ) * 100 );
		if ( (int) ( isset( $tx['amount'] ) ? $tx['amount'] : 0 ) !== $expected || 'KES' !== strtoupper( (string) ( isset( $tx['currency'] ) ? $tx['currency'] : '' ) ) ) {
			update_post_meta( $id, '_dck_gift_note', 'Paystack amount/currency mismatch - check manually.' );
			return false;
		}
		update_post_meta( $id, '_dck_gift_status', 'verified' );
		update_post_meta( $id, '_dck_gift_paid_at', isset( $tx['paid_at'] ) ? sanitize_text_field( $tx['paid_at'] ) : current_time( 'mysql' ) );
		if ( ! empty( $tx['receipt_number'] ) ) {
			update_post_meta( $id, '_dck_gift_note', 'M-Pesa receipt: ' . sanitize_text_field( $tx['receipt_number'] ) );
		}
		$m = function_exists( 'dck_gift_meta' ) ? dck_gift_meta( $id ) : array();
		if ( ! empty( $m['email'] ) && ! get_post_meta( $id, '_dck_gift_thanked', true ) ) {
			$msg = dck_ct_fill(
				dck_home_val( 'dck_give_thanks_wa', 'Dear {name}, thank you for your gift of {amount} towards {purpose}. We have received it with gratitude. God bless you abundantly! - Deliverance Church Kabete N' ),
				array( 'name' => $m['name'], 'amount' => dck_ct_money( $m['amount'], 'KES' ), 'purpose' => $m['purpose'] )
			);
			$msg .= "\n\n" . sprintf( 'Receipt reference: %s | Date: %s', $m['code'], wp_date( 'j M Y, H:i' ) );
			wp_mail( $m['email'], __( 'Thank you for your gift', 'dck-elite' ) . ' - ' . get_bloginfo( 'name' ), $msg );
			update_post_meta( $id, '_dck_gift_thanked', time() );
		}
		wp_mail(
			dck_ct_email_to( 'dck_give_finance_email' ),
			'M-Pesa gift received - ' . dck_ct_money( isset( $m['amount'] ) ? $m['amount'] : 0, 'KES' ) . ' (' . ( isset( $m['purpose'] ) ? $m['purpose'] : '' ) . ')',
			sprintf( "Paid and confirmed automatically by Paystack.\n\nName: %s\nPhone: %s\nPurpose: %s\nAmount: %s\nReference: %s\n\n%s", $m['name'], $m['phone'], $m['purpose'], dck_ct_money( $m['amount'], 'KES' ), $m['code'], admin_url( 'edit.php?post_type=dck_gift' ) )
		);
		return true;
	}
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route( 'dck/v1', '/pay/start', array( 'methods' => 'POST', 'callback' => 'dck_pay_start', 'permission_callback' => '__return_true' ) );
		register_rest_route( 'dck/v1', '/pay/otp', array( 'methods' => 'POST', 'callback' => 'dck_pay_otp', 'permission_callback' => '__return_true' ) );
		register_rest_route( 'dck/v1', '/pay/status', array( 'methods' => 'POST', 'callback' => 'dck_pay_status', 'permission_callback' => '__return_true' ) );
		register_rest_route( 'dck/v1', '/pay/webhook', array( 'methods' => 'POST', 'callback' => 'dck_pay_webhook', 'permission_callback' => '__return_true' ) );
	}
);

if ( ! function_exists( 'dck_pay_reply' ) ) {
	function dck_pay_reply( $ok, $msg, $extra = array() ) {
		return new WP_REST_Response( array_merge( array( 'ok' => $ok, 'message' => $msg ), $extra ), 200 );
	}
}

/* 1. Start: create the record and send the STK push. */
if ( ! function_exists( 'dck_pay_start' ) ) {
	function dck_pay_start( $request ) {
		if ( ! dck_pay_ready() ) {
			return dck_pay_reply( false, __( 'Instant M-Pesa giving is not available right now. Please use the Paybill details below.', 'dck-elite' ) );
		}
		$p = (array) $request->get_json_params();
		$g = dck_sec_guard( $request, $p, 'paystart', 2000, 5, 900 );
		if ( true !== $g ) {
			return $g;
		}
		$amount  = (int) round( (float) preg_replace( '/[^0-9.]/', '', (string) ( isset( $p['amount'] ) ? $p['amount'] : '' ) ) );
		$name    = dck_sec_cap( sanitize_text_field( isset( $p['name'] ) ? $p['name'] : '' ), 120 );
		$email   = dck_sec_cap( sanitize_email( isset( $p['email'] ) ? $p['email'] : '' ), 190 );
		$purpose = sanitize_text_field( isset( $p['purpose'] ) ? $p['purpose'] : '' );
		$phone   = dck_ct_wa_number( isset( $p['phone'] ) ? $p['phone'] : '' );
		$min     = max( 10, (int) dck_home_val( 'dck_pay_min', 10 ) );
		if ( $amount < $min || $amount > 150000 ) {
			return dck_pay_reply( false, sprintf( __( 'Please enter an amount between KES %1$s and KES 150,000 (the M-Pesa limit).', 'dck-elite' ), number_format_i18n( $min ) ) );
		}
		if ( ! preg_match( '/^254(7|1)\d{8}$/', $phone ) ) {
			return dck_pay_reply( false, __( 'Please enter a Kenyan M-Pesa number, e.g. 0712 345 678.', 'dck-elite' ) );
		}
		if ( ! in_array( $purpose, dck_gift_purposes(), true ) ) {
			$purpose = __( 'Offerings', 'dck-elite' );
		}
		if ( '' === $name ) {
			$name = __( 'Anonymous giver', 'dck-elite' );
		}
		if ( $email && ! is_email( $email ) ) {
			$email = '';
		}
		$ref = 'DCK-' . strtoupper( wp_generate_password( 10, false, false ) );
		$id  = wp_insert_post( array( 'post_type' => 'dck_gift', 'post_status' => 'private', 'post_title' => sprintf( '%s - %s - %s', $name, dck_ct_money( $amount, 'KES' ), $purpose ) ) );
		if ( ! $id || is_wp_error( $id ) ) {
			return dck_pay_reply( false, __( 'We could not start the payment. Please try again.', 'dck-elite' ) );
		}
		foreach ( array( 'name' => $name, 'phone' => '+' . $phone, 'email' => $email, 'purpose' => $purpose, 'amount' => $amount, 'currency' => 'KES', 'code' => $ref, 'channel' => 'paystack', 'status' => 'pending', 'note' => 'Waiting for M-Pesa PIN' ) as $k => $v ) {
			update_post_meta( $id, '_dck_gift_' . $k, $v );
		}
		$res = dck_pay_api(
			'POST',
			'/charge',
			array(
				'email'        => $email ? $email : $phone . '@' . wp_parse_url( home_url(), PHP_URL_HOST ),
				'amount'       => $amount * 100,
				'currency'     => 'KES',
				'reference'    => $ref,
				'mobile_money' => array( 'phone' => '+' . $phone, 'provider' => 'mpesa' ),
				'metadata'     => array( 'purpose' => $purpose, 'name' => $name, 'gift_id' => $id, 'custom_fields' => array( array( 'display_name' => 'Purpose', 'variable_name' => 'purpose', 'value' => $purpose ) ) ),
			)
		);
		if ( is_wp_error( $res ) || empty( $res['status'] ) ) {
			update_post_meta( $id, '_dck_gift_status', 'rejected' );
			update_post_meta( $id, '_dck_gift_note', 'Paystack error: ' . ( is_wp_error( $res ) ? $res->get_error_message() : ( isset( $res['message'] ) ? sanitize_text_field( $res['message'] ) : '' ) ) );
			return dck_pay_reply( false, __( 'M-Pesa could not be reached just now. Please try again in a minute or use the Paybill details below.', 'dck-elite' ) );
		}
		$st = isset( $res['data']['status'] ) ? $res['data']['status'] : '';
		if ( 'success' === $st ) {
			dck_pay_mark_paid( $id, $res['data'] );
		}
		return dck_pay_reply(
			true,
			isset( $res['data']['display_text'] ) ? sanitize_text_field( $res['data']['display_text'] ) : __( 'Check your phone and enter your M-Pesa PIN to complete.', 'dck-elite' ),
			array( 'reference' => $ref, 'status' => $st )
		);
	}
}

/* 1b. Some phones get an OTP first. */
if ( ! function_exists( 'dck_pay_otp' ) ) {
	function dck_pay_otp( $request ) {
		$p   = (array) $request->get_json_params();
		$g   = dck_sec_guard( $request, $p, 'payotp', 0, 8, 900 );
		$ref = isset( $p['reference'] ) ? preg_replace( '/[^A-Z0-9-]/', '', strtoupper( (string) $p['reference'] ) ) : '';
		$otp = isset( $p['otp'] ) ? preg_replace( '/\D/', '', (string) $p['otp'] ) : '';
		if ( true !== $g ) {
			return $g;
		}
		if ( ! dck_pay_find( $ref ) || '' === $otp ) {
			return dck_pay_reply( false, __( 'Please enter the code sent to your phone.', 'dck-elite' ) );
		}
		$res = dck_pay_api( 'POST', '/charge/submit_otp', array( 'otp' => $otp, 'reference' => $ref ) );
		if ( is_wp_error( $res ) || empty( $res['status'] ) ) {
			return dck_pay_reply( false, __( 'That code did not work. Please check and try again.', 'dck-elite' ) );
		}
		return dck_pay_reply( true, isset( $res['data']['display_text'] ) ? sanitize_text_field( $res['data']['display_text'] ) : __( 'Now enter your M-Pesa PIN on your phone.', 'dck-elite' ), array( 'status' => isset( $res['data']['status'] ) ? $res['data']['status'] : '' ) );
	}
}

/* 2. Status (page polls every few seconds; verifies with Paystack at most every 8s). */
if ( ! function_exists( 'dck_pay_status' ) ) {
	function dck_pay_status( $request ) {
		$p   = (array) $request->get_json_params();
		$ref = isset( $p['reference'] ) ? preg_replace( '/[^A-Z0-9-]/', '', strtoupper( (string) $p['reference'] ) ) : '';
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! $nonce || ! wp_verify_nonce( sanitize_text_field( (string) $nonce ), 'wp_rest' ) ) {
			return dck_pay_reply( false, 'expired', array( 'state' => 'unknown' ) );
		}
		$id = dck_pay_find( $ref );
		if ( ! $id || 'paystack' !== get_post_meta( $id, '_dck_gift_channel', true ) ) {
			return dck_pay_reply( false, '', array( 'state' => 'unknown' ) );
		}
		$st = get_post_meta( $id, '_dck_gift_status', true );
		if ( 'pending' === $st ) {
			$last = (int) get_transient( 'dck_pay_chk_' . $ref );
			if ( time() - $last >= 8 ) {
				set_transient( 'dck_pay_chk_' . $ref, time(), 300 );
				$res = dck_pay_api( 'GET', '/transaction/verify/' . rawurlencode( $ref ) );
				$tx  = ( ! is_wp_error( $res ) && ! empty( $res['data'] ) ) ? $res['data'] : array();
				$ts  = isset( $tx['status'] ) ? $tx['status'] : '';
				if ( 'success' === $ts ) {
					dck_pay_mark_paid( $id, $tx );
				} elseif ( in_array( $ts, array( 'failed', 'abandoned', 'reversed' ), true ) ) {
					update_post_meta( $id, '_dck_gift_status', 'rejected' );
					update_post_meta( $id, '_dck_gift_note', 'Paystack: ' . $ts . ( ! empty( $tx['gateway_response'] ) ? ' - ' . sanitize_text_field( $tx['gateway_response'] ) : '' ) );
				}
				$st = get_post_meta( $id, '_dck_gift_status', true );
			}
		}
		$state = 'verified' === $st ? 'paid' : ( 'rejected' === $st ? 'failed' : 'pending' );
		return dck_pay_reply( true, '', array( 'state' => $state ) );
	}
}

/* 3. Webhook from Paystack (signature-checked). */
if ( ! function_exists( 'dck_pay_webhook' ) ) {
	function dck_pay_webhook( $request ) {
		$raw = (string) $request->get_body();
		$sig = (string) $request->get_header( 'x-paystack-signature' );
		if ( ! dck_pay_ready() || '' === $sig || ! hash_equals( hash_hmac( 'sha512', $raw, dck_pay_secret() ), $sig ) ) {
			return new WP_REST_Response( array( 'ok' => false ), 401 );
		}
		$ev = json_decode( $raw, true );
		if ( is_array( $ev ) && 'charge.success' === ( isset( $ev['event'] ) ? $ev['event'] : '' ) && ! empty( $ev['data']['reference'] ) ) {
			$ref = preg_replace( '/[^A-Za-z0-9-]/', '', (string) $ev['data']['reference'] );
			$id  = dck_pay_find( $ref );
			if ( $id ) {
				// Re-verify with Paystack before trusting the payload.
				$res = dck_pay_api( 'GET', '/transaction/verify/' . rawurlencode( $ref ) );
				if ( ! is_wp_error( $res ) && isset( $res['data']['status'] ) && 'success' === $res['data']['status'] ) {
					dck_pay_mark_paid( $id, $res['data'] );
				}
			}
		}
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}
}

/* Front-end: the instant M-Pesa card at the top of the Give page. */
if ( ! function_exists( 'dck_pay_form' ) ) {
	function dck_pay_form() {
		if ( ! dck_pay_ready() ) {
			return;
		}
		wp_enqueue_script( 'dck-paystack', get_template_directory_uri() . '/assets/js/paystack.js', array(), DCK_ELITE_VERSION, true );
		wp_localize_script(
			'dck-paystack',
			'DCK_PAY',
			array(
				'start'  => rest_url( 'dck/v1/pay/start' ),
				'otp'    => rest_url( 'dck/v1/pay/otp' ),
				'status' => rest_url( 'dck/v1/pay/status' ),
				'nonce'  => wp_create_nonce( 'wp_rest' ),
			)
		);
		$amounts = array_filter( array_map( 'absint', explode( ',', (string) dck_home_val( 'dck_ct_quick_amounts', '500, 1000, 2000, 5000, 10000' ) ) ) );
		?>
		<section class="dck-section dck-pay" id="give-mpesa">
			<div class="dck-container">
				<div class="dck-pay__card">
					<div class="dck-pay__side">
						<span class="dck-pay__badge"><?php esc_html_e( 'Fastest way to give', 'dck-elite' ); ?></span>
						<h2><?php echo esc_html( dck_home_val( 'dck_pay_title', __( 'Give instantly with M-Pesa', 'dck-elite' ) ) ); ?></h2>
						<p><?php echo esc_html( dck_home_val( 'dck_pay_text', __( 'Enter the amount and your M-Pesa number. A prompt will pop up on your phone - just enter your PIN. Your gift is recorded and receipted automatically.', 'dck-elite' ) ) ); ?></p>
						<ol class="dck-pay__steps">
							<li><?php esc_html_e( 'Choose what your gift is for', 'dck-elite' ); ?></li>
							<li><?php esc_html_e( 'Enter amount and M-Pesa number', 'dck-elite' ); ?></li>
							<li><?php esc_html_e( 'Enter your PIN on the phone prompt', 'dck-elite' ); ?></li>
						</ol>
						<p class="dck-pay__secure"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg><?php esc_html_e( 'Secured by Paystack. We never see your PIN.', 'dck-elite' ); ?><?php echo dck_pay_test_mode() ? ' <strong>(TEST MODE)</strong>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					</div>
					<form class="dck-pay__form" novalidate data-dck-pay>
						<div class="dck-cform__field">
							<span><?php esc_html_e( 'Give towards', 'dck-elite' ); ?></span>
							<div class="dck-pay__purposes" role="radiogroup">
								<?php $first = true; foreach ( dck_gift_purposes() as $pp ) : ?>
									<label class="dck-pay__chip"><input type="radio" name="purpose" value="<?php echo esc_attr( $pp ); ?>"<?php checked( $first ); ?>><span><?php echo esc_html( $pp ); ?></span></label>
								<?php $first = false; endforeach; ?>
							</div>
						</div>
						<label class="dck-cform__field"><span><?php esc_html_e( 'Amount', 'dck-elite' ); ?> <em class="dck-req">*</em></span>
							<span class="dck-pay__amount"><b>KES</b><input type="number" name="amount" min="10" max="150000" step="1" inputmode="numeric" required placeholder="0"></span>
						</label>
						<div class="dck-amounts dck-pay__quick"><?php foreach ( $amounts as $amt ) : ?><button type="button" class="dck-chip" data-pay-amt="<?php echo esc_attr( $amt ); ?>"><?php echo esc_html( number_format_i18n( $amt ) ); ?></button><?php endforeach; ?></div>
						<label class="dck-cform__field"><span><?php esc_html_e( 'M-Pesa phone number', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="tel" name="phone" autocomplete="tel" inputmode="tel" required placeholder="0712 345 678"></label>
						<div class="dck-cform__row">
							<label class="dck-cform__field"><span><?php esc_html_e( 'Your name', 'dck-elite' ); ?> <small class="dck-opt"><?php esc_html_e( 'Optional', 'dck-elite' ); ?></small></span><input type="text" name="name" autocomplete="name"></label>
							<label class="dck-cform__field"><span><?php esc_html_e( 'Email for receipt', 'dck-elite' ); ?> <small class="dck-opt"><?php esc_html_e( 'Optional', 'dck-elite' ); ?></small></span><input type="email" name="email" autocomplete="email"></label>
						</div>
						<div class="dck-hp" aria-hidden="true"><label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
						<button type="submit" class="dck-btn dck-pay__btn"><span data-pay-label><?php esc_html_e( 'Send M-Pesa prompt', 'dck-elite' ); ?></span></button>
						<p class="dck-pay__msg" role="status" aria-live="polite"></p>
					</form>
					<div class="dck-pay__wait" data-pay-wait hidden>
						<div class="dck-pay__phone" aria-hidden="true"><span></span></div>
						<h3 data-pay-wait-title><?php esc_html_e( 'Check your phone', 'dck-elite' ); ?></h3>
						<p data-pay-wait-text><?php esc_html_e( 'An M-Pesa prompt has been sent. Enter your PIN to complete your gift.', 'dck-elite' ); ?></p>
						<form class="dck-pay__otp" data-pay-otp hidden><label class="dck-cform__field"><span><?php esc_html_e( 'Code sent to your phone', 'dck-elite' ); ?></span><input type="text" name="otp" inputmode="numeric" autocomplete="one-time-code" maxlength="8"></label><button type="submit" class="dck-btn dck-btn--primary"><?php esc_html_e( 'Continue', 'dck-elite' ); ?></button></form>
						<p class="dck-pay__timer"><span data-pay-timer>3:00</span> <?php esc_html_e( 'remaining', 'dck-elite' ); ?></p>
						<button type="button" class="dck-pay__link" data-pay-retry hidden><?php esc_html_e( 'Try again', 'dck-elite' ); ?></button>
					</div>
					<div class="dck-pay__done" data-pay-done hidden>
						<div class="dck-pay__tick" aria-hidden="true"></div>
						<h3><?php esc_html_e( 'Thank you! Your gift was received.', 'dck-elite' ); ?></h3>
						<p data-pay-done-text></p>
						<p class="dck-pay__ref"><?php esc_html_e( 'Reference:', 'dck-elite' ); ?> <strong data-pay-ref></strong></p>
						<button type="button" class="dck-btn dck-btn--outline" data-pay-again><?php esc_html_e( 'Give again', 'dck-elite' ); ?></button>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}
