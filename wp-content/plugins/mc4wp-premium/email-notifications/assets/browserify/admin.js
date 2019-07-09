'use strict';

var mount = document.getElementById( 'email-message-template-tags' );
var tags = [];
var tagOpen = '[';
var tagClose = ']';

function updateAvailableEmailTags() {
	var fields = mc4wp.forms.editor.query('[name]');
	tags = [ '_ALL_' ];

	for( var i=0; i<fields.length; i++) {

		var tagName = fields[i].getAttribute('name').toUpperCase();

		// strip empty arrays []
		// add in semicolon for named array keys
		tagName = tagName
			.replace('[]','')
			.replace(/\[(\w+)\]/, ':$1');


		if( tags.indexOf( tagName ) < 0 ) {
			tags.push( tagName );
		}

	}

	mount.innerHTML = tags.map(function(tagName) {
		return '<input readonly style="background: transparent; border: 0;" onclick="this.select();" onfocus="this.select()" value="' + tagOpen + tagName + tagClose + '" />';
	}).join(' ');
}

window.addEventListener('load', function() {
	mc4wp.forms.editor.on('change', mc4wp.helpers.debounce(updateAvailableEmailTags, 1000 ) );
	updateAvailableEmailTags();
});