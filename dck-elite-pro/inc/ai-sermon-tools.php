<?php
/**
 * DCK Elite - Sermon AI Tools (Phase 2+).
 * Extends the AI Content Studio (inc/ai-content.php) with:
 *  - Opt-in auto-generation of Summary + Study Guide when a sermon is published.
 *  - A bulk "AI Backfill" tool to fill missing summaries/guides across the library.
 *  - A printable sermon view (button + open-details behaviour handled in JS/CSS).
 * Reuses dck_ai_sermon_context(), dck_ai_content_prompt(), dck_ai_chat().
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------- Customizer: auto-generate toggle ---------------- */
add_action( 'customize_register', 'dck_ai_tools_customize', 40 );
if ( ! function_exists( 'dck_ai_tools_customize' ) ) {
	function dck_ai_tools_customize( $wp_customize ) {
		$wp_customize->add_setting( 'dck_ai_autogen', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'dck_ai_autogen', array(
			'label'       => __( 'Auto-generate sermon summary & study guide on publish', 'dck-elite' ),
			'description' => __( 'When a sermon is published without a summary or study guide, generate them automatically in the background. Requires an API key.', 'dck-elite' ),
			'section'     => 'dck_ai',
			'type'        => 'checkbox',
		) );
	}
}

/* ---------------- Shared generator ---------------- */
if ( ! function_exists( 'dck_ai_generate_content' ) ) {
	function dck_ai_generate_content( $post_id, $kind ) {
		if ( ! function_exists( 'dck_ai_get_key' ) || ! dck_ai_get_key() ) {
			return new WP_Error( 'dck_ai_no_key', 'No API key configured.' );
		}
		if ( ! function_exists( 'dck_ai_content_prompt' ) || ! function_exists( 'dck_ai_sermon_context' ) || ! function_exists( 'dck_ai_chat' ) ) {
			return new WP_Error( 'dck_ai_missing', 'AI content module unavailable.' );
		}
		$sys = dck_ai_content_prompt( $kind );
		if ( '' === $sys ) {
			return new WP_Error( 'dck_ai_kind', 'Unknown content type.' );
		}
		$messages = array(
			array( 'role' => 'system', 'content' => $sys ),
			array( 'role' => 'user', 'content' => dck_ai_sermon_context( $post_id ) ),
		);
		$max = ( 'study_guide' === $kind ) ? 950 : 550;
		$out = dck_ai_chat( $messages, array( 'max_tokens' => $max, 'temperature' => 0.6 ) );
		if ( is_wp_error( $out ) ) {
			return $out;
		}
		return trim( (string) $out );
	}
}

/* Generate + save the stored fields for one sermon. Only fills empties unless forced. */
if ( ! function_exists( 'dck_ai_backfill_one' ) ) {
	function dck_ai_backfill_one( $post_id, $force = false ) {
		$did = false;
		$sum = get_post_meta( $post_id, '_dck_summary', true );
		if ( $force || '' === trim( (string) $sum ) ) {
			$r = dck_ai_generate_content( $post_id, 'summary' );
			if ( ! is_wp_error( $r ) && '' !== $r ) {
				update_post_meta( $post_id, '_dck_summary', $r );
				$did = true;
			}
		}
		$g = get_post_meta( $post_id, '_dck_study_guide', true );
		if ( $force || '' === trim( (string) $g ) ) {
			$r = dck_ai_generate_content( $post_id, 'study_guide' );
			if ( ! is_wp_error( $r ) && '' !== $r ) {
				update_post_meta( $post_id, '_dck_study_guide', $r );
				$did = true;
			}
		}
		return $did;
	}
}

/* ---------------- Auto-generate on publish (background) ---------------- */
add_action( 'transition_post_status', 'dck_ai_autogen_on_publish', 10, 3 );
if ( ! function_exists( 'dck_ai_autogen_on_publish' ) ) {
	function dck_ai_autogen_on_publish( $new_status, $old_status, $post ) {
		if ( 'publish' !== $new_status || ! isset( $post->post_type ) || 'dck_sermon' !== $post->post_type ) {
			return;
		}
		if ( ! get_theme_mod( 'dck_ai_autogen', '' ) ) {
			return;
		}
		if ( ! function_exists( 'dck_ai_get_key' ) || ! dck_ai_get_key() ) {
			return;
		}
		$sum = get_post_meta( $post->ID, '_dck_summary', true );
		$g   = get_post_meta( $post->ID, '_dck_study_guide', true );
		if ( '' !== trim( (string) $sum ) && '' !== trim( (string) $g ) ) {
			return;
		}
		if ( ! wp_next_scheduled( 'dck_ai_autogen_run', array( (int) $post->ID ) ) ) {
			wp_schedule_single_event( time() + 15, 'dck_ai_autogen_run', array( (int) $post->ID ) );
		}
	}
}
add_action( 'dck_ai_autogen_run', 'dck_ai_backfill_one' );

/* ---------------- Bulk backfill ---------------- */
if ( ! function_exists( 'dck_ai_backfill_pending_ids' ) ) {
	function dck_ai_backfill_pending_ids( $limit = 300 ) {
		$q = new WP_Query( array(
			'post_type'      => 'dck_sermon',
			'post_status'    => 'publish',
			'posts_per_page' => (int) $limit,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'meta_query'     => array(
				'relation' => 'OR',
				array( 'key' => '_dck_summary', 'compare' => 'NOT EXISTS' ),
				array( 'key' => '_dck_summary', 'value' => '', 'compare' => '=' ),
				array( 'key' => '_dck_study_guide', 'compare' => 'NOT EXISTS' ),
				array( 'key' => '_dck_study_guide', 'value' => '', 'compare' => '=' ),
			),
		) );
		return $q->posts;
	}
}

if ( ! function_exists( 'dck_ai_rest_backfill' ) ) {
	function dck_ai_rest_backfill( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_REST_Response( array( 'done' => true, 'remaining' => 0, 'msg' => 'Not allowed.' ), 403 );
		}
		if ( ! function_exists( 'dck_ai_get_key' ) || ! dck_ai_get_key() ) {
			return new WP_REST_Response( array( 'done' => true, 'remaining' => 0, 'msg' => 'Add an API key in Appearance, Customize, DCK AI Assistant first.' ), 200 );
		}
		$ids = dck_ai_backfill_pending_ids( 300 );
		if ( empty( $ids ) ) {
			return new WP_REST_Response( array( 'done' => true, 'remaining' => 0, 'processed' => 0 ), 200 );
		}
		$pid   = (int) $ids[0];
		$title = get_the_title( $pid );
		dck_ai_backfill_one( $pid, false );
		$remaining = count( dck_ai_backfill_pending_ids( 300 ) );
		return new WP_REST_Response( array(
			'done'      => ( 0 === $remaining ),
			'remaining' => $remaining,
			'processed' => $pid,
			'title'     => $title,
		), 200 );
	}
}
add_action( 'rest_api_init', function () {
	register_rest_route( 'dck/v1', '/sermon-backfill', array(
		'methods'             => 'POST',
		'callback'            => 'dck_ai_rest_backfill',
		'permission_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );
} );

/* Admin page under Sermons. */
add_action( 'admin_menu', function () {
	add_submenu_page(
		'edit.php?post_type=dck_sermon',
		__( 'AI Backfill', 'dck-elite' ),
		__( 'AI Backfill', 'dck-elite' ),
		'edit_posts',
		'dck-ai-backfill',
		'dck_ai_backfill_page'
	);
} );

if ( ! function_exists( 'dck_ai_backfill_page' ) ) {
	function dck_ai_backfill_page() {
		$pending = count( dck_ai_backfill_pending_ids( 300 ) );
		$has_key = ( function_exists( 'dck_ai_get_key' ) && dck_ai_get_key() );
		echo '<div class="wrap"><h1>' . esc_html__( 'AI Backfill for Sermons', 'dck-elite' ) . '</h1>';
		echo '<p>' . esc_html__( 'Generate the AI summary and small-group study guide for every published sermon that is still missing them. Sermons are processed one at a time to stay within your provider limits.', 'dck-elite' ) . '</p>';
		if ( ! $has_key ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'Add your API key in Appearance, Customize, DCK AI Assistant before running the backfill.', 'dck-elite' ) . '</p></div>';
		}
		echo '<p><strong>' . esc_html__( 'Sermons still missing content:', 'dck-elite' ) . '</strong> <span id="dck-bf-count">' . esc_html( $pending ) . '</span></p>';
		echo '<p><button type="button" class="button button-primary" id="dck-bf-go"' . ( ( $has_key && $pending > 0 ) ? '' : ' disabled' ) . '>' . esc_html__( 'Generate missing content', 'dck-elite' ) . '</button> ';
		echo '<button type="button" class="button" id="dck-bf-stop" style="display:none">' . esc_html__( 'Stop', 'dck-elite' ) . '</button></p>';
		echo '<p id="dck-bf-log" class="description"></p>';
		echo '</div>';
	}
}

/* Backfill admin assets. */
add_action( 'admin_enqueue_scripts', 'dck_ai_backfill_assets' );
if ( ! function_exists( 'dck_ai_backfill_assets' ) ) {
	function dck_ai_backfill_assets( $hook ) {
		if ( false === strpos( (string) $hook, 'dck-ai-backfill' ) ) {
			return;
		}
		wp_enqueue_script( 'dck-backfill-admin', get_template_directory_uri() . '/assets/js/backfill-admin.js', array(), DCK_ELITE_VERSION, true );
		wp_localize_script( 'dck-backfill-admin', 'DCK_BF', array(
			'rest'  => esc_url_raw( rest_url( 'dck/v1/sermon-backfill' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		) );
	}
}

/* ---------------- Printable sermon (front-end asset) ---------------- */
add_action( 'wp_enqueue_scripts', 'dck_sermon_print_enqueue' );
if ( ! function_exists( 'dck_sermon_print_enqueue' ) ) {
	function dck_sermon_print_enqueue() {
		if ( ! is_singular( 'dck_sermon' ) ) {
			return;
		}
		wp_enqueue_script( 'dck-sermon-print', get_template_directory_uri() . '/assets/js/sermon-print.js', array(), DCK_ELITE_VERSION, true );
	}
}
