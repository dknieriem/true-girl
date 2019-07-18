<?php

namespace WPS\Factories\DB;

if (!defined('ABSPATH')) {
	exit;
}

use WPS\DB;

class Settings_Plugin_Factory {

	protected static $settings = [];

	public static function build() {

		if (empty(self::$settings)) {

         $general = new DB\Settings_General();
         $connection = new DB\Settings_Connection();
         $license = new DB\Settings_License();
         $syncing = new DB\Settings_Syncing();
         $shop = new DB\Shop();

         $general_rows = $general->get_all_rows();
         $connection_rows = $connection->get_all_rows();
         $license_rows = $license->get_all_rows();
         $syncing_rows = $syncing->get_all_rows();
         $shop_rows = $shop->get_all_rows();

         self::$settings['general'] = !empty($general_rows) ? $general_rows[0] : false;
         self::$settings['connection'] = !empty($connection_rows) ? $connection_rows[0] : false;
         self::$settings['license'] = !empty($license_rows) ? $license_rows[0] : false;
         self::$settings['syncing'] = !empty($syncing_rows) ? $syncing_rows[0] : false;
         self::$settings['shop'] = !empty($shop_rows) ? $shop_rows[0] : false;

		}

		return self::$settings;

	}

}
