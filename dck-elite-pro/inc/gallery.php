<?php
/**
 * DCK Elite - Unlimited Photo Gallery (v3.7.0).
 *
 * Manage photos via: Gallery Photos > Add New in wp-admin.
 *   - Upload the photo as the Featured Image.
 *   - Enter a caption as the Title (optional).
 *   - Assign a Gallery Category to enable filter tabs.
 *
 * Falls back to bundled images in assets/images/gallery/ when
 * no gallery posts are published yet.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   1. Image sizes
   ============================================================ */
add_action( 'after_setup_theme', 'dck_gallery_image_sizes' );
if ( ! function_exists( 'dck_gallery_image_sizes' ) ) {
	function dck_gallery_image_sizes() {
		/* Grid thumb: max 700 wide, natural height (masonry friendly). */
		add_image_size( 'dck-gallery-thumb', 700, 0, false );
	}
}

/* ============================================================
   2. Custom post type: dck_photo
   ============================================================ */
add_action( 'init', 'dck_gallery_register_cpt' );
if ( ! function_exists( 'dck_gallery_register_cpt' ) ) {
	function dck_gallery_register_cpt() {
		register_post_type( 'dck_photo', array(
			'labels' => array(
				'name'               => __( 'Gallery Photos',       'dck-elite' ),
				'singular_name'      => __( 'Gallery Photo',        'dck-elite' ),
				'add_new_item'       => __( 'Add New Photo',        'dck-elite' ),
				'edit_item'          => __( 'Edit Photo',           'dck-elite' ),
				'view_item'          => __( 'View Photo',           'dck-elite' ),
				'all_items'          => __( 'All Photos',           'dck-elite' ),
				'menu_name'          => __( 'Gallery Photos',       'dck-elite' ),
				'not_found'          => __( 'No photos found.',     'dck-elite' ),
				'not_found_in_trash' => __( 'No photos in Trash.', 'dck-elite' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_icon'       => 'dashicons-format-gallery',
			'menu_position'   => 25,
			'supports'        => array( 'title', 'thumbnail' ),
			'has_archive'     => false,
			'rewrite'         => false,
			'show_in_rest'    => false,
			'capability_type' => 'post',
			'description'     => __( 'Set a Featured Image as the photo. Title = caption (optional). Assign Gallery Categories for filter tabs.', 'dck-elite' ),
		) );
	}
}

/* ============================================================
   3. Taxonomy: dck_gallery_cat
   ============================================================ */
add_action( 'init', 'dck_gallery_register_tax' );
if ( ! function_exists( 'dck_gallery_register_tax' ) ) {
	function dck_gallery_register_tax() {
		register_taxonomy( 'dck_gallery_cat', array( 'dck_photo' ), array(
			'labels' => array(
				'name'              => __( 'Gallery Categories', 'dck-elite' ),
				'singular_name'     => __( 'Gallery Category',  'dck-elite' ),
				'add_new_item'      => __( 'Add New Category',  'dck-elite' ),
				'edit_item'         => __( 'Edit Category',     'dck-elite' ),
				'all_items'         => __( 'All Categories',    'dck-elite' ),
			),
			'hierarchical' => true,
			'show_ui'      => true,
			'show_in_rest' => false,
			'rewrite'      => false,
		) );
	}
}

/* ============================================================
   4. Query helpers
   ============================================================ */
if ( ! function_exists( 'dck_gallery_get_photos' ) ) {
	function dck_gallery_get_photos( $per = 20, $category = '' ) {
		$args = array(
			'post_type'              => 'dck_photo',
			'post_status'            => 'publish',
			'posts_per_page'         => (int) $per,
			'update_post_term_cache' => false,
			'no_found_rows'          => true,
		);
		if ( '' !== $category && 'all' !== $category ) {
			$args['tax_query'] = array( array(
				'taxonomy' => 'dck_gallery_cat',
				'field'    => 'slug',
				'terms'    => $category,
			) );
		}
		return get_posts( $args );
	}
}

if ( ! function_exists( 'dck_gallery_categories' ) ) {
	function dck_gallery_categories() {
		$terms = get_terms( array( 'taxonomy' => 'dck_gallery_cat', 'hide_empty' => true ) );
		return is_array( $terms ) ? $terms : array();
	}
}

/* ============================================================
   5. REST endpoint — AJAX load-more and category filter
   ============================================================ */
add_action( 'rest_api_init', 'dck_gallery_register_route' );
if ( ! function_exists( 'dck_gallery_register_route' ) ) {
	function dck_gallery_register_route() {
		register_rest_route( 'dck/v1', '/gallery', array(
			'methods'             => 'GET',
			'callback'            => 'dck_gallery_rest',
			'permission_callback' => '__return_true',
			'args'                => array(
				'page'     => array( 'type' => 'integer', 'default' => 1, 'minimum' => 1 ),
				'per_page' => array( 'type' => 'integer', 'default' => 20, 'minimum' => 1, 'maximum' => 40 ),
				'category' => array( 'type' => 'string',  'default' => '' ),
			),
		) );
	}
}

if ( ! function_exists( 'dck_gallery_rest' ) ) {
	function dck_gallery_rest( $request ) {
		$page = max( 1, (int) $request->get_param( 'page' ) );
		$per  = min( 40, max( 1, (int) $request->get_param( 'per_page' ) ) );
		$cat  = sanitize_text_field( (string) $request->get_param( 'category' ) );

		$args = array(
			'post_type'              => 'dck_photo',
			'post_status'            => 'publish',
			'posts_per_page'         => $per,
			'paged'                  => $page,
			'no_found_rows'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);
		if ( '' !== $cat && 'all' !== $cat ) {
			$args['tax_query'] = array( array(
				'taxonomy' => 'dck_gallery_cat',
				'field'    => 'slug',
				'terms'    => $cat,
			) );
		}

		$q      = new WP_Query( $args );
		$photos = array();
		foreach ( $q->posts as $post ) {
			$thumb = get_the_post_thumbnail_url( $post->ID, 'dck-gallery-thumb' );
			if ( ! $thumb ) {
				$thumb = get_the_post_thumbnail_url( $post->ID, 'medium_large' );
			}
			$full = get_the_post_thumbnail_url( $post->ID, 'large' );
			if ( ! $full ) {
				$full = get_the_post_thumbnail_url( $post->ID, 'full' );
			}
			if ( ! $thumb || ! $full ) {
				continue;
			}
			$src      = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium_large' );
			$photos[] = array(
				'shape'   => function_exists( 'dck_gallery_shape' ) ? dck_gallery_shape( $src ) : '',
				'id'      => $post->ID,
				'thumb'   => esc_url_raw( $thumb ),
				'full'    => esc_url_raw( $full ),
				'caption' => esc_html( $post->post_title ),
			);
		}
		$total    = (int) $q->found_posts;
		$has_more = ( $page * $per ) < $total;
		return new WP_REST_Response( array(
			'photos'   => $photos,
			'has_more' => $has_more,
			'total'    => $total,
			'page'     => $page,
		), 200 );
	}
}

/* ============================================================
   6. Enqueue gallery JS (gallery template pages only)
   ============================================================ */
add_action( 'wp_enqueue_scripts', 'dck_gallery_enqueue' );
if ( ! function_exists( 'dck_gallery_enqueue' ) ) {
	function dck_gallery_enqueue() {
		if ( ! is_page_template( 'template-gallery.php' ) ) {
			return;
		}
		wp_enqueue_script(
			'dck-gallery',
			get_template_directory_uri() . '/assets/js/gallery.js',
			array(),
			DCK_ELITE_VERSION,
			true
		);
		wp_localize_script( 'dck-gallery', 'DCK_GALLERY', array(
			'rest'  => esc_url_raw( rest_url( 'dck/v1/' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		) );
	}
}

/* ============================================================
   7. Backward-compat: dck_gallery_images() returns URL array
      (used by any code that called this before v3.7.0)
   ============================================================ */
if ( ! function_exists( 'dck_gallery_images' ) ) {
	function dck_gallery_images() {
		$posts = dck_gallery_get_photos( 100 );
		if ( ! empty( $posts ) ) {
			$imgs = array();
			foreach ( $posts as $p ) {
				$u = get_the_post_thumbnail_url( $p->ID, 'large' );
				if ( ! $u ) {
					$u = get_the_post_thumbnail_url( $p->ID, 'full' );
				}
				if ( $u ) {
					$imgs[] = $u;
				}
			}
			if ( ! empty( $imgs ) ) {
				return $imgs;
			}
		}
		/* Fallback: bundled gallery images in assets/images/gallery/. */
		$dir   = get_template_directory()     . '/assets/images/gallery/';
		$uri   = get_template_directory_uri() . '/assets/images/gallery/';
		$files = glob( $dir . '*.{webp,jpg,jpeg,png,gif,WEBP,JPG,JPEG,PNG}', GLOB_BRACE );
		if ( ! is_array( $files ) ) {
			$files = array();
		}
		sort( $files );
		$imgs = array();
		foreach ( $files as $f ) {
			$imgs[] = $uri . basename( $f );
		}
		return $imgs;
	}
}


if ( ! function_exists( 'dck_gallery_shape' ) ) {
	/** 'wide', 'tall' or '' from an image src array (url, w, h). */
	function dck_gallery_shape( $src ) {
		if ( ! is_array( $src ) || empty( $src[1] ) || empty( $src[2] ) ) {
			return '';
		}
		$r = $src[1] / $src[2];
		return $r >= 1.6 ? 'wide' : ( $r <= 0.8 ? 'tall' : '' );
	}
}
