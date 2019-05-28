<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * @param Plugin|Settings $plugin
 */
function updatePlugin( $plugin ) {

	if ( ! class_exists( 'PixelYourSite\Plugin_Updater' ) ) {
		require_once 'class-plugin-updater.php';
	}

	$license_key = $plugin->getOption( 'license_key' );

	new Plugin_Updater( 'https://www.pixelyoursite.com', $plugin->getPluginFile(), array(
			'version'   => $plugin->getPluginVersion(),
			'license'   => $license_key,
			'item_name' => $plugin->getPluginName(),
			'author'    => 'PixelYourSite'
		)
	);

}