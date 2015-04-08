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

		init: function() {

			this.cache = {};
			this.cache.body = pkppg.cache.body || $( 'body' );
			this.cache.modal = $( '#pkp-release-modal' );
			this.cache.fields = this.cache.modal.find( '.pkp-release-fields' );
			this.cache.add = $( '.pkp-release-form-buttons .add' );
			this.cache.save = this.cache.modal.find( '.pkp-release-form-buttons .save' );
			this.cache.cancel = this.cache.modal.find( '.pkp-release-form-buttons .cancel' );

			// Open/close new release form
			this.cache.add.click( this.showReleaseForm );
			this.cache.cancel.click( this.hideReleaseForm );
			this.cache.modal.click( this.hideReleaseForm );
			$( document ).keyup( function(e) { if ( e.which == '27' ) { pkppg.form.hideReleaseForm(); } } );
		},

		/**
		 * Show the modal for adding/editing a release
		 *
		 * @since 0.1
		 */
		showReleaseForm: function(e) {

			if ( typeof e !== 'undefined' ) {
				e.stopPropagation();
				e.preventDefault();
			}

			pkppg.form.cache.body.addClass( 'pkppg-modal-is-visible' );
			pkppg.form.cache.modal.addClass( 'is-visible' );
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

			pkppg.form.cache.body.removeClass( 'pkppg-modal-is-visible' );
			pkppg.form.cache.modal.removeClass( 'is-visible' );
		}
	};

	pkppg.form.init();
	console.log( pkppg );

});
