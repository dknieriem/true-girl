var WPViews = WPViews || {};
var WPV_Toolset = WPV_Toolset  || {};

WPViews.ViewAddonMapsSettings = function( $ ) {
	const API_GOOGLE = 'google';
	const API_AZURE  = 'azure';
	
	var self = this;
	self.marker_add_button = $( '#js-wpv-addon-maps-marker-add' );
	self.settings = {
		api_key: $( '#js-wpv-map-api-key' ).val(),
		server_side_api_key: $( '#js-wpv-map-server_side_api_key' ).val(),
		apiUsed: wpv_addon_maps_settings_local.api_used,
		azureKey: $( '#js-wpv-map-azure-api-key' ).val(),
	};
	self.legacy_mode_api_key = ( $( '.js-wpv-map-api-key-save' ).length > 0 );
	
	if ( typeof WPV_Toolset.only_img_src_allowed_here !== "undefined" ) {
		WPV_Toolset.only_img_src_allowed_here.push( "wpv-addpn-maps-custom-marker-newurl" );
	}
	
	$( '#wpv-addpn-maps-custom-marker-newurl' ).on( 'js_icl_media_manager_inserted', function( event ) {
		var thiz = $( this ),
		thiz_container = thiz.closest( '.js-wpv-add-item-settings-wrapper' ),
		thiz_list = thiz_container.find( '.js-wpv-add-item-settings-list' ),
		spinnerContainer = $('<div class="wpv-spinner ajax-loader">'),
		data = {
			action:		'wpv_addon_maps_update_marker',
			csaction:	'add',
			cstarget:	thiz.val(),
			wpnonce:	wpv_addon_maps_settings_local.global_nonce
		};
		
		if ( $( '.js-wpv-addon-maps-custom-marker-list img[src="' + thiz.val() + '"]' ).length > 0 ) {
			thiz.val('');
			return;
		}
		
		self.marker_add_button.prop( 'disabled', true );
		spinnerContainer.insertAfter( self.marker_add_button ).show();
		
		$.ajax({
			type:		"POST",
			dataType:	"json",
			url:		ajaxurl,
			data:		data,
			success:	function( response ) {
				if ( response.success ) {
					thiz_list.append('<li class="js-wpv-addon-maps-custom-marker-item"><img src="' + thiz.val() + '" class="js-wpv-addon-maps-custom-marker-item-img" /> <i class="icon-remove-sign fa fa-times-circle js-wpv-addon-maps-custom-marker-delete"></i></li>');
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
				}
			},
			error:		function( ajaxContext ) {
				
			},
			complete:	function() {
				thiz.val('');
				spinnerContainer.remove();
				self.marker_add_button.prop( 'disabled', false );
			}
		});
		
	});
	
	$( document ).on( 'click', '.js-wpv-addon-maps-custom-marker-delete', function() {
		var thiz = $( this ),
		thiz_container = thiz.closest( '.js-wpv-add-item-settings-wrapper' ),
		thiz_item = thiz.closest( '.js-wpv-addon-maps-custom-marker-item' ),
		thiz_image = thiz_item.find( 'img' ).attr( 'src' ),
		spinnerContainer = $('<div class="wpv-spinner ajax-loader">').insertAfter( self.marker_add_button ).show(),
		data = {
			action:		'wpv_addon_maps_update_marker',
			csaction:	'delete',
			cstarget:	thiz_image,
			wpnonce:	wpv_addon_maps_settings_local.global_nonce
		};
		
		self.marker_add_button.prop( 'disabled', true );

		$.ajax({
			type:		"POST",
			dataType:	"json",
			url:		ajaxurl,
			data:		data,
			success:	function( response ) {
				if ( response.success ) {
					thiz_item
						.addClass( 'remove' )
						.fadeOut( 'fast', function() {
							$( this ).remove(); 
						});
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
				}
			},
			error:		function( ajaxContext ) {
				
			},
			complete:	function() {
				spinnerContainer.remove();
				self.marker_add_button.prop( 'disabled', false );
			}
		});
		
	});

	/**
	 * For a little bit of DRYness and to explain to static tools that we are getting string here, not a jQuery object.
	 * @since 1.4
	 * @param {String} id
	 * @returns {String}
	 */
	self.get_select_value = function( id ) {
		var select = $( 'select#'+id );
		return select.val();
	};

	/**
	 * Ajax call to check if Google API is correctly set up.
	 * @since 1.4.2
	 */
	self.checkGApi = function() {
		var $resultP = $('#toolset-maps-api-check-result');
		var $checkApiButton = $('#toolset-maps-check-api-button');

		$checkApiButton.attr('disabled', true);

		$.ajax( {
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: {
				action: 'wpv_addon_maps_check_g_api',
				wpnonce: wpv_addon_maps_settings_local.global_nonce
			}
		} )
			.done( function( response ) {
				if ( response.success ) {
					$resultP.removeClass().addClass('toolset-maps-success');
				} else {
					$resultP.removeClass().addClass('toolset-maps-error');
				}
				$resultP.text( response.data );
			} )
			.fail( function( jqXHR, textStatus, errorThrown ) {
				console.log( errorThrown );
			} )
			.always( function() {
				$checkApiButton.attr('disabled', false);
			} );
	};
	$('#toolset-maps-check-api-button').click( self.checkGApi );

	/**
	 * Ajax call to update default map style. Saving style name, as URL may change on another site.
	 * @since 1.4
	 * @param {String} style_select
	 */
	self.update_default_map_style = function( style_select ) {
		$( document ).trigger( 'js-toolset-event-update-setting-section-triggered' );

		var style_name = $( style_select ).find( 'option:selected' ).text();

		$.ajax( {
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: {
				action: 'wpv_addon_maps_update_default_map_style',
				style: style_name,
				wpnonce: wpv_addon_maps_settings_local.global_nonce
			}
		} )
			.done( function( response, textStatus ) {
				if ( response.success ) {
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
				} else {
					$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
				}
			} )
			.fail( function( jqXHR, textStatus, errorThrown ) {
				$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
			} );
	};

	/**
	 * Handles map style JSON upload
	 * @since 1.4
	 * @listens Event js_icl_media_manager_inserted @ #js-wpv-toolset-maps-add-map-style
	 * @param {Event} event
	 */
	self.on_js_icl_media_manager_inserted_at_map_style = function( event ) {
		$( '.js-wpv-toolset-maps-media-manager' ).prop( 'disabled', true );
		$( document ).trigger( 'js-toolset-event-update-setting-section-triggered' );

		var json_links = $( event.currentTarget.value );
		// On 2nd and subsequent uploads without reload, we get a list of links, and need only the last one
		var last_link_index = json_links.length - 1;
		var last_link = $( json_links[last_link_index] );
		var json_name = last_link.text();
		var json_url = last_link.attr('href');
		var data = {
			action: 'wpv_addon_maps_update_json_file',
			csaction: 'add',
			cstargetname: json_name,
			cstargeturl: json_url,
			wpnonce: wpv_addon_maps_settings_local.global_nonce
		};

		if ( self.is_duplicate_json_style( json_name ) ) {
			$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
			alert( wpv_addon_maps_settings_local.duplicate_style_warning );
			return;
		}

		$.ajax( {
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: data
		} )
			.done( function( response, textStatus ) {
				if ( response.success ) {
					// Add JSON file to dropdown and select it. Trigger change so map preview updates.
					$('select#wpv-map-render-style_json')
						.append( $( '<option>', {
							value: json_url,
							text : json_name
						} ) )
						.val( json_url )
						.trigger( 'change' );
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
				} else {
					$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
				}
			} )
			.fail( function( jqXHR, textStatus, errorThrown ) {
				$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
			} )
			.always( function() {
				$( '.js-wpv-toolset-maps-media-manager' ).prop( 'disabled', false );
			} );
	};
	$( document ).on(
		'js_icl_media_manager_inserted',
		'#js-wpv-addon-maps-json-style-newurl',
		self.on_js_icl_media_manager_inserted_at_map_style
	);

	/**
	 * Checks if we already have a JSON style with given name
	 * @param {String} style_name
	 * @return {boolean}
	 */
	self.is_duplicate_json_style = function( style_name ) {
		var duplicate = false;

		$('select#wpv-map-render-style_json option').each( function( index, option ) {
			if ( option.text === style_name ) {
				duplicate = true;
				return false;
			}
		} );

		return duplicate;
	};

	/**
	 * Sends Ajax request to delete JSON style file (deletion from options will be triggered too).
	 * @since 1.4
	 * @param {Event} event
	 */
	self.on_uploaded_map_style_delete = function( event ) {
		var button = $( event.currentTarget );

		button.prop( 'disabled', true );
		$( document ).trigger( 'js-toolset-event-update-setting-section-triggered' );

		var json_name = $( 'select#wpv-map-render-style_json option:selected' ).text();
		var json_url = $( 'select#wpv-map-render-style_json' ).val();
		var data = {
			action: 'wpv_addon_maps_update_json_file',
			csaction: 'delete',
			cstargetname: json_name,
			cstargeturl: json_url,
			wpnonce: wpv_addon_maps_settings_local.global_nonce
		};

		$.ajax( {
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: data
		} )
			.done( function( response, textStatus ) {
				if ( response.success ) {
					$( 'select#wpv-map-render-style_json option:selected' ).remove();
					$( 'select#wpv-map-render-style_json' )
						.val(
							$('select#wpv-map-render-style_json option:contains(Standard)').val()
						)
						.trigger( 'change' );
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
				} else {
					$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
				}
			} )
			.fail( function( jqXHR, textStatus, errorThrown ) {
				$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
			} )
			.always( function() {
				button.prop( 'disabled', false );
			});
	};
	$( document ).on( 'click', 'button.js-wpv-toolset-maps-delete-json-file', self.on_uploaded_map_style_delete );

	$( document ).on( 'change keyup input cut paste', '#js-wpv-map-api-key', function() {
		var thiz = $( this );
		self.maybe_glow_api_key( thiz );
		if ( thiz.val() != self.settings.api_key ) {
			if ( self.legacy_mode_api_key ) {
				// Legacy: we add a button on Views < 2.0
				$( '.js-wpv-map-api-key-save' )
					.addClass( 'button-primary' )
					.removeClass( 'button-secondary' )
					.prop( 'disabled', false );
			} else {
				self.api_key_debounce_update();
			}
		} else if ( self.legacy_mode_api_key ) {
			$( '.js-wpv-map-api-key-save' )
				.addClass( 'button-secondary' )
				.removeClass( 'button-primary' )
				.prop( 'disabled', true );
		}
	});

	// Listener for saving optional API key 2
	$( document ).on( 'change keyup input cut paste', '#js-wpv-map-server_side_api_key', function() {
		var $this = $( this );

		self.maybe_glow_api_key( $this );

		if ( $this.val() !== self.settings.server_side_api_key ) {
			self.api_key_debounce_update();
		}
	});

	// Listener for API used change
	$( document ).on( 'change', 'input[name="toolset-maps-api-used"]', function( event ) {
		self.saveApiUsed( event.target.value );
	} );

	// Listener for Azure API key change
	$( document ).on( 'change keyup input cut paste', 'input#js-wpv-map-azure-api-key', function() {
		var $this = $( this );

		self.maybe_glow_api_key( $this );

		if ( $this.val() !== self.settings.azureKey ) {
			self.updateAzureApiKeyDebounced( $this.val() );
		}
	} );
	
	$( document ).on( 'click', '.js-wpv-map-api-key-save', function() {
		var thiz = $( this );
		spinnerContainer = $('<div class="wpv-spinner ajax-loader">').insertBefore( thiz ).show();
		self.save_api_key_options();
	});
	
	$( document ).on( 'js-toolset-event-update-setting-section', function( event, data ) {
		if ( self.legacy_mode_api_key ) {
			$( '.js-wpv-map-api-key-save' )
				.addClass( 'button-secondary' )
				.removeClass( 'button-primary' )
				.prop( 'disabled', true );
			$( '.js-wpv-map-api-key-save' )
				.closest( '.update-button-wrap' )
					.find( '.wpv-spinner' )
						.remove();
			$( '.js-wpv-map-api-key-save' )
				.closest( '.update-button-wrap' )
					.find( '.js-wpv-messages' )
						.wpvToolsetMessage({
								text: wpv_addon_maps_settings_local.setting_saved,
								type: 'success',
								inline: true,
								stay: false
							});
		}
	});

	/**
	 * Save API to use setting.
	 *
	 * @since 1.5
	 * @param {string} apiUsed
	 */
	self.saveApiUsed = function( apiUsed ) {
		$( 'input[name="toolset-maps-api-used"]' ).attr( 'disabled', true );
		$( document ).trigger( 'js-toolset-event-update-setting-section-triggered' );

		$.ajax( {
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: {
				action:     'toolset_maps_update_api_used',
				api_used:   apiUsed,
				wpnonce:    wpv_addon_maps_settings_local.global_nonce
			}
		} )
			.done( function( response, textStatus ) {
				if ( response.success ) {
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
					self.showAppropriateSettingsSections( apiUsed );
				} else {
					$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
				}
			} )
			.fail( function( jqXHR, textStatus, errorThrown ) {
				$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
			} )
			.always( function() {
				$( 'input[name="toolset-maps-api-used"]' ).attr( 'disabled', false );
			} );
	};

	/**
	 * Save Azure API key
	 *
	 * @since 1.5
	 * @param {string} apiKey
	 */
	self.saveAzureApiKey = function( apiKey ) {
		$( document ).trigger( 'js-toolset-event-update-setting-section-triggered' );

		$.ajax( {
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: {
				action:         'toolset_maps_update_azure_api_key',
				azure_api_key:  apiKey,
				wpnonce:        wpv_addon_maps_settings_local.global_nonce
			}
		} )
			.done( function( response, textStatus ) {
				if ( response.success ) {
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
				} else {
					$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
				}
			} )
			.fail( function( jqXHR, textStatus, errorThrown ) {
				$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
			} )
	};

	self.updateAzureApiKeyDebounced = _.debounce( self.saveAzureApiKey, 1000 );

	/**
	 * Shows appropriate settings sections for the API being used
	 * @param {string} apiUsed
	 */
	self.showAppropriateSettingsSections = function( apiUsed ) {
		let $shownForGoogle = $( 'div#toolset-maps-api-key, div#toolset-style-files, div#toolset-maps-legacy' );
		let $shownForAzure = $( 'div#toolset-maps-azure-key' );

		if ( apiUsed === API_GOOGLE ) {
			$shownForAzure.hide();
			$shownForGoogle.show();
		} else {
			$shownForGoogle.hide();
			$shownForAzure.show();
		}
	};

	/**
	 * @since 1.4.2 - do an API check after successful key save
	 * @since 1.5 - save server-side API key too
	 */
	self.save_api_key_options = function() {
		var api_key = $( '#js-wpv-map-api-key' ).val();
		var server_side_api_key = $( '#js-wpv-map-server_side_api_key' ).val();
		var data = {
			action:		            'wpv_addon_maps_update_api_key',
			api_key:	            api_key,
			server_side_api_key:    server_side_api_key,
			wpnonce:	            wpv_addon_maps_settings_local.global_nonce
		};

		$( document ).trigger( 'js-toolset-event-update-setting-section-triggered' );		
		$.ajax({
			type:		"POST",
			dataType:	"json",
			url:		ajaxurl,
			data:		data,
			success:	function( response ) {
				if ( response.success ) {
					self.settings.api_key = api_key;
					self.settings.server_side_api_key = server_side_api_key;
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
					self.checkGApi();
				} else {
					$( document ).trigger( 'js-toolset-event-update-setting-section-failed', [ response.data ] );
				}
			},
			error:		function( ajaxContext ) {
				$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
			},
			complete:	function() {
				if ( self.legacy_mode_api_key ) {
					$( '.js-wpv-map-api-key-save' )
						.addClass( 'button-secondary' )
						.removeClass( 'button-primary' )
						.prop( 'disabled', true );
					$( '.js-wpv-map-api-key-save' )
						.closest( '.update-button-wrap' )
							.find( '.wpv-spinner' )
								.remove();
				}
			}
		});
	};
	
	self.api_key_debounce_update = _.debounce( self.save_api_key_options, 1000 );
	
	self.maybe_glow_api_key = function( api_key_field ) {
		if ( api_key_field.val() == '' ) {
			api_key_field.css( {'box-shadow': '0 0 5px 1px #f6921e'} );
		} else {
			api_key_field.css( {'box-shadow': 'none'} );
		}
		return self;
	};
	
	$( document ).on( 'click', '#js-wpv-map-load-stored-data', function() {
		var thiz = $( this ),
		thiz_before = thiz.closest( '.js-wpv-map-load-stored-data-before' ),
		thiz_after = $( '.js-wpv-map-load-stored-data-after' ),
		spinnerContainer = $('<div class="wpv-spinner ajax-loader">').insertBefore( thiz ).show(),
		data = {
			action:		'wpv_addon_maps_get_stored_data',
			wpnonce:	wpv_addon_maps_settings_local.global_nonce
		};
		
		thiz.prop( 'disabled', true );
		
		$.ajax({
			type:		"GET",
			dataType:	"json",
			url:		ajaxurl,
			data:		data,
			success:	function( response ) {
				if ( response.success ) {
					thiz_after
						.html( response.data.table )
						.fadeIn();
				}
			},
			error:		function( ajaxContext ) {
				
			},
			complete:	function() {
				spinnerContainer.remove();
				thiz.prop( 'disabled', false );
				thiz_before.remove();
			}
		});
	});
	
	self.delete_stored_addresses = function( keys ) {
		data = {
			action:		'wpv_addon_maps_delete_stored_addresses',
			keys:		keys,
			wpnonce:	wpv_addon_maps_settings_local.global_nonce
		};		
		return $.ajax({
			type:		"POST",
			dataType:	"json",
			url:		ajaxurl,
			data:		data
		});
	};
	
	$( document ).on( 'click', '.js-wpv-map-delete-stored-address', function() {
		var thiz = $( this ),
		thiz_key = thiz.data( 'key' ),
		thiz_row = thiz.closest( 'tr' ),
		data = {
			action:		'wpv_addon_maps_delete_stored_addresses',
			keys:		[ thiz_key ],
			wpnonce:	wpv_addon_maps_settings_local.global_nonce
		};
		
		thiz.toggleClass( 'fa-times fa-circle-o-notch fa-spin wpv-map-delete-stored-address-deleting' );
		$( document ).trigger( 'js-toolset-event-update-setting-section-triggered' );
		self.delete_stored_addresses( [ thiz_key ] )
			.done( function( response ) {
				if ( response.success ) {
					thiz_row
						.addClass( 'deleted' )
						.fadeOut( 'fast', function() {
							thiz_row.remove();
						});
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
				} else {
					$( document ).trigger( 'js-toolset-event-update-setting-section-failed', [ response.data ] );
				}
			})
			.fail( function( ajaxContext ) {
				$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
			});
	});

	/**
	 * Delible = able to be deleted.
	 *
	 * Because we have some uploaded maps than can be deleted and some preloaded that can't.
	 *
	 * @param {String|jQuery} style_select
	 * @return {boolean}
	 */
	self.is_map_delible = function( style_select ) {
		var map_location = $( style_select ).find( 'option:selected' ).val();

		// "Standard" uses no style JSON but default settings. Not delible.
		if ( !map_location ) return false;

		// Preloaded maps have this part of path, so they will give false. Uploaded will return true.
		return ( map_location.indexOf('views-addon-maps/resources/json') === -1 );
	};

	/**
	 * Shows or hides map delete button.
	 *
	 * Because it's an opportunity to use both "delible" and "delibility" in one short method.
	 *
	 * @param {String|jQuery} style_select
	 */
	self.check_map_delibility = function( style_select ) {
		if ( self.is_map_delible( style_select ) ) {
			$('button.js-wpv-toolset-maps-delete-json-file').show();
		} else {
			$('button.js-wpv-toolset-maps-delete-json-file').hide();
		}
	};
	
	// ------------------------------------
	// Init
	// ------------------------------------

	/**
	 * Only start these listeners if there is a G Maps API key and Map preview is needed.
	 */
	self.init_map_preview = function() {
		if ( ! self.settings.api_key ) return;
		if ( $('#js-wpv-addon-maps-render-map-preview').length === 0 ) return;

		// Start listeners
		$( 'select#wpv-map-render-style_json' ).on( 'change', function( event ) {
			WPViews.addon_maps_preview.render_preview_map( event.currentTarget.value );
			self.update_default_map_style( event.currentTarget );
			self.check_map_delibility( event.currentTarget );
		} );

		$( 'a.js-toolset-nav-tab' ).on( "toolsetSettings:afterTabSwitch.render_map", function( event, tab ) {
			if ( tab === 'maps' ) {
				$( 'a.js-toolset-nav-tab' ).off( "toolsetSettings:afterTabSwitch.render_map" );
				WPViews.addon_maps_preview.render_preview_map( self.get_select_value( 'wpv-map-render-style_json' ) );
				self.check_map_delibility( $( 'select#wpv-map-render-style_json' ) );
			}
		} );
	};

	self.init = function() {
		self.showAppropriateSettingsSections( self.settings.apiUsed );

		self.maybe_glow_api_key( $( '#js-wpv-map-api-key' ) );
		self.maybe_glow_api_key( $( '#js-wpv-map-azure-api-key' ) );

		if ( API_GOOGLE === self.settings.apiUsed ) {
			self.init_map_preview();
		}
	};
	
	self.init();

};

jQuery( document ).ready( function( $ ) {
    WPViews.view_addon_maps_settings = new WPViews.ViewAddonMapsSettings( $ );
});