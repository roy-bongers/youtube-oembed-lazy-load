<?php
/**
 * Plugin Name: YouTube oEmbed Lazy Load
 * Plugin URI: https://roybongers.nl/
 * Description: Only display a still (image) instead of loading the complete YouTube player.
 * Version: 0.1
 * Author: Roy Bongers
 * Author URI: https://roybongers.nl/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

define( 'YOUTUBE_OEMBED_LAZY_LOAD_VERSION', '1.0.0' );

// Load YouTube oEmbed LazyLoad class.
require_once __DIR__ . '/class-youtube-oembed-lazy-load.php';

// Initialize class.
YouTube_oEmbed_Lazy_Load::get_instance();

/**
 * Register activation hook.
 */
function youtube_oembed_lazy_load_activation() {
	YouTube_oEmbed_Lazy_Load::get_instance()->clear_youtube_oembed_cache();
}
register_activation_hook( __FILE__, 'youtube_oembed_lazy_load_activation' );

/**
 * Register deactivation hook.
 */
function youtube_oembed_lazy_load_deactivation() {
	YouTube_oEmbed_Lazy_Load::get_instance()->clear_youtube_oembed_cache();
}
register_deactivation_hook( __FILE__, 'youtube_oembed_lazy_load_deactivation' );
