<?php
/* DCK Elite - Sermon transcription pipeline (Whisper, timestamped). */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Core: download the sermon audio and transcribe with timestamps. */
if ( ! function_exists( 'dck_ai_do_transcribe' ) ) {
	function dck_ai_do_transcribe( $post_id ) {
		if ( function_exists( 'set_time_limit' ) ) { @set_time_limit( 0 ); }
		if ( function_exists( 'ignore_user_abort' ) ) { @ignore_user_abort( true ); }

		update_post_meta( $post_id, '_dck_transcript_status', 'processing' );
		update_post_meta( $post_id, '_dck_transcript_msg', '' );

		$url = get_post_meta( $post_id, '_dck_audio_url', true );
		if ( ! $url ) {
			return dck_ai_tr_fail( $post_id, 'Add an Audio URL (mp3) to this sermon and click Update, then transcribe.' );
		}
		if ( ! function_exists( 'dck_ai_stt_key' ) || ! dck_ai_stt_key() ) {
			return dck_ai_tr_fail( $post_id, 'Add a Transcription API Key (Groq) in Appearance, Customize, DCK AI Assistant first.' );
		}

		$get = wp_remote_get( $url, array( 'timeout' => 60 ) );
		if ( is_wp_error( $get ) || 200 !== (int) wp_remote_retrieve_response_code( $get ) ) {
			return dck_ai_tr_fail( $post_id, 'Could not download the audio. Please check the Audio URL is a direct link to an mp3.' );
		}
		$bin = wp_remote_retrieve_body( $get );
		if ( '' === $bin ) {
			return dck_ai_tr_fail( $post_id, 'The audio file was empty. Please check the Audio URL.' );
		}
		if ( strlen( $bin ) > 25000000 ) {
			return dck_ai_tr_fail( $post_id, 'Audio is larger than 25MB (the transcription limit). Please upload a compressed MP3 (about 64kbps mono) under 24MB and try again.' );
		}

		$eol      = chr( 13 ) . chr( 10 );
		$boundary = 'dcktr' . wp_generate_password( 16, false, false );
		$body  = '--' . $boundary . $eol . 'Content-Disposition: form-data; name="model"' . $eol . $eol . dck_ai_stt_model() . $eol;
		$body .= '--' . $boundary . $eol . 'Content-Disposition: form-data; name="response_format"' . $eol . $eol . 'verbose_json' . $eol;
		$body .= '--' . $boundary . $eol . 'Content-Disposition: form-data; name="file"; filename="sermon.mp3"' . $eol . 'Content-Type: audio/mpeg' . $eol . $eol . $bin . $eol;
		$body .= '--' . $boundary . '--' . $eol;

		$resp = wp_remote_post( dck_ai_stt_base_url() . '/audio/transcriptions', array(
			'timeout' => 300,
			'headers' => array(
				'Authorization' => 'Bearer ' . dck_ai_stt_key(),
				'Content-Type'  => 'multipart/form-data; boundary=' . $boundary,
			),
			'body'    => $body,
		) );
		if ( is_wp_error( $resp ) || 200 !== (int) wp_remote_retrieve_response_code( $resp ) ) {
			return dck_ai_tr_fail( $post_id, 'The transcription service returned an error. Try again with a shorter or smaller (64kbps mono) file.' );
		}
		$json = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( ! is_array( $json ) ) {
			return dck_ai_tr_fail( $post_id, 'Could not read the transcription result. Please try again.' );
		}

		$full = isset( $json['text'] ) ? trim( $json['text'] ) : '';
		$segs = array();
		if ( isset( $json['segments'] ) && is_array( $json['segments'] ) ) {
			foreach ( $json['segments'] as $s ) {
				$st = isset( $s['start'] ) ? (float) $s['start'] : 0;
				$tx = isset( $s['text'] ) ? trim( wp_strip_all_tags( $s['text'] ) ) : '';
				if ( '' !== $tx ) { $segs[] = array( 's' => round( $st, 1 ), 't' => $tx ); }
			}
		}
		update_post_meta( $post_id, '_dck_transcript', $full );
		update_post_meta( $post_id, '_dck_transcript_segments', wp_json_encode( $segs ) );
		update_post_meta( $post_id, '_dck_transcript_status', 'done' );
		update_post_meta( $post_id, '_dck_transcript_msg', '' );
		return array( 'status' => 'done', 'count' => count( $segs ), 'text' => $full );
	}
}
if ( ! function_exists( 'dck_ai_tr_fail' ) ) {
	function dck_ai_tr_fail( $post_id, $msg ) {
		update_post_meta( $post_id, '_dck_transcript_status', 'error' );
		update_post_meta( $post_id, '_dck_transcript_msg', $msg );
		return array( 'status' => 'error', 'msg' => $msg );
	}
}

/* REST: run transcription (admin). */
if ( ! function_exists( 'dck_ai_rest_transcribe' ) ) {
	function dck_ai_rest_transcribe( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) { return new WP_REST_Response( array( 'status' => 'error', 'msg' => 'Not allowed.' ), 403 ); }
		$p   = $request->get_json_params();
		$pid = isset( $p['post_id'] ) ? intval( $p['post_id'] ) : 0;
		if ( ! $pid || ! current_user_can( 'edit_post', $pid ) ) {
			return new WP_REST_Response( array( 'status' => 'error', 'msg' => 'Please save the sermon as a draft first.' ), 200 );
		}
		return new WP_REST_Response( dck_ai_do_transcribe( $pid ), 200 );
	}
}
/* REST: status (admin). */
if ( ! function_exists( 'dck_ai_rest_transcribe_status' ) ) {
	function dck_ai_rest_transcribe_status( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) { return new WP_REST_Response( array( 'status' => '' ), 403 ); }
		$pid = intval( $request->get_param( 'post_id' ) );
		if ( ! $pid ) { return new WP_REST_Response( array( 'status' => '' ), 200 ); }
		$status = get_post_meta( $pid, '_dck_transcript_status', true );
		$raw    = get_post_meta( $pid, '_dck_transcript_segments', true );
		$segs   = $raw ? json_decode( $raw, true ) : array();
		$count  = is_array( $segs ) ? count( $segs ) : 0;
		return new WP_REST_Response( array(
			'status' => $status ? $status : '',
			'msg'    => get_post_meta( $pid, '_dck_transcript_msg', true ),
			'text'   => ( 'done' === $status ) ? get_post_meta( $pid, '_dck_transcript', true ) : '',
			'count'  => $count,
		), 200 );
	}
}
add_action( 'rest_api_init', function () {
	register_rest_route( 'dck/v1', '/transcribe', array( 'methods' => 'POST', 'callback' => 'dck_ai_rest_transcribe', 'permission_callback' => function () { return current_user_can( 'edit_posts' ); } ) );
	register_rest_route( 'dck/v1', '/transcribe-status', array( 'methods' => 'GET', 'callback' => 'dck_ai_rest_transcribe_status', 'permission_callback' => function () { return current_user_can( 'edit_posts' ); } ) );
} );

/* Meta box on the sermon editor. */
if ( ! function_exists( 'dck_ai_tr_box' ) ) {
	function dck_ai_tr_box( $post ) {
		$status = get_post_meta( $post->ID, '_dck_transcript_status', true );
		$msg    = get_post_meta( $post->ID, '_dck_transcript_msg', true );
		$full   = get_post_meta( $post->ID, '_dck_transcript', true );
		$audio  = get_post_meta( $post->ID, '_dck_audio_url', true );
		echo '<div class="dck-tr">';
		if ( ! $audio ) {
			echo '<p class="dck-studio-warn">Set an <strong>Audio URL</strong> (a direct link to an mp3) for this sermon and click Update, then transcribe.</p>';
		}
		echo '<p><button type="button" class="button button-primary" id="dck-tr-go">Transcribe sermon audio</button> <span id="dck-tr-status" class="description">' . esc_html( $status ? $status : '' ) . '</span></p>';
		if ( $msg ) { echo '<p class="description">' . esc_html( $msg ) . '</p>'; }
		echo '<textarea id="dck-tr-text" rows="8" class="widefat" readonly placeholder="The transcript will appear here after transcription.">' . esc_textarea( $full ) . '</textarea>';
		echo '<p class="description">The transcript powers on-page sermon search (tap any line to jump the audio there) and richer AI summaries. For long sermons, upload a compressed MP3 (about 64kbps mono) under 24MB.</p>';
		echo '</div>';
	}
}
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'dck_ai_transcript', 'Sermon Transcript (AI)', 'dck_ai_tr_box', 'dck_sermon', 'normal', 'default' );
} );

/* Admin assets on the sermon editor. */
if ( ! function_exists( 'dck_ai_tr_admin_assets' ) ) {
	function dck_ai_tr_admin_assets( $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) { return; }
		$screen = get_current_screen();
		if ( ! $screen || 'dck_sermon' !== $screen->post_type ) { return; }
		global $post;
		$pid = ( $post && isset( $post->ID ) ) ? intval( $post->ID ) : 0;
		wp_enqueue_script( 'dck-tr-admin', get_template_directory_uri() . '/assets/js/transcribe-admin.js', array(), DCK_ELITE_VERSION, true );
		wp_localize_script( 'dck-tr-admin', 'DCK_TR', array(
			'rest'   => esc_url_raw( rest_url( 'dck/v1/' ) ),
			'nonce'  => wp_create_nonce( 'wp_rest' ),
			'postId' => $pid,
		) );
	}
}
add_action( 'admin_enqueue_scripts', 'dck_ai_tr_admin_assets' );

/* Front-end: transcript script on single sermons that have a transcript. */
if ( ! function_exists( 'dck_ai_tr_front_enqueue' ) ) {
	function dck_ai_tr_front_enqueue() {
		if ( ! is_singular( 'dck_sermon' ) ) { return; }
		$pid = get_queried_object_id();
		if ( ! $pid || ! get_post_meta( $pid, '_dck_transcript_segments', true ) ) { return; }
		wp_enqueue_script( 'dck-transcript', get_template_directory_uri() . '/assets/js/transcript.js', array(), DCK_ELITE_VERSION, true );
	}
}
add_action( 'wp_enqueue_scripts', 'dck_ai_tr_front_enqueue' );

/* Front-end: render the searchable, timestamped transcript panel. */
if ( ! function_exists( 'dck_ai_transcript_html' ) ) {
	function dck_ai_transcript_html( $post_id ) {
		$raw  = get_post_meta( $post_id, '_dck_transcript_segments', true );
		$segs = $raw ? json_decode( $raw, true ) : array();
		if ( ! is_array( $segs ) || 0 === count( $segs ) ) { return ''; }
		$out  = '<details class="dck-transcript"><summary data-i18n="transcript_label">' . esc_html__( 'Full Transcript', 'dck-elite' ) . '</summary>';
		$out .= '<div class="dck-tr-search"><input type="text" id="dck-tr-input" data-i18n-ph="tr_search" placeholder="' . esc_attr__( 'Search the sermon...', 'dck-elite' ) . '"></div>';
		$out .= '<div class="dck-tr-list">';
		foreach ( $segs as $s ) {
			$st    = isset( $s['s'] ) ? (float) $s['s'] : 0;
			$tx    = isset( $s['t'] ) ? $s['t'] : '';
			$mm    = (int) floor( $st / 60 );
			$ss    = (int) floor( $st - ( $mm * 60 ) );
			$stamp = sprintf( '%d:%02d', $mm, $ss );
			$out  .= '<button type="button" class="dck-seg" data-start="' . esc_attr( $st ) . '"><span class="dck-seg-t">' . esc_html( $stamp ) . '</span> ' . esc_html( $tx ) . '</button>';
		}
		$out .= '</div></details>';
		return $out;
	}
}
