<?php

if (!defined('ABSPATH')) die('No direct access allowed');

/**
 * All cache commands that are intended to be available for calling from any sort of control interface (e.g. wp-admin, UpdraftCentral) go in here. All public methods should either return the data to be returned, or a WP_Error with associated error code, message and error data.
 */
class WP_Optimize_Cache_Commands {

	private $optimizer;

	private $options;

	/**
	 * WP_Optimize_Cache_Commands constructor.
	 */
	public function __construct() {
		$this->optimizer = WP_Optimize()->get_optimizer();
		$this->options = WP_Optimize()->get_options();
	}

	/**
	 * Save cache settings
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function save_cache_settings($data) {

		if (!class_exists('WPO_Cache_Config')) return array(
			'result' => false,
			'message' => "WPO_Cache_Config class doesn't exist",
		);

		// disable cache.
		if (empty($data['cache-settings']['enable_page_caching'])) {
			WPO_Page_Cache::instance()->disable();
		} else {
			// we need to rebuild advanced-cache.php and add WP_CACHE to wp-config.
			WPO_Page_Cache::instance()->enable();
		}

		$save_settings_result = WPO_Cache_Config::instance()->update($data['cache-settings']);

		return array(
			'result' => $save_settings_result,
		);
	}

	/**
	 * Purge WP-Optimize page cache.
	 *
	 * @return bool
	 */
	public function purge_page_cache() {
		return WP_Optimize()->get_page_cache()->purge();
	}

	/**
	 * Enable or disable browser cache.
	 *
	 * @param array $params - ['browser_cache_expire' => '1 month 15 days 2 hours' || '' - for disable cache]
	 * @return array
	 */
	public function enable_browser_cache($params) {
		return WP_Optimize()->get_browser_cache()->enable_browser_cache_command_handler($params);
	}
}
