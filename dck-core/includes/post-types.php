<?php
/**
 * Content types owned by the plugin so church content (sermons) survives a
 * theme change. Registered with exactly the same names, slugs and arguments as
 * the theme; the theme skips its own registration when these already exist.
 *
 * @package DCK_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_core_register_post_types' ) ) {
	function dck_core_register_post_types() {
		if ( ! post_type_exists( 'dck_sermon' ) ) {
			register_post_type(
				'dck_sermon',
				array(
					'labels'       => array(
						'name'          => __( 'Sermons', 'dck-core' ),
						'singular_name' => __( 'Sermon', 'dck-core' ),
						'add_new_item'  => __( 'Add New Sermon', 'dck-core' ),
						'edit_item'     => __( 'Edit Sermon', 'dck-core' ),
						'search_items'  => __( 'Search Sermons', 'dck-core' ),
						'menu_name'     => __( 'Sermons', 'dck-core' ),
					),
					'public'       => true,
					'has_archive'  => true,
					'show_in_rest' => true,
					'menu_icon'    => 'dashicons-microphone',
					'rewrite'      => array( 'slug' => 'sermons' ),
					'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'author' ),
				)
			);
		}

		$taxes = array(
			'dck_sermon_series'  => array( __( 'Series', 'dck-core' ), __( 'Series', 'dck-core' ), true, 'series' ),
			'dck_sermon_speaker' => array( __( 'Speakers', 'dck-core' ), __( 'Speaker', 'dck-core' ), false, 'speaker' ),
			'dck_sermon_topic'   => array( __( 'Topics', 'dck-core' ), __( 'Topic', 'dck-core' ), false, 'topic' ),
		);
		foreach ( $taxes as $tax => $t ) {
			if ( taxonomy_exists( $tax ) ) {
				continue;
			}
			register_taxonomy(
				$tax,
				'dck_sermon',
				array(
					'labels'       => array(
						'name'          => $t[0],
						'singular_name' => $t[1],
					),
					'public'       => true,
					'hierarchical' => $t[2],
					'show_in_rest' => true,
					'rewrite'      => array( 'slug' => $t[3] ),
				)
			);
		}
	}
}
add_action( 'init', 'dck_core_register_post_types', 5 );
