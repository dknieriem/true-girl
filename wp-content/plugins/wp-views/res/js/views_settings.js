var WPViews = WPViews || {};

WPViews.ViewsSettingsScreen = function( $ ) {
	
	var self = this;
	
	self.cache = {};
	
	self.DialogSpinnerContent = $(
        '<div style="min-height: 150px;">' +
            '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; ">' +
                '<div class="wpv-spinner ajax-loader"></div>' +
                '<p>Loading</p>' +
            '</div>' +
        '</div>'
    );
	
	self.init_dialogs = function() {
		// Initialize dialogs
		if ( ! $('#js-wpv-hidden-custom-fields-dialog-container').length ) {
			$( 'body' ).append( '<div id="js-wpv-hidden-custom-fields-dialog-container" class="toolset-shortcode-gui-dialog-container wpv-shortcode-gui-dialog-container js-wpv-hidden-custom-fields-dialog-container"></div>' );
		}
		self.dialog_hidden_custom_fields = $( "#js-wpv-hidden-custom-fields-dialog-container" ).dialog({
			autoOpen: false,
			modal: true,
			minWidth: 450,
			show: { 
				effect: "blind", 
				duration: 800 
			},
			create: function( event, ui ) {
				$( event.target ).parent().css( 'position', 'fixed' );
			},
			open: function( event, ui ) {
				$( 'body' ).addClass( 'modal-open' );
				$( '.js-wpv-hidden-custom-fields-apply' )
					.show()
					.addClass( 'button-primary' )
					.removeClass( 'button-secondary' )
					.prop( 'disabled', false );
			},
			close: function( event, ui ) {
				$( 'body' ).removeClass( 'modal-open' );
			},
			buttons:[
				{
					class: 'toolset-shortcode-gui-dialog-button-align-right button-primary js-wpv-hidden-custom-fields-apply',
					text: wpv_settings_texts.apply,
					click: function() {
						self.hidden_custom_fields_apply();
					}
				},
				{
					class: 'button-secondary',
					text: wpv_settings_texts.close,
					click: function() {
						$( this ).dialog( "close" );
					}
				}
			]
		});

	}
	
	/**
	* --------------------
	* Hidden custom fields
	* --------------------
	*/
	
	self.hidden_custom_fields_selected_list = [];
	self.hidden_custom_fields_existing_count = 1;
	
	$( '.js-wpv-hidden-custom-fields-selected-list-item' ).each( function() {
		self.hidden_custom_fields_selected_list.push( $( this ).data( 'field' ) );
	});
	
	$( document ).on( 'click', '.js-wpv-select-hidden-custom-fields', function() {
		var dialog_height = $(window).height() - 100;
        // Show the "empty" dialog with a spinner while loading dialog content
		if ( self.hidden_custom_fields_existing_count > 0 ) {
			self.dialog_hidden_custom_fields.dialog('open').dialog({
				title: wpv_settings_texts.hidde_fields_dialog_title,
				width: 770,
				maxHeight: dialog_height,
				draggable: false,
				resizable: false,
				position: { 
					my: "center top+50", 
					at: "center top", 
					of: window, 
					collision: "none" 
				}
			});
			self.dialog_hidden_custom_fields.html( self.DialogSpinnerContent );
			var data = {
				action:		'wpv_get_hidden_custom_fields',
				wpnonce:	$( '#wpv_show_hidden_custom_fields_nonce' ).val()
			};
			$.ajax({
				url: ajaxurl,
				data: data,
				type: "GET",
				dataType:"json",
				success: function( response ) {
					if ( response.success ) {
						self.dialog_hidden_custom_fields.html( response.data.content );
						self.hidden_custom_fields_existing_count = response.data.count;
						if ( self.hidden_custom_fields_existing_count == 0 ) {
							$( '.js-wpv-hidden-custom-fields-apply' ).hide();
						}
					}
				}
			});
		} else {
			self.dialog_hidden_custom_fields.dialog('open').dialog({
				title: wpv_settings_texts.hidde_fields_dialog_title,
				width: 770,
				maxHeight: dialog_height,
				draggable: false,
				resizable: false,
				position: { 
					my: "center top+50", 
					at: "center top", 
					of: window, 
					collision: "none" 
				}
			});
			self.dialog_hidden_custom_fields.html( '<div class="wpv-dialog"><p class="toolset-alert toolset-alert-info">' + wpv_settings_texts.hidden_fields_count_zero + '</p></div>' );
			$( '.js-wpv-hidden-custom-fields-apply' ).hide();
		}
	});
	
	self.hidden_custom_fields_apply = function() {
		var apply_button = $( '.js-wpv-hidden-custom-fields-apply' ),
		selected_fields = [],
		spinnerContainer = $( '<div class="wpv-spinner ajax-loader">' ).insertBefore( apply_button ).show();
		apply_button
			.toggleClass( 'button-primary button-secondary' )
			.prop( 'disabled', true );
		$( '.js-wpv-hidden-custom-fields-all-list' )
			.find( '.js-wpv-hidden-field-item:checked' )
			.each( function() {
				selected_fields.push( $( this ).val() );
			});
		var data = {
			action:		'wpv_set_hidden_custom_fields',
			fields:		selected_fields,
			wpnonce:	$( '#wpv_show_hidden_custom_fields_nonce' ).val()
		};
		$.ajax({
			url: ajaxurl,
			data: data,
			type: "POST",
			dataType:"json",
			success: function( response ) {
				if ( response.success ) {
					$( '.js-wpv-hidden-custom-fields-summary' ).html( response.data.content );
					self.dialog_hidden_custom_fields.dialog( "close" );
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
				}
			},
			complete: function() {
				spinnerContainer.remove();
            }
		});
	};
	
	/**
	* --------------------
	* Custom inner shortcodes and conditional functions
	* --------------------
	*/
	
	// Save custom inner shortcodes and conditional functions options

	$( document ).on( 'input cut paste', '.js-wpv-add-item-settings-form-newname', function( e ) {
		var thiz = $( this ),
		thiz_form = thiz.closest( '.js-wpv-add-item-settings-form' );
		$( '.js-wpv-cs-error, .js-wpv-cs-dup, .js-wpv-cs-ajaxfail', thiz_form ).hide();
		if ( thiz.val() != '' ) {
			$( '.js-wpv-add-item-settings-form-button', thiz_form )
				.addClass( 'button-primary' )
				.removeClass( 'button-secondary' )
				.prop( 'disabled', false );
		} else {
			$( '.js-wpv-add-item-settings-form-button', thiz_form )
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary' )
				.prop( 'disabled', true );
		}
	});

	$( '.js-wpv-add-item-settings-form' ).submit( function( e ) {
		e.preventDefault();
		var thiz = $( this );
		$( '.js-wpv-add-item-settings-form-button', thiz ).click();
		return false;
	});
	
	// Add additional inner shortcodes
	
	$( '.js-wpv-custom-inner-shortcodes-add' ).on( 'click', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
		shortcode_pattern = /^[a-z0-9\-\_]+$/,
		parent_form = thiz.closest( '.js-wpv-add-item-settings-form' ),
		parent_container = thiz.closest( '.js-wpv-add-item-settings-wrapper' ),
		newshortcode = $( '.js-wpv-add-item-settings-form-newname', parent_form ),
		spinnerContainer = $('<div class="wpv-spinner ajax-loader">');
		$( '.js-wpv-cs-error, .js-wpv-cs-dup, .js-wpv-cs-ajaxfail', parent_form ).hide();
		if ( shortcode_pattern.test( newshortcode.val() ) == false ) {
			$( '.js-wpv-cs-error', parent_form ).show();
		} else if ( $( '.js-' + newshortcode.val() + '-item, .js-' + newshortcode.val() + '-api-item', parent_container ).length > 0 ) {
			$( '.js-wpv-cs-dup', parent_form ).show();
		} else {
			spinnerContainer.insertAfter( thiz ).show();
			thiz
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary' )
				.prop( 'disabled', true );
			var data = {
				action: 'wpv_update_custom_inner_shortcodes',
				csaction: 'add',
				cstarget: newshortcode.val(),
				wpnonce: $( '#wpv_custom_inner_shortcodes_nonce' ).val()
			};

			$.ajax({
				dataType: "json",
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function( response ) {
					if ( response.success ) {
						$( '.js-wpv-add-item-settings-list', parent_container )
							.append('<li class="js-' + newshortcode.val() + '-item"><span class="">[' + newshortcode.val() + ']</span> <i class="icon-remove-sign fa fa-times-circle js-wpv-custom-shortcode-delete" data-target="' + newshortcode.val() + '"></i></li>');
						newshortcode.val('');
						$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
					} else {
						$( '.js-wpv-cs-ajaxfail', parent_form ).show();
						console.log( "Error: AJAX returned ", response );
					}
				},
				error: function (ajaxContext) {
					$( '.js-wpv-cs-ajaxfail', parent_form ).show();
					console.log( "Error: ", ajaxContext.responseText );
				},
				complete: function() {
					spinnerContainer.remove();
				}
			});
		}
		return false;
	});
	
	// Delete additional inner shortcodes

	$(document).on('click', '.js-wpv-custom-shortcode-delete', function(e){
		e.preventDefault();
		var thiz = $( this ),
		thiz_target = thiz.data( 'target' ),
		parent_container = thiz.closest( '.js-wpv-add-item-settings-wrapper' ),
		spinnerContainer = $( '<div class="wpv-spinner ajax-loader">' ).insertAfter( $( '.js-wpv-custom-inner-shortcodes-add' ) ).show();
		var data = {
			action: 'wpv_update_custom_inner_shortcodes',
			csaction: 'delete',
			cstarget: thiz_target,
			wpnonce: $( '#wpv_custom_inner_shortcodes_nonce' ).val()
		};

		$.ajax({
			dataType: "json",
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function( response ) {
				if ( response.success ) {
					$( 'li.js-' + thiz_target + '-item', parent_container )
						.addClass( 'remove' )
						.fadeOut( 'fast', function() { 
							$( this ).remove(); 
						});
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
				} else {
					$( '.js-wpv-cs-ajaxfail', parent_container ).show();
					console.log( "Error: AJAX returned ", response );
				}
			},
			error: function (ajaxContext) {
				$( '.js-wpv-cs-ajaxfail', parent_container ).show();
				console.log( "Error: ", ajaxContext.responseText );
			},
			complete: function() {
				spinnerContainer.remove();
			}
		});

		return false;
	});
	
	// Add custom conditional functions
	
	$( '.js-wpv-custom-conditional-function-add' ).on( 'click', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
		shortcode_pattern = /^[a-zA-Z0-9\:\-\_]+$/,
		parent_form = thiz.closest( '.js-wpv-add-item-settings-form' ),
		parent_container = thiz.closest( '.js-wpv-add-item-settings-wrapper' ),
		newshortcode = $( '.js-wpv-add-item-settings-form-newname', parent_form ),
		sanitized_val = newshortcode.val().replace( '::', '-_paamayim_-' ),
		spinnerContainer = $('<div class="wpv-spinner ajax-loader">');
		$( '.js-wpv-cs-error, .js-wpv-cs-dup, .js-wpv-cs-ajaxfail', parent_form ).hide();
		if ( shortcode_pattern.test( newshortcode.val() ) == false ) {
			$( '.js-wpv-cs-error', parent_form ).show();
		} else if ( $( '.js-' + sanitized_val + '-item', parent_container ).length > 0 ) {
			$( '.js-wpv-cs-dup', parent_form ).show();
		} else {
			spinnerContainer.insertAfter( thiz ).show();
			thiz
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary' )
				.prop( 'disabled', true );
			var data = {
				action: 'wpv_update_custom_conditional_functions',
				csaction: 'add',
				cstarget: newshortcode.val(),
				wpnonce: $( '#wpv_custom_conditional_functions_nonce' ).val()
			};

			$.ajax({
				dataType: "json",
				type: "POST",
				url: ajaxurl,
				data: data,
				success:function( response ) {
					if ( response.success ) {
						$( '.js-wpv-add-item-settings-list', parent_container )
							.append('<li class="js-' + sanitized_val + '-item"><span class="">' + newshortcode.val() + '</span> <i class="icon-remove-sign fa fa-times-circle js-wpv-custom-function-delete" data-target="' + sanitized_val + '"></i></li>');
						newshortcode.val('');
						$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
					} else {
						$('.js-wpv-cs-ajaxfail', parent_form).show();
						console.log( "Error: AJAX returned ", response );
					}
				},
				error: function (ajaxContext) {
					$('.js-wpv-cs-ajaxfail', parent_form).show();
					console.log( "Error: ", ajaxContext.responseText );
				},
				complete: function() {
					spinnerContainer.remove();
				}
			});
		}
		return false;
	});
	
	// Delete custom conditional functions
	
	$( document ).on( 'click', '.js-wpv-custom-function-delete', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
		thiz_target = thiz.data( 'target' ),
		parent_container = thiz.closest( '.js-wpv-add-item-settings-wrapper' ),
		spinnerContainer = $( '<div class="wpv-spinner ajax-loader">' ).insertAfter( $( '.js-wpv-custom-conditional-function-add' ) ).show();
		var data = {
			action: 'wpv_update_custom_conditional_functions',
			csaction: 'delete',
			cstarget: thiz_target,
			wpnonce: $( '#wpv_custom_conditional_functions_nonce' ).val()
		};

		$.ajax({
			dataType: "json",
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function( response ) {
				if ( response.success ) {
					$( 'li.js-' + thiz_target + '-item', parent_container )
						.addClass( 'remove' )
						.fadeOut( 'fast', function() { 
							$( this ).remove(); 
						});
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
				} else {
					$('.js-wpv-cs-ajaxfail', parent_container).show();
					console.log( "Error: AJAX returned ", response );
				}
			},
			error: function (ajaxContext) {
				$('.js-wpv-cs-ajaxfail', parent_container).show();
				console.log( "Error: ", ajaxContext.responseText );
			},
			complete: function() {
				spinnerContainer.remove();
			}
		});

		return false;
	});

	/**
	 * @since 2.3
	 */
	// Add whitelist domains

	$( '.js-wpv-whitelist-domains-add' ).on( 'click', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
			domain_pattern = /^[a-zA-Z0-9.\*\:\-\_]+$/,
			parent_form = thiz.closest( '.js-wpv-add-item-settings-form' ),
			parent_container = thiz.closest( '.js-wpv-add-item-settings-wrapper' ),
			new_domain = $( '.js-wpv-add-item-settings-form-newname', parent_form ),
			sanitized_val = new_domain.val().replace( /\./g, '-' ),
			sanitized_class = sanitized_val.replace( /\*/g, '-' ).replace( /:/g, '-' ),
			spinnerContainer = $('<div class="wpv-spinner ajax-loader">');
		$( '.js-wpv-cs-error, .js-wpv-cs-dup, .js-wpv-cs-ajaxfail', parent_form ).hide();
		if ( domain_pattern.test( new_domain.val() ) == false ) {
			$( '.js-wpv-cs-error', parent_form ).show();
		} else if ( $( '.js-' + sanitized_class + '-item', parent_container ).length > 0 ) {
			$( '.js-wpv-cs-dup', parent_form ).show();
		} else {
			spinnerContainer.insertAfter( thiz ).show();
			thiz
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary' )
				.prop( 'disabled', true );
			var data = {
				action: 'wpv_update_whitelist_domains',
				csaction: 'add',
				cstarget: new_domain.val(),
				wpnonce: $( '#wpv_whitelist_domains_nonce' ).val()
			};

			$.ajax({
				dataType: "json",
				type: "POST",
				url: ajaxurl,
				data: data,
				success:function( response ) {
					if ( response.success ) {
						$( '.js-wpv-add-item-settings-list', parent_container )
							.append('<li class="js-' + sanitized_class + '-item"><span class="">' + new_domain.val() + '</span> <i class="icon-remove-sign fa fa-times-circle js-wpv-whitelist-domains-delete" data-target="' + sanitized_val + '"></i></li>');
						new_domain.val('');
						$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
					} else {
						$('.js-wpv-cs-ajaxfail', parent_form).show();
						console.log( "Error: AJAX returned ", response );
					}
				},
				error: function (ajaxContext) {
					$('.js-wpv-cs-ajaxfail', parent_form).show();
					console.log( "Error: ", ajaxContext.responseText );
				},
				complete: function() {
					spinnerContainer.remove();
				}
			});
		}
		return false;
	});

	/**
	 * @since 2.3
	 */
	// Delete whitelist domains
	$( document ).on( 'click', '.js-wpv-whitelist-domains-delete', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
			thiz_target = thiz.data( 'target' ),
			parent_container = thiz.closest( '.js-wpv-add-item-settings-wrapper' ),
			spinnerContainer = $( '<div class="wpv-spinner ajax-loader">' ).insertAfter( $( '.js-wpv-whitelist-domains-add' ) ).show();
		var data = {
			action: 'wpv_update_whitelist_domains',
			csaction: 'delete',
			cstarget: thiz_target,
			wpnonce: $( '#wpv_whitelist_domains_nonce' ).val()
		};

		$.ajax({
			dataType: "json",
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function( response ) {
				if ( response.success ) {
					$( 'li.js-' + thiz_target.toString().replace(/\*/g, '-').replace(/:/g, '-') + '-item', parent_container )
						.addClass( 'remove' )
						.fadeOut( 'fast', function() {
							$( this ).remove();
						});
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
				} else {
					$('.js-wpv-cs-ajaxfail', parent_container).show();
					console.log( "Error: AJAX returned ", response );
				}
			},
			error: function (ajaxContext) {
				$('.js-wpv-cs-ajaxfail', parent_container).show();
				console.log( "Error: ", ajaxContext.responseText );
			},
			complete: function() {
				spinnerContainer.remove();
			}
		});

		return false;
	});

	/**
	* --------------------
	* Map plugin
	* --------------------
	*/
	
	self.map_plugin_state = ( $( '.js-wpv-map-plugin' ).length > 0 ) ? $( '.js-wpv-map-plugin' ).prop( 'checked' ) : false;
	
	$( '.js-wpv-map-plugin' ).on( 'change', function() {
		if ( self.map_plugin_state != $('.js-wpv-map-plugin').prop('checked') ) {
			self.views_maps_options_debounce_update();
		}
	});
	
	self.save_views_maps_options = function() {
		var data = {
			action: 'wpv_update_map_plugin_status',
			status: $( '.js-wpv-map-plugin' ).prop( 'checked' ),
			wpnonce: $('#wpv_map_plugin_nonce').val()
		};
		$( document ).trigger( 'js-toolset-event-update-setting-section-triggered' );
		$.ajax({
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: data,
			success: function( response ) {
				if ( response.success ) {
					self.map_plugin_state = $( '.js-wpv-map-plugin' ).prop( 'checked' );
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
				}
				else {
					$( document ).trigger( 'js-toolset-event-update-setting-section-failed', [ response.data ] );
				}
			},
			error: function( ajaxContext ) {
				$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
			},
			complete: function() {
				
			}
		});
	};
	
	self.views_maps_options_debounce_update = _.debounce( self.save_views_maps_options, 1000 );
	
	/**
	* --------------------
	* CodeMirror
	* --------------------
	*/
	
	self.codemirror_autoresize_state = ( $( '#js-wpv-codemirror-autoresize' ).length > 0 ) ? $( '#js-wpv-codemirror-autoresize' ).prop( 'checked' ) : false;
	
	$( '#js-wpv-codemirror-autoresize' ).on( 'change', function() {
		self.codemirror_options_debounce_update();
	});
	
	self.save_codemirror_options = function() {
		if ( $( '#js-wpv-codemirror-autoresize' ).prop( 'checked' ) != self.codemirror_autoresize_state ) {
			var data = {
				action: 'wpv_update_codemirror_status',
				autoresize: $( '#js-wpv-codemirror-autoresize' ).prop( 'checked' ),
				wpnonce: $('#wpv_codemirror_options_nonce').val()
			};
			$( document ).trigger( 'js-toolset-event-update-setting-section-triggered' );
			$.ajax({
				type: "POST",
				dataType: "json",
				url: ajaxurl,
				data: data,
				success: function( response ) {
					if ( response.success ) {
						self.codemirror_autoresize_state = $( '#js-wpv-codemirror-autoresize' ).prop( 'checked' );
						$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
					} else {
						$( document ).trigger( 'js-toolset-event-update-setting-section-failed', [ response.data ] );
					}
				},
				error: function( ajaxContext ) {
					$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
				},
				complete: function() {
					
				}
			});
		}
	};
	
	self.codemirror_options_debounce_update = _.debounce( self.save_codemirror_options, 1000 );
	
	/**
	* --------------------
	* Enable history management
	* --------------------
	*/
	
	self.enable_history_management_state = ( $( '.js-wpv-enable-manage-history' ).length > 0 ) ? $( '.js-wpv-enable-manage-history' ).serialize() : false;
	
	$( '.js-wpv-enable-manage-history' ).on( 'change', function() {
		self.history_management_options_debounce_update();
	});
	
	self.save_history_management_options = function() {
		if ( $( '.js-wpv-enable-manage-history' ).serialize() != self.enable_history_management_state ) {
			var data = {
				action: 'wpv_update_pagination_options',
				enable_pagination_history_management: $( '#js-wpv-enable-pagination-manage-history' ).prop( 'checked' ),
				enable_parametric_search_history_management: $( '#js-wpv-enable-parametric-search-manage-history' ).prop( 'checked' ),
				wpnonce: wpv_settings_texts.wpnonce
			};
			$( document ).trigger( 'js-toolset-event-update-setting-section-triggered' );
			$.ajax({
				type: "POST",
				dataType: "json",
				url: ajaxurl,
				data: data,
				success: function( response ) {
					if ( response.success ) {
						self.enable_history_management_state = $( '.js-wpv-enable-manage-history' ).serialize();
						$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
					} else {
						$( document ).trigger( 'js-toolset-event-update-setting-section-failed', [ response.data ] );
					}
				},
				error: function( ajaxContext ) {
					$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
				},
				complete: function() {
					
				}
			});
		}
	};
	
	self.history_management_options_debounce_update = _.debounce( self.save_history_management_options, 1000 );
	

	
	/**
	* --------------------
	* WPML
	* --------------------
	*/
	
	self.wpml_translation_settings = ( $( '.js-wpv-content-template-translation:checked' ).length > 0 ) ? $( '.js-wpv-content-template-translation:checked' ).val() : 0;
	
	$( document ).on( 'change', '.js-wpv-content-template-translation', function() {
		if ( self.wpml_translation_settings != $( '.js-wpv-content-template-translation:checked' ).val() ) {
			self.views_wpml_options_debounce_update();
		}
	});
	
	self.save_views_wpml_options = function() {
		var data = {
			action: 'wpv_update_wpml_settings',
			status: $( '.js-wpv-content-template-translation:checked' ).val(),
			wpnonce: $('#wpv_wpml_settings_nonce').val()
		};
		$( document ).trigger( 'js-toolset-event-update-setting-section-triggered' );
        $.ajax({
            type: "POST",
			dataType: "json",
            url: ajaxurl,
            data: data,
            success: function( response ) {
                if ( response.success ) {
					self.wpml_translation_settings = $( '.js-wpv-content-template-translation:checked' ).val();
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
                } else {
					$( document ).trigger( 'js-toolset-event-update-setting-section-failed', [ response.data ] );
				}
            },
            error: function( ajaxContext ) {
				$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
            },
            complete: function() {
                
            }
        });
	};
	
	self.views_wpml_options_debounce_update = _.debounce( self.save_views_wpml_options, 1000 );
	
	/**
	* --------------------
	* Frontend edit links
	* --------------------
	*/
	
	self.show_edit_view_link_state = ( $( '.js-wpv-show-edit-view-link' ).length > 0 ) ? $( '.js-wpv-show-edit-view-link' ).prop( 'checked' ) : false;
	
	$( '.js-wpv-show-edit-view-link' ).on( 'change', function() {
		self.frontend_links_options_debounce_update();
	});
	
	self.save_frontend_links_options = function() {
		if ( self.show_edit_view_link_state != $( '.js-wpv-show-edit-view-link' ).prop( 'checked' ) ) {
			var data = {
				action: 'wpv_update_show_edit_view_link_status',
				status: $( '.js-wpv-show-edit-view-link' ).prop( 'checked' ),
				wpnonce: $('#wpv_show_edit_view_link_nonce').val()
			};
			$( document ).trigger( 'js-toolset-event-update-setting-section-triggered' );
			$.ajax({
				type: "POST",
				dataType: "json",
				url: ajaxurl,
				data: data,
				success: function( response ) {
					if ( response.success ) {
						self.show_edit_view_link_state = $( '.js-wpv-show-edit-view-link' ).prop( 'checked' );
						$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
					} else {
						$( document ).trigger( 'js-toolset-event-update-setting-section-failed', [ response.data ] );
					}
				},
				error: function( ajaxContext ) {
					$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
				},
				complete: function() {
					
				}
			});
		}
	};
	
	self.frontend_links_options_debounce_update = _.debounce( self.save_frontend_links_options, 1000 );
	
	/**
	* --------------------
	* Theme debug
	* --------------------
	*/
	
	self.content_template_theme_support_function = $( '.js-wpv-content-templates-theme-support-function' ).val();
	self.content_template_theme_support_debug = $( '.js-wpv-content-templates-theme-support-debug' ).prop( 'checked' );
	
	$( document ).on( 'change cut click paste keyup', '.js-wpv-content-templates-theme-support-function', function() {
		if ( self.content_template_theme_support_function == $( '.js-wpv-content-templates-theme-support-function' ).val() ) {
			$( '.js-wpv-content-templates-theme-support-function-save' )
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary' )
				.prop( 'disabled', true );
		} else {
			$( '.js-wpv-content-templates-theme-support-function-save' )
				.addClass( 'button-primary' )
				.removeClass( 'button-secondary' )
				.prop( 'disabled', false );
		}
	});
	
    $( '.js-wpv-content-templates-theme-support-function-save' ).on( 'click', function( e ) {
        e.preventDefault();
		var thiz = $( this ),
		spinnerContainer = $('<div class="wpv-spinner ajax-loader">').insertAfter( thiz ).show(),
		data = {
			action: 'wpv_update_content_templates_theme_support_settings',
			theme_function: $( '.js-wpv-content-templates-theme-support-function' ).val(),
			wpnonce: $('#wpv_view_templates_theme_support').val()
		};
		thiz
			.addClass( 'button-secondary' )
			.removeClass( 'button-primary' )
			.prop( 'disabled', true );
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function( response ) {
                if ( response.success ) {
                    self.content_template_theme_support_function = $( '.js-wpv-content-templates-theme-support-function' ).val();
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
                } else {
					$( document ).trigger( 'js-toolset-event-update-setting-section-failed', [ response.data ] );
				}
            },
            error: function( ajaxContext ) {
				$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
            },
            complete: function() {
				spinnerContainer.remove();
            }
        });
    });
	
	$( '.js-wpv-content-templates-theme-support-debug' ).on( 'change', function() {
		self.content_templates_theme_support_debug_options_debounce_update();
	});
	
	self.save_content_templates_theme_support_debug_options = function() {
		if ( self.content_template_theme_support_debug != $( '.js-wpv-content-templates-theme-support-debug' ).prop( 'checked' ) ) {
			var data = {
				action: 'wpv_update_content_templates_theme_support_settings',
				theme_debug: $( '.js-wpv-content-templates-theme-support-debug' ).prop( 'checked' ),
				wpnonce: $('#wpv_view_templates_theme_support').val()
			};
			$( document ).trigger( 'js-toolset-event-update-setting-section-triggered' );
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function( response ) {
					if ( response.success ) {
						self.content_template_theme_support_debug = $( '.js-wpv-content-templates-theme-support-debug' ).prop( 'checked' );
						$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
					} else {
						$( document ).trigger( 'js-toolset-event-update-setting-section-failed', [ response.data ] );
					}
				},
				error: function( ajaxContext ) {
					$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
				},
				complete: function() {
					
				}
			});
		}
	};
	
	self.content_templates_theme_support_debug_options_debounce_update = _.debounce( self.save_content_templates_theme_support_debug_options, 1000 );
	
	/**
	* --------------------
	* Debug
	* --------------------
	*/
	
	self.debug_mode_state = $('.js-toolset-views-debug-settings input').serialize();
	
	$( '.js-toolset-views-debug-settings input' ).on( 'change', function( e ) {
		if ( $( '.js-wpv-debug-mode' ).prop( 'checked' ) ) {
			$( '.js-wpv-views-debug-additional-options' ).fadeIn( 'fast' );
		} else {
			$( '.js-wpv-views-debug-additional-options' ).hide();
		}
		if ( self.debug_mode_state != $('.js-toolset-views-debug-settings input').serialize() ) {
			self.views_debug_options_debounce_update();
		}
	});
	
	self.save_views_debug_options = function() {
		var data = {
			action: 'wpv_update_views_debug_status',
			debug_status: ( $( '.js-wpv-debug-mode' ).prop( 'checked' ) ) ? 1 : 0,
			debug_mode_type: $( 'input[name=wpv_debug_mode_type]:radio:checked' ).val(),
			wpnonce: $('#wpv_views_debug_nonce').val()
		};
		$( document ).trigger( 'js-toolset-event-update-setting-section-triggered' );
		$.ajax({
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: data,
			success: function( response ) {
				if ( response.success ) {
					self.debug_mode_state = $('.js-toolset-views-debug-settings input').serialize();
					$( document ).trigger( 'js-toolset-event-update-setting-section-completed' );
				} else {
					$( document ).trigger( 'js-toolset-event-update-setting-section-failed', [ response.data ] );
				}
			},
			error: function( ajaxContext ) {
				$( document ).trigger( 'js-toolset-event-update-setting-section-failed' );
			},
			complete: function() {
				
			}
		});
	};
	
	self.views_debug_options_debounce_update = _.debounce( self.save_views_debug_options, 1000 );
	
	self.init = function() {
		self.init_dialogs();
	};
	
	self.init();

};

jQuery( document ).ready( function( $ ) {
    WPViews.views_settings_screen = new WPViews.ViewsSettingsScreen( $ );
    if( /#toolset-admin-bar-settings$/.test( window.location.href ) ) {
        $( '#toolset-admin-bar-settings' ).parent().css( 'background-color', '#ffffca' );
    }
});