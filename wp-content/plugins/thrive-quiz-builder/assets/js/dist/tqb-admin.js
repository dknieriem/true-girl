/**
 * Created by Ovidiu on 8/30/2016.
 */
var ThriveQuizB = ThriveQuizB || {};
ThriveQuizB.models = ThriveQuizB.models || {};
ThriveQuizB.collections = ThriveQuizB.collections || {};

(function ( $ ) {

	$( function () {

		/**
		 * Sets Backbone to emulate HTTP requests for models
		 * HTTP_X_HTTP_METHOD_OVERRIDE set to PUT|POST|PATH|DELETE|GET
		 *
		 * @type {boolean}
		 */
		Backbone.emulateHTTP = true;

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
			}
		} );

		/**
		 * Quiz Model
		 */
		ThriveQuizB.models.Quiz = ThriveQuizB.models.Base.extend( {
			defaults: {
				ID: '',
				shortcode_code: '',
				type: '',
				results: [],
				minimum_results: 2,
				style: '',
				tpl: '',
				structure: [],
				state: ThriveQuizB.util.states.normal,
				validation: {
					valid: false,
					error: {
						qna: ThriveQuizB.t.NoQuestionError,
						results: ThriveQuizB.t.ResultPageError
					}
				}
			},
			parse: function ( data ) {
				data.results = new ThriveQuizB.collections.Options( data.results );

				var quiz_type = this.getQuizTypeText( data.type );
				data.quiz_type_text = quiz_type.text;
				data.quiz_type_label = quiz_type.label;

				if ( data.thrive_images ) {
					data.thrive_images = new ThriveQuizB.collections.ThriveImages( data.thrive_images );
				}

				return data;
			},
			getQuizTypeText: function ( type ) {
				var quiz_type = {
					text: ThriveQuizB.t.quiz_type_text_not_set,
					label: ThriveQuizB.t.quiz_type_text_not_set
				};
				if ( type === ThriveQuizB.data.quiz_types.personality ) {
					quiz_type.text = ThriveQuizB.t.quiz_type_text_personality;
					quiz_type.label = ThriveQuizB.t.quiz_type_label_personality;
				} else if ( type === ThriveQuizB.data.quiz_types.number ) {
					quiz_type.text = ThriveQuizB.t.quiz_type_text_number;
					quiz_type.label = ThriveQuizB.t.quiz_type_label_number;
				} else if ( type === ThriveQuizB.data.quiz_types.percentage ) {
					quiz_type.text = ThriveQuizB.t.quiz_type_text_percentage;
					quiz_type.label = ThriveQuizB.t.quiz_type_label_percentage;
				}

				return quiz_type;
			},
			url: function () {
				var url = ThriveQuizB.util.ajaxurl( 'action=tqb_admin_ajax_controller&route=quiz' );

				if ( this.get( 'ID' ) ) {
					url += '&ID=' + this.get( 'ID' );
				}

				return url;
			},
			getCode: function () {

				if ( this.get( 'shortcode_code' ).length ) {
					return this.get( "shortcode_code" );
				}

				var code = '[' + ThriveQuizB.shortcode_name + ' id=\'' + this.get( 'ID' ) + '\']';
				this.set( "shortcode_code", code );

				return this.get( "shortcode_code" );
			},
			/**
			 * Overwrite Backbone validation
			 * Return something to invalidate the model
			 *
			 * @param {Object} attrs
			 * @param {Object} options
			 */
			validate: function ( attrs, options ) {
				var errors = [];
				if ( ! attrs.post_title ) {
					errors.push( this.validation_error( 'post_title', ThriveQuizB.t.invalid_name ) );
				}

				if ( errors.length ) {
					return errors;
				}
			},
			get_settings_step: function () {
				if ( ! this.get( 'type' ) ) {
					return 'type';
				}

				if ( ! this.get( 'style' ) ) {
					return 'style';
				}
			},
			/**
			 * get the data required for the chart (impressions and conversion reported to time intervals)
			 * searches first for a local copy of the chart data
			 *
			 * @param {Function} cb callback to apply on successful data retrieval
			 * @returns mixed
			 */
			load_chart_data: function ( cb ) {
				if ( ! this.get( 'ID' ) ) {
					return null;
				}
				if ( this.has( 'chart_data' ) ) {
					return cb.call( null, this.get( 'chart_data' ) );
				}

				$.ajax( {
					url: this.url(),
					data: {
						custom: 'chart_data',
						_nonce: ThriveQuizB.admin_nonce
					},
					type: 'post',
					dataType: 'json'
				} ).done( _.bind( function ( r ) {
					this.set( 'chart_data', r );
					cb.call( null, this.get( 'chart_data' ) );
				}, this ) ).fail( _.bind( function () {
					cb.call( null, null );
				}, this ) );
			},
			/**
			 * get dummy data for displaying a "placeholder" chart when there is no impression / conversion registered
			 *
			 * @returns {Object}
			 */
			get_chart_dummy_data: function () {
				var _data = {};
				_data.conversions = [11, 8, 14, 11, 17, 12, 9, 19, 17, 11, 21, 13, 4];
				_data.impressions = [64, 58, 89, 85, 93, 75, 74, 83, 88, 72, 90, 82, 27];
				_data.labels = _.map( function () {
					return ''
				}, _.range( _data.impressions.length ) );

				return _data;
			},
			get_edit_route: function () {
				return '#dashboard/quiz/' + this.get( 'ID' );
			},
			get_report_route: function () {
				return '#reports/' + this.get( 'ID' );
			}
		} );

		/**
		 * Quiz Page Model
		 */
		ThriveQuizB.models.Page = ThriveQuizB.models.Base.extend( {
			defaults: {
				ID: ''
			},
			parse: function ( data ) {
				if ( data.variations ) {
					data.variations = new ThriveQuizB.collections.Variations( data.variations )
				}
				if ( data.archived_variations ) {
					data.archived_variations = new ThriveQuizB.collections.Variations( data.archived_variations )
				}

				return data;
			},
			url: function () {
				var url = ThriveQuizB.util.ajaxurl( 'action=tqb_admin_ajax_controller&route=page' );

				if ( this.get( 'ID' ) ) {
					url += '&ID=' + this.get( 'ID' );
				}

				return url;
			},
			checkVariationsContent: function ( ajax_params, form_data ) {
				if ( ! form_data ) {
					form_data = {};
				}
				var oAjaxParams = _.extend( {
					type: 'post',
					dataType: 'json',
					url: this.url() + '&custom_action=check_variations_content',
					data: form_data
				}, ajax_params );

				return jQuery.ajax( oAjaxParams );
			}
		} );

		ThriveQuizB.models.Variation = ThriveQuizB.models.Base.extend( {
			idAttribute: 'id',
			defaults: {
				id: '',
				quiz_id: '',
				page_id: '',
				parent_id: '',
				is_control: 0,
				post_title: '',
				post_status: ThriveQuizB.data.variation_status.publish,
				state_order: '',
				cache_impressions: 0,
				cache_optins: 0,
				cache_optin_conversion_rate: 'N/A',
				cache_social_shares: 0,
				cache_social_share_conversion_rate: 'N/A',
				content: ''
			},
			/**
			 * Overwrite Backbone validation
			 * Return something to invalidate the model
			 *
			 * @param {Object} attrs
			 * @param {Object} options
			 */
			validate: function ( attrs, options ) {
				var errors = [];
				if ( ! attrs.post_title && ! attrs.default_name ) {
					errors.push( this.validation_error( 'post_title', ThriveQuizB.t.invalid_name ) );
				}
				if ( errors.length ) {
					return errors;
				}
			},
			url: function () {
				var url = ThriveQuizB.util.ajaxurl( 'action=tqb_admin_ajax_controller&route=variation' );

				if ( this.get( 'id' ) ) {
					url += '&id=' + this.get( 'id' );
				}

				return url;
			},
			resetStatistics: function ( ajax_params, form_data ) {
				if ( ! form_data ) {
					form_data = {};
				}
				var oAjaxParams = _.extend( {
					type: 'post',
					dataType: 'json',
					url: this.url() + '&custom_action=reset_statistics',
					data: form_data
				}, ajax_params );

				return jQuery.ajax( oAjaxParams );
			},
			generateFirstVariation: function ( ajax_params, form_data ) {
				if ( ! form_data ) {
					form_data = {};
				}
				var oAjaxParams = _.extend( {
					type: 'post',
					dataType: 'json',
					url: this.url() + '&custom_action=generate_first_variation',
					data: form_data
				}, ajax_params );

				return jQuery.ajax( oAjaxParams );
			},
			cloneVariation: function ( ajax_params, form_data ) {
				if ( ! form_data ) {
					form_data = {};
				}
				var oAjaxParams = _.extend( {
					type: 'post',
					dataType: 'json',
					url: this.url() + '&custom_action=clone_variation',
					data: form_data
				}, ajax_params );

				return jQuery.ajax( oAjaxParams );
			},
			get_name: function () {
				var name;

				try {
					name = ThriveQuizB.data.quiz_structure_item_types[this.get( 'type' )].name;
				} catch ( e ) {
					return this.get( 'name' );
				}

				return name;
			}
		} );


		/**
		 * BreadcrumbLink model
		 */
		ThriveQuizB.models.BreadcrumbLink = ThriveQuizB.models.Base.extend( {
			defaults: {
				ID: '',
				hash: '',
				label: '',
				full_link: false
			},
			/**
			 * we pass only hash and label, and build the ID based on the label
			 *
			 * @param {object} att
			 */
			initialize: function ( att ) {
				if ( ! this.get( 'ID' ) ) {
					this.set( 'ID', att.label.split( ' ' ).join( '' ).toLowerCase() );
				}
				this.set( 'full_link', att.hash.match( /^http/ ) );
			},
			/**
			 *
			 * @returns {String}
			 */
			get_url: function () {
				return this.get( 'full_link' ) ? this.get( 'hash' ) : ( '#' + this.get( 'hash' ));
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
		 * Quizzes Collection
		 */
		ThriveQuizB.collections.Quizzes = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.Quiz,
			/**
			 * Used to sort the collection
			 *
			 * @param model
			 * @returns {*}
			 */
			comparator: function ( model ) {
				return parseInt( model.get( 'order' ) );
			},
			url: function () {
				return ThriveQuizB.util.ajaxurl( 'action=tqb_admin_ajax_controller&route=quizzes' );
			}
		} );

		/**
		 * Breadcrumb links collection
		 */
		ThriveQuizB.collections.Breadcrumbs = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.Base.extend( {
				defaults: {
					hash: '',
					label: ''
				}
			} ),
			/**
			 * helper function allows adding items to the collection easier
			 *
			 * @param {string} route
			 * @param {string} label
			 */
			add_page: function ( route, label ) {
				var _model = new ThriveQuizB.models.BreadcrumbLink( {
					hash: route,
					label: label
				} );
				return this.add( _model );
			}
		} );

		/**
		 * Option Model
		 */
		ThriveQuizB.models.Option = ThriveQuizB.models.Base.extend( {
			defaults: {}
		} );

		/**
		 * Structure Item Model
		 */
		ThriveQuizB.models.StructureItem = ThriveQuizB.models.Base.extend( {
			defaults: {
				image: null,
				type: null,
				name: null,
				mandatory: false,
				last: false,
				viewed: false
			}
		} );

		/**
		 * Structure items Collection
		 */
		ThriveQuizB.models.Structure = ThriveQuizB.models.Base.extend( {
			defaults: {
				splash: false,
				qna: false,
				optin: false,
				results: false
			},
			url: function () {
				return ThriveQuizB.util.ajaxurl( 'action=tqb_admin_ajax_controller&route=structure' );
			}
		} );

		/**
		 * Options Collections
		 */
		ThriveQuizB.collections.Options = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.Option
		} );

		/**
		 * Thrive Image Model
		 */
		ThriveQuizB.models.ThriveImage = ThriveQuizB.models.Base.extend( {
			defaults: {
				ID: '',
				state: 'normal'
			},
			url: function () {
				var _url = ajaxurl + '?action=' + TIE.admin_ajax_controller + '&route=image&_nonce=' + TIE.nonce;

				if ( parseInt( this.get( 'ID' ) ) ) {
					_url += '&ID=' + this.get( 'ID' );
				}

				return _url;
			},
			get_bg_style: function () {


				var _style = 'background-image: ',
					_template = ThriveQuizB.globals.badge_templates.findWhere( {key: this.get( 'settings' ).template} );

				if ( this.get( 'image_url' ) ) {
					_style += 'url(' + this.get( 'image_url' ) + ')';
				} else {
					_style += 'url(' + _template.get( 'thumb' ) + ')';
				}

				return _style;
			}
		} );

		/**
		 * Thrive Images Collection
		 */
		ThriveQuizB.collections.ThriveImages = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.ThriveImage
		} );

		/**
		 * Quiz Style Model
		 */
		ThriveQuizB.models.QuizStyle = ThriveQuizB.models.Base.extend( {
			idAttribute: 'ID',
			defaults: {
				ID: '',
				quiz_id: '',
				style: ''
			},
			url: function () {
				return ThriveQuizB.util.ajaxurl( 'action=tqb_admin_ajax_controller&route=style' );
			},
			validate: function ( attrs, options ) {
				var errors = [];
				if ( ! attrs.style ) {
					errors.push( this.validation_error( 'style', ThriveQuizB.t.invalid_name ) );
				}

				if ( errors.length ) {
					return errors;
				}
			}
		} );

		/**
		 * Quiz Type Model
		 */
		ThriveQuizB.models.QuizType = ThriveQuizB.models.Base.extend( {
			idAttribute: 'ID',
			defaults: {
				ID: '',
				type: '',
				results: [],
				minimum_results: 2
			},
			url: function () {
				return ThriveQuizB.util.ajaxurl( 'action=tqb_admin_ajax_controller&route=type' );
			},
			/**
			 * Overwrite Backbone validation
			 * Return something to invalidate the model
			 *
			 * @param {Object} attrs
			 * @param {Object} options
			 */
			validate: function ( attrs, options ) {
				var errors = [], hasEmptyResults = false;

				if ( attrs.type && attrs.type === ThriveQuizB.data.quiz_types.personality ) {
					if ( attrs.results.length < attrs.minimum_results ) {
						hasEmptyResults = true;
					} else {
						var filledResults = [];

						attrs.results.each( function ( model, index ) {
							if ( model.get( 'text' ) ) {
								filledResults.push( model.get( 'text' ) );
							}
						} );

						if ( filledResults.length < attrs.minimum_results ) {
							hasEmptyResults = true;
						}
					}

					if ( hasEmptyResults ) {
						errors.push( ThriveQuizB.t.minimum_results_warning );
						return errors;
					}
				}
			}
		} );

		/**
		 * Test Model
		 */
		ThriveQuizB.models.Test = ThriveQuizB.models.Base.extend( {
			idAttribute: 'id',
			defaults: function () {
				return {
					id: '',
					item_ids: [],
					auto_win_min_conversions: 100,
					auto_win_min_duration: 14,
					auto_win_chance_original: 95,
					conversion_goal: null,
					title: '',
					notes: '',
					page_id: null,
				}
			},

			validate: function ( attrs ) {
				var errors = [];
				if ( ! attrs.title ) {
					errors.push( this.validation_error( 'test_title', ThriveQuizB.t.TestTitleRequired ) );
				}

				if ( ! attrs.conversion_goal && attrs.is_results ) {
					errors.push( this.validation_error( 'conversion_goal', ThriveQuizB.t.ConversionGoalRequired ) );
					TVE_Dash.err( ThriveQuizB.t.ConversionGoalRequired );
				}

				if ( attrs.auto_win_enabled ) {
					if ( attrs.auto_win_min_conversions.length <= 0 ) {
						errors.push( this.validation_error( 'auto_win_min_conversions', ThriveQuizB.t.AutoWinMinConversionsRequired ) );
					} else if ( isNaN( attrs.auto_win_min_conversions ) || attrs.auto_win_min_conversions < 0 || attrs.auto_win_min_conversions != parseInt( attrs.auto_win_min_conversions, 10 ) ) {
						errors.push( this.validation_error( 'auto_win_min_conversions', ThriveQuizB.t.PositiveIntegerNumber ) );
					}

					if ( attrs.auto_win_min_duration.length <= 0 ) {
						errors.push( this.validation_error( 'auto_win_min_duration', ThriveQuizB.t.AutoWinMinDurationRequired ) );
					} else if ( isNaN( attrs.auto_win_min_duration ) || attrs.auto_win_min_duration < 0 || attrs.auto_win_min_duration != parseInt( attrs.auto_win_min_duration, 10 ) ) {
						errors.push( this.validation_error( 'auto_win_min_duration', ThriveQuizB.t.PositiveIntegerNumber ) );
					}

					if ( attrs.auto_win_chance_original.length <= 0 ) {
						errors.push( this.validation_error( 'auto_win_chance_original', ThriveQuizB.t.AutoWinChanceOriginalRequired ) );
					} else if ( isNaN( attrs.auto_win_chance_original ) || attrs.auto_win_chance_original > 100 || attrs.auto_win_chance_original < 0 ) {
						errors.push( this.validation_error( 'auto_win_chance_original', ThriveQuizB.t.PositivePercentNumber ) );
					}
				}

				if ( attrs.form_types && attrs.form_types.length < 2 ) {
					errors.push( this.validation_error( 'form_types', ThriveQuizB.t.MinimumTwoFormTypesAreRequired ) );
				}

				if ( errors.length ) {
					return errors;
				}
			},
			url: function () {
				return ThriveQuizB.util.ajaxurl( 'action=tqb_admin_ajax_controller&route=test' + '&id=' + this.get( 'id' ) );
			}
		} );

		/**
		 * Test Collection
		 */
		ThriveQuizB.collections.Tests = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.Test
		} );

		/**
		 * Test items model
		 */
		ThriveQuizB.models.TestItem = ThriveQuizB.models.Base.extend( {
			defaults: {
				ID: '',
				active: 1,
				conversions: 0,
				id: null,
				impressions: 0,
				is_control: 0,
				is_winner: 0,
				social_shares: 0,
				stopped_date: null,
				test_id: null,
				variation_id: null
			},
			url: function () {
				return ThriveQuizB.util.ajaxurl( 'action=tqb_admin_ajax_controller&route=test_item' );
			},
			getConversionRate: function () {
				var rate = parseInt( this.get( 'impressions' ) ) ? parseInt( this.get( 'optins_conversions' ) ) / parseInt( this.get( 'impressions' ) ) * 100 : 0;
				rate = ThriveQuizB.util.roundNumber( rate, 3 );
				rate = rate.toFixed( 2 );
				return rate;
			},
			getPercentageImprovementColor: function () {
				var _value = parseFloat( this.get( 'percentage_improvement' ) );
				if ( isNaN( _value ) ) {
					return '';
				}

				return _value < 0 ? ThriveQuizB.data.colors.red : (_value > 0 ? ThriveQuizB.data.colors.green : '');
			}
		} );

		/**
		 * Test items collection
		 */
		ThriveQuizB.collections.TestItems = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.TestItem,
			getTotals: function () {
				var impressions = this.getTotalImpressions(),
					conversions = this.getTotalConversions(),
					conversions_rate = this.getConversionRate();
				return {
					impressions: impressions,
					conversions: conversions,
					conversions_rate: conversions_rate
				};
			},
			getTotalImpressions: function () {
				var _impressions = 0;
				_.each( this.models, function ( test_item ) {
					_impressions += parseInt( test_item.get( 'impressions' ) );
				} );
				return _impressions;
			},
			getTotalConversions: function () {
				var _conversions = 0;
				_.each( this.models, function ( test_item ) {
					_conversions += parseInt( test_item.get( 'optins_conversions' ) );
				} );
				return _conversions;
			},
			getConversionRate: function () {
				var _rate = this.getTotalImpressions() !== 0 ? this.getTotalConversions() / this.getTotalImpressions() * 100 : 0;
				_rate = _rate.toFixed( 2 );

				return _rate;
			},
			/**
			 * returns the data and colors for the bar chart
			 */
			getCandleStickData: function () {
				var chartData = [];
				_.each( this.models, function ( item ) {
					var conversion_rate = parseInt( item.get( 'conversion_rate' ) );
					var impressions = parseInt( item.get( 'impressions' ) );
					if ( isNaN( conversion_rate ) || isNaN( impressions ) || impressions === 0 ) {
						chartData.push( [0, 0] );
					} else {
						var standard_deviation = Math.round( Math.sqrt( (conversion_rate * (100 - conversion_rate) / impressions) ) * 100 ) / 100;

						chartData.push( [
							conversion_rate - standard_deviation,
							conversion_rate + standard_deviation
						] );

					}
				} );

				/*
				 In chartData, now we have the starting and the ending of the bar. The control( at index 0 ) bar will always be grey.
				 For the other bars, it will be grey where it overlaps the control, red where it's lower then the control and green where it's higher.
				 We have 6 cases, 1. full red 2. red and grey 3. only grey 4. grey and green 5. only green 6. red, grey and green.
				 */
				var RED = ThriveQuizB.data.colors.red;
				var GREY = ThriveQuizB.data.colors.grey;
				var GREEN = ThriveQuizB.data.colors.green;

				var control = chartData[0];
				var newChartData = [];
				var chartColors = [];
				_.each( chartData, function ( item ) {
					var newItem = [];
					var colors = [];
					if ( item[0] < control[0] ) {
						colors.push( RED );
						newItem.push( item[0] );
						if ( item[1] < control[0] ) {
							//case 1
							newItem.push( item[1] );
						} else if ( item[1] < control[1] ) {
							//case 2
							colors.push( GREY );
							newItem.push( control[0] );
							newItem.push( item[1] );
						} else if ( control[1] < item[1] ) {
							//case 6
							colors.push( GREY );
							colors.push( GREEN );
							newItem.push( control[0] );
							newItem.push( control[1] );
							newItem.push( item[1] );
						}
					} else if ( item[0] < control[1] ) {
						colors.push( GREY );
						newItem.push( item[0] );
						if ( item[1] <= control[1] ) {
							//case 3
							newItem.push( item[1] );
						} else if ( control[1] < item[1] ) {
							//case 4
							colors.push( GREEN );
							newItem.push( control[1] );
							newItem.push( item[1] );
						}
					} else if ( control[1] < item[1] ) {
						//case 5
						colors.push( GREEN );
						newItem.push( item[0] );
						newItem.push( item[1] );
					}
					newChartData.push( newItem );
					chartColors.push( colors );
				} );
				return {chartData: newChartData, chartColors: chartColors};
			},

			getHighestRateItem: function () {
				var temp_rate = 0, highest_ID;
				this.each( function ( item ) {
					if ( parseFloat( item.getConversionRate() ) >= temp_rate ) {
						highest_ID = item.get( 'id' );
						temp_rate = parseFloat( item.getConversionRate() );
					}
				} );

				return temp_rate === 0 ? undefined : highest_ID;
			}
		} );

		ThriveQuizB.models.QuizUserAnswer = ThriveQuizB.models.Base.extend( {
			idAttribute: 'id',
			defaults: {
				id: null,
				question: '',
				answers: {}
			}
		} );

		ThriveQuizB.models.QuizUser = ThriveQuizB.models.Base.extend( {
			idAttribute: 'id',
			defaults: {
				id: null,
				random_identifier: null,
				date_started: null,
				social_badge_lin: null,
				email: null,
				ip_address: null,
				points: null,
				quiz_id: null,
				completed_quiz: null
			}
		} );

		/**
		 * Reporting Page Model
		 */
		ThriveQuizB.models.Reporting = ThriveQuizB.models.Base.extend( {
			defaults: {
				report_type: 'completions',
				quiz_id: null
			},
			url: function () {
				return ThriveQuizB.util.ajaxurl( 'action=tqb_admin_ajax_controller&route=reporting' );
			}
		} );

		/**
		 * Quiz Users Collection
		 */

		ThriveQuizB.models.CompletionQuiz = ThriveQuizB.models.Base.extend( {
			idAttribute: 'id',
			defaults: {
				id: null,
				name: '',
				count: null
			}
		} );

		ThriveQuizB.models.CompletionPagination = ThriveQuizB.models.Base.extend( {
			defaults: {
				currentPage: null,
				pageCount: null,
				totalItems: null,
				itemsPerPage: null,
				pagesToDisplay: null
			}
		} );

		ThriveQuizB.collections.CompletionTableModel = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.CompletionQuiz
		} );

		ThriveQuizB.models.QuestionsReportItem = ThriveQuizB.models.Base.extend( {
			defaults: {
				id: null,
				text: null,
				total: null,
				answers: {}
			}
		} );

		ThriveQuizB.collections.QuestionsReportItems = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.QuestionsReportItem
		} );

		ThriveQuizB.models.QuestionsReportAnswer = ThriveQuizB.models.Base.extend( {
			defaults: {
				count: null,
				percent: null,
				text: null
			}
		} );

		ThriveQuizB.collections.QuestionsReportAnswers = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.QuestionsReportAnswer
		} );

		/**
		 * Quiz Users Collection
		 */
		ThriveQuizB.collections.QuizUsers = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.QuizUser
		} );

		/**
		 * Quiz Users Answers Collection
		 */
		ThriveQuizB.collections.QuizUserAnswers = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.QuizUserAnswer,
			url: function () {
				return ThriveQuizB.util.ajaxurl( 'action=tqb_admin_ajax_controller&route=user_answers' );
			}
		} );

		/**
		 * Quiz Style Collection
		 */
		ThriveQuizB.collections.QuizStyles = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.Base
		} );

		/**
		 * Quiz Variations Collection
		 */
		ThriveQuizB.collections.Variations = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.Variation
		} );

		/**
		 * Pages Variations Collection
		 */
		ThriveQuizB.collections.Pages = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.Page
		} );

		/**
		 * Questions a user answered
		 */
		ThriveQuizB.models.UserQuestion = ThriveQuizB.models.Base.extend( {
			idAttribute: 'id',
			defaults: {
				id: '',
				quiz_id: null,
				image: '',
				q_type: null,
				text: ''
			}
		} );

		ThriveQuizB.collections.UserQuestions = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.UserQuestion
		} );

		/**
		 * Answers from a question a user answered
		 */
		ThriveQuizB.models.UserAnswer = ThriveQuizB.models.Base.extend( {
			defaults: {
				question_id: '',
				quiz_id: null,
				image: '',
				result_id: null,
				text: '',
				chosen: false
			}
		} );

		ThriveQuizB.collections.UserAnswers = ThriveQuizB.collections.Base.extend( {
			model: ThriveQuizB.models.UserAnswer
		} );

		ThriveQuizB.models.CompletionReport = ThriveQuizB.models.Base.extend( {
			idAttribute: 'ID',
			defaults: function () {
				return {
					interval: 'day',
					chart_data: [],
					chart_title: '',
					chart_x_axis: [],
					chart_y_axis: ''
				};
			}
		} );

		/**
		 * Test chart
		 */
		ThriveQuizB.models.ChartModel = ThriveQuizB.models.Base.extend( {
			idAttribute: 'ID',
			defaults: function () {
				return {
					interval: 'day',
					chart_data: [],
					chart_title: '',
					chart_x_axis: [],
					chart_y_axis: ''
				};
			},
			url: function () {
				return ThriveQuizB.util.ajaxurl( 'action=tqb_admin_ajax_controller&route=chartAPI&chart_type=testChart' + '&id=' + this.get( 'ID' ) + '&interval=' + this.get( 'interval' ) );
			}
		} );

		/**
		 * Completion chart
		 */
		ThriveQuizB.models.CompletionChartModel = ThriveQuizB.models.Base.extend( {
			idAttribute: 'quiz_id',
			defaults: function () {
				return {
					interval: 'day',
					quiz_id: '',
					chart_data: [],
					chart_title: '',
					chart_x_axis: [],
					chart_y_axis: ''
				};
			},
			url: function () {
				return ThriveQuizB.util.ajaxurl( 'action=tqb_admin_ajax_controller&route=completionchart&id=' + this.get( 'id' ) + '&interval=' + this.get( 'interval' ) + '&date=' + this.get( 'date' ) );
			}
		} );

		ThriveQuizB.models.UsersList = ThriveQuizB.models.Base.extend( {
			defaults: function () {
				return {
					current_page: 1,
					total_items: null,
					per_page: 10,
					total_pages: null,
					quiz_id: null
				};
			},
			url: function () {
				return ThriveQuizB.util.ajaxurl( 'action=tqb_admin_ajax_controller&route=users_reporting' );
			}
		} );

		ThriveQuizB.models.FlowReport = ThriveQuizB.models.Base.extend( {
			defaults: {
				splash: null,
				qna: null,
				optin: null,
				results: null,
				quiz_id: null
			}
		} );

		ThriveQuizB.models.FlowReportSection = ThriveQuizB.models.Base.extend( {
			defaults: {
				type: 'splash',
				data: null
			}
		} );
		/**
		 * Badge Templates Collection
		 */
		ThriveQuizB.collections.BadgeTemplates = ThriveQuizB.collections.Base.extend( {} );

		/**
		 * Settings model
		 */
		ThriveQuizB.models.Settings = ThriveQuizB.models.Base.extend( {
			url: function () {
				return ThriveQuizB.util.ajaxurl( 'action=tqb_admin_ajax_controller&route=settings' );
			},
			defaults: {
				tqb_promotion_badge: 1
			}
		} );

	} );

})( jQuery );
;/**
 * Created by Ovidiu on 8/30/2016.
 */
var ThriveQuizB = ThriveQuizB || {};
ThriveQuizB.views = ThriveQuizB.views || {};

(function ( $ ) {

	$( function () {

		/**
		 * remove tvd-invalid class for all inputs in the view's root element
		 *
		 * @returns {Backbone.View}
		 */
		Backbone.View.prototype.tvd_clear_errors = function () {
			this.$( '.tvd-invalid' ).removeClass( 'tvd-invalid' );
			this.$( 'select' ).trigger( 'tvdclear' );
			return this;
		};

		$( document ).keypress( function ( ev ) {
			if ( ev.which == 13 || ev.keyCode == 13 ) {
				if ( ! jQuery( 'a' ).is( ':focus' ) ) {
					if ( jQuery( '.tqb-detele-structure-card-modal' ).is( ':visible' ) ) {
						jQuery( '.tqb-detele-structure-card-action' ).focus().click();
					}
				}
			}
		} );

		/**
		 *
		 * @param {Backbone.Model|object} [model] backbone model or error object with 'field' and 'message' properties
		 *
		 * @returns {Backbone.View|undefined}
		 */
		Backbone.View.prototype.tvd_show_errors = function ( model ) {
			model = model || this.model;

			if ( ! model ) {
				return;
			}

			var err = model instanceof Backbone.Model ? model.validationError : model,
				self = this,
				$all = $();

			function show_error( error_item ) {
				if ( typeof error_item === 'string' ) {
					return TVE_Dash.err( error_item );
				}
				$all = $all.add( self.$( '[data-field=' + error_item.field + ']' ).addClass( 'tvd-invalid' ).each( function () {
					var $this = $( this );
					if ( $this.is( 'select' ) ) {
						$this.trigger( 'tvderror', error_item.message );
					} else {
						$this.next( 'label' ).attr( 'data-error', error_item.message )
					}
				} ) );
			}

			if ( $.isArray( err ) ) {
				_.each( err, function ( item ) {
					show_error( item );
				} );
			} else {
				show_error( err );
			}
			$all.not( '.tvd-no-focus' ).first().focus();
			/* if the first error message is not visible, scroll the contents to include it in the viewport. At the moment, this is only implemented for modals */
			this.scroll_first_error( $all.first() );

			return this;
		};


		/**
		 * scroll the contents so that the first errored input is visible
		 * currently this is only implemented for modals
		 *
		 * @param {Object} $input first input element that has the error
		 *
		 * @returns {Backbone.View}
		 */
		Backbone.View.prototype.scroll_first_error = function ( $input ) {
			if ( ! ( this instanceof TVE_Dash.views.Modal ) || ! $input.length ) {
				return this;
			}
			var input_top = $input.offset().top,
				content_top = this.$_content.offset().top,
				scroll_top = this.$_content.scrollTop(),
				content_height = this.$_content.outerHeight();
			if ( input_top >= content_top && input_top < content_height + content_top - 50 ) {
				return this;
			}

			this.$_content.animate( {
				'scrollTop': scroll_top + input_top - content_top - 40 // 40px difference
			}, 200, 'swing' );
		};

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
			},
			/**
			 *
			 * Instantiate and open a new modal which has the view constructor assigned and send params further along
			 *
			 * @param ViewConstructor View constructor
			 * @param params
			 */
			modal: function ( ViewConstructor, params ) {
				return TVE_Dash.modal( ViewConstructor, params );
			},
			bind_zclip: function () {
				TVE_Dash.bindZClip( this.$el.find( 'a.tvd-copy-to-clipboard' ) );
			}
		} );

		/**
		 * Dashboard view
		 */
		ThriveQuizB.views.Dashboard = ThriveQuizB.views.Base.extend( {
			className: 'tvd-container',
			template: TVE_Dash.tpl( 'dashboard' ),
			events: {
				'click .tqb-add-quiz-dashboard': 'addNew'
			},
			addNew: function () {
				this.modal( ThriveQuizB.views.ModalNewQuiz, {
					model: new ThriveQuizB.models.Quiz( {} ),
					collection: this.collection,
					width: '750px',
					in_duration: 200,
					out_duration: 0,
					templates: new Backbone.Collection( ThriveQuizB.quiz_templates )
				} );

				return this;
			},
			render: function () {
				this.$el.html( this.template( {} ) );

				var quizListView = new ThriveQuizB.views.QuizList( {
					el: this.$( '#tqb-quiz-list' ),
					collection: this.collection
				} );
				quizListView.render();

				TVE_Dash.hideLoader();

				return this;
			}
		} );

		/**
		 * Quiz List view
		 */
		ThriveQuizB.views.QuizList = ThriveQuizB.views.Base.extend( {
			events: {
				'click .tqb-add-quiz': 'addNew'
			},
			/**
			 * @param {Array} used for sortable
			 */
			itemViews: [],
			render: function () {
				this.collection.each( this.renderOne, this );
				var self = this;

				function show_position( event, ui ) {
					var $placeholder = $( ui.placeholder ),
						position = $placeholder.prevAll().not( ui.item ).length + 1;
					$placeholder.html( "<div class='tqb-inside-placeholder'><span>" + position + (
							ThriveQuizB.t.n_suffix[position] ? ThriveQuizB.t.n_suffix[position] : ThriveQuizB.t.n_th
						) + ' ' + ThriveQuizB.t.priority + "</span></div>" );
				}

				this.$el.sortable( {
					placeholder: 'ui-sortable-placeholder',
					items: '.tqb-quiz-item',
					forcePlaceholderSize: true,
					handle: '.tqb-drag-card',
					update: _.bind( self.updateOrder, this ),
					tolerance: 'pointer',
					change: show_position,
					start: function ( event, ui ) {
						show_position( event, ui );
						$( 'body' ).addClass( 'tqb-sorting' );
					},
					stop: function () {
						setTimeout( function () {
							$( 'body' ).removeClass( 'tqb-sorting' );
						}, 200 );
					}
				} );
				return this;
			},
			updateOrder: function () {
				var to_update = {};
				_.each( this.itemViews, function ( item ) {
					if ( item.model.get( 'order' ) != item.$el.index() ) {
						item.model.set( 'order', item.$el.index() );
						to_update[item.model.get( 'ID' )] = item.$el.index();
					}
				} );

				this.collection.sort();
				$.ajax( {
					type: 'post',
					url: ajaxurl,
					data: {
						action: ThriveQuizB.ajax_actions.admin_controller,
						route: 'quiz',
						custom: 'update_order',
						new_order: to_update,
						_nonce: ThriveQuizB.admin_nonce
					}
				} );

			},
			renderOne: function ( item ) {
				var $lastItem = this.$( '.tqb-quiz-item' ).last(),
					view = new ThriveQuizB.views.Quiz( {
						model: item,
						collection: this.collection
					} );

				if ( $lastItem.length ) {
					$lastItem.after( view.render().$el );
				} else {
					this.$el.prepend( view.render().$el );
				}
				this.itemViews.push( view );

				return this;
			},
			addNew: function () {
				this.modal( ThriveQuizB.views.ModalNewQuiz, {
					model: new ThriveQuizB.models.Quiz( {} ),
					collection: this.collection,
					width: '750px',
					in_duration: 200,
					out_duration: 0,
					templates: new Backbone.Collection( ThriveQuizB.quiz_templates )
				} );

				return this;
			}
		} );

		/**
		 * Quiz View
		 */
		ThriveQuizB.views.Quiz = ThriveQuizB.views.Base.extend( {
			initialize: function () {
				this.listenTo( this.model, 'change:state', this.renderState );
			},
			render: function () {
				this.renderState();

				return this;
			},
			renderState: function () {
				var state = this.model.get( 'state' );

				if ( ! ThriveQuizB.views['Quiz' + TVE_Dash.upperFirst( state ) + 'State'] ) {
					return;
				}

				var view = new ThriveQuizB.views['Quiz' + TVE_Dash.upperFirst( state ) + 'State']( {
					model: this.model,
					collection: this.collection
				} );

				view.render();
				this.$el.replaceWith( view.$el );
				this.setElement( view.$el );
			}
		} );

		/**
		 * Quiz Normal view
		 */
		ThriveQuizB.views.QuizNormalState = ThriveQuizB.views.Base.extend( {
			className: 'tvd-col tvd-s6 tvd-ms6 tvd-m4 tvd-l3 tqb-quiz-item', //TODO: implement this quiz item
			template: TVE_Dash.tpl( 'quiz/item' ),
			events: {
				'click .tqb-delete-quiz': 'setDeleteState',
				'click .tqb-edit-quiz-title': 'editTitle',
				'click .tqb-copy-shortcode': 'copyShortcodeModal'
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				this.$quizTitle = this.$( '.tvd-card-title' );

				return this;
			},
			setDeleteState: function () {
				this.$el.live_tooltip( 'destroy' );
				var delete_state = this.collection.findWhere( {state: 'delete'} );
				if ( delete_state ) {
					delete_state.set( 'state', ThriveQuizB.util.states.normal );
				}
				this.model.set( 'state', ThriveQuizB.util.states.delete );
			},
			/**
			 * Hides Title and shows Edit Title input
			 */
			editTitle: function () {
				var self = this,
					edit_btn = this.$( '.tqb-edit-quiz-title' ),
					edit_model = new Backbone.Model( {
						value: this.model.get( 'post_title' ),
						label: ThriveQuizB.t.quiz_name,
						required: true
					} );

				edit_btn.hide();

				var textEdit = new ThriveQuizB.views.TextEdit( {
					model: edit_model,
					tagName: 'div'
				} );
				this.$quizTitle.hide().after( textEdit.render().$el );
				edit_model.on( 'change:value', function () {
					self.saveTitle.apply( self, arguments );
					textEdit.remove();
					self.$quizTitle.show();
					edit_btn.show();
				} );
				edit_model.on( 'tqb_no_change', function () {
					self.$quizTitle.html( self.model.get( 'post_title' ) ).show();
					textEdit.remove();
					edit_btn.show();
				} );
				textEdit.focus();
			},
			/**
			 * Saves the new title and hides the input value
			 */
			saveTitle: function ( edit_model, new_value ) {
				var self = this;

				this.model.set( 'post_title', new_value );
				this.model.save( null, {
					success: function () {
						self.$quizTitle.html( new_value );
						TVE_Dash.success( ThriveQuizB.t.quiz_title_save_success_toast );
					},
					error: function () {
						TVE_Dash.err( ThriveQuizB.t.quiz_title_save_fail_toast );
					}
				} );
			},
			copyShortcodeModal: function () {
				var self = this;
				this.modal( ThriveQuizB.views.ModalCopyShortcode, {
					item: self.model,
					width: '800px'
				} );
			},
			/**
			 * render the chart displaying impressions and conversion rates
			 *
			 * @param {object} _data chart data (impressions, conversion_rates and labels)
			 */
			chart: function ( _data ) {
				var has_chart_data = true,
					$no_data_clone = this.$( '.tqb-chart-no-data' ).clone();
				if ( ! _data || _data.impressions.length < 2 ) {
					has_chart_data = false;
					_data = this.model.get_chart_dummy_data();
				}
				var y_axis = [
						{
							title: {
								text: ThriveQuizB.t.no_of_impressions
							}
						}
					],
					series = [
						{
							name: ThriveQuizB.t.impressions,
							type: 'line',
							data: _data.impressions
						}
					];
				if ( _data.conversions ) {
					y_axis.push( {
						labels: {
							format: '{value}'
						},
						title: {
							text: ThriveQuizB.t.no_of_conversions
						},
						opposite: true
					} );
					series.push( {
						name: ThriveQuizB.t.conversions,
						type: 'line',
						yAxis: 1,
						data: _data.conversions,
						tooltip: {
							valueSuffix: ''
						}
					} );
				}
				setTimeout( _.bind( function () {
					this.$( '.tqb-quiz-chart' ).highcharts( {
						colors: has_chart_data ? ['#3498db', '#47bb28'] : ['#fff', '#fff'],
						credits: {
							enabled: false
						},
						plotOptions: {
							line: {
								marker: {
									enabled: false
								}
							}
						},
						title: {
							text: ' '
						},
						xAxis: {
							categories: _data.labels,
							labels: {
								enabled: false
							}
						},
						yAxis: y_axis,
						tooltip: {
							shared: true
						},
						legend: {
							enabled: false
						},
						series: series
					} );
					if ( ! has_chart_data ) {
						this.$( '.tqb-quiz-chart' ).addClass( 'tvd-relative tqb-blurred' ).append( $no_data_clone.removeClass( 'tvd-hide' ) );
					}
				}, this ) );
			}
		} );


		/**
		 * Campaign Delete State View
		 */
		ThriveQuizB.views.QuizDeleteState = ThriveQuizB.views.Base.extend( {
			className: 'tvd-col tvd-s6 tvd-m4 tvd-ms6 tvd-l3 tqb-quiz-item',
			template: TVE_Dash.tpl( 'quiz/delete-state' ),
			events: {
				'click .tqb-delete-no': function () {
					this.model.set( 'state', ThriveQuizB.util.states.normal );
				},
				'click .tqb-delete-yes': 'yes',
				'keydown': 'keyAction'
			},
			initialize: function () {
				this.listenTo( this.collection, 'remove', this.remove );
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				var _this = this;
				_.defer( function () {
					_this.$( '.tve-delete-quiz-card' ).focus();
				} );
				return this;
			},
			keyAction: function ( e ) {
				var code = e.which;
				if ( code == 13 ) {
					this.yes();
				} else if ( code == 27 ) {
					this.model.set( 'state', ThriveQuizB.util.states.normal );
				}
			},
			yes: function () {
				TVE_Dash.cardLoader( this.$el );
				this.model.destroy( {
					wait: true,
					success: function () {
						TVE_Dash.success( ThriveQuizB.t.quiz_deleted_success_toast );
						ThriveQuizB.util.showHideNoQuizInfo();
					},
					error: function () {
						TVE_Dash.err( ThriveQuizB.t.quiz_deleted_fail_toast );
					},
					complete: function () {
						TVE_Dash.hideLoader();
					}
				} );

			}
		} );

		ThriveQuizB.views.EditQuiz = ThriveQuizB.views.Base.extend( {
			className: 'tvd-container',
			template: TVE_Dash.tpl( 'quiz/edit' ),
			events: {
				'click .tqb-edit-quiz-title': 'editTitle',
				'click .tqb-edit-quiz-type': 'editQuizType'
			},
			initialize: function () {
				this.model.on( 'tqb_style_changed', _.bind( function () {
					if ( this.model.get( 'type' ) ) {
						this.check_structure();
					}
					this.render();
				}, this ) );

				this.listenTo( this.model, 'change:type', function ( model ) {

					if ( this.model.get( 'style' ) ) {
						this.check_structure();
					}

					this.render();

				}, this );

				this.listenTo( this.model.get( 'thrive_images' ), 'add', this.render_badge_card );
			},
			render: function () {

				this.$el.html( this.template( {item: this.model} ) );
				this.$quizTitle = this.$( '.tqb-quiz-title' );

				if ( this.model.get( 'style' ) && this.model.get( 'type' ) ) {
					this.renderStructure();
				}

				this.render_type_card();
				this.render_style_card();
				this.render_badge_card();

				var self = this;
				if ( ! this.model.get( 'wizard_complete' ) ) {
					if ( this.model.get( 'type' ) &&
					     this.model.get( 'style' ) &&
					     this.model.get( 'structure' ).viewed &&
					     this.model.get( 'structure' ).viewed.results &&
					     this.model.get( 'structure' ).tge_question_number > 0 ) {

						$.ajax( {
							type: 'post',
							url: ajaxurl,
							data: {
								action: ThriveQuizB.ajax_actions.admin_controller,
								route: 'quiz',
								custom: 'wizard_complete',
								ID: this.model.get( 'ID' ),
								_nonce: ThriveQuizB.admin_nonce
							}, success: function ( result ) {
								if ( result ) {
									self.model.set( 'wizard_complete', 1 );
									TVE_Dash.success( ThriveQuizB.t.wizard_complete );
								}
							}
						} );
					}
				}

				this.bind_zclip();
				TVE_Dash.materialize( this.$el );

				return this;
			},
			check_structure: function () {
				var structure = this.model.get( 'structure' );
				if ( ! structure.length ) {
					this.renderStructure( true );
				}
			},
			render_type_card: function () {
				var aux = this.model.getQuizTypeText( this.model.get( 'type' ) );
				this.$el.find( '.tqb-quiz-type' ).text( aux.label );
				this.$el.find( '.tqb-quiz-card-summary' ).text( aux.text );
				this.$el.find( '.tqb-logo-holder i' ).attr( 'id', this.model.get( 'type' ) );

				if ( ( this.model.get( 'page_variations' ) > 0 || this.model.get( 'structure' ).tge_question_number ) && this.model.get( 'type' ) != ThriveQuizB.data.quiz_types.personality ) {
					this.$el.find( '.tqb-edit-quiz-type' ).attr( 'disabled', 'disabled' );
					this.$el.find( '.tqb-quiz-description' ).html( ThriveQuizB.t.type_can_not_change_txt );
				}
			},
			render_style_card: function () {
				var quiz_style_card = new ThriveQuizB.views.QuizStyle( {
					model: this.model,
					collection: new ThriveQuizB.collections.QuizStyles( ThriveQuizB.quiz_styles )
				} );
				this.$( '#tqb-quiz-style-card' ).html( quiz_style_card.render().$el );
			},
			render_badge_card: function () {
				var $el = this.$( '#tqb-badge-card' ),
					badge_card = new ThriveQuizB.views.Badge( {
						el: $el,
						model: this.model.get( 'thrive_images' ).first(),
						collection: this.model.get( 'thrive_images' )
					} );
				badge_card.render();
			},
			renderStructure: function ( save ) {
				var self = this;

				if ( this.model.get( 'style' ) ) {
					var attributes = this.model.get( 'structure' ),
						structure = null;
					if ( this.structure_view ) {
						this.structure_view.remove();
					}
					if ( typeof attributes.ID !== 'undefined' ) {
						structure = new ThriveQuizB.models.Structure( attributes );
						this.structure_view = new ThriveQuizB.views.QuizStructure( {
							model: structure
						} );
						this.$el.find( '#tqb-quiz-structure-row' ).append( this.structure_view.render().$el );
						setTimeout( function () {
							ThriveQuizB.util.bind_wistia();
						}, 0 );
					} else {
						var attributes;
						ThriveQuizB.quiz_templates.forEach( function ( item, index ) {
							if ( item.id === self.model.get( 'tpl' ) ) {
								attributes = item;
							}
						} );

						attributes.ID = self.model.get( 'ID' );
						structure = new ThriveQuizB.models.Structure( attributes );
						if ( save ) {
							structure.save( null, {
								success: function ( model, response ) {
									self.structure_view = new ThriveQuizB.views.QuizStructure( {
										model: model
									} );
									self.$el.find( '#tqb-quiz-structure-row' ).append( self.structure_view.render().$el );
									self.model.set( 'structure', response );
									setTimeout( function () {
										ThriveQuizB.util.bind_wistia();
									}, 0 );
								},
								error: function () {
									TVE_Dash.err( ThriveQuizB.t.quiz_structure_save_fail_toast );
								}
							} );
						}

					}

				}
			},
			/**
			 * Hides Title and shows Edit Title input
			 */
			editTitle: function () {
				var self = this,
					edit_btn = this.$el.find( '.tqb-edit-quiz-title' ),
					edit_model = new Backbone.Model( {
						value: this.model.get( 'post_title' ),
						label: ThriveQuizB.t.quiz_name,
						required: true
					} );
				edit_btn.hide();
				edit_model.on( 'change:value', function () {
					self.saveTitle.apply( self, arguments );
					self.$quizTitle.show();
					textEdit.remove();
					edit_btn.show();
				} );
				edit_model.on( 'tqb_no_change', function () {
					self.$quizTitle.html( self.model.get( 'post_title' ) ).show();
					textEdit.remove();
					edit_btn.show();
				} );

				var textEdit = new ThriveQuizB.views.TextEdit( {
					model: edit_model,
					tagName: 'div'
				} );

				this.$quizTitle.hide().after( textEdit.render().$el );
				textEdit.focus();
			},
			/**
			 * Open Modal to edit the quiz type
			 */
			editQuizType: function () {
				var _modal_step = 0;
				if ( ( this.model.get( 'page_variations' ) > 0 || this.model.get( 'structure' ).tge_question_number ) && this.model.get( 'type' ) != ThriveQuizB.data.quiz_types.personality ) {
					TVE_Dash.err( ThriveQuizB.t.CanNotEditQuizConfig );
					return;
				} else if ( this.model.get( 'type' ) == ThriveQuizB.data.quiz_types.personality && ( this.model.get( 'page_variations' ) > 0 || this.model.get( 'structure' ).tge_question_number ) ) {
					_modal_step ++;
				}

				var model = new ThriveQuizB.models.QuizType( {
					'ID': this.model.get( 'ID' ),
					'type': this.model.get( 'type' ),
					'results': this.model.get( 'results' )
				} );

				this.modal( ThriveQuizB.views.ModalEditQuizType, {
					model: model,
					quiz_model: this.model,
					types: new Backbone.Collection( ThriveQuizB.quiz_types ),
					width: '800px',
					modal_step: _modal_step,
					'max-width': '60%'
				} );
			},
			/**
			 * Saves the new title and hides the input value
			 */
			saveTitle: function ( edit_model, new_value ) {
				var self = this;

				this.model.set( {
					post_title: new_value
				} );

				this.model.save( null, {
					success: function ( model, response ) {
						self.$quizTitle.html( new_value );
						try {
							var quiz = ThriveQuizB.globals.quizzes.findWhere( {ID: self.model.get( 'ID' )} );
							if ( quiz instanceof ThriveQuizB.models.Quiz ) {
								quiz.set( 'post_title', new_value );
							}
						} catch ( error ) {
							console.log( 'Error: ' + error );
						}
						TVE_Dash.success( ThriveQuizB.t.quiz_title_save_success_toast );
					},
					error: function ( model, response ) {
						TVE_Dash.err( ThriveQuizB.t.quiz_title_save_fail_toast );
					}
				} );
			}
		} );

		ThriveQuizB.views.TextEdit = ThriveQuizB.views.Base.extend( {
			className: 'tvd-input-field tqb-inline-edit',
			template: TVE_Dash.tpl( 'textedit' ),
			events: {
				'keyup input': 'keyup',
				'change input': function () {
					if ( ! $.trim( this.input.val() ) ) {
						this.input.addClass( 'tvd-invalid' );
						return false;
					}
					this.model.set( 'value', this.input.val() );
					return false;
				},
				'blur input': function () {
					this.model.trigger( 'tqb_no_change' );
				}
			},
			keyup: function ( event ) {
				if ( event.which === 27 ) {
					this.model.trigger( 'tqb_no_change' );
				}
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				this.input = this.$( 'input' );

				return this;
			},
			focus: function () {
				this.input.focus().select();
			}
		} );


		/**
		 * breadcrumbs view - renders breadcrumb links
		 */
		ThriveQuizB.views.Breadcrumbs = ThriveQuizB.views.Base.extend( {
			el: $( '#tqb-breadcrumbs-wrapper' )[0],
			template: TVE_Dash.tpl( 'breadcrumbs' ),
			/**
			 * setup collection listeners
			 */
			initialize: function () {
				this.$title = $( 'head > title' );
				this.original_title = this.$title.html();
				this.listenTo( this.collection, 'change', this.render );
				this.listenTo( this.collection, 'add', this.render );
			},
			/**
			 * render the html
			 */
			render: function () {
				this.$el.empty().html( this.template( {links: this.collection} ) );
			}
		} );

		/**
		 *  Option Input Multiple View
		 */
		ThriveQuizB.views.OptionResultInput = ThriveQuizB.views.Base.extend( {
			className: 'tqb-option-input tvd-collapse tvd-row',
			template: TVE_Dash.tpl( 'result-item' ),
			events: {
				'click .tqb-remove-input': 'remove_option',
				'keyup input': 'validate',
				'blur input': 'validate',
				'change input': function ( event ) {
					$( event.currentTarget ).removeClass( 'tvd-invalid' );
				}
			},
			initialize: function ( options ) {
				this.quiz_model = options.quiz_model;
//				this.collection = options.quiz_model.get( 'results' );
				if ( options.item ) {
					this.item = options.item;
				}
			},
			render: function () {
				this.$el.append( this.template( {item: this.category} ) );
				return this;
			},
			remove_option: function () {
				this.quiz_model.get( 'results' ).remove( this.model );
				this.remove();
				this.quiz_model.trigger( 'tqb_check_for_empty_inputs' );
			},
			validate: function ( event ) {
				var $target = $( event.currentTarget ), hasEmptyValues = false;
				$target.attr( 'data-value', event.currentTarget.value );

				this.model.set( {'text': event.currentTarget.value} );
				if ( ! this.model.get( 'text' ) ) {
					this.quiz_model.trigger( 'tqb_results_disabled_add_new_result' );
				}
				this.quiz_model.get( 'results' ).add( this.model );
				this.quiz_model.get( 'results' ).each( function ( model ) {
					if ( ! model.get( 'text' ) ) {
						hasEmptyValues = true;
					}
				}, this );

				if ( false === hasEmptyValues ) {  //&& this.quiz_model.get( 'results' ).length >= this.quiz_model.get( 'minimum_results' )
					this.quiz_model.trigger( 'tqb_results_enable_add_new_result' );
					if ( event.which == 13 ) {
						this.quiz_model.trigger( 'tqb_add_result' );
					}
				} else {
					if ( event.which == 13 ) {
						this.quiz_model.trigger( 'tqb_result_enter_pressed', $target );
					}
				}

				return false;
			}
		} );

		/**
		 * New Social Share Badge Button
		 */
		ThriveQuizB.views.Badge = ThriveQuizB.views.Base.extend( {
			initialize: function () {
				this.listenTo( this.model, 'change:state', this.render_state );
			},
			render: function () {
				this.render_state();
			},
			render_state: function () {
				var state;

				if ( this.model && this.model.get( 'settings' ).template ) {
					state = this.model.get( 'state' );
				} else {
					state = 'add';
				}

				var view_name = 'Badge' + TVE_Dash.upperFirst( state ) + 'State';

				if ( ! ThriveQuizB.views[view_name] ) {
					return;
				}

				var view = new ThriveQuizB.views[view_name]( {
					model: this.model,
					collection: this.collection
				} );

				view.render();

				this.$el.replaceWith( view.$el );
				this.setElement( view.$el );
			}
		} );

		ThriveQuizB.views.BadgeNormalState = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'badge/card' ),
			className: 'tvd-col tvd-s12 tvd-m4 tvd-l4',
			id: 'tqb-badge-card',
			events: {
				'click #tqb-remove-badge': 'remove'
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );

				return this;
			},
			remove: function () {
				this.model.set( 'state', 'delete' );
			}
		} );

		ThriveQuizB.views.BadgeAddState = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'badge/add' ),
			className: 'tvd-col tvd-s12 tvd-m4 tvd-l4 tvd-pointer',
			id: 'tqb-badge-card',
			events: {
				'click #tqb-add-badge': 'add_badge'
			},
			render: function () {
				this.$el.html( this.template( {
					item: this.model
				} ) );

				return this;
			},
			add_badge: function () {

				/**
				 * If quiz has a image model
				 * we do not open the modal
				 */
				if ( this.model ) {
					return;
				}

				/**
				 * open modal for already created quizzes which have this.model undefined
				 */
				this.modal( ThriveQuizB.views.ModalBadgeTemplates, {
					collection: this.collection,
					'max-width': '80%'
				} );
			}
		} );

		/**
		 * Campaign Delete State View
		 */
		ThriveQuizB.views.BadgeDeleteState = ThriveQuizB.views.Base.extend( {
			className: 'tvd-col tvd-s12 tvd-m4 tvd-l4',
			template: TVE_Dash.tpl( 'badge/delete' ),
			id: 'tqb-badge-card',
			events: {
				'click .tqb-delete-no': function () {
					this.model.set( 'state', ThriveQuizB.util.states.normal );
				},
				'click .tqb-delete-yes': 'yes',
				'keydown': 'keyAction'
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );

				return this;
			},
			keyAction: function ( e ) {
				var code = e.which;
				if ( code == 13 ) {
					this.yes();
				} else if ( code == 27 ) {
					this.model.set( 'state', ThriveQuizB.util.states.normal );
				}
			},
			yes: function () {
				var self = this;
				TVE_Dash.cardLoader( this.$el );

				/**
				 * we always need to have a thrive_image sub-post for the quiz
				 * to redirect user to this post, to edit the image
				 */
				var image = new ThriveQuizB.models.ThriveImage( {
					post_parent: ThriveQuizB.globals.quiz.get( 'ID' )
				} );

				this.model.destroy( {
					wait: true,
					success: function () {
						/**
						 * delete the existing and add a new one
						 * and push it to collection, to render the proper card html
						 */
						image.save( null, {
							success: function ( model, response, options ) {
								self.collection.push( model );
								TVE_Dash.success( ThriveQuizB.t.badge_deleted_success );
							}
						} );
					}
				} );
			}
		} );

		/**
		 * Quiz Style view
		 */
		ThriveQuizB.views.QuizStyle = ThriveQuizB.views.Base.extend( {
			className: 'tvd-card',
			events: {
				'click': 'editQuizStyle'
			},
			render: function () {
				if ( this.model.get( 'style' ).length === 0 ) {
					this.render_new_button();
				} else {
					this.render_card_content()
				}

				TVE_Dash.materialize( this.$el );

				return this;
			},
			render_card_content: function () {
				var _template = TVE_Dash.tpl( 'quiz/style/card' );

				this.$el.html( _template( {
					model: this.collection.findWhere( {id: this.model.get( 'style' )} )
				} ) );

				this.$el.addClass( 'tvd-white' );
			},
			render_new_button: function () {
				var _template = TVE_Dash.tpl( 'quiz/style/new-button' ),
					_label = ThriveQuizB.t.choose_quiz_style;

				this.$el.html( _template( {
					item: new Backbone.Model( {label: _label} ),
					model: this.model
				} ) );

				this.$el.addClass( 'tvd-medium-xxsmall tvd-card-new tvd-valign-wrapper tvd-relative tqb-new-button' );
			},
			editQuizStyle: function () {
				this.modal( ThriveQuizB.views.ModalEditQuizStyle, {
					collection: this.collection,
					model: this.model,
					styles: [],
					width: '800px',
					'max-width': '60%'
				} );
			}
		} );

		/**
		 * Quiz Style Item
		 */
		ThriveQuizB.views.QuizStyleItem = ThriveQuizB.views.Base.extend( {
			className: 'tvd-row tvd-no-margin-bottom tvd-collapse tvd-pointer tqb-quiz-style-item',
			template: TVE_Dash.tpl( 'quiz/style/item' ),
			render: function ( selected ) {

				this.$el.html( this.template( {
					item: this.model,
					selected: selected === true
				} ) );

				this.$el.attr( 'data-style', this.model.get( 'id' ) );

				if ( this.model.get( 'tqb_splash' ) ) {
					this.render_page( new Backbone.Model( this.model.get( 'tqb_splash' ) ) );
				}

				if ( this.model.get( 'tqb_qna' ) ) {
					this.render_page( new Backbone.Model( this.model.get( 'tqb_qna' ) ) );
				}

				if ( this.model.get( 'tqb_optin' ) ) {
					this.render_page( new Backbone.Model( this.model.get( 'tqb_optin' ) ) );
				}

				if ( this.model.get( 'tqb_results' ) ) {
					this.render_page( new Backbone.Model( this.model.get( 'tqb_results' ) ) );
				}

				TVE_Dash.materialize( this.$el );

				return this;
			},
			render_page: function ( page_model ) {
				var view = new ThriveQuizB.views.StyleItemCard( {
					el: this.$( '.tbq-style-pages' ),
					model: page_model
				} );
				view.render();
			}
		} );

		/**
		 * The Page Variation Dashboard
		 */
		ThriveQuizB.views.QuizPageVariations = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'variations/dashboard' ),
			events: {
				'click .tqb-add-new-form': 'addNewForm',
				'click .tqb-toggle-display': 'toggleDisplay',
				'click .tqb-start-variation-test': 'startVariationTestPopup'
			},
			initialize: function ( data ) {
				this.completed_tests = data.completed_tests;
				this.model.on( 'tqb_variation_removed', _.bind( function () {
					this.render();
				}, this ) );
				this.model.on( 'tqb_variation_added', _.bind( function () {
					this.render();
				}, this ) );
				this.model.on( 'tqb_completed_test_removed', _.bind( function () {
					this.render();
				}, this ) );
				this.model.on( 'adjust_completed_tests_counter', _.bind( function () {
					this.$el.find( '#tqb-completed-tests-count' ).html( '(' + this.completed_tests.length + ')' );
				}, this ) );
			},
			render: function () {
				this.$el.html( this.template( {model: this.model} ) );
				this.toggleLogic();
				this.renderVariationList();
				this.renderCompletedtests();
				this.renderArchivedVariationList();
				TVE_Dash.materialize( this.$el );
				return this;
			},
			renderCompletedtests: function () {
				if ( this.completed_tests.length ) {
					var completed_tests = new ThriveQuizB.views.CompletedTestItemList( {
						model: this.model,
						collection: this.completed_tests,
						el: this.$el.find( '#tqb-completed-test-list' )
					} );
					completed_tests.render();
					this.$el.find( '#tqb-completed-tests-count' ).html( '(' + this.completed_tests.length + ')' );
					this.$el.find( '#tqb-completed-tests' ).show();
				}
			},
			renderVariationList: function () {
				if ( this.variation_list ) {
					this.variation_list.remove();
				}
				this.variation_list = new ThriveQuizB.views.VariationList( {
					el: this.$( '#tqb-page-variations-list' ),
					collection: this.model.get( 'variations' ),
					model: this.model
				} );
				this.$el.find( '#tqb-page-variations-list' ).append( this.variation_list.render().$el );
			},
			renderArchivedVariationList: function () {
				if ( this.model.get( 'archived_variations' ).length <= 0 ) {
					return;
				}

				if ( this.archived_variation_list ) {
					this.archived_variation_list.remove();
				}

				this.archived_variation_list = new ThriveQuizB.views.VariationList( {
					el: this.$( '#tqb-archived-page-variations-list' ),
					collection: this.model.get( 'archived_variations' ),
					model: this.model
				} );
				this.$el.find( '#tqb-archived-page-variations-list' ).append( this.archived_variation_list.render().$el );
			},
			toggleDisplay: function ( e ) {
				var $elem = jQuery( e.currentTarget ),
					collapsed = $elem.hasClass( 'collapsed' );
				jQuery( $elem.data( 'target' ) )[collapsed ? 'slideDown' : 'slideUp']( 200 );
				$elem.toggleClass( 'collapsed' );
				$elem.toggleClass( 'hover' );
			},
			toggleLogic: function () {
				this.$el.find( '#tqb-archived-forms' )[this.model.get( 'archived_variations' ).length ? 'show' : 'hide']();
			},
			addNewForm: function () {
				this.modal( ThriveQuizB.views.ModalAddVariation, {
					collection: this.model.get( 'variations' ),
					model: new ThriveQuizB.models.Variation( {
						name: this.model.get( 'post_title' ),
						page_id: this.model.get( 'ID' ),
						quiz_id: this.model.get( 'post_parent' )
					} ),
					width: '800px',
					'max-width': '60%'
				} );
			},
			startVariationTestPopup: function ( event ) {
				var self = this;

				TVE_Dash.showLoader();
				this.model.checkVariationsContent( {
					success: function ( response ) {
						self.$el.find( '.tvd-collection-item' ).removeClass( 'tve-form-no-content' );
						if ( response.length > 0 ) {
							response.forEach( function ( value, index ) {
								self.$el.find( "[data-id='" + value + "']" ).addClass( 'tve-form-no-content' );
							} );

							TVE_Dash.err( ThriveQuizB.t.MissingFormContent );
						} else {
							var ids = [],
								variations = self.model.get( 'variations' );
							variations.each( function ( model ) {
								ids.push( model.get( 'id' ) );
							} );

							var model = new ThriveQuizB.models.Test( {
								item_ids: ids,
								page_id: self.model.get( 'ID' ),
								is_results: self.model.get( 'post_type' ) == ThriveQuizB.data.quiz_structure_item_types.results.key
							} );
							self.modal( ThriveQuizB.views.ModalVariationTest, {
								model: model,
								width: '800px',
								'max-width': '60%'
							} );
						}
					},
					error: function () {
						//TODO: error handling
					},
					complete: function () {
						TVE_Dash.hideLoader();
					}
				}, {'quiz_id': this.model.get( 'post_parent' )} );
			}
		} );


		/**
		 * The Page's Running Test Dashboard
		 */
		ThriveQuizB.views.QuizPageTest = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'tests/view' ),
			events: {
				'click .tqb-toggle-display': 'toggleDisplay',
			},
			initialize: function ( data ) {
				this.completed_tests = data.completed_tests;

				var index = 0;
				this.model.get( 'test_items' ).forEach( function ( element ) {
					element.color = ThriveQuizB.chart_colors[index % ThriveQuizB.chart_colors.length];
					index ++;
				} );
				this.model.get( 'stopped_items' ).forEach( function ( element ) {
					element.color = ThriveQuizB.chart_colors[index % ThriveQuizB.chart_colors.length];
				} );

				this.model.set( 'test_items', new ThriveQuizB.collections.TestItems( this.model.get( 'test_items' ) ) );
				this.model.set( 'stopped_items', new ThriveQuizB.collections.TestItems( this.model.get( 'stopped_items' ) ) );
			},
			render: function () {
				this.$el.html( this.template( {test: this.model} ) );
				this.model.get( 'test_items' ).each( this.renderOne, this );
				this.renderTotal();
				this.renderStoppedItems();
				this.renderChart();
				this.renderCompletedtests();
				if ( this.model.get( 'status' ) ) {
					this.renderOptions();
				}
				this.displayNoDataOverlay( this.model.get( 'test_items' ) );
				TVE_Dash.materialize( this.$el );
				return this;
			},
			renderStoppedItems: function () {
				if ( this.model.get( 'stopped_items' ).length ) {
					var stoppedItems = new ThriveQuizB.views.TestStoppedItemList( {
						model: this.model,
						collection: this.model.get( 'stopped_items' ),
						el: this.$el.find( '#tqb-form-test-stopped-items' )
					} );
					stoppedItems.render();
				}
			},
			renderChart: function () {
				var chartModel = new ThriveQuizB.models.ChartModel( {
					ID: this.model.get( 'id' ),
					start_date: this.model.get( 'date_started' ),
					interval: 'day'
				} );

				var _chart = new ThriveQuizB.views.ChartView( {
					model: chartModel,
					el: this.$el.find( '.tve-chart-interval' )
				} );
				_chart.drawCandleStick( this.model.get( 'test_items' ), 'tve-candle-stick-chart' );
				if ( this.model.get( 'stopped_items' ) ) {
					_chart.drawCandleStick( this.model.get( 'stopped_items' ), 'tve-candle-stick-chart-stopped' );
				}
				chartModel.fetch();
			},
			renderOptions: function () {
				var options = new ThriveQuizB.views.TestOptions( {
					model: this.model,
					collection: this.model.get( 'test_items' ),
					el: this.$el.find( "#tqb-test-options" )
				} );

				options.render();
			},
			renderCompletedtests: function () {
				if ( this.completed_tests.length ) {
					var completed_tests = new ThriveQuizB.views.CompletedTestItemList( {
						model: this.model,
						collection: this.completed_tests,
						el: this.$el.find( '#tqb-completed-test-list' )
					} );
					completed_tests.render();
					this.$el.find( '#tqb-completed-tests-count' ).html( '(' + this.completed_tests.length + ')' )
					this.$el.find( '#tqb-completed-tests' ).show();
				}
			},
			renderTotal: function () {
				var total_view = new ThriveQuizB.views.TestItemsTotals( {
					model: this.model,
					collection: this.model.get( 'test_items' ),
					el: this.$el.find( "#tqb-test-items-total" )
				} );

				total_view.render();
			},
			renderOne: function ( item, index ) {
				item.set( 'show_graph', true );
				item.set( 'parent_test_running', this.model.get( 'status' ) );
				var test_item = new ThriveQuizB.views.TestItem( {
					model: item,
					collection: this.model.get( 'test_items' )
				} );
				this.$el.find( '#tqb-form-test-items' ).append( test_item.render().$el );
			},
			toggleDisplay: function ( e ) {
				var $elem = jQuery( e.currentTarget ),
					collapsed = $elem.hasClass( 'collapsed' );
				jQuery( $elem.data( 'target' ) )[collapsed ? 'slideDown' : 'slideUp']( 200 );
				$elem.toggleClass( 'collapsed' );
				$elem.toggleClass( 'hover' );
			},
			displayNoDataOverlay: function ( items ) {
				var noData = true;
				items.each( function ( item ) {
					if ( parseInt( item.get( 'impressions' ) ) > 0 ) {
						noData = false;
						return;
					}
				} );
				if ( noData ) {
					this.$el.find( '.tve-chart-overlay' ).show();
				} else {
					this.$el.find( '.tve-chart-overlay' ).hide();
				}
			}
		} );

		ThriveQuizB.views.CompletedTestItemList = ThriveQuizB.views.Base.extend( {
			initialize: function () {
				this.collection.on( 'tqb_test_removed', _.bind( function () {
					this.render();
					if ( this.collection.length == 0 ) {
						this.model.trigger( 'tqb_completed_test_removed' );
					} else {
						this.model.trigger( 'adjust_completed_tests_counter' );
					}
				}, this ) );
			},
			addOne: function ( item, index ) {
				var color = ThriveQuizB.data.colors[item.get( 'index' ) % ThriveQuizB.data.colors.length];
				item.set( 'show_graph', true );
				item.set( 'parent_test_running', this.model.get( 'status' ) );
				item.set( 'color', color );
				var v = new ThriveQuizB.views.CompletedTestItem( {
					model: item,
					collection: this.collection
				} );
				this.$el.append( v.render().el );
			},
			addAll: function ( items ) {
				items.each( this.addOne, this );
			},
			render: function () {
				var header = this.$el.find( '.tvd-collection-header' );
				this.$el.html( header );
				this.addAll( this.collection );
				return this;
			}
		} );

		ThriveQuizB.views.CompletedTestItem = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'tests/completed-item' ),
			tagName: 'li',
			className: 'tvd-collection-item tvd-gray',
			events: {
				'click .tqb-test-delete': 'deleteTest',
				'click .tqb-view-test': 'viewTest'
			},
			initialize: function ( options ) {
				this.highest_id = options.highest_id;
				this.model.on( 'tqb_test_removed', _.bind( function () {
					this.collection.trigger( 'tqb_test_removed' );
				}, this ) );
			},
			render: function () {
				this.$el.html( this.template( {test: this.model} ) );
				if ( this.highest_id === this.model.get( 'id' ) ) {
					this.$el.addClass( 'highlight' );
				}
				return this;
			},
			viewTest: function () {
				ThriveQuizB.router.navigate( '#dashboard/test/' + this.model.get( 'id' ), {trigger: true} );
			},
			deleteTest: function () {
				this.modal( ThriveQuizB.views.ModalDeleteTest, {
					width: '800px',
					'max-width': '60%',
					model: this.model
				} );
			}
		} );

		ThriveQuizB.views.TestOptions = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'tests/options' ),
			events: {
				'click .tqb-stop-test': 'stopTest'
			},
			stopTest: function () {
				this.modal( ThriveQuizB.views.ModalStopVariationTest, {
					title: ThriveQuizB.t.ChooseWinner,
					width: 1230,
					'max-width': '90%',
					model: this.model,
					collection: this.collection
				} );
			},
			render: function () {
				this.$el.append( this.template( this.model.toJSON() ) );
			}
		} );

		ThriveQuizB.views.TestItemsTotals = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'tests/test-total' ),
			events: {
				'click .tqb-edit-test': 'editTest'
			},
			initialize: function () {
				this.listenTo( this.model, 'auto_win_changed', _.bind( this.render, this ) );
			},
			render: function () {
				this.$el.html( this.template( {
					totals: this.collection.getTotals(),
					test: this.model
				} ) );
			},
			editTest: function () {
				this.modal( ThriveQuizB.views.ModalEditVariationTest, {
					title: ThriveQuizB.t.AutomaticWinnerSettings,
					model: this.model,
					width: '800px',
					'max-width': '60%'
				} );
			}
		} );

		ThriveQuizB.views.TestItem = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'tests/test-item' ),
			tagName: 'tr',
			className: 'tvd-collection-item',
			initialize: function ( data ) {
				this.page_id = data.page_id;
			},
			events: {
				'click .tqb-test-item-winner': 'setWinner',
				'click .tqb-stop-test-variation-modal': 'stopVariation'
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				return this;
			},
			setWinner: function ( e ) {
				e.preventDefault();

				var self = this;

				TVE_Dash.showLoader();

				this.model.set( 'is_winner', true );

				this.model.save( {},
					{
						success: function ( model, response ) {
							//Redirect to dashboard
							ThriveQuizB.router.navigate( '#dashboard/page/' + self.page_id, {trigger: true} );
						},
						complete: function ( response ) {
							self.model.trigger( 'tqb_close_modal' );
						}
					} );
				TVE_Dash.showLoader();
			},
			stopVariation: function ( ev ) {
				this.modal( ThriveQuizB.views.ModalStopVariation, {
					model: this.model,
					collection: this.collection,
					'max-width': '60%',
					width: 450
				} );
			}
		} );

		/**
		 * Variation List view
		 */
		ThriveQuizB.views.VariationList = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'variations/list' ),
			initialize: function () {
				this.listenTo( this.collection, 'remove', this.removeAction );
				this.listenTo( this.collection, 'add', this.addAction );
			},
			removeAction: function () {
				this.model.trigger( 'tqb_variation_removed' );
			},
			addAction: function () {
				this.model.trigger( 'tqb_variation_added' );
			},
			render: function () {
				this.$el.html( this.template( {model: this.model, collection: this.collection} ) );
				this.collection.each( this.renderOne, this );
				TVE_Dash.materialize( this.$el );
				return this;
			},
			renderOne: function ( item ) {
				if ( this.model.get( 'is_default' ) ) {
					item.set( 'is_default', true );
				}
				var variation_item = new ThriveQuizB.views.VariationItem( {
					item: item,
					page_model: this.model
				} );
				this.$el.find( '#tqb-page-variations-item' ).append( variation_item.render().$el );
			}
		} );

		/**
		 * Variation Item
		 */
		ThriveQuizB.views.VariationItem = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'variations/item' ),
			tagName: 'tr',
			className: 'tvd-collection-item',
			events: {
				'click .tqb-variation-archive': 'archive',
				'click .tqb-variation-reset': 'reset',
				'click .tqb-variation-delete': 'delete',
				'click .tqb-variation-re-add': 'reAdd',
				'click .tqb-variation-clone': 'clone',
				'click .tqb-edit-variation-title': 'editVariationTitle'
			},
			initialize: function ( arg ) {
				this.item = arg.item;
				this.page_model = arg.page_model;
				//Adds data attribute to view dynamically.
				this.$el.attr( 'data-id', this.item.get( 'id' ) );

				if ( this.item.get( 'post_status' ) === ThriveQuizB.data.variation_status.archive ) {
					this.template = TVE_Dash.tpl( 'variations/item-archived' );
				} else {
					this.template = TVE_Dash.tpl( 'variations/item' );
				}
				/*Listen to statistics change*/
				this.listenTo( this.item, 'change:cache_impressions', this.render );
				/*Listen to control change*/
				this.listenTo( this.item, 'change:is_control', this.render );
			},
			render: function () {
				this.$el.html( this.template( {item: this.item, page_model: this.page_model} ) );
				this.$variationTitle = this.$el.find( '.tqb-variation-title' );
				return this;
			},
			/**
			 * Archive this form variation - show confirmation
			 */
			archive: function () {
				if ( this.page_model.get( 'variations' ).length == 1 ) {
					TVE_Dash.modal( ThriveQuizB.views.ModalArchiveLastVariation, {
						model: this.item,
						page_model: this.page_model
					} );
				} else {
					TVE_Dash.modal( ThriveQuizB.views.ModalArchiveVariation, {
						model: this.item,
						page_model: this.page_model
					} );
				}
			},
			/**
			 * show a lightbox that warns the user he will lose any test and report data associated with this Form Variation
			 */
			reset: function () {
				TVE_Dash.modal( ThriveQuizB.views.ModalResetVariation, {
					model: this.item
				} );
			},
			/**
			 * re-add an archived form
			 * The add new form lightbox pops up asking the user for a new name
			 * The design and trigger is copied to become a new active form with the new name
			 * The archived form stays in the archive without change
			 */
			reAdd: function () {
				var _data = this.item.toJSON(),
					variation = new ThriveQuizB.models.Variation();

				/**
				 * Unset the ID, the post_title and set the status to publish
				 */
				delete _data.id;
				delete _data.post_title;
				variation.set( _data );
				variation.set( {'post_status': ThriveQuizB.data.variation_status.publish, 'name': this.page_model.get( 'post_title' )} );

				TVE_Dash.modal( ThriveQuizB.views.ModalReAddVariation, {
					model: variation,
					page_model: this.page_model,
					width: '800px',
					'max-width': '60%'
				} );

			},
			clone: function () {
				TVE_Dash.showLoader();
				var _data = this.item.toJSON(),
					self = this,
					variation = new ThriveQuizB.models.Variation();
				/**
				 * Unset the is_control, impressions, opt-ins and social shares
				 */
				delete _data.is_control;
				_data.cache_impressions = 0;
				_data.cache_optins = 0;
				_data.cache_optins_conversions = 0;
				_data.cache_social_shares = 0;
				_data.cache_social_shares_conversions = 0;
				variation.set( _data );
				variation.set( 'post_title', ThriveQuizB.t.copy_of + ' ' + _data.post_title );

				TVE_Dash.showLoader();
				variation.cloneVariation( {
					success: function ( response ) {
						variation.trigger( 'add:parent_post', response );
						variation.set( {
							'id': response.id,
							'tcb_editor_url': response.tcb_editor_url,
							'cache_optin_conversion_rate': 'N/A',
							'cache_social_share_conversion_rate': 'N/A'
						} );
						self.page_model.get( 'variations' ).add( variation );
					},
					error: function () {
						//TODO: error handling
					},
					complete: function () {
						TVE_Dash.hideLoader();
					}
				}, variation.toJSON() );
			},
			/**
			 * Deletes this form variation - show confirmation
			 */
			delete: function () {
				TVE_Dash.modal( ThriveQuizB.views.ModalDeleteVariation, {
					model: this.item
				} );
			},
			editVariationTitle: function () {
				var self = this,
					edit_btn = this.$el.find( '.tqb-edit-variation-title' ),
					edit_model = new Backbone.Model( {
						value: this.item.get( 'post_title' ),
						label: ThriveQuizB.t.variation_name,
						required: true
					} );
				edit_btn.hide();

				edit_model.on( 'change:value', function () {
					self.saveTitle.apply( self, arguments );
					self.$variationTitle.show();
					textEdit.remove();
					edit_btn.show();
				} );
				edit_model.on( 'tqb_no_change', function () {
					self.$variationTitle.html( self.item.get( 'post_title' ) ).show();
					textEdit.remove();
					edit_btn.show();
				} );

				var textEdit = new ThriveQuizB.views.TextEdit( {
					model: edit_model,
					tagName: 'div'
				} );
				this.$variationTitle.hide().after( textEdit.render().$el );
				textEdit.focus();
			},
			/**
			 * Saves the new title and hides the input value
			 */
			saveTitle: function ( edit_model, new_value ) {
				var self = this;
				this.item.set( {
					post_title: new_value
				} );
				this.item.save( null, {
					success: function ( model, response ) {
						self.$variationTitle.html( new_value );
						TVE_Dash.success( ThriveQuizB.t.variation_title_save_success_toast );
					},
					error: function ( model, response ) {
						TVE_Dash.err( ThriveQuizB.t.variation_title_save_fail_toast );
					}
				} );
			}
		} );

		ThriveQuizB.views.QuizStructureItem = ThriveQuizB.views.Base.extend( {
			className: 'tvd-col tvd-s12 tvd-m3 tvd-valign tqb-structure-item-wrapper tqb-relative-tooltip-position',
			template: TVE_Dash.tpl( 'quiz/structure-item' ),
			events: {
				'click .tqb-structure-item-card-delete': 'deleteCardModal',
				'click .tqb-add-form': 'addNewForm',
				'click .tqb-view-test': 'viewTest'
			},
			initialize: function ( args ) {
				this.model.on( 'tqb_render_structure_item', this.deleteCard, this );
			},
			render: function () {
				this.$el.html( this.template( {
					item: this.model,
					tge_url: ThriveQuizB.globals.quiz.get( 'tge_url' )
				} ) );

				return this;
			},
			deleteCardModal: function () {
				var self = this;

				this.modal( ThriveQuizB.views.ModalDeleteStructureItem, {
					model: self.model,
					width: '600px',
					'max-width': '60%'
				} );
			},
			deleteCard: function () {
				this.model.trigger( 'delete:structureItem', this.model );
				this.remove();
			},
			addNewForm: function () {
//				if ( this.model.get( 'ID' ) ) {
				var running_test = this.model.get( 'has_running_test' ),
					extra_param = '';
				if ( running_test ) {
					ThriveQuizB.router.navigate( '#dashboard/test/' + running_test.id, {trigger: true} );
				}
				if ( ! this.model.get( 'viewed' ) ) {
					extra_param = '/true';
				}
				ThriveQuizB.router.navigate( '#dashboard/page/' + this.model.get( 'ID' ) + extra_param, {trigger: true} );

//				} else {
//					var self = this;
//					var model = new ThriveQuizB.models.Variation( {
//						page_id: this.model.get( 'ID' ),
//						quiz_id: this.model.get( 'parent_id' ),
//						type: this.model.get( 'type' ),
//						default_name: true
//					} );
//
//					model.generateFirstVariation( {
//						success: function ( model, response ) {
//							ThriveQuizB.router.navigate( '#dashboard/page/' + model.page_id + '/true', {trigger: true} );
//						},
//						error: function ( model, response ) {
//
//						},
//						complete: function () {
//							TVE_Dash.hideLoader();
//						}
//					}, model.toJSON() );
//				}
			},
			viewTest: function () {
				ThriveQuizB.router.navigate( '#dashboard/test/' + this.model.get( 'has_running_test' ).id, {trigger: true} );
			}
		} );

		ThriveQuizB.views.QuizStructureButton = ThriveQuizB.views.Base.extend( {
			className: 'tvd-row tvd-center',
			template: TVE_Dash.tpl( 'quiz/structure-button' ),
			events: {
				'click .tqb-dotted-holder': 'addCard'
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				return this;
			},
			addCard: function () {

				this.model.trigger( 'add:structureItem', this.model );
				this.remove();
				$( '.tvd-material-tooltip' ).hide();
			}
		} );

		ThriveQuizB.views.QuizStructure = ThriveQuizB.views.Base.extend( {
			className: 'tvd-col tvd-s12',
			template: TVE_Dash.tpl( 'quiz/structure' ),
			initialize: function ( data ) {
			},
			render: function () {
				this.$el.html( this.template() );
				this.renderItem( 'splash' );
				this.renderItem( 'qna' );
				this.renderItem( 'optin' );
				this.renderItem( 'results' );

				this.hideShowExtra();

				return this;
			},
			renderItem: function ( type ) {
				var item = {},
					container = null,
					model = null,
					self = this,
					model_type = this.model.get( type ),
					post_id = typeof(model_type) === 'number' ? model_type : false,
					view;
				item['last'] = (type == 'results');
				item['type'] = type;
				item['parent_id'] = this.model.get( 'ID' );
				item['viewed'] = this.model.get( 'viewed' ) ? this.model.get( 'viewed' )[type] : true;
				item['mandatory'] = ThriveQuizB.data.quiz_structure_item_types[type].mandatory;
				item['post_type'] = ThriveQuizB.data.quiz_structure_item_types[type].key;
				item['name'] = ThriveQuizB.data.quiz_structure_item_types[type].name;
				if ( this.model.get( 'running_tests' ) ) {
					item['has_running_test'] = this.model.get( 'running_tests' )[type];
				} else {
					item['has_running_test'] = false;
				}
				item['question_number'] = (this.model.get( 'tge_question_number' )) ? this.model.get( 'tge_question_number' ) : 0;
				// Number of page variations
				item['nr_of_variations'] = (this.model.get( 'nr_of_variations' ) && this.model.get( 'nr_of_variations' )[type]) ? this.model.get( 'nr_of_variations' )[type] : 0;

				item['ID'] = post_id;

				model = new ThriveQuizB.models.StructureItem( item );
				model.on( 'add:structureItem', function ( item ) {
					self.addCard( item );
				} );
				model.on( 'delete:structureItem', function ( item ) {
					self.deleteCard( item );
				} );

				if ( model_type ) {
					view = new ThriveQuizB.views.QuizStructureItem( {model: model} );
					container = this.$el.find( '#tqb-structure-card-' + type );
				} else {
					view = new ThriveQuizB.views.QuizStructureButton( {model: model} );
					container = this.$el.find( '#tqb-structure-button-' + type );
				}

				container.append( view.render().$el );
				return this;
			},
			addCard: function ( model ) {
				var type = model.get( 'type' ),
					self = this;
				this.model.set( type, true );
				this.model.save( null, {
					success: function () {
						self.renderItem( type );
						ThriveQuizB.util.bind_wistia();
					},
					error: function () {
						TVE_Dash.err( ThriveQuizB.t.quiz_structure_save_fail_toast );
					}
				} );

				this.hideShowExtra();
			},
			deleteCard: function ( model ) {
				var type = model.get( 'type' ),
					self = this;
				this.model.set( type, false );
				this.model.save( null, {
					success: function () {
						self.renderItem( type );
					},
					error: function () {
						TVE_Dash.err( ThriveQuizB.t.quiz_structure_save_fail_toast );
					}
				} );

				this.hideShowExtra();
			},
			checkIfFull: function () {
				var self = this,
					is_full = true;
				Object.keys( ThriveQuizB.data.quiz_structure_item_types ).forEach( function ( element, index ) {
					if ( ! self.model.get( element ) ) {
						is_full = false;
					}
				} );
				return is_full;
			},
			hideShowExtra: function () {
				if ( this.checkIfFull() ) {
					this.$el.find( '#tqb-structure-button-wrapper' ).hide();
				} else {
					this.$el.find( '#tqb-structure-button-wrapper' ).show();
				}
			}
		} );

		ThriveQuizB.views.StyleItemCard = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'quiz/style/item/card' ),
			render: function () {
				this.$el.append( this.template( {item: this.model} ) );

				return this;
			}
		} );

		/**
		 * View assigned for the test chart
		 */
		ThriveQuizB.views.ChartView = ThriveQuizB.views.Base.extend( {
			events: {
				'change #tqb-chart-interval-select': 'intervalChanged',
			},
			currentInterval: 'day',
			currentSource: '',
			currentDate: '',
			initialize: function () {
				this.source = this.model.get( 'quiz_id' );
				this.createChart();
				var self = this;
				this.listenTo( this.model, 'change:interval', function () {
					self.model.fetch();
				} );
				this.listenTo( this.model, 'change:date', function () {
					self.model.fetch();
				} );
				this.listenTo( this.model, 'change', this.updateChart );

			},
			intervalChanged: function () {

				var interval = this.$el.find( '#tqb-chart-interval-select' ).val();

				if ( interval == this.currentInterval ) {
					return;
				} else {
					this.currentInterval = interval;
				}

				if ( typeof this.chart !== 'undefined' ) {
					this.chart.showLoading();
				}

				this.model.set( 'interval', interval );
			},
			updateChart: function () {
				this.chart.set( 'data', this.model.get( 'chart_data' ) );
				this.chart.set( 'title', this.model.get( 'chart_title' ) );
				this.chart.set( 'x_axis', this.model.get( 'chart_x_axis' ) );
				this.chart.set( 'y_axis', this.model.get( 'chart_y_axis' ) );

				this.chart.redraw();

			},
			createChart: function () {
				var customLegend = false;
				if ( this.model.get( 'quiz_id' ) || this.model.get( 'quiz_id' ) == 0 ) {
					customLegend = true;
				}
				this.chart = new ThriveQuizB.LineChart( {
					title: '',
					data: [],
					renderTo: 'tqb-chart-container',
					legend: customLegend
				} );
				this.chart.showLoading();
			},
			drawCandleStick: function ( activeTestItems, container ) {
				if ( activeTestItems ) {
					var data = activeTestItems.getCandleStickData();
				} else {
					return;
				}
				this.bar_chart = new ThriveQuizB.BarChart( {
					title: '',
					data: data.chartData,
					colors: data.chartColors,
					renderTo: container
				} );
			}
		} );
		/**
		 * Test Stopped Item List View
		 */
		ThriveQuizB.views.TestStoppedItemList = ThriveQuizB.views.Base.extend( {
			addOne: function ( item, index ) {
				item.set( 'show_graph', true );
				item.set( 'parent_test_running', this.model.get( 'status' ) );
				var v = new ThriveQuizB.views.TestStoppedItem( {
					model: item
				} );
				this.$el.append( v.render().el );
			},
			addAll: function ( items ) {
				items.each( this.addOne, this );
			},
			render: function () {
				this.addAll( this.collection );
				return this;
			}
		} );

		/**
		 * Table items from the Form Test view
		 */
		ThriveQuizB.views.TestStoppedItem = ThriveQuizB.views.Base.extend( {
			tagName: 'tr',
			className: 'tvd-collection-item tvd-gray',
			template: TVE_Dash.tpl( 'tests/stopped-item' ),

			initialize: function ( options ) {
				this.highest_id = options.highest_id;
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				if ( this.highest_id === this.model.get( 'id' ) ) {
					this.$el.addClass( 'highlight' );
				}
				return this;
			}
		} );

		/**
		 * Badge Template Item for ModalBadgeTemplate
		 */
		ThriveQuizB.views.BadgeTemplateItem = ThriveQuizB.views.Base.extend( {
			className: 'tqb-badge-template-card',
			template: TVE_Dash.tpl( 'badge/template' ),
			events: {
				'click .tvd-card': 'toggle_selected'
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );

				return this;
			},
			toggle_selected: function ( event ) {
				var $card = $( event.currentTarget ),
					_key = event.currentTarget.dataset.key;

				this.collection.each( function ( model, index, collection ) {
					model.set( 'selected', false, {silent: true} );
				} );

				TVE_Dash.select_card( $card, $card.parents( '#tqb-badge-templates-list' ).first().find( '.tvd-card' ) );

				this.collection.findWhere( {key: _key} ).set( 'selected', true );
			}
		} );

		ThriveQuizB.views.ReportSources = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'reporting/source-options' ),
			render: function () {
				this.$el.html( this.template( {sources: this.collection} ) );
				TVE_Dash.materialize( this.$el );
				return this;
			}
		} );

		/**
		 * Reporting view
		 */
		ThriveQuizB.views.Reporting = ThriveQuizB.views.Base.extend( {
			className: 'tvd-container',
			template: TVE_Dash.tpl( 'reporting-main' ),
			events: {
				'change #tqb-report-source': 'changeQuiz',
				'change #tqb-report-type': 'changeReport',
				'change #tqb-chart-date-select': 'changeDateInterval',
				'change #tqb-chart-interval-select': 'changeReport',
				'click .calendar-trigger-icon': 'triggerDatepicker'
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				this.$el.find( '.tqb-secondary-selectors' ).hide();
				this.$el.find( '.tqb-custom-filter' ).hide();

				this.renderQuizList();

				return this;
			},
			renderQuizList: function () {
				var self = this;
				this.collection.fetch( {
					success: function ( collection, response ) {
						var sources = new ThriveQuizB.views.ReportSources( {collection: self.collection} );
						self.$( '#tqb-report-source-container' ).html( sources.render().$el );
						if ( self.model.get( 'selected' ) ) {
							self.$el.find( '#tqb-report-source' ).val( self.model.get( 'selected' ) );
						}
						self.changeQuiz();
						self.changeReport();
						self.getReport();
						TVE_Dash.materialize( self.$el );

					}
				} );

			},
			changeQuiz: function ( trigger ) {
				this.model.set( 'quiz_id', this.$el.find( '#tqb-report-source' ).val() );
				if ( trigger ) {
					this.getReport();
				}
			},
			changeDateInterval: function ( trigger ) {
				this.setModel();
				if ( this.model.get( 'report_type' ) == 'completions' && this.model.get( 'date' ) == ThriveQuizB.date_intervals.custom ) {
					this.$el.find( '.tqb-custom-filter' ).show();
					this.getReport();
					return;
				}
				this.$el.find( '.tqb-custom-filter' ).hide();
				if ( trigger ) {
					this.getReport();
				}
			},
			changeReport: function ( trigger ) {
				this.setModel();
				this.hideAllOption();
				if ( trigger ) {
					this.getReport();
				}
			},
			hideAllOption: function () {
				if ( this.model.get( 'report_type' ) !== 'completions' ) {
					if ( this.$el.find( '#tqb-report-source' ).val() == 0 ) {
						this.$el.find( '#tqb-report-source' ).val( this.$el.find( '#tqb-report-source option:first' ).val() ).trigger( "change" );
					}
					this.$el.find( '#tqb-report-source option[value="0"]' ).detach();
				} else {
					if ( this.$el.find( '#tqb-report-source option[value="0"]' ).length == 0 ) {
						this.$el.find( '#tqb-report-source' ).append( TVE_Dash.tpl( 'reporting/all-source' ) );
					}
				}
			},
			setModel: function () {
				this.model.set( 'report_type', this.$el.find( '#tqb-report-type' ).val() );
				this.model.set( 'date', this.$el.find( '#tqb-chart-date-select' ).val() );
				this.model.set( 'interval', this.$el.find( '#tqb-chart-interval-select' ).val() );
				this.model.set( 'start_date', this.$el.find( '#tqb-report-start-date' ).val() );
				this.model.set( 'end_date', this.$el.find( '#tqb-report-end-date' ).val() );
			},
			getReport: function () {
				TVE_Dash.showLoader();
				var self = this;
				this.model.save( null, {
					success: function ( model, response ) {
						if ( response ) {
							if ( self.model.get( 'report_type' ) == 'users' ) {
								self.renderUsersView( response );
							}
							if ( self.model.get( 'report_type' ) == 'flow' ) {
								self.renderFlowView( response );
							}
							if ( self.model.get( 'report_type' ) == 'completions' ) {
								self.renderCompletionView( response );
							}
							if ( self.model.get( 'report_type' ) == 'questions' ) {
								self.renderQuestionsView( response );
							}
						} else {
							TVE_Dash.err( ThriveQuizB.t.SourceNotFound );
						}
					},
					error: function ( model, response ) {

					},
					complete: function () {
						TVE_Dash.hideLoader();
					}
				} );
			},
			renderCompletionView: function ( response ) {
				var view = new ThriveQuizB.views.CompletionReport( {
					model: new ThriveQuizB.models.CompletionReport( response ),
					el: this.$el.find( '#tqb-report-content' )
				} );
				view.render();
				var self = this;
				this.$el.find( '#tqb-report-start-date' ).pickadate( {
					format: 'yyyy-mm-dd',
					format_submit: 'yyyy-mm-dd',
					onClose: function () {
						self.validateDate( 'start', self.$el.find( '#tqb-report-start-date' ).val() );
					},
					onStart: function () {
						var date = new Date();
						self.model.set( 'start_date', [date.getFullYear(), date.getMonth(), (date.getDate() - 7)] );
						this.set( 'select', self.model.get( 'start_date' ) );
					}
				} );

				this.$el.find( '#tqb-report-end-date' ).pickadate( {
					format: 'yyyy-mm-dd',
					format_submit: 'yyyy-mm-dd',
					onClose: function () {
						self.validateDate( 'end', self.$el.find( '#tqb-report-end-date' ).val() );
					},
					onStart: function () {
						var date = new Date();
						self.model.set( 'end_date', [date.getFullYear(), date.getMonth(), date.getDate()] );
						this.set( 'select', self.model.get( 'end_date' ) );
					}
				} );
				this.changeReport();
				this.$el.find( '.tqb-secondary-selectors' ).show();
				return this;
			},
			validateDate: function ( input, date ) {
				if ( input === 'start' && this.model.get( 'start_date' ) !== date ) {
					this.model.set( 'start_date', date );
					this.changeReport( true );
				}

				if ( input === 'end' && this.model.get( 'end_date' ) !== date ) {
					this.model.set( 'end_date', date );
					this.changeReport( true );
				}
			},
			renderUsersView: function ( response ) {
				var self = this;
				var view = new ThriveQuizB.views.QuizUsersList( {
					collection: new ThriveQuizB.collections.QuizUsers( response.data ),
					model: new ThriveQuizB.models.UsersList( {
						total_items: response.total_items,
						total_pages: response.total_pages,
						per_page: response.per_page,
						current_page: response.current_page,
						offset: response.offset,
						quiz_id: this.model.get( 'quiz_id' )
					} )
				} );
				self.$el.find( '#tqb-report-content' ).html( view.render().el );
				this.$el.find( '.tqb-secondary-selectors' ).hide();
				return this;
			},
			renderFlowView: function ( response ) {
				var view = new ThriveQuizB.views.FlowReport( {
					model: new ThriveQuizB.models.FlowReport( response ),
					el: this.$el.find( '#tqb-report-content' )
				} );
				view.render();
				this.$el.find( '.tqb-secondary-selectors' ).hide();
				return this;
			},
			renderQuestionsView: function ( response ) {
				var collection = new ThriveQuizB.collections.CompletionTableModel(),
					model;

				this.parseQuestions( response ).forEach( function ( element, index ) {
					model = new ThriveQuizB.models.QuestionsReportItem( element );
					collection.push( model );
				} );

				var view = new ThriveQuizB.views.QuestionsReport( {
					collection: collection,
					el: this.$el.find( '#tqb-report-content' )
				} );

				view.render();
				this.$el.find( '.tqb-secondary-selectors' ).hide();
				return this;
			},
			parseQuestions: function ( object ) {
				var array = $.map( object, function ( value, index ) {
					return value;
				} );
				return array;
			}
		} );

		/**
		 * Questions report  view
		 */
		ThriveQuizB.views.QuestionsReport = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'reporting/questions-report' ),
			render: function () {
				this.$el.html( this.template( {} ) );
				this.renderQuestions();
				return this;
			},
			renderQuestions: function () {
				if ( this.collection.length ) {
					this.collection.each( this.renderOne, this );
				} else {
					this.$el.find( '.tqb-questions-report-no-data' ).show();
				}

				return this;
			},
			renderOne: function ( item ) {
				var view = new ThriveQuizB.views.QuestionsReportItem( {
					model: item
				} );
				this.$el.find( '.tqb-questions-container' ).append( view.render().$el );
				view.renderChart();
				return this;
			}
		} );

		ThriveQuizB.views.QuestionsReportItem = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'reporting/questions-question' ),
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				this.renderAnswers();
				return this;
			},
			renderChart: function () {
				var self = this;
				this.pie_chart = new ThriveQuizB.Pie3DChart( {
					title: '',
					data: $.map( self.model.get( 'answers' ), function ( value, index ) {
						return value;
					} ),
					renderTo: 'tqb-chart-container-' + self.model.get( 'id' )
				} );
			},
			renderAnswers: function () {
				var collection = new ThriveQuizB.collections.QuestionsReportAnswers(),
					model;
				this.parseQuestions( this.model.get( 'answers' ) ).forEach( function ( element, index ) {
					model = new ThriveQuizB.models.QuestionsReportAnswer( element );
					collection.push( model );
				} );
				var view = new ThriveQuizB.views.QuestionsReportAnswers( {
					collection: collection,
					el: this.$el.find( '.tqb-answer-table' )
				} );
				view.render();
				return this;
			},
			parseQuestions: function ( object ) {
				var array = $.map( object, function ( value, index ) {
					return value;
				} );
				return array;
			}
		} );

		ThriveQuizB.views.QuestionsReportAnswers = ThriveQuizB.views.Base.extend( {
			render: function () {
				this.collection.each( this.renderOne, this );
				return this;
			},
			renderOne: function ( item ) {
				var view = new ThriveQuizB.views.QuestionsReportAnswer( {
					model: item
				} );
				this.$el.append( view.render().$el );
				return this;
			}
		} );

		ThriveQuizB.views.QuestionsReportAnswer = ThriveQuizB.views.Base.extend( {
			tagName: 'li',
			className: 'tvd-collection-item',
			template: TVE_Dash.tpl( 'reporting/questions-answer' ),
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				return this;
			}
		} );

		/**
		 * Completions view
		 */
		ThriveQuizB.views.CompletionReport = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'reporting/completion-report' ),
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				this.updateColorList( this.model );
				var chartModel = new ThriveQuizB.models.CompletionChartModel( this.model.toJSON() );
				var _chart = new ThriveQuizB.views.ChartView( {
					model: chartModel,
					el: this.$el.find( '.tve-chart-interval' )
				} );
				this.validateChartData();
				_chart.updateChart();

				var table_array = this.parseTableQuizzes( this.model.get( 'quiz_list' ) ),
					tableCollection = new ThriveQuizB.collections.CompletionTableModel(),
					model,
					self = this;

				if ( table_array.length ) {

					table_array.forEach( function ( element, index ) {
						model = new ThriveQuizB.models.CompletionQuiz( element );
						model.set( 'color', self.colors[element.quiz_id] );
						tableCollection.push( model );
					} );

					var _table = new ThriveQuizB.views.CompletionTable( {
						collection: tableCollection,
						el: this.$el.find( '.tqb-table-container' )
					} );

					_table.render();
					this.$el.find( '#tqb-table-items' ).show();
				} else {
					this.$el.find( '#tqb-table-items' ).hide();
				}

				TVE_Dash.materialize( this.$el );
				return this;
			},
			updateColorList: function () {
				this.colors = [];
				var self = this;
				this.parseTableQuizzes( this.model.get( 'chart_data' ) ).forEach( function ( element, index ) {
					self.colors[element.id] = ThriveQuizB.chart_colors[index % ThriveQuizB.chart_colors.length];
				} );
			},
			parseTableQuizzes: function ( object ) {
				var array = $.map( object, function ( value, index ) {
					return value;
				} );
				return array;
			},
			validateChartData: function () {
				if ( _.isEmpty( this.model.get( 'chart_data' ) ) ) {
					this.$el.find( '.tve-chart-overlay' ).show();
				}
			}

		} );

		ThriveQuizB.views.CompletionQuiz = ThriveQuizB.views.Base.extend( {
			tagName: 'li',
			className: 'tvd-collection-item',
			template: TVE_Dash.tpl( 'reporting/completion-quiz' ),

			render: function () {

				this.$el.html( this.template( {item: this.model} ) );
				return this;
			}
		} );

		ThriveQuizB.views.CompletionTable = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'reporting/completion-table' ),
			currentPage: 1,
			pageCount: 1,
			pagesToDisplay: 2,
			itemsPerPage: 10,
			totalItems: 0,
			collectionSlice: null,
			from: ( this.currentPage - 1 ) * this.itemsPerPage,
			events: {
				'click a.page': 'changePage'
			},
			initialize: function () {
				if ( ! this.collectionSlice ) {
					this.collectionSlice = this.collection;
				}
				if ( this.collectionSlice.length > this.itemsPerPage ) {
					this.getItemsToDisplay( this.collectionSlice );
				}
				this.totalItems = this.collection.length;
				this.pageCount = Math.ceil( this.totalItems / this.itemsPerPage );
			},
			getItemsToDisplay: function ( collection ) {
				var from = ( this.currentPage - 1 ) * this.itemsPerPage;
				this.collectionSlice = new ThriveQuizB.collections.CompletionTableModel( collection.chain().rest( from ).first( this.itemsPerPage ).value() );
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				if ( this.totalItems > this.itemsPerPage ) {
					this.renderPagination();
				}
				this.renderList( this.collectionSlice );

				return this;
			},
			renderList: function ( list ) {
				var header = this.$el.find( '#tqb-table-items .tvd-collection-header' );
				this.$el.find( '#tqb-table-items' ).html( header );
				list.each( this.renderOne, this );
			},
			renderOne: function ( item ) {
				var view = new ThriveQuizB.views.CompletionQuiz( {
					model: item
				} );

				this.$el.find( '#tqb-table-items' ).append( view.render().$el );
				return this;
			},
			renderPagination: function () {
				var self = this;
				var view = new ThriveQuizB.views.CompletionReportPagination( {
					model: new ThriveQuizB.models.CompletionPagination( {
						currentPage: parseInt( self.currentPage ),
						pageCount: parseInt( self.pageCount ),
						totalItems: parseInt( self.totalItems ),
						itemsPerPage: parseInt( self.itemsPerPage ),
						pagesToDisplay: parseInt( self.pagesToDisplay )
					} )
				} );

				self.$el.find( '#tqb-completion-pagination' ).html( view.render().$el );
				return true;
			},
			changePage: function ( event, args ) {
				var self = this,
					data = {
						itemsPerPage: self.itemsPerPage
					};

				/* Set the current page of the pagination. This can be changed by clicking on a page or by just calling this method with params */
				if ( event && typeof event.currentTarget !== 'undefined' ) {
					self.currentPage = jQuery( event.currentTarget ).attr( 'value' );
				} else if ( args && typeof args.page !== 'undefined' ) {
					self.currentPage = parseInt( args.page );
				}

				/* just to make sure */
				if ( self.currentPage < 1 ) {
					self.currentPage = 1;
				}
				this.getItemsToDisplay( this.collection );

				/* render sliced view collection */
				this.renderList( this.collectionSlice );
				/* render pagination */
				this.renderPagination();


				return false;
			}
		} );

		/**
		 * Users List view
		 */
		ThriveQuizB.views.QuizUsersList = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'reporting/users-list' ),
			events: {
				'click .tqb-view-quiz-user-data': 'viewUserAnswers',
				'click .tqb-previous-page': 'previousPage',
				'click .tqb-next-page': 'nextPage',
				'change #tqb-user-per-page': 'changeNrOfRows',
				'click .tqb-jump-to-page-button': 'jumpToPage'
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				this.collection.each( this.renderOne, this );

				if ( Number( this.model.get( 'total_items' ) ) < Number( this.model.get( 'per_page' ) ) ) {
					this.hidePagination();
				}
					this.$el.find( '#tqb-user-per-page' ).val( this.model.get( 'per_page' ) );

				this.enterPress();
				return this;
			},
			renderOne: function ( item ) {
				var view = new ThriveQuizB.views.User( {
					model: item,
					collection: this.collection
				} );
				this.$el.find( '.tqb-users-list-table-content' ).append( view.render().$el );
				return this;
			},
			hidePagination: function () {
				this.$el.find( '.tqb-users-report-jump' ).hide();
				this.$el.find( '.tqb-users-report-pagination' ).hide();
			},
			enterPress: function () {
				var self = this;
				$( document ).off( 'keypress' );
				$( document ).on( 'keypress', function ( ev ) {
					if ( ev.which == 13 || ev.keyCode == 13 ) {
						self.jumpToPage();
					}
				} );
				return this;
			},
			jumpToPage: function () {
				var current_page = this.$el.find( '#tqb-current-page-input' ).val();
				if ( (current_page <= this.model.get( 'total_pages' )) && ( current_page >= 1 ) ) {
					var per_page = this.model.get( 'per_page' );
					this.model.set( 'current_page', current_page );
					this.model.set( 'jump_page', current_page );
					this.model.set( 'offset', (current_page - 1) * per_page );
					this.refreshList();
				} else {
					TVE_Dash.err( this.$el.find( '#tqb-current-page-input' ).attr( 'data-error' ) );
					this.$el.find( '#tqb-current-page-input' ).addClass( 'tvd-invalid' );
				}
			},
			changeNrOfRows: function () {
				this.model.set( 'offset', 0 );
				this.model.set( 'current_page', 0 );
				this.model.set( 'per_page', this.$el.find( '#tqb-user-per-page' ).val() );
				this.refreshList();
			},
			previousPage: function () {
				var current_page = this.model.get( 'current_page' ),
					per_page = this.model.get( 'per_page' );
				if ( current_page > 1 ) {
					this.model.set( 'offset', (current_page - 2) * per_page );
					this.model.set( 'current_page', -- current_page );
					this.refreshList();
				}
			},
			nextPage: function () {
				var current_page = this.model.get( 'current_page' ),
					per_page = this.model.get( 'per_page' );
				if ( current_page < this.model.get( 'total_pages' ) ) {
					this.model.set( 'offset', current_page * per_page );
					this.model.set( 'current_page', ++ current_page );
					this.refreshList();
				}
			},
			refreshList: function () {
				TVE_Dash.showLoader();
				var self = this;
				this.model.save( null, {
					success: function ( model, response ) {
						self.collection = new ThriveQuizB.collections.QuizUsers( response.data );
						self.model.set( 'total_pages', response.total_pages );
						self.render();
					},
					error: function ( model, response ) {

					},
					complete: function () {
						TVE_Dash.hideLoader();
					}
				} );
			}
		} );

		ThriveQuizB.views.User = ThriveQuizB.views.Base.extend( {
			tagName: 'tr',
			className: 'tqb-user-table-item tvd-gray',
			template: TVE_Dash.tpl( 'reporting/users-item' ),
			events: {
				'click .tqb-view-users-answers': 'showAnswers'
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );

				return this;
			},
			showAnswers: function () {
				var self = this,
					answers = new ThriveQuizB.collections.QuizUserAnswers();
				answers.fetch( {
					data: {
						quiz_id: self.model.get( 'quiz_id' ),
						user_id: self.model.get( 'id' )
					},
					success: function ( model, response, options ) {
						self.modal( ThriveQuizB.views.ModalViewUserAnswers, {
							model: self.model,
							collection: new ThriveQuizB.collections.UserQuestions( response ),
							width: '750px',
						} );
					},
					error: function ( collection, response, options ) {

					}
				} );
			}
		} );

		/**
		 * User question inside modal
		 */
		ThriveQuizB.views.UserQuestion = ThriveQuizB.views.Base.extend( {
			className: 'tvd-collection-item',
			tagName: 'li',
			template: TVE_Dash.tpl( 'reporting/user-question' ),
			initialize: function () {
				this.collection = new ThriveQuizB.collections.UserAnswers( this.model.get( 'answers' ) );
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				var $list = this.$el.find( ".tqb-user-answers" ),
					self = this;
				$list.empty();
				this.collection.each( function ( item ) {
					var view = new ThriveQuizB.views.UserQuestionAnswer( {model: item} );
					$list.append( view.render().el );
				} );
				return this;
			}
		} );

		ThriveQuizB.views.CompletionReportPagination = ThriveQuizB.views.Base.extend( {
			template: TVE_Dash.tpl( 'reporting/completion-pagination' ),
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				return this;
			}
		} );

		/**
		 * User question answers inside modal
		 */
		ThriveQuizB.views.UserQuestionAnswer = ThriveQuizB.views.Base.extend( {
			className: 'tvd-collection-item tvd-gray',
			template: TVE_Dash.tpl( 'reporting/user-answer' ),
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				return this;
			}
		} );

		/**
		 * Flow report
		 */
		ThriveQuizB.views.FlowReport = ThriveQuizB.views.Base.extend( {
			className: 'tqb-flow-report',
			template: TVE_Dash.tpl( 'reporting/flow-report' ),
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );


				if ( this.model.get( 'splash' ) ) {
					this.renderSplash();
				}

				this.renderQNA();

				if ( this.model.get( 'optin' ) ) {
					this.renderOptin();
				}

				this.renderResults();

				this.renderConclusion();

				return this;
			},
			renderConclusion: function () {
				var impressions = this.model.get( 'splash' ) ? this.model.get( 'splash' )[ThriveQuizB.event_types.impression] : this.model.get( 'qna' )[ThriveQuizB.event_types.impression],
					conversions = this.model.get( 'results' )[ThriveQuizB.event_types.conversion];

				this.$el.find( '.tqb-visitors-completed' ).html( conversions );
				this.$el.find( '.tqb-conversion-rate' ).html( (conversions * 100 / impressions).toFixed( 2 ) + '%' );
				this.$el.find( '.tqb-flow-report-conclusion' ).show();
				this.$el.find( '.tqb-flow-report-no-data' ).hide();
			},
			renderSplash: function () {
				var view = new ThriveQuizB.views.FlowReportSection( {
					model: new ThriveQuizB.models.FlowReportSection( {
						type: 'splash',
						data: this.model.get( 'splash' ),
						total: this.model.get( 'users' )
					} )
				} );

				this.$el.find( '.tqb-flow-report-splash' ).html( view.render().el );
			},
			renderQNA: function () {
				var view = new ThriveQuizB.views.FlowReportSection( {
					model: new ThriveQuizB.models.FlowReportSection( {
						type: 'qna',
						data: this.model.get( 'qna' ),
						total: this.model.get( 'users' )
					} )
				} );

				this.$el.find( '.tqb-flow-report-qna' ).html( view.render().el );
			},
			renderOptin: function () {
				var view = new ThriveQuizB.views.FlowReportSection( {
					model: new ThriveQuizB.models.FlowReportSection( {
						type: 'optin',
						data: this.model.get( 'optin' ),
						subscribers: this.model.get( 'optin_subscribers' ),
						total: this.model.get( 'users' )
					} )
				} );

				this.$el.find( '.tqb-flow-report-optin' ).html( view.render().el );

			},
			renderResults: function () {
				var view = new ThriveQuizB.views.FlowReportSection( {
					model: new ThriveQuizB.models.FlowReportSection( {
						type: 'results',
						data: this.model.get( 'results' ),
						subscribers: this.model.get( 'results_subscribers' ),
						social_shares: this.model.get( 'results_social_shares' ),
						total: this.model.get( 'users' )
					} )
				} );

				this.$el.find( '.tqb-flow-report-results' ).html( view.render().el );
			}
		} );
		ThriveQuizB.views.FlowReportSection = ThriveQuizB.views.Base.extend( {
			className: 'tvd-collection-item tvd-row',
			template: TVE_Dash.tpl( 'reporting/flow-section' ),
			initialize: function () {
				var data = this.model.get( 'data' );

				if ( typeof this.model.get( 'data' )[ThriveQuizB.event_types.skip_optin] === 'undefined' ) {
					data[ThriveQuizB.event_types.skip_optin] = 0;
					this.model.set( 'data', data );
				}
				if ( typeof this.model.get( 'data' )[ThriveQuizB.event_types.conversion] === 'undefined' ) {
					data[ThriveQuizB.event_types.conversion] = 0;
					this.model.set( 'data', data );
				}
				if ( typeof this.model.get( 'data' )[ThriveQuizB.event_types.impression] === 'undefined' ) {
					data[ThriveQuizB.event_types.impression] = 0;
					this.model.set( 'data', data );
				}

				if ( this.model.get( 'data' )[ThriveQuizB.event_types.skip_optin] ) {
					var count = Number( data[ThriveQuizB.event_types.skip_optin] ) + Number( data[ThriveQuizB.event_types.conversion] );
					data[ThriveQuizB.event_types.conversion] = count;
					this.model.set( 'data', data );
				}
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				return this;
			}
		} );


	} );

})( jQuery );
;/**
 * Created by Ovidiu on 8/30/2016.
 */
var ThriveQuizB = ThriveQuizB || {};
ThriveQuizB.views = ThriveQuizB.views || {};

(function ( $ ) {

	$( function () {

		$( 'body' ).on( 'click', '.tqb-toggle-settings', function ( e ) {
			TVE_Dash.modal( ThriveQuizB.views.ModalSettings, {
				'max-width': '35%'
			} );
		} );

		/**
		 * Modal Steps View
		 * If a wizard is needed implement or extend this view
		 */
		ThriveQuizB.views.ModalSteps = TVE_Dash.views.Modal.extend( {
			stepClass: '.tqb-modal-step',
			currentStep: 0,
			$step: null,
			events: {
				'click .tqb-modal-next-step': "next",
				'click .tqb-modal-prev-step': "prev"
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

				this.currentStep = index;

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
		 * New Quiz Modal View
		 */
		ThriveQuizB.views.ModalNewQuiz = ThriveQuizB.views.ModalSteps.extend( {
			template: TVE_Dash.tpl( 'modals/new-quiz' ),
			events: function () {
				return _.extend( {}, ThriveQuizB.views.ModalSteps.prototype.events, {
					'click .tqb-new-quiz-tpl': 'chooseQuizTemplate',
					'click .tvd-modal-submit': 'save'
				} );
			},
			afterInitialize: function () {
				this.listenTo( this.model, 'change:tpl', _.bind( function () {
					var _tpl = this.templates.findWhere( {id: this.model.get( 'tpl' )} );
					this.model.set( 'post_title', _tpl.get( 'is_empty' ) ? '' : _tpl.get( 'name' ) );
					this.model.set( 'tpl', _tpl.get( 'id' ) );
				}, this ) );
			},
			afterRender: function () {
				this.steps = this.$( this.stepClass ).hide();
				this.gotoStep( 0 );
				this.renderTemplates();

				ThriveQuizB.util.data_binder( this );
			},
			chooseQuizTemplate: function ( event ) {
				var targetCard = $( event.target ).closest( '.tvd-card' )[0],
					siblingCards = this.$( '.tqb-new-quiz-tpl' );

				this.$el.find( '.tqb-modal-next-step' ).removeAttr( 'disabled' );

				TVE_Dash.select_card( $( targetCard ), siblingCards );
				this.model.set( 'tpl', targetCard.dataset.id );
				this.renderDescription();
			},
			renderTemplates: function () {
				var $wrapper = this.$( '.tqb-new-quiz-templates' );
				$wrapper.empty();

				this.data.templates.each( function ( template ) {
					$wrapper.append( TVE_Dash.tpl( 'modals/new-quiz-tpl', {
						item: template
					} ) );
				}, this );

				return this;
			},
			renderDescription: function () {
				var $wrapper = this.$( '.tqb-new-quiz-description' ),
					tpl = this.model.get( 'tpl' );

				if ( tpl ) {
					var description = this.data.templates.findWhere( {id: tpl} );
					$wrapper.html( '<span>' + description.get( 'description' ) + '</span>' );
					$wrapper.append( description.get( 'learn_more' ) ); // Wistia Videos
					ThriveQuizB.util.bind_wistia();
				}
			},
			next: function () {
				if ( ! this.model.get( 'tpl' ) ) {
					return TVE_Dash.err( ThriveQuizB.t.QuizMissingTemplate );
				}

				this.gotoStep( this.currentStep + 1 );
			},
			save: function ( ev ) {
				var self = this;

				if ( ! this.model.isValid() ) {
					/* error message is shown automatically from the "invalid" listener setup in the data_binder function */
					return;
				}

				TVE_Dash.showLoader();
				this.model.set( 'order', this.collection.length, {silent: true} );
				this.model.save( null, {
					success: function ( model, response ) {
						model.set( 'ID', response );
						self.collection.add( model, {at: self.collection.length} );
						ThriveQuizB.globals.quizzes.add( self.model, {at: self.collection.length} );
						self.close();
						ThriveQuizB.router.navigate( model.get_edit_route(), {trigger: true} );
					},
					error: function () {
						TVE_Dash.err( ThriveQuizB.t.quiz_save_fail_toast );
					},
					complete: function () {
						TVE_Dash.hideLoader();
					}
				} );
			}
		} );

		ThriveQuizB.views.ModalEditQuizType = ThriveQuizB.views.ModalSteps.extend( {
			template: TVE_Dash.tpl( 'modals/edit-quiz-type' ),
			className: 'tvd-modal tvd-modal-fixed-footer',
			modal_step: 0,
			events: function () {
				return _.extend( {}, ThriveQuizB.views.ModalSteps.prototype.events, {
					'click .tqb-quiz-selector': 'changeQuizType',
					'click #tqb-add-result': 'addResult',
					'click .tqb-modal-submit': 'save'
				} );
			},
			afterInitialize: function ( args ) {
				this.quiz_model = args.quiz_model;
				this.modal_step = args.modal_step;

				this.listenTo( this.model, 'change:type', _.bind( function () {
					var type = this.types.findWhere( {key: this.model.get( 'type' )} );
					this.renderQuizType( type );
				}, this ) );

				this.listenTo( this.model, 'tqb_results_disabled_add_new_result', _.bind( function () {
					this.$el.find( '#tqb-add-result' ).addClass( 'tvd-nm-disabled-overlay' );
				}, this ) );

				this.listenTo( this.model, 'tqb_results_enable_add_new_result', _.bind( function () {
					this.$el.find( '#tqb-add-result' ).removeClass( 'tvd-nm-disabled-overlay' );
				}, this ) );

				this.listenTo( this.model, 'tqb_check_for_empty_inputs', _.bind( function () {
					var hasEmptyValues = false;
					this.$el.find( '.tqb-result-item' ).each( function () {
						if ( ! $( this ).val() ) {
							hasEmptyValues = true;
						}
					} );

					if ( ! hasEmptyValues ) {
						this.model.trigger( 'tqb_results_enable_add_new_result' );
					}
				}, this ) );

				this.listenTo( this.model, 'tqb_add_result', _.bind( function () {
					var hasEmptyValues = false;
					this.$el.find( '.tqb-result-item' ).each( function () {
						if ( ! $( this ).val() ) {
							hasEmptyValues = true;
						}
					} );

					if ( ! hasEmptyValues ) {
						this.addResult();
					}

				}, this ) );

				this.listenTo( this.model, 'tqb_result_enter_pressed', _.bind( function ( $target ) {
					if ( $target.val() ) {
						this.$el.find( ".tqb-result-item[value='']" ).focus();
					}
				}, this ) );

			},
			afterRender: function () {
				this.steps = this.$el.find( this.stepClass ).hide();
				this.gotoStep( this.modal_step );
				if ( this.modal_step != 0 ) {
					this.$el.find( '.tqb-modal-prev-step' ).parent().html( '&nbsp;' );
					this.$el.find( '#tqb-quiz-type-results-message' ).html( ThriveQuizB.t.personality_result_change_text );
				}

				/*get the quiz type and show the next step button or the save button depending on the type*/
				this.$el.find( '.tqb-modal-type-action' ).hide();
				if ( this.model.get( 'type' ) ) {
					var type = this.types.findWhere( {key: this.model.get( 'type' )} );
					this.renderQuizType( type );
				}
				this.checkResultStatus( this.model );

			},
			changeQuizType: function ( e ) {
				var $this = e.currentTarget,
					type = $( $this ).attr( 'data-type' ),
					description = $( $this ).attr( 'data-description' ),
					learn_more = $( $this ).attr( 'data-learn-more' ),
					targetCard = $( $this ).children( '.tvd-card' ),
					siblingCards = this.$el.find( '.tvd-card' );

				TVE_Dash.select_card( targetCard, siblingCards );
				this.$el.find( '#tqb-quiz-type-description' ).html( description + ' ' + learn_more );
				ThriveQuizB.util.bind_wistia();
				this.model.set( 'type', type );
			},
			renderQuizType: function ( type ) {
				if ( type.get( 'has_next_step' ) ) {
					this.changeNextButton( 'tqb-modal-submit', 'tqb-modal-next-step' );
					this.$el.find( '.tqb-modal-type-action' ).text( ThriveQuizB.t.continue );
				} else {
					this.changeNextButton( 'tqb-modal-next-step', 'tqb-modal-submit' );
					this.$el.find( '.tqb-modal-type-action' ).text( ThriveQuizB.t.save );
					this.model.set( 'results', new ThriveQuizB.collections.Options( [] ) );
				}
			},
			changeNextButton: function ( removeClass, addClass ) {
				this.$el.find( '.tqb-modal-type-action' ).show().removeClass( removeClass ).addClass( addClass );
			},
			checkResultStatus: function ( model ) {

				if ( model == this.model && this.model.get( 'results' ).length > 0 ) {
					this.model.get( 'results' ).each( function ( model ) {
						var view = new ThriveQuizB.views.OptionResultInput( {
							item: model.get( 'text' ),
							quiz_model: this.model,
							model: model
						} );
						this.$( '#tqb-multiple-input-wrapper' ).append( view.render().$el );
					}, this );
				} else {
					for ( var i = 0; i < this.model.get( 'minimum_results' ); i ++ ) {
						var model = new ThriveQuizB.models.Base( {value: ''} );
						this.model.get( 'results' ).add( model );
					}

					this.model.get( 'results' ).each( function ( model ) {
						var view = new ThriveQuizB.views.OptionResultInput( {
							item: model.get( 'text' ),
							category: '',
							quiz_model: this.model,
							model: model
						} );
						this.$( '#tqb-multiple-input-wrapper' ).append( view.render().$el );
					}, this );


					this.model.trigger( 'tqb_results_disabled_add_new_result' );
				}
			},
			addResult: function () {
				if ( this.model.get( 'results' ).length >= ThriveQuizB.data.max_interval_number ) {
					TVE_Dash.err( ThriveQuizB.t.maximum_limit_of_results );
					return;
				}

				var view = new ThriveQuizB.views.OptionResultInput( {
					item: '',
					category: '',
					quiz_model: this.model,
					model: new ThriveQuizB.models.Base()
				} );
				this.$( '#tqb-multiple-input-wrapper' ).append( view.render().$el );
				this.$el.find( '.tqb-result-item' ).last().focus();
				this.model.trigger( 'tqb_results_disabled_add_new_result' );
			},
			next: function () {
				if ( ! this.model.get( 'type' ) ) {
					return TVE_Dash.err( ThriveQuizB.t.QuizMissingType );
				}

				this.gotoStep( this.currentStep + 1 );
			},
			save: function () {
				this.tvd_clear_errors();

				if ( ! this.model.isValid() ) {
					return this.tvd_show_errors( this.model );
				}

				var self = this,
					$change_type_btn = $( '.tqb-edit-quiz-type' );
				TVE_Dash.showLoader();

				this.model.get( 'results' ).each( function ( result ) {
					if ( result && ! result.get( 'text' ) ) {
						result.destroy();
					}
				} );

				this.model.save( null, {
					success: function ( model, response ) {
						self.quiz_model.set( 'type', model.get( 'type' ) );
						self.quiz_model.set( 'results', new ThriveQuizB.collections.Options( response.returned_results ) );
						if ( model.get( 'type' ) == ThriveQuizB.data.quiz_types.personality && ( self.quiz_model.get( 'page_variations' ) > 0 || self.quiz_model.get( 'structure' ).tge_question_number ) ) {
							$change_type_btn.html( ThriveQuizB.t.manage_results );
						} else {
							$change_type_btn.html( ThriveQuizB.t.change_type );
						}
						try {
							var quiz = ThriveQuizB.globals.quizzes.findWhere( {ID: self.quiz_model.get( 'ID' )} );
							if ( quiz instanceof ThriveQuizB.models.Quiz ) {
								quiz.set( 'type', model.get( 'type' ) );
							}
						} catch ( error ) {
							console.log( 'Error: ' + error );
						}

						TVE_Dash.success( response.responseText );
					},
					error: function ( model, response ) {
						TVE_Dash.err( response.responseText );
					},
					complete: function () {
						TVE_Dash.hideLoader();
						self.close();
					}
				} );
			}
		} );

		ThriveQuizB.views.ModalEditQuizStyle = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/edit-quiz-style' ),
			className: 'tvd-modal tvd-modal-fixed-footer tqb-choose-quiz-style',
			events: {
				'click .tqb-quiz-style-item': 'selectStyleItem',
				'click .tvd-modal-submit': 'save'
			},
			afterRender: function () {
				var from_ix = this.collection.indexOf( this.collection.findWhere( {id: this.model.get( 'style' )} ) ),
					to_ix = 0;
				if ( from_ix !== - 1 ) {
					this.collection.models.splice( to_ix, 0, this.collection.models.splice( from_ix, 1 )[0] );
				}
				this.collection.each( this.renderOne, this );
			},
			renderOne: function ( item ) {
				var selected = this.model.get( 'style' ) === item.get( 'id' ),
					view = new ThriveQuizB.views.QuizStyleItem( {
						model: item
					} );

				this.$( '#tqb-quiz-styles-list' ).append( view.render( selected ).$el );
			},
			selectStyleItem: function ( e ) {
				var $this = $( e.currentTarget ),
					style_id = $this.attr( 'data-style' ),
					siblingCards = $this.parent().find( '.tqb-card-style-item' );

				TVE_Dash.select_card( $this.find( '.tvd-card' ).first(), siblingCards );

				this.model.set( 'style', style_id );
			},
			save: function () {

				this.tvd_clear_errors();
				if ( ! this.model.get( 'style' ) ) {
					TVE_Dash.err( ThriveQuizB.t.style_warning );
					return;
				}

				var self = this,
					style_model = new ThriveQuizB.models.QuizStyle( {
						style: this.model.get( 'style' ),
						quiz_id: this.model.get( 'ID' ),
						structure: this.model.get( 'structure' )
					} );


				TVE_Dash.showLoader();

				style_model.save( null, {
					success: function ( model, response ) {
						self.model.trigger( 'tqb_style_changed' );
					},
					error: function ( model, response ) {

					},
					complete: function () {
						TVE_Dash.hideLoader();
						self.close();
					}
				} );
			}
		} );

		/**
		 * Add Variation Modal View
		 */
		ThriveQuizB.views.ModalAddVariation = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/add-variation' ),
			events: {
				'click .tvd-modal-submit': 'save'
			},
			afterRender: function () {

				this._prepare_translations();

				ThriveQuizB.util.data_binder( this );
			},
			_prepare_translations: function () {
				var self = this;
				this.$( '.tqb-translate' ).each( function () {
					var html = $( this ).html();
					html = html.replace( '%s', self.model.get_name() );
					$( this ).html( html );
				} );
			},
			save: function () {
				this.tvd_clear_errors();
				if ( ! this.model.isValid() ) {
					return this.tvd_show_errors( this.model );
				}

				var self = this;
				TVE_Dash.showLoader();

				this.model.save( null, {
					success: function ( model, response ) {
						self.model.trigger( 'add:parent_post', response );
						if ( Backbone.history.getFragment().indexOf( 'dashboard/page/' ) !== - 1 ) {
							model.set( {'id': response.id, 'tcb_editor_url': response.tcb_editor_url} );
							self.collection.add( model );

						} else {
							ThriveQuizB.router.navigate( '#dashboard/page/' + response.page_id, {trigger: true} );
						}
					},
					error: function ( model, response ) {

					},
					complete: function () {
						TVE_Dash.hideLoader();
						self.close();
					}
				} );
			}
		} );

		/**
		 *  Re - Add Variation Modal View
		 */
		ThriveQuizB.views.ModalReAddVariation = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/add-variation' ),
			events: {
				'click .tvd-modal-submit': 'save'
			},
			afterRender: function () {

				this._prepare_translations();

				ThriveQuizB.util.data_binder( this );
			},
			_prepare_translations: function () {
				var self = this;
				this.$( '.tqb-translate' ).each( function () {
					var html = $( this ).html();
					html = html.replace( '%s', self.model.get_name() );
					$( this ).html( html );
				} );
			},
			save: function () {
				this.tvd_clear_errors();
				if ( ! this.model.isValid() ) {
					return this.tvd_show_errors( this.model );
				}

				var self = this;
				TVE_Dash.showLoader();

				this.model.save( null, {
					success: function ( model, response ) {
						self.model.trigger( 'add:parent_post', response );
						model.set( {'id': response.id, 'tcb_editor_url': response.tcb_editor_url} );
						self.page_model.get( 'variations' ).add( model );
					},
					error: function ( model, response ) {

					},
					complete: function () {
						TVE_Dash.hideLoader();
						self.close();
					}
				} );
			}
		} );

		ThriveQuizB.views.ModalResetVariation = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/reset-variation' ),
			events: {
				'click .tvd-modal-submit': 'reset'
			},
			reset: function () {
				var self = this;
				TVE_Dash.showLoader();

				this.model.resetStatistics( {
					success: function ( response ) {
						/*Unset this variables for security reasons.*/
						delete response.id;
						delete response.page_id;
						delete response.quiz_id;

						self.model.set( response );
					},
					error: function () {
						//TODO: error handling
					},
					complete: function () {
						self.close();
						TVE_Dash.hideLoader();
					}
				}, this.model.toJSON() );
			}
		} );

		/**
		 * Archive Last Variation View
		 */
		ThriveQuizB.views.ModalArchiveLastVariation = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/archive-last-variation' ),
			afterInitialize: function ( args ) {
				this.page_model = args.page_model;
			}
		} );
		/**
		 * Archive Variation Modal View
		 */
		ThriveQuizB.views.ModalArchiveVariation = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/archive-variation' ),
			events: {
				'click .tvd-modal-submit': 'save'
			},
			afterInitialize: function ( args ) {
				this.page_model = args.page_model;
			},
			save: function () {
				var self = this, is_control = this.model.get( 'is_control' );
				this.model.set( {'post_status': ThriveQuizB.data.variation_status.archive} );

				TVE_Dash.showLoader();
				this.model.save( null, {
					success: function ( model, response ) {
						if ( is_control == 1 && response.new_control_id ) {
							self.page_model.get( 'variations' ).get( response.new_control_id ).set( {'is_control': 1} );
						}
						self.page_model.get( 'archived_variations' ).add( model );
						self.page_model.get( 'variations' ).remove( model );
						TVE_Dash.success( ThriveQuizB.t.archive_variation_success_toast );
					},
					error: function ( model, response ) {
						TVE_Dash.err( ThriveQuizB.t.archive_variation_fail_toast );
					},
					complete: function () {
						TVE_Dash.hideLoader();
						self.close();
					}
				} );
			}
		} );

		/**
		 * Delete Variation Modal View
		 */
		ThriveQuizB.views.ModalDeleteVariation = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/delete-variation' ),
			events: {
				'click .tvd-modal-submit': 'delete'
			},
			afterRender: function () {
				this.$el.addClass( 'tvd-red' );
			},
			delete: function () {
				var self = this;
				TVE_Dash.showLoader();
				this.model.destroy( {
					success: function ( model, response ) {
						TVE_Dash.success( ThriveQuizB.t.delete_variation_success_toast );
					},
					error: function ( model, response ) {
						TVE_Dash.err( ThriveQuizB.t.delete_variation_fail_toast );
					},
					complete: function () {
						TVE_Dash.hideLoader();
						self.close();
					}
				} );
			}
		} );

		ThriveQuizB.views.ModalCopyShortcode = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/copy-quiz-shortcode' ),
			className: 'tvd-modal tqb-copy-shortcode-modal',
			events: {},
			afterRender: function () {
				this.bind_zclip();
			},
			bind_zclip: function () {
				TVE_Dash.bindZClip( this.$el.find( 'a.tvd-copy-to-clipboard' ) );
			}
		} );

		ThriveQuizB.views.ModalDeleteStructureItem = TVE_Dash.views.Modal.extend( {

			template: TVE_Dash.tpl( 'modals/delete-structure-item' ),

			events: {
				'click .tvd-modal-submit': 'delete'
			},

			afterRender: function () {
				var $has_placeholder = this.$( '.tqb-has-placeholder' ),
					new_text = $has_placeholder.html().replace( '%s', this.model.get( 'name' ) );
				$has_placeholder.html( new_text );
			},

			delete: function () {
				this.close();
				this.model.trigger( 'tqb_render_structure_item' );
			}
		} );

		ThriveQuizB.views.ModalEditVariationTest = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/edit-variation-test' ),
			events: {
				'click .tve-edit-variation-test': 'saveTest',
				'blur #auto_win_min_conversions': 'checkMinimumConversions',
				'blur #auto_win_min_duration': 'checkMinimumDuration',
				'blur #auto_win_chance_original': 'checkChanceToBeatOriginal',
				'change #tve-enable-test-settings': 'toggleAutoWinSettings'
			},
			checkMinimumConversions: function ( ev ) {
				var elem = jQuery( ev.currentTarget );
				if ( parseInt( elem.val() ) < 100 ) {
					this.$el.find( '.tve_engagements_notice' ).removeClass( 'tvd-hide' );
				} else {
					this.$el.find( '.tve_engagements_notice' ).addClass( 'tvd-hide' );
				}
			},
			checkMinimumDuration: function ( ev ) {
				var elem = jQuery( ev.currentTarget );
				if ( parseInt( elem.val() ) < 7 ) {
					this.$el.find( '.tve_duration_notice' ).removeClass( 'tvd-hide' );
				} else {
					this.$el.find( '.tve_duration_notice' ).addClass( 'tvd-hide' );
				}
			},
			checkChanceToBeatOriginal: function ( ev ) {
				var elem = jQuery( ev.currentTarget );
				if ( parseFloat( elem.val() ) < 90 ) {
					this.$el.find( '.tve_beat_original_notice' ).removeClass( 'tvd-hide' );
				} else {
					this.$el.find( '.tve_beat_original_notice' ).addClass( 'tvd-hide' );
				}
			},
			saveTest: function ( e ) {

				var self = this;

				this.model.set( 'auto_win_enabled', this.$el.find( "input[name='auto_win_enabled']" ).is( ':checked' ) );
				this.model.set( 'auto_win_min_conversions', this.$el.find( 'input[name="auto_win_min_conversions"]' ).val() );
				this.model.set( 'auto_win_min_duration', this.$el.find( 'input[name="auto_win_min_duration"]' ).val() );
				this.model.set( 'auto_win_chance_original', this.$el.find( 'input[name="auto_win_chance_original"]' ).val() );

				this.tvd_clear_errors();
				if ( ! this.model.isValid() ) {
					return this.tvd_show_errors( this.model );
				}

				this.model.trigger( 'auto_win_changed' );

				TVE_Dash.showLoader();
				this.model.save( {},
					{
						success: function () {
							self.close();
							TVE_Dash.hideLoader();
						}
					} );
			},
			toggleAutoWinSettings: function ( e ) {
				var $input = jQuery( e.currentTarget );
				if ( $input.is( ':checked' ) ) {
					this.$el.find( '.tve-winner-settings-input' ).show( 0 );
				} else {
					this.$el.find( '.tve-winner-settings-input' ).hide( 0 );
				}
			}
		} );

		ThriveQuizB.views.ModalStopVariation = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/stop-variation' ),
			className: 'tvd-modal tvd-red',
			events: {
				'click .tqb-stop-test-variation-action': 'stopVariation'
			},
			initialize: function () {

			},
			stopVariation: function ( event ) {
				var self = this;
				location.hash = '#dashboard/test/' + self.model.get( 'test_id' ) + '/stop';
				TVE_Dash.showLoader();
				self.model.set( 'active', 0 );
				self.model.save( null, {
					success: function ( model, response ) {

						if ( self.collection.length == 2 ) {
							ThriveQuizB.router.navigate( '#dashboard/page/' + self.collection.first().get( 'page_id' ), {trigger: true} );
						} else {
							ThriveQuizB.router.navigate( '#dashboard/test/' + self.model.get( 'test_id' ), {trigger: true} );
						}
					},
					error: function () {
						location.hash = '#dashboard/test/' + self.model.get( 'test_id' );
						TVE_Dash.err( ThriveQuizB.t.stopVariationFailToast );
					},
					complete: function () {
						self.close();
						TVE_Dash.hideLoader();
					}
				} );
			}
		} );

		ThriveQuizB.views.ModalStopVariationTest = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/stop-variation-test' ),
			afterRender: function () {
				this.$el.addClass( 'tvd-stop-test-modal' );
				var $list = this.$el.find( "#tqb-stop-variation" ),
					self = this,
					highest_id = self.collection.getHighestRateItem();
				$list.empty();
				this.collection.each( function ( item ) {
					item.set( 'show_graph', false );
					item.on( 'tqb_close_modal', function () {
						self.close();
					} );
					var v = new ThriveQuizB.views.TestItem( {
						model: item,
						highest_id: highest_id,
						page_id: self.model.get( 'page_id' )
					} );
					$list.append( v.render().el );
				} );

				return this;
			}
		} );
		ThriveQuizB.views.ModalDeleteTest = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'tests/delete-test' ),
			events: {
				'click .tve-confirm-delete-test': 'deleteTest'
			},
			afterInitialize: function ( args ) {
				this.$el.addClass( 'tvd-red' );
			},
			deleteTest: function ( event ) {
				var self = this;
				TVE_Dash.showLoader();
				this.model.destroy( {
					success: function () {
						self.model.trigger( 'tqb_test_removed' );
						TVE_Dash.success( ThriveQuizB.t.deleteTestToast );
					},
					error: function () {
						//TODO: error handling
					},
					complete: function () {
						self.close();
						TVE_Dash.hideLoader();
					}
				} );
			}
		} );

		ThriveQuizB.views.ModalVariationTest = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/variation-start-test' ),
			className: 'tvd-modal',
			events: {
				'click .tqb-toggle-dropdown': 'toggleSettings',
				'click .tqb-start-variation-test': 'saveTest',
				'change #tqb-enable-test-settings': 'toggleAutoWinSettings',
				'blur #auto_win_min_conversions': 'checkMinimumConversions',
				'blur #auto_win_min_duration': 'checkMinimumDuration',
				'blur #auto_win_chance_original': 'checkChanceToBeatOriginal',
			},
			checkMinimumConversions: function ( ev ) {
				var elem = jQuery( ev.currentTarget );
				if ( parseInt( elem.val() ) < 100 ) {
					this.$el.find( '.tqb_engagements_notice' ).removeClass( 'tvd-hide' );
				} else {
					this.$el.find( '.tqb_engagements_notice' ).addClass( 'tvd-hide' );
				}
			},
			checkMinimumDuration: function ( ev ) {
				var elem = jQuery( ev.currentTarget );
				if ( parseInt( elem.val() ) < 7 ) {
					this.$el.find( '.tqb_duration_notice' ).removeClass( 'tvd-hide' );
				} else {
					this.$el.find( '.tqb_duration_notice' ).addClass( 'tvd-hide' );
				}
			},
			checkChanceToBeatOriginal: function ( ev ) {
				var elem = jQuery( ev.currentTarget );
				if ( parseFloat( elem.val() ) < 90 ) {
					this.$el.find( '.tqb_beat_original_notice' ).removeClass( 'tvd-hide' );
				} else {
					this.$el.find( '.tqb_beat_original_notice' ).addClass( 'tvd-hide' );
				}
			},
			toggleSettings: function () {
				var automatic_setting = this.$el.find( '#tqb-winner-test-container' ), title_settings = this.$el.find( '#tqb-winner-test-settings' );
				if ( automatic_setting.is( ":visible" ) ) {
					automatic_setting.hide( 0 );
				} else {
					automatic_setting.fadeIn( 'fast' );
				}
			},
			toggleAutoWinSettings: function ( e ) {
				var $input = jQuery( e.currentTarget );
				if ( $input.is( ':checked' ) ) {
					this.$el.find( '.tqb-winner-settings-input' ).show( 0 );
				} else {
					this.$el.find( '.tqb-winner-settings-input' ).hide( 0 );
				}
			},
			saveTest: function ( e ) {

				var self = this;
				this.model.set( 'title', this.$el.find( 'input[name="test_title"]' ).val() );
				this.model.set( 'notes', this.$el.find( 'textarea[name="test_notes"]' ).val() );
				this.model.set( 'conversion_goal', this.$el.find( 'input[name="conversion_goal"]:checked' ).val() );
				this.model.set( 'auto_win_enabled', this.$el.find( "input[name='auto_win_enabled']" ).is( ':checked' ) );
				this.model.set( 'auto_win_min_conversions', this.$el.find( 'input[name="auto_win_min_conversions"]' ).val() );
				this.model.set( 'auto_win_min_duration', this.$el.find( 'input[name="auto_win_min_duration"]' ).val() );
				this.model.set( 'auto_win_chance_original', this.$el.find( 'input[name="auto_win_chance_original"]' ).val() );
				this.tvd_clear_errors();
				if ( ! this.model.isValid() ) {
					return this.tvd_show_errors( this.model );
				}

				this.model.trigger( 'auto_win_changed' );

				TVE_Dash.showLoader();
				this.model.save( null,
					{
						success: function ( model, response ) {
							ThriveQuizB.router.navigate( '#dashboard/test/' + response, {trigger: true} );
							self.close();
							TVE_Dash.hideLoader();
						}
					} );
			}
		} );

		/**
		 * Badge Templates Modal
		 */
		ThriveQuizB.views.ModalBadgeTemplates = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/badge-templates' ),
			className: 'tvd-modal tvd-modal-fixed-footer',
			events: {
				'click .tvd-modal-submit': 'set_template'
			},
			afterInitialize: function () {
				this.badge_templates = ThriveQuizB.globals.badge_templates;
			},
			afterRender: function () {

				var self = this,
					$list = this.$( '#tqb-badge-templates-list' );

				this.badge_templates.each( function ( model, index, collection ) {
					var _template_view = new ThriveQuizB.views.BadgeTemplateItem( {
						model: model,
						collection: self.badge_templates
					} );
					$list.append( _template_view.render().$el );
				} );
			},
			set_template: function () {

				var _template = this.badge_templates.findWhere( {selected: true} );

				if ( ! _template ) {
					return TVE_Dash.err( ThriveQuizB.t.no_badge_template_selected );
				}

				var self = this,
					image = new ThriveQuizB.models.ThriveImage( {
						post_parent: ThriveQuizB.globals.quiz.get( 'ID' ),
						template: _template.get( 'key' )
					} );

				TVE_Dash.showLoader();

				image.save( null, {
					wait: true,
					success: function ( model, response, options ) {
						self.collection.push( model );
						TVE_Dash.hideLoader();
						window.location = model.get( 'editor_url' );
					},
					error: function ( model, response, options ) {
						TVE_Dash.err( response.responseJSON.data.message );
						TVE_Dash.hideLoader();
					},
					complete: function () {
						self.close();
					}
				} );
			}
		} );

		ThriveQuizB.views.ModalViewUserAnswers = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/view-user-answers' ),
			afterRender: function () {

				var $list = this.$el.find( "#tqb-user-quiz-answers-list" ),
					self = this;
				$list.empty();
				this.collection.each( function ( item ) {
					var view = new ThriveQuizB.views.UserQuestion( {model: item} );
					$list.append( view.render().el );
				} );

				return this;
			}
		} );

		ThriveQuizB.views.ModalSettings = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/settings' ),
			events: {
				'click .tqb-setting-checkbox': 'checkCheckboxValue',
				'click .tvd-modal-submit': 'save_settings'
			},
			afterInitialize: function () {
				this.model = new ThriveQuizB.models.Settings( ThriveQuizB.data.settings );
			},
			checkCheckboxValue: function ( ev ) {
				var $elem = $( ev.currentTarget ), obj = {};

				obj[$elem.attr( 'data-key' )] = (
					Number( $elem[0].checked )
				);
				this.model.set( obj );
			},
			save_settings: function () {
				var self = this;
				TVE_Dash.showLoader();
				this.model.save( null, {
					success: function ( model, response ) {
						ThriveQuizB.data.settings = response;
					},
					complete: function () {
						TVE_Dash.hideLoader();
						self.close();
					}
				} );
			}
		} );

	} );
})( jQuery );
;/**
 * Created by Ovidiu on 8/30/2016.
 */
var ThriveQuizB = ThriveQuizB || {};
ThriveQuizB.util = ThriveQuizB.util || {};

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
 * Override Backbone ajax call and append wp security token
 *
 * @returns {*}
 */
Backbone.ajax = function () {
	if ( arguments[0].url.indexOf( '_nonce' ) === - 1 ) {
		arguments[0]['url'] += "&_nonce=" + ThriveQuizB.admin_nonce;
	}

	return Backbone.$.ajax.apply( Backbone.$, arguments );
};

(function ( $ ) {

	window.addEventListener( 'message', function ( e ) {

		if ( e.data.event && e.data.event == 'tqb_question_counter_update' ) {

			if ( parseInt( $( '.tqb-questions-number' ).text() ) == 0 || e.data.number == 0 ) {
				ThriveQuizB.globals.EditedQuiz.view.model.fetch( {
					success: function () {
						ThriveQuizB.globals.EditedQuiz.view.render();
					}
				} );
			} else {
				$( '.tqb-questions-number' ).html( e.data.number );
			}
		}
	}, false );


	/**
	 * Some constants defined
	 *
	 * @type {{normal: string, delete: string}}
	 */
	ThriveQuizB.util.states = {
		normal: 'normal',
		delete: 'delete'
	};

	/**
	 * Bind wistia pop-over for videos
	 */
	ThriveQuizB.util.bind_wistia = function () {
		if ( window.rebindWistiaFancyBoxes ) {
			window.rebindWistiaFancyBoxes();
		}
	}

	/**
	 * Structure card types
	 *
	 * @type {{splash: object, qna: object, optin: object, results: object}}
	 */
	ThriveQuizB.util.structure_items = {
		splash: {
			name: ThriveQuizB.t.splash_name,
			mandatory: false
		},
		qna: {
			name: ThriveQuizB.t.qna_name,
			mandatory: true
		},
		optin: {
			name: ThriveQuizB.t.optin_name,
			mandatory: false
		},
		results: {
			name: ThriveQuizB.t.results_name,
			mandatory: true
		}
	};

	/**
	 * pre-process the ajaxurl admin js variable and append a querystring to it
	 * some plugins are adding an extra parameter to the admin-ajax.php url. Example: admin-ajax.php?lang=en
	 *
	 * @param {string} [query_string] optional, query string to be appended
	 */
	ThriveQuizB.util.ajaxurl = function ( query_string ) {
		var _q = ajaxurl.indexOf( '?' ) !== - 1 ? '&' : '?';
		if ( ! query_string || ! query_string.length ) {
			return ajaxurl + _q + '_nonce=' + ThriveQuizB.admin_nonce;
		}
		query_string = query_string.replace( /^(\?|&)/, '' );
		query_string += '&_nonce=' + ThriveQuizB.admin_nonce;

		return ajaxurl + _q + query_string;
	};

	/**
	 * Add loading spinner to a specified element
	 * @param $elem
	 */
	ThriveQuizB.util.setLoading = function ( $elem ) {
		$elem.addClass( 'tvd-disabled' );
		$elem.prepend( '<span class="tvd-icon-spinner mdi-pulse tvo-small-icon tvd-margin-right-small"></span>' );
	};

	/**
	 * Remove loading spinner from element
	 * @param $elem
	 */
	ThriveQuizB.util.removeLoading = function ( $elem ) {
		$elem.removeClass( 'tvd-disabled' );
		$elem.find( '.tvd-icon-spinner' ).remove();
	};

	/**
	 * Hide/Show no quiz info
	 */
	ThriveQuizB.util.showHideNoQuizInfo = function () {
		if ( ThriveQuizB.globals.quizzes.length ) {
			$( '.tqb-no-quiz-wrapper' ).hide();
		} else {
			$( '.tqb-no-quiz-wrapper' ).show();
		}
	};


	ThriveQuizB.util.roundNumber = function ( number, digits ) {
		var multiple = Math.pow( 10, digits );

		return Math.round( number * multiple ) / multiple;
	};

	/**
	 * Create structure item array
	 * @param elem
	 */

	ThriveQuizB.util.createInitialStructureObject = function ( array ) {
		var structureObject = {},
			currentObject = {},
			current_key = null;
		Object.keys( ThriveQuizB.data.quiz_structure_item_types ).forEach( function ( element, index ) {
			current_key = ThriveQuizB.data.quiz_structure_item_types[element].key
			if ( typeof array[current_key] !== 'undefined' ) {
				currentObject = array[current_key];
				currentObject.key = current_key;
				structureObject[element] = currentObject;
			}
		} );
		return structureObject;
	};

	/**
	 * binds all form elements on a view
	 * Form elements must have a data-bind attribute which should contain the field name from the model
	 * composite fields are not supported
	 *
	 * this will bind listeners on models and on the form elements
	 *
	 * @param {Backbone.View} view
	 * @param {Backbone.Model} [model] optional, it will default to the view's model
	 */
	ThriveQuizB.util.data_binder = function ( view, model ) {

		if ( typeof model === 'undefined' ) {
			model = view.model;
		}

		if ( ! model instanceof Backbone.Model ) {
			return;
		}

		/**
		 * separate value by input type
		 *
		 * @param {object} $input jquery
		 * @returns {*}
		 */
		function value_getter( $input ) {
			if ( $input.is( ':checkbox' ) ) {
				return $input.is( ':checked' ) ? true : false;
			}
			if ( $input.is( ':radio' ) ) {
				return $input.is( ':checked' ) ? $input.val() : '';
			}

			return $input.val();
		}

		/**
		 * separate setter vor values based on input type
		 *
		 * @param {object} $input jquery object
		 * @param {*} value
		 * @returns {*}
		 */
		function value_setter( $input, value ) {
			if ( $input.is( ':radio' ) ) {
				return view.$el.find( 'input[name="' + $input.attr( 'name' ) + '"]:radio' ).filter( '[value="' + value + '"]' ).prop( 'checked', true );
			}
			if ( $input.is( ':checkbox' ) ) {
				return $input.prop( 'checked', value ? true : false );
			}

			return $input.val( value );
		}

		/**
		 * iterate through each of the elements and bind change listeners on DOM and on the model
		 */
		var $elements = view.$el.find( '[data-bind]' ).each(
			function () {

				var $this = $( this ),
					prop = $this.attr( 'data-bind' ),
					_dirty = false;

				$this.on(
					'change', function () {
						var _value = value_getter( $this );
						if ( model.get( prop ) != _value ) {
							_dirty = true;
							model.set( prop, _value );
							_dirty = false;
						}
					}
				);

				view.listenTo(
					model, 'change:' + prop, function () {
						if ( ! _dirty ) {
							value_setter( $this, this.model.get( prop ) );
						}
					}
				);
			}
		);

		/**
		 * if a model defines a validate() function, it should return an array of binds in the form of:
		 *      ['post_title']
		 * this will add error classes to the bound dom elements
		 */
		view.listenTo(
			model, 'invalid', function ( model, error ) {
				if ( _.isArray( error ) ) {
					_.each(
						error, function ( field ) {
							var _field = field;
							if ( field.field ) { // if this is an object, we need to use the field property
								_field = field.field
							}
							var $target = $elements.filter( '[data-bind="' + _field + '"]' ).first().addClass( 'tvd-validate tvd-invalid' ).focus();
							if ( field.message ) {
								$target.siblings( 'label' ).attr( 'data-error', field.message );
							}
							if ( $target.is( ':radio' ) || $target.is( ':checkbox' ) ) {
								TVE_Dash.err( $target.next( 'label' ).attr( 'data-error' ) );
							}
						}
					);
				} else if ( _.isString( error ) ) {
					TVE_Dash.err( error );
				}
			}
		);
	};

})( jQuery );
;/**
 * Created by Ovidiu on 8/30/2016.
 */
var ThriveQuizB = ThriveQuizB || {};
ThriveQuizB.globals = ThriveQuizB.globals || {};

(function ( $ ) {
	var Router = Backbone.Router.extend( {
		view: null,
		$el: $( "#tqb-admin-dashboard-wrapper" ),
		routes: {
			'dashboard': 'dashboard',
			'dashboard/quiz/:id': 'quizEdit',
			'purge-cache': 'purgeCache',
			'reports(/:id)': 'reports',
			'dashboard/page/:id(/:is_default)': 'editQuizPages',
			'dashboard/test/:id': 'editQuizPageTest'
		},
		breadcrumbs: {
			col: null,
			view: null
		},
		/**
		 * init the breadcrumbs collection and view
		 */
		init_breadcrumbs: function () {
			this.breadcrumbs.col = new ThriveQuizB.collections.Breadcrumbs();
			this.breadcrumbs.view = new ThriveQuizB.views.Breadcrumbs( {
				collection: this.breadcrumbs.col
			} )
		},
		/**
		 * set the current page - adds the structure to breadcrumbs and sets the new document title
		 *
		 * @param {string} section page hierarchy
		 * @param {string} label current page label
		 *
		 * @param {Array} [structure] optional the structure of the links that lead to the current page
		 */
		set_page: function ( section, label, structure ) {
			this.breadcrumbs.col.reset();
			structure = structure || {};
			/* Thrive Dashboard is always the first element */
			this.breadcrumbs.col.add_page( ThriveQuizB.dash_url, ThriveQuizB.t.Thrive_Dashboard, true );
			_.each( structure, _.bind( function ( item ) {
				this.breadcrumbs.col.add_page( item.route, item.label );
			}, this ) );
			/**
			 * last link - no need for route
			 */
			this.breadcrumbs.col.add_page( '', label );
			/* update the page title */
			var $title = $( 'head > title' );
			if ( ! this.original_title ) {
				this.original_title = $title.html();
			}
			$title.html( label + ' &lsaquo; ' + this.original_title )
		},
		/**
		 * dashboard route callback
		 */
		dashboard: function () {
			this.set_page( 'dashboard', ThriveQuizB.t.Dashboard );
			var self = this;
			TVE_Dash.showLoader();

			if ( this.view ) {
				this.view.remove();
			}
			ThriveQuizB.globals.quizzes.fetch( {
				update: true,
				success: function ( model, response, options ) {
					self.view = new ThriveQuizB.views.Dashboard( {
						collection: ThriveQuizB.globals.quizzes
					} );

					self.$el.html( self.view.render().$el );
					ThriveQuizB.util.bind_wistia();
					ThriveQuizB.util.showHideNoQuizInfo();
				},
				error: function ( collection, response, options ) {

				}
			} );

		},

		/**
		 * reports route callback
		 */
		reports: function ( id ) {
			this.set_page( 'reports', ThriveQuizB.t.Reporting, [{route: 'dashboard', label: ThriveQuizB.t.Dashboard}] );
			TVE_Dash.showLoader();

			if ( this.view ) {
				this.view.remove();
			}
			if ( ! id ) {
				id = null;
			}

			this.view = new ThriveQuizB.views.Reporting( {
				model: new ThriveQuizB.models.Reporting( {selected: id} ),
				collection: new ThriveQuizB.collections.Quizzes()
			} );

			this.$el.html( this.view.render().$el );
			TVE_Dash.hideLoader();
		},
		/**
		 * Edit quiz route callback
		 * @param id int
		 */
		quizEdit: function ( id ) {
			if ( this.view ) {
				this.view.remove();
			}

			if ( TVE_Dash.opened_modal_view ) {
				TVE_Dash.opened_modal_view.close();
			}

			$( '.tvd-material-tooltip' ).hide();

			if ( ! id ) {
				ThriveQuizB.router.navigate( '#dashboard', {trigger: true} );
				return;
			}
			ThriveQuizB.globals.EditedQuiz = this;
			var self = this,
				model = new ThriveQuizB.models.Quiz( {ID: id} );

			TVE_Dash.showLoader();

			//fetch the model from the server and after that renders the EditCampaign view
			model.fetch( {
				cache: false,
				success: function ( model, response, options ) {
					ThriveQuizB.globals.quiz = model;
					self.view = new ThriveQuizB.views.EditQuiz( {
						model: model
					} );

					self.$el.html( self.view.render().$el );

					self.set_page( 'dashboard/quiz', model.get( 'post_title' ), [{route: 'dashboard', label: ThriveQuizB.t.Dashboard}] );
					// when editing the title, also update the breadcrumbs / page title for the campaign dashboard
					model.on( 'change:post_title', function () {
						self.set_page( 'dashboard/quiz', model.get( 'post_title' ), [{route: 'dashboard', label: ThriveQuizB.t.Dashboard}] );
					} );

					TVE_Dash.hideLoader();
				}
			} ).error( function ( response ) {
				TVE_Dash.err( response.responseText );
				TVE_Dash.hideLoader();
				ThriveQuizB.router.navigate( '#dashboard', {trigger: true} );
			} );
		},
		/**
		 * Edit page test route
		 * @param id int
		 */
		editQuizPageTest: function ( id ) {
			if ( this.view ) {
				this.view.remove();
			}


			if ( TVE_Dash.opened_modal_view ) {
				TVE_Dash.opened_modal_view.close();
			}

			$( '.tvd-material-tooltip' ).hide();

			if ( ! id ) {
				ThriveQuizB.router.navigate( '#dashboard', {trigger: true} );
				return;
			}

			var self = this,
				model = new ThriveQuizB.models.Test( {id: id} );

			TVE_Dash.showLoader();
			model.fetch( {
				success: function ( model, response, options ) {
					if ( model ) {
						model.set( 'status', Number( model.get( 'status' ) ) );
						self.view = new ThriveQuizB.views.QuizPageTest( {
							model: model,
							completed_tests: new ThriveQuizB.collections.Tests( model.get( 'completed_tests' ) )
						} );
						self.$el.html( self.view.$el );
						self.view.render();

						self.set_page( 'dashboard/page', model.get( 'title' ), [
							{
								route: 'dashboard',
								label: ThriveQuizB.t.Dashboard
							},
							{
								route: 'dashboard/quiz/' + model.get( 'quiz_id' ),
								label: model.get( 'quiz_name' )
							}
						] );

					}
					TVE_Dash.hideLoader();
				}
			} ).error( function ( response ) {
				TVE_Dash.err( response.responseText );
				TVE_Dash.hideLoader();
				ThriveQuizB.router.navigate( '#dashboard', {trigger: true} );
			} );

		},
		/**
		 * Edit page type route callback
		 * @param quizID
		 * @param pageType
		 */
		editQuizPages: function ( id, is_default ) {
			if ( this.view ) {
				this.view.remove();
			}

			if ( TVE_Dash.opened_modal_view ) {
				TVE_Dash.opened_modal_view.close();
			}

			$( '.tvd-material-tooltip' ).hide();

			if ( ! id ) {
				ThriveQuizB.router.navigate( '#dashboard', {trigger: true} );
				return;
			}
			var self = this,
				model = new ThriveQuizB.models.Page( {ID: id} );

			TVE_Dash.showLoader();
			model.fetch( {
				data: {viewed: true},
				success: function ( model, response, options ) {
					if ( is_default ) {
						model.set( 'is_default', true );
					}

					self.view = new ThriveQuizB.views.QuizPageVariations( {
						model: model,
						completed_tests: new ThriveQuizB.collections.Tests( model.get( 'completed_tests' ) )
					} );
					self.$el.html( self.view.render().$el );
					self.set_page( 'dashboard/page', model.get( 'tqb_page_name' ), [
						{
							route: 'dashboard',
							label: ThriveQuizB.t.Dashboard
						},
						{
							route: 'dashboard/quiz/' + model.get( 'post_parent' ),
							label: model.get( 'quiz_name' )
						}
					] );

					TVE_Dash.hideLoader();
				}
			} ).error( function ( response ) {
				TVE_Dash.err( response.responseText );
				TVE_Dash.hideLoader();
				ThriveQuizB.router.navigate( '#dashboard', {trigger: true} );
			} );

		},
		purgeCache: function () {
			TVE_Dash.showLoader();
			$.ajax( {
				type: 'post',
				url: ajaxurl,
				dataType: 'json',
				data: {
					action: 'tqb_admin_ajax_controller',
					route: 'settings',
					custom: 'purge_cache',
					_nonce: ThriveQuizB.admin_nonce
				}
			} ).done( _.bind( function ( response ) {
				ThriveQuizB.globals.quizzes.reset( response );
				this.navigate( "#dashboard", {trigger: true} );
			}, this ) ).always( function () {
				TVE_Dash.hideLoader();
			} );
		}

	} );

	$( function () {
		ThriveQuizB.globals.quizzes = new ThriveQuizB.collections.Quizzes( ThriveQuizB.data.quizzes );
		ThriveQuizB.globals.badge_templates = new ThriveQuizB.collections.BadgeTemplates( ThriveQuizB.badge_templates );

		ThriveQuizB.router = new Router;
		ThriveQuizB.router.init_breadcrumbs();

		Backbone.history.stop();
		Backbone.history.start( {hashchange: true} );
		if ( ! Backbone.history.fragment ) {
			ThriveQuizB.router.navigate( '#dashboard', {trigger: true} );
		}
	} );

})( jQuery );;var ThriveQuizB = ThriveQuizB || {};

(function () {
	ThriveQuizB.LineChart = Backbone.Model.extend( {
		defaults: function () {
			return {
				id: '',
				title: '',
				renderTo: '',
				type: 'line',
				data: []
			};

		},
		initialize: function () {
			var title = this.get( 'title' ),
				type = this.get( 'type' ),
				renderTo = this.get( 'renderTo' ),
				legend = this.get( 'legend' );
			this.chart = this.dochart( title, type, renderTo, legend );
		},
		redraw: function () {
			var title = this.get( 'title' ),
				data = this.get( 'data' ),
				x_axis = this.get( 'x_axis' ),
				y_axis = this.get( 'y_axis' ),
				ids = [],
				x_axis_length = this.get( 'x_axis' ).length;

			//add series or update data if it already exists
			for ( var i in data ) {
				ids.push( data[i].id );
				var series = this.chart.get( data[i].id );
				if ( series === null ) {
					this.chart.addSeries( data[i], false, false )
				} else {
					series.setData( data[i].data );
				}
			}
			//delete old series
			for ( var i = 0; i < this.chart.series.length; i ++ ) {
				if ( ids.indexOf( this.chart.series[i].options.id ) < 0 ) {
					this.chart.series[i].remove( false );
					i --;
				}
			}

			this.chart.get( 'time_interval' ).setCategories( x_axis );
			this.chart.xAxis[0].update( {
				tickInterval: x_axis_length > 13 ? Math.ceil( x_axis_length / 13 ) : 1
			} );

			this.chart.setTitle( {text: title} );
			if ( this.chart.yAxis[0].axisTitle ) {
				this.chart.yAxis[0].axisTitle.attr( {
					text: y_axis
				} );
			}
			this.chart.redraw();
			this.chart.hideLoading();
		},
		showLoading: function () {
			this.chart.showLoading();
		},
		hideLoading: function () {
			this.chart.hideLoading();
		},
		dochart: function ( title, type, renderTo, customLegend ) {
			var legend = {}
			if ( customLegend ) {
				legend = {
					layout: 'vertical',
					backgroundColor: '#FFFFFF',
					align: 'right',
					verticalAlign: 'top',
					floating: false,
					y: 75,
					x: - 50
				}
			}
			;
			return new Highcharts.Chart( {
				chart: {
					type: type,
					renderTo: renderTo,
					style: {
						fontFamily: 'Open Sans,sans-serif'
					}
				},
				colors: ThriveQuizB.chart_colors,
				yAxis: {
					allowDecimals: false,
					title: {
						text: 'Conversions'
					},
					min: 0
				},
				xAxis: {
					id: 'time_interval'
				},
				credits: {
					enabled: false
				},
				title: {
					text: title
				},
				legend: legend,
				tooltip: {
					shared: false,
					useHTML: true,
					formatter: function () {
						if ( this.series.type == 'scatter' ) {
							/* We don't display tooltips for the scatter graph */
							return false;
						} else {
							return this.x + '<br/>' +
							       this.series.name + ': ' + '<b>' + this.y + '</b>';
						}
					}
				},
				plotOptions: {
					series: {
						dataLabels: {
							shape: 'callout',
							backgroundColor: 'rgba(0, 0, 0, 0.75)',
							style: {
								color: '#FFFFFF',
								textShadow: 'none'
							}
						},
						events: {
							legendItemClick: function () {
								if ( this.type == 'scatter' ) {
									/* The labels are not hidden by clicking on the legend so we have to do it manually */
									if ( this.visible ) {
										jQuery( '.highcharts-data-labels' ).hide();
									} else {
										jQuery( '.highcharts-data-labels' ).show();
									}
								}
							}
						}
					}
				}
			} );
		}
	} );

	ThriveQuizB.PieChart = Backbone.Model.extend( {
		defaults: function () {
			return {
				id: '',
				title: '',
				data: []
			};

		},
		initialize: function () {
			var title = this.get( 'title' ),
				data = this.get( 'data' );
			this.chart = this.dopie( title );
		},
		redraw: function () {
			var title = this.get( 'title' ),
				data = this.get( 'data' ),
				x_axis = this.get( 'x_axis' );

			this.chart.series[0].setData( data );
			this.chart.setTitle( title );
			this.chart.redraw();
			this.chart.hideLoading();
		},
		showLoading: function () {
			this.chart.showLoading();
		},
		hideLoading: function () {
			this.chart.hideLoading();
		},
		dopie: function ( title ) {
			return new Highcharts.Chart( {
				chart: {
					renderTo: 'tve-report-chart',
					plotBackgroundColor: null,
					plotBorderWidth: null,
					plotShadow: false
				},
				colors: ThriveQuizB.chart_colors,
				credits: {
					enabled: false
				},
				title: {
					text: title
				},
				plotOptions: {
					pie: {
						allowPointSelect: false,
						cursor: 'default',
						showInLegend: true,
						dataLabels: {
							enabled: true,
							format: '<b>{point.name}</b>: {point.percentage:.1f} %',
							style: {
								color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
							}
						}
					}
				},
				tooltip: {
					formatter: function () {
						return this.key + ': ' + parseInt( this.y );
					}
				},
				series: [
					{
						type: 'pie',
						name: title
					}
				]
			} );
		}
	} );

	ThriveQuizB.Pie3DChart = Backbone.Model.extend( {
		defaults: function () {
			return {
				id: '',
				data: [],
				colors: []
			};
		},
		initialize: function () {
			var renderTo = this.get( 'renderTo' ),
				data = this.get( 'data' ),
				array =[],
				total = this.get( 'total' );
			data.forEach( function ( element, index ) {
				if(element.percent > 0){
					var temp = {};
					temp.name = element.text;
					temp.y = element.percent;
					temp.color = element.color;
					array.push(temp);
				}
			});
			this.doChart( array, renderTo );

		},
		showLoading: function () {
			this.chart.showLoading();
		},
		hideLoading: function () {
			this.chart.hideLoading();
		},
		doChart: function ( data, renderTo ) {
			return new Highcharts.Chart( {
				tooltip: {
					pointFormat: '<b>{point.percentage:.1f}%</b>'
				},
				legend: {
					enabled: false
				},
				chart: {
					renderTo: renderTo,
					type: 'pie',
					options3d: {
						enabled: true,
						alpha: 25,
						beta: 0
					}
				},
				plotOptions: {
					pie: {
						allowPointSelect: true,
						cursor: 'pointer',
						depth: 35,
						showInLegend: false,
						dataLabels: {
							enabled: false
						}
					}
				},
				title: {
					text: ''
				},
				series: [
					{
						type: 'pie',
						data: data
					}
				]
			} );

		}
	} );

	ThriveQuizB.BarChart = Backbone.Model.extend( {
		defaults: function () {
			return {
				id: '',
				data: [],
				colors: []
			};

		},
		initialize: function () {
			var renderTo = this.get( 'renderTo' ),
				data = this.get( 'data' ),
				colors = this.get( 'colors' ),
				maxRange = 0,
				minRange = 100,
				minDeviation = 100;

			//find the chart range
			for ( var i in data ) {
				if ( data[i][0] < minRange ) {
					minRange = data[i][0];
				}
				if ( data[i][data[i].length - 1] > maxRange ) {
					maxRange = data[i][data[i].length - 1];
				}
				if ( (data[i][data[i].length - 1] - data[i][0]) / 2 < minDeviation ) {
					minDeviation = (data[i][data[i].length - 1] - data[i][0]) / 2;
				}
			}

			minRange = minRange > minDeviation / 3 ? minRange - minDeviation / 3 : 0;
			maxRange = maxRange + minDeviation / 3;
			for ( var i in data ) {
				var new_data = [],
					middle;
				for ( var j = 1; j < data[i].length; j ++ ) {
					new_data.push( {
						data: [
							{
								low: data[i][j - 1],
								high: data[i][j]
							}
						]
					} );
				}
				middle = (data[i][0] + data[i][data[i].length - 1]) / 2;
				this.doChart( new_data, middle, minRange, maxRange, colors[i], renderTo + '-' + i );
			}
		},
		showLoading: function () {
			this.chart.showLoading();
		},
		hideLoading: function () {
			this.chart.hideLoading();
		},
		doChart: function ( data, middle, minRange, maxRange, colors, renderTo ) {
			return new Highcharts.Chart( {
				chart: {
					renderTo: renderTo,
					type: 'columnrange',
					inverted: true,
					height: 50,
					width: 240,
					spacing: [10, 10, 10, 10]
				},
				colors: colors,
				plotOptions: {
					columnrange: {
						grouping: false
					}
				},
				title: {
					text: ''
				},
				xAxis: {
					title: {
						text: ''
					},
					labels: {
						enabled: false
					},
					lineWidth: 0,
					minorGridLineWidth: 0,
					lineColor: 'transparent',
					minorTickLength: 0,
					tickLength: 0
				},
				yAxis: {
					title: {
						text: ''
					},
					labels: {
						enabled: false
					},
					plotLines: [
						{
							color: '#000000',
							value: middle,
							width: 1,
							zIndex: 100
						}
					],
					min: minRange,
					max: maxRange,
					lineWidth: 0,
					minorGridLineWidth: 0,
					lineColor: 'transparent',
					minorTickLength: 0,
					tickLength: 0
				},
				legend: {
					enabled: false
				},
				credits: {
					enabled: false
				},
				tooltip: {
					enabled: false
				},
				series: data
			} );

		}
	} );
})();
