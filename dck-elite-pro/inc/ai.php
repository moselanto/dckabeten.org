<?php
/**
 * DCK Shared AI Layer.
 *
 * One secure service every AI feature plugs into: key storage, chat calls,
 * retrieval (RAG) over the church's own approved content, guardrails, and
 * REST endpoints for the front-end assistant and prayer requests.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------- Settings (Customizer) ---------------- */

if ( ! function_exists( 'dck_ai_customize' ) ) {
	function dck_ai_customize( $wp_customize ) {
		$wp_customize->add_section(
			'dck_ai',
			array(
				'title'       => __( 'DCK AI Assistant', 'dck-elite' ),
				'priority'    => 31,
				'description' => __( 'Connect OpenAI to power the AI Church Assistant. Your key is stored on your server and never shown to visitors.', 'dck-elite' ),
			)
		);

		$wp_customize->add_setting( 'dck_ai_enabled', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'dck_ai_enabled', array( 'label' => __( 'Enable AI Assistant', 'dck-elite' ), 'section' => 'dck_ai', 'type' => 'checkbox' ) );

		$wp_customize->add_setting( 'dck_ai_key', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'dck_ai_key', array( 'label' => __( 'OpenAI API Key', 'dck-elite' ), 'description' => __( 'Paste your key (starts with sk-). For extra security you may instead define DCK_AI_KEY in wp-config.php.', 'dck-elite' ), 'section' => 'dck_ai', 'type' => 'password' ) );

		$wp_customize->add_setting( 'dck_ai_model', array( 'default' => 'gemini-flash-latest', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'dck_ai_model', array( 'label' => __( 'Model', 'dck-elite' ), 'description' => __( 'Recommended: gemini-flash-latest (Google free tier, always the current Gemini Flash, so it never gets retired). For OpenAI set gpt-4o-mini; on Groq use llama-3.3-70b-versatile. Match the API Base URL below to your provider.', 'dck-elite' ), 'section' => 'dck_ai', 'type' => 'text' ) );

		$wp_customize->add_setting( 'dck_ai_base', array( 'default' => 'https://generativelanguage.googleapis.com/v1beta/openai', 'sanitize_callback' => 'esc_url_raw' ) );
		$wp_customize->add_control( 'dck_ai_base', array( 'label' => __( 'API Base URL', 'dck-elite' ), 'description' => __( 'Advanced. Default is Google Gemini (free, no card): https://generativelanguage.googleapis.com/v1beta/openai with Model gemini-flash-latest. For OpenAI use https://api.openai.com/v1 and Model gpt-4o-mini. Groq: https://api.groq.com/openai/v1 . OpenRouter: https://openrouter.ai/api/v1', 'dck-elite' ), 'section' => 'dck_ai', 'type' => 'text' ) );

		$wp_customize->add_setting( 'dck_ai_stt_base', array( 'default' => 'https://api.groq.com/openai/v1', 'sanitize_callback' => 'esc_url_raw' ) );
		$wp_customize->add_control( 'dck_ai_stt_base', array( 'label' => __( 'Transcription API Base URL', 'dck-elite' ), 'description' => __( 'For sermon and voice transcription only (Whisper). Gemini has no speech-to-text, so this is kept separate from chat. Default is Groq (free, no card): https://api.groq.com/openai/v1', 'dck-elite' ), 'section' => 'dck_ai', 'type' => 'text' ) );

		$wp_customize->add_setting( 'dck_ai_stt_key', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'dck_ai_stt_key', array( 'label' => __( 'Transcription API Key', 'dck-elite' ), 'description' => __( 'Your Groq API key from console.groq.com (free). Leave blank to reuse the main API Key above.', 'dck-elite' ), 'section' => 'dck_ai', 'type' => 'text' ) );

		$wp_customize->add_setting( 'dck_ai_stt_model', array( 'default' => 'whisper-large-v3', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'dck_ai_stt_model', array( 'label' => __( 'Transcription Model', 'dck-elite' ), 'description' => __( 'On Groq use whisper-large-v3 (or whisper-large-v3-turbo). On OpenAI use whisper-1.', 'dck-elite' ), 'section' => 'dck_ai', 'type' => 'text' ) );

		$wp_customize->add_setting( 'dck_ai_name', array( 'default' => 'Hope', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'dck_ai_name', array( 'label' => __( 'Assistant Name', 'dck-elite' ), 'section' => 'dck_ai', 'type' => 'text' ) );

		$wp_customize->add_setting( 'dck_ai_welcome', array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field' ) );
		$wp_customize->add_control( 'dck_ai_welcome', array( 'label' => __( 'Welcome Message', 'dck-elite' ), 'section' => 'dck_ai', 'type' => 'textarea' ) );

		$wp_customize->add_setting( 'dck_ai_prayer_email', array( 'default' => '', 'sanitize_callback' => 'sanitize_email' ) );
		$wp_customize->add_control( 'dck_ai_prayer_email', array( 'label' => __( 'Prayer Team Email', 'dck-elite' ), 'description' => __( 'Where prayer requests are sent. Defaults to the site admin email.', 'dck-elite' ), 'section' => 'dck_ai', 'type' => 'email' ) );

		$wp_customize->add_setting( 'dck_prayer_whatsapp', array( 'default' => '254110304521', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'dck_prayer_whatsapp', array( 'label' => __( 'Pastor WhatsApp Number', 'dck-elite' ), 'description' => __( 'Digits only with country code, e.g. 254110304521. Prayer requests and pastoral chats hand off to this WhatsApp.', 'dck-elite' ), 'section' => 'dck_ai', 'type' => 'text' ) );
	}
}
add_action( 'customize_register', 'dck_ai_customize' );

if ( ! function_exists( 'dck_prayer_whatsapp' ) ) {
	function dck_prayer_whatsapp() {
		$w = preg_replace( '/[^0-9]/', '', (string) get_theme_mod( 'dck_prayer_whatsapp', '254110304521' ) );
		return $w ? $w : '254110304521';
	}
}

/* ---------------- Core helpers ---------------- */

if ( ! function_exists( 'dck_ai_get_key' ) ) {
	function dck_ai_get_key() {
		if ( defined( 'DCK_AI_KEY' ) && DCK_AI_KEY ) {
			return DCK_AI_KEY;
		}
		return trim( (string) get_theme_mod( 'dck_ai_key', '' ) );
	}
}

if ( ! function_exists( 'dck_ai_enabled' ) ) {
	function dck_ai_enabled() {
		return (bool) get_theme_mod( 'dck_ai_enabled', '' );
	}
}

if ( ! function_exists( 'dck_ai_base_url' ) ) {
	function dck_ai_base_url() {
		$b = trim( (string) get_theme_mod( 'dck_ai_base', 'https://generativelanguage.googleapis.com/v1beta/openai' ) );
		if ( '' === $b ) { $b = 'https://generativelanguage.googleapis.com/v1beta/openai'; }
		return untrailingslashit( $b );
	}
}

if ( ! function_exists( 'dck_ai_stt_base_url' ) ) {
	function dck_ai_stt_base_url() {
		$b = trim( (string) get_theme_mod( 'dck_ai_stt_base', 'https://api.groq.com/openai/v1' ) );
		if ( '' === $b ) { return dck_ai_base_url(); }
		return untrailingslashit( $b );
	}
}

if ( ! function_exists( 'dck_ai_stt_key' ) ) {
	function dck_ai_stt_key() {
		if ( defined( 'DCK_AI_STT_KEY' ) && DCK_AI_STT_KEY ) { return DCK_AI_STT_KEY; }
		$k = trim( (string) get_theme_mod( 'dck_ai_stt_key', '' ) );
		if ( '' === $k ) { $k = dck_ai_get_key(); }
		return $k;
	}
}

if ( ! function_exists( 'dck_ai_stt_model' ) ) {
	function dck_ai_stt_model() {
		$m = trim( (string) get_theme_mod( 'dck_ai_stt_model', 'whisper-large-v3' ) );
		if ( '' === $m ) { $m = 'whisper-large-v3'; }
		return $m;
	}
}

/**
 * Central chat call. Every AI feature uses this one function.
 *
 * @param array $messages Array of role/content messages.
 * @return string|WP_Error
 */
if ( ! function_exists( 'dck_ai_chat' ) ) {
	function dck_ai_chat( $messages, $opts = array() ) {
		$key = dck_ai_get_key();
		if ( ! $key ) {
			return new WP_Error( 'dck_ai_no_key', 'No API key configured.' );
		}
		$model = isset( $opts['model'] ) ? $opts['model'] : get_theme_mod( 'dck_ai_model', 'gemini-flash-latest' );
		$body  = array(
			'model'       => $model ? $model : 'gemini-flash-latest',
			'messages'    => $messages,
			'temperature' => isset( $opts['temperature'] ) ? $opts['temperature'] : 0.6,
			'max_tokens'  => isset( $opts['max_tokens'] ) ? $opts['max_tokens'] : 2048,
		);
		$resp = wp_remote_post(
			dck_ai_base_url() . '/chat/completions',
			array(
				'timeout' => 45,
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $key,
				),
				'body'    => wp_json_encode( $body ),
			)
		);
		if ( is_wp_error( $resp ) ) {
			return $resp;
		}
		$code = (int) wp_remote_retrieve_response_code( $resp );
		$raw  = wp_remote_retrieve_body( $resp );
		$data = json_decode( $raw, true );
		if ( $code < 200 || $code >= 300 ) {
			$emsg = '';
			if ( isset( $data['error']['message'] ) ) { $emsg = $data['error']['message']; }
			elseif ( isset( $data[0]['error']['message'] ) ) { $emsg = $data[0]['error']['message']; }
			if ( '' === $emsg ) { $emsg = substr( (string) $raw, 0, 300 ); }
			return new WP_Error( 'dck_ai_api', 'HTTP ' . $code . ': ' . $emsg );
		}
		if ( empty( $data['choices'][0]['message']['content'] ) ) {
			$fr = isset( $data['choices'][0]['finish_reason'] ) ? $data['choices'][0]['finish_reason'] : 'unknown';
			return new WP_Error( 'dck_ai_empty', 'Empty content (finish_reason: ' . $fr . '). Raise max_tokens.' );
		}
		return trim( $data['choices'][0]['message']['content'] );
	}
}

/**
 * Retrieval (RAG-lite): church facts + relevant published content snippets.
 */
if ( ! function_exists( 'dck_ai_knowledge' ) ) {
	function dck_ai_knowledge( $query ) {
		$facts = "Church: Deliverance Church Kabete N Market (Cistern of Hope Center), Uthiru, Nairobi, Kenya.
Location: Behind N Market, Uthiru, Nairobi. Phone: +254 110 304 521.
Lead Pastors: Pastor Justus Kubai and Mrs. Clare Kubai. Assistant Pastors: Mr. and Mrs. Henry Onzere.
Sunday services: Intensive Prayers 8:00 to 9:00 AM; Intensive Bible Study 9:30 to 10:30 AM; Glorious Service 10:40 AM to 1:00 PM. Keshas (overnight prayer) on the 1st and 3rd week of the month.
Ministries: Men of Faith (married men), Daughters of Faith (women), Youth on the Move (youth), Golden Gift or Double G (singles), Flames of Fire (worship team), Cistern of Hope Voices (choir), Dunamis (teens 13 to 19), Conquerors (children 7 to 12), Champions of Christ (children 3 to 6), Missions and Evangelism, Media Team, Shield Crew (security), Welfare, Care Ministry (ushering and hospitality), Protocol.
Giving: M-Pesa Paybill 247247 Account 403263 (Tithes and Offerings); M-Pesa Till 9900619 (Give to a Project); Equity Bank, Account Name Deliverance Church Kabete Technical, Account Number 1370274104398, SWIFT EQBLKENA, Branch Kangemi.
Watch Live: services stream on the Watch Live page on Sundays.";

		$facts .= PHP_EOL . 'Talk to or pray with a pastor: WhatsApp https://wa.me/' . dck_prayer_whatsapp() . ' or call +254 110 304 521. Prayer requests submitted on the Prayer Request page reach the prayer team and pastors.';

		$extra = '';
		$q     = new WP_Query(
			array(
				's'              => $query,
				'post_type'      => array( 'page', 'dck_sermon', 'post' ),
				'posts_per_page' => 3,
				'no_found_rows'  => true,
				'post_status'    => 'publish',
			)
		);
		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$extra .= '- ' . get_the_title() . ': ' . wp_trim_words( wp_strip_all_tags( get_the_content() ), 40 ) . ' (' . get_permalink() . ')' . PHP_EOL;
			}
			wp_reset_postdata();
		}
		if ( $extra ) {
			$facts .= PHP_EOL . 'Relevant content:' . PHP_EOL . $extra;
		}
		return $facts;
	}
}

if ( ! function_exists( 'dck_ai_system_prompt' ) ) {
	function dck_ai_system_prompt( $query ) {
		$name  = get_theme_mod( 'dck_ai_name', 'Hope' );
		$know  = dck_ai_knowledge( $query );
		return "You are " . $name . ", the warm and caring AI assistant for Deliverance Church Kabete N Market (Cistern of Hope Center) in Uthiru, Nairobi, Kenya.

Purpose: welcome visitors, answer questions about the church, and help people take a next step in faith. Be warm, hopeful, concise, and never pushy. Use simple, encouraging English; a little Kiswahili warmth like Karibu is welcome.

Care guidelines:
- If someone shares distress (depression, grief, anxiety, loss, or thoughts of self-harm), respond with compassion, remind them they are deeply loved by God and not alone, offer one encouraging scripture, and gently offer to submit a confidential prayer request or connect them with a pastor. If there is any risk to life, urge them to reach local emergency services or a trusted person right away.
- If someone wants to know Jesus or give their life to Christ, gently affirm them, share John 3:16 and Romans 10:9, lead a short prayer of salvation, and offer to connect them with the church for a new believers class and follow-up.
- For prayer requests, invite them to share and assure them the prayer team will pray. Mention the confidential option.
- Only state facts about the church that appear in the KNOWLEDGE below. If you do not know something, say so kindly and share the phone number +254 110 304 521 or the Contact page. Never invent service times, events, names, or giving details.
- Keep replies short (2 to 5 sentences) unless more is requested. When helpful, offer a next step: Plan Your Visit, Watch Live, Give, or a Prayer Request.
- Write in plain, warm text. Do NOT use Markdown, asterisks, bullet symbols, or headings; use short sentences or simple line breaks, because the chat shows plain text.
- NEVER output placeholder text or square brackets such as [Insert Service Times] or [Insert Church Location]. Always give the ACTUAL service times and location from KNOWLEDGE. If a detail is not in KNOWLEDGE, leave it out or share the phone number - never a blank template.
- If someone wants to pray with a pastor, talk to a pastor, book a visit, or asks how their request reaches the pastor, give them the direct WhatsApp link https://wa.me/" . dck_prayer_whatsapp() . " and the phone +254 110 304 521, and mention the Prayer Request page. Reassure them a real person will receive it.

KNOWLEDGE:
" . $know;
	}
}

if ( ! function_exists( 'dck_ai_fallback_reply' ) ) {
	function dck_ai_fallback_reply() {
		return "Karibu, and thank you for reaching out. Our AI assistant is being set up. In the meantime: we gather every Sunday for Intensive Prayers at 8:00 AM, Bible Study at 9:30 AM, and the Glorious Service at 10:40 AM, behind N Market in Uthiru. You can call us on +254 110 304 521, or use the Plan Your Visit page. We would love to welcome you.";
	}
}

/* ---------------- Rate limiting ---------------- */

if ( ! function_exists( 'dck_ai_rate_ok' ) ) {
	function dck_ai_rate_ok() {
		$ip  = function_exists( 'dck_sec_client_ip' ) ? dck_sec_client_ip() : ( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'x' );
		$key = 'dck_ai_rl_' . md5( $ip );
		$n   = (int) get_transient( $key );
		if ( $n >= 25 ) {
			return false;
		}
		set_transient( $key, $n + 1, 10 * MINUTE_IN_SECONDS );
		return true;
	}
}

/* ---------------- Prayer Requests CPT ---------------- */

if ( ! function_exists( 'dck_ai_register_prayer_cpt' ) ) {
	function dck_ai_register_prayer_cpt() {
		register_post_type(
			'dck_prayer',
			array(
				'labels'    => array(
					'name'          => __( 'Prayer Requests', 'dck-elite' ),
					'singular_name' => __( 'Prayer Request', 'dck-elite' ),
				),
				'public'    => false,
				'show_ui'   => true,
				'menu_icon' => 'dashicons-heart',
				'supports'  => array( 'title', 'editor' ),
			)
		);
	}
}
add_action( 'init', 'dck_ai_register_prayer_cpt' );

/* ---------------- REST endpoints ---------------- */

if ( ! function_exists( 'dck_ai_register_routes' ) ) {
	function dck_ai_register_routes() {
		register_rest_route( 'dck/v1', '/assistant', array(
			'methods'             => 'POST',
			'callback'            => 'dck_ai_rest_assistant',
			'permission_callback' => '__return_true',
		) );
		register_rest_route( 'dck/v1', '/prayer', array(
			'methods'             => 'POST',
			'callback'            => 'dck_ai_rest_prayer',
			'permission_callback' => '__return_true',
		) );
	}
}
add_action( 'rest_api_init', 'dck_ai_register_routes' );

if ( ! function_exists( 'dck_ai_rest_assistant' ) ) {
	function dck_ai_rest_assistant( $request ) {
		if ( ! dck_ai_rate_ok() ) {
			return new WP_REST_Response( array( 'reply' => 'You are sending messages quite fast. Please try again in a few minutes.' ), 200 );
		}
		$params   = $request->get_json_params();
		$user_msg = isset( $params['message'] ) ? (string) $params['message'] : '';
		$user_msg = trim( wp_strip_all_tags( $user_msg ) );
		if ( strlen( $user_msg ) > 1500 ) {
			$user_msg = substr( $user_msg, 0, 1500 );
		}
		if ( '' === $user_msg ) {
			return new WP_REST_Response( array( 'reply' => 'How can I help you today?' ), 200 );
		}
		if ( ! dck_ai_get_key() ) {
			return new WP_REST_Response( array( 'reply' => dck_ai_fallback_reply() ), 200 );
		}

		$messages   = array();
		$messages[] = array( 'role' => 'system', 'content' => dck_ai_system_prompt( $user_msg ) );
		$dck_lang = isset( $params['lang'] ) ? sanitize_key( $params['lang'] ) : '';
		$dck_note = function_exists( 'dck_ai_lang_note' ) ? dck_ai_lang_note( $dck_lang ) : '';
		if ( $dck_note ) { $messages[] = array( 'role' => 'system', 'content' => $dck_note ); }
		$history    = ( isset( $params['messages'] ) && is_array( $params['messages'] ) ) ? array_slice( $params['messages'], -6 ) : array();
		foreach ( $history as $h ) {
			if ( isset( $h['role'], $h['content'] ) && in_array( $h['role'], array( 'user', 'assistant' ), true ) ) {
				$messages[] = array( 'role' => $h['role'], 'content' => wp_strip_all_tags( (string) $h['content'] ) );
			}
		}
		$messages[] = array( 'role' => 'user', 'content' => $user_msg );

		$reply = dck_ai_chat( $messages );
		if ( is_wp_error( $reply ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				return new WP_REST_Response( array( 'reply' => '[admin diagnostic] ' . $reply->get_error_message() ), 200 );
			}
			return new WP_REST_Response( array( 'reply' => 'I had trouble responding just now. Please try again, or call us on +254 110 304 521.' ), 200 );
		}
		return new WP_REST_Response( array( 'reply' => $reply ), 200 );
	}
}

if ( ! function_exists( 'dck_ai_rest_prayer' ) ) {
	function dck_ai_rest_prayer( $request ) {
		if ( ! dck_ai_rate_ok() ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => 'Please try again shortly.' ), 200 );
		}
		$p       = $request->get_json_params();
		if ( function_exists( 'dck_sec_guard' ) ) {
			$dck_guard = dck_sec_guard( $request, $p, 'prayer' );
			if ( true !== $dck_guard ) { return $dck_guard; }
		}
		$name    = isset( $p['name'] ) ? sanitize_text_field( $p['name'] ) : '';
		$email   = isset( $p['email'] ) ? sanitize_email( $p['email'] ) : '';
		$phone   = isset( $p['phone'] ) ? sanitize_text_field( $p['phone'] ) : '';
		$req     = isset( $p['request'] ) ? sanitize_textarea_field( $p['request'] ) : '';
		if ( function_exists( 'dck_sec_cap' ) ) {
			$name  = dck_sec_cap( $name, 120 );
			$email = dck_sec_cap( $email, 190 );
			$phone = dck_sec_cap( $phone, 40 );
			$req   = dck_sec_cap( $req, 5000 );
		}
		$dck_lang = isset( $p['lang'] ) ? sanitize_key( $p['lang'] ) : '';
		$private = ! empty( $p['private'] );
		if ( '' === trim( $name ) || '' === trim( $phone ) || '' === trim( $email ) || ! is_email( $email ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => 'Please enter your name, phone number, and a valid email so our pastors can reach you.' ), 200 );
		}
		if ( '' === trim( $req ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'message' => 'Please share your prayer request so we can pray with you.' ), 200 );
		}
		if ( ( function_exists( 'dck_sec_looks_spammy' ) && dck_sec_looks_spammy( $req . ' ' . $name ) ) || ( function_exists( 'dck_sec_email_disposable' ) && dck_sec_email_disposable( $email ) ) ) {
			return new WP_REST_Response( array( 'ok' => true, 'message' => 'Thank you. Our prayer team has received your request.' ), 200 );
		}
		$who   = $private ? 'Confidential' : ( $name ? $name : 'Anonymous' );
		$title = 'Prayer Request - ' . $who . ' - ' . current_time( 'Y-m-d H:i' );
		$body  = 'Name: ' . ( $name ? $name : 'Anonymous' ) . PHP_EOL;
		$body .= 'Phone: ' . $phone . PHP_EOL;
		$body .= 'Email: ' . $email . PHP_EOL;
		$body .= $private ? '[Confidential request - pastors only]' . PHP_EOL : '';
		$body .= PHP_EOL . $req;
		wp_insert_post( array(
			'post_type'    => 'dck_prayer',
			'post_title'   => $title,
			'post_content' => $body,
			'post_status'  => 'private',
		) );
		$dck_rec  = array();
		$dck_team = trim( (string) get_theme_mod( 'dck_ai_prayer_email', '' ) );
		if ( $dck_team ) { $dck_rec[] = $dck_team; }
		$dck_admin = get_option( 'admin_email' );
		if ( $dck_admin ) { $dck_rec[] = $dck_admin; }
		$dck_rec = array_values( array_unique( array_filter( $dck_rec ) ) );
		if ( ! empty( $dck_rec ) ) {
			wp_mail( $dck_rec, 'Prayer Request', $body );
		}
		$wa_url = 'https://wa.me/' . dck_prayer_whatsapp() . '?text=' . rawurlencode( 'Prayer Request from ' . ( $name ? $name : 'a website visitor' ) . ': ' . $req );
		$msg = ( 'sw' === $dck_lang )
			? 'Asante. Timu yetu ya maombi imepokea ombi lako na tutakuombea. Haupo peke yako.'
			: 'Thank you. Our prayer team has received your request and will be praying with you. You are not alone.';
		return new WP_REST_Response( array( 'ok' => true, 'message' => $msg, 'whatsapp' => $wa_url ), 200 );
	}
}

/* ---------------- Front-end enqueue ---------------- */

if ( ! function_exists( 'dck_ai_enqueue' ) ) {
	function dck_ai_enqueue() {
		if ( ! dck_ai_enabled() ) {
			return;
		}
		wp_enqueue_script( 'dck-ai', get_template_directory_uri() . '/assets/js/assistant.js', array( 'dck-voice' ), DCK_ELITE_VERSION, true );
		$welcome = get_theme_mod( 'dck_ai_welcome', '' );
		if ( ! $welcome ) {
			$welcome = 'Hi, I am here to help. Ask me anything about our church, or how to plan a visit, watch live, give, or request prayer.';
		}
		$welcome_sw = 'Karibu. Niko hapa kukusaidia. Niulize lolote kuhusu kanisa letu, jinsi ya kupanga ziara, kutazama moja kwa moja, kutoa, au kuomba maombi.';
		wp_localize_script( 'dck-ai', 'DCK_AI', array(
			'rest'    => esc_url_raw( rest_url( 'dck/v1/' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'name'    => get_theme_mod( 'dck_ai_name', 'Hope' ),
			'welcome' => $welcome,
			'welcome_sw' => $welcome_sw,
			'lang'    => get_theme_mod( 'dck_ai_lang', 'sw' ),
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'dck_ai_enqueue' );
