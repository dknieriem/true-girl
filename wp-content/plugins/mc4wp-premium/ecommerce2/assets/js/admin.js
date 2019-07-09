(function () { var require = undefined; var define = undefined; (function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

function ProgressBar(element, count) {
	var wrapper = element,
	    bar = document.createElement('div'),
	    step_size = 100 / count,
	    progress = 0;

	wrapper.style.height = "40px";
	wrapper.style.width = "100%";
	wrapper.style.border = "1px solid #ccc";
	wrapper.style.lineHeight = "40px";

	bar.style.boxSizing = 'border-box';
	bar.style.backgroundColor = '#cc4444';
	bar.style.textAlign = 'center';
	bar.style.fontWeight = 'bold';
	bar.style.height = "100%";
	bar.style.color = 'white';
	bar.style.fontSize = '16px';
	bar.style.width = progress + "%";
	wrapper.appendChild(bar);

	function tick(ticks) {
		if (done()) {
			return;
		}

		ticks = ticks === undefined ? 1 : ticks;
		progress += step_size * ticks;
		bar.style.width = progress + "%";

		bar.innerText = parseInt(progress) + "%";

		if (done()) {
			bar.innerText = 'Done!';
		}
	}

	function done() {
		return progress >= 100;
	}

	return {
		'tick': tick,
		'done': done
	};
}

module.exports = ProgressBar;

},{}],2:[function(require,module,exports){
'use strict';

function request(url, options) {

	var request = new XMLHttpRequest();
	request.onreadystatechange = function () {
		if (this.readyState === 4) {
			if (this.status >= 200 && this.status < 400) {
				options.onSuccess && options.onSuccess(this.responseText);
			} else {
				options.onError && options.onError(this.status, this.responseText);
			}
		}
	};
	request.open(options.method || 'GET', url, true);

	if (options.method && options.method.toUpperCase() === 'POST') {
		request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
	}

	request.send(options.data || {});
	return request;
}

module.exports = request;

},{}],3:[function(require,module,exports){
'use strict';

var ProgressBar = require('./_progress-bar.js');
var request = require('./_request.js');
var count = mc4wp_ecommerce.untracked_order_count;
var form = document.getElementById('add-untracked-orders-form');
var progressBarMount = document.getElementById('add-untracked-orders-progress');
var progress_bar, progress_poll, worker;

// hook into form submit
if (form) {
	form.addEventListener('submit', start);
}

function start(e) {

	// prevent default form submit
	e.preventDefault();

	var button = form.querySelector('input[type="submit"]');
	button.setAttribute('disabled', true);

	// init progress bar
	progress_bar = new ProgressBar(progressBarMount, count);
	progress_poll = window.setTimeout(fetchProgress, 500);
	work();
}

function work() {
	var limit = parseInt(form.elements["limit"].value);
	var offset = parseInt(form.elements["offset"].value);
	var url = ajaxurl + "?action=mc4wp_ecommerce_add_untracked_orders&offset=" + offset + "&limit=" + limit;
	var previousCount = count;

	worker = request(url, {
		onSuccess: function onSuccess(data) {
			updateProgress(data);

			if (previousCount <= data) {
				// We're not making progress..
				var textElement = document.createElement('p');
				textElement.style.color = 'red';
				textElement.innerHTML = "We're stuck. Please <a href=\"admin.php?page=mailchimp-for-wp-other\">check the debug log</a> for errors.";
				progressBarMount.parentNode.appendChild(textElement);
			} else if (data > 0) {
				// Keep going if there's more
				work();
			}
		},

		onError: function onError(code, response) {
			// if we got a 504 Gateway Timeout, try again.
			if (code == 504) {
				work();
			}
		}
	});
}

function updateProgress(new_count) {
	progress_bar.tick(count - new_count);
	count = new_count;
}

function fetchProgress() {
	if (progress_bar.done()) {

		// refresh page
		window.setTimeout(function () {
			window.location.reload();
		}, 2500);

		return;
	}

	var url = ajaxurl + "?action=mc4wp_ecommerce_get_untracked_orders_count";
	request(url, {
		onSuccess: function onSuccess(data) {
			updateProgress(data);
			window.setTimeout(fetchProgress, 2000);
		}
	});
}

},{"./_progress-bar.js":1,"./_request.js":2}]},{},[3]);
 })();