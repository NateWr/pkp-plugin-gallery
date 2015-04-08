<?php defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'pkppgAjaxHandler' ) ) {
/**
 * Class to handle requests to WordPress's ajaxurl handler
 *
 * @since 0.1
 */
class pkppgAjaxHandler {

	/**
 	 * Register hooks
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// Add ajaxurl to script data for frontend access
		add_filter( 'pkppg_admin_script_data', array( $this, 'add_ajaxurl' ) );

		// Release submission
		add_action( 'wp_ajax_pkppg-submit-release', array( $this, 'ajax_release_submission' ) );
		add_action( 'wp_ajax_nopriv_pkppg-submit-release', array( $this, 'nopriv' ) );

	}

	/**
	 * Add the ajaxurl value to the data array passed to scripts
	 * so that it's accessible on the frontend
	 *
	 * @since 0.1
	 */
	public function add_ajaxurl( $data ) {

		$data['ajaxurl'] = admin_url( 'admin-ajax.php' );

		return $data;
	}

	/**
	 * Authenticate a request
	 *
	 * This checks for the nonce and appropriate user permissions
	 * before allowing a request to move forward.
	 *
	 * @uses self::nopriv()
	 * @since 0.1
	 */
	public function authenticate( ) {

		// @todo update user permissions check so that users can
		// modify their own items where needed
		if ( !check_ajax_referer( 'pkppg', 'nonce', false ) || !current_user_can( 'manage_options' ) ) {
			$this->nopriv();
		}
	}

	/**
	 * Handle requests from logged out users
	 *
	 * @todo Call a sensible redirect in wp_login_url()
	 * @since 0.1
	 */
	public function nopriv() {

		wp_send_json_error(
			array(
				'error' => 'loggedout',
				'msg' => sprintf( __( 'You have been logged out. Please %slogin again%s.', 'pkp-plugin-gallery' ), '<a href="' . wp_login_url() . '">', '</a>' ),
			)
		);
	}

	/**
	 * Process a release submission request via ajax
	 *
	 * @since 0.1
	 */
	public function ajax_release_submission() {

		$this->authenticate();

		if ( empty( $_POST['release'] ) ) {
			wp_send_json_error(
				array(
					'error' => 'norelease',
					'msg' => __( 'No release data was received with this request.', 'pkp-plugin-gallery' ),
				)
			);
		}

		$release = new pkppgPluginRelease();
		$release->parse_params( $_POST['release'] );

		if ( $release->save() ) {
			wp_send_json_success(
				array(
					'release' => $release,
				)
			);
		} else {
			wp_send_json_error(
				array(
					'error'             => 'save_failed',
					'msg'               => __( 'Your attempt to save this release failed.', 'pkp-plugin-gallery' ),
					'validation_errors' => $release->validation_errors,
				)
			);
		}
	}

}
} // endif
