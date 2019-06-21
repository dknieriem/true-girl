/**
 * Created by ahmed on 6/7/17.
 */

var WPViews = WPViews || {};

WPViews.DistanceFilterFrontend = function ( $ ) {
	var self = this;

	const API_GOOGLE = 'google';
	const API_AZURE  = 'azure';

    self.distanceValueSelector     = ".js-toolset-maps-distance-value";
    self.distanceUnitSelector      = ".js-toolset-maps-distance-unit";
    self.distanceCenterSelector    = ".js-toolset-maps-distance-center";
    self.userLocationSelector      = ".js-toolset-maps-distance-current-location";
    self.distanceCenterLatSelector = '.js-toolset-maps-distance-center-lat';
    self.distanceCenterLngSelector = '.js-toolset-maps-distance-center-lng';
    self.hiddenCoordsFormSelector  = '#js-toolset-maps-coords-form';

    self.current_lat  = null;
    self.current_long = null;
    self.is_using_current_location = false;
    self.view_id = $('div.js-wpv-view-layout').first().data('viewnumber');

	self.hiddenCoordsForm = '<form id="js-toolset-maps-coords-form" class="js-wpv-ajax-results-enabled"></form>';

    self.eventsOn = function() {
        $( document ).on( 'geocode:result', self.distanceCenterSelector, function(evt, data) {
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

            $( self.userLocationSelector ).prop('disabled', false);
        });

        $( document ).on('toolset_maps_views_distance_filter_center_updated', function() {
            $( self.distanceCenterLatSelector ).val(self.current_lat);
            $( self.distanceCenterLngSelector ).val(self.current_long);

	        if( self.is_using_current_location ) {
		        $( self.distanceCenterSelector ).val( self.getCurrentLatLng() );
	        }

	        // Trigger change event on other selector to start Ajax when using immediate update,
	        // Because distanceCenterSelector is set to not react before proper values are filled in.
	        // (Sending enter key pressed event to distanceCenterSelector doesn't work because geocomplete
	        // library seems to eat it and Views never get it.)
	        $( self.distanceValueSelector ).change();
        });
    };


    self.init = function ( ) {
        self.initFormFunctionality();

        self.eventsOn();
        self.updateQueryFilterWithUserLocation();

        //Check if the browser supports geolocation services and if the site uses ssl
        if ( navigator.geolocation  && self.isSecurePage() ) {
            $( document ).on('click', self.userLocationSelector, function (event) {
                event.preventDefault();
                self.acquireCurrentLocation(self.updateFilterCenter);
            });

            $( document ).on('toolset_maps_views_distance_filter_user_location_acquired', function() {
                $( self.userLocationSelector ).prop('disabled', true);
            });

            // If needed, add hidden form for ajax results loading
	        if ( !self.areDistanceCenterFormElementsPresentOnPage() ) {
	        	$('.js-wpv-view-layout-'+self.view_id).append( self.hiddenCoordsForm );
	        }
        }

	    // Re-init form functionality after ajax pagination
	    $( document ).on('js_event_wpv_pagination_completed', function() {
		    self.initFormFunctionality();
	    });

	    // Re-init form functionality after form updated (reset button)
        $( document ).on('js_event_wpv_parametric_search_form_updated', function( event, data ) {
        	// When using current visitors location, refresh that data
        	if ( self.is_using_current_location ) {
		        $( self.distanceCenterSelector ).val(self.getCurrentLatLng());
		        $( self.userLocationSelector ).prop('disabled', true);
	        }

	        self.initFormFunctionality();
	    });
    };

	/**
	 * Enables extra functionality on custom search form - geocomplete and geolocation
	 */
	self.initFormFunctionality = function() {
		// Only init geocomplete if it's loaded (and that happens only if using Google API)
		if ( $.fn.geocomplete ) {
			$(self.distanceCenterSelector).geocomplete({types: []});
		} else {
			WPViews.mapsAddressAutocomplete.initField($(self.distanceCenterSelector));
		}

	    if ( navigator.geolocation  && self.isSecurePage() ) {
		    $(self.userLocationSelector).show();
	    }
    };

    self.areDistanceCenterFormElementsPresentOnPage = function() {
    	return (
    		Boolean( $( self.distanceCenterLatSelector ).length )
		    && Boolean( $( self.distanceCenterLngSelector ).length )
	    );
    };

    self.isSecurePage = function () {
        return (location.protocol === 'https:');
    }

    self.acquireCurrentLocation = function(success, error) {
        navigator.geolocation.getCurrentPosition(success, error);
    };

    self.updateFilterCenter = function(position) {
        if(position.coords) {
            self.is_using_current_location = true;

            self.current_lat  = position.coords.latitude;
            self.current_long = position.coords.longitude;

            $( document ).trigger('toolset_maps_views_distance_filter_user_location_acquired');
        } else if (position.address_update) {
            self.current_lat  = position.location.latitude;
            self.current_long = position.location.longitude;
        }

        $( document ).trigger('toolset_maps_views_distance_filter_center_updated', {
            position: position
        });
    };

    self.getCurrentLatLng = function() {
        var current_latlng = "";
        if(self.current_lat != null && self.current_long != null) {
            current_latlng = self.current_lat + ', ' + self.current_long;
        }
        return current_latlng;

    }

	self.updateQueryFilterWithUserLocation = function() {
		if(
			window.hasOwnProperty('toolset_maps_distance_filter_settings')
			&& toolset_maps_distance_filter_settings.use_user_location
		) {
			self.acquireCurrentLocation(function(data) {
				// Put coords into URL
				var url = WPViews.view_frontend_utils.updateUrlQuery(
					'toolset_maps_visitor_coords',
					data.coords.latitude + ',' + data.coords.longitude,
					window.location.href
				);
				window.history.pushState(null, '', url);

				// Put parametric data into (hidden) form
				$( self.hiddenCoordsFormSelector ).data('parametric', {
					id: self.view_id,
					widget_id: self.view_id,
					sort: {},
					environment: {}
				} );

				var queryResultsPromise = WPViews.view_frontend_utils.get_updated_query_results(
					self.view_id,
					0,
					$( self.hiddenCoordsFormSelector ),
					'full'
				);

				queryResultsPromise.done(function(data, textStatus, jqXHR){
					// We get the new form and layout together, one after each other. But, because there is no
					// guaranteed container element, put new stuff in place of form, and remove layout, resulting in
					// same order as before. Oh, and do that on the exact form and layout, using view id, because there
					// may be multiple on page.
					$( 'form[name^="wpv-filter-'+data.data.id+'"' ).replaceWith( data.data.full );
					$( 'div[id^=wpv-view-layout-'+data.data.id+']' ).remove();

					// After getting data by ajax, init maps to collect map data and render maps
					// If G API key is missing, there may not be a map to render, so check for that too
					if ( WPViews.view_addon_maps ) {
						WPViews.view_addon_maps.init();
						WPViews.view_addon_maps.init_maps();
					}
				});
			}, function(error) {
				window.alert( toolset_maps_distance_filter_settings.geolocation_error + error.message );
			});
		}
	};

    self.init();
};

jQuery( document ).ready( function( $ ) {
    WPViews.distance_filter_frontend = new WPViews.DistanceFilterFrontend( $ );
});
