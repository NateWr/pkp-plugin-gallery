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

            // Track when ajax is firing to prevent multiple requests
            this.loading = false;

            this.cache = {};
            this.cache.body = pkppg.cache.body || $( 'body' );
            this.cache.form = $( 'form#post' );
            this.cache.save = this.cache.form.find( '#save-post' );
            this.cache.disable = this.cache.form.find( '#disable-post' );
            this.cache.compare = this.cache.form.find( '#compare-changes' );
            this.cache.diff_modal = $( '#pkppg-diff' );
            this.cache.diff_modal_controls = this.cache.diff_modal.find( '.controls' );
            this.cache.diff_modal_close = this.cache.diff_modal.find( '.close' );
            this.cache.diff_modal_publish = this.cache.diff_modal.find( '.publish' );
            this.cache.diff_modal_diff = this.cache.diff_modal.find( '#pkp-plugin-diff' );
            this.cache.publish_title = this.cache.form.find( '#submitdiv h3.hndle' );

            // Disable the lost-changes warning when saving for later
            // This turns off a WP core event that is triggered on submit of the
            // form
            this.cache.save.click( function() {
                $(window).off( 'beforeunload.edit-post' );
            });

            // Open/close compare changes modal
            this.cache.compare.click( this.loadDiff );
            this.cache.diff_modal_close.click( this.hideDiffModal );
            this.cache.diff_modal_publish.click( this.publishChanges );
			this.cache.diff_modal.click( function(e) { if ( $( e.target ).is( pkppg.edit_post.cache.diff_modal ) ) { pkppg.edit_post.hideDiffModal(); } } );
			$( document ).keyup( function(e) { if ( e.which == '27' ) { pkppg.edit_post.hideDiffModal(); } } );

            // Disable a plugin
            this.cache.disable.click( this.disablePlugin );
        },

        /**
         * Show the modal for viewing and committing changes
         *
         * @since 0.1
         */
        showDiffModal: function(id, diff) {

            if ( !id ) {
                return;
            }

            pkppg.edit_post.cache.diff_modal_publish.data( 'id', id );

            if ( diff ) {
                pkppg.edit_post.cache.diff_modal_diff.html( diff );
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

            pkppg.edit_post.cache.diff_modal_publish.data( 'id', '' );

            pkppg.edit_post.cache.body.removeClass( 'pkppg-modal-is-visible' );
            pkppg.edit_post.cache.diff_modal.removeClass( 'is-visible' );
            pkppg.edit_post.cache.diff_modal_controls.removeClass( 'pkpr-loading pkpr-success pkpr-error' );
            pkppg.edit_post.cache.diff_modal_publish.attr( 'disabled', false );

            pkppg.edit_post.cache.diff_modal_diff.empty();
        },

        /**
         * Retrieve a diff for this object
         *
         * @since 0.1
         */
        loadDiff: function(e) {

            if ( typeof e !== 'undefined' ) {
                e.stopPropagation();
                e.preventDefault();
            }

            if ( pkppg.edit_post.loading ) {
                return;
            }

            pkppg.edit_post.loading = true;

            pkppg.edit_post.cache.publish_title.append( '<span class="pkp-spinner"></span>' );
            pkppg.edit_post.cache.compare.attr( 'disabled', true );

            var params = {};

            params.action = 'pkppg-get-update-diff';
            params.nonce = pkppg.data.nonce;
            params.ID = pkppg.edit_post.cache.form.find( '#post_ID' ).val();

            var data = $.param( params );

            $.get( pkppg.data.ajaxurl, data )
                .done( function(r) {
                    pkppg.edit_post.loading = false;
                    pkppg.edit_post.cache.publish_title.find( '.pkp-spinner' ).remove();
                    pkppg.edit_post.cache.compare.attr( 'disabled', false );

                    if ( r.success ) {
                        pkppg.edit_post.showDiffModal( params.ID, r.data.diff );

                    } else {
                        // @todo handle failure
                    }
                });
        },

        /**
         * Publish changes
         *
         * @since 0.1
         */
        publishChanges: function(e) {

           if ( typeof e !== 'undefined' ) {
               e.stopPropagation();
               e.preventDefault();
           }

            if ( pkppg.edit_post.loading ) {
                return;
            }

            if ( !window.confirm( 'Are you sure you want to publish these changes to the live version?' ) ) {
                return;
            }

            pkppg.edit_post.loading = true;

            pkppg.edit_post.cache.diff_modal_controls.removeClass( 'pkpr-loading pkpr-success pkpr-error' ).addClass( 'pkpr-loading' );
            pkppg.edit_post.cache.diff_modal_publish.attr( 'disabled', true );

			var params = {};

			params.action = 'pkppg-merge-update';
			params.nonce = pkppg.data.nonce;
			params.ID = pkppg.edit_post.cache.diff_modal_publish.data( 'id' );

			var data = $.param( params );

			$.post( pkppg.data.ajaxurl, data )
				.done( function(r) {
                    pkppg.edit_post.loading = false;
                    pkppg.edit_post.cache.diff_modal_controls.removeClass( 'pkpr-loading pkpr-success pkpr-error' );
                    setTimeout( function() { pkppg.edit_post.cache.diff_modal_controls.removeClass( 'pkpr-loading pkpr-success pkp-error' ); }, 4000 );

					if ( r.success ) {
                        pkppg.edit_post.cache.diff_modal_controls.addClass( 'pkpr-success' );

                        // Load edit post screen for a plugin
                        if ( r.data.redirect ) {
                            window.location.replace( r.data.redirect );

                        // Update the release in the list
                        } else {
                            pkppg.form.updateReleaseInList( r.data.release.ID, r.data.overview );
                            pkppg.edit_post.hideDiffModal();
                        }

					} else {
                        pkppg.edit_post.cache.diff_modal_controls.addClass( 'pkpr-error' );
                        pkppg.edit_post.cache.diff_modal_publish.attr( 'disabled', false );
					}
				});
        },

        /**
         * Disable plugin
         *
         * @since 0.1
         */
        disablePlugin: function(e) {

            if ( typeof e !== 'undefined' ) {
                e.stopPropagation();
                e.preventDefault();
            }

            if ( pkppg.edit_post.loading ) {
                return;
            }

            var id = $( e.target ).data( 'id' );
            if ( !id ) {
                return;
            }

            pkppg.edit_post.loading = true;

            pkppg.edit_post.cache.publish_title.append( '<span class="pkp-spinner"></span>' );
            pkppg.edit_post.cache.disable.attr( 'disabled', true );

            var params = {};

            params.action = 'pkppg-disable-post';
            params.nonce = pkppg.data.nonce;
            params.post = id;

            var data = $.param( params );

            $.post( pkppg.data.ajaxurl, data )
                .done( function(r) {
                    pkppg.edit_post.loading = false;
                    pkppg.edit_post.cache.publish_title.find( '.pkp-spinner' ).remove();
                    pkppg.edit_post.cache.disable.attr( 'disabled', false );

                    if ( r.success ) {
                        window.location.replace( r.data.redirect );

                    } else {
                        // @todo handle failure
                    }
                });
        }

    };

    // Initialize component
    pkppg.edit_post.init();

});
