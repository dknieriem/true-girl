<?php defined( 'ABSPATH' ) or exit;

$search_query = ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

/**
 * @var MC4WP_Forms_Table $table
 */
?>
<div id="mc4wp-admin" class="wrap">

	<p class="breadcrumbs">
		<span class="prefix"><?php echo __( 'You are here: ', 'mailchimp-for-wp' ); ?></span>
		<a href="<?php echo admin_url( 'admin.php?page=mailchimp-for-wp' ); ?>">MailChimp for WordPress</a> &rsaquo;
		<span class="current-crumb"><strong><?php _e( 'Forms', 'mailchimp-for-wp' ); ?></strong></span>
	</p>

	<h1 class="page-title"><?php _e( 'Forms', 'mailchimp-for-wp' ); ?>
		<a href="<?php echo mc4wp_get_add_form_url(); ?>" class="page-title-action">
			<span class="dashicons dashicons-plus-alt" style=""></span>
			<?php _e( 'Add new form', 'mailchimp-for-wp' ); ?>
		</a>

		<?php if ( $search_query )
			printf( ' <span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', $search_query );
		?>
	</h1>

	<?php // h2 for settings errors ?>
	<h2 style="display: none;"></h2>
	<?php settings_errors(); ?>

	<?php $table->views(); ?>

	<form method="get" action="<?php echo admin_url( 'admin.php' ); ?>">
		<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>" />
		<?php if( ! empty( $_GET['post_status'] ) ) { ?>
			<input type="hidden" name="post_status" value="<?php echo esc_attr( $_GET['post_status'] ); ?>" />
		<?php } ?>
		<?php $table->search_box( 'search', 'mc4wp-log-search' ); ?>
	</form>

	<form method="post">
		<?php $table->display(); ?>
	</form>
</div>