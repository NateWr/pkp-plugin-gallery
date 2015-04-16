<?php defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'pkppgSettings' ) ) {
/**
 * Class to handle settings for the plugin gallery
 *
 * This class will load the settings page and provide
 * access to settings.
 *
 * @since 0.1
 */
class pkppgSettings {

	/**
	 * Defaults
	 *
	 * @since 0.1
	 */
	public $defaults;

	/**
	 * Stored values for settings
	 *
	 * @since 0.1
	 */
	public $settings;

	/**
	 * Register hooks
	 *
	 * @since 0.1
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );

		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Get a setting's value or fallback to a default if one exists
	 *
	 * @since 0.1
	 */
	public function get_setting( $setting ) {

		if ( empty( $this->settings ) ) {
			$this->settings = get_option( 'pkp_plugin_settings' );
		}

		if ( !empty( $this->settings[ $setting ] ) ) {
			return $this->settings[ $setting ];
		}

		if ( !empty( $this->defaults[ $setting ] ) ) {
			return $this->defaults[ $setting ];
		}
	}

	/**
	 * Add a settings page
	 *
	 * @since 0.1
	 */
	public function add_settings_page() {

		add_submenu_page(
			'edit.php?post_type=pkp_plugin',
			__( 'Settings', 'pkp-plugin-gallery' ),
			__( 'Settings', 'pkp-plugin-gallery' ),
			'manage_options',
			'pkp_plugin_settings',
			array( $this, 'print_settings_page' )
		);
	}

	/**
	 * Register the settings with the Settings API
	 *
	 * @since 0.1
	 */
	public function register_settings() {

		add_settings_section(
			'pkp_plugin_settings',
			__( 'Settings', 'pkp-plugin-gallery' ),
			'__return_false',
			'pkp_plugin_settings'
		);

		add_settings_field(
			'page',
			__( 'Plugin Gallery Page', 'pkp-plugin-gallery' ),
			array( $this, 'print_page_select' ),
			'pkp_plugin_settings',
			'pkp_plugin_settings',
			$this->get_setting( 'page' )
		);

		register_setting(
			'pkp_plugin_settings',
			'pkp_plugin_settings',
			array( $this, 'sanitize_settings' )
		);
	}

	/**
	 * Print the settings page
	 *
	 * @since 0.1
	 */
	public function print_settings_page() {

		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( __('You do not have sufficient permissions to access this page.', 'pkp-plugin-gallery' ) );
		}

		?>

		<div class="wrap">
			<h2>Settings</h2>
		</div>
		<form method="post" action="options.php">
			<?php settings_fields( 'pkp_plugin_settings' ); ?>
			<?php do_settings_sections( 'pkp_plugin_settings' ); ?>
			<?php submit_button(); ?>
		 </form>

		<?php
	}

	/**
	 * Print a page selection field
	 *
	 * @since 0.1
	 */
	public function print_page_select( $value ) {

		$pages = new WP_Query(
			array(
					'post_type' 		=> 'page',
					'posts_per_page'	=> -1,
					'post_status'		=> 'publish',
			)
		);

		if ( !$pages->have_posts() ) :

		?>

			<p class="description">
				<?php _e( 'You have no pages eligible for the Plugin Gallery Page. You need at least one published page.', 'pkp-plugin-gallery' ); ?>
			</p>

		<?php else : ?>

			<select name="pkp_plugin_settings[page]">

				<?php while( $pages->have_posts() ) : $pages->the_post(); ?>
				<option value="<?php echo get_the_ID(); ?>" <?php selected( $value, get_the_ID() ); ?>>
					<?php echo esc_attr( get_the_title() ); ?>
				</option>
				<?php endwhile; ?>

			</select>

		<?php endif;
	}

	/**
	 * Sanitize settings values
	 *
	 * @since 0.1
	 */

	public function sanitize_settings( $input ) {

		$new = array();

		if ( !empty( $input['page']) ) {
			$new['page'] = absint( $input['page'] );
		}

		return $new;
	}

}
} //endif
