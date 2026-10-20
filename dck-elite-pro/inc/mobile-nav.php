<?php
/**
 * Mobile navigation: bottom tab bar (Home, Sermons, Live, Give, Menu) and the
 * quick actions shown inside the full-screen mobile menu. Visible below 1024px only.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_mnav_icon' ) ) {
	function dck_mnav_icon( $name ) {
		$p = array(
			'home'    => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/>',
			'sermons' => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5z"/><path d="M4 20.5A2.5 2.5 0 0 0 6.5 23H20v-5"/><path d="M9 8h7M9 12h5"/>',
			'live'    => '<rect x="2.5" y="5" width="19" height="14" rx="3"/><path d="m10 9 5 3-5 3z" fill="currentColor"/>',
			'give'    => '<path d="M12 21s-7.5-4.6-9.3-9.2C1.4 8.4 3.6 5 7 5c2 0 3.4 1.1 5 3 1.6-1.9 3-3 5-3 3.4 0 5.6 3.4 4.3 6.8C19.5 16.4 12 21 12 21z"/>',
			'menu'    => '<path d="M4 7h16M4 12h16M4 17h10"/>',
			'pray'    => '<path d="M12 3v7"/><path d="M8.5 21 7 14l5-4 5 4-1.5 7"/><path d="M9 21h6"/>',
			'phone'   => '<path d="M6.6 10.8a15 15 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.24 11 11 0 0 0 3.5.56 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11 11 0 0 0 .56 3.5 1 1 0 0 1-.24 1Z"/>',
			'map'     => '<path d="M12 21s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/>',
			'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		);
		return '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ( isset( $p[ $name ] ) ? $p[ $name ] : '' ) . '</svg>';
	}
}

/* Quick actions + contact strip inside the opened mobile menu. */
if ( ! function_exists( 'dck_mobile_menu_extras' ) ) {
	function dck_mobile_menu_extras() {
		$tel   = function_exists( 'dck_home_tel' ) ? dck_home_tel() : '';
		$items = array(
			array( dck_elite_page_url( 'live' ), 'live', dck_home_val( 'dck_btn_watch_live', __( 'Watch Live', 'dck-elite' ) ) ),
			array( dck_elite_page_url( 'prayer-requests' ), 'pray', __( 'Prayer Request', 'dck-elite' ) ),
			array( dck_elite_page_url( 'give' ), 'give', __( 'Give', 'dck-elite' ) ),
			array( dck_elite_page_url( 'services' ), 'clock', __( 'Service Times', 'dck-elite' ) ),
		);
		echo '<div class="dck-nav__extras">';
		if ( function_exists( 'dck_pwa_install_button' ) ) {
			dck_pwa_install_button();
		}
		echo '<a class="dck-nav__new" href="' . esc_url( dck_elite_page_url( 'new-here' ) ) . '"><strong>' . esc_html__( 'New here?', 'dck-elite' ) . '</strong> ' . esc_html__( 'Tell us you visited - we would love to welcome you.', 'dck-elite' ) . '</a>';
		echo '<p class="dck-nav__label">' . esc_html__( 'Quick actions', 'dck-elite' ) . '</p><div class="dck-nav__quick">';
		foreach ( $items as $it ) {
			echo '<a href="' . esc_url( $it[0] ) . '">' . dck_mnav_icon( $it[1] ) . '<span>' . esc_html( $it[2] ) . '</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG.
		}
		echo '</div><div class="dck-nav__contact">';
		if ( $tel ) {
			echo '<a class="dck-nav__call" href="tel:' . esc_attr( $tel ) . '">' . dck_mnav_icon( 'phone' ) . '<span>' . esc_html( dck_home_val( 'dck_top_phone', '+254 110 304521' ) ) . '</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( function_exists( 'dck_home_directions' ) ) {
			echo '<a href="' . esc_url( dck_home_directions() ) . '" target="_blank" rel="noopener">' . dck_mnav_icon( 'map' ) . '<span>' . esc_html__( 'Get directions', 'dck-elite' ) . '</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</div>';
		echo '<div class="dck-nav__lang" id="dck-nav-lang"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.6 2.7 2.6 15.3 0 18M12 3c-2.6 2.7-2.6 15.3 0 18"/></svg><span>' . esc_html__( 'Language', 'dck-elite' ) . '</span></div>';
		if ( function_exists( 'dck_social_icons' ) ) {
			echo '<div class="dck-nav__social">';
			dck_social_icons();
			echo '</div>';
		}
		echo '</div>';
	}
}

/* Bottom tab bar. */
add_action(
	'wp_footer',
	function () {
		if ( is_page_template( 'template-bible.php' ) ) {
			return; // The Bible reader has its own bottom toolbar.
		}
		$sermons = get_post_type_archive_link( 'dck_sermon' );
		$tabs    = array(
			array( home_url( '/' ), 'home', __( 'Home', 'dck-elite' ), is_front_page() ),
			array( $sermons ? $sermons : home_url( '/' ), 'sermons', __( 'Sermons', 'dck-elite' ), is_post_type_archive( 'dck_sermon' ) || is_singular( 'dck_sermon' ) ),
			array( dck_elite_page_url( 'live' ), 'live', __( 'Live', 'dck-elite' ), is_page_template( 'template-live.php' ) ),
			array( dck_elite_page_url( 'give' ), 'give', __( 'Give', 'dck-elite' ), is_page_template( 'template-give.php' ) ),
		);
		echo '<nav class="dck-tabbar" aria-label="' . esc_attr__( 'Quick navigation', 'dck-elite' ) . '">';
		foreach ( $tabs as $t ) {
			$cls = 'dck-tabbar__item' . ( 'live' === $t[1] ? ' dck-tabbar__item--live' : '' ) . ( $t[3] ? ' is-active' : '' );
			echo '<a class="' . esc_attr( $cls ) . '" href="' . esc_url( $t[0] ) . '"' . ( $t[3] ? ' aria-current="page"' : '' ) . '><span class="dck-tabbar__ic">' . dck_mnav_icon( $t[1] ) . '</span><span class="dck-tabbar__txt">' . esc_html( $t[2] ) . '</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '<button type="button" class="dck-tabbar__item dck-tabbar__menu" aria-controls="dck-nav" aria-expanded="false"><span class="dck-tabbar__ic">' . dck_mnav_icon( 'menu' ) . '</span><span class="dck-tabbar__txt">' . esc_html__( 'Menu', 'dck-elite' ) . '</span></button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</nav>';
	},
	5
);

add_filter(
	'body_class',
	function ( $c ) {
		if ( ! is_page_template( 'template-bible.php' ) ) {
			$c[] = 'has-tabbar';
		}
		return $c;
	}
);
