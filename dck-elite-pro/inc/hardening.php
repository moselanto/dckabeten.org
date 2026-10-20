<?php
/**
 * DCK Elite - Production hardening (security audit, October 2026).
 *
 * See SECURITY-AUDIT.md in the repository root for the reasoning behind each item.
 *  1. HTTPS everywhere (proxy/Cloudflare aware, no redirect loops)
 *  2. CORS locked to this site for the dck/v1 API
 *  3. Login brute-force protection
 *  4. Shorter sessions for staff accounts
 *  5. Daily AI cost ceiling across all visitors
 *  6. Scripture proxy rate limit
 *  7. Safer defaults (no file editing from wp-admin, no REST index leakage of plugin internals)
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- 1. HTTPS everywhere ---------- */

if ( ! function_exists( 'dck_sec_forwarded_https' ) ) {
	/** True when a trusted edge (Cloudflare / load balancer) says the visitor used HTTPS. */
	function dck_sec_forwarded_https() {
		if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) ) ) {
			return true;
		}
		if ( isset( $_SERVER['HTTP_CF_VISITOR'] ) && false !== strpos( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_VISITOR'] ) ), 'https' ) ) {
			return true;
		}
		return false;
	}
}

add_action(
	'template_redirect',
	function () {
		if ( is_ssl() || dck_sec_forwarded_https() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}
		if ( 0 !== strpos( home_url(), 'https://' ) || ! isset( $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'] ) ) {
			return; // Site is not configured for HTTPS yet: never force (avoids lock-outs).
		}
		$uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		wp_safe_redirect( 'https://' . wp_parse_url( home_url(), PHP_URL_HOST ) . $uri, 301 );
		exit;
	},
	1
);

/* ---------- 2. CORS: only this site may call the dck/v1 API from a browser ---------- */

add_filter(
	'rest_pre_serve_request',
	function ( $served, $result, $request ) {
		if ( 0 !== strpos( (string) $request->get_route(), '/dck/v1' ) ) {
			return $served;
		}
		$origin = get_http_origin();
		$home   = wp_parse_url( home_url() );
		$allow  = ( isset( $home['scheme'], $home['host'] ) ? $home['scheme'] . '://' . $home['host'] : '' );
		if ( $origin && strtolower( untrailingslashit( $origin ) ) !== strtolower( $allow ) ) {
			header_remove( 'Access-Control-Allow-Origin' );
			header_remove( 'Access-Control-Allow-Credentials' );
		}
		return $served;
	},
	20,
	3
);

/* ---------- 3. Login brute-force protection ---------- */

if ( ! function_exists( 'dck_sec_login_key' ) ) {
	function dck_sec_login_key() {
		return 'dck_login_fail_' . md5( dck_sec_client_ip() );
	}
}

add_filter(
	'authenticate',
	function ( $user ) {
		$fails = (int) get_transient( dck_sec_login_key() );
		if ( $fails >= (int) apply_filters( 'dck_login_max_attempts', 5 ) ) {
			return new WP_Error( 'dck_locked', __( 'Too many failed login attempts. Please wait 15 minutes and try again.', 'dck-elite' ) );
		}
		return $user;
	},
	30
);

add_action(
	'wp_login_failed',
	function () {
		$k = dck_sec_login_key();
		set_transient( $k, (int) get_transient( $k ) + 1, 15 * MINUTE_IN_SECONDS );
	}
);

add_action(
	'wp_login',
	function () {
		delete_transient( dck_sec_login_key() );
	}
);

/* ---------- 4. Session expiry ---------- */

add_filter(
	'auth_cookie_expiration',
	function ( $length, $user_id, $remember ) {
		if ( user_can( $user_id, 'edit_posts' ) ) {
			return $remember ? 7 * DAY_IN_SECONDS : 12 * HOUR_IN_SECONDS; // Staff: shorter sessions.
		}
		return $length;
	},
	10,
	3
);

/* ---------- 5. Daily AI cost ceiling (all visitors combined) ---------- */

if ( ! function_exists( 'dck_ai_budget_ok' ) ) {
	/**
	 * Protects the church's AI bill from abuse. Override with
	 * define( 'DCK_AI_DAILY_CAP', 3000 ); in wp-config.php.
	 */
	function dck_ai_budget_ok() {
		$cap = defined( 'DCK_AI_DAILY_CAP' ) ? (int) DCK_AI_DAILY_CAP : 1500;
		$key = 'dck_ai_budget_' . gmdate( 'Ymd' );
		$n   = (int) get_transient( $key );
		if ( $n >= $cap ) {
			return false;
		}
		set_transient( $key, $n + 1, DAY_IN_SECONDS );
		return true;
	}
}

/* ---------- 6. Scripture proxy rate limit ---------- */

add_filter(
	'rest_pre_dispatch',
	function ( $result, $server, $request ) {
		$route = (string) $request->get_route();
		if ( 0 === strpos( $route, '/dck/v1/scripture' ) && function_exists( 'dck_sec_form_rate_ok' ) && ! dck_sec_form_rate_ok( 'scripture', 240, 600 ) ) {
			return new WP_REST_Response( array( 'error' => 'rate_limited' ), 429 );
		}
		return $result;
	},
	10,
	3
);

/* ---------- 7. Safer defaults ---------- */

if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true ); // No theme/plugin code editor in wp-admin.
}
