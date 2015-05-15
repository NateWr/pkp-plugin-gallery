<?php
/**
 * Plugin Name: PKP Plugin Gallery
 * Plugin URI: http://pkp.sfu.ca
 * Description: Create and manage a central plugin repository for PKP software.
 * Version: 0.1.0
 * Author: Public Knowledge Project
 * Author URI: http://pkp.sfu.ca
 * License:     GNU General Public License v2.0 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: pkp-plugin-gallery
 * Domain Path: /languages/
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License as published by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write
 * to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( !class_exists( 'pkppgInit' ) ) {
class pkppgInit {

	/**
	 * The single instance of this class
	 *
	 * @since 0.1
	 */
	private static $instance;

	/**
	 * Path to the plugin directory
	 *
	 * @since 0.1
	 */
	static $plugin_dir;

	/**
	 * URL to the plugin
	 *
	 * @since 0.1
	 */
	static $plugin_url;

	/**
	 * Create or retrieve the single instance of the class
	 *
	 * @since 0.1
	 */
	public static function instance() {

		if ( !isset( self::$instance ) ) {

			self::$instance = new pkppgInit();

			self::$plugin_dir = untrailingslashit( plugin_dir_path( __FILE__ ) );
			self::$plugin_url = untrailingslashit( plugin_dir_url( __FILE__ ) );

			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Initialize the plugin and register hooks
	 *
	 * @since 0.1
	 */
	public function init() {

		// Set up the plugin's core components and configuration
		$this->load_config();

		// Load textdomain
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Load admin assets
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Load frontend assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

	}

	/**
	 * Load the plugin textdomain for localistion
	 *
	 * @since 0.1
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'pkp-plugin-gallery', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Set up the plugin's core components and configuration
	 *
	 * @since 0.1
	 */
	public function load_config() {

		// Load files
		require_once( self::$plugin_dir . '/includes/CustomPostTypes.class.php' );
		require_once( self::$plugin_dir . '/includes/Settings.class.php' );
		require_once( self::$plugin_dir . '/includes/Query.class.php' );
		require_once( self::$plugin_dir . '/includes/PostModel.class.php' );
		require_once( self::$plugin_dir . '/includes/Plugin.class.php' );
		require_once( self::$plugin_dir . '/includes/PluginRelease.class.php' );
		require_once( self::$plugin_dir . '/includes/AjaxHandler.class.php' );
		require_once( self::$plugin_dir . '/includes/Compatibility.class.php' );
		require_once( self::$plugin_dir . '/includes/template-helpers.php' );

		// Load custom post types
		$this->cpts = new pkppgCustomPostTypes();

		// Load settings
		$this->settings = new pkppgSettings();

		// Load compatibility routines
		new pkppgCompatibility();

		// Load handler for calls to WP's ajaxurl
		new pkppgAjaxHandler();
	}

	/**
	 * Load the assets (JavaScript and CSS) to be used in the
	 * admin panel
	 *
	 * @since 0.1
	 */
	public function enqueue_admin_assets() {

		if ( !$this->is_owned_page() ) {
			return;
		}

		// Load minified assets unless WP_DEBUG is on
		$min = WP_DEBUG ? '' : '.min';

		wp_enqueue_style( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_enqueue_style( 'pkppg-admin', self::$plugin_url . '/assets/css/admin' . $min . '.css' );
		wp_enqueue_script( 'pkppg-admin', self::$plugin_url . '/assets/js/admin' . $min . '.js', array( 'jquery' ), '', true );
		$this->send_js_data( 'pkppg-admin' );

		// Print any required modals
		add_action( 'admin_footer', array( $this, 'print_modals' ) );
	}

	/**
	 * Check if we're on an admin page owned by this
	 * plugin
	 *
	 * @since 0.1
	 */
	public function is_owned_page() {

		if ( !function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();
		if ( empty( $screen ) || !is_a( $screen, 'WP_Screen' ) || $screen->post_type !== pkppgInit()->cpts->plugin_post_type ) {
			return false;
		}

		return true;
	}

	/**
	 * Load assets (JavaScript and CSS) to be used on the frontend
	 *
	 * @since 0.1
	 */
	public function enqueue_frontend_assets() {

		if ( !is_single() ) {
			return;
		}

		global $post;

		if ( get_post_type() != $this->cpts->plugin_post_type || !pkp_is_author( get_the_ID() ) ) {
			return;
		}

		// Load minified assets unless WP_DEBUG is on
		$min = WP_DEBUG ? '' : '.min';

		wp_enqueue_style( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'pkppg-gallery', self::$plugin_url . '/assets/css/frontend' . $min . '.css' );
		wp_enqueue_script( 'pkppg-gallery', self::$plugin_url . '/assets/js/gallery' . $min . '.js', array( 'jquery' ), '', true );
		$this->send_js_data( 'pkppg-gallery' );

		// Print the releases modal in the footer
		add_action( 'wp_footer', array( $this, 'print_modals' ) );
	}

	/**
	 * Make data available to JavaScript assets
	 *
	 * @since 0.1
	 */
	public function send_js_data( $script ) {
		wp_localize_script(
			$script,
			'pkppg_data',
			apply_filters(
				'pkppg_script_data',
				array(
					'nonce'        => wp_create_nonce( 'pkppg' ),
					'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				)
			)
		);
	}

	/**
	 * Print modal content that should be delivered to the page
	 *
	 * @since 0.1
	 */
	public function print_modals() {

		$release = new pkppgPluginRelease();

		$this->print_modal( 'pkp-release-modal', $release->get_form(), __( 'Release', 'pkp-plugin-gallery' ) );
	}

	/**
	 * Print a modal instance
	 *
	 * @since 0.1
	 */
	public function print_modal( $id, $content, $title = '' ) {

		?>

		<div id="<?php echo esc_attr( $id ); ?>" class="pkppg-modal">
			<div class="pkppg-modal-container">

				<?php if ( $title ) : ?>
				<h3><?php echo $title; ?></h3>
				<?php endif; ?>

				<?php echo $content; ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Retrieve the full path to a template file if it exists
	 *
	 * @since 0.1
	 */
	public function get_template_path( $file ) {

		$path = pkppgInit::$plugin_dir . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file;

		if ( !file_exists( $path ) ) {
			return;
		}

		return $path;
	}

}
} // endif;

/**
 * This function returns one pkppgInit instance everywhere
 * and can be used like a global, without needing to declare the global.
 *
 * Example: $pkppg = pkppgInit();
 */
if ( !function_exists( 'pkppgInit' ) ) {
function pkppgInit() {
	return pkppgInit::instance();
}
add_action( 'plugins_loaded', 'pkppgInit' );
} // endif;

/**
 * Flush the rewrite rules when the plugin is activated or deactivated
 *
 * This must be called before `plugins_loaded`, so it can't be added into
 * the normal plugin loading routines.
 *
 * @since 0.1
 */
if ( function_exists( 'flush_rewrite_rules' ) ) {
	register_activation_hook( __FILE__, 'flush_rewrite_rules' );
	register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
} // endif
