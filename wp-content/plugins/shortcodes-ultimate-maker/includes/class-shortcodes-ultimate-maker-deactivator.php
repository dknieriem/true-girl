<?php

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since        1.5.5
 * @package      Shortcodes_Ultimate_Maker
 * @subpackage   Shortcodes_Ultimate_Maker/includes
 */
class Shortcodes_Ultimate_Maker_Deactivator {

	/**
	 * Plugin deactivation.
	 *
	 * @since    1.5.5
	 */
	public static function deactivate() {
		self::deactivate_license_key();
	}

	/**
	 * Deactivate license key.
	 *
	 * @access  private
	 * @since   1.5.5
	 */
	private static function deactivate_license_key() {

		$addon_id = 'shortcode-creator';
		$option   = "su_option_{$addon_id}_license";
		$key      = get_option( $option );
		$api_url  = 'https://getshortcodes.com/api/v1/deactivate-license-key';

		delete_option( $option );

		if ( empty( $key ) ) {
			return;
		}

		wp_remote_post( $api_url, array(
				'timeout' => 5,
				'headers' => array(
					'Content-Type' => 'application/json'
				),
				'body'    => json_encode( array(
						'site'    => parse_url( home_url(), PHP_URL_HOST ),
						'key'     => $key,
						'product' => $addon_id,
					) ),
			) );

	}

}
