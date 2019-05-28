<p>
	<label for="title">
		Title (optional):
		<br />
		<input
			type="text" tabindex="1" 
			name="<?php echo $this->get_field_name('title'); ?>" 
			id="<?php echo $this->get_field_id('title'); ?>"
			value="<?php echo esc_attr($instance['title']); ?>"
		/>
	</label>
</p>
<p>
	<label for="title">
		Artist Name:
		<br />
		<input
			type="text" tabindex="1" 
			name="<?php echo $this->get_field_name('artist'); ?>" 
			id="<?php echo $this->get_field_id('artist'); ?>"
			value="<?php echo esc_attr($instance['artist']); ?>"
		/>
	</label>
</p>
<p>
	<label for="title">
		Display Limit:
		<br />
		<input
			type="text" tabindex="1" 
			name="<?php echo $this->get_field_name('display_limit'); ?>" 
			id="<?php echo $this->get_field_id('display_limit'); ?>"
			value="<?php echo esc_attr($instance['display_limit']); ?>"
			size="4"
		/>
	</label>
	<br />
	<small>Leave blank to show all events.</small>
</p>