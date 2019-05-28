/**
 * Created by Ovidiu on 1/17/2017.
 */
var TVE_Content_Builder = TVE_Content_Builder || {};
TVE_Content_Builder.ext = TVE_Content_Builder.ext || {};

var TQB_External_Editor = TQB_External_Editor || {};

(function ( $ ) {
	/**
	 * DOMReady
	 */
	$( function () {
		TVE_Content_Builder.add_filter( 'menu_show', TQB_External_Editor.on_menu_show );
	} );

	TQB_External_Editor.on_menu_show = function ( element ) {
		if ( element.hasClass( 'thrive-quiz-builder-shortcode' ) ) {
			load_control_panel_menu( element, 'thrive_quiz_builder_shortcode' );
			return true;
		}
		return false;
	};

	TVE_Content_Builder.ext.tqb = {
		fetch_quiz_content: function ( $btn, $element, e ) {

			this.quiz_ajax( {
				quiz_id: $( '#tve_qb_quiz' ).val()
			} ).done( function ( response ) {
				if ( response ) {
					var $tve_editor = $( '#tve_editor' );

					if ( $tve_editor.find( '.thrive-quiz-builder-shortcode.edit_mode' ).length ) {
						$tve_editor.find( '.thrive-quiz-builder-shortcode.edit_mode' ).replaceWith( response );
					} else {
						$tve_editor.find( '.tve_custom_html_placeholder.tve_active_lightbox' ).replaceWith( response );
					}
				}

				TVE_Content_Builder.controls.lb_close();
				TVE_Editor_Page.overlay( 'close' );
			} );

		},
		quiz_ajax: function ( data, ajax_param ) {
			var params = {
				type: 'post',
				dataType: 'json',
				url: tqb_page_data.ajaxurl
			};
			TVE_Editor_Page.overlay();
			data.action = 'tqb_frontend_ajax_controller';
			data.route = 'shortcode';
			data.tqb_in_tcb_editor = 'inside_tcb';
			data._nonce = tqb_page_data.security;
			params.data = data;

			if ( ajax_param ) {
				for ( var k in ajax_param ) {
					params[k] = ajax_param[k];
				}
			}

			return jQuery.ajax( params, data );
		}
	}
})( jQuery );