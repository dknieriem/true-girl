<?php
$focus_area_class = $current_attrs['_thrive_meta_focus_color'][0];
$wrapper_class    = ( $position == "top" ) ? "wrp" : "wrp lfa";
$section_position = ( $position == "bottom" ) ? "farb" : "";
$btn_class        = ( empty( $current_attrs['_thrive_meta_focus_button_color'][0] ) ) ? "blue" : strtolower( $current_attrs['_thrive_meta_focus_button_color'][0] );
?>

<section class="far f4 <?php echo $focus_area_class; ?> <?php echo $section_position; ?>">
	<div class="<?php echo $wrapper_class; ?>">
		<div class="left">
			<h4 class="upp"><?php echo $current_attrs['_thrive_meta_focus_heading_text'][0]; ?></h4>

			<p>
				<?php echo nl2br( do_shortcode( $current_attrs['_thrive_meta_focus_subheading_text'][0] ) ); ?>
			</p>
		</div>

		<?php if ( ! empty( $optin_id ) ) : ?>
			<div class="right">
				<div class="frm">
					<form action="<?php echo $optinFormAction; ?>" method="<?php echo $optinFormMethod ?>">

						<?php echo $optinHiddenInputs; ?>

						<?php echo $optinNotVisibleInputs; ?>

						<?php if ( $optinFieldsArray ): ?>
							<?php foreach ( $optinFieldsArray as $name_attr => $field_label ): ?>
								<?php echo Thrive_OptIn::getInstance()->getInputHtml( $name_attr, $field_label ); ?>
							<?php endforeach; ?>
						<?php endif; ?>

						<div class="btn <?php echo $btn_class; ?>">
							<input type="submit" class="focus_submit fbt"
							       value="<?php echo $current_attrs['_thrive_meta_focus_button_text'][0]; ?>"/>
						</div>
					</form>
				</div>
				<?php if ( $current_attrs['_thrive_meta_focus_spam_text'][0] != "" ): ?>
					<p><?php echo $current_attrs['_thrive_meta_focus_spam_text'][0]; ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<div class="clear"></div>
	</div>
</section>
