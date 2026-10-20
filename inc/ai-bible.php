<?php
/* DCK Elite - AI Bible Assistant module (REST + page enqueue + menu). */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'dck_ai_bible_prompt' ) ) {
	function dck_ai_bible_prompt( $level ) {
		$lvl = $level ? $level : 'Balanced';
		return "You are a warm, faithful Bible study assistant for Deliverance Church Kabete N Market (Cistern of Hope Center) in Uthiru, Nairobi, Kenya. Explain Scripture clearly, reverently, and grounded firmly in the Bible.

When the user asks about a verse or a topic, reply using these labelled sections, each heading on its own line starting with two hash marks:
## Explanation
A clear, plain-language explanation.
## Context
The historical and biblical context.
## Cross References
Two to four related passages as book chapter:verse.
## Original Language
One or two Greek or Hebrew word insights when they add light.
## Application
Practical application for daily life.
## Prayer
A short prayer of a few sentences.
## Reflection
Two reflection questions.

Adapt the depth to this level: " . $lvl . ". Simple means everyday language; Children means very gentle and simple; Youth means relatable to teenagers; Theological means deeper doctrine; Original means focus on Greek or Hebrew. Be encouraging and warm, stay non-denominational in tone, avoid speculation and date-setting, and gently encourage the reader to read the full passage in context and to grow with their local church. Use plain text only, with the section headings shown.";
	}
}

if ( ! function_exists( 'dck_ai_rest_bible' ) ) {
	function dck_ai_rest_bible( $request ) {
		if ( function_exists( 'dck_ai_rate_ok' ) && ! dck_ai_rate_ok() ) {
			return new WP_REST_Response( array( 'reply' => 'Please try again in a few minutes.', 'sermons' => array() ), 200 );
		}
		$p = $request->get_json_params();
		$query = isset( $p['query'] ) ? trim( wp_strip_all_tags( (string) $p['query'] ) ) : '';
		$level = isset( $p['level'] ) ? sanitize_text_field( (string) $p['level'] ) : '';
		if ( strlen( $query ) > 400 ) { $query = substr( $query, 0, 400 ); }
		if ( '' === $query ) {
			return new WP_REST_Response( array( 'reply' => 'Ask me about a verse, for example Romans 8:28, or a topic, for example anxiety or forgiveness.', 'sermons' => array() ), 200 );
		}
		if ( ! function_exists( 'dck_ai_get_key' ) || ! dck_ai_get_key() ) {
			return new WP_REST_Response( array( 'reply' => 'The Bible assistant is being set up. Please check back soon, or reach us on +254 110 304 521.', 'sermons' => array() ), 200 );
		}

		$dck_lang = isset( $p['lang'] ) ? sanitize_key( $p['lang'] ) : '';
		$messages = array( array( 'role' => 'system', 'content' => dck_ai_bible_prompt( $level ) ) );
		$dck_note = function_exists( 'dck_ai_lang_note' ) ? dck_ai_lang_note( $dck_lang ) : '';
		if ( $dck_note ) { $messages[] = array( 'role' => 'system', 'content' => $dck_note ); }
		$messages[] = array( 'role' => 'user', 'content' => $query );
		$dck_ck     = 'dck_bible_' . md5( $query . '|' . $level . '|' . $dck_lang );
		$dck_cached = get_transient( $dck_ck );
		if ( is_string( $dck_cached ) && '' !== $dck_cached ) {
			$sermons = function_exists( 'dck_ai_find_sermons' ) ? dck_ai_find_sermons( $query, 3 ) : array();
			return new WP_REST_Response( array( 'reply' => $dck_cached, 'sermons' => $sermons ), 200 );
		}
		$reply = dck_ai_chat( $messages, array( 'max_tokens' => 950, 'temperature' => 0.5 ) );
		if ( is_wp_error( $reply ) ) {
			if ( current_user_can( 'manage_options' ) ) {
					return new WP_REST_Response( array( 'reply' => '[admin diagnostic] ' . $reply->get_error_message() . ' - open Appearance > Customize > AI Assistant and confirm the API key, Model, and API Base URL match your provider.', 'sermons' => array() ), 200 );
				}
				return new WP_REST_Response( array( 'reply' => 'I had trouble responding just now. Please try again shortly, or reach us on +254 110 304 521.', 'sermons' => array() ), 200 );
		}

		set_transient( $dck_ck, $reply, 12 * HOUR_IN_SECONDS );
		$sermons = function_exists( 'dck_ai_find_sermons' ) ? dck_ai_find_sermons( $query, 3 ) : array();
		return new WP_REST_Response( array( 'reply' => $reply, 'sermons' => $sermons ), 200 );
	}
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'dck/v1', '/bible', array(
		'methods'             => 'POST',
		'callback'            => 'dck_ai_rest_bible',
		'permission_callback' => '__return_true',
	) );
} );

if ( ! function_exists( 'dck_ai_bible_enqueue' ) ) {
	function dck_ai_bible_enqueue() {
		if ( ! is_page_template( 'template-bible.php' ) ) { return; }
		wp_enqueue_script( 'dck-bible', get_template_directory_uri() . '/assets/js/bible.js', array(), DCK_ELITE_VERSION, true );
		wp_localize_script( 'dck-bible', 'DCK_BIBLE', array(
			'rest'  => esc_url_raw( rest_url( 'dck/v1/' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'dck_ai_bible_enqueue' );

/* Ensure a Bible item exists in the primary menu for sites upgrading in place. */
if ( ! function_exists( 'dck_ai_ensure_bible_menu' ) ) {
	function dck_ai_ensure_bible_menu() {
		$menu = wp_get_nav_menu_object( 'DCK Primary' );
		if ( ! $menu ) { return; }
		$items = wp_get_nav_menu_items( $menu->term_id );
		if ( empty( $items ) ) { return; }
		foreach ( $items as $it ) {
			if ( 'Bible' === $it->title ) { return; }
		}
		$url = function_exists( 'dck_elite_page_url' ) ? dck_elite_page_url( 'bible' ) : home_url( '/bible/' );
		wp_update_nav_menu_item( $menu->term_id, 0, array(
			'menu-item-title'   => 'Bible',
			'menu-item-url'     => $url,
			'menu-item-status'  => 'publish',
			'menu-item-type'    => 'custom',
		) );
	}
}
add_action( 'admin_init', 'dck_ai_ensure_bible_menu' );
