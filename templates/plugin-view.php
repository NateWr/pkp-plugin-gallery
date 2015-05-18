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

<div class="plugin view">
    <h1><?php echo $plugin->name; ?></h1>
    <div class="category">
        <?php echo $plugin->get_term_name( 'pkp_category', ', ', 'id' ); ?>
    </div>

    <div class="details">
        <?php echo esc_html( $plugin->description ); ?>

        <?php if ( !empty( $plugin->installation ) ) : ?>
        <h3><?php esc_html_e( 'Installation Instructions', 'pkp-plugin-gallery' ); ?></h3>
        <p><?php echo $plugin->installation; ?></p>
        <?php endif; ?>
    </div>

    <ul class="actions">
        <?php if ( !empty( $plugin->homepage ) ) : ?>
        <li class="homepage">
            <a href="<?php esc_url( $plugin->homepage ); ?>">
                <?php esc_html_e( 'Project Website', 'pkp-plugin-gallery' ); ?>
            </a>
        </li>
        <?php endif; ?>
        <?php if ( pkp_is_author( $plugin->ID ) ) : ?>
        <li>
            <a href="<?php echo esc_url( get_permalink( $plugin->ID ) . 'edit' ); ?>" class="edit">
                <?php esc_html_e( 'Submit Edit', 'pkp-plugin-gallery' ); ?>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<div class="pkp-releases-form">
    <h2><?php esc_html_e( 'Releases', 'pkp-plugin-gallery' ); ?></h2>

    <ul class="releases">
    <?php if ( !empty( $plugin->release_objects ) ) : foreach( $plugin->release_objects as $release ) : ?>
        <li>
        <?php echo $release->print_view(); ?>
        </li>
    <?php endforeach; endif; ?>
    </ul>

    <?php if ( current_user_can( 'manage_options' ) || pkp_is_author( $plugin->ID ) ) : ?>
    <fieldset class="pkp-release-form-buttons">
        <a href="#" class="button add" data-plugin="<?php echo $plugin->ID; ?>">
            <?php esc_html_e( 'Add Release', 'pkppg-plugin-gallery' ); ?>
        </a>
    </fieldset>
    <?php endif; ?>
</div>
