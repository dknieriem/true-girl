<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 9/5/2016
 * Time: 11:28 AM
 *
 * @package Thrive Quiz Builder
 */

global $wpdb;

$collate = '';

if ( $wpdb->has_cap( 'collation' ) ) {
	$collate = $wpdb->get_charset_collate();
}

$form_variations = tqb_table_name( 'variations' );
$tests           = tqb_table_name( 'tests' );
$tests_items     = tqb_table_name( 'tests_items' );
$log_table       = tqb_table_name( 'event_log' );
$answers         = tqb_table_name( 'user_answers' );
$users           = tqb_table_name( 'users' );
$results         = tqb_table_name( 'results' );

$sqls = array();

/*Form variation table*/
$sqls[] = "CREATE TABLE IF NOT EXISTS {$form_variations} (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`quiz_id` INT NOT NULL ,
	`date_added` DATETIME NOT NULL ,
	`date_modified` DATETIME NOT NULL ,
	`page_id` INT NOT NULL ,
	`parent_id` INT NOT NULL DEFAULT '0' ,
	`post_status` VARCHAR(20) NOT NULL DEFAULT 'publish' ,
	`post_title` TEXT NOT NULL ,
	`is_control` TINYINT NOT NULL DEFAULT '0' ,
	`state_order` INT NOT NULL DEFAULT '0' ,
	`cache_impressions` INT NOT NULL DEFAULT '0' ,
	`cache_optins` INT NOT NULL DEFAULT '0' ,
	`cache_optins_conversions` INT NOT NULL DEFAULT '0' ,
	`cache_social_shares` INT NOT NULL DEFAULT '0' ,
	`cache_social_shares_conversions` INT NOT NULL DEFAULT '0' ,
	`tcb_fields` LONGTEXT NULL DEFAULT NULL ,
	`content` LONGTEXT NOT NULL , PRIMARY KEY (`id`)
	) $collate";

/*Test table*/
$sqls[] = "CREATE TABLE IF NOT EXISTS {$tests} (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`page_id` INT NOT NULL,
  	`date_added` DATETIME NOT NULL ,
   	`date_started` DATETIME NOT NULL ,
	`date_completed` DATETIME NOT NULL ,
 	`title` VARCHAR(255) NOT NULL ,
  	`notes` TINYTEXT NOT NULL ,
   	`conversion_goal` INT NULL ,
    `auto_win_enabled` INT NOT NULL ,
 	`auto_win_min_conversions` INT NOT NULL ,
  	`auto_win_min_duration` INT NOT NULL ,
   	`auto_win_chance_original` DOUBLE NOT NULL ,
    `status` INT NOT NULL , PRIMARY KEY (`id`)
    ) $collate";

/*Test items*/
$sqls[] = "CREATE TABLE IF NOT EXISTS {$tests_items} (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`test_id` INT NOT NULL ,
	`variation_id` INT NOT NULL ,
	`is_control` INT NOT NULL ,
	`is_winner` INT NOT NULL ,
	`impressions` INT NOT NULL ,
	`optins` INT NOT NULL ,
	`optins_conversions` INT NOT NULL ,
	`social_shares` INT NOT NULL ,
	`social_shares_conversions` INT NOT NULL ,
	`active` TINYINT NOT NULL DEFAULT '1' ,
	`stopped_date` DATETIME NOT NULL ,
	PRIMARY KEY (`id`)
	) $collate";

$sqls[] = "CREATE TABLE IF NOT EXISTS {$log_table}(
`id` INT( 11 ) AUTO_INCREMENT,
`date` DATETIME NULL,
`event_type` TINYINT( 2 ),
`variation_id` INT( 11 ) NULL,
`user_unique` VARCHAR(255) NOT NULL,
`page_id` INT( 11 ) NULL,
`optin` TINYINT NULL,
`social_share` TINYINT NULL,
`duplicate` TINYINT NULL,
PRIMARY KEY( `id` )
) $collate";

$sqls[] = "CREATE TABLE IF NOT EXISTS {$answers} (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id` INT UNSIGNED NOT NULL,
	`question_id` INT UNSIGNED DEFAULT NULL,
	`answer_id` INT UNSIGNED DEFAULT NULL,
	`quiz_id` INT UNSIGNED DEFAULT NULL,
	PRIMARY KEY (`id`)
	) $collate";

$sqls[] = "CREATE TABLE IF NOT EXISTS {$users} (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`random_identifier` VARCHAR(255) NOT NULL,
	`date_started` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`social_badge_link` VARCHAR(255) NULL,
	`email` VARCHAR(255) NULL,
	`ip_address` VARCHAR(255) NULL,
	`points` INT(10) NULL DEFAULT NULL,
	`quiz_id` TEXT NOT NULL,
	`completed_quiz` TINYINT UNSIGNED NULL,
	`ignore_user` TINYINT NULL,
	PRIMARY KEY (`id`)
	) $collate";

$sqls[] = "CREATE TABLE IF NOT EXISTS {$results} (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`quiz_id` INT UNSIGNED NOT NULL,
	`text` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`)
	) $collate";

foreach ( $sqls as $sql ) {
	if ( $wpdb->query( $sql ) === false ) {
		return false;
	}
}

return true;
