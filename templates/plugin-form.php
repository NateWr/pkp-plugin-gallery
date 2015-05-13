<?php
/**
 * Builds a form template for editing a plugin. This template
 * expects to be able to find the following variables.
 *
 * @var $plugin pkppgPlugin Object representing a plugin.
 */

 if ( empty( $plugin->ID ) ) {
     $title = __( 'Submit Plugin', 'pkp-plugin-gallery' );
 } else {
     $title = __( 'Edit Plugin', 'pkp-plugin-gallery' );
}
?>

<div class="pkp-submit">

    <h1><?php echo esc_html( $title ); ?></h1>

    <form method="POST">
        <?php wp_nonce_field( 'pkp-plugin-submission', 'pkp-plugin-nonce' ); ?>

		<fieldset class="plugin">
			<legend><?php esc_html_e( 'Plugin Details', 'pkp-plugin-gallery' ); ?></legend>
			<div class="name">
				<label for="pkp-plugin-name">
					<?php esc_html_e( 'Name', 'pkp-plugin-gallery' ); ?>
				</label>
				<input type="text" name="pkp-plugin-name" value="<?php echo esc_attr( $plugin->name ); ?>">
			</div>
			<div class="category">
				<label for="pkp-plugin-category">
					<?php esc_html_e( 'Category', 'pkp-plugin-gallery' ); ?>
				</label>
				<?php pkppg_print_taxonomy_select( 'pkp_category', $plugin->category ); ?>
			</div>
			<div class="summary">
				<label for="pkp-plugin-summary">
					<?php esc_html_e( 'Summary', 'pkp-plugin-gallery' ); ?>
				</label>
				<textarea name="pkp-plugin-summary"><?php echo $plugin->summary; ?></textarea>
			</div>
			<div class="description">
				<label for="pkp-plugin-description">
					<?php esc_html_e( 'Description', 'pkp-plugin-gallery' ); ?>
				</label>
				<textarea name="pkp-plugin-description"><?php echo $plugin->description; ?></textarea>
			</div>
			<div class="homepage">
				<label for="pkp-plugin-homepage">
					<?php esc_html_e( 'Project URL', 'pkp-plugin-gallery' ); ?>
				</label>
				<input type="url" name="pkp-plugin-homepage" value="<?php echo esc_attr( $plugin->homepage ); ?>" placeholder="http://github.com/you/your_plugin/">
			</div>
			<div class="installation">
				<label for="pkp-plugin-installation">
					<?php esc_html_e( 'Installation Instructions', 'pkp-plugin-gallery' ); ?>
				</label>
				<textarea name="pkp-plugin-installation"><?php echo $plugin->installation; ?></textarea>
			</div>
		</fieldset>

        <fieldset class="buttons">
            <button type="submit" class="save">
                <?php esc_html_e( 'Save', 'pkppg-plugin-gallery' ); ?>
            </button>
        </fieldset>
    </form>

</div>
