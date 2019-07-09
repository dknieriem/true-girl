<?php
defined( 'ABSPATH' ) or exit;

$begin_year = 2012;
$current_year = date('Y');
$current_month = date('n');
?>
<div class="metabox-holder">
<div class="postbox">
	<h3 style="margin-top: 0;"><span><?php _e( 'Export sign-up attempts', 'mailchimp-for-wp' ); ?></span></h3>
	<div class="inside">
		<form method="POST">
			<input type="hidden" name="_mc4wp_action" value="log_export" />
			<p>
				<?php _e( 'Use the following button to export your entire log to a CSV file.', 'mailchimp-for-wp' ); ?>
			</p>
			<p>
				<label for="start_year" class="screen-reader-text"><?php _e( 'Start year', 'mailchimp-for-wp' ); ?></label>
				<select name="start_year" id="start_year">
					<option disabled><?php _e( 'Year' ); ?></option>
					<?php for( $i = $begin_year; $i <= $current_year; $i++) { ?>
						<option><?php echo $i; ?></option>
					<?php } ?>
				</select>

				<label for="start_month" class="screen-reader-text"><?php _e( 'Start month', 'mailchimp-for-wp' ); ?></label>
				<select name="start_month" id="start_month">
					<option disabled><?php _e( 'Month' ); ?></option>
					<?php foreach( range( 1, 12 ) as $month_number) { ?>
						<option value="<?php echo $month_number; ?>"><?php echo $month_number; ?></option>
					<?php }?>
				</select>
				<?php _e( 'to', 'mailchimp-for-wp' ); ?>

				<label for="end_year" class="screen-reader-text"><?php _e( 'End year', 'mailchimp-for-wp' ); ?></label>
				<select name="end_year" id="end_year">
					<option disabled><?php _e( 'Year' ); ?></option>
					<?php for( $i = $begin_year; $i <= $current_year; $i++) { ?>
						<option <?php selected( $i, $current_year ); ?>><?php echo $i; ?></option>
					<?php
					}
					?>
				</select>

				<label for="end_month" class="screen-reader-text"><?php _e( 'End month', 'mailchimp-for-wp' ); ?></label>
				<select name="end_month" id="end_month">
					<option disabled><?php _e( 'Month' ); ?></option>
					<?php foreach( range( 1, 12 ) as $month_number) { ?>
						<option value="<?php echo $month_number; ?>" <?php selected( $month_number, $current_month ); ?>><?php echo $month_number; ?></option>
					<?php }?>
				</select>
			</p>

			<p>
				<input type="submit" class="button" value="<?php esc_attr_e( 'Export to CSV', 'mailchimp-for-wp' ); ?>" />
			</p>
		</form>
	</div><!-- .inside -->
</div>
</div>