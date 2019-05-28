<?php
global $variation;
$variation_manager = new TQB_Variation_Manager( $variation['quiz_id'], $variation['page_id'] );
$intervals         = $variation_manager->get_page_variations( array( 'parent_id' => $variation['id'] ) );
if ( count( $intervals ) === 0 ) {
	return;
}
$interval_content = array();
?>

<div id="tqb-draggable-state-area" class="ui-widget-content" style="position: absolute; top:100px; left:0;">
	<p class="tqb-state-title"><?php echo __( 'Preview Mode', Thrive_Quiz_Builder::T ); ?></p>
	<div class="tqb-state-picker">
		<p class="tqb-state-option"><?php echo __( 'Choose state to preview:', Thrive_Quiz_Builder::T ); ?></p>
		<select id="tqb-state-picker">
			<?php foreach ( $intervals as $key => $value ) : ?>
				<option value="<?php echo $value['id']; ?>"><?php echo $value['post_title']; ?></option>
				<?php $interval_content[ $value['id'] ] = $value['content']; ?>
			<?php endforeach; ?>
		</select>
	</div>
	<script type="text/javascript">
		var interval_content = [];
		<?php foreach ( $intervals as $key => $value ) : ?>
		interval_content[<?php echo $value['id']; ?>] = '<?php echo preg_replace( '/\r|\n/', '', $value['content'] ); ?>';
		<?php endforeach; ?>
	</script>
</div>
