<?php
/**
 * MARKERS SECTION
 * ----------------------------------------------------------------------------
 */
 
$rvm_div_class = !isset( $rvm_tab_active_default ) && ( isset( $rvm_tab_active ) && $rvm_tab_active == 'rvm_markers' )  ? ' class="rvm_active hidden" >' : ' class="hidden">' ; 
$output .= '<div id="rvm_markers" ' . $rvm_div_class ; 

//Start : marker uploader
// Retrieve options
$rvm_options = rvm_retrieve_options();
//Retrieve custom marker icon 
$rvm_custom_marker_icon_path = get_post_meta( $post->ID, '_rvm_mbe_custom_marker_icon_path', true );
$rvm_marker_module_dir_url_array = rvm_set_absolute_upload_dir_url();

//Load marker module only if there is a module installed and the module has a valid path
if ( !empty( $rvm_options[ 'rvm_custom_icon_marker_module_path_verified' ] ) ) { 
 
    if( rvm_is_marker_module_in_download_dir_yet( $rvm_options['rvm_custom_icon_marker_module_path_verified']) ) {
            @include $rvm_marker_module_dir_url_array[1] . $rvm_options['rvm_custom_icon_marker_module_path_verified'] ;
        } else { $output .= '<div class="rvm_messages rvm_error_messages">' . __( 'It seems there is no marker module installed or maybe was deleted. Please reinstall it using RVM global settings in Settings menu' , RVM_TEXT_DOMAIN ) . '</div>'; }


}//if ( !empty( $rvm_options[ 'rvm_custom_icon_marker_module_path_verified' ] ) && ( $rvm_options['rvm_custom_icon_marker_module_path_verified'] != 'default' ) )

else {
	$output .= '<div class="rvm_messages rvm_notice_messages rvm_marker_messages"><img src="' . RVM_IMG_PLUGIN_DIR . '/map-icon-16x16.png' . '" alt="Map pintpoint icon"/>' . __( 'Tired to use the default circle icon for Markers\' pinpoints? This is the right time to <a href="https://www.responsivemapsplugin.com/redirect-to-marker-icon-module-from-plugin-dashboard/" target="_blank">download the Custom Icon Marker Module!</a>' , RVM_TEXT_DOMAIN ) . '</div>';
}

//End : marker uploader   
     
$output .= '<div id="rvm_mbe_fields_wrap">' ;
$output .= '<h2 class="rvm_h2_title">' . __( 'Create New Markers' , RVM_TEXT_DOMAIN ) . '</h2>' ;

$output .= '<input type="button" id="rvm_mbe_add_field_button" class="button-primary" value="' . __( 'Add Markers' , RVM_TEXT_DOMAIN ) . '" >';      
$output .= '<div style="clear:left;"></div>' ;

// function markers() can be found in rvm_core.php
$marker_array_serialized = markers( $post->ID, 'retrieve', 'serialized' ) ;
$marker_array_unserialized = markers( $post->ID, 'retrieve', 'unserialized' ) ;
   
$rvm_marker_array_count =  count( $marker_array_unserialized[ 'rvm_marker_name_array' ]  ) ;  // count element of the array starting from 1
       
if( is_array( $marker_array_unserialized[ 'rvm_marker_name_array' ] ) && $rvm_marker_array_count > 0  ) {            
                        
    $rvm_is_marker = 1 ;
            
    $output .= '<h4 class="rvm_h4_title rvm_added_markers_title">' . __( 'Added Markers' , RVM_TEXT_DOMAIN ) .'<span>' . __( ' ( Not visible in map preview )' , RVM_TEXT_DOMAIN ) . '</span></h4>' ;
    
    for( $i=0; $i < $rvm_marker_array_count; $i++ ) {
        
        $output .= '<div class="rvm_markers">' ;            
        $output .= '<p><label for="marker_name" class="rvm_label rvm_label_markers">' . __( 'Name' , RVM_TEXT_DOMAIN ) . '*</label><input type="text" name="rvm_marker_name[]" value="' . strip_tags( wp_unslash( $marker_array_unserialized[ 'rvm_marker_name_array' ][ $i ] ) ) . '" /></p>' ;            
        $output .= '<p><label for="marker_lat" class="rvm_label rvm_label_markers">' . __( 'Latitude' , RVM_TEXT_DOMAIN ) . '*</label><input type="text" name="rvm_marker_lat[]" value="' . strip_tags( $marker_array_unserialized[ 'rvm_marker_lat_array' ][ $i ] ) . '" placeholder="e.g. 48.921537" /></p>' ;            
        $output .= '<p><label for="marker_long" class="rvm_label rvm_label_markers">' . __( 'Longitude' , RVM_TEXT_DOMAIN ) . '*</label><input type="text" name="rvm_marker_long[]" value="' . strip_tags( $marker_array_unserialized[ 'rvm_marker_long_array' ][ $i ] ) . '" placeholder="e.g. -66.829834" /></p>' ;       
        $output .= '<p><label for="marker_link" class="rvm_label rvm_label_markers">' . __( 'Link' , RVM_TEXT_DOMAIN ) . '</label><input type="text" name="rvm_marker_link[]" value="' . esc_url( $marker_array_unserialized[ 'rvm_marker_link_array' ][ $i ] ) . '" /></p>' ;
        $output .= '<p><label for="marker_dim" class="rvm_label rvm_label_markers">' . __( 'Dimension' , RVM_TEXT_DOMAIN ) . '<br><span class="rvm_small_text">'  . __( 'Use only integer or decimal' , RVM_TEXT_DOMAIN ) .  '</span></label><input type="text" name="rvm_marker_dim[]" value="' . strip_tags( $marker_array_unserialized[ 'rvm_marker_dim_array' ][ $i ] ) . '" placeholder="' . __( 'e.g. 591.20' , RVM_TEXT_DOMAIN ) . '" /></p>' ;
        $output .= '<p><label for="marker_popup" class="rvm_label rvm_label_markers" style="vertical-align:top;">' . __( 'Popup label' , RVM_TEXT_DOMAIN ) . '</label><textarea name="rvm_marker_popup[]" placeholder="' . __( 'e.g. Rome precipitation (mm) long term averages' , RVM_TEXT_DOMAIN ) . '" >' . esc_attr( wp_unslash( $marker_array_unserialized[ 'rvm_marker_popup_array' ][ $i ] ) ) . '</textarea></p>' ;                
        $output .= '<input type="submit" class="rvm_remove_field button-secondary" value="' . __( 'Remove' , RVM_TEXT_DOMAIN ) . '">' ;              
        $output .= '</div>' ;            
                    
    }   

} //!empty( $rvm_marker_name ))


//$output .= 'count: ' . $rvm_marker_array_count . '<br>'; 
//$output .= '$marker_array[ \'rvm_marker_dim_array\' ][ $i ]: ' . $marker_array_unserialized[ 'rvm_marker_name_array' ] . '<br>';
$output .= '</div>' ; // <div id="rvm_mbe_fields_wrap">
if( is_array( $marker_array_unserialized[ 'rvm_marker_name_array' ] ) && ( $rvm_marker_array_count > 1 ) &&  ( $rvm_is_marker = 1 ) ) {

    $output .= '<div id="rvm_markers_delete_button_wrapper">' ;

    $output .= '<input type="button" id="rvm_delete_all_markers_button" class="button-primary"  value=" ' . __( 'Delete all Markers', RVM_TEXT_DOMAIN ) . '" />';

    $output .= '</div>';
    
}
$output .= '<div style="clear:both;"></div>' ;

//Show default markers' icon if no module is installed and no icon selected
if( !rvm_check_custom_marker_icon_available( $rvm_custom_marker_icon_path ) || empty( $rvm_options[ 'rvm_custom_icon_marker_module_path_verified' ] ) ) {

    //$output .= '<hr class="rvm_separator">';    

    $output .= '<div id="rvm_settings_wrap">' ;    
    $output .= '<h2 class="rvm_h2_title">' . __( 'Markers Colours' , RVM_TEXT_DOMAIN ) . '</h2>' ; 
    $output .=  '<div class="rvm_markers_values">' ;
    $output .=  '<div id="rvm_bg_color_wrapper">' ;
    $output .= isset( $output_markers_bg_colour ) ? $output_markers_bg_colour : '' ;
    $output .=  '</div>' ;
    $output .=  '<div id="rvm_border_color_wrapper">' ;
    $output .= isset( $output_markers_border_colour ) ? $output_markers_border_colour : '' ;
    $output .=  '</div>' ;
    $output .=  '</div>' ; //.rvm_markers_values
    $output .= '<div style="clear:left;"></div>' ;
    $output .= '<h2 class="rvm_h2_title">' . __( 'Markers Dimensions' , RVM_TEXT_DOMAIN ) . '</h2>' ;
    $output .= '<p>' . __( 'Minimum and maximum values will affect the radius dimensions of the Markers. Basically they are a scale within input values for marker dimensions will be represented. The smallest will be equal to "minimum value" while the biggest will be equal to "maximum value". Default values are', RVM_TEXT_DOMAIN ) .  RVM_MARKER_DIM_MIN_VALUE . __(' and ', RVM_TEXT_DOMAIN ) . RVM_MARKER_DIM_MAX_VALUE . '.</p>' ;
    $output .= isset( $output_marker_dim_min ) ? $output_marker_dim_min : '' ;
    $output .= isset( $output_marker_dim_max ) ? $output_marker_dim_max : '' ;  
    $output .= '<div style="clear:left;"></div>' ;   

    $output .= '</div>' ;// #rvm_settings_wrap
    
}//if( rvm_check_custom_marker_icon_available( $rvm_custom_marker_icon_path ) )


$output .= '<h2 class="rvm_h2_title">' . __( 'Effects' , RVM_TEXT_DOMAIN ) . '</h2>' ;
    $output .=  '<div id="rvm_markers_rain_effect_wrapper">' ;
    $output .= isset( $output_markers_rain_effect ) ? $output_markers_rain_effect : '' ;
    $output .=  '</div>' ;


//Show the export markers button only if we have markers in DB
if( $rvm_is_marker ) {
    $output .= '<h2 class="rvm_h2_title">' . __( 'Export Markers' , RVM_TEXT_DOMAIN ) . '</h2>' ;
    $output .= '<div class="rvm_export_markers_button_wrapper">';
    $output .= '<input type="button" id="rvm_export_markers_button" class="button-primary"  value=" ' . __( 'Export Markers', RVM_TEXT_DOMAIN ) . '" />';
    //$output .= '<div id="rvm_export_markers_status" class="rvm_notice_messages"></div>' ;
    $output .= '</div>';//.rvm_export_markers_button_wrapper

    $output .= '<div style="clear:left;"></div>' ;
}

$output .= '<h2 class="rvm_h2_title">' . __( 'Import Markers  ( please use exported files from RVM only )' , RVM_TEXT_DOMAIN ) . '</h2>' ;
$output .= '<div class="rvm_import_markers_button_wrapper">';
$output .= '<input type="hidden" id="rvm_upload_markers_file_path" value=""/>';
$output .= '<input type="button" id="rvm_upload_markers_button" class="button-primary rvm_media_uploader"  value=" ' . __( 'Select Markers File', RVM_TEXT_DOMAIN ) . '" />';
$output .= '<input type="button" id="rvm_import_markers_button" class="button-primary"  value=" ' . __( 'Import Markers', RVM_TEXT_DOMAIN ) . '" />';
$output .= '<input type="button" id="rvm_import_reset_markers_button" class="button-secondary"  value=" ' . __( 'Oops... I messed up, I want to go back', RVM_TEXT_DOMAIN ) . '" onclick="window.location.reload();" />';

$output .= '</div>';// .rvm_import_markers_button_wrapper
$output .= '<div id="rvm_import_markers_status" class="rvm_messages rvm_notice_messages"></div>' ;

$output .= '<div style="clear:left;"></div>' ;


$output .= '</div>' ; // <div id="rvm_markers">

?>