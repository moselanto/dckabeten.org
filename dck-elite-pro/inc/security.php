<?php
/**
 * DCK Elite - Security hardening.
 *
 * Safe additions only: removes information leakage, disables comments,
 * blocks user enumeration, adds HTTP security headers via the correct
 * wp_headers filter, and provides a shared form guard used by REST endpoints.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   1. HTTP security headers (wp_headers filter — correct WP way)
   ============================================================ */
add_filter( 'wp_headers', 'dck_sec_http_headers' );

if ( ! function_exists( 'dck_sec_http_headers' ) ) {
	function dck_sec_http_headers( $headers ) {
		$headers['X-Frame-Options']           = 'SAMEORIGIN';
		$headers['X-Content-Type-Options']    = 'nosniff';
		$headers['Referrer-Policy']           = 'strict-origin-when-cross-origin';
		$headers['X-XSS-Protection']          = '0'; // Legacy filter disabled (modern guidance); CSP below replaces it.
		$headers['Permissions-Policy']        = 'camera=(), geolocation=(), payment=(), usb=(), microphone=(self)';
		$headers['Cross-Origin-Opener-Policy'] = 'same-origin-allow-popups';
		/* Safe CSP baseline: blocks clickjacking, plugins and <base> hijacking without breaking embeds/inline scripts. */
		$headers['Content-Security-Policy']   = "frame-ancestors 'self'; base-uri 'self'; object-src 'none'; upgrade-insecure-requests";
		if ( is_ssl() || dck_sec_forwarded_https() ) {
			$headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
		}
		unset( $headers['X-Pingback'] );
		return $headers;
	}
}

/* ============================================================
   2. Comments — disabled everywhere
   ============================================================ */
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open',    '__return_false', 20 );

add_action( 'init', 'dck_sec_disable_comments', 100 );

if ( ! function_exists( 'dck_sec_disable_comments' ) ) {
	function dck_sec_disable_comments() {
		foreach ( get_post_types() as $pt ) {
			if ( post_type_supports( $pt, 'comments' ) ) {
				remove_post_type_support( $pt, 'comments' );
				remove_post_type_support( $pt, 'trackbacks' );
			}
		}
	}
}

add_action( 'template_redirect', 'dck_sec_redirect_comments' );

if ( ! function_exists( 'dck_sec_redirect_comments' ) ) {
	function dck_sec_redirect_comments() {
		if ( is_comment_feed() ) {
			wp_die( esc_html__( 'Comments are closed.', 'dck-elite' ), '', array( 'response' => 403 ) );
		}
	}
}

add_action( 'admin_menu', 'dck_sec_remove_comments_menu' );

if ( ! function_exists( 'dck_sec_remove_comments_menu' ) ) {
	function dck_sec_remove_comments_menu() {
		remove_menu_page( 'edit-comments.php' );
	}
}

/* ============================================================
   3. XML-RPC — disable pingback methods only (keep for Jetpack etc.)
   ============================================================ */
add_filter( 'xmlrpc_methods', 'dck_sec_xmlrpc_methods' );

if ( ! function_exists( 'dck_sec_xmlrpc_methods' ) ) {
	function dck_sec_xmlrpc_methods( $methods ) {
		unset( $methods['pingback.ping'] );
		unset( $methods['pingback.extensions.getPingbacks'] );
		return $methods;
	}
}

/* ============================================================
   4. Information leakage — hide version, generator tag
   ============================================================ */
add_filter( 'the_generator', '__return_empty_string' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

add_filter( 'login_errors', 'dck_sec_login_error' );

if ( ! function_exists( 'dck_sec_login_error' ) ) {
	function dck_sec_login_error() {
		return __( 'Incorrect login details.', 'dck-elite' );
	}
}

/* ============================================================
   5. User enumeration — block ?author=N redirect
   ============================================================ */
add_action( 'template_redirect', 'dck_sec_block_author_scan' );

if ( ! function_exists( 'dck_sec_block_author_scan' ) ) {
	function dck_sec_block_author_scan() {
		if ( is_admin() || is_user_logged_in() ) {
			return;
		}
		if ( isset( $_GET['author'] ) && preg_match( '/^\d+$/', sanitize_text_field( wp_unslash( $_GET['author'] ) ) ) ) {
			wp_die( esc_html__( 'Not permitted.', 'dck-elite' ), '', array( 'response' => 403 ) );
		}
	}
}

add_filter( 'rest_endpoints', 'dck_sec_hide_rest_users', 20 );

if ( ! function_exists( 'dck_sec_hide_rest_users' ) ) {
	function dck_sec_hide_rest_users( $endpoints ) {
		if ( is_user_logged_in() ) {
			return $endpoints;
		}
		unset( $endpoints['/wp/v2/users'] );
		unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
		return $endpoints;
	}
}

/* ============================================================
   6. Shared client IP helper (Cloudflare-aware)
   ============================================================ */
if ( ! function_exists( 'dck_sec_ip_in_cidr' ) ) {
	/** IPv4/IPv6 CIDR match. */
	function dck_sec_ip_in_cidr( $ip, $cidr ) {
		list( $net, $bits ) = array_pad( explode( '/', $cidr ), 2, null );
		$ip_b  = @inet_pton( $ip );  // phpcs:ignore WordPress.PHP.NoSilencedErrors
		$net_b = @inet_pton( $net ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		if ( false === $ip_b || false === $net_b || strlen( $ip_b ) !== strlen( $net_b ) ) {
			return false;
		}
		$bits = null === $bits ? strlen( $ip_b ) * 8 : (int) $bits;
		$full = intdiv( $bits, 8 );
		if ( substr( $ip_b, 0, $full ) !== substr( $net_b, 0, $full ) ) {
			return false;
		}
		$rem = $bits % 8;
		if ( 0 === $rem ) {
			return true;
		}
		$mask = chr( ( 0xff << ( 8 - $rem ) ) & 0xff );
		return ( $ip_b[ $full ] & $mask ) === ( $net_b[ $full ] & $mask );
	}
}

if ( ! function_exists( 'dck_sec_is_cloudflare' ) ) {
	/** True when the direct peer is a Cloudflare edge server (published ranges). */
	function dck_sec_is_cloudflare( $ip ) {
		$ranges = array( '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13', '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22', '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32' );
		foreach ( $ranges as $r ) {
			if ( dck_sec_ip_in_cidr( $ip, $r ) ) {
				return true;
			}
		}
		return false;
	}
}

if ( ! function_exists( 'dck_sec_client_ip' ) ) {
	/**
	 * Visitor IP for rate limiting. Forwarding headers are trusted ONLY when the
	 * request really comes through Cloudflare (or a proxy declared with the
	 * DCK_TRUSTED_PROXY constant), so attackers cannot spoof their IP to bypass limits.
	 */
	function dck_sec_client_ip() {
		$remote = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		if ( ! filter_var( $remote, FILTER_VALIDATE_IP ) ) {
			return '0.0.0.0';
		}
		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) && dck_sec_is_cloudflare( $remote ) ) {
			$cf = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
			if ( filter_var( $cf, FILTER_VALIDATE_IP ) ) {
				return $cf;
			}
		}
		if ( defined( 'DCK_TRUSTED_PROXY' ) && DCK_TRUSTED_PROXY && $remote === DCK_TRUSTED_PROXY && isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$xff = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$ip  = trim( $xff[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
		return $remote;
	}
}

/* ============================================================
   7. Rate limiting helper
   ============================================================ */
if ( ! function_exists( 'dck_sec_form_rate_ok' ) ) {
	function dck_sec_form_rate_ok( $bucket, $limit = 6, $window = 900 ) {
		$key = 'dck_rl_' . preg_replace( '/[^a-z0-9]/', '', strtolower( (string) $bucket ) ) . '_' . md5( dck_sec_client_ip() );
		$n   = (int) get_transient( $key );
		if ( $n >= (int) $limit ) {
			return false;
		}
		set_transient( $key, $n + 1, (int) $window );
		return true;
	}
}

/* ============================================================
   8. Spam content check
   ============================================================ */
if ( ! function_exists( 'dck_sec_looks_spammy' ) ) {
	function dck_sec_looks_spammy( $text ) {
		$t = strtolower( wp_strip_all_tags( (string) $text ) );
		if ( preg_match_all( '#https?://#i', $t ) >= 2 ) {
			return true;
		}
		$bad = array( 'viagra', 'cialis', 'casino', 'crypto', 'bitcoin', 'porn', 'escort', 'forex', 'backlink', 'seo service', 'buy followers' );
		foreach ( $bad as $kw ) {
			if ( false !== strpos( $t, $kw ) ) {
				return true;
			}
		}
		return false;
	}
}

/* ============================================================
   9. Disposable email check
   ============================================================ */
if ( ! function_exists( 'dck_sec_email_disposable' ) ) {
	function dck_sec_email_disposable( $email ) {
		$at = strrpos( strtolower( (string) $email ), '@' );
		if ( false === $at ) {
			return false;
		}
		$domain = substr( strtolower( (string) $email ), $at + 1 );
		$bad    = array( 'mailinator.com', 'guerrillamail.com', '10minutemail.com', 'tempmail.com', 'temp-mail.org', 'yopmail.com', 'trashmail.com', 'sharklasers.com', 'getnada.com', 'maildrop.cc' );
		return in_array( $domain, $bad, true );
	}
}

/* ============================================================
   10. String cap helper
   ============================================================ */
if ( ! function_exists( 'dck_sec_cap' ) ) {
	function dck_sec_cap( $str, $max ) {
		return substr( (string) $str, 0, (int) $max );
	}
}

/* ============================================================
   11. Signature content check (for opt-out form)
   ============================================================ */
if ( ! function_exists( 'dck_sec_signature_ok' ) ) {
	function dck_sec_signature_ok( $data_url ) {
		if ( '' === $data_url ) {
			return false;
		}
		if ( 0 !== strpos( $data_url, 'data:image/png;base64,' ) ) {
			return false;
		}
		if ( strlen( $data_url ) > 700000 ) {
			return false;
		}
		$raw = base64_decode( substr( $data_url, strlen( 'data:image/png;base64,' ) ), true );
		if ( false === $raw ) {
			return false;
		}
		return strlen( $raw ) > 300;
	}
}

/* ============================================================
   12. Unified form guard
   Returns true on pass, WP_REST_Response on reject.
   ============================================================ */
if ( ! function_exists( 'dck_sec_guard' ) ) {
	function dck_sec_guard( $request, $p, $bucket, $min_ms = 3000, $rate_limit = 6, $rate_window = 900 ) {
		/* 1. Nonce */
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) || ! wp_verify_nonce( sanitize_text_field( (string) $nonce ), 'wp_rest' ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => 'Your session has expired. Please refresh the page and try again.' ), 200 );
		}
		/* 2. Honeypot */
		$hp = isset( $p['website'] ) ? trim( (string) $p['website'] ) : '';
		if ( '' !== $hp ) {
			return new WP_REST_Response( array( 'ok' => true, 'message' => 'Thank you. Your submission has been received.' ), 200 );
		}
		/* 3. Time trap */
		$elapsed = isset( $p['elapsed'] ) ? (int) $p['elapsed'] : 999999;
		if ( $elapsed < (int) $min_ms ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => 'That was too quick. Please take a moment and try again.' ), 200 );
		}
		/* 4. Rate limit */
		if ( ! dck_sec_form_rate_ok( $bucket, $rate_limit, $rate_window ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => 'Too many submissions. Please wait a few minutes and try again.' ), 200 );
		}
		return true;
	}
}

/* ============================================================
   13. Block PHP execution in uploads (runs once on admin_init)
   ============================================================ */
add_action( 'admin_init', 'dck_sec_harden_uploads' );

if ( ! function_exists( 'dck_sec_harden_uploads' ) ) {
	function dck_sec_harden_uploads() {
		if ( get_transient( 'dck_sec_hardened' ) ) {
			return;
		}
		set_transient( 'dck_sec_hardened', 1, 12 * HOUR_IN_SECONDS );
		$up = wp_upload_dir();
		if ( empty( $up['basedir'] ) || ! is_dir( $up['basedir'] ) ) {
			return;
		}
		$htaccess = $up['basedir'] . '/.htaccess';
		$rule     = "# DCK Elite — block script execution\n<FilesMatch \"\.(php|phtml|php3|php4|php5|php7|php8|phar|pl|py|cgi|asp|sh)$\">\n  Require all denied\n</FilesMatch>\n<IfModule mod_authz_core.c>\n  <FilesMatch \"\.(php|phtml|php3|php4|php5|php7|php8|phar|pl|py|cgi|asp|sh)$\">\n    Order deny,allow\n    Deny from all\n  </FilesMatch>\n</IfModule>";
		$cur      = file_exists( $htaccess ) ? (string) file_get_contents( $htaccess ) : '';
		if ( false === strpos( $cur, 'DCK Elite' ) ) {
			file_put_contents( $htaccess, $rule . "\n" . $cur );
		}
	}
}
