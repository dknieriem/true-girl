<?php
defined( 'ABSPATH' ) or exit;

/** @var WPDB $wpdb */
global $wpdb;

$table_name = $wpdb->prefix . 'mc4wp_log';
$charset_collate = $wpdb->get_charset_collate();

// Create table if it does not exist
$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
        ID BIGINT(20) NOT NULL AUTO_INCREMENT,
        email VARCHAR(255) NOT NULL,
        list_ids VARCHAR(255) NOT NULL,
        type VARCHAR(255) NOT NULL,
        success TINYINT(1) DEFAULT 1,
		data TEXT NULL,
        related_object_ID BIGINT(20) NULL,
        url VARCHAR(255) DEFAULT '',
        datetime timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (ID)
		) $charset_collate";

$wpdb->query( $sql );