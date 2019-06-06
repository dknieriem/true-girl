(function ($) {
	$(function () {

		// Setup a click handler to initiate the Ajax request and handle the response to generate the map

		/****************  Start: Preview *****************/

		$('#preview_button').click(function () {

			$('#close_preview_button').css('display', 'block');

			//alert( 'preview_button fired!' ) ;
			var ajax_loader = '<div class=\"rvm_ajax_loader\"><h1>' + objectL10n.loading + '</h1></div>';
			/*ajax_loader = ajax_loader + '<img src=\"' ;
			ajax_loader = ajax_loader + objectL10n.images_js_path ;
			ajax_loader = ajax_loader + '\/ajax-loader.gif"></div>' ;*/
			$('#rvm_map_preview').html(ajax_loader);

			var data = {

				action: 'rvm_preview', // The function for handling the request
				map: $('#rvm_mbe_select_map').val(), // map
				zoom: $('#rvm_mbe_zoom:checked').val(), // zoom      
				width: $('#rvm_mbe_width').val(), // width
				padding: $('#rvm_mbe_map_padding').val(), // padding
				rvm_mbe_post_id: $('#post_ID').val(), // post ID
				nonce: $('#rvm_ajax_nonce').text(), // The security nonce
				transparentcanvas: $('#rvm_mbe_map_transparent_canvas:checked').val(), // Transparent background for canvas				
				canvascolor: $('#rvm_mbe_map_canvascolor').val(), // canvas background color
				bgcolor: $('#rvm_mbe_map_bgcolor').val(), // map background color
				bordercolor: $('#rvm_mbe_map_bordercolor').val(), // map border color
				borderwidth: $('#rvm_mbe_border_width').val(), // map border width
				subdivisionselectedstatus: $('#rvm_mbe_subdivision_background_selected_status:checked').val(), // map background color checkbox
				bgselectedcolor: $('#rvm_mbe_map_bg_selected_color').val() // map background color on select			

			};

			$.post(ajaxurl, data, function (response) {

				$('#rvm_map_preview').html(response);

			});


		}); // $( '#rvm_mbe_select_map' ).change( function()

		$('#close_preview_button').click(function () { // when Close Map Preview button is clicked...			

			$('#close_preview_button').css('display', 'none');
			$('#rvm_map_preview').html('');

		}); // $( '#preview_button' ).click( function()

		/****************  End: Preview *****************/


		/****************  Colour Picker *****************/

		$('.rvm_color_picker,#rvm_mbe_map_canvascolor,#rvm_mbe_map_bgcolor,#rvm_mbe_map_bg_selected_color,#rvm_mbe_map_bordercolor,#rvm_mbe_map_marker_bg_color ,#rvm_mbe_map_marker_border_color,#rvm_mbe_regions_mouseover_colour').wpColorPicker();


		/****************  Start: Tabs Show/Hide Functionality *****************/

		$('.rvm_tabs').click(function (event) {

			event.preventDefault();
			$('#rvm_meta div, #rvm_meta #rvm_tabs ul li').removeClass('rvm_active');
			var activeTab = $(this).attr('rel');
			$("#" + activeTab).addClass('rvm_active');
			$(this).addClass('rvm_active');
			$("#rvm_mbe_tab_active").val(activeTab); // change the value to be saved into DB

		});

		/****************  End: Tabs Show/Hide Functionality *****************/


		/****************  Start: Add Marker Fields *****************/

		var marker_fields = '';
		var wrapper = $('#rvm_mbe_fields_wrap'); //Fields wrapper
		var add_button = $('#rvm_mbe_add_field_button');

		add_button.click(function (e) { //on add input button click

			e.preventDefault();
			//alert('fired');

			marker_fields = '<div class="rvm_markers">';
			marker_fields = marker_fields + '<p><label for="marker_name" class="rvm_label">' + objectL10n.marker_name + '*</label><input type="text" name="rvm_marker_name[]" /></p>';
			marker_fields = marker_fields + '<p><label for="marker_lat" class="rvm_label">' + objectL10n.marker_lat + '*</label><input type="text" name="rvm_marker_lat[]" placeholder="e.g. 41.909730" /></p>';
			marker_fields = marker_fields + '<p><label for="marker_long" class="rvm_label">' + objectL10n.marker_long + '*</label><input type="text" name="rvm_marker_long[]" placeholder="e.g. 12.255814" /></p>';
			marker_fields = marker_fields + '<p><label for="marker_link" class="rvm_label">' + objectL10n.marker_link + '</label><input type="text" name="rvm_marker_link[]" /></p>';
			marker_fields = marker_fields + '<p><label for="marker_dim" class="rvm_label">' + objectL10n.marker_dim + '<br><span class="rvm_small_text">' + objectL10n.marker_dim_expl + '</span></label><input type="text" name="rvm_marker_dim[]" placeholder="' + objectL10n.marker_dim_placeholder + '" /></p>';
			marker_fields = marker_fields + '<p><label for="marker_popup" class="rvm_label" style="vertical-align:top;">' + objectL10n.marker_popup + '</label><textarea name="rvm_marker_popup[]" placeholder="' + objectL10n.marker_popup_placeholder + '" ></textarea></p>';
			marker_fields = marker_fields + '<input type="submit" class="rvm_remove_field button-secondary" value="' + objectL10n.marker_remove + '">';
			marker_fields = marker_fields + '</div>'; //class="rvm_markers" 

			wrapper.append(marker_fields); //add input box          


		});

		wrapper.on('click', '.rvm_remove_field', function (e) { //user click on remove text*/        	
			e.preventDefault();
			$(this).parent('div').remove();

		});

		/****************  End: Add Marker Fields *****************/


		/****************  Start: Add Custom Map Field *****************/

		$('#rvm_mbe_select_map').change(function () {
			//console.log($( '#rvm_mbe_select_map' ).val());
			var rvm_mbe_select_map = $('#rvm_mbe_select_map').val();
			console.log(rvm_mbe_select_map);

			if (rvm_mbe_select_map === 'rvm_custom_map') {

				$('.rvm_hidden_when_custom_map, hr.rvm_separator').hide();
				var rvm_custom_map_filename = '';

				//rvm_custom_map_filename = rvm_custom_map_filename + '<h3 class="rvm_custom_map_filename_title">Paste here the map name ( i.e.:  italy_merc_en ) loaded via Media Uploader</h3>';
				rvm_custom_map_filename = rvm_custom_map_filename + '<p>';
				rvm_custom_map_filename = rvm_custom_map_filename + '<input type="text" id="rvm_custom_map_filename" value="" name="rvm_custom_map_filename"  size="50" />';
				rvm_custom_map_filename = rvm_custom_map_filename + '<input id="rvm_custom_map_uploader_button" class="rvm_custom_map_uploader_button rvm_media_uploader button-primary" name="rvm_custom_map_uploader_button" type="submit" value="Select Map" />';
				rvm_custom_map_filename = rvm_custom_map_filename + '<input type="button" id="unzip_button" class="button-primary" value="Install your map" />';
				rvm_custom_map_filename = rvm_custom_map_filename + '</p>';

				$('#rvm_mbe_custom_map_wrapper').append(rvm_custom_map_filename);


			} else { //show again standard field
				$('#rvm_mbe_custom_map_wrapper, #rvm_custom_map_unzip_progress').empty(); // empty the rvm fields appended
				$('.rvm_hidden_when_custom_map').show();
			}

			// Check if map is installed, if not and if in custom map stop publishing
			$('form#post').submit(function (event) {
				if ($('#rvm_mbe_select_map').val() === 'rvm_custom_map') {
					var rvm_custom_map_is_installed = $('#rvm_custom_map_is_installed');

					//console.log(rvm_mbe_select_map);

					if (rvm_custom_map_is_installed.length) {
						// let's rock 'n roll ... save into DB
						//console.log('Map is installed');
						//disable "Install map" button
						$("#unzip_button").attr('disabled', 'disabled');
						return true;
					} else {
						alert("Click on Install your map before publishing");
						return false;
					}
				}

			});

		});

		/****************  End: Add Custom Map Field *****************/


		/****************  Start: Unzip Custom Map *****************/

		//using the 'on' method on wrapper make it work even if DOM is already loaded
		$('#rvm_mbe_custom_map_wrapper').on('click', '#unzip_button', function (e) {
			e.preventDefault();
			//console.log( 'unzip custom map button  fired!' ) ; 

			// Get value of custom map path
			var rvm_custom_map_filename = $('#rvm_custom_map_filename').val();

			if (rvm_custom_map_filename.length) {

				var ajax_loader = '<div class=\"rvm_ajax_loader\"><h1>' + objectL10n.unzipping + '</h1></div>';
				/*ajax_loader = ajax_loader + '<img src=\"' ;
				ajax_loader = ajax_loader + objectL10n.images_js_path ;
				ajax_loader = ajax_loader + '\/ajax-loader.gif"></div>' ;*/
				$('#rvm_custom_map_unzip_progress').html(ajax_loader);


				//Check if users provided entire path to map name uploaded via media uploader,and if yes change input value with just map name

				var rvm_custom_map_filename_array = rvm_custom_map_filename.split("/");

				if (rvm_custom_map_filename_array.length > 1) {
					$('#rvm_custom_map_filename').val(rvm_custom_map_filename_array[rvm_custom_map_filename_array.length - 1]);
				}

				var data = {

					action: 'rvm_custom_map', // The function for handling the request
					map: $('#rvm_mbe_select_map').val(), // map
					custom_map_filename: $('#rvm_custom_map_filename').val(), // path to zipped custom map
					nonce: $('#rvm_ajax_nonce').text() // The security nonce							

				};

				$.post(ajaxurl, data, function (response) {

					$('#rvm_custom_map_unzip_progress').html(response);

				});

			} else {
				alert( objectL10n.no_map_selected );

			}


		}); // $( '#rvm_mbe_unzip_custom_map' ).click( function()  


		/****************  End: Unzip Custom Map *****************/


		/****************  Start: Set Default Marker Icon *****************/

		$('#rvm_mbe_restore_marker_default_icon').click(function (e) {
			e.preventDefault();
			$('#rvm_actual_marker_icon_wrapper').css( "padding" , "0 10px");
			$('#rvm_actual_marker_icon_wrapper').addClass( "rvm_error_messages" );
			$('#rvm_actual_marker_icon_wrapper, #rvm_restore_marker_default_icon_wrapper').fadeOut( "fast");
			$('#rvm_marker_default_icon_restored').fadeIn( "slow");
			$('#rvm_mbe_custom_marker_icon_path_hidden').val( "default");

			var data = {

				action: 'rvm_restore_default_marker_icon', // The function for handling the request
				nonce: $('#rvm_ajax_nonce').text(),// The security nonce
				rvm_mbe_post_id: $('#rvm_mbe_post_id').val() 							

			};

			$.post(ajaxurl, data, function (response) {

				$('#rvm_marker_default_icon_restored').html(response);

			});
		});

		/****************  End: Set Default Marker Icon *****************/


		/****************  Start: multiple select checkboxes *****************/

		// Thanks Jordan Reiter : http://stackoverflow.com/questions/659508/how-can-i-shift-select-multiple-checkboxes-like-gmail
		var lastChecked = null;

		$(document).ready(function () {
			var $chkboxes = $('.rvm_region_checkbox');
			$chkboxes.click(function (e) {
				if (!lastChecked) {
					lastChecked = this;
					return;
				}

				if (e.shiftKey) {
					var start = $chkboxes.index(this);
					var end = $chkboxes.index(lastChecked);

					$chkboxes.slice(Math.min(start, end), Math.max(start, end) + 1).prop('checked', lastChecked.checked);

				}

				lastChecked = this;
			});
		});


		/****************  End: multiple select checkboxes *****************/

		/****************  Start: copy 'n paste shortcode link *****************/

		$('.rvm_copy_shortcode_action_link').click(function (e) {
			e.preventDefault();
			var rvm_shortcode_to_copy = $("#rvm_shortcode_to_copy");

			//$( "#rvm_shortcode_to_copy" ).text().clone().appendTo( ".rvm_copy_shortcode_action_link" );
			//alert( rvm_shortcode_to_copy );
			//rvm_shortcode_to_copy.clone().appendTo( ".updated" );
			$("#content").val($("#content").val() + rvm_shortcode_to_copy.text());
		});


		/****************  End: copy shortcode link *****************/


		/****************  Start: Delete all Markers *****************/
		
		$('#rvm_delete_all_markers_button').click( function(e) {
			e.preventDefault();
			$('.rvm_markers,.rvm_added_markers_title').remove();
			$(this).remove();
		});

		/****************  End: Delete all Markers *****************/


		/****************  Start: Export Markers *****************/

		$('#rvm_export_markers_button').click( function(e) {
			e.preventDefault();
			
			/*var ajax_loader = '<img src=\"' ;
			ajax_loader = ajax_loader + objectL10n.images_js_path ;
			ajax_loader = ajax_loader + '\/rvm-ajax-loader.gif"></div>' ;
			$('#rvm_export_markers_status').html(ajax_loader);*/

			var rvm_mbe_map_name = $('#rvm_mbe_select_map').val();
			
			var data = {
					//contentType: "application/csv",
					action: 'rvm_export_markers', // The function for handling the request
					rvm_mbe_post_id: $('#rvm_mbe_post_id').val(),
					nonce: $('#rvm_ajax_nonce').text() // The security nonce							

				};

				$.post(ajaxurl, data, function (response) {
					var uri = 'data:application/csv;charset=UTF-8,' + encodeURIComponent(response);
					//the only way to assign a specific filename.extension is creating an anchor link
					var link = document.createElement("a");
					link.download =  $('#rvm_mbe_select_map').val() + '-markers.csv';
					link.href = uri;

					document.body.appendChild(link);
					link.click();

					// Cleanup the DOM
					document.body.removeChild(link);
					delete link;

					//$('#rvm_export_markers_status').remove();


				});
		});

		/****************  End: Export Markers *****************/


		/****************  Start: Import Markers *****************/

		$('#rvm_import_markers_button').click( function(e) {
			e.preventDefault();
			
			var ajax_loader = '<img src=\"' ;
			ajax_loader = ajax_loader + objectL10n.images_js_path ;
			ajax_loader = ajax_loader + '\/rvm-ajax-loader.gif"></div>' ;
			$('#rvm_import_markers_status').css("display","inline-block");
			$('#rvm_import_markers_status').html(ajax_loader);

			var rvm_mbe_map_name = $('#rvm_mbe_select_map').val();
			
			var data = {
					//contentType: "application/csv",
					action: 'rvm_import_markers', // The function for handling the request
					rvm_mbe_post_id: $('#rvm_mbe_post_id').val(),
					rvm_upload_markers_file_path: $('#rvm_upload_markers_file_path').val(),
					nonce: $('#rvm_ajax_nonce').text() // The security nonce							

				};

				$.post(ajaxurl, data, function (response) {
					$('#rvm_import_markers_status').html(objectL10n.markers_correctly_imported);
					$('#rvm_mbe_fields_wrap').html(response);
					$('#rvm_import_reset_markers_button').css("display","block");

				});
		});

		/****************  End: Import Markers *****************/

        
		/****************  Start: Export Subdivions *****************/

		$('#rvm_export_regions_button').click( function(e) {
			e.preventDefault();
			
			/*var ajax_loader = '<img src=\"' ;
			ajax_loader = ajax_loader + objectL10n.images_js_path ;
			ajax_loader = ajax_loader + '\/rvm-ajax-loader.gif"></div>' ;
			$('#rvm_export_regions_status').html(ajax_loader);*/

			var rvm_mbe_map_name = $('#rvm_mbe_select_map').val();
			
			var data = {
					//contentType: "application/csv",
					action: 'rvm_export_regions', // The function for handling the request
					rvm_mbe_post_id: $('#rvm_mbe_post_id').val(),
					rvm_mbe_select_map: $('#rvm_mbe_select_map').val(),
					nonce: $('#rvm_ajax_nonce').text() // The security nonce							

				};

				$.post(ajaxurl, data, function (response) {
					var uri = 'data:application/csv;charset=UTF-8,' + encodeURIComponent(response);
					//the only way to assign a specific filename.extension is creating an anchor link
					var link = document.createElement("a");
					link.download =  $('#rvm_mbe_select_map').val() + '-regions.csv';
					link.href = uri;

					document.body.appendChild(link);
					link.click();

					// Cleanup the DOM
					document.body.removeChild(link);
					delete link;

					//$('#rvm_export_markers_status').remove();


				});
		});

		/****************  End: Export Subdivions *****************/


		/****************  Start: Import Subdivions *****************/

		$('#rvm_import_regions_button').click( function(e) {
			e.preventDefault();
			
			var ajax_loader = '<img src=\"' ;
			ajax_loader = ajax_loader + objectL10n.images_js_path ;
			ajax_loader = ajax_loader + '\/rvm-ajax-loader.gif"></div>' ;
			$('#rvm_import_regions_status').css("display","inline-block");
			$('#rvm_import_regions_status').html(ajax_loader);

			var rvm_mbe_map_name = $('#rvm_mbe_select_map').val();
			
			var data = {
					//contentType: "application/csv",
					action: 'rvm_import_regions', // The function for handling the request
					rvm_mbe_post_id: $('#rvm_mbe_post_id').val(),
					rvm_upload_regions_file_path: $('#rvm_upload_regions_file_path').val(),
					nonce: $('#rvm_ajax_nonce').text() // The security nonce							

				};

				$.post(ajaxurl, data, function (response) {
					$('#rvm_import_regions_status').html(objectL10n.regions_correctly_imported);
					$('#rvm_regions_from_db').html(response);
					$('.rvm_color_picker_imported_regions').wpColorPicker();
					$('#rvm_import_reset_regions_button').css("display","block");
                    var rvm_item_handle = $('.rvm_region_name');
                    var rvm_item_arrow = 'h4 > span.rvm_arrow';

                    rvm_item_handle.click(function() {
                        $(this).find('h4').toggleClass('rvm_region_active');
                        $(this).next().toggle('fast');
                        $(this).find(rvm_item_arrow).toggleClass('rvm_arrow_up');
                    });
					

				});
		});

		/****************  End: Import Subdivions *****************/

	});
})(jQuery);