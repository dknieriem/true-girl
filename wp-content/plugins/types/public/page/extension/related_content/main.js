var Types = Types || {};

// the head.js object
Types.head = Types.head || head;

Types.page = Types.page || {};

// Everything related to this page.
Types.page.extension = Types.page.extension || {};
Types.page.extension.relatedContent = {};
Types.page.extension.relatedContent.viewmodels = {};
Types.page.extension.relatedContent.strings = {};


/**
 * Metabox controller class.
 *
 * Handles metabox initialization.
 *
 * @constructor
 * @since m2m
 */
Types.page.extension.relatedContent.Class = function() {

	var self = this;

	// Extend the generic listing metabox controller.
	Toolset.Gui.ListingPage.call(self);

	// Methods required by the generic listing metabox controller
	//
	//

	/**
	 * Parent override. The whole initialization sequence.
	 *
	 * @since m2m
	 */
	self.init = function() {
		var data = self.getModelData();
		if ( typeof data[0] !== 'undefined' ) {
			self.beforeInit();

			self.initTemplates();
			self.initKnockout();

			self.loadDependencies(function() {
				self.initMainViewModel();

				self.afterInit();
			});
		}

	};


	/**
	 * Initialize everything AJAX-related here.
	 *
	 * @since 2.0
	 */
	self.initAjax = function() {

		/**
		 * Perform an AJAX call on field definitions.
		 *
		 * @param {string} relatedContentAction Name of the action, see Types_Ajax::callback_field_control_action() for details.
		 * @param {string} nonce Name of the nonce for this action.
		 * @param {object} data Custom action-specific data.
		 * @param {function} successCallback Callback to be used after AJAX call is completed. It will get two parameters,
		 *     the complete AJAX response and the 'data' element for convenience.
		 * @param {function} [failCallback] Analogous to successCallback for the case of failure. If missing,
		 *     successCallback will be used instead.
		 *
		 * @since 2.0
		 */
		Types.page.extension.relatedContent.doAjax = function (relatedContentAction, nonce, data, successCallback, failCallback) {

			var ajaxData = {
				action: Types.page.extension.relatedContent.ajaxInfo.actionName,
                skip_capability_check: true,
				related_content_action: relatedContentAction,
				wpnonce: nonce
			};

			// If data is a jQuery.serializeArray array
			if (_.isArray(data)) {
				if (_.has(data[0], 'name') && _.has(data[0], 'value')) {
					_.each(data, function(item) {
						if ( item.name.match(/\[\]/) ) {
							if ( typeof ajaxData[item.name] === 'undefined' ) {
								ajaxData[item.name] = [];
							}
							ajaxData[item.name].push( item.value );
						} else {
							ajaxData[item.name] = item.value;
						}
					});
				// If data is pair key=>value
				}
			} else if (_.isObject(data)) {
				for(var key in data) {
					ajaxData[key] = data[key];
				}
			}

			// AJAX call is missing the WPML language information, we need to pass it along.
			var currentLang = WPV_Toolset.Utils.getParameterByName('lang');
			if( null !== currentLang ) {
				ajaxData['current_language'] = currentLang;
			}

			if (typeof(failCallback) === 'undefined') {
				failCallback = successCallback;
			}

			jQuery.ajax({
				async: true,
				type: 'POST',
				url: ajaxurl,
				data: ajaxData,

				success: function (originalResponse) {
					var response = WPV_Toolset.Utils.Ajax.parseResponse(originalResponse);
					self.debug('AJAX response', ajaxData, originalResponse);

					if (response.success) {
						successCallback(response, response.data || {});
					} else {
						failCallback(response, response.data || {});
					}
				},

				error: function (ajaxContext) {
					console.log('Error:', ajaxContext.responseText);
					failCallback({success: false, data: {}}, {});
				}
			});

		}
	};


	self.beforeInit = function () {
		self.modelData = self.getModelData();

		self.initStaticData(self.modelData);
		self.initAjax();

	};


	self.loadDependencies = function (nextStep) {
	    var typesVersion = Types.page.extension.relatedContent.typesVersion;
		// Continue after loading the view of the listing table.
		Types.head.load(
			Types.page.extension.relatedContent.jsPath + '/viewmodels/ListingViewModel.js?ver=' + typesVersion,
			Types.page.extension.relatedContent.jsPath + '/viewmodels/RelatedContentViewModel.js?ver=' + typesVersion,
			Types.page.extension.relatedContent.jsPath + '/viewmodels/DisconnectDialogViewModel.js?ver=' + typesVersion,
			Types.page.extension.relatedContent.jsPath + '/viewmodels/AddNewDialogViewModel.js?ver=' + typesVersion,
			Types.page.extension.relatedContent.jsPath + '/viewmodels/ConnectExistingDialogViewModel.js?ver=' + typesVersion,
			Types.page.extension.relatedContent.jsPath + '/viewmodels/TranslatableContentDialogViewModel.js?ver=' + typesVersion,
			Types.page.extension.relatedContent.jsPath + '/viewmodels/SelectFieldsDisplayedDialogViewModel.js?ver=' + typesVersion,
			nextStep
		);
	};


	/**************************************
	 *
	 * Toolset.Gui.ListingPage Overrides
	 *
	 **************************************/

	/**
	 * Read model data from PHP passed in a standard way through Toolset_Gui_Base and Twig.
	 *
	 * The result will be stored in self.modelData.
	 *
	 * @param {string} [selector] CSS selector to target the element with the encoded model data. Defaults to
	 *     the Toolset GUI Base default value, so better leave it alone. It is taken into account only first time
	 *     this function is called.
	 *
	 * @returns {*} The loaded model data.
	 *
	 * @since 2.2
	 */
	self.getModelData = function(selector) {
		if(!_.has(this, 'modelData')) {
			if(typeof(selector) === 'undefined') {
				// Several IDs because there are several metaboxes
				selector = '[id=toolset_model_data]';
			}
			self.modelData = [];
			jQuery(selector).each(function() {
				self.modelData.push(jQuery.parseJSON(WPV_Toolset.Utils.editor_decode64(jQuery(this).html())));
			});
		}

		return self.modelData;
	};


	/**
	 * Get the jQuery element that wraps the whole page.
	 * Overrides Toolset.Gui.AbstractPage method
	 *
	 * @returns {*}
	 * @since m2m
	 */
	self.getPageContent = function() {
		return jQuery('[id=toolset-page-content]');
	};


	/**
	 * Initialize the main viewmodel.
	 * Overrides Toolset.Gui.ListingPage
	 *
	 * That means creating it and then hiding the spinner that was displayed by default, and displaying the
	 * wrapper for the main metabox content that was hidden by default.
	 *
	 * @since m2m
	 */
	self.initMainViewModel = function() {
		self.viewModel = self.getMainViewModel();

		var pageContent = self.getPageContent();

		// Show the listing after it's been fully rendered by knockout.
		pageContent.find('.toolset-page-spinner').hide();
		pageContent.find('.toolset-actual-content-wrapper').show();

	};

	self.getMainViewModel = function() {
		return _.map(self.getModelData(), function(model) {
			return new Types.page.extension.relatedContent.viewmodels.ListingViewModel(model);
		});
	};


	self.initStaticData = function(modelData) {
		Types.page.extension.relatedContent.itemsPerPage = modelData[0].itemsPerPage || {};
		Types.page.extension.relatedContent.strings = modelData[0].strings || {};
		Types.page.extension.relatedContent.ajaxInfo = modelData[0].ajaxInfo || {};
        // noinspection JSUnresolvedVariable
        Types.page.extension.relatedContent.jsPath = modelData[0].jsIncludePath || '';
        Types.page.extension.relatedContent.typesVersion = modelData[0].typesVersion || 'unset';
	};


};

// Make everything happen.
Types.page.extension.relatedContent.main = new Types.page.extension.relatedContent.Class(jQuery);
Types.head.ready(Types.page.extension.relatedContent.main.init);
