/**
 * Views Maps Distance Filter GUI - script
 *
 * Adds basic interaction for the Distance Filter
 *
 * @package Views Addon Maps
 *
 * @since 1.4.0
 */


var WPViews = WPViews || {};

WPViews.DistanceFilterGUI = function ( $ ) {

    var self = this;

	const API_GOOGLE = 'google';

    self.view_id = $('.js-post_ID').val();

    self.spinner = '<span class="wpv-spinner ajax-loader"></span>';

    self.post_row							    = '.js-wpv-filter-row-maps-distance';
    self.post_options_container_selector	    = '.js-wpv-filter-maps-distance-options';
    self.post_summary_container_selector	    = '.js-wpv-filter-maps-distance-summary';
    self.post_edit_open_selector		    	= '.js-wpv-filter-maps-distance-edit-open';
    self.post_close_save_selector			    = '.js-wpv-filter-maps-distance-edit-ok';
    self.map_distance_center_selector		    = '.js-toolset-google-map-geocomplete-added';
    self.map_distance_center_map_selector       = '.js-toolset-google-map-preview';
    self.map_distance_input_selector            = '.js-wpv-filter-maps-distance';
    self.map_distance_unit_input_selector       = '.js-wpv-filter-maps-distance-unit';
    self.map_distance_center_lat_selector       = '.js-toolset-google-map-lat';
    self.map_distance_center_lng_selector       = '.js-toolset-google-map-lon';
    self.map_distance_center_controls_selector  = '.js-filter-maps-center-controls';
    self.map_distance_center_controls_link      = '.js-filter-maps-center-controls-link';
    self.map_distance_center_source_selector    = '.js-filter-center-source';

    self.post_current_options = $( self.post_options_container_selector + ' input, ' + self.post_options_container_selector + ' select' ).serialize();

    self.map_in_use = null;
    self.marker_in_use = null;
    self.radius_circle = null;
    self.is_using_current_location = false;
    self.current_location = null;
    self.current_lat = null;
    self.current_long = null;

    self.add_validation_flags = function() {
        $('.js-distance-center-shortcode').addClass('js-wpv-filter-validate').data('type', 'shortcode');
        $('.js-distance-center-url-param').addClass('js-wpv-filter-validate').data('type', 'url');
        $(self.map_distance_input_selector).addClass('js-wpv-filter-validate').data('type', 'numeric_natural');
    };

    self.save_filter_maps_distance = function( event, propagate ) {
        var thiz = $( self.post_close_save_selector );
        WPViews.query_filters.clear_validate_messages( self.post_row );

        if ( self.post_current_options == $( self.post_options_container_selector + ' input, ' + self.post_options_container_selector + ' select' ).serialize() ) {
            WPViews.query_filters.close_filter_row( self.post_row );
            thiz.hide();
        } else {
            var valid = WPViews.query_filters.validate_filter_options( '.js-filter-maps-distance' );
            if ( valid ) {
                var action = thiz.data( 'saveaction' ),
                    nonce = thiz.data('nonce'),
                    spinnerContainer = $( self.spinner ).insertBefore( thiz ).show(),
                    error_container = thiz
                        .closest( '.js-filter-row' )
                        .find( '.js-wpv-filter-toolset-messages' );
                self.post_current_options = $( self.post_options_container_selector + ' input, ' + self.post_options_container_selector + ' select' ).serialize();
                var data = {
                    action:			action,
                    id:				self.view_id,
                    filter_options:	self.translateOptionFieldNames( self.post_current_options ),
                    wpnonce:		nonce
                };

                $.post( ajaxurl, data, function( response ) {
                    if ( response.success ) {
                        $( self.post_close_save_selector )
                            .addClass('button-secondary')
                            .removeClass('button-primary js-wpv-section-unsaved')
                            .html(
                                WPViews.query_filters.icon_edit + $( self.post_close_save_selector ).data( 'close' )
                            );
                        $( self.post_summary_container_selector ).html( response.data.summary );
                        WPViews.query_filters.close_and_glow_filter_row( self.post_row, 'wpv-filter-saved' );
                        $( document ).trigger( event );
                        if ( propagate ) {
                            $( document ).trigger( 'js_wpv_save_section_queue' );
                        } else {
                            $( document ).trigger( 'js_event_wpv_set_confirmation_unload_check' );
                        }
                    } else {
                        Toolset.hooks.doAction( 'wpv-action-wpv-edit-screen-manage-ajax-fail', { data: response.data, container: error_container} );
                        if ( propagate ) {
                            $( document ).trigger( 'js_wpv_save_section_queue' );
                        }
                    }
                }, 'json' )
                    .fail( function( jqXHR, textStatus, errorThrown ) {
                        console.log( "Error: ", textStatus, errorThrown );
                        Toolset.hooks.doAction( 'wpv-action-wpv-edit-screen-manage-save-fail-queue', 'save_filter_maps_distance' );
                        if ( propagate ) {
                            $( document ).trigger( 'js_wpv_save_section_queue' );
                        }
                    })
                    .always( function() {
                        spinnerContainer.remove();
                        thiz.hide();
                    });
            } else {
                Toolset.hooks.doAction( 'wpv-action-wpv-edit-screen-manage-save-fail-queue', 'save_filter_maps_distance' );
                if ( propagate ) {
                    $( document ).trigger( 'js_wpv_save_section_queue' );
                }
            }
        }
    };

	/**
	 * Translates input field names from Maps Editor to what backend expects.
	 * @param {String} options
	 * @return {String}
	 */
	self.translateOptionFieldNames = function( options ) {
	    return options.replace( 'map_center_address', 'map_distance_center' )
		    .replace( 'toolset-extended-form-map_center_address%5Blatitude%5D', 'map_center_lat' )
		    .replace( 'toolset-extended-form-map_center_address%5Blongitude%5D', 'map_center_lng' );
	};

    self.eventsOn = function() {
        //Add necessary event listeners
        $( document ).on( 'click', self.post_close_save_selector, function() {
            self.save_filter_maps_distance( 'js_event_wpv_save_filter_maps_distance_completed', false );
        });

        $( document ).on( 'click', self.post_edit_open_selector, function() {
            self.initDialogInputs();
        });

        $( document ).on( 'js_event_wpv_query_filter_created', function( event, filter_type ) {
            if ( filter_type == 'maps_distance' ) {
                Toolset.hooks.doAction( 'wpv-action-wpv-edit-screen-manage-save-queue', { section: 'save_filter_maps_distance', action: 'add' } );
                self.initDialogInputs();
            }
        });

        $( document ).on( 'toolset_maps_views_distance_filter_center_updated', function(event, data) {
            self.showOrHideMapPreview( event );
        });

        $( document ).on( 'js_event_wpv_query_filter_deleted', function( event, filter_type ) {
            if ( 'maps_distance' == filter_type ) {
                self.clear_save_queue();
            }
        });
    };

	self.clear_save_queue = function() {
		self.map_in_use = null;
	};

    self.init = function() {
        self.eventsOn();
        self.add_validation_flags();
        jQuery(document).on('js_event_wpv_query_filter_created', function() {
            self.eventsOn();
        });
    };

    self.initDialogInputs = function () {
        $( document ).on( 'change', self.map_distance_unit_input_selector, function() {
            self.drawRadiusCircle();
        });

        $( document ).on( 'keyup', self.map_distance_input_selector, function() {
            self.drawRadiusCircle();
        });

        $( document ).on( 'click', self.map_distance_center_controls_link, function(event) {
            event.preventDefault();
            $( self.map_distance_center_controls_selector ).toggle();

            var current_button_text = $(self.map_distance_center_controls_link).text();
            current_button_text = (current_button_text == "Show Coordinates" ? "Hide Coordinates" : "Show Coordinates");
            $(self.map_distance_center_controls_link).text(current_button_text);

        });

        $( document ).on( 'geocode:result', self.map_distance_center_selector, function(evt, data) {
            var addressLat  = data.geometry.location.lat();
            var addressLng = data.geometry.location.lng();

            self.is_using_current_location = false;

            self.updateFilterCenter({
                location: {
                    latitude: addressLat,
                    longitude: addressLng
                },
                address_update: true
            });
        });

        $( document ).on(
        	'change',
	        self.map_distance_center_source_selector,
	        self.showOrHideMapPreview
        );

        //Set current lat and lng from saved items
        self.current_lat  = $( self.map_distance_center_lat_selector ).val();
        self.current_long = $( self.map_distance_center_lng_selector ).val();

        // Initial show of preview if needed
        self.showOrHideMapPreview( {type: 'initial'} );
    };

	/**
	 * Because empty map as "preview" looks ugly, so don't show it if there is no center yet.
	 * @param {Event|Object} event
	 */
    self.showOrHideMapPreview = function( event ) {
	    var map_distance_center = $( self.map_distance_center_selector );
	    var map_distance_center_map = $( self.map_distance_center_map_selector );

		// If we aren't using fixed location, just hide the preview.
		if (
			event.type === 'change' && event.target.value !== 'address'
			|| event.type === 'initial' && $( self.map_distance_center_source_selector+':checked' ).val() !== 'address'
		) {
			map_distance_center_map.hide();
			return;
		}

	    map_distance_center_map.show();

		// Lazy initialize the map only when we actually need it for the 1st time.
		if ( !self.map_in_use ) {
			if ( API_GOOGLE === views_addon_maps_i10n.api_used ) {
				WPViews.addon_maps_editor.init();
			} else {
				WPViews.mapsAddressAutocomplete.init();
            }

			// We can only get this data once there is an address and the map is drawn.
			if (map_distance_center.length) {
				self.map_in_use = map_distance_center.geocomplete('map');
				self.marker_in_use = map_distance_center.geocomplete('marker');
			}
		}

		if (map_distance_center.length && map_distance_center[0].value) {
			self.setPreviewMapCenter();
		}
	};

    self.drawRadiusCircle = function () {
        if (self.map_in_use !== null) {

            if(self.radius_circle != null) {
                self.radius_circle.setMap(null);
            }

            self.radius_circle = new google.maps.Circle({
                map: self.map_in_use,
                radius: self.getSimpleDistance(),    // 10 miles in metres
                fillColor: '#AA0000'
            });

            self.radius_circle.bindTo('center', self.marker_in_use, 'position');
        }
    }

    self.getSimpleDistance = function () {
        var distance_value = $( self.map_distance_input_selector ).val();
        var distance_unit  = $( self.map_distance_unit_input_selector ).val();

        if(!isNaN(distance_value)) {
            switch(distance_unit) {
                case 'km':
                    return distance_value * 1000;
                    break;
                case 'mi':
                    return distance_value * 1609.344;
                    break;
            }
        }

        return 0;
    }

    self.acquireCurrentLocation = function() {
        navigator.geolocation.getCurrentPosition(self.updateFilterCenter);
    };

    self.updateFilterCenter = function(position) {
        if(position.coords) {
            self.is_using_current_location = true;
            $( self.map_distance_center_selector ).val(position.coords.latitude + ', ' + position.coords.longitude);

            self.current_lat  = position.coords.latitude;
            self.current_long = position.coords.longitude;
        }

        if(position.address_update) {
            self.current_lat  = position.location.latitude;
            self.current_long = position.location.longitude;
        }

        //Update center lat/lng inputs
        $( self.map_distance_center_lat_selector ).val( self.current_lat );
        $( self.map_distance_center_lng_selector ).val( self.current_long );

        $( document ).trigger('toolset_maps_views_distance_filter_center_updated', {
            position: position
        });
    };

    self.isSecurePage = function () {
        return (location.protocol === 'https:');
    }

    self.setPreviewMapCenter = function() {
        var latLng = new google.maps.LatLng(self.current_lat, self.current_long);
        self.map_in_use.setCenter( latLng );
        self.marker_in_use.setPosition( latLng );

        self.drawRadiusCircle();
    };

    self.init();
}

jQuery( document ).ready( function( $ ) {
    WPViews.distance_filter_gui = new WPViews.DistanceFilterGUI( $ );
});
