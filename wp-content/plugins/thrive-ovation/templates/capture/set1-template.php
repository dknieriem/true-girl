<div id="<?php echo $unique_id; ?>" class="tvo_testimonial_form tvo-set1-template tve_black">
	<div class="tvo_form_state">
		<div class="tvo_inputs_container tvo-item-grid">
			<div class="tvo-item-col <?php if ( ! empty( $config['image_display'] ) ) : ?>tvo-item-l8<?php endif; ?> tvo-item-m12 tvo-inputs-col">
				<div class="tvo-form-field tvo-item-m12 tvo-item-col">
					<input autocomplete="off" class="tvo-form-input tvo_input <?php echo $config['name_required'] ? 'tvo_required' : ''; ?>" type="text" name="name" placeholder="<?php echo $config['name_label'] ?>"/>
				</div>
				<div class="tvo-form-field tvo-item-m12 tvo-item-col">
					<input autocomplete="off" class="tvo-form-input tvo_email tvo_input <?php echo $config['email_required'] ? 'tvo_required' : ''; ?>" type="email" name="email" placeholder="<?php echo $config['email_label'] ?>"/>
				</div>
				<?php if ( ! empty( $config['role_display'] ) ) : ?>
					<div class="tvo-form-field tvo-item-m12 tvo-item-col">
						<input autocomplete="off" class="tvo-form-input tvo_input <?php echo $config['role_required'] ? 'tvo_required' : ''; ?>" type="text" name="role" placeholder="<?php echo $config['role_label'] ?>"/>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $config['image_display'] ) ) : ?>
				<div class="tvo-item-col tvo-item-l4 tvo-item-m12 tvo-form-picture">
					<?php include 'picture.php'; ?>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $config['website_url_display'] ) ) : ?>
				<div class="tvo-form-field tvo-item-m12 tvo-item-col">
					<span class="tvo-question-label"><?php echo $config['website_url_label'] ?></span>
					<input autocomplete="off" class="tvo-form-input tvo_input tvo_website_url <?php echo $config['website_url_required'] ? 'tvo_required' : ''; ?>" type="text" name="website_url"/>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $config['title_display'] ) ) : ?>
				<div class="tvo-form-field tvo-item-m12 tvo-item-col">
					<span class="tvo-question-label"><?php echo $config['title_label'] ?></span>
					<input autocomplete="off" class="tvo-form-input tvo_input <?php echo $config['title_required'] ? 'tvo_required' : ''; ?>" type="text" name="title"/>
				</div>
			<?php endif; ?>
			<?php foreach ( $config['questions'] as $index => $q ) : ?>
				<div class="tvo-form-field tvo-item-m12 tvo-item-col">
					<span class="tvo-question-label"><?php echo $q; ?></span>
					<textarea placeholder="<?php echo empty( $config['placeholders'][ $index ] ) ? '' : $config['placeholders'][ $index ]; ?>" autocomplete="off" class="tvo_input tvo-form-textarea <?php echo $config['questions_required'][ $index ] ? 'tvo_required' : ''; ?>"></textarea>
				</div>
			<?php endforeach; ?>
			<?php if ( !empty($config['reCaptcha_option'])  ) { include 'reCaptcha.php'; } ?>
			<div class="tvo-form-field tvo-item-m12 tvo-item-col">
				<button type="Submit" class="tvo-form-button">
					<?php echo empty( $config['button_text'] ) ? __( 'Submit', TVO_TRANSLATE_DOMAIN ) : $config['button_text']; ?>
				</button>
			</div>
			<?php include dirname( __FILE__ ) . '/config.php'; ?>
		</div>
	</div>
	<div class="tvo_success_state" style="display: none;">
		<p class="tvo-success-message">
			<?php echo $config['on_success']; ?>
		</p>
	</div>
</div>
