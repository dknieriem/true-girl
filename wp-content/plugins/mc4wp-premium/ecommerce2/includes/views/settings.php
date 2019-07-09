<?php

defined( 'ABSPATH' ) or exit;

/** @var array $opts */
?>
<div class="medium-margin">
	<h3><?php _e( 'eCommerce360', 'mailchimp-for-wp' ); ?></h3>
	<p>
		<label>
			<?php /* hidden input field to send `0` when checkbox is not checked */ ?>
			<input type="hidden" name="mc4wp[ecommerce]" value="0" />
			<input type="checkbox" name="mc4wp[ecommerce]" value="1" <?php checked( $opts['ecommerce'], 1 ); ?>>
			<?php echo sprintf( __( 'Enable <a href="%s">eCommerce360 tracking</a>.', 'mailchimp-for-wp' ), 'https://mc4wp.com/kb/what-is-ecommerce360/' ); ?>
			<?php if( $opts['ecommerce'] ) {
				echo sprintf( __( 'Looking to <a href="%s">add all past orders to MailChimp</a>?', 'mailchimp-for-wp' ), admin_url( 'admin.php?page=mailchimp-for-wp-ecommerce' ) );
			} ?>
		</label>
	</p>


</div>
