<?php
$config = array(
	'email'    => 'Please enter a valid email address',
	'phone'    => 'Please enter a valid phone number',
	'required' => 'Please fill in the required fields',
)
?>
<div class="thrv_wrapper tve_no_drag tve-tqb-page-type tqb-optin-template-1 tve_editor_main_content tve_clearfix" style="<?php echo $main_content_style; ?>">
	<h2 class="tve_p_center">Great job!</h2>
	<p class="tve_p_center">Be the first to know about the new quizzes</p>

	<div class="thrv_wrapper thrv_contentbox_shortcode" data-tve-style="5">
		<div class="tve_cb tve_cb5">
			<div class="tve_cb_cnt">
				<div class="thrv_wrapper thrv_content_container_shortcode">
					<div class="tve_clear"></div>
					<div class="tve_content_inner" style="min-width: 50px; min-height: 2em; width: 550px;">
						<div class="thrv_wrapper thrv_lead_generation tve_clearfix thrv_lead_generation_vertical tve_centerBtn">
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
									<div class="tve_lg_input_container tve_submit_container tve_lg_submit tve_lg_3">
										<button type="Submit">Submit and go to the results</button>
									</div>
								</div>
							</div>
						</div>
						<div class="tve_clear"></div>
						<p class="tve_p_center tqb-skip-this-step">
							<a class="tve_evt_manager_listen tve_et_click"
							   data-tcb-events='__TCB_EVENT_[{"t":"click","a":"thrive_quiz_next_step","config":{},"elementType":"a"}]_TNEVE_BCT__' href="javascript:void(0)">Skip
								this step</a>
						</p>
					</div>
					<div class="tve_clear"></div>
				</div>
			</div>
		</div>
	</div>
</div>
