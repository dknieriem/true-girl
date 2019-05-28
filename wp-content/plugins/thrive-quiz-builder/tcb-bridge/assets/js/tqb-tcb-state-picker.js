(function ( $ ) {
	$( function () {
		var $state_picker = $( '#tqb-state-picker' ),
			$tve_editor = ThriveGlobal.$j( tve_frontend_options.is_editor_page ? '#tve_editor' : "body" );

		$( '#tqb-draggable-state-area' ).draggable( {
			containment: 'window',
			scroll: false
		} );

		$tve_editor.find( '.tqb-dynamic-content-container' ).html( interval_content[$state_picker.val()] );
		$tve_editor.on( 'click', '.tqb-social-share-badge-container .tve_s_link', function () {
			var $element = ThriveGlobal.$j( this ).parents( '.tve_s_item' ),
				_type = $element.attr( 'data-s' );

			TCB_Front.onSocialCustomClick[_type] && TCB_Front.onSocialCustomClick[_type]( $element );
		} );

		$state_picker.change( function () {
			var _key = jQuery( this ).val();
			$tve_editor.find( '.tqb-dynamic-content-container' ).html( interval_content[_key] );
		} );

	} );
})( jQuery );