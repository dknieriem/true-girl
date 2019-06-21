var WPViews = WPViews || {};

WPViews.AddonMapsPreview = function( $ ) {
	'use strict';

	var self = this;

	/**
	 * Renders a preview map with given style
	 * @param style_json
	 */
	self.render_preview_map = function ( style_json ) {
		WPViews.view_addon_maps.init_map_after_loading_styles({
			map: "map-preview",
			markers: [{
				marker: "marker-preview",
				markericon: "",
				markericonhover: "",
				markerinfowindow: "",
				markerlat: 43.5039342,
				markerlon: 16.5237792
			}],
			cluster_options: {
				cluster: "off",
				gridSize: 60,
				maxZoom: null,
				minimumClusterSize:	2,
				zoomOnClick: true
			},
			options: {
				background_color: "",
				cluster: "off",
				double_click_zoom: "on",
				draggable: "on",
				fitbounds: "on",
				full_screen_control: "off",
				general_center_lat: 43.5138889,
				general_center_lon: 16.4558333,
				general_zoom: 5,
				map_type: "roadmap",
				map_type_control: "on",
				marker_icon: "",
				marker_icon_hover: "",
				scrollwheel: "on",
				show_layer_interests: false,
				single_center: "on",
				single_zoom: 10,
				street_view_control: "on",
				style_json: style_json
			}
		});
	};



};

jQuery( document ).ready( function( $ ) {
	WPViews.addon_maps_preview = new WPViews.AddonMapsPreview( $ );
});