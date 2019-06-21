var WPViews = WPViews || {};

WPViews.ViewAddonMaps = function( $ ) {
	const API_GOOGLE = 'google';
	const API_AZURE  = 'azure';

	/** @var {Object} views_addon_maps_i10n */

	var self = this;

	if ( API_GOOGLE === views_addon_maps_i10n.api_used ) {
		/** @var {Object} google */
		self.api = google.maps;
	} else {
		/** @var {Object} atlas */
		self.api = null;
	}

	self.maps_data = [];
	self.maps = {};
	self.markers = {};
	self.infowindows = {};
	self.bounds = {};

	self.default_cluster_options = {
		imagePath:			views_addon_maps_i10n.cluster_default_imagePath,
		gridSize:			60,
		maxZoom:			null,
		zoomOnClick:		true,
		minimumClusterSize:	2
	};

	self.cluster_options = {};
	self.has_cluster_options = {};

	self.resize_queue = [];

	/**
	* collect_maps_data
	*
	* Before init_maps
	*
	* @since 1.0
	*/

	self.collect_maps_data = function() {
		$( '.js-wpv-addon-maps-render' ).each( function() {
			self.collect_map_data( $( this ) );
		});

		$( document ).trigger('js_event_wpv_addon_maps_map_data_collected');
	};

	/**
	 * Initializes street view mode on given map.
	 * @param Object map
	 * @param latlon
	 * @since 1.5
	 */
	self.initStreetView = function( map, latlon ) {
		var streetViewService = new google.maps.StreetViewService();
		var panorama = self.maps[ map.map_id ].getStreetView();

		streetViewService.getPanoramaByLocation( latlon, 100, function (streetViewPanoramaData, status) {

			if ( status === google.maps.StreetViewStatus.OK ) {
				var streetLatlon = streetViewPanoramaData.location.latLng;
				var heading = ( map.map_options.heading !== '' )
					? map.map_options.heading
					: google.maps.geometry.spherical.computeHeading( streetLatlon, latlon);
				var pitch = ( map.map_options.pitch !== '' ) ? map.map_options.pitch : 0;

				panorama.setPosition( streetLatlon );
				panorama.setPov({
					heading: heading,
					pitch: pitch
				});
				panorama.setVisible( true );
			}
		});
	};

	/**
	 * Sets a trigger to init street view after map is ready. Namespaces trigger so we do this only once per map.
	 * @param String mapId
	 * @param jQuery $marker
	 * @listens js_event_wpv_addon_maps_init_map_completed
	 * @since 1.5
	 */
	self.waitForMapInitThenInitStreetView = function( mapId, $marker ) {
		$( document ).on( 'js_event_wpv_addon_maps_init_map_completed', function( event, map ) {
			if ( mapId === map.map_id ){
				var latlon = new google.maps.LatLng( $marker.data('markerlat'), $marker.data('markerlon') );
				self.initStreetView( map, latlon );
			}
		} );
	};

	/**
	 * collect_map_data
	 *
	 * @since 1.0
	 * @since 1.5 handle Street Views
	 */
	self.collect_map_data = function( thiz_map ) {
		var thiz_map_id = thiz_map.data( 'map' ),
		thiz_map_points = [],
		thiz_map_options = {};
		var streetViewMarkerFound = false;

		$( '.js-wpv-addon-maps-markerfor-' + thiz_map_id ).each( function() {
			var thiz_marker = $( this );
			// Handle special case when we don't have coordinates, but instead need to ask browser for current users
			// position (only for Google API)
			if (
				thiz_marker.data( 'markerlat' ) === 'geo'
				&& API_GOOGLE === views_addon_maps_i10n.api_used
			) {
				// In case map render wait is requested by a marker, add that data to map
				if (thiz_marker.data( 'markerlon' ) === 'wait') {
					thiz_map_options['render'] = 'wait';
					self.add_current_visitor_location_after_geolocation(thiz_map_id, thiz_marker);
				} else {
					self.add_current_visitor_location_after_init(thiz_map_id, thiz_marker);
				}
				return true;
			}

			// Handle Street View marker as special case (only when using Google API)
			if (
				thiz_map.data('streetview') === 'on'
				&& API_GOOGLE === views_addon_maps_i10n.api_used
			) {
				if ( thiz_map.data('markerid') === thiz_marker.data('marker') ) {
					self.waitForMapInitThenInitStreetView( thiz_map_id, thiz_marker );
					streetViewMarkerFound = true;
					return true;
				} else if ( thiz_map.data('location') === 'first' && !streetViewMarkerFound ) {
					self.waitForMapInitThenInitStreetView( thiz_map_id, thiz_marker );
					streetViewMarkerFound = true;
					return true;
				}
			}

			thiz_map_points.push(
				{
					'marker': thiz_marker.data('marker'),
					'title': thiz_marker.data('markertitle'),
					'markerlat': thiz_marker.data('markerlat'),
					'markerlon': thiz_marker.data('markerlon'),
					'markerinfowindow': thiz_marker.html(),
					'markericon': thiz_marker.data('markericon'),
					'markericonhover': thiz_marker.data('markericonhover')
				}
			);

		});

		// Some error catching when street view from marker requested and marker not found
		if (
			(
				thiz_map.data('location') === 'first'
				|| thiz_map.data('markerid')
			)
			&& !streetViewMarkerFound
		) {
			console.warn( views_addon_maps_i10n.marker_not_found_warning, thiz_map_id );
		}

		thiz_map_options['general_zoom'] = thiz_map.data( 'generalzoom' );
		thiz_map_options['general_center_lat'] = thiz_map.data( 'generalcenterlat' );
		thiz_map_options['general_center_lon'] = thiz_map.data( 'generalcenterlon' );
		thiz_map_options['fitbounds'] = thiz_map.data( 'fitbounds' );
		thiz_map_options['single_zoom'] = thiz_map.data( 'singlezoom' );
		thiz_map_options['single_center'] = thiz_map.data( 'singlecenter' );
		thiz_map_options['map_type'] = thiz_map.data( 'maptype' );
		thiz_map_options['show_layer_interests'] = thiz_map.data( 'showlayerinterests' );
		thiz_map_options['marker_icon'] = thiz_map.data( 'markericon' );
		thiz_map_options['marker_icon_hover'] = thiz_map.data( 'markericonhover' );
		thiz_map_options['draggable'] = thiz_map.data( 'draggable' );
		thiz_map_options['scrollwheel'] = thiz_map.data( 'scrollwheel' );
		thiz_map_options['double_click_zoom'] = thiz_map.data( 'doubleclickzoom' );
		thiz_map_options['map_type_control'] = thiz_map.data( 'maptypecontrol' );
		thiz_map_options['full_screen_control'] = thiz_map.data( 'fullscreencontrol' );
		thiz_map_options['zoom_control'] = thiz_map.data( 'zoomcontrol' );
		thiz_map_options['street_view_control'] = thiz_map.data( 'streetviewcontrol' );
		thiz_map_options['background_color'] = thiz_map.data( 'backgroundcolor' );
		thiz_map_options['cluster'] = thiz_map.data( 'cluster' );
		thiz_map_options['style_json'] = thiz_map.data( 'stylejson' );
		thiz_map_options['spiderfy'] = thiz_map.data( 'spiderfy' );
		thiz_map_options['lat'] = thiz_map.data( 'lat' );
		thiz_map_options['long'] = thiz_map.data( 'long' );
		thiz_map_options['heading'] = thiz_map.data( 'heading' );
		thiz_map_options['pitch'] = thiz_map.data( 'pitch' );

		var thiz_cluster_options = {
			'cluster':				thiz_map.data( 'cluster' ),
			'gridSize':				parseInt( thiz_map.data( 'clustergridsize' ) ),
			'maxZoom':				( parseInt( thiz_map.data( 'clustermaxzoom' ) ) > 0 ) ? parseInt( thiz_map.data( 'clustermaxzoom' ) ) : null,
			'zoomOnClick':			( thiz_map.data( 'clusterclickzoom' ) == 'off' ) ? false : true,
			'minimumClusterSize':	parseInt( thiz_map.data( 'clusterminsize' ) )
		};

		// As we might have cleared those options if we are on a reload event, we need to set them again with the data we saved in self.has_cluster_options
		if ( _.has( self.has_cluster_options, thiz_map_id ) ) {
			if ( _.has( self.has_cluster_options[ thiz_map_id ] , "styles" ) ) {
				thiz_cluster_options['styles'] = self.has_cluster_options[ thiz_map_id ]['styles'];
			}
			if ( _.has( self.has_cluster_options[ thiz_map_id ] , "calculator" ) ) {
				thiz_cluster_options['calculator'] = self.has_cluster_options[ thiz_map_id ]['calculator'];
			}
		}

		var thiz_map_collected = {
			'map':				thiz_map_id,
			'markers':			thiz_map_points,
			'options':			thiz_map_options,
			'cluster_options':	thiz_cluster_options
		};

		self.maps_data.push( thiz_map_collected );
		self.cluster_options[ thiz_map_id ] = thiz_cluster_options;

		return thiz_map_collected;

	};

	/**
	 * Gets current visitor location from browser, then adds the marker to given map.
	 *
	 * If location fetching failed, render the map without it...
	 *
	 * @since 1.4
	 * @param {String} thiz_map_id
	 * @param {jQuery} thiz_marker
	 */
	self.add_current_visitor_location = function(thiz_map_id, thiz_marker) {
		var map_key = self.get_map_key_by_id(thiz_map_id);
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(
				function (position) {
					// Add data to collection and ask map to redraw
					self.maps_data[map_key].markers.push({
						'marker': thiz_marker.data('marker'),
						'title': thiz_marker.data('markertitle'),
						'markerlat': position.coords.latitude,
						'markerlon': position.coords.longitude,
						'markerinfowindow': thiz_marker.html(),
						'markericon': thiz_marker.data('markericon'),
						'markericonhover': thiz_marker.data('markericonhover')
					});

					self.init_map_after_loading_styles(self.maps_data[map_key]);
				},
				function (position_error) {
					self.init_map_after_loading_styles(self.maps_data[map_key]);
				}
			);
		} else {
			self.init_map_after_loading_styles(self.maps_data[map_key]);
		}
	};

	/**
	 * Wraps waiting for map to render first time before adding browser location marker
	 * @since 1.4
	 * @param {String} thiz_map_id
	 * @param {jQuery} thiz_marker
	 */
	self.add_current_visitor_location_after_init = function(thiz_map_id, thiz_marker) {
		$( document ).on( 'js_event_wpv_addon_maps_init_map_completed.'+thiz_map_id, function( event, data ) {
			if (thiz_map_id === data.map_id) {
				// Stop listening to this event for this map (because the event can fire multiple times
				// and we really need to add marker only once)
				$( document ).off( 'js_event_wpv_addon_maps_init_map_completed.'+thiz_map_id );

				self.add_current_visitor_location(thiz_map_id, thiz_marker);
			}
		});
	};

	/**
	 * Wraps waiting for geolocation before rendering map.
	 *
	 * Waits for all map data to be ready before trying to render it. (Problems could otherwise happen when geolocation
	 * is already approved and map data is not yet collected.)
	 *
	 * @since 1.4
	 * @param {String} thiz_map_id
	 * @param {jQuery} thiz_marker
	 */
	self.add_current_visitor_location_after_geolocation = function(thiz_map_id, thiz_marker) {
		$( document ).one('js_event_wpv_addon_maps_map_data_collected.'+thiz_map_id, function() {
			self.add_current_visitor_location(thiz_map_id, thiz_marker);
		});
	};

	/**
	 * Given map id string returns array key for a map in maps_data
	 * @since 1.4
	 * @param {String} map_id
	 * @returns {Number}
	 */
	self.get_map_key_by_id = function(map_id) {
		return _.findLastIndex(self.maps_data, { map: map_id });
	};

	/**
	 * Checks if json style needed, loads JSON file with map styles and then inits the map
	 * @since 1.4
	 * @param {Object} map
	 */
	self.init_map_after_loading_styles = function( map ) {
		if ( map.options.style_json ) {
			$.getJSON( map.options.style_json )
				.done( function( styles ) {
					map.options.styles = styles;
				} )
				.always( function () {
					// Even if styles loading failed, map can be rendered with standard style, so do it always.
					self.initMapOrWaitIfInsideHiddenBootstrapAccordionOrTab( map );
				} );
		} else {
			self.initMapOrWaitIfInsideHiddenBootstrapAccordionOrTab( map );
		}
	};

	/**
	 * Checks if this map is inside a hidden (collapsed) Bootstrap accordion.
	 *
	 * @param {String} map_selector
	 *
	 * @return {String} Returns accordion id if found, empty string otherwise
	 *
	 * @since 1.4.2
	 */
	self.check_being_inside_collapsed_bootstrap_accordion = function( map_selector ) {
		var accordion_id = '';
		var toggle_href = '';

		// Find all accordion toggles which are currently collapsed, then find which of the accordions contains our map
		// and return accordion id. If none, we'll return empty string.
		$('a[data-toggle="collapse"].collapsed').each( function( index, toggle ) {
			toggle_href = $( toggle ).attr('href');

			if ( $( toggle_href ).has( map_selector ).length ) {
				accordion_id = toggle_href;
				return false;
			}
		} );

		return accordion_id;
	};

	/**
	 * Checks if this map is inside a hidden Bootstrap tab.
	 *
	 * @param {String} map_selector
	 *
	 * @return {string}
	 *
	 * @since 1.4.2
	 */
	self.check_being_inside_hidden_bootstrap_tab = function( map_selector ) {
		var tab_toggle = '';
		var toggle_href = '';
		var $tab_body;

		$('a[data-toggle="tab"]').each( function( index, toggle ) {
			toggle_href = $( toggle ).attr('href');
			$tab_body = $( toggle_href );

			if ( !$tab_body.hasClass('active') && $tab_body.has( map_selector ).length ) {
				tab_toggle = toggle;
				return false;
			}
		} );

		return tab_toggle;
	};

	/**
	 * If not inside hidden Bootstrap accordion or tab, init map, if inside, hook to shown event to init then.
	 *
	 * @param {Object} map
	 *
	 * @since 1.4.2
	 */
	self.initMapOrWaitIfInsideHiddenBootstrapAccordionOrTab = function(map ) {
		var map_selector = '#js-wpv-addon-maps-render-' + map.map;
		var accordion_id = self.check_being_inside_collapsed_bootstrap_accordion( map_selector );
		var tab_toggle = self.check_being_inside_hidden_bootstrap_tab( map_selector );

		if ( accordion_id ) {
			$( accordion_id ).one('shown.bs.collapse', function() {
				self.init_map( map );
			} );
		} else if( tab_toggle ) {
			$( tab_toggle ).one('shown.bs.tab', function() {
				self.init_map( map );
			} );
		} else {
			self.init_map( map );
		}
	};

	/**
	* init_maps
	*
	* @since 1.0
	*/

	self.init_maps = function() {
		self.maps_data.map( function( map ) {
			// If there is a marker on the map with option for map rendering to wait until it's ready, do nothing.
			// (Marker itself will trigger map rendering when ready.)
			if (
				map.options.render
				&& map.options.render === 'wait'
			) {
				return true;
			}

			// Handle other maps
			self.init_map_after_loading_styles( map );
		});
	};

	/**
	* init_map
	*
	* @since 1.0
	*/

	self.init_map = function( map ) {
		var map_icon = '',
		map_icon_hover = '',
		map_settings = {
			zoom: map.options['general_zoom']
		},
		event_settings = {
			map_id:			map.map,
			map_options:	map.options
		};
		var spiderfy = false;
		var clickEvent = 'click';

		$( document ).trigger( 'js_event_wpv_addon_maps_init_map_started', [ event_settings ] );

		if (
			map.options['general_center_lat'] != ''
			&& map.options['general_center_lon'] != ''
		) {
			map_settings['center'] = {
				lat: map.options['general_center_lat'],
				lng: map.options['general_center_lon']
			};
		} else {
			map_settings['center'] = {
				lat: 0,
				lng: 0
			};
		}

		if ( map.options['draggable'] == 'off' ) {
			map_settings['draggable'] = false;
		}

		if ( map.options['scrollwheel'] == 'off' ) {
			map_settings['scrollwheel'] = false;
		}

		if ( map.options['double_click_zoom'] == 'off' ) {
			map_settings['disableDoubleClickZoom'] = true;
		}

		if ( map.options['map_type_control'] == 'off' ) {
			map_settings['mapTypeControl'] = false;
		}

		if ( map.options['full_screen_control'] == 'on' ) {
			map_settings['fullscreenControl'] = true;
		}

		if ( map.options['zoom_control'] == 'off' ) {
			map_settings['zoomControl'] = false;
		}

		if ( map.options['street_view_control'] == 'off' ) {
			map_settings['streetViewControl'] = false;
		}

		if ( map.options['background_color'] != '' ) {
			map_settings['backgroundColor'] = map.options['background_color'];
		}

		if ( map.options['styles'] ) {
			map_settings['styles'] = map.options['styles'];
		}

		if ( map.options['map_type'] ) {
			map_settings['mapTypeId'] = map.options['map_type'];
		}

		self.maps[ map.map ] = new self.api.Map( document.getElementById( 'js-wpv-addon-maps-render-' + map.map ), map_settings );

		$( document ).trigger( 'js_event_wpv_addon_maps_init_map_inited', [ event_settings ] );

		self.bounds[ map.map ] = new self.api.LatLngBounds();

		if ( map.options['marker_icon'] != '' ) {
			map_icon = map.options['marker_icon'];
		}

		if ( map.options['marker_icon_hover'] != '' ) {
			map_icon_hover = map.options['marker_icon_hover'];
		}

		if ( 'on' === map.options['spiderfy'] ) {
			var oms = new OverlappingMarkerSpiderfier( self.maps[ map.map ], {
				markersWontMove: true,
				markersWontHide: true,
				basicFormatEvents: true
			});
			spiderfy = true;
		}

		map.markers.map( function( marker ) {
			var marker_lat_long = new self.api.LatLng( marker.markerlat, marker.markerlon ),
			marker_map_icon = ( marker.markericon == '' ) ? map_icon : marker.markericon,
			marker_map_icon_hover = ( marker.markericonhover == '' ) ? map_icon_hover : marker.markericonhover,
			marker_settings = {
				position: marker_lat_long,
				optimized: false
			};

			if ( spiderfy ) {
				clickEvent = 'spider_click';
			} else {
				marker_settings.map = self.maps[ map.map ]
				clickEvent = 'click';
			}

			// Helps SVG marker icons to have the same size as others and render properly in Internet Explorer
			var scaledSize = new google.maps.Size(32, 32);

			if ( marker_map_icon != '' ) {
				marker_settings['icon'] = {
					url: marker_map_icon,
					scaledSize: scaledSize,
				}
			}
			if ( marker.title != '' ) {
				marker_settings['title'] = marker.title;
			}

			self.markers[ map.map ] = self.markers[ map.map ] || {};

			self.markers[ map.map ][ marker.marker ] = new self.api.Marker(marker_settings);

			self.bounds[ map.map ].extend( self.markers[ map.map ][ marker.marker ].position );

			if (
				marker_map_icon != ''
				|| marker_map_icon_hover != ''
			) {
				marker_map_icon = ( marker_map_icon == '' ) ? views_addon_maps_i10n.marker_default_url : marker_map_icon;
				marker_map_icon_hover = ( marker_map_icon_hover == '' ) ? marker_map_icon : marker_map_icon_hover;

				if ( marker_map_icon != marker_map_icon_hover ) {
					var marker_icon_scaled = {
						url: marker_map_icon,
						scaledSize: scaledSize,
					};
					var marker_hover_icon_scaled = {
						url: marker_map_icon_hover,
						scaledSize: scaledSize,
					};

					self.api.event.addListener( self.markers[ map.map ][ marker.marker ], 'mouseover', function() {
						self.markers[ map.map ][ marker.marker ].setIcon( marker_hover_icon_scaled );
					});
					self.api.event.addListener( self.markers[ map.map ][ marker.marker ], 'mouseout', function() {
						self.markers[ map.map ][ marker.marker ].setIcon( marker_icon_scaled );
					});
					// Add custom classnames to reproduce this hover effect from an HTML element
					$( document ).on( 'mouseover', '.js-toolset-maps-hover-map-' + map.map + '-marker-' + marker.marker, function() {
						self.markers[ map.map ][ marker.marker ].setIcon( marker_hover_icon_scaled );
					});
					$( document ).on( 'mouseout', '.js-toolset-maps-hover-map-' + map.map + '-marker-' + marker.marker, function() {
						self.markers[ map.map ][ marker.marker ].setIcon( marker_icon_scaled );
					});
				}
			}

			if ( marker.markerinfowindow != '' ) {
				// Create a single self.api.InfoWindow object for each map, if needed, and populate its content based on
				// the marker
				self.infowindows[ map.map ] = self.infowindows[ map.map ] || new self.api.InfoWindow({ content: '' });
				self.api.event.addListener( self.markers[ map.map ][ marker.marker ], clickEvent, function() {
					self.infowindows[ map.map ].setContent( marker.markerinfowindow );
					self.infowindows[ map.map ].open( self.maps[ map.map ], self.markers[ map.map ][ marker.marker ] );
				});
				$( document ).on(
					clickEvent,
					'.js-toolset-maps-open-infowindow-map-' + map.map + '-marker-' + marker.marker,
					function()
				{
					self.infowindows[ map.map ].setContent( marker.markerinfowindow );
					self.openInfowindowWhenMarkerVisible( map.map, marker.marker );
				});
			}

			if ( spiderfy ) {
				oms.addMarker( self.markers[ map.map ][ marker.marker ] )
			}
		});

		if ( _.size( map.markers ) == 1 ) {
			if ( map.options['single_zoom'] != '' ) {
				self.maps[ map.map ].setZoom( map.options['single_zoom'] );
				if ( map.options['fitbounds'] == 'on' ) {
					self.api.event.addListenerOnce( self.maps[ map.map ], 'bounds_changed', function( event ) {
						self.maps[ map.map ].setZoom( map.options['single_zoom'] );
					});
				}
			}
			if ( map.options['single_center'] == 'on' ) {
				for ( var mark in self.markers[ map.map ] ) {
					self.maps[ map.map ].setCenter( self.markers[ map.map ][ mark ].getPosition() );
					break;
				}
			}
		} else if ( _.size( map.markers ) > 1 ) {
			if ( map.options['fitbounds'] == 'on' ) {
				self.maps[ map.map ].fitBounds( self.bounds[ map.map ] );
			}
		}

		if ( _.contains( self.resize_queue, map.map ) ) {
			self.keep_map_center_and_resize( self.maps[ map.map ] );
			_.reject( self.resize_queue, function( item ) {
				return item == map.map;
			});
		}

		$( document ).trigger( 'js_event_wpv_addon_maps_init_map_completed', [ event_settings ] );

		// Init Street View overlay if lat and long are provided (coming from address)
		if ( map.options.lat && map.options.long ) {
			self.initStreetView( event_settings, new google.maps.LatLng( map.options.lat, map.options.long ) );
		}
	};
	/**
	 * Make sure that marker is visible (not in cluster) before opening info window. If it isn't, wait until it is.
	 *
	 * Because, if marker isn't visible, info window will get wrong location to open (usually another, visible marker).
	 *
	 * @param {String} mapId
	 * @param {String} markerId
	 * @since 1.5
	 */
	self.openInfowindowWhenMarkerVisible = function( mapId, markerId ) {
		if ( self.markers[mapId][markerId].map ) {
			self.infowindows[mapId].open( self.maps[mapId], self.markers[mapId][markerId] );
		} else {
			_.delay(function () {
				self.openInfowindowWhenMarkerVisible( mapId, markerId );
			}, 150);
		}
	};
	/**
	* clean_map_data
	*
	* @param map_id
	*
	* @since 1.0
	*/

	self.clean_map_data = function( map_id ) {
		self.maps_data = _.filter( self.maps_data, function( map_data_unique ) {
			return map_data_unique.map != map_id;
		});

		self.maps				= _.omit( self.maps, map_id );
		self.markers			= _.omit( self.markers, map_id );
		self.infowindows		= _.omit( self.infowindows, map_id );
		self.bounds				= _.omit( self.bounds, map_id );
		self.cluster_options	= _.omit( self.cluster_options, map_id );

		var settings = {
			map_id: map_id
		};

		$( document ).trigger( 'js_event_wpv_addon_maps_clean_map_completed', [ settings ] );

	};

	/**
	* keep_map_center_and_resize
	*
	* @param map
	*
	* @since 1.1
	*/

	self.keep_map_center_and_resize = function( map ) {
		var map_iter_center = map.getCenter();
		self.api.event.trigger( map, "resize" );
		map.setCenter( map_iter_center );
	};

	/**
	 * Init all maps - Azure API version
	 * @since 1.5
	 */
	self.initMapsAzure = function() {
		let maps = _.map( self.maps_data, self.resolveGeolocatedMarkerThenInitMapAzure );
		let mapIds = _.pluck( self.maps_data, 'map' );

		self.maps = _.object( mapIds, maps );
	};

	/**
	 * Resolve geolocated marker (if any)
	 *
	 * Among markers for a map, there may be a special one which should trigger current visitor geolocation. (It makes
	 * no sense to have more than one, because the visitor is not a quark). This method checks if there is one such
	 * marker, if geolocation is available, and if the map rendering should wait for geolocation or if that marker
	 * should be added after map and other markers render.
	 *
	 * @since 1.5
	 *
	 * @param {Object} map
	 */
	self.resolveGeolocatedMarkerThenInitMapAzure = function( map ) {
		let geoMarker = _.findWhere( map.markers, {markerlat: 'geo'} );
		let renderedMap = null;

		if ( geoMarker ) {
			let geoMarkerIndex = _.indexOf( map.markers, geoMarker );

			if ( "immediate" === geoMarker.markerlon ) {
				// Render map immediately, marker will be added when and if available, center and bounds adjusted if
				// needed.
				renderedMap = self.initMapAzure( map );

				if (navigator.geolocation) {
					navigator.geolocation.getCurrentPosition(function (position) {
						map.markers[geoMarkerIndex].markerlat = position.coords.latitude;
						map.markers[geoMarkerIndex].markerlon = position.coords.longitude;

						if (
							map.markers.length === 1
							&& map.options.single_center === "on"
						) {
							let mapCenter = [position.coords.longitude, position.coords.latitude];
							renderedMap.setCamera( {center: mapCenter} );
						}

						self.maybeFitboundsAzure( map, renderedMap );

						let pin = new atlas.data.Feature(
							new atlas.data.Point( [position.coords.longitude, position.coords.latitude] ),
							{title: geoMarker.title, popup: geoMarker.markerinfowindow},
							geoMarker.marker
						);
						renderedMap.addEventListener('load', function() {
							renderedMap.addPins( [pin], {
								fontColor: "#000",
								fontSize: 14,
								icon: "pin-red",
								iconSize: 1,
								name: "default-pin-layer",
								cluster: ( map.options.cluster === 'on' ),
								textOffset: [0, 20],
							});
						} );
					});
				}
			} else {
				if (navigator.geolocation) {
					navigator.geolocation.getCurrentPosition(function (position) {
						map.markers[geoMarkerIndex].markerlat = position.coords.latitude;
						map.markers[geoMarkerIndex].markerlon = position.coords.longitude;

						// We now have position for geo marker, render as usual.
						renderedMap = self.initMapAzure( map );
					});
				} else {
					// No geolocation? Render what we have, without this marker.
					renderedMap = self.initMapAzure( map );
				}
			}
		} else {
			// No geomarker, just render as usual.
			renderedMap = self.initMapAzure( map );
		}

		return renderedMap;
	};

	/**
	 * If there is more than 1 marker and fitbounds is requested for this map, find bound points and set camera bounds.
	 *
	 * @since 1.5
	 *
	 * @param {Object} map
	 * @param {atlas.Map} renderedMap
	 */
	self.maybeFitboundsAzure = function( map, renderedMap ) {
		if ( map.markers.length <= 1 || map.options.fitbounds !== "on" ) return;

		let markerLons = _.pluck( map.markers, 'markerlon' );
		let southernmost = _.min( markerLons );
		let northernmost = _.max( markerLons );
		let markerLats = _.pluck( map.markers, 'markerlat' );
		let westmost = _.min( markerLats );
		let eastmost = _.max( markerLats );

		renderedMap.setCameraBounds( {
			bounds: [southernmost, westmost, northernmost, eastmost],
			padding: 50
		} );
	};

	/**
	 * Map type names are sometimes, but not always the same between Google and Azure.
	 *
	 * @since 1.5.3
	 * @param {string} mapType
	 * @return {string}
	 */
	self.translateMapTypesToAzure = function( mapType ) {
		const translationTable = {
			'roadmap': 'road',
			'hybrid': 'satellite_road_labels',
			'terrain': 'road' // 'terrain' type is not supported by Azure, so render the closest - 'road'
		};

		if ( mapType in translationTable ) return translationTable[mapType];
		// 'satellite' type has the same name, so just falls through.
		else return mapType;
	};

	/**
	 * Returns value for marker icon set from marker, map, or default
	 *
	 * @since 1.5.3
	 * @param {Object} marker
	 * @param {Object} mapOptions
	 * @return {string}
	 */
	self.getMarkerIconValue = function( marker, mapOptions ) {
		if ( marker.markericon ) {
			return marker.markericon;
		} else if ( mapOptions.marker_icon ) {
			return mapOptions.marker_icon;
		} else {
			return 'pin-red';
		}
	};

	/**
	 * Init a map with Azure API
	 *
	 * @since 1.5
	 *
	 * @param {Object} map
	 *
	 * @return {atlas.Map}
	 */
	self.initMapAzure = function( map ) {
		let event_settings = {
			map_id:			map.map,
			map_options:	map.options
		};
		$( document ).trigger( 'js_event_wpv_addon_maps_init_map_started', [ event_settings ] );

		// Init defaults from map options
		let mapCenter = [map.options.general_center_lon, map.options.general_center_lat];
		let mapZoom = map.options.general_zoom;
		let cluster = ( map.options.cluster === 'on' );

		// 1 marker logic
		if ( map.markers.length === 1 ) {
			if (
				// marker geolocated and rendered after map, handled in self.resolveGeolocatedMarkerThenInitMapAzure
				_.isNumber( map.markers[0].markerlon )
				&& map.options.single_center === "on"
			) {
				mapCenter = [map.markers[0].markerlon, map.markers[0].markerlat];
			}

			mapZoom = map.options.single_zoom;
		}

		// Remove possible map loading content. Google Maps does it by itself, Azure doesn't
		$( '#js-wpv-addon-maps-render-' + map.map ).empty();

		// Render map
		let renderedMap = new atlas.Map( "js-wpv-addon-maps-render-" + map.map, {
			"subscription-key": views_addon_maps_i10n.azure_api_key,
			center: mapCenter,
			zoom: mapZoom,
			scrollZoomInteraction: ( map.options.scrollwheel === 'on' ),
			dblClickZoomInteraction: ( map.options.double_click_zoom === 'on' ),
			dragPanInteraction: ( map.options.draggable === 'on' ),
			style: self.translateMapTypesToAzure( map.options.map_type ) // Ignored with API 1.1, but works with 1.2
		} );

		$( document ).trigger( 'js_event_wpv_addon_maps_init_map_inited', [ event_settings ] );

		// Bounds can only be set on an already rendered map with Azure API
		self.maybeFitboundsAzure( map, renderedMap );

		// Create pins (markers)
		let pins = _.map( map.markers, function( marker ) {
			return new atlas.data.Feature(
				new atlas.data.Point( [marker.markerlon, marker.markerlat] ),
				{
					title: marker.title,
					popup: marker.markerinfowindow,
					icon: self.getMarkerIconValue( marker, map.options ),
					iconhover: marker.markericonhover ? marker.markericonhover : map.options.marker_icon_hover,
					id: marker.marker
				},
				marker.marker
			);
		} );

		// Create custom icons for pins (if any)
		let icons = _.filter(
			_.union(
				_.pluck( map.markers, 'markericon' ),
				_.pluck( map.markers, 'markericonhover' ),
				[map.options.marker_icon, map.options.marker_icon_hover]
			)
		);

		// Add markers and their events to map (waiting for load event first, otherwise the library throws a style
		// rendering warning)
		renderedMap.addEventListener('load', function() {
			_.each( icons, function( icon ) {
				var iconImg = document.getElementById( icon );

				if ( iconImg ) {
					renderedMap.addIcon( icon, iconImg );
				}
			} );

			self.addPins( renderedMap, pins, cluster );

			// FUTURE: this needs Azure Maps API 1.2, which is buggy at the moment and kills some other things. Revisit.
			// Add controls
			/*if ( map.options.zoom_control === 'on' ) {
				renderedMap.addControl(new atlas.control.ZoomControl, {});
			}
			if ( map.options.map_type_control === 'on' ) {
				renderedMap.addControl(new atlas.control.StyleControl, {});
			}*/

			// Add an event listener for click (popup)
			renderedMap.addEventListener( 'click', "default-pin-layer", function( event ) {
				// Does pin have some popup text?
				if ( ! event.features[0].properties.popup ) return;

				// Create content for popup
				let popupContentElement = document.createElement("div");
				popupContentElement.style.padding = "8px";
				let popupNameElement = document.createElement("div");
				popupNameElement.innerHTML = event.features[0].properties.popup;
				popupContentElement.appendChild(popupNameElement);

				// Create a popup
				let popup = new atlas.Popup( {
					content: popupContentElement,
					position: event.features[0].geometry.coordinates,
					pixelOffset: [0, 0]
				} );

				popup.open( renderedMap );
			} );

			// Add event listeners for hover (possible marker icon change)
			let originalIcon = '';
			let hoveredPinIndex = 0;
			renderedMap.addEventListener( 'mouseover', "default-pin-layer", function( event ) {
				if ( ! event.features[0].properties.iconhover ) return;

				let id = event.features[0].properties.id;
				let pin = _.findWhere( pins, {id: id} );

				originalIcon = event.features[0].properties.icon;
				hoveredPinIndex = _.indexOf( pins, pin );

				pin.properties.icon = pin.properties.iconhover;
				pins[hoveredPinIndex] = pin;

				self.addPins( renderedMap, pins, cluster );
			} );
			renderedMap.addEventListener( 'mouseout', "default-pin-layer", function( event ) {
				if ( ! originalIcon ) return;

				pins[hoveredPinIndex].properties.icon = originalIcon;

				self.addPins( renderedMap, pins, cluster );

				originalIcon = '';
			} );
		} );

		$( document ).trigger( 'js_event_wpv_addon_maps_init_map_completed', [ event_settings ] );

		return renderedMap;
	};

	/**
	 * Adds (or changes) all the pins for the given map on default pin layer.
	 *
	 * (Because you cannot simply change one pin with Azure, you have to change the layer).
	 *
	 * @since 1.5.3
	 * @param atlas.Map map
	 * @param {array} pins
	 * @param {bool} cluster
	 */
	self.addPins = function( map, pins, cluster ) {
		map.addPins( pins, {
			fontColor: "#000",
			fontSize: 14,
			name: "default-pin-layer",
			cluster: cluster,
			textOffset: [0, 20],
			overwrite: true
		} );
	};

	// ------------------------------------
	// API
	// ------------------------------------

	/**
	* WPViews.view_addon_maps.reload_map
	*
	* @param map_id
	*
	* @since 1.0
	*/

	self.reload_map = function( map_id ) {
		var settings = {
			map_id: map_id
		};
		$( document ).trigger( 'js_event_wpv_addon_maps_reload_map_started', [ settings ] );
		$( document ).trigger( 'js_event_wpv_addon_maps_reload_map_triggered', [ settings ] );
		$( document ).trigger( 'js_event_wpv_addon_maps_reload_map_completed', [ settings ] );
	};

	/**
	* document.js_event_wpv_addon_maps_reload_map_triggered
	*
	* @param event
	* @param data
	* 	data.map_id
	*
	* @since 1.0
	* @since 1.5 supports Azure API
	*/
	$( document ).on( 'js_event_wpv_addon_maps_reload_map_triggered', function( event, data ) {
		var defaults = {
			map_id: false
		},
		settings = $.extend( {}, defaults, data );
		if (
			settings.map_id == false
			|| $( '#js-wpv-addon-maps-render-' + settings.map_id ).length != 1
		) {
			return;
		}
		self.clean_map_data( settings.map_id );
		var mpdata = self.collect_map_data( $( '#js-wpv-addon-maps-render-' + settings.map_id ) );

		if ( API_GOOGLE === views_addon_maps_i10n.api_used ) {
			self.init_map_after_loading_styles( mpdata );
		} else {
			$( '#js-wpv-addon-maps-render-' + settings.map_id ).empty();
			self.maps[settings.map_id] = self.resolveGeolocatedMarkerThenInitMapAzure( mpdata );
		}
	});

	/**
	* WPViews.view_addon_maps.get_map
	*
	* @param map_id
	*
	* @return google.maps.Map object | false
	*
	* @since 1.0
	*/

	self.get_map = function( map_id ) {
		if ( map_id in self.maps ) {
			return self.maps[ map_id ];
		} else {
			return false;
		}
	};

	/**
	* WPViews.view_addon_maps.get_map_marker
	*
	* @param marker_id
	* @param map_id
	*
	* @return google.maps.Marker object | false
	*
	* @since 1.0
	*/

	self.get_map_marker = function( marker_id, map_id ) {
		if (
			map_id in self.markers
			&& marker_id in self.markers[ map_id ]
		) {
			return self.markers[ map_id ][ marker_id ];
		} else {
			return false;
		}
	};

	// ------------------------------------
	// Interaction
	// ------------------------------------

	/**
	* Reload on js-wpv-addon-maps-reload-map.click
	*
	* @since 1.0
	*/

	$( document ).on( 'click', '.js-wpv-addon-maps-reload-map', function( e ) {
		e.preventDefault();
		var thiz = $( this );
		if ( thiz.attr( 'data-map' ) ) {
			self.reload_map( thiz.data( 'map' ) );
		}
	});

	/**
	* Center on a marker on js-wpv-addon-maps-center-map.click
	*
	* @since 1.0
	* @since 1.5 Azure API supported
	*/
	$( document ).on( 'click', '.js-wpv-addon-maps-focus-map', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
		thiz_map,
		thiz_marker,
		thiz_zoom;
		if (
			thiz.attr( 'data-map' )
			&& thiz.attr( 'data-marker' )
		) {
			thiz_map = thiz.data( 'map' );
			thiz_marker = thiz.data( 'marker' );

			if ( API_GOOGLE === views_addon_maps_i10n.api_used ) {
				if (
					thiz_map in self.maps
					&& thiz_map in self.markers
					&& thiz_marker in self.markers[thiz_map]
				) {
					thiz_zoom = ($('#js-wpv-addon-maps-render-' + thiz_map).data('singlezoom') != '')
						? $('#js-wpv-addon-maps-render-' + thiz_map).data('singlezoom')
						: 14;
					self.maps[thiz_map].setCenter(self.markers[thiz_map][thiz_marker].getPosition());
					self.maps[thiz_map].setZoom(thiz_zoom);
				}
			} else {
				if (
					thiz_map in self.maps
				) {
					thiz_zoom = ($('#js-wpv-addon-maps-render-' + thiz_map).data('singlezoom') != '')
						? $('#js-wpv-addon-maps-render-' + thiz_map).data('singlezoom')
						: 14;
					var $thisMarker = $('.js-wpv-addon-maps-marker-' + thiz_marker);

					self.maps[thiz_map].setCamera({
						center: [$thisMarker.eq(0).data('markerlon'), $thisMarker.eq(0).data('markerlat')],
						zoom: thiz_zoom
					});
				}
			}
		}
	});

	/**
	* Center map on fitbounds on js-wpv-addon-maps-center-map-fitbounds.click
	*
	* @todo make the restore work when having several points and no fitbounds, or a single point and no single_zoom or single_center
	*
	* @since 1.0
	*/

	$( document ).on( 'click', '.js-wpv-addon-maps-restore-map', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
		thiz_map,
		current_map_data_array,
		current_map_data;
		if ( thiz.attr( 'data-map' ) ) {
			thiz_map = thiz.data( 'map' );
			if (
				thiz_map in self.maps
				&& thiz_map in self.bounds
			) {
				self.maps[ thiz_map ].fitBounds( self.bounds[ thiz_map ] );
			}

			if ( _.size( self.markers[ thiz_map ] ) == 1 ) {
				current_map_data_array = _.filter( self.maps_data, function( map_data_unique ) {
					return map_data_unique.map == thiz_map;
				});
				current_map_data = _.first( current_map_data_array );
				if ( current_map_data.options['single_zoom'] != '' ) {
					self.maps[ thiz_map ].setZoom( current_map_data.options['single_zoom'] );
					if ( current_map_data.options['fitbounds'] == 'on' ) {
						self.api.event.addListenerOnce( self.maps[ thiz_map ], 'bounds_changed', function( event ) {
							self.maps[ thiz_map ].setZoom( current_map_data.options['single_zoom'] );
						});
					}
				}
				if ( current_map_data.options['single_center'] == 'on' ) {
					for ( var mark in self.markers[ thiz_map ] ) {
						self.maps[ thiz_map ].setCenter( self.markers[ thiz_map ][ mark ].getPosition() );
						break;
					}
				}
			}

			if ( API_AZURE === views_addon_maps_i10n.api_used ) {
				current_map_data_array = _.filter( self.maps_data, function( map_data_unique ) {
					return map_data_unique.map == thiz_map;
				});
				current_map_data = _.first( current_map_data_array );

				if ( _.size( current_map_data.markers ) === 1 ) {
					var $thisMarker = $('.js-wpv-addon-maps-markerfor-' + thiz_map);
					self.maps[thiz_map].setCamera({
						center: [$thisMarker.eq(0).data('markerlon'), $thisMarker.eq(0).data('markerlat')],
						zoom: current_map_data.options.single_zoom
					});
				} else {
					self.maybeFitboundsAzure(current_map_data, self.maps[thiz_map]);
				}
			}
		}
	});

	// ------------------------------------
	// Views compatibility
	// ------------------------------------

	/**
	* Reload the maps contained on the parametric search results loaded via AJAX
	*
	* @since 1.0
	*/

	$( document ).on( 'js_event_wpv_parametric_search_results_updated', function( event, data ) {
		var affected_maps = self.get_affected_maps( data.layout );
		_.each( affected_maps, self.reload_map );
	});

	/**
	* Reload the maps contained on the pagination results loaded via AJAX
	*
	* @since 1.0
	*/

	$( document ).on( 'js_event_wpv_pagination_completed', function( event, data ) {
		var affected_maps = self.get_affected_maps( data.layout );
		_.each( affected_maps, self.reload_map );
	});

	/**
	* get_affected_maps
	*
	* Get all the maps that have related data used in the given containr, no matter if a map render or a marker
	*
	* @param container	object
	*
	* @return array
	*
	* @since 1.1
	*/

	self.get_affected_maps = function( container ) {
		var affected_maps = [];
		container.find( '.js-wpv-addon-maps-render' ).each( function() {
			affected_maps.push( $( this ).data( 'map' ) );
		});
		container.find( '.js-wpv-addon-maps-marker' ).each( function() {
			affected_maps.push( $( this ).data( 'markerfor' ) );
		});
		affected_maps = _.uniq( affected_maps );
		return affected_maps;
	};

	// ------------------------------------
	// Clusters definitions and interactions
	// ------------------------------------

	/**
	* WPViews.view_addon_maps.set_cluster_options
	*
	* Sets options for clusters, either global or for a specific map
	*
	* @param options	object
	*		@param options.styles = [
	* 			{
	* 				url:		string		URL of the cluster image for this style,
	* 				height:		integer		Width of the cluster image for this style,
	* 				width:		integer		Height of the cluster image for this style,
	* 				textColor:	string		(optional) Color of the counter text in this cluster, as a color name or hex value (with #),
	* 				textSize	integer		(optional) Text size for the counter in this cluster, in px (without unit)
	* 			},
	* 			{ ... }
	* 		]
	* 		@param options.calculator = function( markers, numStyles ) {
	* 			@param markers		array	Markers in this cluster
	* 			@param numStyles	integer	Number of styles defined
	* 			@return {
	* 				text:	string		Text to be displayed inside the marker,
	* 				index:	integer		Index of the options.styles array that will be applied to this cluster - please make it less than numStyles
	* 			};
	* 		}
	* @param map_id		(optional) string The map ID this options will be binded to, global otherwise
	*
	* @note Most of the cluster options for a map are set in the map shortcode
	* @note Maps without specific styling options will get the current global options
	* @note We stoe the options in self.has_cluster_options for later usage, like on reload events
	*
	* @since 1.0
	*/

	self.set_cluster_options = function( options, map_id ) {
		if ( typeof map_id === 'undefined' ) {
			// If map_id is undefined, set global options
			if ( _.has( options , "styles" ) ) {
				self.default_cluster_options['styles'] = options['styles'];
			}
			if ( _.has( options , "calculator" ) ) {
				self.default_cluster_options['calculator'] = options['calculator'];
			}
		} else {
			// Otherwise, bind to a specific map ID
			// Note that defaults are also used
			self.cluster_options[ map_id ] = self.get_cluster_options( map_id );
			if ( _.has( options , "styles" ) ) {
				self.cluster_options[ map_id ]['styles'] = options['styles'];
			}
			if ( _.has( options , "calculator" ) ) {
				self.cluster_options[ map_id ]['calculator'] = options['calculator'];
			}
			self.has_cluster_options[ map_id ] = options;
		}
	};

	/**
	* WPViews.view_addon_maps.get_cluster_options
	*
	* Gets options for clusters, either global of for a specific map
	*
	* @param map_id		(optional) string	The map ID to get options from
	*
	* @return options	object				Set of options, either global or dedicated if the passed map_id has specific options
	*
	* @since 1.0
	*/

	self.get_cluster_options = function( map_id ) {
		var options = self.default_cluster_options;
		if (
			typeof map_id !== 'undefined'
			&& _.has( self.cluster_options, map_id )
		) {
			options = self.cluster_options[ map_id ];
		}
		return options;
	};

	// ------------------------------------
	// Init
	// ------------------------------------

	/**
	 * Inits Google Maps API specific code paths
	 * @since 1.5
	 */
	self.initGoogle = function() {
		self.api.event.addDomListener( window, 'load', self.init_maps );

		self.api.event.addDomListener( window, "resize", function() {
			_.each( self.maps, function( map_iter, map_id ) {
				self.keep_map_center_and_resize( map_iter );
			});
		});

		$( document ).on( 'js_event_wpv_layout_responsive_resize_completed', function( event ) {
			$( '.js-wpv-layout-responsive .js-wpv-addon-maps-render' ).each( function() {
				var thiz = $( this ),
					thiz_map = thiz.data( 'map' );
				if ( thiz_map in self.maps ) {
					self.keep_map_center_and_resize( self.maps[ thiz_map ] );
				} else {
					self.resize_queue.push( thiz_map );
					_.uniq( self.resize_queue );
				}
			});
		});

		self.maybeInitElementorEditorPreviewFix( self.init_maps );
	};

	/**
	 * Inits Microsoft Azure Maps specific code paths
	 * @since 1.5
	 */
	self.initAzure = function() {
		self.initMapsAzure();
		self.maybeInitElementorEditorPreviewFix( self.initMapsAzure );
	};

	/**
	 * Initialize maps on Elementor widget ready so it works when previewing an Elementor design.
	 *
	 * @since 1.5.3
	 *
	 * @param {Function} mapInitCallback
	 */
	self.maybeInitElementorEditorPreviewFix = function( mapInitCallback ) {
		if (typeof elementor === 'object' && typeof elementorFrontend === 'object') {
			elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
				var mapInScope = $scope.find('div.js-wpv-addon-maps-render');
				if (mapInScope.length) {
					self.collect_maps_data();
					mapInitCallback();
				}
			});
		}
	};

	/**
	 * Initializes a single map by given id, and also cleans up if map with same id was already initialized.
	 *
	 * API agnostic.
	 *
	 * @since 1.7
	 * @param {string} id
	 */
	self.initMapById = function( id ) {
		self.clean_map_data( id );

		const mapData = self.collect_map_data( $( 'div#js-wpv-addon-maps-render-' + id ) );

		if ( API_GOOGLE === views_addon_maps_i10n.api_used ) {
			self.init_map_after_loading_styles( mapData );
			// This is emitted only by collect_maps_data, not by the single map data collector that's used here, and is
			// needed by add_current_visitor_location_after_geolocation in order to run at the right time. (Google API
			// only).
			$( document ).trigger('js_event_wpv_addon_maps_map_data_collected');
		} else {
			self.maps[id] = self.resolveGeolocatedMarkerThenInitMapAzure( mapData );
		}
	};

	self.init = function() {
		self.collect_maps_data();

		if ( API_GOOGLE === views_addon_maps_i10n.api_used ) {
			self.initGoogle();
		} else {
			self.initAzure();
		}
	};

	self.init();
};

jQuery( document ).ready( function( $ ) {
	WPViews.view_addon_maps = new WPViews.ViewAddonMaps( $ );
});
