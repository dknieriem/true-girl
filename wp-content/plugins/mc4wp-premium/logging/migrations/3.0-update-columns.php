<?php

defined( 'ABSPATH' ) or exit;

global $wpdb;

$table_name = $wpdb->prefix . 'mc4wp_log';

$wpdb->suppress_errors(true);
$wpdb->hide_errors();

// merge columns `form_ID` and `comment_ID` into `related_object_ID`
$wpdb->query( "ALTER TABLE `{$table_name}` CHANGE COLUMN `form_ID` `related_object_ID` BIGINT(20)" );
$wpdb->query( "UPDATE `{$table_name}` SET `related_object_ID` = `comment_ID` WHERE `related_object_ID` = 0 AND `comment_ID` > 0 " );
$wpdb->query( "ALTER TABLE `{$table_name}` DROP COLUMN `comment_ID`" );

// add 'success' column
$wpdb->query( "ALTER TABLE `{$table_name}` ADD COLUMN `success` TINYINT(1) DEFAULT 1" );

// rename columns
$wpdb->query( "ALTER TABLE `{$table_name}` CHANGE COLUMN `signup_method` `method` VARCHAR(255)" );
$wpdb->query( "ALTER TABLE `{$table_name}` CHANGE COLUMN `signup_type` `type` VARCHAR(255)" );
$wpdb->query( "ALTER TABLE `{$table_name}` CHANGE COLUMN `merge_vars` `data` TEXT" );

// alter datatype of `datetime`
$wpdb->query( "ALTER TABLE `{$table_name}` CHANGE COLUMN `datetime` `datetime` timestamp DEFAULT CURRENT_TIMESTAMP" );

// change `sign-up-form` to just `form`
$wpdb->query( "UPDATE `{$table_name}` SET `type` = 'form' WHERE `type` = 'sign-up-form'" );
$wpdb->query( "UPDATE `{$table_name}` SET `type` = 'form' WHERE `method` = 'form'" );

// drop `method` column
$wpdb->query( "ALTER TABLE `{$table_name}` DROP COLUMN `method`" );


