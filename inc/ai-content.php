<?php
/* DCK Elite - AI Content Studio: sermon summaries + content generator (admin). */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Gather a faithful text context from a sermon post. */
if ( ! function_exists( 'dck_ai_sermon_context' ) ) {
	function dck_ai_sermon_context( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) { return ''; }
		$title     = get_the_title( $post_id );
		$scripture = get_post_meta( $post_id, '_dck_scripture', true );
		$speaker   = wp_strip_all_tags( get_the_term_list( $post_id, 'dck_sermon_speaker', '', ', ', '' ) );
		$series    = wp_strip_all_tags( get_the_term_list( $post_id, 'dck_sermon_series', '', ', ', '' ) );
		$transcript = get_post_meta( $post_id, '_dck_transcript', true );
		$src        = $transcript ? $transcript : (string) $post->post_content;
		$body       = trim( wp_strip_all_tags( $src ) );
		if ( strlen( $body ) > 12000 ) { $body = substr( $body, 0, 12000 ); }

		$ctx = 'Sermon title: ' . $title . PHP_EOL;
		if ( $scripture ) { $ctx .= 'Main scripture: ' . $scripture . PHP_EOL; }
		if ( $speaker ) { $ctx .= 'Speaker: ' . $speaker . PHP_EOL; }
		if ( $series ) { $ctx .= 'Series: ' . $series . PHP_EOL; }
		if ( strlen( $body ) > 30 ) {
			$ctx .= 'Sermon notes or transcript:' . PHP_EOL . $body;
		} else {
			$ctx .= 'Note: detailed notes were not provided. Base your work faithfully on the title and scripture above and keep it general.';
		}
		return $ctx;
	}
}

/* System prompt per content kind. */
if ( ! function_exists( 'dck_ai_content_prompt' ) ) {
	function dck_ai_content_prompt( $kind ) {
		$base = 'You are a faithful content assistant for Deliverance Church Kabete N Market (Cistern of Hope Center) in Uthiru, Nairobi. Work only from the sermon details provided. Stay biblically faithful, warm, and clear. If the notes are thin, base your work on the title and scripture and stay general rather than inventing specifics. ';
		if ( 'summary' === $kind ) {
			return $base . 'Write a warm two to three sentence summary of the sermon. Then, on a new line, write "## Key Takeaways" followed by three to five short bullet points, each starting with "- ". Plain text only.';
		}
		if ( 'study_guide' === $kind ) {
			return $base . 'Create a small-group study guide using these labelled sections, each heading on its own line starting with two hash marks: ## Overview (two sentences), ## Key Scripture (list the main passages), ## Discussion Questions (four to five questions as "- " bullets), ## Application (one practical challenge), ## Closing Prayer (two to three sentences). Plain text only.';
		}
		if ( 'questions' === $kind ) {
			return $base . 'Write five thoughtful small-group discussion questions based on the sermon. Return them as "- " bullet points, plain text only.';
		}
		if ( 'social' === $kind ) {
			return $base . 'Write three ready-to-post social media captions from the sermon, each under its own heading line starting with two hash marks: ## Facebook (two to three warm sentences with an invitation), ## X (under 220 characters), ## Instagram (a caption plus five relevant hashtags). Plain text only.';
		}
		if ( 'devotional' === $kind ) {
			return $base . 'Create a five-day personal devotional from the sermon. For each day use a heading line starting with two hash marks, for example "## Day 1", then a Scripture reference, a two to three sentence reflection, and a one sentence prayer. Plain text only.';
		}
		return '';
	}
}

/* Admin-only generation endpoint. */
if ( ! function_exists( 'dck_ai_rest_generate' ) ) {
	function dck_ai_rest_generate( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_REST_Response( array( 'text' => 'You are not allowed to do that.' ), 403 );
		}
		$p       = $request->get_json_params();
		$post_id = isset( $p['post_id'] ) ? intval( $p['post_id'] ) : 0;
		$kind    = isset( $p['kind'] ) ? sanitize_key( $p['kind'] ) : '';
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_REST_Response( array( 'text' => 'Please save the sermon as a draft first, then generate.' ), 200 );
		}
		if ( ! function_exists( 'dck_ai_get_key' ) || ! dck_ai_get_key() ) {
			return new WP_REST_Response( array( 'text' => 'Add your OpenAI key in Appearance, Customize, DCK AI Assistant to enable generation.' ), 200 );
		}
		$sys = dck_ai_content_prompt( $kind );
		if ( '' === $sys ) { return new WP_REST_Response( array( 'text' => 'Unknown content type.' ), 200 ); }
		$ln = function_exists( 'dck_ai_lang_note' ) ? dck_ai_lang_note( isset( $p['lang'] ) ? sanitize_key( $p['lang'] ) : '' ) : '';
		if ( $ln ) { $sys .= ' ' . $ln; }

		$messages = array(
			array( 'role' => 'system', 'content' => $sys ),
			array( 'role' => 'user', 'content' => dck_ai_sermon_context( $post_id ) ),
		);
		$max = ( 'study_guide' === $kind || 'devotional' === $kind ) ? 950 : 550;
		$out = dck_ai_chat( $messages, array( 'max_tokens' => $max, 'temperature' => 0.6 ) );
		if ( is_wp_error( $out ) ) {
			return new WP_REST_Response( array( 'text' => 'Generation failed. Please try again shortly.' ), 200 );
		}
		return new WP_REST_Response( array( 'text' => $out ), 200 );
	}
}
add_action( 'rest_api_init', function () {
	register_rest_route( 'dck/v1', '/generate', array(
		'methods'             => 'POST',
		'callback'            => 'dck_ai_rest_generate',
		'permission_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
} );

/* Meta box on the sermon editor. */
if ( ! function_exists( 'dck_ai_studio_box' ) ) {
	function dck_ai_studio_box( $post ) {
		wp_nonce_field( 'dck_ai_studio', 'dck_ai_studio_nonce' );
		$summary = get_post_meta( $post->ID, '_dck_summary', true );
		$guide   = get_post_meta( $post->ID, '_dck_study_guide', true );
		$has_key = ( function_exists( 'dck_ai_get_key' ) && dck_ai_get_key() );
		echo '<div class="dck-studio">';
		if ( ! $has_key ) {
			echo '<p class="dck-studio-warn">Add your OpenAI key in Appearance, Customize, DCK AI Assistant to enable one-click generation. You can still write these fields by hand.</p>';
		}
		echo '<p><label><strong>Language:</strong> <select id="dck_gen_lang"><option value="">Auto</option><option value="en">English</option><option value="sw">Kiswahili</option></select></label></p>';
		echo '<p><strong>Sermon Summary</strong> <button type="button" class="button dck-gen" data-kind="summary" data-target="dck_summary_fld">Generate</button> <span class="spinner dck-sp"></span></p>';
		echo '<textarea id="dck_summary_fld" name="dck_summary" rows="5" class="widefat">' . esc_textarea( $summary ) . '</textarea>';
		echo '<p class="description">Shown on the public sermon page. A short summary plus a "## Key Takeaways" list works best.</p>';

		echo '<p style="margin-top:16px"><strong>Small-Group Study Guide</strong> <button type="button" class="button dck-gen" data-kind="study_guide" data-target="dck_guide_fld">Generate</button> <span class="spinner dck-sp"></span></p>';
		echo '<textarea id="dck_guide_fld" name="dck_study_guide" rows="6" class="widefat">' . esc_textarea( $guide ) . '</textarea>';
		echo '<p class="description">Shown in a collapsible section on the sermon page.</p>';

		echo '<hr><p><strong>Content generator</strong> (copy and paste into bulletins, WhatsApp, and social media):</p>';
		echo '<p><button type="button" class="button dck-gen" data-kind="questions" data-target="dck_scratch">Discussion Questions</button> ';
		echo '<button type="button" class="button dck-gen" data-kind="social" data-target="dck_scratch">Social Posts</button> ';
		echo '<button type="button" class="button dck-gen" data-kind="devotional" data-target="dck_scratch">5-Day Devotional</button> <span class="spinner dck-sp"></span></p>';
		echo '<textarea id="dck_scratch" rows="7" class="widefat" placeholder="Generated content appears here for you to copy..."></textarea>';
		echo '<p><button type="button" class="button dck-copy" data-target="dck_scratch">Copy</button></p>';
		echo '</div>';
	}
}
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'dck_ai_studio', 'AI Content Studio', 'dck_ai_studio_box', 'dck_sermon', 'normal', 'high' );
} );

/* Save the two stored fields. */
if ( ! function_exists( 'dck_ai_studio_save' ) ) {
	function dck_ai_studio_save( $post_id ) {
		if ( ! isset( $_POST['dck_ai_studio_nonce'] ) ) { return; }
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dck_ai_studio_nonce'] ) ), 'dck_ai_studio' ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
		if ( isset( $_POST['dck_summary'] ) ) {
			update_post_meta( $post_id, '_dck_summary', sanitize_textarea_field( wp_unslash( $_POST['dck_summary'] ) ) );
		}
		if ( isset( $_POST['dck_study_guide'] ) ) {
			update_post_meta( $post_id, '_dck_study_guide', sanitize_textarea_field( wp_unslash( $_POST['dck_study_guide'] ) ) );
		}
	}
}
add_action( 'save_post_dck_sermon', 'dck_ai_studio_save' );

/* Admin assets, only on the sermon editor. */
if ( ! function_exists( 'dck_ai_studio_admin_assets' ) ) {
	function dck_ai_studio_admin_assets( $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) { return; }
		$screen = get_current_screen();
		if ( ! $screen || 'dck_sermon' !== $screen->post_type ) { return; }
		global $post;
		$pid = ( $post && isset( $post->ID ) ) ? intval( $post->ID ) : 0;
		wp_enqueue_style( 'dck-admin', get_template_directory_uri() . '/assets/css/admin.css', array(), DCK_ELITE_VERSION );
		wp_enqueue_script( 'dck-admin', get_template_directory_uri() . '/assets/js/content-admin.js', array(), DCK_ELITE_VERSION, true );
		wp_localize_script( 'dck-admin', 'DCK_STUDIO', array(
			'rest'   => esc_url_raw( rest_url( 'dck/v1/' ) ),
			'nonce'  => wp_create_nonce( 'wp_rest' ),
			'postId' => $pid,
		) );
	}
}
add_action( 'admin_enqueue_scripts', 'dck_ai_studio_admin_assets' );

/* Front-end: render stored AI text (## headings, - bullets) safely without regex. */
if ( ! function_exists( 'dck_ai_render_blocks' ) ) {
	function dck_ai_render_blocks( $text ) {
		$text  = str_replace( '**', '', (string) $text );
		$text  = str_replace( chr( 13 ), '', $text );
		$lines = explode( chr( 10 ), $text );
		$html  = '';
		$inlist = false;
		foreach ( $lines as $raw ) {
			$ln = trim( $raw );
			if ( '' === $ln ) {
				if ( $inlist ) { $html .= '</ul>'; $inlist = false; }
				continue;
			}
			if ( 0 === strpos( $ln, '## ' ) ) {
				if ( $inlist ) { $html .= '</ul>'; $inlist = false; }
				$html .= '<h4>' . esc_html( trim( substr( $ln, 3 ) ) ) . '</h4>';
			} elseif ( 0 === strpos( $ln, '- ' ) ) {
				if ( ! $inlist ) { $html .= '<ul>'; $inlist = true; }
				$html .= '<li>' . esc_html( trim( substr( $ln, 2 ) ) ) . '</li>';
			} else {
				if ( $inlist ) { $html .= '</ul>'; $inlist = false; }
				$html .= '<p>' . esc_html( $ln ) . '</p>';
			}
		}
		if ( $inlist ) { $html .= '</ul>'; }
		return $html;
	}
}
