<?php defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'pkppgPluginGallery' ) ) {
/**
 * Class to handle the front-end display and management of
 * the plugin gallery.
 *
 * This class will load the plugin gallery, show submit and
 * edit functions, and handle sorting/filtering the list.
 *
 * @since 0.1
 */
class pkppgPluginGallery {

	/**
	 * Register load hook
	 *
	 * @since 0.1
	 */
	public function __construct() {

		add_action( 'wp', array( $this, 'load_plugin_gallery' ) );
	}

	/**
	 * Load the plugin gallery on the appropriate page
	 *
	 * @since 0.1
	 */
	public function load_plugin_gallery() {

		if ( !is_page( pkppgInit()->settings->get_setting( 'page' ) ) ) {
			return;
		}

		// Enqueue frontend assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Replace the page content with the plugin gallery
		add_action( 'the_content', array( $this, 'replace_content' ), 100 );
	}

	/**
	 * Enqueue the frontend assets
	 *
	 * @since 0.1
	 */
	public function enqueue_assets() {

		// Load minified assets unless WP_DEBUG is on
		$min = WP_DEBUG ? '' : '.min';

		wp_enqueue_style( 'pkppg', pkppgInit::$plugin_url . '/assets/css/frontend' . $min . '.css' );
	}

	/**
	 * Replace the page content with the plugin gallery
	 *
	 * @since 0.1
	 */
	public function replace_content( $content ) {

		$content = '';

		$content = $this->get_plugin_list();

		return $content;
	}

	/**
	 * Generate a list of plugins
	 *
	 * @since 0.1
	 */
	public function get_plugin_list() {

		$query = new pkppgQuery();
		$query->sanitize_incoming_request();
		$plugins = $query->get_results();

		$output = '';
		foreach( $plugins as $plugin ) {
			$output .= '<p>' . $plugin->post_title. '</p>';
		}
		return $output;
	}

}
} // endif
