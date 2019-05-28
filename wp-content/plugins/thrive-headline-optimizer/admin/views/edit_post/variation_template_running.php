<div class="tho-variation-progress-container tho-variation-control-progress">
	<span class="tho-variation-progress-title"><?php echo __( "Engagement rate", THO_TRANSLATE_DOMAIN ); ?></span>
	<div class="tho-variation-progress">
		<div class="tho-variation-pb-out">
			<div class="tho-variation-pb-in"
			     style="width:  <?php echo( $isTestRunning ? ( $new_percentage[ $control_id ] == 'N/A' ? 0 : $new_percentage[ $control_id ] . '%' ) : "" ); ?>">
				<div class="tho-variation-progress-bar"></div>
			</div>
		</div>
		<span class="tho-variation-counter"><?php echo $engagement_rate[ $control_id ] . ( is_numeric( $engagement_rate[ $control_id ] ) ? '%' : '' ); ?></span>
		<div class="tho-clear"></div>
	</div>
</div>


<div id='tho_headline_variation_container'>
	<?php
	if ( ! empty( $post_variations ) ):
		$index = 1;
		foreach ( $post_variations as $key => $variation ):
			?>
			<div class="tho_headline_variation">
				<div class="tho-input-container">
					<div class="tvd-input-field ">
						<input type="text" name="tho_post_variation[]" class='variation_field tho-input-field'
						       spellcheck="true" id="variation-<?php echo $key; ?>" autocomplete="off"
						       value="<?php echo htmlspecialchars( $variation, ENT_QUOTES, 'UTF-8' ); ?>" disabled="disabled"/>
					</div>
				</div>
				<div class="tho-variation-progress-container">

					<div class="tho-variation-progress">
						<div class="tho-variation-pb-out">
							<div class="tho-variation-pb-in"
							     style="width: <?php echo( $isTestRunning ? ( $new_percentage[ $key ] == 'N/A' ? 0 : $new_percentage[ $key ] . '%' ) : "" ); ?>">
								<div class="tho-variation-progress-bar"></div>
							</div>
						</div>
						<span class="tho-variation-counter"><?php echo $engagement_rate[ $key ] . ( is_numeric( $engagement_rate[ $key ] ) ? '%' : '' ); ?></span>
						<div class="tho-clear"></div>
					</div>
				</div>
				<div class="tho-clear"></div>
			</div>
			<?php
			$index ++;
		endforeach;
		$test_url = admin_url( 'admin.php?page=tho_admin_dashboard' ) . '#test/' . $runningTest->id;
		?>
		<a href='<?php echo $test_url; ?>'
		   class="page-title-action tho-blue-btn"><?php echo __( "View test", THO_TRANSLATE_DOMAIN ); ?></a>
		<?php
	endif;
	?>
</div>
<div class="tho-clear"></div>
<script>
	jQuery( "#title" ).prop( "disabled", true );
</script>
<style>
	#titlediv {
		float: left;
		width: 70%;
	}

	@media all and (min-width: 1400px) {
		#titlediv {
			width: 80%;
		}
	}
</style>