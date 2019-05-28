<?php $config = ( empty( $_POST['config'] ) ? array() : $_POST['config'] ); ?>
<?php $config = array_merge( tvo_get_default_shortcode_config( 'capture' ), $config ) ?>

<div id="tvo_capture_form_settings" class="tvo-frontend-modal">
	<h4>
		<?php echo __( 'Form Settings', TVO_TRANSLATE_DOMAIN ) ?>
	</h4>
	<hr class="tve_lightbox_line">

	<table>
		<thead>
		<tr>
			<th>
				<?php echo __( 'Display', TVO_TRANSLATE_DOMAIN ) ?>
			</th>
			<th>
				<?php echo __( 'Field', TVO_TRANSLATE_DOMAIN ) ?>
			</th>
			<th>
				<?php echo __( 'Label', TVO_TRANSLATE_DOMAIN ) ?>
			</th>
			<th>
				<?php echo __( 'Required', TVO_TRANSLATE_DOMAIN ) ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td>
				<?php echo __( 'Always', TVO_TRANSLATE_DOMAIN ) ?>
			</td>
			<td>
				<?php echo __( 'Name', TVO_TRANSLATE_DOMAIN ) ?>
			</td>
			<td>
				<input class="tvo_config_field tve_lightbox_input" type="text" name="name_label"
				       value="<?php echo empty( $config['name_label'] ) ? '' : stripslashes( $config['name_label'] ); ?>">
			</td>
			<td style="text-align: right">
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="name_required"
					       id="name_required" <?php checked( $config['name_required'], 1, true ); ?>>
					<label for="name_required"></label>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __( 'Always', TVO_TRANSLATE_DOMAIN ) ?>
			</td>
			<td>
				<?php echo __( 'Email', TVO_TRANSLATE_DOMAIN ) ?>
			</td>
			<td>
				<input class="tvo_config_field tve_lightbox_input" type="text" name="email_label"
				       value="<?php echo empty( $config['email_label'] ) ? '' : stripslashes( $config['email_label'] ); ?>">
			</td>
			<td style="text-align: right">
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="email_required"
					       id="email_required" <?php checked( $config['email_required'], 1, true ); ?>>
					<label for="email_required"></label>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="role_display"
					       id="role_display" <?php checked( $config['role_display'], 1, true ); ?>>
					<label for="role_display"></label>
				</div>
			</td>
			<td>
				<?php echo __( 'Role/Description', TVO_TRANSLATE_DOMAIN ) ?>
			</td>
			<td>
				<input class="tvo_config_field tve_lightbox_input" type="text" name="role_label"
				       value="<?php echo empty( $config['role_label'] ) ? '' : stripslashes( $config['role_label'] ); ?>">
			</td>
			<td style="text-align: right">
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="role_required"
					       id="role_required" <?php checked( $config['role_required'], 1, true ); ?>>
					<label for="role_required"></label>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="website_url_display"
					       id="website_url_display" <?php checked( $config['website_url_display'], 1, true ); ?>>
					<label for="website_url_display"></label>
				</div>
			</td>
			<td>
				<?php echo __( 'Website URL', TVO_TRANSLATE_DOMAIN ) ?>
			</td>
			<td>
				<input class="tvo_config_field tve_lightbox_input tvo_website_url" type="text" name="website_url_label"
				       value="<?php echo empty( $config['website_url_label'] ) ? __( 'Website URL', TVO_TRANSLATE_DOMAIN ) : stripslashes( $config['website_url_label'] ); ?>">
			</td>
			<td style="text-align: right">
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="website_url_required"
					       id="website_url_required" <?php checked( $config['website_url_required'], 1, true ); ?>>
					<label for="website_url_required"></label>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="title_display"
					       id="title_display" <?php checked( $config['title_display'], 1, true ); ?>>
					<label for="title_display"></label>
				</div>
			</td>
			<td>
				<?php echo __( 'Title', TVO_TRANSLATE_DOMAIN ) ?>
			</td>
			<td>
				<input class="tvo_config_field tve_lightbox_input" type="text" name="title_label"
				       value="<?php echo empty( $config['title_label'] ) ? '' : stripslashes( $config['title_label'] ); ?>">
			</td>
			<td style="text-align: right">
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo_config_field" type="checkbox" name="title_required"
					       id="title_required" <?php checked( $config['title_required'], 1, true ); ?>>
					<label for="title_required"></label>
				</div>
			</td>
		</tr>
		</tbody>
	</table>
	<div id="tvo-questions-list">

		<div class="tvo-row tvo-collapse tvo-question tvo-default-question">
			<div class="tvo-col tvo-s2">
				<span class="tvo-q-label"><?php echo __( 'Question 1', TVO_TRANSLATE_DOMAIN ) ?></span>
				<a href="javascript:void(0)" class="tvo-remove-question tvo-right tvo-f-icon tvo-red-text"><span class="tvo-f-icon-trash"></span></a>
			</div>
			<div class="tvo-col tvo-s9">
				<input class="tve_lightbox_input tvo-question-input" type="text"
				       value="<?php echo empty( $config['questions'][0] ) ? __( 'What was your experience with our product like?', TVO_TRANSLATE_DOMAIN ) :  stripslashes( $config['questions'][0] ); ?>">
				<a href="javascript:void(0)" <?php if ( ! empty( $config['placeholders'][0] ) ) : ?> style="display: none" <?php endif; ?> class="tvo-show-placeholder">+ <?php echo __( 'Add placeholder text', TVO_TRANSLATE_DOMAIN ); ?></a>
			</div>
			<div class="tvo-col tvo-s1">
				<div class="tve_lightbox_input_holder tve_lightbox_no_label">
					<input class="tvo-required" type="checkbox" id="tvo_question_req_0" <?php checked( $config['questions_required'][0], 1, true ); ?>>
					<label for="tvo_question_req_0"></label>
				</div>
			</div>
			<div class=" tvo-col tvo-s12 tvo-row tvo-placeholder" <?php if ( empty( $config['placeholders'][0] ) ) : ?> style="display: none" <?php endif; ?>>
				<div class="tvo-col tvo-s2">
					<span class="tvo-p-label"><?php echo __( 'Placeholder 1', TVO_TRANSLATE_DOMAIN ) ?></span>
					<a href="javascript:void(0)" class="tvo-remove-placeholder tvo-right tvo-f-icon tvo-red-text"><span class="tvo-f-icon-trash"></span></a>
				</div>
				<div class="tvo-col tvo-s9">
					<span class="tvo-placeholder-connect">L</span>
					<input class="tve_lightbox_input tvo-placeholder-input" type="text"
						<?php echo empty( $config['placeholders'][0] ) ? __( ' placeholder="Enter your placeholder text here."', TVO_TRANSLATE_DOMAIN ) : ' value="' . stripslashes( $config['placeholders'][0] ) . '""'; ?>>
				</div>
				<div class="tvo-col tvo-s1"></div>
			</div>
		</div>
		<?php if ( ! empty( $config['questions'] ) ) : ?>
			<?php foreach ( $config['questions'] as $index => $q ) : ?>
				<?php if ( $index === 0 ) : continue; endif; ?>
				<div class="tvo-row tvo-collapse tvo-question">
					<div class="tvo-col tvo-s2">
						<span class="tvo-q-label"><?php echo __( 'Question ' . ( $index + 1 ), TVO_TRANSLATE_DOMAIN ) ?></span>
						<a href="javascript:void(0)" class="tvo-remove-question tvo-right tvo-f-icon tvo-red-text"><span class="tvo-f-icon-trash"></span></a>
					</div>
					<div class="tvo-col tvo-s9">
						<input class="tve_lightbox_input tvo-question-input" type="text" value="<?php echo stripslashes( $q ); ?>">
						<a href="javascript:void(0)" <?php if ( ! empty( $config['placeholders'][ $index ] ) ) : ?> style="display: none" <?php endif; ?> class="tvo-show-placeholder">+ <?php echo __( 'Add placeholder text', TVO_TRANSLATE_DOMAIN ); ?></a>
					</div>
					<div class="tvo-col tvo-s1">
						<div class="tve_lightbox_input_holder tve_lightbox_no_label">
							<input id="tvo_question_req_<?php echo $index; ?>" class="tvo-required" type="checkbox" <?php checked( $config['questions_required'][ $index ], 1, true ); ?>>
							<label for="tvo_question_req_<?php echo $index; ?>"></label>
						</div>
					</div>
					<div class=" tvo-col tvo-s12 tvo-row tvo-placeholder" <?php if ( empty( $config['placeholders'][ $index ] ) ) : ?> style="display: none" <?php endif; ?>>
						<div class="tvo-col tvo-s2">
							<span class="tvo-p-label"><?php echo __( 'Placeholder ' . ( $index + 1 ), TVO_TRANSLATE_DOMAIN ) ?></span>
							<a href="javascript:void(0)" class="tvo-remove-placeholder tvo-right tvo-f-icon tvo-red-text"><span class="tvo-f-icon-trash"></span></a>
						</div>
						<div class="tvo-col tvo-s9">
							<span class="tvo-placeholder-connect">L</span>
							<input class="tve_lightbox_input tvo-placeholder-input" type="text"
								<?php echo empty( $config['placeholders'][ $index ] ) ? __( ' placeholder="Enter your placeholder text here."', TVO_TRANSLATE_DOMAIN ) : ' value="' . stripslashes( $config['placeholders'][ $index ] ) . '""'; ?>>
						</div>
						<div class="tvo-col tvo-s1"></div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
		<a class="tvo-add-question" href="javascript:void(0);">
			<div class="tvo-card tvo-card-xsmall">
				<div class="tvo-card-content">
					<span class="tvo-f-icon-add tvo-add-testimonial"></span>
					<?php echo __( 'Add New Question', TVO_TRANSLATE_DOMAIN ) ?>
				</div>
			</div>
		</a>
	</div>
	<br>
	<h5>
		<?php echo __( 'Other settings', TVO_TRANSLATE_DOMAIN ) ?>
	</h5>
	<div class="tvo-row tvo-collapse">
		<div class="tvo-col tvo-s4">
			<p>
				<span class="tvo-f-icon-image tvo-margin-right-small"></span>
				<?php echo __( 'Include image field', TVO_TRANSLATE_DOMAIN ) ?>
			</p>
		</div>
		<div class="tvo-col tvo-s6">
			<div class="tve_lightbox_input_holder tve_lightbox_no_label">
				<label class="tvo_switch">
					<input id="tvo_dispaly_image" name="image_display" class="tvo_config_field" type="checkbox" <?php checked( $config['image_display'], 1, true ); ?>>
					<span></span>
				</label>
			</div>
		</div>
	</div>
	<div class="tvo-row tvo-collapse">
		<div class="tvo-col tvo-s4">
			<p>
				<span class="tvo-f-icon-check tvo-text-green tvo-margin-right-small"></span>
				<?php echo __( 'On successful message', TVO_TRANSLATE_DOMAIN ) ?>
			</p>
		</div>
		<div class="tvo-col tvo-s6">
			<div class="tve_lightbox_select_holder">
				<select class="tvo_config_field" name="on_success_option" data-message="<?php echo __( 'Thanks for submitting your testimonial.', TVO_TRANSLATE_DOMAIN ); ?>" data-redirect="http://">
					<option <?php selected( $config['on_success_option'], 'message', true ); ?> value="message"><?php echo __( 'Show success message', TVO_TRANSLATE_DOMAIN ) ?></option>
					<option <?php selected( $config['on_success_option'], 'redirect', true ); ?> value="redirect"><?php echo __( 'Redirect to url', TVO_TRANSLATE_DOMAIN ) ?></option>
				</select>
			</div>
			<div>
				<input class="tvo_config_field tve_lightbox_input" type="text" name="on_success"
				       value="<?php echo empty( $config['on_success'] ) ? __( 'Thanks for submitting your testimonial.', TVO_TRANSLATE_DOMAIN ) : stripslashes( $config['on_success'] ); ?>">
			</div>
		</div>
	</div>
	<div class="tvo-row tvo-collapse">
		<div class="tvo-col tvo-s4">
			<p>
				<span class="tvo-f-icon-chat tvo-text-blue tvo-margin-right-small"></span>
				<?php echo __( 'Submit button text', TVO_TRANSLATE_DOMAIN ) ?>
			</p>
		</div>
		<div class="tvo-col tvo-s6">
			<input class="tvo_config_field tve_lightbox_input" type="text" name="button_text"
			       value="<?php echo empty( $config['button_text'] ) ? __( 'Submit', TVO_TRANSLATE_DOMAIN ) : stripslashes( $config['button_text'] ); ?>">
		</div>
	</div>
	<br>
	<div class="tvo-row tvo-collapse">
		<div class="tvo-col tvo-s4">
			<p>
				<span class="tvo-f-icoan-tags tvo-orange-text tvo-margin-right-small"></span>
				<?php echo __( 'Add tags (optional)', TVO_TRANSLATE_DOMAIN ) ?>
			</p>
		</div>
		<div class="tvo-col tvo-s6">
			<select class="tvo_config_field tve_lightbox_input tvo-all-tags" type="text" name="tags" data-selected='<?php echo json_encode( $config['tags'] ); ?>'></select>
		</div>
	</div>

	<?php
	$captcha_api       = Thrive_Dash_List_Manager::credentials( 'recaptcha' );
	$captcha_available = ! empty( $captcha_api['site_key'] );
	?>

	<div class="tvo-row tvo-collapse">
		<div class="tvo-col tvo-s12">
			<p>
			<input class="tvo_config_field"
			       type="checkbox"
			       name="reCaptcha_option"
			       id="tvo_activate_reCaptcha"
				<?php
					if( !empty($config['reCaptcha_option'] ) ) {
						checked( $config['reCaptcha_option'], 1, true );
					}
				?>
				<?php echo $captcha_available ? '' : ' disabled'; ?>
			>
			 <?php echo __( 'Activate reCaptcha ', TVO_TRANSLATE_DOMAIN ) ?> </p>

			<label for="reCaptcha_option">
				<?php if ( ! $captcha_available ) : ?>
					<a href = <?php echo admin_url('admin.php?page=tve_dash_api_connect') ?> > <?php echo __( 'Requires integration with Google ReCaptcha', TVO_TRANSLATE_DOMAIN ) ?> </a>
					<?php else: ?>
					<p> <?php echo __( 'Please note that only one reCaptcha may be active on a page!', TVO_TRANSLATE_DOMAIN ) ?></p>
				<?php endif ?>
			</label>
		</div>
	</div>

	<input type="button" class="tve_editor_button tve_editor_button_success tve_click tve_right"
	       data-ctrl="function:tvo.capture.save_form_settings" value="Save">
</div>

<script type="text/javascript">
	jQuery( document ).ready( function () {
		jQuery( '#tvo_capture_form_settings' ).find( '.tvo-all-tags' ).select2( {
			multiple: true,
			tags: true,
			data: TVO_Front.all_tags
		} ).on( 'select2:select', function ( e ) {
			if ( e.params.data.text != e.params.data.id ) {
				return;
			}

			var $select = jQuery( this );

			jQuery.ajax( {
				headers: {
					'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
				},
				cache: false,
				url: '<?php echo tvo_get_route_url( 'tags' ); ?>',
				type: 'POST',
				data: {'name': e.params.data.text}
			} ).done( function ( response ) {
				TVO_Front.all_tags.push( {
					id: response.tag.term_id,
					text: response.tag.name
				} );
				$select.find( 'option[value="' + e.params.data.text + '"]' ).remove();
				$select.append( '<option value="' + response.tag.term_id + '" selected>' + response.tag.name + '</option>' ).trigger( 'change' );
			} );
		} ).val(<?php echo json_encode( empty( $config['tags'] ) ? array() : $config['tags'] ); ?>).trigger( 'change' );

		jQuery( '.tvo-add-question' ).click( function () {
			var $q = jQuery( '<div>', {
				class: 'tvo-row tvo-collapse tvo-question',
				html: jQuery( '.tvo-default-question' ).html()
			} ).insertBefore( '.tvo-add-question' );


			$q.find( '.tvo-question-input' ).val( 'Enter your question here' );
			$q.find( '.tvo-placeholder-input' ).attr( 'placeholder', 'Enter your placeholder text here' ).val( '' );
			$q.find( '.tvo-show-placeholder' ).show();
			$q.find( '.tvo-placeholder' ).hide();

			tvo_question_number();
		} );

		jQuery( document ).on( 'click', '.tvo-remove-question', function () {
			jQuery( this ).parents( '.tvo-question' ).remove();
			tvo_question_number();
		} ).on( 'click', '.tvo-show-placeholder', function () {
			var $this = jQuery( this );
			$this.parents( '.tvo-question' ).find( '.tvo-placeholder' ).show();
			$this.hide();
		} ).on( 'click', '.tvo-remove-placeholder', function () {
			var $q = jQuery( this ).parents( '.tvo-question' );
			$q.find( '.tvo-placeholder' ).hide();
			$q.find( '.tvo-show-placeholder' ).show();
			$q.find( '.tvo-placeholder-input' ).val( '' );
		} );

		function tvo_question_number() {
			jQuery( '.tvo-question' ).each( function ( index ) {
				var $this = jQuery( this );
				$this.find( '.tvo-q-label' ).html( 'Question ' + (index + 1) );
				$this.find( '.tvo-p-label' ).html( 'Placeholder ' + (index + 1) );
				$this.find( '.tvo-required' ).attr( 'id', 'tvo_question_req_' + index );
				$this.find( 'label' ).attr( 'for', 'tvo_question_req_' + index );
			} );
		}

		jQuery( '.tvo_config_field[name="on_success_option"]' ).change( function () {
			jQuery( '.tvo_config_field[name="on_success"]' ).val( this.getAttribute( 'data-' + this.value ) );
		} )
	} );
</script>
