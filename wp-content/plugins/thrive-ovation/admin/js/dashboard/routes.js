/**
 * Thrive Ovation Routers
 */

var ThriveOvation = ThriveOvation || {};
ThriveOvation.objects = ThriveOvation.objects || {};

(function ( $ ) {
	var Router = Backbone.Router.extend( {
		view: null,
		$el: $( "#tvo-dashboard-wrapper" ),
		routes: {
			"testimonials": 'testimonials',
			"settings": 'settings',
			"testimonials(/:id)": 'testimonial',
			"socialimport": 'social_media_import',
			"shortcodes/(:type)": 'short_codes'
		},
		params: {},
		/**
		 * Display Dashboard - Test Items Collections
		 */
		initialize: function () {
			this.breadcrumbs = new ThriveOvation.views.Breadcrumbs( {
				el: $( '.tvo-breadcrumbs' )
			} );
		},
		route: function ( route, name, callback ) {
			var router = this;
			if ( ! callback ) {
				callback = this[name];
			}
			var f = function () {
				ThriveOvation.util.clearMCEEditor();
				callback.apply( router, arguments );
			};
			return Backbone.Router.prototype.route.call( this, route, name, f );
		},
		social_media_import: function () {

			this.breadcrumbs.render( ThriveOvation.util.Breadcrumbs( ThriveOvation.breadcrumbs, 'socialimport' ) );

			if ( this.view ) {
				this.view.remove();
			}

			this.view = new ThriveOvation.views.SocialMediaImport();
			this.$el.html( this.view.render().$el );

		},
		testimonials: function () {
			TVE_Dash.showLoader();

			this.breadcrumbs.render( ThriveOvation.util.Breadcrumbs( ThriveOvation.breadcrumbs, 'testimonials' ) );

			if ( this.view ) {
				this.view.remove();
			}
			this.view = new ThriveOvation.views.Testimonials();
			this.$el.html( this.view.$el );
		},
		testimonial: function ( id ) {

			this.breadcrumbs.render( ThriveOvation.util.Breadcrumbs( ThriveOvation.breadcrumbs, 'testimonial' ) );

			if ( this.view ) {
				this.view.remove();
				jQuery( '.tvd-material-tooltip' ).hide();
			}
			ThriveOvation.objects.Testimonial = new ThriveOvation.models.Testimonial( {id: id} );
			ThriveOvation.objects.Testimonial.fetch();
			ThriveOvation.objects.TestimonialView = new ThriveOvation.views.Testimonial( {
				model: ThriveOvation.objects.Testimonial
			} );
			this.$el.html( ThriveOvation.objects.TestimonialView.$el );
		},
		short_codes: function ( type ) {

			if ( type !== 'capture' && type !== 'display' ) {
				this.testimonials();
				return;
			}

			this.breadcrumbs.render( ThriveOvation.util.Breadcrumbs( ThriveOvation.breadcrumbs, type + '-shortcodes' ) );

			if ( this.view ) {
				this.view.remove();
			}

			ThriveOvation.objects.Shortcodes = new ThriveOvation.collections.Shortcodes();

			var view = new ThriveOvation.views.Shortcodes( {
				type: type,
				collection: ThriveOvation.objects.Shortcodes
			} );

			ThriveOvation.objects.Shortcodes.fetch( {
				data: {
					type: type
				},
				success: function () {
					view.render();
					ThriveOvation.util.bind_wistia();
				}
			} );

			this.$el.html( view.$el );
		},
		settings: function () {

			TVE_Dash.showLoader();

			this.breadcrumbs.render( ThriveOvation.util.Breadcrumbs( ThriveOvation.breadcrumbs, 'settings' ) );

			if ( this.view ) {
				this.view.remove();
			}

			ThriveOvation.objects.Settings = new ThriveOvation.models.Settings();
			ThriveOvation.objects.Settings.fetch();

			this.view = new ThriveOvation.views.Setting( {
				model: ThriveOvation.objects.Settings,
			} );
			this.$el.html( this.view.$el );
		}
	} );

	$( function () {
		ThriveOvation.router = new Router;
		Backbone.history.start( {hashchange: true} );
		if ( ! Backbone.history.fragment ) {
			ThriveOvation.router.navigate( '#testimonials', {trigger: true} );
		}
	} );
})( jQuery );