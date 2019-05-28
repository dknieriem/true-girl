var ThriveQuizB = ThriveQuizB || {};
ThriveQuizB.models = ThriveQuizB.models || {};
ThriveQuizB.collections = ThriveQuizB.collections || {};


(function ( $ ) {
	$( function () {
		/**
		 * Base Model and Collection
		 */
		ThriveQuizB.models.Base = Backbone.Model.extend( {
			idAttribute: 'ID',
			validation_error: function ( field, message ) {
				return {
					field: field,
					message: message
				};
			},
			custom_action: function ( ajax_params, form_data, custom_action ) {
				if ( ! form_data ) {
					form_data = {};
				}
				var oAjaxParams = _.extend( {
					type: 'post',
					dataType: 'json',
					url: this.url() + '&custom_action=' + custom_action,
					data: form_data
				}, ajax_params );

				return jQuery.ajax( oAjaxParams );
			}
		} );

		/**
		 * Base Collection
		 */
		ThriveQuizB.collections.Base = Backbone.Collection.extend( {
			/**
			 * helper function to get the last item of a collection
			 *
			 * @return Backbone.Model
			 */
			last: function () {
				return this.at( this.size() - 1 );
			}
		} );

		/**
		 * Shortcode Model
		 */
		ThriveQuizB.models.Shortcode = ThriveQuizB.models.Base.extend( {
			idAttribute: 'id',
			defaults: {
				id: null,
				quiz_id: null,
				user_unique: null,
				page_type: '',
				page: {
					page_id: null,
					variation_id: null,
					html: '',
					css: '',
					fonts: ''
				},
				question: null
			},
			url: function () {
				return ThriveQuizB.ajaxurl( '&route=shortcode' );
			},
			logSocialShareConversion: function ( ajax_params, form_data ) {
				this.custom_action( ajax_params, form_data, 'log_social_share_conversion' );
			},
			saveUserCustomSocialShareBadge: function ( ajax_params, form_data ) {
				this.custom_action( ajax_params, form_data, 'save_user_custom_social_share_badge' );
			}
		} );

		/**
		 * Question Model
		 */
		ThriveQuizB.models.Question = ThriveQuizB.models.Base.extend( {
			idAttribute: 'id',
			defaults: {
				id: null,
				question_id: null,
				type: null,
				text: '',
				image: '',
				description: ''
			},

			get_image: function () {

				if ( typeof this.get( 'image' ) === 'string' ) {
					return this.get( 'image' );
				}

				return this.get( 'image' ).sizes.full.url;
			}
		} );

		/**
		 * Answer Model
		 */
		ThriveQuizB.models.Answer = ThriveQuizB.models.Base.extend( {
			idAttribute: 'id',
			defaults: {
				id: null,
				order: null,
				text: '',
				image: ''
			},

			get_image: function () {

				if ( typeof this.get( 'image' ) === 'string' ) {

					return this.get( 'image' );
				}

				return this.get( 'image' ).sizes.full.url;
			}
		} );

		/**
		 * Shortcodes Collection
		 */
		ThriveQuizB.collections.Shortcodes = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.Shortcode
		} );

		/**
		 * Answers Collection
		 */
		ThriveQuizB.collections.Answers = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.Answer
		} );

	} );
})( jQuery );;var ThriveQuizB = ThriveQuizB || {};
ThriveQuizB.views = ThriveQuizB.views || {};

(function ( $ ) {

	$( function () {
		/**
		 * Base View
		 */
		ThriveQuizB.views.Base = Backbone.View.extend( {
			/**
			 * Always try to return this !!!
			 *
			 * @returns {ThriveQuizB.views.Base}
			 */
			render: function () {
				return this;
			}
		} );

		/**
		 * Shortcode List View
		 */
		ThriveQuizB.views.ShortcodeList = ThriveQuizB.views.Base.extend( {
			events: {},
			el: $( 'body' ),
			render: function () {
				this.collection.each( this.renderOne, this );
			},
			renderOne: function ( item ) {
				if ( item.get( 'page' ).html || item.get( 'question' ) ) {
					var view = new ThriveQuizB.views.Shortcode( {
						model: item,
						collection: this.collection,
						el: this.$el.find( '#tqb-shortcode-wrapper-' + item.get( 'quiz_id' ) + '-' + item.get( 'id' ) )
					} );

					view.render();
				}
				if ( item.get( 'error' ) && TQB_Front.is_preview ) {
					this.$el.find( '#tqb-shortcode-wrapper-' + item.get( 'quiz_id' ) + '-' + item.get( 'id' ) ).find( '.tqb-frontend-error-message' ).html( item.get( 'error' ) )
				}

			}
		} );

		/**
		 * Shortcode Single View
		 */
		ThriveQuizB.views.Shortcode = ThriveQuizB.views.Base.extend( {
			events: {
				'click .tqb-shortcode-new-content .tqb-shortcode-submit-action': 'nextPage',
				'click .tqb-shortcode-new-content .tve_evt_manager_listen': 'runEvent'
			},
			to_load: 0,
			powered_by_tqb: ThriveQuizB.tpl( 'powered-by-thrive-themes' ),
			render: function () {
				this.showLoader();
				this.loadScripts();
				this.initListeners( this.model );
				this.hideLoader();
				return this;
			},
			loadScripts: function () {
				var self = this,
					scripts = [];
				this.$el.find( '.tqb-shortcode-new-content' ).html( '' );
				if ( this.model.get( 'page' ) ) {
					scripts = scripts.concat( this.model.get( 'page' ).css );
					scripts = scripts.concat( this.model.get( 'page' ).fonts );
				}
				if ( this.model.get( 'question' ) ) {
					scripts = scripts.concat( this.model.get( 'question' ).css );
				}
				this.to_load = scripts.length;
				_.each( scripts, function ( href, index, list ) {
					var $link = $( '<link rel="stylesheet" type="text/css" media="all" href="' + href + '">' );
					this.$el.find( '.tqb-shortcode-new-content' ).append( $link );
					$link.load( function () {
						self.to_load --;
					} );
				}, this );
				this.check_loaded();
			},
			loadHTML: function () {
				var self = this;
				if ( ! _.isEmpty( this.model.get( 'page' ) ) ) {

					this.template = _.template( this.model.get( 'page' ).html );
					this.$el.find( '.tqb-shortcode-new-content' ).append( this.template( this.model.toJSON() ) );
					this.$el.find( '.tqb-shortcode-new-content' ).append( '<style type="text/css">' + this.model.get( 'page' ).user_css.replace( /\\/g, '' ) + '</style>' );
					this.setUserIdentifier();
					this.listener();
					if ( TQB_Front.settings.tqb_promotion_badge ) {
						this.$el.find( '.tqb-shortcode-new-content .tve-tqb-page-type' ).append( this.powered_by_tqb() );
					}

					if ( this.model.get( 'page_type' ) === 'results' ) {
						this.resultPageListener();
					} else {
						this.model.trigger( 'tqb:close_loader', this.model );
					}
				} else {
					this.template = ThriveQuizB.tpl( 'question-wrapper' );
					this.$el.find( '.tqb-shortcode-new-content' ).append( this.template() );
					this.renderQuestion();
					if ( TQB_Front.settings.tqb_promotion_badge ) {
						this.$el.find( '.tqb-shortcode-new-content .tqb-question-wrapper' ).append( this.powered_by_tqb() );
					}
					this.model.trigger( 'tqb:close_loader', this.model );
				}
				setTimeout( function () {
					self.setOverlayHeight();
				}, 500 );

				TCB_Front.event_triggers( ThriveGlobal.$j( this.$el ) );
				TCB_Front.onDOMReady();
				ThriveGlobal.$j( TCB_Front ).trigger( 'content_loaded.thrive', [this.$el.find( '.tqb-shortcode-new-content' )] );
			},
			check_loaded: function () {
				var self = this;
				var timeout_id;
				if ( this.to_load === 0 ) {
					this.loadHTML();
					clearTimeout( timeout_id );
					return;
				}
				timeout_id = setTimeout( function () {
					self.check_loaded();
				}, 50 );
			},
			runEvent: function ( event ) {
				event.preventDefault();
				var event_string = $( event.currentTarget ).attr( 'data-tcb-events' ),
					is_next_event = false,
					events = ThriveGlobal.$j.parseJSON( event_string.replace( '__TCB_EVENT_', '' ).replace( '_TNEVE_BCT__', '' ) );
				for ( var i = 0, len = events.length; i < len; i ++ ) {
					if ( events[i].a === 'thrive_quiz_next_step' ) {
						is_next_event = true;
					}
				}
				if ( is_next_event ) {
					this.shortcodeAction();
				}
			},
			setUserIdentifier: function () {
				this.$el.find( '.tqb-hidden-form-info.tqb-hidden-user-unique' ).val( this.model.get( 'user_unique' ) );
			},
			setOverlayHeight: function () {

				var height = this.$el.find( '.tqb-shortcode-new-content' ).outerHeight();
				console.log(height);
				this.$el.find( '.tqb-loading-overlay' ).attr( 'style', 'height:' + height + 'px;' );

			},
			listener: function () {
				var self = this;
				ThriveGlobal.$j( this.$el ).off( 'lead_conversion_success.tcb' ).on( 'lead_conversion_success.tcb', '.thrv_lead_generation', function ( event ) {
					event.content_unlocked = self.model.get( 'page_type' ) !== 'results';
					self.nextPage();
				} );
				ThriveGlobal.$j( this.$el ).off( 'should_submit_form.tcb' ).on( 'should_submit_form.tcb', '.thrv_lead_generation', function ( event ) {
					event.flag_need_data = true;
					return true;
				} );
			},
			switchContent: function () {
				var old_content = this.$el.find( '.tqb-shortcode-old-content' ),
					new_content = this.$el.find( '.tqb-shortcode-new-content' );
				old_content.html( new_content.html() );
			},
			nextPage: function () {
				this.shortcodeAction();
			},
			shortcodeAction: function ( answerData ) {
				if ( this.model.get( 'page_type' ) == 'results' ) {
					return;
				}
				this.hideErrorToast();
				this.model.trigger( 'tqb:show_loader', this.model );
				if ( answerData ) {
					var data = this.getQuestionData( answerData );
				} else {
					var data = this.getPageData();
				}
				if ( data ) {
					var self = this;

					this.model.fetch( {
						data: data,
						success: function ( model, response, options ) {
							self.render();
						},
						error: function ( collection, response, options ) {
						}
					} );
				}
			},
			hideErrorToast: function () {
				$( '#tve-lg-error-container' ).hide();
			},
			getQuestionData: function ( answerData ) {
				return {
					user_unique: this.model.get( 'user_unique' ),
					quiz_id: this.model.get( 'quiz_id' ),
					page_type: this.model.get( 'page_type' ),
					answer_id: answerData.get( 'id' )
				}
			},
			getPageData: function () {
				return {
					user_unique: this.model.get( 'user_unique' ),
					quiz_id: this.model.get( 'quiz_id' ),
					page_type: this.model.get( 'page_type' ),
					variation: {
						page_id: this.model.get( 'page' ).page_id,
						id: this.model.get( 'page' ).variation_id,
					}
				}
			},
			renderQuestion: function () {
				var answers = new ThriveQuizB.collections.Answers( this.model.get( 'question' ).answers ),
					self = this;
				answers.on( 'tqb:answer_question', function ( model ) {
					self.shortcodeAction( model );
				} );
				var view = new ThriveQuizB.views.Question( {
					model: new ThriveQuizB.models.Question( this.model.get( 'question' ).data ),
					collection: answers,
					el: this.$el.find( '.tqb-shortcode-new-content .tqb-question-wrapper' )
				} );
				return view.render();
			},
			resultPageListener: function () {
				var self = this;

				if ( self.model.get( 'page' ).has_social_badge && self.model.get( 'page' ).has_social_badge == 1 && self.model.get( 'page' ).html_canvas ) {
					var $social_share_badge_container_height = 300,
						$social_share_badge_container_width = self.$el.find( '.tve_editor_main_content' )[0].offsetWidth - 80,
						$canvas = $( '<div id="tie-html-canvas" style="overflow: visible; position: absolute; z-index: -1; visibility: hidden;">' + self.model.get( 'page' ).html_canvas + '</div>' ),
						$preloader_gif = $( '<div id="tie-preloader-gif" style="background-image: url(\'' + self.model.get( 'page' ).social_loader_url + '\'); background-repeat: no-repeat; background-position: center center;background-color: #e8e8e8; width: ' + $social_share_badge_container_width + 'px; max-width: 100%; height: ' + $social_share_badge_container_height + 'px ;"></div>' );

					// Add class to make all parents of the canvas overflow visible
					self.$el.find( '.tqb-shortcode-new-content' ).parents().each( function ( ind, elem ) {
						$( elem ).addClass( 'tqb-overflow-visible-badge' );
					} );

					self.$el.find( '.tqb-shortcode-new-content' ).prepend( $canvas );

					/*Hide the social share badge elements and append the preloader*/
					self.$el.find( '.tqb-social-share-badge-container  img' ).removeAttr( 'src' );
					self.$el.find( '.tqb-social-share-badge-container' ).children().hide();
					self.$el.find( '.tqb-social-share-badge-container' ).append( $preloader_gif );

					html2canvas( [$canvas.get( 0 )], {
						onrendered: function ( canvas ) {
							var image_data = canvas.toDataURL( 'image/png' ),
								img_data = image_data.replace( /^data:image\/(png|jpg);base64,/, '' );

							// Remove class that make all parents of the canvas overflow visible
							self.$el.find( '.tqb-shortcode-new-content' ).parents().each( function ( ind, elem ) {
								$( elem ).removeClass( 'tqb-overflow-visible-badge' );
							} );

							self.model.saveUserCustomSocialShareBadge( {
								success: function ( response ) {
									self.$el.find( '.tqb-social-share-badge-container  img' ).attr( 'src', response );
									self.$el.find( '.tqb-social-share-badge-container  .tve_s_fb_share' ).attr( 'data-image', response );

									/*Click on social share button inside social share element*/
									ThriveGlobal.$j( tve_frontend_options.is_editor_page ? '#tve_editor' : 'body' ).on( 'click', '.tqb-social-share-badge-container .tve_s_link', function () {
										self.model.logSocialShareConversion( {}, {
											'page_id': self.model.get( 'page' ).page_id,
											'quiz_id': self.model.get( 'page' ).quiz_id,
											'variation_id': self.model.get( 'page' ).variation_id,
											'tqb-variation-user_unique': self.model.get( 'user_unique' )
										} );

										var $element = ThriveGlobal.$j( this ).parents( '.tve_s_item' ),
											_type = $element.attr( 'data-s' );

										TCB_Front.onSocialCustomClick[_type] && TCB_Front.onSocialCustomClick[_type]( $element );
									} );

									$canvas.remove();
									/*Remove the preloader and show the social share badge elements on image loaded*/
									self.$el.find( '.tqb-social-share-badge-container  img' ).load( function () {
										$preloader_gif.remove();
										self.$el.find( '.tqb-social-share-badge-container' ).children().show();
									} );
								},
								complete: function () {
									self.model.trigger( 'tqb:close_loader', self.model );
								}
							}, {
								'img_data': img_data,
								'quiz_id': self.model.get( 'quiz_id' ),
								'user_id': self.model.get( 'user_id' ),
								'result': self.model.get( 'page' ).result
							} );
						}
					} );
				} else {
					this.model.trigger( 'tqb:close_loader', this.model );
				}

				ThriveGlobal.$j( tve_frontend_options.is_editor_page ? '#tve_editor' : 'body' ).on( 'click', '.thrv_social_custom .tve_s_link', function () {
					self.model.logSocialShareConversion( {}, {
						'page_id': self.model.get( 'page' ).page_id,
						'quiz_id': self.model.get( 'page' ).quiz_id,
						'variation_id': self.model.get( 'page' ).variation_id,
						'tqb-variation-user_unique': self.model.get( 'user_unique' )
					} );
				} );
			},
			showLoader: function () {
				this.$el.find( '.tqb-loading-overlay' ).show();
				this.$el.find( '.tqb-shortcode-new-content' ).html( '' );
			},
			hideLoader: function () {
				this.$el.find( '.tqb-loading-overlay' ).hide();
			},
			initListeners: function ( item ) {
				var self = this;
				item.on( 'tqb:show_loader', function ( model ) {
					self.showLoader();
				} );
				item.on( 'tqb:close_loader', function ( model ) {
					self.hideLoader();
				} );
			}
		} );

		/**
		 * Question View
		 */
		ThriveQuizB.views.Question = ThriveQuizB.views.Base.extend( {
			template: ThriveQuizB.tpl( 'question' ),
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				this.renderAnswers();
				return true;
			},
			renderAnswers: function () {

				var is_image = this.model.get( 'q_type' ) == 2;

				if ( is_image ) {
					this.$el.find( '.tqb-answers-container' ).addClass( 'tqb-answer-has-image' );
				}
				var view = new ThriveQuizB.views.Answers( {
					model: this.model,
					collection: this.collection,
					el: this.$el.find( '.tqb-answers-container' )
				} );

				return view.render();
			}
		} );

		/**
		 * Answers View
		 */
		ThriveQuizB.views.Answers = ThriveQuizB.views.Base.extend( {
			events: {},
			render: function () {
				this.collection.each( this.renderOne, this );
				return this;
			},
			renderOne: function ( item ) {
				var self = this;
				item.on( 'tqb:choose_answer', function () {
					self.collection.trigger( 'tqb:answer_question', item );
				} );
				var view = new ThriveQuizB.views.Answer( {
					model: item
				} );
				this.$el.append( view.render( this.model.get( 'q_type' ) == 2 ).$el );
			}
		} );

		/**
		 * Answer View
		 */
		ThriveQuizB.views.Answer = ThriveQuizB.views.Base.extend( {
			className: 'tqb-answer-inner-wrapper',
			template: ThriveQuizB.tpl( 'answer' ),
			events: {
				'click .tqb-answer-action': 'answerAction'
			},
			render: function ( is_image ) {

				this.template = is_image ? ThriveQuizB.tpl( 'answer-image' ) : this.template;

				this.$el.html( this.template( {item: this.model} ) );
				return this;
			},
			answerAction: function () {
				this.$el.find( '.tqb-answer-action' ).addClass( 'tqe-selected-answer' );
				this.model.trigger( 'tqb:choose_answer', this.model );

			}
		} );

	} );
})( jQuery );;(function ( $ ) {

	/**
	 * Settings for the underscore templates
	 * Enables <##> tags instead of <%%>
	 *
	 * @type {{evaluate: RegExp, interpolate: RegExp, escape: RegExp}}
	 */
	_.templateSettings = {
		evaluate: /<#([\s\S]+?)#>/g,
		interpolate: /<#=([\s\S]+?)#>/g,
		escape: /<#-([\s\S]+?)#>/g
	};

	/**
	 * Building ajax urls
	 */
	ThriveQuizB.ajaxurl = function ( query_string ) {
		var _q = TQB_Front.ajax_url.indexOf( '?' ) !== - 1 ? '&' : '?';
		if ( ! query_string || ! query_string.length ) {
			return TQB_Front.ajax_url + _q + '_nonce=' + ThriveQuizB.admin_nonce;
		}
		query_string = query_string.replace( /^(\?|&)/, '' );
		query_string += '&_nonce=' + TQB_Front.nonce;
		query_string += '&tqb-post-id=' + TQB_Front.post_id;

		return TQB_Front.ajax_url + _q + query_string;
	};

	ThriveQuizB.tpl = function ( tpl_path, opt ) {
		var _html = $( 'script#' + tpl_path.replace( /\//g, '-' ) ).html() || '';
		_html = _html.replace( /(\n|\t|\r)/g, '' );
		if ( opt ) {
			return _.template( _html )( opt );
		}
		return _.template( _html );
	};

	$.fn.tqb = function () {
		var shortcodes = new ThriveQuizB.collections.Shortcodes(),
			shortcode_ids = {};

		$( '.tqb-shortcode-wrapper' ).each( function ( index ) {
			var shortcode_id = $( this ).attr( 'data-quiz-id' );
			var unique_id = $( this ).attr( 'data-unique' );
			shortcode_ids[unique_id] = shortcode_id;
		} );
		if ( Object.keys( shortcode_ids ).length === 0 ) {
			return false;
		}

		// Include the facebook SDK
		if ( ! window.FB && window.tve_frontend_options.social_fb_app_id ) {
			$.getScript( '//connect.facebook.com/en_US/sdk.js', function () {
				FB.init( {
					appId: window.tve_frontend_options.social_fb_app_id,
					xfbml: false,
					version: 'v2.3'
				} );
			} );
		}

		if ( window.TVE_Dash && ! TVE_Dash.ajax_sent ) {
			$( document ).on( 'tve-dash.load', function ( event ) {
				TVE_Dash.add_load_item( 'tqb_lazy_load', {
					quiz_ids: shortcode_ids
				}, function ( response ) {
					var shortcodes = new ThriveQuizB.collections.Shortcodes();
					Object.keys( response ).forEach( (function ( element ) {
						response[element].id = element;
						var model = new ThriveQuizB.models.Shortcode( response[element] );
						shortcodes.add( [model] );
					}) );

					ThriveQuizB.shortcode_list = new ThriveQuizB.views.ShortcodeList( {collection: shortcodes} );
					ThriveQuizB.shortcode_list.render();

				} );
			} );
			return;
		}
	};

	$( function () {
		var TQB_Shortcodes = $( this ).tqb();
	} );
})( jQuery );

