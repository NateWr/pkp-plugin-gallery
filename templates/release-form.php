<?php
/**
 * Builds a form template for editing a plugin release. This template
 * is meant to include no values. The values are populated when the release is
 * loaded via an Ajax call.
 */
?>

<form class="pkppg-release-form">

    <fieldset class="pkp-release-fields">
		<input type="hidden" name="ID" id="pkp-release-id" value="">
		<div class="version">
			<label for="pkp-release-version">
				<?php esc_html_e( 'Version', 'pkp-plugin-gallery' ); ?>
			</label>
			<input type="text" name="version" id="pkp-release-version">
		</div>
		<div class="date">
			<label for="pkp-release-date">
				<?php esc_html_e( 'Release Date', 'pkp-plugin-gallery' ); ?>
			</label>
			<input type="text" name="date" id="pkp-release-date">
			<p class="description">
				<?php esc_html_e( 'Please enter the date this version was released.' ); ?>
			</p>
		</div>
		<div class="_package">
			<label for="pkp-release-package">
				<?php esc_html_e( 'Download Package', 'pkp-plugin-gallery' ); ?>
			</label>
			<input type="url" name="package" id="pkp-release-package" placeholder="http://github.com/you/your_plugin/archive/your_plugin.zip">
			<p class="description">
				<?php esc_html_e( 'Please enter the URL to the download package.' ); ?>
			</p>
		</div>
		<div class="description">
			<label for="pkp-release-description">
				<?php esc_html_e( 'Description', 'pkp-plugin-gallery' ); ?>
			</label>
			<textarea name="description" id="pkp-release-description"></textarea>
			<p class="description">
				<?php esc_html_e( 'Please enter a brief description of changes in this version.' ); ?>
			</p>
		</div>
		<?php // @todo better cap check ?>
		<?php if ( is_admin() && current_user_can( 'manage_options' ) ) : ?>
		<div class="_md5">
			<label for="pkp-release-md5">
				<?php esc_html_e( 'MD5 Hash', 'pkp-plugin-gallery' ); ?>
			</label>
			<input type="text" name="md5" id="pkp-release-md5">
			<p class="description">
				<?php esc_html_e( 'Please enter the MD5 hash for the download package that has been vetted.' ); ?>
			</p>
		</div>
		<div class="certification">
			<label for="pkp-release-certification">
				<?php esc_html_e( 'Certification', 'pkp-plugin-gallery' ); ?>
			</label>
			<?php pkppg_print_taxonomy_select( 'pkp_certification' ); ?>
		</div>
		<?php endif; ?>
		<div class="applications">
			<h3>
				<?php esc_html_e( 'Compatible Applications', 'pkp-plugin-gallery' ); ?>
			</h3>
			<?php pkppg_print_application_select(); ?>
		</div>
	</fieldset>

    <fieldset class="pkp-release-form-buttons">
        <a href="#" class="button button-primary save">
            <?php esc_html_e( 'Save Release', 'pkppg-plugin-gallery' ); ?>
        </a>
        <a href="#" class="button cancel">
            <?php esc_html_e( 'Cancel', 'pkppg-plugin-gallery' ); ?>
        </a>

        <span class="status">
            <span class="pkp-spinner"></span>
            <span class="pkp-success"></span>
            <span class="pkp-error"></span>
        </span>
    </fieldset>

</form>
