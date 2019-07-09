(function () { var require = undefined; var define = undefined; (function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var mount = document.getElementById('email-message-template-tags');
var tags = [];
var tagOpen = '[';
var tagClose = ']';

function updateAvailableEmailTags() {
	var fields = mc4wp.forms.editor.query('[name]');
	tags = ['_ALL_'];

	for (var i = 0; i < fields.length; i++) {

		var tagName = fields[i].getAttribute('name').toUpperCase();

		// strip empty arrays []
		// add in semicolon for named array keys
		tagName = tagName.replace('[]', '').replace(/\[(\w+)\]/, ':$1');

		if (tags.indexOf(tagName) < 0) {
			tags.push(tagName);
		}
	}

	mount.innerHTML = tags.map(function (tagName) {
		return '<input readonly style="background: transparent; border: 0;" onclick="this.select();" onfocus="this.select()" value="' + tagOpen + tagName + tagClose + '" />';
	}).join(' ');
}

window.addEventListener('load', function () {
	mc4wp.forms.editor.on('change', mc4wp.helpers.debounce(updateAvailableEmailTags, 1000));
	updateAvailableEmailTags();
});

},{}]},{},[1]);
 })();