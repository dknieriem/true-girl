<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}


/**
 * Appends the WordPress tables prefix and the default tqb_ prefix to the table name
 *
 * @param string $table name of the table.
 *
 * @return string the modified table name
 */
function tge_table_name( $table ) {
	global $wpdb;

	return $wpdb->prefix . Thrive_Graph_Editor::DB_PREFIX . $table;
}
