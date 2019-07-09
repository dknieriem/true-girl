<?php
defined( 'ABSPATH' ) or exit;
?>

<div id="mc4wp-admin" class="wrap ecommerce">

	<h1 class="page-title">
		<?php echo __( 'MailChimp for WordPress', 'mc4wp-ecommerce' ) . ': ' . __( 'E-Commerce', 'mc4wp-ecommerce' ); ?>
	</h1>

	<?php if( $connected_list ) { ?>

<?php
// show notice when e-commerce automations are still disabled
if( $settings['store']['is_syncing'] ) {
	echo '<div class="notice notice-warning"><p>'. sprintf( __( '<strong>Heads up!</strong> Your e-commerce automations are disabled. If you\'re done adding orders, please <a href="%s">re-enable them</a>.', 'mc4wp-ecommerce' ), add_query_arg( array( 'edit' => 'store' ) ) ).'</p></div>';
} ?>

		<form method="POST">
		<input type="hidden" name="_mc4wp_action" value="save_ecommerce_settings" />
		<?php wp_nonce_field( 'save_ecommerce_settings' ); ?>

		<table class="form-table">

			<tr valign="top">
				<th scope="row">
					<label><?php _e( 'Enable order tracking?', 'mc4wp-ecommerce' ); ?></label>
				</th>
				<td>
					<label class="choice-wrap"><input type="radio" name="mc4wp_ecommerce[enable_object_tracking]" value="1" <?php checked( $settings['enable_object_tracking'], 1 ); ?> />&rlm; <?php _e( 'Yes' ); ?></label>
					<label class="choice-wrap"><input type="radio" name="mc4wp_ecommerce[enable_object_tracking]" value="0" <?php checked( $settings['enable_object_tracking'], 0 ); ?> />&rlm; <?php _e( 'No' ); ?></label>
					<p class="help"><?php _e( 'This synchronizes all product, customer & order data with MailChimp.', 'mc4wp-ecommerce' ); ?></p>
				</td>
			</tr>

			<!-- Track all order statuses -->
			<?php $config = array( 'element' => 'mc4wp_ecommerce[enable_object_tracking]', 'value' => 1, 'hide' => false ); ?>
			<tr valign="top" data-showif="<?php echo esc_attr( json_encode( $config ) ); ?>">
				<th scope="row">
					<label><?php _e( 'Include all order statuses?', 'mc4wp-ecommerce' ); ?></label>
				</th>
				<td>
					<label class="choice-wrap"><input type="radio" name="mc4wp_ecommerce[include_all_order_statuses]" value="1" <?php checked( $settings['include_all_order_statuses'], 1 ); ?> />&rlm; <?php _e( 'Yes' ); ?></label>
					<label class="choice-wrap"><input type="radio" name="mc4wp_ecommerce[include_all_order_statuses]" value="0" <?php checked( $settings['include_all_order_statuses'], 0 ); ?> />&rlm; <?php _e( 'No' ); ?></label>
					<p class="help"><?php 
	_e( 'By default, only completed orders are sent to MailChimp. Select "Yes" to send refunded, cancelled and pending orders too. This is only needed if you use the Order Notifications automation.', 'mc4wp-ecommerce' ); ?>
					</p>
				</td>
			</tr>

			<?php $config = array( 'element' => 'mc4wp_ecommerce[enable_object_tracking]', 'value' => 1, 'hide' => false ); ?>
			<tr valign="top" data-showif="<?php echo esc_attr( json_encode( $config ) ); ?>">
				<th scope="row">
					<label><?php _e( 'Enable cart tracking?', 'mc4wp-ecommerce' ); ?></label>
				</th>
				<td>
					<label class="choice-wrap"><input type="radio" name="mc4wp_ecommerce[enable_cart_tracking]" value="1" <?php checked( $settings['enable_cart_tracking'], 1 ); ?> />&rlm; <?php _e( 'Yes' ); ?></label>
					<label class="choice-wrap"><input type="radio" name="mc4wp_ecommerce[enable_cart_tracking]" value="0" <?php checked( $settings['enable_cart_tracking'], 0 ); ?> />&rlm; <?php _e( 'No' ); ?></label>
					<p class="help"><?php printf( __( 'This allows you to <a href="%s">setup an abandoned cart recovery workflow in MailChimp</a>.', 'mc4wp-ecommerce' ), 'https://kb.mc4wp.com/enabling-abandoned-cart-recovery/#utm_source=wp-plugin&utm_medium=mc4wp-premium&utm_campaign=ecommerce-settings-page' ); ?></p>
				</td>
			</tr>
	
			<?php $config = array( 'element' => 'mc4wp_ecommerce[enable_object_tracking]', 'value' => 1, 'hide' => false ); ?>
			<tr valign="top" data-showif="<?php echo esc_attr( json_encode( $config ) ); ?>">
				<th scope="row">
					<label><?php _e( 'Load MC.js?', 'mc4wp-ecommerce' ); ?></label>
				</th>
				<td>
					<label class="choice-wrap"><input type="radio" name="mc4wp_ecommerce[load_mcjs_script]" value="1" <?php checked( $settings['load_mcjs_script'], 1 ); ?> />&rlm; <?php _e( 'Yes' ); ?></label>
					<label class="choice-wrap"><input type="radio" name="mc4wp_ecommerce[load_mcjs_script]" value="0" <?php checked( $settings['load_mcjs_script'], 0 ); ?> />&rlm; <?php _e( 'No' ); ?></label>
					<p class="help"><?php _e( 'Enabling this loads a JavaScript file from MailChimp that allows for product retargeting & pop-ups.', 'mc4wp-ecommerce' ); ?></p>
				</td>
			</tr>

		</table>

		<?php submit_button(); ?>
	</form>

	<div style="margin: 40px 0;"></div>

	<div>
		<h2><?php _e( 'Manage MailChimp data', 'mc4wp-ecommerce' ); ?></h2>


		<p>
			<?php printf( __( 'Your store is currently connected to <strong>%s</strong> in MailChimp as <strong>%s</strong>. (<a href="%s">edit</a>)', 'mc4wp-ecommerce' ), sprintf( '<a href="%s">%s</a>', $connected_list->get_web_url(), esc_html( $connected_list->name ) ), esc_html( $settings['store']['name'] ), add_query_arg( array( 'edit' => 'store' ) ) ); ?>
		</p>

<?php
// show last updated timestamp
if( ! empty( $settings['last_updated'] ) ) {
	$formatted_date = date( get_option('date_format') . ' ' . get_option('time_format'), $settings['last_updated'] );
	printf('<p><strong>' . __( 'Last updated:', 'mc4wp-ecommerce' ) . '</strong> %s</p>', $formatted_date );
}

if( $queue ) {
	$next_run = wp_next_scheduled( 'mc4wp_ecommerce_process_queue' );
	$count = count( $queue->all() );
	echo '<div class="well margin">';
	echo '<h3>' . __( 'Queued background jobs', 'mc4wp-ecommerce' ) . '</h3>';
	echo '<p>';

	echo sprintf( __( '<strong>%d</strong> background jobs waiting to be processed.', 'mc4wp-ecommerce' ), $count );

	if( $count > 0 ) {
		echo ' ' . sprintf( __( 'Pending jobs will be processed on <strong>%s</strong> at <strong>%s</strong>.'), date( get_option( 'date_format' ), $next_run ), date( get_option( 'time_format' ), $next_run ) );
	}

	echo '</p>';

	if( $count > 0 ) {
		echo '<div id="queue-processor"></div>';
	}

	echo '<p class="help muted">' . sprintf( __( 'Please keep an eye on the <a href="%s">debug log</a> for any errors.', 'mc4wp-ecommerce' ), admin_url( 'admin.php?page=mailchimp-for-wp-other' ) ) . '</p>';

	echo '</div>';
} ?>

		<!-- products wizard -->
		<div class="well margin">
			<?php require __DIR__ . '/parts/config-products.php'; ?>
		</div>
		<!-- / End products wizard -->

		<!-- orders wizard -->
		<div class="well margin">
			<?php require __DIR__ . '/parts/config-orders.php'; ?>
		</div>
		<!-- / End orders wizard -->

		<div class="margin-big">
			<h3><?php _e( 'Reset', 'mc4wp-ecommerce' ); ?></h3>
			<p><?php _e( 'The following button allows you to reset all of your e-commerce data.', 'mc4wp-ecommerce' ); ?></p>
			<form method="POST" data-confirm="<?php esc_attr_e( 'Are you sure you want to reset all of your e-commerce data?', 'malchimp-for-wp' ); ?>">
				<input type="hidden" name="_mc4wp_action" value="ecommerce_reset">
				<?php wp_nonce_field( 'mc4wp_ecommerce_reset' ); ?>
				<p>
					<input type="submit" value="<?php esc_attr_e( 'Reset Data', 'mc4wp-ecommerce' ); ?>" class="button button-secondary" />
				</p>
			</form>
		</div>

	</div> <!-- / End store data overview -->
	<?php } else { ?>
		<div class="margin-big">
			<h3><?php _e( 'Connect your store to MailChimp', 'mc4wp-ecommerce' ); ?></h3>
			<p><?php printf( __( 'To use the e-commerce features, please start by <a href="%s">connecting your store to MailChimp</a>.', 'mc4wp-ecommerce' ), add_query_arg( array( 'wizard' => 1 ) ) ); ?></p>
		</div>
	<?php } ?>

	<div style="margin: 40px 0;"></div>

	<!-- help link -->
	<p>
		<?php printf( __( 'For more information on <a href="%s">using MailChimp e-commerce</a>, please refer to our knowledge base.', 'mc4wp-ecommerce' ), 'https://mc4wp.com/kb/what-is-ecommerce360/' ); ?>
	</p>
	<!-- / help link -->

	<div style="margin: 40px 0;"></div>

</div><!-- / End page wrap -->
