<?php
/**
 * Two-factor login for staff.
 *
 * Uses the official "Two Factor" plugin (wordpress.org/plugins/two-factor, maintained by
 * WordPress contributors) for the codes themselves - we do not roll our own crypto.
 * This file: styles its code screen, nudges admins to install it, and makes staff
 * (anyone who can edit posts) switch it on before using wp-admin.
 * Turn enforcement off with define( 'DCK_REQUIRE_2FA', false ); in wp-config.php.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_2fa_available' ) ) {
	function dck_2fa_available() {
		return class_exists( 'Two_Factor_Core' );
	}
}

if ( ! function_exists( 'dck_2fa_user_enabled' ) ) {
	function dck_2fa_user_enabled( $user_id ) {
		return dck_2fa_available() && Two_Factor_Core::is_user_using_two_factor( $user_id );
	}
}

if ( ! function_exists( 'dck_2fa_required' ) ) {
	function dck_2fa_required() {
		return ! defined( 'DCK_REQUIRE_2FA' ) || DCK_REQUIRE_2FA;
	}
}

/* Plugin missing: tell administrators how to get it. */
add_action(
	'admin_notices',
	function () {
		if ( dck_2fa_available() || ! current_user_can( 'install_plugins' ) ) {
			return;
		}
		$url = admin_url( 'plugin-install.php?s=two-factor&tab=search&type=term' );
		echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'Protect staff logins with two-factor:', 'dck-elite' ) . '</strong> ' .
			esc_html__( 'install and activate the free "Two Factor" plugin (by WordPress.org contributors). The DCK theme will then ask every staff member to set it up.', 'dck-elite' ) .
			' <a href="' . esc_url( $url ) . '">' . esc_html__( 'Install Two Factor', 'dck-elite' ) . '</a></p></div>';
	}
);

/* Plugin active, staff member has not set it up: send them to their profile. */
add_action(
	'admin_init',
	function () {
		if ( ! dck_2fa_available() || ! dck_2fa_required() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		$user = wp_get_current_user();
		if ( ! $user->exists() || ! user_can( $user, 'edit_posts' ) || dck_2fa_user_enabled( $user->ID ) ) {
			return;
		}
		global $pagenow;
		if ( in_array( $pagenow, array( 'profile.php', 'admin-post.php' ), true ) ) {
			return;
		}
		wp_safe_redirect( admin_url( 'profile.php?dck_2fa=1#two-factor-options' ) );
		exit;
	}
);

add_action(
	'admin_notices',
	function () {
		if ( ! dck_2fa_available() ) {
			return;
		}
		$user = wp_get_current_user();
		if ( ! user_can( $user, 'edit_posts' ) || dck_2fa_user_enabled( $user->ID ) ) {
			return;
		}
		$must = dck_2fa_required();
		echo '<div class="notice ' . ( $must ? 'notice-error' : 'notice-warning' ) . '"><p><strong>' . esc_html__( 'Set up two-factor login', 'dck-elite' ) . '</strong><br>' .
			esc_html__( '1. Under "Two Factor Options" below, tick "Enable Authenticator App" and scan the QR code with Google Authenticator, Microsoft Authenticator or Authy, then enter the 6-digit code.', 'dck-elite' ) . '<br>' .
			esc_html__( '2. Tick "Recovery Codes", generate them and keep them somewhere safe (they get you in if you lose your phone).', 'dck-elite' ) . '<br>' .
			esc_html__( '3. Set Primary Method to "Authenticator App" and click Update Profile.', 'dck-elite' ) .
			( $must ? '<br><em>' . esc_html__( 'The rest of the dashboard opens once this is done.', 'dck-elite' ) . '</em>' : '' ) .
			'</p></div>';
	}
);
