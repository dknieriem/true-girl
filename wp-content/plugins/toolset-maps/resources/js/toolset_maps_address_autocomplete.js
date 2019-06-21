var WPViews = WPViews || {};

/**
 * Address autocomplete component. Uses Azure API.
 *
 * @param jQuery $
 * @constructor
 * @since 1.5
 */
WPViews.MapsAddressAutocomplete = function( $ ) {
	"use strict";

	let self = this;

	/** @var {Object} toolset_maps_address_autocomplete_i10n */

	const ADDRESS_AUTOCOMPLETE_SELECTOR = '.js-toolset-maps-address-autocomplete';
	const AUTOCOMPLETE_INITED_SELECTOR = '.ui-autocomplete-input';
	const AUTOCOMPLETE_SETTINGS = {
		source: function (request, response) {
			$.get({
				url: "https://atlas.microsoft.com/search/address/json",
				data: {
					'subscription-key': toolset_maps_address_autocomplete_i10n.azure_api_key,
					'api-version': '1.0',
					typeahead: true,
					query: request.term
				},
				success: function (data) {
					let positions = _.pluck( data.results, 'position' );

					let addresses = _.pluck( data.results, 'address' );
					let freeFormAddresses = _.pluck( addresses, 'freeformAddress' );

					self.latLngCache = _.object( freeFormAddresses, positions );

					response(freeFormAddresses);
				}
			});
		},
		minLength: 2,
		select: function( event, ui ) {
			let $container = $( event.target ).closest('.js-toolset-google-map-inputs-container');

			// If there is a container, then this is an address editor component, and not just a stand alone
			// autocomplete. In that case, update lat/lon and map.
			if ( $container.length ) {
				let position = self.latLngCache[ui.item.value];

				self.updateLatlonValues($container, position.lat, position.lon, 'address');
			}
		}
	};
	const MAP_EDITOR_CONTAINER = '.js-toolset-google-map-container';
	const MAP_EDITOR_INPUTS_CONTAINER = '.js-toolset-google-map-inputs-container';

	// Latitude and longitude validation regex
	self.validateLat = /^(-?([0-9]|8[0-4]|[1-7][0-9])(\.{1}\d{1,20})?)$/;
	self.validateLon = /^-?([0-9]|[1-9][0-9]|[1][0-7][0-9]|180)(\.{1}\d{1,20})?$/;

	// Counters
	self.mapCounter = 0;

	// Maps
	self.maps = {};

	// Lat/lng cache
	self.latLngCache = {};

	/**
	 * @param {Number} lat
	 * @return {boolean}
	 */
	self.isValidLatitude = function( lat ) {
		return self.validateLat.test( lat );
	};

	/**
	 * @param {Number} lon
	 * @return {boolean}
	 */
	self.isValidLongitude = function( lon ) {
		return self.validateLon.test( lon );
	};

	/**
	 * Checks if current page is on a secure connection.
	 *
	 * @return {boolean}
	 *
	 * @since 1.5.3
	 */
	self.isSecurePage = function () {
		return ( location.protocol === 'https:' );
	};

	/**
	 * Inits all the events for this module
	 */
	self.initEvents = function() {
		// Init address field(s) on toolset_ajax_fields_loaded event.
		$( document ).on( 'toolset_ajax_fields_loaded', function( event, form ) {
			self.initFieldsInsideContainer( $( 'form#' + form.form_id ) );
			self.initMapEditorComponentsInsideContainer( $( 'form#' + form.form_id ) );
		});

		// Reacts to event triggered by Types after the fields are loaded by ajax (too late for regular init).
		$( document ).on( 'toolset_types_rfg_item_toggle', function( event, item ) {
			if ( item.visible() ) {
				self.initAllFields();
				self.initMapEditorComponents();
			}
		});

		// Adds autocomplete to an added repetitive field
		$( document ).on( 'toolset_repetitive_field_added', function( event, parent ) {
			self.initFieldsInsideContainer( $( parent ) );
			self.initMapEditorComponentsInsideContainer( $( parent ) );
		});

		// Toogle container for the latitude and longitude inputs
		$( document ).on( 'click', '.js-toolset-google-map-toggle-latlon', function( event ) {
			event.preventDefault();

			let $this = $( this ),
				$this_container = $this.closest( '.js-toolset-google-map-inputs-container' ),
				$this_toggling = $this_container.find( '.js-toolset-google-map-toggling-latlon' );

			$this_toggling.slideToggle( 'fast' );
		});

		// Update latitude and longitude values when editing the values of latitude or longitude inputs
		$( document ).on( 'input cut paste', '.js-toolset-google-map-latlon', function( event ) {
			let $container = $( this ).closest( '.js-toolset-google-map-inputs-container' ),
				latVal = $container.find( '.js-toolset-google-map-lat' ).val(),
				lonVal = $container.find( '.js-toolset-google-map-lon' ).val();

			self.updateLatlonValues( $container, latVal, lonVal, 'address' );
		});

		// Update latitude and longitude values when editing the values of the address input if it uses the {lat,lon}
		// format
		$( document ).on( 'input cut paste', ADDRESS_AUTOCOMPLETE_SELECTOR, function( event ) {
			let $this = $( this ),
				thisVal = $this.val();

			if (
				thisVal
				&& thisVal.match("^{")
				&& thisVal.match("}$")
			) {
				let thisCoords = thisVal.slice( 1, -1 ),
					thisLocation = thisCoords.split( ',' ),
					$thisContainer = $this.closest( '.js-toolset-google-map-inputs-container' );
				if ( thisLocation.length === 2 ) {
					self.updateLatlonValues( $thisContainer, thisLocation[0], thisLocation[1], 'latlon' );
				}
			}
		});

		// Fills latitude & longitude fields with current user location got from browser
		$( document ).on( 'click', '.js-toolset-google-map-use-visitor-location', function( event ) {
			event.preventDefault();

			let $container = $( this ).closest('.js-toolset-google-map-inputs-container');

			navigator.geolocation.getCurrentPosition(
				function (position) {
					self.updateLatlonValues( $container, position.coords.latitude, position.coords.longitude, 'both' );
				},
				function (position_error) {
					console.warn( position_error.message );
				}
			);
		});

		// Applies the closest address provided
		$( document ).on( 'click', '.js-toolset-google-map-preview-closest-address-apply', function( event ) {
			event.preventDefault();

			let $this = $( this ),
				$thisContainer = $this.closest( '.js-toolset-google-map-container' ),
				$thisAddress = $thisContainer.find( '.js-toolset-google-map-preview-closest-address-value' ),
				$lat = $thisContainer.find( '.js-toolset-google-map-lat' ),
				$lon = $thisContainer.find( '.js-toolset-google-map-lon' ),
				$address = $thisContainer.find( '.js-toolset-google-map' );

			$lat.val( $thisAddress.data( 'lat' ) );
			$lon.val( $thisAddress.data( 'lon' ) );
			$address
				.val( $thisAddress.html() )
				.data( 'coordinates', "{" + $thisAddress.data( 'lat' ) + ',' + $thisAddress.data( 'lon' ) + "}" )
				.trigger( 'js_event_toolset_latlon_values_updated' );

			self.glowSelectors( $address, 'toolset-being-updated' );
			self.glowSelectors( $lat, 'toolset-being-updated' );
			self.glowSelectors( $lon, 'toolset-being-updated' );

			$thisContainer
				.find( '.toolset-google-map-preview-closest-address' )
				.slideUp( 'fast' );
		});

		// Re-init address fields on Toolset forms after they get submitted using AJAX.
		$( document ).on( 'js_event_cred_ajax_form_response_completed', function( event ) {
			self.initAllFields();
			self.initMapEditorComponents();
		});
	};

	/**
	 * Update the address, latitude and longitude fields on a simple container, and maybe force the preview reload
	 *
	 * @param $container		{jQuery}	The container for the given address field instance
	 * @param latVal			{Number}	The new latitude value
	 * @param lonVal			{Number}	The new longitude value
	 * @param updateMainTarget	{string}	The reason for the update, the fields that will get new values
	 *
	 * @since 1.5.3
	 */
	self.updateLatlonValues = function( $container, latVal, lonVal, updateMainTarget ) {
		let $lat = $container.find('.js-toolset-google-map-lat'),
			$lon = $container.find('.js-toolset-google-map-lon'),
			$address = $container.find('.js-toolset-google-map'),
			$thisToggling = $container.find('.js-toolset-google-map-toggling-latlon');

		$container
			.find('.js-toolset-latlon-error')
				.removeClass('toolset-latlon-error js-toolset-latlon-error');

		if ( !self.isValidLatitude( latVal ) ) {
			$lat.addClass('toolset-latlon-error js-toolset-latlon-error');
			$address.trigger('js_event_toolset_latlon_values_error');
		} else if ( !self.isValidLongitude( lonVal ) ) {
			$lon.addClass('toolset-latlon-error js-toolset-latlon-error');
			$address.trigger('js_event_toolset_latlon_values_error');
		} else {
			$lat.val( latVal );
			$lon.val( lonVal );
			$address
				.val( "{" + latVal + ',' + lonVal + "}" )
				.data( 'coordinates', "{" + latVal + ',' + lonVal + "}" )
				.trigger( 'js_event_toolset_latlon_values_updated' );

			// Update map preview, if there is one
			let mapId = $container.siblings('.js-toolset-google-map-preview').first().data('id');
			if ( typeof mapId !== 'undefined' ) {
				self.movePin(self.maps[mapId], [lonVal, latVal]);
			}

			if ( updateMainTarget === 'address' ) {
				self.glowSelectors( $address, 'toolset-being-updated' );
			} else if ( updateMainTarget === 'latlon' ) {
				$thisToggling.slideDown( 'fast', function() {
					self.glowSelectors( $lat, 'toolset-being-updated' );
					self.glowSelectors( $lon, 'toolset-being-updated' );
				});
			} else if ( updateMainTarget === 'both' ) {
				$thisToggling.slideDown( 'fast', function() {
					self.glowSelectors( $address, 'toolset-being-updated' );
					self.glowSelectors( $lat, 'toolset-being-updated' );
					self.glowSelectors( $lon, 'toolset-being-updated' );
				});
			}
		}
	};

	/**
	 * Glow a given selector for a given reason
	 *
	 * @param selectors	    {Object}		The selectors that will glow
	 * @param reason		{string}		The reason for the glow, as a classname
	 *
	 * @since 1.5.3
	 */
	self.glowSelectors = function( selectors, reason ) {
		$( selectors ).addClass( reason );
		setTimeout( function () {
			$( selectors ).removeClass( reason );
		}, 500 );
	};

	/**
	 * Init just the given field
	 * @param jQuery $field
	 */
	self.initField = function( $field ) {
		if ( ! $field.hasClass( AUTOCOMPLETE_INITED_SELECTOR ) ) {
			$field.autocomplete( AUTOCOMPLETE_SETTINGS );
		}
	};

	/**
	 * Init all uninited fields inside given container
	 * @param jQuery $container
	 */
	self.initFieldsInsideContainer = function( $container ) {
		$container
			.find( ADDRESS_AUTOCOMPLETE_SELECTOR )
			.not( AUTOCOMPLETE_INITED_SELECTOR )
			.autocomplete( AUTOCOMPLETE_SETTINGS );
	};

	/**
	 * Inits all the fields with ADDRESS_AUTOCOMPLETE_SELECTOR class
	 */
	self.initAllFields = function() {
		self.initFieldsInsideContainer( $( document ) );
	};

	/**
	 * Inits all the other stuff (except address field) that belongs to map editor: lat/lng fields, map & geolocation.
	 * @since 1.5.3
	 */
	self.initMapEditorComponents = function() {
		self.initMapEditorComponentsInsideContainer( $( document ) );
	};

	/**
	 * @param jQuery container
	 */
	self.initMapEditorComponentsInsideContainer = function( $container ) {
		$container
			.find( MAP_EDITOR_CONTAINER )
				.each( function( index, container ) {
					let $container = $( container );
					let $previewContainer = $container.children( '.js-toolset-google-map-preview' );

					// Only init newly added previews
					if ( !$previewContainer.length ) {
						let $inputsContainer = $container.children(MAP_EDITOR_INPUTS_CONTAINER).first();
						let latLng = self.getLatLngFromAutocomplete($inputsContainer);

						$container.append(self.getPreviewStructure(self.mapCounter));
						self.maps[self.mapCounter] = self.initMapPreview(self.mapCounter, latLng);
						self.mapCounter++;
					}
				} );

		$container
			.find( MAP_EDITOR_INPUTS_CONTAINER )
				.not( '.js-toolset-google-map-inputs-container-inited' )
					.each( function( index, container ) {
						let $container = $( container );
						let latLng = self.getLatLngFromAutocomplete( $container );

						$container.append( self.getInputsStructure( latLng[0], latLng[1] ) );
						$container.addClass( 'js-toolset-google-map-inputs-container-inited' );
					} );
	};

	/**
	 * @param jQuery $container
	 * @return {array}
	 */
	self.getLatLngFromAutocomplete = function( $container ) {
		let $autocomplete = $container.children( '.js-toolset-maps-address-autocomplete' ).first();
		let coordinates = $autocomplete.data( 'coordinates' );

		if ( coordinates ) {
			return coordinates.slice( 1, -1 ).split( ',' );
		} else {
			return ['', ''];
		}
	};

	/**
	 * This module is a dependency of other modules, and is loaded in lots of places. Since it's difficult to keep track
	 * of all the cases where it's used from server-side, it's easiest to simply have it carry its own HTML with it.
	 * @since 1.5.3
	 */
	self.initTemplates = function() {
		/**
		 * @param {string} lat
		 * @param {string} lng
		 * @return {string}
		 */
		self.getInputsStructure = function( lat, lng ) {
			let inputsStructure = '<a class="toolset-google-map-toggle-latlon js-toolset-google-map-toggle-latlon">'
				+ toolset_maps_address_autocomplete_i10n.showhidecoords
				+ '</a>';
			if (navigator.geolocation && self.isSecurePage()) {
				inputsStructure += ' | <a class="toolset-google-map-use-visitor-location js-toolset-google-map-use-visitor-location">'
					+ toolset_maps_address_autocomplete_i10n.usemylocation
					+ '</a>';
			}
			inputsStructure += '<div class="js-toolset-google-map-toggling-latlon toolset-google-map-toggling-latlon" style="display:none"><p><label for="toolset-google-map-lat" class="toolset-google-map-label js-wpt-auxiliar-label">'
				+ toolset_maps_address_autocomplete_i10n.latitude
				+ '</label><input id="toolset-google-map-lat" class="js-toolset-google-map-latlon js-toolset-google-map-lat toolset-google-map-lat" type="text" value="'
				+ lat
				+ '" /></p>'
				+ '<p><label for="toolset-google-map-lon" class="toolset-google-map-label js-wpt-auxiliar-label">'
				+ toolset_maps_address_autocomplete_i10n.longitude
				+ '</label><input id="toolset-google-map-lon" class="js-toolset-google-map-latlon js-toolset-google-map-lon toolset-google-map-lon" type="text" value="'
				+ lng
				+ '" /></p></div>';
			return inputsStructure;
		};

		/**
		 *
		 * @param {int} counter
		 * @return {string}
		 */
		self.getPreviewStructure = function ( counter ) {
			return '<div id="js-toolset-maps-preview-map-'
				+ counter
				+ '" class="toolset-google-map-preview js-toolset-google-map-preview" data-id="'
				+ counter
				+ '" style="background-image: none;"></div>'
				+ '<div style="display:none;" class="toolset-google-map-preview-closest-address js-toolset-google-map-preview-closest-address"><div style="padding:5px 10px 10px;">'
				+ toolset_maps_address_autocomplete_i10n.closestaddress
				+ '<span class="toolset-google-map-preview-closest-address-value js-toolset-google-map-preview-closest-address-value"></span><br /><button class="button buton-secondary button-small js-toolset-google-map-preview-closest-address-apply">'
				+ toolset_maps_address_autocomplete_i10n.usethisaddress
				+ '</button></div></div>';
		};
	};

	/**
	 * There is no library like geocomplete for Azure, so we are doing preview map ourselves here.
	 * @param {int} counter
	 * @param {array} latLng
	 * @return {atlas.Map}
	 */
	self.initMapPreview = function( counter, latLng ) {
		let mapId = "js-toolset-maps-preview-map-" + counter;
		let $mapDiv = $( '#' + mapId );
		let mapCenter = [latLng[1], latLng[0]];

		// Render map
		let renderedMap = new atlas.Map( mapId, {
			"subscription-key": toolset_maps_address_autocomplete_i10n.azure_api_key,
			center: mapCenter,
			zoom: 10,
		} );

		// If there is an already saved location, render the pin too
		if ( !_.isEmpty( _.filter( latLng ) ) ) {
			let pin = new atlas.data.Feature(new atlas.data.Point(mapCenter));

			renderedMap.addEventListener('load', function () {
				renderedMap.addPins([pin], {
					icon: "pin-red",
					name: "default-pin-layer"
				});
			});
		}

		renderedMap.getCanvasContainer().style.cursor = "pointer";

		// Upon a mouse click, show lat/lng of the click, and get the closest address
		renderedMap.addEventListener( "click", function( event ) {
			// Send a request to Azure Maps reverse address search API
			let xhttp = new XMLHttpRequest();
			let url = "https://atlas.microsoft.com/search/address/reverse/json?"
				+ "&api-version=1.0"
				+ "&query=" + event.position[1] + "," + event.position[0]
				+ "&subscription-key=" + toolset_maps_address_autocomplete_i10n.azure_api_key;
			xhttp.open( "GET", url, true );
			xhttp.send();

			$mapDiv
				.siblings( ' .js-toolset-google-map-preview-closest-address' )
					.slideDown( 'fast' );

			self.movePin( renderedMap, event.position );
			self.updateLatlonValues( $mapDiv.parent(), event.position[1], event.position[0], 'both' );

			// Parse the API response and show click location address
			xhttp.onreadystatechange = function() {
				if (this.readyState === 4 && this.status === 200) {
					let response = JSON.parse(xhttp.responseText);
					let address = response["addresses"];
					let freeformAddress = address.length !== 0
							? address[0]["address"]["freeformAddress"]
							: "No address for that location!";
					$mapDiv
						.siblings( '.js-toolset-google-map-preview-closest-address' )
							.find( '.js-toolset-google-map-preview-closest-address-value' )
								.text( freeformAddress )
								.data( 'lat', event.position[1] )
								.data( 'lon', event.position[0] );
				}
			}
		} );

		return renderedMap;
	};

	/**
	 * Moves the pin and map center on a given map to given coordinates
	 * @param atlas.Map map
	 * @param {array} coordinates
	 */
	self.movePin = function( map, coordinates ) {
		let pin = new atlas.data.Feature( new atlas.data.Point( coordinates ) );
		map.addPins( [pin], {
			icon: "pin-red",
			name: "default-pin-layer",
			overwrite: true
		} );
		map.setCamera( {center: coordinates} );
	};

	self.init = function() {
		self.initTemplates();
		self.initAllFields();
		self.initMapEditorComponents();
		self.initEvents();
	};

	self.init();
};

jQuery( document ).ready( function( $ ) {
	WPViews.mapsAddressAutocomplete = new WPViews.MapsAddressAutocomplete( $ );
});