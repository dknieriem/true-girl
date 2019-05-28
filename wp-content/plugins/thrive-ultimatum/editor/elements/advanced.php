<div class="cp_draggable tve_option_separator tve_clearfix" title="<?php echo __( "Thrive Ultimatum Shortcodes", TVE_Ult_Const::T ) ?>">
	<div class="tve_icm tve-ic-my-library-books tve_left"></div>
	<span class="tve_expanded tve_left"><?php echo __( "Ultimatum Shortcodes", TVE_Ult_Const::T ) ?></span>
	<span class="tve_caret tve_icm tve_sub_btn tve_right tve_expanded"></span>
	<div class="tve_clear"></div>
	<div class="tve_sub_btn">
		<div class="tve_sub">
			<ul>
				<?php if ( count( $tu_campaigns ) ) : ?>
					<li class="cp_draggable" data-elem="sc_thrive_ultimatum_shortcode">
						<div class="tve_icm tve-ic-plus"></div>
						<?php echo __( 'Countdown', TVE_Ult_Const::T ); ?>
					</li>
				<?php else: ?>
					<li><?php echo __( 'No countdown available', TVE_Ult_Const::T ) ?></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>