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

	$plugin->load_release_objects();

	?>

	<div class="pkp-releases-form">
		<ul class="releases">

		<?php

		if ( !empty( $plugin->release_objects ) ) {
			foreach( $plugin->release_objects as $release ) :
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
				<?php esc_html_e( 'Add Release', 'pkppg-plugin-gallery' ); ?>
			</a>
		</fieldset>
		<?php endif; ?>
	</div>

	<?php
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

/**
 * Check if user owns any plugins
 *
 * @since 0.1
 */
if ( !function_exists( 'pkp_user_owns_plugins' ) ) {
function pkp_user_owns_plugins( $author = 0 ) {

	if ( empty( $author ) ) {
		$author = get_current_user_id();
	}

	$args = array(
		'post_type' => pkppgInit()->cpts->plugin_post_type,
		'post_status' => pkppgInit()->cpts->valid_post_statuses,
		'post_author' => (int) $author,
		'posts_per_page' => 1,
		'no_found_rows' => true,
	);

	$result = new WP_QUery( $args );

	$owns_plugins = $result->have_posts();

	wp_reset_query();

	return $owns_plugins;
}
} // endif;

/**
 * Retrieve a URL to a user's personal plugins page
 *
 * @since 0.1
 */
if ( !function_exists( 'pkp_get_user_plugins_url' ) ) {
function pkp_get_user_plugins_url() {

	$user = wp_get_current_user();

	return trailingslashit( home_url() ) . pkppgInit()->cpts->plugin_archive_slug . DIRECTORY_SEPARATOR . 'maintainer' . DIRECTORY_SEPARATOR . $user->user_login;
}
} // endif
