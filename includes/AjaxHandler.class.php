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
		add_action( 'wp_ajax_pkppg-insert-release', array( $this, 'ajax_insert_release' ) );
		add_action( 'wp_ajax_nopriv_pkppg-insert-release', array( $this, 'nopriv' ) );

		// Fetch release
		add_action( 'wp_ajax_pkppg-get-release', array( $this, 'ajax_get_release' ) );
		add_action( 'wp_ajax_nopriv_pkppg-get-release', array( $this, 'nopriv' ) );

		// Delete release
		add_action( 'wp_ajax_pkppg-delete-release', array( $this, 'ajax_delete_release' ) );
		add_action( 'wp_ajax_nopriv_pkppg-delete-release', array( $this, 'nopriv' ) );

		// Publish release
		add_action( 'wp_ajax_pkppg-publish-release', array( $this, 'ajax_publish_release' ) );
		add_action( 'wp_ajax_nopriv_pkppg-publish-release', array( $this, 'nopriv' ) );

		// Disable release
		add_action( 'wp_ajax_pkppg-disable-post', array( $this, 'ajax_disable_post' ) );
		add_action( 'wp_ajax_nopriv_pkppg-disable-post', array( $this, 'nopriv' ) );

		// Load an update diff
		add_action( 'wp_ajax_pkppg-get-update-diff', array( $this, 'ajax_get_update_diff' ) );
		add_action( 'wp_ajax_nopriv_pkppg-get-update-diff', array( $this, 'nopriv' ) );

		// Merge update
		add_action( 'wp_ajax_pkppg-merge-update', array( $this, 'ajax_merge_update' ) );
		add_action( 'wp_ajax_nopriv_pkppg-merge-update', array( $this, 'nopriv' ) );

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

		// @todo we need a user cap check here for submissions. It's loose now for
		// testing but eventually the only non-admin users to pass should be editing
		// their own objet
		if ( !check_ajax_referer( 'pkppg', 'nonce', false ) ) {
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
	public function ajax_insert_release() {

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
		if ( !empty( $_POST['release']['ID'] ) ) {
			$release->load_post( $_POST['release']['ID'] );
		}
		$release->parse_params( $_POST['release'] );

		if ( $release->save() ) {
			$release->load_updates();
			wp_send_json_success(
				array(
					'release' => $release,
					'overview' => $release->get_control_overview(),
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

	/**
	 * Get a release
	 *
	 * @since 0.1
	 */
	public function ajax_get_release() {

		$this->authenticate();

		if ( empty( $_GET['release'] ) ) {
			wp_send_json_error(
				array(
					'error' => 'norelease',
					'msg' => __( 'No release data was received with this request.', 'pkp-plugin-gallery' ),
				)
			);
		}

		$release = new pkppgPluginRelease();

		if ( $release->load_post( (int) $_GET['release'] ) ) {
			wp_send_json_success(
				array(
					'release' => $release,
				)
			);
		} else {
			wp_send_json_error(
				array(
					'error'             => 'get_failed',
					'msg'               => __( 'The requested release could not be found', 'pkp-plugin-gallery' ),
				)
			);
		}
	}

	/**
	 * Delete a release
	 *
	 * @since 0.1
	 */
	public function ajax_delete_release() {

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

		if ( !$release->load_post( (int) $_POST['release'] ) ) {
			wp_send_json_error(
				array(
					'error' => 'noreleasefound',
					'msg' => __( 'This release could not be found in order to be deleted.', 'pkp-plugin-gallery' ),
				)
			);
		}

		if ( $release->delete() ) {
			wp_send_json_success(
				array(
					'release' => $release,
				)
			);
		} else {
			wp_send_json_error(
				array(
					'error' => 'delete_failed',
					'msg' => __( 'There was an error while attempting to delete this release.', 'pkp-plugin-gallery' ),
				)
			);
		}
	}

	/**
	 * Publish a release
	 *
	 * @since 0.1
	 */
	public function ajax_publish_release() {

		$this->authenticate();

		if ( empty( $_POST['release'] ) ) {
			wp_send_json_error(
				array(
					'error' => 'norelease',
					'msg'   => __( 'No release data was received with this request.', 'pkp-plugin-gallery' ),
				)
			);
		}

		$release = new pkppgPluginRelease();

		if ( !$release->load_post( (int) $_POST['release'] ) ) {
			wp_send_json_error(
				array(
					'error' => 'noreleasefound',
					'msg'   => __( 'This release could not be found in order to be published.', 'pkp-plugin-gallery' ),
				)
			);
		}

		if ( $release->publish() ) {
			$release->load_updates();
			wp_send_json_success(
				array(
					'release'  => $release,
					'overview' => $release->get_control_overview(),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'error' => 'publish_failed',
					'msg'   => __( 'There was an error while attempting to publish this release.', 'pkp-plugin-gallery' ),
				)
			);
		}
	}

	/**
	 * Disable a plugin or release post
	 *
	 * @since 0.1
	 */
	public function ajax_disable_post() {

		$this->authenticate();

		if ( empty( $_POST['post'] ) ) {
			wp_send_json_error(
				array(
					'error' => 'nopost',
					'msg'   => __( 'No release or plugin data was received with this request.', 'pkp-plugin-gallery' ),
				)
			);
		}

		$post = get_post( (int) $_POST['post'] );

		$obj = $post->post_type == pkppgInit()->cpts->plugin_post_type ? new pkppgPlugin() : new pkppgPluginRelease();

		if ( !$obj->load_post( $post ) ) {
			wp_send_json_error(
				array(
					'error' => 'nopostfound',
					'msg'   => __( 'This plugin or release could not be found in order to be disabled.', 'pkp-plugin-gallery' ),
				)
			);
		}

		if ( $obj->disable() ) {
			if( $obj->post_type == pkppgInit()->cpts->plugin_release_post_type ) {
				$obj->load_updates();
				wp_send_json_success(
					array(
						'release'  => $obj,
						'overview' => $obj->get_control_overview(),
					)
				);
			} else {
				$url = add_query_arg(
					array(
						'post'   => (int) $obj->ID,
						'action' => 'edit',
					),
					admin_url( 'post.php' )
				);
				wp_send_json_success(
					array(
						'redirect' => esc_url_raw( $url ),
					)
				);
			}
		} else {
			wp_send_json_error(
				array(
					'error' => 'disable_failed',
					'msg'   => __( 'There was an error while attempting to disable this release.', 'pkp-plugin-gallery' ),
				)
			);
		}
	}

	/**
	 * Get a diff for an update and it's parent
	 *
	 * @since 0.1
	 */
	public function ajax_get_update_diff() {

		$this->authenticate();

		// Only admins!
		// @todo better cap check
		if ( !current_user_can( 'manage_options' ) ) {
			$this->nopriv();
		}

		if ( empty( $_GET['ID'] ) ) {
			wp_send_json_error(
				array(
					'error' => 'nopost',
					'msg'   => __( 'No post ID was received with this request.', 'pkp-plugin-gallery' ),
				)
			);
		}

		$post = get_post( (int) $_GET['ID'] );

		if ( !$post || is_wp_error( $post ) || !pkppgInit()->cpts->is_valid_type( $post->post_type ) ) {
			wp_send_json_error(
				array(
					'error' => 'nopostfound',
					'msg'   => __( 'This item could not be found.', 'pkp-plugin-gallery' ),
				)
			);
		}

		if ( empty( $post->post_parent ) ) {
			wp_send_json_error(
				array(
					'error' => 'noparentfound',
					'msg'   => __( 'This update is not attached to any existing plugin.', 'pkp-plugin-gallery' ),
				)
			);
		}

		$update = $post->post_type == pkppgInit()->cpts->plugin_post_type ? new pkppgPlugin() : new pkppgPluginRelease();

		if ( !$update->load_post( $post ) ) {
			wp_send_json_error(
				array(
					'error' => 'nopostfound',
					'var' => (int) $_GET['ID'],
					'post' => $post,
					'also' => 'this',
					'msg'   => __( 'This item could not be found.', 'pkp-plugin-gallery' ),
				)
			);
		}

		$class = get_class( $update );
		$parent = new $class();

		if ( !$parent->load_post( $post->post_parent ) ) {
			wp_send_json_error(
				array(
					'error' => 'nopostfound',
					'var' => $post->post_parent,
					'post' => $post,
					'also' => 'this',
					'msg'   => __( 'No plugin was found for this update.', 'pkp-plugin-gallery' ),
				)
			);
		}

		$diff = $parent->get_diff( $update );

		wp_send_json_success(
			array(
				'parent' => $parent,
				'update' => $update,
				'diff' => $parent->get_diff( $update ),
			)
		);
	}

	/**
	 * Publish the changes from an update to it's parent post
	 *
	 * @since 0.1
	 */
	public function ajax_merge_update() {

		$this->authenticate();

		// Only admins!
		// @todo better cap check
		if ( !current_user_can( 'manage_options' ) ) {
			$this->nopriv();
		}

		if ( empty( $_POST['ID'] ) ) {
			wp_send_json_error(
				array(
					'error' => 'nopost',
					'msg'   => __( 'No post ID was received with this request.', 'pkp-plugin-gallery' ),
				)
			);
		}

		$post = get_post( (int) $_POST['ID'] );

		if ( !$post || is_wp_error( $post ) || !pkppgInit()->cpts->is_valid_type( $post->post_type ) ) {
			wp_send_json_error(
				array(
					'error' => 'nopostfound',
					'msg'   => __( 'This item could not be found.', 'pkp-plugin-gallery' ),
				)
			);
		}

		$obj = $post->post_type == pkppgInit()->cpts->plugin_post_type ? new pkppgPlugin() : new pkppgPluginRelease();

		if ( !$obj->load_post( $post ) ) {
			wp_send_json_error(
				array(
					'error' => 'nopostfound',
					'var' => $_POST['ID'],
					'post' => $post,
					'also' => 'this',
					'msg'   => __( 'This item could not be found.', 'pkp-plugin-gallery' ),
				)
			);
		}

		if ( $obj->merge_update() ) {
			if( $obj->post_type == pkppgInit()->cpts->plugin_release_post_type ) {
				$obj->load_updates();
				wp_send_json_success(
					array(
						'release'  => $obj,
						'overview' => $obj->get_control_overview(),
					)
				);
			} else {
				$url = add_query_arg(
					array(
						'post'   => (int) $obj->ID,
						'action' => 'edit',
					),
					admin_url( 'post.php' )
				);
				wp_send_json_success(
					array(
						'redirect' => esc_url_raw( $url ),
					)
				);
			}
		} else {
			wp_send_json_error(
				array(
					'error' => 'mergeupdatefailed',
					'msg'   => __( 'There was an error while attempting to merge this update.', 'pkp-plugin-gallery' ),
				)
			);
		}
	}

}
} // endif
