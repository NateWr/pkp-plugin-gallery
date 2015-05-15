<?php
/**
 * Builds a public view template for a plugin summary. This template should be
 * used in lists and other places where the full plugin details are not
 * required. Ite expects to be able to find the following variables.
 *
 * @var $plugin pkppgPlugin Object representing a plugin.
 */
?>

<div class="plugin <?php echo esc_attr( $plugin->post_status ); ?>">
    <div class="title">

        <?php if ( $plugin->post_status == 'update' ) : ?>
        <span class="dashicons dashicons-update"></span>
        <span class="status-notice">
            <?php esc_html_e( 'Update: ', 'pkp-plugin-gallery' ); ?>
        </span>

        <?php elseif ( $plugin->post_status == 'submission' ) : ?>
        <span class="dashicons dashicons-download"></span>
        <span class="status-notice">
            <?php esc_html_e( 'Submission: ', 'pkp-plugin-gallery' ); ?>
        </span>

        <?php elseif ( $plugin->post_status == 'disable' ) : ?>
        <span class="dashicons dashicons-shield-alt"></span>
        <span class="status-notice">
            <?php esc_html_e( 'Disabled: ', 'pkp-plugin-gallery' ); ?>
        </span>
        <?php endif; ?>

        <?php echo $plugin->name; ?>
    </div>
    <div class="actions">
        <a href="<?php echo get_the_permalink( $plugin->ID ); ?>">
            <?php esc_html_e( 'View', 'pkp-plugin-gallery' ); ?>
        </a>
        <?php if ( pkp_is_author( $plugin->ID ) && $plugin->post_status !== 'update' ) : ?>
        <a href="<?php echo esc_url( get_the_permalink( $plugin->ID ) . 'edit' ); ?>">
            <?php esc_html_e( 'Edit', 'pkp-plugin-gallery' ); ?>
        </a>
        <?php endif; ?>
    </div>

    <?php if ( pkp_is_author( $plugin->ID ) && get_query_var( 'author_name' ) ) : $plugin->load_updates(); ?>
        <?php if ( !empty( $plugin->updates ) ) : ?>
            <ul class="updates">
                <?php foreach( $plugin->updates as $update ) : ?>
                <li>
                    <?php $update->print_summary_view(); ?>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>

</div>
