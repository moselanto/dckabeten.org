<?php
/**
 * 4.34.0 - Internal links and script loading.
 * - Adds a "Planning a visit?" box with links to key pages and local guides at the end of every blog post.
 * - Loads the theme's front-end scripts with defer so pages paint sooner on mobile.
 *
 * @package DCK_Elite_Pro
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_post_start_here_links' ) ) {
	function dck_post_start_here_links() {
		$links = array(
			'plan-your-visit'                                  => 'Plan your visit',
			'sunday-service-in-uthiru-what-to-expect'          => 'Sunday service in Uthiru: what to expect',
			'directions-to-deliverance-church-kabete-n-market' => 'Directions to the church',
			'what-is-a-kesha-overnight-prayer'                 => 'What is a Kesha? Overnight prayer',
			'church-near-upper-kabete-campus-students'         => 'A church near Upper Kabete Campus for students',
			'ibada-ya-jumapili-uthiru-kabete'                  => 'Ibada ya Jumapili Uthiru (Kiswahili)',
		);
		return apply_filters( 'dck_post_start_here_links', $links );
	}
}

if ( ! function_exists( 'dck_post_start_here' ) ) {
	function dck_post_start_here( $content ) {
		if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() || is_feed() ) {
			return $content;
		}
		$current = get_post_field( 'post_name', get_the_ID() );
		$items   = '';
		foreach ( dck_post_start_here_links() as $slug => $label ) {
			if ( $slug === $current ) {
				continue;
			}
			$items .= '<li><a href="' . esc_url( home_url( '/' . $slug . '/' ) ) . '">' . esc_html( $label ) . '</a></li>';
		}
		if ( ! $items ) {
			return $content;
		}
		$box = '<aside class="dck-start-here" aria-label="Plan a visit" style="margin:2rem 0;padding:1.25rem 1.5rem;border-left:4px solid #c9a227;background:rgba(201,162,39,.08);border-radius:8px">'
			. '<h2 style="margin-top:0;font-size:1.25rem">Planning a visit to Deliverance Church Kabete N Market?</h2>'
			. '<p>We meet behind N Market in Uthiru, Kabete, off Waiyaki Way. Call or WhatsApp +254 110 304521.</p>'
			. '<ul>' . $items . '</ul></aside>';
		return $content . $box;
	}
}
add_filter( 'the_content', 'dck_post_start_here', 20 );

if ( ! function_exists( 'dck_defer_theme_scripts' ) ) {
	function dck_defer_theme_scripts() {
		if ( is_admin() ) {
			return;
		}
		$wp_scripts = wp_scripts();
		foreach ( (array) $wp_scripts->queue as $handle ) {
			if ( 0 === strpos( $handle, 'dck' ) ) {
				wp_script_add_data( $handle, 'strategy', 'defer' );
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'dck_defer_theme_scripts', 999 );
