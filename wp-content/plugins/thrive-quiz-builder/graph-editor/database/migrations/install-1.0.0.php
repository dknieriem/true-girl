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

$questions = tge_table_name( 'questions' );
$answers = tge_table_name( 'answers' );

$sqls = array();

$sqls[] = "CREATE TABLE IF NOT EXISTS {$questions} (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`quiz_id` INT UNSIGNED NOT NULL,
	`start` TINYINT(4) UNSIGNED NULL,
	`q_type` TINYINT(4) NOT NULL,
	`text` TEXT NOT NULL,
	`image` TEXT NOT NULL DEFAULT '',
	`description` TEXT DEFAULT NULL,
	`next_question_id` INT UNSIGNED DEFAULT NULL,
	`previous_question_id` INT UNSIGNED NULL,
	`position` VARCHAR(255) NULL,
	PRIMARY KEY (`id`),
	INDEX `quiz_id_index` (`quiz_id`)
	) $collate";

$sqls[] = "CREATE TABLE IF NOT EXISTS {$answers} (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`question_id` INT UNSIGNED NOT NULL,
	`next_question_id` INT UNSIGNED DEFAULT NULL,
	`quiz_id` INT UNSIGNED NOT NULL,
	`order` TINYINT UNSIGNED NULL,
	`text` TEXT NOT NULL,
	`image` TEXT NULL,
	`points` INT DEFAULT 1,
	`result_id` INT NULL,
	PRIMARY KEY (`id`),
	INDEX `question_id_index` (`question_id`)
	) $collate";

foreach ( $sqls as $sql ) {
	if ( $wpdb->query( $sql ) === false ) {
		return false;
	}
}

return true;
