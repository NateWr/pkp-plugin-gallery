<?php
/**
 * Builds the publish metabox which appears on the admin interface to edit
 * `pkp_plugin` posts. Markup is designed to blend with existing WP admin
 * design.
 */
?>

<div class="submitbox">
    <div id="minor-publishing">
        <div id="minor-publishing-actions">

            <?php if ( $post->post_status == 'publish' ) : ?>
            <a href="#" id="disable-post" class="disable button" data-id="<?php echo (int) $post->ID; ?>">
                <?php esc_html_e( 'Disable', 'pkp-plugin-gallery' ); ?>
            </a>
            <?php else : ?>
            <div id="save-action">
                <input type="submit" name="save" id="save-post" value="<?php echo esc_attr( __( 'Save for Later', 'pkp-plugin-gallery' ) ); ?>" class="button">
            </div>
            <?php endif; ?>


            <div class="clear"></div>
        </div>
        <div id="misc-publishing-actions">

            <?php if ( $post->post_status == 'update' ) : ?>
            <div class="misc-pub-section">
                <p>
                    <?php
                    printf(
                        __( 'This plugin is an <em>update</em> to an <a href="%s">existing plugin</a>.', 'pkp-plugin-gallery' ),
                        esc_url( $parent_url )
                    );
                    ?>
                </p>
            </div>

            <?php else : ?>

                <?php if ( $revisions_count && $revisions_to_keep ) : ?>
                <div class="misc-pub-section misc-pub-revisions">
                    <?php printf( __( 'Revisions: %s' ), '<b>' . number_format_i18n( $revisions_count ) . '</b>' ); ?>
                    <a class="hide-if-no-js" href="<?php echo esc_url( get_edit_post_link( $revision_id ) ); ?>">
                        <span aria-hidden="true"><?php _ex( 'Browse', 'revisions', 'pkp-plugin-gallery' ); ?></span>
                        <span class="screen-reader-text"><?php _e( 'Browse revisions', 'pkp-plugin-gallery' ); ?></span>
                    </a>
                </div>
                <?php endif; ?>

            <?php endif; ?>

            <?php if ( !empty( $updates ) ) : ?>
    		<div class="misc-pub-section misc-pub-pkp-updates">
    			<span class="dashicons dashicons-update"></span>
    			<?php printf( wp_kses( 'Updates: <strong>%d</strong>', 'pkp-plugin-gallery' ), count( $updates ) ); ?>
    			<div class="pkp-updates">
    				<?php foreach( $updates as $update ) : ?>
    				<div class="pkp-update">
    					<a href="<?php echo esc_url( add_query_arg( 'post', $update->ID, $edit_url ) ); ?>"><?php echo esc_html( $update->name ); ?></a>
    					<span class="date">
    						<?php echo $update->post_modified; ?>
    					</span>
    				</div>
    				<?php endforeach; ?>
    			</div>
    		</div>
        <?php endif; ?>

        </div>
        <div class="clear"></div>
    </div>
    <div id="major-publishing-actions">
        <div id="delete-action">

            <?php if ( current_user_can( 'delete_post', $post->ID ) ) : ?>
            <a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>">
                <?php echo $delete_text; ?>
            </a>
            <?php endif; ?>

        </div>
        <div id="publishing-action">
            <span class="spinner"></span>

            <?php if ( $post->post_status == 'update' ) : ?>
            <a href="#" id="compare-changes" class="merge button-primary" data-id="<?php echo (int) $post->ID; ?>">
                <?php esc_html_e( 'Compare Changes', 'pkp-plugin-gallery' ); ?>
            </a>

            <?php elseif ( $post->post_status == 'disable' ) : ?>
            <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Enable', 'pkp-plugin-gallery' ); ?>" />
            <?php submit_button( __( 'Enable' ), 'primary button-large', 'publish', false ); ?>
            </a>

            <?php else : ?>
            <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish', 'pkp-plugin-gallery' ); ?>" />
            <?php submit_button( __( 'Publish' ), 'primary button-large', 'publish', false ); ?>
            <?php endif; ?>
        </div>
        <div class="clear"></div>
    </div>
</div>
