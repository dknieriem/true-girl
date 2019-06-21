<?php
/**
* Toolset Maps - Forms integration
*
* @package ToolsetMaps
*
* @since 0.1
*
*/

class Toolset_Addon_Maps_CRED {

    public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
        add_action( 'save_post', array( $this, 'save_post' ) );
		
		add_filter( 'cred_filter_field_type_before_noadd_to_form', array( $this, 'force_map_field_type' ), 10, 2 );
		add_filter( 'cred_filter_field_type_before_add_to_form', array( $this, 'force_map_field_type' ), 10, 2 );
    }
	
	public function wp_enqueue_scripts() {
		
	}

    public function save_post( $post_id ) {
		
    }
	
	public function force_map_field_type( $type, $computed_values ) {
		$current_field = $computed_values['field'];
		if (
			isset( $current_field )
			&& isset( $current_field['type'] )
			&& $current_field['type'] == TOOLSET_ADDON_MAPS_FIELD_TYPE
		) {
			$type = TOOLSET_ADDON_MAPS_FIELD_TYPE;
		}
		return $type;
	}

}

$Toolset_Addon_Maps_CRED = new Toolset_Addon_Maps_CRED();
