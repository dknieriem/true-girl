<?php
$quizzes = TQB_Quiz_Manager::get_valid_quizzes();
?>

<h4><?php echo __( 'Thrive Quiz Builder Shortcodes', Thrive_Quiz_Builder::T ) ?></h4>
<div id="tve_thrive_ultimatum_shortcode_lightbox">
	<hr class="tve_lightbox_line"/>
	<p>
		<?php echo __( 'Select the quiz you want to be displayed in your content.', Thrive_Quiz_Builder::T ) ?>
	</p>
	<div class="tve_options_wrapper tve_clearfix">
		<div class="tve_option_container tve_clearfix">
			<label class="lblOption"><?php echo __( 'Quiz Name', Thrive_Quiz_Builder::T ) ?>:</label>
			<div class="tve_fields_container">
				<div class="tve_lightbox_select_holder">
					<select id="tve_qb_quiz" name="tve_qb_quiz">
						<?php foreach ( $quizzes as $quiz ) : ?>
							<option value="<?php echo $quiz->ID ?>"><?php echo $quiz->post_title ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
	</div>
	<br>
	<div class="tve_editor_button tve_editor_button_success tve_right tve_click"
		 data-ctrl="function:ext.tqb.fetch_quiz_content">
		<div class="tve_update">
			<?php echo __( 'Save', Thrive_Quiz_Builder::T ) ?>
		</div>
	</div>
</div>
<style>
	#tve_lightbox_buttons {
		display: none !important;
	}
</style>
