<?php
/* DCK Elite - Swahili-first multilingual layer. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Language directive appended to AI system prompts. */
if ( ! function_exists( 'dck_ai_lang_note' ) ) {
	function dck_ai_lang_note( $lang ) {
		$lang = strtolower( trim( (string) $lang ) );
		if ( 'sw' === $lang ) {
			return 'Respond entirely in warm, natural Kiswahili as spoken in Kenya, regardless of the language of the question. Keep Bible book and chapter references in their standard form.';
		}
		if ( 'en' === $lang ) {
			return 'Respond in clear, warm English.';
		}
		return 'The congregation speaks both Kiswahili and English. Detect the language of the visitor message and reply in that same language. If the message mixes Swahili and English or uses Sheng, reply in warm, natural Kiswahili.';
	}
}

/* Default-language control, added to the existing DCK AI section. */
if ( ! function_exists( 'dck_ai_i18n_customize' ) ) {
	function dck_ai_i18n_customize( $wp_customize ) {
		$wp_customize->add_setting( 'dck_ai_lang', array( 'default' => 'sw', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'dck_ai_lang', array(
			'label'       => __( 'Default site language', 'dck-elite' ),
			'description' => __( 'Swahili-first is recommended for a Kiswahili-speaking congregation. Auto-detect lets the assistant mirror each visitor language. Visitors can switch with the on-page toggle.', 'dck-elite' ),
			'section'     => 'dck_ai',
			'type'        => 'select',
			'choices'     => array(
				'sw'   => 'Kiswahili (Swahili-first)',
				'en'   => 'English',
				'auto' => 'Auto-detect per visitor',
			),
		) );
	}
}
add_action( 'customize_register', 'dck_ai_i18n_customize' );

/* Enqueue the language toggle + translation script on the front end. */
if ( ! function_exists( 'dck_ai_i18n_enqueue' ) ) {
	function dck_ai_i18n_enqueue() {
		if ( is_admin() ) { return; }
		wp_enqueue_script( 'dck-i18n', get_template_directory_uri() . '/assets/js/i18n.js', array(), DCK_ELITE_VERSION, true );
		wp_localize_script( 'dck-i18n', 'DCK_I18N', array(
			'def' => get_theme_mod( 'dck_ai_lang', 'sw' ),
		) );
	}
}
/* v2.2.2: SW/EN toggle retired; full-site translation now via the top-bar language switcher. */
