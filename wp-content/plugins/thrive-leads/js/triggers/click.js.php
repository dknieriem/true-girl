<script type="text/javascript">
	(function ($) {
	$( function () {
		var event_data = <?php echo json_encode( $data ) ?>,
			_selector = <?php echo json_encode( $selector ) ?>;
		event_data.source = 'click';
		event_data.first = true;
		$( 'body' ).on( 'click', _selector, function ( event ) {
			event_data.TargetEvent = event;
			$( TL_Front ).trigger( 'showform.thriveleads', event_data );
		} );
	} );
})
	(ThriveGlobal.$j);
</script>