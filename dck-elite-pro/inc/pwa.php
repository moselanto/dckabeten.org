<?php
/**
 * Installable church app (Progressive Web App).
 * - /?dck_manifest=1  Web app manifest (name, icons, shortcuts)
 * - /?dck_sw=1        Service worker (served from the site root so it controls the whole site)
 * - /?dck_offline=1   Friendly offline page
 * Visitors can "Add to Home Screen"; recently viewed pages and sermons open without internet.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_pwa_icon' ) ) {
	function dck_pwa_icon( $file ) {
		return get_template_directory_uri() . '/assets/images/app/' . $file;
	}
}

add_action(
	'init',
	function () {
		// phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_GET['dck_manifest'] ) ) {
			$name  = dck_home_val( 'dck_app_name', get_bloginfo( 'name' ) );
			$short = dck_home_val( 'dck_app_short_name', 'DCK Kabete' );
			$sc    = array();
			foreach ( array( array( 'live', __( 'Watch Live', 'dck-elite' ) ), array( 'give', __( 'Give', 'dck-elite' ) ), array( 'prayer-requests', __( 'Prayer Request', 'dck-elite' ) ) ) as $s ) {
				$sc[] = array( 'name' => $s[1], 'url' => wp_make_link_relative( dck_elite_page_url( $s[0] ) ), 'icons' => array( array( 'src' => dck_pwa_icon( 'icon-192.png' ), 'sizes' => '192x192' ) ) );
			}
			$archive = get_post_type_archive_link( 'dck_sermon' );
			if ( $archive ) {
				$sc[] = array( 'name' => __( 'Sermons', 'dck-elite' ), 'url' => wp_make_link_relative( $archive ), 'icons' => array( array( 'src' => dck_pwa_icon( 'icon-192.png' ), 'sizes' => '192x192' ) ) );
			}
			$m = array(
				'id'               => '/',
				'name'             => $name,
				'short_name'       => $short,
				'description'      => get_bloginfo( 'description' ),
				'start_url'        => '/?source=app',
				'scope'            => '/',
				'display'          => 'standalone',
				'orientation'      => 'portrait',
				'background_color' => '#FAF8F4',
				'theme_color'      => '#0E1C4D',
				'lang'             => get_bloginfo( 'language' ),
				'icons'            => array(
					array( 'src' => dck_pwa_icon( 'icon-192.png' ), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any' ),
					array( 'src' => dck_pwa_icon( 'icon-512.png' ), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any' ),
					array( 'src' => dck_pwa_icon( 'icon-maskable-512.png' ), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable' ),
				),
				'shortcuts'        => $sc,
			);
			nocache_headers();
			header( 'Content-Type: application/manifest+json; charset=utf-8' );
			header( 'Cache-Control: public, max-age=86400' );
			echo wp_json_encode( $m, JSON_UNESCAPED_SLASHES );
			exit;
		}
		if ( isset( $_GET['dck_sw'] ) ) {
			$js = (string) file_get_contents( get_template_directory() . '/assets/js/sw.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$js = str_replace(
				array( '__VERSION__', '__OFFLINE__', '__PRECACHE__' ),
				array(
					esc_js( DCK_ELITE_VERSION ),
					esc_url_raw( home_url( '/?dck_offline=1' ) ),
					wp_json_encode( array( home_url( '/?dck_offline=1' ), dck_pwa_icon( 'icon-192.png' ), get_template_directory_uri() . '/assets/images/logo.webp' ), JSON_UNESCAPED_SLASHES ),
				),
				$js
			);
			header( 'Content-Type: application/javascript; charset=utf-8' );
			header( 'Service-Worker-Allowed: /' );
			header( 'Cache-Control: no-cache, no-store, must-revalidate' );
			echo $js; // phpcs:ignore WordPress.Security.EscapeOutput -- static theme file.
			exit;
		}
		if ( isset( $_GET['dck_offline'] ) ) {
			header( 'Content-Type: text/html; charset=utf-8' );
			$tel = function_exists( 'dck_home_tel' ) ? dck_home_tel() : '';
			?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#0E1C4D"><title><?php esc_html_e( 'You are offline', 'dck-elite' ); ?></title>
<style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#FAF8F4;font:16px/1.6 system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:#141414;padding:24px;box-sizing:border-box}.c{max-width:420px;text-align:center;background:#fff;border-radius:20px;padding:32px 26px;box-shadow:0 30px 60px -40px rgba(14,28,77,.5)}img{width:72px;height:72px;border-radius:18px}h1{font-family:Georgia,serif;color:#0E1C4D;margin:14px 0 6px;font-size:26px}p{color:#5d6683;margin:0 0 18px}a,button{display:block;width:100%;box-sizing:border-box;padding:14px;border-radius:12px;font-weight:600;font-size:15px;text-decoration:none;border:0;cursor:pointer;margin-top:10px}button{background:#24409A;color:#fff}a{background:#E9EEFA;color:#0E1C4D}</style></head>
<body><div class="c"><img src="<?php echo esc_url( dck_pwa_icon( 'icon-192.png' ) ); ?>" alt=""><h1><?php esc_html_e( 'You are offline', 'dck-elite' ); ?></h1><p><?php esc_html_e( 'Check your internet connection. Pages and sermons you opened recently are still available.', 'dck-elite' ); ?></p><button onclick="location.reload()"><?php esc_html_e( 'Try again', 'dck-elite' ); ?></button><?php if ( $tel ) : ?><a href="tel:<?php echo esc_attr( $tel ); ?>"><?php esc_html_e( 'Call the church', 'dck-elite' ); ?></a><?php endif; ?></div></body></html>
			<?php
			exit;
		}
		// phpcs:enable
	},
	1
);

add_action(
	'wp_head',
	function () {
		echo '<link rel="manifest" href="' . esc_url( home_url( '/?dck_manifest=1' ) ) . '">' . "\n";
		echo '<link rel="apple-touch-icon" href="' . esc_url( dck_pwa_icon( 'apple-touch-icon.png' ) ) . '">' . "\n";
		echo '<meta name="mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-status-bar-style" content="default"><meta name="apple-mobile-web-app-title" content="' . esc_attr( dck_home_val( 'dck_app_short_name', 'DCK Kabete' ) ) . '">' . "\n";
	},
	3
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_script( 'dck-pwa', get_template_directory_uri() . '/assets/js/pwa.js', array(), DCK_ELITE_VERSION, true );
		wp_localize_script( 'dck-pwa', 'DCK_PWA', array( 'sw' => home_url( '/?dck_sw=1' ) ) );
	}
);

/* "Install the app" button inside the mobile menu (shown only when the phone supports it). */
if ( ! function_exists( 'dck_pwa_install_button' ) ) {
	function dck_pwa_install_button() {
		echo '<div class="dck-install" hidden><span class="dck-install__ic"><img src="' . esc_url( dck_pwa_icon( 'icon-192.png' ) ) . '" alt="" width="40" height="40" loading="lazy"></span><span class="dck-install__txt"><strong>' . esc_html__( 'Get the DCK app', 'dck-elite' ) . '</strong><small class="dck-install__hint">' . esc_html__( 'Free, no app store needed', 'dck-elite' ) . '</small></span><button type="button" class="dck-btn dck-btn--gold dck-install__btn">' . esc_html__( 'Install', 'dck-elite' ) . '</button></div>';
	}
}
