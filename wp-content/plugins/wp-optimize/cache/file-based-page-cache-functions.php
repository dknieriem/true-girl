<?php

if (!defined('ABSPATH')) die('No direct access allowed');

/**
 * Extensions directory.
 */
if (!defined('WPO_CACHE_EXT_DIR')) define('WPO_CACHE_EXT_DIR', dirname(__FILE__).'/extensions');

/**
 * Holds utility functions used by file based cache
 */

/**
 * Cache output before it goes to the browser
 *
 * @param  string $buffer Page HTML.
 * @param  int    $flags  OB flags to be passed through.
 * @return string
 */
function wpo_cache($buffer, $flags) {
	global $post;

	if (strlen($buffer) < 255) {
		return $buffer;
	}

	// Don't cache pages for logged in users.
	if (is_user_logged_in()) {
		return $buffer;
	}

	// Don't cache search, 404, or password protected.
	if (is_404() || is_search() || !empty($post->post_password)) {
		return $buffer;
	}

	// No root cache folder, so short-circuit here
	if (!file_exists(WPO_CACHE_DIR)) return $buffer;

	// Try creating a folder for cached files, if it was flushed recently
	if (!file_exists(WPO_CACHE_FILES_DIR)) {
		if (!mkdir(WPO_CACHE_FILES_DIR)) {
			// Can not cache!
			return $buffer;
		}
	}

	$can_cache_page = apply_filters('wpo_can_cache_page', true, $buffer, $flags);

	if (!$can_cache_page) return $buffer;

	$buffer = apply_filters('wpo_pre_cache_buffer', $buffer, $flags);

	$url_path = wpo_get_url_path();

	$dirs = explode('/', $url_path);

	$path = WPO_CACHE_FILES_DIR;

	foreach ($dirs as $dir) {
		if (!empty($dir)) {
			$path .= '/' . $dir;

			if (!file_exists($path)) {
				if (!mkdir($path)) {
					// Can not cache!
					return $buffer;
				}
			}
		}
	}

	// Prevent mixed content when there's an http request but the site URL uses https.
	$home_url = get_home_url();

	if (!is_ssl() && 'https' === strtolower(parse_url($home_url, PHP_URL_SCHEME))) {
		$https_home_url = $home_url;
		$http_home_url  = str_ireplace('https://', 'http://', $https_home_url);
		$buffer		 = str_replace(esc_url($http_home_url), esc_url($https_home_url), $buffer);
	}

	if (preg_match('#</html>#i', $buffer)) {
		if (!empty($GLOBALS['wpo_cache_config']['enable_mobile_caching']) && wpo_is_mobile()) {
			$buffer .= "\n<!-- Cached by WP Optimize for mobile devices - Last modified: " . gmdate('D, d M Y H:i:s', $modified_time) . " GMT -->\n";
		} else {
			$buffer .= "\n<!-- Cached by WP Optimize - Last modified: " . gmdate('D, d M Y H:i:s', $modified_time) . " GMT -->\n";
		}
	}

	/**
	 * Save $buffer into cache file.
	 */
	$cache_filename = wpo_cache_filename();
	$cache_file = $path . '/' .$cache_filename;

	$contents = wpo_cache_gzip_enabled() ? gzencode($buffer, apply_filters('wpo_cache_gzip_level', 6)) : $buffer;
	$modified_time = time(); // Take this as soon before writing as possible

	file_put_contents($cache_file, $contents);

	header('Cache-Control: no-cache'); // Check back every time to see if re-download is necessary.
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $modified_time) . ' GMT');

	if (function_exists('ob_gzhandler') && !empty($GLOBALS['wpo_cache_config']['enable_gzip_compression'])) {
		return ob_gzhandler($buffer, $flags);
	} else {
		return $buffer;
	}
}

/**
 * Load files for support plugins.
 */
function wpo_cache_load_extensions() {
	$extensions = glob(WPO_CACHE_EXT_DIR . '/*.php');

	if (empty($extensions)) return;

	foreach ($extensions as $extension) {
		if (is_file($extension)) require_once $extension;
	}
}

/**
 * Get filename for store cache, depending on gzip, mobile and cookie settings.
 *
 * @param string $ext
 * @return string
 */
function wpo_cache_filename($ext = '.html') {
	$filename = 'index';

	if (wpo_cache_gzip_enabled()) {
		$ext .= '.gz';
	}

	if (wpo_cache_mobile_caching_enabled() && wpo_is_mobile()) {
		$filename = 'mobile.' . $filename;
	}

	$cookies = wpo_cache_cookies();

	$cache_key = '';

	/**
	 * Add cookie values to filename if need.
	 */
	if (!empty($cookies)) {
		foreach ($cookies as $key => $cookie_name) {
			if (is_array($cookie_name) && isset($_COOKIE[$key])) {
				foreach ($cookie_name as $cookie_key) {
					if (isset($_COOKIE[$key][$cookie_key]) && '' !== $_COOKIE[$key][$cookie_key]) {
						$_cache_key = $cookie_key.'='.$_COOKIE[$key][$cookie_key];
						$_cache_key = preg_replace('/[^a-z0-9_\-\=]/i', '-', $_cache_key);
						$cache_key .= '-' . $_cache_key;
					}
				}
				continue;
			}

			if (isset($_COOKIE[$cookie_name]) && '' !== $_COOKIE[$cookie_name]) {
				$_cache_key = $cookie_name.'='.$_COOKIE[$cookie_name];
				$_cache_key = preg_replace('/[^a-z0-9_\-\=]/i', '-', $_cache_key);
				$cache_key .= '-' . $_cache_key;
			}
		}
	}

	$query_variables = wpo_cache_query_variables();

	/**
	 * Add GET variables to cache file name if need.
	 */
	if (!empty($query_variables)) {
		foreach ($query_variables as $variable) {
			if (isset($_GET[$variable]) && !empty($_GET[$variable])) {
				$_cache_key = $variable.'='.$_GET[$variable];
				$_cache_key = preg_replace('/[^a-z0-9_\-\=]/i', '-', $_cache_key);
				$cache_key .= '-' . $_cache_key;
			}
		}
	}

	// add hash of queried cookies and variables to cache file name.
	if ('' !== $cache_key) {
		$filename .= '-' . md5($cache_key);
	}

	return $filename . $ext;
}

/**
 * Returns site url from site_url() function or if it is not available from cache configuration.
 */
function wpo_site_url() {
	if (is_callable('site_url')) return site_url('/');

	$site_url = empty($GLOBALS['wpo_cache_config']['site_url']) ? '' : $GLOBALS['wpo_cache_config']['site_url'];
	return $site_url;
}

/**
 * Get cookie names which impact on cache file name.
 *
 * @return array
 */
function wpo_cache_cookies() {
	$cookies = empty($GLOBALS['wpo_cache_config']['wpo_cache_cookies']) ? array() : $GLOBALS['wpo_cache_config']['wpo_cache_cookies'];
	return $cookies;
}

/**
 * Get GET variable names which impact on cache file name.
 *
 * @return array
 */
function wpo_cache_query_variables() {
	if (defined('WPO_CACHE_URL_PARAMS') && WPO_CACHE_URL_PARAMS) {
		$variables = array_keys($_GET);
	} else {
		$variables = empty($GLOBALS['wpo_cache_config']['wpo_cache_query_variables']) ? array() : $GLOBALS['wpo_cache_config']['wpo_cache_query_variables'];
	}

	if (!empty($variables)) {
		sort($variables);
	}

	return $variables;
}

/**
 * Check if gzip setting is set and available.
 *
 * @return bool
 */
function wpo_cache_gzip_enabled() {
	if (!empty($GLOBALS['wpo_cache_config']['enable_gzip_compression']) && function_exists('gzencode')) return true;
	return false;
}

/**
 * Check if mobile cache is enabled and current request is from moblile device.
 *
 * @return bool
 */
function wpo_cache_mobile_caching_enabled() {
	if (!empty($GLOBALS['wpo_cache_config']['enable_mobile_caching'])) return true;
	return false;
}

/**
 * Serves the cache and exits
 */
function wpo_serve_cache() {

	$file_name = wpo_cache_filename();

	$path = WPO_CACHE_FILES_DIR . '/' . rtrim(wpo_get_url_path(), '/') . '/' . $file_name;

	$modified_time = file_exists($path) ? (int) filemtime($path) : time();

	// Cache has expired, purge and exit.
	if (!empty($GLOBALS['wpo_cache_config']['page_cache_length'])) {
		if (time() > ($GLOBALS['wpo_cache_config']['page_cache_length'] + $modified_time)) {
			wpo_delete_files($path);
			return;
		}
	}

	header('Cache-Control: no-cache'); // Check back in an hour.

	if (!empty($modified_time) && !empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) === $modified_time) {
		if (function_exists('gzencode') && !empty($GLOBALS['wpo_cache_config']['enable_gzip_compression'])) {
			header('Content-Encoding: gzip');
		}

		header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304);
		exit;
	}

	if (file_exists($path) && is_readable($path)) {
		if (function_exists('gzencode') && !empty($GLOBALS['wpo_cache_config']['enable_gzip_compression'])) {
			header('Content-Encoding: gzip');
		}

		readfile($path);

		exit;
	}
}

/**
 * Clears the cache
 */
function wpo_cache_flush() {

	wpo_delete_files(WPO_CACHE_FILES_DIR);

	if (function_exists('wp_cache_flush')) {
		wp_cache_flush();
	}

	do_action('wpo_cache_flush');
}

/**
 * Get URL path for caching
 *
 * @since  1.0
 * @return string
 */
function wpo_get_url_path() {
	$url_parts = parse_url(wpo_current_url());

	if (!isset($url_parts['path'])) $url_parts['path'] = '';

	return $url_parts['host'].'/'.$url_parts['path'];
}

/**
 * Get requested url.
 *
 * @return string
 */
function wpo_current_url() {
	return rtrim('http' . ((isset($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'] || 1 == $_SERVER['HTTPS']) ||
			isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO']) ? 's' : '' )
		. '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}", '/');
}

/**
 * Return true of exception url matches current url
 *
 * @param  string $exception Exceptions to check URL against.
 * @param  bool   $regex	 Whether to check with regex or not.
 * @return bool   true if matched, false otherwise
 */
function wpo_current_url_exception_match($exception) {

	return wpo_url_exception_match(wpo_current_url(), $exception);
}

/**
 * Check if url string match with exception.
 *
 * @param string $url       - complete url string i.e. http(s):://domain/path
 * @param string $exception - complete url or absolute path, can consist (.*) wildcards
 *
 * @return bool
 */
function wpo_url_exception_match($url, $exception) {
	if (preg_match('#^[\s]*$#', $exception)) {
		return false;
	}

	$exception = str_replace('*', '.*', $exception);

	$exception = trim($exception);

	// used to test websites placed in subdirectories.
	$sub_dir = '';

	// if exception defined from root i.e. /page1 then remove domain part in url.
	if (preg_match('/^\//', $exception)) {
		// get site sub directory.
		$sub_dir = preg_replace('#^(http|https):\/\/.*\/#Ui', '', wpo_site_url());
		// add prefix slash and remove slash.
		$sub_dir = ('' == $sub_dir) ? '' : '/' . rtrim($sub_dir, '/');
		// get relative path
		$url = preg_replace('#^(http|https):\/\/.*\/#Ui', '/', $url);
	}

	$url = rtrim($url, '/') . '/';
	$exception = rtrim($exception, '/');

	// if we have no wildcat in the end of exception then add slash.
	if (!preg_match('#\(\.\*\)$#', $exception)) $exception .= '/';

	$exception = str_replace('/', '\/', $exception);

	return preg_match('#^'.$exception.'$#i', $url) || preg_match('#^'.$sub_dir.$exception.'$#i', $url);
}

/**
 * Checks if its a mobile device
 *
 * @see https://developer.wordpress.org/reference/functions/wp_is_mobile/
 */
function wpo_is_mobile() {
	if (empty($_SERVER['HTTP_USER_AGENT'])) {
		$is_mobile = false;
	// many mobile devices (all iPhone, iPad, etc.)
	} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false
	) {
		$is_mobile = true;
	} else {
		$is_mobile = false;
	}

	return $is_mobile;
}

/**
 * Check if current browser agent is not disabled in options.
 *
 * @return bool
 */
function wpo_is_accepted_user_agent($user_agent) {

	$exceptions = is_array($GLOBALS['wpo_cache_config']['cache_exception_browser_agents']) ? $GLOBALS['wpo_cache_config']['cache_exception_browser_agents'] : preg_split('#(\n|\r)#', $GLOBALS['wpo_cache_config']['cache_exception_browser_agents']);

	if (!empty($exceptions)) {
		foreach ($exceptions as $exception) {
			if ('' == trim($exception)) continue;

			if (preg_match('#'.$exception.'#i', $user_agent)) return false;
		}
	}

	return true;
}

/**
 * Delete function that deals with directories recursively
 *
 * @param string $src path of the folder
 *
 * @return bool
 */
function wpo_delete_files($src) {
	if (!file_exists($src)) {
		return true;
	}

	if (is_file($src)) {
		return unlink($src);
	}

	$success = true;

	$dir = opendir($src);
	$file = readdir($dir);

	while (false !== $file) {
		if (('.' != $file) && ('..' != $file)) {
			if (is_dir($src . '/' . $file)) {
				if (!wpo_delete_files($src . '/' . $file)) {
					$success = false;
				}
			} else {
				if (!unlink($src . '/' . $file)) {
					$success = false;
				}
			}
		}

		$file = readdir($dir);
	}

	closedir($dir);
	rmdir($src);

	return $success;
}
