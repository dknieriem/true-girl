<?php

defined( 'ABSPATH' ) or exit;

global $wpdb;
$table = $wpdb->prefix . 'mc4wp_log';

$items = $wpdb->get_results( sprintf( 'SELECT ID, merge_fields FROM %s WHERE merge_fields IS NOT NULL AND ip_signup IS NULL', $table ) );
$values = array();

// create string of "1, '127.0.0.1'" format.
foreach( $items as $item ) {
    $data = json_decode( $item->merge_fields, ARRAY_A );
    if( ! empty( $data['OPTIN_IP'] ) ) {
        $values[] = "$item->ID, '{$data['OPTIN_IP']}'";
    }
}

if( ! empty( $values ) ) {
    // generate SQL
    $sql = 'INSERT INTO %s (ID, ip_signup) VALUES %s ON DUPLICATE KEY UPDATE ip_signup=VALUES(ip_signup)';
    $values = '(' . join( '),(', $values ) . ')';
    $sql = sprintf( $sql, $table, $values );

    $wpdb->query( $sql );
}
