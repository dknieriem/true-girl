<?php
$config = array(
	'email'    => 'Please enter a valid email address',
	'phone'    => 'Please enter a valid phone number',
	'required' => 'Please fill in the required fields',
)
?>
<div class="thrv_wrapper tve-tqb-page-type tqb-result-template-2 tve_editor_main_content tve_no_drag tve_clearfix" style="<?php echo $main_content_style; ?>">
	<h2 class="tve_p_center">Congratulations!</h2>
	<p class="tve_p_center">You achieved the following result:</p>
	<div class="thrv_wrapper thrv_contentbox_shortcode tqb_content_box" data-tve-style="5">
		<div class="tve_cb tve_cb5">
			<div class="tve_cb_cnt">
				<p class="tve_p_center tve_tqb_result">%result%</p>
			</div>
		</div>
	</div>
	<div class="thrv_wrapper thrv_contentbox_shortcode tqb_content_box_light" data-tve-style="5">
		<div class="tve_cb tve_cb5">
			<div class="tve_cb_cnt">
				<h3 class="tve_p_center">GET YOUR DETAILED RESULTS</h3>
				<p class="tve_p_center tqb-form-subtitle">To get more detailed results use the form below:</p>

				<div class="thrv_wrapper thrv_lead_generation tve_clearfix thrv_lead_generation_vertical" data-tve-style="1" data-tve-version="1">
					<input type="hidden" class="tve-lg-err-msg" value="<?php echo htmlspecialchars( json_encode( $config ) ) ?>">
					<div class="thrv_lead_generation_code" style="display: none;"></div>
					<div class="thrv_lead_generation_container tve_clearfix">
						<div class="tve_lead_generated_inputs_container tve_clearfix">
							<div class="tve_lead_fields_overlay"></div>
							<div class="tve_lg_input_container tve_lg_input tve_lg_3">
								<input type="text" placeholder="Your name" value="" name="name">
							</div>
							<div class="tve_lg_input_container tve_lg_input tve_lg_3">
								<input type="text" placeholder="your_name@domain.com" value="" name="email">
							</div>
							<div class="tve_lg_input_container tve_submit_container tve_lg_submit tve_lg_3" tve-data-style="1">
								<button type="Submit">Send me my detailed results</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="tve_clear"></div>
</div>
