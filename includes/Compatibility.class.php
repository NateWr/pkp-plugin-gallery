<?php defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'pkppgCompatibility' ) ) {
/**
 * Class to handle special compatibility requirements
 *
 * This code will handle any backwards compatiblity issues as
 * well as code required to manage compatiblity with any other
 * plugins or themes being run on the site.
 *
 * @since 0.1
 */
class pkppgCompatibility {

	/**
	 * Register hooks
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// Hide sidebar on PKP Plugin editing screen
		add_action( 'do_meta_boxes', array( $this, 'remove_bowtie_sidebar_metabox' ) );
	}

	/**
	 * Remove the "Show Sidebar" meta box from the PKP
	 * Plugin editing screen. This meta box is added by
	 * the Bowtie theme.
	 *
	 * @since 0.1
	 */
	public function remove_bowtie_sidebar_metabox() {

		if ( !is_admin() || !function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( !is_a( $screen, 'WP_Screen' ) || $screen->post_type !== pkppgInit()->cpts->plugin_post_type ) {
			return;
		}

		remove_meta_box( 'myplugin_sectionid', $screen->post_type, 'side' );
	}
}
} // endif
