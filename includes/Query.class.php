<?php defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'pkpkpgQuery' ) ) {
/**
 * Class to handle queries used to pull plugins from
 * the database.
 *
 * Queries return an array of pkppgPlugin or
 * pkppgPluginRelease objects.
 *
 * @since 0.1
 */
class pkppgQuery {

	/**
	 * Query args
	 *
	 * Passed to WP_Query
	 * http://codex.wordpress.org/Class_Reference/WP_Query
	 *
	 * @since 0.1
	 */
	public $args = array();

	/**
	 * Default query args
	 *
	 * @since 0.1
	 */
	public $defaults;

	/**
	 * Instantiate the query with an array of arguments
	 *
	 * This supports all WP_Query args as well as several
	 * short-hand arguments or directives to modify query
	 * results.
	 *
	 * @see rtbQuery::prepare_args()
	 * @param args array Options to tailor the query
	 * @param context string Context for the query, used
	 *  in filters
	 * @since 0.1
	 */
	public function __construct( $args = array() ) {

		$this->defaults = array(
			'post_type'			=> pkppgInit()->cpts->plugin_post_type,
			'posts_per_page'	=> 10,
			'post_status'		=> 'publish',
		);

		$this->args = wp_parse_args( $args, $this->defaults );
	}

	/**
	 * Parse the args array and convert custom arguments
	 * for use by WP_Query
	 *
	 * @since 0.1
	 */
	public function prepare_args() {

		// Taxonomies
		if ( !empty( $this->args['categories'] ) ) {
			$this->add_taxonomy_terms( 'pkp_category', $this->args['categories'] );
			unset( $this->args['categories'] );
		}

		if ( !empty( $this->args['applications'] ) ) {
			$this->add_taxonomy_terms( 'pkp_application', $this->args['applications'] );
			unset( $this->args['applications'] );
		}

		if ( !empty( $this->args['certifications'] ) ) {
			$this->add_taxonomy_terms( 'pkp_certification', $this->args['certifications'] );
			unset( $this->args['certifications'] );
		}

		// Post status
		if ( is_string( $this->args['post_status'] ) ) {
			if ( !pkppgInit()->cpts->is_valid_status( $this->args['post_status'] ) ) {
				$this->args['post_status'] = $this->defaults['post_status'];
			}
		} elseif ( is_array( $this->args['post_status'] ) ) {
			$statuses = array();
			foreach( $this->args['post_status'] as $status ) {
				if ( pkppgInit()->cpts->is_valid_status( $status ) ) {
					$statuses[] = $status;
				}
			}
			if ( empty( $statuses ) ) {
				$this->args['post_status'] = $this->defaults['post_status'];
			} else {
				$this->args['post_status'] = $statuses;
			}
		}
	}

	/**
	 * Sanitize incoming `$_REQUEST` args
	 *
	 * @since 0.1
	 */
	public function sanitize_incoming_request() {

		// Taxonomies
		$taxonomies = array( 'categories', 'applications', 'certifications' );
		foreach( $taxonomies as $taxonomy ) {
			if ( !isset( $_REQUEST[ $taxonomy ] ) ) {
				continue;
			} elseif ( is_array( $_REQUEST[ $taxonomy ] ) ) {
				$terms = array_map( 'absint', $_REQUEST[ $taxonomy ] );
			} elseif ( is_string( $_REQUEST[ $taxonomy ] ) ) {
				$terms = absint( $_REQUEST[ $taxonomy ] );
			}
			$this->args[ $taxonomy ] = $terms;
		}

		// Post status
		if ( isset( $_REQUEST['post_status'] ) && is_string( $_REQUEST['post_status'] ) ) {
			$this->args['post_status'] = sanitize_key( $_REQUEST['post_status'] );
		} elseif ( isset( $_REQUEST['post_status'] ) && is_array( $_REQUEST['post_status'] ) ) {
			$this->args['post_status'] = array_map( 'sanitize_key', $_REQUEST['post_status'] );
		}
	}

	/**
	 * Add a set of taxonomy terms to the query args
	 *
	 * @since 0.1
	 */
	public function add_taxonomy_terms( $taxonomy, $terms ) {

		if ( !isset( $this->args['tax_query'] ) ) {
			$this->args['tax_query'] = array();
		}

		$this->args['tax_query'][] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'term_id',
			'terms'    => $terms,
		);
	}

	/**
	 * Execute query and return results
	 *
	 * @since 0.1
	 */
	public function get_results() {

		$this->prepare_args();

		// Store old $post so that we can use this in admin where `$post` isn't
		// constructed from the usual query args
		global $post;
		$old_post = $post;

		$query = new WP_Query( $this->args );

		if ( !$query->have_posts() ) {
			return;
		}

		$results = array();
		while( $query->have_posts() ) {
			$query->the_post();

			$obj = $query->post->post_type == pkppgInit()->cpts->plugin_post_type ? new pkppgPlugin() : new pkppgPluginRelease();
			$obj->load_post( $query->post );

			// Add updates for each object to the results
			// This is not very performant and requires a new lookup for each
			// parent, so it should only really be used when retrieving singular
			// objects or a small number of them.
			if ( $this->args['with_updates'] ) {
				$obj->load_updates();
			}

			$results[] = $obj;
		}

		// Manually reset the `$post` in case we are in the admin area where
		// it's not set by query args.
		$post = $old_post;
		wp_reset_query();

		return $results;
	}

}
} // endif
