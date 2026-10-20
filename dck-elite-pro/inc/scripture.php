<?php
/**
 * Bible reader: multilingual Scripture (English, Kiswahili and Kenyan languages)
 * served through a cached proxy of the Free Use Bible API (bible.helloao.org),
 * which publishes openly licensed translations from eBible.org and partners.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_bible_catalog' ) ) {
	/**
	 * Known translations: id => array( label, short, language, group ).
	 */
	function dck_bible_catalog() {
		return array(
			'BSB'         => array( 'Berean Standard Bible', 'BSB', 'English', 'English' ),
			'eng_kjv'     => array( 'King James Version', 'KJV', 'English', 'English' ),
			'ENGWEBP'     => array( 'World English Bible', 'WEB', 'English', 'English' ),
			'eng_asv'     => array( 'American Standard Version (1901)', 'ASV', 'English', 'English' ),
			'eng_bbe'     => array( 'Bible in Basic English', 'BBE', 'English', 'English' ),
			'eng_ylt'     => array( "Young's Literal Translation", 'YLT', 'English', 'English' ),
			'eng_net'     => array( 'NET Bible', 'NET', 'English', 'English' ),
			'swh_bib'     => array( 'Neno: Bibilia Takatifu (Kiswahili Contemporary)', 'NEN', 'Kiswahili', 'Kiswahili' ),
			'swh_onmm'    => array( 'Neno: Maandiko Matakatifu', 'ONMM', 'Kiswahili', 'Kiswahili' ),
			'swh_ulb'     => array( 'Biblia Takatifu (Unlocked Literal)', 'SULB', 'Kiswahili', 'Kiswahili' ),
			'swh_swa'     => array( 'Biblia Takatifu (1850, portions)', 'SWA', 'Kiswahili', 'Kiswahili' ),
			'kik_bib'     => array( 'Kiugo Gĩtheru Kĩa Ngai (Gĩkũyũ)', 'KIK', 'Gĩkũyũ', 'Kenyan languages' ),
			'luo_bib'     => array( 'Ochiw Thuolo Motingʼo Loko Manyien (Dholuo)', 'LUO', 'Dholuo', 'Kenyan languages' ),
			'spy_wbt'     => array( 'Sabaot New Testament (Kalenjin)', 'SPY', 'Sabaot', 'Kenyan languages' ),
			'sxb_wbt'     => array( 'Suba New Testament', 'SXB', 'Suba', 'Kenyan languages' ),
			'gax_twf'     => array( 'Kitaaba Woyyuu (Borana / Guji Oromo)', 'GAX', 'Borana', 'Kenyan languages' ),
			'som_sim'     => array( 'Kitaabka Quduuska Ah (Somali)', 'SOM', 'Somali', 'Kenyan languages' ),
		);
	}
}

if ( ! function_exists( 'dck_bible_versions' ) ) {
	/** Enabled translations (editable in DCK Church > Page Text, one ID per line). */
	function dck_bible_versions() {
		$cat = dck_bible_catalog();
		$raw = (string) dck_home_val( 'dck_bible_versions', implode( "\n", array_keys( $cat ) ) );
		$out = array();
		foreach ( preg_split( '/\r\n|\r|\n|,/', $raw ) as $id ) {
			$id = trim( $id );
			if ( '' === $id || ! preg_match( '/^[A-Za-z0-9_]{2,20}$/', $id ) ) {
				continue;
			}
			$c          = isset( $cat[ $id ] ) ? $cat[ $id ] : array( $id, strtoupper( substr( $id, 0, 5 ) ), 'Other', 'Other languages' );
			$out[ $id ] = array( 'id' => $id, 'name' => $c[0], 'short' => $c[1], 'lang' => $c[2], 'group' => $c[3] );
		}
		return $out;
	}
}

if ( ! function_exists( 'dck_bible_fetch' ) ) {
	function dck_bible_fetch( $path, $ttl ) {
		$key  = 'dck_bbl_' . md5( $path );
		$data = get_transient( $key );
		if ( false !== $data ) {
			return $data;
		}
		$res = wp_remote_get( 'https://bible.helloao.org/api/' . $path, array( 'timeout' => 15 ) );
		if ( is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
			return null;
		}
		$data = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! is_array( $data ) ) {
			return null;
		}
		set_transient( $key, $data, $ttl );
		return $data;
	}
}

if ( ! function_exists( 'dck_bible_flatten' ) ) {
	function dck_bible_flatten( $content ) {
		$t = '';
		foreach ( (array) $content as $c ) {
			if ( is_string( $c ) ) {
				$t .= $c;
			} elseif ( is_array( $c ) ) {
				if ( isset( $c['text'] ) ) {
					$t .= is_array( $c['text'] ) ? dck_bible_flatten( $c['text'] ) : $c['text'];
				} elseif ( isset( $c['heading'] ) ) {
					$t .= '';
				} elseif ( ! empty( $c['lineBreak'] ) ) {
					$t .= ' ';
				}
			}
		}
		return trim( preg_replace( '/\s+/u', ' ', $t ) );
	}
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'dck/v1',
			'/scripture/books',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => function ( $req ) {
					$tr = (string) $req->get_param( 'tr' );
					$vs = dck_bible_versions();
					if ( ! isset( $vs[ $tr ] ) ) {
						return new WP_REST_Response( array( 'error' => 'unknown_translation' ), 400 );
					}
					$d = dck_bible_fetch( rawurlencode( $tr ) . '/books.json', 30 * DAY_IN_SECONDS );
					if ( ! $d || empty( $d['books'] ) ) {
						return new WP_REST_Response( array( 'error' => 'unavailable' ), 502 );
					}
					$books = array();
					foreach ( $d['books'] as $b ) {
						$books[] = array( 'id' => $b['id'], 'name' => isset( $b['commonName'] ) ? $b['commonName'] : $b['name'], 'chapters' => (int) $b['numberOfChapters'], 'order' => (int) $b['order'] );
					}
					$t = isset( $d['translation'] ) ? $d['translation'] : array();
					$r = new WP_REST_Response(
						array(
							'books'   => $books,
							'license' => isset( $t['licenseUrl'] ) ? $t['licenseUrl'] : '',
							'website' => isset( $t['website'] ) ? $t['website'] : '',
							'dir'     => isset( $t['textDirection'] ) ? $t['textDirection'] : 'ltr',
						)
					);
					$r->header( 'Cache-Control', 'public, max-age=86400' );
					return $r;
				},
			)
		);
		register_rest_route(
			'dck/v1',
			'/scripture/chapter',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => function ( $req ) {
					$tr   = (string) $req->get_param( 'tr' );
					$book = strtoupper( (string) $req->get_param( 'book' ) );
					$ch   = (int) $req->get_param( 'ch' );
					$vs   = dck_bible_versions();
					if ( ! isset( $vs[ $tr ] ) || ! preg_match( '/^[1-3A-Z][A-Z0-9]{2}$/', $book ) || $ch < 1 || $ch > 150 ) {
						return new WP_REST_Response( array( 'error' => 'bad_request' ), 400 );
					}
					$d = dck_bible_fetch( rawurlencode( $tr ) . '/' . $book . '/' . $ch . '.json', 14 * DAY_IN_SECONDS );
					if ( ! $d || empty( $d['chapter']['content'] ) ) {
						return new WP_REST_Response( array( 'error' => 'not_found' ), 404 );
					}
					$items = array();
					foreach ( $d['chapter']['content'] as $c ) {
						if ( 'heading' === $c['type'] ) {
							$items[] = array( 'h' => dck_bible_flatten( $c['content'] ) );
						} elseif ( 'verse' === $c['type'] ) {
							$items[] = array( 'n' => (int) $c['number'], 't' => dck_bible_flatten( $c['content'] ) );
						}
					}
					$audio = '';
					if ( ! empty( $d['thisChapterAudioLinks'] ) && is_array( $d['thisChapterAudioLinks'] ) ) {
						$audio = esc_url_raw( reset( $d['thisChapterAudioLinks'] ) );
					}
					$r = new WP_REST_Response(
						array(
							'book'  => array( 'id' => $book, 'name' => isset( $d['book']['commonName'] ) ? $d['book']['commonName'] : $book ),
							'ch'    => $ch,
							'items' => $items,
							'audio' => $audio,
							'next'  => isset( $d['nextChapterReference'] ) ? $d['nextChapterReference'] : null,
							'prev'  => isset( $d['previousChapterReference'] ) ? $d['previousChapterReference'] : null,
						)
					);
					$r->header( 'Cache-Control', 'public, max-age=86400' );
					return $r;
				},
			)
		);
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! is_page_template( 'template-bible.php' ) ) {
			return;
		}
		wp_enqueue_script( 'dck-bible-reader', get_template_directory_uri() . '/assets/js/bible-reader.js', array(), DCK_ELITE_VERSION, true );
		$votd = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) dck_home_val( 'dck_bible_votd', "JHN 3 16\nPSA 23 1\nPHP 4 13\nJER 29 11\nISA 40 31\nROM 8 28\nPRO 3 5\nMAT 11 28\nJOS 1 9\nPSA 46 1\nISA 41 10\n2CO 5 17\nLAM 3 22\nHEB 11 1\nMAT 6 33\nROM 15 13\nPSA 121 1\n1PE 5 7\nPHP 4 6\nGAL 2 20" ) ) ) ) );
		wp_localize_script(
			'dck-bible-reader',
			'DCK_READER',
			array(
				'rest'     => esc_url_raw( rest_url( 'dck/v1/scripture/' ) ),
				'versions' => array_values( dck_bible_versions() ),
				'default'  => dck_home_val( 'dck_bible_default', 'BSB' ),
				'votd'     => $votd,
				'i18n'     => array(
					'loading'  => __( 'Opening the Word...', 'dck-elite' ),
					'error'    => __( 'This chapter could not be loaded right now. Please try again, or choose another version.', 'dck-elite' ),
					'notIn'    => __( 'This version does not include that book yet (some local-language Bibles are New Testament only).', 'dck-elite' ),
					'copied'   => __( 'Copied', 'dck-elite' ),
					'none'     => __( 'No second version', 'dck-elite' ),
					'chapter'  => __( 'Chapter', 'dck-elite' ),
					'notFound' => __( 'Try a reference like John 3:16, Zaburi 23 or Psalm 23.', 'dck-elite' ),
				),
			)
		);
	}
);
