<?php
/**
 * Ajax calls
 * ----------------------------------------------------------------------------
 */
/* Map Preview */
add_action( 'wp_ajax_rvm_preview', 'rvm_ajax_preview' );
function rvm_ajax_preview( ) {
                if ( isset( $_REQUEST[ 'nonce' ] ) && isset( $_REQUEST[ 'map' ] ) && $_REQUEST[ 'map' ] != 'select_country' ) {
                                // Verify that the incoming request is coming with the security nonce
                                if ( wp_verify_nonce( $_REQUEST[ 'nonce' ], 'rvm_ajax_nonce' ) ) {
                                                //inject html and javascript to create teh map preview
                                                $array_countries = rvm_countries_array();
                                                foreach ( $array_countries as $country_field ) {
                                                                if ( $_REQUEST[ 'map' ] == $country_field[ 0 ] ) {
                                                                                $js_map_id  = $country_field[ 3 ];
                                                                                $js_vectormap = $country_field[ 2 ];
                                                                                $map_group    = $country_field[ 5 ];
                                                                                $js_map_path  = $country_field[ 7 ];
                                                                } //$_REQUEST[ 'map' ] == $country_field[ 0 ]
                                                } // foreach( $array_countries as $country_field )*/
                                                $map_zoom               = empty( $_REQUEST[ 'zoom' ] ) ? 'false' : 'true';
                                                $map_bg_selected_status = empty( $_REQUEST[ 'subdivisionselectedstatus' ] ) ? 'false' : 'true';
                                                // If custom map load javascript from upload map subdir 
                                                if ( $map_group === 'custom_maps' && $js_map_path ) {
                                                                $rvm_custom_map_url = $js_map_path;
                                                                $output = '<script type="text/javascript" src="' . $rvm_custom_map_url . '/jquery-jvectormap-' . $js_map_id . '.js"></script>';
                                                } //$map_group === 'custom_maps' && $js_map_path
                                                else {
                                                                $output = '<script type="text/javascript" src="' . RVM_JS_JVECTORMAP_PLUGIN_DIR . '/jquery-jvectormap-' . $js_map_id . '.js"></script>';
                                                }
                                                $map_name = $_REQUEST[ 'map' ];
                                                $map_transparent_canvas = !empty( $_REQUEST[ 'transparentcanvas' ] ) ? true : false;
                                                $map_canvas_color = !empty( $_REQUEST[ 'canvascolor' ] ) ? $_REQUEST[ 'canvascolor' ] : RVM_CANVAS_BG_COLOUR; //default setting fallback
                                                if ( $map_transparent_canvas ) {
                                                                $map_canvas_color = 'transparent';
                                                } //$map_transparent_canvas
                                                $map_bg_color = !empty( $_REQUEST[ 'bgcolor' ] ) ? $_REQUEST[ 'bgcolor' ] : RVM_MAP_BG_COLOUR;
                                                $map_bg_selected_color = !empty( $_REQUEST[ 'bgselectedcolor' ] ) ? $_REQUEST[ 'bgselectedcolor' ] : RVM_MAP_BG_SELECTED_COLOUR;
                                                $map_border_color = !empty( $_REQUEST[ 'bordercolor' ] ) ? $_REQUEST[ 'bordercolor' ] : RVM_MAP_BORDER_COLOUR;
                                                $map_border_width = !empty( $_REQUEST[ 'borderwidth' ] ) ? $_REQUEST[ 'borderwidth' ] : RVM_MAP_BORDER_WIDTH;
                                                $map_width = !empty( $_REQUEST[ 'width' ] ) ? 'style="width: ' . $_REQUEST[ 'width' ] . ';"' : '';
                                                $map_padding =  !empty( $_REQUEST[ 'padding' ] ) ? $_REQUEST[ 'padding' ] : '';
                                                // Get padding of the map
                                                if( $map_padding ) {
                                                                $output .= '<style>';
                                                                $output .= '#' . $map_name . '-map .jvectormap-container';
                                                                $output .= '{ padding: ' . $map_padding . ' !important; box-sizing: border-box !important}';
                                                                $output .= '</style>';
                                                } 
                                                $output .= '<div class="preview-map-container" id="' . $map_name . '-map" ' . $map_width . '></div>';
                                                $output .= '<script>';
                                                $output .= '(function($) { $(function(){';
                                                $output .= '$("#' . $map_name . '-map").vectorMap({ map: "' . $js_map_id . '",';
                                                $output .= 'regionsSelectable: ' . $map_bg_selected_status . ',';
                                                $output .= 'regionStyle: { initial: { fill: "' . $map_bg_color . '", "fill-opacity": 1, stroke: "' . $map_border_color . '", "stroke-width": ' . $map_border_width . ' }, 
                                    selected: { fill: "' . $map_bg_selected_color . '" }},
                                    backgroundColor: "' . $map_canvas_color . '",';
                                                $output .= 'zoomButtons: ' . $map_zoom . ', zoomOnScroll: false });';
                                                $output .= '});})(jQuery);</script>';
                                                echo $output;
                                                die( );
                                } //wp_verify_nonce( $_REQUEST[ 'nonce' ], 'rvm_ajax_nonce' )
                                else {
                                                die( __( 'There was an issue with the preview generation tool', RVM_TEXT_DOMAIN ) );
                                }
                } //isset( $_REQUEST[ 'nonce' ] ) && isset( $_REQUEST[ 'map' ] ) && $_REQUEST[ 'map' ] != 'select_country'
                else {
                                die( __( 'Choose a valid region from the drop down menu', RVM_TEXT_DOMAIN ) );
                }
} // add_action( 'wp_ajax_rvm_preview', 'rvm_ajax_preview' );
/* Custom Maps */
add_action( 'wp_ajax_rvm_custom_map', 'rvm_ajax_custom_map' );
function rvm_ajax_custom_map( $post_id ) {
                // check if custom_map value is sent
                if ( isset( $_REQUEST[ 'nonce' ] ) && isset( $_REQUEST[ 'map' ] ) && $_REQUEST[ 'map' ] = 'rvm_custom_map' ) {
                                if ( function_exists( 'unzip_file' ) ) {
                                                $output = '';
                                                $custom_map_filename_ext = '.zip';
                                                $custom_map_separator = '_';
                                                //Get uploaded map path getting rid of any spaces
                                                $custom_map_filename = trim( $_REQUEST[ 'custom_map_filename' ] );
                                                //check if filename has the .zip extension or not: this is not intended for file extension checking
                                                if ( rvm_retrieve_custom_map_ext( $custom_map_filename, $custom_map_filename_ext ) != $custom_map_filename_ext ) {
                                                                // so basically if user copied and pasted map name without the .zip extension, this is the right moment to add it :-)
                                                                $custom_map_filename = $custom_map_filename . $custom_map_filename_ext;
                                                } //rvm_retrieve_custom_map_ext( $custom_map_filename, $custom_map_filename_ext ) != $custom_map_filename_ext
                                                // Access the WP filesystem and upload dir
                                                WP_Filesystem();
                                                $destination = wp_upload_dir();
                                                $destination_dir_path = $destination[ 'path' ];
                                                $destination_url = $destination[ 'url' ];
                                                $destination_basedir_path = $destination[ 'basedir' ]; //i.e /Applications/MAMP/htdocs/wordpress4.3/wp-content/uploads
                                                $destination_baseurl_path = $destination[ 'baseurl' ]; // i.e http://localhost:8888/wordpress4.3/wp-content/uploads
                                                $destination_relative_uploads_path = _wp_relative_upload_path( $destination_dir_path ); // i.e 2015/10
                                                //Get list of files and directories inside WP uploads
                                                if ( is_dir( $destination_dir_path ) ) {
                                                                $rvm_upload_list = scandir( $destination_dir_path );
                                                                foreach ( $rvm_upload_list as $rvm_upload_entry ) { 
                                                                                //Check if file already exists in uploads directory
                                                                                if ( $rvm_upload_entry == rvm_retrieve_custom_map_raw_name( $custom_map_filename ) ) {
                                                                                                $rvm_map_is_in_uploads_already = true;
                                                                                                break;
                                                                                } //$rvm_upload_entry == rvm_retrieve_custom_map_raw_name( $custom_map_filename )
                                                                } //$rvm_upload_list as $rvm_upload_entry
                                                                //If already in directory
                                                                if ( isset( $rvm_map_is_in_uploads_already ) && $rvm_map_is_in_uploads_already ) {
                                                                                $old_map_dir_content = scandir( $destination_dir_path . '/' . $rvm_upload_entry );
                                                                                foreach ( $old_map_dir_content as $old_map_dir_content_single_file ) {
                                                                                                if ( $old_map_dir_content_single_file != '.' && $old_map_dir_content_single_file != '..' ) {
                                                                                                                // Clean directory content
                                                                                                                unlink( $destination_dir_path . '/' . $rvm_upload_entry . '/' . $old_map_dir_content_single_file );
                                                                                                } //$old_map_dir_content_single_file != '.' && $old_map_dir_content_single_file != '..'
                                                                                } //$old_map_dir_content as $old_map_dir_content_single_file
                                                                                // Delete old directory
                                                                                rmdir( $destination_dir_path . '/' . $rvm_upload_entry );
                                                                                //Unzip file content                                                 
                                                                                $unzipfile = unzip_file( $destination_dir_path . '/' . $custom_map_filename, $destination_dir_path );
                                                                } //isset( $rvm_map_is_in_uploads_already ) && $rvm_map_is_in_uploads_already
                                                                //Unzip file content
                                                                else {
                                                                                $unzipfile = unzip_file( $destination_dir_path . '/' . $custom_map_filename, $destination_dir_path );
                                                                }
                                                                //Get list of files and directories inside WP uploads again
                                                                $rvm_upload_list = scandir( $destination_dir_path );
                                                                // Now check if .zip file was succesfully unzipped
                                                                foreach ( $rvm_upload_list as $rvm_upload_entry ) {
                                                                                //$output  .=  $rvm_upload_entry . '<br>'; 
                                                                                //Check if the unzipped file matches the filename sent by user without ".zip" extension
                                                                                if ( $rvm_upload_entry != '.' && $rvm_upload_entry != '..' ) {
                                                                                                if ( $rvm_upload_entry == rvm_retrieve_custom_map_raw_name( $custom_map_filename ) ) {
                                                                                                                $rvm_valid_unzip = true;
                                                                                                                break;
                                                                                                } //$rvm_upload_entry == rvm_retrieve_custom_map_raw_name( $custom_map_filename )
                                                                                } //$rvm_upload_entry != '.' && $rvm_upload_entry != '..'
                                                                } //  foreach( $rvm_upload_list as  $rvm_upload_entry )
                                                                if ( $unzipfile && isset( $rvm_valid_unzip ) && $rvm_valid_unzip ) {
                                                                                // Now check if .zip file was succesfully unzipped
                                                                                foreach ( $rvm_upload_list as $rvm_upload_entry ) {
                                                                                                //Check if the zip file is still there, in case "Install your map" is clicked twice or more
                                                                                                if ( $rvm_upload_entry == $custom_map_filename ) {
                                                                                                                //Ok, we do not need you anymore: Destroy the .zip file  just uploaded and let's do a spring clean                                                
                                                                                                                unlink( $destination_dir_path . '/' . $custom_map_filename );
                                                                                                                break;
                                                                                                } //$rvm_upload_entry == rvm_retrieve_custom_map_raw_name( $custom_map_filename )
                                                                                } //  foreach( $rvm_upload_list as  $rvm_upload_entry )                
                                                                                //Get custom maps if exist on DB
                                                                                $rvm_custom_maps_options                                                             = rvm_retrieve_custom_maps_options();
                                                                                //push new value into the arary ( existing or not )
                                                                                $rvm_custom_maps_options[ rvm_retrieve_custom_map_raw_name( $custom_map_filename ) ] = $destination_relative_uploads_path . '/'; //new dynamic path format year/month
                                                                                // Let's save this path into db
                                                                                // we need this options in order to retrieve it inside the style and script register and enqueue functions
                                                                                update_option( 'rvm_custom_maps_options', $rvm_custom_maps_options );
                                                                                //Use following value to enable the publish button ONLY when a map is installed
                                                                                $output .= '<input type="hidden" id="rvm_custom_map_is_installed" name="rvm_custom_map_is_installed" value="1" />';
                                                                                $output .= '<p class="rvm_cm_messages"><img  src="' . RVM_IMG_PLUGIN_DIR . '/green-check2.png" alt="check" /><span>' . __( 'You have succesfully installed your custom map . Well done !', RVM_TEXT_DOMAIN ) . '<br>' . __( 'Now you can <strong>Publish</strong> your post.', RVM_TEXT_DOMAIN ) . '</span></p>';
                                                                                //Disable Install your map
                                                                                $output .= '<script>jQuery( "#unzip_button" ).attr("disabled", "disabled");</script>';
                                                                } //if ( $unzipfile && $rvm_valid_unzip  )
                                                                else {
                                                                                $output .= '<p class="rvm_cm_messages"><img  src="' . RVM_IMG_PLUGIN_DIR . '/warning-icon.png" alt="check" /><span>' . __( 'Damned... Something went wrong !  Please check if name of the map is correct ( place just map name)  or if you have uploaded the map previous month and try again uploading map now using wordpress media uploader.', RVM_TEXT_DOMAIN ) . '</span></p>';
                                                                }
                                                                echo $output;
                                                                die( );
                                                } //is_dir( $destination_dir_path )
                                                else {
                                                                die( __( 'It seems there is no directory where to find your map!', RVM_TEXT_DOMAIN ) );
                                                } //if( is_dir( $destination_dir_path ) )
                                } // if(  function_exists( 'unzip_file' )  )
                                else {
                                                die( __( 'You have not unzip_file() function available for you WP or you did not provided a valid map name... come on !', RVM_TEXT_DOMAIN ) );
                                }
                } // if( isset( $_REQUEST[ 'nonce' ] )
                else {
                                die( __( 'Please select the custom map option from the drop menu ', RVM_TEXT_DOMAIN ) );
                }
} //function rvm_ajax_custom_map
?>