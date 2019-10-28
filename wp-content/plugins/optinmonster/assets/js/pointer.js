jQuery(document).ready( function($) {
	omapi_open_pointer();

	// Trigger a pointer close when clicking on the link in the pointer
	$('#omPointerButton, .om-pointer-close-link').click(function () {
        omapiPointer;
        $(omapiPointer.target).pointer('close');
	});

    function omapi_open_pointer() {
        pointer = omapiPointer;
        options = $.extend(pointer.options, {
            close: function() {
                $.post( ajaxurl, {
                    pointer: pointer.id,
                    action: 'dismiss-wp-pointer'
                });
            }
        });

        $(pointer.target).pointer( options ).pointer('open');
    }
});
