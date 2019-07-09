(function () { var require = undefined; var define = undefined; (function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

function Config(objectName) {
    this.objectName = objectName;
}

Config.prototype.get = function (k, d) {
    return window[this.objectName] !== undefined ? window[this.objectName][k] : d;
};

Config.prototype.set = function (k, v) {
    if (!window[this.objectName]) {
        window[this.objectName] = {};
    }

    window[this.objectName][k] = v;
};

module.exports = Config;

},{}],2:[function(require,module,exports){
'use strict';

function getButtonText(button) {
    return button.innerHTML ? button.innerHTML : button.value;
}

function setButtonText(button, text) {
    button.innerHTML ? button.innerHTML = text : button.value = text;
}

function Loader(formElement) {
    this.form = formElement;
    this.button = formElement.querySelector('input[type="submit"], button[type="submit"]');
    this.loadingInterval = 0;
    this.character = '\xB7';

    if (this.button) {
        this.originalButton = this.button.cloneNode(true);
    }
}

Loader.prototype.setCharacter = function (c) {
    this.character = c;
};

Loader.prototype.start = function () {
    if (this.button) {
        // loading text
        var loadingText = this.button.getAttribute('data-loading-text');
        if (loadingText) {
            setButtonText(this.button, loadingText);
            return;
        }

        // Show AJAX loader
        var styles = window.getComputedStyle(this.button);
        this.button.style.width = styles.width;
        setButtonText(this.button, this.character);
        this.loadingInterval = window.setInterval(this.tick.bind(this), 500);
    } else {
        this.form.style.opacity = '0.5';
    }
};

Loader.prototype.tick = function () {
    // count chars, start over at 5
    var text = getButtonText(this.button);
    var loadingChar = this.character;
    setButtonText(this.button, text.length >= 5 ? loadingChar : text + " " + loadingChar);
};

Loader.prototype.stop = function () {
    if (this.button) {
        this.button.style.width = this.originalButton.style.width;
        var text = getButtonText(this.originalButton);
        setButtonText(this.button, text);
        window.clearInterval(this.loadingInterval);
    } else {
        this.form.style.opacity = '';
    }
};

module.exports = Loader;

},{}],3:[function(require,module,exports){
'use strict';

var ConfigStore = require('./_config.js');
var Loader = require('./_form-loader.js');

var busy = false;
var config = new ConfigStore('mc4wp_ajax_vars');

// failsafe against including script twice
if (!config.get('ready')) {
	window.mc4wp.forms.on('submit', function (form, event) {
		// does this form have AJAX enabled?
		if (form.element.getAttribute('class').indexOf('mc4wp-ajax') < 0) {
			return;
		}

		// blur active input field
		if (document.activeElement && document.activeElement.tagName === 'INPUT') {
			document.activeElement.blur();
		}

		try {
			submit(form);
		} catch (e) {
			console.error(e);
			return true;
		}

		event.returnValue = false;
		event.preventDefault();
		return false;
	});
}

function submit(form) {
	var loader = new Loader(form.element);
	var loadingChar = config.get('loading_character');
	if (loadingChar) {
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
		request.onreadystatechange = function () {
			// are we done?
			if (this.readyState == 4) {
				clean();

				if (this.status >= 200 && this.status < 400) {
					// Request success! :-)
					try {
						var response = JSON.parse(this.responseText);
					} catch (error) {
						console.log('MailChimp for WordPress: failed to parse AJAX response.\n\nError: "' + error + '"');

						// Not good..
						form.setResponse('<div class="mc4wp-alert mc4wp-error"><p>' + config.get('error_text') + '</p></div>');
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

	function process(response) {
		trigger('submitted', form, null);

		if (response.error) {
			form.setResponse(response.error.message);
			trigger('error', form, response.error.errors);
		} else {
			var data = form.getData();

			// trigger events
			trigger('success', form, data);
			trigger(response.data.event, form, data);

			// for BC: always trigger "subscribed" event when firing "updated_subscriber" event
			if (response.data.event === 'updated_subscriber') {
				trigger('subscribed', form, data);
			}

			if (response.data.hide_fields) {
				form.element.querySelector('.mc4wp-form-fields').style.display = 'none';
			}

			// show success message
			form.setResponse(response.data.message);

			// reset form element
			form.element.reset();

			// maybe redirect to url
			if (response.data.redirect_to) {
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
	if (!busy) {
		start();
	}
}

config.set('ready', true);

},{"./_config.js":1,"./_form-loader.js":2}]},{},[3]);
 })();