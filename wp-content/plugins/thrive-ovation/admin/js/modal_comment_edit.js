/**
 * Created by Ovidiu on 5/11/2016.
 */


(function ( $ ) {
	jQuery( document ).ready( function () {

		jQuery( ".tvo-add-tag-autocomplete" ).select2( {
			tags: true,
			multiple: true
		} ).on( 'select2:select', function ( e ) {
			ThriveOvation.util.addNewTagInTheSystemFromComments( e.params.data );
		} );

		jQuery( '#tvo-author-new-tag-modal' ).val( null ).trigger( 'change' );

		setTimeout( function () {
			parent.ThriveOvation.comments.hideLoader();
		}, 1000 );

		jQuery( '#tvo-ask-permission-email' ).click( function () {
			parent.ThriveOvation.comments.askPermissionEmail( Number( jQuery( this ).is( ':checked' ) ), jQuery( '#tvo-author-name' ).val(), tinyMCE.get( 'tvo-testimonial-content-tinymce' ).getContent() );
		} );

	} );

})( jQuery );

/**
 *  Refreshes the email previuew content
 */
function tvo_refresh_preview_email() {
	parent.ThriveOvation.comments.askPermissionEmail( Number( jQuery( '#tvo-ask-permission-email' ).is( ':checked' ) ), jQuery( '#tvo-author-name' ).val(), tinyMCE.get( 'tvo-testimonial-content-tinymce' ).getContent() );
}

/**
 * Opens media
 */
function tvo_open_media() {
	if ( wp_file_frame ) {
		wp_file_frame.open();
		return;
	}

	var wp_file_frame = wp.media( {
		title: ThriveOvation.translations.choose_testimonial_image,
		button: {
			text: ThriveOvation.translations.choose_testimonial_image_button
		},
		library: {
			type: 'image'
		},
		multiple: false,
		frame: 'select'
	} );

	wp_file_frame.on( 'select', function () {
		var attachment = wp_file_frame.state().get( 'selection' ).first().toJSON();
		jQuery( '.tvo-testimonial-author-image img' ).attr( 'src', attachment.url );
		jQuery( '#tvo-upload-testimonial-image' ).hide();
		jQuery( '.tvo-image-uploaded' ).show();
	} );
	wp_file_frame.open();
}

function remove_image() {
	var default_image = jQuery( '#tvo-remove-testimonial-image' ).attr( 'data-default' );
	jQuery( '.tvo-testimonial-author-image img' ).attr( 'src', default_image );
	jQuery( '#tvo-upload-testimonial-image' ).show();
	jQuery( '.tvo-image-uploaded' ).hide();
}

/**
 * Action to add testimonial from iframe
 */
function tvo_add_edit_testimonial_action() {
	if ( tvo_validate_modal_window() ) {

		var obj = {
			'name': jQuery( '#tvo-author-name' ).val(),
			'title': jQuery( '#tvo-title' ).val(),
			'email': jQuery( '#tvo-author-email' ).val(),
			'role': jQuery( '#tvo-author-role' ).val(),
			'website_url': jQuery( '#tvo-author-website' ).val(),
			'content': ThriveOvation.util.getTestimonialContent( jQuery( '.tvo-comments-modal' ) ),
			'tags': jQuery( '#tvo-author-new-tag-modal' ).val(),
			'picture_url': jQuery( '.tvo-testimonial-author-image img' ).attr( 'src' ),
			'comment_id': jQuery( '#tvo-comment-id' ).val(),
			'send_email': Number( jQuery( '#tvo-ask-permission-email' ).is( ':checked' ) )
		};

		parent.ThriveOvation.comments.storeEditTestimonial( obj );
	}
}

/**
 * Validates the iframe inputs
 *
 * @returns {boolean}
 */
function tvo_validate_modal_window() {
	var valid = true;
	if ( ! ThriveOvation.util.validateInput( jQuery( '#tvo-author-name' ), ThriveOvation.translations.isRequired ) ) {
		valid = false;
	}
	if ( ! ThriveOvation.util.validateInput( jQuery( '#tvo-author-email' ), ThriveOvation.translations.isRequired ) ) {
		valid = false;
	}
	if ( ! ThriveOvation.util.validateEmail( jQuery( '#tvo-author-email' ) ) ) {
		valid = false;
	}
	if ( jQuery( '#tvo-author-website' ).val() && ! ThriveOvation.util.validateURL( jQuery( '#tvo-author-website' ) ) ) {
		valid = false;
	}
	if ( ! ThriveOvation.util.validateInput( ThriveOvation.util.getTestimonialContent( jQuery( '.tvo-comments-modal' ) ), ThriveOvation.translations.testimonial_content_missing, true ) ) {
		valid = false;
		jQuery( '.tvo-testimonial-content' ).css( 'border', '1px solid red' );
	} else {
		jQuery( '.tvo-testimonial-content' ).css( 'border', 'none' );
	}
	if ( valid == false ) {
		jQuery( 'html, body' ).animate( {
			scrollTop: 0
		}, 1000 );
	}

	return valid;
}