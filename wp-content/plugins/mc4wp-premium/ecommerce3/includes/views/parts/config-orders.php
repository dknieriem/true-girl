<h3>
    <?php _e( 'Orders', 'mc4wp-ecommerce' ); ?>
    <?php printf( '<span class="mc4wp-status-label">%d/%d</span>', $order_count->tracked, $order_count->all ); ?>
</h3>

<p>
    <?php _e( 'Adding your orders to MailChimp will allow you to see purchases made by your list subscribers.', 'mc4wp-ecommerce' ); ?>
</p>

<div id="mc4wp-ecommerce-orders-progress-bar"></div>

<noscript><?php esc_html_e( 'Please enable JavaScript to use this feature.', 'mc4wp-ecommerce' ); ?></noscript>
<form method="POST" id="mc4wp-ecommerce-orders-wizard">
    <p><input type="submit" class="button" value="Synchronize" /></p>
</form>