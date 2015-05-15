<?php
/**
 * Builds a public view template for a plugin. This template expects to be able
 * to find the following variables.
 *
 * @var $plugin pkppgPlugin Object representing a plugin. Releases will only be
 *      show if $plugin->load_release_objects() was called before loading this
 *      template.
 */
?>

<div class"plugin">
    <h1><?php echo $plugin->name; ?></h1>
    <div class="category">
        <?php echo $plugin->get_term_name( 'pkp_category', ', ', 'id' ); ?>
    </div>

    <div class="details">
        <?php echo esc_html( $plugin->description ); ?>

        <?php if ( !empty( $plugin->installation ) ) : ?>
        <h3><?php esc_html_e( 'Installation Instructions', 'pkp-bowtie-child' ); ?><?h3>
        <p><?php echo $plugin->installation; ?></p>
        <?php endif; ?>
    </div>

    <ul class="actions">
        <?php if ( !empty( $plugin->homepage ) ) : ?>
        <li class="homepage">
            <a href="<?php esc_url( $plugin->homepage ); ?>">
                <?php esc_html_e( 'Website', 'pkp-bowtie-child' ); ?>
            </a>
        </li>
        <?php endif; ?>
        <?php if ( pkp_is_author( $plugin->ID ) ) : ?>
        <li>
            <a href="<?php echo esc_url( get_permalink( $plugin->ID ) . 'edit' ); ?>" class="edit">
                <?php esc_html_e( 'Edit', 'pkp-plugin-gallery' ); ?>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<?php if ( !empty( $plugin->release_objects ) ) : ?>
<div class="pkp-releases-form">
    <h2><?php esc_html_e( 'Releases', 'pkp-bowtie-child' ); ?></h2>

    <ul class="releases">
    <?php foreach( $plugin->release_objects as $release ) : ?>
        <li>
        <?php echo $release->print_view(); ?>
        </li>
    <?php endforeach; ?>
    </ul>
</div>

<?php else : ?>
<div class="notice">
    <p><?php esc_html_e( 'This plugin has not been released yet.', 'pkp-bowtie-child' ); ?></p>
</div>
<?php endif; ?>
