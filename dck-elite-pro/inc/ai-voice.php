<?php
/* DCK Elite - AI Voice: speech-to-text (Whisper) and text-to-speech (OpenAI). */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'dck_ai_voice_enabled' ) ) {
	function dck_ai_voice_enabled() {
		return (bool) get_theme_mod( 'dck_ai_voice', '' );
	}
}

/* Voice settings added to the DCK AI section. */
if ( ! function_exists( 'dck_ai_voice_customize' ) ) {
	function dck_ai_voice_customize( $wp_customize ) {
		$wp_customize->add_setting( 'dck_ai_voice', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'dck_ai_voice', array(
			'label'       => __( 'Enable AI Voice', 'dck-elite' ),
			'description' => __( 'Adds a microphone (speak to the assistant) and spoken replies using OpenAI. Uses your API key and requires the site to be served over HTTPS. When off, browsers that support it still fall back to built-in speech.', 'dck-elite' ),
			'section'     => 'dck_ai',
			'type'        => 'checkbox',
		) );
		$wp_customize->add_setting( 'dck_ai_tts_voice', array( 'default' => 'alloy', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'dck_ai_tts_voice', array(
			'label'   => __( 'Spoken reply voice', 'dck-elite' ),
			'section' => 'dck_ai',
			'type'    => 'select',
			'choices' => array( 'alloy' => 'Alloy', 'verse' => 'Verse', 'shimmer' => 'Shimmer', 'coral' => 'Coral', 'sage' => 'Sage' ),
		) );
	}
}
add_action( 'customize_register', 'dck_ai_voice_customize' );

/* Speech to text via OpenAI Whisper (multipart upload). */
if ( ! function_exists( 'dck_ai_rest_stt' ) ) {
	function dck_ai_rest_stt( $request ) {
		if ( function_exists( 'dck_ai_rate_ok' ) && ! dck_ai_rate_ok() ) { return new WP_REST_Response( array( 'text' => '' ), 200 ); }
		if ( ! dck_ai_voice_enabled() || ! function_exists( 'dck_ai_stt_key' ) || ! dck_ai_stt_key() ) { return new WP_REST_Response( array( 'text' => '' ), 200 ); }
		if ( empty( $_FILES['audio'] ) || ! isset( $_FILES['audio']['tmp_name'] ) ) { return new WP_REST_Response( array( 'text' => '' ), 200 ); }
		$file = $_FILES['audio'];
		if ( ! empty( $file['error'] ) ) { return new WP_REST_Response( array( 'text' => '' ), 200 ); }
		/* Upload hardening: real upload, size cap, MIME sniffed from content (never trust the browser). */
		if ( ! is_uploaded_file( $file['tmp_name'] ) ) { return new WP_REST_Response( array( 'text' => '' ), 400 ); }
		if ( (int) $file['size'] <= 0 || (int) $file['size'] > 8 * MB_IN_BYTES ) { return new WP_REST_Response( array( 'text' => '' ), 413 ); }
		$allowed = array( 'audio/webm', 'video/webm', 'audio/ogg', 'audio/mpeg', 'audio/mp4', 'video/mp4', 'audio/x-m4a', 'audio/wav', 'audio/x-wav', 'audio/aac' );
		$ctype   = '';
		if ( function_exists( 'finfo_open' ) ) {
			$fi    = finfo_open( FILEINFO_MIME_TYPE );
			$ctype = (string) finfo_file( $fi, $file['tmp_name'] );
			finfo_close( $fi );
		}
		if ( ! in_array( $ctype, $allowed, true ) ) { return new WP_REST_Response( array( 'text' => '' ), 415 ); }
		$bin = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( false === $bin || '' === $bin ) { return new WP_REST_Response( array( 'text' => '' ), 200 ); }
		$params = $request->get_params();
		$lang   = isset( $params['lang'] ) ? sanitize_key( $params['lang'] ) : '';

		$eol      = chr( 13 ) . chr( 10 );
		$boundary = 'dckform' . wp_generate_password( 16, false, false );
		$body  = '--' . $boundary . $eol;
		$body .= 'Content-Disposition: form-data; name="model"' . $eol . $eol . dck_ai_stt_model() . $eol;
		if ( 'sw' === $lang || 'en' === $lang ) {
			$body .= '--' . $boundary . $eol;
			$body .= 'Content-Disposition: form-data; name="language"' . $eol . $eol . $lang . $eol;
		}
		$body .= '--' . $boundary . $eol;
		$body .= 'Content-Disposition: form-data; name="file"; filename="audio.webm"' . $eol;
		$body .= 'Content-Type: ' . $ctype . $eol . $eol;
		$body .= $bin . $eol;
		$body .= '--' . $boundary . '--' . $eol;

		$resp = wp_remote_post( dck_ai_stt_base_url() . '/audio/transcriptions', array(
			'timeout' => 45,
			'headers' => array(
				'Authorization' => 'Bearer ' . dck_ai_stt_key(),
				'Content-Type'  => 'multipart/form-data; boundary=' . $boundary,
			),
			'body'    => $body,
		) );
		if ( is_wp_error( $resp ) ) { return new WP_REST_Response( array( 'text' => '' ), 200 ); }
		$json = json_decode( wp_remote_retrieve_body( $resp ), true );
		$text = ( is_array( $json ) && isset( $json['text'] ) ) ? $json['text'] : '';
		return new WP_REST_Response( array( 'text' => $text ), 200 );
	}
}

/* Text to speech via OpenAI, returned as a base64 data URL. */
if ( ! function_exists( 'dck_ai_rest_tts' ) ) {
	function dck_ai_rest_tts( $request ) {
		if ( function_exists( 'dck_ai_rate_ok' ) && ! dck_ai_rate_ok() ) { return new WP_REST_Response( array( 'audio' => '' ), 200 ); }
		if ( ! dck_ai_voice_enabled() || ! function_exists( 'dck_ai_get_key' ) || ! dck_ai_get_key() ) { return new WP_REST_Response( array( 'audio' => '' ), 200 ); }
		$p    = $request->get_json_params();
		$text = isset( $p['text'] ) ? trim( wp_strip_all_tags( (string) $p['text'] ) ) : '';
		if ( strlen( $text ) > 900 ) { $text = substr( $text, 0, 900 ); }
		if ( '' === $text ) { return new WP_REST_Response( array( 'audio' => '' ), 200 ); }
		$voice = get_theme_mod( 'dck_ai_tts_voice', 'alloy' );
		if ( ! in_array( $voice, array( 'alloy', 'echo', 'fable', 'onyx', 'nova', 'shimmer' ), true ) ) { $voice = 'alloy'; }

		$resp = wp_remote_post( dck_ai_base_url() . '/audio/speech', array(
			'timeout' => 45,
			'headers' => array(
				'Authorization' => 'Bearer ' . dck_ai_get_key(),
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( array( 'model' => 'tts-1', 'voice' => $voice, 'input' => $text, 'response_format' => 'mp3' ) ),
		) );
		if ( is_wp_error( $resp ) ) { return new WP_REST_Response( array( 'audio' => '' ), 200 ); }
		if ( 200 !== (int) wp_remote_retrieve_response_code( $resp ) ) { return new WP_REST_Response( array( 'audio' => '' ), 200 ); }
		$bin = wp_remote_retrieve_body( $resp );
		if ( '' === $bin ) { return new WP_REST_Response( array( 'audio' => '' ), 200 ); }
		return new WP_REST_Response( array( 'audio' => 'data:audio/mpeg;base64,' . base64_encode( $bin ) ), 200 );
	}
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'dck/v1', '/stt', array( 'methods' => 'POST', 'callback' => 'dck_ai_rest_stt', 'permission_callback' => '__return_true' ) );
	register_rest_route( 'dck/v1', '/tts', array( 'methods' => 'POST', 'callback' => 'dck_ai_rest_tts', 'permission_callback' => '__return_true' ) );
} );

/* Enqueue the voice client whenever the assistant is enabled. */
if ( ! function_exists( 'dck_ai_voice_enqueue' ) ) {
	function dck_ai_voice_enqueue() {
		if ( ! function_exists( 'dck_ai_enabled' ) || ! dck_ai_enabled() ) { return; }
		wp_enqueue_script( 'dck-voice', get_template_directory_uri() . '/assets/js/voice.js', array(), DCK_ELITE_VERSION, true );
		wp_localize_script( 'dck-voice', 'DCK_VOICE_CFG', array(
			'rest'   => esc_url_raw( rest_url( 'dck/v1/' ) ),
			'nonce'  => wp_create_nonce( 'wp_rest' ),
			'server' => ( dck_ai_voice_enabled() && function_exists( 'dck_ai_get_key' ) && dck_ai_get_key() ) ? 1 : 0,
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'dck_ai_voice_enqueue' );
