<?php
/**
 * DCK Elite - Front-end performance helpers.
 * Defers theme JS, adds font resource hints, trims emoji cruft.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Defer theme scripts so they never block first paint (skip admin + jQuery). */
add_filter( 'script_loader_tag', 'dck_defer_scripts', 10, 2 );
if ( ! function_exists( 'dck_defer_scripts' ) ) {
	function dck_defer_scripts( $tag, $handle ) {
		if ( is_admin() ) {
			return $tag;
		}
		if ( strpos( (string) $handle, 'dck' ) === false ) {
			return $tag;
		}
		if ( strpos( $tag, ' defer' ) !== false || strpos( $tag, ' async' ) !== false ) {
			return $tag;
		}
		return str_replace( ' src', ' defer src', $tag );
	}
}

/* Preconnect to Google Fonts hosts to speed up font delivery. */
add_filter( 'wp_resource_hints', 'dck_resource_hints', 10, 2 );
if ( ! function_exists( 'dck_resource_hints' ) ) {
	function dck_resource_hints( $hints, $relation ) {
		if ( 'preconnect' === $relation ) {
		}
		return $hints;
	}
}

/* Remove the emoji detection script/styles (unused weight on every page). */
add_action( 'init', 'dck_trim_head' );
if ( ! function_exists( 'dck_trim_head' ) ) {
	function dck_trim_head() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
	}
}

/* Preload the self-hosted fonts used above the fold (faster first paint, less font-swap CLS). */
add_action( 'wp_head', 'dck_preload_fonts', 1 );
if ( ! function_exists( 'dck_preload_fonts' ) ) {
	function dck_preload_fonts() {
		$b = get_template_directory_uri();
		echo '<link rel="preload" href="' . esc_url( $b . '/assets/fonts/playfair-700.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
		echo '<link rel="preload" href="' . esc_url( $b . '/assets/fonts/inter-400.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	}
}
