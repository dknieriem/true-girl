<?php

if (!defined('ABSPATH')) die('No direct access allowed');

/**
 * File based page cache drop in
 */
require_once(dirname(__FILE__) . '/file-based-page-cache-functions.php');

if (!defined('WPO_CACHE_DIR')) define('WPO_CACHE_DIR', untrailingslashit(WP_CONTENT_DIR) . '/wpo-cache');

/**
 * Load extensions.
 */
wpo_cache_load_extensions();

// Don't cache robots.txt or htacesss.
if (strpos($_SERVER['REQUEST_URI'], 'robots.txt') !== false || strpos($_SERVER['REQUEST_URI'], '.htaccess') !== false) {
	return;
}

// Don't cache non-GET requests.
if (!isset($_SERVER['REQUEST_METHOD']) || 'GET' !== $_SERVER['REQUEST_METHOD']) {
	return;
}

$file_extension = $_SERVER['REQUEST_URI'];
$file_extension = preg_replace('#^(.*?)\?.*$#', '$1', $file_extension);
$file_extension = trim(preg_replace('#^.*\.(.*)$#', '$1', $file_extension));

// Don't cache disallowed extensions. Prevents wp-cron.php, xmlrpc.php, etc.
if (!preg_match('#index\.php$#i', $_SERVER['REQUEST_URI']) && in_array($file_extension, array( 'php', 'xml', 'xsl' ))) {
	return;
}

// Don't cache if logged in.
if (!empty($_COOKIE)) {
	$wp_cookies = array('wordpressuser_', 'wordpresspass_', 'wordpress_sec_', 'wordpress_logged_in_');

	if (empty($GLOBALS['wpo_cache_config']['enable_user_caching']) || false == $GLOBALS['wpo_cache_config']['enable_user_caching']) {
		foreach ($_COOKIE as $key => $value) {
			foreach ($wp_cookies as $cookie) {
				if (false !== strpos($key, $cookie)) {
					// Logged in!
					return;
				}
			}
		}
	}

	if (!empty($_COOKIE['wpo_commented_posts'])) {
		foreach ($_COOKIE['wpo_commented_posts'] as $path) {
			if (rtrim($path, '/') === rtrim($_SERVER['REQUEST_URI'], '/')) {
				// User commented on this post.
				return;
			}
		}
	}

	// get cookie exceptions from options.
	$cache_exception_cookies = !empty($GLOBALS['wpo_cache_config']['cache_exception_cookies']) ? $GLOBALS['wpo_cache_config']['cache_exception_cookies'] : array();
	// filter cookie exceptions.
	$cache_exception_cookies = apply_filters('wpo_cache_exception_cookies', $cache_exception_cookies);

	// check if any cookie exists from exception list.
	if (!empty($cache_exception_cookies)) {
		foreach ($_COOKIE as $key => $value) {
			foreach ($cache_exception_cookies as $cookie) {
				if ('' != trim($cookie) && false !== strpos($key, $cookie)) {
					return;
				}
			}
		}
	}
}

// check in not disabled current user agent
if (!empty($_SERVER['HTTP_USER_AGENT']) && false === wpo_is_accepted_user_agent($_SERVER['HTTP_USER_AGENT'])) return;

// Deal with optional cache exceptions.
if (!empty($GLOBALS['wpo_cache_config']['cache_exception_urls'])) {
	$exceptions = is_array($GLOBALS['wpo_cache_config']['cache_exception_urls']) ? $GLOBALS['wpo_cache_config']['cache_exception_urls'] : preg_split('#(\n|\r)#', $GLOBALS['wpo_cache_config']['cache_exception_urls']);

	foreach ($exceptions as $exception) {

		if (wpo_current_url_exception_match($exception)) {
			// Exception match.
			return;
		}
	}
}

if (!empty($_GET)) {
	// get variables used for building filename.
	$get_variable_names = wpo_cache_query_variables();

	// get current GET variables.
	$get_variables = array_keys($_GET);

	// if GET variables include one or more then we don't cache.
	$diff = array_diff($get_variables, $get_variable_names);
	if (!empty($diff)) return;
}

wpo_serve_cache();

ob_start('wpo_cache');
