'use strict';

var ConfigStore = require('./_config.js');
var Loader = require('./_form-loader.js');

var busy = false;
var config = new ConfigStore('mc4wp_ajax_vars');

// failsafe against including script twice
if( ! config.get('ready') ) {
	window.mc4wp.forms.on('submit', function( form, event ) {
		// does this form have AJAX enabled?
		if( form.element.getAttribute('class').indexOf('mc4wp-ajax') < 0 ) {
			return;
		}

		// blur active input field
		if(document.activeElement && document.activeElement.tagName === 'INPUT') {
			document.activeElement.blur();
		}

		try{
			submit(form);
		} catch(e) {
			console.error(e);
			return true;
		}

		event.returnValue = false;
		event.preventDefault();
		return false;
	});
}

function submit( form ) {
	var loader = new Loader(form.element);
	var loadingChar = config.get('loading_character');
	if( loadingChar ) {
		loader.setCharacter(loadingChar);
	}

	function start() {
		// Clear possible errors from previous submit
		form.setResponse('');
		loader.start();
		fire();
	}

	function fire() {
		// prepare request
		busy = true;
		var request = new XMLHttpRequest();
		request.onreadystatechange = function() {
			// are we done?
			if (this.readyState == 4) {
				clean();

				if (this.status >= 200 && this.status < 400) {
					// Request success! :-)
					try {
						var response = JSON.parse(this.responseText);
					} catch(error) {
						console.log( 'MailChimp for WordPress: failed to parse AJAX response.\n\nError: "' + error + '"' );

						// Not good..
						form.setResponse('<div class="mc4wp-alert mc4wp-error"><p>'+ config.get('error_text') + '</p></div>');
						return;
					}

					process(response);
				} else {
					// Error :(
					console.log(this.responseText);
				}
			}
		};
		request.open('POST', config.get('ajax_url'), true);
		request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		request.send(form.getSerializedData());
		request = null;
	}

	function process( response ) {
		trigger('submitted', form, null);

		if( response.error ) {
			form.setResponse(response.error.message);
			trigger('error', form, response.error.errors);
		} else {
			var data  = form.getData();

			// trigger events
			trigger('success', form, data);
			trigger( response.data.event, form, data);

			// for BC: always trigger "subscribed" event when firing "updated_subscriber" event
			if( response.data.event === 'updated_subscriber' ) {
                trigger('subscribed', form, data);
			}

			if( response.data.hide_fields ) {
				form.element.querySelector('.mc4wp-form-fields').style.display = 'none';
			}

			// show success message
			form.setResponse(response.data.message);

			// reset form element
			form.element.reset();

			// maybe redirect to url
			if( response.data.redirect_to ) {
				window.location.href = response.data.redirect_to;
			}
		}
	}

	function trigger(event, form, data) {
		window.mc4wp.forms.trigger(event, [form, data]);
		window.mc4wp.forms.trigger(form.id + "." + event, [form, data]);
	}

	function clean() {
		loader.stop();
		busy = false;
	}

	// let's do this!
	if( ! busy ) {
		start();
	}
}

config.set('ready', true);
