<?php
/**
 * Editable content layer: defaults + helpers for content that used to be
 * hardcoded (service times, giving details, phone/email tokens, legal page).
 * Everything here is editable in DCK Church > Church Settings (DCK Core plugin)
 * or Appearance > Customize, and falls back to these defaults when blank.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------- Contact basics ---------------- */

if ( ! function_exists( 'dck_phone_display' ) ) {
	function dck_phone_display() {
		return function_exists( 'dck_home_val' ) ? dck_home_val( 'dck_top_phone', '+254 110 304521' ) : '+254 110 304521';
	}
}

if ( ! function_exists( 'dck_church_email' ) ) {
	function dck_church_email() {
		return function_exists( 'dck_home_val' ) ? dck_home_val( 'dck_top_email', 'info@dckabeten.org' ) : 'info@dckabeten.org';
	}
}

if ( ! function_exists( 'dck_church_address' ) ) {
	function dck_church_address() {
		return function_exists( 'dck_home_val' ) ? dck_home_val( 'dck_top_address', 'Behind N Market, Uthiru, Kabete' ) : 'Behind N Market, Uthiru, Kabete';
	}
}

if ( ! function_exists( 'dck_notify_email' ) ) {
	/** Where form notifications go: prayer team email, then church email, then admin. */
	function dck_notify_email() {
		$e = get_theme_mod( 'dck_ai_prayer_email', '' );
		if ( ! $e || ! is_email( $e ) ) {
			$e = dck_church_email();
		}
		return is_email( $e ) ? $e : get_option( 'admin_email' );
	}
}

/* ---------------- Service times ---------------- */

if ( ! function_exists( 'dck_elite_default_service_times' ) ) {
	function dck_elite_default_service_times() {
		return array(
			array( 'name' => 'Intensive Prayers', 'time' => '8:00 - 9:00 AM' ),
			array( 'name' => 'Intensive Bible Study', 'time' => '9:30 - 10:30 AM' ),
			array( 'name' => 'Glorious Service', 'time' => '10:40 AM - 1:00 PM' ),
			array( 'name' => 'Keshas (Overnight)', 'time' => '1st & 3rd Week' ),
		);
	}
}

if ( ! function_exists( 'dck_service_times_sentence' ) ) {
	function dck_service_times_sentence() {
		$parts = array();
		foreach ( dck_elite_service_times() as $s ) {
			$parts[] = $s['name'] . ' ' . $s['time'];
		}
		return implode( '; ', $parts );
	}
}

/* ---------------- Giving ---------------- */

if ( ! function_exists( 'dck_give_defaults' ) ) {
	function dck_give_defaults() {
		return array(
			'dck_give_paybill'            => '247247',
			'dck_give_paybill_account'    => '403263',
			'dck_give_till'               => '9900619',
			'dck_give_bank_name'          => 'Equity Bank of Kenya',
			'dck_give_bank_account_name'  => 'Deliverance Church Kabete Technical',
			'dck_give_bank_account'       => '1370274104398',
			'dck_give_bank_swift'         => 'EQBLKENA',
			'dck_give_bank_branch'        => 'Kangemi',
		);
	}
}

if ( ! function_exists( 'dck_give_val' ) ) {
	function dck_give_val( $key ) {
		$d = dck_give_defaults();
		$v = get_theme_mod( $key, '' );
		return ( '' === $v || null === $v ) ? ( isset( $d[ $key ] ) ? $d[ $key ] : '' ) : $v;
	}
}

if ( ! function_exists( 'dck_giving_summary' ) ) {
	function dck_giving_summary() {
		$p = array();
		if ( dck_give_val( 'dck_give_paybill' ) ) {
			$p[] = 'M-Pesa Paybill ' . dck_give_val( 'dck_give_paybill' ) . ( dck_give_val( 'dck_give_paybill_account' ) ? ', Account ' . dck_give_val( 'dck_give_paybill_account' ) : '' );
		}
		if ( dck_give_val( 'dck_give_till' ) ) {
			$p[] = 'M-Pesa Till ' . dck_give_val( 'dck_give_till' );
		}
		if ( dck_give_val( 'dck_give_bank_account' ) ) {
			$p[] = dck_give_val( 'dck_give_bank_name' ) . ' Account ' . dck_give_val( 'dck_give_bank_account' ) . ( dck_give_val( 'dck_give_bank_account_name' ) ? ' (' . dck_give_val( 'dck_give_bank_account_name' ) . ')' : '' ) . ( dck_give_val( 'dck_give_bank_swift' ) ? ', SWIFT ' . dck_give_val( 'dck_give_bank_swift' ) : '' ) . ( dck_give_val( 'dck_give_bank_branch' ) ? ', ' . dck_give_val( 'dck_give_bank_branch' ) . ' branch' : '' );
		}
		return implode( '; ', $p );
	}
}

/* ---------------- Text tokens ---------------- */

if ( ! function_exists( 'dck_tokens' ) ) {
	/**
	 * Replace content tokens with live settings: %HOME% %PHONE% %EMAIL% %ADDRESS% %GIVING% %SERVICES%.
	 */
	function dck_tokens( $s ) {
		return strtr(
			(string) $s,
			array(
				'%HOME%'     => home_url( '/' ),
				'%PHONE%'    => dck_phone_display(),
				'%EMAIL%'    => dck_church_email(),
				'%ADDRESS%'  => dck_church_address(),
				'%GIVING%'   => dck_giving_summary(),
				'%SERVICES%' => dck_service_times_sentence(),
			)
		);
	}
}

/* ---------------- Legal page ---------------- */

if ( ! function_exists( 'dck_legal_default_sections' ) ) {
	function dck_legal_default_sections() {
		return array(
			array(
				'title' => 'About these terms',
				'body'  => '<p>This website is operated by Deliverance Church Kabete N Market (Cistern of Hope Center), Uthiru, Nairobi, Kenya ("the church", "we", "us"). By using this website you agree to these terms. If you do not agree, please do not use the site.</p>',
			),
			array(
				'title' => 'Using this website',
				'body'  => '<ul><li>Use the site lawfully and respectfully. Do not attempt to disrupt, misuse or gain unauthorised access to it.</li><li>Do not submit false information, spam or anything offensive through our forms.</li><li>We may update, suspend or change any part of the site at any time.</li></ul>',
			),
			array(
				'title' => 'AI assistant and Bible tools',
				'body'  => '<p>Our AI church assistant and Bible study tools are provided for encouragement and general information only. They can make mistakes and are not a substitute for pastoral care, medical, legal, financial or professional advice.</p><p><strong>In an emergency</strong>, contact your local emergency services immediately. To speak with a pastor, call or WhatsApp us on %PHONE%.</p>',
			),
			array(
				'title' => 'Giving',
				'body'  => '<p>All gifts are voluntary and support the ministry and work of the church. Please confirm payment details on our <a href="%HOME%give/">Giving page</a> before sending a gift. The church will never ask you for your M-Pesa PIN, bank passwords or card details by phone, SMS or social media.</p><p>If you made a gift in error, contact us on %PHONE% or %EMAIL% as soon as possible and we will do our best to help.</p>',
			),
			array(
				'title' => 'Sermons and content',
				'body'  => '<p>Sermons, teaching notes, photos, videos and other materials on this site belong to the church unless stated otherwise. You are welcome to share links and use them for personal encouragement and non-commercial ministry with credit to the church. Please do not reproduce, edit or sell them without our written permission.</p>',
			),
			array(
				'title' => 'Links to other sites',
				'body'  => '<p>Our site may link to other websites, such as live streams, maps or social media. We are not responsible for the content or privacy practices of those external sites.</p>',
			),
			array(
				'title' => 'Privacy: what we collect',
				'body'  => '<p>We only collect what we need to serve you:</p><ul><li><strong>Contact form:</strong> your name, email, phone (optional) and message.</li><li><strong>Prayer requests:</strong> your name, contact details and request. Requests marked confidential reach our pastors only.</li><li><strong>Photography preference:</strong> your name, phone, email and signature.</li><li><strong>Website usage:</strong> basic technical data (such as browser type and pages visited) to keep the site secure and working well.</li></ul>',
			),
			array(
				'title' => 'Privacy: how we use and protect it',
				'body'  => '<ul><li>We use your details only to reply to you, pray for you, care for you and honour your preferences.</li><li>We <strong>never sell or rent</strong> your information.</li><li>Access is limited to authorised church staff and ministry leaders.</li><li>We keep information only as long as needed for these purposes, then delete it.</li><li>We handle personal data in line with the Kenya Data Protection Act, 2019, and respect the rights of visitors from other countries.</li></ul>',
			),
			array(
				'title' => 'Your rights',
				'body'  => '<p>You may ask to see, correct or delete the information we hold about you, or withdraw your consent at any time. Email %EMAIL% or call %PHONE% and we will respond as quickly as we can.</p>',
			),
			array(
				'title' => 'Cookies and third-party services',
				'body'  => '<p>This site uses a small number of essential cookies and trusted services to work properly, such as YouTube or Facebook for live streams, Google Translate for languages and Google Maps for directions. These services may set their own cookies under their own privacy policies.</p>',
			),
			array(
				'title' => 'Photography and video at church',
				'body'  => '<p>To share the joy and testimony of what God is doing, we sometimes take photos and record video during services, events and gatherings. Only authorised media team members may do this on behalf of the church.</p><p>These photos and videos may be used to encourage and inform our church family and community, for example on this website, our social media pages, posters and other church communications.</p><p>By attending, you may appear in photos or video. If you would prefer not to be photographed or recorded, complete the form below or speak to an usher when you arrive. Children are photographed only with care, and we will remove any image on request.</p>',
			),
			array(
				'title' => 'Children and young people',
				'body'  => '<p>Our online forms are intended for adults. Parents or guardians may contact us on behalf of children under 18.</p>',
			),
			array(
				'title' => 'Changes and contact',
				'body'  => '<p>We may update this page from time to time; the date at the top shows the latest version. Questions about these terms or your privacy? Email %EMAIL%, call %PHONE%, or visit us at %ADDRESS%.</p>',
			),
		);
	}
}

if ( ! function_exists( 'dck_legal_sections' ) ) {
	function dck_legal_sections() {
		$s = get_theme_mod( 'dck_legal_sections', array() );
		if ( ! is_array( $s ) || empty( $s ) ) {
			$s = dck_legal_default_sections();
		}
		$out = array();
		foreach ( $s as $row ) {
			if ( empty( $row['title'] ) ) {
				continue;
			}
			$out[] = array(
				'title' => (string) $row['title'],
				'body'  => dck_tokens( isset( $row['body'] ) ? (string) $row['body'] : '' ),
				'id'    => sanitize_title( $row['title'] ),
			);
		}
		return $out;
	}
}

/* ---------------- Recommend the DCK Core plugin ---------------- */

add_action(
	'admin_notices',
	function () {
		if ( defined( 'DCK_CORE_VERSION' ) || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && ! in_array( $screen->id, array( 'dashboard', 'themes', 'plugins' ), true ) ) {
			return;
		}
		echo '<div class="notice notice-info"><p><strong>DCK Elite Pro:</strong> ' . esc_html__( 'Activate the DCK Core plugin to edit all church content (contact details, service times, giving, ministries, terms and more) from one simple Church Settings screen.', 'dck-elite' ) . '</p></div>';
	}
);
