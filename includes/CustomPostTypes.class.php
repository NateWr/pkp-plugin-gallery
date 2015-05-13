<?php defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'pkppgCustomPostTypes' ) ) {
/**
 * Class to declare custom post types and post meta boxes, and handle WP's
 * native edit_post screen
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
	public $valid_post_statuses = array( 'submission', 'publish', 'update', 'disable' );

	/**
	 * Register hooks
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// Register custom post types
		add_action( 'init', array( $this, 'load_cpts' ) );

		// Add meta boxes to edit post screens
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );

		// Add the compare changes modal to the `pkp_plugin` edit post screen
		add_action( 'admin_footer', array( $this, 'print_diff_modal' ) );

		// Add new views to plugin admin list table
		add_filter( 'views_edit-pkp_plugin', array( $this, 'add_views' ) );
		add_filter( 'request', array( $this, 'modify_admin_table_request' ) );

		// Remove attached releases when a plugin is deleted
		add_action( 'delete_post', array( $this, 'fire_on_delete' ) );

		// Add notification bubble to plugin
		add_filter( 'add_menu_classes', array( $this, 'add_update_notification' ) );

		// Bust update count transient whenever a plugin or release is saved
		add_action( 'pkppg_insert_' . $this->plugin_post_type, array( $this, 'delete_update_transient' ) );
		add_action( 'pkppg_update_' . $this->plugin_post_type, array( $this, 'delete_update_transient' ) );
		add_action( 'pkppg_insert_' . $this->plugin_release_post_type, array( $this, 'delete_update_transient' ) );
		add_action( 'pkppg_update_' . $this->plugin_release_post_type, array( $this, 'delete_update_transient' ) );
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
				'rewrite' => array(
					'slug' => 'plugins/application',
					'with_front' => false,
					'hierarchical' => true,
				),
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
				'rewrite' => array(
					'slug' => 'plugins/category',
					'with_front' => false,
				),
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
				),
				'rewrite'      => array(
					'slug'       => __( 'plugins', 'pkp-plugin-gallery' ),
					'with_front' => false,
					'feeds'      => false,
				),
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
				'exclude_from_search' => true,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
			)
		);

		// Update post status
		register_post_status(
			'update',
			array(
				'label' => __( 'Update', 'pkp-plugin-gallery' ),
				'label_count' => _n_noop( 'Update <span class="count">(%s)</span>', 'Edits <span class="count">(%s)</span>' ),
				'exclude_from_search' => true,
			)
		);

		// Disable post status
		register_post_status(
			'disable',
			array(
				'label' => __( 'Disabled', 'pkp-plugin-gallery' ),
				'label_count' => _n_noop( 'Disabled <span class="count">(%s)</span>', 'Edits <span class="count">(%s)</span>' ),
				'exclude_from_search' => true,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
			)
		);
	}

	/**
	 * Add metaboxes to the post editing screens
	 *
	 * @since 0.1
	 */
	public function add_meta_boxes() {

		// Override publish metabox
		add_meta_box(
			'submitdiv',
			__( 'Publish' ),
			array( $this, 'print_submit_metabox' ),
			$this->plugin_post_type,
			'side',
			'high'
		);

		// Add a homepage metabox
		add_meta_box(
			'pkppg_homepage',
			__( 'Project URL', 'pkp-plugin-gallery' ),
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

		<input type="text" name="_homepage" value="<?php echo esc_attr( $homepage ); ?>" placeholder="http://github.com/you/your_plugin/">

		<p class="description">
			<?php esc_html_e( 'Enter the URL where we can find more information about this plugin.' ); ?>
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
			<?php esc_html_e( 'Enter a brief description of any installation instructions or requirements.' ); ?>
		</p>

		<?php
	}

	/**
	 * Print a metabox to add version releases of a plugin
	 *
	 * @since 0.1
	 */
	public function print_releases_metabox( $post ) {

		if ( $post->post_status == 'update' ) {
			$url = add_query_arg( 'post', $post->post_parent );
			?>

			<p>
				<?php printf( __( 'You are viewing an update to a <a href="%s">published plugin</a>. All releases should be managed there.', 'pkp-plugin-gallery' ), esc_url( $url ) ); ?>
			</p>

			<?php
		} else {
			pkppg_print_releases_editor( $post->ID );
		}
	}

	/**
	 * Print the publish metabox override on admin edit post screens
	 *
	 * HTML markup is designed to mimic WordPress publish metabox markup
	 *
	 * @since 0.1
	 */
	public function print_submit_metabox( $post ) {

		$delete_text = __( 'Move to Trash', 'pkp-plugin-gallery' );
		if ( !EMPTY_TRASH_DAYS ) {
			$delete_text = __( 'Delete Permanently', 'pkp-plugin-gallery' );
		}

		$parent_url = '';
		if ( $post->post_status == 'update' ) {
			$parent_url = admin_url( 'post.php?post=' . (int) $post->post_parent . '&action=edit' );

		} else {

			// Get revisions
			$revisions_count = 0;
			if ( $post->post_status != 'auto-draft' ) {
				$revisions = wp_get_post_revisions( $post->ID );
				$revisions_count = count( $revisions );
				$revision_id = key( $revisions );
				$revisions_to_keep = wp_revisions_to_keep( $post );
			}

			// Get updates
			$args = array(
				'posts_per_page' => 1000,
				'post_status' => 'update',
				'post_parent' => $post->ID,
				'orderby' => 'modified',
			);

			$query = new pkppgQuery( $args );
			$updates = $query->get_results();

		}


		$edit_url = add_query_arg( 'action', 'edit', admin_url( 'post.php' ) );

		$template = pkppgInit()->get_template_path( 'metabox-publish.php' );
		if ( !empty( $template ) ) {
			include( $template );
		}
	}

	/**
	 * Add the compare changes modal to the `pkp_plugin` edit post screen
	 *
	 * @since 0.1
	 */
	public function print_diff_modal() {

		if ( !function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();
		if ( empty( $screen ) || !is_a( $screen, 'WP_Screen' ) || $screen->post_type !== $this->plugin_post_type || $screen->parent_base !== 'edit' ) {
			return false;
		}

		ob_start();
		?>

		<div id="pkp-plugin-diff"></div>
		<div class="controls">
			<a href="#" class="publish button-primary">
				<?php esc_html_e( 'Publish Changes', 'pkp-plugin-gallery' ); ?>
			</a>
			<a href="#" class="close button">
				<?php esc_html_e( 'Close', 'pkp-plugin-gallery' ); ?>
			</a>
			<span class="pkp-spinner"></span>
			<span class="pkp-success"></span>
			<span class="pkp-error"></span>
		</div>

		<?php

		pkppgInit()->print_modal( 'pkppg-diff', ob_get_clean(), __( 'Compare Changes', 'pkp-plugin-gallery' ) );
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

		// Bust update count transient
		$this->delete_update_transient();

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
	 * Check if a post type is valid
	 *
	 * @since 0.1
	 */
	public function is_valid_type( $type ) {
		return $type == $this->plugin_post_type || $type == $this->plugin_release_post_type;
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

	/**
	 * Get array of IDs for plugins with updates or where attached releases have
	 * updates
	 *
	 * @since 0.1
	 */
	public function get_plugins_with_updates() {

		// Get the number of plugins with updates
		$args = array(
			'posts_per_page' => 1000, // large upper limit
			'post_status' => 'update',
			'post_type' => array( $this->plugin_post_type, $this->plugin_release_post_type ),
		);
		$updated = new WP_Query( $args );

		$plugins = $releases = array();
		foreach( $updated->posts as $post ) {
			if ( $post->post_type == $this->plugin_post_type ) {
				$plugins[] = $post->post_parent;
			} else {
				$releases[] = $post->post_parent;
			}
		}

		// To get a list of just the plugins let's query the release IDs we have
		// and get their parents, which should be plugins.
		if ( !empty( $releases ) ) {
			$releases = array_unique( $releases );

			$args = array(
				'posts_per_page' => 1000, // large upper limit
				'post_status' => $this->valid_post_statuses,
				'post_type' => array( $this->plugin_release_post_type ),
				'fields' => 'id=>parent',
				'post__in' => $releases,
			);

			$updated = new WP_Query( $args );

			foreach( $updated->posts as $post ) {
				$plugins[] = $post->post_parent;
			}
		}

		wp_reset_query();

		return array_unique( $plugins );
	}

	/**
	 * Get count of plugins with updates
	 *
	 * This is cached as a transient and refreshed only once per hour
	 *
	 * @since 0.1
	 */
	public function get_plugin_update_count() {

		$count = get_transient( 'pkppg_plugin_updates' );
		if ( $count !== false ) {
			return $count;
		}

		$count = count( $this->get_plugins_with_updates() );


		set_transient( 'pkppg_plugin_updates', $count, 4 * HOUR_IN_SECONDS );

		return $count;

	}

	/**
	 * Add custom views
	 *
	 * @since 0.1
	 */
	public function add_views( $views ) {

		$plugin_count = $this->get_plugin_update_count();

		$current = '';
		if ( !empty( $_GET['has_update'] ) ) {
			$current = ' class="current"';
		}

		$count = '<span class="count">' . sprintf( esc_html__( '(%d)', 'pkp-plugin-gallery' ), $plugin_count ) . '</span>';

		$url = admin_url( 'edit.php?post_type=' . $this->plugin_post_type . '&has_update=1' );
		$views['has_update'] = '<a href="' . esc_url( $url ) . '"' . $current . '>' . sprintf( esc_html__( 'Updated %s', 'pkp-plugin-gallery' ), $count ) . '</a>';

		return $views;
	}

	/**
	 *
	 *
	 * @since 0.1
	 */
	public function modify_admin_table_request( $args ) {

		if ( empty( $_GET['has_update'] ) || !is_admin() || !function_exists( 'get_current_screen' ) ) {
			return $args;
		}

		$screen = get_current_screen();
		if ( empty( $screen ) || !is_a( $screen, 'WP_SCREEN' ) || $screen->id !== 'edit-pkp_plugin' ) {
			return $args;
		}

		$plugins = $this->get_plugins_with_updates();

		// Make sure no results are found
		if ( empty( $plugins ) ) {
			$args['p'] = -1;
			return $args;
		}

		$args['post__in'] = $plugins;

		return $args;
	}

	/**
	 * Fire when a plugin is deleted
	 *
	 * This is fired whenever a post is deleted. If the post is a plugin, it
	 * will load the object and run a method that processes maintenance routines.
	 * Ideally, this code should go into our pkppgPlugin model. But when we're
	 * using the native WordPress delete UI we won't have the object
	 * automatically set up. So in order to ensure we're hooked in properly, we
	 * need to instantiate the process here in the pkppgCustomPostTypes
	 * singleton, fire up our plugin object, and run the object's method.
	 *
	 * @since 0.1
	 */
	public function fire_on_delete( $id ) {

		$post = get_post( $id );

		if ( $post->post_type !== $this->plugin_post_type ) {
			return;
		}

		$plugin = new pkppgPlugin();
		$plugin->load_post( $post );

		$plugin->delete_attached_releases();
		$plugin->delete_attached_updates();

		// Delete the transient where update counts are stored
		$this->delete_update_transient();
	}

	/**
	 * Add a notification bubble to the admin menu when there are updates
	 * pending for plugins
	 *
	 * @since 0.1
	 */
	public function add_update_notification( $menu ) {

		$updates = $this->get_plugin_update_count();

		if ( !$updates ) {
			return $menu;
		}

		foreach( $menu as $i => $item ) {
			if ( $item[2] == 'edit.php?post_type=' . $this->plugin_post_type ) {
				$menu[$i][0] .= ' <span class="awaiting-mod"><span>' . $updates . '</span></span>';
			}
		}

		return $menu;
	}

	/**
	 * Delete update count transient
	 *
	 * @since 0.1
	 */
	public function delete_update_transient() {
		delete_transient( 'pkppg_plugin_updates' );
	}

}
} // endif
