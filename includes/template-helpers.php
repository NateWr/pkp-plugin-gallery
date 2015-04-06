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

	global $post;
	$post_terms = wp_get_post_terms( $post->ID, $taxonomy );
	$post_term_id = is_a( $post_terms[0], 'stdClass' ) ? $post_terms[0]->term_id : '';

	?>

	<select name="tax_input[<?php echo esc_attr( $taxonomy ); ?>]">
	<?php foreach ( $terms as $term ) : ?>
		<option name="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( $post_term_id, $term->term_id ); ?>>
			<?php echo $term->name; ?>
		</option>
	<?php endforeach; ?>
	</select>

	<?php
}
} // endif

/**
 * Print the form fields for adding and editing release
 * versions
 *
 * @since 0.1
 */
if ( !function_exists( 'pkppg_print_releases_editor' ) ) {
function pkppg_print_releases_editor( $plugin_id ) {

	$plugin_id = (int) $plugin_id;

	?>

	<div class="pkppg-releases-form">
		<h3>
			<?php _e( 'Release Versions', 'pkppg-plugin-gallery' ); ?>
		</h3>

	<?php

	$query = new WP_Query(
		array(
			'post_parent' => $plugin_id,
			'post_type'   => pkppgInit()->cpts->plugin_release_post_type,
			'limit'       => 1000,
		)
	);

	if ( !empty( $plugin_id ) && $query->have_posts() ) {
		$i = 0;
		while( $query->have_posts() ) {
			$query->the_post() ;

			$release = new pkppgPluginRelease();
			$release->load_post( $post );

			pkppg_print_release_field_template( $i, $release );
			$i++;
		}
	}

	?>

		<fieldset class="pkp-release-form-buttons">
			<a href="#" class="button add">
				<?php _e( 'Add Release', 'pkppg-plugin-gallery' ); ?>
			</a>
			<a href="#" class="cancel">
				<?php _e( 'Cancel', 'pkppg-plugin-gallery' ); ?>
			</a>
		</fieldset>

	</div>

	<?php

}
} // endif;

/**
 * Print a set of form fields for a single version release
 * of a plugin
 *
 * @todo use date picker for release date
 * @since 0.1
 */
if ( !function_exists( 'pkppg_print_release_field_template' ) ) {
function pkppg_print_release_field_template( $i, $release = null ) {

	$i = (int) $i;

	?>

	<fieldset class="pkppg-release-form">
		<div class="version">
			<label for="pkp-release-version-<?php echo $i; ?>">
				<?php _e( 'Version', 'pkp-plugin-gallery' ); ?>
			</label>
			<input type="text" name="pkp-release[<?php echo $i; ?>][version]" id="pkp-release-version-<?php echo $i; ?>">
		</div>
		<div class="date">
			<label for="pkp-release-date-<?php echo $i; ?>">
				<?php _e( 'Release Date', 'pkp-plugin-gallery' ); ?>
			</label>
			<input type="text" name="pkp-release[<?php echo $i; ?>][date]" id="pkp-release-date-<?php echo $i; ?>">
			<p class="description">
				<?php _e( 'Please enter the date this version was released.' ); ?>
			</p>
		</div>
		<div class="_package">
			<label for="pkp-release-_package-<?php echo $i; ?>">
				<?php _e( 'Download URL', 'pkp-plugin-gallery' ); ?>
			</label>
			<input type="text" name="pkp-release[<?php echo $i; ?>][_package]" id="pkp-release-_package-<?php echo $i; ?>">
			<p class="description">
				<?php _e( 'Please enter the URL to the download package.' ); ?>
			</p>
		</div>
		<div class="description">
			<label for="pkp-release-description-<?php echo $i; ?>">
				<?php _e( 'Description', 'pkp-plugin-gallery' ); ?>
			</label>
			<textarea name="pkp-release[<?php echo $i; ?>][description]" id="pkp-release-description-<?php echo $i; ?>">
			</textarea>
			<p class="description">
				<?php _e( 'Please enter a brief description of changes in this version.' ); ?>
			</p>
		</div>
		<div class="_md5">
			<label for="pkp-release-_md5-<?php echo $i; ?>">
				<?php _e( 'MD5 Hash', 'pkp-plugin-gallery' ); ?>
			</label>
			<input type="text" name="pkp-release[<?php echo $i; ?>][_md5]" id="pkp-release-_md5-<?php echo $i; ?>">
			<p class="description">
				<?php _e( 'Enter the MD5 hash for the download package that has been vetted.' ); ?>
			</p>
		</div>
	</fieldset>

	<?php
}
}
