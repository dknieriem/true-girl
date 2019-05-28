<?php
/**
 * Thrive Themes  https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

global $wpdb;
$users = tqb_table_name( 'users' );

$sqls   = array();
$sqls[] = " ALTER TABLE {$users} CHANGE `points` `points` VARCHAR(255) NULL DEFAULT NULL;";

foreach ( $sqls as $sql ) {
	if ( $wpdb->query( $sql ) === false ) {
		return false;
	}
}

return true;
