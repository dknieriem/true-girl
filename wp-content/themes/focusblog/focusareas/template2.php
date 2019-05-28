<?php
$focus_area_class   = $current_attrs['_thrive_meta_focus_color'][0];
$action_link_target = ( $current_attrs['_thrive_meta_focus_new_tab'][0] == 1 ) ? "_blank" : "_self";
$wrapper_class      = ( $position == "top" ) ? "wrp" : "wrp lfa";
$section_position   = ( $position == "bottom" ) ? "farb" : "";
$btn_class          = ( empty( $current_attrs['_thrive_meta_focus_button_color'][0] ) ) ? "blue" : strtolower( $current_attrs['_thrive_meta_focus_button_color'][0] );
?>

<section class="far f2 <?php echo $focus_area_class; ?> <?php echo $section_position; ?>">
	<div class="<?php echo $wrapper_class; ?>">
		<?php if ( $current_attrs['_thrive_meta_focus_image'][0] != "" ): ?>
			<div class="left">
				<img class="f2l left" src="<?php echo $current_attrs['_thrive_meta_focus_image'][0]; ?>"
				     alt="<?php echo @$current_attrs['_thrive_meta_focus_image_alt'][0]; ?>">
				<div class="f2r left">
					<h4 class="upp"><?php echo $current_attrs['_thrive_meta_focus_heading_text'][0]; ?></h4>
					<p><?php echo nl2br( do_shortcode( $current_attrs['_thrive_meta_focus_subheading_text'][0] ) ); ?></p>
				</div>
				<div class="clear"></div>
			</div>
		<?php else: ?>
			<div class="left">
				<h4 class="upp"><?php echo $current_attrs['_thrive_meta_focus_heading_text'][0]; ?></h4>
				<p><?php echo nl2br( do_shortcode( $current_attrs['_thrive_meta_focus_subheading_text'][0] ) ); ?></p>
			</div>
		<?php endif; ?>
		<div class="right">
			<a href="<?php echo $current_attrs['_thrive_meta_focus_button_link'][0]; ?>"
			   class="btn <?php echo $btn_class; ?>" target="<?php echo $action_link_target; ?>">
				<span class="fbt"><?php echo $current_attrs['_thrive_meta_focus_button_text'][0]; ?></span>
			</a>
		</div>
		<div class="clear"></div>
	</div>
</section>
