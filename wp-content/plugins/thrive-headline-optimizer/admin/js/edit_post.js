ThriveHead = ThriveHead || {};

jQuery( document ).ready( function () {
	jQuery( '.tho-has-test' ).each( function () {
		var element = jQuery( this ),
			parent = element.parent();
		element.detach();
		parent.prepend( element );
	} );
} );

if ( typeof ThriveHead.hasActiveTest !== 'undefined' && ThriveHead.hasActiveTest != 1 ) {

	var variations = [];
	jQuery( document ).ready( function () {
		                  populateVariationArr();
	                  } )
	                  .on( 'blur', '.variation_field', function () {
		                  checkForDuplicateVariations();
	                  } )
	                  .keypress( function ( e ) {
		                  if ( e.which == 13 ) {
			                  var target = e.target;
			                  if ( jQuery( target ).hasClass( 'variation_field' ) && jQuery( target ).val() != '' ) {
				                  e.preventDefault();
				                  addVariation();
				                  jQuery( '#tho_headline_variation_container' ).find( '.variation_field' ).last().focus();
			                  } else {
				                  if ( target.type != 'textarea' ) {
					                  return false;
				                  }
			                  }
		                  }
	                  } );

	/**
	 * Methor for checking for duplicate variations
	 */
	function checkForDuplicateVariations() {
		variations = [];
		populateVariationArr();
		var i, j,
			n = variations.length,
			invalid = false;

		for ( i = 0; i < n; i ++ ) {
			for ( j = i + 1; j < n; j ++ ) {
				if ( variations[i] == variations[j] && variations[i] != '' ) {
					invalid = true;
					jQuery( "#tho_headline_variation_container" ).find( ".variation_field[data-value='" + addslashes( variations[i] ) + "']" ).css( 'border-color', '#e74c3c' );
				}
			}
		}

		if ( invalid ) {
			jQuery( '#save-post' ).prop( "disabled", true );
			jQuery( '#publish' ).prop( "disabled", true );
			jQuery( '#tho_error_notice' ).removeClass( 'hidden' );
		} else {
			jQuery( '#save-post' ).prop( "disabled", false );
			jQuery( '#publish' ).prop( "disabled", false );
			jQuery( '#tho_error_notice' ).addClass( 'hidden' );
		}
	}

	/**
	 * Populates variation array
	 */
	function populateVariationArr() {
		if ( jQuery( "#title" ).val() != '' ) {
			variations.push( addslashes( jQuery( "#title" ).val() ) );
		}
		jQuery( ".variation_field" ).each( function () {
			jQuery( this ).attr( 'data-value', addslashes( jQuery( this ).val() ) );
			jQuery( this ).css( 'border-color', '' );
			variations.push( addslashes( this.value ) );
		} );

		/*Remove the last value because it will be empty all the time. It comes from the template*/
		variations.pop();
	}

	/**
	 * Add a variation
	 */
	function addVariation() {
		var clone = jQuery( '#tho_headline_variation_template' ).find( '.tho_headline_variation' ).clone();
		jQuery( '#tho_headline_variation_container' ).append( clone );
		jQuery( '.tho-add-variation-btn' ).attr( "data-tooltip", ThriveHead.addedVariationsText );
		jQuery( '#tho_error_cache_notice' ).removeClass( 'hidden' );
	}

	/**
	 * Delete a variation
	 * @param elem
	 */
	function deleteVariation( elem ) {
		jQuery( elem ).parents( '.tho_headline_variation' ).remove();
		checkForDuplicateVariations();
	}

	function addslashes( str ) {
		return (
			str + ''
		).replace( /[\\"']/g, '\\$&' ).replace( /\u0000/g, '\\0' );
	}
}