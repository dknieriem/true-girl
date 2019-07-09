<?php defined( 'ABSPATH' ) or exit; ?>

<div class="wrap" id="mc4wp-admin">
	<h1 class="page-title">eCommerce360: <?php _e( 'Record past orders', 'mc4wp-ecommerce' ); ?></h1>

	<?php if( $untracked_order_count > 0 ) { ?>
		<p><?php printf( __( 'You have %s orders which are not yet recorded in MailChimp. Do you want to send those orders to MailChimp now?', 'mc4wp-ecommerce' ), '<strong>' . $untracked_order_count . '</strong>' ); ?></p>

		<form method="post" action="" id="add-untracked-orders-form">
			<p>
				<input type="submit" value="<?php esc_attr_e( 'Record Orders', 'mc4wp-ecommerce' ); ?>" class="button" />
			</p>

			<input type="hidden" name="_mc4wp_action" value="ecommerce_add_untracked_orders" />
			<input type="hidden" name="offset" value="0">
			<input type="hidden" name="limit" value="100" />
		</form>

		<div id="add-untracked-orders-progress"></div>

		<p class="help">
			<?php printf( __( 'This can take a while. If you have command line access, consider <a href="%s">using WP CLI to record all orders</a> as this greatly speeds up the process.', 'mailchimp-for-wp' ), 'https://mc4wp.com/kb/ecommerce360-wp-cli-commands/' ); ?>
		</p>
	<?php } else {
		echo '<p>' . __( 'You have no untracked orders. Hurray!', 'mc4wp-ecommerce' ) . '</p>';
	} ?>

	<p>
		<a href="<?php echo admin_url( 'admin.php?page=mailchimp-for-wp-other' ); ?>"><?php _e( 'Go back' ); ?></a>
	</p>
</div>