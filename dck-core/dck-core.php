<?php
/**
 * Plugin Name:       DCK Core
 * Plugin URI:        https://dckabeten.org/
 * Description:       Core features for Deliverance Church Kabete N Market. Adds one simple Church Settings screen where every piece of site content can be edited (contact details, service times, homepage, ministries, giving, live stream, SEO and legal pages), keeps Sermons safe even if the theme changes, and holds the private church Member Register with branded Excel export.
 * Version:           1.10.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            DCK Elite Team
 * License:           GPL v2 or later
 * Text Domain:       dck-core
 *
 * @package DCK_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DCK_CORE_VERSION', '1.10.0' );
define( 'DCK_CORE_FILE', __FILE__ );
define( 'DCK_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'DCK_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once DCK_CORE_DIR . 'includes/helpers.php';
require_once DCK_CORE_DIR . 'includes/post-types.php';
require_once DCK_CORE_DIR . 'includes/settings-schema.php';
require_once DCK_CORE_DIR . 'includes/settings-page.php';
require_once DCK_CORE_DIR . 'includes/members.php';

register_activation_hook(
	__FILE__,
	function () {
		dck_core_register_post_types();
		flush_rewrite_rules();
	}
);
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/* Settings link on the Plugins screen. */
add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=dck-church' ) ) . '">' . esc_html__( 'Church Settings', 'dck-core' ) . '</a>' );
		return $links;
	}
);
