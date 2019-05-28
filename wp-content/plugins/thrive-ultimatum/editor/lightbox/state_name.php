<?php
/**
 * shown when adding / editing a design state - it allows the user to input a name for the design state
 */
?>
<div style="display: none;" id="lb_ult_state_name">
	<h4><?php echo __( 'Add / edit design state', TVE_Ult_Const::T ) ?></h4>
	<hr class="tve_lightbox_line"/>
	<input class="tve_lightbox_input tve_keyup" name="post_title" id="lb_ult_state_title" data-ctrl="controls.keyup.lb_success" placeholder="<?php echo __( "State name", TVE_Ult_Const::T ) ?>"/>
	<div class="tve-sp"></div>
	<div class="tve_clearfix">
		<a href="javascript:void(0)" class="tve_click tve_editor_button tve_editor_button_success tve_right" data-ctrl="function:ext.tve_ult.state.save"><?php echo __( 'Save', TVE_Ult_Const::T ) ?></a>
	</div>
</div>