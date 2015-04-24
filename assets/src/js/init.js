/**
 * JavaScript to initialize the `pkppg` component
 */

var pkppg = pkppg || {};

jQuery( document ).ready( function( $ ) {

    // Load data
    pkppg.data = pkppg.data || pkppg_data;

    // Global cache
    pkppg.cache = pkppg.cache || {};
    pkppg.cache.body = pkppg.cache.body || $( 'body' );

});
