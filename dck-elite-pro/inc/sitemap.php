<?php
/**
 * DCK Elite - XML sitemap hardening.
 * Builds on the WordPress core sitemap (wp-sitemap.xml):
 *  - Drops the authors/users sitemap (privacy + enumeration hardening).
 *  - Excludes any content flagged noindex via _dck_seo_noindex.
 *  - Advertises the sitemap in robots.txt and blocks search-result crawling.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Make sure core sitemaps stay on. */
add_filter( 'wp_sitemaps_enabled', function () { return ! ( function_exists( 'dck_seo_external' ) && dck_seo_external() ); } ); /* One sitemap system: the SEO plugin's when active. */

/* Remove the users (authors) sitemap provider. */
add_filter( 'wp_sitemaps_add_provider', 'dck_sitemap_drop_users', 10, 2 );
if ( ! function_exists( 'dck_sitemap_drop_users' ) ) {
	function dck_sitemap_drop_users( $provider, $name ) {
		if ( 'users' === $name ) {
			return false;
		}
		return $provider;
	}
}

/* Exclude noindex-flagged posts/pages from the sitemap. */
add_filter( 'wp_sitemaps_posts_query_args', 'dck_sitemap_exclude_noindex', 10, 2 );
if ( ! function_exists( 'dck_sitemap_exclude_noindex' ) ) {
	function dck_sitemap_exclude_noindex( $args, $post_type ) {
		if ( ! isset( $args['meta_query'] ) || ! is_array( $args['meta_query'] ) ) {
			$args['meta_query'] = array();
		}
		$args['meta_query'][] = array(
			'relation' => 'OR',
			array( 'key' => '_dck_seo_noindex', 'compare' => 'NOT EXISTS' ),
			array( 'key' => '_dck_seo_noindex', 'value' => '1', 'compare' => 'NOT LIKE' ),
		);
		return $args;
	}
}

/* Advertise the sitemap in robots.txt and keep search results out of the index. */
add_filter( 'robots_txt', 'dck_sitemap_robots', 10, 2 );
if ( ! function_exists( 'dck_sitemap_robots' ) ) {
	function dck_sitemap_robots( $output, $public ) {
		if ( ! $public ) {
			return $output;
		}
		if ( false === strpos( (string) $output, 'wp-sitemap.xml' ) ) {
			$output .= 'Sitemap: ' . esc_url( home_url( '/wp-sitemap.xml' ) ) . chr( 10 );
		}
		$output .= 'Disallow: /?s=' . chr( 10 );
		$output .= 'Disallow: /search/' . chr( 10 );
		$output .= 'Disallow: /wp-json/dck/' . chr( 10 );
		/*
		 * Search/answer engines that cite and link back to the site (ChatGPT search, Perplexity,
		 * Claude search) are explicitly welcome on public pages. Model-training crawlers
		 * (GPTBot, Google-Extended, CCBot) are a separate church decision - filter 'dck_ai_training_bots'.
		 */
		$nl      = chr( 10 );
		$output .= $nl . 'User-agent: OAI-SearchBot' . $nl . 'Allow: /' . $nl . 'Disallow: /wp-admin/' . $nl . 'Disallow: /wp-json/dck/' . $nl;
		$output .= $nl . 'User-agent: PerplexityBot' . $nl . 'Allow: /' . $nl . 'Disallow: /wp-admin/' . $nl . 'Disallow: /wp-json/dck/' . $nl;
		$output .= $nl . 'User-agent: Claude-SearchBot' . $nl . 'Allow: /' . $nl . 'Disallow: /wp-admin/' . $nl . 'Disallow: /wp-json/dck/' . $nl;
		foreach ( (array) apply_filters( 'dck_ai_training_bots', array() ) as $bot ) {
			$output .= $nl . 'User-agent: ' . sanitize_text_field( $bot ) . $nl . 'Disallow: /' . $nl;
		}
		return $output;
	}
}
