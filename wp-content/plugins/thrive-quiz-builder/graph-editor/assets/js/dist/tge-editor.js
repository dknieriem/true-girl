/**
 * Created by dan bilauca on 11/16/2016.
 */

var TGE_Editor = TGE_Editor || {};
TGE_Editor.models = TGE_Editor.models || {};
TGE_Editor.collections = TGE_Editor.collections || {};

(function ( $ ) {

	Backbone.emulateHTTP = true;

	/**
	 * Base Model
	 */
	TGE_Editor.models.Base = Backbone.Model.extend( {

		//idAttribute: 'ID',

		defaults: {
			//id: ''
		},

		toDeepJSON: function () {

			var obj = $.extend( true, {}, this.attributes );
			_.each( _.keys( obj ), function ( key ) {
				if ( ! _.isUndefined( obj[key] ) && ! _.isNull( obj[key] ) && _.isFunction( obj[key].toJSON ) ) {
					obj[key] = obj[key].toJSON();
				}
			} );

			return obj;
		},

		validation_error: function ( field, message, callback ) {
			return {
				field: field,
				message: message,
				callback: callback
			};
		},

		url: function ( params ) {
			params = params || {};

			params = _.extend( {}, params, {
				action: TGE_Editor.ajax_controller_action,
				_nonce: TGE_Editor.nonce
			} );

			return TGE_Editor.ajaxurl + '?' + $.param( params );
		}
	} );

	/**
	 * Question Model
	 */
	TGE_Editor.models.Question = TGE_Editor.models.Base.extend( {

		defaults: _.extend( {}, TGE_Editor.models.Base.prototype.defaults, {
			quiz_id: TGE_Editor.quiz.ID,
			text: '',
			position: {
				x: 100,
				y: 100
			}
		} ),

		initialize: function ( data ) {

			var answers = [];

			if ( data && data.image ) {
				this.set( 'image', new TGE_Editor.models.Image( data.image ) );
			}

			if ( data && data.answers ) {
				answers = new TGE_Editor.collections.Answers( data.answers, {
					q_type: this.get_type_key()
				} );
				this.set( 'answers', answers );
			} else {
				answers = new TGE_Editor.collections.Answers( [] );
				this.set( 'answers', answers );
			}
		},

		/**
		 * based on q_type which is number
		 * return a string type of question
		 *
		 * @returns {string}
		 */
		get_type_key: function () {
			//hardcoded this because we dont want to search the key in collection(TGE_Editor.globals.question_types)
			return this.get( 'q_type' ) == 1 ? 'button' : 'image';
		},

		parse: function ( server_json ) {

			server_json.answers = new TGE_Editor.collections.Answers( server_json.answers || [] );

			if ( server_json.image ) {
				server_json.image = new TGE_Editor.models.Image( server_json.image );
			}

			return server_json;
		},

		validate: function () {
			var errors = [];

			if ( ! this.get( 'text' ) || this.get( 'text' ).length === 0 ) {
				errors.push( this.validation_error( 'text', TGE_Editor.t.question_text_required ) );
				return errors;
			}

			var valid_answers = true;

			this.get( 'answers' ).each( function ( answer, index, list ) {

				if ( valid_answers ) {
					var model = this.factory_answer( answer.toJSON() );
					if ( ! model.isValid() ) {
						valid_answers = false;
						answer.trigger( 'invalid', answer, model.validationError );
					}
				}
			}, this );

			if ( ! valid_answers ) {
				return [];
			}

			if ( ! errors.length && this.get( 'answers' ) instanceof Backbone.Collection && this.get( 'answers' ).length < 1 ) {
				errors = TGE_Editor.t.insufficient_answers;
			}

			if ( errors.length ) {
				return errors;
			}
		},

		factory_answer: function ( data ) {

			var type = TGE_Editor.globals.question_types.findWhere( {id: parseInt( this.get( 'q_type' ) )} );
			if ( ! type ) {
				return new TGE_Editor.models.Answer( data );
			}

			var model_name = 'Answer';
			model_name += TVE_Dash.upperFirst( type.get( 'key' ) );

			return new TGE_Editor.models[model_name]( data );
		},

		url: function () {
			var params = {
				route: 'question'
			};

			if ( this.get( 'id' ) ) {
				params.id = this.get( 'id' );
			}

			return TGE_Editor.models.Base.prototype.url.call( this, params );
		}
	} );

	/**
	 * Questions Collection
	 */
	TGE_Editor.collections.Questions = Backbone.Collection.extend( {
		model: TGE_Editor.models.Question
	} );

	/**
	 * Answer Model
	 */
	TGE_Editor.models.Answer = TGE_Editor.models.Base.extend( {

		defaults: _.extend( {}, TGE_Editor.models.Base.prototype.defaults, {
			text: '',
			points: 1
		} ),

		initialize: function ( data ) {

			if ( data && data.image ) {
				this.set( 'image', new TGE_Editor.models.Image( data.image ) );
			}
		},

		validate: function () {

			var errors = [];

			if ( ! this.get( 'points' ) || this.get( 'points' ).length <= 0 ) {
				errors.push( this.validation_error( 'points', TGE_Editor.quiz.quiz_type !== 'personality' ? TGE_Editor.t.answer_points_required : TGE_Editor.t.answer_weight_required ) );
			}

			if ( this.get( 'points' ) && isNaN( this.get( 'points' ) ) ) {
				errors.push( this.validation_error( 'points', TGE_Editor.t.points_input_number ) );
			}

			if ( errors.length ) {
				return errors;
			}

			if ( TGE_Editor.quiz.quiz_type === 'personality' && (this.get( 'result_id' ) == - 1 || _.isUndefined( this.get( 'result_id' ) )) ) {
				errors.push( this.validation_error( 'result_id', TGE_Editor.t.select_result ) );
			}

			if ( errors.length ) {
				return errors;
			}
		}
	} );

	/**
	 * Answer Model
	 */
	TGE_Editor.models.AnswerImage = TGE_Editor.models.Answer.extend( {

		defaults: _.extend( {}, TGE_Editor.models.Answer.prototype.defaults, {
			image: ''
		} ),

		initialize: function () {

			TGE_Editor.models.Answer.prototype.initialize.apply( this, arguments );
		},

		validate: function () {

			var errors = TGE_Editor.models.Answer.prototype.validate.apply( this, arguments ) || [];

			if ( ! this.get( 'image' ) || this.get( 'image' ).length === 0 ) {
				errors.push( this.validation_error( 'image', TGE_Editor.t.answer_image_required, function ( $element ) {
					var $label = $element.siblings( 'label' );
					$label.text( $label.attr( 'data-error' ) );
				} ) );
			}

			if ( errors.length ) {
				return errors;
			}
		}
	} );

	TGE_Editor.models.Image = TGE_Editor.models.Base.extend( {

		defaults: _.extend( {}, TGE_Editor.models.Base.prototype.defaults, {
			sizes: {
				thumbnail: {
					url: null
				}
			}
		} ),

		initialize: function ( data ) {

			/**
			 * backwards compatibility
			 */
			if ( typeof data === 'string' ) {

				this.set( _.extend( {}, this.defaults, {
					sizes: {
						full: {
							url: data
						},
						thumbnail: {
							url: data
						}
					},
					url: data
				} ) )
			}
		},

		get_thumb: function () {

			if ( ! this.attributes.sizes.thumbnail ) {
				return this.get( 'url' ) || null;
			}

			return this.attributes.sizes.thumbnail.url || this.get( 'url' ) || null;
		}
	} );

	/**
	 * Answer Model
	 */
	TGE_Editor.models.AnswerButton = TGE_Editor.models.Answer.extend( {

		defaults: _.extend( {}, TGE_Editor.models.Answer.prototype.defaults, {} ),

		validate: function () {

			var errors = TGE_Editor.models.Answer.prototype.validate.apply( this, arguments ) || [];

			if ( this.get( 'text' ).length <= 0 ) {
				errors.push( this.validation_error( 'text', TGE_Editor.t.answer_text_required ) );
			}

			if ( errors.length ) {
				return errors;
			}
		}

	} );

	/**
	 * Answers Collection
	 */
	TGE_Editor.collections.Answers = Backbone.Collection.extend( {

		model: function ( attributes, options ) {

			var model_name = 'Answer',
				q_type = options.q_type ? options.q_type : '';

			model_name += TVE_Dash.upperFirst( q_type );

			return new TGE_Editor.models[model_name]( attributes, options );

		},

		comparator: function ( a, b ) {

			a = a.get( 'order' );
			b = b.get( 'order' );

			return a > b ? 1 : a < b ? - 1 : 0;
		}
	} );

	/**
	 * Connection Model
	 */
	TGE_Editor.models.Connection = TGE_Editor.models.Base.extend( {

		defaults: _.extend( {}, TGE_Editor.models.Base.prototype.defaults, {
			id: ''
		} ),

		url: function () {
			var params = {
				route: 'connection'
			};

			return TGE_Editor.models.Base.prototype.url.call( this, params );
		}

	} );

	TGE_Editor.models.Disconnecion = TGE_Editor.models.Base.extend( {

		defaults: _.extend( {}, TGE_Editor.models.Base.prototype.defaults, {
			id: ''
		} ),

		url: function () {
			var params = {
				route: 'disconnection'
			};

			return TGE_Editor.models.Base.prototype.url.call( this, params );
		}

	} );

	TGE_Editor.models.Settings = TGE_Editor.models.Base.extend( {

		defaults: _.extend( {}, TGE_Editor.models.Base.prototype.defaults, {
			id: '',
			display_weight: TGE_Editor.quiz.display_weight,
			quiz_id: TGE_Editor.quiz.ID
		} ),

		url: function () {

			var params = {
				route: 'settings'
			};

			return TGE_Editor.models.Base.prototype.url.call( this, params );
		}
	} );

})( jQuery );
;/**
 * Created by dan bilauca on 11/16/2016.
 */
var TGE_Editor = TGE_Editor || {};
TGE_Editor.modals = TGE_Editor.modals || {};

(function ( $ ) {
	$( function () {


		TGE_Editor.modals.ModalSteps = TVE_Dash.views.Modal.extend( {
			stepClass: '.tge-modal-step',
			currentStep: 0,
			$step: null,
			events: {
				'click .tge-modal-next-step': "next",
				'click .tge-modal-prev-step': "prev"
			},
			afterRender: function () {
				this.steps = this.$el.find( this.stepClass ).hide();
				this.gotoStep( 0 );
				return this;
			},
			gotoStep: function ( index ) {
				var step = this.steps.hide().eq( index ).show(),
					self = this;
				this.$step = step;
				setTimeout( function () {
					self.input_focus( step );
				}, 50 );

				if ( this.currentStep !== index ) {
					this.currentStep = index;
					this.trigger( 'modal:step_changed', parseInt( index ) );
				}

				return this;
			},
			next: function () {
				this.beforeNext();
				this.gotoStep( this.currentStep + 1 );
				this.afterNext();
			},
			prev: function () {
				this.beforePrev();
				this.gotoStep( this.currentStep - 1 );
				this.afterPrev();
			},
			beforeNext: function () {
				return this;
			},
			afterNext: function () {
				return this;
			},
			beforePrev: function () {
				return this;
			},
			afterPrev: function () {
				return this;
			}
		} );

		/**
		 * Add Question Modal View
		 */
		TGE_Editor.modals.AddQuestion = TGE_Editor.modals.ModalSteps.extend( {

			className: 'tvd-modal-fixed-footer tvd-modal',

			template: TVE_Dash.tpl( 'modals/add-question' ),

			events: _.extend( {}, TGE_Editor.modals.ModalSteps.prototype.events, {
				'click .tge-q-type': 'onTypeSelected',
				'click .tvd-modal-submit': 'saveQuestion'
			} ),

			initialize: function ( args ) {
				TGE_Editor.modals.ModalSteps.prototype.initialize.apply( this, arguments );

				this.on( 'modal:step_changed', function ( step ) {
					switch ( step ) {
						case 1:
							this.renderQuestionForm( this._getFormWrapper() );
							break;
						default:
							this.$el.css( {
								'max-width': this.data['max-width']
							} );
							this.form_view.remove();
							break;
					}
				}, this );
			},

			afterRender: function () {

				this.renderTypes( TGE_Editor.globals.question_types );

				TGE_Editor.modals.ModalSteps.prototype.afterRender.apply( this, arguments );

				return this;
			},

			input_focus: function ( $root ) {

				$root = $root || this.$el;

				var $inputs = $root.find( 'input:not(.tvd-select-dropdown),textarea,.tve-confirm-delete-action' );
				$inputs.filter( ':visible' ).filter( ':not(.tvd-no-focus)' ).first().focus().select();
			},

			/**
			 *
			 * @param collection Backbone.Collection
			 */
			renderTypes: function ( collection ) {

				var template = TVE_Dash.tpl( 'question/type-card' );

				if ( ! (collection instanceof Backbone.Collection) ) {
					return false;
				}

				collection.each( function ( item, index, list ) {
					this._getListWrapper().append( template( {
						item: item,
						selected: item.get( 'id' ) == this.model.get( 'q_type' )
					} ) );
				}, this );

			},

			/**
			 * cache for types list wrapper
			 *
			 * @returns {*}
			 * @private
			 */
			_getListWrapper: function () {

				if ( ! this.$types_list_wrapper ) {
					this.$types_list_wrapper = this.$( '#tge-question-types' );
				}

				return this.$types_list_wrapper;
			},

			_getFormWrapper: function () {

				if ( ! this.$form_wrapper ) {
					this.$form_wrapper = this.$( '#tge-question-form' );
				}

				return this.$form_wrapper;
			},

			renderQuestionForm: function ( $el ) {

				this.$el.css( {
					'max-width': '80%'
				} );

				var reset_data = _.extend( {}, this.model.defaults, {
					q_type: this.model.get( 'q_type' ),
					position: this.model.get( 'position' ),
					answers: new TGE_Editor.collections.Answers(),
					id: ''
				} );

				/**
				 * clear the model of any previous answers or type
				 * case to cover the flow when user gets back and re-set the question type
				 */
				this.model.clear( {silent: true} );
				this.model.set( reset_data );

				this.form_view = new TGE_Editor.views.FormQuestion( {
					model: this.model
				} );

				this.form_view.render();

				$el.html( this.form_view.$el );
			},

			onTypeSelected: function ( event ) {

				this.model.set( 'q_type', event.currentTarget.dataset.id );
				TVE_Dash.select_card( $( event.currentTarget ), this._getListWrapper().find( '.tge-q-type' ) );
			},

			next: function () {

				if ( ! this.beforeNext() ) {
					return;
				}

				this.gotoStep( this.currentStep + 1 );
				this.afterNext();
			},

			beforeNext: function () {

				if ( ! this.model.get( 'q_type' ) ) {
					TVE_Dash.err( TGE_Editor.t.select_question_type );
					return false;
				}

				return true;
			},

			saveQuestion: function () {

				if ( ! this.model.isValid() ) {
					return;
				}

				var self = this;

				/**
				 * this is the 1st question and it needs
				 * to be positioned under the startFlow cell
				 */
				if ( window.tge_app_view.questions.length === 0 ) {
					this.model.set( 'start', '1' );
					this.model.set( 'position', {
						x: tge_app_view.startFlow.position().x + tge_app_view.startFlow.get( 'size' ).width / 2,
						y: tge_app_view.startFlow.position().y + 100
					} );
				}

				window.tge_app_view.saving();
				TVE_Dash.showLoader();

				this.model.save( null, {
					success: function ( model, response, option ) {
						window.tge_app_view.questions.add( self.model );
						// Send event to parent window
						if ( ! window.opener ) {
							return;
						}
						window.opener.postMessage( {
							event: 'tqb_question_counter_update',
							number: window.tge_app_view.questions.length
						}, '*' );
					},
					error: function ( model, response ) {
						TVE_Dash.err( response.responseJSON.data.message );
					},
					complete: function () {
						TVE_Dash.hideLoader();
						window.tge_app_view.change_automatically_saved();
						self.close();
					}
				} );
			}

		} );

		/**
		 * Delete Question Modal View
		 */
		TGE_Editor.modals.DeleteQuestion = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/delete-question' ),
			events: {
				'click .tve-confirm-delete-action': 'delete'
			},
			afterRender: function () {

				this.$el.addClass( 'tvd-red' );
			},
			delete: function () {

				var question = window.tge_app_view.questions.findWhere( {id: this.model.id} );

				if ( ! ( question instanceof TGE_Editor.models.Question) ) {
					return;
				}

				var self = this;
				TVE_Dash.showLoader();
				question.destroy( {
					success: function ( model, response ) {
						TVE_Dash.success( TGE_Editor.t.question_success_deleted );
						self.model.remove();
						// Send event to parent window
						if ( ! window.opener ) {
							return;
						}
						window.opener.postMessage( {
							event: 'tqb_question_counter_update',
							number: window.tge_app_view.questions.length
						}, '*' );
					},
					error: function ( model, response ) {
						TVE_Dash.err( TGE_Editor.t.question_error_deleted );
					},
					complete: function () {
						TVE_Dash.hideLoader();
						self.close();
					}
				} );
			}
		} );

		/**
		 * Edit Question Modal View
		 */
		TGE_Editor.modals.EditQuestion = TGE_Editor.modals.AddQuestion.extend( {

			renderQuestionForm: function ( $el ) {

				this.$el.css( {
					'max-width': '80%'
				} );

				this.form_view = new TGE_Editor.views.FormQuestionEdit( {
					model: this.model
				} );

				this.form_view.render();

				$el.html( this.form_view.$el );
			},

			afterRender: function () {
				TGE_Editor.modals.AddQuestion.prototype.afterRender.apply( this, arguments );
				this.next();
				this.$( '.tge-modal-prev-step' ).html( TGE_Editor.t.change_question_type );
				this.$( '.tvd-modal-title' ).html( TGE_Editor.t.edit_question );
			},

			saveQuestion: function () {

				if ( ! this.model.isValid() ) {
					return;
				}

				var self = this;

				TVE_Dash.showLoader();
				window.tge_app_view.saving();

				this.model.save( null, {
					success: function ( model, response, option ) {
						var jQuestion = window.tge_app_view.graph.getCell( model.get( 'id' ) );
						jQuestion.set( model.toDeepJSON() );
					},
					error: function ( model, response ) {
						TVE_Dash.err( response.responseJSON.data.message );
					},
					complete: function () {
						TVE_Dash.hideLoader();
						window.tge_app_view.change_automatically_saved();
						self.close();
					}
				} );
			}

		} );

	} );
})( jQuery );
;/**
 * Created by dan bilauca on 11/3/2016.
 */

var TGE_Editor = TGE_Editor || {};
TGE_Editor.views = TGE_Editor.views || {};
TGE_Editor.globals = TGE_Editor.globals || {};
TGE_Editor.const = TGE_Editor.const || {};

TGE_Editor.const = {
	answers_per_row: 5,
	answer: {
		margin: {
			left: 20,
			bottom: 10
		},
		image: {
			size: {
				width: 100,
				height: 100
			}
		},
		size: {
			width: 150,
			height: 30
		}
	},
	question: {
		image: {
			size: {
				width: 100
			}
		},
		size: {
			height: 30
		}
	},
	navigator: {
		size: {
			width: 250,
			height: 250
		}
	},
	paper: {
		size: {
			width: 5000,
			height: 5000
		}
	}
};

TGE_Editor.globals.question_types = new Backbone.Collection( TGE_Editor.question_types );
TGE_Editor.globals.weight_switcher = new TGE_Editor.models.Settings();

TGE_Editor.globals.weight_switcher.on( 'change:display_weight', function () {
	this.save();
} );

(function ( $ ) {

	$( function () {

		/**
		 * App View
		 */
		TGE_Editor.views.AppView = Backbone.View.extend( {

			el: '#tge-app',
			selectedCell: null,
			notMovableModels: ['tge.Answer', 'tge.AnswerImage', 'tge.NewQuestion', 'tge.StartFlow'],
			sidebarList: null,
			costs: [],
			$saving_status: null,

			attrLinks: {
				".connection": {
					stroke: '#87548d',
					'stroke-width': 2
				}
			},

			events: {
				'scroll': 'on_scroll'
			},

			initialize: function () {

				this.$scrollers = $( '.tge-scroll' );
				this.$collapseBtn = $( '#tge-slide-cp' );
				this.$collapseBtn.click( _.bind( this.toggle_control_panel, this ) );
				this.$saving_status = $( '#tge-saving-status' );
				this.sidebarList = $( '#tge-items-list' );
				this.questions = new TGE_Editor.collections.Questions( TGE_Editor.questions );
				this.answers = new Backbone.Collection();
				this.$add_new_question = $( '#tge-add-new-question' ).click( this.add_new_question );

				this.listenTo( this.questions, 'add', function ( model ) {
					this.loadQuestion( model );
				}, this );

				this.listenTo( this.questions, 'remove', function () {
					this.render_sidebar_list();
				}, this );

				this.listenTo( this.questions, 'change:text', function () {
					this.updateAnswersCollection();
					this.render_sidebar_list();
				} );

				this.render_sidebar_list();

				this.initializePaper();
				this.initializeNavigatorPaper();
				this.loadQuestions();
				this.initializeStart();
				this.updateAnswersCollection();

				this.int_navigator();
				this.$scrollers.hide();

			},

			initializeNewQuestionButton: function () {

				if ( _.isUndefined( this.newQuestionButton ) ) {
					this.newQuestionButton = new joint.shapes.tge.NewQuestion( {
						id: 'new-question'
					} );
					this.graph.addCell( this.newQuestionButton );
				}

				var position = {
					x: TGE_Editor.const.paper.size.width / 2 - (this.$el.width() / 2) + 50,
					y: 50
				};

				//position the rect on middle of paper
				this.newQuestionButton.position( position.x, position.y );
			},

			initializeStart: function () {

				if ( _.isUndefined( this.startFlow ) ) {
					this.startFlow = new joint.shapes.tge.StartFlow( {
						id: 'start'
					} );
					this.graph.addCell( this.startFlow );
				}

				var viewable_width = TGE_Editor.const.paper.size.width;
				var position = {
					x: viewable_width / 2 - this.startFlow.get( 'size' ).width / 2,
					y: 50
				};

				this.startFlow.position( position.x, position.y );

				var start_question = this.graph.get( 'cells' ).findWhere( {start: '1'} );

				if ( start_question ) {
					this.createStartLink( start_question );
				}
			},

			createStartLink: function ( start_question ) {
				this.create_link( {
					id: 'start',
					port: 'start-port',
					selector: 'g:nth-child(1) > g:nth-child(5) > circle:nth-child(1)'
				}, {
					id: start_question.id,
					port: 'q-in-port-' + start_question.id,
					selector: 'g:nth-child(1) > g:nth-child(3) > circle:nth-child(1)'
				} );
			},

			initializeNavigatorPaper: function () {

				var paperSmall = new joint.dia.Paper( {
					preventContextMenu: false,
					el: $( '#tge-nav-paper' ),
					width: TGE_Editor.const.navigator.size.width,
					height: TGE_Editor.const.navigator.size.height,
					model: this.graph
				} );

				this.scale = TGE_Editor.const.navigator.size.width / this.paper.options.width;

				paperSmall.scale( this.scale );

				var self = this,
					scroll_step = 10,
					current_x = this.$el.scrollTop(),
					current_y = this.$el.scrollLeft();

				var scale_width = ( TGE_Editor.const.paper.size.width / TGE_Editor.const.navigator.size.width ),
					scale_height = ( TGE_Editor.const.paper.size.height / TGE_Editor.const.navigator.size.height ),
					port_view_size = {
						width: this.$el.width(),
						height: this.$el.height()
					};

				/**
				 * move the port view to center of paper
				 * cos there is the add new question cell
				 */
				setTimeout( function () {
					self.$el.animate( {
						scrollLeft: TGE_Editor.const.paper.size.width / 2 - port_view_size.width / 2
					}, 500, 'swing' );
				}, 500 );


				function scroll( direction ) {

					var current_x = self.$el.scrollTop(),
						current_y = self.$el.scrollLeft();

					switch ( direction ) {
						case 'bottom':
							if ( current_x >= (TGE_Editor.const.paper.size.height - port_view_size.height) ) {
								return;
							}
							current_x += scroll_step;
							self.$el.scrollTop( current_x );
							self.$nav_handler.css( {
								top: current_x / scale_height
							} );
							break;
						case 'top':
							if ( current_x <= - (scroll_step) ) {
								return;
							}
							current_x -= scroll_step;
							self.$el.scrollTop( current_x );
							self.$nav_handler.css( {
								top: current_x / scale_height
							} );
							break;
						case 'right':
							if ( current_y >= (TGE_Editor.const.paper.size.width - port_view_size.width) ) {
								return;
							}
							current_y += scroll_step;
							self.$el.scrollLeft( current_y );
							self.$nav_handler.css( {
								left: current_y / scale_width
							} );
							break;
						case 'left':
							if ( current_y <= - (scroll_step) ) {
								return;
							}
							current_y -= scroll_step;
							self.$el.scrollLeft( current_y );
							self.$nav_handler.css( {
								left: current_y / scale_width
							} );
							break;
					}
				}

				var interval;

				this.$scrollers.on( 'mouseover', function ( event ) {
					var dir = event.target.dataset.dir;
					interval = setInterval( function () {
						scroll( dir );
					}, 10 );
				} ).on( 'mouseout', function () {
					clearInterval( interval )
				} );
			},

			initializePaper: function () {

				var self = this;

				/**
				 * Instantiate Graph
				 */
				this.graph = new joint.dia.Graph;

				/**
				 * Instantiate paper
				 */
				this.paper = new joint.dia.Paper( {
					preventContextMenu: false,
					restrictTranslate: true,
					el: this.$( '#tge-paper' ),
					width: TGE_Editor.const.paper.size.width,
					height: TGE_Editor.const.paper.size.height,
					model: this.graph,
					gridSize: 1,
					snapLinks: {radius: 50},
					linkPinning: false,
					markAvailable: true,
					clickThreshold: 1,

					defaultLink: new tgeLink( {
						router: {
							name: 'manhattan'
						},
						connector: {
							name: 'rounded'
						},
						attrs: this.attrLinks
					} ),

					interactive: function ( cellView ) {

						if ( cellView.model instanceof joint.dia.Link ) {
							// Disable the default vertex add functionality on pointerdown.
							return {vertexAdd: false};
						}
						return true;
					},

					validateConnection: function ( cellViewS, magnetS, cellViewT, magnetT, end, linkView ) {

						//answer to its question
						var parent_q_id = cellViewS.model.get( 'question_id' );
						if ( parent_q_id ) {
							var target_q_id = magnetT.getAttribute( 'qid' );
							if ( target_q_id === parent_q_id + 'q' ) {
								return false;
							}
						}

						//only one link from port
						var sourcePort = magnetS.getAttribute( 'port' );
						var sourceLinks = self.graph.getConnectedLinks( cellViewS.model, {outbound: true} );
						var sourcePortLinks = _.filter( sourceLinks, function ( link ) {
							return link.get( 'source' ).port == sourcePort;
						} );
						if ( sourcePortLinks.length > 1 ) {
							return false;
						}

						//from in we do not start a link
						if ( magnetS && magnetS.getAttribute( 'port-group' ) === 'in' ) {
							return false;
						}

						//we do not link links into out ports
						if ( magnetT && magnetT.getAttribute( 'port-group' ) === 'out' ) {
							return false;
						}

						//we do not set links between same questions
						if ( magnetS && magnetT && magnetS.getAttribute( 'qid' ) === magnetT.getAttribute( 'qid' ) ) {
							return false;
						}

						//we do not support links between the same cell(recursively)
						return cellViewS !== cellViewT;
					}
				} );

				/**
				 * Paper binds
				 */
				this.paper.on( 'cell:pointerdown', this.onCellPointerdown, this );
				this.paper.on( 'cell:pointerup', this.onCellPointerup, this );
				this.paper.on( 'link:connect', this.onLinkConnect, this );
				this.paper.on( 'cell:mouseover', this.onCellMouseover, this );
				this.paper.on( 'cell:mouseout', this.onCellMouseout, this );

				/**
				 * Graph binds
				 */
				this.graph.on( 'remove', this.onGraphRemove, this );
				this.graph.on( 'add', this.onGraphAdd, this );
				this.graph.on( 'change:position', this.onGraphChangePosition, this );
			},

			loadQuestions: function () {

				this.graph.fromJSON( {cells: TGE_Editor.questions}, {silent: false} );

				/**
				 * After loading all the cells render Answer to Question Links
				 */
				var text_answers = this.graph.get( 'cells' ).where( {
						type: 'tge.Answer'
					} ),
					image_answers = this.graph.get( 'cells' ).where( {
						type: 'tge.AnswerImage'
					} );

				_.each( _.union( text_answers, image_answers ), function ( item, index, list ) {
					if ( ! item.get( 'next_question_id' ) ) {
						/**
						 * if answer does not have next question id
						 * we do not have to render any link
						 */
						return;
					}

					var source,
						target;

					source = {
						id: item.get( 'id' ),
						"selector": "g:nth-child(1) > g:nth-child(3) > circle:nth-child(1)",
						"port": "a-port-" + item.get( 'id' )
					};
					target = {
						id: item.get( 'next_question_id' ) + 'q',
						selector: 'g:nth-child(1) > g:nth-child(3) > circle:nth-child(1)',
						port: 'q-in-port-' + item.get( 'next_question_id' ) + 'q'
					};

					this.create_link( source, target );
				}, this );

				/**
				 * Render questions links
				 */
				var questions = this.graph.get( 'cells' ).where( {
					type: 'tge.Question'
				} );

				_.each( questions, function ( item, index, list ) {
					if ( ! item.get( 'next_question_id' ) ) {
						return;
					}

					var source,
						target;

					source = {
						id: item.get( 'id' ),
						selector: 'g:nth-child(1) > g:nth-child(4) > circle:nth-child(1)',
						port: 'q-out-port-' + item.get( 'id' )
					};
					target = {
						id: item.get( 'next_question_id' ),
						selector: 'g:nth-child(1) > g:nth-child(3) > circle:nth-child(1)',
						port: 'q-in-port-' + item.get( 'next_question_id' )
					};

					this.create_link( source, target );

				}, this );
			},

			loadQuestion: function ( bQuestion ) {

				var jQuestion,
					model = bQuestion.toDeepJSON(),
					cell = this.graph.getCell( model.id ),
					col = - 1,
					row = 0;

				if ( cell ) {
					cell.set( model );
					jQuestion = cell;
				} else {
					jQuestion = new joint.shapes.tge.Question( _.extend( model, {} ) );
					this.graph.addCell( jQuestion );
				}

				_.each( model.answers, function ( answer, index, list ) {
					if ( index && index % TGE_Editor.const.answers_per_row === 0 ) {
						col = - 1;
						row ++;
					}
					col ++;
					this.loadAnswer( answer, jQuestion, model, col, row );
				}, this );

				if ( jQuestion.get( 'start' ) == 1 ) {

					this.createStartLink( jQuestion );

					var position = jQuestion.position();
					var new_position = {
						x: position.x - jQuestion.get( 'size' ).width / 2,
						y: position.y
					};

					this.selectedCell = this.paper.findViewByModel( jQuestion );

					jQuestion.position( new_position.x, new_position.y );
					jQuestion.loadAnswers();

					this.paper.trigger( 'cell:pointerup', this.selectedCell );

					this.render_sidebar_list();
				}
			},

			loadAnswer: function ( answer, jQuestion, question, col, row ) {

				var jAnswer,
					cell = this.graph.getCell( answer.id );

				if ( cell ) {
					cell.set( answer );
				} else {
					jAnswer = new joint.shapes.tge.Answer( _.extend( answer, {
						col: col,
						row: row,
						jQuestion: jQuestion.toJSON()
					} ) );
					jQuestion.embed( jAnswer );
					this.graph.addCell( jAnswer );
				}
			},

			create_link: function ( source, target ) {

				var link = {
					type: "link",
					source: {
						id: "qqqq1",
						selector: "g:nth-child(1) > g:nth-child(4) > circle:nth-child(1)",
						port: "q-out-port-q1"
					},
					target: {
						id: "q2",
						selector: 'g:nth-child(1) > g:nth-child(3) > circle:nth-child(1)',
						port: 'q-in-port-q2'
					},
					router: {
						name: "manhattan"
					},
					connector: {
						"name": "rounded"
					},
					embeds: "",
					z: 16,
					attrs: this.attrLinks
				};

				if ( source ) {
					link.source = source;
				}

				if ( target ) {
					link.target = target;
				}

				if ( this.graph.getCell( link.source.id ) && this.graph.getCell( link.target.id ) ) {
					var l_model = new tgeLink( link );
					this.graph.addCell( l_model );
				}
			},

			disconnect_link: function ( source, target ) {

				if ( ! source.id || ! target.id ) {
					return;
				}

				var link = new TGE_Editor.models.Disconnecion( {
					source: source,
					target: target
				} );

				window.tge_app_view.saving();

				link.save( null, {
					error: function ( model, response ) {
						TVE_Dash.err( response.responseJSON.data.message );
					},
					complete: function () {
						window.tge_app_view.change_automatically_saved();
					}
				} );
			},

			save_link: function ( source, target, linkView ) {

				var link = new TGE_Editor.models.Connection( {
					source: source,
					target: target
				} );

				window.tge_app_view.saving();

				link.save( null, {
					error: function ( model, response ) {
						linkView.model.disconnect();
						TVE_Dash.err( response.responseJSON.data.message );
					},
					complete: function () {
						window.tge_app_view.change_automatically_saved();
					}
				} );
			},

			renderQuestion: function ( model ) {
				var view = new TGE_Editor.views.QuestionView( {
					model: model
				} );

				this.sidebarList.append( view.render().$el );
			},

			onCellPointerup: function ( cellView, event ) {

				this.selectedCell = null;
				var target_class = event ? event.target.getAttribute( 'class' ) : null;

				if ( target_class !== 'tge-remove-tool' && target_class !== 'tge-edit-tool' && cellView.model.get( 'type' ) === 'tge.Question' ) {
					if ( cellView.model.changed.position ) {
						var b_model = this.questions.findWhere( {id: cellView.model.id} );
						b_model.set( 'position', cellView.model.get( 'position' ) );

						window.tge_app_view.saving();

						b_model.save( null, {
							complete: function () {
								window.tge_app_view.change_automatically_saved();
							}
						} );
					}
				}

				this.$scrollers.hide();
			},

			onCellPointerdown: function ( cell ) {

				this.selectedCell = cell;
				this.$scrollers.show();
			},

			onCellMouseover: function ( cellView ) {

				if ( cellView.model.get( 'type' ) === 'tge.Question' ) {
					cellView.$( '.tge-question-tools' ).attr( 'style', 'display: inline' );
				}
			},

			onCellMouseout: function ( cellView ) {

				if ( cellView.model.get( 'type' ) === 'tge.Question' ) {
					cellView.$( '.tge-question-tools' ).attr( 'style', 'display: none' );
				}
			},

			onLinkConnect: function ( linkView ) {

				this.save_link( linkView.model.get( 'source' ), linkView.model.get( 'target' ), linkView );

				/**
				 * if the link is one that decides the start question then
				 * update the question and re-render the sidebar list
				 */
				if ( linkView.model.get( 'source' ).id === 'start' ) {
					var q = this.questions.findWhere( {id: linkView.model.get( 'target' ).id} );
					if ( q instanceof Backbone.Model ) {
						q.set( 'start', '1' );
					}
				} else {
					var id = linkView.model.get( 'source' ).id;
					var item;

					if ( id.indexOf( 'a' ) !== - 1 ) {
						item = this.answers.findWhere( {id: id} );
					} else {
						item = this.questions.findWhere( {id: id} );
					}

					if ( item instanceof Backbone.Model ) {
						item.set( 'next_question_id', linkView.model.get( 'target' ).id );
					}
				}

				this.render_sidebar_list();
			},

			onGraphRemove: function ( model ) {

				if ( model.isLink() && ! _.isUndefined( model.get( 'source' ).id ) && ! _.isUndefined( model.get( 'target' ).id ) ) {
					this.disconnect_link( model.get( 'source' ), model.get( 'target' ) );

					if ( model.get( 'source' ).id === 'start' ) {
						var q = this.questions.findWhere( {id: model.get( 'target' ).id} );
						if ( q instanceof Backbone.Model ) {
							q.set( 'start', null );

						}
					} else {
						var id = model.get( 'source' ).id;
						var item;

						if ( id.indexOf( 'a' ) !== - 1 ) {
							item = this.answers.findWhere( {id: id} );
						} else {
							item = this.questions.findWhere( {id: id} );
						}

						if ( item instanceof Backbone.Model ) {
							item.set( 'next_question_id', null );
						}
					}

					this.render_sidebar_list();
				}
			},

			onGraphAdd: function ( model ) {

				if ( model.get( 'type' ) === 'tge.Question' ) {
					model.loadAnswers();
				}
			},

			onGraphChangePosition: function ( cellView ) {

				if ( this.selectedCell && _.indexOf( this.notMovableModels, this.selectedCell.model.get( 'type' ) ) !== - 1 ) {
					cellView.set( 'position', cellView.previous( 'position' ) );
				}
			},

			render_questions: function ( q_model, level ) {

				if ( level === undefined ) {
					level = 0;
				}

				if ( ! (q_model instanceof Backbone.Model) || ! (q_model.get( 'answers' ) instanceof Backbone.Collection) ) {
					return;
				}

				q_model.attributes['level'] = level;

				if ( q_model.get( 'rendered' ) !== true ) {
					this.renderQuestion( q_model );
				}

				var _next_q_id = q_model.get( 'next_question_id' ),
					next_q_model = null;

				if ( _next_q_id ) {
					next_q_model = this.questions.findWhere( {id: _next_q_id.replace( 'q', '' ) + 'q'} );
				}

				q_model.get( 'answers' ).each( function ( a_model, index, list ) {

					if ( ! a_model.get( 'next_question_id' ) ) {
						return;
					}

					var _next_id = a_model.get( 'next_question_id' ),
						_next_model = this.questions.findWhere( {id: _next_id.replace( 'q', '' ) + 'q'} ),
						_new_level = level;

					if ( _next_model instanceof Backbone.Model ) {
						_new_level ++;
						this.render_questions( _next_model, _new_level );
					}

				}, this );

				if ( next_q_model instanceof Backbone.Model ) {
					this.render_questions( next_q_model, level );
				}
			},

			/**
			 * Deprecated
			 */
			_get_paths: function ( q, points_sum, path, level ) {

				if ( _.isUndefined( q ) ) {
					return;
				}

				var r = {
					q: q.get( 'text' ),
					points: points_sum,
					path_key: path,
					paths: [],
					level: level
				};

				if ( q.get( 'rendered' ) !== true ) {

					q.attributes['level'] = level;
					q.attributes['path'] = path;

					this.renderQuestion( q );
				}

				q.get( 'answers' ).each( function ( a, index, list ) {

					var new_level = level;

					if ( a.get( 'next_question_id' ) ) {
						new_level ++;
					}

					var next_question_id = a.get( 'next_question_id' ) ? a.get( 'next_question_id' ) : q.get( 'next_question_id' );
					next_question_id = next_question_id ? next_question_id : '';

					var next_question = this.questions.findWhere( {id: next_question_id.replace( 'q', '' ) + 'q'} );
					var points = parseInt( a.get( 'points' ) );

					if ( next_question instanceof Backbone.Model ) {

						r.paths.push( this._get_paths( next_question, points_sum + points, path + ':' + a.get( 'text' ), new_level ) );

					} else {
						r.paths.push( {
							path_key: path + ':' + a.get( 'text' ),
							points: a.get( 'points' )
						} );
						this.costs.push( points_sum + points );
					}
				}, this );

				return r;
			},

			render_sidebar_list: function () {

				this.sidebarList.empty();

				this.questions.each( function ( item ) {
					item.attributes['rendered'] = false;
				} );

				try {
					this.render_questions( this.questions.findWhere( {start: '1'} ), 0, 'Start', 0 );
				} catch ( Error ) {
					TVE_Dash.err( 'Please check the links and break the loops.' );
				}
			},

			updateAnswersCollection: function () {

				this.answers.reset();

				this.questions.each( function ( item ) {
					item.get( 'answers' ).each( function ( a ) {
						this.answers.push( a );
					}, this )
				}, this );
			},

			saving: function () {

				this.$saving_status.html( TGE_Editor.t.saving );
			},

			change_automatically_saved: function () {

				this.$saving_status.html( '<span class="tvd-icon-question-circle"></span>' + '<span>' + TGE_Editor.t.changes_automatically_saved + '</span>' );
			},

			toggle_control_panel: function ( event ) {

				var self = this,
					toggle_class = 'tge-cp-collapsed',
					$cp = $( '#tge-control-panel' );

				$cp.toggleClass( toggle_class );
				this.$el.toggleClass( toggle_class );

				setTimeout( function () {
					event.cp_collapsed = self.$el.hasClass( toggle_class );
					self.trigger( 'tge:cp:collapse', event );
				}, 700 );

			},

			int_navigator: function () {

				var $nav_control = $( '#tge-nav-control' );

				$nav_control.click( function () {

					var $this = $( this );

					if ( $this.hasClass( 'tvd-icon-minus' ) ) {
						$( '#tge-nav-paper' ).css( {
							height: 0
						} );
						$this.removeClass( 'tvd-icon-minus' ).addClass( 'tvd-icon-plus' );
						$this.attr( 'data-tooltip', TGE_Editor.t.maximize );
						return;
					}

					$( '#tge-nav-paper' ).css( {
						height: 250
					} );
					$this.removeClass( 'tvd-icon-plus' ).addClass( 'tvd-icon-minus' );
					$this.attr( 'data-tooltip', TGE_Editor.t.minimize );
				} );

				var Handler = {
					get_size: function ( port_view_size ) {
						var size = {
							width: 10,
							height: 10
						};

						var paper_width = TGE_Editor.const.paper.size.width;
						var paper_height = TGE_Editor.const.paper.size.height;
						var port_view_scale = {
							width: port_view_size.width / paper_width,
							height: port_view_size.height / paper_height
						};

						size.width = TGE_Editor.const.navigator.size.width * port_view_scale.width;
						size.height = TGE_Editor.const.navigator.size.height * port_view_scale.height;

						return {
							width: ( size.width ) + 'px',
							height: ( size.height ) + 'px'
						};
					}
				};


				var self = this,
					port_view_size = {
						width: this.$el.width(),
						height: this.$el.height()
					};

				this.$nav_handler = $( '#tge-nav-handler' );

				var scale_width = ( TGE_Editor.const.paper.size.width / TGE_Editor.const.navigator.size.width ),
					scale_height = ( TGE_Editor.const.paper.size.height / TGE_Editor.const.navigator.size.height );

				this.$nav_handler.css( Handler.get_size( port_view_size ) );
				this.$nav_handler.draggable( {
					containment: 'parent',
					drag: function ( event, ui ) {

						var left = ui.position.left * scale_width,
							top = ui.position.top * scale_height;

						tge_app_view.$el.scrollLeft( left );
						tge_app_view.$el.scrollTop( top );
					}
				} );

				this.on( 'tge:cp:collapse', function ( event ) {
					var port_view_size = {
						width: this.$el.width(),
						height: this.$el.height()
					};
					var size = Handler.get_size( port_view_size );
					self.$nav_handler.css( size );
				} );

			},

			on_scroll: function ( event ) {
				var scrolled_top = event.target.scrollTop,
					scrolled_left = event.target.scrollLeft;

				var scale_width = ( TGE_Editor.const.paper.size.width / TGE_Editor.const.navigator.size.width ),
					scale_height = ( TGE_Editor.const.paper.size.height / TGE_Editor.const.navigator.size.height );

				this.$nav_handler.css( {
					top: scrolled_top / scale_height,
					left: scrolled_left / scale_width
				} );
			},

			add_new_question: function () {

				var position = {
						x: 150 + tge_app_view.$el.scrollLeft(),
						y: 150 + tge_app_view.$el.scrollTop()
					},
					new_question = new TGE_Editor.models.Question( {
						position: position
					} );

				TVE_Dash.modal( TGE_Editor.modals.AddQuestion, {
					model: new_question,
					'max-width': '40%'
				} );
			}
		} );

		/**
		 * Question Form View
		 */
		TGE_Editor.views.FormQuestion = Backbone.View.extend( {

			template: TVE_Dash.tpl( 'question/form' ),

			answers_views: [],

			events: {
				'click #tge-qf-add-description': 'add_description_field',
				'click #tge-gf-delete-description': 'remove_description_field',
				'click #tge-qf-add-answer': 'add_answer',
				'keyup .tge-answer-text': function ( event ) {

					event.stopPropagation();

					if ( event.keyCode === 13 ) {
						this.add_answer();
					}
				},
				'change #tge-toggle-points': function ( event ) {
					TGE_Editor.globals.weight_switcher.set( 'display_weight', event.target.checked );
				}
			},

			initialize: function () {
				this.listenTo( this.model.get( 'answers' ), 'add', this.renderAnswerForm );
			},

			render: function () {

				this.$el.html( this.template( {item: this.model} ) );
				this.renderAnswers( this.model.get( 'answers' ) );
				this.renderImagePicker( this.$( '#tge-qf-image-loader' ) );

				if ( TGE_Editor.quiz.quiz_type === 'personality' ) {
					this.render_switcher();
				}

				TVE_Dash.data_binder( this );

				this.add_answer();

				return this;
			},

			render_switcher: function () {

				var _tpl = TVE_Dash.tpl( 'question/points_switcher' );
				this.$( '#tge-points-switcher' ).html( _tpl( {item: TGE_Editor.globals.weight_switcher} ) );
			},

			renderAnswerForm: function ( item ) {

				var self = this,
					type = TGE_Editor.globals.question_types.findWhere( {id: parseInt( this.model.get( 'q_type' ) )} );

				if ( ! (type instanceof Backbone.Model) || ( typeof TGE_Editor.views['FormAnswer' + TVE_Dash.upperFirst( type.get( 'key' ) )] !== 'function') ) {
					return;
				}

				var answer_form_view = new TGE_Editor.views['FormAnswer' + TVE_Dash.upperFirst( type.get( 'key' ) )]( {
					model: item,
					collection: this.model.get( 'answers' )
				} );

				this.answers_views.push( answer_form_view );

				this._getAnswersWrapper().append( answer_form_view.render().$el ).sortable( {
					handle: this.$( '.tge-sort-handle' ),
					placeholder: 'tge-sortable-placeholder',
					sort: function ( event, ui ) {
						ui.placeholder.css( {
							border: '2px dashed gray',
							width: ui.helper.css( 'width' ),
							height: ui.helper.css( 'height' ),
							margin: ui.helper.css( 'margin' )
						} )
					},
					update: function () {
						self.update_answers_order();
					}
				} );
			},

			update_answers_order: function () {

				_.each( this.answers_views, function ( view, index, list ) {
					view.model.attributes['order'] = view.$el.index();
				}, this );
			},

			_getAnswersWrapper: function () {

				if ( ! this.$answers_wrapper ) {
					this.$answers_wrapper = this.$( '#tge-answers-wrapper' );
				}

				return this.$answers_wrapper;
			},

			add_description_field: function () {

				this.$( '#tge-question-description' ).show();
				this.$( '#tge-qf-add-description' ).hide();
				this.$( '#tge-question-description-textarea' ).trigger( 'autoresize' );
			},

			add_answer: function () {

				var empty_model = this.model.get( 'answers' ).findWhere( this.model.get( 'q_type' ) == 2 ? {image: ''} : {text: ''} );
				if ( empty_model instanceof Backbone.Model ) {

					return;
				}

				var type = TGE_Editor.globals.question_types.findWhere( {id: parseInt( this.model.get( 'q_type' ) )} );
				var model_name = 'Answer';

				if ( type instanceof Backbone.Model && typeof TGE_Editor.models[model_name + TVE_Dash.upperFirst( type.get( 'key' ) )] === 'function' ) {
					model_name += TVE_Dash.upperFirst( type.get( 'key' ) );
				}

				var model = new TGE_Editor.models[model_name]( {
					order: this.model.get( 'answers' ).length
				} );

				this.model.get( 'answers' ).push( model );
			},

			remove_description_field: function () {

				this.$( '#tge-question-description' ).hide();
				this.$( '#tge-qf-add-description' ).show();
			},

			renderAnswers: function ( collection ) {

				this.answers_views = [];

				if ( ! (collection instanceof Backbone.Collection) ) {
					return;
				}

				collection.each( function ( item, index, list ) {
					this.renderAnswerForm( item );
				}, this );
			},

			renderImagePicker: function ( $el ) {

				var view = new TGE_Editor.views.ImagePicker( {
					model: this.model,
					el: $el
				} );

				view.render();
			}
		} );

		TGE_Editor.views.FormQuestionEdit = TGE_Editor.views.FormQuestion.extend( {

			render: function () {

				this.$el.html( this.template( {item: this.model} ) );

				TVE_Dash.data_binder( this );

				this.renderAnswers( this.model.get( 'answers' ) );
				this.renderImagePicker( this.$( '#tge-qf-image-loader' ) );

				if ( TGE_Editor.quiz.quiz_type === 'personality' ) {
					this.render_switcher();
				}

				return this;
			}

		} );

		/**
		 * Answer Form View
		 */
		TGE_Editor.views.FormAnswer = Backbone.View.extend( {

			className: 'tvd-row ' + (TGE_Editor.quiz.quiz_type === 'personality' ? 'tge-personality-answer' : ''),

			template: TVE_Dash.tpl( 'answer/form' ),

			events: {
				'click .tvd-icon-trash-o': function () {

					this.collection.remove( this.model );
					this.remove();
				}
			},

			initialize: function () {

				this.listenTo( TGE_Editor.globals.weight_switcher, 'change:display_weight', this.toggle_points );
			},

			toggle_points: function ( q_model, display_weight ) {

				this._get_points_column().css( {
					display: display_weight ? 'block' : 'none'
				} );

				if ( display_weight ) {
					this.results_view.$el.removeClass( 'tvd-s4' ).addClass( 'tvd-s3' );
				} else {
					this.results_view.$el.removeClass( 'tvd-s3' ).addClass( 'tvd-s4' );
				}
			},

			render: function () {

				var self = this;
				this.$el.html( this.template( {item: this.model} ) );

				if ( TGE_Editor.quiz.quiz_type === 'personality' ) {
					this.render_results_options();
				}

				TVE_Dash.data_binder( this );

				setTimeout( function () {
					TVE_Dash.materialize( self.$el );
					self.$( 'input.tge-answer-text' ).first().focus().select();
				}, 10 );

				return this;
			},

			render_results_options: function () {

				this.results_view = new TGE_Editor.views.QuizResults( {
					collection: new Backbone.Collection( TGE_Editor.quiz.results ),
					model: this.model
				} );

				this._get_points_column().removeClass( 'tvd-s4' ).addClass( 'tvd-s1' );
				this._get_points_column().before( this.results_view.render().$el );

				if ( TGE_Editor.globals.weight_switcher.get( 'display_weight' ) ) {
					this._get_points_column().css( {
						display: 'block'
					} );
					this.results_view.$el.removeClass( 'tvd-s4' ).addClass( 'tvd-s3' );
				} else {
					this._get_points_column().css( {
						display: 'none'
					} );
					this.results_view.$el.removeClass( 'tvd-s3' ).addClass( 'tvd-s4' );
				}
			},

			_get_points_column: function () {

				if ( ! this.$points_column ) {
					this.$points_column = this.$( '.tge-points-column' );
				}

				return this.$points_column;
			}
		} );

		/**
		 * Image Answer Form View
		 */
		TGE_Editor.views.FormAnswerImage = TGE_Editor.views.FormAnswer.extend( {

			template: TVE_Dash.tpl( 'answer/form/image' ),

			render: function () {

				TGE_Editor.views.FormAnswer.prototype.render.apply( this, arguments );

				this.renderImagePicker( this.$( '.tge-image-loader' ) );

				return this;
			},

			renderImagePicker: function ( $el ) {

				var view = new TGE_Editor.views.ImagePickereAnswer( {
					model: this.model,
					el: $el
				} );

				view.render();
			}
		} );

		/**
		 * Button Answer Form View
		 */
		TGE_Editor.views.FormAnswerButton = TGE_Editor.views.FormAnswer.extend( {
			template: TVE_Dash.tpl( 'answer/form/button' ),

			events: _.extend( {}, TGE_Editor.views.FormAnswer.prototype.events, {} )

		} );

		TGE_Editor.views.ImagePicker = Backbone.View.extend( {

			template: TVE_Dash.tpl( 'image/form' ),

			events: {
				'click .tge-add-image': 'open_media',
				'click .tge-remove-image': 'remove',
				'click .tge-change-image': 'open_media'
			},

			render: function () {

				this.$el.html( this.template( {item: this.model} ) );

				this.$buttonAdd = this.$( '.tge-add-image' );
				this.$buttonRemove = this.$( '.tge-remove-image' );
				this.$buttonChange = this.$( '.tge-change-image' );
				this.$imageWrapper = this.$( '.tge-form-image-wrapper' );

				this.load_image();

				TVE_Dash.data_binder( this );

				return this;
			},

			open_media: function () {

				var self = this,
					frame = wp.media( {
						title: TGE_Editor.t.media.question_title
					} );

				frame.on( 'select', function () {
					var attachment = frame.state().get( 'selection' ).first().toJSON();

					if ( ! attachment.sizes.thumbnail ) {
						attachment.sizes.thumbnail = attachment.sizes.full;
					}

					self.model.set( 'image', new TGE_Editor.models.Image( attachment ) );

					self.load_image();
				} );

				frame.open();
			},

			remove: function () {

				this.$imageWrapper.css( {
					'background-image': '',
					height: 0
				} );

				this.model.set( 'image', null );

				this.$buttonAdd.css( {display: 'inline'} );
				this.$buttonChange.css( {display: 'none'} );
				this.$buttonRemove.css( {display: 'none'} );
			},

			load_image: function () {

				if ( ! this.model.get( 'image' ) || ! this.model.get( 'image' ).get_thumb() ) {
					return;
				}

				this.$imageWrapper.css( {
					'background-image': 'url(' + this.model.get( 'image' ).get_thumb() + ')',
					height: '100px'
				} );
				this.$( 'label' ).html( '' );
				this.$buttonAdd.css( {display: 'none'} );
				this.$buttonChange.css( {display: 'inline'} );
				this.$buttonRemove.css( {display: 'inline'} );
			}
		} );

		TGE_Editor.views.ImagePickereAnswer = TGE_Editor.views.ImagePicker.extend( {

			template: TVE_Dash.tpl( 'image/answer' )

		} );

		TGE_Editor.views.QuizResults = Backbone.View.extend( {

			className: 'tvd-col tvd-s4',

			template: TVE_Dash.tpl( 'quiz/responses' ),

			render: function () {

				this.$el.html( this.template( {item: this.model} ) );
				this.$select = this.$( 'select' );

				this.collection.each( function ( item, index, list ) {
					var $option = $( '<option/>' );
					$option.val( item.get( 'id' ) );
					$option.text( item.get( 'text' ) );
					if ( this.model.get( 'result_id' ) === item.get( 'id' ) ) {
						$option.attr( 'selected', 'selected' );
					}
					this.$select.append( $option );
				}, this );

				if ( this.model.get( 'result_id' ) == 0 ) {
					this.$select.find( 'option[value="0"]' ).attr( 'selected', 'selected' );
				}

				return this;
			}
		} );

		TGE_Editor.views.QuestionView = Backbone.View.extend( {
			tagName: 'li',
			className: 'tvd-collection-item tge-q-item',
			template: TVE_Dash.tpl( 'question/item' ),

			events: {
				'click': 'onClick'
			},

			render: function () {

				this.$el.html( this.template( {item: this.model} ) );
				this.$el.attr( 'id', 'tge-question-' + this.model.get( 'id' ) );
				this.$el.css( {
					'border-left-width': this.model.get( 'level' ) * 2 + 'px'
				} );

				this.model.attributes['rendered'] = true;

				return this;
			},

			highlight_callView: function ( cellView ) {

				var highlight_element = '.question-body',
					animation_duration = 150, //milliseconds; if u change this pls update the css transition time
					highlighting_opt = {
						highlighter: {
							name: 'addClass',
							options: {
								className: 'tge-opacity'
							}
						}
					};

				cellView.highlight( highlight_element, highlighting_opt );

				setTimeout( function () {
					cellView.unhighlight( highlight_element, highlighting_opt );
				}, animation_duration );
			},

			onClick: function () {

				var cellView = tge_app_view.paper.findViewByModel( this.model.get( 'id' ) ),
					q_model = tge_app_view.graph.getCell( this.model.get( 'id' ) );

				var current_x = tge_app_view.$el.scrollLeft(),
					current_y = tge_app_view.$el.scrollTop(),
					move_to_y = this.model.get( 'position' ).y - (tge_app_view.$el.height() - q_model.get( 'size' ).height) / 2,
					move_to_x = this.model.get( 'position' ).x - (tge_app_view.$el.width() - q_model.get( 'size' ).width) / 2;

				var scale_width = ( TGE_Editor.const.paper.size.width / TGE_Editor.const.navigator.size.width ),
					scale_height = ( TGE_Editor.const.paper.size.height / TGE_Editor.const.navigator.size.height );

				if ( tge_app_view.scrolling === true || (current_y === move_to_y && current_x === move_to_x) ) {
					this.highlight_callView( cellView );

					return;
				}

				var self = this,
					top = move_to_y / scale_height,
					left = move_to_x / scale_width;

				tge_app_view.$nav_handler.animate( {
					top: top < 0 ? 0 : top,
					left: left < 0 ? 0 : left
				} );


				tge_app_view.scrolling = true;
				tge_app_view.$el.animate( {
					scrollTop: move_to_y + 'px',
					scrollLeft: move_to_x + 'px'
				}, 600, 'swing', function () {
					self.highlight_callView( cellView );
					tge_app_view.scrolling = false;
				} );
			}
		} );

	} );
})( jQuery );
;/**
 * Created by dan bilauca on 11/3/2016.
 */
joint.shapes.tge = {};

joint.shapes.tge.Question = joint.dia.Element.extend( {

	markup: '<g class="rotatable">' +
	        '<g class="scalable"><rect class="question-body"/></g>' +
	        '<text class="question-text"/>' +
	        '<image class="question-image"/>' +
	        '</g>',

	portMarkup: '<circle class="port-body" r="7" fill="gray" />',

	toolsMarkup: '<g class="tge-question-tools">' +
	             '<image class="tge-remove-tool" width="13" height="14"/>' +
	             '<image class="tge-edit-tool" width="14" height="15"/>' +
	             '</g>',

	defaults: joint.util.deepSupplement( {
		position: {
			x: 0,
			y: 0
		},
		type: 'tge.Question',
		attrs: {
			'.': {magnet: false},
			'.question-body': {
				rx: '4px',
				ry: '4px',
				fill: '#595c60',
				filter: {
					name: 'dropShadow',
					args: {
						dx: 2,
						dy: 2,
						blur: 3
					}
				}
			},
			'.question-image': {
				x: 0,
				y: 0,
				width: TGE_Editor.const.question.image.size.width,
				preserveAspectRatio: 'xMidYMid slice'
			},
			'.question-text': {
				ref: '.question-body',
				fill: 'white',
				'ref-x': .5,
				'ref-y': 15,
				'x-alignment': 'middle',
				'font-size': '13px',
				'font-family': 'Arial'
			},
			'.tge-question-tools>image': {
				preserveAspectRatio: 'xMidYMid meet'
			},
			'.tge-remove-tool': {
				ref: '.question-body',
				'ref-x': 7,
				'ref-y': 5
			},
			'.tge-edit-tool': {
				ref: '.question-body',
				'ref-dx': - 20,
				'ref-y': 5
			}
		},
		ports: {
			groups: {
				in: {
					position: {
						name: 'top'
					}
				},
				out: {
					position: {
						name: 'bottom'
					}
				}
			},
			items: []
		}
	}, joint.dia.Element.prototype.defaults ),

	initialize: function () {

		this.on( 'change:text', function ( model, new_value, opt ) {
			this.attr( '.question-text/text', this.get_text() );
		} );

		this.on( 'change:image', function () {
			this.attr( '.question-image/xlink:href', this.get_image() );
			this.auto_resize();
			this.loadAnswers();
			this.attr( '.question-text/text', this.get_text() );
		} );

		this.on( 'change:q_type', function () {
			this.trigger( 'change:answers' );
		} );

		this.on( 'change:answers', function () {
			this.loadAnswers();
			this.auto_resize();
			this.attr( '.question-image/height', this.get( 'size' ).height );
			this.attr( '.question-text/text', this.get_text() );
		}, this );

		this.auto_resize();
		this.initializePorts();

		this.attr( '.question-text/text', this.get_text() );

		if ( this.get( 'image' ) ) {
			this.attr( '.question-image/xlink:href', this.get_image() );
		}

		this.attr( '.tge-remove-tool/xlink:href', TGE_Editor.icons.delete );
		this.attr( '.tge-edit-tool/xlink:href', TGE_Editor.icons.edit );

		joint.dia.Element.prototype.initialize.apply( this, arguments );
	},

	loadAnswers: function () {
		var col = - 1,
			row = 0;

		var jAnswers = _.map( this.getEmbeddedCells(), 'id' );
		var current_answers = _.map( this.get( 'answers' ), 'id' );
		var to_be_removed = _.difference( jAnswers, current_answers );

		/**
		 * remove answer cell that do not exists in answers prop
		 */
		_.each( to_be_removed, function ( item, index, list ) {
			window.tge_app_view.graph.getCell( item ).remove();
		}, this );

		var answers = _.sortBy( this.get( 'answers' ), function ( item ) {
			return item.order;
		} );

		_.each( answers, function ( answer, index, list ) {
			if ( index && index % TGE_Editor.const.answers_per_row === 0 ) {
				col = - 1; //reset col
				row ++;
			}
			col ++;
			this.loadAnswer( answer, col, row );
		}, this );
	},

	loadAnswer: function ( model, col, row ) {

		var existing_jAnswer = this.graph.getCell( model.id );

		if ( existing_jAnswer ) {
			existing_jAnswer.set( _.extend( {}, model, {
				col: col,
				row: row,
				jQuestion: this.toJSON()
			} ) );
			existing_jAnswer.trigger( this.get( 'q_type' ) == 2 ? 'change:image' : 'change:text' );
		} else {
			var model_name = this.get( 'q_type' ) == 2 ? 'Answer' : 'Answer';
			var jAnswer = new joint.shapes.tge[model_name]( _.extend( model, {
				col: col,
				row: row,
				jQuestion: this.toJSON()
			} ) );
			this.embed( jAnswer );
			this.graph.addCell( jAnswer );
		}
	},

	get_image: function () {

		if ( this.get( 'image' ) === null ) {
			return '';
		}

		if ( ! this.get( 'image' ).sizes.thumbnail ) {
			return '';
		}

		if ( typeof this.get( 'image' ) === 'string' ) {
			return this.get( 'image' );
		}

		return this.get( 'image' ).sizes.thumbnail.url;
	},

	get_text: function () {

		var width = this.get( 'size' ).width;

		width = width - TGE_Editor.const.answer.margin.left * 2;

		if ( this.get( 'answers' ).length === 0 ) {
			return this.get( 'text' );
		}

		var text = this.get( 'text' ),
			text_size = this.get( 'text_size' );

		if ( true || ! text_size ) {
			text_size = joint.util.measureText( text, {
				fontSize: this.attr( '.question-text/font-size' )
			} );
			this.set( 'text_size', text_size );
		}

		if ( this.get( 'q_type' ) && this.get( 'image' ) ) {
			this.attr( '.question-text/x-alignment', null );
			this.attr( '.question-text/ref-x', null );
			this.attr( '.question-text/x', 120 );
			width -= 105;
		} else {
			this.attr( '.question-text/x-alignment', 'middle' );
			this.attr( '.question-text/ref-x', .5 );
			this.attr( '.question-text/x', null );
		}

		var fit = text_size.width < width,
			suffix;

		if ( ! fit ) {
			width -= 10;
			suffix = '...';
		}

		while ( ! fit ) {
			text = text.slice( 0, text.length - 1 );
			text_size = joint.util.measureText( text, {
				fontSize: this.attr( '.question-text/font-size' )
			} );

			fit = text_size.width < width;
		}

		return suffix ? text + suffix : text;
	},

	initializePorts: function () {

		var attrs = {
			'.port-body': {
				magnet: true,
				stroke: 'white',
				'stroke-width': 1,
				'qid': this.get( 'id' )
			}
		};
		this.get( 'ports' ).items.push( {id: 'q-in-port-' + this.get( 'id' ), group: 'in', attrs: attrs} );
		this.get( 'ports' ).items.push( {id: 'q-out-port-' + this.get( 'id' ), group: 'out', attrs: attrs} );
	},

	auto_resize: function () {

		var width = this._calculate_width(),
			height = this._calculate_height();

		height += TGE_Editor.const.question.size.height;

		this.attr( '.question-body/width', width );
		this.attr( '.question-body/height', height );
		this.attr( '.question-image/height', height );

		this.resize( width, height );
	},

	_calculate_width: function () {

		var answers = this.get( 'answers' ).length,
			answers_per_row = TGE_Editor.const.answers_per_row,
			answer_margin_left = TGE_Editor.const.answer.margin.left,
			answer_width = this.get( 'q_type' ) == 1 ? TGE_Editor.const.answer.size.width : TGE_Editor.const.answer.image.size.width;

		var width = answers * answer_width + (answer_margin_left * (answers + 1));

		if ( answers >= answers_per_row ) {
			width = answers_per_row * answer_width + (answer_margin_left * (answers_per_row + 1))
		}

		if ( this.get( 'image' ) ) {
			width += TGE_Editor.const.question.image.size.width;
		}

		return width || 1;
	},

	_calculate_height: function () {

		var total_answers = this.get( 'answers' ).length,
			rows = Math.ceil( total_answers / TGE_Editor.const.answers_per_row ),
			answer_height = this.get( 'q_type' ) == 1 ? TGE_Editor.const.answer.size.height : TGE_Editor.const.answer.image.size.height,
			answer_margin_bottom = TGE_Editor.const.answer.margin.bottom;

		return rows * answer_height + (rows * answer_margin_bottom ) + 25 || 1;
	},

	save: function () {
		var bQuestion = new TGE_Editor.models.Question( this.toJSON() );
		bQuestion.save();
	}

} );

joint.shapes.tge.QuestionView = joint.dia.ElementView.extend( {

	initialize: function () {

		joint.dia.ElementView.prototype.initialize.apply( this, arguments );
	},

	render: function () {

		joint.dia.ElementView.prototype.render.apply( this, arguments );

		this.renderTools();
		this.update();
	},

	renderTools: function () {

		var toolsMarkup = this.model.toolsMarkup || this.model.get( 'toolsMarkup' );
		if ( toolsMarkup ) {
			var nodes = V( toolsMarkup );
			V( this.el ).append( nodes );
		}
	},

	pointerclick: function ( event ) {

		if ( event.target.getAttribute( 'class' ) === 'tge-remove-tool' ) {
			TVE_Dash.modal( TGE_Editor.modals.DeleteQuestion, {
				model: this.model
			} );
		}

		if ( event.target.getAttribute( 'class' ) === 'tge-edit-tool' ) {
			var q = window.tge_app_view.questions.findWhere( {id: this.model.id} );
			TVE_Dash.modal( TGE_Editor.modals.EditQuestion, {
				model: q,
				jModel: this.model,
				'max-width': '40%'
			} );
		}

		joint.dia.ElementView.prototype.pointerclick.apply( this, arguments );
	}

} );

joint.shapes.tge.Answer = joint.dia.Element.extend( {

	markup: '<g class="rotatable">' +
	        '<g class="scalable"><rect class="body"/></g>' +
	        '<image/>' +
	        '<text class="answer-text"/>' +
	        '</g>',

	portMarkup: '<circle class="port-body" r="6" fill="gray" />',

	defaults: joint.util.deepSupplement( {
		type: 'tge.Answer',

		attrs: {
			'.': {
				magnet: false
			},
			'.body': {
				width: TGE_Editor.const.answer.size.width,
				height: TGE_Editor.const.answer.size.height,
				rx: '3px',
				ry: '3px',
				fill: '#797e84'
			},
			image: {
				width: TGE_Editor.const.answer.image.size.width,
				height: TGE_Editor.const.answer.image.size.height,
				preserveAspectRatio: 'xMidYMid slice'
			},
			text: {
				ref: 'rect',
				'ref-x': .5,
				'ref-y': .5,
				'y-alignment': 'middle',
				'x-alignment': 'middle',
				fill: 'white',
				'font-size': '11px',
				filter: {
					name: 'dropShadow',
					args: {
						blur: 1,
						opacity: 1,
						color: 'black'
					}
				}
			}
		},

		ports: {
			groups: {
				out: {
					position: {
						name: 'bottom'
					}
				}
			},
			items: []
		}

	}, joint.dia.Element.prototype.defaults ),

	initialize: function () {

		this.on( 'change:text', function () {
			this.attr( '.answer-text/text', this.get_text() );
			this.attr( 'image/xlink:href', null );
			this.auto_resize();
			this.auto_position();
		} );

		this.on( 'change:image', function () {
			this.attr( 'image/xlink:href', this.get_image() );
			this.attr( '.answer-text/text', this.get_text() );
			this.auto_resize();
			this.auto_position();
		} );

		if ( this.get( 'image' ) && this.get( 'jQuestion' ).q_type == 2 ) {
			this.attr( 'image/xlink:href', this.get_image() );
		}

		this.attr( '.answer-text/text', this.get_text() );

		this.auto_position();
		this.auto_resize();
		this.initializePorts();

		joint.dia.Element.prototype.initialize.apply( this, arguments );
	},

	initializePorts: function () {

		var attrs = {
			'.port-body': {
				magnet: true,
				stroke: '#393d44',
				'stroke-width': 1,
				'aid': this.get( 'id' )
			}
		};
		this.get( 'ports' ).items.push( {id: 'a-port-' + ( this.get( 'id' ) ), group: 'out', attrs: attrs} );
	},

	auto_resize: function () {

		var width = this.get( 'jQuestion' ).q_type == 2 ? TGE_Editor.const.answer.image.size.width : TGE_Editor.const.answer.size.width,
			height = this.get( 'jQuestion' ).q_type == 2 ? TGE_Editor.const.answer.image.size.height : TGE_Editor.const.answer.size.height;

		this.attr( '.body/width', width );
		this.attr( '.body/height', height );

		if ( this.get( 'image' ) && this.get( 'jQuestion' ).q_type == 2 ) {
			this.attr( 'image/width', TGE_Editor.const.answer.image.size.width );
			this.attr( 'image/height', TGE_Editor.const.answer.image.size.height );
		} else {
			this.attr( 'image/width', 0 );
			this.attr( 'image/height', 0 );
		}

		this.resize( width, height );
	},

	auto_position: function () {

		var x = this.get( 'jQuestion' ).position.x,
			y = this.get( 'jQuestion' ).position.y,
			q_image = this.get( 'jQuestion' ).image,
			q_text_size = this.get( 'jQuestion' ).text_size,
			answer_width = this.get( 'jQuestion' ).q_type == 2 ? TGE_Editor.const.answer.image.size.width : TGE_Editor.const.answer.size.width,
			answer_height = this.get( 'jQuestion' ).q_type == 2 ? TGE_Editor.const.answer.image.size.height : TGE_Editor.const.answer.size.height,
			col = this.get( 'col' ),
			row = this.get( 'row' );

		y += Math.round( q_text_size.height ) + 25;

		x += (TGE_Editor.const.answer.margin.left * (col ? col + 1 : 1) ) + (col * answer_width);
		y += (answer_height * row) + (TGE_Editor.const.answer.margin.bottom * row);

		if ( q_image ) {
			x += TGE_Editor.const.question.image.size.width;
		}

		this.set( 'position', {
			x: x,
			y: y
		} )
	},

	get_image: function () {

		if ( this.get( 'image' ) === null ) {
			return '';
		}

		if ( typeof this.get( 'image' ) === 'string' ) {
			return this.get( 'image' );
		}

		if ( this.get( 'image' ) instanceof Backbone.Model ) {
			return this.get( 'image' ).get( 'sizes' ).thumbnail.url;
		}

		return this.get( 'image' ).sizes.thumbnail.url;
	},

	get_text: function () {

		if ( this.get( 'jQuestion' ).q_type == 2 ) {
			return this.get( 'points' ) + 'p';
		}

		var text = this.get( 'points' ) + 'p - ' + this.get( 'text' );

		if ( text.length <= 24 ) {
			return text;
		}

		return text.slice( 0, 24 ) + '...';
	}

} );

joint.shapes.tge.AnswerImage = joint.shapes.tge.Answer.extend( {

	markup: '<g class="rotatable">' +
	        '<g class="scalable"><image/></g>' +
	        '</g>',

	defaults: joint.util.deepSupplement( {
		type: 'tge.AnswerImage',
		size: {
			width: TGE_Editor.const.answer.image.size.width,
			height: TGE_Editor.const.answer.image.size.height
		},

		attrs: {
			image: {
				width: TGE_Editor.const.answer.image.size.width,
				height: TGE_Editor.const.answer.image.size.height,
				preserveAspectRatio: 'xMidYMid slice'
			}
		}

	}, joint.shapes.tge.Answer.prototype.defaults ),

	initialize: function () {

		this.on( 'change:image', function () {
			this.attr( 'image/xlink:href', this.get( 'image' ) );
		} );

		this.attr( 'image/xlink:href', this.get( 'image' ) );

		this.auto_position();
		this.initializePorts();

		joint.dia.Element.prototype.initialize.apply( this, arguments );
	},

	auto_position: function () {

		var x = this.get( 'jQuestion' ).position.x,
			y = this.get( 'jQuestion' ).position.y,
			q_image = this.get( 'jQuestion' ).image,
			q_text_size = this.get( 'jQuestion' ).text_size,
			col = this.get( 'col' ),
			row = this.get( 'row' );

		y += Math.round( q_text_size.height ) + 25;

		x += (TGE_Editor.const.answer.margin.left * (col ? col + 1 : 1) ) + (col * TGE_Editor.const.answer.image.size.width);
		y += (TGE_Editor.const.answer.size.height * row) + (TGE_Editor.const.answer.margin.bottom * row);

		if ( q_image ) {
			x += TGE_Editor.const.question.image.size.width;
		}

		this.set( 'position', {
			x: x,
			y: y
		} )
	}

} );

joint.shapes.tge.NewQuestion = joint.dia.Element.extend( {

	markup: '<g class="rotatable" style="cursor: pointer"><g class="scalable"><rect class="body"/></g><circle class="circle"/><text class="circle-text"/><text class="text"/></g>',
	portMarkup: '<circle class="port-body" r="7" fill="gray" />',

	defaults: joint.util.deepSupplement( {
		type: 'tge.NewQuestion',
		size: {width: 158, height: 55},

		ports: {
			groups: {
				out: {
					position: {
						name: 'bottom'
					}
				}
			},
			items: []
		},

		attrs: {
			'.': {
				magnet: false
			},
			'.body': {
				width: 175,
				height: 50,
				fill: '#87548d',
				rx: 3,
				ry: 3,
				stroke: 'gray',
				'stroke-dasharray': '5,5'
			},
			text: {
				'font-family': 'Arial'
			},
			'.text': {
				ref: '.body',
				x: 60,
				'ref-y': .5,
				'y-alignment': 'middle',
				fill: 'white',
				'font-size': '14px',
				text: 'Add Question'
			},
			'.circle': {
				ref: '.body',
				'ref-x': 30,
				'ref-y': .5,
				r: 15,
				fill: '#66686c',
				stroke: 'white',
				'stroke-width': 1
			},
			'.circle-text': {
				ref: '.circle',
				'ref-x': .5,
				'ref-y': .5,
				'x-alignment': 'middle',
				'y-alignment': 'middle',
				text: '+',
				fill: 'white',
				'font-size': '20px'
			}
		}
	}, joint.dia.Element.prototype.defaults ),

	initialize: function () {

		//this.initializePorts();
		joint.dia.Element.prototype.initialize.apply( this, arguments );
	},

	initializePorts: function () {

		var attrs = {
			'.port-body': {
				magnet: true,
				stroke: 'gray',
				fill: 'white',
				'stroke-width': 2
			}
		};
		this.get( 'ports' ).items.push( {
			id: 'start-port',
			group: 'out',
			attrs: attrs
		} );
	}
} );


joint.shapes.tge.NewQuestionView = joint.dia.ElementView.extend( {

	pointerclick: function ( event ) {

		var position = {
				x: 100 + tge_app_view.$el.scrollLeft(),
				y: 100
			},
			new_question = new TGE_Editor.models.Question( {
				position: position
			} );

		TVE_Dash.modal( TGE_Editor.modals.AddQuestion, {
			model: new_question,
			'max-width': '40%'
		} );

		joint.dia.ElementView.prototype.pointerclick.apply( this, arguments );
	}

} );

joint.shapes.tge.StartFlow = joint.dia.Element.extend( {

	markup: '<g class="rotatable" style="cursor: pointer"><g class="scalable"><rect class="body"/></g><text/>' +
	        '<g>' +
	        '<path class="path1" d="M16 0c-8.837 0-16 7.163-16 16s7.163 16 16 16 16-7.163 16-16-7.163-16-16-16zM16 29c-7.18 0-13-5.82-13-13s5.82-13 13-13 13 5.82 13 13-5.82 13-13 13zM12 9l12 7-12 7z"></path>' +
	        '</g>' +
	        '</g>',
	portMarkup: '<circle class="port-body" r="7" fill="gray" />',

	defaults: joint.util.deepSupplement( {
		type: 'tge.StartFlow',
		size: {
			width: 130,
			height: 40
		},

		ports: {
			groups: {
				out: {
					position: {
						name: 'bottom'
					}
				}
			},
			items: []
		},

		attrs: {
			'.': {
				magnet: false
			},
			'.body': {
				width: 130,
				height: 40,
				fill: 'white',
				rx: 3,
				ry: 3,
				stroke: '#595c60',
				'stroke-width': .5
			},
			'.path1': {
				fill: '#87548d',
				transform: 'translate(9,8),scale(0.7)'
			},
			text: {
				ref: '.body',
				x: 40,
				'ref-y': .5,
				'y-alignment': 'middle',
				'font-family': 'Arial',
				'font-size': '16px',
				'font-weight': 'bold',
				text: TGE_Editor.t.quiz_start,
				fill: '#87548d'
			}
		}
	}, joint.dia.Element.prototype.defaults ),

	initialize: function () {

		this.initializePorts();
		joint.dia.Element.prototype.initialize.apply( this, arguments );
	},

	initializePorts: function () {

		var attrs = {
			'.port-body': {
				magnet: true,
				stroke: 'white',
				fill: '#87548d',
				'stroke-width': 2
			}
		};
		this.get( 'ports' ).items.push( {
			id: 'start-port',
			group: 'out',
			attrs: attrs
		} );
	}
} );

joint.shapes.tge.StartFlowView = joint.dia.ElementView.extend( {

	pointerclick: function ( event ) {

		if ( $( event.target ).is( 'path' ) ) {
			TGE_Editor.wistia.start_quiz.play();
			return;
		}

		var position = {
				x: 100 + tge_app_view.$el.scrollLeft(),
				y: 100
			},
			new_question = new TGE_Editor.models.Question( {
				position: position
			} );

		TVE_Dash.modal( TGE_Editor.modals.AddQuestion, {
			model: new_question,
			'max-width': '40%'
		} );

		joint.dia.ElementView.prototype.pointerclick.apply( this, arguments );
	}

} );

joint.util.measureText = function ( text, attrs ) {

	var fontSize = parseInt( attrs.fontSize, 10 ) || 10;

	var svgDocument = V( 'svg' ).node;
	var textElement = V( '<text><tspan></tspan></text>' ).node;
	var textSpan = textElement.firstChild;
	var textNode = document.createTextNode( '' );

	textSpan.setAttribute( 'font-size', attrs.fontSize );
	textSpan.appendChild( textNode );
	svgDocument.appendChild( textElement );
	document.body.appendChild( svgDocument );

	var lines = text.split( '\n' );
	var width = 0;

	// Find the longest line width.
	_.each( lines, function ( line ) {

		textNode.data = line;
		var lineWidth = textSpan.getComputedTextLength();

		width = Math.max( width, lineWidth );
	} );

	var height = lines.length * (fontSize * 1.2);

	V( svgDocument ).remove();

	return {width: width, height: height};
};

var tgeLink = joint.dia.Link.extend( {

	arrowheadMarkup: [
		'<g class="marker-arrowhead-group marker-arrowhead-group-<%= end %>">',
		'<path class="marker-arrowhead" d="M 10 0 L 0 5 L 10 10 z" />',
		'</g>'
	].join( '' ),

	toolMarkup: [
		'<g class="link-tool">',
		'<g class="tool-remove" event="remove">',
		'<circle r="9" />',
		'<path transform="scale(.6) translate(-16, -16)" d="M24.778,21.419 19.276,15.917 24.777,10.415 21.949,7.585 16.447,13.087 10.945,7.585 8.117,10.415 13.618,15.917 8.116,21.419 10.946,24.248 16.447,18.746 21.948,24.248z" />',
		'<title>Remove link.</title>',
		'</g>',
		'<g class="tool-options" event="link:options">',
		'<circle r="11" transform="translate(25)"/>',
		'<path fill="white" transform="scale(.55) translate(29, -16)" d="M31.229,17.736c0.064-0.571,0.104-1.148,0.104-1.736s-0.04-1.166-0.104-1.737l-4.377-1.557c-0.218-0.716-0.504-1.401-0.851-2.05l1.993-4.192c-0.725-0.91-1.549-1.734-2.458-2.459l-4.193,1.994c-0.647-0.347-1.334-0.632-2.049-0.849l-1.558-4.378C17.165,0.708,16.588,0.667,16,0.667s-1.166,0.041-1.737,0.105L12.707,5.15c-0.716,0.217-1.401,0.502-2.05,0.849L6.464,4.005C5.554,4.73,4.73,5.554,4.005,6.464l1.994,4.192c-0.347,0.648-0.632,1.334-0.849,2.05l-4.378,1.557C0.708,14.834,0.667,15.412,0.667,16s0.041,1.165,0.105,1.736l4.378,1.558c0.217,0.715,0.502,1.401,0.849,2.049l-1.994,4.193c0.725,0.909,1.549,1.733,2.459,2.458l4.192-1.993c0.648,0.347,1.334,0.633,2.05,0.851l1.557,4.377c0.571,0.064,1.148,0.104,1.737,0.104c0.588,0,1.165-0.04,1.736-0.104l1.558-4.377c0.715-0.218,1.399-0.504,2.049-0.851l4.193,1.993c0.909-0.725,1.733-1.549,2.458-2.458l-1.993-4.193c0.347-0.647,0.633-1.334,0.851-2.049L31.229,17.736zM16,20.871c-2.69,0-4.872-2.182-4.872-4.871c0-2.69,2.182-4.872,4.872-4.872c2.689,0,4.871,2.182,4.871,4.872C20.871,18.689,18.689,20.871,16,20.871z"/>',
		'<title>Link options.</title>',
		'</g>',
		'</g>'
	].join( '' )

} );
;/**
 * Created by dan bilauca on 11/3/2016.
 */
var TGE_Editor = TGE_Editor || {};
TGE_Editor.views = TGE_Editor.views || {};


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

(function ( production ) {

	var originalConsole = {
		log: (function ( c ) {
			return function ( v ) {
				c.log( v );
			};
		}( window.console ))
	};

	var console = {
		log: function ( v ) {
		}
	};

	window.console = production ? console : originalConsole;
})( TGE_Editor.debug_mode != 1 );


(function ( $ ) {
	$( function () {
		var $html = $( 'html' );
		$( 'html' ).css( {
			height: window.innerHeight - parseInt( $html.css( 'margin-top' ) ) - 4
		} );
		window.tge_app_view = new TGE_Editor.views.AppView;

		TGE_Editor.wistia = {};

		window._wq = window._wq || [];
		_wq.push( {
			id: "f6sh0ulno2", onReady: function ( video ) {
				TGE_Editor.wistia.start_quiz = video;
			}
		} );

	} );

})( jQuery );
