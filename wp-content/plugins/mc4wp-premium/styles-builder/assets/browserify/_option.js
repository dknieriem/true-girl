'use strict';

var Option = function( element ) {
	this.element = element;
	this.$element = window.jQuery(element);
};

Option.prototype.getColorValue = function() {
	this.element.value = this.element.value.trim();

	if( this.element.value.length > 0 ) {
		if( this.element.className.indexOf('wp-color-picker') !== -1) {
			return this.$element.wpColorPicker('color');
		} else {
			return this.element.value;
		}
	}

	return '';
};

Option.prototype.getPxOrPercentageValue = function(fallback) {
	var value = this.element.value.trim();

	if( value.length > 0 ) {
		if( value.substring(value.length-2, value.length) !== 'px' && value.substring(value.length-1, value.length) !== '%') {
			value = parseInt(value) + 'px';
		}
		return value;
	}

	return fallback || '';
};

Option.prototype.getPxValue = function(fallback) {
	this.element.value = this.element.value.trim();

	if( this.element.value.length > 0 ) {
		return parseInt( this.element.value ) + "px";
	}

	return fallback || '';
};

Option.prototype.getValue = function(fallback) {
	this.element.value = this.element.value.trim();

	if( this.element.value.length > 0 ) {
		return this.element.value;
	}

	return fallback || '';
};

Option.prototype.clear = function() {
	this.element.value = '';
};

Option.prototype.setValue = function(value) {
	this.element.value = value;
};

module.exports = Option;