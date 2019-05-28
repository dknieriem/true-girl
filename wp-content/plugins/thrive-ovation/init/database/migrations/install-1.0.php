<?php
/**
 * Main installer script
 */


defined( 'TVO_DB_UPGRADE' ) or exit();
global $wpdb;

$activity_log_table       = tvo_table_name( 'activity_log' );

$sql = "CREATE TABLE IF NOT EXISTS {$activity_log_table} (
    `id` INT( 11 ) AUTO_INCREMENT,
    `post_id` INT( 11 ),
    `date` DATETIME NULL,
    `activity_type` VARCHAR( 128 ) NULL,
    `activity_data` TEXT NULL DEFAULT NULL,
     PRIMARY KEY( `id` )
 ) CHARACTER SET utf8 COLLATE utf8_general_ci;";
$wpdb->query( $sql );

