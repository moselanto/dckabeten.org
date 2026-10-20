<?php
/**
 * Shared helpers. Values are stored as theme mods with the same keys the
 * DCK Elite Pro theme already uses, so the Customizer and the Church Settings
 * screen always show the same data and nothing needs migrating.
 *
 * @package DCK_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_core_get' ) ) {
	/**
	 * Read a church setting with a fallback.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Fallback.
	 * @return mixed
	 */
	function dck_core_get( $key, $default = '' ) {
		$v = get_theme_mod( $key, $default );
		return ( '' === $v || null === $v ) ? $default : $v;
	}
}

if ( ! function_exists( 'dck_core_is_active' ) ) {
	function dck_core_is_active() {
		return true;
	}
}
