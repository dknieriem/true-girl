jQuery("document").ready(function () {
    elementor.hooks.addAction( 'panel/open_editor/widget/ffwd-elementor', function( panel, model, view ) {
        var ffwd_el = jQuery('select[data-setting="ffwd_feeds"]',window.parent.document);
        ffwd_add_edit_link(ffwd_el);
    });
    jQuery('body').on('change', 'select[data-setting="ffwd_feeds"]',window.parent.document, function (){
        ffwd_add_edit_link(jQuery(this));
    });
});

function ffwd_add_edit_link(el) {
        var ffwd_el = el;
        var ffwd_id = ffwd_el.val();
        var a_link = ffwd_el.closest('.elementor-control-content').find('.elementor-control-field-description').find('a');
        var ffwd_nonce = a_link.data("ffwd_nonce");
        var new_link = 'admin.php?page=info_ffwd';
        if(ffwd_id !== '0'){
            new_link = 'admin.php?page=info_ffwd&task=edit&current_id='+ffwd_el.val()+"&ffwd_nonce="+ffwd_nonce;
        }
        a_link.attr( 'href', new_link);
}


