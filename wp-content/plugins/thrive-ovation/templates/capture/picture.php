<div class="tvo-picture-wrapper" style="background-image: url(<?php echo $default_placeholder; ?>)" data-default="<?php echo $default_placeholder; ?>"></div>
<div class="tvo-social-picture">
	<?php echo empty( $facebook_app_id ) ? '' : '<div class="tvo-fb-button"></div>'; ?>
	<?php echo empty( $google_client_id ) ? '' : '<div class="tvo-google-button"></div>'; ?>
</div>
<div class="tvo-remove-image" style="display: none;">
	<?php echo __( 'Remove Picture', TVO_TRANSLATE_DOMAIN ); ?>
</div>
