<?php defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'pkppgPostModel' ) ) {
/**
 * Base class for models that reflect posts in the database
 *
 * @since 0.1
 */
abstract class pkppgPostModel {

	/**
	 * Post Type
	 *
	 * @since 0.1
	 */
	public $post_type;

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
	 * @since 0.1
	 */
	public function __construct() {}

	/**
	 * Load post information from a WP_Post object or an ID
	 *
	 * @uses self::load_wp_post()
	 * @since 0.1
	 */
	public function load_post( $post ) {

		if ( is_int( $post ) || is_string( $post ) ) {
			$post = get_post( $post );
		}

		if ( is_a( $post, 'WP_Post' ) && $post->post_type === $this->post_type ) {
			$this->load_wp_post( $post );
			return true;
		}

		return false;
	}

	/**
	 * Load post object from a WP_Post object, retrieve metadata
	 * and load taxonomies
	 *
	 * @since 0.1
	 */
	abstract function load_wp_post( $post );

	/**
	 * Get the category this post is assigned to
	 *
	 * @since 0.1
	 */
	public function get_category() {

		if ( !empty( $this->category ) ) {
			return $this->category;
		}

		if ( empty( $this->ID ) ) {
			return;
		}

		$categories = wp_get_post_terms( $this->ID, 'pkp_category' );
		if ( !is_wp_error( $categories ) && is_array( $categories ) && !empty( $categories ) ) {
			$this->category = $categories[0]->term_id;
		}

		return $this->category;
	}

	/**
	 * Get the applications this post is compatible with
	 *
	 * @since 0.1
	 */
	public function get_applications() {

		if ( !empty( $this->applications ) ) {
			return $this->applications;
		}

		if ( empty( $this->ID ) ) {
			return;
		}

		$applications = wp_get_post_terms( $this->ID, 'pkp_application' );
		if ( !is_wp_error( $applications ) ) {
			foreach( $applications as $application) {
				$this->applications[] = $application->term_id;
			}
		}

		return $this->applications;
	}

	/**
	 * Get the certification assigned to this post
	 *
	 * @since 0.1
	 */
	public function get_certification() {

		if ( !empty( $this->certification ) ) {
			return $this->certification;
		}

		if ( empty( $this->ID ) ) {
			return;
		}

		$certifications = wp_get_post_terms( $this->ID, 'pkp_certification' );
		if ( !is_wp_error( $certifications ) ) {
			foreach( $certifications as $certification ) {
				$this->certification[] = $certification->term_id;
			}
		}

		return $this->certification;
	}

	/**
	 * Format the date when it's pulled from the database
	 *
	 * @todo Format the date properly once we have set up a datepicker
	 * and plugged the pieces together properly.
	 * @since 0.1
	 */
	public function format_date( $date ) {
		$dt = DateTime::createFromFormat( 'Y-m-d H:i:s', $date );
		return $dt->format( 'Y-m-d' );
//		return mysql2date( get_option( 'date_format' ), $date );
	}

	/**
	 * Parse and sanitize incoming data
	 *
	 * @since 01.
	 */
	abstract function parse_params( $params );

	/**
	 * Validate data in this object
	 *
	 * This should be called before adding a post to the database. It
	 * will only check for required values and set sane defaults where
	 * they are missing.
	 *
	 * @since 0.1
	 */
	abstract function validate();

	/**
	 * Check if post is valid
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
	 * Save a post to the database
	 *
	 * Validates the data, adds it to the database, and fires off an
	 * action
	 *
	 * @uses self::insert_post_data()
	 * @since 0.1
	 */
	public function save() {

		$action = empty( $this->ID ) ? 'insert' : 'update';

		if ( $this->validate() === false ) {
			return false;
		}

		if ( $this->insert_post_data() === false ) {
			return false;
		}

		do_action( 'pkppg_' . $action . '_' . $this->post_type, $this );

		return true;
	}

	/**
	 * Insert post data for a new or updated entry
	 *
	 * You should usually self::save() instead of calling
	 * this method directly. self::save() will check data
	 * validation and call an expected action hook.
	 *
	 * @since 0.1
	 */
	abstract function insert_post_data();

	/**
	 * Delete post from the database
	 *
	 * @since 0.1
	 */
	public function delete() {

		if ( empty( $this->ID ) ) {
			return false;
		}

		return wp_delete_post( $this->ID );
	}

	/**
	 * Add an error to the validation errors array
	 *
	 * @since 0.1
	 */
	public function add_error( $field, $value, $message ) {
		$this->validation_errors[] = array(
			'field'   => $field,
			'value'   => $value,
			'message' => $message,
		);
	}

}
} // endif
