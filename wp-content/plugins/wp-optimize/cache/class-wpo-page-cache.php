<?php
/**
 * Page caching functionality
 */

if (!defined('ABSPATH')) die('No direct access allowed');

/**
 * Base cache directory, everything else goes under here
 */
if (!defined('WPO_CACHE_DIR')) define('WPO_CACHE_DIR', untrailingslashit(WP_CONTENT_DIR).'/wpo-cache');

/**
 * Extensions directory.
 */
if (!defined('WPO_CACHE_EXT_DIR')) define('WPO_CACHE_EXT_DIR', dirname(__FILE__).'/extensions');

/**
 * Directory that stores config and related files
 */
if (!defined('WPO_CACHE_CONFIG_DIR')) define('WPO_CACHE_CONFIG_DIR', WPO_CACHE_DIR.'/config');

/**
 * Directory that stores the cache, including gzipped files and mobile specifc cache
 */
if (!defined('WPO_CACHE_FILES_DIR')) define('WPO_CACHE_FILES_DIR', untrailingslashit(WP_CONTENT_DIR).'/cache/wpo-cache');


if (!class_exists('WPO_Cache_Config')) require_once(dirname(__FILE__) . '/class-wpo-cache-config.php');
if (!class_exists('WPO_Cache_Rules')) require_once(dirname(__FILE__) . '/class-wpo-cache-rules.php');

if (!class_exists('Updraft_Abstract_Logger')) require_once(WPO_PLUGIN_MAIN_PATH.'/includes/class-updraft-abstract-logger.php');
if (!class_exists('Updraft_PHP_Logger')) require_once(WPO_PLUGIN_MAIN_PATH.'/includes/class-updraft-php-logger.php');

if (!class_exists('Updraft_Abstract_Logger')) require_once(WPO_PLUGIN_MAIN_PATH . '/includes/class-updraft-abstract-logger.php');
if (!class_exists('Updraft_PHP_Logger')) require_once(WPO_PLUGIN_MAIN_PATH . '/includes/class-updraft-php-logger.php');

require_once dirname(__FILE__) . '/file-based-page-cache-functions.php';
wpo_cache_load_extensions();

if (!class_exists('WPO_Page_Cache')) :

class WPO_Page_Cache {

	/**
	 * Cache config object
	 *
	 * @var mixed
	 */
	public $config;

	/**
	 * Logger for this class
	 *
	 * @var mixed
	 */
	public $logger;

	/**
	 * Instance of this class
	 *
	 * @var mixed
	 */
	public static $instance;


	/**
	 * Set everything up here
	 */
	public function __construct() {
		$this->config = WPO_Cache_Config::instance();
		$this->rules  = WPO_Cache_Rules::instance();
		$this->logger = new Updraft_PHP_Logger();
	}

	/**
	 * Enables page cache
	 *
	 * @return WP_Error|bool - true on success, error otherwise
	 */
	public function enable() {
		static $already_ran_enable = false;

		if ($already_ran_enable) return true;

		if (!$this->create_folders()) {
			return new WP_Error("create_folders", "The request to the filesystem to create the cache directories failed");
		}


		if (!$this->write_advanced_cache()) {
			return new WP_Error("write_advanced_cache", "The request to write the advanced-cache.php file failed");
		}


		if (!$this->write_wp_config(true)) {
			return new WP_Error("write_wp_config", "Could not toggle the WP_CACHE constant in wp-config.php");
		}

		if (!$this->verify_cache()) {
			return new WP_Error("verify_cache", "Could not verify if cache was enabled");
		}

		$already_ran_enable = true;

		return true;
	}


	/**
	 * Disables page cache
	 *
	 * @return bool - true on success, false otherwise
	 */
	public function disable() {
		$ret = true;

		if (false === self::clean(untrailingslashit(WP_CONTENT_DIR) . '/advanced-cache.php')) {
			$this->log("The request to the filesystem to write the advanced-cache.php file failed");
			$ret = false;
		}

		if (!$this->write_wp_config(false)) {
			$this->log("Could not toggle the WP_CACHE constant in wp-config.php");
			$ret = false;
		}

		// Delete cache to avoid stale cache on next activation
		$this->purge();

		return $ret;
	}


	/**
	 * Purges the cache
	 *
	 * @return bool - true on success, false otherwise
	 */
	public function purge() {

		if (!self::delete(WPO_CACHE_FILES_DIR)) {
			$this->log("The request to the filesystem to delete the cache failed");
			return false;
		}

		return true;
	}

	/**
	 * Purges the cache
	 *
	 * @return bool - true on success, false otherwise
	 */
	public function clean_up() {

		$this->disable();

		if (!self::delete(WPO_CACHE_DIR, true)) {
			$this->log("The request to the filesystem to clean up the cache failed");
			return false;
		}

		return true;
	}

	/**
	 * Check if cache is enabled and working
	 *
	 * @return bool - true on success, false otherwise
	 */
	public function is_enabled() {

		if (!defined('WP_CACHE') || !WP_CACHE) {
			return false;
		}

		if (!defined('WPO_ADVANCED_CACHE') || !WPO_ADVANCED_CACHE) {
			return false;
		}

		if (!$this->config->get_option('enable_page_caching', false)) {
			return false;
		}

		return true;
	}

	/**
	 * Create the folder structure needed for cache to work
	 *
	 * @return bool - true on success, false otherwise
	 */
	private function create_folders() {

		if (!is_dir(WPO_CACHE_DIR) && !wp_mkdir_p(WPO_CACHE_DIR)) {
			$this->log('The request to the filesystem failed, unable to create - ' . WPO_CACHE_DIR);
			return false;
		}

		if (!is_dir(WPO_CACHE_CONFIG_DIR) && !wp_mkdir_p(WPO_CACHE_CONFIG_DIR)) {
			$this->log('The request to the filesystem failed, unable to create - ' . WPO_CACHE_CONFIG_DIR);
			return false;
		}
		
		if (!is_dir(WPO_CACHE_FILES_DIR) && !wp_mkdir_p(WPO_CACHE_FILES_DIR)) {
			$this->log('The request to the filesystem failed, unable to create - ' . WPO_CACHE_FILES_DIR);
			return false;
		}

		return true;
	}

	/**
	 * Writes advanced-cache.php
	 *
	 * @return bool
	 */
	private function write_advanced_cache() {

		$file = untrailingslashit(WP_CONTENT_DIR) . '/advanced-cache.php';
		$contents = '';

		if (!$this->config->get_option('enable_page_caching', false)) {
			return false;
		}

		$cache_file = untrailingslashit(plugin_dir_path(__FILE__)) . '/file-based-page-cache.php';
		$config_file = WPO_CACHE_CONFIG_DIR . '/config-' . $_SERVER['HTTP_HOST'] . '.php';
		$cache_path = WPO_CACHE_DIR;
		$cache_config_path = WPO_CACHE_CONFIG_DIR;
		$cache_files_path = WPO_CACHE_FILES_DIR;
		$cache_extensions_path = WPO_CACHE_EXT_DIR;

		// CS does not like heredoc
		// @codingStandardsIgnoreStart
		$contents = <<<EOF
<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (!defined('WPO_ADVANCED_CACHE')) define('WPO_ADVANCED_CACHE', true);
if (!defined('WPO_CACHE_DIR')) define('WPO_CACHE_DIR', '$cache_path');
if (!defined('WPO_CACHE_CONFIG_DIR')) define('WPO_CACHE_CONFIG_DIR', '$cache_config_path');
if (!defined('WPO_CACHE_FILES_DIR')) define('WPO_CACHE_FILES_DIR', '$cache_files_path');
if (!defined('WPO_CACHE_EXT_DIR')) define('WPO_CACHE_EXT_DIR', '$cache_extensions_path');

if (is_admin()) { return; }
if (!@file_exists('$config_file')) { return; }

\$GLOBALS['wpo_cache_config'] = json_decode(file_get_contents('$config_file'), true);

if (empty(\$GLOBALS['wpo_cache_config']) || empty(\$GLOBALS['wpo_cache_config']['enable_page_caching'])) { return; }

if (@file_exists('$cache_file')) { include_once('$cache_file'); }

EOF;
		// @codingStandardsIgnoreEnd
		if (!file_put_contents($file, $contents)) {
			return false;
		}

		return true;
	}

	/**
	 * Set WP_CACHE on or off in wp-config.php
	 *
	 * @param  boolean $status value of WP_CACHE.
	 * @return boolean true if the value was set, false otherwise
	 */
	private function write_wp_config($status = true) {

		if (defined('WP_CACHE') && WP_CACHE === $status) {
			return true;
		}

		$config_path = $this->_get_wp_config();

		// Couldn't find wp-config.php.
		if (!$config_path) {
			return false;
		}

		$config_file_string = file_get_contents($config_path);

		// Config file is empty. Maybe couldn't read it?
		if (empty($config_file_string)) {
			return false;
		}

		$config_file = preg_split("#(\n|\r)#", $config_file_string);
		$line_key    = false;

		foreach ($config_file as $key => $line) {
			if (!preg_match('/^\s*define\(\s*(\'|")([A-Z_]+)(\'|")(.*)/i', $line, $match)) {
				continue;
			}

			if ('WP_CACHE' === $match[2]) {
				$line_key = $key;
			}
		}

		if (false !== $line_key) {
			unset($config_file[$line_key]);
		}


		if ($status) {
			array_shift($config_file);
			array_unshift($config_file, '<?php', "define('WP_CACHE', true); // WP-Optimize Cache");
		}

		foreach ($config_file as $key => $line) {
			if ('' === $line) {
				unset($config_file[$key]);
			}
		}
		if (!file_put_contents($config_path, implode("\r\n", $config_file))) {
			return false;
		}

		return true;
	}

	/**
	 * Verify we can write to the file system
	 *
	 * @return boolean
	 */
	private function verify_cache() {
		if (function_exists('clearstatcache')) {
			clearstatcache();
		}

		// First check wp-config.php.
		if (!$this->_get_wp_config() && !is_writable($this->_get_wp_config())) {
			$this->log("Unable to write to or find wp-config.php, please check file/folder permissions");
			return false;
		}

		// Now check wp-content. We need to be able to create files of the same user as this file.
		if (!is_writable(untrailingslashit(WP_CONTENT_DIR))) {
			$this->log("Unable to write inside the wp-content folder, please check file/folder permissions");
			return false;
		}

		// If the cache and config directories exist, make sure they're writeable.
		if (file_exists(WPO_CACHE_DIR)) {
			if (!is_writable(WPO_CACHE_DIR)) {
				$this->log("Unable to write inside the cache folder, please check file/folder permissions");
				return false;
			}
		}

		if (file_exists(WPO_CACHE_FILES_DIR)) {
			if (!is_writable(WPO_CACHE_FILES_DIR)) {
				$this->log("Unable to write inside the cache files folder, please check file/folder permissions");
				return false;
			}
		}

		if (file_exists(WPO_CACHE_CONFIG_DIR)) {
			if (!is_writable(WPO_CACHE_CONFIG_DIR)) {
				$this->log("Unable to write inside the cache configuration folder, please check file/folder permissions");
				return false;
			}
		}

		return true;
	}

	/**
	 * Update cache config. Used to support 3d party plugins.
	 */
	public function update_cache_config() {
		// get current cache settings.
		$current_config = $this->config->get();
		// and call update to change if need cookies and query variable names.
		$this->config->update($current_config);
	}

	/**
	 * Returns the path to wp-config
	 *
	 * @return string wp-config.php path.
	 */
	private function _get_wp_config() {

		$file = '/wp-config.php';
		$config_path = false;

		foreach (get_included_files() as $filename) {
			if (0 === stripos(strrev($filename), strrev($file))) {
				$config_path = $filename;
			}
		}

		// Couldn't find wp-config.php.
		if (!$config_path) {
			return false;
		}

		return $config_path;
	}

	/**
	 * Util to delete folders and/or files
	 *
	 * @param string $src
	 * @return boolean
	 */
	public static function delete($src) {

		return wpo_delete_files($src);

	}

	/**
	 * Make an empty file.
	 *
	 * @param string $src
	 */
	public static function clean($src) {
		return file_put_contents($src, '');
	}

	/**
	 * Logs error messages
	 *
	 * @param  string $message
	 * @return null|void
	 */
	public function log($message) {
		if (isset($this->logger)) {
			$this->logger->log('ERROR', $message);
		} else {
			error_log($message);
		}
	}

	/**
	 * Returns an instance of the current class, creates one if it doesn't exist
	 *
	 * @return object
	 */
	public static function instance() {
		if (empty(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

endif;
