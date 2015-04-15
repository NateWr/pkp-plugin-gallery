<?php defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'pkppgCustomPostTypes' ) ) {
/**
 * Class to declare custom post types and post meta
 *
 * @since 0.1
 */
class pkppgCustomPostTypes {

	/**
	 * Plugin post type
	 *
	 * @since 0.1
	 */
	public $plugin_post_type = 'pkp_plugin';

	/**
	 * Plugin release post type
	 *
	 * @since 0.1
	 */
	public $plugin_release_post_type = 'pkp_plugin_release';

	/**
     * Valid post statuses
	 *
	 * @since 0.1
	 */
	public $valid_post_statuses = array( 'submission', 'publish', 'revision' );

	/**
	 * Register hooks
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// Register custom post types
		add_action( 'init', array( $this, 'load_cpts' ) );

		// Add meta boxes
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	/**
	 * Register custom post types, taxonomies and post statuses
	 *
	 * @since 0.1
	 */
	public function load_cpts() {

		// Register the `pkp_application` taxonomy
		register_taxonomy(
			'pkp_application',
			$this->plugin_post_type,
			array(
				'labels' => array(
					'name'                       => __( 'Application',                   'pkp-plugin-gallery' ),
					'singular_name'              => __( 'Application',                   'pkp-plugin-gallery' ),
					'menu_name'                  => __( 'Applications',                   'pkp-plugin-gallery' ),
					'all_items'                  => __( 'All Applications',                   'pkp-plugin-gallery' ),
					'edit_item'                  => __( 'Edit Application',                   'pkp-plugin-gallery' ),
					'view_item'                  => __( 'View Application',                   'pkp-plugin-gallery' ),
					'update_item'                => __( 'Update Application',                   'pkp-plugin-gallery' ),
					'add_new_item'               => __( 'Add New Application',                   'pkp-plugin-gallery' ),
					'new_item_name'              => __( 'New Application Name',                   'pkp-plugin-gallery' ),
					'parent_item'                => __( 'Parent Application',                   'pkp-plugin-gallery' ),
					'search_items'               => __( 'Search Applications',                   'pkp-plugin-gallery' ),
					'popular_items'              => __( 'Common Applications',                   'pkp-plugin-gallery' ),
					'separate_items_with_commas' => __( 'Separate applications with commas',                   'pkp-plugin-gallery' ),
					'add_or_remove_items'        => __( 'Add or remove compatible applications',                   'pkp-plugin-gallery' ),
					'choose_from_most_used'      => __( 'Choose from most common applications',                   'pkp-plugin-gallery' ),
					'not_found'                  => __( 'No applications found',                   'pkp-plugin-gallery' ),
				),
				'hierarchical' => true,
				'meta_box_cb' => false,
			)
		);

		// Register the `pkp_category` taxonomy
		register_taxonomy(
			'pkp_category',
			$this->plugin_post_type,
			array(
				'labels' => array(
					'name'                       => __( 'Category',                   'pkp-plugin-gallery' ),
					'singular_name'              => __( 'Category',                   'pkp-plugin-gallery' ),
					'menu_name'                  => __( 'Categories',                   'pkp-plugin-gallery' ),
					'all_items'                  => __( 'All Categories',                   'pkp-plugin-gallery' ),
					'edit_item'                  => __( 'Edit Category',                   'pkp-plugin-gallery' ),
					'view_item'                  => __( 'View Category',                   'pkp-plugin-gallery' ),
					'update_item'                => __( 'Update Category',                   'pkp-plugin-gallery' ),
					'add_new_item'               => __( 'Add New Category',                   'pkp-plugin-gallery' ),
					'new_item_name'              => __( 'New Category Name',                   'pkp-plugin-gallery' ),
					'parent_item'                => __( 'Parent Category',                   'pkp-plugin-gallery' ),
					'search_items'               => __( 'Search Categories',                   'pkp-plugin-gallery' ),
					'popular_items'              => __( 'Popular Categories',                   'pkp-plugin-gallery' ),
					'separate_items_with_commas' => __( 'Separate categories with commas',                   'pkp-plugin-gallery' ),
					'add_or_remove_items'        => __( 'Add or remove assigned categories',                   'pkp-plugin-gallery' ),
					'choose_from_most_used'      => __( 'Choose from most used categories',                   'pkp-plugin-gallery' ),
					'not_found'                  => __( 'No categories found',                   'pkp-plugin-gallery' ),
				),
				'meta_box_cb' => array( $this, 'print_category_metabox' ),
			)
		);

		// Register the `pkp_certification` taxonomy
		register_taxonomy(
			'pkp_certification',
			$this->plugin_post_type,
			array(
				'labels' => array(
					'name'                       => __( 'Certification',                   'pkp-plugin-gallery' ),
					'singular_name'              => __( 'Certification',                   'pkp-plugin-gallery' ),
					'menu_name'                  => __( 'Certifications',                   'pkp-plugin-gallery' ),
					'all_items'                  => __( 'All Certifications',                   'pkp-plugin-gallery' ),
					'edit_item'                  => __( 'Edit Certification',                   'pkp-plugin-gallery' ),
					'view_item'                  => __( 'View Certification',                   'pkp-plugin-gallery' ),
					'update_item'                => __( 'Update Certification',                   'pkp-plugin-gallery' ),
					'add_new_item'               => __( 'Add New Certification',                   'pkp-plugin-gallery' ),
					'new_item_name'              => __( 'New Certification Name',                   'pkp-plugin-gallery' ),
					'parent_item'                => __( 'Parent Certification',                   'pkp-plugin-gallery' ),
					'search_items'               => __( 'Search Certifications',                   'pkp-plugin-gallery' ),
					'popular_items'              => __( 'Popular Certifications',                   'pkp-plugin-gallery' ),
					'separate_items_with_commas' => __( 'Separate certifications with commas',                   'pkp-plugin-gallery' ),
					'add_or_remove_items'        => __( 'Add or remove certifications',                   'pkp-plugin-gallery' ),
					'choose_from_most_used'      => __( 'Choose from most used certifications',                   'pkp-plugin-gallery' ),
					'not_found'                  => __( 'No certifications found',                   'pkp-plugin-gallery' ),
				),
				'meta_box_cb' => false,
			)
		);

		// Register the `pkp_plugin` post type
		register_post_type(
			$this->plugin_post_type,
			array(
				'labels' => array(
					'name'               => __( 'PKP Plugins',                   'pkp-plugin-gallery' ),
					'singular_name'      => __( 'PKP Plugin',                    'pkp-plugin-gallery' ),
					'menu_name'          => __( 'PKP Plugins',                   'pkp-plugin-gallery' ),
					'name_admin_bar'     => __( 'PKP Plugin',                   'pkp-plugin-gallery' ),
					'add_new'            => __( 'Add New',                 	   'pkp-plugin-gallery' ),
					'add_new_item'       => __( 'Add New PKP Plugin',            'pkp-plugin-gallery' ),
					'edit_item'          => __( 'Edit PKP Plugin',               'pkp-plugin-gallery' ),
					'new_item'           => __( 'New PKP Plugin',                'pkp-plugin-gallery' ),
					'view_item'          => __( 'View PKP Plugin',               'pkp-plugin-gallery' ),
					'search_items'       => __( 'Search PKP Plugins',            'pkp-plugin-gallery' ),
					'not_found'          => __( 'No PKP plugins found',          'pkp-plugin-gallery' ),
					'not_found_in_trash' => __( 'No pkp plugins found in trash', 'pkp-plugin-gallery' ),
					'all_items'          => __( 'All PKP Plugins',               'pkp-plugin-gallery' ),
				),
				'public'       => true,
				'has_archive'  => true,
				'show_ui'      => true,
				'menu_icon'    => 'dashicons-networking',
				'taxonomies'   => array(
					'pkp_application',
					'pkp_certification',
					'pkp_category',
				),
				'supports'     => array(
					'title',
					'editor',
					'author',
					'excerpt',
					'revisions',
				)
			)
		);

		// Register the `pkp_plugin_release` post type
		register_post_type(
			$this->plugin_release_post_type,
			array(
				'labels' => array(
					'name'               => __( 'PKP Plugin Releases',                   'pkp-plugin-gallery' ),
					'singular_name'      => __( 'PKP Plugin Release',                    'pkp-plugin-gallery' ),
					'menu_name'          => __( 'PKP Plugin Release',                   'pkp-plugin-gallery' ),
					'name_admin_bar'     => __( 'PKP Plugin Release',                   'pkp-plugin-gallery' ),
					'add_new'            => __( 'Add New',                 	   'pkp-plugin-gallery' ),
					'add_new_item'       => __( 'Add New PKP Plugin Release',            'pkp-plugin-gallery' ),
					'edit_item'          => __( 'Edit PKP Plugin Release',               'pkp-plugin-gallery' ),
					'new_item'           => __( 'New PKP Plugin Release',                'pkp-plugin-gallery' ),
					'view_item'          => __( 'View PKP Plugin Release',               'pkp-plugin-gallery' ),
					'search_items'       => __( 'Search PKP Plugin Releases',            'pkp-plugin-gallery' ),
					'not_found'          => __( 'No PKP plugin releases found',          'pkp-plugin-gallery' ),
					'not_found_in_trash' => __( 'No pkp plugin releases found in trash', 'pkp-plugin-gallery' ),
					'all_items'          => __( 'All PKP Plugin Releases',               'pkp-plugin-gallery' ),
				),
				'public'       => false,
				'taxonomies'   => array(
					'pkp_application',
					'pkp_certification',
				),
			)
		);

		// Submission post status
		register_post_status(
			'submission',
			array(
				'label' => __( 'Submission', 'pkp-plugin-gallery' ),
				'label_count' => _n_noop( 'Submission <span class="count">(%s)</span>', 'Submissions <span class="count">(%s)</span>' ),
			)
		);
	}

	/**
	 * Add metaboxes to the post editing screens
	 *
	 * @since 0.1
	 */
	public function add_meta_boxes() {

		// Add a homepage metabox
		add_meta_box(
			'pkppg_homepage',
			'Plugin Homepage',
			array( $this, 'print_homepage_metabox' ),
			$this->plugin_post_type,
			'side',
			'core'
		);

		// Add an installation instructions metabox
		add_meta_box(
			'pkppg_installation',
			'Installation Instructions',
			array( $this, 'print_installation_metabox' ),
			$this->plugin_post_type,
			'side',
			'core'
		);

		// Add a releases metabox
		add_meta_box(
			'pkppg_releases',
			'Releases',
			array( $this, 'print_releases_metabox' ),
			$this->plugin_post_type,
			'normal',
			'core'
		);
	}

	/**
	 * Print a metabox to select a category
	 *
	 * @since 0.1
	 */
	public function print_category_metabox() {
		pkppg_print_taxonomy_select( 'pkp_category' );
	}

	/**
	 * Print a metabox to enter a plugin's homepage
	 *
	 * @since 0.1
	 */
	public function print_homepage_metabox( $post ) {

		$homepage = get_post_meta( $post->ID, '_homepage', true );

		?>

		<?php wp_nonce_field( 'pkppg_edit_plugin', 'pkppg_edit_plugin' ); ?>

		<input type="text" name="_homepage" value="<?php echo esc_attr( $homepage ); ?>" placeholder="http://">

		<p class="description">
			<?php _e( 'Enter the URL where we can find more information about this plugin.' ); ?>
		</p>

		<?php
	}

	/**
	 * Print a metabox to enter a plugin's installation instructions
	 *
	 * @since 0.1
	 */
	public function print_installation_metabox( $post ) {

		$installation = get_post_meta( $post->ID, '_installation', true );

		?>

		<textarea name="_installation"><?php echo $installation; ?></textarea>

		<p class="description">
			<?php _e( 'Enter a brief description of any installation instructions or requirements.' ); ?>
		</p>

		<?php
	}

	/**
	 * Print a metabox to add version releases of a plugin
	 *
	 * @since 0.1
	 */
	public function print_releases_metabox( $post ) {

		pkppg_print_releases_editor( $post->ID );
	}

	/**
	 * Save post metabox data from the PKP Plugin editing screen
	 *
	 * @since 0.1
	 */
	public function save_post( $post_id ) {

		// Verify the nonce
		if( empty( $_POST['pkppg_edit_plugin'] ) || !wp_verify_nonce( $_POST['pkppg_edit_plugin'], 'pkppg_edit_plugin' ) ) {
			return $post_id;
		}

		// Check permissions
		// @todo use custom permissions
		if ( !current_user_can( 'edit_post' , $post_id ) ) {
			return $post_id;
		}

		// Define sanitization callbacks for each meta field
		$meta = array(
			'_homepage' => 'sanitize_text_field',
			'_installation' => 'wp_kses_post',
		);

		// Sanitize and save meta data
		foreach( $meta as $meta_id => $sanitize_callback ) {
			$cur = get_post_meta( $post_id, $meta_id, true );
			$new = call_user_func( $sanitize_callback, $_POST[ $meta_id ] );
			if ( !empty( $new ) && $new != $cur ) {
				update_post_meta( $post_id, $meta_id, $new );
			} elseif ( isset( $new ) && $new == '' ) {
				delete_post_meta( $post_id, $meta_id, $cur );
			}
		}
	}

	/**
	 * Check if a post status is valid
	 *
	 * @since 0.1
	 */
	public function is_valid_status( $status ) {
		return in_array( $status, $this->valid_post_statuses );
	}

	/**
	 * Get a hierarchical array of `pkp_application` terms
	 *
	 * @since 0.1
	 */
	public function get_application_terms( $args = array() ) {

		$args = array_merge(
			array(
				'hide_empty' => false,
				'order' => 'DESC',
			),
			$args
		);

		$terms = get_terms( 'pkp_application', $args );

		// Put terms into a hierarchical array so that child terms
		// are attached to their parents
		$ordered = array();
		foreach( $terms as $term ) {

			$parent = $term->parent ? $term->parent : $term->term_id;

			if ( empty( $ordered[ $parent ] ) ) {
				$ordered[ $parent ] = array();
			}

			if ( $parent == $term->term_id ) {
				$ordered[ $parent ] = $term;
			} else {

				if ( empty( $ordered[ $parent ]->children ) ) {
					$ordered[ $parent ]->children = array();
				}

				$ordered[ $parent ]->children[ $term->term_id ] = $term;
			}
		}

		return $ordered;
	}

}
} // endif
