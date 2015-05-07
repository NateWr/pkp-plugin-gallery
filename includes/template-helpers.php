<?php defined( 'ABSPATH' ) || exit;

/**
 * Helper functions which generate template and UI markup
 *
 * These functions help output submission form components, admin
 * adding/editing components and plugin management screens.
 */

/**
 * Print a select dropdown of taxonomy terms
 *
 * @since 0.1
 */
if ( !function_exists( 'pkppg_print_taxonomy_select' ) ) {
function pkppg_print_taxonomy_select( $taxonomy, $selected = '', $args = array() ) {

	if ( !isset( $args['hide_empty'] ) ) {
		$args['hide_empty'] = false;
	}

	$terms = get_terms( $taxonomy, $args );

	if ( empty( $selected ) ) {
		global $post;
		if ( is_a( $post, 'WP_POST' ) ) {
			$post_terms = wp_get_post_terms( $post->ID, $taxonomy );
			$selected = !empty( $post_terms[0] ) && is_a( $post_terms[0], 'stdClass' ) ? $post_terms[0]->slug : '';
		}
	} elseif ( is_int( $selected ) ) {
		$term = get_term( $selected, $taxonomy );
		$selected = $term->slug;
	}

	?>

	<select name="tax_input[<?php echo esc_attr( $taxonomy ); ?>]">
	<?php foreach ( $terms as $term ) : ?>
		<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $selected, $term->slug ); ?>>
			<?php echo $term->name; ?>
		</option>
	<?php endforeach; ?>
	</select>

	<?php
}
} // endif

/**
 * Print the compatible applications select component
 *
 * @since 0.1
 */
if ( !function_exists( 'pkppg_print_application_select' ) ) {
function pkppg_print_application_select( $selected = array() ) {

	$terms = pkppgInit()->cpts->get_application_terms();

	?>

	<ul>

		<?php foreach( $terms as $term ) : ?>
		<li>
			<label>
				<?php echo $term->name; ?>
			</label>
			<?php if ( !empty( $term->children ) ) : ?>
			<ul>
				<?php foreach( $term->children as $child ) : ?>
				<li>
					<label>
						<input type="checkbox" name="tax_input[pkp_application][]" value="<?php echo $child->slug; ?>" <?php in_array( $child->slug, $selected ) ? ' checked="checked"' : '' ?>>
						<?php echo $child->name; ?>
					</label>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</li>
		<?php endforeach; ?>

	</ul>

	<?php
}
} // endif;

/**
 * Print the form fields for adding and editing release
 * versions
 *
 * @since 0.1
 */
if ( !function_exists( 'pkppg_print_releases_editor' ) ) {
function pkppg_print_releases_editor( $plugin_id = 0 ) {

	$plugin = new pkppgPlugin();

	if ( !$plugin->load_post( (int) $plugin_id ) ) {
		return;
	}

	$releases = pkp_get_plugin_releases( $plugin->ID );

	?>

	<div class="pkp-releases-form">
		<ul class="releases">

		<?php

		if ( !empty( $releases ) ) {
			foreach( $releases as $release ) :
			?>

			<li><?php $release->print_control_overview(); ?></li>

			<?php
			endforeach;
		}

		?>

		</ul>

		<?php if ( ( is_admin() && current_user_can( 'manage_options' ) ) || $plugin->maintainer == get_current_user_id() ) : ?>
		<fieldset class="pkp-release-form-buttons">
			<a href="#" class="button add" data-plugin="<?php echo $plugin->ID; ?>">
				<?php _e( 'Add Release', 'pkppg-plugin-gallery' ); ?>
			</a>
		</fieldset>
		<?php endif; ?>
	</div>

	<?php

}
} // endif;

/**
 * Return the add/edit form for the release modal
 *
 * @since 0.1
 */
if ( !function_exists( 'pkppg_get_release_form' ) ) {
function pkppg_get_release_form() {

	ob_start();

	?>

	<form class="pkppg-release-form">

		<?php pkppg_print_release_fields(); ?>

		<fieldset class="pkp-release-form-buttons">
			<a href="#" class="button button-primary save">
				<?php _e( 'Save Release', 'pkppg-plugin-gallery' ); ?>
			</a>
			<a href="#" class="button cancel">
				<?php _e( 'Cancel', 'pkppg-plugin-gallery' ); ?>
			</a>

			<span class="status">
				<span class="pkp-spinner"></span>
				<span class="pkp-success"></span>
				<span class="pkp-error"></span>
			</span>
		</fieldset>

	</form>

	<?php

	return ob_get_clean();

}
} // endif;

/**
 * Print a set of form fields for a single version release
 * of a plugin
 *
 * @todo use date picker for release date
 * @since 0.1
 */
if ( !function_exists( 'pkppg_print_release_fields' ) ) {
function pkppg_print_release_fields() {

	?>

	<fieldset class="pkp-release-fields">
		<input type="hidden" name="ID" id="pkp-release-id" value="">
		<div class="version">
			<label for="pkp-release-version">
				<?php _e( 'Version', 'pkp-plugin-gallery' ); ?>
			</label>
			<input type="text" name="version" id="pkp-release-version">
		</div>
		<div class="date">
			<label for="pkp-release-date">
				<?php _e( 'Release Date', 'pkp-plugin-gallery' ); ?>
			</label>
			<input type="text" name="date" id="pkp-release-date">
			<p class="description">
				<?php _e( 'Please enter the date this version was released.' ); ?>
			</p>
		</div>
		<div class="_package">
			<label for="pkp-release-package">
				<?php _e( 'Download URL', 'pkp-plugin-gallery' ); ?>
			</label>
			<input type="url" name="package" id="pkp-release-package" placeholder="http://">
			<p class="description">
				<?php _e( 'Please enter the URL to the download package.' ); ?>
			</p>
		</div>
		<div class="description">
			<label for="pkp-release-description">
				<?php _e( 'Description', 'pkp-plugin-gallery' ); ?>
			</label>
			<textarea name="description" id="pkp-release-description"></textarea>
			<p class="description">
				<?php _e( 'Please enter a brief description of changes in this version.' ); ?>
			</p>
		</div>
		<?php // @todo better cap check ?>
		<?php if ( is_admin() && current_user_can( 'manage_options' ) ) : ?>
		<div class="_md5">
			<label for="pkp-release-md5">
				<?php _e( 'MD5 Hash', 'pkp-plugin-gallery' ); ?>
			</label>
			<input type="text" name="md5" id="pkp-release-md5">
			<p class="description">
				<?php _e( 'Please enter the MD5 hash for the download package that has been vetted.' ); ?>
			</p>
		</div>
		<div class="certification">
			<label for="pkp-release-certification">
				<?php _e( 'Certification', 'pkp-plugin-gallery' ); ?>
			</label>
			<?php pkppg_print_taxonomy_select( 'pkp_certification' ); ?>
		</div>
		<?php endif; ?>
		<div class="applications">
			<h3>
				<?php _e( 'Compatible Applications', 'pkp-plugin-gallery' ); ?>
			</h3>
			<?php pkppg_print_application_select(); ?>
		</div>

	</fieldset>

	<?php
}
} // endif;

/**
 * Get all releases for a plugin
 *
 * @since 0.1
 */
if ( !function_exists( 'pkp_get_plugin_releases' ) ) {
function pkp_get_plugin_releases( $plugin_id, $args = array() ) {

	// Only show submissions  and updates in admin area
	// @todo better user cap
	$post_statuses = array( 'publish' );
	$with_updates = false;
	if ( is_admin() && current_user_can( 'manage_options' ) ) {
		$post_statuses[] = 'submission';
		$post_statuses[] = 'disable';
		$with_updates = true;
	}

	$defaults = array(
		'post_parent'    => $plugin_id,
		'post_type'      => pkppgInit()->cpts->plugin_release_post_type,
		'posts_per_page' => 1000,
		'post_status'    => $post_statuses,
		'with_updates'   => $with_updates,
		'orderby'        => 'title',
		'order'			 => 'DESC',
	);

	$args = array_merge( $defaults, $args );

	$query = new pkppgQuery( $args );

	return $query->get_results();
}
} // endif;

/**
 * Check if user is author of post
 *
 * @since 0.1
 */
if ( !function_exists( 'pkp_is_author' ) ) {
function pkp_is_author( $post, $author = 0 ) {

	if ( !is_a( $post, 'WP_POST' ) ) {
		$post = get_post( $post );
	}

	if ( empty( $author ) ) {
		$author = get_current_user_id();
	}

	return $post->post_author == $author;
}
} // endif
