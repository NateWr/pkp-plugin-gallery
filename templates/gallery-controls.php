<?php
/**
 * Builds a template part which displays the filters and search controls for
 * the plugin gallery, as well as the user controls to submit a plugin or view
 * their own plugins.
 */
global $wp;
$applications = get_terms( 'pkp_application', array( 'parent' => 0 ) );
$term_title = single_term_title( '', false );
?>

<div id="pkppg-controls" class="pkppg-controls clearfix">
    <ul class="filters">
        <li class="all<?php echo is_tax() ? '' : ' current'; ?>">
            <a href="<?php trailingslashit( home_url( pkppgInit()->cpts->plugin_archive_slug ) ); ?>">
                <?php esc_html_e( 'All', 'pkp-plugin-gallery' ); ?>
            </a>
        </li>
        <?php foreach( $applications as $application ) : $current = $term_title == $application->name ? ' current' : ''; ?>
        <li class="<?php echo esc_attr( $application->slug . $current ); ?>">
            <a href="<?php echo get_term_link( $application, 'pkp_application' ); ?>">
                <?php echo esc_html( $application->name ); ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>

    <form method="POST" class="search">
        <input type="text" name="pkp-search" placeholder="<?php esc_html_e( 'Search', 'pkp-plugin-gallery' ); ?>">
        <input class="screen-reader-text" type="submit" value="<?php esc_attr_e( 'Search', 'pkp-plugin-gallery' ); ?>">
    </form>
</div>

<ul class="pkppg-user-controls">
    <?php if ( pkp_user_owns_plugins() ) : ?>
    <li>
        <a href="<?php echo esc_url( pkp_get_user_plugins_url() ); ?>">
            <?php esc_html_e( 'My Plugins', 'pkp-plugin-gallery' ); ?>
        </a>
    </li>
    <?php endif; ?>

    <li>
        <a href="<?php echo esc_url( trailingslashit( home_url( $wp->request ) ) . 'submit' ); ?>">
            <?php esc_html_e( 'Submit Plugin', 'pkp-plugin-gallery' ); ?>
        </a>
    </li>
</ul>
