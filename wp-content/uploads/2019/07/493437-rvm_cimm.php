<?php

//RVM Marker Module: ver 1.1 rel March 2019


/*
* Start Translations
*
*/

if ( get_locale() == 'it_IT') {
    //if italian
    $rvm_choose_marker_icon = 'Scegli la Tua Icona per I Marker Personalizzata';
    $rvm_select_your_marker_icon = 'Selezione la Tua Icona per I Marcatori';
    $rvm_actual_marker_icon = 'Attuale Icona dei Marcatori';
    $rvm_restore_default_markers_icon = 'Ripristina l\'Icona predefinita dei Marcatori';
    $rvm_your_marker_icon = 'La tua Icona per i Marcatori';
}

else {
    //english, default
    $rvm_choose_marker_icon = 'Choose Custom Marker Icon';
    $rvm_select_your_marker_icon = 'Select Your Marker Icon';
    $rvm_actual_marker_icon = 'Actual Marker Icon';
    $rvm_restore_default_markers_icon = 'Restore default Marker Icon';
    $rvm_your_marker_icon = 'Your Custom Marker Icon';
}

/*
* End Translations
*
*/

$output .= '<div id="rvm_marker_icon_uploader_wrapper" class="rvm_wrapper" data-marker-module-ver="1.0">';
$output .= '<h3>' . $rvm_your_marker_icon . '</h3>';
$output .= $output_markers_custom_icon_path;//call value in rvm_core.php
$output .= $output_markers_custom_icon_path_hidden;//call value in rvm_core.php
$output .= '<input id="rvm_custom_marker_icon_module_uploader_button" class="rvm_custom_marker_icon_module_uploader_button rvm_media_uploader button-primary" name="rvm_mbe_custom_marker_icon_uploader_button" value="' . $rvm_select_your_marker_icon . '" type="submit">';
//Actual Marker icon
if( rvm_check_custom_marker_icon_available( $rvm_custom_marker_icon_path ) ) {
    $output .= '<div id="rvm_actual_marker_icon_wrapper" class="rvm_wrapper">';
    $output .= '<h4>' . $rvm_actual_marker_icon . '</h4>';   
    $output .= '<img src="' . $rvm_marker_module_dir_url_array[0] . $rvm_custom_marker_icon_path  . '" alt="' .esc_attr( $rvm_actual_marker_icon ) . '">';
    $output .= '</div>';//#rvm_actual_marker_icon_wrapper

    //Restore Deafault marker icon
    //$output .= '<input type="hidden"  id="rvm_mbe_post_id" value="' .  $post->ID. '" />' ;
    $output .= '<div id="rvm_marker_default_icon_restored"></div>';
    $output .= '<div id="rvm_restore_marker_default_icon_wrapper" class="rvm_wrapper">';
    $output .= '<input id="rvm_mbe_restore_marker_default_icon" class="button-secondary" name="rvm_mbe_restore_marker_default_icon" type="submit" value="' . $rvm_restore_default_markers_icon . '">';
    $output .= '</div>';//#rvm_restore_marker_default_icon_wrapper

}//if( isset ( $rvm_custom_marker_icon_path ) && !empty( $rvm_custom_marker_icon_path) && $rvm_custom_marker_icon_path != 'default' )

$output .= '</div>';//#rvm_marker_icon_uploader_wrapper

$output .= '<hr class="rvm_separator">';

?>