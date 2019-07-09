'use strict';

var Option = require('./_option.js'),
	$ = window.jQuery;

function lightenColor(col, amt) {

	var usePound = false;

	if (col[0] == "#") {
		col = col.slice(1);
		usePound = true;
	}

	var num = parseInt(col,16);

	var r = (num >> 16) + amt;

	if (r > 255) r = 255;
	else if  (r < 0) r = 0;

	var b = ((num >> 8) & 0x00FF) + amt;

	if (b > 255) b = 255;
	else if  (b < 0) b = 0;

	var g = (num & 0x0000FF) + amt;

	if (g > 255) g = 255;
	else if (g < 0) g = 0;

	return (usePound?"#":"") + String("000000" + (g | (b << 8) | (r << 16)).toString(16)).slice(-6);
}


var FormPreview = function(context) {
	var $context = $(context),
		$elements;

	// create option elements
	var options = createOptions();

	// attach events
	$(".mc4wp-option").on('input change', applyStyles);
	$('.color-field').wpColorPicker({
		change: function() {
			window.setTimeout(applyStyles, 10);
		},
		clear: applyStyles
	});

	// initialize form preview
	function init() {
		var $form = $context.contents().find('.mc4wp-form');
		var $fields = $form.find('.mc4wp-form-fields');

		$elements = {
			form: $form,
			labels: $fields.find('label'),
			fields: $fields.find('input[type="text"], input[type="email"], input[type="url"], input[type="number"], input[type="date"], select, textarea'),
			choices: $fields.find('input[type="radio"], input[type="checkbox"]'),
			buttons: $fields.find('input[type="submit"], input[type="button"], button'),
			messages: $form.find('.mc4wp-alert'),
			css: $context.contents().find('#custom-css')
		};

		// apply custom styles to fields (focus)
		$elements.fields.focus(setFieldFocusStyles);
		$elements.fields.focusout(setDefaultFieldStyles);

		// apply custom styles to buttons (hover)
		$elements.buttons.hover(setButtonHoverStyles, setDefaultButtonStyles);
	}

	// create option elements from HTML elements
	function createOptions() {
		var optionElements = document.querySelectorAll('.mc4wp-option');
		var options = {};

		for( var i=0; i<optionElements.length; i++ ) {
			options[ optionElements[i].id ] = new Option( optionElements[i] );
		}

		return options;
	}

	function clearStyles() {
		$elements.form.removeAttr('style');
		$elements.labels.removeAttr('style');
		$elements.fields.removeAttr('style');
		$elements.buttons.removeAttr('style');
		$elements.choices.removeAttr('style');
		$elements.messages.removeAttr('style');
	}

	function applyStyles() {

		$elements.choices.css({
			'display': 'inline-block',
			'margin-right': '6px'
		});

		$elements.buttons.css({
			"text-align": "center",
			"cursor": "pointer",
			"padding": "6px 12px",
			"text-shadow": "none",
			"box-sizing": "border-box",
			"line-height": "normal",
			"vertical-align": "top"
		});

		// apply custom styles to form
		$elements.form.css({
			'max-width': options["form-width"].getPxOrPercentageValue(),
			'text-align': options["form-text-align"].getValue(),
			'font-size': options["form-font-size"].getPxValue(),
			"color": options["form-font-color"].getColorValue(),
			"background-color": options["form-background-color"].getColorValue(),
			"border-color": options["form-border-color"].getColorValue(),
			"border-width": options["form-border-width"].getPxValue(),
			"padding": options["form-padding"].getPxValue()
		});

		// responsive form width
		if( options["form-width"].getValue().length > 0 ) {
			$elements.form.css('width', '100%');
		}

		// set background image (if set, otherwise reset)
		if( options["form-background-image"].getValue().length > 0 ) {
			$elements.form.css('background-image', 'url("' + options["form-background-image"].getValue() + '")');

			
			var bgRepeat = options["form-background-repeat"].getValue();
			var property = (['cover'].indexOf(bgRepeat) > -1) ? 'background-size' : 'background-repeat';
			$elements.form.css(property, bgRepeat);
		} else {
			$elements.form.css('background-image', 'initial');
			$elements.form.css('background-repeat','');
			$elements.form.css('background-size','');
		}

		if( options["form-border-width"].getValue() > 0 ) {
			$elements.form.css( 'border-style', 'solid' );
		}

		// apply custom styles to labels
		$elements.labels.css({
			"margin-bottom": "6px",
			"box-sizing": "border-box",
			"vertical-align": "top",
			"color": options["labels-font-color"].getColorValue(),
			"font-size": options["labels-font-size"].getPxValue(),
			"display": options["labels-display"].getValue(),
			"max-width": options["labels-width"].getPxOrPercentageValue()
		});

		// responsive label width
		if( options["labels-width"].getValue().length > 0 ) {
			$elements.labels.css('width', '100%');
		}

		// reset font style of <span> elements inside <label> elements
		$elements.labels.find('span').css('font-weight', 'normal' );

		// only set label text style if it's set
		var labelsFontStyle = options["labels-font-style"].getValue();
		if( labelsFontStyle.length > 0 ) {
			$elements.labels.css({
				"font-weight": (labelsFontStyle == 'bold' || labelsFontStyle == 'bolditalic') ? 'bold' : 'normal',
				"font-style": (labelsFontStyle == 'italic' || labelsFontStyle == 'bolditalic') ? 'italic' : 'normal'
			});
		}

		// apply custom styles to inputs
		$elements.fields.css({
			"padding": '6px 12px',
			"margin-bottom": "6px",
			"box-sizing": "border-box",
			"vertical-align": "top",
			"border-width": options["fields-border-width"].getPxValue(),
			"border-color": options["fields-border-color"].getColorValue(),
			"border-radius": options["fields-border-radius"].getPxValue(),
			"display": options["fields-display"].getValue(),
			"max-width": options["fields-width"].getPxOrPercentageValue(),
			"height": options["fields-height"].getPxValue()
		});

		// responsive field width
		if( options["fields-width"].getValue().length > 0 ) {
			$elements.fields.css('width', '100%');
		}

		// apply custom styles to buttons
		$elements.buttons.css({
			'border-width': options["buttons-border-width"].getPxValue(),
			'border-color': options["buttons-border-color"].getColorValue(),
			"border-radius": options["buttons-border-radius"].getPxValue(),
			'max-width': options["buttons-width"].getValue(),
			'height': options["buttons-height"].getPxValue(),
			'background-color': options["buttons-background-color"].getColorValue(),
			'color': options["buttons-font-color"].getColorValue(),
			'font-size': options["buttons-font-size"].getPxValue()
		});

		// responsive buttons width
		if( options["buttons-width"].getValue().length ) {
			$elements.buttons.css('width', '100%');
		}

		// add border style if border-width is set and bigger than 0
		if( options["buttons-border-width"].getValue() > 0 ) {
			$elements.buttons.css( 'border-style', 'solid' );
		}

		// add background reset if custom button background was set
		if( options["buttons-background-color"].getColorValue().length ) {
			$elements.buttons.css({
				"background-image": "none",
				"filter": "none"
			});

			// calculate hover color
			var hoverColor = lightenColor( options["buttons-background-color"].getColorValue(), -20 );
			options["buttons-hover-background-color"].setValue(hoverColor);
		} else {
			options["buttons-hover-background-color"].setValue('');
		}

		if( options["buttons-border-color"].getColorValue().length ) {
			var hoverColor = lightenColor( options["buttons-border-color"].getColorValue(), -20 );
			options["buttons-hover-border-color"].setValue(hoverColor);
		} else {
			options["buttons-hover-border-color"].setValue('');
		}

		// apply custom styles to messages
		$elements.messages.filter('.mc4wp-success').css({
			'color': options["messages-font-color-success"].getColorValue()
		});

		$elements.messages.filter('.mc4wp-error').css({
			'color': options["messages-font-color-error"].getColorValue()
		});

		// print custom css in container element
		$elements.css.html(options["manual-css"].getValue());
	}

	function setButtonHoverStyles() {
		// calculate darker color
		$elements.buttons.css('background-color', options["buttons-hover-background-color"].getColorValue() );
		$elements.buttons.css('border-color', options["buttons-hover-border-color"].getColorValue() );
	}

	function setDefaultButtonStyles() {
		$elements.buttons.css({
			'border-color': options["buttons-border-color"].getColorValue(),
			'background-color': options["buttons-background-color"].getColorValue()
		});
	}

	function setFieldFocusStyles() {
		if( options["fields-focus-outline-color"].getColorValue().length ) {
			$elements.fields.css('outline', '2px solid ' + options["fields-focus-outline-color"].getColorValue() );
		} else {
			setDefaultFieldStyles();
		}
	}

	function setDefaultFieldStyles() {
		$elements.fields.css('outline', '' );
	}

	return {
		init: init,
		applyStyles: applyStyles
	}

};


module.exports = FormPreview;
