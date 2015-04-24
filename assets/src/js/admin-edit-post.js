/**
 * JavaScript helpers for the admin Edit Post interface for plugins
 */

var pkppg = pkppg || {};
pkppg.data = pkppg.data || pkppg_data;
pkppg.cache = pkppg.cache || {};

jQuery( document ).ready( function( $ ) {

    if ( !$( 'form#post' ).length ) {
        return;
    }

    pkppg.edit_post = {};

    pkppg.cache.edit_post_form = $( 'form#post' );
    pkppg.cache.edit_post_save = pkppg.cache.edit_post_form.find( '#save-post' );

    // Disable the lost-changes warning when saving for later
    // This turns off a WP core trigger event
    pkppg.cache.edit_post_save.click( function() {
        $(window).off( 'beforeunload.edit-post' );
    });

});
