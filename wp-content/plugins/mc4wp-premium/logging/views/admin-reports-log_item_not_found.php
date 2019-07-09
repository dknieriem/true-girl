<?php
defined( 'ABSPATH' ) or exit;
?>

<h2 style="margin-top: 0;"><span><?php esc_html_e( 'Item not found', 'mailchimp-for-wp' ); ?></span></h2>

<p><?php printf( __( 'Sorry, no item with ID %d exists.', 'mailchimp-for-wp' ), intval( $_GET['id'] ) ); ?></p>

<p><a href="javascript:history.go(-1);">Go back</a>.</p>
