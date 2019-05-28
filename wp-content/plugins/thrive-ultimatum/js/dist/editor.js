var TVE_Content_Builder = TVE_Content_Builder || {};
TVE_Content_Builder.ext = TVE_Content_Builder.ext || {};

var TU_Editor = TU_Editor || {};

(function ( $ ) {
	/**
	 * DOMReady
	 */
	$( function () {
		/**
		 * the minimized state bar is draggable
		 */
		$( '.tl-state-minimized' ).draggable( {
			handle: '.multistep-lightbox-heading',
			stop: function ( event, ui ) {
				if ( "JSON" in window ) {
					Util.set_cookie( 'tve_ult_states_pos', JSON.stringify( ui.position ) );
				}
			}
		} );

		TVE_Content_Builder.add_filter( 'menu_show', TU_Editor.on_menu_show );

		if ( ! tve_ult_page_data.has_content ) {
			/* we can't add filter because apply filter is called earlier than this point :| */
			TU_Editor.open_template_chooser();
			TVE_Content_Builder.add_filter( 'main_ajax_callback', TU_Editor.open_template_chooser );
		}

		/**
		 * Media selector callback - used to insert a background image on the design
		 */
		TVE_Content_Builder.add_filter( 'tcb_media_selected', TU_Editor.media_selected );

		/**
		 * populate the control panel inputs with values read from DOM
		 */
		TVE_Content_Builder.add_filter( 'load_button_values', TU_Editor.load_button_values );

		$( TVE_Content_Builder ).on( 'menu_show_countdown_timer', TU_Editor.hide_countdown_timer_controls );

		/**
		 * pre-process the inserted saved content template
		 */
		TVE_Content_Builder.add_filter( 'tcb_insert_content_template', TU_Editor.pre_process_content_template );
	} );

	/**
	 * pre-process the HTML node to be inserted
	 *
	 * @param {object} $html jQuery wrapper over the HTML to be inserted
	 */
	TU_Editor.pre_process_content_template = function ( $html ) {
		var tu_classes = [
			'thrv_ult_bar',
			'thrv_ult_widget'
		];

		$.each( tu_classes, function ( i, cls ) {
			if ( $html.hasClass( cls ) ) {
				$html = $html.find( '.tve_editor_main_content' ).children();
				$html.find( '.tve-ult-bar-close' ).remove();
				return false;
			}
		} );

		return $html;
	};

	/**
	 * called after the main AJAX request callback - it will open the template chooser by default,
	 * allowing the user to choose a template the first time a form variation is edited
	 */
	TU_Editor.open_template_chooser = function () {
		TVE_Editor_Page.overlay();
		TCB_Main.trigger( 'trigger_action', '#tvu-tpl-chooser', 'click' );

		//hide the close button on the template chooser so that the user cannot cancel the process
		setTimeout( function () {
			$( '#tve_lightbox_frame' ).find( '.tve-lightbox-close' ).hide();
			hide_sub_menu();
		}, 0 );
	};

	/**
	 * callback applied when the user selects an image from the WP media frame API and the type has not been identified by TCB
	 *
	 * @param {object} $element
	 * @param {string} type
	 * @param {object} attachment
	 */
	TU_Editor.media_selected = function ( $element, type, attachment ) {
		switch ( type ) {
			case 'tu_design_bg':
				$element.tve_ult_design().bgImage( attachment );
				break;
			default:
				break;
		}
	};

	/**
	 * display the custom menu for designs
	 *
	 * @param {object} element
	 *
	 * @return {Boolean} whether or not the element menu is identified
	 */
	TU_Editor.on_menu_show = function ( element ) {
		if ( element.hasClass( 'thrv_ult_bar' ) || element.hasClass( 'thrv_ult_widget' ) ) {
			load_control_panel_menu( element, 'ult_design' );
			return true;
		}

		return false;
	};

	/**
	 * populate inputs with values saved in the DOM for the element
	 *
	 * @param {string} type
	 * @param {object} $element element being edited
	 * @param {object} $menu current opened menu
	 */
	TU_Editor.load_button_values = function ( type, $element, $menu ) {
		switch ( type ) {
			case 'ult_design':

				break;
			default:
				break;
		}
		/**
		 * some default controls
		 */
		var $border_control = $menu.find( '.tu-border-width' );
		if ( $border_control.length ) {
			var bw = parseInt( $element.css( 'borderBottomWidth' ) );
			bw = isNaN( bw ) ? 0 : bw;
			$border_control.val( bw );
		}
	};

	TU_Editor.hide_countdown_timer_controls = function ( event, $element, $menu ) {
		$menu.find( '.tve_date_control' ).remove();
		$menu.find( '> ul > li.tve_clear' ).remove();
	};

	var Util = {
		tpl_ajax: function ( data, ajax_param, no_loader ) {
			var params = {
				type: 'post',
				dataType: 'json',
				url: tve_ult_page_data.ajaxurl
			};
			if ( typeof no_loader === 'undefined' || ! no_loader ) {
				TVE_Editor_Page.overlay();
			}
			data.action = tve_ult_page_data.tpl_action;
			data.design_id = data.design_id || tve_ult_page_data.design_id;
			data.post_id = tve_ult_page_data.post_id;
			data.security = tve_ult_page_data.security;
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
				url: tve_ult_page_data.ajaxurl
			};
			TVE_Editor_Page.overlay();
			data.action = tve_ult_page_data.state_action;
			data.design_id = data.design_id || tve_ult_page_data.design_id;
			data.post_id = tve_ult_page_data.post_id;
			data.security = tve_ult_page_data.security;

			params.data = data;

			if ( ajax_param ) {
				for ( var k in ajax_param ) {
					params[k] = ajax_param[k];
				}
			}

			return jQuery.ajax( params, data );
		},
		/**
		 *
		 * @param {string} cookie_name
		 * @param {string|Number} value
		 * @param {int} [expires]
		 * @param {string} [path]
		 */
		set_cookie: function ( cookie_name, value, expires, path ) {
			path = path || '/';
			expires = expires || (
					30 * 24 * 3600
				);

			var _now = new Date(), sExpires;
			expires = parseInt( expires );
			_now.setTime( _now.getTime() + expires * 1000 );
			sExpires = _now.toUTCString();

			document.cookie = encodeURIComponent( cookie_name ) + '=' + encodeURIComponent( value ) + ';expires=' + sExpires + ';path=' + path;
		}
	};

	TVE_Content_Builder.ext.tve_ult = {
		template: {
			choose: function ( $btn ) {
				var $selected = $( '#tve-ult-tpl' ).find( '.tve_cell_selected:visible' );
				if ( ! $selected.length ) {
					tve_add_notification( tve_ult_page_data.L.alert_choose_tpl, true );
					return false;
				}
				/**
				 * if the user is choosing a multi-step, warn him that he will lose all other states
				 */
				if ( tve_ult_page_data.has_content && $selected.find( '.multi_step' ).val() === '1' && ! confirm( tve_ult_page_data.L.confirm_multi_step ) ) {
					return false;
				}
				Util.tpl_ajax( {
					custom: 'choose',
					tpl: $selected.find( '.lp_code' ).val()
				} ).done( TVE_Content_Builder.ext.tve_ult.state.insertResponse );

				TVE_Content_Builder.controls.lb_close();
				$( '#tve_lightbox_frame' ).find( '.tve-lightbox-close' ).show();
			},
			reset: function ( $btn ) {
				if ( ! confirm( tve_ult_page_data.L.confirm_tpl_reset ) ) {
					return false;
				}
				Util.tpl_ajax( {
					custom: 'reset'
				} ).done( TVE_Content_Builder.ext.tve_ult.state.insertResponse );
				TVE_Content_Builder.controls.lb_close();
			},
			/**
			 * get all the user saved templates
			 */
			get_saved: function () {
				$( '#tl-saved-templates' ).html( '<p class="tu-tpl-loading">' + tve_ult_page_data.L.fetching_saved_templates + '</p>' );
				var current_template = $( '#tl-user-current-templates:checked' ).length;
				Util.tpl_ajax( {
					custom: 'get_saved',
					current_template: current_template
				}, {
					dataType: 'html'
				}, true ).done( function ( response ) {
					TVE_Editor_Page.overlay( 'close' );
					$( '#tl-saved-templates' ).html( response );
				} );
				return false;
			},
			/**
			 * Save the current template as a user-defined one
			 */
			save: function ( $btn ) {
				var _name = $btn.parent().find( 'input#tve_landing_page_name' ).val();
				if ( ! _name ) {
					tve_add_notification( tve_ult_page_data.L.tpl_name_required, true, 5000 );
					return;
				}
				Util.tpl_ajax( {
					custom: 'save',
					name: $btn.parent().find( 'input#tve_landing_page_name' ).val()
				} ).done( function ( response ) {
					$( '#tve_landing_page_msg' ).removeClass( 'tve_warning' ).addClass( 'tve_success' ).html( response.message );
					$( '#tl-saved-templates' ).html( response.list );
					TVE_Editor_Page.overlay( 'close' );
				} );
			},
			user_tab_clicked: function () {
				if ( $( '#tl-saved-templates' ).find( '.tu-tpl-loading' ).length ) {
					setTimeout( this.get_saved, 150 );
				}
			},
			/**
			 * delete a saved template
			 *
			 * @returns {boolean}
			 */
			delete_saved: function () {
				var $selected = jQuery( '#tl-saved-templates' ).find( '.tve_cell_selected:visible' );

				if ( ! $selected.length ) {
					alert( tve_ult_page_data.L.alert_choose_tpl );
					return false;
				}

				if ( ! confirm( tve_ult_page_data.L.tpl_confirm_delete ) ) {
					return false;
				}

				Util.tpl_ajax( {
					custom: 'delete',
					tpl: $selected.find( '.lp_code' ).val()
				}, {
					dataType: 'html'
				} ).done( function ( response ) {
					jQuery( '#tl-saved-templates' ).html( response );
					TVE_Editor_Page.overlay( 'close' );
					tve_add_notification( tve_ult_page_data.L.template_deleted, false, 2000 );
				} );
			}
		},
		state: {
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
					window.tvu_loaded_count = window.tvu_loaded_count || 1;
					window.tvu_loaded_count ++;
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
					if ( all_loaded || window.tvu_loaded_count > 40 ) {
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
				 * custom page buttons need changing (reset contents, choose template, x settings)
				 */
				// $( '#tve-ult-page-tpl-options' ).html( response.page_buttons );
				/**
				 * if the template has changed, remove the old css (the new one will be added automatically)
				 */
				if ( tve_ult_page_data.current_css != response.tve_ult_page_data.current_css ) {
					$( '#' + tve_ult_page_data.current_css + '-css' ).remove();
				}
				/**
				 * javascript params that need updating
				 */
				tve_path_params = jQuery.extend( tve_path_params, response.tve_path_params, true );

				/**
				 * javascript page data
				 */
				tve_ult_page_data = jQuery.extend( tve_ult_page_data, response.tve_ult_page_data, true );

				$( '#tu-form-states' ).html( response.state_bar );
				$( '#tve-ult-editor-replace' ).replaceWith( $( response.main_page_content ) );

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
			},
			toggle_manager: function ( $link ) {
				var clsFn = 'addClass',
					cookie_exp = 30 * 24 * 3600,
					$target = $( '.multistep-lightbox' );

				if ( $link.attr( 'data-expand' ) ) {
					clsFn = 'removeClass';
					cookie_exp = - cookie_exp;
				}

				$( 'body' )[clsFn]( 'tl-state-collapse' );
				Util.set_cookie( 'tve_ult_state_collapse', 1, cookie_exp, '/' );

				$target.css( 'display', '' );
			},
			/**
			 * open a lightbox that allows editing / changing the state name
			 */
			add_edit: function ( $btn, $element, event ) {
				this.current_state_id = $btn.attr( 'data-id' ) || 0;
				var result = TVE_Content_Builder.controls.lb_open( $btn, $element, event );
				$( '#tve_lightbox_frame #lb_ult_state_title' ).val( this.current_state_id ? $btn.parents( 'li' ).first().find( '.lightbox-step-name' ).text() : '' );
				return result;
			},
			save: function ( $btn ) {

				var _name = $.trim( $( '#lb_ult_state_title' ).val() );
				if ( ! _name ) {
					tve_add_notification( tve_ult_page_data.L.state_name_required, true );
					return false;
				}
				var self = this;

				function add() {
					Util.state_ajax( {
						custom: 'add',
						post_title: _name
					} ).done( jQuery.proxy( self.insertResponse, self ) );
				}

				if ( ! this.current_state_id ) { // save the current post
					return tve_save_post( 'true', add ); // passed in callback function to skip the closing of overlay
				}
				//just save the new name
				Util.state_ajax( {
					custom: 'edit_name',
					post_title: _name,
					id: this.current_state_id
				} ).done( function ( response ) {
					response.state_bar && $( '#tu-form-states' ).html( response.state_bar );
					TVE_Content_Builder.controls.lb_close();
					setTimeout( function () {
						TVE_Editor_Page.overlay( 'close' );
					}, 1 );
				} );
			},
			duplicate: function ( $link, $element, event ) {
				var self = this;
				tve_save_post( 'true', function () {
					Util.state_ajax( {
						custom: 'duplicate',
						id: $link.attr( 'data-id' )
					} ).done( self.insertResponse );
				} );

				event.stopPropagation();
			},
			state_click: function ( $link ) {
				var self = this;
				tve_save_post( 'true', function () {
					self.display( $link.attr( 'data-id' ), true );
				} ); // passed in callback function to skip the closing of overlay
			},
			display: function ( id ) {

				/**
				 * get the state via ajax
				 */
				Util.state_ajax( {
					custom: 'display',
					id: id
				} ).done( jQuery.proxy( this.insertResponse, this ) );
			},
			remove: function ( $btn ) {

				if ( ! confirm( tve_ult_page_data.L.confirm_state_delete ) ) {
					return false;
				}

				var self = this;
				Util.state_ajax( {
					custom: 'delete',
					id: $btn.attr( 'data-id' )
				} ).done( jQuery.proxy( self.insertResponse, self ) );

				event.stopPropagation();
			}
		},
		/**
		 * show the WP media API selector for design backgrounds
		 */
		open_bg_media: function ( $btn, $element ) {
			thrive_open_media( null, 'load', 'tu_design_bg', $element );
		},
		open_cpanel: function ( $btn, $element, e ) {
			hide_sub_menu();
			var $wrapper = $( $btn.data( 'element-selector' ) );
			e.target = $wrapper;
			e.currentTarget = $wrapper;
			tve_editor_init( e, $wrapper );
		},
		/**
		 * set border width for the design
		 * @param $input
		 * @param element
		 */
		border_width: function ( $input, element ) {
			element.css( 'border-width', $input.val() );
		}

	};

	/**
	 * general plugin (handler) for all design types
	 * @returns {object}
	 */
	$.fn.tve_ult_design = function () {
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
					'background-image': '',
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
