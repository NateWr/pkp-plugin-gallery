<?php
/**
 * Builds a template part which displays the filters and search controls for
 * the plugin gallery, as well as the user controls to submit a plugin or view
 * their own plugins.
 */
global $wp;
$applications = get_terms( 'pkp_application', array( 'parent' => 0 ) );
?>

<div id="pkppg-controls" class="pkppg-controls clearfix">
    <ul class="filters">
        <li class="all">
            <?php esc_html_e( 'All', 'pkp-plugin-gallery' ); ?>
        </li>
        <?php foreach( $applications as $application ) : ?>
        <li class="<?php esc_attr( $application->slug ); ?>">
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
            <?php esc_html_e( 'My Plugins', 'pkp-bowtie-child' ); ?>
        </a>
    </li>
    <?php endif; ?>

    <li>
        <a href="<?php echo esc_url( trailingslashit( home_url( $wp->request ) ) . 'submit' ); ?>">
            <?php esc_html_e( 'Submit Plugin', 'pkp-bowtie-child' ); ?>
        </a>
    </li>
</ul>
