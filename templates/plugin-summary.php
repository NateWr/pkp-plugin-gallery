<?php
/**
 * Builds a public view template for a plugin summary. This template should be
 * used in lists and other places where the full plugin details are not
 * required. Ite expects to be able to find the following variables.
 *
 * @var $plugin pkppgPlugin Object representing a plugin.
 */
?>

<div class"plugin">
    <div class="title">
        <?php echo $plugin->name; ?>
    </div>
    <div class="actions">
        <a href="<?php echo get_the_permalink( $plugin->ID ); ?>">
            <?php esc_html_e( 'View', 'pkp-plugin-gallery' ); ?>
        </a>
        <?php if ( pkp_is_author( $plugin->ID ) ) : ?>
        <a href="<?php echo esc_url( get_the_permalink( $plugin->ID ) . 'edit' ); ?>">
            <?php esc_html_e( 'Edit', 'pkp-plugin-gallery' ); ?>
        </a>
        <?php endif; ?>
    </div>
</div>
