<?php
/**
 * Header.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php if ( function_exists( 'wp_body_open' ) ) { wp_body_open(); } ?>

<a class="dck-skip" href="#dck-main"><?php esc_html_e( 'Skip to content', 'dck-elite' ); ?></a>

<div class="dck-topbar">
	<div class="dck-container dck-topbar__inner">
		<div class="dck-topbar__meta">
			<div class="dck-lang"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3c2.6 2.7 2.6 15.3 0 18M12 3c-2.6 2.7-2.6 15.3 0 18"/></svg><div id="google_translate_element" class="dck-translate" aria-label="Language selector"></div></div>
			<a class="dck-topbar__item" href="mailto:<?php echo esc_attr( dck_home_val( 'dck_top_email', 'info@dckabeten.org' ) ); ?>">
				<svg class="dck-ic" viewBox="0 0 24 24" width="15" height="15" fill="currentColor" aria-hidden="true" focusable="false"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5-8-5V6l8 5 8-5Z"/></svg>
				<span><?php echo esc_html( dck_home_val( 'dck_top_email', 'info@dckabeten.org' ) ); ?></span>
			</a>
			<span class="dck-topbar__item">
				<svg class="dck-ic" viewBox="0 0 24 24" width="15" height="15" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"/></svg>
				<span><?php echo esc_html( dck_home_val( 'dck_top_address', 'Behind N Market, Uthiru, Kabete' ) ); ?></span>
			</span>
			<a class="dck-topbar__item" href="tel:<?php echo esc_attr( function_exists( 'dck_home_tel' ) ? dck_home_tel() : '+254110304521' ); ?>">
				<svg class="dck-ic" viewBox="0 0 24 24" width="15" height="15" fill="currentColor" aria-hidden="true" focusable="false"><path d="M6.6 10.8a15 15 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.24 11 11 0 0 0 3.5.56 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11 11 0 0 0 .56 3.5 1 1 0 0 1-.24 1Z"/></svg>
				<span><?php echo esc_html( dck_home_val( 'dck_top_phone', '+254 110 304521' ) ); ?></span>
			</a>
		</div>
		<div class="dck-topbar__right">
			<?php if ( function_exists( 'dck_social_icons' ) ) { dck_social_icons(); } ?>
			<a class="dck-live" href="<?php echo esc_url( dck_elite_page_url( 'live' ) ); ?>"><?php echo esc_html( dck_home_val( 'dck_btn_watch_live', 'Watch Live' ) ); ?></a>
		</div>
	</div>
</div>

<header class="dck-header">
	<div class="dck-container">
		<div class="dck-brand">
			<?php
			if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) {
				the_custom_logo();
			} else {
				$dck_logo = get_template_directory() . '/assets/images/logo.png';
				if ( file_exists( $dck_logo ) ) {
					echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="dck-brand__logo"><picture><source srcset="' . esc_url( get_template_directory_uri() . '/assets/images/logo.webp' ) . '" type="image/webp"><img src="' . esc_url( get_template_directory_uri() . '/assets/images/logo.png' ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" width="256" height="56" fetchpriority="high" decoding="async" class="skip-lazy" data-no-lazy="1"></picture></a>';
				} else {
					echo '<span>' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
				}
			}
			?>
		</div>

		<nav class="dck-nav" id="dck-nav" aria-label="<?php esc_attr_e( 'Primary', 'dck-elite' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => '',
					'fallback_cb'    => 'dck_elite_fallback_menu',
					'depth'          => 2,
				)
			);
			?>
		</nav>

		<div class="dck-header__cta">
			<a class="dck-btn dck-btn--primary" href="<?php echo esc_url( dck_elite_page_url( 'prayer-requests' ) ); ?>"><?php esc_html_e( 'Prayer Request', 'dck-elite' ); ?></a>
			<button class="dck-burger" aria-label="<?php esc_attr_e( 'Menu', 'dck-elite' ); ?>" aria-expanded="false" aria-controls="dck-nav">&#9776;</button>
		</div>
	</div>
</header>
<?php do_action( 'dck_after_header' ); ?>

<main id="dck-main">
