var WPViews = WPViews || {};

WPViews.ViewsListingScreen = function( $ ) {
	
	var self = this;
	
	self.shortcodeDialogSpinnerContent = $(
        '<div style="min-height: 150px;">' +
            '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; ">' +
                '<div class="wpv-spinner ajax-loader"></div>' +
                '<p>' + views_listing_texts.loading_options + '</p>' +
            '</div>' +
        '</div>'
    );
	
	self.duplicating_id = 0;
	self.duplicating_title = '';
	
	self.bulkcombined_action = '';
	self.bulkcombined_target = [];
	self.bulkcombined_nonce = '';
	
	self.calculate_dialog_maxWidth = function() {
		return ( $( window ).width() - 100 );
	};
	
	self.calculate_dialog_maxHeight = function() {
		return ( $( window ).height() - 100 );
	};
	
	/**
	* -----------------
	* Scan button
	* -----------------
	*/
	
	$( document ).on( 'click', '.js-scan-button', function() {

        if ( ! $(this).data('loading') ) {

			var thiz = $( this ),
            view_id = thiz.data( 'view-id' ),
            cellParent = thiz.parent(),
			postsList = $('<ul class="posts-list">'),
			spinnerContainer = $('<div class="wpv-spinner ajax-loader">').insertAfter( thiz ).show(),
            data = {
				action: 'wpv_scan_view_usage',
				id: view_id,
				wpnonce : $( '#work_views_listing' ).val()
            };
            thiz
                .data( 'loading', true )
                .prop( 'disabled', true );
			
			$.ajax({
				type: "POST",
				dataType: "json",
				url: ajaxurl,
				data: data,
				success: function( response ) {
					if ( response.success ) {
						if ( response.data.used_on.length > 0 ) {
							postsList.appendTo( cellParent );
							$.each( response.data.used_on, function( index, value ) {
								$( '<li><a target="_blank" href="'+ value['link'] + '">' + value['title'] + '</a></li>' ).appendTo( postsList );
							});
						} else {
							thiz.parent().find('.js-nothing-message').show();
						}
						thiz
							.data( 'loading', false )
							.prop( 'disabled', false )
							.remove();
					}
				},
				error: function( ajaxContext ) {
					//console.log( "Error: ", ajaxContext.responseText );
				},
				complete: function() {
					thiz
						.data( 'loading', false )
						.prop( 'disabled', false );
					spinnerContainer.remove();
				}
			});
        }
    });
	
	/**
	* -----------------
	* Search and pagination
	* -----------------
	*/

    $( '#posts-filter' ).submit( function( e ) {
	    e.preventDefault();
	    var url_params = decodeURIParams( $( this ).serialize() );
	    if (
			typeof( url_params['s'] ) !== 'undefined' 
			&& url_params['s'] == ''
		) {
		    url_params['s'] = null;
	    }
	    navigateWithURIParams( url_params );
        return false;
    });

    $( document ).on( 'change', '.js-items-per-page', function() {
	    var url_params = decodeURIParams( 'paged=1&items_per_page=' + $( this ).val() );
	    navigateWithURIParams( url_params );
    });

    $( document ).on( 'click', '.js-wpv-display-all-items', function( e ) {
	    e.preventDefault();
	    var url_params = decodeURIParams( 'paged=1&items_per_page=-1' );
	    navigateWithURIParams( url_params );
    });

    $( document ).on( 'click', '.js-wpv-display-default-items', function( e ) {
	    e.preventDefault();
	    var url_params = decodeURIParams( 'paged=1&items_per_page=20' );
	    navigateWithURIParams( url_params );
    });
	
	/**
	* -----------------
	* Add View dialog
	* -----------------
	*/
	
	$( document ).on( 'click', '.js-wpv-views-add-new-top, .js-wpv-views-add-new', function( e ) {
	    e.preventDefault();
		var dialog_height = $( window ).height() - 100;
		self.dialog_create_view.dialog( "open" ).dialog({
			maxHeight:	self.calculate_dialog_maxHeight(),
			maxWidth:	self.calculate_dialog_maxWidth(),
			position:	{ 
				my:			"center top+50", 
				at:			"center top", 
				of:			window, 
				collision:	"none"
			}
		});
    });

    /**
     * Create View action from the Toolset Dashboard on Types
     *
     * @since 2.3.0
     */
    $( document ).on( 'click', '.js-toolset-dashboard-create-view', function( e ) {
        e.preventDefault();
        var thiz = $( this );
        $( '.js-wpv-create-new-view' ).attr( 'data-type', thiz.data( 'type' ) );
        $( '.js-wpv-create-new-view' ).attr( 'data-redirect-url', thiz.data( 'redirect-url' ) );
        var dialog_height = $( window ).height() - 100;
        self.dialog_create_view.dialog( "open" ).dialog({
            maxHeight: self.calculate_dialog_maxHeight(),
            maxWidth: self.calculate_dialog_maxWidth(),
            position: {
                my: "center top+50",
                at: "center top",
                of: window,
                collision: "none"
            }
        });
    });
	
	$( document ).on( 'change input cut paste', '.js-wpv-purpose, .js-new-post_title', function() {
	    $( '#js-wpv-create-view-form-dialog' ).find( '.toolset-alert' ).remove();
		$( '.js-new-post_title' ).removeClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
		var thiz_message_container = $( this ).closest( '#js-wpv-create-view-form-dialog' ).find( '.js-wpv-error-container' );
	    if ( 
			'' != $('input.js-new-post_title').val() 
			&& 0 < $( 'input.js-wpv-purpose:checked' ).length
		) {
		    $( '.js-wpv-create-new-view' )
				.prop( 'disabled', false )
				.addClass( 'button-primary' )
				.removeClass( 'button-secondary' );
	    } else {
		    $( '.js-wpv-create-new-view' )
				.prop( 'disabled', true )
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary' );
	    }
		/*
	    if ( '' == $('.js-new-post_title' ).val() ) {
			thiz_message_container
				.wpvToolsetMessage({
					text: views_listing_texts.dialog_create_add_title_hint,
					type: 'info',
					stay: true
				});
	    }
		*/
	});

	$( document ).on( 'click', '.js-wpv-create-new-view', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
		thiz_message_container = $( '#js-wpv-create-view-form-dialog .js-wpv-error-container' ),
		spinnerContainer = $('<div class="wpv-spinner ajax-loader">').insertAfter( thiz ).show(),
		title = $('.js-new-post_title').val(),
		purpose = $('input.js-wpv-purpose:checked').val(),
		thiz_redirect_url = typeof thiz.data( 'redirect-url' ) != 'undefined' ? thiz.data( 'redirect-url' ) : '';
		data = {
			action: 'wpv_create_view',
			purpose: purpose,
			post_type: typeof thiz.data( 'type' ) != 'undefined' ? [ thiz.data( 'type' ) ] : [],
			wpnonce : $( '#wp_nonce_create_view' ).attr('value')
		};
		title = title.replace( /\'/gi, '' );
		title = WPV_Toolset.Utils._strip_tags_and_preserve_text( _.unescape( title ) );
		data.title = title;
		thiz
			.addClass( 'button-secondary' )
			.removeClass( 'button-primary' )
			.prop( 'disabled',true );
		$.post( ajaxurl, data, function( response ) {
			if ( response.success ) {
				var url = $( '.js-view-new-redirect' ).val();
				$( location ).attr( 'href', url + response.data.new_view_id + thiz_redirect_url );
			} else {
				$( '.js-new-post_title' ).addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
				thiz_message_container
					.wpvToolsetMessage({
						text: response.data.message,
						type: 'error',
						stay: true
					});
				thiz
					.addClass( 'button-primary' )
					.removeClass( 'button-secondary' )
					.prop( 'disabled', false );
				spinnerContainer.remove();
				//console.log(temp_res.error_message);
				return false;
			}
		})
		.fail( function( jqXHR, textStatus, errorThrown ) {
			if ( $('.js-create-view').parent().find('.unsaved').length < 1) {
				$('<span class="message unsaved"><i class="icon-warning-sign fa fa-exclamation-triangle"></i> error</span>').insertAfter($('.js-wpv-create-new-view')).show();
			}
			//console.log( "Error: ", textStatus, errorThrown );
		})
		.always (function() {

		});
	});
	
	
	/**
	* -----------------
	* Duplicate View dialog
	* -----------------
	*/
	
	$( '.js-views-actions-duplicate' ).on( 'click', function( e ) {
		e.preventDefault();

        var thiz = $( this ),
		dialog_height = $( window ).height() - 100;
		
		self.duplicating_id = thiz.data( 'view-id' );
		self.duplicating_title = thiz.data( 'view-title' );
		
		self.dialog_duplicate_view.dialog( "open" ).dialog({
			maxHeight:	self.calculate_dialog_maxHeight(),
			maxWidth:	self.calculate_dialog_maxWidth(),
			position:	{ 
				my:			"center top+50", 
				at:			"center top", 
				of:			window, 
				collision:	"none"
			}
		});
		
	});
	
	$( document ).on( 'change input cut paste', '.js-wpv-duplicated-title', function() {
		$( '#js-wpv-duplicate-view-dialog .toolset-alert').remove();
		if ( $( this ).val().length !== 0 ) {
			enablePrimaryButton( $( '.js-wpv-duplicate-view' ) );
		} else {
			disablePrimaryButton( $( '.js-wpv-duplicate-view' ) );
		}
	});
	
	$( document ).on( 'click', '.js-wpv-duplicate-view', function() {
		var thiz = $( this );
		disablePrimaryButton( thiz );
		showSpinnerBefore( thiz );

		if ( $('.js-wpv-duplicated-title').val().length !== 0 ) {

			var data = {
				action: 'wpv_duplicate_this_view',
				id: self.duplicating_id, 
				wpnonce : views_listing_texts.dialog_duplicate_nonce
			},
			name = $('.js-wpv-duplicated-title').val();
			name = name.replace( /\'/gi, '' );
			name = WPV_Toolset.Utils._strip_tags_and_preserve_text( _.unescape( name ) );
			data.name = name;
			
			$.ajax({
				async: false,
				type: "POST",
				dataType: "json",
				url: ajaxurl,
				data: data,
				success: function( response ) {
					if ( response.success ) {
						navigateWithURIParams( decodeURIParams() );
					} else {
						$('#js-wpv-duplicate-view-dialog .js-wpv-error-container').wpvToolsetMessage({
							text: response.data.message,
							stay: true,
							type: 'error'
						});
						hideSpinner();
					}
				},
				error: function (ajaxContext) {
					//console.log( "Error: ", ajaxContext.responseText );
				}
			});
		}
	});
	
	/**
	* -----------------
	* Action links
	* -----------------
	*/
	
	/**
	 * Delete action. Show the confirmation popup.
	 *
	 * @since unknown
	 */
	$( '.js-views-actions-delete' ).on( 'click', function( e ) {
		e.preventDefault();
        var thiz = $( this ),
		view_id = thiz.data( 'view-id' ),
		nonce = thiz.data( 'viewactionnonce' );
		// Act as if this was a bulk action.
		self.trashdelViewsConfirmation( [ view_id ], nonce, 'delete' );
	});
	
	/**
	 * Trash action. Move to trash and reload the page
	 *
	 * @since unknown
	 */
	$( '.js-views-actions-trash' ).on( 'click', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
        view_id = thiz.data( 'view-id' ),
		nonce = thiz.data( 'viewactionnonce' );
		// Act as if this was a bulk action.
		self.trashdelViewsConfirmation( [ view_id ], nonce, 'trash' )
	});
	
	/**
	 * Undo "trash" action when user clicks on the Undo link.
	 *
	 * @see wpv_admin_view_listing_message_undo() in wpv-views-listing-page.php
	 */ 
	$( document ).on( 'click', '.js-wpv-untrash', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
		nonce = thiz.data( 'nonce' ),
		viewIDs = decodeURIComponent( thiz.data( 'ids' ) ).split( ',' );
		
		showSpinnerAfter( thiz );
		untrashViews( viewIDs, nonce );
	});
	
	/**
	 * Restore from trash action.
	 *
	 * @since unknown
	 */
	$( '.js-views-actions-restore-from-trash' ).on( 'click', function( e ) {
		e.preventDefault();
        var thiz = $( this ),
		view_id = thiz.data( 'view-id' ),
		nonce = thiz.data( 'viewactionnonce' );

		$(this).parents('.js-wpv-view-list-row').find('h3').append(' <div class="wpv-spinner ajax-loader"></div>');
		$('.subsubsub').append('<div class="wpv-spinner ajax-loader"></div>');
		var data = {
			action: 'wpv_view_change_status',
			id: view_id,
			newstatus: 'publish',
			wpnonce : nonce
		};
		$.ajax({
			async: false,
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(response){
				if ( (typeof(response) !== 'undefined') && (response == data.id)) {
					var url_params = decodeURIParams();
					url_params['paged'] = updatePagedParameter( url_params, 1 );
					url_params['untrashed'] = 1;
					navigateWithURIParams(url_params);
				} else {
					//console.log( "Error: AJAX returned ", response );
				}
			},
			error: function (ajaxContext) {
				//console.log( "Error: ", ajaxContext.responseText );
			},
			complete: function() { }
		});
	});
	
	/**
	* -----------------
	* Bulk actions
	* -----------------
	*/
	
	/**
	 * Bulk action.
	 *
	 * Fires when user hits the Apply button near bulk action select field.
	 *
	 * @since 1.7
	 */
	$( '.js-wpv-views-listing-bulk-action-submit' ).on( 'click', function( e ) {
		e.preventDefault();
		showSpinner();
		var thiz = $( this ),
		nonce = thiz.data( 'viewactionnonce' ),
		// Get a position. That's important to determine which select field is relevant for us.
		selectPosition = thiz.data( 'position' ),
		// Launch appropriate bulk action
		action = $( '.js-wpv-views-listing-bulk-action-select.position-' + selectPosition ).val(),
		// Get an array of checked View IDs.
		checkedViews = $('.wpv-admin-listing-col-bulkactions input:checkbox:checked').map(function() {
			var value = $( this ).val();
			// Filter out values of checkboxes in table header and footer rows.
			if ( $.isNumeric( value ) ) {
				return value;
			}
		}).get();

		// If there are no items selected, do nothing.
		if ( checkedViews.length == 0 ) {
			hideSpinner();
			return;
		}

		switch ( action ) {
			case 'trash':
				self.trashdelViewsConfirmation( checkedViews, nonce, 'trash' );
				break;
			case 'restore-from-trash':
				untrashViews( checkedViews, nonce );
				break;
			case 'delete':
				self.trashdelViewsConfirmation( checkedViews, nonce, 'delete' );
				break;
			default:
				// do nothing
				hideSpinner();
				return;
		}
	});
	
	/**
	 * Show a popup with confirmation message and a table of Views to be deleted or trashed, each View
	 * with a "Scan" button with the same function as in the listing (see .js-scan-button).
	 *
	 * Views are deleted after clicking on .js-bulk-remove-view-permanent/trashed after clicking
	 * on .js-bulk-confirm-view-trash.
	 *
	 * @param string view_action Type of action user should confirm. Can be 'delete' or 'trash'.
	 *
	 * @since 1.7
	 */
	self.trashdelViewsConfirmation = function( viewIDs, nonce, view_action ) {
	
		self.bulkcombined_action = view_action;
		self.bulkcombined_target = viewIDs;
		self.bulkcombined_nonce = ( self.bulkcombined_action == 'trash' ) ? views_listing_texts.dialog_bulktrash_nonce : views_listing_texts.dialog_bulkdel_nonce;
		
		var dialog_height = $( window ).height() - 100;
		self.dialog_bulkcombined_view.dialog( 'open' ).dialog({
            title:		( self.bulkcombined_action == 'trash' ) ? views_listing_texts.dialog_bulktrash_dialog_title : views_listing_texts.dialog_bulkdel_dialog_title,
            width:		770,
			maxHeight:	self.calculate_dialog_maxHeight(),
			maxWidth:	self.calculate_dialog_maxWidth(),
			position:	{ 
				my:			"center top+50", 
				at:			"center top", 
				of:			window, 
				collision:	"none"
			}
        });
		
		self.manage_dialog_bulkcombined_view_button_labels();

        self.dialog_bulkcombined_view.html( self.shortcodeDialogSpinnerContent );
		
		var data = {
			action: 'wpv_view_bulk_trashdel_render_popup',
			ids: viewIDs,
			wpnonce : nonce,
			view_action: self.bulkcombined_action
		};

		$.ajax({
			async: false,
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: data,
			success: function( response ) {
				if ( response.success ) {
					if( response.data.ids != null && response.data.ids.length > 0 ) {
                        self.dialog_bulkcombined_view.html(response.data.dialog_content);
                        self.bulkcombined_target = response.data.ids;
                    } else {
                        self.dialog_bulkcombined_view.dialog( 'close' );
                    }
				}
				// We're waiting on user input - hide the spinner shown at the start of bulk action
				hideSpinner();
			},
			error: function( ajaxContext ) {
				//console.log( "Error: ", ajaxContext.responseText );
			},
			complete: function() { }
		});
	};
	
	self.manage_dialog_bulkcombined_view_button_labels = function() {
		if ( self.bulkcombined_action == 'trash' ) {
			$( '.js-wpv-bulkcombined-view .ui-button-text' ).html( views_listing_texts.dialog_bulktrash_action );
		} else {
			$( '.js-wpv-bulkcombined-view .ui-button-text' ).html( views_listing_texts.dialog_bulkdel_action );
		}
	};
	
	$( document ).on( 'click', '.js-wpv-bulkcombined-view', function() {
		var thiz = $( this );
		switch( self.bulkcombined_action ) {
			case 'trash':
				disablePrimaryButton( thiz );
				showSpinnerAfter( thiz );
				trashViews( self.bulkcombined_target, self.bulkcombined_nonce ); 
				break;
			case 'delete':
				disablePrimaryButton( thiz );
				showSpinnerAfter( thiz );
				var data = {
					action: 'wpv_bulk_delete_views_permanent',
					ids: self.bulkcombined_target,
					wpnonce : self.bulkcombined_nonce
				};
				$.ajax({
					async: false,
					type: "POST",
					url: ajaxurl,
					data: data,
					success: function( response ){
						// response == 1 indicates success
						if ( ( typeof( response ) !== 'undefined' ) && ( 1 == response ) ) {
							// reload the page with "deleted" message
							var url_params = decodeURIParams();
							var affectedItemCount = self.bulkcombined_target.length;
							url_params['paged'] = updatePagedParameter( url_params, affectedItemCount );
							url_params['deleted'] = affectedItemCount;
							navigateWithURIParams( url_params );
						} else {
							//console.log( "Error: AJAX returned ", response );
						}
					},
					error: function (ajaxContext) {
						//console.log( "Error: ", ajaxContext.responseText );
					},
					complete: function() {	}
				});
				break;
			default:
				break;
		}
	});

    /**
     * Empty trash. Show the confirmation popup.
     *
     * @since 2.3.0
     */
    $( '.js-wpv-views-empty-trash' ).on( 'click', function( e ) {
        e.preventDefault();
        var thiz = $( this ),
            view_id = '-1',
            nonce = thiz.data( 'viewactionnonce' );
        // Act as if this was a bulk action.
        self.trashdelViewsConfirmation( [ view_id ], nonce, 'delete' );
        //console.log(nonce);
    });
	
	/**
	* -----------------
	* Init dialogs
	* -----------------
	*/
	
	self.init_dialogs = function() {
		
		self.dialog_create_view = $( "#js-wpv-create-view-form-dialog" ).dialog({
			autoOpen:	false,
			modal:		true,
			title:		views_listing_texts.dialog_create_dialog_title,
			minWidth:	600,
			draggable:	false,
			resizable:	false,
			show: { 
				effect: "blind", 
				duration: 800 
			},
			open: function( event, ui ) {
				$( 'body' ).addClass( 'modal-open' );
				$( '.js-new-post_title' ).val( '' );
				if ( 0 < $('input.js-wpv-purpose:checked').length)  {
					$( 'input.js-wpv-purpose:checked' ).prop('checked', false);
				}
				disablePrimaryButton( $( '.js-wpv-create-new-view' ) );
				$( '#js-wpv-create-view-form-dialog .toolset-alert' ).remove();
                $( '#js-wpv-create-view-form-dialog' ).find( '.toolset-alert' ).remove();
                $( '.js-new-post_title' ).removeClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
			},
			close: function( event, ui ) {
				$( 'body' ).removeClass( 'modal-open' );
			},
			buttons:[
				{
					class: 'toolset-shortcode-gui-dialog-button-align-right button-primary js-wpv-create-new-view',
					text: views_listing_texts.dialog_create_action,
					click: function() {

					}
				},
				{
					class: 'button-secondary',
					text: views_listing_texts.dialog_cancel,
					click: function() {
						$( this ).dialog( "close" );
					}
				}
			]
		});
		
		self.dialog_duplicate_view = $( "#js-wpv-duplicate-view-dialog" ).dialog({
			autoOpen:	false,
			modal:		true,
			title:		views_listing_texts.dialog_duplicate_dialog_title,
			minWidth:	600,
			draggable:	false,
			resizable:	false,
			show: { 
				effect: "blind", 
				duration: 800 
			},
			open: function( event, ui ) {
				$( 'body' ).addClass( 'modal-open' );
				$('.js-duplicate-origin-title').html( self.duplicating_title );
				$('.js-wpv-duplicated-title').focus().val('');
				disablePrimaryButton( $( '.js-wpv-duplicate-view' ) );
				$( '#js-wpv-duplicate-view-dialog .toolset-alert').remove();
			},
			close: function( event, ui ) {
				$( 'body' ).removeClass( 'modal-open' );
				self.duplicating_id = 0;
				self.duplicating_title = '';
			},
			buttons:[
				{
					class: 'toolset-shortcode-gui-dialog-button-align-right button-primary js-wpv-duplicate-view',
					text: views_listing_texts.dialog_duplicate_action,
					click: function() {

					}
				},
				{
					class: 'button-secondary',
					text: views_listing_texts.dialog_cancel,
					click: function() {
						$( this ).dialog( "close" );
					}
				}
			]
		});
		
		$( 'body' ).append( '<div id="js-wpv-dialog-bulkcombined-view" class="toolset-shortcode-gui-dialog-container wpv-shortcode-gui-dialog-container js-wpv-shortcode-gui-dialog-container"></div>' );
		
		self.dialog_bulkcombined_view = $( "#js-wpv-dialog-bulkcombined-view" ).dialog({
			autoOpen:	false,
			modal:		true,
			title:		views_listing_texts.dialog_bulktrash_dialog_title,
			minWidth:	600,
			draggable:	false,
			resizable:	false,
			show: { 
				effect: "blind", 
				duration: 800 
			},
			open: function( event, ui ) {
				$( 'body' ).addClass( 'modal-open' );
			},
			close: function( event, ui ) {
				$( 'body' ).removeClass( 'modal-open' );
				self.bulkcombined_action = '';
				self.bulkcombined_target = [];
				self.bulkcombined_nonce = '';
			},
			buttons:[
				{
					class: 'toolset-shortcode-gui-dialog-button-align-right button-primary js-wpv-bulkcombined-view js-wpv-empty-trash-confirm',
					text: views_listing_texts.dialog_bulktrash_action,
					click: function() {

					}
				},
				{
					class: 'button-secondary',
					text: views_listing_texts.dialog_cancel,
					click: function() {
						$( this ).dialog( "close" );
					}
				}
			]
		});
		
	};
	
	self.init = function() {
		self.init_dialogs();
	};
	
	self.init();

};

jQuery( document ).ready( function( $ ) {
    WPViews.views_listing_screen = new WPViews.ViewsListingScreen( $ );
});
