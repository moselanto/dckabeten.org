<?php
/**
 * DCK Elite Pro theme functions.
 *
 * Version is defined once in DCK_ELITE_VERSION below (keep style.css header in sync).
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DCK_ELITE_VERSION' ) ) {
	define( 'DCK_ELITE_VERSION', '4.8.0' );
}

/**
 * Show a friendly admin notice on very old PHP instead of risking a fatal.
 */
if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice notice-warning"><p>DCK Elite works best on PHP 7.4 or newer. Your server runs PHP ' . esc_html( PHP_VERSION ) . '. Please ask your host to upgrade PHP.</p></div>';
		}
	);
}

if ( ! function_exists( 'dck_elite_setup' ) ) {
	function dck_elite_setup() {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support(
			'custom-logo',
			array( 'height' => 80, 'width' => 320, 'flex-height' => true, 'flex-width' => true )
		);
		register_nav_menus(
			array(
				'primary' => __( 'Primary Menu', 'dck-elite' ),
				'footer'  => __( 'Footer Menu', 'dck-elite' ),
			)
		);
	}
}
add_action( 'after_setup_theme', 'dck_elite_setup' );

if ( ! function_exists( 'dck_elite_assets' ) ) {
	function dck_elite_assets() {
		// Main stylesheet has NO external dependency, so it always loads.
		wp_enqueue_style( 'dck-elite', get_stylesheet_uri(), array(), DCK_ELITE_VERSION );

		// Fonts are self-hosted via @font-face in style.css (no render-blocking external request).

		wp_enqueue_script( 'dck-elite', get_template_directory_uri() . '/assets/js/main.js', array(), DCK_ELITE_VERSION, true );

		if ( is_front_page() ) {
			wp_enqueue_script( 'dck-elite-home', get_template_directory_uri() . '/assets/js/home.js', array( 'dck-elite' ), DCK_ELITE_VERSION, true );
		}
		wp_enqueue_script( 'dck-elite-a11y', get_template_directory_uri() . '/assets/js/a11y.js', array(), DCK_ELITE_VERSION, true );
		if ( is_singular( 'dck_sermon' ) || is_page_template( 'template-bible.php' ) ) {
			wp_enqueue_script( 'dck-read-aloud', get_template_directory_uri() . '/assets/js/read-aloud.js', array(), DCK_ELITE_VERSION, true );
		}
		wp_enqueue_script( 'dck-translate', get_template_directory_uri() . '/assets/js/translate.js', array(), DCK_ELITE_VERSION, true );
	}
}
add_action( 'wp_enqueue_scripts', 'dck_elite_assets' );

if ( ! function_exists( 'dck_elite_widgets' ) ) {
	function dck_elite_widgets() {
		register_sidebar(
			array(
				'name'          => __( 'Footer Widgets', 'dck-elite' ),
				'id'            => 'footer-1',
				'description'   => __( 'Appears in the footer.', 'dck-elite' ),
				'before_widget' => '<div class="dck-widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			)
		);
	}
}
add_action( 'widgets_init', 'dck_elite_widgets' );

if ( ! function_exists( 'dck_elite_content_width' ) ) {
	function dck_elite_content_width() {
		if ( ! isset( $GLOBALS['content_width'] ) ) {
			$GLOBALS['content_width'] = 1180;
		}
	}
}
add_action( 'after_setup_theme', 'dck_elite_content_width', 0 );

/**
 * One-time site setup version. Only bump this when the page/menu setup itself changes,
 * NOT on every theme release, so routine updates never rebuild the menu or overwrite
 * manual changes made in wp-admin.
 */
if ( ! defined( 'DCK_ELITE_SETUP_VERSION' ) ) {
	define( 'DCK_ELITE_SETUP_VERSION', '4.2.3' );
}

if ( ! function_exists( 'dck_elite_service_times' ) ) {
	/**
	 * Weekly gatherings. Editable in DCK Church > Service Times; defaults live in inc/content.php.
	 */
	function dck_elite_service_times() {
		$rows = get_theme_mod( 'dck_service_times', array() );
		$out  = array();
		if ( is_array( $rows ) ) {
			foreach ( $rows as $r ) {
				if ( ! empty( $r['name'] ) ) {
					$out[] = array( 'name' => (string) $r['name'], 'time' => isset( $r['time'] ) ? (string) $r['time'] : '' );
				}
			}
		}
		if ( empty( $out ) && function_exists( 'dck_elite_default_service_times' ) ) {
			$out = dck_elite_default_service_times();
		}
		return $out;
	}
}

if ( ! function_exists( 'dck_elite_ministries' ) ) {
	/**
	 * All ministry departments. Derived from the homepage department list
	 * (inc/home.php) so every page shows the same, complete set.
	 */
	function dck_elite_ministries() {
		if ( function_exists( 'dck_all_departments' ) ) {
			return dck_all_departments();
		}
		return array();
	}
}

if ( ! function_exists( 'dck_elite_fallback_menu' ) ) {
	function dck_elite_fallback_menu() {
		$about    = dck_elite_page_url( 'about' );
		$pastors  = dck_elite_page_url( 'our-pastors' );
		$services = dck_elite_page_url( 'services' );
		$min      = dck_elite_page_url( 'ministries' );
		$events   = dck_elite_page_url( 'events' );
		$live     = dck_elite_page_url( 'live' );
		$gallery  = dck_elite_page_url( 'gallery' );
		$blog     = dck_elite_page_url( 'blog' );
		$give     = dck_elite_page_url( 'give' );
		$bible    = dck_elite_page_url( 'bible' );
		$contact  = dck_elite_page_url( 'contact' );
		$faq      = dck_elite_page_url( 'faq' );
		$sermons  = get_post_type_archive_link( 'dck_sermon' );
		if ( ! $sermons ) { $sermons = home_url( '/' ); }
		echo '<ul>';
		echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'dck-elite' ) . '</a></li>';
		echo '<li class="menu-item-has-children"><a href="' . esc_url( $about ) . '">' . esc_html__( 'Who We Are', 'dck-elite' ) . '</a><ul class="sub-menu">';
		echo '<li><a href="' . esc_url( $about ) . '">' . esc_html__( 'About Us', 'dck-elite' ) . '</a></li>';
		echo '<li><a href="' . esc_url( $pastors ) . '">' . esc_html__( 'Our Pastors', 'dck-elite' ) . '</a></li>';
		echo '<li><a href="' . esc_url( $faq ) . '">' . esc_html__( 'FAQ', 'dck-elite' ) . '</a></li>';
		echo '</ul></li>';
		echo '<li><a href="' . esc_url( $services ) . '">' . esc_html__( 'Services', 'dck-elite' ) . '</a></li>';
		echo '<li><a href="' . esc_url( $min ) . '">' . esc_html__( 'Ministries', 'dck-elite' ) . '</a></li>';
		echo '<li class="menu-item-has-children"><a href="' . esc_url( $events ) . '">' . esc_html__( 'Media', 'dck-elite' ) . '</a><ul class="sub-menu">';
		echo '<li><a href="' . esc_url( $events ) . '">' . esc_html__( 'Events', 'dck-elite' ) . '</a></li>';
		echo '<li><a href="' . esc_url( $live ) . '">' . esc_html__( 'Live Stream', 'dck-elite' ) . '</a></li>';
		echo '<li><a href="' . esc_url( $gallery ) . '">' . esc_html__( 'Photo Gallery', 'dck-elite' ) . '</a></li>';
		echo '<li><a href="' . esc_url( $blog ) . '">' . esc_html__( 'Blog', 'dck-elite' ) . '</a></li>';
		echo '</ul></li>';
		echo '<li><a href="' . esc_url( $give ) . '">' . esc_html__( 'Giving', 'dck-elite' ) . '</a></li>';
		echo '<li><a href="' . esc_url( $sermons ) . '">' . esc_html__( 'Sermons', 'dck-elite' ) . '</a></li>';
		echo '<li><a href="' . esc_url( $bible ) . '">' . esc_html__( 'Bible', 'dck-elite' ) . '</a></li>';
		echo '<li><a href="' . esc_url( $contact ) . '">' . esc_html__( 'Contact', 'dck-elite' ) . '</a></li>';
		echo '</ul>';
	}
}


/**
 * Sermons content type — built into the theme so no separate plugin is required.
 * Guarded with post_type_exists() so it never conflicts if a DCK Core plugin is added later.
 */
if ( ! function_exists( 'dck_elite_register_sermons' ) ) {
	function dck_elite_register_sermons() {

		if ( ! post_type_exists( 'dck_sermon' ) ) {
			register_post_type(
				'dck_sermon',
				array(
					'labels'       => array(
						'name'          => __( 'Sermons', 'dck-elite' ),
						'singular_name' => __( 'Sermon', 'dck-elite' ),
						'add_new_item'  => __( 'Add New Sermon', 'dck-elite' ),
						'edit_item'     => __( 'Edit Sermon', 'dck-elite' ),
						'search_items'  => __( 'Search Sermons', 'dck-elite' ),
						'menu_name'     => __( 'Sermons', 'dck-elite' ),
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

		if ( ! taxonomy_exists( 'dck_sermon_series' ) ) {
			register_taxonomy(
				'dck_sermon_series',
				'dck_sermon',
				array(
					'labels'       => array(
						'name'          => __( 'Series', 'dck-elite' ),
						'singular_name' => __( 'Series', 'dck-elite' ),
					),
					'public'       => true,
					'hierarchical' => true,
					'show_in_rest' => true,
					'rewrite'      => array( 'slug' => 'series' ),
				)
			);
		}

		if ( ! taxonomy_exists( 'dck_sermon_speaker' ) ) {
			register_taxonomy(
				'dck_sermon_speaker',
				'dck_sermon',
				array(
					'labels'       => array(
						'name'          => __( 'Speakers', 'dck-elite' ),
						'singular_name' => __( 'Speaker', 'dck-elite' ),
					),
					'public'       => true,
					'hierarchical' => false,
					'show_in_rest' => true,
					'rewrite'      => array( 'slug' => 'speaker' ),
				)
			);
		}

		if ( ! taxonomy_exists( 'dck_sermon_topic' ) ) {
			register_taxonomy(
				'dck_sermon_topic',
				'dck_sermon',
				array(
					'labels'       => array(
						'name'          => __( 'Topics', 'dck-elite' ),
						'singular_name' => __( 'Topic', 'dck-elite' ),
					),
					'public'       => true,
					'hierarchical' => false,
					'show_in_rest' => true,
					'rewrite'      => array( 'slug' => 'topic' ),
				)
			);
		}
	}
}
add_action( 'init', 'dck_elite_register_sermons' );

/**
 * Flush permalinks once after this theme version registers the Sermon CPT,
 * so /sermons/ works without visiting Settings > Permalinks manually.
 */
if ( ! function_exists( 'dck_elite_maybe_flush' ) ) {
	function dck_elite_maybe_flush() {
		if ( get_option( 'dck_elite_flushed_ver' ) !== DCK_ELITE_VERSION ) {
			dck_elite_register_sermons();
			flush_rewrite_rules();
			update_option( 'dck_elite_flushed_ver', DCK_ELITE_VERSION );
		}
	}
}
add_action( 'after_switch_theme', 'dck_elite_maybe_flush' );
add_action( 'init', 'dck_elite_maybe_flush', 20 );


/* =====================================================================
 * PHASE 2 - Live streaming + Sermon platform
 * ===================================================================== */

/**
 * Customizer: live streaming settings.
 */
if ( ! function_exists( 'dck_elite_customize_register' ) ) {
	function dck_elite_customize_register( $wp_customize ) {
		$wp_customize->add_section(
			'dck_live',
			array(
				'title'       => __( 'DCK Live Streaming', 'dck-elite' ),
				'priority'    => 30,
				'description' => __( 'Connect your YouTube or Facebook so live services appear on the Watch Live page automatically.', 'dck-elite' ),
			)
		);

		$wp_customize->add_setting( 'dck_youtube_channel_id', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
		$wp_customize->add_control(
			'dck_youtube_channel_id',
			array(
				'label'       => __( 'YouTube Channel ID', 'dck-elite' ),
				'description' => __( 'Starts with UC... From YouTube Studio > Settings > Channel > Advanced. When you go live, the page shows the stream automatically.', 'dck-elite' ),
				'section'     => 'dck_live',
				'type'        => 'text',
			)
		);

		$wp_customize->add_setting( 'dck_youtube_live_url', array( 'sanitize_callback' => 'esc_url_raw', 'default' => '' ) );
		$wp_customize->add_control(
			'dck_youtube_live_url',
			array(
				'label'       => __( 'YouTube Live/Video URL (fallback)', 'dck-elite' ),
				'description' => __( 'Optional. Paste a specific YouTube live or video link if you are not using a Channel ID.', 'dck-elite' ),
				'section'     => 'dck_live',
				'type'        => 'url',
			)
		);

		$wp_customize->add_setting( 'dck_facebook_url', array( 'sanitize_callback' => 'esc_url_raw', 'default' => '' ) );
		$wp_customize->add_control(
			'dck_facebook_url',
			array(
				'label'       => __( 'Facebook Video URL (fallback)', 'dck-elite' ),
				'description' => __( 'Optional. Paste a Facebook live/video link to embed instead of YouTube.', 'dck-elite' ),
				'section'     => 'dck_live',
				'type'        => 'url',
			)
		);

		$wp_customize->add_setting( 'dck_youtube_playlist_url', array( 'sanitize_callback' => 'esc_url_raw', 'default' => '' ) );
		$wp_customize->add_control(
			'dck_youtube_playlist_url',
			array(
				'label'       => __( 'YouTube Playlist URL (past services)', 'dck-elite' ),
				'description' => __( 'Optional. A playlist of past services shown in the archive on the Watch Live page.', 'dck-elite' ),
				'section'     => 'dck_live',
				'type'        => 'url',
			)
		);
	}
}
add_action( 'customize_register', 'dck_elite_customize_register' );

/**
 * Turn a YouTube/Facebook URL into a responsive embed.
 */
if ( ! function_exists( 'dck_elite_video_embed' ) ) {
	function dck_elite_video_embed( $url, $title = '' ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return '';
		}
		$title = $title ? $title : get_bloginfo( 'name' );
		$lc    = strtolower( $url );
		$embed = '';

		if ( false !== strpos( $lc, 'youtu' ) ) {
			$id = '';
			if ( false !== strpos( $lc, 'watch' ) ) {
				$q = wp_parse_url( $url, PHP_URL_QUERY );
				if ( $q ) {
					parse_str( $q, $vars );
					if ( ! empty( $vars['v'] ) ) {
						$id = $vars['v'];
					}
				}
			} else {
				$path = wp_parse_url( $url, PHP_URL_PATH );
				if ( $path ) {
					$parts = explode( '/', trim( $path, '/' ) );
					$id    = end( $parts );
				}
			}
			if ( $id ) {
				$embed = '<iframe src="https://www.youtube.com/embed/' . esc_attr( $id ) . '" title="' . esc_attr( $title ) . '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>';
			}
		} elseif ( false !== strpos( $lc, 'facebook' ) || false !== strpos( $lc, 'fb.watch' ) ) {
			$embed = '<iframe src="https://www.facebook.com/plugins/video.php?show_text=false&href=' . rawurlencode( $url ) . '" title="' . esc_attr( $title ) . '" allowfullscreen loading="lazy" scrolling="no"></iframe>';
		} else {
			$embed = '<iframe src="' . esc_url( $url ) . '" title="' . esc_attr( $title ) . '" allowfullscreen loading="lazy"></iframe>';
		}

		return '<div class="dck-embed">' . $embed . '</div>';
	}
}

/**
 * Timestamp (ms) of the next Sunday Glorious Service (10:40 in the site timezone).
 */
if ( ! function_exists( 'dck_elite_next_service_ms' ) ) {
	function dck_elite_next_service_ms() {
		$tz     = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'Africa/Nairobi' );
		$now    = new DateTime( 'now', $tz );
		$target = new DateTime( 'now', $tz );
		$day    = (int) dck_home_val( 'dck_live_day', '0' );
		$hm     = explode( ':', (string) dck_home_val( 'dck_live_start', '10:40' ) );
		$days   = ( 7 + $day - (int) $now->format( 'w' ) ) % 7;
		$target->modify( '+' . $days . ' days' );
		$target->setTime( (int) $hm[0], isset( $hm[1] ) ? (int) $hm[1] : 0, 0 );
		if ( $target <= $now ) {
			$target->modify( '+7 days' );
		}
		return $target->getTimestamp() * 1000;
	}
}

/**
 * Sermon details meta box.
 */
if ( ! function_exists( 'dck_elite_sermon_meta_box' ) ) {
	function dck_elite_sermon_meta_box() {
		add_meta_box( 'dck_sermon_details', __( 'Sermon Details', 'dck-elite' ), 'dck_elite_sermon_meta_cb', 'dck_sermon', 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes', 'dck_elite_sermon_meta_box' );

if ( ! function_exists( 'dck_elite_sermon_meta_cb' ) ) {
	function dck_elite_sermon_meta_cb( $post ) {
		wp_nonce_field( 'dck_sermon_meta', 'dck_sermon_meta_nonce' );
		$video     = get_post_meta( $post->ID, '_dck_video_url', true );
		$audio     = get_post_meta( $post->ID, '_dck_audio_url', true );
		$scripture = get_post_meta( $post->ID, '_dck_scripture', true );
		$download  = get_post_meta( $post->ID, '_dck_download_url', true );
		$simg      = get_post_meta( $post->ID, '_dck_sermon_image', true );
		echo '<p><label><strong>' . esc_html__( 'Video URL (YouTube/Facebook)', 'dck-elite' ) . '</strong><br><input type="url" style="width:100%" name="dck_video_url" value="' . esc_attr( $video ) . '" placeholder="https://www.youtube.com/watch?v=..."></label></p>';
		echo '<p><label><strong>' . esc_html__( 'Audio URL (MP3)', 'dck-elite' ) . '</strong><br><input type="url" style="width:100%" name="dck_audio_url" value="' . esc_attr( $audio ) . '" placeholder="https://.../sermon.mp3"></label></p>';
		echo '<p><label><strong>' . esc_html__( 'Scripture Reference', 'dck-elite' ) . '</strong><br><input type="text" style="width:100%" name="dck_scripture" value="' . esc_attr( $scripture ) . '" placeholder="John 3:16-21"></label></p>';
		echo '<p><label><strong>' . esc_html__( 'Download URL (notes/PDF)', 'dck-elite' ) . '</strong><br><input type="url" style="width:100%" name="dck_download_url" value="' . esc_attr( $download ) . '"></label></p>';
		echo '<p><label><strong>' . esc_html__( 'Sermon Image URL (optional, used if no Featured Image)', 'dck-elite' ) . '</strong><br><input type="url" style="width:100%" name="dck_sermon_image" value="' . esc_attr( $simg ) . '"></label></p>';
	}
}

if ( ! function_exists( 'dck_elite_save_sermon_meta' ) ) {
	function dck_elite_save_sermon_meta( $post_id ) {
		if ( ! isset( $_POST['dck_sermon_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dck_sermon_meta_nonce'] ), 'dck_sermon_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$fields = array(
			'_dck_video_url'    => 'esc_url_raw',
			'_dck_audio_url'    => 'esc_url_raw',
			'_dck_scripture'    => 'sanitize_text_field',
			'_dck_download_url' => 'esc_url_raw',
			'_dck_sermon_image' => 'esc_url_raw',
		);
		foreach ( $fields as $meta => $cb ) {
			$key = ltrim( $meta, '_' );
			if ( isset( $_POST[ $key ] ) ) {
				$val = call_user_func( $cb, wp_unslash( $_POST[ $key ] ) );
				update_post_meta( $post_id, $meta, $val );
			}
		}
	}
}
add_action( 'save_post_dck_sermon', 'dck_elite_save_sermon_meta' );

/**
 * Seed the church's real sermons once, so the Sermons archive is populated on install.
 * Church can edit, delete, or add more under Sermons in wp-admin.
 */
if ( ! function_exists( 'dck_elite_seed_sermons' ) ) {
	function dck_elite_seed_sermons() {
		if ( get_option( 'dck_sermons_seeded' ) ) {
			return;
		}
		if ( ! post_type_exists( 'dck_sermon' ) ) {
			return;
		}
		$base = get_template_directory_uri() . '/assets/images/sermons/';
		$sp   = 'Pastor Justus Kubai';
		$seeds = array(
			array(
				"title"  => "Extending God's Kingdom Through Preaching",
				"date"   => "2026-04-26 10:40:00",
				"scr"    => "Matthew 24:14 (NLT)",
				"img"    => "extending-god-kingdom.webp",
				"series" => "Extending God's Kingdom",
				"exc"    => "God extends His Kingdom through the faithful preaching of the Gospel by every believer. You are not just a church member, you are a kingdom extender.",
				"body"   => "<p><strong>Scripture:</strong> Matthew 24:14 (NLT) - And the Good News about the Kingdom will be preached throughout the whole world, so that all nations will hear it; and then the end will come.</p><p>Beloved Cistern of Hope family, God extends His Kingdom through the faithful preaching of the Gospel by every believer. You are not just a church member, you are a kingdom extender.</p><h3>1. Through Divine Encounters</h3><p>God uses personal encounters to raise those who will preach His message - the Four Lepers (2 Kings 7:9-11), the Samaritan Woman (John 4:28-30), and the Delivered Man of the Decapolis (Mark 5:19-20). When you truly encounter Jesus, you cannot remain silent; your testimony becomes your message.</p><h3>2. Through Persecution and Challenges</h3><p>What looks like a setback can become a divine strategy. When the early church was scattered (Acts 8:4-5) they went everywhere preaching the Word, and the Gospel reached new regions (Acts 11:19-20).</p><p>Do not leave as a listener - leave as a witness. Let us arise and extend His Kingdom together. Shalom.</p>",
			),
			array(
				"title"  => "Advancing God's Kingdom",
				"date"   => "2026-04-18 10:40:00",
				"scr"    => "Matthew 6:10; Matthew 13:31-32",
				"img"    => "advancing-the-kingdom.webp",
				"series" => "Extending God's Kingdom",
				"exc"    => "We are all products of giving (John 3:16). God's Kingdom can be extended to new regions and bring remarkable impact through giving.",
				"body"   => "<p><strong>Scripture:</strong> Matthew 6:10; Matthew 13:31-32.</p><p>Are you aware that as believers in Christ we are all products of giving? John 3:16 - For God so loved the world that He gave His only Son. Giving is freely releasing something to be owned by another.</p><p>Like the mustard seed that a man sowed, a small seed grows into the largest of plants and blesses the birds of the air. In the same way God's Kingdom is extended to unknown places through giving.</p><h3>We can give</h3><p>Our lives, our time, our resources and our finances.</p><h3>Examples in Scripture</h3><p>The women who supported Jesus (Luke 8:1-3), the little boy with five loaves and two fish (John 6:9), and a very poor church that begged for the opportunity to give (2 Corinthians 8:1-4). Giving is not about wealth, it is about a willing heart - God looks at the sacrifice, not the size.</p>",
			),
			array(
				"title"  => "Conquering the Land",
				"date"   => "2026-03-22 10:40:00",
				"scr"    => "Joshua 1:3",
				"img"    => "conquering-the-land.webp",
				"series" => "",
				"exc"    => "Every place that the sole of your foot treads God has given you. Five keys to conquering the land God has promised.",
				"body"   => "<p><strong>Scripture:</strong> Joshua 1:3.</p><p>God has already given us the land; our part is to rise and possess it. Here is how we conquer:</p><ol><li><strong>Be strong and courageous</strong> - Joshua 1:9; Deuteronomy 31:6.</li><li><strong>Meditate on the Word of God</strong> - Joshua 1:8; Psalm 119:11.</li><li><strong>Trust in God's presence</strong> - Joshua 1:9; Isaiah 41:10.</li><li><strong>Be bold in sharing the Gospel</strong> - Acts 4:31.</li><li><strong>Rely on God's strength</strong> - Matthew 6:33; Philippians 4:13.</li></ol>",
			),
			array(
				"title"  => "Divine Restoration",
				"date"   => "2026-03-15 10:40:00",
				"scr"    => "2 Kings 8:1-6",
				"img"    => "divine-restoration.webp",
				"series" => "",
				"exc"    => "Divine restoration requires faith and obedience. Some losses are meant to preserve and secure our future.",
				"body"   => "<p><strong>Scripture:</strong> 2 Kings 8:1-6.</p><h3>Take-away lessons</h3><ul><li>Divine restoration requires faith and obedience to instructions (vs 1-2).</li><li>Some losses are meant to preserve and secure futures.</li><li>Waiting on God is a sign of maturity.</li><li>It is biblically right to claim divine restoration (vs 3b).</li><li>Restored people become living testimonies that encourage others.</li></ul><h3>Results of double restoration</h3><ul><li>It brings re-union (Job 42:10-11; Luke 15:24).</li><li>It brings overflowing joy (Psalm 126:2-3; Luke 15:23).</li><li>It takes away shame - the finest robe, a ring and sandals (Luke 15:22; Isaiah 61:7).</li><li>It gives birth to testimonies (Psalm 126:2-3).</li></ul>",
			),
			array(
				"title"  => "Ungodly Patterns in Our Bloodline",
				"date"   => "2026-03-08 10:40:00",
				"scr"    => "Matthew 22:23-27",
				"img"    => "ungodly-patterns-in-our-bloodline.webp",
				"series" => "",
				"exc"    => "Ungodly patterns are repeated family cycles that oppose God's perfect will. Learn how they are established and how to break them.",
				"body"   => "<p><strong>Scripture:</strong> Matthew 22:23-27.</p><p>Ungodly patterns are repeated family cycles that oppose God's perfect will from prevailing. They are established through sin, spoken words, evil covenants and ungodly traditions.</p><h3>Biblical examples</h3><p>In the bloodline of Abraham we see repeated barrenness, repeated lies, and firstborns missing their inheritance. Anger ran through Simeon and Levi to Moses, and the house of Herod became a generation of shedding blood.</p><h3>How to break these cycles</h3><ul><li>Identify them and trace their repetition across generations.</li><li>Repent on behalf of your family and yourself.</li><li>Renounce them verbally and disconnect from them.</li><li>Break their cycles with the authority of Jesus Christ.</li><li>Establish godly patterns through positive declarations and a voluntary sacrifice (Psalm 50:5).</li></ul>",
			),
			array(
				"title"  => "Anointing Service 8th Feb 2026",
				"date"   => "2026-02-08 10:40:00",
				"scr"    => "Isaiah 10:27",
				"img"    => "anointing-service.webp",
				"series" => "",
				"exc"    => "The yoke shall be destroyed because of the anointing. What the anointing does in your life.",
				"body"   => "<p><strong>Scripture:</strong> Isaiah 10:27.</p><p>The anointing breaks evil patterns and destroys every yoke. As we saw in 1 Samuel 16:13, when the Spirit of the Lord comes upon a life, everything changes.</p><h3>What the anointing does in your life</h3><ul><li>It transforms you.</li><li>It equips you to break the patterns enslaving your generation.</li><li>It destroys the yoke and every spiritual pattern.</li><li>It protects your life.</li></ul>",
			),
		);
		foreach ( $seeds as $s ) {
			$pid = wp_insert_post( array(
				'post_type'    => 'dck_sermon',
				'post_title'   => $s['title'],
				'post_content' => $s['body'],
				'post_excerpt' => $s['exc'],
				'post_status'  => 'publish',
				'post_date'    => $s['date'],
			) );
			if ( $pid && ! is_wp_error( $pid ) ) {
				update_post_meta( $pid, '_dck_scripture', $s['scr'] );
				update_post_meta( $pid, '_dck_sermon_image', $base . $s['img'] );
				wp_set_object_terms( $pid, $sp, 'dck_sermon_speaker' );
				if ( '' !== $s['series'] ) {
					wp_set_object_terms( $pid, $s['series'], 'dck_sermon_series' );
				}
			}
		}
		update_option( 'dck_sermons_seeded', 1 );
	}
}
add_action( 'admin_init', 'dck_elite_seed_sermons' );

/**
 * Filter the Sermon archive by taxonomy query vars.
 */
if ( ! function_exists( 'dck_elite_filter_sermons' ) ) {
	function dck_elite_filter_sermons( $q ) {
		if ( is_admin() || ! $q->is_main_query() ) {
			return;
		}
		if ( $q->is_post_type_archive( 'dck_sermon' ) ) {
			$q->set( 'posts_per_page', 9 );
			if ( isset( $_GET['sort'] ) && 'oldest' === sanitize_key( wp_unslash( $_GET['sort'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$q->set( 'order', 'ASC' );
			}
			$tax_query = array();
			foreach ( array( 'dck_sermon_series', 'dck_sermon_speaker', 'dck_sermon_topic' ) as $tax ) {
				if ( ! empty( $_GET[ $tax ] ) ) {
					$tax_query[] = array(
						'taxonomy' => $tax,
						'field'    => 'slug',
						'terms'    => sanitize_title( wp_unslash( $_GET[ $tax ] ) ),
					);
				}
			}
			if ( $tax_query ) {
				$q->set( 'tax_query', $tax_query );
			}
		}
	}
}
add_action( 'pre_get_posts', 'dck_elite_filter_sermons' );


/* =====================================================================
 * PHASE 2.1 - Robust one-time site setup (pages, front page, menu)
 * ===================================================================== */

if ( ! function_exists( 'dck_elite_page_url' ) ) {
	function dck_elite_page_url( $slug ) {
		$p = get_page_by_path( $slug );
		return $p ? get_permalink( $p ) : home_url( '/' . $slug );
	}
}

if ( ! function_exists( 'dck_elite_about_content' ) ) {
	function dck_elite_about_content() {
		return "<p>Deliverance Church Kabete N Market (Cistern of Hope Center) was founded by Pastor Justus Kubai and Mrs. Clare Kubai, after years of faithful service at Deliverance Church Kangemi and Muthangari (2002-2017). In obedience to God's call, they planted a church in N Market, Uthiru.</p><p>Our journey began with a prayer meeting on 9 June 2017, our first Sunday service on 11 June 2017, and the official launch on 18 June 2017 by Rev. Ben Luvutse. In 2020, during the COVID-19 pandemic, we moved to our current site and, by God's grace and the faith of our members, we continue to build.</p><p><strong>Lead Pastors:</strong> Pastor Justus and Mrs. Clare Kubai. <strong>Assistant Pastors:</strong> Mr. and Mrs. Henry Onzere.</p>";
	}
}

if ( ! function_exists( 'dck_elite_visit_content' ) ) {
	function dck_elite_visit_content() {
		return "<p>We would love to welcome you this Sunday. Whatever your story, you belong here.</p><p><strong>When:</strong> Every Sunday. Intensive Prayers 8:00 AM, Bible Study 9:30 AM, Glorious Service 10:40 AM to 1:00 PM. Keshas on the 1st and 3rd week of the month.</p><p><strong>Where:</strong> Behind N Market, Uthiru, Nairobi.</p><p><strong>What to expect:</strong> A warm welcome, joyful worship, Bible-based teaching, and a place for your whole family. Children and youth are well cared for. Come as you are.</p><p><strong>Questions?</strong> Call us on " . ( function_exists( 'dck_phone_display' ) ? dck_phone_display() : '+254 110 304521' ) . ".</p>";
	}
}

if ( ! function_exists( 'dck_elite_contact_content' ) ) {
	function dck_elite_contact_content() {
		return "<p><strong>Deliverance Church Kabete N Market</strong> (Cistern of Hope Center)</p><p>Behind N Market, Uthiru, Nairobi, Kenya</p><p>Phone: " . ( function_exists( 'dck_phone_display' ) ? dck_phone_display() : '+254 110 304521' ) . "</p><p>Sunday: Intensive Prayers 8:00 AM, Bible Study 9:30 AM, Glorious Service 10:40 AM to 1:00 PM.</p>";
	}
}

if ( ! function_exists( 'dck_elite_ministries_page_content' ) ) {
	function dck_elite_ministries_page_content() {
		$out = "<p>There is a family for every season of life at DCK. Our departments drive our growth and impact:</p><ul>";
		foreach ( dck_elite_ministries() as $m ) {
			$out .= "<li><strong>" . esc_html( $m['name'] ) . ":</strong> " . esc_html( $m['desc'] ) . "</li>";
		}
		$out .= "</ul>";
		return $out;
	}
}

if ( ! function_exists( 'dck_elite_page_defs' ) ) {
	function dck_elite_page_defs() {
		return array(
			array( 'home', 'Home', '', '' ),
			array( 'about', 'About Us', 'template-about.php', dck_elite_about_content() ),
			array( 'our-pastors', 'Our Pastors', 'template-pastors.php', '<p>Meet the pastors who faithfully shepherd Deliverance Church Kabete N Market.</p>' ),
			array( 'services', 'Services', 'template-services.php', '<p>Our order of services and what to expect when you gather with us.</p>' ),
			array( 'ministries', 'Ministries', 'template-ministries.php', dck_elite_ministries_page_content() ),
			array( 'events', 'Events', 'template-events.php', '<p>Upcoming gatherings, conferences and Keshas. Our latest calendar will appear here.</p>' ),
			array( 'gallery', 'Photo Gallery', 'template-gallery.php', '<p>A window into the life of our church family.</p>' ),
			array( 'blog', 'Blog', '', '<p>Faith articles, devotions and church news from Deliverance Church Kabete.</p>' ),
			array( 'faq', 'FAQ', 'template-faq.php', '<p>Answers to common questions about our church, services and giving.</p>' ),
			array( 'give', 'Give Online', 'template-give.php', '' ),
			array( 'plan-your-visit', 'Plan Your Visit', '', dck_elite_visit_content() ),
			array( 'prayer-requests', 'Prayer Requests', 'template-prayer.php', '<p>Share your prayer request with our team. You are not alone; we would be honoured to pray with you.</p>' ),
			array( 'contact', 'Contact', 'template-contact.php', dck_elite_contact_content() ),
			array( 'terms', 'Terms & Privacy', 'template-terms.php', dck_elite_terms_content() ),
			array( 'live', 'Watch Live', 'template-live.php', '' ),
			array( 'bible', 'Bible', 'template-bible.php', '<p>Study the Word with our AI Bible study assistant. Ask about any verse or topic and receive a clear, Scripture-grounded explanation.</p>' ),
		);
	}
}

if ( ! function_exists( 'dck_elite_build_menu' ) ) {
	function dck_elite_build_menu( $ids ) {
		$menu_name = 'DCK Primary';
		// Remove every menu named 'DCK Primary' to clear duplicates, then start clean.
		$existing = wp_get_nav_menus();
		if ( is_array( $existing ) ) {
			foreach ( $existing as $m ) {
				if ( isset( $m->name ) && $m->name === $menu_name ) {
					wp_delete_nav_menu( (int) $m->term_id );
				}
			}
		}
		$menu_id = wp_create_nav_menu( $menu_name );
		if ( is_wp_error( $menu_id ) || ! $menu_id ) {
			return;
		}

		$u_about   = ! empty( $ids['about'] ) ? get_permalink( $ids['about'] ) : dck_elite_page_url( 'about' );
		$u_pastors = ! empty( $ids['our-pastors'] ) ? get_permalink( $ids['our-pastors'] ) : dck_elite_page_url( 'our-pastors' );
		$u_serv    = ! empty( $ids['services'] ) ? get_permalink( $ids['services'] ) : dck_elite_page_url( 'services' );
		$u_min     = ! empty( $ids['ministries'] ) ? get_permalink( $ids['ministries'] ) : dck_elite_page_url( 'ministries' );
		$u_events  = ! empty( $ids['events'] ) ? get_permalink( $ids['events'] ) : dck_elite_page_url( 'events' );
		$u_live    = ! empty( $ids['live'] ) ? get_permalink( $ids['live'] ) : dck_elite_page_url( 'live' );
		$u_gallery = ! empty( $ids['gallery'] ) ? get_permalink( $ids['gallery'] ) : dck_elite_page_url( 'gallery' );
		$u_blog    = ! empty( $ids['blog'] ) ? get_permalink( $ids['blog'] ) : dck_elite_page_url( 'blog' );
		$u_give    = ! empty( $ids['give'] ) ? get_permalink( $ids['give'] ) : dck_elite_page_url( 'give' );
		$u_bible   = ! empty( $ids['bible'] ) ? get_permalink( $ids['bible'] ) : dck_elite_page_url( 'bible' );
		$u_contact = ! empty( $ids['contact'] ) ? get_permalink( $ids['contact'] ) : dck_elite_page_url( 'contact' );
		$u_faq     = ! empty( $ids['faq'] ) ? get_permalink( $ids['faq'] ) : dck_elite_page_url( 'faq' );
		$u_sermons = get_post_type_archive_link( 'dck_sermon' );
		if ( ! $u_sermons ) { $u_sermons = home_url( '/' ); }

		$add = 'dck_elite_add_menu_item';

		dck_elite_add_menu_item( $menu_id, 'Home', home_url( '/' ), 0 );

		$who = dck_elite_add_menu_item( $menu_id, 'Who We Are', $u_about, 0 );
		dck_elite_add_menu_item( $menu_id, 'About Us', $u_about, $who );
		dck_elite_add_menu_item( $menu_id, 'Our Pastors', $u_pastors, $who );
		dck_elite_add_menu_item( $menu_id, 'FAQ', $u_faq, $who );

		dck_elite_add_menu_item( $menu_id, 'Services', $u_serv, 0 );
		dck_elite_add_menu_item( $menu_id, 'Ministries', $u_min, 0 );

		$media = dck_elite_add_menu_item( $menu_id, 'Media', $u_events, 0 );
		dck_elite_add_menu_item( $menu_id, 'Events', $u_events, $media );
		dck_elite_add_menu_item( $menu_id, 'Live Stream', $u_live, $media );
		dck_elite_add_menu_item( $menu_id, 'Photo Gallery', $u_gallery, $media );
		dck_elite_add_menu_item( $menu_id, 'Blog', $u_blog, $media );

		dck_elite_add_menu_item( $menu_id, 'Giving', $u_give, 0 );
		dck_elite_add_menu_item( $menu_id, 'Sermons', $u_sermons, 0 );
		dck_elite_add_menu_item( $menu_id, 'Bible', $u_bible, 0 );
		dck_elite_add_menu_item( $menu_id, 'Contact', $u_contact, 0 );

		$locations = get_theme_mod( 'nav_menu_locations' );
		if ( ! is_array( $locations ) ) { $locations = array(); }
		$locations['primary'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}

if ( ! function_exists( 'dck_elite_add_menu_item' ) ) {
	function dck_elite_add_menu_item( $menu_id, $title, $url, $parent ) {
		return wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $title,
				'menu-item-url'       => $url,
				'menu-item-status'    => 'publish',
				'menu-item-type'      => 'custom',
				'menu-item-parent-id' => (int) $parent,
			)
		);
	}
}

if ( ! function_exists( 'dck_elite_setup_site' ) ) {
	function dck_elite_setup_site() {
		if ( get_option( 'dck_elite_setup_ver' ) === DCK_ELITE_SETUP_VERSION ) {
			return;
		}

		if ( '' === get_option( 'permalink_structure' ) ) {
			update_option( 'permalink_structure', '/%postname%/' );
			global $wp_rewrite;
			if ( $wp_rewrite ) {
				$wp_rewrite->init();
			}
		}

		dck_elite_register_sermons();

		$ids = array();
		foreach ( dck_elite_page_defs() as $d ) {
			$existing = get_page_by_path( $d[0] );
			if ( $existing ) {
				$ids[ $d[0] ] = (int) $existing->ID;
				if ( $d[2] ) {
					update_post_meta( $existing->ID, '_wp_page_template', $d[2] );
				}
				continue;
			}
			$pid = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_name'    => $d[0],
					'post_title'   => $d[1],
					'post_status'  => 'publish',
					'post_content' => $d[3],
				)
			);
			if ( $pid && ! is_wp_error( $pid ) ) {
				$ids[ $d[0] ] = (int) $pid;
				if ( $d[2] ) {
					update_post_meta( $pid, '_wp_page_template', $d[2] );
				}
			}
		}

		if ( ! empty( $ids['home'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $ids['home'] );
		}
		if ( ! empty( $ids['blog'] ) ) {
			update_option( 'page_for_posts', $ids['blog'] );
		}

		dck_elite_build_menu( $ids );
		flush_rewrite_rules();
		update_option( 'dck_elite_setup_ver', DCK_ELITE_SETUP_VERSION );
	}
}
add_action( 'admin_init', 'dck_elite_setup_site' );
add_action( 'after_switch_theme', 'dck_elite_setup_site' );


/* Editable content layer (defaults + helpers). Loaded first. */
if ( file_exists( get_template_directory() . '/inc/content.php' ) ) {
	require_once get_template_directory() . '/inc/content.php';
}

if ( file_exists( get_template_directory() . '/inc/sermons.php' ) ) {
	require_once get_template_directory() . '/inc/sermons.php';
}

if ( file_exists( get_template_directory() . '/inc/pages.php' ) ) {
	require_once get_template_directory() . '/inc/pages.php';
}

if ( file_exists( get_template_directory() . '/inc/scripture.php' ) ) {
	require_once get_template_directory() . '/inc/scripture.php';
}

/* Shared AI layer (Church Assistant). */
if ( file_exists( get_template_directory() . '/inc/ai.php' ) ) {
	require_once get_template_directory() . '/inc/ai.php';
}
if ( file_exists( get_template_directory() . '/inc/ai-i18n.php' ) ) {
	require_once get_template_directory() . '/inc/ai-i18n.php';
}
if ( file_exists( get_template_directory() . '/inc/ai-voice.php' ) ) {
	require_once get_template_directory() . '/inc/ai-voice.php';
}

if ( file_exists( get_template_directory() . '/inc/ai-sermons.php' ) ) {
	require_once get_template_directory() . '/inc/ai-sermons.php';
}
if ( file_exists( get_template_directory() . '/inc/ai-bible.php' ) ) {
	require_once get_template_directory() . '/inc/ai-bible.php';
}
if ( file_exists( get_template_directory() . '/inc/ai-content.php' ) ) {
	require_once get_template_directory() . '/inc/ai-content.php';
}
if ( file_exists( get_template_directory() . '/inc/ai-transcribe.php' ) ) {
	require_once get_template_directory() . '/inc/ai-transcribe.php';
}
if ( file_exists( get_template_directory() . '/inc/ai-sermon-tools.php' ) ) {
	require_once get_template_directory() . '/inc/ai-sermon-tools.php';
}

if ( file_exists( get_template_directory() . '/inc/home.php' ) ) {
	require_once get_template_directory() . '/inc/home.php';
}

if ( file_exists( get_template_directory() . '/inc/seo.php' ) ) {
	require_once get_template_directory() . '/inc/seo.php';
}
if ( file_exists( get_template_directory() . '/inc/seo-schema.php' ) ) {
	require_once get_template_directory() . '/inc/seo-schema.php';
}
if ( file_exists( get_template_directory() . '/inc/seo-pages.php' ) ) {
	require_once get_template_directory() . '/inc/seo-pages.php';
}
if ( file_exists( get_template_directory() . '/inc/blog.php' ) ) {
	require_once get_template_directory() . '/inc/blog.php';
}
if ( file_exists( get_template_directory() . '/inc/perf.php' ) ) {
	require_once get_template_directory() . '/inc/perf.php';
}
if ( file_exists( get_template_directory() . '/inc/faq.php' ) ) {
	require_once get_template_directory() . '/inc/faq.php';
}
if ( file_exists( get_template_directory() . '/inc/gallery.php' ) ) {
	require_once get_template_directory() . '/inc/gallery.php';
}
if ( file_exists( get_template_directory() . '/inc/sitemap.php' ) ) {
	require_once get_template_directory() . '/inc/sitemap.php';
}

if ( file_exists( get_template_directory() . '/inc/events.php' ) ) {
	require_once get_template_directory() . '/inc/events.php';
}

if ( file_exists( get_template_directory() . '/inc/security.php' ) ) {
	require_once get_template_directory() . '/inc/security.php';
}

if ( file_exists( get_template_directory() . '/inc/media.php' ) ) {
	require_once get_template_directory() . '/inc/media.php';
}

if ( file_exists( get_template_directory() . '/inc/terms.php' ) ) {
	require_once get_template_directory() . '/inc/terms.php';
}

if ( file_exists( get_template_directory() . '/inc/contact.php' ) ) {
	require_once get_template_directory() . '/inc/contact.php';
}


/* One-time removal of the retired city landing pages (we are Nairobi-only). */
add_action( 'admin_init', 'dck_elite_remove_locality_pages', 25 );
if ( ! function_exists( 'dck_elite_remove_locality_pages' ) ) {
	function dck_elite_remove_locality_pages() {
		if ( get_option( 'dck_locality_removed_v1' ) ) {
			return;
		}
		$slugs = array( 'church-in-nairobi', 'church-in-nakuru', 'church-in-mombasa', 'church-in-eldoret' );
		foreach ( $slugs as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				wp_trash_post( $page->ID );
			}
		}
		update_option( 'dck_locality_removed_v1', 1 );
	}
}
