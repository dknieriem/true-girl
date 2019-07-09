<?php
/**
 * GENERAL FUNCTIONS
 * ----------------------------------------------------------------------------
 */
//Get rid of last character in a  string
function rvm_delete_last_character( $str ) {
                $output_temp = substr( $str, 0, -1 );
                return $output_temp;
}
//check numeric entry values for array
function rvm_check_is_number_in_array( $array_to_check ) //check if numeric, used e.g. for markers lat and long
                {
                $rvm_checked_number_array = $array_to_check;
                foreach ( $rvm_checked_number_array as $key => $rvm_single_value ) {
                                if ( !is_numeric( $rvm_single_value ) ) {
                                                $rvm_checked_number_array[ $key ] = '';
                                } //!is_numeric( $rvm_single_value )
                                else {
                                                $rvm_checked_number_array[ $key ] = $rvm_single_value;
                                }
                } //$rvm_checked_number_array as $key => $rvm_single_value
                return $rvm_checked_number_array;
}
//check html entry values for array and change it into html entities
function rvm_check_is_html_in_array( $array_to_check ) //check if numeric, used e.g. for markers lat and long
                {
                $rvm_checked_html_array = $array_to_check;
                foreach ( $rvm_checked_html_array as $key => $rvm_single_value ) {
                                if ( empty( $rvm_single_value ) ) {
                                                $rvm_checked_html_array[ $key ] = '';
                                } //empty( $rvm_single_value )
                                else {
                                                $rvm_checked_html_array[ $key ] = $rvm_single_value;
                                } // codify single and double quotes
                } //$rvm_checked_html_array as $key => $rvm_single_value
                return $rvm_checked_html_array;
}
function rvm_retrieve_custom_maps_options( ) {
                // Retrieve all user options from DB
                $rvm_custom_maps_options = get_option( 'rvm_custom_maps_options' );
                return $rvm_custom_maps_options;
}
//Get first part of map name
function rvm_retrieve_custom_map_name( $map_name ) {
                $custom_map_name_array = explode( '_', trim( $map_name ) );
                $custom_map_name = $custom_map_name_array[ 0 ];
                return $custom_map_name;
}
//Get map name without the "_" sign
function rvm_retrieve_custom_map_name_without_underscore( $map_name ) {
                $custom_map_name_without_underscore = str_replace( '_', ' ', $map_name );
                return $custom_map_name_without_underscore;
}
//Get name of maps without file extension
function rvm_retrieve_custom_map_raw_name( $filename ) {
                $custom_map_raw_name_array = explode( '.', trim( $filename ) );
                // Explode return a string even if the delimiter is not in the string. First string would be map name in case no extension's provided
                return trim( $custom_map_raw_name_array[ 0 ] );
}
//Get filename extension: i.e. .zip
function rvm_retrieve_custom_map_ext( $filename, $map_ext ) {
                $custom_map_ext = substr( trim( $filename ), -( strlen( $map_ext ) ) );
                return trim( $custom_map_ext );
}
//Get custom map dir and url path from options
function rvm_retrieve_custom_map_dir_and_url_path( $rvm_custom_maps_options ) {
                // RVM_CUSTOM_MAPS_PATHS_DELIMITER = -@rvm@-
                $rvm_retrieve_custom_map_dir_and_url_path = explode( RVM_CUSTOM_MAPS_PATHS_DELIMITER, $rvm_custom_maps_options );
                // Access the WP filesystem and upload dir
                //WP_Filesystem();
                $destination = wp_upload_dir();
                $destination_dir_path = $destination[ 'path' ];
                $destination_basedir_path = $destination[ 'basedir' ]; //i.e /Applications/MAMP/htdocs/wordpress4.3/wp-content/uploads
                $destination_baseurl_path = $destination[ 'baseurl' ]; // i.e http://localhost:8888/wordpress4.3/wp-content/uploads 
                //if we have new dynamic format year/month, so we get 1 elemnt only
                if ( count( $rvm_retrieve_custom_map_dir_and_url_path ) < 2 ) {
                                $rvm_retrieve_custom_map_dir_and_url_path[ 0 ] = $destination_basedir_path . '/' . $rvm_custom_maps_options;
                                $rvm_retrieve_custom_map_dir_and_url_path[ 1 ] = $destination_baseurl_path . '/' . $rvm_custom_maps_options;
                } // if( count( $rvm_retrieve_custom_map_dir_and_url_path ) < 2 ) 
                else {
                                $rvm_retrieve_custom_map_dir_path_temp = substr( $rvm_retrieve_custom_map_dir_and_url_path[ 0 ], strpos( strtolower( $rvm_retrieve_custom_map_dir_and_url_path[ 0 ] ), 'uploads' ) ); // get from uploads on included
                                $rvm_retrieve_custom_map_dir_path_temp = substr( $rvm_retrieve_custom_map_dir_path_temp, 8 ); // strips out uploads/
                                $rvm_retrieve_custom_map_url_path_temp = substr( $rvm_retrieve_custom_map_dir_and_url_path[ 1 ], strpos( strtolower( $rvm_retrieve_custom_map_dir_and_url_path[ 1 ] ), 'uploads' ) ); // get from uploads on included
                                $rvm_retrieve_custom_map_url_path_temp = substr( $rvm_retrieve_custom_map_url_path_temp, 8 ); // strips out uploads/
                                $rvm_retrieve_custom_map_dir_and_url_path[ 0 ] = $destination_basedir_path . '/' . $rvm_retrieve_custom_map_dir_path_temp;
                                $rvm_retrieve_custom_map_dir_and_url_path[ 1 ] = $destination_baseurl_path . '/' . $rvm_retrieve_custom_map_url_path_temp;
                                //$rvm_retrieve_custom_map_dir_and_url_path = array_replace( $rvm_retrieve_custom_map_dir_and_url_path,  $rvm_retrieve_custom_map_dir_and_url_path_temp );
                }
                return $rvm_retrieve_custom_map_dir_and_url_path;
}
// Check if we are in a custom map
function rvm_is_custom_map( $postid ) {
                $rvm_is_custom_map       = false;
                $rvm_custom_map_name     = get_post_meta( $postid, '_rvm_mbe_select_map', true );
                $rvm_custom_maps_options = rvm_retrieve_custom_maps_options();
                if ( !empty( $rvm_custom_maps_options ) && !empty( $rvm_custom_map_name ) ) {
                                $rvm_custom_maps_options = array_reverse( $rvm_custom_maps_options );
                                foreach ( $rvm_custom_maps_options as $key => $value ) {
                                                if ( $key === trim( $rvm_custom_map_name ) ) {
                                                                $rvm_is_custom_map = true;
                                                } //$key === trim( $rvm_custom_map_name )
                                } //$rvm_custom_maps_options as $key => $value
                } //!empty( $rvm_custom_maps_options ) && !empty( $rvm_custom_map_name )
                return $rvm_is_custom_map;
}
function rvm_region_match_when_numeric( $value ) {
                if ( substr( trim( $value ), 0, 4 ) === PREFIX ) {
                                $path = substr( trim( $value ), 4 );
                } //substr( trim( $value ), 0, 4 ) === PREFIX
                else {
                                $path = $value;
                }
                return $path;
}
function rvm_is_map_in_download_dir_yet( $dir, $rvm_custom_map_name ) {
                $rvm_upload_dir = scandir( $dir );
                if ( in_array( $rvm_custom_map_name, $rvm_upload_dir ) ) {
                                $rvm_custom_map_still_in_upload_dir = true;
                } //in_array( $rvm_custom_map_name, $rvm_upload_dir )
                else {
                                $rvm_custom_map_still_in_upload_dir = false;
                }
                return $rvm_custom_map_still_in_upload_dir;
}
function rvm_is_dir_path_dynamic( $dir ) {
                //If the custom map path are in the old long format
                if ( count( explode( '/', $dir ) ) > 2 ) {
                                $rvm_is_dir_path_dynamic = false;
                } //count( explode( '/', $dir ) ) > 2
                else {
                                //If the custom map path are in the new dynamic format year/month
                                $rvm_is_dir_path_dynamic = true;
                }
                return $rvm_is_dir_path_dynamic;
}
function rvm_include_custom_map_settings( $map_id, $rvm_selected_map ) {
                if ( rvm_is_custom_map( $map_id ) || rvm_retrieve_custom_maps_options() ) {
                                $rvm_custom_maps_options = rvm_retrieve_custom_maps_options();
                                $rvm_custom_maps_options = array_reverse( $rvm_custom_maps_options );
                                foreach ( $rvm_custom_maps_options as $key => $value ) {
                                                if ( $key === trim( $rvm_selected_map ) ) {
                                                                $rvm_retrieve_custom_map_dir_and_url_path = rvm_retrieve_custom_map_dir_and_url_path( $value );
                                                                include $rvm_retrieve_custom_map_dir_and_url_path[ 0 ] . $key . '/rvm-cm-settings.php';
                                                                $rvm_custom_maps_found_in_option = true;
                                                } //$key === trim( $rvm_selected_map )
                                } //$rvm_custom_maps_options as $key => $value
                } //if ( rvm_is_custom_map( $map_id ) || rvm_retrieve_custom_maps_options() )
                if ( !isset( $rvm_custom_maps_found_in_option ) ) {
                                include RVM_INC_PLUGIN_DIR . '/regions/' . $rvm_selected_map . '-regions.php';
                } //!isset($rvm_custom_maps_found_in_option)
                return $regions;
}
function rvm_retrieve_options( ) {
                // Retrieve all user options from DB
                $rvm_options = get_option( 'rvm_options' );
                return $rvm_options;
}
function rvm_retrieve_custom_maps_url_path( $rvm_custom_map_name ) {
    
                //Get custom maps if exist on DB
            $rvm_custom_maps_options = rvm_retrieve_custom_maps_options();

            //Here $key is the javascript name and $value the path to javascript itself
            if ( !empty( $rvm_custom_maps_options ) ) {
                        // get last value entered temporally
                        $rvm_custom_maps_options = array_reverse( $rvm_custom_maps_options );
                        foreach ( $rvm_custom_maps_options as $key => $value ) {
                            if( $rvm_custom_map_name ==  $key ) {
                                    $rvm_retrieve_custom_map_dir_and_url_path = rvm_retrieve_custom_map_dir_and_url_path( $value );
                                    // Check if custom map is still in original upload subdir: if not do not show it in drop down
                                    $rvm_is_map_in_download_dir_yet = rvm_is_map_in_download_dir_yet( $rvm_retrieve_custom_map_dir_and_url_path[ 0 ] , $key ) ;
                                    
                                     if ( $rvm_is_map_in_download_dir_yet ) {
                                                return $rvm_retrieve_custom_map_dir_and_url_path[ 1 ]  . $key . '/jquery-jvectormap-' . $key . '.js';
                                     }//if ( $rvm_is_map_in_download_dir_yet ) 
                                     break;                                   
                            }  //if( $rvm_custom_map_name ==  $value )                                   
                        } //$rvm_custom_maps_options as $key => $value
            } //!empty( $rvm_custom_maps_options )
}
?>