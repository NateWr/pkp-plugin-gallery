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
