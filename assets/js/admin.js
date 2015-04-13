/**
 * JavaScript to handle a plugin submission form for the
 * PKP Plugin Gallery.
 */

var pkppg = pkppg || {};
pkppg.data = pkppg.data || pkppg_data;
pkppg.cache = pkppg.cache || {};

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

				if ( target.hasClass( 'edit' ) ) {
					pkppg.form.loadRelease( target.parents( '.release' ).data( 'id' ) );
				} else if ( target.hasClass( 'delete' ) ) {
					pkppg.form.deleteRelease( target.parents( '.release' ).data( 'id' ) );
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
			pkppg.form.cache.release_fields.find( 'input, textarea' ).val( '' );

			// Clear current release from
			delete pkppg.form.current.release;

			pkppg.form.current.plugin = 0;
		},

		/**
		 * Load a release for editing
		 *
		 * @since 0.1
		 */
		loadRelease: function( id ) {

			var params = {};

			params.action = 'pkppg-get-release';
			params.nonce = pkppg.data.nonce;
			params.release = id;

			var data = $.param( params );

			$.get( pkppg.data.ajaxurl, data )
				.done( function(r) {
					console.log( r );

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

			console.log( 'deleteRelease:' + id );

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

			pkppg.form.setStatus( 'working' );

			var params = {};

			params.action = 'pkppg-insert-release';
			params.nonce = pkppg.data.nonce;
			params.release = pkppg.form.objectFromForm();

			var data = $.param( params );

			$.post( pkppg.data.ajaxurl, data, function( r ) {

				if ( r.success ) {
					pkppg.form.setStatus( 'success' );
					pkppg.form.updateReleaseList( r.data.release.ID, r.data.overview );
					setTimeout( pkppg.form.hideReleaseForm, 1000 );

				} else {
					pkppg.form.setStatus( 'error' );
				}

				// Clear status after 4 seconds
				setTimeout( pkppg.form.resetStatus, 4000 );
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
				release[ params[i].name ] = params[i].value;
			}

			if ( this.current.plugin ) {
				release.plugin = this.current.plugin;
			}

			return release;
		},

		/**
		 * Set the status of the form and disable/enable the
		 * appropriate buttons
		 *
		 * @since 0.1
		 */
		setStatus: function( status ) {

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
		resetStatus: function() {
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
		updateReleaseList: function( id, overview ) {

			var replaced = false;

			// Replace an existing release
			pkppg.form.cache.releases.find( '.release' ).each( function() {
				if ( $(this).data( 'id' ) == id ) {
					$(this).parent().html( overview );
					replaced = true;
					return;
				}
			});

			if ( !replaced ) {
				pkppg.form.cache.releases.find( '.releases' ).append( '<li>' + overview + '</li>' );
			}
		}
	};

	pkppg.form.init();

});