<?php
/**
 * Shared helpers for the church tools (giving records, visitors, event registration).
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Kenyan-friendly phone normaliser for WhatsApp links: 0712.. / +254712.. / 254712.. -> 254712.. */
if ( ! function_exists( 'dck_ct_wa_number' ) ) {
	function dck_ct_wa_number( $phone ) {
		$d = preg_replace( '/\D+/', '', (string) $phone );
		if ( '' === $d ) {
			return '';
		}
		$cc = preg_replace( '/\D+/', '', (string) get_theme_mod( 'dck_ct_country_code', '254' ) );
		$cc = $cc ? $cc : '254';
		if ( 0 === strpos( $d, '00' ) ) {
			$d = substr( $d, 2 ); // International 00 prefix.
		} elseif ( 0 === strpos( $d, '0' ) && strlen( $d ) >= 9 && strlen( $d ) <= 11 ) {
			$d = $cc . substr( $d, 1 ); // Local trunk 0 -> country code.
		} elseif ( 9 === strlen( $d ) && '254' === $cc && in_array( $d[0], array( '7', '1' ), true ) ) {
			$d = $cc . $d;
		}
		return ( strlen( $d ) >= 10 && strlen( $d ) <= 15 ) ? $d : '';
	}
}

if ( ! function_exists( 'dck_ct_wa_link' ) ) {
	function dck_ct_wa_link( $phone, $text ) {
		$n = dck_ct_wa_number( $phone );
		return $n ? 'https://wa.me/' . $n . '?text=' . rawurlencode( (string) $text ) : '';
	}
}

/* Currencies accepted on giving records (international givers). */
if ( ! function_exists( 'dck_ct_currencies' ) ) {
	function dck_ct_currencies() {
		return apply_filters( 'dck_currencies', array( 'KES' => 'KES - Kenyan Shilling', 'USD' => 'USD - US Dollar', 'GBP' => 'GBP - British Pound', 'EUR' => 'EUR - Euro', 'CAD' => 'CAD - Canadian Dollar', 'AUD' => 'AUD - Australian Dollar', 'UGX' => 'UGX - Ugandan Shilling', 'TZS' => 'TZS - Tanzanian Shilling', 'RWF' => 'RWF - Rwandan Franc', 'ZAR' => 'ZAR - South African Rand', 'AED' => 'AED - UAE Dirham' ) );
	}
}
if ( ! function_exists( 'dck_ct_money' ) ) {
	function dck_ct_money( $amount, $cur = 'KES' ) {
		$dec = in_array( $cur, array( 'KES', 'UGX', 'TZS', 'RWF' ), true ) ? 0 : 2;
		$amt = (float) $amount;
		return $cur . ' ' . number_format_i18n( $amt, ( $dec && floor( $amt ) != $amt ) ? 2 : 0 ); // phpcs:ignore Universal.Operators.StrictComparisons
	}
}

/* Replace {placeholders} in a message template. */
if ( ! function_exists( 'dck_ct_fill' ) ) {
	function dck_ct_fill( $tpl, $vars ) {
		foreach ( (array) $vars as $k => $v ) {
			$tpl = str_replace( '{' . $k . '}', (string) $v, $tpl );
		}
		return $tpl;
	}
}

/* Where a notification goes: setting -> church email -> admin email. */
if ( ! function_exists( 'dck_ct_email_to' ) ) {
	function dck_ct_email_to( $mod ) {
		$to = get_theme_mod( $mod, '' );
		if ( ! is_email( $to ) ) {
			$to = get_theme_mod( 'dck_top_email', '' );
		}
		return is_email( $to ) ? $to : get_option( 'admin_email' );
	}
}

/* Stream a CSV download (cells are protected against spreadsheet formula injection). */
if ( ! function_exists( 'dck_ct_csv' ) ) {
	function dck_ct_csv( $filename, $head, $rows ) {
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- UTF-8 BOM for Excel.
		fputcsv( $out, $head );
		foreach ( $rows as $r ) {
			$clean = array();
			foreach ( $r as $c ) {
				$c       = (string) $c;
				$clean[] = preg_match( '/^[=+\-@\t\r]/', $c ) ? "'" . $c : $c;
			}
			fputcsv( $out, $clean );
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
}

/* Small status pill for admin lists. */
if ( ! function_exists( 'dck_ct_pill' ) ) {
	function dck_ct_pill( $label, $tone ) {
		$c = array(
			'green' => '#e6f4ea;color:#1e6b34',
			'amber' => '#fff4d6;color:#7a5800',
			'blue'  => '#e9eefa;color:#1d347f',
			'red'   => '#fde8e8;color:#9b1c1c',
			'gray'  => '#eee;color:#444',
		);
		$s = isset( $c[ $tone ] ) ? $c[ $tone ] : $c['gray'];
		return '<span style="display:inline-block;padding:3px 10px;border-radius:999px;font-weight:600;font-size:12px;background:' . $s . '">' . esc_html( $label ) . '</span>';
	}
}

/* Shared front-end form script (giving confirmation, I'm New, event registration). */
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_register_script( 'dck-forms', get_template_directory_uri() . '/assets/js/church-forms.js', array(), DCK_ELITE_VERSION, true );
		wp_localize_script(
			'dck-forms',
			'DCK_FORMS',
			array(
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'sending' => __( 'Sending...', 'dck-elite' ),
				'errNet'  => __( 'Something went wrong. Please try again or call us.', 'dck-elite' ),
				'errReq'  => __( 'Please fill in the required fields.', 'dck-elite' ),
			)
		);
	}
);
