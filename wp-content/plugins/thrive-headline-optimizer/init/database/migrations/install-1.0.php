<?php
/**
 * Main installer script
 */


defined( 'THO_DB_UPGRADE' ) or exit();
global $wpdb;

$log_table       = tho_table_name( 'event_log' );
$test_table      = tho_table_name( 'tests' );
$test_item_table = tho_table_name( 'test_items' );

$sql = "CREATE TABLE IF NOT EXISTS {$log_table}(
    `id` INT( 11 ) AUTO_INCREMENT,
    `date` DATETIME NULL,
    `log_type` TINYINT( 2 ),
    `engagement_type` TINYINT( 2 ),
    `post_id` INT( 11 ),
    `variation` INT( 11 ),
    `post_type` VARCHAR( 128 ) NULL,
    `referrer` VARCHAR( 255 ) NULL,
    `archived` TINYINT( 1 ) NULL DEFAULT '0',
     PRIMARY KEY( `id` )
 ) CHARACTER SET utf8 COLLATE utf8_general_ci;";
$wpdb->query( $sql );

$sql = "CREATE TABLE IF NOT EXISTS {$test_table} (
    `id` INT( 11 ) AUTO_INCREMENT,
    `post_id` INT( 11 ),
    `date_started` DATETIME NULL DEFAULT NULL,
    `date_completed` DATETIME NULL DEFAULT NULL,
    `config` TEXT NULL DEFAULT NULL,
    `status` INT ( 3 ),
     PRIMARY KEY( `id` )
) CHARACTER SET utf8 COLLATE utf8_general_ci;";
$wpdb->query( $sql );

$sql = "CREATE TABLE IF NOT EXISTS {$test_item_table} (
    `id` INT( 11 ) AUTO_INCREMENT,
    `test_id` INT( 11 ),
    `variation_title` TEXT NULL DEFAULT NULL,
    `is_control` INT( 1 ) NULL DEFAULT '0',
    `is_winner` INT( 1 ) NULL DEFAULT '0',
    `views` INT( 11 ) NULL DEFAULT '0',
    `engagements` INT( 11 ) NULL DEFAULT '0',
     PRIMARY KEY( `id` )
 ) CHARACTER SET utf8 COLLATE utf8_general_ci;";
$wpdb->query( $sql );
