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
 	 * URL to view the gallery
	 *
	 * @since 0.1
	 */
	public $base_url;

	/**
 	 * URL for a plugin editing view of the gallery
	 *
	 * @since 0.1
	 */
	public $edit_url;

	/**
	 * Current view being displayed
	 *
	 * @since 0.1
	 */
	public $view;

	/**
	 * Current plugin id being handled
	 *
	 * @since 0.1
	 */
	public $plugin;

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

		$this->view = $this->get_current_view();

		// Replace the page content with the plugin gallery
		add_action( 'the_content', array( $this, 'replace_content' ), 100 );

		// Run gallery actions
		if ( $this->view == 'gallery' ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_gallery_assets' ) );

		// Print editing content
		} elseif ( $this->view == 'edit' ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_edit_assets' ) );
			add_action( 'wp_footer', array( $this, 'print_edit_modal' ) );
		}
	}

	/**
	 * Determine the current view being shown
	 *
	 * @since 0.1
	 */
	public function get_current_view() {

		$this->base_url = get_permalink( pkppgInit()->settings->get_setting( 'page' ) );
		$this->edit_url = add_query_arg( 'view', 'edit', $this->base_url );

		if ( empty( $_REQUEST['view'] ) || !is_user_logged_in() ) {
			return 'gallery';
		}

		if ( $_REQUEST['view'] == 'edit' ) {

			if ( !empty( $_REQUEST['id'] ) ) {
				$this->plugin = absint( $_REQUEST['id'] );
			}

			if ( !is_user_logged_in() ) {
				return 'login';
			} else {
				return 'edit';
			}
		}
	}

	/**
	 * Enqueue the frontend assets to show the gallery
	 *
	 * @since 0.1
	 */
	public function enqueue_gallery_assets() {

		// Load minified assets unless WP_DEBUG is on
		$min = WP_DEBUG ? '' : '.min';

		wp_enqueue_style( 'pkppg-frontend', pkppgInit::$plugin_url . '/assets/css/frontend' . $min . '.css' );
	}

	/**
	 * Enqueue the asseets to add and edit submissions
	 *
	 * @since 0.1
	 */
	public function enqueue_edit_assets() {

		// Load minified assets unless WP_DEBUG is on
		$min = WP_DEBUG ? '' : '.min';

		wp_enqueue_style( 'pkppg-frontend', pkppgInit::$plugin_url . '/assets/css/frontend' . $min . '.css' );
		wp_enqueue_script( 'pkppg-gallery', pkppgInit::$plugin_url . '/assets/js/gallery' . $min . '.js', array( 'jquery' ), '', true );
		wp_localize_script(
			'pkppg-gallery',
			'pkppg_data',
			apply_filters(
				'pkppg_admin_script_data',
				array(
					'nonce'        => wp_create_nonce( 'pkppg' )
				)
			)
		);
	}

	/**
	 * Print the edit modal
	 *
	 * @since 0.1
	 */
	public function print_edit_modal() {
		pkppgInit()->print_modal( 'pkp-release-modal', pkppg_get_release_form(), __( 'Release', 'pkp-plugin-gallery' ) );
	}

	/**
	 * Replace the page content with the plugin gallery
	 *
	 * @since 0.1
	 */
	public function replace_content( $content ) {

		// Destroy existing content
		$content = '';

		if ( $this->view == 'edit' ) {

			if ( !$this->process_submission() ) {
				$content = $this->get_plugin_form( $this->plugin );
			} else {
				$content = '<p>' . __( 'Your submission has been processed.', 'pkp-plugin-gallery' ) . '</p>';
			}

		} else {
			$content = $this->get_plugin_list();
		}

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


		ob_start();

		?>

		<a href="<?php echo esc_url( $this->edit_url ); ?>">
			<?php _e( 'Submit Plugin', 'pkp-plugin-gallery' ); ?>
		</a>
		<ul class="plugins">

			<?php foreach( $plugins as $plugin ) : ?>
			<li>
				<div class"plugin">
					<?php echo $plugin->name; ?>
				</div>
				<div class="actions">
					<?php // @todo better user cap ?>
					<?php if ( current_user_can( 'manage_options' ) || $plugin->maintainer == get_current_user_id() ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'id', $plugin->ID, $this->edit_url ) ); ?>">
						<?php _e( 'Edit', 'pkp-plugin-gallery' ); ?>
					</a>
					<?php endif; ?>
				</div>
			</li>
			<?php endforeach; ?>

		</ul>

		<?php

		return ob_get_clean();
	}

	/**
	 * Generate the form for a plugin
	 *
	 * @since 0.1
	 */
	public function get_plugin_form( $plugin_id = 0 ) {

		$plugin = new pkppgPlugin();
		if ( $plugin_id ) {
			$plugin->load_post( $plugin_id );
		}

		$heading = $this->view == 'edit' ? __( 'Edit Plugin', 'pkp-plugin-gallery' ) : __( 'Submit Plugin', 'pkp-plugin-gallery' );

		?>

		<div class="pkp-submit">

			<h1><?php echo $heading; ?></h1>

			<form method="POST">
				<?php wp_nonce_field( 'pkp-plugin-submission', 'pkp-plugin-nonce' ); ?>
				<?php $plugin->print_form_fields(); ?>

				<fieldset class="buttons">
					<button type="submit" class="save">
						<?php _e( 'Save', 'pkppg-plugin-gallery' ); ?>
					</button>
				</fieldset>
			</form>

		</div>

		<?php
	}

	/**
	 * Process a submission
	 *
	 * This may be a new submission or an edit of an existing
	 * plugin.
	 *
	 * @since 0.1
	 */
	public function process_submission() {

		if ( !isset( $_POST['pkp-plugin-nonce'] ) || !wp_verify_nonce( $_POST['pkp-plugin-nonce'], 'pkp-plugin-submission' ) ) {
			return;
		}

		$plugin = new pkppgPlugin();

		$params = array();
		foreach( $_POST as $key => $value ) {
			if ( strpos( $key, 'pkp-plugin' ) === 0 ) {
				$params[ substr( $key, 11 ) ] = $value;
			} elseif ( $key === 'tax_input' ) {
				$params['category'] = $value['pkp_category'];
			}
		}

		if ( !empty( $_GET['id'] ) ) {
			$params['ID'] = (int) $_GET['id'];
		}

		$plugin->parse_params( $params );

		if ( $plugin->save() ) {
			return true;
		}

		$this->plugin = $plugin;
		return false;
	}

}
} // endif
