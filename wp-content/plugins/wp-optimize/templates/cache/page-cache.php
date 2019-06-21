<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>

<div class="wpo-fieldgroup wpo-first-child cache-options">
	<div class="switch-container">
		<label class="switch">
			<input name="enable_page_caching" id="enable_page_caching" class="cache-settings" type="checkbox" value="true" <?php checked($wpo_cache_options['enable_page_caching']); ?>>			
			<span class="slider round"></span>
		</label>
		<label for="enable_page_caching">
			<?php _e('Enable page caching', 'wp-optimize'); ?>
		</label>
	</div>
	<p class="wpo-text__dim">
		<?php echo __("This is all that's needed for caching to work.", 'wp-optimize').' '.__('WP-Optimize will automatically detect and configure itself optimally for your site.', 'wp-optimize').' '.__('You can tweak the the settings below and in the advanced settings tab, if needed.', 'wp-optimize'); ?>
	</p>

	<?php if (!empty($active_cache_plugins)) { ?>
	<p class="wpo-error">
		<?php
			printf(__('It looks like you already have an active caching plugin (%s) installed. This might create unexpected results.', 'wp-optimize'), implode(', ', $active_cache_plugins));
		?>
	</p>
	<?php } ?>

</div>


<h3 class="purge-cache" <?php echo $display; ?>> <?php _e('Purge the cache', 'wp-optimize'); ?></h3>
<div class="wpo-fieldgroup cache-options purge-cache" <?php echo $display; ?> >
	<p class="wpo-button-wrap">
		<input id="wp-optimize-purge-cache" class="button button-primary" type="submit" value="<?php _e('Purge cache', 'wp-optimize'); ?>">
		<img class="wpo_spinner" src="<?php echo esc_attr(admin_url('images/spinner-2x.gif')); ?>" alt="...">
		<span class="save-done dashicons dashicons-yes display-none"></span>
	</p>
	<small class="wpo-text__dim">
		<?php _e('Deletes the entire cache contents but keeps page cache enabled.', 'wp-optimize'); ?>
	</small>
</div>

<h3><?php _e('Cache settings', 'wp-optimize'); ?></h3>

<div class="wpo-fieldgroup cache-options">

	<div class="wpo-fieldgroup__subgroup">
		<label for="enable_mobile_caching">
			<input name="enable_mobile_caching" id="enable_mobile_caching" class="cache-settings" type="checkbox" value="true" <?php checked($wpo_cache_options['enable_mobile_caching'], 1); ?>>
			<?php _e('Generate separate files for mobile devices', 'wp-optimize'); ?>
		</label>
		<span tabindex="0" data-tooltip="<?php _e('Useful if your website has mobile specific content.', 'wp-optimize');?>"><span class="dashicons dashicons-editor-help"></span> </span>
	</div>

	<div class="wpo-fieldgroup__subgroup">
		<label for="enable_user_caching">
			<input name="enable_user_caching" id="enable_user_caching" class="cache-settings" type="checkbox" value="true" <?php checked($wpo_cache_options['enable_user_caching']); ?>>
			<?php _e('Serve cached pages to logged in users', 'wp-optimize'); ?>
		</label>
		<span tabindex="0" data-tooltip="<?php _e('Enable this option when you do not have user-specific or restricted content on your website.', 'wp-optimize');?>"><span class="dashicons dashicons-editor-help"></span> </span>
	</div>

	<div class="wpo-fieldgroup__subgroup">
		<label for="page_cache_length_value"><?php _e('Cache lifespan', 'wp-optimize'); ?></label>
		<p>
			<input name="page_cache_length_value" id="page_cache_length_value" class="cache-settings" type="text" value="<?php echo esc_attr($wpo_cache_options['page_cache_length_value']); ?>">
			<select name="page_cache_length_unit" id="page_cache_length_unit" class="cache-settings">
				<option value="hours" <?php selected('hours', $wpo_cache_options['page_cache_length_unit']); ?>><?php _e('Hours', 'wp-optimize'); ?></option>
				<option value="days" <?php selected('days', $wpo_cache_options['page_cache_length_unit']); ?>><?php _e('Days', 'wp-optimize'); ?></option>
				<option value="months" <?php selected('months', $wpo_cache_options['page_cache_length_unit']); ?>><?php _e('Months', 'wp-optimize'); ?></option>
			</select>
		</p>
		<small class="wpo-text__dim">
			<?php _e('Time after which a new cached version will be generated (0 = unlimited)', 'wp-optimize'); ?>
		</small>
	</div>

</div>

<input id="wp-optimize-save-cache-settings" class="button button-primary" type="submit" name="wp-optimize-save-cache-settings" value="<?php _e('Save changes', 'wp-optimize'); ?>">

<img class="wpo_spinner" src="<?php echo esc_attr(admin_url('images/spinner-2x.gif')); ?>" alt="....">

<span class="save-done dashicons dashicons-yes display-none"></span>

