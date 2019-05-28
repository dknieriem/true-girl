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
	'Permanent Marker' => '//fonts.googleapis.com/css?family=Permanent Marker',
	'Lato'             => '//fonts.googleapis.com/css?family=Lato',
	'Roboto' => '//fonts.googleapis.com/css?family=Roboto',
);

return array(
	'name'     => __( 'Dynamic', Thrive_Image_Editor::T ),
	'settings' => $settings
);
