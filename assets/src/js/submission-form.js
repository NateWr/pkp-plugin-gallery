/**
 * JavaScript to handle a plugin submission form for the
 * PKP Plugin Gallery.
 */

var pkppg = pkppg || {};
pkppg.data = pkppg.data || pkppg_data;
pkppg.cache = pkppg.cache || {};

jQuery( document ).ready( function( $ ) {

	console.log( 'pkppg.form loaded' );

	pkppg.form = {

		/**
		 * Parent plugin that the current form's release
		 * is for. This should get reset every time the
		 * form is opened/closed.
		 *
		 * @since 0.1
		 */
		plugin: 0,

		/**
		 * Initialize the form events
		 *
		 * @since 0.1
		 */
		init: function() {

			this.cache = {};
			this.cache.body = pkppg.cache.body || $( 'body' );
			this.cache.modal = $( '#pkp-release-modal' );
			this.cache.fields = this.cache.modal.find( '.pkp-release-fields' );
			this.cache.add = $( '.pkp-release-form-buttons .add' );
			this.cache.save = this.cache.modal.find( '.pkp-release-form-buttons .save' );
			this.cache.cancel = this.cache.modal.find( '.pkp-release-form-buttons .cancel' );
			this.cache.status = this.cache.modal.find( '.pkp-release-form-buttons .status' );

			// Open/close new release form
			this.cache.add.click( this.showReleaseForm );
			this.cache.cancel.click( this.hideReleaseForm );
			this.cache.modal.click( function(e) { if ( $( e.target ).is( pkppg.form.cache.modal ) ) { pkppg.form.hideReleaseForm(); } } );
			$( document ).keyup( function(e) { if ( e.which == '27' ) { pkppg.form.hideReleaseForm(); } } );

			// Add a release
			this.cache.save.click( this.save );
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
			pkppg.form.cache.modal.addClass( 'is-visible' );

			if ( typeof plugin !== 'undefined' ) {
				pkppg.form.plugin = plugin;
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
			if ( pkppg.form.cache.cancel.attr( 'disabled' ) ) {
				return;
			}

			pkppg.form.cache.body.removeClass( 'pkppg-modal-is-visible' );
			pkppg.form.cache.modal.removeClass( 'is-visible' );

			// Clear fields
			pkppg.form.cache.fields.find( 'input, textarea' ).val( '' );

			pkppg.form.plugin = 0;
		},

		/**
		 * Save a release
		 *
		 * @since 0.1
		 */
		save: function(e) {

			if ( typeof e !== 'undefined' ) {
				e.stopPropagation();
				e.preventDefault();
			}

			// Do nothing if the save button is disabled
			if ( pkppg.form.cache.save.attr( 'disabled' ) ) {
				return;
			}

			pkppg.form.setStatus( 'working' );

			var params = {};

			params.action = 'pkppg-submit-release';
			params.nonce = pkppg.data.nonce;
			params.release = pkppg.form.objectFromForm();

			var data = $.param( params );

			$.post( pkppg.data.ajaxurl, data, function( r ) {
				console.log( r ); // @todo removeme

				if ( r.success ) {
					pkppg.form.setStatus( 'success' );
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
			var params = pkppg.form.cache.modal.find( 'form' ).serializeArray();

			for( var i = 0; i < params.length; i++ ) {
				release[ params[i].name ] = params[i].value;
			}

			if ( this.plugin ) {
				release.plugin = this.plugin;
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

			pkppg.form.cache.status.removeClass( 'working success error' ).addClass( status );

			var disabled = status === 'working' ? true : false;
			pkppg.form.cache.save.attr( 'disabled', disabled );
			pkppg.form.cache.cancel.attr( 'disabled', disabled );
		},

		/**
		 * Reset the status of the form and enable any disabled fibuttonselds
		 *
		 * @since 0.1
		 */
		resetStatus: function() {
			pkppg.form.cache.status.removeClass( 'working success error' );
			pkppg.form.cache.save.attr( 'disabled', false );
			pkppg.form.cache.cancel.attr( 'disabled', false );
		}
	};

	pkppg.form.init();
	console.log( pkppg );

});
