<?php
/**
 * Branded, secure login screen (wp-login.php).
 *
 * Design: split screen - photo + welcome panel on the left, sign-in card on the right.
 * Security: honeypot field, generic errors, lost-password rate limit and
 * non-enumerating messages (lockout itself lives in inc/hardening.php).
 * Text and photo are editable in DCK Church > Church & Contact > Staff login page.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- Assets ---------- */
add_action(
	'login_enqueue_scripts',
	function () {
		wp_enqueue_style( 'dck-login', get_template_directory_uri() . '/assets/css/login.css', array(), DCK_ELITE_VERSION );
		$img = dck_theme_image( 'dck_login_img', 'assets/images/home/joy.webp' );
		wp_add_inline_style( 'dck-login', 'body.login{--dck-login-img:url(' . esc_url_raw( $img ) . ')}' );
	}
);

add_filter( 'login_headerurl', function () { return home_url( '/' ); } );
add_filter( 'login_headertext', function () { return get_bloginfo( 'name' ); } );

/* Privacy link falls back to the Terms & Privacy page. */
add_filter(
	'privacy_policy_url',
	function ( $url ) {
		return $url ? $url : dck_elite_page_url( 'terms' );
	}
);

/* ---------- Left welcome panel ---------- */
add_action(
	'login_header',
	function () {
		$name   = get_bloginfo( 'name' );
		$points = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) dck_home_val( 'dck_login_points', "Publish sermons, events and photos\nRead prayer requests and messages\nUpdate church details in DCK Church" ) ) ) );
		?>
		<aside class="dck-lg-aside" aria-hidden="false">
			<div class="dck-lg-aside__top">
				<a class="dck-lg-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.webp' ); ?>" alt="" width="40" height="40">
					<span><?php echo esc_html( $name ); ?></span>
				</a>
			</div>
			<div class="dck-lg-aside__body">
				<span class="dck-lg-tag"><?php echo esc_html( dck_home_val( 'dck_login_tag', __( 'Staff & ministry team', 'dck-elite' ) ) ); ?></span>
				<h2><?php echo esc_html( dck_home_val( 'dck_login_title', __( 'Every life we reach online begins with the work you do here.', 'dck-elite' ) ) ); ?></h2>
				<p><?php echo esc_html( dck_home_val( 'dck_login_text', __( 'Sign in to look after the website of Deliverance Church Kabete N Market, Cistern of Hope Center.', 'dck-elite' ) ) ); ?></p>
				<?php if ( $points ) : ?>
					<ul>
						<?php foreach ( $points as $pt ) : ?><li><?php echo esc_html( $pt ); ?></li><?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
			<p class="dck-lg-aside__help">
				<?php esc_html_e( 'Need access or help?', 'dck-elite' ); ?>
				<a href="tel:<?php echo esc_attr( dck_home_tel() ); ?>"><?php echo esc_html( dck_phone_display() ); ?></a>
				<a href="mailto:<?php echo esc_attr( dck_church_email() ); ?>"><?php echo esc_html( dck_church_email() ); ?></a>
			</p>
		</aside>
		<?php
	}
);

/* ---------- Heading above the form ---------- */
add_filter(
	'login_message',
	function ( $message ) {
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification
		if ( in_array( $action, array( 'login', '' ), true ) && ! isset( $_GET['checkemail'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$message = '<div class="dck-lg-head"><h1>' . esc_html__( 'Welcome back', 'dck-elite' ) . '</h1><p>' . esc_html__( 'Sign in to manage the church website.', 'dck-elite' ) . '</p></div>' . $message;
		} elseif ( 'lostpassword' === $action ) {
			$message = '<div class="dck-lg-head"><h1>' . esc_html__( 'Reset your password', 'dck-elite' ) . '</h1><p>' . esc_html__( 'Enter your username or email and we will send you a secure reset link.', 'dck-elite' ) . '</p></div>';
		}
		return $message;
	}
);

/* ---------- Security note + copyright under the card ---------- */
add_action(
	'login_footer',
	function () {
		echo '<div class="dck-lg-foot"><p class="dck-lg-secure"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>' . esc_html__( 'Secure connection. Repeated failed attempts are paused for 15 minutes.', 'dck-elite' ) . '</p>';
		echo '<p class="dck-lg-copy">&copy; ' . esc_html( wp_date( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) ) . '</p></div>';
	}
);

/* ---------- Honeypot on login + lost-password forms ---------- */
if ( ! function_exists( 'dck_login_honeypot_field' ) ) {
	function dck_login_honeypot_field() {
		echo '<p class="dck-lg-hp" aria-hidden="true"><label>' . esc_html__( 'Leave this field empty', 'dck-elite' ) . '<input type="text" name="dck_hp_site" value="" tabindex="-1" autocomplete="off"></label></p>';
	}
}
add_action( 'login_form', 'dck_login_honeypot_field' );
add_action( 'lostpassword_form', 'dck_login_honeypot_field' );

add_filter(
	'authenticate',
	function ( $user ) {
		if ( isset( $_POST['dck_hp_site'] ) && '' !== trim( (string) wp_unslash( $_POST['dck_hp_site'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			return new WP_Error( 'dck_bot', __( 'Incorrect login details.', 'dck-elite' ) );
		}
		return $user;
	},
	25
);

/* ---------- Lost password: rate limit + no account enumeration ---------- */
add_action(
	'lostpassword_post',
	function ( $errors ) {
		if ( isset( $_POST['dck_hp_site'] ) && '' !== trim( (string) wp_unslash( $_POST['dck_hp_site'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			$errors->add( 'dck_bot', __( 'Request blocked.', 'dck-elite' ) );
			return;
		}
		if ( function_exists( 'dck_sec_form_rate_ok' ) && ! dck_sec_form_rate_ok( 'lostpw', 3, HOUR_IN_SECONDS ) ) {
			$errors->add( 'dck_rate', __( 'Too many reset requests. Please try again in an hour.', 'dck-elite' ) );
		}
	}
);

/* Same message whether or not the account exists. */
add_filter(
	'lostpassword_errors',
	function ( $errors ) {
		if ( is_wp_error( $errors ) && ( $errors->get_error_message( 'invalid_email' ) || $errors->get_error_message( 'invalidcombo' ) ) ) {
			wp_safe_redirect( wp_login_url() . '?checkemail=confirm' );
			exit;
		}
		return $errors;
	}
);
