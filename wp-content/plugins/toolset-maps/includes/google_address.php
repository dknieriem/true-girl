<?php

/**
* Toolset Maps - Types field editor GUI and definition
*
* @note This file can not be renamed, since the field slug must match the file name
*
* @package ToolsetMaps
*
* @since 0.1
*/

// Editor GUI class

class Toolset_Addon_Maps_Types {
	
	static $types_postmeta_fields = array();
	static $types_postmeta_fields_slugs = array();
	static $types_usermeta_fields = array();
	static $types_usermeta_fields_slugs = array();
	static $types_termmeta_fields = array();
	static $types_termmeta_fields_slugs = array();
	
	function __construct() {
		$types_opt = get_option( 'wpcf-fields', array() );
		$types_opt = wp_list_filter( $types_opt, array( 'type' => 'google_address' ) );
		self::$types_postmeta_fields = $types_opt;
		$types_opt_slugs = Toolset_Addon_Maps_Common::pseudo_list_pluck( $types_opt, 'meta_key' );
		self::$types_postmeta_fields_slugs = $types_opt_slugs;
		
		$types_u_opt = get_option( 'wpcf-usermeta', array() );
		$types_u_opt = wp_list_filter( $types_u_opt, array( 'type' => 'google_address' ) );
		self::$types_usermeta_fields = $types_u_opt;
		$types_u_opt_slugs = Toolset_Addon_Maps_Common::pseudo_list_pluck( $types_u_opt, 'meta_key' );
		self::$types_usermeta_fields_slugs = $types_u_opt_slugs;
		
		$types_t_opt = get_option( 'wpcf-termmeta', array() );
		$types_t_opt = wp_list_filter( $types_t_opt, array( 'type' => 'google_address' ) );
		self::$types_termmeta_fields = $types_t_opt;
		$types_t_opt_slugs = Toolset_Addon_Maps_Common::pseudo_list_pluck( $types_t_opt, 'meta_key' );
		self::$types_termmeta_fields_slugs = $types_t_opt_slugs;
		
		// Register field type and set its icon
		add_filter( 'types_register_fields', array( $this, 'google_address_register_field' ) );
		add_filter( 'toolset_editor_google_address_icon_class', array( $this, 'google_address_icon_class' ) );
		// Make it work nicely with field control mechanisms
		add_filter( 'wpcf_filter_field_control_change_type_allowed_types', array( $this, 'google_address_field_control_change_type_allowed' ) );
		add_filter( 'wpcf_filter_field_control_change_type_allowed_types_from', array( $this, 'google_address_field_control_change_type_allowed_from' ), 10, 2 );
		// Thise are called from the Views integration on init
		add_filter( 'toolset_filter_toolset_maps_get_types_postmeta_fields', array( $this, 'toolset_filter_toolset_maps_get_types_postmeta_fields' ) );
		add_filter( 'toolset_filter_toolset_maps_get_types_termmeta_fields', array( $this, 'toolset_filter_toolset_maps_get_types_termmeta_fields' ) );
		add_filter( 'toolset_filter_toolset_maps_get_types_usermeta_fields', array( $this, 'toolset_filter_toolset_maps_get_types_usermeta_fields' ) );
		// Init
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'on_admin_enqueue_scripts' ), 20 );
	}
	
	function init() {
		
		add_action( 'added_post_meta', array( $this, 'save_field_coordinates' ), 10, 4 );
		add_action( 'updated_post_meta', array( $this, 'save_field_coordinates' ), 10, 4 );
		add_action( 'deleted_post_meta', array( $this, 'delete_field_coordinates' ), 10, 4 );
		add_action( 'added_term_meta', array( $this, 'save_termmeta_coordinates' ), 10, 4 );
		add_action( 'updated_term_meta', array( $this, 'save_termmeta_coordinates' ), 10, 4 );
		add_action( 'deleted_term_meta', array( $this, 'delete_termmeta_coordinates' ), 10, 4 );
		add_action( 'added_user_meta', array( $this, 'save_usermeta_coordinates' ), 10, 4 );
		add_action( 'updated_user_meta', array( $this, 'save_usermeta_coordinates' ), 10, 4 );
		add_action( 'deleted_user_meta', array( $this, 'delete_usermeta_coordinates' ), 10, 4 );
		
	}

	/**
	 * @since 1.5
	 */
	public function on_admin_enqueue_scripts() {
		// RFG fields are loaded by ajax JSON, so we can't add our JS then, and we don't know if there are address
		// fields on the page before. So this is the best hook we have: if there is RFG JS enqueued, enqueue our JS.
		if ( wp_script_is('types-repeatable-group') ) {
			if ( apply_filters( 'toolset_maps_get_api_used', '' ) === Toolset_Addon_Maps_Common::API_GOOGLE ) {
				wp_enqueue_script( 'toolset-google-map-editor-script' );
			} else {
				wp_enqueue_script( 'toolset-maps-address-autocomplete' );
				Toolset_Addon_Maps_Common::maybe_enqueue_azure_css();
			}
		}
	}

	function google_address_register_field( $fields ) {
		$fields[TOOLSET_ADDON_MAPS_FIELD_TYPE] = TOOLSET_ADDON_MAPS_PATH . '/includes/google_address.php';
		return $fields;
	}
	
	function google_address_icon_class( $icon_class ) {
		return 'fa fa-map icon-map';
	}
	
	function google_address_field_control_change_type_allowed( $allowed ) {
		$allowed[ 'google_address' ] = apply_filters( 'wpcf_filter_field_control_change_type_allowed_types_from', array( 'wysiwyg', 'textfield', 'textarea', 'email', 'url', 'date', 'phone', 'file', 'image', 'numeric', 'audio', 'video', 'embed', 'google_address' ), 'google_address' );
		return $allowed;
	}
	
	function google_address_field_control_change_type_allowed_from( $targets, $origin ) {
		if ( 
			in_array( $origin, array( 'audio', 'textfield', 'textarea', 'date', 'email', 'embed', 'file', 'image', 'numeric', 'phone', 'select', 'skype', 'url', 'checkbox', 'radio', 'video', 'google_address' ) ) 
			&& ! in_array( 'google_address', $targets ) 
		) {
			$targets[] = 'google_address';
		}
		return $targets;
	}
	
	static function wpcf_fields_google_address_editor_callback( $field, $settings ) {
		$tabs = array();
		$tabs[ 'display' ] = array(
			'menu_title'	=> __( 'Display', 'toolset-maps' ),
			'title'			=> __( 'Display', 'toolset-maps' )
		);
		$extra_content_api_key	= '';
		
		$maps_api_key = apply_filters( 'toolset_filter_toolset_maps_get_server_side_api_key', '' );
		if ( 
			empty( $maps_api_key ) 
			&& current_user_can( 'manage_options' ) 
		) {
			$analytics_strings = array(
				'utm_source'	=> 'toolsetmapsplugin',
				'utm_campaign'	=> 'toolsetmaps',
				'utm_medium'	=> 'views-integration-settings-for-api-key',
				'utm_term'		=> 'our documentation'
			);
			$extra_content_api_key = sprintf(
				__( '<p><strong>You wil need a Google Maps API key</strong> to display Toolset Maps address fields on a map. Find more information in %1$sour documentation%2$s.</p>', 'toolset-maps' ),
				'<a href="' . Toolset_Addon_Maps_Common::get_documentation_promotional_link( array( 'query' => $analytics_strings, 'anchor' => 'api-key' ), TOOLSET_ADDON_MAPS_DOC_LINK ) . '" target="_blank">',
				'</a>'
			);
		}
		
		if ( class_exists( 'Toolset_Addon_Maps_Views' ) ) {
			$analytics_strings = array(
				'utm_source'	=> 'toolsetmapsplugin',
				'utm_campaign'	=> 'toolsetmaps',
				'utm_medium'	=> 'marker-field-types-dialog',
				'utm_term'		=> 'Toolset Google Maps documentation'
			);
			$extra_content = '<div class="js-types-modal-output-address-extra-for-map js-types-modal-output-address-extra-for-map-default" style="display: none;margin-left: 6px;">'
					. '<p style="font-weight: bold;">'
						. __( 'Want to show this address on a map?', 'toolset-maps' )
					. '</p>'
					. $extra_content_api_key
					. '<p>'
						. '<strong>' . __( '1. Display a Google Map', 'toolset-maps' ) . '</strong>'
						. '<span style="display:block;margin-left: 14px;">'
						. __( 'Click on the <strong>Fields and Views</strong> button &rsaquo; find the <strong>Google Map</strong> section &rsaquo; click on the <strong>Map</strong> item.', 'toolset-maps' ) . '</dt>'
						. '</span>'
					. '</p>'
					. '<p>'
						. '<strong>' . __( '2. Add a marker to the map', 'toolset-maps' ) . '</strong>'
						. '<span style="display:block;margin-left: 14px;">'
							. __( 'Click again on the <strong>Fields and Views</strong> button &rsaquo; find the <strong>Google Map</strong> section &rsaquo; click on the <strong>Marker</strong> item. Now, fill the marker settings:', 'toolset-maps' )
							. '<br />'
							. '<span style="margin-left:20px;">'
								. __( '<strong>Map ID</strong>: same as the Map ID set for your Map', 'toolset-maps' )
							. '</span>'
							. '<br />'
							. '<span style="margin-left:20px;">'
								. __( '<strong>Marker address comes from</strong>: your Google Maps field', 'toolset-maps' )
							. '</span>'
						. '</span>'
					. '</p>'
					. '<p>'
						. sprintf(
							__( '%1$sToolset Google Maps documentation%2$s', 'toolset-maps' ),
							'<a href="' . Toolset_Addon_Maps_Common::get_documentation_promotional_link( array( 'query' => $analytics_strings, 'anchor' => 'displaying-the-map' ) ) . '" target="_blank">',
							'</a>'
						)
					. '</p>'
				. '</div>';
			$extra_content .= '<div class="js-types-modal-output-address-extra-for-map js-types-modal-output-address-extra-for-map-views" style="display: none;margin-left: 6px;">'
					. '<p style="font-weight: bold;">'
						. __( 'Want to show this address on a map?', 'toolset-maps' )
					. '</p>'
					. $extra_content_api_key
					. '<p>'
						. '<strong>' . __( '1. Display a Google Map', 'toolset-maps' ) . '</strong>'
						. '<span style="display:block;margin-left: 14px;">'
						. __( 'Place your cursor <strong>outside</strong> the View loop <code>&lt;wpv-loop&gt; &lt;/wpv-loop&gt;</code> in the <strong>Loop Output</strong>.', 'toolset-maps' )
						. TOOLSET_ADDON_MAPS_MESSAGE_SPACE_CHAR
						. __( 'Click on the <strong>Fields and Views</strong> button &rsaquo; find the <strong>Google Map</strong> section &rsaquo; click on the <strong>Map</strong> item.', 'toolset-maps' ) . '</dt>'
						. '</span>'
					. '</p>'
					. '<p>'
						. '<strong>' . __( '2. Add a marker to the map', 'toolset-maps' ) . '</strong>'
						. '<span style="display:block;margin-left: 14px;">'
							. __( 'Place your cursor <strong>inside</strong> the View loop <code>&lt;wpv-loop&gt; &lt;/wpv-loop&gt;</code> in the <strong>Loop Output</strong>.', 'toolset-maps' )
							. TOOLSET_ADDON_MAPS_MESSAGE_SPACE_CHAR
							. __( 'Click again on the <strong>Fields and Views</strong> button &rsaquo; find the <strong>Google Map</strong> section &rsaquo; click on the <strong>Marker</strong> item. Now, fill the marker settings:', 'toolset-maps' )
							. '<br />'
							. '<span style="margin-left:20px;">'
								. __( '<strong>Map ID</strong>: same as the Map ID set for your Map', 'toolset-maps' )
							. '</span>'
							. '<br />'
							. '<span style="margin-left:20px;">'
								. __( '<strong>Marker address comes from</strong>: your Google Maps field', 'toolset-maps' )
							. '</span>'
						. '</span>'
					. '</p>'
					. '<p>'
						. sprintf(
							__( '%1$sToolset Google Maps documentation%2$s', 'toolset-maps' ),
							'<a href="' . Toolset_Addon_Maps_Common::get_documentation_promotional_link( array( 'query' => $analytics_strings, 'anchor' => 'displaying-the-map' ) ) . '" target="_blank">',
							'</a>'
						)
					. '</p>'
				. '</div>';
		} else {
			$analytics_strings = array(
				'utm_source'	=> 'toolsetmapsplugin',
				'utm_campaign'	=> 'toolsetmaps',
				'utm_medium'	=> 'marker-field-types-dialog',
				'utm_term'		=> 'Views plugin'
			);
			$extra_content = '<div class="js-types-modal-output-address-extra-for-map js-types-modal-output-address-extra-for-map-default" style="display: none;margin-left: 8px;">'
					. '<div class="toolset-help-content">'
						. '<p style="font-weight: bold;">'
							. __( 'Want to show this address on a map?', 'toolset-maps' )
						. '</p>'
						. $extra_content_api_key
						. '<p>'
							. sprintf(
								__( 'You need the %1$sViews plugin%2$s to display maps and add markers to them', 'toolset-maps' ),
								'<a href="' . Toolset_Addon_Maps_Common::get_documentation_promotional_link( array( 'query' => $analytics_strings, 'anchor' => 'views' ), 'https://toolset.com/home/toolset-components/' ) . '" target="_blank">',
								'</a>'
							)
						. '</p>'
					. '</div>'
					. '<div class="toolset-help-sidebar"></div>'
				. '</div>';
		}
		
		$extra_content .= '<script type="text/javascript">'
			. '';
		$extra_content .= 'jQuery( document ).on( "change", ".js-types-modal-css-output-address-field", function() {'
			. 'var address_output_selected = jQuery( ".js-types-modal-css-output-address-field:checked").val();'
			. 'jQuery( ".js-types-modal-output-address-extra-for-coordinates, .js-types-modal-output-address-extra-for-map" ).hide();'
			. 'if ( address_output_selected == "map" ) {'
				. 'jQuery( ".types-media-modal-content .media-button-insert" ).attr( "disabled", true );'
				. 'if ( typeof parent.WPViews == "undefined" ) {'
					. 'jQuery( ".js-types-modal-output-address-extra-for-map-default" ).fadeIn();'
				. '} else {'
					. 'if ( typeof parent.WPViews.view_edit_screen != "undefined" || typeof parent.WPViews.wpa_edit_screen != "undefined" ) {'
						. 'jQuery( ".js-types-modal-output-address-extra-for-map-views" ).fadeIn();'
					. '} else {'
						. 'jQuery( ".js-types-modal-output-address-extra-for-map-default" ).fadeIn();'
					. '}'
				. '}'
			. '} else {'
				. 'if ( address_output_selected == "coordinates" ) {'
					. 'jQuery( ".js-types-modal-output-address-extra-for-coordinates" ).fadeIn();'
				. '}'
				. 'jQuery( ".types-media-modal-content .media-button-insert" ).attr( "disabled", false );'
			. '}'
		. '});'
		. '';
		$extra_content .= '( function() {'
				. 'jQuery( "#types-modal-css-output-map" ).click();'
				. 'jQuery( "#types-modal-css-output-map.js-types-modal-css-output-address-field" ).trigger( "change" );'
				. 'setTimeout( function() {'
					. 'jQuery( ".types-media-modal-content .media-button-insert" ).attr( "disabled", true );'
				. '}, 2 );'
			. '} )();'
			. '';
		$extra_content .= '</script>';
		
		$tabs[ 'display' ]['content'] = '<div>'
			. '<ul>'
			. '<li>'
			. '<label for="types-modal-css-output-map">' 
			. '<input type="radio" class="js-types-modal-css-output-address-field" id="types-modal-css-output-map" name="value-output" value="map" checked="checked" />'
			. __( 'Display the address on a Google Map', 'toolset-maps' ) . '</label>'
			. '</li>'
			. '<li>'
			. '<label for="types-modal-css-output-address">' 
			. '<input type="radio" class="js-types-modal-css-output-address-field" id="types-modal-css-output-address" name="value-output" value="address" />'
			. __( 'Display the address as text', 'toolset-maps' ) . '</label>'
			. '</li>'
			. '<li>'
			. '<label for="types-modal-css-output-coordinates">' 
			. '<input type="radio" class="js-types-modal-css-output-address-field" id="types-modal-css-output-coordinates" name="value-output" value="coordinates" />'
			. __( 'Display the latitude/longitude coordinates of the address', 'toolset-maps' ) . '</label>'
			. '<div class="js-types-modal-output-address-extra-for-coordinates" style="display: none; margin-left: 25px;">'
			. '<input type="text" name="value-output-coordinates-format" class="regular-text" value="FIELD_ADDRESS: FIELD_LATITUDE, FIELD_LONGITUDE" />'
			. '<p>'
			. __( 'Available placeholders:', 'toolset-maps' )
			. '<ul>'
			. '<li>FIELD_ADDRESS - shows address</li>'
			. '<li>FIELD_LATITUDE - shows numeric latitutde (xx.xxxx)</li>'
			. '<li>FIELD_LONGITUDE - shows numeric longitude (xx.xxxx) </li>'
			. '</ul>'
			. '</p>'
			. '</div>'
			. '</li>'
			. '</ul>'
			. '</div>'
			. $extra_content;
		
		return array(
			'tabs' => $tabs
		);
	}
	
	static function wpcf_fields_google_address_editor_submit( $data, $field, $type ) {
		$add = '';
		if (
			isset( $data['value-output'] ) 
			&& isset( $data['value-output-coordinates-format'] )
			&& $data['value-output'] == 'coordinates'
		) {
			$add .= ' format="' . $data['value-output-coordinates-format'] . '"';
		}

		// Generate and return shortcode
		if ( $type == 'usermeta' ) {
			// Note: this is a nightmare: we adjust id attributes in different ways for posts and users...
			$add .= wpcf_get_usermeta_form_addon_submit();
			return wpcf_usermeta_get_shortcode( $field, $add );
		} elseif ( $type == 'termmeta' ) {
			// Note: this is a nightmare: we adjust id attributes in different ways for posts and users...
			$add .= wpcf_get_termmeta_form_addon_submit();
			return wpcf_termmeta_get_shortcode( $field, $add );
		} else {
			return wpcf_fields_get_shortcode( $field, $add );
		}
	}
	
	static function wpcf_fields_google_address_view( $data ) {
		$content = '';
		
		if ( 
			isset( $data['field_value'] )
            && $data['field_value'] !== "" 
		) {
			$content = $data['field_value'];
			if ( isset( $data['format'] ) ) {
				$content = $data['format'];
				$content = str_replace( 'FIELD_ADDRESS', $data['field_value'], $content );
				if (
					strpos( $content, 'FIELD_LATITUDE' ) !== false 
					|| strpos( $content, 'FIELD_LONGITUDE' ) !== false
				) {
					$coordinates = Toolset_Addon_Maps_Common::get_coordinates( $data['field_value'] );
					if ( ! is_array( $coordinates ) ) {
						$coordinates = array(
							'lat'	=> '',
							'lon'	=> ''
						);
					}
					$content = str_replace( 'FIELD_LATITUDE', $coordinates['lat'], $content );
					$content = str_replace( 'FIELD_LONGITUDE', $coordinates['lon'], $content );
				}
			}
		} else {
			$content = '__wpcf_skip_empty';
		}
		
		if ( empty( $content ) ) {
			$content = '__wpcf_skip_empty';
		}
		return $content;
	}
	
	/**
	*
	*/
	
	function toolset_filter_toolset_maps_get_types_postmeta_fields( $fields ) {
		$types_postmeta_fields = self::$types_postmeta_fields;
		return $types_postmeta_fields;
	}
	
	function toolset_filter_toolset_maps_get_types_termmeta_fields( $fields ) {
		$types_termmeta_fields = self::$types_termmeta_fields;
		return $types_termmeta_fields;
	}
	
	function toolset_filter_toolset_maps_get_types_usermeta_fields( $fields ) {
		$types_usermeta_fields = self::$types_usermeta_fields;
		return $types_usermeta_fields;
	}
	
	/**
	* save_field_coordinates
	*
	* Save fields coordinates when creating or updating a Types field
	*
	* @since 1.0
	*/
	
	function save_field_coordinates( $mid, $object_id, $meta_key, $_meta_value ) {
		$types_postmeta_fields_slugs = self::$types_postmeta_fields_slugs;
		if ( 
			! in_array( $meta_key, $types_postmeta_fields_slugs ) 
			|| empty( $_meta_value )
		) {
			return;
		}
		Toolset_Addon_Maps_Common::get_coordinates( $_meta_value );
	}
	
	/**
	*
	*/
	
	function delete_field_coordinates( $mid, $object_id, $meta_key, $_meta_value ) {
		$types_postmeta_fields_slugs = self::$types_postmeta_fields_slugs;
		if ( 
			! in_array( $meta_key, $types_postmeta_fields_slugs ) 
			|| empty( $_meta_value )
		) {
			return;
		}
	}
	
	/**
	* save_termmeta_coordinates
	*
	* Save fields coordinates when creating or updating a Types field
	*
	* @since 1.0
	*/
	
	function save_termmeta_coordinates( $mid, $object_id, $meta_key, $_meta_value ) {
		$types_termmeta_fields_slugs = self::$types_termmeta_fields_slugs;
		if ( 
			! in_array( $meta_key, $types_termmeta_fields_slugs ) 
			|| empty( $_meta_value )
		) {
			return;
		}
		Toolset_Addon_Maps_Common::get_coordinates( $_meta_value );
	}
	
	function delete_termmeta_coordinates( $mid, $object_id, $meta_key, $_meta_value ) {
		$types_termmeta_fields_slugs = self::$types_termmeta_fields_slugs;
		if ( 
			! in_array( $meta_key, $types_termmeta_fields_slugs ) 
			|| empty( $_meta_value )
		) {
			return;
		}
	}
	
	/**
	* save_usermeta_coordinates
	*
	* Save fields coordinates when creating or updating a Types field
	*
	* @since 1.0
	*/
	
	function save_usermeta_coordinates( $mid, $object_id, $meta_key, $_meta_value ) {
		$types_usermeta_fields_slugs = self::$types_usermeta_fields_slugs;
		if ( 
			! in_array( $meta_key, $types_usermeta_fields_slugs ) 
			|| empty( $_meta_value )
		) {
			return;
		}
		Toolset_Addon_Maps_Common::get_coordinates( $_meta_value );
	}
	
	/**
	*
	*/
	
	function delete_usermeta_coordinates( $mid, $object_id, $meta_key, $_meta_value ) {
		$types_usermeta_fields_slugs = self::$types_usermeta_fields_slugs;
		if ( 
			! in_array( $meta_key, $types_usermeta_fields_slugs ) 
			|| empty( $_meta_value )
		) {
			return;
		}
	}
	
}

$Toolset_Addon_Maps_Types = new Toolset_Addon_Maps_Types();

function wpcf_fields_google_address() {
    return array(
        'path'			=> __FILE__,
        'id'			=> TOOLSET_ADDON_MAPS_FIELD_TYPE,
        'validate'		=> array( 'required' ),
        'wp_version'	=> '3.3',
        'title'			=> __( 'Address', 'toolset-maps' ),
        'icon_class'	=> 'fa fa-map',
		'font-awesome'	=> 'map'
    );
}

function WPToolset_Field_google_address_loader() {
    if ( class_exists('WPToolset_Field_google_address' ) ) {
        return;
    }
    include_once TOOLSET_ADDON_MAPS_PATH . '/includes/toolset-maps-types.class.php';
}

/**
 * Adds editor popup callnack.
 * 
 * This form will be showed in editor popup
 */
function wpcf_fields_google_address_editor_callback( $field, $settings ) {
	return Toolset_Addon_Maps_Types::wpcf_fields_google_address_editor_callback( $field, $settings );
}

/**
 * Processes editor popup submit
 */
function wpcf_fields_google_address_editor_submit( $data, $field, $type ) {
	return Toolset_Addon_Maps_Types::wpcf_fields_google_address_editor_submit( $data, $field, $type );
}

/**
 * Renders view
 */
function wpcf_fields_google_address_view( $data ) {
	return Toolset_Addon_Maps_Types::wpcf_fields_google_address_view( $data );
}


/**
 * Legacy method to generate metaform for an empty google_address field.
 *
 * AFAIK this is used only when an attachment post type field has assigned a field group that contains repetitive
 * google_address field.
 *
 * @param array $field Field definition array.
 * @return array|void 
 * @since ?
 * @deprecated Do not use this anywhere.
 */
function wpcf_fields_google_address_meta_box_form( $field ) {
	WPToolset_Field_google_address_loader();
	
	$ga_field = new WPToolset_Field_google_address( $field, '', '' );
	return $ga_field->metaform();
}
