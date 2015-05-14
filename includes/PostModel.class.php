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
	 * Post parent
	 *
	 * Used by posts with `update` status to refer to the plugin or release
	 * which they are an update for.
	 *
	 * @since 0.1
	 */
	public $post_parent;

	/**
	 * Last modified date
	 *
	 * @since 0.1
	 */
	public $post_modified;

	/**
	 * Updates for this object
	 *
	 * @since 0.1
	 */
	public $updates = array();

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
	 * Load updates for this object
	 *
	 * This will pull posts with this object as their `post_parent` and a
	 * `post_status` of `update`. New objects are created and attached as
	 * updates.
	 *
	 * @since 0.1
	 */
	public function load_updates() {

		$args = array(
			'posts_per_page' => 1000, // large upper range
			'no_found_rows' => true,
			'post_type' => $this->post_type,
			'post_status' => 'update',
			'post_parent' => $this->ID,
			'orderby' => 'modified',
		);

		$query = new WP_Query( $args );

		if ( !$query->have_posts() ) {
			return;
		}

		while( $query->have_posts() ) {
			$query->the_post();

			$obj = $query->post->post_type == pkppgInit()->cpts->plugin_post_type ? new pkppgPlugin() : new pkppgPluginRelease();
			$obj->load_post( $query->post );
			$this->updates[] = $obj;
		}
	}

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
				$this->applications[] = $application->slug;
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
				$this->certification[] = $certification->slug;
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
	 * Publish post
	 *
	 * This only shifts the post status after checking user
	 * capabilities.
	 *
	 * @since 0.1
	 */
	public function publish() {

		// @todo better user cap check
		if ( empty( $this->ID ) || !current_user_can( 'manage_options' ) ) {
			return;
		}

		// Update the model
		$this->post_status = 'publish';

		wp_publish_post( $this->ID );

		// wp_publish_post always returns null
		return true;
	}

	/**
	 * Disable post
	 *
	 * This only shifts the post status after checking user
	 * capabilities.
	 *
	 * @since 0.1
	 */
	public function disable() {

		// @todo better user cap check
		if ( empty( $this->ID ) || !current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->post_status = 'disable';

		return wp_update_post( array( 'ID' => $this->ID, 'post_status' => $this->post_status ) );
	}

	/**
	 * Merge this post with its parent
	 *
	 * This simply takes the current post object (with taxonomies and metadata)
	 * and swaps out its ID for its parent's ID, then saves it to overwrite the
	 * parent. This should only happen with `update` posts where the parent has
	 * a `publish` post status. Only admins should be able to do this.
	 */
	public function merge_update() {

		// @todo better cap check
		if ( !current_user_can( 'manage_options' ) || $this->post_status !== 'update' ) {
			return;
		}

		$class = get_class( $this );

		$parent = new $class();
		$parent->load_post( $this->post_parent );

		// Sanity check...
		if ( $parent->post_status !== 'publish' ) {
			return;
		}

		// Save copy of old object
		$old = clone $this;

		// Assume parent's identity
		$this->ID = $this->post_parent;
		$this->post_parent = $parent->post_parent;
		$this->post_status = $parent->post_status;

		if ( !$this->save() ) {
			return;
		}

		// Delete the old update post
		return $old->delete();
	}

	/**
	 * Check if a post with a status of `update` is a new
	 * update of a submission/published post or an edit of
	 * update already assigned.
	 *
	 * @since 0.1
	 */
	public function is_update_new() {

		if ( empty( $this->ID ) ) {
			return true;
		}

		$old = get_post( $this->ID );

		if ( !$old || is_wp_error( $old ) || $old->post_status !== 'update' ) {
			return true;
		}

		return false;
	}

	/**
	 * Return the name of the requested taxonomy terms assigned to this object
	 *
	 * If the object has an array of terms, it will return the names separated
	 * by `$sep`
	 *
	 * @since 0.1
	 */
	public function get_term_name( $taxonomy, $sep = ', ', $field = 'slug' ) {

		$slugs = '';
		if ( $taxonomy == 'pkp_application' ) {
			$slugs = $this->applications;
		} elseif ( $taxonomy == 'pkp_certification' ) {
			$slugs = $this->certification;
		} elseif ( $taxonomy = 'pkp_category' ) {
			$slugs = $this->category;
		}

		if ( empty( $slugs ) ) {
			return '';
		}

		if ( is_array( $slugs ) ) {
			$name = array();
			foreach( $slugs as $slug ) {
				$term = get_term_by( $field, $slug, $taxonomy );
				$name[] = $term->name;
			}

			return join( $sep, $name );
		}


		$term = get_term_by( $field, $slugs, $taxonomy );

		if ( is_wp_error( $term ) ) {
			return '';
		}

		return $term->name;
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

	/**
	 * Print an HTML block with any errors attached to the requested field
	 *
	 * @since 0.1
	 */
	public function print_errors( $field ) {

		if ( empty( $field ) ) {
			return;
		}

		foreach( $this->validation_errors as $error ) {
			if ( $error['field'] == $field ) {
				?>

				<div class="error error-<?php echo esc_attr( $error['field'] ); ?>">
					<?php echo esc_html( $error['message'] ); ?>
				</div>

				<?php
			}
		}
	}

}
} // endif
