<?php defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'pkppgPluginRelease' ) ) {
/**
 * Class to handle a release version of a plugin
 *
 * This class will load, format, validate and save data for
 * plugin release posts.
 *
 * @since 0.1
 */
class pkppgPluginRelease extends pkppgPostModel {

	/**
	 * Plugin that this release is attached to
	 *
	 * @since 0.1
	 */
	public $release;

	/**
	 * Version
	 *
	 * @since 0.1
	 */
	public $version;

	/**
	 * Release date
	 *
	 * @since 0.1
	 */
	public $release_date;

	/**
	 * Description of this release
	 *
	 * @since 0.1
	 */
	public $description;

	/**
	 * URL to download release package
	 *
	 * @since 0.1
	 */
	public $package;

	/**
	 * MD5 has of a vetted download package
	 *
	 * @since 0.1
	 */
	public $md5;

	/**
	 * Plugin this release is attached to
	 *
	 * Corresponds to `post_parent` in the database
	 *
	 * @since 0.1
	 */
	public $plugin;

	/**
	 * Author of the plugin
	 *
	 * @todo update docs when we decide what this value should contain.
	 *	just a user id? or a user object or a has of the user details?
	 *	need to be able to validate newly submitted details, and may
	 *	not have a full user record.
	 * @since 0.1
	 */
	public $author;

	/**
	 * Applications and versions that this release is
	 * expected to be compatible with.
	 *
	 * @param applications array Assigned terms in pkp_application taxonomy
	 * @since 0.1
	 */
	public $applications = array();

	/**
	 * Certification level assigned to this release
	 *
	 * @param certification id Assigned term id in pkp_certification taxonomy
	 * @since 0.1
	 */
	public $certification;

	/**
	 * Initialize
	 *
	 * @since 0.1
	 */
	public function __construct() {
		$this->post_type = pkppgInit()->cpts->plugin_release_post_type;
	}

	/**
	 * Load release object from a WP_Post object, retrieve metadata
	 * and load taxonomies
	 *
	 * @since 0.1
	 */
	public function load_wp_post( $post ) {

		$this->plugin = $post->post_parent;
		$this->ID = $post->ID;
		$this->version = $post->post_title;
		$this->release_date = $this->format_date( $post->post_date );
		$this->description = $post->post_content;
		$this->package = get_post_meta( $post->ID, '_package', true );
		$this->md5 = get_post_meta( $post->ID, '_md5', true );
		$this->author = $post->post_author; // @todo figure out how to handle user data
		$this->applications = $this->get_applications();
		$this->certification = $this->get_certification();
		$this->post_status = $post->post_status;
	}

	/**
	 * Parse and sanitize incoming data
	 *
	 * @since 0.1
	 */
	public function parse_params( $params ) {

		if ( !empty( $params['plugin'] ) ) {
			$this->plugin = (int) $params['plugin'];
		}

		if ( !empty( $params['ID'] ) ) {
			$this->ID = (int) $params['ID'];
		}

		if ( isset( $params['version'] ) ) {
			$this->version = sanitize_text_field( $params['version'] );
		}

		if ( isset( $params['date'] ) ) {
			$date = DateTime::createFromFormat( 'Y-m-d', $params['date'] );
			if ( $date ) {
				$this->release_date = $date->format( 'Y-m-d' );
			}
		}

		if ( isset( $params['description'] ) ) {
			$this->description = wp_kses_post( $params['description'] );
		}

		if ( isset( $params['package'] ) ) {
			$this->package = filter_var( $params['package'], FILTER_VALIDATE_URL );
		}

		if ( isset( $params['md5'] ) ) {
			$md5 = preg_match( '/^[a-f0-9]{32}$/', $params['md5'] );
			if( $md5 ) {
				$this->version = sanitize_text_field( $params['version'] );
			}
		}

		if ( isset( $params['certification'] ) ) {
			$this->certification = absint( $params['certification'] );
		}

		if ( isset( $params['applications'] ) ) {
			$this->applications = array_map( 'absint', $params['applications'] );
			$this->add_parent_applications();
		}

		if ( !empty( $params['author'] ) ) {
			$this->author = absint( $params['author'] );
		}

		if ( !empty( $params['post_status'] ) && pkppgInit()->cpts->is_valid_status( $params['post_status'] ) ) {
			$this->post_status = $params['post_status'];
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

		// Plugin
		if ( empty( $this->plugin ) ) {
			$this->add_error(
				'plugin',
				$this->plugin,
				__( 'This release is not assigned to any plugin.', 'pkp-plugin-gallery' )
			);
		} else {
			$post = get_post( $this->plugin );
			if ( !is_a( $post, 'WP_POST' ) || $post->post_type !== pkppgInit()->cpts->plugin_post_type ) {
				$this->add_error(
					'plugin',
					$this->plugin,
					__( 'The plugin for this release could not be found.', 'pkp-plugin-gallery' )
				);
			}
		}

		// Post Status
		if ( empty( $this->post_status ) ) {
			$this->post_status = 'submission';
		} elseif ( !pkppgInit()->cpts->is_valid_status( $this->post_status ) ) {
			$this->add_error(
				'post_status',
				$this->post_status,
				__( 'Please select a valid post status.', 'pkp-plugin-gallery' )
			);
		}

		// Version
		if ( empty( $this->version ) ) {
			$this->add_error(
				'version',
				$this->version,
				__( 'Please enter a version number for this release.', 'pkp-plugin-gallery' )
			);
		}

		// Package
		if ( empty( $this->package ) ) {
			$this->add_error(
				'package',
				$this->package,
				__( 'Please enter the URL to a download package for this release.', 'pkp-plugin-gallery' )
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
			'post_type'    => pkppgInit()->cpts->plugin_release_post_type,
			'post_title'   => $this->version,
			'post_content' => $this->description,
			'post_date'    => $this->release_date,
			'post_status'  => $this->post_status,
			'post_parent'  => $this->plugin,
		);

		if ( !empty( $this->ID ) ) {
			$args['ID'] = $this->ID;
		}

		$id = wp_insert_post( $args );

		if ( is_wp_error( $id ) || $id === false ) {
			$this->insert_post_error = $id;
			return false;
		} else {
			$this->ID = $id;
		}

		if ( isset( $this->applications ) ) {
			wp_set_object_terms( $this->ID, $this->applications, 'pkp_application' );
		}

		if ( isset( $this->certification ) ) {
			wp_set_object_terms( $this->ID, $this->certification, 'pkp_certification' );
		}

		if ( !empty( $this->package ) ) {
			update_post_meta( $this->ID, '_package', $this->package );
		}

		if ( !empty( $this->md5 ) ) {
			update_post_meta( $this->ID, '_md5', $this->md5 );
		}
	}

	/**
	 * Get HTML for an overview of this release for the plugin submission
	 * and editing controls
	 *
	 * @since 0.1
	 */
	public function get_control_overview() {

		ob_start();

		?>

		<div class="release" data-id="<?php echo (int) $this->ID; ?>">
			<div class="title">
				<span class="version">
					<?php echo $this->version; ?>
				</span>
				&mdash;
				<span class="date">
					<?php echo $this->release_date; ?>
				</span>
			</div>
			<div class="details">
				<?php echo $this->description; ?>
			</div>
			<div class="actions">
				<span class="pkp-spinner"></span>
				<a href="#" class="edit">
					<?php _e( 'Edit', 'pkp-plugin-gallery' ); ?>
				</a>
				<a href="#" class="delete">
					<?php _e( 'Delete', 'pkp-plugin-gallery' ); ?>
				</a>
			</div>
		</div>

		<?php

		return ob_get_clean();
	}

	/**
	 * Print an overview of this release for the plugin submission
	 * and editing controls
	 *
	 * @since 0.1
	 */
	public function print_control_overview() {
		echo $this->get_control_overview();
	}

	/**
	 * Add missing parent `pkp_application` term ids to the array
	 * of assigned term ids
	 *
	 * If a child term is assigned, the parent term should always be
	 * assigned as well. This will search a list of terms and ensure
	 * that any missing parent ids are added.
	 *
	 * @since 0.1
	 */
	public function add_parent_applications() {

		if ( empty( $this->applications ) ) {
			return;
		}

		$args = array(
			'include' => $this->applications,
			'hide_empty' => false
		);
		$terms = get_terms( 'pkp_application', $args );

		foreach( $terms as $term ) {
			if ( $term->parent ) {
				$this->applications[] = (int) $term->parent;
			}
		}

		$this->applications = array_unique( $this->applications );
	}

}
} // endif
