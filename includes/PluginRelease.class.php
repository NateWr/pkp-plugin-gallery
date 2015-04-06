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
class pkppgPluginRelease {

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
	 * Post status
	 *
	 * @since 0.1
	 */
	public $post_status;

	/**
	 * Validation errors
	 *
	 * @since 0.1
	 */
	public $validation_errors = array();

	/**
	 * Initialize an empty object
	 *
	 * This way we can choose a number of methods to populate data.
	 *
	 * @since 0.1
	 */
	public function __construct() {}

	/**
	 * Load release information from a WP_Post object or an ID
	 *
	 * @uses self::load_wp_post()
	 * @since 0.1
	 */
	public function load_post( $post ) {

		if ( is_int( $post ) || is_string( $post ) ) {
			$post = get_post( $post );
		}

		if ( is_a( $post, 'WP_Post' ) && $post->post_type === pkppgInit()->cpts->plugin_release_post_type ) {
			$this->load_wp_post( $post );
			return true;
		}

		return false;
	}

	/**
	 * Load release object from a WP_Post object, retrieve metadata
	 * and load taxonomies
	 *
	 * @since 0.1
	 */
	public function load_wp_post( $post ) {

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
	 * Get the applications this release is compatible with
	 *
	 * @since 0.1
	 */
	public function get_applications() {

		if ( !empty( $this->applications ) ) {
			return $this->applications;
		}

		$applications = wp_get_post_terms( $post->ID, 'pkp_application' );
		if ( !is_wp_error( $applications ) ) {
			foreach( $applications as $application) {
				$this->applications[] = $application->term_id;
			}
		}

		return $this->applications;
	}

	/**
	 * Get the certification assigned to this release
	 *
	 * @since 0.1
	 */
	public function get_certification() {

		if ( !empty( $this->certification ) ) {
			return $this->certification;
		}

		$certifications = wp_get_post_terms( $post->ID, 'pkp_application' );
		if ( !is_wp_error( $certifications ) ) {
			foreach( $certifications as $certification ) {
				$this->certification[] = $certification->term_id;
			}
		}

		return $this->$certification;
	}

	/**
	 * Format the release date when it's pulled from the database
	 *
	 * @since 0.1
	 */
	public function format_date( $date ) {
		return mysql2date( get_option( 'date_format' ), $date );
	}

	/**
	 * Validate release data submitted from a form
	 *
	 * Expects to find data in $_POST
	 *
	 * @since 0.1
	 */
	public function validate_submission() {

		// @todo validate the data and assign errors to
		// $this->validation_errors().

		return $this->is_valid();
	}

	/**
	 * Check if release is valid
	 *
	 * This does not actually run the validation checks. It only looks
	 * to see if any validation errors have been set.
	 *
	 * @since 0.1
	 */
	public function is_valid() {
		return empty( $this->validation_errors );
	}

	/**
	 * Insert a release into the database
	 *
	 * Validates the data, adds it to the database, and fires off an
	 * action, either `pkppg_insert_release` or `pkppg_update_release`.
	 *
	 * @uses self::insert_post_data()
	 * @since 0.1
	 */
	public function insert_release() {

		$action = empty( $this->ID ) ? 'insert' : 'update';

		if ( $this->validate_submission() === false ) {
			return false;
		}

		if ( $this->insert_post_data() === false ) {
			return false;
		}

		do_action( 'pkppg_' . $action . '_release', $this );

		return true;
	}

	/**
	 * Insert post data for a new or updated release entry
	 *
	 * You should usually self::insert_release() instead of calling
	 * this method directly. self::insert_release() will check data
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
		);

		if ( !empty( $this->ID ) ) {
			$args['ID'] = $this->ID;
		}

		if ( !empty( $this->applications ) || !empty( $this->certification ) ) {
			$args['tax_input'] = array();

			if ( !empty( $this->applications ) ) {
				$args['tax_input']['pkp_application'] = $this->applications;
			}

			if ( !empty( $this->certification ) ) {
				$args['tax_input']['pkp_certification'] = $this->certification;
			}
		}

		$id = wp_insert_post( $args );

		if ( is_wp_error( $id ) || $id === false ) {
			$this->insert_post_error = $id;
			return false;
		} else {
			$this->ID = $id;
		}

		if ( !empty( $this->package ) ) {
			update_post_meta( $this->ID, '_package', $this->package );
		}

		if ( !empty( $this->md5 ) ) {
			update_post_meta( $this->ID, '_md5', $this->md5 );
		}
	}

}
} // endif
