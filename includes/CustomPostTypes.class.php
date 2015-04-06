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
	 * Register hooks
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// Register custom post types
		add_action( 'init', array( $this, 'load_cpts' ) );
	}

	/**
	 * Register custom post types
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
				'meta_box_cb' => array( $this, 'print_category_selection' ),
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
				'meta_box_cb' => array( $this, 'print_certification_selection' ),
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
	}

	/**
	 * Print a metabox to select a category
	 *
	 * @since 0.1
	 */
	public function print_category_selection() {
		pkppg_print_taxonomy_select( 'pkp_category' );
	}

	/**
	 * Print a metabox to select a certification
	 *
	 * @since 0.1
	 */
	public function print_certification_selection() {
		pkppg_print_taxonomy_select( 'pkp_certification' );
	}

}
} // endif
