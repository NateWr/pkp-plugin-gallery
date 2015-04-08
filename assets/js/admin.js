/**
 * JavaScript to handle a plugin submission form for the
 * PKP Plugin Gallery.
 */

var pkppg = pkppg || {};
pkppg.data = pkppg.data || pkppg_data;

jQuery( document ).ready( function( $ ) {

	console.log( 'pkppg.form loaded' );

	pkppg.form = {

		init: function() {

			this.cache = {};
			this.cache.releases = $( '.pkppg-releases-form' );
			this.cache.add = this.cache.releases.find( '.pkp-release-form-buttons .add' );
			this.cache.cancel = this.cache.releases.find( '.pkp-release-form-buttons .cancel' );
			this.cache.new = this.cache.releases.find( '#pkp-new-release' );
		}
	};

	pkppg.form.init();
	console.log( pkppg.form );

});
