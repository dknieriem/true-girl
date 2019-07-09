<?php

defined( 'ABSPATH' ) or exit;

/**
 * @var WPDB $wpdb
 */
global $wpdb, $charset_collate;
$table_name = $wpdb->prefix . 'mc4wp_log';

$wpdb->suppress_errors(true);
$wpdb->hide_errors();

$wpdb->query( "ALTER TABLE `{$table_name}` CHANGE COLUMN `email` `email_address` VARCHAR(255)" );
$wpdb->query( "ALTER TABLE `{$table_name}` CHANGE COLUMN `list_ids` `list_id` VARCHAR(255)" );
$wpdb->query( "ALTER TABLE `{$table_name}` CHANGE COLUMN `data` `merge_fields` TEXT NULL" );
$wpdb->query( "ALTER TABLE `{$table_name}` ADD COLUMN `interests` TEXT NULL" );
$wpdb->query( "ALTER TABLE `{$table_name}` ADD COLUMN `status` VARCHAR(60) NULL" );
$wpdb->query( "ALTER TABLE `{$table_name}` ADD COLUMN `email_type` VARCHAR(4) NULL" );
$wpdb->query( "ALTER TABLE `{$table_name}` ADD COLUMN `ip_signup` VARCHAR(255) NULL" );
$wpdb->query( "ALTER TABLE `{$table_name}` ADD COLUMN `language` VARCHAR(127) NULL" );
$wpdb->query( "ALTER TABLE `{$table_name}` ADD COLUMN `vip` TINYINT(1) NULL" );