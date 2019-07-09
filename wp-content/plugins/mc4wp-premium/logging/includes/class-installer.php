<?php

/**
 * Class MC4WP_Logging_Installer
 *
 * @ignore
 */
class MC4WP_Logging_Installer {

    public static function run() {
        /** @var WPDB $wpdb */
        global $wpdb;

        $table_name = $wpdb->prefix . 'mc4wp_log';
        $charset_collate = $wpdb->get_charset_collate();

        // create TABLE
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
        `ID` BIGINT(20) NOT NULL AUTO_INCREMENT,
        `email_address` VARCHAR(255) NOT NULL,
        `list_id` VARCHAR(255) NOT NULL,
        `type` VARCHAR(255) NOT NULL,
        `merge_fields` TEXT NULL,
        `interests` TEXT NULL,
        `status` VARCHAR(60) NULL,
        `email_type` VARCHAR(4) NULL,
        `ip_signup` VARCHAR(255) NULL,
        `language` VARCHAR(60) NULL,
        `vip` TINYINT(1) NULL,
        `related_object_ID` BIGINT(20) NULL,
        `url` VARCHAR(255) NULL,
        `datetime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `success` TINYINT(1) DEFAULT 1,
        PRIMARY KEY  (ID)
		) $charset_collate";

        $wpdb->query( $sql );
    }

}
