<div id='tho_headline_variation_container'>
	<?php
	if ( ! empty( $post_variations ) ):
		$index = 1;
		foreach ( $post_variations as $key => $variation ):
			?>
			<div class="tho_headline_variation">
				<div class="tho-input-container tho-input-container-large">

					<input type="text" name="tho_post_variation[]" class='variation_field tho-input-field'
					       spellcheck="true" id="variation-<?php echo $key; ?>" autocomplete="off" value="<?php echo htmlspecialchars( $variation, ENT_QUOTES, 'UTF-8' ); ?>"/>
				</div>
				<a href='javascript:void(0);' class="page-title-action tho-delete-btn tho-align-right" onclick='deleteVariation(this)'><i class="dashicons dashicons-trash"></i><?php echo __( "Delete", THO_TRANSLATE_DOMAIN ); ?></a>
			</div>
			<?php
			$index ++;
		endforeach;
	endif;
	?>
</div>
<div class="tho-clear"></div>

<div id="tho_headline_variation_template" class='hidden'>
	<div class="tho_headline_variation">
		<div class="tho-input-container tho-input-container-large">
			<input type="text" name="tho_post_variation[]" class='variation_field tho-input-field' spellcheck="true" autocomplete="off"/>
		</div>
		<a href='javascript:void(0);' class="page-title-action tho-delete-btn tho-align-right"
		   onclick='deleteVariation(this)'>
			<i class="dashicons dashicons-trash"></i>
			<?php echo __( "Delete", THO_TRANSLATE_DOMAIN ); ?>
		</a>
		<div class="tho-clear"></div>
	</div>
</div>
<a href='javascript:void(0);' <?php if ( $license ): ?> onclick='addVariation()' id='addaction' <?php endif; ?> class='alignright tho-add-variation-btn tvd-tooltipped'
   data-position="left" data-tooltip="<?php echo $license ? __( "Add some variations in order to start a headline test.", THO_TRANSLATE_DOMAIN ) : __( "Please activate your license to start using Thrive Headline Optimizer.", THO_TRANSLATE_DOMAIN ); ?>">
	<i class="dashicons dashicons-plus"></i><?php echo __( "Add new headline", THO_TRANSLATE_DOMAIN ); ?>
</a>
<div class="tho-clear"></div>

<style>

	a:focus {
		outline: 0;
	}

</style>
