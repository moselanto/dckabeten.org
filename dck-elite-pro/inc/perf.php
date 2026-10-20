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

/*
 * Serve minified copies (*.min.js / style.min.css) built by tools/build-assets.sh.
 * assets/min-manifest.json records each source file's size at build time; a minified copy is
 * only used while its source is unchanged, so editing a CSS/JS file without rebuilding can
 * never serve stale code. SCRIPT_DEBUG true in wp-config.php always loads the originals.
 */
if ( ! function_exists( 'dck_min_manifest' ) ) {
	function dck_min_manifest() {
		static $m = null;
		if ( null === $m ) {
			$f = get_template_directory() . '/assets/min-manifest.json';
			$m = is_readable( $f ) ? json_decode( (string) file_get_contents( $f ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$m = is_array( $m ) ? $m : array();
		}
		return $m;
	}
}
if ( ! function_exists( 'dck_min_src' ) ) {
	function dck_min_src( $src ) {
		if ( is_admin() || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ! is_string( $src ) ) {
			return $src;
		}
		$base = get_template_directory_uri();
		if ( 0 !== strpos( $src, $base ) || false !== strpos( $src, '.min.' ) ) {
			return $src;
		}
		$parts = explode( '?', $src, 2 );
		$rel   = ltrim( substr( $parts[0], strlen( $base ) ), '/' );
		$man   = dck_min_manifest();
		if ( ! isset( $man[ $rel ] ) ) {
			return $src;
		}
		$dir     = get_template_directory() . '/';
		$min_rel = preg_replace( '/\.(js|css)$/', '.min.$1', $rel );
		if ( is_readable( $dir . $min_rel ) && (int) @filesize( $dir . $rel ) === (int) $man[ $rel ] ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
			return $base . '/' . $min_rel . ( isset( $parts[1] ) ? '?' . $parts[1] : '' );
		}
		return $src;
	}
}
add_filter( 'script_loader_src', 'dck_min_src', 20 );
add_filter( 'style_loader_src', 'dck_min_src', 20 );

/* Remove WordPress extras the theme does not use (smaller pages, fewer requests). */
add_action(
	'init',
	function () {
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		add_filter( 'emoji_svg_url', '__return_false' );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		/* wp-embed lets others embed our posts; not needed on visitors' pages. */
		wp_deregister_script( 'wp-embed' );
		/* Front-end heartbeat polling is only useful while editing. */
		if ( ! is_user_logged_in() ) {
			wp_deregister_script( 'heartbeat' );
		}
		/* Block editor styles: only on posts/pages whose content actually uses blocks. */
		$uses_blocks = ( is_singular() && has_blocks( get_post() ) ) || is_active_sidebar( 'sidebar-1' ) || is_active_sidebar( 'footer-1' );
		if ( ! $uses_blocks ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
			wp_dequeue_style( 'classic-theme-styles' );
			wp_dequeue_style( 'global-styles' );
		}
	},
	100
);


/* Let the browser start loading the main stylesheet and logo as early as possible. */
add_action(
	'wp_head',
	function () {
		echo '<link rel="preload" href="' . esc_url( get_template_directory_uri() . '/assets/images/logo.webp' ) . '" as="image" type="image/webp">' . "\n";
	},
	2
);
