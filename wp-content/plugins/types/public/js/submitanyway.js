/**
 * Prefixed and slightly modified version for Toolset Types.
 *
 * @license GPL 3.0
 * @link https://github.com/ilisepe1/submitanyway
 */

var Toolset = Toolset || {};
Toolset.Types = Toolset.Types || {};
Toolset.Types.SubmitAnyway = window.SubmitAnyway || {};

Toolset.Types.SubmitAnyway = function ( $ ) {

	//filter form elements with same name
	var merge = function ( els ) {
		var ret = [];
		var merged = [];
		$.each( els, function ( i, el ) {
			//console.log("name=" + el.name + ", type=" + el.type);
			var type = el.type;
			var name = el.name;
			if ( merged.indexOf( name ) < 0 && ( type === "radio" || type === "checkbox" ) ) {
				merged.push( name );
				$( 'input[name="' + name + '"]:checked' ).each( function () {
					// .serialize() ?
					var tmpEl = { "name": name, "value": this.value };
					ret.push( tmpEl );
				} );
			}
			else if ( merged.indexOf( name ) < 0 && type === "select-multiple" || type === "select-one" ) {
				merged.push( name );
				$( 'select[name="' + name + '"] :selected' ).each( function () {
					var tmpEl = { "name": name, "value": this.value };
					ret.push( tmpEl );
				} );
			}

		} );
		return ret;
	};

	//delete temp hidden x-submitanyway-temp elements
	var cleanup = function () {
		$( "[x-submitanyway-temp]" ).remove();
	};

	//the main handler
	var submitanyway = function ( selector ) {
		$( selector ).submit( function () {
			// Had to remove "e.preventDefault();" from here, since it was triggering a "confirmUnload" message
			// in the browser through some TinyMCE dark magic.
			var id = this.id;

			cleanup();

			//get values of form elements with data-submitanyway attributes
			//and add them to form
			var params = merge( $( "[data-submitanyway]" ) );
			//console.log("params=", params);
			if ( params != null && params.length > 0 ) {
				$.each( params, function ( i, param ) {
					// console.log("adding x-temp: name=" + param.name + ", value=" + param.value);
					$( '<input />' ).attr( 'type', 'hidden' )
						.attr( 'name', param.name )
						.attr( 'value', param.value )
						.attr( 'x-submitanyway-temp', "" )
						.appendTo( '#' + id );
				} );
			}
		} );
	};

	return {
		"submitanyway": submitanyway,
		"cleanup": cleanup
	}

}( jQuery );
