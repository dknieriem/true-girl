/**
 * Thrive Dashboard frontend script
 * @var {Object} tve_dash_front
 */
var TVE_Dash = TVE_Dash || {};
var ThriveGlobal = ThriveGlobal || {$j: jQuery.noConflict()};
(function ( $ ) {
	TVE_Dash.ajax_sent = false;
	var ajax_data = {},
		callbacks = {};

	/**
	 * add a load item - this will be sent on the initial ajax load
	 *
	 * @param {string} tag unique identifier for ajax action
	 * @param {object} data object
	 * @param {Function} [callback] optional callback function to handle the response
	 *
	 * @return {boolean} whether or not the data adding has been successful
	 */
	TVE_Dash.add_load_item = function ( tag, data, callback ) {
		if ( ! data ) {
			console.error && console.error( 'missing ajax data' );
			return false;
		}
		if ( typeof callback !== 'function' ) {
			callback = $.noop;
		}
		if ( ajax_data[tag] ) {
			console.error && console.error( tag + ' ajax action already defined' );
		}
		ajax_data[tag] = data;
		callbacks[tag] = callback;

		return true;
	};

	/**
	 * Append external CSS stylesheets to the head
	 *
	 * @param {Object} list
	 */
	TVE_Dash.ajax_load_css = function ( list ) {
		$.each( list, function ( k, href ) {
			k += '-css';
			if ( ! $( 'link#' + k ).length ) {
				$( '<link rel="stylesheet" id="' + k + '" type="text/css" href="' + href + '"/>' ).appendTo( 'head' );
			}
		} );
	};

	/**
	 * Loads all javascripts from the list
	 *
	 * @param {Object} list
	 */
	TVE_Dash.ajax_load_js = function ( list ) {
		var body = document.body;
		$.each( list, function ( k, src ) {
			if ( k.indexOf( '_before' ) !== - 1 ) {
				return true;
			}
			var script = document.createElement( 'script' );
			if ( list[k + '_before'] ) {
				var l = $( '<script type="text/javascript">' + list[k + '_before'] + '</script>' );
				l.after( body.lastChild );
			}
			if ( k ) {
				script.id = k + '-script';
			}
			script.src = src;
			body.appendChild( script );
		} );
	};

	$( function () {
		setTimeout( function () {
			var evt = new $.Event( 'tve-dash.load' );
			$( document ).trigger( evt );
			/* if no ajax-data has been registered, do not make the ajax call */
			if ( $.isEmptyObject( ajax_data ) ) {
				return false;
			}
			/* We don't need to run this initial AJAX request if a bot is currently crawling the site - performance improvement */
			if ( tve_dash_front.is_crawler ) {
				return false;
			}
			$.ajax( {
				url: tve_dash_front.ajaxurl,
				data: {
					action: 'tve_dash_front_ajax',
					tve_dash_data: ajax_data
				},
				dataType: 'json',
				type: 'post'
			} ).done( function ( response ) {
				if ( ! response || ! $.isPlainObject( response ) ) {
					return;
				}
				if ( response.__resources ) {
					if ( response.__resources.css ) {
						TVE_Dash.ajax_load_css( response.__resources.css );
					}
					if ( response.__resources.js ) {
						TVE_Dash.ajax_load_js( response.__resources.js );
					}
					delete response.__resources;
				}
				$.each( response, function ( tag, response ) {
					if ( typeof callbacks[tag] !== 'function' ) {
						return true;
					}
					callbacks[tag].call( null, response );

				} );
			} );
			TVE_Dash.ajax_sent = true;
		} );
	} );
})( ThriveGlobal.$j );
