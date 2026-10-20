<?php
/* DCK Elite - AI Sermon Search module (shared helper + REST). */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'dck_ai_word3' ) ) {
	function dck_ai_word3( $w ) {
		return strlen( $w ) >= 3;
	}
}

if ( ! function_exists( 'dck_ai_sermon_cards' ) ) {
	function dck_ai_sermon_cards( $ids ) {
		$out = array();
		foreach ( (array) $ids as $id ) {
			$speaker = wp_strip_all_tags( get_the_term_list( $id, 'dck_sermon_speaker', '', ', ', '' ) );
			$series  = wp_strip_all_tags( get_the_term_list( $id, 'dck_sermon_series', '', ', ', '' ) );
			$scr     = get_post_meta( $id, '_dck_scripture', true );
			$out[] = array(
				'title'     => get_the_title( $id ),
				'url'       => get_permalink( $id ),
				'date'      => get_the_date( '', $id ),
				'speaker'   => $speaker ? $speaker : '',
				'series'    => $series ? $series : '',
				'scripture' => $scr ? wp_strip_all_tags( $scr ) : '',
				'excerpt'   => wp_trim_words( wp_strip_all_tags( get_the_excerpt( $id ) ), 26 ),
			);
		}
		return $out;
	}
}

if ( ! function_exists( 'dck_ai_recent_sermons' ) ) {
	function dck_ai_recent_sermons( $limit ) {
		$q = new WP_Query( array(
			'post_type'      => 'dck_sermon',
			'posts_per_page' => $limit ? intval( $limit ) : 6,
			'post_status'    => 'publish',
			'no_found_rows'  => true,
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		return dck_ai_sermon_cards( $q->posts );
	}
}

if ( ! function_exists( 'dck_ai_find_sermons' ) ) {
	function dck_ai_find_sermons( $query, $limit ) {
		$limit = $limit ? intval( $limit ) : 5;
		$q = new WP_Query( array(
			's'              => $query,
			'post_type'      => 'dck_sermon',
			'posts_per_page' => $limit,
			'post_status'    => 'publish',
			'no_found_rows'  => true,
			'fields'         => 'ids',
		) );
		$ids = $q->posts;
		if ( empty( $ids ) ) {
			$words = array_filter( preg_split( '/\s+/', $query ), 'dck_ai_word3' );
			foreach ( $words as $w ) {
				$qw = new WP_Query( array(
					's'              => $w,
					'post_type'      => 'dck_sermon',
					'posts_per_page' => $limit,
					'post_status'    => 'publish',
					'no_found_rows'  => true,
					'fields'         => 'ids',
				) );
				foreach ( $qw->posts as $pid ) {
					$ids[] = $pid;
				}
			}
			$qm = new WP_Query( array(
				'post_type'      => 'dck_sermon',
				'posts_per_page' => $limit,
				'post_status'    => 'publish',
				'no_found_rows'  => true,
				'fields'         => 'ids',
				'meta_query'     => array( array( 'key' => '_dck_scripture', 'value' => $query, 'compare' => 'LIKE' ) ),
			) );
			foreach ( $qm->posts as $pid ) {
				$ids[] = $pid;
			}
			$ids = array_slice( array_values( array_unique( $ids ) ), 0, $limit );
		}
		return dck_ai_sermon_cards( $ids );
	}
}

if ( ! function_exists( 'dck_ai_rest_sermon_search' ) ) {
	function dck_ai_rest_sermon_search( $request ) {
		if ( function_exists( 'dck_ai_rate_ok' ) && ! dck_ai_rate_ok() ) {
			return new WP_REST_Response( array( 'note' => 'Please try again in a few minutes.', 'results' => array() ), 200 );
		}
		$p = $request->get_json_params();
		$query = isset( $p['query'] ) ? trim( wp_strip_all_tags( (string) $p['query'] ) ) : '';
		if ( strlen( $query ) > 300 ) { $query = substr( $query, 0, 300 ); }
		if ( '' === $query ) { return new WP_REST_Response( array( 'note' => '', 'results' => array() ), 200 ); }

		$results = dck_ai_find_sermons( $query, 8 );
		$dck_fallback = false;
		if ( 0 === count( $results ) ) {
			$results = dck_ai_recent_sermons( 6 );
			$dck_fallback = true;
		}
		$note = '';
		$dck_lang = isset( $p['lang'] ) ? sanitize_key( $p['lang'] ) : '';
		$langnote = function_exists( 'dck_ai_lang_note' ) ? dck_ai_lang_note( $dck_lang ) : '';

		if ( function_exists( 'dck_ai_get_key' ) && dck_ai_get_key() ) {
			if ( count( $results ) > 0 ) {
				$list = '';
				foreach ( $results as $r ) {
					$list .= '- ' . $r['title'];
					if ( $r['scripture'] ) { $list .= ' (' . $r['scripture'] . ')'; }
					$list .= ': ' . $r['excerpt'] . PHP_EOL;
				}
				$msgs = array(
					array( 'role' => 'system', 'content' => 'You help a church visitor find sermons. Given their search and a list of matching sermons, write a warm 1 to 2 sentence note pointing them to what to watch first and why. Only reference sermons in the list. Never invent sermons. ' . $langnote ),
					array( 'role' => 'user', 'content' => 'Search: ' . $query . PHP_EOL . 'Matching sermons:' . PHP_EOL . $list ),
				);
				$ai = dck_ai_chat( $msgs, array( 'max_tokens' => 170, 'temperature' => 0.5 ) );
				if ( ! is_wp_error( $ai ) ) { $note = $ai; }
			} else {
				$msgs = array(
					array( 'role' => 'system', 'content' => 'A church visitor searched the sermon library but nothing matched yet. In 2 to 3 warm sentences, offer two or three encouraging Bible passages on their topic (book chapter:verse) and invite them to check back or join us on Sunday. Keep it brief and kind. ' . $langnote ),
					array( 'role' => 'user', 'content' => $query ),
				);
				$ai = dck_ai_chat( $msgs, array( 'max_tokens' => 200, 'temperature' => 0.6 ) );
				if ( ! is_wp_error( $ai ) ) { $note = $ai; }
			}
		}
		if ( '' === $note && ! empty( $dck_fallback ) ) {
			$note = ( 'sw' === $dck_lang )
				? 'Hatukupata ujumbe unaolingana kabisa, lakini hizi ni baadhi ya jumbe za hivi karibuni unazoweza kufurahia.'
				: 'We could not find an exact match, but here are some recent messages you may enjoy.';
		}
		return new WP_REST_Response( array( 'note' => $note, 'results' => $results ), 200 );
	}
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'dck/v1', '/sermon-search', array(
		'methods'             => 'POST',
		'callback'            => 'dck_ai_rest_sermon_search',
		'permission_callback' => '__return_true',
	) );
} );

if ( ! function_exists( 'dck_ai_ss_enqueue' ) ) {
	function dck_ai_ss_enqueue() {
		if ( ! is_post_type_archive( 'dck_sermon' ) ) { return; }
		wp_enqueue_script( 'dck-ss', get_template_directory_uri() . '/assets/js/sermon-search.js', array(), DCK_ELITE_VERSION, true );
		wp_localize_script( 'dck-ss', 'DCK_SS', array(
			'rest'  => esc_url_raw( rest_url( 'dck/v1/' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'dck_ai_ss_enqueue' );
