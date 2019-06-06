(function ($) {
	$(function () {


		/****************  start media uploader *****************/


		var file_frame;
		var rvm_tab,rvm_button_action;

		$(".rvm_media_uploader").live('click', function (event) {
			//access to #id of selected button in order to establish in which tab we are
			//and then assigning correclty the attachment url to correct input value
			if(this.id === 'rvm_upload_regions_button') {
				rvm_tab = 'regions';
				rvm_button_action = 'import';
			}

			else if(this.id === 'rvm_import_regions_button') {
				rvm_tab = 'regions';
				rvm_button_action = 'import';
			}

			else if(this.id === 'rvm_export_regions_button') {
				rvm_tab = 'regions';
				rvm_button_action = 'export';
			}

			else if(this.id === 'rvm_upload_markers_button') {
				rvm_tab = 'markers';
				rvm_button_action = 'import';
			}

			else if(this.id === 'rvm_import_markers_button') {
				rvm_tab = 'markers';
				rvm_button_action = 'import';
			}

			else if(this.id === 'rvm_export_markers_button') {
				rvm_tab = 'markers';
				rvm_button_action = 'export';
			}

			

			event.preventDefault();

			// If the media frame already exists, reopen it.
			if (file_frame) {
				file_frame.open();
				return;
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				title: $(this).data('uploader_title'),
				button: {
					text: $(this).data('uploader_button_text'),
				},
				multiple: false // Set to true to allow multiple files to be selected
			});

			// When an image is selected, run a callback.
			file_frame.on('select', function () {
				// We set multiple to false so only get one image from the uploader
				attachment = file_frame.state().get('selection').first().toJSON();

				// Do something with attachment.id and/or attachment.url here

				jQuery("#rvm_custom_map_filename,#rvm_mbe_custom_marker_icon_path,#rvm_option_custom_marker_icon_module_path,#rvm_upload_" + rvm_tab + "_file_path").val(attachment.url);
				jQuery("#rvm_import_" + rvm_tab + "_button").css("display","block");
			});

			// Finally, open the modal
			file_frame.open();
		});

		/****************  end media uploader *****************/


		/****************  Start show/hide regions' block in "Subdivision" tab *****************/
	    
	    var rvm_item_handle = $('.rvm_region_name');
	    var rvm_item_arrow = 'h4 > span.rvm_arrow';

	    rvm_item_handle.click(function() {
	    	$(this).find('h4').toggleClass('rvm_region_active');
	        $(this).next().toggle('fast');
	        $(this).find(rvm_item_arrow).toggleClass('rvm_arrow_up');
	    });

	    /****************  end show/hide regions' block in "Subdivision" tab *****************/

	    $(".rvm_region_label_action").change( function() {	    	
	    	var region_id = $(this).next(".rvm_regions_sub_block").val();//find value of hidden field just next select .rvm_region_label_action
	    	if( $(this).val() == 'open_link' ) {
	    		var rvm_label_link_var = "Link";
	    		$("#rvm_region_input_link_" + region_id).addClass("rvm_show");
	    		$("#rvm_region_input_link_" + region_id).removeClass("rvm_hide");
	    	}

	    	else if( $(this).val() == 'open_label_onto_default_card' ) {
	    		var rvm_label_link_var = "Display label onto default card";
	    		$("#rvm_region_input_link_" + region_id).addClass("rvm_hide");
	    		$("#rvm_region_input_link_" + region_id).removeClass("rvm_show");
	    	}

	    	else {//case show_custom_selector
	    		var rvm_label_link_var = objectL10n.show_custom_tag_label;
	    		$("#rvm_region_input_link_" + region_id).addClass("rvm_show");
	    		$("#rvm_region_input_link_" + region_id).removeClass("rvm_hide");	    			
	    	}

	    	$("#rvm_region_" + region_id + " .rvm_regions_wrapper_link label").html(rvm_label_link_var);


	    	//empty input content $("#rvm_region_" + region_id + " .rvm_regions_wrapper_link input").val("");

	    	//$(this).val();
	    });	

	});
})(jQuery);