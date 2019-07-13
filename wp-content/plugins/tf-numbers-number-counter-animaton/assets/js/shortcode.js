/*=========================================
                SHORTCODE
==========================================*/          
(function($) {

  var Grps = names;

  tinymce.create('tinymce.plugins.tf_numbers', {
    init: function(ed, url) {
      ed.addButton('tf_numbers', {
        title: 'tf_numbers',
        icon: 'tf-n dashicons-before dashicons-slides',
        cmd: 'tf_numbers_cmd'        
      });
 
      ed.addCommand('tf_numbers_cmd', function() {
        ed.windowManager.open(
          //  Window Properties
          {
            file: url + '/../../inc/tf_numbers-insert.html',
            title: 'Insert tf_numbers',
            width: 370,
            height: 350,
            inline: 1
          },
          //  Windows Parameters/Arguments
          {
            editor: ed,
            groups: Grps,
            jquery: $ // PASS JQUERY
          }
        );
      });
    }
  });
  tinymce.PluginManager.add('tf_numbers', tinymce.plugins.tf_numbers);
})(jQuery);
