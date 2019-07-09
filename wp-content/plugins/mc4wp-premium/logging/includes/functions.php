<?php

/**
 * @param $datetime
 * @param string $format
 * @return false|string
 */
function mc4wp_logging_gmt_date_format( $datetime, $format = '' ) {

    if( $format === '' ) {
        $format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
    }

    // add or subtract GMT offset to given mysql time
    $local_datetime = strtotime( $datetime ) + ( get_option( 'gmt_offset') * HOUR_IN_SECONDS );

    return date( $format, $local_datetime );
}

/**
 * Schedule the purge logging events with WP Cron.
 */
function _mc4wp_logging_schedule_purge_event() {
    $expected_next = time() + ( 60 * 60 * 24 );
    $event_name = 'mc4wp_logging_purge_old_items';
    $actual_next = wp_next_scheduled( $event_name );

    if( ! $actual_next || $actual_next > $expected_next ) {
        wp_schedule_event( $expected_next, 'daily', $event_name );
    }
}
