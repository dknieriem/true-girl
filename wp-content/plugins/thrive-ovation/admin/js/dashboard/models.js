/**
 * Thrive Headline Optimizer Models and Collections
 */

var ThriveOvation = ThriveOvation || {};
ThriveOvation.models = ThriveOvation.models || {};
ThriveOvation.collections = ThriveOvation.collections || {};

(function ( $ ) {

	Backbone.emulateHTTP = true;

	/**
	 * Base Model and Collection
	 */
	ThriveOvation.models.Base = Backbone.Model.extend( {
		idAttribute: 'id',
		/**
		 * Set nonce header before every Backbone sync.
		 *
		 * @param {string} method.
		 * @param {Backbone.Model} model.
		 * @param {{beforeSend}, *} options.
		 * @returns {*}.
		 */
		sync: function ( method, model, options ) {
			var beforeSend;

			options = options || {};

			options.cache = false;

			if ( ! _.isUndefined( ThriveOvation.nonce ) && ! _.isNull( ThriveOvation.nonce ) ) {
				beforeSend = options.beforeSend;

				options.beforeSend = function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', ThriveOvation.nonce );

					if ( beforeSend ) {
						return beforeSend.apply( this, arguments );
					}
				};
			}

			return Backbone.sync( method, model, options );
		}
	} );

	ThriveOvation.collections.Base = Backbone.Collection.extend( {
		/**
		 * Set nonce header before every Backbone sync.
		 *
		 * @param {string} method.
		 * @param {Backbone.Model} model.
		 * @param {{beforeSend}, *} options.
		 * @returns {*}.
		 */
		sync: function ( method, model, options ) {
			var beforeSend;

			options = options || {};
			options.cache = false;
			options.url = this.url();

			if ( ! _.isUndefined( ThriveOvation.nonce ) && ! _.isNull( ThriveOvation.nonce ) ) {
				beforeSend = options.beforeSend;

				options.beforeSend = function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', ThriveOvation.nonce );

					if ( beforeSend ) {
						return beforeSend.apply( this, arguments );
					}
				};
			}

			return Backbone.sync( method, model, options );
		}
	} );


	ThriveOvation.models.Settings = ThriveOvation.models.Base.extend( {
		url: ThriveOvation.routes.settings

	} );

	ThriveOvation.models.PostMeta = ThriveOvation.models.Base.extend( {
		defaults: {
			key: '',
			meta_key: '',
			meta_value: ''
		},
		url: function () {
			return ThriveOvation.routes.postmeta + '/' + this.get( 'key' );
		}
	} );

	ThriveOvation.models.Filters = ThriveOvation.models.Base.extend( {
		defaults: {
			show_hide_tags: 1,
			show_hide_type: 1,
			show_hide_status: 1,
			testimonial_content: 'summary'
		},
		url: function () {
			return ThriveOvation.routes.filters;
		}
	} );

	ThriveOvation.models.Testimonial = ThriveOvation.models.Base.extend( {
		defaults: {
			title: '',
			name: '',
			date: '',
			email: '',
			role: '',
			website_url: '',
			picture_url: '',
			tags: [],
			content: '',
			status: - 1,
			source: '',
			sent_emails: 0
		},
		url: function () {
			var url = ThriveOvation.routes.testimonials;
			if ( this.get( 'id' ) ) {
				url += '/' + this.get( 'id' );
			}
			return url;
		}
	} );

	ThriveOvation.models.copyTestimonial = ThriveOvation.models.Base.extend( {
		defaults: {
			title: '',
			name: '',
			date: '',
			email: '',
			role: '',
			website_url: '',
			picture_url: '',
			tags: [],
			content: '',
			status: - 1,
			source: '',
			sent_emails: 0
		},
		url: function () {
			return ThriveOvation.routes.testimonials +'/copy';
		}
	} );


	ThriveOvation.models.Tag = ThriveOvation.models.Base.extend( {
		idAttribute: 'term_id',
		defaults: {
			name: '',
			post_id: ''
		},
		url: function () {
			var url = ThriveOvation.routes.tags;
			if ( this.get( 'term_id' ) ) {
				url += '/' + this.get( 'term_id' );
			}
			return url;
		}
	} );

	ThriveOvation.models.ActivityLogEntry = ThriveOvation.models.Base.extend( {
		defaults: {
			id: '',
			post_id: '',
			text: '',
			class: '',
			date: ''
		}
	} );

	ThriveOvation.models.Shortcode = ThriveOvation.models.Base.extend( {
defaults: {
			name: '',
			content: '',
			type: '',
			thumbnail: ''
		},
		url: function () {
			var url = ThriveOvation.routes.shortcodes;
			if ( this.get( 'id' ) ) {
				url += '/' + this.get( 'id' );
			}
			return url;
		}
	} );

	ThriveOvation.collections.Shortcodes = ThriveOvation.collections.Base.extend( {
		model: ThriveOvation.models.Shortcode,
		url: function () {
			return ThriveOvation.routes.shortcodes
		},
		comparator: function ( model ) {
			return model.get( 'id' );
		},
		parse: function ( shortcodes ) {
			shortcodes.forEach( function ( item, index ) {
				if ( item.config.template && ( item.config.template.length > 1 ) ) {
					var type;
					if ( item.config.type == 'display' ) {
						type = item.config.template.split( '/', 1 )[0];
					} else if ( item.config.type == 'capture' ) {
						type = 'capture';
					}

					shortcodes[index].thumbnail = ThriveOvation.admin_url + 'img/shortcode_' + type + '.jpg';
				} else {
					shortcodes[index].thumbnail = '';
				}
			} );
			return shortcodes;
		}
	} );

	ThriveOvation.models.LandingCombo = ThriveOvation.models.Base.extend( {
		defaults: {
			'id': '',
			'title': ''
		}
	} );

	ThriveOvation.collections.LandingCombo = ThriveOvation.collections.Base.extend( {
		model: ThriveOvation.models.LandingCombo
	});

	ThriveOvation.collections.ActivityLog = ThriveOvation.collections.Base.extend( {
		model: ThriveOvation.models.ActivityLogEntry,
		url: function () {
			return ThriveOvation.routes.testimonials + '/activity/';
		}
	} );

	/**
	 * Collection of Campaigns
	 * It will be used for READ and CREATE routes
	 */
	ThriveOvation.collections.Testimonials = ThriveOvation.collections.Base.extend( {
		model: ThriveOvation.models.Testimonial,
		url: function () {
			return ThriveOvation.routes.testimonials;
		},
		comparator: function ( model ) {
			return - model.get( 'id' );
		}
	} );

	ThriveOvation.models.AssetWizardConnection = ThriveOvation.models.Base.extend( {
		idAttribute: 'ID',
		defaults: function () {
			return {
				'connection': "",
				'active': "",
				'connection_instance': ""
			}
		}
	} );

	ThriveOvation.models.NewConnection = ThriveOvation.models.Base.extend( {
		idAttribute: 'ID',
		defaults: {
			'connected_apis': {}
		},

		url: function () {
			return ThriveOvation.routes.api + '/' + this.get( 'connection' );
		}
	} );


	/**
	 *  Landing page config modal page - model
	 */
	ThriveOvation.models.LandingPageConfig = ThriveOvation.models.Base.extend( {
		defaults: {
			'approve': '',
			'approve_url': '',
			'approve_post_id': '',
			'approve_post_val': '',
			'not_approve': '',
			'not_approve_url': '',
			'not_approve_post_id': '',
			'not_approve_post_val': ''
		},
		url: function () {
			return ThriveOvation.routes.settings + '/landing-page/config';
		}
	} );



	ThriveOvation.models.EmailConfig = ThriveOvation.models.Base.extend( {
		defaults: {
			subject: '',
			template: ''
		},
		url: function () {
			return ThriveOvation.routes.settings + '/email/config';
		}
	} );

	ThriveOvation.models.ConfirmEmailSend = ThriveOvation.models.Base.extend( {
		defaults: {}
	} );

	/**
	 * Collection of Available email Apis
	 */

	ThriveOvation.collections.AssetConnection = ThriveOvation.collections.Base.extend( {
		model: ThriveOvation.models.AssetWizardConnection

	} );

})( jQuery );