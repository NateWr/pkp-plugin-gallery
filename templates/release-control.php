<?php
/**
 * Builds a control template for a plugin release. This is used in the admin to
 * load editing panels, view diffs, enable/disable releases, or delete them.
 * The template expects to be able to find the following variables.
 *
 * @var $release pkppgPluginRelease Object representing a release.
 */
?>

<div class="release <?php echo esc_attr( $release->post_status ); ?>" data-id="<?php echo (int) $release->ID; ?>">
    <input type="hidden" name="pkp-plugin-releases[]" value="<?php echo (int) $release->ID; ?>">
    <div class="title">

        <?php if ( $release->post_status == 'update' ) : ?>
        <span class="dashicons dashicons-update"></span>
        <span class="status-notice">
            <?php esc_html_e( 'Update: ', 'pkp-plugin-gallery' ); ?>
        </span>

        <?php elseif ( $release->post_status == 'submission' ) : ?>
        <span class="dashicons dashicons-download"></span>
        <span class="status-notice">
            <?php esc_html_e( 'Submission: ', 'pkp-plugin-gallery' ); ?>
        </span>

        <?php elseif ( $release->post_status == 'disable' ) : ?>
        <span class="dashicons dashicons-shield-alt"></span>
        <span class="status-notice">
            <?php esc_html_e( 'Disabled: ', 'pkp-plugin-gallery' ); ?>
        </span>
        <?php endif; ?>

        <?php if ( $release->post_status != 'update' ) : ?>
        <span class="version">
            <?php echo $release->version; ?>
        </span>
        &mdash;
        <span class="date">
            <?php echo $release->release_date; ?>
        </span>

        <?php else : ?>
        <span class="date">
            <?php echo mysql2date( get_option( 'date_format' ), $release->post_modified ); ?>
        </span>
        <?php endif; ?>
    </div>

    <?php if ( $release->post_status == 'publish' ) : ?>
    <div class="details">
        <?php echo $release->description; ?>
    </div>
    <?php endif; ?>

    <?php if ( current_user_can( 'manage_options' ) || pkp_is_author( $release->ID ) ) : ?>
    <div class="actions">
        <span class="pkp-spinner"></span>
        <a href="#" class="edit">
            <?php esc_html_e( 'Edit', 'pkp-plugin-gallery' ); ?>
        </a>

        <?php // @todo better user_cap ?>
        <?php if ( current_user_can( 'manage_options' ) ) : ?>

            <?php if ( $release->post_status == 'submission' ) : ?>
            <a href="#" class="approve">
                <?php esc_html_e( 'Approve', 'pkp-plugin-gallery' ); ?>
            </a>

            <?php elseif ( $release->post_status == 'publish' ) : ?>
            <a href="#" class="disable">
                <?php esc_html_e( 'Disable', 'pkp-plugin-gallery' ); ?>
            </a>

            <?php elseif ( $release->post_status == 'update' ) : ?>
            <a href="#" class="compare">
                <?php esc_html_e( 'Compare Changes', 'pkp-plugin-gallery' ); ?>
            </a>

            <?php elseif ( $release->post_status == 'disable' ) : ?>
            <a href="#" class="enable">
                <?php esc_html_e( 'Enable', 'pkp-plugin-gallery' ); ?>
            </a>
            <?php endif; ?>

            <a href="#" class="delete">
                <?php esc_html_e( 'Delete', 'pkp-plugin-gallery' ); ?>
            </a>
        <?php endif; ?>

    </div>
    <?php endif; ?>

    <?php // @todo better user cap ?>
    <?php if ( !empty( $release->updates ) && is_admin() && current_user_can( 'manage_options' ) ) : ?>
    <ul class="updates">
        <?php foreach( $release->updates as $update ) : ?>
        <li>
            <?php echo $update->get_control_overview(); ?>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>
