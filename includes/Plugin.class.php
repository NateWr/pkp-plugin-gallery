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
	 * Initialize
	 *
	 * @since 0.1
	 */
	public function __construct() {
		$this->post_type = pkppgInit()->cpts->plugin_post_type;
	}

	/**
	 * Load release object from a WP_Post object, retrieve metadata
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
			$this->category = absint( $params['category'] );
		}

		if ( isset( $params['applications'] ) ) {
			$this->applications = array_map( 'absint', $params['applications'] );
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

		// Post Status
		if ( empty( $this->post_status ) ) {
			$this->post_status = empty( $this->ID ) ? 'submission' : 'inherit';

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
			if ( $this->post_status == 'inherit' ) {
				$args['post_type'] = 'revision';
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

		if ( !empty( $this->md5 ) ) {
			update_post_meta( $this->ID, '_installation', $this->installation );
		}
	}
}
} // endif
