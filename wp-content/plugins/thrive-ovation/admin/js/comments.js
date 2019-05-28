/**
 * Created by Ovidiu on 4/21/2016.
 */

var ThriveOvation = ThriveOvation || {};
ThriveOvation.comments = ThriveOvation.comments || {};

(function ( $ ) {

	jQuery( document ).ready( function () {
		// the "href" attribute of .modal-trigger must specify the modal ID that wants to be triggered
		jQuery( '.modal-trigger' ).leanModal();
	} );

	/**
	 *  Shows Loader
	 */
	ThriveOvation.comments.showLoader = function () {
		jQuery( '.tvd-modal-preloader' ).removeClass( 'tvo-hide' );
		jQuery( '.tvd-lean-overlay' ).addClass( 'tvo-show' );
	};

	/**
	 * Hide Loader
	 */
	ThriveOvation.comments.hideLoader = function () {
		jQuery( '.tvd-modal-preloader' ).addClass( 'tvo-hide' );
		jQuery( '.tvd-lean-overlay' ).removeClass( 'tvo-show' );
	};


	/**
	 * Edit a coment as testimonial
	 * @param commentID
	 */
	ThriveOvation.comments.addEditTestimonial = function ( commentID ) {
		this.showLoader();
		var iframe = jQuery( '#tvo-modal-edit-testimonial' ).find( 'iframe' ),
			source = iframe.attr( 'data-source' );

		iframe.attr( 'src', source + commentID );
		jQuery( '#tvo-modal-edit-testimonial-trigger' ).click();

	};

	/**
	 * Callback function. After the user presses add testimonial on the modal screen
	 * @param obj
	 */
	ThriveOvation.comments.storeEditTestimonial = function ( obj ) {

		$.ajax( {
			headers: {
				'X-WP-Nonce': ThriveOvation.nonce
			},
			cache: false,
			url: ThriveOvation.routes.comments + '/import_edit_testimonial',
			type: 'POST',
			data: {'testimonial_obj': obj}
		} ).done( function ( response ) {

			switch ( response.code ) {
				case 0: // Error
					console.log( "ERROR" );
					break;
				case 1: // Success
					jQuery( '.tvd-lean-overlay' ).trigger( 'click' );
					jQuery( '.tvo_comment_section_' + obj.comment_id ).html( response.html );
					jQuery( '.tvo_notice_text' ).html( response.notice_text );
					jQuery( '#tvo_notice' ).addClass( response.class ).removeClass( 'hidden' );
					break;
				default:
					break;
			}
		} )
		 .error( function () {
			 console.log( "error" );
		 } )
		 .always( function () {
			 console.log( "always" );
		 } );
	};

	/**
	 * Checks the permission to send email to customer from comments
	 *
	 * @param permission
	 * @param email_name
	 * @param email_content
	 */
	ThriveOvation.comments.askPermissionEmail = function ( permission, email_name, email_content ) {
		$.ajax( {
			headers: {
				'X-WP-Nonce': ThriveOvation.nonce
			},
			cache: false,
			url: ThriveOvation.routes.comments + '/ask_permission_email',
			type: 'POST',
			data: {'tvo_permission': permission, 'tvo_email_name': email_name, 'tvo_email_content': email_content}
		} ).done( function ( response ) {

			var $iframe = jQuery( 'iframe' );
			$iframe.ready( function () {
				$iframe.contents().find( '.tvo-ask-permission-email-response' ).html( response.html );
				$iframe.contents().find( '.tvo-save-new-testimonial' ).html( response.button_text );
			} );

		} ).error( function () {
			console.log( "error" );
		} ).always( function () {
			console.log( "always" );
		} );
	};
})( jQuery );
