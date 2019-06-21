var WPViews = WPViews || {};

/**
 * Location class. Use it to get current visitor location (either from navigator.geolocation or saved in cookie)
 *
 * Usage example:
 * WPViews.location.init();
 * if ( WPViews.location.isSet() ) {
 *     doSomethingWithLocation( WPViews.location.getLat(), WPViews.location.getLng() );
 * }
 *
 * @param jQuery $
 * @constructor
 * @since 1.5
 */
WPViews.Location = function( $ ) {
	"use strict";

	var self = this;

	self.lat = null;
	self.lng = null;
	self.cookieName = 'toolset_maps_location';

	/**
	 * Obtains location, either from saved cookie, or navigator.geolocation
	 * @param {boolean} [reload]
	 */
	self.obtainLocation = function( reload ) {
		var location = Cookies.get( self.cookieName );
		if ( location ) {
			var latLong = location.split(',');
			self.lat = latLong[0];
			self.lng = latLong[1];
			return;
		}

		if ( navigator.geolocation ) {
			navigator.geolocation.getCurrentPosition(
				function ( position ) {
					self.lat = position.coords.latitude;
					self.lng = position.coords.longitude;
					self.saveLocation();
					if ( reload ) {
						window.location.reload( false );
					}
				},
				function ( position_error ) {
					console.log( position_error );
				}
			);
		}
	};

	/**
	 * Check if location is available.
	 * @return {Boolean}
	 */
	self.isSet = function() {
		return ( self.lat && self.lng );
	};

	/**
	 * Saves location in cookie
	 */
	self.saveLocation = function() {
		Cookies.set( self.cookieName, self.lat + ',' + self.lng );
	};

	/**
	 * @return {null|Number}
	 */
	self.getLat = function() {
		return self.lat;
	};

	/**
	 * @return {null|Number}
	 */
	self.getLng = function() {
		return self.lng;
	};

	/**
	 * Obtains location (use this if you only need it in frontend code)
	 */
	self.init = function() {
		self.obtainLocation();
	};

	/**
	 * Obtains location and reloads the page (so location cookie becomes available to backend)
	 */
	self.initWithReload = function() {
		self.obtainLocation( true );
	};
};

jQuery( document ).ready( function( $ ) {
	WPViews.location = new WPViews.Location( $ );
});