<p class="tqb-import-content-title">Import content</p>
<p class="tqb-import-content-description">Select the state you want to bring content from:</p>

<?php
if ( empty( $_POST['tqb_child_variation_id'] ) || ! is_numeric( $_POST['tqb_child_variation_id'] ) ) {
	exit( 'Not allowed! Empty child variation id!' );
}

$variation          = tqb_get_variation( $_REQUEST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] );
$child_variation_id = $_POST['tqb_child_variation_id'];


$variation_manager = new TQB_Variation_Manager( $variation['quiz_id'], $variation['page_id'] );
$child_variations  = $variation_manager->get_page_variations( array( 'parent_id' => $variation['id'] ) );
?>
<select id="tqb-import-from">
	<?php
	foreach ( $child_variations as $child ) :
		if ( $child['id'] != $child_variation_id ) :
			?>
			<option value="<?php echo $child['id']; ?>"><?php echo $child['post_title']; ?></option>
			<?php
		endif;
	endforeach;
	?>
</select>

<div class="tqb-tcb-row">
	<div class="tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-small tve_click tvd-modal-close tve_editor_button tve_right tve_click"
	     data-ctrl="function:ext.tqb.state.import_content"
	     data-tqb-import-to="<?php echo $child_variation_id; ?>">
		<div class="tve_update">
			<?php echo __( 'Import', Thrive_Quiz_Builder::T ) ?>
		</div>
	</div>
</div>
<style>
	#tve_lightbox_buttons {
		display: none !important;
	}
</style>
