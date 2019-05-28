<?php
$captcha_api = Thrive_Dash_List_Manager::credentials( 'recaptcha' );
if ( ! empty( $captcha_api ) ) :
	?>
	<div id="tvo-reCaptcha">
		<div class="g-recaptcha" data-sitekey="<?php echo $captcha_api['site_key']; ?>"></div>
		<br/>
	</div>
<?php endif; ?>
