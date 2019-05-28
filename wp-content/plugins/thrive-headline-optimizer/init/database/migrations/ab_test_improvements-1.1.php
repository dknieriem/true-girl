<?php
/**
 * A / B Tests improvements - Part 2
 */

defined( 'THO_DB_UPGRADE' ) or exit();
global $wpdb;

$test_item_table = tho_table_name( 'test_items' );

$sql = "ALTER TABLE {$test_item_table} ADD `active` TINYINT(2) NOT NULL DEFAULT '1' AFTER `engagements`, ADD `stopped_date` DATETIME NULL DEFAULT NULL AFTER `active`;";
$wpdb->query( $sql );
