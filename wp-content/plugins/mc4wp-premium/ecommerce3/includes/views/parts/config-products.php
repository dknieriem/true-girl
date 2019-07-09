<h3>
    <?php _e( 'Products', 'mc4wp-ecommerce' ); ?>
    <?php printf( '<span class="mc4wp-status-label">%d/%d</span>', $product_count->tracked, $product_count->all ); ?>
</h3>

<p>
    <?php _e( 'Your products have to be synchronized to MailChimp before we can proceed to tracking your orders.', 'mc4wp-ecommerce' ); ?>
</p>

<div id="mc4wp-ecommerce-products-progress-bar"></div>

<noscript><?php esc_html_e( 'Please enable JavaScript to use this feature.', 'mc4wp-ecommerce' ); ?></noscript>
<form method="POST" id="mc4wp-ecommerce-products-wizard">
    <p><input type="submit" class="button" value="Synchronize" /></p>
</form>