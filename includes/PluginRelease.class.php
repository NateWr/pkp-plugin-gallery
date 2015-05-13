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
	 * ID of a plugin this release is attached to OR another release which this
	 * release is an update for.
	 *
	 * @since 0.1
	 */
	public $post_parent;

	/**
	 * Author of the plugin
	 *
	 * @var int
	 * @since 0.1
	 */
	public $author;

	/**
	 * Applications and versions that this release is
	 * expected to be compatible with.
	 *
	 * Note: this array may include term slugs or IDs. Most WP functions which
	 * store or interact with the database can accept a mixed array, but beware
	 * when printing. Generally speaking, term slugs are used. But in cases
	 * where a parent term is assigned, it will usually just add the ID.
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

		// Update related data when a release is deleted or updated
		add_action( 'delete_post', array( $this, 'fire_on_delete' ) );
		add_action( 'wp_insert_post', array( $this, 'fire_on_update' ), 10, 3 );
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
		$this->author = $post->post_author;
		$this->applications = $this->get_applications();
		$this->certification = $this->get_certification();
		$this->post_status = $post->post_status;
		$this->post_parent = $post->post_parent;
		$this->post_modified = $post->post_modified;
	}

	/**
	 * Parse and sanitize incoming data
	 *
	 * @since 0.1
	 */
	public function parse_params( $params ) {

		if ( !empty( $params['plugin'] ) ) {
			$this->post_parent = (int) $params['plugin'];
		}

		if ( !empty( $params['release'] ) ) {
			$this->post_parent = (int) $params['release'];
		}

		if ( !empty( $params['post_parent'] ) ) {
			$this->post_parent = (int) $params['post_parent'];
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
			$this->description = wp_kses_post( nl2br( $params['description'] ) );
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
			$this->certification = sanitize_text_field( $params['certification'] );
		}

		if ( isset( $params['applications'] ) ) {
			$this->applications = array_map( 'sanitize_text_field', $params['applications'] );
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

		if ( empty( $this->post_parent ) ) {
			$this->add_error(
				'post_parent',
				$this->post_parent,
				__( 'This release is not attached to a plugin or another release.', 'pkp-plugin-gallery' )
			);
		}

		// Post Status
		if ( empty( $this->post_status ) ) {
			$this->post_status = empty( $this->ID ) ? 'submission' : 'update';
		} elseif ( !pkppgInit()->cpts->is_valid_status( $this->post_status ) ) {
			$this->add_error(
				'post_status',
				$this->post_status,
				__( 'Please select a valid post status.', 'pkp-plugin-gallery' )
			);
		} elseif( $this->post_status == 'publish' ) {

			if ( !current_user_can( 'manage_options' ) && get_current_user_id() == $this->author ) {
				$this->post_status = 'update';
				$this->post_parent = $this->ID;
				$this->ID = null;

			// @todo better cap check
			} elseif ( !current_user_can( 'manage_options' ) ) {
				$this->add_error(
					'post_status',
					$this->post_status,
					__( 'You do not have permission to edit this published release.', 'pkp-plugin-gallery' )
				);
			}
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

		$args = array(
			'post_type'    => pkppgInit()->cpts->plugin_release_post_type,
			'post_title'   => $this->version,
			'post_content' => $this->description,
			'post_date'    => $this->release_date,
			'post_parent'  => $this->post_parent,
			'post_status'  => $this->post_status,
			'post_author'  => $this->author,
		);

		if ( !empty( $this->ID ) ) {
			if ( $this->post_status == 'update' && $this->is_update_new() ) {
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

		// Call this directly because we have changed the terms since firing
		// `wp_insert_post`
		$this->update_parent_terms();
	}

	/**
	 * Fire maintenance functions when post deleted
	 *
	 * @since 0.1
	 */
	public function fire_on_delete( $id ) {

		if ( empty( $this->ID ) || $id !== $this->ID ) {
			return;
		}

		$this->update_parent_terms();

		// Delete the transient where update counts are stored
		pkppgInit()->cpts->delete_update_transient();
	}

	/**
	 * Fire maintenance functions when a post is inserted or updated
	 *
	 * This is
	 * @since 0.1
	 */
	public function fire_on_update( $id, $post, $is_update = false ) {

		if ( empty( $this->ID ) || $id !== $this->ID ) {
			return;
		}

		$this->update_parent_terms();
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
				// It's ok to mix term slugs and term IDs here
				$this->applications[] = (int) $term->parent;
			}
		}

		$this->applications = array_unique( $this->applications );
	}

	/**
	 * Update parent terms to ensure that any terms assigned to this release
	 * are added/removed from the parent plugin as needed
	 *
	 * @since 0.1
	 */
	public function update_parent_terms() {

		if ( empty( $this->post_parent ) ) {
			return;
		}

		$parent = get_post( $this->post_parent );

		if ( empty( $parent ) || is_wp_error( $parent ) || $parent->post_type !== pkppgInit()->cpts->plugin_post_type ) {
			return;
		}

		$plugin = new pkppgPlugin();
		$plugin->load_post( $parent );
		$plugin->adopt_child_terms();
	}

	/**
	 * Get HTML for an overview of this release for the release
	 * and editing controls
	 *
	 * @since 0.1
	 */
	public function get_control_overview() {

		$release = $this;
		$template = pkppgInit()->get_template_path( 'release-control.php' );
		if ( !empty( $template ) ) {
			ob_start();
			include( $template );
			return ob_get_clean();
		}

		return '';
	}

	/**
	 * Print an overview of this release for the release
	 * and editing controls
	 *
	 * @since 0.1
	 */
	public function print_control_overview() {
		echo $this->get_control_overview();
	}

	/**
	 * Get HTML for a view of this release. Intended for user-facing views.
	 *
	 * @since 0.1
	 */
	public function get_view() {

		$release = $this;
		$template = pkppgInit()->get_template_path( 'release-view.php' );
		if ( !empty( $template ) ) {
			ob_start();
			include( $template );
			return ob_get_clean();
		}

		return '';
	}

	/**
	 * Print a view of this release. Intended for user-facing views.
	 *
	 * @since 0.1
	 */
	public function print_view() {
		echo $this->get_view();
	}

	/**
	 * Get HTML for an editing form for this release. Does not require any data
	 * so can be called from an empty release object
	 *
	 * @since 0.1
	 */
	public function get_form() {

		$release = $this;
		$template = pkppgInit()->get_template_path( 'release-form.php' );
		if ( !empty( $template ) ) {
			ob_start();
			include( $template );
			return ob_get_clean();
		}

		return '';
	}

	/**
	 * Print an editing form for this release. Does not require any data so
	 * can be called from an empty release object
	 *
	 * @since 0.1
	 */
	public function print_form() {
		echo $this->get_view();
	}

	/**
	 * Generate a diff of changes against an updated object
	 *
	 * This will generate a series of diff tables which indicate changes between
	 * `$this` and an updated object which is passed to this method.
	 *
	 * @since 0.1
	 */
	public function get_diff( $update ) {

		$strings = array(
			'version',
			'release_date',
			'description',
			'package',
			'md5',
		);

		ob_start();

		foreach( $strings as $string ) :
			$diff = wp_text_diff( $this->{$string}, $update->{$string} );

			if ( empty( $diff ) ) {
				continue;
			}
		?>

		<div class="param">
			<h4><?php echo ucfirst( $string ); ?></h4>
			<?php echo $diff; ?>
		</div>

		<?php
		endforeach;

		$taxonomies = array(
			'pkp_application' => __( 'Compatible Applications', 'pkp-plugin-gallery' ),
			'pkp_certification' => __( 'Certification', 'pkp-plugin-gallery' ),
		);

		foreach( $taxonomies as $taxonomy => $label ) {
			$current_terms = wp_get_object_terms( $this->ID, $taxonomy, array( 'fields' => 'names' ) );
			$update_terms = wp_get_object_terms( $update->ID, $taxonomy, array( 'fields' => 'names' ) );

			$diff = wp_text_diff( join( ', ', $current_terms ), join( ', ', $update_terms ) );

			if ( !empty( $diff ) ) :
			?>

			<div class="param">
				<h4><?php echo esc_html( $label ); ?></h4>
				<?php echo $diff; ?>
			</div>

			<?php
			endif;
		}

		return ob_get_clean();
	}

}
} // endif
