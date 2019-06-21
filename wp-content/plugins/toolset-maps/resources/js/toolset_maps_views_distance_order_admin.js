var WPViews = WPViews || {};

/**
 * Admin side JS for distance order functionality
 * @param $
 * @constructor
 * @since 1.5
 */
WPViews.DistanceOrderAdmin = function( $ ) {
	"use strict";

	let self = this;

	const API_GOOGLE = 'google';

	self.centerSelector = '.js-wpv-posts-distance-order-center';
	self.centerURLParameterSelector = '.js-wpv-posts-distance-order-center-url-parameter';
	self.sourceSelector = '.js-wpv-posts-distance-order-source';
	self.postOrderByAsSelector = '#wpv-settings-orderby-as';
	self.userOrderByAsSelector = '#wpv-settings-user-orderby-as';
	self.taxonomyOrderByAsSelector = '#wpv-settings-taxonomy-orderby-as';
	self.orderByAsSelectors = self.postOrderByAsSelector + ', ' + self.userOrderByAsSelector + ', '
		+ self.taxonomyOrderByAsSelector;
	self.usersAndTaxonomyOrderBySelectors = '.js-wpv-users-orderby, .js-wpv-taxonomy-orderby';

	// Gets filled from backend side with all address field names using wp_add_inline_script
	self.addressFieldNames = [];

	/**
	 * Register event listener early on, because it will be triggered right on document ready, and then it's a race
	 * and can be too late.
	 */
	self.earlyInit = function(){
		$( document ).on(
			'js_event_wpv_sorting_posts_update_order_availability',
			self.onSortingUpdateOrderAvailability
		);
	};

	self.init = function() {
		// Register for event to add our data fields
		$( document ).on('js_event_wpv_save_view_sorting_options_additional_data', self.onSaveViewSortingOptions );

		// Trigger ajax saving on our added fields changes
		$( self.sourceSelector ).change( WPViews.view_edit_screen.sorting_debounce_update );
		if ( API_GOOGLE === WPViews.distanceOrderAdmin.apiUsed ) {
			$( self.centerSelector ).on( 'geocode:result', WPViews.view_edit_screen.sorting_debounce_update );
		} else {
			$( self.centerSelector ).change( WPViews.view_edit_screen.sorting_debounce_update );
		}
		$( self.centerURLParameterSelector ).change( WPViews.view_edit_screen.sorting_debounce_update );

		// React on Order by change (but only for users and taxonomy, posts are covered with
		// js_event_wpv_sorting_update_order_availability
		$( self.usersAndTaxonomyOrderBySelectors ).change( self.onOrderByChangeForUsersAndTaxonomy );

		// Show distance order center field and init geocomplete when needed
		$( self.sourceSelector ).change( self.onDistanceOrderSourceChange );

		// When "order by as" is changed, hide secondary sorting and show source selector field only with distance order
		$( self.orderByAsSelectors ).change( function(){
			var toggle = ( getOrderByAs() === 'DISTANCE' );

			toggleSecondarySorting( toggle );
			toggleSourceSelect( toggle );
			toggleDistanceOrderCenterField( getSource(), getOrderByAs() );
		} );

		// Init geocomplete if needed
		toggleDistanceOrderCenterField( getSource(), getOrderByAs() );
	};

	/**
	 * Disable 'As a distance from' option from custom fields which are not of type address
	 * @param {string} orderby
	 */
	const enableOrDisableAsADistanceFromDependingOnCustomFieldType = function( orderby ) {
		if ( _.contains( self.addressFieldNames, orderby ) ) {
			$( self.orderByAsSelectors ).children( 'option[value="DISTANCE"]' ).attr('disabled', false);
		} else {
			$( self.orderByAsSelectors ).children( 'option[value="DISTANCE"]' ).attr('disabled', true);
		}
	};

	/**
	 * Disables secondary order option when ordering by location is used - it couldn't work because ordering by address
	 * is done in postprocessing. (Secondary order would actually be executed first on db query, and then reordered
	 * by postprocessing.)
	 *
	 * @since 1.5
	 * @since 1.5.3 Also disables 'As a distance from' on custom fields which are not address
	 * @listens js_event_wpv_sorting_posts_update_order_availability
	 * @param {Event} event
	 * @param {Object} orderbys
	 */
	self.onSortingUpdateOrderAvailability = function( event, orderbys ) {
		// This is is only for posts queries
		if ( getQueryType() !== 'posts' ) return;

		var toggle = (
			_.contains( self.addressFieldNames, orderbys.orderby )
			&& getOrderByAs() === 'DISTANCE'
		);

		toggleSecondarySorting( toggle );
		toggleSourceSelect( toggle );
		toggleDistanceOrderCenterField( getSource(), getOrderByAs() );
		enableOrDisableAsADistanceFromDependingOnCustomFieldType( orderbys.orderby );
	};

	/**
	 * @since 1.5
	 * @since 1.5.3 Also disables 'As a distance from' on custom fields which are not address
	 * @param {Event} event
	 */
	self.onOrderByChangeForUsersAndTaxonomy = function( event ) {
		var fieldName = event.target.value.replace( 'user-', '' ).replace( 'taxonomy-', '' );
		var toggle = (
			_.contains( self.addressFieldNames, fieldName )
			&& getOrderByAs() === 'DISTANCE'
		);

		toggleSourceSelect( toggle );
		toggleDistanceOrderCenterField( getSource(), getOrderByAs() );
		enableOrDisableAsADistanceFromDependingOnCustomFieldType( fieldName );
	};

	/**
	 * @param {Boolean} toggle
	 */
	const toggleSecondarySorting = function( toggle ) {
		var $orderSecondary = $( '.js-wpv-settings-posts-order-secondary' );

		if ( toggle ) {
			$orderSecondary.hide();
		} else {
			$orderSecondary.fadeIn( 'fast' );
		}
	};

	/**
	 * Adds our fields to ajax save function data.
	 *
	 * @listens js_event_wpv_save_view_sorting_options_additional_data
	 * @param {Event} event
	 * @param {Object} data
	 */
	self.onSaveViewSortingOptions = function( event, data ) {
		data.distance_order = {};
		data.distance_order.source = getSource();
		data.distance_order.center = getCenter();
		data.distance_order.url_parameter = getURLParameter();
	};

	/**
	 * @listens #distance_order_source:change
	 * @param {Event} event
	 */
	self.onDistanceOrderSourceChange = function( event ) {
		toggleDistanceOrderCenterField( event.target.value, getOrderByAs() );
	};

	/**
	 * @param {String} distanceOrderSource
	 */
	const toggleDistanceOrderCenterField = function( distanceOrderSource, orderByAs ) {
		$( self.centerSelector+', '+self.centerURLParameterSelector ).hide();

		if ( orderByAs !== 'DISTANCE' ) return;

		if ( distanceOrderSource === 'fixed' ) {
			let $centerAutocompleteField = $(self.centerSelector);

			$centerAutocompleteField.show();
			if (API_GOOGLE === WPViews.distanceOrderAdmin.apiUsed) {
				$centerAutocompleteField.geocomplete({types: []});
			} else {
				// This object might still be uninstantiated on early init, so instantiate it in that case.
				if (typeof WPViews.mapsAddressAutocomplete === 'undefined') {
					WPViews.mapsAddressAutocomplete = new WPViews.MapsAddressAutocomplete($);
				}
			}
		} else if ( distanceOrderSource === 'url_parameter' ) {
			$( self.centerURLParameterSelector ).show();
		}
	};

	/**
	 * @param {Boolean} toggle
	 */
	const toggleSourceSelect = function( toggle ) {
		if ( toggle ) {
			$( convertClassNames2Selectors( getQueryTypeClass() ) ).find( self.sourceSelector ).show();
		} else {
			$( self.sourceSelector ).filter(':visible').hide();
		}
	};

	/**
	 * @param {String} classes
	 * @return {String}
	 */
	const convertClassNames2Selectors = function( classes ) {
		return '.' + classes.replace(' ', '.');
	};

	/**
	 * @return {String}
	 */
	const getSource = function() {
		return String( $( self.sourceSelector+':visible' ).val() );
	};

	/**
	 * Because there are multiple fields with same selector (post, user, term), we want the value of the visible one.
	 *
	 * Returns empty string instead of 'undefined' if there are no visible center fields.
	 *
	 * @return {String}
	 */
	const getCenter = function() {
		var value = $( self.centerSelector+':visible').val();
		var center = value ? value : '';

		return center;
	};

	/**
	 * @since 1.6
	 * @return {String}
	 */
	const getURLParameter = function() {
		return String( $( self.centerURLParameterSelector+':visible' ).val() );
	};

	/**
	 * @return {String}
	 */
	const getOrderByAs = function() {
		switch ( getQueryTypeClass() ) {
			case 'wpv-settings-query-type-posts js-wpv-settings-posts-order':
				return String( $( self.postOrderByAsSelector ).val() );
			case 'wpv-settings-query-type-users':
				return String( $( self.userOrderByAsSelector ).val() );
			case 'wpv-settings-query-type-taxonomy':
				return String( $( self.taxonomyOrderByAsSelector ).val() );
			default:
				return '';
		}
	};

	/**
	 * @return {String}
	 */
	const getQueryTypeClass = function() {
		return $('.js-wpv-settings-ordering').find('ul:visible').attr('class');
	};

	/**
	 * Gives a readable query type.
	 * @return {string}
	 */
	const getQueryType = function() {
		switch ( getQueryTypeClass() ) {
			case 'wpv-settings-query-type-posts js-wpv-settings-posts-order':
				return 'posts';
			case 'wpv-settings-query-type-users':
				return 'users';
			case 'wpv-settings-query-type-taxonomy':
				return 'taxonomy';
			default:
				return '';
		}
	}
};

WPViews.distanceOrderAdmin = new WPViews.DistanceOrderAdmin( jQuery );
WPViews.distanceOrderAdmin.earlyInit();

jQuery( document ).ready( function() {
	WPViews.distanceOrderAdmin.init();
});