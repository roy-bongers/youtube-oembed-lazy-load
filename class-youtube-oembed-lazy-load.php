<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * YouTube_oEmbed_Lazy_Load
 */
class YouTube_oEmbed_Lazy_Load {

	protected static $instance;

	protected $version = '1.0.0';

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_filter( 'oembed_dataparse', array( $this, 'render_oembed_html' ), 20, 3 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Get class instance.
	 *
	 * @return YouTube_oEmbed_Lazy_Load
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new YouTube_oEmbed_Lazy_Load();
		}
		return self::$instance;
	}

	/**
	 * Enqueue CSS and JavaScript files.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'fontawesome', 'https://use.fontawesome.com/releases/v5.6.1/css/all.css', false, YOUTUBE_OEMBED_LAZY_LOAD_VERSION );
		wp_enqueue_style( 'yt-oembed-lazyload', plugins_url( 'assets/dist/css/youtube-oembed-lazy-load.css', __FILE__ ), false, YOUTUBE_OEMBED_LAZY_LOAD_VERSION );
		wp_enqueue_script( 'yt-oembed-lazyload', plugins_url( '/assets/dist/js/youtube-oembed-lazy-load.js', __FILE__ ), false, YOUTUBE_OEMBED_LAZY_LOAD_VERSION );
	}

	/**
	 * Clear YouTube oEmbed caches.
	 */
	public function clear_youtube_oembed_cache() {
		global $wpdb;
		// Get all post_meta with a YouTube oEmbed code.
		$query   = "SELECT meta_id, meta_key, meta_value FROM $wpdb->postmeta WHERE $wpdb->postmeta.meta_key LIKE '_oembed_%' AND $wpdb->postmeta.meta_value LIKE '%youtube%';";
		$results = $wpdb->get_results( $query, OBJECT );

		// Create a list of all meta_keys we have to remove.
		$where_clauses = array();
		foreach ( $results as $result ) {
			$oembed_hash     = str_replace( '_oembed_', '', $result->meta_key );
			$where_clauses[] = "'_oembed_" . $oembed_hash . "'";
			$where_clauses[] = "'_oembed_time_" . $oembed_hash . "'";
		}

		// Delete all oEmbed caches which contain YouTube data.
		if ( ! empty( $where_clauses ) ) {
			$query = "DELETE FROM $wpdb->postmeta WHERE $wpdb->postmeta.meta_key IN (" . implode( ',', $where_clauses ) . ')';
			if ( false === $wpdb->query( $query ) ) {
				// @todo logging?
			}
		}
	}

	/**
	 * Embed YouTube only with a still image until someone clicks on the image.
	 * Only then the YouTube iframe is loaded.
	 *
	 * @param string $return HTML which is the oEmbed data.
	 * @param object $data   Data received from the oEmbed API.
	 * @param string $url    Original URL that was embedded.
	 *
	 * @return string HTML oEmbed code.
	 */
	public function render_oembed_html( $return, $data, $url ) {
		if ( 'YouTube' !== $data->provider_name ) {
			return $return;
		}

		// we are going to parse HTML with the PHP DOM classes.
		$dom = new DOMDocument();

		// load the WordPress post.
		$dom->loadHTML( $return, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		// regular expression to find any Youtube URL's + the video ID.
		$youtube_regex = '@https?://(www\.)?youtu(\.be|be(-nocookie)?\.com)/(embed/)?([^/"?]+)@i';

		// loop all iframes for possible YouTube video's.
		foreach ( $dom->getElementsByTagName( 'iframe' ) as $iframe ) {

			// only alter iframes with an YouTube URL.
			if ( preg_match( $youtube_regex, $iframe->getAttribute( 'src' ), $matches ) ) {
				// YouTube movie ID.
				$video_id = $matches[5];

				// YouTube video src url.
				$youtube_src = $iframe->getAttribute( 'src' );

				// get thumbnail url and calculate aspect ratio.
				$thumbnail_url = $data->thumbnail_url;
				$aspect_ratio  = floor( $data->height * 100 / $data->width );
				if ( $aspect_ratio > 50 && $aspect_ratio < 60 ) {
					$aspect_ratio       = '56.25';
					$aspect_ratio_class = 'aspect-ratio-16x9';
				} else {
					$aspect_ratio       = '75';
					$aspect_ratio_class = 'aspect-ratio-4x3';
				}

				// create dom node for image still.
				$img_still = $dom->createElement( 'span' );
				$img_still->setAttribute( 'class', 'youtube-still ' . $aspect_ratio_class );
				$img_still->setAttribute( 'style', 'padding-top: ' . $aspect_ratio . "%; background-image: url('" . $thumbnail_url . "');" );
				$img_still->setAttribute( 'data-id', $video_id );

				// create empty div to load the YouTube player in.
				$div = $dom->createElement( 'div' );
				$div->setAttribute( 'id', 'youtube-' . $video_id );

				// create empty container div for the YouTube iframe.
				$container_div = $dom->createElement( 'div' );
				$container_div->setAttribute( 'class', 'yt-iframe-container ' . $aspect_ratio_class );
				$container_div->setAttribute( 'style', 'display:none;' );

				$container_div->appendChild( $div );

				// insert still and div node.
				$iframe->parentNode->insertBefore( $img_still, $iframe );
				$iframe->parentNode->insertBefore( $container_div, $iframe );

				// remove the iframe node.
				$iframe->parentNode->removeChild( $iframe );
			}
		}

		return $dom->saveHTML();
	}
}
