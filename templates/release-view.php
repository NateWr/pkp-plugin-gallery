<?php
/**
 * Builds a public view template for a plugin release. This template expects to
 * be called in a theme's template files and expects to be able to find the
 * following variables.
 *
 * @var $release pkppgPluginRelease Object representing a release.
 */
?>

<div class="release <?php echo esc_attr( $release->post_status ); ?>" data-id="<?php echo (int) $release->ID; ?>">
    <input type="hidden" name="pkp-plugin-releases[]" value="<?php echo (int) $release->ID; ?>">

    <div class="title">

        <span class="version">
            <?php echo $release->version; ?>
        </span>
        &mdash;
        <span class="date">
            <?php echo $release->release_date; ?>
        </span>
    </div>

    <?php if ( $release->post_status == 'submission' ) : ?>
    <div class="notice">
        <span class="dashicons dashicons-download"></span>
        <span class="status-notice">
            <?php esc_html_e( 'This submission has not yet been approved. ', 'pkp-plugin-gallery' ); ?>
        </span>
    </div>
    <?php endif; ?>

    <?php if ( $release->post_status == 'publish' ) : ?>
    <div class="details">
        <?php echo $release->description; ?>
    </div>
    <?php endif; ?>

    <ul class="actions">
        <li>
            <a href="<?php echo esc_url( $release->package ); ?>" class="download">
                <?php esc_html_e( 'Download', 'pkp-plugin-gallery' ); ?>
            </a>
        </li>
        <?php if ( pkp_is_author( $release->ID ) ) : ?>
        <li>
            <span class="pkp-spinner"></span>
            <a href="#" class="edit">
                <?php esc_html_e( 'Edit', 'pkp-plugin-gallery' ); ?>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <ul class="terms">
        <?php if ( !empty( $release->certification ) ) : ?>
        <li class="pkp_certification">
            <span class="label">
                <?php esc_html_e( 'Certification: ', 'pkp-plugin-gallery' ); ?>
            </span>
            <span class="value">
                <?php echo $release->get_term_name( 'pkp_certification' ); ?>
            </span>
        </li>
        <?php endif; ?>
        <?php if ( !empty( $release->applications ) ) : ?>
        <li class="pkp_application">
            <span class="label">
                <?php esc_html_e( 'Compatible With: ', 'pkp-plugin-gallery' ); ?>
            </span>
            <span class="value">
                <?php echo get_the_term_list( $release->ID, 'pkp_application', '', '', '' ); ?>
            </span>
        </li>
        <?php endif; ?>
    </ul>

    <?php // @todo better user cap ?>
    <?php if ( !empty( $release->updates ) && ( ( is_admin() && current_user_can( 'manage_options' ) ) || pkp_is_author( $release->ID ) ) ) : ?>
    <ul class="updates">
        <?php foreach( $release->updates as $update ) : ?>
        <li>
            <?php echo $update->get_control_overview(); ?>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>
