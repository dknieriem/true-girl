<?php defined( 'ABSPATH' ) or exit;

/**
 * @var MC4WP_Log_Table $table
 */
?>

<?php $table->views(); ?>
<form method="get" action="<?php echo admin_url( 'admin.php' ); ?>">
    <input type="hidden" name="page" value="mailchimp-for-wp-reports" />
    <input type="hidden" name="tab" value="log" />
	<?php $table->search_box( 'search', 'mc4wp-log-search' ); ?>
	<?php $table->display(); ?>
</form>