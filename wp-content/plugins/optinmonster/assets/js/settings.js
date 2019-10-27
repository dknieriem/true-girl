/* ==========================================================
 * edit.js
 * ==========================================================
 * Copyright 2018 Awesome Motive.
 * https://awesomemotive.com
 * ========================================================== */
jQuery(document).ready(function ($) {

	// Initialize Select2.
	omapiSelect();

	// Hide/show any state specific settings.
	omapiToggleSettings();

	// Support Toggles on content
	omapiSettingsToggle();

	// Confirm resetting settings.
	omapiResetSettings();

	// Copy to clipboard Loading
	omapiClipboard();

	// Recognize Copy to Clipboard Buttons
	omapiCopytoClipboardBtn();

	// Support PDF generation
	omapiBuildSupportPDF();

	// Run Tooltip lib on any tooltips
	omapiFindTooltips();

	// Add "Connect to OptinMonster" functionality
	omapiHandleApiKeyConnect();

	/**
	 * Add the listeners necessary for the connect to OptinMonster button
	 */
	function omapiHandleApiKeyConnect() {
		function updateForm(val, $btn) {
			var field = document.getElementById('omapi-field-apikey');
			field.value = val;

			// Start spinner.
			$('.om-api-key-spinner').remove();
			$btn.after('<div class="om-api-key-spinner spinner is-active" style="float: none;margin-top: 13px;"></div>');

			HTMLFormElement.prototype.submit.call(field.form)
		}

		$('#omapiAuthorizeButton').click(function (e) {
			e.preventDefault();
			var w = window.open(OMAPI.app_url + 'wordpress/connect/', '_blank', 'location=no,width=500,height=730,scrollbars=0');
			w.focus();
		});

		window.addEventListener('message', function(msg) {
			if (msg.origin.replace(/\/$/, '') !== OMAPI.app_url.replace(/\/$/, '')) {
				return;
			}

			if ( ! msg.data || 'string' !== typeof msg.data ) {
				console.error('Messages from "' + OMAPI.app_url + '" must contain an api key string.');
				return;
			}

			updateForm(msg.data, $('#omapiAuthorizeButton'));
		});

		// Also initialize the "Click Here to enter an API Key" link
		$('#omapiShowApiKey').click(function (e) {
			e.preventDefault();
			$('#omapi-form-api .omapi-hidden').removeClass('omapi-hidden');
			$('#omapi-field-apikey').focus().select();

		});

		// Add the listener for disconnecting the API Key.
		$('#omapiDisconnectButton').click(function (e) {
			e.preventDefault();
			updateForm('', $(this));
		});
	}

	/**
	 * Dynamic Toggle functionality
	 */
	function omapiSettingsToggle() {

		$('.omapi-ui-toggle-controller').click(function (e) {
			var thisToggle = e.currentTarget;
			$(thisToggle).toggleClass("toggled");
			$(thisToggle).next(".omapi-ui-toggle-content").toggleClass("visible");
		});

	}

	/**
	 * Confirms the settings reset for the active tab.
	 *
	 * @since 1.0.0
	 */
	function omapiResetSettings() {
		$(document).on('click', 'input[name=reset]', function (e) {
			return confirm(omapi.confirm);
		});
	}

	/**
	 * Toggles the shortcode list setting.
	 *
	 * @since 1.1.4
	 */
	function omapiToggleSettings() {
		var shortcode_val = $('#omapi-field-shortcode').is(':checked');
		if (!shortcode_val) {
			$('.omapi-field-box-shortcode_output').hide();
		}
		$(document).on('change', '#omapi-field-shortcode', function (e) {
			if ($(this).is(':checked')) {
				$('.omapi-field-box-shortcode_output').show(0);
			} else {
				$('.omapi-field-box-shortcode_output').hide(0);
			}
		});

		var mailpoet_val = $('#omapi-field-mailpoet').is(':checked');
		if (!mailpoet_val) {
			$('.omapi-field-box-mailpoet_list').hide();
		}
		$(document).on('change', '#omapi-field-mailpoet', function (e) {
			if ($(this).is(':checked')) {
				$('.omapi-field-box-mailpoet_list').show(0);
			} else {
				$('.omapi-field-box-mailpoet_list').hide(0);
			}
		});

		var automatic_val = $('#omapi-field-automatic').is(':checked');
		if (automatic_val) {
			$('.omapi-field-box-automatic_shortcode').hide();
		}
		$(document).on('change', '#omapi-field-automatic', function (e) {
			if ($(this).is(':checked')) {
				$('.omapi-field-box-automatic_shortcode').hide(0);
			} else {
				$('.omapi-field-box-automatic_shortcode').show(0);
			}
		});
	}

	/**
	 * Initializes the Select2 replacement for select fields.
	 *
	 * @since 1.0.0
	 */
	function omapiSelect() {
		$('.omapi-select').each(function (i, el) {
			var data = $(this).attr('id').indexOf('taxonomies') > -1 ? OMAPI.tags : OMAPI.posts;
			$(this).select2({
				minimumInputLength: 1,
				multiple: true,
				data: data,
				initSelection: function (el, cb) {
					var ids = $(el).val();
					ids = ids.split(',');
					items = data.filter(function(d) {
						return ids.indexOf(d.id) > -1;
					});
					cb(items);
				}
			}).on('change select2-removed', function () {});
		});
	}

	/**
	 * Generate support PDF from localized data
	 *
	 * @since 1.1.5
	 */
	function omapiBuildSupportPDF() {
		var selector = $('#js--omapi-support-pdf');

		selector.click(function (e) {
			e.preventDefault();

			var doc = new jsPDF('p', 'mm', 'letter');

			var supportData = omapi.supportData;
			var serverData = supportData.server;
			var optinData = supportData.optins;

			// Doc Title
			doc.text(10, 10, 'OptinMonster Support Assistance');

			// Server Info
			i = 10;
			$.each(serverData, function (key, value) {
				i += 10;
				doc.text(10, i, key + ' : ' + value);
			});

			// Optin Info
			$.each(optinData, function (key, value) {

				//Move down 10mm
				i = 10;
				// Add a new page
				doc.addPage();
				//Title as slug
				doc.text(10, 10, key);
				$.each(value, function (key, value) {

					// Keep from outputing ugly Object text
					output = ( $.isPlainObject(value) ? '' : value );
					// new line
					i += 10;
					doc.text(10, i, key + ' : ' + output);
					//Output any object data from the value
					if ($.isPlainObject(value)) {
						$.each(value, function (key, value) {
							i += 10;
							doc.text(20, i, key + ' : ' + value);
						});
					}
				});

			});

			// Save the PDF
			doc.save('OMSupportHelp.pdf');

		});
	}

	/**
	 * Clipboard Helpers
	 *
	 * @since 1.1.5
	 */
	function omapiClipboard() {
		var ompaiClipboard = new Clipboard('.omapi-copy-button');

		ompaiClipboard.on('success', function (e) {
			setTooltip(e.trigger, 'Copied to Clipboard!');
			hideTooltip(e.trigger);
		});
		ompaiClipboard.on('error', function (e) {
			var fallbackMessage = '';

			if(/iPhone|iPad/i.test(navigator.userAgent)) {
				fallbackMessage = 'Unable to Copy on this device';
			}
			else if (/Mac/i.test(navigator.userAgent)) {
				fallbackMessage = 'Press ⌘-C to Copy';
			}
			else {
				fallbackMessage = 'Press Ctrl-C to Copy';
			}
			setTooltip(e.trigger, fallbackMessage);
			hideTooltip(e.trigger);
		});
	}

	/**
	 * Standardize Copy to clipboard button
	 *
	 * @since 1.1.5
	 */
	function omapiCopytoClipboardBtn() {
		$('omapi-copy-button').tooltip({
			trigger: 'click',
			placement: 'top',

		});
	}
	/**
	 * Set BS Tooltip based on Clipboard data
	 *
	 * @since 1.1.5
	 * @param btn
	 * @param message
	 */
	function setTooltip(btn, message) {
		$(btn).attr('data-original-title', message)
			.tooltip('show');
	}

	/**
	 * Remove tooltip after Clipboard message shown
	 *
	 * @since 1.1.5
	 * @param btn
	 */
	function hideTooltip(btn) {
		setTimeout(function() {
			$(btn).tooltip('destroy');
		}, 2000);
	}

	function omapiFindTooltips() {
		$('[data-toggle="tooltip"]').tooltip()
	}

});
