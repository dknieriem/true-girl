(function () {
    tinymce.create('tinymce.plugins.thriveShortcodes', {
        init: function (ed, url) {
            ed.onNodeChange.add(
                function (ed, cm, n) {
                    var img_icon_path = ThriveThemeUrl + "/inc/images/thrive-shortcode-1.png";
                    var _element_id = "content_thriveShortcodes_text";
                    if (jQuery("#thrive_meta_focus_subheading_text_thriveShortcodes").length > 0) {
                        _element_id = "thrive_meta_focus_subheading_text_thriveShortcodes";
                    }
                    jQuery("#" + _element_id).html('');
                    jQuery("#" + _element_id).css({
                        "background-image": "url('" + img_icon_path + "')",
                        "background-repeat": "no-repeat",
                        "background-position": "center"
                    });
                    jQuery("#" + _element_id).attr('class', "mceButton mceButtonEnabled");
                    jQuery("#content_thriveShortcodes_open").remove();
                });
        },
        createControl: function (n, cm) {

            if (n == 'thriveShortcodes') {
                var mlb = cm.createListBox('thriveShortcodes', {
                    title: 'Shortcodes',
                    onselect: function (v) {
                        ThriveHandleAddShortcote(v);
                        setTimeout(function () {
                            jQuery("#content_thriveShortcodes_text").html("");
                        }, 20);
                    }
                });

                for (var i in thrive_shortcodes)
                    mlb.add(thrive_shortcodes[i], thrive_shortcodes[i]);

                return mlb;
            }
            return null;
        }


    });
    tinymce.PluginManager.add('thriveShortcodes', tinymce.plugins.thriveShortcodes);

    // Adds an observer to the onInit event using tinyMCE.init
    tinyMCE.init({
        setup: function (ed) {
            ed.onInit.add(function (ed) {
                //console.debug('Editor is done: ' + ed.id);
            });
        }
    });
})();


jQuery(document).ready(function () {
    jQuery("#content_thriveShortcodes_text").css('color', 'red !important');
});