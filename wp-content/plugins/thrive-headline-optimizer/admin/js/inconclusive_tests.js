/**
 * Created by Ovidiu on 6/17/2016.
 */
var ThriveHeadInconclusive = ThriveHeadInconclusive || {};
ThriveHeadInconclusive.inconclusive_tests = ThriveHeadInconclusive.inconclusive_tests || {};

(function ( $ ) {

	jQuery( document ).on( 'click', '.tho_error_inconclusive_test_notice .notice-dismiss', function () {
		var test_id = jQuery( this ).parent().attr( 'data-test-id' );
		ThriveHeadInconclusive.inconclusive_tests.dismiss_notice( test_id, false );
	} );

	/**
	 * Trigger dismiss notice
	 * @param test_id
	 */
	ThriveHeadInconclusive.inconclusive_tests.trigger_dismiss_notice = function ( test_id ) {
		this.dismiss_notice( test_id, true );
	};

	/**
	 * Dismiss notice ajax request
	 * @param test_id
	 * @param redirect
	 */
	ThriveHeadInconclusive.inconclusive_tests.dismiss_notice = function ( test_id, redirect ) {

		$.ajax( {
			headers: {
				'X-WP-Nonce': ThriveHeadInconclusive.nonce
			},
			cache: false,
			url: ThriveHeadInconclusive.routes.tests + '/log_inconclusive_test',
			type: 'POST',
			data: {'test_id': test_id, 'redirect': redirect}
		} ).done( function ( response ) {
			if ( response.result == true && redirect == true ) {
				top.location.href = response.redirect_url;
			}
			jQuery('div').find('[data-test-id="' + test_id + '"]').remove();
		} )
		 .error( function () {
			 console.log( "error" );
		 } )
		 .always( function () {
			 console.log( "always" );
		 } );
	};

})( jQuery );