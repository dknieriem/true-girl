<?php defined( 'ABSPATH' ) or exit;

/** @var MC4WP_Styles_Builder $builder */
?>
<style>#mc4wp-admin label{ font-weight: bold; display: block; }</style>

<div id="mc4wp-admin" class="wrap mc4wp-settings">

	<p class="breadcrumbs">
		<span class="prefix"><?php echo __( 'You are here: ', 'mailchimp-for-wp' ); ?></span>
		<a href="<?php echo admin_url( 'admin.php?page=mailchimp-for-wp' ); ?>">MailChimp for WordPress</a> &rsaquo;
		<a href="<?php echo admin_url( 'admin.php?page=mailchimp-for-wp-forms' ); ?>"><?php _e( 'Forms', 'mailchimp-for-wp' ); ?></a> &rsaquo;
		<a href="<?php echo admin_url( 'admin.php?page=mailchimp-for-wp-forms&form_id=' . $form_id . '&view=edit-form' ); ?>">Form <?php echo $form_id; ?> | <?php echo esc_html( $form->name ); ?></a> &rsaquo;
		<span class="current-crumb"><strong><?php _e( 'Styles Builder', 'mailchimp-for-wp' ); ?></strong></span>
	</p>

	<h1 class="page-title">
		<?php _e( 'Styles Builder', 'mailchimp-for-wordpress' ); ?>
	</h1>
	
	<div class="row small-margin">
		<div class="main-content col col-3">
			<!-- Main Content -->

			<p>
				<?php _e( 'Use the fields below to create custom styling rules for your forms.', 'mailchimp-for-wp' ); ?>
			</p>


			<form action="" method="get">
				<table class="form-table">
					<tr valign="top">
						<th style="width: 250px"><label><?php _e( 'Select form to build styles for:', 'mailchimp-for-wp' ); ?></label></th>
						<td>
							<select name="form_id" class="widefat mc4wp-form-select">
								<?php foreach( $forms as $form ) { ?>
									<option value="<?php echo $form->ID; ?>" <?php selected( $form->ID, $form_id ); ?>><?php printf( '%d | %s', $form->ID, esc_html( $form->name ) ); ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
				</table>

				<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>">
				<input type="hidden" name="view" value="<?php echo esc_attr( $_GET['view'] ); ?>">
				<input type="submit" value="Apply" style="display: none;" />
			</form>

			<form action="" method="post" id="form-css-settings">
				<input type="hidden" name="_mc4wp_action" value="styles_builder_save" />
				<?php wp_nonce_field( 'styles_builder_save' ); ?>
				<input type="hidden" name="form_id" value="<?php echo esc_attr( $form_id ); ?>" />
				<input type="submit" name="submit" class="button button-primary" value="Build CSS File" style="display: none;">

				<noscript><p><?php _e( 'You need to have JavaScript enabled to see a preview of your form.', 'mailchimp-for-wp' ); ?></p></noscript>

				<div class="mc4wp-css-settings mc4wp-accordion" id="mc4wp-css-form">

					<div>
						<h4><?php _e( 'Form container style', 'mailchimp-for-wp' ); ?></h4>
						<div>
							<div class="row small-margin">
								<div class="col col-3">
									<label for="form-width"><?php _e( 'Form width', 'mailchimp-for-wp' ); ?></label>
									<input id="form-width" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_width]" type="text" value="<?php echo esc_attr( $styles['form_width'] ); ?>" class="mc4wp-option" pattern="((\d+)(px|%)*)" />
									<span class="help block"><?php _e( 'px or %', 'mailchimp-for-wp' ); ?></span>
								</div>
								<div class="col col-3">
									<label for="form-text-align"><?php _e( 'Text alignment', 'mailchimp-for-wp' ); ?></label>
									<select name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_text_align]" id="form-text-align" class="mc4wp-option">
										<option value="" <?php selected( $styles['form_text_align'], '' ); ?>><?php _e( 'Choose alignment', 'mailchimp-for-wp' ); ?></option>
										<option value="left" <?php selected( $styles['form_text_align'], 'left' ); ?>><?php _e( 'Left', 'mailchimp-for-wp' ); ?></option>
										<option value="center" <?php selected( $styles['form_text_align'], 'center' ); ?>><?php _e( 'Center', 'mailchimp-for-wp' ); ?></option>
										<option value="right" <?php selected( $styles['form_text_align'], 'right' ); ?>><?php _e( 'Right', 'mailchimp-for-wp' ); ?></option>
									</select>
								</div>
							</div>
							<div class="row small-margin">
								<div class="col col-3">
									<label for="form-background-color"><?php _e( 'Background color', 'mailchimp-for-wp' ); ?></label>
									<input id="form-background-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_background_color]" type="text" class="color-field mc4wp-option" value="<?php echo esc_attr( $styles['form_background_color'] ); ?>" />
								</div>
								<div class="col col-3">
									<label for="form-padding"><?php _e( 'Padding', 'mailchimp-for-wp' ); ?></label>
									<input id="form-padding" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_padding]" type="number" class="small-text mc4wp-option" max="99" min="0" value="<?php echo esc_attr( $styles['form_padding'] ); ?>"  /> &nbsp;
								</div>
							</div>
							<div class="row small-margin">
								<div class="col col-3">
									<label for="form-border-color"><?php _e( 'Border color', 'mailchimp-for-wp' ); ?></label>
									<input id="form-border-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_border_color]" type="text" class="color-field mc4wp-option" value="<?php echo esc_attr( $styles['form_border_color'] ); ?>" />
								</div>
								<div class="col col-3">
									<label for="form-border-width"><?php _e( 'Border width', 'mailchimp-for-wp' ); ?></label>
									<input id="form-border-width" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_border_width]" type="number" class="small-text mc4wp-option" max="99" min="0" value="<?php echo esc_attr( $styles['form_border_width'] ); ?>" />
								</div>
							</div>
							<div class="row small-margin">
								<div class="col col-3">
									<label for="form-font-color"><?php _e( 'Text color', 'mailchimp-for-wp' ); ?></label>
									<input id="form-font-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_font_color]" type="text" class="color-field mc4wp-option" value="<?php echo esc_attr( $styles['form_font_color'] ); ?>" />
								</div>
								<div class="col col-3">
									<label for="form-font-size"><?php _e( 'Text size', 'mailchimp-for-wp' ); ?></label>
									<input id="form-font-size" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_font_size]" type="number" class="small-text mc4wp-option" max="99" min="0" value="<?php echo esc_attr( $styles['form_font_size'] ); ?>"  />
								</div>
							</div>
							<div class="row small-margin">
								<div class="col col-6">
									<label for="form-background-image"><?php _e( 'Background image', 'mailchimp-for-wp' ); ?></label>
									<input type="text" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_background_image]" id="form-background-image" class="mc4wp-option" value="<?php echo esc_attr( $styles['form_background_image'] ); ?>" placeholder="http://..." />
									<input type="button" class="button upload-image" value="<?php _e( 'Upload Image' ); ?>" />
									<br />
									<label class="screen-reader-text" for="form-background-repeat"><?php _e( "Repeat background image?", 'mailchimp-for-wp' ); ?></label>
									<select class="mc4wp-option" id="form-background-repeat" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_background_repeat]">
										<option value="repeat" <?php selected( $styles['form_background_repeat'], 'repeat' ); ?>><?php _e( 'Repeat image', 'mailchimp-for-wp' ); ?></option>
										<option value="repeat-x" <?php selected( $styles['form_background_repeat'], 'repeat-x' ); ?>><?php _e( 'Repeat horizontally', 'mailchimp-for-wp' ); ?></option>
										<option value="repeat-y" <?php selected( $styles['form_background_repeat'], 'repeat-y' ); ?>><?php _e( 'Repeat vertically', 'mailchimp-for-wp' ); ?></option>
										<option value="no-repeat" <?php selected( $styles['form_background_repeat'], 'no-repeat' ); ?>><?php _e( 'Do not repeat', 'mailchimp-for-wp' ); ?></option>
										<option value="cover" <?php selected( $styles['form_background_repeat'], 'cover' ); ?>><?php _e( 'Cover entire area', 'mailchimp-for-wp' ); ?></option>
									</select>
								</div>
							</div>
							<!-- end accordion block -->
						</div>
					</div>

					<div>
						<h4><?php _e( 'Label styles', 'mailchimp-for-wp' ); ?></h4>
						<div>

							<div class="row small-margin">
								<div class="col col-3">
									<label for="labels-width"><?php _e( 'Label width', 'mailchimp-for-wp' ); ?></label>
									<input id="labels-width" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][labels_width]" type="text" value="<?php echo esc_attr( $styles['labels_width'] ); ?>" class="mc4wp-option" pattern="((\d+)(px|%)*)" />
									<span class="help block"><?php _e( 'px or %', 'mailchimp-for-wp' ); ?></span>
								</div>
								<div class="col col-3">
									<label for="labels-font-color"><?php _e( 'Text color', 'mailchimp-for-wp' ); ?></label>
									<input id="labels-font-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][labels_font_color]" value="<?php echo esc_attr( $styles['labels_font_color'] ); ?>" type="text" class="color-field mc4wp-option" />
								</div>
							</div>
							<div class="row small-margin">
								<div class="col col-3">
									<label for="labels-font-size"><?php _e( 'Text size', 'mailchimp-for-wp' ); ?></label>
									<input id="labels-font-size" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][labels_font_size]" type="number" class="small-text mc4wp-option" max="99" min="0" value="<?php echo esc_attr( $styles['labels_font_size'] ); ?>"  />
								</div>
								<div class="col col-3">
									<label for="labels-font-style"><?php _e( 'Text style', 'mailchimp-for-wp' ); ?></label>
									<select id="labels-font-style" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][labels_font_style]" class="mc4wp-option">
										<option value="" <?php selected( $styles['labels_font_style'], '' ); ?>><?php _e( 'Choose text style..', 'mailchimp-for-wp' ); ?></option>
										<option value="normal" <?php selected( $styles['labels_font_style'], 'normal' ); ?>><?php _e( 'Normal', 'mailchimp-for-wp' ); ?></option>
										<option value="bold" <?php selected( $styles['labels_font_style'], 'bold' ); ?>><?php _e( 'Bold', 'mailchimp-for-wp' ); ?></option>
										<option value="italic" <?php selected( $styles['labels_font_style'], 'italic' ); ?>><?php _e( 'Italic', 'mailchimp-for-wp' ); ?></option>
										<option value="bolditalic" <?php selected( $styles['labels_font_style'], 'bolditalic' ); ?>><?php _e( 'Bold & Italic', 'mailchimp-for-wp' ); ?></option>
									</select>
								</div>
							</div>
							<div class="row small-margin">
								<div class="col col-3">
									<label for="labels-display"><?php _e( 'Display', 'mailchimp-for-wp' ); ?></label>

									<select id="labels-display" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][labels_display]" class="mc4wp-option">
										<option value=""></option>
										<option value="inline-block" <?php selected( $styles['labels_display'], 'inline-block' ); ?>> <?php _e( 'Inline', 'mailchimp-for-wp' ); ?></option>
										<option value="block" <?php selected( $styles['labels_display'], 'block' ); ?>> <?php _e( 'New line', 'mailchimp-for-wp' ); ?></option>
									</select>
								</div>
								<div class="col col-3">

								</div>
							</div>

							<!-- end block -->
						</div>
					</div>

					<div>
						<h4><?php _e( 'Field styles', 'mailchimp-for-wp' ); ?></h4>
						<div>

							<div class="row small-margin">
								<div class="col col-3">
									<label for="fields-width"><?php _e( 'Field width', 'mailchimp-for-wp' ); ?></label>
									<input id="fields-width" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][fields_width]" type="text" value="<?php echo esc_attr( $styles['fields_width'] ); ?>" class="mc4wp-option" pattern="((\d+)(px|%)*)" />
									<span class="help block"><?php _e( 'px or %', 'mailchimp-for-wp' ); ?></span>
								</div>
								<div class="col col-3">
									<label for="fields-height"><?php _e( 'Field height', 'mailchimp-for-wp' ); ?></label>
									<input id="fields-height" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][fields_height]" min="0" type="number" class="small-text mc4wp-option" value="<?php echo esc_attr( $styles['fields_height'] ); ?>" />
								</div>
							</div>
							<div class="row small-margin">
								<div class="col col-3">
									<label for="fields-border-color"><?php _e( 'Border color', 'mailchimp-for-wp' ); ?></label>
									<input id="fields-border-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][fields_border_color]" type="text" class="color-field mc4wp-option" value="<?php echo esc_attr( $styles['fields_border_color'] ); ?>" />
								</div>
								<div class="col col-3">
									<label for="fields-border-width"><?php _e( 'Border width', 'mailchimp-for-wp' ); ?></label>
									<input id="fields-border-width" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][fields_border_width]" type="number" class="small-text mc4wp-option" max="99" min="0" value="<?php echo esc_attr( $styles['fields_border_width'] ); ?>" />
								</div>
							</div>
							<div class="row small-margin">
								<div class="col col-3">
									<label for="fields-display"><?php _e( 'Display', 'mailchimp-for-wp' ); ?></label>
									<select id="fields-display" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][fields_display]" class="widefat mc4wp-option">
										<option value=""></option>
										<option value="inline-block" <?php selected( $styles['fields_display'], 'inline-block' ); ?>> <?php _e( 'Inline', 'mailchimp-for-wp' ); ?></option>
										<option value="block" <?php selected( $styles['fields_display'], 'block' ); ?>> <?php _e( 'New line', 'mailchimp-for-wp' ); ?></option>
									</select>
								</div>
								<div class="col col-3">
									<label for="fields-border-radius"><?php _e( 'Border radius', 'mailchimp-for-wp' ); ?></label>
									<input id="fields-border-radius" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][fields_border_radius]" type="number" class="small-text mc4wp-option" max="99" min="0" value="<?php echo esc_attr( $styles['fields_border_radius'] ); ?>" />
								</div>
							</div>
							<div class="row small-margin">
								<div class="col col-3">
									<label for="fields-focus-outline-color"><?php _e( 'Focus outline', 'mailchimp-for-wp' ); ?></label>
									<input id="fields-focus-outline-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][fields_focus_outline_color]" type="text" class="color-field mc4wp-option" value="<?php echo esc_attr( $styles['fields_focus_outline_color'] ); ?>" />
								</div>
								<div class="col col-3">

								</div>
							</div>

							<!-- End of block -->

						</div>
					</div>

					<div>
						<h4><?php _e( 'Button styles', 'mailchimp-for-wp' ); ?></h4>
						<div>
							<div class="row small-margin">
								<div class="col col-3">
									<label for="buttons-width"><?php _e( 'Button width', 'mailchimp-for-wp' ); ?></label>
									<input id="buttons-width" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_width]" type="text" value="<?php echo esc_attr( $styles['buttons_width'] ); ?>" class="mc4wp-option" pattern="((\d+)(px|%)*)" />
									<span class="help block"><?php _e( 'px or %', 'mailchimp-for-wp' ); ?></span>
								</div>
								<div class="col col-3">
									<label for="buttons-height"><?php _e( 'Button height', 'mailchimp-for-wp' ); ?></label>
									<input id="buttons-height" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_height]" min="0" type="number" class="small-text mc4wp-option" value="<?php echo esc_attr( $styles['buttons_height'] ); ?>" />
								</div>
							</div>
							<div class="row small-margin">
								<div class="col col-3">
									<label for="buttons-background-color"><?php _e( 'Background color', 'mailchimp-for-wp' ); ?></label>
									<input id="buttons-background-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_background_color]" type="text" class="color-field mc4wp-option" value="<?php echo esc_attr( $styles['buttons_background_color'] ); ?>" />
									<input id="buttons-hover-background-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_hover_background_color]" type="hidden" class="mc4wp-option" value="<?php echo esc_attr( $styles['buttons_hover_background_color'] ); ?>" />
								</div>
								<div class="col col-3">
									<label for="buttons-border-width"><?php _e( 'Border width', 'mailchimp-for-wp' ); ?></label>
									<input id="buttons-border-width" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_border_width]" type="number" class="small-text mc4wp-option" max="99" min="0" value="<?php echo esc_attr( $styles['buttons_border_width'] ); ?>" />
								</div>
							</div>
							<div class="row small-margin">
								<div class="col col-3">
									<label for="buttons-border-color"><?php _e( 'Border color', 'mailchimp-for-wp' ); ?></label>
									<input id="buttons-border-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_border_color]" type="text" class="color-field mc4wp-option" value="<?php echo esc_attr( $styles['buttons_border_color'] ); ?>" />
									<input id="buttons-hover-border-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_hover_border_color]" type="hidden" class="mc4wp-option" value="<?php echo esc_attr( $styles['buttons_hover_border_color'] ); ?>" />
								</div>
								<div class="col col-3">
									<label for="buttons-border-radius"><?php _e( 'Border radius', 'mailchimp-for-wp' ); ?></label>
									<input id="buttons-border-radius" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_border_radius]" type="number" class="small-text mc4wp-option" max="99" min="0" value="<?php echo esc_attr( $styles['buttons_border_radius'] ); ?>" />
								</div>
							</div>
							<div class="row small-margin">
								<div class="col col-3">
									<label for="buttons-font-color"><?php _e( 'Text color', 'mailchimp-for-wp' ); ?></label>
									<input id="buttons-font-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_font_color]" type="text" class="color-field mc4wp-option" value="<?php echo esc_attr( $styles['buttons_font_color'] ); ?>" />
								</div>
								<div class="col col-3">
									<label for="buttons-font-size"><?php _e( 'Text size', 'mailchimp-for-wp' ); ?></label>
									<input id="buttons-font-size" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_font_size]" type="number" class="small-text mc4wp-option" max="99" min="0" value="<?php echo esc_attr( $styles['buttons_font_size'] ); ?>"  />
								</div>
							</div>
							
							<!-- End of block -->
						</div>
					</div>

					<div>
						<h4><?php _e( 'Error and success messages', 'mailchimp-for-wp' ); ?></h4>
						<div>

							<div class="row small-margin">
								<div class="col col-3">
									<label for="messages-font-color-success"><?php _e( 'Success text color', 'mailchimp-for-wp' ); ?></label>
									<input id="messages-font-color-success" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][messages_font_color_success]" type="text" class="color-field mc4wp-option" value="<?php echo esc_attr( $styles['messages_font_color_success'] ); ?>" />

								</div>
								<div class="col col-3">
									<label for="messages-font-color-error"><?php _e( 'Error text color', 'mailchimp-for-wp' ); ?></label>
									<input id="messages-font-color-error" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][messages_font_color_error]" type="text" class="color-field mc4wp-option" value="<?php echo esc_attr( $styles['messages_font_color_error'] ); ?>" />
								</div>
							</div>

							<div class="row small-margin">
								<div class="col col-3">
									<label for="messages-font-color-notice"><?php _e( 'Notice text color', 'mailchimp-for-wp' ); ?></label>
									<input id="messages-font-color-notice" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][messages_font_color_notice]" type="text" class="color-field mc4wp-option" value="<?php echo esc_attr( $styles['messages_font_color_notice'] ); ?>" />

								</div>
							</div>
							<!-- end of block -->
						</div>
					</div>

					<div>
						<h4><?php _e( 'Advanced', 'mailchimp-for-wp' ); ?></h4>
						<div>

							<div class="small-margin"></div>

							<label for="css-selector-prefix"><?php _e( 'CSS Selector Prefix', 'mailchimp-for-wp' ); ?></label>
							<input type="text" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][selector_prefix]" value="<?php echo esc_attr( $styles['selector_prefix'] ); ?>" placeholder="Example: #content" class="mc4wp-option" id="css-selector-prefix"/>
							<span class="help block"><?php _e( 'Use this to create a more specific (and thus more "important") CSS selector.', 'mailchimp-for-wp' ); ?></span>

							<div class="small-margin"></div>

							<label for="manual-css"><?php _e( 'Manual CSS', 'mailchimp-for-wp' ); ?></label>
							<?php $rows = substr_count( $styles['manual'], PHP_EOL ) + 6; ?>
							<textarea class="widefat mc4wp-option" rows="<?php echo esc_attr( $rows ); ?>" cols="50" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][manual]" id="manual-css" placeholder="Example: .mc4wp-form { background: url('http://...'); }"><?php echo esc_textarea( $styles['manual'] ); ?></textarea>

							<div class="small-margin"></div>

							<?php if( count( $forms ) > 1 ) { // only show copy feature if multiple forms exist ?>
							<label for="copy_from_form_id"><?php _e( 'Copy styles from other form', 'mailchimp-for-wp' );?></label>
							<select name="copy_from_form_id" id="copy_from_form_id">
								<?php foreach( $forms as $form ) {
									// skip current form
									if( $form->ID === $form_id ) { continue; }
									?>
									<option value="<?php echo $form->ID; ?>"><?php printf( '%d | %s', $form->ID, esc_html( $form->name ) ); ?></option>
								<?php } ?>
							</select>
							<input type="submit" name="_mc4wp_copy_form_styles" value="<?php _e( 'Copy Styles', 'mailchimp-for-wp' ); ?>" class="button-secondary" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to copy form styles from another form? This will overwrite current styles for this form.', 'mailchimp-for-wp' ); ?>');"/>
							<?php } ?>
							<!-- end of block -->

						</div>
					</div>

				</div><!-- End Accordion -->

				<p class="submit">
					<?php submit_button( null, 'primary', 'submit', false ); ?>
					<button type="submit" tabindex="9999" name="_mc4wp_delete_form_styles" value="<?php echo $form_id; ?>" class="button-secondary" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete all custom styles for this form?', 'mailchimp-for-wp' ); ?>');"><?php _e( 'Delete Form Styles', 'mailchimp-for-wp' ); ?></button>
				</p>

			</form>
			<!-- / Main Content -->
		</div>

		<div class="col col-3">
			<!-- Preview -->
			<h3><?php _e( 'Form Preview', 'mailchimp-for-wp' ); ?></h3>
			<iframe class="form-preview" id="mc4wp-css-preview" data-src-url="<?php echo esc_attr( $preview_url ); ?>" src="<?php echo esc_attr( $preview_url ) ?>"></iframe>
			<!-- / Preview -->
		</div>
	</div>


	<p class="mc4wp-notice">
		<span class="dashicons dashicons-info" style="color: #999;"></span>
		<?php printf( __( 'Tip: have a look at our <a href="%s">knowledge base</a> articles on <a href="%s">creating an inline form</a> or <a href="%s">styling your form</a> in general.', 'mailchimp-for-wp' ), 'https://mc4wp.com/kb/', 'https://mc4wp.com/kb/single-line-forms/', 'https://mc4wp.com/kb/category/styling-your-form/' ); ?>
	</p>



</div>
