<?php

defined( 'ABSPATH' ) or exit;

global $wpdb;

$table_name = $wpdb->prefix . 'mc4wp_log';

// remove all failed log items
$wpdb->query( "DELETE FROM `{$table_name}` WHERE `success` = 0" );

// drop `success` column
$wpdb->query( "ALTER TABLE `{$table_name}` DROP COLUMN `success`" );