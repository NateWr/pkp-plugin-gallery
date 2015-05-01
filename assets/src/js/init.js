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

    // Utility functions
    pkppg.utils = pkppg.utils || {};

    /**
     * Replace <br> tags with a new line
     *
     * @since 0.1
     */
    pkppg.utils.strip_br = function( str ) {
        return str.replace(/<br\s*\/?>/mg,'');
    };

});
