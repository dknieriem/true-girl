/*
    Post Reference Field Script
    Handles the select2 functionality for the post reference field

    @since m2m
 */
;(function( $ ) {
    $( document ).on( 'ready', function() {
        var self = this,
            staticData = $.parseJSON( WPV_Toolset.Utils.editor_decode64( $( '#types_post_reference_model_data' ).html() ) );

        $( '[data-types-post-reference]' ).each( function() {
            var selectField = $( this );
            selectField.toolset_select2({
                allowClear: true,
                triggerChange: true,
                width: '100%',
                ajax: {
                    url: ajaxurl,
                    dataType: 'json',
                    delay: 250,
                    type: 'post',
	                data: function (params) {
		                return {
			                action: staticData['action']['name'],
			                skip_capability_check: true,
			                wpnonce: staticData['action']['nonce'],
							post_reference_field_action: 'json_post_reference_field_posts',
							post_id: staticData['post_id'],
							search: params.term,
							page: params.page,
							post_type: selectField.data( 'types-post-reference' ),
							field_slug: selectField.data( 'wpt-id' ),
							relationship_slug: selectField.data( 'types-relationship' ),
						};
					},
                    processResults: function (data, params) {
                        // console.log( data.items );
                        params.page = params.page || 1;
                        return {
                            results: data.items,
                            pagination: {
                                more: ( params.page * staticData['select2']['posts_per_load'] ) < data.total_count
                            }
                        };
                    },
                    cache: false
                },
                templateSelection: function( selected ) {
                    // add the url to the <option> tag as 'data-url' attribute
                    // (required for PRF Yoast compatiblity)
                    jQuery( selected.element ).attr("data-url", selected.url );

                    return selected.text;
                }
            });
        } );

        // Pointers.
        jQuery( '.toolset-post-reference-field .js-show-tooltip').click( function() {
            ToolsetTypes.Utils.Pointer.show( this );
        } );
    });

})( jQuery );
