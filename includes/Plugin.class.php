<?php defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'pkppgPlugin' ) ) {
/**
 * Class to handle a plugin
 *
 * This class will load, format, validate and save data for
 * plugin posts.
 *
 * @since 0.1
 */
class pkppgPlugin extends pkppgPostModel {

	/**
	 * Name
	 *
	 * @since 0.1
	 */
	public $name;

	/**
	 * Short text id
	 *
	 * @since 0.1
	 */
	public $product;

	/**
	 * Summary
	 *
	 * @since 0.1
	 */
	public $summary;

	/**
	 * Description
	 *
	 * @since 0.1
	 */
	public $description;

	/**
	 * URL to webpage with more information about the plugin
	 *
	 * @since 0.1
	 */
	public $homepage;

	/**
	 * Short description of installation requirements or
	 * instructions
	 *
	 * @since 0.1
	 */
	public $installation;

	/**
	 * User ID responsible for this plugin
	 *
	 * @since 0.1
	 */
	public $maintainer;

	/**
	 * Category of the plugin
	 *
	 * @param category id Assigned term id in pkp_category taxonomy
	 * @since 0.1
	 */
	public $category;

	/**
	 * Applications this plugin is compatible with
	 *
	 * Application terms are inherited from child
	 * `pkp_plugin_release` post types. Any terms
	 * assigned to a child `pkp_plugin_release`
	 * will be assigned to a plugin. Terms will
	 * never be manually assigned to a plugin.
	 *
	 * @param applications array Assigned terms in pkp_application taxonomy
	 * @since 0.1
	 */
	public $applications = array();

	/**
	 * IDs of releases attached to this plugin
	 *
	 * @param releases array `pkp_plugin_release` post ids
	 * @since 0.1
	 */
	public $releases = array();

	/**
	 * Full release objects attached to this plugin
	 *
	 * @param release_objects array pkppgPluginRelease objects
	 * @since 0.1
	 */
	public $release_objects = array();

	/**
	 * Plugin objects for updates to this plugin
	 *
	 * @param updates array pkppgPlugin objects
	 * @since 0.1
	 */
	public $updates = array();

	/**
	 * Originally posted date
	 *
	 * @since 0.1
	 */
	public $post_date;

	/**
	 * Initialize
	 *
	 * @since 0.1
	 */
	public function __construct() {
		$this->post_type = pkppgInit()->cpts->plugin_post_type;
	}

	/**
	 * Load plugin object from a WP_Post object, retrieve metadata
	 * and load taxonomies
	 *
	 * @since 0.1
	 */
	public function load_wp_post( $post ) {

		$this->ID = $post->ID;
		$this->name = $post->post_title;
		$this->product = $post->post_name;
		$this->summary = $post->post_excerpt;
		$this->description = $post->post_content;
		$this->homepage = get_post_meta( $post->ID, '_homepage', true );
		$this->installation = get_post_meta( $post->ID, '_installation', true );
		$this->maintainer = $post->post_author;
		$this->category = $this->get_category();
		$this->applications = $this->get_applications();
		$this->post_status = $post->post_status;
		$this->post_parent = $post->post_parent;
		$this->post_date = $post->post_status;
		$this->post_modified = $post->post_modified;
	}

	/**
	 * Load any attached releases
	 *
	 * @since 0.1
	 */
	public function load_release_objects() {

		if ( empty( $this->ID ) ) {
			return;
		}

		$args = array(
			'post_type'      => pkppgInit()->cpts->plugin_release_post_type,
			'posts_per_page' => 1000, // high upper limit
			'post_parent'    => $this->ID,
			'post_status'    => array( 'publish' ),
			'orderby'        => 'title',
			'order'	         => 'DESC',
		);

		// Maintainers should always see submissions and updates
		if ( get_current_user_id() == $this->maintainer ) {
			array_push( $args['post_status'], 'submission' );
			$args['with_updates'] = true;
		}

		// Show disabled releases and updates in admin to admins
		if ( is_admin() && current_user_can( 'manage_options' ) ) {
			array_push( $args['post_status'], 'disable', 'submission' );
		}

		$query = new pkppgQuery( $args );

		$this->release_objects = $query->get_results();

		return $this->release_objects;
	}

	/**
	 * Load any attached updates
	 *
	 * @since 0.1
	 */
	public function load_updates() {

		// Get updates
		$args = array(
			'posts_per_page' => 1000,
			'post_status' => 'update',
			'post_parent' => $this->ID,
			'orderby' => 'modified',
		);

		$query = new pkppgQuery( $args );

		$this->updates = $query->get_results();

		return $this->updates;
	}

	/**
	 * Parse and sanitize incoming data
	 *
	 * @since 0.1
	 */
	public function parse_params( $params ) {

		if ( !empty( $params['ID'] ) ) {
			$this->ID = (int) $params['ID'];
		}

		if ( isset( $params['name'] ) ) {
			$this->name = sanitize_text_field( $params['name'] );
		}

		if ( isset( $params['product'] ) ) {
			$this->product = sanitize_text_field( $params['product'] );
		}

		if ( isset( $params['summary'] ) ) {
			$this->summary = sanitize_text_field( $params['summary'] );
		}

		if ( isset( $params['description'] ) ) {
			$this->description = wp_kses_post( $params['description'] );
		}

		if ( isset( $params['homepage'] ) ) {
			$this->homepage = filter_var( $params['homepage'], FILTER_VALIDATE_URL );
		}

		if ( isset( $params['installation'] ) ) {
			$this->installation = wp_kses_post( $params['installation'] );
		}

		if ( !empty( $params['maintainer'] ) ) {
			$this->maintainer = absint( $params['maintainer'] );
		}

		if ( isset( $params['category'] ) ) {
			$this->category = sanitize_text_field( $params['category'] );
		}

		if ( isset( $params['applications'] ) ) {
			$this->applications = array_map( 'sanitize_text_field', $params['applications'] );
		}

		if ( !empty( $params['post_status'] ) && pkppgInit()->cpts->is_valid_status( $params['post_status'] ) ) {
			$this->post_status = $params['post_status'];
		}

		if ( !empty( $params['releases'] ) ) {
			$this->releases = array_map( 'absint', $params['releases'] );
		}
	}

	/**
	 * Validate data in this object
	 *
	 * This should be called before adding a release to the database. It
	 * will only check for required values and set sane defaults where
	 * they are missing.
	 *
	 * @since 0.1
	 */
	public function validate() {

		// Post Status
		if ( empty( $this->post_status ) ) {
			$this->post_status = empty( $this->ID ) ? 'submission' : 'update';

		} elseif ( !pkppgInit()->cpts->is_valid_status( $this->post_status ) ) {
			$this->add_error(
				'post_status',
				$this->post_status,
				__( 'Please select a valid post status.', 'pkp-plugin-gallery' )
			);

		// @todo use a better capabilities check
		} elseif( $this->post_status == 'publish' && !current_user_can( 'manage_options' ) ) {
			$this->add_error(
				'post_status',
				$this->post_status,
				__( 'You do not have permission to publish plugins.', 'pkp-plugin-gallery' )
			);
		}

		// Name
		if ( empty( $this->name ) ) {
			$this->add_error(
				'name',
				$this->name,
				__( 'Please enter a name for this plugin.', 'pkp-plugin-gallery' )
			);
		}

		// Category
		if ( empty( $this->category ) ) {
			$this->add_error(
				'category',
				$this->category,
				__( 'Please enter a category for this plugin' )
			);
		}

		return $this->is_valid();
	}

	/**
	 * Insert post data for a new or updated release entry
	 *
	 * You should usually self::save() instead of calling
	 * this method directly. self::save() will check data
	 * validation and call an expected action hook.
	 *
	 * @since 0.1
	 */
	public function insert_post_data() {

		$args = array(
			'post_type'    => pkppgInit()->cpts->plugin_post_type,
			'post_title'   => $this->name,
			'post_excerpt' => $this->summary,
			'post_content' => $this->description,
			'post_author'  => $this->maintainer,
			'post_status'  => $this->post_status,
		);

		if ( !empty( $this->ID ) ) {
			if ( $this->post_status == 'update' && $this->is_update_new() ) {
				$args['post_parent'] = $this->ID;
			} else {
				$args['ID'] = $this->ID;
			}
		}

		$id = wp_insert_post( $args );

		if ( is_wp_error( $id ) || $id === false ) {
			$this->insert_post_error = $id;
			return false;
		} else {
			$this->ID = $id;
		}

		if ( isset( $this->category ) ) {
			wp_set_object_terms( $this->ID, $this->category, 'pkp_category' );
		}

		if ( !empty( $this->homepage ) ) {
			update_post_meta( $this->ID, '_homepage', $this->homepage );
		}

		if ( !empty( $this->installation ) ) {
			update_post_meta( $this->ID, '_installation', $this->installation );
		}

		// Ensure any atttached releases have the proper post_parent.
		// This catches cases where a release might be added before
		// its parent is assigned an id
		if ( !empty( $this->releases ) ) {
			foreach ( $this->releases as $release ) {
				$args = array( 'ID' => $release, 'post_parent' => $this->ID );
				wp_update_post( $args );
			}
		}

		// Assign release `pkp_application` terms to plugin
		$this->adopt_child_terms();
	}

	/**
	 * Get any `pkp_application` taxonomy terms assigned
	 * to attached releases and assign them to this plugin.
	 *
	 * This replaces any taxonomy terms previously associated with the plugin
	 *
	 * @todo maybe adopt `pkp_certification` terms too
	 * @since 0.1
	 */
	public function adopt_child_terms() {

		$this->load_release_objects();

		$applications = array();
		if ( !empty( $this->release_objects ) ) {
			foreach( $this->release_objects as $release ) {
				if ( !empty( $release->applications ) ) {
					$applications = array_merge( $applications, $release->applications );
				}
			}
		}

		$this->applications = $applications;

		return wp_set_object_terms( $this->ID, $this->applications, 'pkp_application' );
	}

	/**
	 * Deleted any attached releases
	 *
	 * Generally this should only be called when the plugin is deleted.
	 *
	 * @since 0.1
	 */
	public function delete_attached_releases() {

		// Get all child releases
		$args = array(
			'post_type' => pkppgInit()->cpts->plugin_release_post_type,
			'posts_per_page' => 1000, // large upper limit
			'post_status' => array_merge( array( 'trash', 'draft' ), pkppgInit()->cpts->valid_post_statuses ),
			'post_parent' => $this->ID,
			'fields' => 'ids',
		);
		$query = new WP_Query( $args );
		$releases = $query->posts;

		if ( empty( $releases ) ) {
			return;
		}

		// Get all releases that were attached to those releases
		unset( $args['post_parent'] );
		$args['post_parent__in'] = $releases;
		$query = new WP_Query( $args );
		$releases = array_merge( $releases, $query->posts );

		// Delete them
		foreach( $releases as $release ) {
			wp_delete_post( $release );
		}
	}

	/**
	 * Deleted any attached updates
	 *
	 * Generally this should only be called when the plugin is deleted.
	 *
	 * @since 0.1
	 */
	public function delete_attached_updates() {

		// Get all child plugins
		$args = array(
			'post_type' => pkppgInit()->cpts->plugin_post_type,
			'posts_per_page' => 1000, // large upper limit
			'post_status' => array_merge( array( 'trash', 'draft' ), pkppgInit()->cpts->valid_post_statuses ),
			'post_parent' => $this->ID,
			'fields' => 'ids',
		);
		$query = new WP_Query( $args );
		$plugins = $query->posts;

		if ( empty( $plugins ) ) {
			return;
		}

		// Delete them
		foreach( $plugins as $plugin ) {
			wp_delete_post( $plugin );
		}
	}

	/**
	 * Process a submission or edit form from the front-end
	 *
	 * This may be a new submission or an edit of an existing plugin.
	 *
	 * @since 0.1
	 */
	public function process_form() {

		// No form submitted
		if ( !isset( $_POST['pkp-plugin-nonce']) ) {
			return false;
		}

		// Logged out or trying to break the rules
		if ( !wp_verify_nonce( $_POST['pkp-plugin-nonce'], 'pkp-plugin-submission' ) ) {
			$this->add_error(
				'auth',
				'nonce_failed_verification',
				// @todo add login link
				__( 'You were logged out. Please login and try again.', 'pkp-plugin-gallery' )
			);
			return false;
		}

		// Only authors or admins can submit new plugin
		if ( !current_user_can( 'edit_posts' ) ) {
			$this->add_error(
				'auth',
				'not_allowed_to_submit',
				__( 'You do not have permission to add or update plugins.', 'pkp-plugin-gallery' )
			);
			return false;
		}

		$this->retrieve_post_data();

		if ( !empty( $this->ID ) ) {

			// Only authors can edit their own plugins from the frontend
			if ( !pkp_is_author( $this->ID ) ) {
				$this->add_error(
					'auth',
					'is_not_author',
					// @todo add a contact us link
					__( 'Only plugin maintainers are allowed to submit updates to a plugin. If you think this information is out of date, please contact us.', 'pkp-plugin-gallery' )
				);
				return false;
			}
		}

		if ( $this->save() ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve data from front-end submission/edit form and populate the
	 * object with that data
	 *
	 * @since 0.1
	 */
	public function retrieve_post_data() {

		$params = array();
		foreach( $_POST as $key => $value ) {
			if ( strpos( $key, 'pkp-plugin' ) === 0 ) {
				$params[ substr( $key, 11 ) ] = $value;
			} elseif ( $key === 'tax_input' ) {
				$params['category'] = $value['pkp_category'];
			}
		}

		$this->parse_params( $params );
	}

	/**
	 * Get HTML for a view of this plugin. Intended for user-facing views.
	 *
	 * If release_objects should be displayed, you should have already called
	 * $this->load_release_objects() before loading this view.
	 *
	 * @since 0.1
	 */
	public function get_view() {

		$plugin = $this;
		$template = pkppgInit()->get_template_path( 'plugin-view.php' );
		if ( !empty( $template ) ) {
			ob_start();
			include( $template );
			return ob_get_clean();
		}

		return '';
	}

	/**
	 * Print a view of this plugin. Intended for user-facing views.
	 *
	 * @since 0.1
	 */
	public function print_view() {
		echo $this->get_view();
	}

	/**
	 * Get HTML for a summary view of this plugin. Intended for user-facing views.
	 *
	 * @since 0.1
	 */
	public function get_summary_view() {

		$plugin = $this;
		$template = pkppgInit()->get_template_path( 'plugin-summary.php' );
		if ( !empty( $template ) ) {
			ob_start();
			include( $template );
			return ob_get_clean();
		}

		return '';
	}

	/**
	 * Print a summary view of this plugin. Intended for user-facing views.
	 *
	 * @since 0.1
	 */
	public function print_summary_view() {
		echo $this->get_summary_view();
	}

	/**
	 * Get HTML for an editing form for this plugin
	 *
	 * @since 0.1
	 */
	public function get_form() {

		$plugin = $this;
		$template = pkppgInit()->get_template_path( 'plugin-form.php' );
		if ( !empty( $template ) ) {
			ob_start();
			include( $template );
			return ob_get_clean();
		}

		return '';
	}

	/**
	 * Print an editing form for this plugin.
	 *
	 * @since 0.1
	 */
	public function print_form() {
		echo $this->get_form();
	}

	/**
	 * Generate a diff of changes against an updated object
	 *
	 * This will generate a series of diff tables which indicate changes between
	 * `$this` and an updated object which is passed to this method.
	 *
	 * @since 0.1
	 */
	public function get_diff( $update ) {

		$strings = array(
			'name',
			'product',
			'summary',
			'description',
			'homepage',
			'installation',
		);

		ob_start();

		foreach( $strings as $string ) :
			$diff = wp_text_diff( $this->{$string}, $update->{$string} );

			if ( empty( $diff ) ) {
				continue;
			}
		?>

		<div class="param">
			<h4><?php echo ucfirst( $string ); ?></h4>
			<?php echo $diff; ?>
		</div>

		<?php
		endforeach;

		$current_category = !empty( $this->category ) ? get_term( $this->category, 'pkp_category' ) : '';
		$update_category = !empty( $update->category ) ? get_term( $update->category, 'pkp_category' ) : '';
		$diff = wp_text_diff( $current_category->name, $update_category->name );

		if ( !empty( $diff ) ) :
		?>

		<div class="param">
			<h4><?php esc_html_e( 'Category' ); ?></h4>
			<?php echo $diff; ?>
		</div>

		<?php
		endif;

		return ob_get_clean();
	}
}
} // endif
