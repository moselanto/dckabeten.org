<?php
/**
 * DCK Elite - Extended structured data (schema.org) + breadcrumbs.
 * Adds BreadcrumbList, VideoObject/Article for sermons, Event for events,
 * BlogPosting for posts, and an FAQPage helper. Complements inc/seo.php.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_blog_url' ) ) {
	function dck_blog_url() {
		$pid = (int) get_option( 'page_for_posts' );
		if ( $pid ) {
			return get_permalink( $pid );
		}
		if ( function_exists( 'dck_elite_page_url' ) ) {
			$u = dck_elite_page_url( 'blog' );
			if ( $u ) {
				return $u;
			}
		}
		return home_url( '/blog/' );
	}
}

if ( ! function_exists( 'dck_seo_time24' ) ) {
	function dck_seo_time24( $t ) {
		$ts = strtotime( (string) $t );
		if ( $ts ) {
			return gmdate( 'H:i:s', $ts );
		}
		return '00:00:00';
	}
}

if ( ! function_exists( 'dck_bc_items' ) ) {
	function dck_bc_items() {
		$items = array( array( 'name' => __( 'Home', 'dck-elite' ), 'url' => home_url( '/' ) ) );
		if ( is_singular( 'dck_sermon' ) ) {
			$items[] = array( 'name' => __( 'Sermons', 'dck-elite' ), 'url' => get_post_type_archive_link( 'dck_sermon' ) );
			$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
		} elseif ( is_post_type_archive( 'dck_sermon' ) ) {
			$items[] = array( 'name' => __( 'Sermons', 'dck-elite' ), 'url' => get_post_type_archive_link( 'dck_sermon' ) );
		} elseif ( is_singular( 'dck_event' ) ) {
			$ev = function_exists( 'dck_elite_page_url' ) ? dck_elite_page_url( 'events' ) : home_url( '/events/' );
			$items[] = array( 'name' => __( 'Events', 'dck-elite' ), 'url' => $ev );
			$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
		} elseif ( is_singular( 'post' ) ) {
			$items[] = array( 'name' => __( 'Blog', 'dck-elite' ), 'url' => dck_blog_url() );
			$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
		} elseif ( is_home() ) {
			$items[] = array( 'name' => __( 'Blog', 'dck-elite' ), 'url' => dck_blog_url() );
		} elseif ( is_category() || is_tag() ) {
			$items[] = array( 'name' => __( 'Blog', 'dck-elite' ), 'url' => dck_blog_url() );
			$t = get_queried_object();
			if ( $t && ! is_wp_error( $t ) ) {
				$items[] = array( 'name' => $t->name, 'url' => get_term_link( $t ) );
			}
		} elseif ( is_tax() ) {
			$t = get_queried_object();
			$items[] = array( 'name' => __( 'Sermons', 'dck-elite' ), 'url' => get_post_type_archive_link( 'dck_sermon' ) );
			if ( $t && ! is_wp_error( $t ) ) {
				$items[] = array( 'name' => $t->name, 'url' => get_term_link( $t ) );
			}
		} elseif ( is_page() ) {
			$anc = array_reverse( get_post_ancestors( get_queried_object_id() ) );
			foreach ( $anc as $aid ) {
				$items[] = array( 'name' => get_the_title( $aid ), 'url' => get_permalink( $aid ) );
			}
			$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
		}
		return $items;
	}
}

/* Visual breadcrumb bar, injected just below the header on inner pages. */
if ( ! function_exists( 'dck_bc_html' ) ) {
	function dck_bc_html() {
		if ( is_front_page() || is_404() ) {
			return;
		}
		$items = dck_bc_items();
		if ( count( $items ) < 2 ) {
			return;
		}
		$last = count( $items ) - 1;
		echo '<nav class="dck-breadcrumbs" aria-label="Breadcrumb"><div class="dck-container"><ol>';
		foreach ( $items as $i => $it ) {
			if ( $i === $last ) {
				echo '<li aria-current="page"><span>' . esc_html( $it['name'] ) . '</span></li>';
			} else {
				echo '<li><a href="' . esc_url( $it['url'] ) . '">' . esc_html( $it['name'] ) . '</a></li>';
			}
		}
		echo '</ol></div></nav>';
	}
}
add_action( 'dck_after_header', 'dck_bc_html' );

/* FAQPage from a post's _dck_faq meta (JSON array of {q,a}); false when none. */
if ( ! function_exists( 'dck_faq_from_post' ) ) {
	function dck_faq_from_post( $id ) {
		$raw = get_post_meta( $id, '_dck_faq', true );
		if ( ! $raw ) {
			return false;
		}
		$list = is_array( $raw ) ? $raw : json_decode( $raw, true );
		if ( ! is_array( $list ) || empty( $list ) ) {
			return false;
		}
		$q = array();
		foreach ( $list as $row ) {
			if ( empty( $row['q'] ) || empty( $row['a'] ) ) {
				continue;
			}
			$q[] = array(
				'@type'          => 'Question',
				'name'           => wp_strip_all_tags( $row['q'] ),
				'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $row['a'] ) ),
			);
		}
		if ( empty( $q ) ) {
			return false;
		}
		return array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $q );
	}
}

/* Page-specific nodes for the single @graph built in inc/seo.php (no separate <script> output). */
if ( ! function_exists( 'dck_seo_youtube_id' ) ) {
	function dck_seo_youtube_id( $url ) {
		if ( preg_match( '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|live/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', (string) $url, $m ) ) {
			return $m[1];
		}
		return '';
	}
}

if ( ! function_exists( 'dck_seo_extra_nodes' ) ) {
	function dck_seo_extra_nodes( $page_url ) {
		$church = array( '@id' => dck_seo_id( 'church' ) );
		$nodes  = array();

		if ( ! is_front_page() ) {
			$items = dck_bc_items();
			if ( count( $items ) >= 2 ) {
				$el = array();
				foreach ( $items as $i => $it ) {
					$el[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => wp_strip_all_tags( $it['name'] ), 'item' => $it['url'] );
				}
				$nodes[] = array( '@type' => 'BreadcrumbList', '@id' => $page_url . '#breadcrumb', 'itemListElement' => $el );
			}
		}

		if ( is_singular( 'dck_sermon' ) ) {
			$id    = get_queried_object_id();
			$video = (string) get_post_meta( $id, '_dck_video_url', true );
			$thumb = function_exists( 'dck_sermon_image_url' ) ? dck_sermon_image_url( $id ) : get_post_meta( $id, '_dck_sermon_image', true );
			$desc  = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $id ) ), 40 );
			$scr   = (string) get_post_meta( $id, '_dck_scripture', true );
			$yt    = dck_seo_youtube_id( $video );
			if ( $yt ) {
				// Playable on the page through the YouTube player: embedUrl only (no third-party contentUrl).
				$nodes[] = array_filter(
					array(
						'@type'            => 'VideoObject',
						'@id'              => $page_url . '#video',
						'name'             => get_the_title( $id ),
						'description'      => $desc ? $desc : get_the_title( $id ),
						'thumbnailUrl'     => array( 'https://i.ytimg.com/vi/' . $yt . '/hqdefault.jpg' ),
						'uploadDate'       => get_the_date( 'c', $id ),
						'embedUrl'         => 'https://www.youtube.com/embed/' . $yt,
						'url'              => get_permalink( $id ),
						'publisher'        => $church,
						'mainEntityOfPage' => array( '@id' => $page_url . '#webpage' ),
					)
				);
			} else {
				$nodes[] = array_filter(
					array(
						'@type'            => 'Article',
						'@id'              => $page_url . '#article',
						'headline'         => get_the_title( $id ),
						'description'      => $desc,
						'image'            => $thumb ? $thumb : dck_seo_image_default(),
						'datePublished'    => get_the_date( 'c', $id ),
						'dateModified'     => get_the_modified_date( 'c', $id ),
						'author'           => $church,
						'publisher'        => $church,
						'about'            => $scr ? array( '@type' => 'Thing', 'name' => $scr ) : null,
						'isPartOf'         => array( '@id' => dck_seo_id( 'website' ) ),
						'mainEntityOfPage' => array( '@id' => $page_url . '#webpage' ),
					)
				);
			}
		} elseif ( is_singular( 'dck_event' ) ) {
			$id    = get_queried_object_id();
			$date  = (string) get_post_meta( $id, '_dck_event_date', true );
			$rec   = (string) get_post_meta( $id, '_dck_event_recurring', true );
			$start = (string) get_post_meta( $id, '_dck_event_start', true );
			$end   = (string) get_post_meta( $id, '_dck_event_end', true );
			$loc   = (string) get_post_meta( $id, '_dck_event_location', true );
			// Only real, dated one-off events become Event entities (weekly gatherings are covered by opening hours).
			if ( $date && '1' !== $rec && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
				$ev = array(
					'@type'               => 'Event',
					'@id'                 => $page_url . '#event',
					'name'                => get_the_title( $id ),
					'url'                 => get_permalink( $id ),
					'description'         => wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $id ) ), 40 ),
					'startDate'           => $start ? $date . 'T' . substr( dck_seo_time24( $start ), 0, 5 ) . '+03:00' : $date,
					'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
					'eventStatus'         => 'https://schema.org/EventScheduled',
					'location'            => array( '@type' => 'Place', 'name' => $loc ? $loc : get_bloginfo( 'name' ), 'address' => dck_seo_address() ),
					'organizer'           => $church,
					'mainEntityOfPage'    => array( '@id' => $page_url . '#webpage' ),
				);
				if ( $end && $start ) {
					$ev['endDate'] = $date . 'T' . substr( dck_seo_time24( $end ), 0, 5 ) . '+03:00';
				}
				$img = get_the_post_thumbnail_url( $id, 'large' );
				if ( $img ) {
					$ev['image'] = $img;
				}
				$nodes[] = $ev;
			}
		} elseif ( is_singular( 'post' ) ) {
			$id      = get_queried_object_id();
			$img     = function_exists( 'dck_post_image_url' ) ? dck_post_image_url( $id ) : ( has_post_thumbnail( $id ) ? get_the_post_thumbnail_url( $id, 'large' ) : '' );
			$nodes[] = array_filter(
				array(
					'@type'            => 'BlogPosting',
					'@id'              => $page_url . '#article',
					'headline'         => get_the_title( $id ),
					'description'      => wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $id ) ), 40 ),
					'image'            => $img ? $img : dck_seo_image_default(),
					'datePublished'    => get_the_date( 'c', $id ),
					'dateModified'     => get_the_modified_date( 'c', $id ),
					'author'           => $church,
					'publisher'        => $church,
					'isPartOf'         => array( '@id' => dck_seo_id( 'website' ) ),
					'mainEntityOfPage' => array( '@id' => $page_url . '#webpage' ),
				)
			);
		}

		if ( is_singular() && ! is_page_template( 'template-faq.php' ) ) {
			$faq = dck_faq_from_post( get_queried_object_id() );
			if ( $faq ) {
				unset( $faq['@context'] );
				$faq['@id'] = $page_url . '#faq';
				$nodes[]    = $faq;
			}
		}
		return $nodes;
	}
}
