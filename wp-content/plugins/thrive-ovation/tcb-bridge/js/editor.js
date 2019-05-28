var TVE_Content_Builder = TVE_Content_Builder || {};

(function ( $ ) {

	if ( typeof TVE_Content_Builder.add_filter !== 'undefined' ) {

		TVE_Content_Builder.add_filter( 'menu_show', function ( $element ) {

			var config, $menu;
			/* Display testimonials menu */
			if ( $element.hasClass( 'thrv_tvo_display_testimonials' ) ) {

				load_control_panel_menu( $element, 'tvo_display_testimonials' );

				$menu = $( '#tvo_display_testimonials_menu' );

				TVE_Content_Builder.tvo.display.init( $element, $menu );

				return true;
			}
			/* Capture testimonials menu */
			else if ( $element.hasClass( 'thrv_tvo_capture_testimonials' ) ) {

				load_control_panel_menu( $element, 'tvo_capture_testimonials' );

				$menu = $( '.menu_elem_tvo_capture_testimonials' );

				TVE_Content_Builder.tvo.capture.init( $element, $menu );

				return true;
			}
		} );

		/* clear any route that was left before */
		$( document ).on( 'click', '#tvo_display_testimonial_settings, #tvo_display_testimonial_template', function () {
			document.location.hash = '';
		} )
	}

	$( document ).on( 'mousedown', '.image_placeholder.thrv_tvo_display_testimonials', function () {
		delete TVO_Front.active_element;
		delete TVO_Front.active_config;
	} ).on( 'click', '#tvo-menu-item', function () {
		var $panel = jQuery( '.tve_cpanel_options' );
		$panel.scrollTop( $panel.scrollTop() + 60 );
	} );

	$.fn.extend( {
		tvo_display_testimonial: function ( init ) {
			if ( ! init ) {
				init = false;
			}
			var $element = $( this );
			return {
				$element: $element,
				default_config: {
					template: 0,
					tags: [],
					testimonials: [],
					name: '',
					show_title: 1,
					show_role: 1,
					show_site: 0,
					type: 'display',
					max_testimonials: 0
				},
				getConfig: function () {
					if ( init ) {
						return this.default_config;
					}

					return TVE_Editor_Page.thriveShrtcodeConfig( $element.find( '.thrive-shortcode-config' ), 'tvo_shortcode' ).get();
				},
				saveConfig: function ( config ) {
					if ( init ) {
						return;
					}
					TVE_Editor_Page.thriveShrtcodeConfig( $element.find( '.thrive-shortcode-config' ), 'tvo_shortcode' ).save( config );

				},
				updateConfig: function ( _config ) {
					var config = this.getConfig();
					config = $.extend( {}, config, _config );
					this.saveConfig( config );

					return config;
				},
				updateField: function ( key, value ) {
					var config = this.getConfig();
					config[key] = value;
					this.saveConfig( config );

					return config;
				},
				renderHTML: function ( html, config ) {
					if ( init ) {
						$element.removeClass( 'image_placeholder' );
						init = false;
					}
					/* add config wrapper */
					html += '<div class="thrive-shortcode-config" style="display: none;"></div>';

					$element.html( html );

					this.saveConfig( config );
				}
			}
		},
		tvo_capture_form: function () {
		}
	} );


	TVE_Content_Builder.tvo = {
		capture: {

			default_config: {
				id: 0,
				template: 'default',
				type: 'capture',
				name_label: 'Full Name',
				title_label: 'Testimonial Title',
				email_label: 'Email',
				role_label: 'Role',
				website_url_label: 'Website URL',
				name_required: 1,
				title_required: 0,
				email_required: 0,
				role_required: 0,
				role_display: 0,
				title_display: 0,
				image_display: 1,
				reCaptcha_option: 0,
				on_success_option: 'message',
				on_success: 'Thanks for submitting your testimonial.',
				button_text: 'Submit',
				questions: ['What was your experience with our product like?'],
				placeholders: [''],
				questions_required: [1],
				tags: '',
				color_class: ''
			},

			init: function ( $element, $menu ) {

				TVO_Front.active_capture = $element;

				config = TVE_Editor_Page.thriveShrtcodeConfig( $element.find( '.thrive-shortcode-config' ), 'tvo_shortcode' ).get();

				$menu.find( '#tvo_capture_testimonial_settings' ).data( 'config', config );

				$menu.find( '#tvo_capture_testimonial_template' ).data( 'template', config.template );

				if ( ! $element.data( 'colorpicker-bound' ) && TVE_Editor_Page ) {
					$menu.find( '.tve_default_colors li' ).click( function () {
						var color_class = $( this ).attr( 'class' ),
							$form = $element.find( '.tvo_testimonial_form' );

						$form.attr( 'class', $form.attr( 'class' ).replace( /tve_(\w+)/i, color_class ) );
						TVE_Editor_Page.thriveShrtcodeConfig( $element.find( '.thrive-shortcode-config' ), 'tvo_shortcode' ).update( color_class, 'color_class' );
					} );

					$element.data( 'colorpicker-bound', true );
				}
			},
			open_lb: function ( $btn, $element, event ) {
				TVE_Editor_Page.overlay();
				TVE_Content_Builder.controls.lb_open( $btn, $element, event )
			},
			save_capture_testimonial: function ( $btn, $element, event ) {
				var _template = $( '.tvo_capture_templates .tve_cell_selected input.lp_code' );

				if ( $element.length === 0 ) {
					$element = $( '.tve_active_lightbox' );
				}
				if ( _template.length ) {
					var _template_name = _template.val(),
						$config;

					if ( $element.hasClass( 'image_placeholder' ) ) {
						$element.removeClass( 'image_placeholder' );
						/* we add the template for the first time - has default config */
						$config = this.default_config;
					} else {
						/* get old config */
						$config = TVE_Editor_Page.thriveShrtcodeConfig( $element.find( '.thrive-shortcode-config' ), 'tvo_shortcode' ).get();
					}

					/* update with the new template */
					$config['template'] = _template_name;
					this.update_capture_form( $element, $config );

					$element.attr( 'data-template', _template_name );

					$( '#tvo_capture_testimonial_template' ).data( 'template', _template_name );
				}
			},
			save_form_settings: function () {
				var $config = TVE_Editor_Page.thriveShrtcodeConfig( TVO_Front.active_capture.find( '.thrive-shortcode-config' ), 'tvo_shortcode' ),
					data = $config.get();

				$( '#tvo_capture_form_settings' ).find( '.tvo_config_field' ).each( function () {
					var $this = $( this );
					if ( $this.attr( 'type' ) == 'checkbox' ) {
						data[$this.attr( 'name' )] = $this.is( ':checked' ) ? 1 : 0;
					} else {
						data[$this.attr( 'name' )] = $this.val();
					}
				} );

				data.questions = [];
				data.placeholders = [];
				$( '.tvo-question' ).each( function ( index ) {
					var $this = $( this );
					data.questions[index] = $this.find( '.tvo-question-input' ).val();
					data.placeholders[index] = $this.find( '.tvo-placeholder-input' ).val();
					data.questions_required[index] = $this.find( '.tvo-required' ).is( ':checked' ) ? 1 : 0;
				} );

				$config.save( data );
				$( '#tvo_capture_testimonial_settings' ).data( 'config', data );

				this.update_capture_form( TVO_Front.active_capture, data );
			},
			update_capture_form: function ( $element, config ) {

				var data = {
					action: 'tve_ajax_load',
					ajax_load: 'tvo_load_template'
				};

				data.config = config;

				jQuery.post( TVO_Front.ajaxurl, data, function ( _html ) {

					/* add config wrapper */
					_html += '<div class="thrive-shortcode-config" style="display: none;"></div>';

					$element.html( _html );

					/* save the config in the wrapper */
					if ( typeof TVE_Editor_Page !== 'undefined' ) {
						TVE_Editor_Page.thriveShrtcodeConfig( $element.find( '.thrive-shortcode-config' ), 'tvo_shortcode' ).save( config );
					}

					TVE_Content_Builder.controls.lb_close();
				} );
			}
		},
		display: {

			open_lb: function ( $btn, $element, event ) {
				TVE_Editor_Page.overlay();

				if ( $btn.attr( 'id' ) === 'tvo_display_testimonial_template' ) {
					var config = TVO_Front.active_element.getConfig();
					$btn.data( 'template', config.template );
				}

				TVE_Content_Builder.controls.lb_open( $btn, $element, event )
			},

			init: function ( $element, $menu ) {

				TVO_Front.active_element = $element.tvo_display_testimonial();

				TVO_Front.active_config = TVO_Front.active_element.getConfig();
				TVO_Front.init_template = '';

				$menu.find( '.tvo_show_role' ).prop( 'checked', TVO_Front.active_config['show_role'] != '0' );
				$menu.find( '.tvo_show_site' ).prop( 'checked', TVO_Front.active_config['show_site'] != '0' );
				$menu.find( '.tvo_show_title' ).prop( 'checked', TVO_Front.active_config['show_title'] != '0' );

				if ( ! $element.data( 'colorpicker-bound' ) && TVE_Editor_Page ) {
					$element.on( 'editor.oncolorpickerchange', function ( event, selector, color_config ) {
						var cfg = TVO_Front.active_element.getConfig();

						if ( ! cfg.custom_css ) {
							cfg.custom_css = {};
						}

						cfg.custom_css[color_config.selector] = selector;

						TVO_Front.active_element.saveConfig( cfg );
					} );

					$menu.find( '.tve_default_colors li' ).mousedown( function () {
						var color_class = $( this ).attr( 'class' ),
							$testimonial = TVO_Front.active_element.$element.find( '.tvo-testimonials-display' ),
							cfg = TVO_Front.active_element.getConfig();

						for ( var selector in cfg.custom_css ) {
							$testimonial.parent().find( selector ).removeAttr( 'data-tve-custom-colour' );
						}

						cfg.custom_css = {};
						cfg.color_class = color_class;

						$testimonial.attr( 'class', $testimonial.attr( 'class' ).replace( /tve_(\w+)/i, color_class ) );
						TVO_Front.active_element.saveConfig( cfg );
					} );

					$element.data( 'colorpicker-bound', true );
				}
			},

			fetch_shortcode: function ( config ) {

				TVE_Editor_Page.overlay();

				if ( ! TVO_Front.active_element ) {
					TVO_Front.active_element = $( '.thrv_tvo_display_testimonials.tve_active_lightbox' ).tvo_display_testimonial( true );
				}

				if ( ! config ) {
					config = TVO_Front.active_element.getConfig();
				} else {
					config = TVO_Front.active_element.updateConfig( config );
				}

				if ( (! config.template || config.template.length < 1  ) && TVO_Front.init_template && TVO_Front.init_template.length > 1 ) {
					config.template = TVO_Front.init_template;
				}

				var data = {
					action: 'tve_ajax_load',
					ajax_load: 'tvo_load_template',
					config: config
				};

				$.post( TVO_Front.ajaxurl, data, function ( html ) {

					TVO_Front.active_element.renderHTML( html, config );

					TVE_Editor_Page.overlay( true );

					TVE_Content_Builder.controls.lb_close();
				} );
			},

			save_shortcode: function ( config ) {
				var data = {
						name: config['name'],
						type: 'display',
						config: config,
						content: ''
					},
					self = this;

				if ( (! config.template || config.template.length < 1  ) && TVO_Front.init_template && TVO_Front.init_template.length > 1 ) {
					data.config.template = TVO_Front.init_template;
				}

				$.ajax( {
					headers: {
						'X-WP-Nonce': TVO_Front.nonce
					},
					cache: false,
					url: TVO_Front.routes.shortcodes,
					type: 'POST',
					data: data
				} ).done( function () {
					self.fetch_shortcode( config );
				} );

			},

			save_template: function () {
				var template = $( '.tvo_display_templates .tve_cell_selected input.lp_code' );

				if ( template.length ) {
					TVO_Front.active_element.updateField( 'template', template.val() );

					this.fetch_shortcode();
				}
			},

			toggle_field: function ( $btn, $element ) {
				var config = TVO_Front.active_element.getConfig(),
					$input = $( $btn );

				config['show_' + $input.val()] = $input.is( ':checked' ) ? 1 : 0;

				TVO_Front.active_element.saveConfig( config );

				this.fetch_shortcode();
			}
		}
	};
})( jQuery );