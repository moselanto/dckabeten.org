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

/* Extra JSON-LD graphs in the head. */
add_action( 'wp_head', 'dck_seo_extra', 2 );
if ( ! function_exists( 'dck_seo_extra' ) ) {
	function dck_seo_extra() {
		if ( is_admin() ) {
			return;
		}
		$graphs = array();

		if ( ! is_front_page() ) {
			$items = dck_bc_items();
			if ( count( $items ) >= 2 ) {
				$el = array();
				foreach ( $items as $i => $it ) {
					$el[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => $it['name'], 'item' => $it['url'] );
				}
				$graphs[] = array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $el );
			}
		}

		if ( is_singular( 'dck_sermon' ) ) {
			$id    = get_queried_object_id();
			$video = get_post_meta( $id, '_dck_video_url', true );
			$thumb = has_post_thumbnail( $id ) ? get_the_post_thumbnail_url( $id, 'large' ) : get_post_meta( $id, '_dck_sermon_image', true );
			if ( ! $thumb && function_exists( 'dck_seo_image_default' ) ) {
				$thumb = dck_seo_image_default();
			}
			$desc = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $id ) ), 40 );
			$base = array(
				'@context'    => 'https://schema.org',
				'name'        => get_the_title( $id ),
				'description' => $desc,
				'url'         => get_permalink( $id ),
				'thumbnailUrl' => $thumb,
			);
			if ( $video ) {
				$base['@type']      = 'VideoObject';
				$base['embedUrl']   = $video;
				$base['contentUrl'] = $video;
				$base['uploadDate'] = get_the_date( 'c', $id );
			} else {
				$base['@type']         = 'Article';
				$base['image']         = $thumb;
				$base['datePublished'] = get_the_date( 'c', $id );
				$base['author']        = array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ) );
			}
			$graphs[] = $base;
		} elseif ( is_singular( 'dck_event' ) ) {
			$id    = get_queried_object_id();
			$date  = get_post_meta( $id, '_dck_event_date', true );
			$start = get_post_meta( $id, '_dck_event_start', true );
			$end   = get_post_meta( $id, '_dck_event_end', true );
			$loc   = get_post_meta( $id, '_dck_event_location', true );
			$ev    = array(
				'@context'            => 'https://schema.org',
				'@type'               => 'Event',
				'name'                => get_the_title( $id ),
				'url'                 => get_permalink( $id ),
				'description'         => wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $id ) ), 40 ),
				'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
				'eventStatus'         => 'https://schema.org/EventScheduled',
			);
			if ( $date ) {
				$ev['startDate'] = $start ? ( $date . 'T' . dck_seo_time24( $start ) . '+03:00' ) : $date;
				if ( $end ) {
					$ev['endDate'] = $date . 'T' . dck_seo_time24( $end ) . '+03:00';
				}
			}
			$ev['location']  = array(
				'@type'   => 'Place',
				'name'    => $loc ? $loc : get_bloginfo( 'name' ),
				'address' => array(
					'@type'           => 'PostalAddress',
					'streetAddress'   => get_theme_mod( 'dck_top_address', 'Behind N Market, Uthiru, Kabete' ),
					'addressLocality' => get_theme_mod( 'dck_seo_locality', 'Uthiru' ),
					'addressRegion'   => get_theme_mod( 'dck_seo_region', 'Nairobi' ),
					'addressCountry'  => 'KE',
				),
			);
			$ev['organizer'] = array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ) );
			$graphs[]        = $ev;
		} elseif ( is_singular( 'post' ) ) {
			$id  = get_queried_object_id();
			$img = function_exists( 'dck_post_image_url' ) ? dck_post_image_url( $id ) : ( has_post_thumbnail( $id ) ? get_the_post_thumbnail_url( $id, 'large' ) : '' );
			$graphs[] = array(
				'@context'         => 'https://schema.org',
				'@type'            => 'BlogPosting',
				'headline'         => get_the_title( $id ),
				'description'      => wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $id ) ), 40 ),
				'image'            => $img,
				'datePublished'    => get_the_date( 'c', $id ),
				'dateModified'     => get_the_modified_date( 'c', $id ),
				'author'           => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ) ),
				'publisher'        => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ), 'logo' => array( '@type' => 'ImageObject', 'url' => function_exists( 'dck_seo_image_default' ) ? dck_seo_image_default() : '' ) ),
				'mainEntityOfPage' => get_permalink( $id ),
				'url'              => get_permalink( $id ),
			);
		}

		if ( is_singular() ) {
			$faq = dck_faq_from_post( get_queried_object_id() );
			if ( $faq ) {
				$graphs[] = $faq;
			}
		}

		foreach ( $graphs as $g ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $g, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . chr( 10 );
		}
	}
}
