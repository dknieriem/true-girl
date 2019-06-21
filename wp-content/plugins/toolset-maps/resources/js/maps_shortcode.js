/**
 * API and helper functions for the GUI on Maps shortcodes.
 *
 * @since 1.5
 * @package Maps
 */

var Toolset = Toolset || {};

if ( typeof Toolset.Maps === "undefined" ) {
	Toolset.Maps = {};
}

/*
 * -------------------------------------
 * Shortcode GUI
 * -------------------------------------
 */
Toolset.Maps.ShortcodeManager = function( $ ) {
	"use strict";

	/** @var {Object} maps_shortcode_i18n */

	var self = this;

	const API_GOOGLE = 'google';

	self.dialogs = {};
	self.dialogs.shortcode = null;
	// TODO: move spinner to template
	self.shortcodeDialogSpinnerContent = $(
		'<div style="min-height: 150px;">' +
		'<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; ">' +
		'<div class="ajax-loader"></div>' +
		'<p>' + maps_shortcode_i18n.action.loading + '</p>' +
		'</div>' +
		'</div>'
	);
	self.templates = {
		noGeolocationMessage: wp.template( 'toolset-views-maps-dialogs-no-geolocation-message' )
	};
	self.counters = {
		map: maps_shortcode_i18n.counters.map,
		marker: maps_shortcode_i18n.counters.marker
	};
	self.lastInsertedMapId = null;

	/**
	 * Display a dialog for inserting a generic shortcode.
	 *
	 * @param dialogData object
	 *     shortcode  string Shortcode name.
	 *     title      string Form title.
	 *     parameters object Optional. Hidden parameters to enforce as attributes for the resulting shortcode.
	 *     overrides  object Optional. Attribute values to override/enforce, mainly when editing a shortcode.
	 *
	 * @since 1.5
	 */
	self.shortcodeDialogOpen = function( dialogData ) {

		Toolset.hooks.doAction('wpv-action-wpv-fields-and-views-dialog-do-maybe-close');

		/**
		 * Toolset hooks action: shortcode dialog requested.
		 *
		 * Nothing has happened yet, we just got a request to open the shortcode dialog.
		 *
		 * @since 1.5
		 */
		Toolset.hooks.doAction( 'toolset-action-shortcode-dialog-requested', dialogData );

		// Show the "empty" dialog with a spinner while loading dialog content
		self.dialogs.shortcode.dialog( 'open' ).dialog({
			title: dialogData.title
		});
		self.dialogs.shortcode.html( self.shortcodeDialogSpinnerContent );

		/**
		 * Toolset hooks action: shortcode dialog preloaded.
		 *
		 * The dialog is open and contains a spinner.
		 *
		 * @since 1.5
		 */
		Toolset.hooks.doAction( 'toolset-action-shortcode-dialog-preloaded', dialogData );

		// Warning!! The shortcodes data is stored in maps_shortcode_i18n, but assigning any of the objects it contains
		// is done by reference so it would modify permanently the original set.
		// Using $.extend with deep cloning.
		var mapsShortcodeData = $.extend( true, {}, maps_shortcode_i18n );

		// Use the shortcode attributes for given shortcode
		var shortcodeAttributes = mapsShortcodeData.attributes[dialogData.shortcode];

		// Add the templates and attributes to the main set of data, and render the dialog
		var templateData = _.extend(
			dialogData,
			{
				templates:  self.templates,
				attributes: shortcodeAttributes
			}
		);
		self.dialogs.shortcode.html( self.templates.dialog( templateData ) );

		// Initialize the dialog tabs, if needed
		if ( self.dialogs.shortcode.find( '.js-toolset-shortcode-gui-tabs-list > li' ).length > 1 ) {
			self.dialogs.shortcode.find( '.js-toolset-shortcode-gui-tabs' )
				.tabs({
					beforeActivate: function( event, ui ) {

						var valid = Toolset.hooks.applyFilters( 'toolset-filter-is-shortcode-attributes-container-valid', true, ui.oldPanel );
						if ( ! valid ) {
							event.preventDefault();
							ui.oldTab.focus().addClass( 'toolset-shortcode-gui-tabs-incomplete' );
							setTimeout( function() {
								ui.oldTab.removeClass( 'toolset-shortcode-gui-tabs-incomplete' );
							}, 1000 );
						}
					}
				})
				.addClass( 'ui-tabs-vertical ui-helper-clearfix' )
				.removeClass( 'ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all' );
			$( '#js-toolset-shortcode-gui-dialog-tabs ul, #js-toolset-shortcode-gui-dialog-tabs li' )
				.removeClass( 'ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');
		} else {
			self.dialogs.shortcode.find( '.js-toolset-shortcode-gui-tabs-list' ).remove();
		}

		/**
		 * Toolset hooks action: shortcode dialog loaded.
		 *
		 * The dialog is open and contains the attributes GUI.
		 *
		 * @since 1.5
		 */
		Toolset.hooks.doAction( 'toolset-action-shortcode-dialog-loaded', dialogData );
	};

	self.initHooks = function() {
		// Just until map shortcode is transferred to toolset-shortcode framework too!
		// When map shortcode is inserted, update marker default for map id.
		$( document ).on( 'js_event_wpv_shortcode_action_completed', function( event, shortcode_data ) {
			var nextMapId = self.counters.map + 1;

			if ( shortcode_data.name === 'wpv-map-render' ) {
				if ( shortcode_data.attributes.map_id === 'map-' + nextMapId ) {
					self.counters.map++;
					maps_shortcode_i18n.attributes['wpv-map-marker'].marker.fields.map_id.defaultForceValue='map-'+nextMapId;
				} else {
					self.lastInsertedMapId = shortcode_data.attributes.map_id;
					maps_shortcode_i18n.attributes['wpv-map-marker'].marker.fields.map_id.defaultForceValue=self.lastInsertedMapId;
				}
			}
		} );

		Toolset.hooks.addFilter(
			'toolset-filter-shortcode-gui-wpv-map-marker-computed-attribute-values',
			function( shortcodeAttributeValues, shortcodeData ) {
				var rawAttributes = shortcodeData.rawAttributes;
				// A general rule: attributes that we do not want added to the final shortcode
				// are just valued as FALSE, no need to remove them.
				// We need some fields rendered, but not turned into attributes. In order to make this generic, we have
				// a simple rule: attributes with names starting with _ get filtered out.
				let computedAttributeValues = _.omit( shortcodeAttributeValues, function( value, attribute ) {
					return ( attribute.lastIndexOf( '_', 0) === 0 );
				} );

				// Remove all extra attributes
				computedAttributeValues.marker_source_options = false;
				computedAttributeValues.marker_source_meta = false;

				computedAttributeValues.address = false;

				computedAttributeValues.postmeta = false;
				computedAttributeValues.postmeta_id = false;

				computedAttributeValues.termmeta = false;
				computedAttributeValues.termmeta_id = false;

				computedAttributeValues.usermeta = false;
				computedAttributeValues.usermeta_id = false;

				computedAttributeValues.lat = false;
				computedAttributeValues.lon = false;

				computedAttributeValues.map_render = false;


				// Populate all needed attributes with right values
				// Only add the item/id attribute if needed, of course
				switch ( rawAttributes.marker_source_options ) {
					case 'address':
						computedAttributeValues.address = rawAttributes.address;
						break;
					case 'postmeta':
						computedAttributeValues.marker_field = rawAttributes.postmeta;
						// Add the attribute depending on whether m2m is active
						if ( 'current' != rawAttributes.postmeta_id ) {
							if ( maps_shortcode_i18n.data.m2m.enabled ) {
								computedAttributeValues.item = rawAttributes.postmeta_id;
							} else {
								computedAttributeValues.id = rawAttributes.postmeta_id;
							}
						}
						break;
					case 'termmeta':
						computedAttributeValues.marker_termmeta = rawAttributes.termmeta;
						if ( 'viewloop' != rawAttributes.termmeta_id ) {
							computedAttributeValues.id = rawAttributes.termmeta_id;
						}
						break;
					case 'usermeta':
						computedAttributeValues.marker_usermeta = rawAttributes.usermeta;
						if ( 'current' != rawAttributes.usermeta_id ) {
							computedAttributeValues.id = rawAttributes.usermeta_id;
						}
						break;
					case 'latlon':
						computedAttributeValues.lat = rawAttributes.lat;
						computedAttributeValues.lon = rawAttributes.lon;
						break;
					case 'browser_geolocation':
						computedAttributeValues.current_visitor_location = 'true';
						computedAttributeValues.map_render = rawAttributes.map_render;
						break;
				}

				return computedAttributeValues;
			}
		);

		// Massage distance value shortcode output
		Toolset.hooks.addFilter(
			'toolset-filter-shortcode-gui-toolset-maps-distance-value-computed-attribute-values',
			function( shortcodeAttributeValues, shortcodeData ) {
				switch( shortcodeData.rawAttributes.target_source ) {
					case 'postmeta':
						delete( shortcodeAttributeValues.termmeta );
						delete( shortcodeAttributeValues.usermeta );
						break;
					case 'termmeta':
						delete( shortcodeAttributeValues.postmeta );
						delete( shortcodeAttributeValues.usermeta );
						break;
					case 'usermeta':
						delete( shortcodeAttributeValues.postmeta );
						delete( shortcodeAttributeValues.termmeta );
						break;
				}

				// Convert lat/lon to an address, as it's handled like an address through the pipeline
				if ( shortcodeData.rawAttributes.origin_source === 'latlon' ) {
					shortcodeAttributeValues.location = '{'+shortcodeData.rawAttributes.lat+','
						+shortcodeData.rawAttributes.lon+'}';
					delete( shortcodeAttributeValues.lat );
					delete( shortcodeAttributeValues.lon );
					delete( shortcodeAttributeValues.origin_source );
				}

				if (
					'termmeta_id' in shortcodeAttributeValues
					&& shortcodeAttributeValues.termmeta_id === 'viewloop'
				) {
					delete shortcodeAttributeValues.termmeta_id;
				}

				return shortcodeAttributeValues;
			}
		);

		// Change "reload" button output from a shortcode to a HTML element
		Toolset.hooks.addFilter(
			'toolset-filter-shortcode-gui-reload_button-crafted-shortcode',
			function( shortcode, formData )	{
				let attributes = formData.rawAttributes;
				let elementStart = '';
				let elementEnd = '';
				let styles = attributes.styles ? ' style="' + attributes.styles + '"' : '';
				let classNames = attributes.classnames ? ' ' + attributes.classnames : '';

				if ( attributes.html_element === 'link' ) {
					elementStart = '<a href="#"';
					elementEnd = '</a>';
				} else {
					elementStart = '<button';
					elementEnd = '</button>';
				}

				return elementStart
					+' class="js-wpv-addon-maps-reload-map'
					+classNames
					+'" data-map="'
					+attributes.map_id
					+'"'
					+styles
					+'>'
					+attributes.anchor_text
					+elementEnd;
			}
		);

		// Using this filter because it contains formData, so we can update marker id and defaults for map & marker ids.
		// Not changing shortcode itself.
		Toolset.hooks.addFilter(
			'toolset-filter-shortcode-gui-wpv-map-marker-crafted-shortcode',
			function( shortcode, formData )	{
				var nextMarkerId = self.counters.marker + 1;
				var nextMarker = 'marker-' + nextMarkerId;

				// If the marker ID used was in the usual format of 'marker-n+1', save it locally, on server, and update
				// the default value for next marker dialog to 'marker-n+2'.
				if ( formData.attributes.marker_id === nextMarker ) {
					self.counters.marker++;
					nextMarkerId++;
					nextMarker = 'marker-' + nextMarkerId;

					maps_shortcode_i18n.attributes['wpv-map-marker'].marker.fields.marker_id.defaultForceValue = nextMarker;
					self.updateCounters();
				}

				// Logic for map ids: it should be in the usual format 'map-n'. If it isn't, save lastInsertedMapId
				// client-side and use that one.
				if ( formData.attributes.map_id !== 'map-' + self.counters.map ) {
					self.lastInsertedMapId = formData.attributes.map_id;
					maps_shortcode_i18n.attributes['wpv-map-marker'].marker.fields.map_id.defaultForceValue = self.lastInsertedMapId;
				}

				return shortcode;
			}
		);

		// Add special functionality after the dialog is loaded
		Toolset.hooks.addAction(
			'toolset-action-shortcode-dialog-loaded',
			function( dialogData ) {
				// Set current active editor
				WPViews.addon_maps_dialogs.current_active_editor = window.wpcfActiveEditor;

				// Dialog specific functionalities, tweaks, etc.
				switch ( dialogData.shortcode ) {
					case "wpv-map-marker":
						// Field dependencies init
						self.initDependsOn( dialogData );
						// Marker icons highlights and upload button
						self.initMarkerIcons();
						// Move some fields around so that forms look nicer
						self.initMarkerSource();
						break;
					case "toolset-maps-distance-conditional-display":
						self.initDistanceConditionalDisplaySource();
						break;
					case "toolset-maps-distance-value":
						self.initDependsOn( dialogData );
						self.initDistanceValueSource();
						break;
					case "reload_button":
						self.makeDialogNarrower();
						break;
				}
			}
		);

		// Change marker icon highlights when selected icon changes
		$( document ).on(
			'change',
			'.js-toolset-shortcode-gui-attribute-wrapper-for-marker_icon .js-shortcode-gui-field, '
			+ '.js-toolset-shortcode-gui-attribute-wrapper-for-marker_icon_hover .js-shortcode-gui-field',
			function() {
				WPViews.addon_maps_dialogs.highlight_selected(
					$( this ).closest( '.js-toolset-shortcode-gui-attribute-wrapper' )
				);
			}
		);

		return self;
	};

	/**
	 * Adds address autocomplete to a given input field.
	 * @since 1.6
	 * @param jQuery $location
	 */
	self.addAddressAutocomplete = function ( $location ) {
		if ( API_GOOGLE === views_addon_maps_i10n.api_used ) {
			$location.geocomplete();
		} else {
			$location.addClass( 'js-toolset-maps-address-autocomplete' );
			WPViews.mapsAddressAutocomplete.initField( $location );
		}
	};

	/**
	 * Dialogs with no tabs look somewhat better when a bit narrower.
	 * @since 1.7.1
	 */
	self.makeDialogNarrower = function() {
		$( 'div#js-maps-shortcode-gui-dialog-container-shortcode').parent( 'div' ).css( 'width', '80%' );
	};

	/**
	 * Stuff specific for distance conditional display dialog
	 * @since 1.6
	 */
	self.initDistanceConditionalDisplaySource = function() {
		// Since it has no tabs, this dialog looks somewhat better when a bit narrower
		self.makeDialogNarrower();

		// Init autocomplete for address field
		self.addAddressAutocomplete( $( 'input#toolset-maps-distance-conditional-display-location' ) );
	};

	/**
	 * Stuff specific for distance value dialog
	 * @since 1.6
	 */
	self.initDistanceValueSource = function() {
		self.makeDialogNarrower();

		// Init autocomplete for address field
		self.addAddressAutocomplete( $( 'input#toolset-maps-distance-value-location' ) );

		// Disable the geolocation option in case of no HTTPS
		if ( ! maps_shortcode_i18n.data.geolocation.enabled ) {
			$( '.js-shortcode-gui-field[value="visitor_location"]' )
				.prop( 'disabled', true )
				.closest( 'li' )
					.append( self.templates.noGeolocationMessage( {} ) );
		}

		// Make 2-column GUI for 1st location
		self.moveAttributesToRightColumn(
			$( '.js-toolset-shortcode-gui-attribute-wrapper-for-location_source_meta' ),
			[ 'location', 'url_param', 'lat', 'lon' ],
			$( '#toolset-maps-distance-value-location_source_meta' )
		);
		// Make 2-column GUI for 2nd location
		self.moveAttributesToRightColumn(
			$( '.js-toolset-shortcode-gui-attribute-wrapper-for-target_source_meta' ),
			[ 'postmeta', 'postmeta_id', 'termmeta', 'termmeta_id', 'usermeta', 'usermeta_id' ],
			$( '#toolset-maps-distance-value-target_source_meta' )
		);

		// Check if post, user & taxonomy fields exist. Disable radios for those that don't.
		_.each( ['postmeta', 'termmeta', 'usermeta'], function( field ) {
			if ( !$( 'select#toolset-maps-distance-value-' + field ).eq( 0 ).val() ) {
				$( 'input[name="toolset-maps-distance-value-target_source"][value="' + field + '"]')
					.prop( 'disabled', true )
					.prop( 'checked', false );
			}
		} );
		// If postmeta disabled, switch to another one that's enabled
		if ( $( 'input[name="toolset-maps-distance-value-target_source"][value="postmeta"]').is(':disabled') ) {
			let hasFields = _.find(['termmeta', 'usermeta'], function (field) {
				return $( 'input[name="toolset-maps-distance-value-target_source"][value="' + field + '"]')
					.is(':enabled')
			} );
			if ( hasFields ) {
				$( 'input[name="toolset-maps-distance-value-target_source"][value="' + hasFields + '"]')
					.prop( 'checked', true )
					.trigger( 'change' );
			}
		}

		// If no fields available, disable insert shortcode button and hide useless fields selection options
		if (
			!_.some( ['postmeta', 'termmeta', 'usermeta'], function( field ) {
				return $( 'input[name="toolset-maps-distance-value-target_source"][value="' + field + '"]')
					.is(':enabled')
			} )
		) {
			$( 'button.js-maps-shortcode-gui-button-craft' ).prop( 'disabled', true );
			$( 'div.js-toolset-shortcode-gui-attribute-wrapper-for-target_source_meta' ).hide();
		} else {
			// Select the first radio for where is the post coming from
			$('.js-toolset-shortcode-gui-attribute-wrapper-for-postmeta_id .js-toolset-shortcode-gui-item-selector[checked]')
				.prop('checked', true)
				.trigger('change');
		}
	};

	/**
	 * Move given attributes to the 2nd column of a 2-column GUI (group). Hide dummy from 2nd column.
	 *
	 * For when we need the UI of a 2-column group, but want to put multiple inputs to 2nd column, and then use
	 * dependsOn to show the appropriate one.
	 *
	 * @since 1.6
	 *
	 * @param jQuery $container
	 * @param {array} attributes
	 * @param jQuery $dummy
	 */
	self.moveAttributesToRightColumn = function( $container, attributes, $dummy ) {
		// Hide the dummy attribute used to have a 2-column GUI
		$dummy.hide();

		// Move all relevant attributes to the 2nd column
		_.each( attributes, function( attribute, attributeIndex, attributeList ) {
			$( '.js-toolset-shortcode-gui-attribute-wrapper-for-' + attribute )
				.detach()
				.appendTo( $container );
		});
	};

	self.initTemplates = function() {
		// Gets the shared pool
		self.templates = _.extend(
			Toolset.hooks.applyFilters( 'toolset-filter-get-shortcode-gui-templates', {} ),
			self.templates
		);

		return self;
	};

	self.initDialogs = function() {
		/**
		 * Canonical dialog to insert shortcodes.
		 *
		 * @since 1.5
		 */
		if ( ! $( '#js-maps-shortcode-gui-dialog-container-shortcode' ).length ) {
			$( 'body' ).append(
				'<div id="js-maps-shortcode-gui-dialog-container-shortcode" class="toolset-shortcode-gui-dialog-container js-toolset-shortcode-gui-dialog-container js-maps-shortcode-gui-dialog-container js-maps-shortcode-gui-dialog-container-shortcode"></div>'
			);
		}
		self.dialogs.shortcode = $( "#js-maps-shortcode-gui-dialog-container-shortcode" ).dialog({
			dialogClass: 'toolset-ui-dialog toolset-ui-dialog-responsive',
			autoOpen:	false,
			modal:		true,
			width:		'90%',
			resizable:	false,
			draggable:	false,
			show: {
				effect:		"blind",
				duration:	800
			},
			open: function( event, ui ) {
				$( 'body' ).addClass( 'modal-open' );
				self.repositionDialog();
			},
			close: function( event, ui ) {
				$( 'body' ).removeClass( 'modal-open' );
				self.offDependsOn();
			},
			buttons:[
				{
					class: 'toolset-shortcode-gui-dialog-button-align-right button-primary js-maps-shortcode-gui-button-craft',
					text: maps_shortcode_i18n.action.insert,
					click: function() {
						var shortcodeToInsert = Toolset.hooks.applyFilters( 'toolset-filter-get-crafted-shortcode', false, $( '#js-maps-shortcode-gui-dialog-container-shortcode' ) );
						// shortcodeToInsert will fail on validation failure
						if ( shortcodeToInsert ) {
							$( this ).dialog( "close" );
							Toolset.hooks.doAction( 'toolset-action-do-shortcode-gui-action', shortcodeToInsert );
						}
					}
				},
				{
					class: 'toolset-shortcode-gui-dialog-button-align-right button-secondary toolset-shortcode-gui-dialog-button-back js-maps-shortcode-gui-button-back',
					text: maps_shortcode_i18n.action.back,
					click: function() {
						$( this ).dialog( "close" );
						// Open the Fields and Views dialog
						Toolset.hooks.doAction( 'wpv-action-wpv-fields-and-views-dialog-do-open' );
					}
				},
				{
					class: 'button-secondary toolset-shortcode-gui-dialog-button-close js-maps-shortcode-gui-button-close',
					text: maps_shortcode_i18n.action.cancel,
					click: function() {
						$( this ).dialog( "close" );
					}
				}
			]
		});

		return self;
	};

	/**
	 * Special functionality for marker icons.
	 * @return {Object}
	 */
	self.initMarkerIcons = function(){
		// Highlight first options for marker icon and icon hover lists
		let $marker_container = $( '.js-toolset-shortcode-gui-attribute-wrapper-for-marker_icon' );
		let $marker_hover_container = $( '.js-toolset-shortcode-gui-attribute-wrapper-for-marker_icon_hover' );

		WPViews.addon_maps_dialogs.highlight_selected( $marker_container );
		WPViews.addon_maps_dialogs.highlight_selected( $marker_hover_container );
		$( '.js-wpv-shortcode-gui-attribute-wrapper-for-marker_icon_hover ul' ).hide();

		// Hide the list for the hover icons behind a set of radio buttons
		$marker_hover_container
			.find( 'h3' )
			.after( WPViews.addon_maps_dialogs.add_marker_hover_options() );

		// Add a button to upload a new marker icon in case the user has the right rights
		if ( wpv_addon_maps_dialogs_local.can_manage_options === 'yes' ) {
			$marker_container
				.append(
					WPViews.addon_maps_dialogs.upload_button_template({
						context: 'marker',
						type: 'image',
						button_text: wpv_addon_maps_dialogs_local.add_marker_icon
					})
				);
		}

		return self;
	};

	self.initMarkerSource = function() {
		var $container = $( '.js-toolset-shortcode-gui-attribute-wrapper-for-marker_source_meta', '#wpv-map-marker-marker' ),
			attributesToDetach = [ 'address', 'postmeta', 'postmeta_id', 'termmeta', 'termmeta_id', 'usermeta', 'usermeta_id', 'lat', 'lon', 'map_render' ];

		// Hide the dummy attribute used to have a tabbed GUI
		$( '#wpv-map-marker-marker_source_meta' ).hide();

		// Disable the geolocation option in case of no HTTPS
		if ( ! maps_shortcode_i18n.data.geolocation.enabled ) {
			$( '.js-shortcode-gui-field[value="browser_geolocation"]', '#wpv-map-marker-marker' )
				.prop( 'disabled', true )
				.closest( 'li' )
					.append( self.templates.noGeolocationMessage( {} ) );
		}

		// Move all relevant attributes to the tabbed GUI
		_.each( attributesToDetach, function( attribute, attributeIndex, attributeList ) {
			$( '.js-toolset-shortcode-gui-attribute-wrapper-for-' + attribute, '#wpv-map-marker-marker' )
				.detach()
				.appendTo( $container );
		});
		$( '.js-toolset-shortcode-gui-attribute-group-for-latlon_group', '#wpv-map-marker-marker' )
			.detach()
			.appendTo( $container );

		// Init geocomplete for address field
		if ( API_GOOGLE === views_addon_maps_i10n.api_used ) {
			$('input#wpv-map-marker-address').geocomplete();
		} else {
			let $markerAddress = $( 'input#wpv-map-marker-address' );

			$markerAddress.addClass( 'js-toolset-maps-address-autocomplete' );
			WPViews.mapsAddressAutocomplete.initField( $markerAddress );
		}

		// Init select2 on xxx-meta selectors
		var metaTypes = [ 'post', 'term', 'user' ];
		_.each( metaTypes, function( metaType, metaIndex, metaList ) {
			$( '#wpv-map-marker-' + metaType + 'meta:not(.js-toolset-shortcode-gui-field-select2-inited)', '#wpv-map-marker-marker' ).each( function() {
				self.initMetaSuggest( $( this ), metaType );
			});
		});

		// Rename classnames and the name attributes for object selectors to avoid collissions
		_.each( metaTypes, function( metaType, metaIndex, metaList ) {
			$( '#wpv-map-marker-marker .js-toolset-shortcode-gui-attribute-wrapper-for-' + metaType + 'meta_id' ).each( function() {
				$( this )
					.find( '.js-toolset-shortcode-gui-item-selector' )
						.attr( 'name', 'toolset_shortcode_gui_' + metaType + '_object_id' );
				$( this )
					.find( '.js-toolset-shortcode-gui-item-selector' )
						.first()
							.prop( 'checked', true );
				$( this )
					.find( '.js-toolset-shortcode-gui-item-selector' )
						.addClass( 'js-toolset-shortcode-gui-item-selector-standby' )
						.removeClass( 'js-toolset-shortcode-gui-item-selector' );
				$( this )
					.find( '[name="specific_object_id"]' )
						.attr( 'name', 'specific_object_id_standby' );
				$( this )
					.find( '[name="specific_object_id_raw"]' )
						.attr( 'name', 'specific_object_id_raw_standby' );
			});
		});
	};

	self.initMetaSuggest = function( $selector, metaType ) {
		var $selectorParent = $selector.closest( '.js-toolset-shortcode-gui-dialog-container' );

		$selector
			.addClass( 'js-toolset-shortcode-gui-field-select2-inited' )
			.css( { width: '100%' } )
			.toolset_select2(
				{
					width:				'resolve',
					dropdownAutoWidth:	true,
					dropdownParent:		$selectorParent,
					minimumInputLength:	0,
					ajax: {
						url: toolset_shortcode_i18n.ajaxurl,
						dataType: 'json',
						delay: 250,
						type: 'post',
						data: function( params ) {
							return {
								action:  'toolset_maps_select2_suggest_meta',
								metaType: metaType,
								s:       params.term,
								page:    params.page
							};
						},
						processResults: function( originalResponse, params ) {
							var response = self.parseResponse( originalResponse );
							params.page = params.page || 1;
							if ( response.success ) {
								return {
									results: response.data,
								};
							}
							return {
								results: [],
							};
						},
						cache: false
					}
				}
			)
			.data( 'toolset_select2' )
				.$dropdown
					.addClass( 'toolset_select2-dropdown-in-dialog' );
	};

	self.parseResponse = function (response) {
        if (typeof(response.success) === 'undefined') {
            console.log("parseResponse: no success value", response);
            return {success: false};
        } else {
            return response;
        }
    };

	/**
	 * React on form changes and process 'dependsOn'
	 * @param {Object} dialogData
	 */
	self.initDependsOn = function( dialogData ) {
		let shortcode = dialogData.shortcode;

		// Note: this part is not generic, take care of it when the time comes to use this for other dialogs
		$( 'div#js-maps-shortcode-gui-dialog-container-shortcode input[type="radio"]' ).change( function (event) {
			let key = event.target.name.replace( shortcode + '-', '' );
			let value = event.target.value;

			// Check all fields...
			_.each( dialogData.attributes, function( tabData ) {
				_.each( tabData.fields, function( fieldData, field ) {
					// ...that have hide/show dependencies
					if ( 'dependsOn' in fieldData ) {
						let dependsOn = fieldData.dependsOn;

						// Singular dependency - we can just check if this is it
						if ( dependsOn.length === 1 ) {
							// Is our current key the dependency?
							if ( dependsOn[0].key === key ) {
								// If the value matches, show, if not, hide
								if ( dependsOn[0].value === value ) {
									$( 'div.js-toolset-shortcode-gui-attribute-wrapper-for-' + field )
										.show();
								} else {
									$( 'div.js-toolset-shortcode-gui-attribute-wrapper-for-' + field )
										.hide();
								}
							}
						}
						// Multiple dependencies - we need to check other fields' values as well
						else if ( dependsOn.length > 1 ) {
							// Check if current key is one of dependencies
							let dependencyKeys = _.pluck( dependsOn, 'key' );
							if ( _.contains( dependencyKeys, key ) ) {
								// Does the value for the current key match?
								let currentKeyValue = _.findWhere( dependsOn, {key: key} );
								if ( currentKeyValue.value === value ) {
									// Check other dependencies
									let otherDependsOn = _.reject( dependsOn, function (keyValue) {
										return keyValue === currentKeyValue;
									} );
									let otherDependenciesSatisfied = _.every( otherDependsOn, function( keyValue) {
										return $( 'input[name="'+shortcode+'-'+keyValue.key+'"]:checked' ).val() === keyValue.value;
									} );
									if ( otherDependenciesSatisfied ) {
										$( 'div.js-toolset-shortcode-gui-attribute-wrapper-for-' + field )
											.show();
									} else {
										$( 'div.js-toolset-shortcode-gui-attribute-wrapper-for-' + field )
											.hide();
									}
								} else {
									$( 'div.js-toolset-shortcode-gui-attribute-wrapper-for-' + field )
										.hide();
								}
							}
						}
					}
				} );
			} );
		} );
	};

	/**
	 * Turns off dependsOn dynamic showing/hiding of fields, because next time the dialog is opened, it might contain a
	 * different form for a different shortcode, and it needs to be initialized again.
	 */
	self.offDependsOn = function() {
		$( 'div#js-maps-shortcode-gui-dialog-container-shortcode input[type="radio"]' ).off('change');
	};

	/**
	 * Perform AJAX call to save the new values - both of them.
	 */
	self.updateCounters = function() {
		var data = {
			action: 'wpv_toolset_maps_addon_update_counters',
			map_counter: self.counters.map,
			marker_counter: self.counters.marker,
			wpnonce: wpv_addon_maps_dialogs_local.nonce
		};
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			dataType: "json"
		});
	};

	/**
	 * Reposition the dialogs based on the current window size.
	 *
	 * @since 1.5
	 */
	self.repositionDialog = function() {
		var winH = $( window ).height() - 100;

		self.dialogs.shortcode.dialog( "option", "maxHeight", winH );
		self.dialogs.shortcode.dialog( "option", "position", {
			my:        "center top+50",
			at:        "center top",
			of:        window,
			collision: "none"
		});
	};

	self.initEvents = function() {

		$( document ).on( 'change', '#wpv-map-marker-marker input[name="wpv-map-marker-marker_source_options"]', function() {
			// Adjust the required attributes based on the object source
			// This will provide:
			// - Validation: each object source option will force the right classname for its required items.
			// - Value crafting: each object source will force the right classname to compute the attributes.
			var source = $( 'input[name="wpv-map-marker-marker_source_options"]:checked', '#wpv-map-marker-marker' ).val(),
				$metaOptions = $( '.js-toolset-shortcode-gui-attribute-wrapper-for-marker_source_meta', '#wpv-map-marker-marker' );

			$metaOptions.find( '.js-toolset-shortcode-gui-required' )
				.removeClass( 'js-toolset-shortcode-gui-required' );

			$metaOptions.find( '.js-toolset-shortcode-gui-invalid-attr' )
				.removeClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );

			$metaOptions.find( '.js-toolset-shortcode-gui-item-selector-standby' )
				.removeClass( 'js-toolset-shortcode-gui-item-selector');

			$metaOptions
				.find( '[name="specific_object_id"]' )
					.attr( 'name', 'specific_object_id_standby' );
			$metaOptions
				.find( '[name="specific_object_id_raw"]' )
					.attr( 'name', 'specific_object_id_raw_standby' );

			switch( source ) {
				case 'address':
					$( '#wpv-map-marker-address' ).addClass( 'js-toolset-shortcode-gui-required' );
					break;
				case 'postmeta':
				case 'termmeta':
				case 'usermeta':
					$( '#wpv-map-marker-' + source ).addClass( 'js-toolset-shortcode-gui-required' );
					$( '.js-toolset-shortcode-gui-attribute-wrapper-for-' + source + '_id .js-toolset-shortcode-gui-item-selector-standby' )
						.addClass( 'js-toolset-shortcode-gui-item-selector' );
					$( '.js-toolset-shortcode-gui-attribute-wrapper-for-' + source + '_id [name="specific_object_id_standby"]' )
						.attr( 'name', 'specific_object_id' );
					$( '.js-toolset-shortcode-gui-attribute-wrapper-for-' + source + '_id [name="specific_object_id_raw_standby"]' )
						.attr( 'name', 'specific_object_id_raw' );
					$( '.js-toolset-shortcode-gui-attribute-wrapper-for-' + source + '_id .js-toolset-shortcode-gui-item-selector:checked' )
						.trigger( 'change' );
					break;
				case 'latlon':
					$( '#wpv-map-marker-lat' ).addClass( 'js-toolset-shortcode-gui-required' );
					$( '#wpv-map-marker-lon' ).addClass( 'js-toolset-shortcode-gui-required' );
					break;
			}
		});

		// Distance value GUI events
		$( document ).on(
			'change',
			'#toolset-maps-distance-value-distance_value input[name="toolset-maps-distance-value-origin_source"]',
			function() {
				let source = $(
					'input[name="toolset-maps-distance-value-origin_source"]:checked',
					'#toolset-maps-distance-value-distance_value'
				).val();
				let $metaOptions = $(
					'.js-toolset-shortcode-gui-attribute-wrapper-for-location_source_meta',
					'#toolset-maps-distance-value-distance_value'
				);

				$metaOptions.find( '.js-toolset-shortcode-gui-required' )
					.removeClass( 'js-toolset-shortcode-gui-required' );
				$metaOptions.find( '.js-toolset-shortcode-gui-invalid-attr' )
					.removeClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
				$metaOptions.find( '.js-toolset-shortcode-gui-item-selector-standby' )
					.removeClass( 'js-toolset-shortcode-gui-item-selector');
				$metaOptions.find( '[name="specific_object_id"]' )
					.attr( 'name', 'specific_object_id_standby' );
				$metaOptions.find( '[name="specific_object_id_raw"]' )
					.attr( 'name', 'specific_object_id_raw_standby' );

				switch( source ) {
					case 'address':
						$('#toolset-maps-distance-value-location').addClass('js-toolset-shortcode-gui-required');
						break;
					case 'url_param':
						$('#toolset-maps-distance-value-url_param').addClass('js-toolset-shortcode-gui-required');
						break;
				}
			}
		);
		$( document ).on(
			'change',
			'#toolset-maps-distance-value-distance_value input[name="toolset-maps-distance-value-target_source"]',
			function() {
				let source = $(
					'input[name="toolset-maps-distance-value-target_source"]:checked',
					'#toolset-maps-distance-value-distance_value'
				).val();
				let $metaOptions = $(
					'.js-toolset-shortcode-gui-attribute-wrapper-for-target_source_meta',
					'#toolset-maps-distance-value-distance_value'
				);

				$metaOptions.find( '.js-toolset-shortcode-gui-required' )
					.removeClass( 'js-toolset-shortcode-gui-required' );
				$metaOptions.find( '.js-toolset-shortcode-gui-invalid-attr' )
					.removeClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
				$metaOptions.find( '.js-toolset-shortcode-gui-item-selector-standby' )
					.removeClass( 'js-toolset-shortcode-gui-item-selector');
				$metaOptions.find( '[name="specific_object_id"]' )
					.attr( 'name', 'specific_object_id_standby' );
				$metaOptions.find( '[name="specific_object_id_raw"]' )
					.attr( 'name', 'specific_object_id_raw_standby' );

				$( '#toolset-maps-distance-value-target_source-' + source ).addClass( 'js-toolset-shortcode-gui-required' );
				$( '.js-toolset-shortcode-gui-attribute-wrapper-for-' + source + '_id .js-toolset-shortcode-gui-item-selector-standby' )
					.addClass( 'js-toolset-shortcode-gui-item-selector' );
				$( '.js-toolset-shortcode-gui-attribute-wrapper-for-' + source + '_id [name="specific_object_id_standby"]' )
					.attr( 'name', 'specific_object_id' );
				$( '.js-toolset-shortcode-gui-attribute-wrapper-for-' + source + '_id [name="specific_object_id_raw_standby"]' )
					.attr( 'name', 'specific_object_id_raw' );
				$( '.js-toolset-shortcode-gui-attribute-wrapper-for-' + source + '_id .js-toolset-shortcode-gui-item-selector[checked]' )
					.prop( 'checked', true )
					.trigger( 'change' );
			}
		);

		return self;
	};

	/**
	 * Init main method:
	 * - Init API hooks.
	 * - Init templates
	 * - Init dialogs.
	 *
	 * @since 1.5
	 */
	self.init = function() {

		self.initHooks()
			.initTemplates()
			.initDialogs()
			.initEvents();
	};

	self.init();
};

jQuery( document ).ready( function( $ ) {
	Toolset.Maps.shortcodeManager = new Toolset.Maps.ShortcodeManager( $ );
});
