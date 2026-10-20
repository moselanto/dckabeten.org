<?php
/**
 * Sermon helpers: reliable images, meta and series badges.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_sermon_image_url' ) ) {
	/**
	 * Image for a sermon. Featured image first, then the saved image URL.
	 * Bundled sermon images were saved with the theme folder in the URL, so they
	 * break when the theme is uploaded under a different folder name. Any URL
	 * that points at /assets/images/sermons/ is rebuilt from the active theme.
	 */
	function dck_sermon_image_url( $id, $size = 'large' ) {
		if ( has_post_thumbnail( $id ) ) {
			$u = get_the_post_thumbnail_url( $id, $size );
			if ( $u ) {
				return $u;
			}
		}
		$u = (string) get_post_meta( $id, '_dck_sermon_image', true );
		if ( '' === $u ) {
			return '';
		}
		$pos = strpos( $u, '/assets/images/sermons/' );
		if ( false !== $pos ) {
			$rel = substr( $u, $pos + 1 );
			if ( file_exists( get_template_directory() . '/' . $rel ) ) {
				return get_template_directory_uri() . '/' . $rel;
			}
		}
		return $u;
	}
}

if ( ! function_exists( 'dck_sermon_first_term' ) ) {
	function dck_sermon_first_term( $id, $tax ) {
		$t = get_the_terms( $id, $tax );
		return ( $t && ! is_wp_error( $t ) ) ? $t[0] : null;
	}
}

if ( ! function_exists( 'dck_sermon_read_time' ) ) {
	function dck_sermon_read_time( $id ) {
		$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $id ) ) );
		return max( 1, (int) ceil( $words / 200 ) );
	}
}

if ( ! function_exists( 'dck_sermon_badges' ) ) {
	/** Small media badges: Video / Audio / Notes / read time. */
	function dck_sermon_badges( $id ) {
		$b = array();
		if ( get_post_meta( $id, '_dck_video_url', true ) ) {
			$b[] = __( 'Video', 'dck-elite' );
		}
		if ( get_post_meta( $id, '_dck_audio_url', true ) ) {
			$b[] = __( 'Audio', 'dck-elite' );
		}
		if ( get_post_meta( $id, '_dck_download_url', true ) ) {
			$b[] = __( 'Notes', 'dck-elite' );
		}
		$b[] = sprintf( _n( '%d min read', '%d min read', dck_sermon_read_time( $id ), 'dck-elite' ), dck_sermon_read_time( $id ) );
		return $b;
	}
}
