jQuery( document ).ready( function () {
	jQuery( ".tvo-add-tag-autocomplete" ).select2( {
		tags: true,
		multiple: true,
		placeholder: ThriveOvation.translations.tags_select2_placeholder,
	} );
} );

// Velocity has conflicts when loaded with jQuery, this will check for it
var Vel;
if ( jQuery ) {
	Vel = jQuery.Velocity;
} else {
	Vel = Velocity;
}
