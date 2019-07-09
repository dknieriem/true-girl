<?php
// set correct file permission for bundle file
$upload = wp_upload_dir();
$filename = $upload['basedir'] . '/mc4wp-stylesheets/bundle.css';
@chmod( $filename, 0755 );