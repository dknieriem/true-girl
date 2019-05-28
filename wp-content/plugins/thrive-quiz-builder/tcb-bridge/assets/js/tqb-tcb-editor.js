/**
 * Created by Ovidiu on 10/3/2016.
 */
var TVE_Content_Builder = TVE_Content_Builder || {};
TVE_Content_Builder.ext = TVE_Content_Builder.ext || {};

var TQB_Editor = TQB_Editor || {};

(function ( $ ) {
	/**
	 * DOMReady
	 */
	$( function () {


		if ( tqb_page_data.allow_tqb_advanced ) {

			// Tooltip for results interval
			$( '[data-toggle="tooltip"]' ).tooltip();

			/**
			 * Load popover
			 */
			TQB_Editor.popover();

			TVE_Content_Builder.add_filter( 'element_remove', function ( $element ) {
				if ( $element.hasClass( 'tqb-social-share-badge-container' ) ) {
					$( parent.document ).find( 'li[data-elem="sc_tqb_social_share_badge_container"]' ).css( 'display', 'block' );
				}
			} );

			TVE_Content_Builder.add_filter( 'allow_drop', function ( return_default_value, params ) {
				var $dragged_element = params[0],
					handle = params[1];
				if ( $dragged_element.hasClass( 'tqb-social-share-badge-container' ) &&
				     $( handle.data( 'tt' )[0].parentElement ).closest( '.tqb-dynamic-content-container' ).length ) {
					tve_add_notification( 'Social share element can not be dropped inside dynamic content element!', 2, 5000 );
					return false;
				}
				return true;
			} );

			TVE_Content_Builder.add_filter( 'allow_remove', function ( return_default_value, params ) {
				var $element = params[0];
				if ( $element.hasClass( 'tqb-dynamic-content-container' ) ) {
					$( '#tqb_delete_dynamic_content' ).click();
					return false;
				}
				return true;
			} );

			TVE_Content_Builder.add_filter( 'element_drop', function ( $element ) {
				if ( $element.hasClass( 'tqb-dynamic-content-container' ) ) {
					$element.removeClass( 'tve-dropped' );

					if ( tqb_page_data.is_personality_type ) {
						TVE_Content_Builder.ext.tqb.state.generate_personality_child_variations();
					} else {
						tve_open_lightbox( $( parent.document ).find( 'li[data-elem="sc_tqb_dynamic_content"] a' ) );
					}
					$( parent.document ).find( 'li[data-elem="sc_tqb_dynamic_content"]' ).css( 'display', 'none' );
					TVE_Editor_Page.overlay( 'close' );
				}

				if ( $element.hasClass( 'tqb-social-share-badge-container' ) ) {
					if ( $element.closest( '.tqb-dynamic-content-container' ).length ) {
						$element.remove();
						tve_add_notification( 'Social share element can not be dropped inside dynamic content element!', 2, 5000 );
					} else {
						TVE_Content_Builder.ext.tqb.social_share_badge.change_template();
						$( parent.document ).find( 'li[data-elem="sc_tqb_social_share_badge_container"]' ).css( 'display', 'none' );
					}
				}
			} );

			/* Click the first state item */
			TQB_Editor.click_first_state();

			// Hover functionality
			TQB_Editor.hover_numeric_range_preview();

			/*
			 Social share badge menu settings
			 */
			$( TVE_Content_Builder ).on( 'menu_show_social_share_badge_settings', function ( event, element, $menu ) {
				TVE_Content_Builder.$social = $( element ).tve_social();
				TVE_Content_Builder.social.onMenuShow( $menu, 'custom' );
			} );

			$( '#tve_lightbox_frame' ).on( 'lb-close', function ( event, data ) {
				if ( this.getElementsByClassName( 'tqb-social-share-badge-template-action' ).length > 0 &&
				     $( '.tqb-social-share-badge-container' ).html().length === 0 ) {
					$( '#tqb-editor-replace' ).find( '.tqb-social-share-badge-container' ).remove();
					$( parent.document ).find( 'li[data-elem="sc_tqb_social_share_badge_container"]' ).css( 'display', 'block' );
				}

				if ( this.getElementsByClassName( 'tqb-result-intervals-action' ).length > 0 ) {
					if ( ! $( '#tqb-editor-replace' ).find( '.tqb-dynamic-content-container' ).attr( 'tqb-saved' ) ) {
						$( '#tqb-editor-replace' ).find( '.tqb-dynamic-content-container' ).remove();
						$( parent.document ).find( 'li[data-elem="sc_tqb_dynamic_content"]' ).css( 'display', 'block' );
					}

				}
			} );
		}

		TVE_Content_Builder.add_filter( 'validate_saved_content', function ( $element ) {
			if ( tqb_page_data.variation_type === 'splash' || tqb_page_data.variation_type === 'optin' ) {

				if ( $( '.tqb-shortcode-submit-action' ).length > 0 ) {
					return true;
				}

				if ( $( '.thrv_lead_generation form' ).length > 0 ) {
					return true;
				}

				var is_next_step = false;
				$( '.tve_evt_manager_listen' ).each( function ( index ) {
					var event_string = $( this ).attr( 'data-tcb-events' ),
						events = ThriveGlobal.$j.parseJSON( event_string.replace( '__TCB_EVENT_', '' ).replace( '_TNEVE_BCT__', '' ) );
					for ( var i = 0, len = events.length; i < len; i ++ ) {
						if ( events[i].a === 'thrive_quiz_next_step' ) {
							is_next_step = true;
						}
					}
				} );
				if ( is_next_step ) {
					return true;
				}

				var $modal = $( '#tqb-leanmodal-element' );
				$( '#tqb-leanmodal-action-button', $modal ).html( tqb_page_data.L.missing_step_error_continue ).focus();
				$( '#tqb-leanmodal-description-text', $modal ).html( tqb_page_data.L.missing_step_error + ' <a class="tqb-open-external-link" href="' + tqb_page_data.kb_next_step_article + '" target="_blank">' + tqb_page_data.L.missing_step_error_link + '</a>' );
				$( '#tqb-leanmodal-title-text', $modal ).html( tqb_page_data.L.missing_step_error_title );
				$( '.tqb-open-external-link' ).on( 'click', function ( event ) {
					event.stopPropagation();
				} );
				$modal.openModal();
				return false;
			}

			return true;
		} );

		TVE_Content_Builder.add_filter( 'menu_show', TQB_Editor.on_menu_show );

		if ( ! tqb_page_data.has_content ) {
			/* we can't add filter because apply filter is called earlier than this point :| */
			TQB_Editor.open_template_chooser();
			TVE_Content_Builder.add_filter( 'main_ajax_callback', TQB_Editor.open_template_chooser );
		}

		/**
		 * Media selector callback - used to insert a background image on the design
		 */
		TVE_Content_Builder.add_filter( 'tcb_media_selected', TQB_Editor.media_selected );

		$( TVE_Content_Builder ).on( 'tve.tve_save_post', function ( event ) {
			$( '.thrv_lead_generation form' ).each( function ( index, form ) {
				if ( ! $( form ).hasClass( 'tqb-form' ) ) {
					$( form ).addClass( 'tqb-form' );
					var data = {
						page_id: tqb_page_data.page_id,
						variation_id: tqb_page_data.variation_id,
					};
					var extra_fields = '<input type="hidden" name="tqb-variation-page_id" class="tqb-hidden-form-info" value="' + tqb_page_data.page_id + '" >';
					extra_fields += '<input type="hidden" name="tqb-variation-variation_id" class="tqb-hidden-form-info" value="' + tqb_page_data.variation_id + '" >';
					extra_fields += '<input type="hidden" name="tqb-variation-user_unique" class="tqb-hidden-form-info tqb-hidden-user-unique" value="" >';
					$( form ).prepend( extra_fields );
				}
			} );

		} );

	} );

	TQB_Editor.popover = function () {
		var $popover_selector = $( '.tqb-tcb-intervals-item' );

		if ( $popover_selector.data( 'bs.popover' ) ) {
			$popover_selector.popover( 'destroy' );
		}
		$popover_selector.popover( {} );

		$popover_selector.on( 'click', function ( e ) {
			$popover_selector.not( this ).popover( 'hide' );
		} );
	};

	// Adjust popover position after the state bar is inserted.
	TQB_Editor.popover_position = function () {
		var position = $( '.tqb-tcb-intervals-item' ).position();
		$( '.popover' ).css( 'top', position.top - 150 );
	};

	// Shows / Hide popover
	TQB_Editor.show_hide_popover = function ( interval_id ) {
		$( '.tqb-tcb-intervals-item[data-id="' + interval_id + '"]' ).popover( 'toggle' );
	};

	TQB_Editor.click_first_state = function () {
		var $intervals_items = $( '.tqb-tcb-intervals-item' );
		if ( $intervals_items.length ) {
			TVE_Content_Builder.ext.tqb.state.state_click( $intervals_items.first() );
			TQB_Editor.show_hide_popover( $intervals_items.first().attr( 'data-id' ) );
		}
	};

	/**
	 * Hover functionality. Numeric Range Preview
	 */
	TQB_Editor.hover_numeric_range_preview = function () {
		$( '.tqb-tcb-intervals-item' ).hover(
			function () {
				var $this = $( this );
				if ( ! $this.hasClass( 'tqb-tcb-intervals-item-active' ) ) {
					$( '.tqb-tcb-numeric-range-preview[data-id="' + $this.attr( 'data-id' ) + '"]' ).css( 'visibility', 'visible' );
				}
			},
			function () {
				var $this = $( this );
				if ( ! $this.hasClass( 'tqb-tcb-intervals-item-active' ) ) {
					$( '.tqb-tcb-numeric-range-preview[data-id="' + $this.attr( 'data-id' ) + '"]' ).css( 'visibility', 'hidden' );
				}
			}
		);
	};

	/**
	 * display the custom menu for designs
	 *
	 * @param {object} element
	 *
	 * @return {Boolean} whether or not the element menu is identified
	 */
	TQB_Editor.on_menu_show = function ( element ) {
		if ( element.hasClass( 'tve-tqb-page-type' ) ) {
			load_control_panel_menu( element, 'tqb_page' );
			return true;
		}
		if ( element.hasClass( 'tqb-social-share-badge-container' ) ) {
			load_control_panel_menu( element, 'social_share_badge_settings' );
			return true;
		}
		return false;
	};


	/**
	 * callback applied when the user selects an image from the WP media frame API and the type has not been identified by TCB
	 *
	 * @param {object} $element
	 * @param {string} type
	 * @param {object} attachment
	 */
	TQB_Editor.media_selected = function ( $element, type, attachment ) {
		switch ( type ) {
			case 'tqb_variation_bg':
				$element.tqb_variation().bgImage( attachment );
				break;
			default:
				break;
		}
	};

	/**
	 * called after the main AJAX request callback - it will open the template chooser by default,
	 * allowing the user to choose a template the first time a form variation is edited
	 */
	TQB_Editor.open_template_chooser = function () {
		TVE_Editor_Page.overlay();
		TCB_Main.trigger( 'trigger_action', '#tqb-tpl-chooser', 'click' );

		//hide the close button on the template chooser so that the user cannot cancel the process
		setTimeout( function () {
			$( '#tve_lightbox_frame' ).find( '.tve-lightbox-close' ).hide();
			hide_sub_menu();
		}, 0 );
	};

	var Util = {
		tpl_ajax: function ( data, ajax_param, no_loader ) {
			var params = {
				type: 'post',
				dataType: 'json',
				url: tqb_page_data.ajaxurl
			};
			if ( typeof no_loader === 'undefined' || ! no_loader ) {
				TVE_Editor_Page.overlay();
			}
			data.action = tqb_page_data.tpl_action;
			data.variation_id = data.variation_id || tqb_page_data.variation_id;
			data.page_id = tqb_page_data.page_id;
			data.security = tqb_page_data.security;
			data.tqb_key = true;  // This flag set to true for the tqb-class-hooks.php to be included
			params.data = data;

			if ( ajax_param ) {
				for ( var k in ajax_param ) {
					params[k] = ajax_param[k];
				}
			}

			return jQuery.ajax( params, data );
		},
		/**
		 * actions related to a state
		 * @param data
		 * @param ajax_param
		 * @returns {*}
		 */
		state_ajax: function ( data, ajax_param ) {
			var params = {
				type: 'post',
				dataType: 'json',
				url: tqb_page_data.ajaxurl
			};
			TVE_Editor_Page.overlay();
			data.action = tqb_page_data.state_action;
			data.variation_id = data.variation_id || tqb_page_data.variation_id;
			data.page_id = tqb_page_data.page_id;
			data.security = tqb_page_data.security;
			data.tqb_key = true;  // This flag set to true for the tqb-class-hooks.php to be included
			params.data = data;

			if ( ajax_param ) {
				for ( var k in ajax_param ) {
					params[k] = ajax_param[k];
				}
			}

			return jQuery.ajax( params, data );
		},
		/**
		 * Checks if a string is an integer number
		 *
		 * @param value
		 * @returns {boolean|*}
		 */
		is_integer_number: function ( value ) {
			return Math.floor( value ) == value && $.isNumeric( value );
		}
	};

	TVE_Content_Builder.ext.tqb = {
		open_page_settings: function ( $btn, $element, e ) {
			hide_sub_menu();
			var $wrapper = $( '.tve-tqb-page-type' );
			e.target = $wrapper;
			e.currentTarget = $wrapper;
			tve_editor_init( e, $wrapper );
		},
		/**
		 * show the WP media API selector for design backgrounds
		 */
		open_bg_media: function ( $btn, $element ) {
			thrive_open_media( null, 'load', 'tqb_variation_bg', $element );
		},
		template: {
			/**
			 * Choose a template action
			 *
			 * @param $btn
			 * @returns {boolean}
			 */
			choose: function ( $btn ) {
				var $selected = $( '#tqb-tpl' ).find( '.tve_cell_selected:visible' );
				if ( ! $selected.length ) {
					tve_add_notification( tqb_page_data.L.alert_choose_tpl, true );
					return false;
				}
				/**
				 * if the user is choosing a multi-step, warn him that he will lose all other states
				 */
				if ( tqb_page_data.has_content && $selected.find( '.multi_step' ).val() === '1' && ! confirm( tqb_page_data.L.confirm_multi_step ) ) {
					return false;
				}
				Util.tpl_ajax( {
					custom: 'choose',
					tpl: $selected.find( '.lp_code' ).val()
				} ).done( TVE_Content_Builder.ext.tqb.state.insertResponse );
				TVE_Content_Builder.controls.lb_close();
				$( '#tve_lightbox_frame' ).find( '.tve-lightbox-close' ).show();
			},
			/**
			 * Resets the variation to the default template
			 *
			 * @param $btn
			 * @returns {boolean}
			 */
			reset: function ( $btn ) {
				Util.tpl_ajax( {
					custom: 'reset'
				} ).done( TVE_Content_Builder.ext.tqb.state.insertResponse );
				TVE_Content_Builder.controls.lb_close();
			},
			/**
			 * Save the current template as a user-defined one
			 */
			save: function ( $btn ) {
				var _name = $btn.parent().find( 'input#tve_landing_page_name' ).val();
				if ( ! _name ) {
					tve_add_notification( tqb_page_data.L.tpl_name_required, true, 5000 );
					return;
				}
				Util.tpl_ajax( {
					custom: 'save',
					name: $btn.parent().find( 'input#tve_landing_page_name' ).val()
				} ).done( function ( response ) {
					$( '#tve_landing_page_msg' ).removeClass( 'tve_warning' ).addClass( 'tve_success' ).html( response.message );
					$( '#tqb-saved-templates' ).html( response.list );
					TVE_Editor_Page.overlay( 'close' );
				} );
			},
			/**
			 * get all the user saved templates
			 */
			get_saved: function () {
				$( '#tqb-saved-templates' ).html( '<p class="tqb-tpl-loading">' + tqb_page_data.L.fetching_saved_templates + '</p>' );
				var current_template = $( '#tqb-user-current-templates:checked' ).length;
				Util.tpl_ajax( {
					custom: 'get_saved',
					current_template: current_template
				}, {
					dataType: 'html'
				}, true ).done( function ( response ) {
					TVE_Editor_Page.overlay( 'close' );
					$( '#tqb-saved-templates' ).html( response );
				} );
				return false;
			},
			/**
			 * Triggered when the user clicks on the save template tab
			 */
			user_tab_clicked: function () {
				if ( $( '#tqb-saved-templates' ).find( '.tqb-tpl-loading' ).length ) {
					setTimeout( this.get_saved, 150 );
				}
			},
			/**
			 * delete a saved template
			 *
			 * @returns {boolean}
			 */
			delete_saved: function () {
				var $selected = jQuery( '#tqb-saved-templates' ).find( '.tve_cell_selected:visible' );

				if ( ! $selected.length ) {
					alert( tqb_page_data.L.alert_choose_tpl );
					return false;
				}

				if ( ! confirm( tqb_page_data.L.tpl_confirm_delete ) ) {
					return false;
				}

				Util.tpl_ajax( {
					custom: 'delete',
					tpl: $selected.find( '.lp_code' ).val()
				}, {
					dataType: 'html'
				} ).done( function ( response ) {
					jQuery( '#tqb-saved-templates' ).html( response );
					TVE_Editor_Page.overlay( 'close' );
					tve_add_notification( tqb_page_data.L.template_deleted, false, 5000 );
				} );
			}
		},
		state: {
			/**
			 * Lightbox state chooser
			 *
			 * @param elem
			 */
			lightbox_state_choose: function ( elem ) {
				var $element = $( elem ),
					_value = $element.val(),
					_html = '',
					$interval_prev = $( '#tqb-intervals-preview' ),
					_interval_max = parseInt( $( '#tqb_result_intervals' ).attr( 'max' ) );

				if ( ! Util.is_integer_number( _value ) ) {
					tve_add_notification( 'The input must be an integer number', 2, 5000 );
					$interval_prev.html( '' );
					return;
				}

				if ( _value > _interval_max || _value < 1 ) {
					tve_add_notification( 'The input must be between 1 and ' + _interval_max, 2, 5000 );
					$interval_prev.html( '' );
					return;
				}

				_html += '<div class="tqb-tcb-intervals-preview-row">';
				for ( var i = 0; i < _value; i ++ ) {
					_html += '<div class="tqb-tcb-intervals-preview-column"></div>'
				}
				_html += '</div>';
				_html += '<div class="tqb-tcb-number-preview"><div class="tve_left">' + tqb_page_data.quiz_config.absolute_min_value + '</div><div class="tve_right">' + tqb_page_data.quiz_config.absolute_max_value + '</div></div>'
				$interval_prev.html( _html );
			},
			/**
			 * Generates states based on a given number
			 *
			 * @param $btn
			 */
			lightbox_save_states_number: function ( $btn ) {
				var _result_interval = $( '#tqb_result_intervals' ).val(),
					_interval_max = parseInt( $( '#tqb_result_intervals' ).attr( 'max' ) );

				if ( ! Util.is_integer_number( _result_interval ) ) {
					tve_add_notification( 'The input must be an integer number', 2, 5000 );
					return;
				}


				if ( _result_interval > _interval_max || _result_interval < 0 ) {
					tve_add_notification( 'The input must be between 0 and ' + _interval_max, 2, 5000 );
					return;
				}

				Util.state_ajax( {
					custom: 'set_result_intervals',
					result_interval: _result_interval
				} ).done( function ( response ) {
					TVE_Content_Builder.ext.tqb.state.insertStateResponse( response );
					TQB_Editor.click_first_state();
				} );
			},
			/**
			 * Delete Dynamic Content
			 *
			 * @param $btn
			 */
			lightbox_delete_all_states: function ( $btn ) {
				Util.state_ajax( {
					custom: 'delete_dynamic_content'
				} ).done( function ( response ) {
					$( '#tqb-editor-replace' ).find( '.tqb-dynamic-content-container' ).remove();
					$( '#tqb-form-states' ).html( '' );
					$( parent.document ).find( 'li[data-elem="sc_tqb_dynamic_content"]' ).css( 'display', 'block' );

					//The child variations (dynamic content) has been deleted. Therefore there is no active state with dynamic content
					tve_path_params.custom_post_data.tqb_child_variation_id = null;
					tve_save_post( 'true' );
				} );
			},
			/**
			 * Copy dynamic content from previous page (result / opt-in)
			 *
			 * @param $btn
			 */
			lightbox_copy_states_from_prev_page: function ( $btn ) {
				Util.state_ajax( {
					custom: 'copy_similar_dynamic_content'
				} ).done( function ( response ) {
					TVE_Content_Builder.ext.tqb.state.insertStateResponse( response );
					TQB_Editor.click_first_state();
				} );
			},
			/**
			 * Redirects to custom href
			 *
			 * @param $btn
			 */
			redirect: function ( $btn ) {
				if ( $btn.attr( 'data-href' ) ) {
					top.location.href = $btn.attr( 'data-href' );
				}
			},
			/**
			 * Update interval limits
			 */
			update_intervals: function () {
				var min = $( '#tqb-range-min' ).val(),
					max = $( '#tqb-range-max' ).val(),
					prev_min = parseInt( $( '#tqb-prev-min' ).val() ),
					next_max = parseInt( $( '#tqb-next-max' ).val() ),
					child_id = $( '#tqb-child-id' ).val(),
					child_prev_id = $( '#tqb-child-prev-id' ).val(),
					child_next_id = $( '#tqb-child-next-id' ).val();

				/*
				 The input must be an integer number
				 */
				if ( ! Util.is_integer_number( max ) || ! Util.is_integer_number( min ) ) {
					tve_add_notification( 'The input must be an integer number', 2, 5000 );
					return;
				}

				/*
				 Check for limits of the intervals change:
				 */
				if ( ! Util.is_integer_number( prev_min ) && min != tqb_page_data.quiz_config.absolute_min_value ) {
					tve_add_notification( tqb_page_data.L.intervals_min_val_cannot_be_changed, 2, 5000 );
					return;
				}

				if ( ! Util.is_integer_number( next_max ) && max != tqb_page_data.quiz_config.absolute_max_value ) {
					tve_add_notification( tqb_page_data.L.intervals_max_val_cannot_be_changed, 2, 5000 );
					return;
				}
				/*
				 END Check for limits of the intervals change:
				 */

				/*
				 Check for the minimum requirements
				 */
				if ( min < (prev_min + 1) ) {
					tve_add_notification( tqb_page_data.L.min_value_limit + (prev_min + 1), 2, 5000 );
					return;
				}

				if ( max > (next_max - 1) ) {
					tve_add_notification( tqb_page_data.L.max_value_limit + (next_max - 1), 2, 5000 );
					return;
				}
				/*
				 END Check for the minimum requirements
				 */

				/*
				 * Additional Checks
				 */
				if ( parseInt( max ) < parseInt( min ) ) {
					tve_add_notification( 'ERROR: Max value can not be greater than min value', 2 );
					return;
				}
				/*
				 * END Additional Checks
				 */

				if ( child_id ) {
					Util.state_ajax( {
						custom: 'update',
						min: min,
						max: max,
						child_id: child_id,
						child_prev_id: child_prev_id,
						child_next_id: child_next_id,
						quiz_min: tqb_page_data.quiz_config.absolute_min_value,
						quiz_max: tqb_page_data.quiz_config.absolute_max_value
					} ).done( function ( response ) {
						TVE_Content_Builder.ext.tqb.state.insertStateResponse( response );
//						TQB_Editor.show_hide_popover( child_id );
						$( '.tqb-tcb-intervals-item[data-id="' + child_id + '"]' ).trigger( 'click' );
					} );
				} else {
					tve_add_notification( 'Something is wrong! Please contact Thrive Support!' );
				}
			},
			/**
			 * Generate ABC child variations
			 */
			generate_personality_child_variations: function () {
				Util.state_ajax( {
					custom: 'generate_personality_child_variations'
				} ).done( function ( response ) {
					TVE_Content_Builder.ext.tqb.state.insertStateResponse( response );
					/* Click the first state item */
					TQB_Editor.click_first_state();
				} );
			},
			/**
			 * State click action handler
			 *
			 * @param $btn
			 */
			state_click: function ( $btn ) {
				tve_path_params.custom_post_data.tqb_child_variation_id = $( '.tqb-tcb-intervals-item-active' ).attr( 'data-id' );
				var $interval_item = $( '.tqb-tcb-intervals-item' );

				tve_save_post( 'true', function () {
					Util.state_ajax( {
						custom: 'get_child_variation',
						child_variation: $btn.attr( 'data-id' )
					} ).done( function ( response ) {
						$interval_item.removeClass( 'tqb-tcb-intervals-item-active' );
						$btn.addClass( 'tqb-tcb-intervals-item-active' );
						if ( response ) {

							$( '.tqb-dynamic-content-container' ).html( response.content );
							tve_path_params.custom_post_data.tqb_child_variation_id = response.id;
							// Adjust popover position after the state bar is inserted.
							TQB_Editor.popover_position();
						}
						TVE_Editor_Page.overlay( 'close' );
					} );
				} );
			},
			/**
			 * Split a state into 2 equal states
			 *
			 * @returns {boolean}
			 */
			state_split: function () {
				var child_id = $( '#tqb-child-id' ).val(),
					self = this;

				if ( ! Util.is_integer_number( child_id ) ) {
					return false;
				}

				Util.state_ajax( {
					custom: 'split',
					child_id: child_id
				} ).done( function ( response ) {
					TVE_Content_Builder.ext.tqb.state.insertStateResponse( response );
					$( '.tqb-tcb-intervals-item[data-id="' + child_id + '"]' ).trigger( 'click' );
				} );
			},
			/**
			 * Equalize all states
			 *
			 * @returns {boolean}
			 */
			state_equalize: function () {
				Util.state_ajax( {
					custom: 'equalize'
				} ).done( function ( response ) {
					TVE_Content_Builder.ext.tqb.state.insertStateResponse( response );
					TQB_Editor.click_first_state();
				} );
			},
			/**
			 * Reset all states
			 */
			state_reset: function () {
				tve_open_lightbox( $( parent.document ).find( 'li[data-elem="sc_tqb_dynamic_content"] a' ) );
			},
			/**
			 * Import Content Lightbox Trigger
			 */
			import_content_lightbox: function () {
				tve_open_lightbox( $( '#tqb_import_state_content' ) );
			},
			/**
			 * Import Content Lightbox Action
			 *
			 * @param $btn
			 * @returns {boolean}
			 */
			import_content: function ( $btn ) {
				var import_to = $btn.attr( 'data-tqb-import-to' ),
					import_from = $( '#tqb-import-from' ).val();

				if ( ! Util.is_integer_number( import_to ) || ! Util.is_integer_number( import_from ) ) {
					return false;
				}

				Util.state_ajax( {
					custom: 'import',
					import_to: import_to,
					import_from: import_from
				} ).done( function ( html ) {
					$( '.tqb-dynamic-content-container' ).html( html );
					TQB_Editor.popover_position();
					TVE_Content_Builder.controls.lb_close();
					TVE_Editor_Page.overlay( 'close' );
				} );

			},
			/**
			 * Remove a state
			 *
			 * @param $btn
			 * @returns {boolean}
			 */
			remove_state: function ( $btn ) {
				var child_id = $( '#tqb-child-id' ).val(),
					child_prev_id = $( '#tqb-child-prev-id' ).val(),
					child_next_id = $( '#tqb-child-next-id' ).val(),
					child_to_click = (child_next_id) ? child_next_id : child_prev_id;


				if ( ! Util.is_integer_number( child_id ) ) {
					return false;
				}

				Util.state_ajax( {
					custom: 'remove',
					child_id: child_id,
					child_prev_id: child_prev_id,
					child_next_id: child_next_id
				} ).done( function ( response ) {
					TVE_Content_Builder.ext.tqb.state.insertStateResponse( response );
					$( '.tqb-tcb-intervals-item[data-id="' + child_to_click + '"]' ).trigger( 'click' );
				} );
			},
			/**
			 * Insert the response that comes from the states
			 *
			 * @param response
			 */
			insertStateResponse: function ( response ) {
				$( '#tqb-editor-replace' ).find( '.tqb-dynamic-content-container' ).attr( 'tqb-saved', 'true' );
				TVE_Content_Builder.controls.lb_close();

				/**
				 * javascript page data
				 */
				tqb_page_data = jQuery.extend( tqb_page_data, response.tqb_page_data, true );

				/**
				 State bar is only for the result page
				 */
				if ( response.state_bar ) {
					$( '#tqb-form-states' ).html( response.state_bar );
				}

				/*Popover trigger*/
				TQB_Editor.popover();
				/*Hover trigger*/
				TQB_Editor.hover_numeric_range_preview();

				TVE_Editor_Page.overlay( 'close' );
			},
			insertResponse: function ( response ) {
				if ( ! response || response.error ) {
					tve_add_notification( 'Something went wrong' + (
							response && response.error ? ': ' + response.error : ''
						), true );
					setTimeout( function () {
						TVE_Editor_Page.overlay( 'close' );
					}, 1 );
					return;
				}

				/**
				 * callback to be applied when all css files are loaded and available
				 */
				function on_resources_loaded() {
					TVE_Content_Builder.controls.lb_close();
					TVE_Editor_Page.initEditorActions();
					try {
						tve_init_sliders();
					} catch ( exception ) {
						console.log( exception );
					}
					setTimeout( function () {
						TVE_Editor_Page.overlay( 'close' );
					}, 1 );
				}

				/**
				 * browser-compliant way of accessing stylesheet rules
				 */
				var sheet, cssRules, _link = document.createElement( 'link' );
				if ( 'sheet' in _link ) {
					sheet = 'sheet';
					cssRules = 'cssRules';
				} else {
					sheet = 'styleSheet';
					cssRules = 'rules';
				}

				/**
				 * checks if all the added CSS <link> elements are available (finished loading and applied)
				 *
				 * @param {jQuery} $jq_links collection of added <link> nodes
				 * @param {Function} complete_callback
				 */
				function check_loaded( $jq_links, complete_callback ) {
					var all_loaded = true;
					window.tqb_loaded_count = window.tqb_loaded_count || 1;
					window.tqb_loaded_count ++;
					$jq_links.each( function () {

						/** firefox throws an Error when testing this condition and the css is not loaded yet */
						try {
							if ( ! this[sheet] || ! this[sheet][cssRules] || ! this[sheet][cssRules].length ) {
								all_loaded = false;
								return false; // break the loop
							}
						} catch ( e ) {
							all_loaded = false;
							return false;
						}
					} );
					if ( all_loaded || window.tqb_loaded_count > 40 ) {
						complete_callback();
					} else {
						setTimeout( function () {
							check_loaded( $jq_links, complete_callback );
						}, 500 );
					}
				}

				/** custom CSS */
				$( '.tve_custom_style,.tve_user_custom_style' ).remove();
				TVE_Content_Builder.CSS_Rule_Cache.clear();
				if ( response.custom_css.length ) {
					$( 'head' ).append( response.custom_css );
				}

				/** template-related CSS and fonts */
				if ( ! response.css.thrive_events ) {
					$( '#thrive_events-css,#tve_lightbox_post-css' ).remove();
				}

				/**
				 * javascript page data
				 */
				tqb_page_data = jQuery.extend( tqb_page_data, response.tqb_page_data, true );

				/**
				 State bar is only for the result page
				 */
//				if ( response.state_bar ) {
//					$( '#tqb-form-states' ).html( response.state_bar );
//				}

				$( '#tqb-editor-replace' ).replaceWith( $( response.main_page_content ) );

				if ( tqb_page_data.allow_tqb_advanced ) {
					top.location.reload();
				} else {

					var found = false,
						$css_list = $();
					jQuery.each( response.css, function ( _id, href ) {
						if ( ! $( 'link#' + _id + '-css' ).length ) {
							found = true;
							var $link = $( '<link />', {
								rel: 'stylesheet',
								type: 'text/css',
								id: _id + '-css',
								href: href
							} ).appendTo( 'head' );
							/* for some reason, <link>s from google fonts always have empty cssRules fields - we cannot be sure when those are loaded using the check_loaded function */
							if ( href.indexOf( 'fonts.googleapis' ) === - 1 ) {
								$css_list = $css_list.add( $link );
							}
						}
					} );

					if ( ! found ) {
						on_resources_loaded();
					} else {
						check_loaded( $css_list, on_resources_loaded );
					}
					TVE_Editor_Page.overlay( 'close' );
				}
			}
		},
		social_share_badge: {
			choose_template: function () {
				var $selected = $( '#tqb-social-share-badge-tpl' ).find( '.tve_cell_selected:visible' );

				if ( ! $selected.length ) {
					tve_add_notification( tqb_page_data.L.alert_choose_tpl, true );
					return false;
				}

				TVE_Editor_Page.overlay();
				var template = $selected.find( '.tqb-social-share-badge-file' ).val();

				Util.state_ajax( {
					custom: 'get_social_share_badge_template',
					template: template
				} ).done( function ( response ) {
					$( '.tqb-social-share-badge-container' ).html( response );
					var $element = $( '#tqb-editor-replace .tqb-social-share-badge-container' ).find( '.tve_social_items' );
					$element.attr( 'data-value', template );
					TVE_Content_Builder.controls.lb_close();
					TVE_Editor_Page.overlay( 'close' );
				} );
			},
			change_template: function () {
				tve_path_params.custom_post_data.tqb_social_sharing_badge_template = $( '#tqb-editor-replace .tqb-social-share-badge-container' ).find( '.tve_social_items' ).attr( 'data-value' );
				tve_open_lightbox( $( '#tqb_social_share_badge_template' ) );
			},
			menu_clear_font_size: function ( $btn, $element ) {
				$element.find( '.tve_social_items' ).css( 'font-size', '' );
				var _temp_value = tve_handle_integer_or_float( $element.find( '.tve_social_custom' ).css( 'font-size' ) );
				$btn.parents( '.tve_menu' ).first().find( '.tve_font_size' ).val( _temp_value );
			},
			menu_font_size: function ( $input, $element ) {
				var _temp_value = tve_handle_integer_or_float( $input.val() );
				$element.find( '.tve_social_items' ).css( 'font-size', _temp_value + 'px' );
			}
		},
		custom_buttons: {
			add_result_shortcode: function ( $btn ) {
				if ( $btn.attr( 'data-shortcode' ) ) {
					$( '.edit_mode' ).append( ' ' + $btn.attr( 'data-shortcode' ) );
				}
			},
			lean_modal_trigger: function ( $btn ) {
				var $modal = $( '#tqb-leanmodal-element' );
				$( '#tqb-leanmodal-action-button', $modal ).attr( 'data-ctrl', $btn.attr( 'data-modal-action' ) ).html( $btn.attr( 'data-modal-button-text' ) ).focus();
				if ( $btn.attr( 'data-modal-text' ) ) {
					$( '#tqb-leanmodal-description-text', $modal ).html( $btn.attr( 'data-modal-text' ) );
				}
				if ( $btn.attr( 'data-modal-title' ) ) {
					$( '#tqb-leanmodal-title-text', $modal ).html( $btn.attr( 'data-modal-title' ) );
				}

				//Avoid showing multiple modals on pressing enter
				if ( ! $modal.is( ':visible' ) ) {
					$modal.openModal();
				}
			}
		}
	};

	/**
	 * general plugin (handler) for all design types
	 * @returns {object}
	 */
	$.fn.tqb_variation = function () {
		var $element = this;

		return {
			bgPattern: function ( $btn ) {
				this.clearBgImage().clearBgColor();
				$element.css( {
					'background-image': "url('" + $btn.find( 'input[data-image]' ).attr( 'data-image' ) + "')",
					'background-repeat': 'repeat',
					'background-position': ''
				} );
			},
			clearBgImage: function () {
				$element.css( {
					'background-image': 'none',
					'background-repeat': '',
					'background-size': '',
					'background-position': ''
				} );
				return this;
			},
			clearBgColor: function () {
				if ( $element.tve_color_selector() ) {
					tve_remove_css_rule( $element.tve_color_selector(), 'background-color' );
				}
				$element.css( 'background-color', '' );
				return this;
			},
			bgImage: function ( wpAttachment ) {
				this.clearBgImage();
				$element.css( {
					'background-image': "url('" + wpAttachment.url + "')",
					'background-repeat': '',
					'background-size': 'cover',
					'background-position': 'center center'
				} );
			}
		};
	}

})( jQuery );