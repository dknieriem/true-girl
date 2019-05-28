<div class="tvo-form-config">
	<input type="hidden" class="tags" value='<?php echo json_encode( $config['tags'] ); ?>'>
	<input type="hidden" class="shortcode_id" value="<?php echo $config['id']; ?>">
	<input type="hidden" class="on_success" value="<?php echo $config['on_success_option']; ?>">
	<?php if ( $config['on_success_option'] == 'redirect' ) : ?><input type="hidden" class="on_redirect" value="<?php echo $config['on_success']; ?>"> <?php endif; ?>
</div>
<div class="tvo-form-error" style="color: red;"></div>
<?php
$captcha_api = Thrive_Dash_List_Manager::credentials( 'recaptcha' );
$site_key = isset( $captcha_api['site_key'] ) ? $captcha_api['site_key'] : "";

if ( ! $is_editor ): ?>
	<script>
		var $recaptcha = jQuery( '#tvo-reCaptcha' );
		if ( $recaptcha.length ) {
			function reCaptchaLoaded() {
				$recaptcha.filter( ':not(.tvo-recaptcha-rendered)' ).each( function () {
					$this = jQuery( this );
					$this.addClass( 'tvo-recaptcha-rendered' );
				} );
			}

			function checkCaptchaLoaded() {
				if ( typeof grecaptcha === 'undefined' ) {
					setTimeout( checkCaptchaLoaded, 100 );
				} else {
					reCaptchaLoaded();
				}
			}

			var loading_script = false;
			if ( ! window.tve_gapi_loaded ) {
				jQuery.getScript( 'https://www.google.com/recaptcha/api.js', checkCaptchaLoaded );
				loading_script = true;
				window.tve_gapi_loaded = true;
			} else {

				$recaptcha.empty().css( 'height', '96px' );
				if ( ! $recaptcha.hasClass( 'tvo-recaptcha-rendered' ) ) {
					grecaptcha.render( 'tvo-reCaptcha', {
						'sitekey': '<?php echo $site_key ?>',
						'theme': 'light'
					} );
				}
			}

			if ( ! loading_script ) {
				checkCaptchaLoaded();
			}
		}
	</script>
<?php endif; ?>
<script type="text/javascript">
	/* apply custom color class */
	var $shortcode = jQuery( '#<?php echo $unique_id; ?>' );
	<?php if ( ! empty( $config['color_class'] ) ) : ?>
	var new_class = '<?php echo $config['color_class']; ?>';
	$shortcode.attr( 'class', $shortcode.attr( 'class' ).replace( /tve_(\w+)/i, new_class ) );
	<?php endif;?>
</script>

<?php if ( ! empty( $google_client_id ) && ! $is_editor ) : ?>
	<script src="https://apis.google.com/js/api:client.js"></script>

	<script>
		if ( typeof gapi !== 'undefined' ) {
			gapi.load( 'auth2', function () {
				// Retrieve the singleton for the GoogleAuth library and set up the client.
				if ( ! window.tvo_gapi_loaded ) {
					auth2 = gapi.auth2.init( {
						client_id: '<?php echo $google_client_id; ?>',
						cookiepolicy: 'single_host_origin'
					} );
					window.tvo_gapi_loaded = true;
				}
				jQuery( '.tvo-google-button' ).each( function () {
					var $picture = jQuery( this ).parent().siblings( '.tvo-picture-wrapper' );

					auth2.attachClickHandler( this, {},
						function ( googleUser ) {
							var img = googleUser.getBasicProfile().getImageUrl() + '?sz=512';

							$picture.css( 'background-image', 'url(' + img + ')' );
							$picture.siblings( '.tvo-remove-image' ).show();
						}, function () {
							$picture.css( 'background-image', 'url(' + $picture.data( 'default' ) + ')' );
						} );
				} ).click( function () {
					var $picture = jQuery( this ).parent().siblings( '.tvo-picture-wrapper' );
					$picture.css( 'background-image', 'url("<?php echo TVO_URL; ?>templates/css/images/loading.gif")' );
					setTimeout( function () {
						if ( $picture.css( 'background-image' ).search( 'loading.gif' ) !== - 1 ) {
							$picture.css( 'background-image', 'url(' + $picture.data( 'default' ) + ')' );
						}
					}, 10000 );
				} )
			} );
		}
	</script>
<?php endif; ?>

<?php if ( ! empty( $facebook_app_id ) && ! $is_editor ) : ?>
	<script>
		if ( typeof FB === 'undefined' ) {
			window.fbAsyncInit = function () {
				FB.init( {
					appId: '<?php echo $facebook_app_id; ?>',
					cookie: true,
					xfbml: true,
					version: 'v2.5'
				} );
			};

			// Load the SDK asynchronously
			(function ( d, s, id ) {
				var js, fjs = d.getElementsByTagName( s )[0];
				if ( d.getElementById( id ) ) {
					return;
				}
				js = d.createElement( s );
				js.id = id;
				js.src = "//connect.facebook.net/en_US/sdk.js";
				fjs.parentNode.insertBefore( js, fjs );
			}( document, 'script', 'facebook-jssdk' ));
		}

		$shortcode.find( '.tvo-fb-button' ).click( function () {
			var $picture = jQuery( this ).parent().siblings( '.tvo-picture-wrapper' );

			if ( typeof FB !== 'undefined' ) {

				$picture.css( 'background-image', 'url("<?php echo TVO_URL; ?>templates/css/images/loading.gif")' );

				FB.getLoginStatus( function ( response ) {
					if ( response.status === 'connected' ) {
						FB.api( '/me', function ( profile ) {
							var img = '//graph.facebook.com/' + profile.id + '/picture?type=large';
							$picture.css( 'background-image', 'url(' + img + ')' );
							$picture.siblings( '.tvo-remove-image' ).show();
						} );
					} else {
						FB.login( function ( resp ) {
							if ( resp.authResponse ) {
								FB.api( '/me', function ( resp ) {
									var img = '//graph.facebook.com/' + resp.id + '/picture?type=large';
									$picture.css( 'background-image', 'url(' + img + ')' );
									$picture.siblings( '.tvo-remove-image' ).show();
								} );
							} else {
								$picture.css( 'background-image', 'url(' + $picture.data( 'default' ) + ')' );
							}
						} );
					}
				} );
			}
		} );
	</script>
<?php endif; ?>
