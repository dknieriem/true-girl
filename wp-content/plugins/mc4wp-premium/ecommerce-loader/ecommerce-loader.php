<?php

defined( 'ABSPATH' ) or exit;

// Require at least version 3.1 of MailChimp for WordPress (for Queue classes)
if( ! version_compare( MC4WP_VERSION, '3.1', '>=' ) ) {
    return;
}

// Always load v2 if we're on core 3.x
if( version_compare( MC4WP_VERSION, '4.0.5', '<' ) ) {
    require __DIR__ . '/../ecommerce2/ecommerce2.php';
    return;
}

$opts = mc4wp_get_options();

// if this option is set, it means someone was using v2 and didn't migrate away yet.
if( ! empty( $opts['ecommerce'] ) ) {
    require __DIR__ . '/../ecommerce2/ecommerce2.php';
    return;
}


// On core 4.x with no v2 data: good to go!
require __DIR__ . '/../ecommerce3/ecommerce3.php';