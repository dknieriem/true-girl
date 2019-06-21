<?php

/**
* Toolset Maps - Types field GUI
*
* @package ToolsetMaps
*
* @since 0.1
*/

// This file can be included during AJAX call. In such case we must obtain the file with the parent class manually.
if( !class_exists( 'FieldFactory' ) ) {
	$tcl_bootstrap = Toolset_Common_Bootstrap::getInstance();
	$tcl_bootstrap->register_toolset_forms();

	require_once WPTOOLSET_FORMS_ABSPATH . '/classes/class.field_factory.php';
}

class WPToolset_Field_google_address extends FieldFactory {

    public function metaform() {
        $attributes =  $this->getAttr();
        $attributes['placeholder'] = __('Enter address', 'toolset-maps');
        if ( ! isset( $attributes['class'] ) ) {
            $attributes['class'] = '';
        }
        if ( ! empty($attributes['class'])) {
            $attributes['class'] .= ' ';
        }
		
		$value = $this->getValue();
		
		$wpml_action = $this->getWPMLAction();

        $metaform = array();
		
		

		if ( apply_filters( 'toolset_maps_get_api_used', '' ) === Toolset_Addon_Maps_Common::API_GOOGLE ) {
			$maps_api_key = apply_filters( 'toolset_filter_toolset_maps_get_api_key', '' );
		} else {
			$maps_api_key = apply_filters( 'toolset_filter_toolset_maps_get_azure_api_key', '' );
		}

		if ( empty( $maps_api_key ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				$analytics_strings = array(
					'utm_source'	=> 'toolsetmapsplugin',
					'utm_campaign'	=> 'toolsetmaps',
					'utm_medium'	=> 'views-integration-settings-for-api-key',
					'utm_term'		=> 'our documentation'
				);
				$markup = sprintf(
					__( '<p><small><strong>You need an API key</strong> to use Toolset Maps address fields. Find more information in %1$sour documentation%2$s.</small></p>', 'toolset-maps' ),
					'<a href="' . Toolset_Addon_Maps_Common::get_documentation_promotional_link( array( 'query' => $analytics_strings, 'anchor' => 'api-key' ), TOOLSET_ADDON_MAPS_DOC_LINK ) . '" target="_blank">',
					'</a>'
				);
				$metaform[] = array(
					'#type'		=> 'markup',
					'#inline'	=> true,
					'#markup'	=> $markup
				);
			}
			$before = '';
			$after = '';
		} else {
			$before = '<div class="toolset-google-map-container js-toolset-google-map-container">';
			$before .= '<div class="toolset-google-map-inputs-container js-toolset-google-map-inputs-container">';
			if ( $this->isRepetitive() ) {
				$before .= '<span class="toolset-google-map-label">' . __('Address', 'toolset-maps') . '</span>';
				$attributes['style'] = 'max-width:80%';
			}
			$after = '</div></div>';
			
			$attributes['class'] .= 'toolset-google-map js-toolset-google-map js-toolset-maps-address-autocomplete';
			
			if ( ! isset( $attributes['data-coordinates'] ) ) {
				$attributes['data-coordinates'] = '';
			}
			
			if ( ! empty( $value ) ) {
				$has_coordinates = Toolset_Addon_Maps_Common::get_coordinates( $value );
				if ( is_array( $has_coordinates ) ) {
					$attributes['data-coordinates'] = '{' . esc_attr( $has_coordinates['lat'] ) . ',' . esc_attr( $has_coordinates['lon'] ) . '}';
				}
			}
		}
		
        $metaform[] = array(
            '#type'			=> 'textfield',
			'#before'		=> $before,
			'#after'		=> $after,
            '#description'	=> $this->getDescription(),
            '#name'			=> $this->getName(),
            '#value'		=> $value,
            '#validate'		=> $this->getValidationData(),
            '#repetitive'	=> $this->isRepetitive(),
            '#attributes'	=> $attributes,
            '#title'		=> $this->getTitle(),
			'wpml_action'	=> $wpml_action,
        );
        return $metaform;
    }

	public function enqueueScripts() {
		if ( apply_filters( 'toolset_maps_get_api_used', '' ) === Toolset_Addon_Maps_Common::API_GOOGLE ) {
			wp_enqueue_script( 'toolset-google-map-editor-script' );
		} else {
			wp_enqueue_script( 'toolset-maps-address-autocomplete' );
			Toolset_Addon_Maps_Common::maybe_enqueue_azure_css();
		}
	}

}
