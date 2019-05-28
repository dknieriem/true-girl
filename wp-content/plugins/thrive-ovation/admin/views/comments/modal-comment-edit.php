<div class="tvo-comments-modal">
	<div class="tvd-modal-content">
		<h3 class="tvd-modal-title"><?php echo __( 'Edit Testimonial', TVO_TRANSLATE_DOMAIN ); ?></h3>
		<div class="tvd-v-spacer"></div>
		<div class="tvd-row tvd-collapse tvd-no-mb">
			<div class="tvo-testimonial-author-image tvd-col tvd-s2 tvd-center-align">
				<div class="tvd-v-spacer"></div>
				<img width="110" src="<?php echo $comment->comment_author_picture_url; ?>"
				     onclick="tvo_open_media();"
				     class="tvo-rounded-img tvo-upload-testimonial-image tvd-pointer">
				<input type="hidden" id="tvo-comment-id"
				       value="<?php echo $comment->comment_ID; ?>">
				<br>
				<a href="javascript:void(0)" class="tvo-upload-testimonial-image tvd-small-text"
				   onclick="tvo_open_media();"
				   id="tvo-upload-testimonial-image"><?php echo __( 'Upload picture', TVO_TRANSLATE_DOMAIN ); ?></a>
				<div class="tvo-image-uploaded" style="display:none;">
					<p class="tvo-change-picture-expl"><?php echo __( 'Click the picture to update', TVO_TRANSLATE_DOMAIN ); ?></p>
					<a href="javascript:void(0)" class="tvd-small-text"
					   data-default="<?php echo tvo_get_default_image_placeholder(); ?>"
					   onclick="remove_image();"
					   id="tvo-remove-testimonial-image"><?php echo __( 'Remove picture', TVO_TRANSLATE_DOMAIN ); ?></a>
				</div>
			</div>
			<div class="tvo-testimonial-data tvd-col tvd-s10" data-id="">
				<div class="tvd-v-spacer"></div>
				<div class="tvd-row">
					<div class="tvd-col tvd-s12">
						<div class="tvd-input-field tvo-title">
							<input type="text" id="tvo-title" class="tvd-validate"
							       value=""/>
							<label
								for="tvo-title"
								data-error="<?php echo __( 'Please fill in the title field', TVO_TRANSLATE_DOMAIN ); ?>"><?php echo __( 'Title', TVO_TRANSLATE_DOMAIN ); ?></label>
						</div>
					</div>
				</div>
				<div class="tvd-row">
					<div class="tvd-col tvd-s6">
						<div class="tvd-input-field tvo-author-name">
							<input type="text" id="tvo-author-name" class="tvd-validate"
							       value="<?php echo $comment->comment_author; ?>"/>
							<label
								for="tvo-author-name"
								data-error="<?php echo __( 'Please fill in the name field', TVO_TRANSLATE_DOMAIN ); ?>"><?php echo __( 'Full Name', TVO_TRANSLATE_DOMAIN ); ?></label>
						</div>
					</div>
					<div class="tvd-col tvd-s6">
						<div class="tvd-input-field tvo-author-email">
							<input type="text" id="tvo-author-email" class="tvd-validate"
							       value="<?php echo $comment->comment_author_email; ?>"/>
							<label
								for="tvo-author-email"
								data-error="<?php echo __( 'Please fill in a valid email address', TVO_TRANSLATE_DOMAIN ); ?>"><?php echo __( 'Email Address', TVO_TRANSLATE_DOMAIN ); ?></label>
						</div>
					</div>
				</div>
				<div class="tvd-row">
					<div class="tvd-col tvd-s6">
						<div class="tvd-input-field tvo-author-ocupation">
							<input type="text" id="tvo-author-role" class="tvd-validate"
							       value=""/>
							<label
								for="tvo-author-role"
								data-error="<?php echo __( 'Please fill in the occupation field', TVO_TRANSLATE_DOMAIN ); ?>"><?php echo __( 'Role/Occupation', TVO_TRANSLATE_DOMAIN ); ?></label>
						</div>
					</div>
					<div class="tvd-col tvd-s6">
						<div class="tvd-input-field tvo-author-website">
							<input type="text" id="tvo-author-website" class="tvd-validate"
							       value="<?php echo $comment->comment_author_url; ?>"/>
							<label
								for="tvo-author-website"
								data-error="<?php echo __( 'Please fill in a valid website URL', TVO_TRANSLATE_DOMAIN ); ?>"><?php echo __( 'Website URL', TVO_TRANSLATE_DOMAIN ); ?></label>
						</div>
					</div>
				</div>
				<div class="tvd-row tvo-testimonial-tags">
					<div class="tvd-col tvd-s12">
						<h4 class="tvd-no-margin">
							<?php echo __( 'Tags', TVO_TRANSLATE_DOMAIN ); ?>
						</h4>
						<div class="tvo-testimonial-add-tags tvo-edit-testimonial-tags-container">
							<select id="tvo-author-new-tag-modal" class="tvo-add-tag-autocomplete">
								<?php $tags = tvo_get_all_tags(); ?>
								<?php foreach ( $tags as $tag ) { ?>
									<option
										value="<?php echo $tag['id']; ?>"><?php echo $tag['text']; ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="tvo-testimonial-existing-tags"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="tvo-testimonial-content">
			<?php wp_editor( $comment->comment_content, 'tvo-testimonial-content-tinymce', array(
				'quicktags'     => false,
				'media_buttons' => false,
			) ) ?>
		</div>
		<div class="tvd-v-spacer vs-2"></div>
		<div class="tvd-row">
			<div class="tvd-col tvd-s9">
				<?php echo __( 'Would you like to ask the customer\'s permission to use this comment as a testimonial?', TVO_TRANSLATE_DOMAIN ); ?>
			</div>
			<div class="tvd-col tvd-s3">
				<div class="tvd-switch">
					<label for="tvo-ask-permission-email">
						<?php echo __( 'Off', TVO_TRANSLATE_DOMAIN );
						$landing_page_settings  = tvo_get_option( TVO_LANDING_PAGE_SETTINGS_OPTION );
						$email_template_option  = get_option( TVO_EMAIL_TEMPLATE_OPTION );
						$email_template_subject = get_option( TVO_EMAIL_TEMPLATE_SUBJECT_OPTION );
						if ( ! empty( $landing_page_settings['approve'] ) && ! empty( $landing_page_settings['not_approve'] ) && ! empty( $email_template_option ) && ! empty( $email_template_subject ) ) {
							$checked_option = 'checked="checked"';
						} else {
							$checked_option = '';
						} ?>
						<input class="tvo-setting-input-checkbox" type="checkbox" value="1"
						       id="tvo-ask-permission-email" <?php echo $checked_option ?>>
						<span class="tvd-lever"></span>
						<?php echo __( 'On', TVO_TRANSLATE_DOMAIN ); ?>
					</label>
				</div>
			</div>
		</div>
		<div class="tvd-row">
			<div class="tvd-col tvd-s12 tvo-ask-permission-email-response">
				<?php echo $ask_permission_email_response['html']; ?>
			</div>
		</div>
	</div>
	<div class="tvd-modal-footer">
		<div class="tvd-row">
			<div class="tvd-col tvd-s12">
				<a class="tvo-save-new-testimonial tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-right"
				   onclick="tvo_add_edit_testimonial_action();"
				   href="javascript:void(0);">
					<?php echo $ask_permission_email_response['button_text']; ?>
				</a>
			</div>
		</div>
	</div>
</div>
