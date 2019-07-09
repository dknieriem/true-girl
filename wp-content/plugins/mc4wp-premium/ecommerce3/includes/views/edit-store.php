<?php
defined( 'ABSPATH' ) or exit;

$disable_list_select = true;
?>

<div id="mc4wp-admin" class="wrap ecommerce">

	<h1 class="page-title">
		<?php echo __( 'MailChimp for WordPress', 'mc4wp-ecommerce' ) . ': ' . __( 'E-Commerce', 'mc4wp-ecommerce' ); ?>
	</h1>

	<?php require __DIR__ . '/parts/config-store.php'; ?>

	<p>
		<a href="<?php echo admin_url( 'admin.php?page=mailchimp-for-wp-ecommerce' ); ?>">&lsaquo; Go back</a>
	</p>

	<div style="margin: 40px 0;"></div>

	<!-- help link -->
	<p>
		<?php printf( __( 'For more information on <a href="%s">using MailChimp e-commerce</a>, please refer to our knowledge base.', 'mc4wp-ecommerce' ), 'https://mc4wp.com/kb/what-is-ecommerce360/#utm_source=wp-plugin&utm_medium=mc4wp-premium&utm_campaign=ecommerce-settings-page' ); ?>
	</p>
	<!-- / help link -->

	<div style="margin: 40px 0;"></div>

</div><!-- / End page wrap -->
