<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

$image_settings    = new TIE_Image_Settings( 0 );
$settings          = $image_settings->get_data();
$settings['fonts'] = array(
	'Cabin' => '//fonts.googleapis.com/css?family=Cabin',
	'Roboto' => '//fonts.googleapis.com/css?family=Roboto',
);
return array(
	'name'     => __( 'Stylish', Thrive_Image_Editor::T ),
	'settings' => $settings,
);
