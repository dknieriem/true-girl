/*
 * Example of ted created by WPCF_Editor::renderTedSettings()
 *
 * @since .2. Probably DEPRECATED
 */
/*var ted = {
    fieldID: null,
    fieldType: null,
    fieldTitle: null,
    params: [],
    repetitive: false,
    metaType: 'postmeta',
    postID: -1,
    supports:[]
};*/

/*
 * Knockout inner bindings for Editor modal form.
 */
var tedForm = function() {
    this.cb_mode = ko.observable(ted.params.mode || 'db');
    this.cbs_mode = ko.observable(ted.params.mode || 'display_all');
    this.date_mode = ko.observable(ted.params.style || 'text');
    this.dateStyling = function() {
        var menu = tedFrame.menu();
        if (this.date_mode() == 'calendar') {
            menu.find('#menu-item-styling').show();
            menu.find('#types-modal-css-class').removeAttr('disabled');
        } else {
            menu.find('#menu-item-styling').hide();
            menu.find('#types-modal-css-class').attr('disabled', 'disabled');
        }
        return true;
    };
    this.imageResize = ko.observable(ted.params.resize != 'stretch' ? (ted.params.resize || 'proportional') : 'proportional');
    this.image_size = ko.observable(ted.params.image_size || 'full');
    this.imageKeepProportional = ko.observable(ted.params.resize != 'stretch');
    this.imagePaddingColor = function() {
        return ted.params.padding_color == 'transparent' ? '#FFFFFF' : ted.params.padding_color;
    };
    this.imagePaddingTransparent = ko.observable(ted.params.padding_color == 'transparent');
    this.imageUrl = ko.observable(ted.params.imageUrl || '');
    this.imageUrlDisable = function() {
        var elements = tedFrame.form()
        .find('#image-title, #image-alt, #types-modal-css-class, #types-modal-style, #types-modal-output, #types-modal-showname, #image-alignment, #image-onload');
        if (this.imageUrl()) {
            elements.attr('disabled', 'disabled');
        } else {
            elements.not('.js-raw-disabled').removeAttr('disabled');
        }
        return true;
    };
    this.output = ko.observable(true);
    this.radio_mode = ko.observable(ted.params.mode || 'db');
    this.radioPostType = ko.observable(ted.params.related_post || 'post');
    this.raw = ko.observable();
    this.rawDisableAll = function(data, event) {
        if (this.raw()) {
            // Disable enabled inputs and mark them
            tedFrame.form().find('div.js-raw-disable :enabled')
            .not('#types-modal-raw,#__types_nonce')
            .addClass('js-raw-disabled')
            .attr('disabled', 'disabled');
        } else {
            tedFrame.form().find('.js-raw-disabled').removeAttr('disabled')
            .removeClass('js-raw-disabled');
        }
        return true;
    };
    this.relatedPost = ko.observable(ted.params.post_id || 'current');
    this.selectPostType = ko.observableArray([ted.params.related_post || 'post']);
    this.separator = ko.observable(ted.params.separator || ', ');
    this.showMenuStyling = ko.computed(function() {
        return ted.fieldType != 'date' || (ted.fieldType == 'date' && ted.params.style == 'calendar');
    });
    this.specificPostID = ko.observable(ted.params.specific_post_id || '');
    this.supports = function(feature) {
        return jQuery.inArray(feature, ted.supports) != -1;
    };
    this.url_target = ko.observable(ted.params.target || '_self');
    this.submitDisabled = ko.computed(function() {
        var notUsed = this.radioPostType();
        return ! (
            (
                'related' !== this.relatedPost()
                && 'intermediate' !== this.relatedPost()
            )
            || (
                (
                	'related' === this.relatedPost()
                	|| 'intermediate' === this.relatedPost()
                )
                && jQuery('input[name=post_id]:checked').parent().next().find('input[name=' + this.relatedPost() + '_post]:checked').length > 0
            )
        );
    }, this);
};
/*
 * Editor modal window control.
 */
var tedFrame = (function(window, $){

    var modal = $('#types-editor-modal');
    var modalMenu = modal.find('.types-media-menu');
    var modalMenuItems;
    var modalContent = modal.find('.types-media-frame-content');
    var modalToolbar = modal.find('.types-media-frame-toolbar-inner');
    var modalContentTabs;
    var modalForm;
    var tabIndex = 0;

    function init()
    {

        modalForm = $('#types-editor-modal-form');

        modalMenuItems = modal.find('.types-media-menu a');
        modalContentTabs = modal.find('.types-media-frame-content .tab');

        // Bind menu tabbing
        bindTabbing();

        modalMenu.find('a:first-child').addClass('active');
        modalContent.find('.tab:eq(0)').show();
        modal.find('.media-modal-close, .media-button-cancel').click(function(){
            if (ted.callback == 'views_wizard') {
                window.parent.typesWPViews.wizardCancel();
                return false;
            }
            window.parent.jQuery.colorbox.close();
            return false;
        });

        // Bind submit
        modalToolbar.on('click', '.media-button-insert:not(.disabled)', function(){
            $('#types-editor-modal-form').trigger('submit');
            return false;
        });

        // Show modal content
        modal.css('visibility', 'visible');

        // Bind click to the Colorbox close button
        jQuery('.js-close-types-popup').on('click',function(){
    	if (ted.callback == 'views_wizard') {
                window.parent.typesWPViews.wizardCancel();
                return false;
            }
            parent.jQuery.colorbox.close();
        });
    }

    function bindTabbing()
    {
        modalMenuItems.click(function(){
            modalMenuItems.removeClass('active');
            $(this).addClass('active');
            tabIndex = modalMenuItems.index($(this));
            modalContentTabs.hide();
            modalContent.find('.tab:eq('+tabIndex+')').show();
            return false;
        });
    }

    function resetMenu()
    {
        bindTabbing();
    }

    function insertShortcode( shortcode, esc_shortcode )
    {
        /**
         * Perform a filtering of the shortcode to support different shortcode formats.
         *
         * @param string shortcode The shortcode to be filtered.
         *
         * @since 2.2.20
         */
        shortcode = window.parent.Toolset.hooks.applyFilters( 'wpv-filter-wpv-shortcodes-transform-format', shortcode );
        esc_shortcode = window.parent.Toolset.hooks.applyFilters( 'wpv-filter-wpv-shortcodes-transform-format', esc_shortcode );

        if ( ted.callback == 'views_wizard' ) {
            window.parent.typesWPViews.wizardSendShortcode( shortcode );
            return true;
        }
    if (
    	ted.callback == 'admin_bar'
    	|| ted.callback == 'input_append'
    ) {
            window.parent.typesWPViews.interceptCreateShortcode( shortcode );
            return true;
        }
        // Check if there is custom handler
        if (window.parent.wpcfFieldsEditorCallback_redirect) {
            eval(window.parent.wpcfFieldsEditorCallback_redirect['function'] + '(\''+esc_shortcode+'\', window.parent.wpcfFieldsEditorCallback_redirect[\'params\'])');
            // Reset redirect
            window.parent.wpcfFieldsEditorCallback_redirect = null;
        } else {
            // Use default handler

            window.parent.icl_editor.insert(shortcode);
        }
        window.parent.jQuery.colorbox.close();
    }

    return {
        init: init,
        close: insertShortcode,
        container: function() {
            return modal;
        },
        form: function() {
            return modalForm;
        },
        menu: function() {
            return modalMenu;
        }
    };
})(window, jQuery, undefined);

/*
 * WP Tooltip
 */
jQuery(document).ready(function($){


    /* Generic function to display native WP Tooltip */
    $(document).on('click', '.js-show-tooltip', function() {

        var $this = $(this);

        // default options
        var defaults = {
            edge: "left", // on which edge of the element tooltips should be shown: ( right, top, left, bottom )
            align: "middle", // how the pointer should be aligned on this edge, relative to the target (top, bottom, left, right, middle).
            offset: "15 0 " // pointer offset - relative to the edge
        };

        // custom options passed in HTML "data-" attributes
        var custom = {
            edge: $this.data('edge'),
            align: $this.data('align'),
            offset: $this.data('offset')
        };

        $this.pointer({
            content: '<h3>' + $this.data('header') + '</h3>' + '<p>' + $this.data('content') + '</p>',
            position: $.extend(defaults, custom) // merge defaults and custom attributes
        }).pointer('open');

    });
/* Generic function to display native WP Tooltip END */
});

ko.bindingHandlers.tedSupports = {
    init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
        var feature = valueAccessor();
        if (!viewModel.supports(feature)) {
            jQuery(element).remove();
        }
    }
};

ko.applyBindings(new tedForm());
jQuery(function(){
    tedFrame.init();
    parent.jQuery.colorbox.resize({
        innerHeight: jQuery('#wpcf-ajax').height()
    });
});
