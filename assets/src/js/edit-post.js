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

    pkppg.edit_post = {

    	/**
    	 * Initialize the form events
    	 *
    	 * @since 0.1
    	 */
        init: function() {

            this.cache = {};
            this.cache.body = pkppg.cache.body || $( 'body' );
            this.cache.form = $( 'form#post' );
            this.cache.save = this.cache.form.find( '#save-post' );
            this.cache.compare = this.cache.form.find( '#compare-changes' );
            this.cache.diff_modal = $( '#pkppg-diff' );
            this.cache.diff_modal_close = this.cache.diff_modal.find( '.close' );
            this.cache.diff_modal_publish = this.cache.diff_modal.find( '.publish' );
            this.cache.diff_modal_diff = this.cache.diff_modal.find( '#pkp-plugin-diff' );

            // Disable the lost-changes warning when saving for later
            // This turns off a WP core event that is triggered on submit of the
            // form
            this.cache.save.click( function() {
                $(window).off( 'beforeunload.edit-post' );
            });

            // Open/close compare changes modal
            this.cache.compare.click( this.showDiffModal );
            this.cache.diff_modal_close.click( this.hideDiffModal );
            this.cache.diff_modal_publish.click( this.publishChanges );
			this.cache.diff_modal.click( function(e) { if ( $( e.target ).is( pkppg.edit_post.cache.diff_modal ) ) { pkppg.edit_post.hideDiffModal(); } } );
			$( document ).keyup( function(e) { if ( e.which == '27' ) { pkppg.edit_post.hideDiffModal(); } } );
        },

        /**
         * Show the modal for viewing and committing changes
         *
         * @since 0.1
         */
        showDiffModal: function(e) {

            if ( typeof e !== 'undefined' ) {
                e.stopPropagation();
                e.preventDefault();
            }

            pkppg.edit_post.ID = $( e.target ).data( 'id' );

            if ( !pkppg.edit_post.ID ) {
                return;
            }

            pkppg.edit_post.cache.body.addClass( 'pkppg-modal-is-visible' );
            pkppg.edit_post.cache.diff_modal.addClass( 'is-visible' );
        },

        /**
         * Hide the modal for viewing and committing changes
         *
         * @since 0.1
         */
         hideDiffModal: function(e) {

            if ( typeof e !== 'undefined' ) {
                e.stopPropagation();
                e.preventDefault();
            }

            pkppg.edit_post.cache.body.removeClass( 'pkppg-modal-is-visible' );
            pkppg.edit_post.cache.diff_modal.removeClass( 'is-visible' );

            pkppg.edit_post.cache.diff_modal_diff.empty();
        },

        /**
         * Publish changes
         *
         * @since 0.1
         */
        publishChanges: function(e) {

            if ( !window.confirm( 'Are you sure you want to publish these changes to the live version?' ) ) {
                return;
            }

            console.log( 'Publishing!' );
        }
    };

    // Initialize component
    pkppg.edit_post.init();

});
