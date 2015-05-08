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
	 * @param release array pkppgPluginRelease objects
	 * @since 0.1
	 */
	public $release_objects = array();

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
			'post_type' => pkppgInit()->cpts->plugin_release_post_type,
			'posts_per_page' => 1000, // high upper limit
			'post_parent' => $this->ID,
		);

		$query = new pkppgQuery( $args );

		$this->release_objects = $query->get_results();
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

		// @todo add author support
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

		// @todo ensure all application terms assigned to child releases are added

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
	 * Add/Edit form fields for plugins
	 *
	 * @since 0.1
	 */
	public function print_form_fields() {

		?>

			<fieldset class="plugin">
				<legend><?php _e( 'Plugin Details', 'pkp-plugin-gallery' ); ?></legend>
				<div class="name">
					<label for="pkp-plugin-name">
						<?php _e( 'Name', 'pkp-plugin-gallery' ); ?>
					</label>
					<input type="text" name="pkp-plugin-name" value="<?php echo esc_attr( $this->name ); ?>">
				</div>
				<div class="category">
					<label for="pkp-plugin-category">
						<?php _e( 'Category', 'pkp-plugin-gallery' ); ?>
					</label>
					<?php pkppg_print_taxonomy_select( 'pkp_category', $this->category ); ?>
				</div>
				<div class="summary">
					<label for="pkp-plugin-summary">
						<?php _e( 'Summary', 'pkp-plugin-gallery' ); ?>
					</label>
					<textarea name="pkp-plugin-summary"><?php echo $this->summary; ?></textarea>
				</div>
				<div class="description">
					<label for="pkp-plugin-description">
						<?php _e( 'Description', 'pkp-plugin-gallery' ); ?>
					</label>
					<textarea name="pkp-plugin-description"><?php echo $this->description; ?></textarea>
				</div>
				<div class="homepage">
					<label for="pkp-plugin-homepage">
						<?php _e( 'Project URL', 'pkp-plugin-gallery' ); ?>
					</label>
					<input type="url" name="pkp-plugin-homepage" value="<?php echo esc_attr( $this->homepage ); ?>">
				</div>
				<div class="installation">
					<label for="pkp-plugin-installation">
						<?php _e( 'Installation Instructions', 'pkp-plugin-gallery' ); ?>
					</label>
					<textarea name="pkp-plugin-installation"><?php echo $this->installation; ?></textarea>
				</div>
			</fieldset>

		<?php
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
	 * Generally this should only be called when the plugin is deleted. It is
	 * hooked in automatically to the `delete_post` action.
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
}
} // endif
