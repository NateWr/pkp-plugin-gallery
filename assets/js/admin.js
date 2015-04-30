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
        },

        /**
         * Show the modal for viewing and committing changes
         *
         * @since 0.1
         */
        showDiffModal: function(id, diff) {

            pkppg.edit_post.ID = id;

            if ( !pkppg.edit_post.ID ) {
                return;
            }

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

            pkppg.edit_post.cache.body.removeClass( 'pkppg-modal-is-visible' );
            pkppg.edit_post.cache.diff_modal.removeClass( 'is-visible' );

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
			params.ID = pkppg.edit_post.cache.form.find( '#post_ID' ).val();

			var data = $.param( params );

			$.post( pkppg.data.ajaxurl, data )
				.done( function(r) {
                    pkppg.edit_post.loading = false;
                    pkppg.edit_post.cache.diff_modal_controls.removeClass( 'pkpr-loading pkpr-success pkpr-error' );
                    setTimeout( function() { pkppg.edit_post.cache.diff_modal_controls.removeClass( 'pkpr-loading pkpr-success pkp-error' ); }, 4000 );

					if ( r.success ) {
                        pkppg.edit_post.cache.diff_modal_controls.addClass( 'pkpr-success' );
                        window.location.replace( r.data.redirect );

					} else {
                        pkppg.edit_post.cache.diff_modal_controls.addClass( 'pkpr-error' );
                        pkppg.edit_post.cache.diff_modal_publish.attr( 'disabled', false );
					}
				});
        }
    };

    // Initialize component
    pkppg.edit_post.init();

});

/**
 * JavaScript to handle a plugin submission form for the
 * PKP Plugin Gallery.
 */

var pkppg = pkppg || {};

jQuery( document ).ready( function( $ ) {

	pkppg.form = {

		/**
		 * Initialize the form events
		 *
		 * @since 0.1
		 */
		init: function() {

			this.cache = {};
			this.cache.body = pkppg.cache.body || $( 'body' );
			this.cache.releases = $( '.pkp-releases-form' );
			this.cache.release_modal = $( '#pkp-release-modal' );
			this.cache.release_fields = this.cache.release_modal.find( '.pkp-release-fields' );
			this.cache.release_add = this.cache.releases.find( '.pkp-release-form-buttons .add' );
			this.cache.release_save = this.cache.release_modal.find( '.pkp-release-form-buttons .save' );
			this.cache.release_cancel = this.cache.release_modal.find( '.pkp-release-form-buttons .cancel' );
			this.cache.release_status = this.cache.release_modal.find( '.pkp-release-form-buttons .status' );

			// Details on current items being managed
			this.current = {};
			this.current.plugin = 0;

			// Open/close new release form
			this.cache.release_add.click( this.showReleaseForm );
			this.cache.release_cancel.click( this.hideReleaseForm );
			this.cache.release_modal.click( function(e) { if ( $( e.target ).is( pkppg.form.cache.release_modal ) ) { pkppg.form.hideReleaseForm(); } } );
			$( document ).keyup( function(e) { if ( e.which == '27' ) { pkppg.form.hideReleaseForm(); } } );

			// Add a release
			this.cache.release_save.click( this.saveRelease );

			// Register click handlers on the releases list
			this.cache.releases.click( function(e) {
				e.stopPropagation();
				e.preventDefault();

				var target = $( e.target );

				if ( target.attr( 'disabled' ) ) {
					return;
				}

				var release = target.parents( '.release' );

				if ( release && target.hasClass( 'edit' ) ) {
					pkppg.form.loadRelease( release.data( 'id' ) );
				} else if ( release && target.hasClass( 'delete' ) ) {
					pkppg.form.deleteRelease( release.data( 'id' ) );
				} else if ( release && target.hasClass( 'approve' ) ) {
					pkppg.form.publishRelease( release.data( 'id' ) );
				}
			});
		},

		/**
		 * Show the modal for adding/editing a release
		 *
		 * @since 0.1
		 */
		showReleaseForm: function(e) {

			var plugin;

			if ( typeof e !== 'undefined' ) {
				e.stopPropagation();
				e.preventDefault();

				plugin = $( e.target ).data( 'plugin' );
			}

			pkppg.form.cache.body.addClass( 'pkppg-modal-is-visible' );
			pkppg.form.cache.release_modal.addClass( 'is-visible' );

			if ( typeof plugin !== 'undefined' ) {
				pkppg.form.current.plugin = plugin;
			}

			// Load data from current release
			if ( typeof pkppg.form.current.release !== 'undefined' ) {
				var release = pkppg.form.current.release;
				var fields = pkppg.form.cache.release_fields;
				fields.find( '#pkp-release-id' ).val( release.ID );
				fields.find( '#pkp-release-version' ).val( release.version );
				fields.find( '#pkp-release-date' ).val( release.release_date );
				fields.find( '#pkp-release-package' ).val( release.package );
				fields.find( '#pkp-release-description' ).val( release.description );
				fields.find( '#pkp-release-md5' ).val( release.md5 );
				if ( release.certification ) {
					fields.find( '.certification option[value="' + release.certification + '"]' ).attr( 'selected', 'selected' );
				}
				if ( release.applications.length ) {
					fields.find( '.applications input' ).each( function() {
						if ( $.inArray( $(this).val(), release.applications ) !== -1 ) {
							$(this).attr( 'checked', 'checked' );
						}
					});
				}
				pkppg.form.current.plugin = release.plugin;
			}
		},

		/**
		 * Hide the modal for adding/editing a release
		 *
		 * @since 0.1
		 */
		hideReleaseForm: function(e) {

			if ( typeof e !== 'undefined' ) {
				e.stopPropagation();
				e.preventDefault();
			}

			// Do nothing if the cancel button is disabled
			if ( pkppg.form.cache.release_cancel.attr( 'disabled' ) ) {
				return;
			}

			pkppg.form.cache.body.removeClass( 'pkppg-modal-is-visible' );
			pkppg.form.cache.release_modal.removeClass( 'is-visible' );

			// Clear fields
			pkppg.form.cache.release_fields.find( 'input[type="hidden"], input[type="text"], input[type="url"], textarea' ).val( '' );
			pkppg.form.cache.release_fields.find( 'option:selected' ).removeAttr( 'selected' );
			pkppg.form.cache.release_fields.find( 'input[type="checkbox"]' ).removeAttr( 'checked' );

			// Clear current release
			delete pkppg.form.current.release;

			pkppg.form.current.plugin = 0;
		},

		/**
		 * Load a release for editing
		 *
		 * @since 0.1
		 */
		loadRelease: function( id ) {

			pkppg.form.setReleaseStatus( id, 'loading' );

			var params = {};

			params.action = 'pkppg-get-release';
			params.nonce = pkppg.data.nonce;
			params.release = id;

			var data = $.param( params );

			$.get( pkppg.data.ajaxurl, data )
				.done( function(r) {

					pkppg.form.resetReleaseStatus( id );

					if ( r.success ) {

						pkppg.form.current.release = r.data.release;
						pkppg.form.showReleaseForm();

					} else {
						// @todo handle failure
					}
				});
		},

		/**
		 * Delete a release
		 *
		 * @since 0.1
		 */
		deleteRelease: function( id ) {

			pkppg.form.setReleaseStatus( id, 'deleting' );

			var params = {};

			params.action = 'pkppg-delete-release';
			params.nonce = pkppg.data.nonce;
			params.release = id;

			var data = $.param( params );

			$.post( pkppg.data.ajaxurl, data )
				.done( function(r) {

					pkppg.form.resetReleaseStatus( id );

					if ( r.success ) {

						pkppg.form.removeReleaseFromList( id );

					} else {
						// @todo handle failure
					}

				});

		},

		/**
		 * Save a release
		 *
		 * @since 0.1
		 */
		saveRelease: function(e) {

			if ( typeof e !== 'undefined' ) {
				e.stopPropagation();
				e.preventDefault();
			}

			// Do nothing if the save button is disabled
			if ( pkppg.form.cache.release_save.attr( 'disabled' ) ) {
				return;
			}

			pkppg.form.setModalStatus( 'working' );

			var params = {};

			params.action = 'pkppg-insert-release';
			params.nonce = pkppg.data.nonce;
			params.release = pkppg.form.objectFromForm();

			var data = $.param( params );

			$.post( pkppg.data.ajaxurl, data, function( r ) {

				if ( r.success ) {
					pkppg.form.setModalStatus( 'success' );
					pkppg.form.updateReleaseInList( r.data.release.ID, r.data.overview );
					setTimeout( pkppg.form.hideReleaseForm, 1000 );

				} else {
					pkppg.form.setModalStatus( 'error' );
				}

				// Clear status after 4 seconds
				setTimeout( pkppg.form.resetModalStatus, 4000 );
			});
		},

		/**
		 *  Set a release status to publish
		 *
		 *  @since 0.1
		 */
		publishRelease: function(id) {

			pkppg.form.setReleaseStatus( id, 'publishing' );

			var params = {};

			params.action = 'pkppg-publish-release';
			params.nonce = pkppg.data.nonce;
			params.release = id;

			var data = $.param( params );

			$.post( pkppg.data.ajaxurl, data )
				.done( function(r) {

					pkppg.form.resetReleaseStatus( id );

					if ( r.success ) {

						pkppg.form.updateReleaseInList( r.data.release.ID, r.data.overview );

					} else {
						// @todo handle failure
					}
				});
		},

		/**
		 * Generate an object hash for a release from the form
		 * data
		 *
		 * @since 0.1
		 */
		objectFromForm: function() {

			var release = {};
			var params = pkppg.form.cache.release_modal.find( 'form' ).serializeArray();

			for( var i = 0; i < params.length; i++ ) {

				// Certification taxonomy
				if ( params[i].name == 'tax_input[pkp_certification]' ) {
					release.certification = params[i].value;

				// Application taxonomy
				} else if ( params[i].name == 'tax_input[pkp_application][]' ) {
					if ( typeof release.applications == 'undefined' ) {
						release.applications = [];
					}
					release.applications.push( params[i].value );

				} else {
					release[ params[i].name ] = params[i].value;
				}
			}

			if ( this.current.plugin ) {
				release.plugin = this.current.plugin;
			}

			return release;
		},

		/**
		 * Set the status of the list of releases
		 *
		 * @since 0.1
		 */
		setReleaseStatus: function( id, status ) {

			var release_el = pkppg.form.getReleaseEl( id );
			if ( !release_el ) {
				return;
			}

			release_el.find( '.actions' ).addClass( status ).find( 'a' ).attr( 'disabled', true );

			if ( status == 'loading' ) {
				pkppg.form.cache.releases.find( '.release .edit' ).attr( 'disabled', true );
			}
		},

		/**
		 * Reset the status of the list of releases
		 *
		 * @since 0.1
		 */
		resetReleaseStatus: function( id ) {

			pkppg.form.cache.releases.find( '.release .actions a' ).attr( 'disabled', false );

			var release_el = pkppg.form.getReleaseEl( id );
			if ( !release_el ) {
				return;
			}

			release_el.find( '.actions' ).removeClass( 'loading deleting publishing' ).find( 'a' ).attr( 'disabled', false );
		},

		/**
		 * Set the status of the form and disable/enable the
		 * appropriate buttons
		 *
		 * @since 0.1
		 */
		setModalStatus: function( status ) {

			pkppg.form.cache.release_status.removeClass( 'working success error' ).addClass( status );

			var disabled = status === 'working' ? true : false;
			pkppg.form.cache.release_save.attr( 'disabled', disabled );
			pkppg.form.cache.release_cancel.attr( 'disabled', disabled );
		},

		/**
		 * Reset the status of the form and enable any disabled fibuttonselds
		 *
		 * @since 0.1
		 */
		resetModalStatus: function() {
			pkppg.form.cache.release_status.removeClass( 'working success error' );
			pkppg.form.cache.release_save.attr( 'disabled', false );
			pkppg.form.cache.release_cancel.attr( 'disabled', false );
		},

		/**
		 * Update the release list by modifying an existing review or
		 * adding a new one
		 *
		 * @since 0.1
		 */
		updateReleaseInList: function( id, overview ) {

			var replaced = false;

			// Replace an existing release
			var release_el = pkppg.form.getReleaseEl( id );
			if ( release_el ) {
				release_el.parent().html( overview );
				replaced = true;
				return;
			}

			if ( !replaced ) {
				pkppg.form.cache.releases.find( '.releases' ).append( '<li>' + overview + '</li>' );
			}
		},

		/**
		 * Remove a release from the list
		 *
		 * @since 0.1
		 */
		removeReleaseFromList: function( id ) {
			var release_el = pkppg.form.getReleaseEl( id );
			if ( !release_el ) {
				return;
			}

			release_el.parent().fadeOut( 500, function() { $(this).remove(); } );
		},

		/**
		 * Get a release el by id
		 *
		 * @since 0.1
		 */
		getReleaseEl: function( id ) {

			var el;

			pkppg.form.cache.releases.find( '.release' ).each( function() {
				if ( $(this).data( 'id' ) == id ) {
					el = $(this);
				}
			});

			return el;
		}
	};

	pkppg.form.init();

});
