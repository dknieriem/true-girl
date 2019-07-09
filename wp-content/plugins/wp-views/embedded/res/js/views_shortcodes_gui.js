/**
 * API and helper functions for the GUI on Views shortcodes.
 *
 * @since 1.7.0
 * @since 2.3.0 Added a proper API based on Toolset.hooks
 * @since 2.3.0 Added a proper shortodes parser base on wp.shortcode
 * @todo Add editing capabilities to wpv-view and wpv-form-view shortcodes
 * @package Views
 */

var WPViews = WPViews || {};

if ( typeof WPViews.ShortcodesParser_instance === "undefined" ) {
	WPViews.ShortcodesParser_instance = {};
}

/**
 * -------------------------------------
 * ShortcodesParser
 *
 * @todo this needs tons of cleanup, and maybe move some methods (index, insert, replace) to the icl_editor script
 * -------------------------------------
 */

WPViews.ShortcodesParser = function( textarea ) {

	var self	= this;

	self.textarea	= textarea;
	self.editor		= icl_editor ? icl_editor : undefined;

	self.is_codemirror	= false;
	self.is_tinymce		= false;
	self.is_flat		= false;
	
	self.current_index		= 0;
	self.current_cursor		= 0;
	self.textarea_content	= '';
	
	self.shortcode_data		= {
		tag:		'',
		attrs:		{
			named:		{},
			numeric:	{}
		},
		type:		'',
		content:	''
	};
	
};

WPViews.ShortcodesParser.prototype.parse = function() {
	var self				= this;
	
	self.shortcode_data		= {
		tag:		'',
		attrs:		{
			named:		{},
			numeric:	{}
		},
		type:		'',
		content:	'',
		raw:		'',
		original:	{}
	};

	self.current_index		= 0;
	self.current_cursor		= 0;
	self.textarea_content	= '';

	if ( self.textarea ) {
		try {
			self.cm		= self.editor.isCodeMirror( self.textarea );
			self.tiny	= self.editor.isTinyMce( self.textarea );
		} catch( e ) {
			throw {
				name:        "Missing Dependency",
				message:     "Error detected. You are probably missing dependency with icl_editor object."
			};
		}

		if ( self.tiny ) {
			self.is_tinymce		= true;
			self.manage_tinymce();
		} else if ( self.cm ) {
			self.is_codemirror	= true;
			self.manage_codemirror();
		} else {
			self.is_flat		= true;
			self.manage_flat();
		}
	} else {
		throw {
			name:        "Missing Argument",
			message:     "Error detected. You should pass a valid textarea DOM object to constructor or init methods."
		};
	}
	
	self.shortcode_data.original = {
		tag:	 	self.shortcode_data.tag,
		attrs:		self.shortcode_data.attrs,
		type:		self.shortcode_data.type,
		content:	self.shortcode_data.content,
		raw:		self.shortcode_data.raw
	};
	
	return self.shortcode_data;
};

WPViews.ShortcodesParser.prototype.set_selection = function( shortcode_data ) {
	var self = this;
	if ( self.is_tinymce ) {
		
	} else if ( self.is_codemirror ) {
		var cursor_start	= self.cm.posFromIndex( shortcode_data.pos.start ),
		cursor_end			= self.cm.posFromIndex( shortcode_data.pos.end );
		self.cm.setSelection( cursor_start, cursor_end );
	} else {
		
	}
	return self;
};

WPViews.ShortcodesParser.prototype.unset_selection = function( shortcode_data ) {
	var self = this;
	if ( self.is_tinymce ) {
		
	} else if ( self.is_codemirror ) {
		var cursor = self.cm.posFromIndex( shortcode_data.pos.current );
		self.cm.setSelection( cursor, cursor );
	} else {
		
	}
	return self;
};

WPViews.ShortcodesParser.prototype.insert = function( shortcode_string ) {
	var self = this;
	if ( self.is_tinymce ) {
		self.editor.insert( shortcode_string );
	} else if ( self.is_codemirror ) {
		self.editor.insert( shortcode_string );
	} else {
		self.editor.insert( shortcode_string );
	}
	return self;
};

/**
 * Codemirror parsing
 *
 * @todo Try to move all the parametric-related code to an action/filter executed in its own script
 */

WPViews.ShortcodesParser.prototype.manage_codemirror = function() {
	var self = this;
	
	this.cm.focus();
	
	var cursor	= this.cm.getCursor( false ),
	cursor_start,
	cursor_end,
	index		= this.cm.indexFromPos( cursor );

	this.current_index		= index;
	this.current_cursor		= cursor;
	this.textarea_content	= this.cm.getValue();
	
	var content_before = this.textarea_content.substring( 0, index ),
		content_after = this.textarea_content.substring( index ),
		last_open_bracket = content_before.lastIndexOf( '[' );
	
	if ( 
		last_open_bracket != -1
		&& content_before.substring( last_open_bracket ).substring( 0, 2 ) == '[/' 
	) {
		last_open_bracket = content_before.substring( 0, last_open_bracket - 1 ).lastIndexOf( '[' );
	}
	
	if ( last_open_bracket != -1 ) {
		
		var to_parse		= content_before.substring( last_open_bracket ) + this.textarea_content.substring( index ),
		tag					= to_parse.split( ']' )[0].split( ' ' )[0].substring(1),
		parsed_shortcode	= wp.shortcode.next( tag, to_parse );
		
		// Avoid parsing when there are numbered attributes, or when we are way past the shortcode end
		// @todo we miss ediiting when in the closing tag for wpv-control-set and wpv-control-post-relationship :-(
		if ( 
			_.size( parsed_shortcode.shortcode.attrs.numeric ) == 0 
			&& (
				last_open_bracket + parsed_shortcode.content.length >= ( index + 1 )
				|| ( 
					'wpv-control-post-ancestor' == tag 
					&& content_after.indexOf( '[/wpv-control-post-relationship]' ) != -1
				) || (
					'wpv-control-item' == tag 
					&& content_after.indexOf( '[/wpv-control-set]' ) != -1
				)
			)
		) {
			this.shortcode_data		= parsed_shortcode.shortcode;
			this.shortcode_data.raw	= parsed_shortcode.content;
			this.shortcode_data.pos	= {
				start:		last_open_bracket,
				end:		last_open_bracket + parsed_shortcode.content.length,
				current:	index
			};
			
			if ( 'wpv-control-post-relationship' == this.shortcode_data.tag ) {
				self.manage_codemirror_parse_inner_and_merge( 'wpv-control-post-ancestor' );
			} else if ( 'wpv-control-post-ancestor' == this.shortcode_data.tag ) {
				self.manage_codemirror_parse_outer_and_merge( 'wpv-control-post-relationship' );
			} else if ( 'wpv-control-set' == this.shortcode_data.tag ) {
				self.manage_codemirror_parse_inner_and_merge( 'wpv-control-item' );
			} else if ( 'wpv-control-item' == this.shortcode_data.tag ) {
				self.manage_codemirror_parse_outer_and_merge( 'wpv-control-set' );
			}
		}
		
	}
	
};

WPViews.ShortcodesParser.prototype.manage_codemirror_parse_inner_and_merge = function( target_tag ) {
	var current_shortcode_data = this.shortcode_data,
		current_shortcode_content = current_shortcode_data.content,
		first_open_bracket = current_shortcode_content.indexOf( '[' + target_tag ),
		first_close_bracket = current_shortcode_content.indexOf( ']' );
	
	if (
		first_open_bracket != -1 
		&& first_close_bracket != -1
	) {
		var to_parse			= current_shortcode_content.substring( first_open_bracket ) + current_shortcode_content.substring( first_close_bracket ),
			parsed_shortcode	= wp.shortcode.next( target_tag, to_parse );
	
		if ( _.size( parsed_shortcode.shortcode.attrs.numeric ) == 0 ) {
			_.each( parsed_shortcode.shortcode.attrs.named, function( attr_value, attr_key, list ) {
				if ( ! _.has( current_shortcode_data.attrs.named, attr_key ) ) {
					current_shortcode_data.attrs.named[ attr_key ] = attr_value;
				}
			});
			
			this.shortcode_data.attrs = current_shortcode_data.attrs;
		}
	}
	// Just need to parse current_shortcode_data.content to find the first [target_tag shortcode :-)
}

WPViews.ShortcodesParser.prototype.manage_codemirror_parse_outer_and_merge = function( target_tag ) {
	var current_shortcode_data = this.shortcode_data,
		current_start = this.shortcode_data.pos.start,
		current_end = this.shortcode_data.pos.end,
		current_index = this.shortcode_data.pos.current,
		content_before = this.textarea_content.substring( 0, current_index ),
		content_after = this.textarea_content.substring( current_index ),
		last_open_bracket = content_before.lastIndexOf( '[' + target_tag ),
		last_close_bracket = this.textarea_content.lastIndexOf( '[/' + target_tag + ']' );
	
	if ( last_close_bracket != -1 ) {
		last_close_bracket = last_close_bracket + ( target_tag.length + 3 );
	}
	
	if (
		last_open_bracket != -1 
		&& last_close_bracket != -1
	) {
	
		var to_parse			= content_before.substring( last_open_bracket ) + this.textarea_content.substring( current_index ),
			parsed_shortcode	= wp.shortcode.next( target_tag, to_parse );
		
		if ( 
			_.size( parsed_shortcode.shortcode.attrs.numeric ) == 0 
			&& last_open_bracket + parsed_shortcode.content.length >= ( current_index + 1 )
		) {
			_.each( parsed_shortcode.shortcode.attrs.named, function( attr_value, attr_key, list ) {
				current_shortcode_data.attrs.named[ attr_key ] = attr_value;
			});
			
			this.shortcode_data.tag = target_tag;
			this.shortcode_data.attrs = current_shortcode_data.attrs;
			this.shortcode_data.type = 'closed';
			this.shortcode_data.raw	= this.textarea_content.substring( last_open_bracket, last_close_bracket );
			this.shortcode_data.pos	= {
				start:		last_open_bracket,
				end:		last_close_bracket,
				current:	current_index
			};
		}
		
	}
}

/**
 * TinyMCE parsing
 */

WPViews.ShortcodesParser.prototype.manage_tinymce = function() {
	
};

/**
 * Flat parsing
 */

WPViews.ShortcodesParser.prototype.manage_flat = function() {
	
};

/**
 * -------------------------------------
 * ShortcodesGUI
 * -------------------------------------
 */

WPViews.ShortcodesGUI = function( $ ) {
	
	var self = this;
	
	self.i18n = wpv_shortcodes_gui_texts;
	
	self.page = self.i18n.get_page;
	
	/**
	 * Shortcodes GUI API version.
	 *
	 * First thre digits refer to the Views current version.
	 * Last three digits allow for at least 1000 updates per dev cycle.
	 *
	 * Access to it using the API methods, from inside this object:
	 * - self.get_shortcode_gui_api_version
	 * 
	 * Access it using the API hooks, from the ouside world:
	 * - wpv-filter-wpv-shortcodes-gui-get-gui-api-version
	 *
	 * @since 2.3.0
	 */
	self.shortcode_gui_api_version = 230000;

	// Parametric search
	self.ps_view_id = 0;
	self.ps_orig_id = ( _.contains( [ 'post-new.php', 'post.php' ], wpv_shortcodes_gui_texts.pagenow ) ) ? wpv_shortcodes_gui_texts.post_id : 0;
	self.dialog_insert_view_locked = false;

	/**
	 * Dialogs used by this API.
	 *
	 * @since unknown
	 */
	self.dialog_insert_view					= null;
	self.dialog_insert_shortcode			= null;
	self.dialog_insert_views_conditional	= null;
	self.shortcodes_wrapper_dialogs			= {};
	
	/**
	 * Cache for the suggest field type, to store the last selected value.
	 *
	 * @since unknown
	 */
	self.suggest_cache = {};
	
	/**
	 * The current GUI API action to be performed. Can be 'insert', 'create', 'save', 'append', 'edit'.
	 *
	 * Access to it using the API methods, from inside this object:
	 * - self.get_shortcode_gui_action
	 * - self.set_shortcode_gui_action
	 * 
	 * Access it using the API hooks, from the ouside world:
	 * - wpv-filter-wpv-shortcodes-gui-get-gui-action
	 * - wpv-action-wpv-shortcodes-gui-set-gui-action
	 *
	 * @since unknown
	 * @note Typess access this directly, so we can not better rename it yet.
	 */
	self.shortcode_gui_insert			= 'insert';
	self.shortcode_gui_valid_actions	= [ 'insert', 'create', 'save', 'append', 'edit', 'skip' ];
	
	/**
	 * Set of shortcode fields to display in the Fields and Views dialog.
	 *
	 * Can be 'posts', 'taxonomy', or 'users'.
	 *
	 * @since unknown
	 */
	self.shortcodes_set = 'posts';
	
	/**
	 * Helper to store the shortcode to insert on the target dialog when this GUI API action is 'create'.
	 *
	 * @since unknown
	 */
	self.shortcode_to_insert_on_target_dialog = '';
	
	/**
	 * Helper to store the selector where to append the shortcode when this GUI API action is 'append'.
	 *
	 * @since 2.3.0
	 */
	self.shortcode_to_append_selector = null;
	
	/**
	 * Button to append to inputs eligible for getting Fields and Views shortcodes.
	 *
	 * @since 2.3.0
	 */
	self.button_in_input = $( '<a class="wpv-fields-and-views-in-input js-wpv-fields-and-views-in-input">+</a>' );
	
	/**
	 * Canonical shortcodes that provide a GUI but are ecluded from editing.
	 *
	 * - wpv-for-each is excluded since it holds a wpv-post-field internal shortcode.
	 *
	 * @since 2.3.0
	 */
	self.shortcodes_with_gui_but_excluded_from_edit = [ 'wpv-for-each' ];
	
	/**
	 * Count of the shortcodes managed by this GUI API on the current request.
	 *
	 * @since unknown
	 */
	self.shortcode_gui_insert_count = 0;
	
	/**
	 * Old mechanism to filter attribute values for a shortcode.
	 *
	 * Used by the method self.filter_computed_attribute_pairs it allowed for external filtering of attribute values,
	 * by registering a callback for a shortcode tag.
	 *
	 * Keep for backwards compatibility, but deprecated.
	 *
	 * @since unkwnon
	 */
	self.shortcode_gui_computed_attribute_pairs_filters = {};

	self.views_conditional_qtags_opened	= false;
	self.views_conditional_use_gui		= true;

	/**
	 * Cache for the native post fields items as an array, so we only need to AJAX load it once.
	 *
	 * @since 2.3.2
	 */
	self.post_fields_list = false;
	
	/**
	 * Cache for the native post fields section items, so we only need to AJAX load it once.
	 *
	 * @since 1.10.0
	 */
	self.post_fields_section = false;

	/**
	 * Validation patterns
	 *
	 * @since unknown
	 * @todo Transform into a single object.
	 */
	self.numeric_natural_pattern				= /^[0-9]+$/;
	self.numeric_natural_list_pattern			= /^\d+(?:,\d+)*$/;
	self.numeric_natural_extended_pattern		= /^(-1|[0-9]+)$/;
	self.year_pattern							= /^([0-9]{4})$/;
	self.month_pattern							= /^([1-9]|1[0-2])$/;
	self.week_pattern							= /^([1-9]|[1234][0-9]|5[0-3])$/;
	self.day_pattern							= /^([1-9]|[12][0-9]|3[0-1])$/;
	self.hour_pattern							= /^([0-9]|[1][0-9]|2[0-3])$/;
	self.minute_pattern							= /^([0-9]|[1234][0-9]|5[0-9])$/;
	self.second_pattern							= /^([0-9]|[1234][0-9]|5[0-9])$/;
	self.dayofyear_pattern						= /^([1-9]|[1-9][0-9]|[12][0-9][0-9]|3[0-6][0-6])$/;
	self.dayofweek_pattern						= /^[1-7]+$/;
	self.url_patern								= /^(https?):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;
	self.orderby_postfield_pattern				= /^field-/;
	self.orderby_termmeta_field_pattern			= /^taxonomy-field-/;
	self.orderby_usermeta_field_pattern			= /^user-field-/;

	/**
	 * Temporary dialog content to be displayed while the actual content is loading.
	 *
	 * It contains a simple spinner in the centre. I decided to implement styling directly, it will not be reused and
	 * it would only bloat views-admin.css (jan).
	 *
	 * @type {HTMLElement}
	 * @since 1.9.0
	 */
	self.shortcodeDialogSpinnerContent = $(
		'<div style="min-height: 150px;">' +
		'<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; ">' +
		'<div class="wpv-spinner ajax-loader"></div>' +
		'<p>' + wpv_shortcodes_gui_texts.loading_options + '</p>' +
		'</div>' +
		'</div>'
	);
	
	/**
	 * Dialog content to be displayed while the actual content loading does fail.
	 *
	 * It contains a simple error message.
	 *
	 * @type {HTMLElement}
	 * @since 2.3.0
	 * @todo Proper messages depending on the failure cause.
	 */
	self.shortcodeDialogNonceError = $(
		'<div style="min-height: 150px;">' +
		'<div class="toolset-alert toolset-alert-error" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; ">' +
		'<p>' + wpv_shortcodes_gui_texts.nonce_error + '</p>' +
		'</div>' +
		'</div>'
	);
	
	/**
	 * #######################
	 * API functions
	 *
	 * @since 2.3.0
	 * #######################
	 */
	
	/**
	 * Get the current shortcodes GUI action.
	 *
	 * @see wpv-filter-wpv-shortcodes-gui-get-gui-action
	 *
	 * @since 2.3.0
	 */
	self.get_shortcode_gui_action = function( action ) {
		return self.shortcode_gui_insert;
	};
	
	/**
	 * Set the current shortcodes GUI action.
	 *
	 * @see wpv-action-wpv-shortcodes-gui-set-gui-action
	 *
	 * @since 2.3.0
	 */
	self.set_shortcode_gui_action = function( action ) {
		if ( $.inArray( action, self.shortcode_gui_valid_actions ) !== -1 ) {
			self.shortcode_gui_insert = action;
		}
	};
	
	self.get_shortcode_gui_target = function( target ) {
		return self.shortcodes_set;
	};
	
	/**
	 * Get the current shortcodes GUI API version.
	 *
	 * @see wpv-filter-wpv-shortcodes-gui-get-gui-api-version
	 *
	 * @since 2.3.0
	 */
	self.get_shortcode_gui_api_version = function( version ) {
		return self.shortcode_gui_api_version;
	};
	
	/**
	 * Do the current shortcodes GUI action.
	 *
	 * @see wpv-action-wpv-shortcodes-gui-do-gui-action
	 *
	 * @param object shortcode_data
	 *     shortcode	    string	The shortcode just processed.
	 *     name			    string	The name of the processed shortcode.
	 *     attributes	    object	A key => value set of attribute pairs.
	 *     raw_attributes	object	A key => value set of attribute pairs, as taken from the shortcode dialog.
	 *     content		    string	The shortcode content when it is not self-closing.
	 *
	 * @since 2.3.0
	 */
	self.do_shortcode_gui_action = function( shortcode_data ) {
		var defaults = {
				shortcode:		'',
				name:			'',
				attributes:		{},
				raw_attributes: {},
				content:		''
			},
			shortcode_data_safe		= _.defaults( shortcode_data, defaults );
		
		shortcode_data_safe = Toolset.hooks.applyFilters( 'wpv-filter-wpv-shortcodes-gui-before-do-action', shortcode_data_safe );
		
		var shortcode_name			= shortcode_data_safe.name,
			shortcode_atts			= shortcode_data_safe.attributes,
			shortcode_raw_atts		= shortcode_data_safe.raw_attributes,
			shortcode_content		= shortcode_data_safe.content,
			shortcode_string		= shortcode_data_safe.shortcode,
			shortcode_gui_action	= self.get_shortcode_gui_action();
		
		if ( 
			shortcode_name.length == 0 
			|| shortcode_string.length == 0
		) {
			return;
		}
		
		/**
		 * Custom action executed before performing the shortcodes GUI action.
		 *
		 * @param object shortcode_data_safe
		 *     shortcode	    string	The shortcode just processed.
		 *     name			    string	The name of the processed shortcode.
		 *     attributes	    object	A key => value set of attribute pairs.
		 *     raw_attributes	object	A key => value set of attribute pairs, as taken from the shortcode dialog.
		 *     content		    string	The shortcode content when it is not self-closing.
		 * @param string shortcode_gui_action The action to execute
		 *
		 * @since 2.4.0
		 */
		Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-before-do-action', shortcode_data_safe, shortcode_gui_action );
		
		switch ( shortcode_gui_action ) {
			case 'skip':
				break;
			case 'create':
				
				/**
				 * Backwards compatibility.
				 *
				 * Before Views 2.3.0 and Types 2.2.8, the Beaver Builder integration with Views 
				 * provided a mchanism to insert Fields and Views shortcodes into any text input 
				 * that has been replaced by the 'appent' action of this GUI.
				 * By then, the current action was 'create' as it pretended to be an admin bar 
				 * shortcodes generator clone.
				 * Given that Beaver Builder integrated its frontend editor and the admin bar 
				 * shortcodes generator is only available in the backend, adding this extra 
				 * check here should introduce no harm for the moment.
				 *
				 * @since 2.3.0
				 * @until 2.5.0
				 */
				
				if ( self.shortcode_to_append_selector != null ) {
					self.shortcode_to_append_selector.val( self.shortcode_to_append_selector.val() + shortcode_string );
					self.shortcode_to_append_selector = null;
				} else {
					self.shortcode_to_insert_on_target_dialog = shortcode_string;

					self.textarea_target_dialog.dialog( 'open' );
				
				}
					
				break;
			case 'append':
			
				if ( self.shortcode_to_append_selector != null ) {
					self.shortcode_to_append_selector.val( self.shortcode_to_append_selector.val() + shortcode_string );
					self.shortcode_to_append_selector = null;
				}
				
				break;
			case 'edit':
				
				if ( _.has( WPViews.ShortcodesParser_instance, window.wpcfActiveEditor ) ) {
					WPViews.ShortcodesParser_instance[ window.wpcfActiveEditor ]
						.set_selection( WPViews.ShortcodesParser_instance[ window.wpcfActiveEditor ].shortcode_data )
						.insert( shortcode_string );
				}
				
				break;
			case 'save':
				
				// Managed in the Loop Wizard script
				$( document ).trigger( 'js_event_wpv_shortcode_action_save_triggered', [ shortcode_data_safe ] );
				
				break;
			case 'insert':
			default:
				// When we are inserting a shortcode inside the Filter Editor, if the content of the Filter editor matches
				// the default content, we need to prepend and append a new line character to the inserted shortcode in order
				// to improve look and feel of the Filter Editor after the first filter shortcode is added.
				if ( 'wpv_filter_meta_html_content' === window.wpcfActiveEditor
					&& ( _.indexOf( wpv_parametric_i18n.form_filters_shortcodes, shortcode_data.name ) !== -1
						|| _.indexOf( [ 'wpv-filter-search-box', 'wpv-filter-submit', 'wpv-filter-reset', 'wpv-filter-spinner' ], shortcode_data.name ) !== -1 )
					&& WPV_Toolset.CodeMirror_instance[ window.wpcfActiveEditor ].getValue().indexOf( '[wpv-filter-controls][/wpv-filter-controls]') !==  -1 ) {
                    shortcode_string = '\n' + shortcode_string + '\n';
				}
				window.icl_editor.insert( shortcode_string );
				
				break;
		}
		
		/**
		 * Custom action executed after performing the shortcodes GUI action.
		 *
		 * @param object shortcode_data_safe
		 *     shortcode	    string	The shortcode just processed.
		 *     name			    string	The name of the processed shortcode.
		 *     attributes	    object	A key => value set of attribute pairs.
		 *     raw_attributes	object	A key => value set of attribute pairs, as taken from the shortcode dialog.
		 *     content		    string	The shortcode content when it is not self-closing.
		 * @param string shortcode_gui_action The action to execute
		 *
		 * @since 2.4.0
		 */
		Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-after-do-action', shortcode_data_safe, shortcode_gui_action );
		
		/**
		 * Custom event fired when a shortcode has been processed.
		 *
		 * @param object shortcode_data_safe
		 *     shortcode	string	The shortcode just processed.
		 *     name			string	The name of the processed shortcode.
		 *     attributes	object	A key => value set of attribute pairs.
		 *     raw_attributes	object	A key => value set of attribute pairs, as collected from the dialog.
		 *     content		string	The shortcode content when it is not self-closing.
		 *
		 * @since 2.3.0
		 */
		
		$( document ).trigger( 'js_event_wpv_shortcode_action_completed', [ shortcode_data_safe ] );
		
		// Increment the counter of shortcodes inserted since the last page request
		self.shortcode_gui_insert_count = self.shortcode_gui_insert_count + 1;
		
		// Set the shortcodes GUI action to its default 'insert'
		self.set_shortcode_gui_action( 'insert' );
		
	};
	
	/**
	 * Compatibility: Types event when a shortcode is created.
	 *
	 * @since unknown
	 */
	$( document ).on( 'js_types_shortcode_created', function( event, shortcode_to_insert ) {
		
		var shortcode_data = {
			shortcode:		shortcode_to_insert,
			name:			'types',
			attributes:		{},
			raw_attributes:	{},
			content:		''
		};
		
		self.do_shortcode_gui_action( shortcode_data );
		
	});
	
	/**
	 * Backwards compatibility: up until now, we triggerd this action when creating a shortcode, 
	 * and third parties (like Toolset Maps) used it when the relevant action was different from 'insert'.
	 *
	 * Note that when it was 'insert' all the magic happened on that rhird party, and that those third parties 
	 * are the ones triggering this event by themselves. This does not happen here anymore.
	 *
	 * @deprecated 2.3.0
	 */
	
	$( document ).on( 'js_event_wpv_shortcode_inserted', function( event, shortcode_name, shortcode_content, shortcode_attribute_values, shortcode_to_insert ) {
		
		var shortcode_gui_action = self.get_shortcode_gui_action(),
			shortcode_data = {
				shortcode:		shortcode_to_insert,
				name:			shortcode_name,
				attributes:		shortcode_attribute_values,
				raw_attributes:	shortcode_attribute_values,
				content:		shortcode_content
			};
		
		if ( shortcode_gui_action != 'insert' ) {
			self.do_shortcode_gui_action( shortcode_data );
		}
		
	});
	
	/**
	 * Maybe edit a shortcode, fired when the edit command is triggered but we still do not know 
	 * wheter we do have an editable shortcode under the cursor.
	 *
	 * @param editor The editor ID that should contain a shortcode under the cursor.
	 *
	 * @since 2.3.0
	 */
	self.maybe_edit_shortcode = function( editor ) {
		
		var shortcode_data,
			dialog_data = {};
		
		if ( ! _.has( WPViews.ShortcodesParser_instance, editor ) ) {
			WPViews.ShortcodesParser_instance[ editor ] = new WPViews.ShortcodesParser( $( '#' + editor ) );
		}
		
		// Build a parsers object using editor as key, so we get a parser on demand when needed, and parse at each time :-)
		shortcode_data	= WPViews.ShortcodesParser_instance[ editor ].parse();
		
		shortcode_data = Toolset.hooks.applyFilters( 'wpv-filter-wpv-shortcodes-gui-maybe-edit-shortcode-data', shortcode_data );
		
		if ( 
			_.contains( wpv_shortcodes_gui_texts.shortcodes_with_gui, shortcode_data.tag ) 
			&& ! _.contains( self.shortcodes_with_gui_but_excluded_from_edit, shortcode_data.tag ) 
		) {
			window.wpcfActiveEditor = editor;
		
			self.set_shortcode_gui_action( 'edit' );
			
			self.edit_shortcode( shortcode_data );
		}
		
	}
	
	/**
	 * Edit a shortcode, opening the dialog to modify its attributes.
	 *
	 * @param shortcode_data object
	 *     shortcode	string	The shortcode tag.
	 *     title		string	The dialog title, defaults to wpv_shortcodes_gui_texts.loading_options
	 *     params		object	Fixed initial parameters that should be forced.
	 *     overrides	object	Parameters to override on the edting dialog:
	 *         content		string	The shortcode content, if any.
	 *         atts.named	object	Pairs of attribute names and values.
	 *
	 * @since 2.3.0
	 */
	self.edit_shortcode = function( shortcode_data ) {
		var dialog_data = {
				shortcode:	shortcode_data.tag,
				title:		wpv_shortcodes_gui_texts.loading_options,
				params:		{},
				overrides:	{ content: shortcode_data.content, attributes: shortcode_data.attrs.named }
			};
		
		self.wpv_insert_shortcode_dialog_open( dialog_data );
	};
	
	self.edit_legacy_custom_search_shortcodes = function( shortcode_data ) {
		if ( shortcode_data.tag == 'wpv-control' ) {
			// Support editing legacy wpv-control shortcodes:
			// This will read them and transform their os attributes to the new ones, 
			// adjusting the 'output' arrribute value as `legacy'
			if ( ! _.has( shortcode_data.attrs.named, 'output' ) ) {
				shortcode_data.attrs.named.output = 'legacy';
			}
			if ( _.has( shortcode_data.attrs.named, 'taxonomy' ) ) {
				shortcode_data.tag = 'wpv-control-post-taxonomy';
				if ( _.has( shortcode_data.attrs.named, 'taxonomy_orderby' ) ) {
					shortcode_data.attrs.named.orderby = shortcode_data.attrs.named.taxonomy_orderby;
					delete shortcode_data.attrs.named['taxonomy_orderby'];
				}
				if ( _.has( shortcode_data.attrs.named, 'taxonomy_order' ) ) {
					shortcode_data.attrs.named.order = shortcode_data.attrs.named.taxonomy_order;
					delete shortcode_data.attrs.named['taxonomy_order'];
				}
			} else {
				shortcode_data.tag = 'wpv-control-postmeta';
			}
		} else if ( shortcode_data.tag == 'wpv-control-set' ) {
			shortcode_data.tag = 'wpv-control-post-relationship';
			if ( ! _.has( shortcode_data.attrs.named, 'output' ) ) {
				shortcode_data.attrs.named.output = 'legacy';
			}
		} else if (
			shortcode_data.tag == 'wpv-filter-submit' 
			|| shortcode_data.tag == 'wpv-filter-reset' 
		) {
			if ( ! _.has( shortcode_data.attrs.named, 'output' ) ) {
				shortcode_data.attrs.named.output = 'legacy';
			}
		} else if ( shortcode_data.tag == 'wpv-filter-search-box' ) {
			if ( ! _.has( shortcode_data.attrs.named, 'output' ) ) {
				shortcode_data.attrs.named.output = 'legacy';
			}
		}
		return shortcode_data;
	};
	
	self.get_post_fields_list = function( list ) {
		return self.post_fields_list;
	};
	
	self.set_post_fields_list = function( list ) {
		self.post_fields_list = list;
	};
	
	self.shortcodes_gui_dialog_block = function( dialog_container, block_message ) {
		var block_structure = '';
		
		block_structure += '<div class="wpv-shortcode-gui-dialog-container-overlay">';
			block_structure += '<div class="wpv-transparency"></div>';
			block_structure += '<i class="icon-lock fa fa-lock"></i>';
			block_structure += '<div class="toolset-alert toolset-alert-error">' + block_message + '</div>';
		block_structure += '</div>';
		
		dialog_container
			.siblings( '.ui-dialog-buttonpane' )
				.find( '.js-wpv-shortcode-gui-insert' )
					.addClass( 'button-secondary' )
					.removeClass( 'button-primary' )
					.prop( 'disabled', true );
		dialog_container
			.find( '.js-shortcode-gui-field' )
					.prop( 'disabled', true );
		dialog_container
			.find( '.wpv-dialog' )
				.html( '<div class="wpv-shortcode-gui-dialog-hijack-content"></div>' )
				.append( $( block_structure ) );
		
		return self;
	};
	
	/**
	 *
	 * @param data object The data we have about this scenario.
	 *     shortcode	string	The shortcode tag.
	 *     title		string	The dialog title.
	 *     params		object	The initial fixed attributes for this shortcode.
	 *     overrides	object	The attributes key->value pairs to enforce when editing a shortcode.
	 *     nonce		string	The nonce used to populate the dialog.
	 *     dialog		object	The jQuery UI dialog that was just opened.
	 *
	 *
	 * @since 2.4.0
	 */
	self.maybe_block_toolset_edit_link_dialog = function( data ) {
		if ( $( '#shortcode-gui-content-' + data.shortcode ).length == 0 ) {
			var error_message = $( '<p />').append( $( '.' + data.shortcode + '-information' ).clone() ).html(),
				error_container = $( '.js-insert-' + data.shortcode + '-dialog' )
					.closest( '.js-wpv-shortcode-gui-dialog-container' );
			self.shortcodes_gui_dialog_block( error_container, error_message );
		}
	}
	
	/**
	 * #######################
	 * API hooks
	 *
	 * @since 2.3.0
	 * #######################
	 */
	
	
	/**
	 * Register the canonical Toolst hooks, both API filters and actions.
	 *
	 * @since 2.3.0
	 */
	self.init_hooks = function() {
		
		/**
		 * ###############################
		 * API filters
		 * ###############################
		 */
		
		/**
		 * Return the current shortcodes GUI action: 'insert', 'create', 'append'.
		 *
		 * @since 2.3.0
		 */
		Toolset.hooks.addFilter( 'wpv-filter-wpv-shortcodes-gui-get-gui-action', self.get_shortcode_gui_action );
		
		/**
		 * Return the current shortcodes GUI target: 'posts', 'taxonomy', 'users'.
		 *
		 * @since m2m
		 */
		Toolset.hooks.addFilter( 'wpv-filter-wpv-shortcodes-gui-get-gui-target', self.get_shortcode_gui_target );
		
		/**
		 * Return the current shortcodes GUI API version.
		 *
		 * @since 2.3.0
		 */
		Toolset.hooks.addFilter( 'wpv-filter-wpv-shortcodes-gui-get-gui-api-version', self.get_shortcode_gui_api_version );
		
		/**
		 * Extend the data to be sent when opening the Views shortcodes dialog.
		 *
		 * @since 2.3.0
		 */
		Toolset.hooks.addFilter( 'wpv-filter-wpv-shortcodes-gui-extend-shortcode-dialog-data', self.extend_shortcode_dialog_data, 10, 2 );
		
		/**
		 * Extend the shortcode edit capability to old filter shortcodes.
		 *
		 * @since 2.4.0
		 */
		Toolset.hooks.addFilter( 'wpv-filter-wpv-shortcodes-gui-maybe-edit-shortcode-data', self.edit_legacy_custom_search_shortcodes );
		
		/**
		 * Get the cached native post fields list.
		 *
		 * @since 2.4.0
		 */
		Toolset.hooks.addFilter( 'wpv-filter-wpv-shortcodes-gui-get-post-fields-list', self.get_post_fields_list );
		
		/**
		 * ###############################
		 * API actions
		 * ###############################
		 */
		
		/**
		 * Set the current shortcodes GUI action: 'insert', 'create', 'append', 'skip'.
		 *
		 * @since 2.3.0
		 */
		Toolset.hooks.addAction( 'wpv-action-wpv-shortcodes-gui-set-gui-action', self.set_shortcode_gui_action );
		
		/**
		 * Open the Fields and Views dialog, on demand.
		 *
		 * @since m2m
		 */
		Toolset.hooks.addAction( 'wpv-action-wpv-fields-and-views-dialog-do-open', self.open_fields_and_views_dialog );
		
		/**
		 * Close the Fields and Views dialog, if opened, on demand.
		 *
		 * @since m2m
		 */
		Toolset.hooks.addAction( 'wpv-action-wpv-fields-and-views-dialog-do-maybe-close', self.maybe_close_fields_and_views_dialog );
		
		/**
		 * Open the shortcodes GUI dialog, on demand.
		 *
		 * @since 2.4.0
		 */
		Toolset.hooks.addAction( 'wpv-action-wpv-shortcodes-gui-open-shortcode-dialog', self.wpv_insert_shortcode_dialog_open );
		
		/**
		 * Adjust the dialog for inserting shortcodes.
		 *
		 * @since 2.3.0
		 */
		Toolset.hooks.addAction( 'wpv-action-wpv-shortcodes-gui-shortcode-dialog-preloaded', self.after_preload_shortcode_dialog );
		
		/**
		 * Adjust the dialog for inserting shortcodes.
 		 *
 		 * @since 2.3.0
 		 */
		Toolset.hooks.addAction( 'wpv-action-wpv-shortcodes-gui-after-open-shortcode-dialog', self.after_open_shortcode_dialog );
		
		/**
		 * Init select2 instances in the shortcodes dialog.
		 *
		 * @since m2m
		 */
		Toolset.hooks.addAction( 'wpv-action-wpv-shortcodes-gui-after-open-shortcode-dialog', self.initSelect2 );
		
		/**
		 * Act upon the generated shortcode according to the current shortcodes GUI action: 'insert', 'create', 'append'.
		 *
		 * @since 2.3.0
		 */
		Toolset.hooks.addAction( 'wpv-action-wpv-shortcodes-gui-do-gui-action', self.do_shortcode_gui_action );
		
		/**
		 * Try to edit the shortcode under the cursor in the currently active editor, if possible.
		 *
		 * @since 2.3.0
		 */
		Toolset.hooks.addAction( 'wpv-action-wpv-shortcodes-gui-maybe-edit-shortcode', self.maybe_edit_shortcode );
		
		/**
		 * Edit the shortcode which parsed data gets passed to the callback.
		 *
		 * @since 2.3.0
		 */
		Toolset.hooks.addAction( 'wpv-action-wpv-shortcodes-gui-edit-shortcode', self.edit_shortcode );
		
		/**
		 * Set the cached native post fields list.
		 *
		 * @since 2.4.0
		 */
		Toolset.hooks.addAction( 'wpv-action-wpv-shortcodes-gui-set-post-fields-list', self.set_post_fields_list );
		
		/**
		 * Block a dialog to insert a shortode, on demand.
		 *
		 * @since 2.4.0
		 */
		Toolset.hooks.addAction( 'wpv-action-wpv-shortcodes-gui-block-dialog', self.shortcodes_gui_dialog_block, 10, 2 );
		
		/**
		 * Maybe block a dialog to insert a Toolset edit link shortode, on demand.
		 *
		 * @since 2.4.0
		 */
		Toolset.hooks.addAction( 'wpv-action-wpv-shortcodes-gui-after-open-toolset-edit-post-link-shortcode-dialog', self.maybe_block_toolset_edit_link_dialog );
		Toolset.hooks.addAction( 'wpv-action-wpv-shortcodes-gui-after-open-toolset-edit-user-link-shortcode-dialog', self.maybe_block_toolset_edit_link_dialog );
		
		return self;
		
	};

	/**
	 * #######################
	 * API dialogs
	 *
	 * @since 2.3.0
	 * #######################
	 */
	
	self.init_dialogs = function() {
		
		/**
		 * Canonical dialog to insert Views shortcodes with attributes, except wpv-view and wpv-form-view.
		 *
		 * @since unknown
		 */
		if ( ! $( '#js-wpv-shortcode-gui-dialog-container' ).length ) {
			$( 'body' ).append( '<div id="js-wpv-shortcode-gui-dialog-container" class="toolset-shortcode-gui-dialog-container wpv-shortcode-gui-dialog-container js-wpv-shortcode-gui-dialog-container"></div>' );
		}
		self.dialog_insert_shortcode = $( "#js-wpv-shortcode-gui-dialog-container" ).dialog({
			dialogClass: 'toolset-ui-dialog toolset-ui-dialog-responsive',
			autoOpen:	false,
			modal:		true,
			width:		'90%',
			resizable:	false,
			draggable:	false,
			show: {
				effect:		"blind",
				duration:	800
			},
			open: function( event, ui ) {
				$( 'body' ).addClass( 'modal-open' );
				self.repositionDialog();
				$( '.js-wpv-shortcode-gui-insert' )
					.addClass( 'button-secondary' )
					.removeClass( 'button-primary ui-button-disabled ui-state-disabled' )
					.prop( 'disabled', true );
			},
			close: function( event, ui ) {
				$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_closed' );
				$( 'body' ).removeClass( 'modal-open' );
			},
			buttons:[
				{
					class: 'toolset-shortcode-gui-dialog-button-align-right button-secondary js-wpv-shortcode-gui-button-insert js-wpv-shortcode-gui-insert',
					text: wpv_shortcodes_gui_texts.wpv_insert_shortcode,
					disabled: 'disabled',
					click: function() {
						self.wpv_insert_shortcode();
					}
				},
				{
					class: 'toolset-shortcode-gui-dialog-button-align-right button-secondary toolset-shortcode-gui-dialog-button-back js-wpv-shortcode-gui-button-back js-wpv-shortcode-gui-back',
					text: wpv_shortcodes_gui_texts.wpv_back,
					click: function() {
						$( this ).dialog( "close" );
						self.open_fields_and_views_dialog();
					}
				},
				{
					class: 'button-secondary toolset-shortcode-gui-dialog-button-close js-wpv-shortcode-gui-button-close js-wpv-shortcode-gui-close',
					text: wpv_shortcodes_gui_texts.wpv_cancel,
					click: function() {
						$( this ).dialog( "close" );
					}
				}
			]
		});

		/**
		 * Canonical dialog to insert wpv-view and wpv-form-view shortcodes with attributes.
		 *
		 * @since unknown
		 */
		if ( ! $( '#js-wpv-view-shortcode-gui-dialog-container' ).length ) {
			$( 'body' ).append( '<div id="js-wpv-view-shortcode-gui-dialog-container" class="toolset-shortcode-gui-dialog-container wpv-shortcode-gui-dialog-container js-wpv-shortcode-gui-dialog-container"></div>' );
		}
		self.dialog_insert_view = $( "#js-wpv-view-shortcode-gui-dialog-container" ).dialog({
			dialogClass: 'toolset-ui-dialog toolset-ui-dialog-responsive',
			autoOpen:	false,
			modal:		true,
			width:		'90%',
			resizable:	false,
			draggable:	false,
			show: {
				effect:		"blind",
				duration:	800
			},
			open: function( event, ui ) {
				$( 'body' ).addClass( 'modal-open' );
				self.repositionDialog();
				$( '.js-wpv-insert-view-form-action' )
					.addClass( 'button-secondary' )
					.removeClass( 'button-primary ui-button-disabled ui-state-disabled' )
					.prop( 'disabled', true );
			},
			close: function( event, ui ) {
				$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_closed' );
				$( 'body' ).removeClass( 'modal-open' );
			},
			buttons:[
				{
					class: 'toolset-shortcode-gui-dialog-button-align-right button-secondary js-wpv-shortcode-gui-button-insert js-wpv-insert-view-form-action',
					text: wpv_shortcodes_gui_texts.wpv_insert_shortcode,
					disabled: 'disabled',
					click: function() {
						self.wpv_insert_view_shortcode_to_editor();
					}
				},
				{
					class: 'toolset-shortcode-gui-dialog-button-align-right button-secondary toolset-shortcode-gui-dialog-button-back js-wpv-shortcode-gui-button-back js-wpv-shortcode-gui-back',
					text: wpv_shortcodes_gui_texts.wpv_back,
					click: function() {
						$( this ).dialog( "close" );
						self.open_fields_and_views_dialog();
					}
				},
				{
					class: 'button-secondary toolset-shortcode-gui-dialog-button-close js-wpv-shortcode-gui-button-close',
					text: wpv_shortcodes_gui_texts.wpv_cancel,
					click: function() {
						$( this ).dialog( "close" );
					}
				}
			]
		});

		/**
		 * Canonical dialog to insert conditional shortcodes with attributes.
		 *
		 * @since unknown
		 */
		if ( ! $( '#js-wpv-views-conditional-shortcode-gui-dialog-container' ).length ) {
			$( 'body' ).append( '<div id="js-wpv-views-conditional-shortcode-gui-dialog-container" class="toolset-shortcode-gui-dialog-container wpv-shortcode-gui-dialog-container js-wpv-shortcode-gui-dialog-container"></div>' );
		}
		self.dialog_insert_views_conditional = $( "#js-wpv-views-conditional-shortcode-gui-dialog-container" ).dialog({
			dialogClass: 'toolset-ui-dialog toolset-ui-dialog-responsive',
			autoOpen:	false,
			modal:		true,
			width:		'90%',
			resizable:	false,
			draggable:	false,
			show: {
				effect:		"blind",
				duration:	800
			},
			open: function( event, ui ) {
				$( 'body' ).addClass( 'modal-open' );
				self.repositionDialog();
				$( ".ui-dialog-titlebar-close" ).hide();
				self.views_conditional_use_gui = true;
				$( '.js-wpv-shortcode-gui-insert' )
					.addClass( 'button-secondary' )
					.removeClass( 'button-primary ui-button-disabled ui-state-disabled' )
					.prop( 'disabled', true );
			},
			close: function( event, ui ) {
				if (  !self.views_conditional_qtags_opened && typeof self.wpv_conditional_object.ed.openTags !== 'undefined' ){
					var ed = self.wpv_conditional_object.ed, ret = false, i = 0;
					self.views_conditional_qtags_opened = false;
					while ( i < ed.openTags.length ) {
						ret = ed.openTags[i] == self.wpv_conditional_object.t.id ? i : false;
						i ++;
					}
					ed.openTags.splice(ret, 1);
					self.wpv_conditional_object.e.value = self.wpv_conditional_object.t.display;
				}
				$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_closed' );
				$( 'body' ).removeClass( 'modal-open' );
			},
			buttons:[
				{
					class: 'toolset-shortcode-gui-dialog-button-align-right button-secondary js-wpv-shortcode-gui-insert',
					text: wpv_shortcodes_gui_texts.wpv_insert_shortcode,
					disabled: 'disabled',
					click: function() {
						self.wpv_insert_view_conditional_shortcode();
					}
				},
				{
					class: 'button-secondary toolset-shortcode-gui-dialog-button-close js-wpv-shortcode-gui-close',
					text: wpv_shortcodes_gui_texts.wpv_cancel,
					click: function() {
						// remove wpv-conditional from QTags:opened tags
						self.wpv_conditional_close = false;
						self.views_conditional_qtags_opened = false;
						if ( !self.views_conditional_qtags_opened && typeof self.wpv_conditional_object.openTags !== 'undefined' ) {
							var ed = self.wpv_conditional_object.ed, ret = false, i = 0;
							while ( i < ed.openTags.length ) {
								ret = ed.openTags[i] == self.wpv_conditional_object.t.id ? i : false;
								i ++;
							}
							ed.openTags.splice(ret, 1);
							self.wpv_conditional_object.e.value = self.wpv_conditional_object.t.display;
						}
						$( this ).dialog( "close" );
					}
				}
			]
		});

		/**
		 * Canonical dialogs to offer fields for posts, taxonomy terms and users,
		 * they depend on the existence of the dialog HTML structure.
		 *
		 * @since unknown
		 * @since 2.4.1 	A small refactor was made on this method and also added the clearance of the "Search" box
		 * 					upon dialog closing.
		 */
		var body = $( 'body' ),
			dialog = {
				posts : body.find('.js-wpv-fields-and-views-dialog-for-posts'),
				taxonomy: body.find('.js-wpv-fields-and-views-dialog-for-taxonomy'),
				users: body.find('.js-wpv-fields-and-views-dialog-for-users')
			},
            initFieldsAndViewsDialog = function( contentType ) {
                return $( '.js-wpv-fields-and-views-dialog-for-' + contentType ).dialog({
                    dialogClass: 'toolset-ui-dialog toolset-ui-dialog-responsive',
					autoOpen:	false,
					modal:		true,
					width:		'90%',
                    title:		wpv_shortcodes_gui_texts.wpv_fields_and_views_title,
                    resizable:	false,
                    draggable:	false,
                    show: {
                        effect:		"blind",
                        duration:	800
                    },
                    open: function( event, ui ) {
                        $( 'body' ).addClass('modal-open');
						self.repositionDialog();
                        // Hide top links if div too small
                        wpv_hide_top_groups( $( this ).parent() );
                        $( dialog[contentType] )
                            .find( '.search_field' )
                            .focus();
                        var data_for_events = {};
                        data_for_events.kind = contentType;
                        data_for_events.dialog = this;
                        $( document ).trigger( 'js_event_wpv_fields_and_views_dialog_opened', [ data_for_events ] );
                    },
                    close: function( event, ui ) {
                        $( 'body' ).removeClass( 'modal-open' );
                        $( dialog[contentType] )
                            .find( '.search_field' )
                            .val('')
                            .keyup();
                        $( this ).dialog("close");
                    }
                });
            };

		if ( dialog.posts.length > 0 ) {
			self.shortcodes_wrapper_dialogs[ 'posts' ] = initFieldsAndViewsDialog( 'posts' );
		}

		if ( dialog.taxonomy.length > 0 ) {
			self.shortcodes_wrapper_dialogs[ 'taxonomy' ] = initFieldsAndViewsDialog( 'taxonomy' );
		}

		if ( dialog.users.length > 0 ) {
			self.shortcodes_wrapper_dialogs[ 'users' ] = initFieldsAndViewsDialog( 'users' );
		}

		/**
		 * Canonical dialog to create shortcodes from the Admin Bar shortcodes generator, if any.
		 *
		 * The DOM element is created by the shortcodes generator class itself.
		 *
		 * @since unknown
		 */
		self.textarea_target_dialog = $( '#wpv-shortcode-generator-target-dialog' ).dialog({
			dialogClass: 'toolset-ui-dialog toolset-ui-dialog-responsive',
			autoOpen:	false,
			modal:		true,
			width:		'90%',
			title:		wpv_shortcodes_gui_texts.wpv_shortcode_generated,
			resizable:	false,
			draggable:	false,
			show: {
				effect:		"blind",
				duration:	800
			},
			open: function( event, ui ) {
				self.maybe_close_fields_and_views_dialog();
				$( '#wpv-shortcode-generator-target' )
					.html( self.shortcode_to_insert_on_target_dialog )
					.focus();
				$('body').addClass('modal-open');
				self.repositionDialog();
			},
			close: function( event, ui ) {
				$( 'body' ).removeClass( 'modal-open' );
				self.set_shortcode_gui_action( 'insert' );
				$( this ).dialog( 'close' );
			}
		});
		
		$( window ).resize( self.resizeWindowEvent );
		
		return self;
		
	};
	
	/**
	 * Callback for the window.resize event.
	 *
	 * @since m2m
	 */
	self.resizeWindowEvent = _.debounce( function() {
		self.repositionDialog();
	}, 200);

	/**
	 * Reposition the Types dialogs based on the current window size.
	 *
	 * @since m2m
	 */
	self.repositionDialog = function() {
		var winH = $( window ).height() - 100,
			position = {
				my:        "center top+50",
				at:        "center top",
				of:        window,
				collision: "none"
			};
		
		
		_.each( self.shortcodes_wrapper_dialogs, function( domainDialog, domain, list ) {
			domainDialog.dialog( "option", "maxHeight", winH );
			domainDialog.dialog( "option", "position", position );
		});
		
		self.dialog_insert_shortcode.dialog( "option", "maxHeight", winH );
		self.dialog_insert_shortcode.dialog( "option", "position", position );
		
		self.dialog_insert_view.dialog( "option", "maxHeight", winH );
		self.dialog_insert_view.dialog( "option", "position", position );
		
		self.dialog_insert_views_conditional.dialog( "option", "maxHeight", winH );
		self.dialog_insert_views_conditional.dialog( "option", "position", position );
		
		self.textarea_target_dialog.dialog( "option", "maxHeight", winH );
		self.textarea_target_dialog.dialog( "option", "position", position );
		
	};

	//-----------------------------------------
	// Fields and Views button and dialog management
	//-----------------------------------------

	/**
	 * Init the Admin Bar button, if any, and make sure we load the right dialog when editing a View.
	 *
	 * @since 1.10.0
	 * @since 2.3.0 Enforce the 'taxonomy' dialog on term edit pages.
	 * @since 2.3.0 Enforce the 'users' dialog on users create and edit pages, plus own profile page.
	 */

	self.init_admin_bar_button = function() {
		if ( $( '.js-wpv-shortcode-generator-node a' ).length > 0 ) {
			$( '.js-wpv-shortcode-generator-node a' )
				.addClass( 'js-wpv-fields-and-views-in-adminbar' )
				.removeClass( 'js-wpv-fields-and-views-in-toolbar' );
		}
		if ( $( '.js-wpv-query-type' ).length > 0 ) {
			self.shortcodes_set = $( '.js-wpv-query-type:checked' ).val();
			$( document ).on( 'change', '.js-wpv-query-type', function() {
				self.shortcodes_set = $( '.js-wpv-query-type:checked' ).val();
			});
		} else if ( 
			wpv_shortcodes_gui_texts.pagenow == 'term.php' 
			|| wpv_shortcodes_gui_texts.pagenow == 'edit-tags.php' 
		) {
			self.shortcodes_set = 'taxonomy';
		} else if ( 
			wpv_shortcodes_gui_texts.pagenow == 'user-new.php'
			|| wpv_shortcodes_gui_texts.pagenow == 'user-edit.php'
			|| wpv_shortcodes_gui_texts.pagenow == 'profile.php'
		) {
			self.shortcodes_set = 'users';
		}
	};

	$( document ).on( 'click', '.toolset-shortcodes-shortcode-menu', function( e ) {
		e.preventDefault();
		return false;
	});
	
	/**
	 * Init the eligible textfield inputs shortcodes generator on focus.
	 *
	 * @since 2.3.0
	 */
	self.init_input_button = function() {
		wpv_shortcodes_gui_texts.integrated_inputs = wpv_shortcodes_gui_texts.integrated_inputs || [];
		$.each( wpv_shortcodes_gui_texts.integrated_inputs, function( key, input_selector ) {
            $( document )
				.on( 'focus', input_selector, function() {
					// @todo check whether this takes the focus out of the input
					if ( $( this ).parent( '.js-wpv-fields-and-views-in-input-wrapper' ).length == 0 ) {
						$( this ).wrap( '<span class="js-wpv-fields-and-views-in-input-wrapper" style="display:inline; position:relative;"></span>' );
					}
					$( this )
						.addClass( 'js-wpv-fields-and-views-in-input-target' )
						.before( self.button_in_input.css( 'display', 'block' ) );
				});
        });
	};

	/**
	 * Set the right active editor when clicking any Fields and Views button, and open / close the dialogs when needed.
	 *
	 * Acceptable selectors to trigger actions are:
	 * - Admin Bar: .js-wpv-fields-and-views-in-adminbar
	 * - Editor Toolbar: .js-wpv-fields-and-views-in-toolbar
	 * - Textfield input: .js-wpv-fields-and-views-in-input
	 */
	$( document ).on( 'click','.js-wpv-fields-and-views-in-adminbar', function( e ) {
		e.preventDefault();
		self.set_shortcode_gui_action( 'create' );
		Toolset.hooks.doAction( 'toolset-action-set-shortcode-gui-action', 'create' );
		self.open_fields_and_views_dialog();
		return false;
	});

	$( document ).on( 'click', '.js-wpv-fields-and-views-in-toolbar', function( e ) {
		e.preventDefault();
		var thiz = $( this );
		if ( thiz.attr( 'data-editor' ) ) {
			window.wpcfActiveEditor = thiz.data( 'editor' );
		}
		self.set_shortcode_gui_action( 'insert' );
		self.open_fields_and_views_dialog();
		return false;
	});
	
	$( document ).on( 'click', '.js-wpv-fields-and-views-in-input', function( e ) {
		e.preventDefault();
		var thiz = $( this );
		if ( 
			thiz.parent( '.js-wpv-fields-and-views-in-input-wrapper' ).length > 0 
			&& thiz.parent( '.js-wpv-fields-and-views-in-input-wrapper' ).find( '.js-wpv-fields-and-views-in-input-target' ).length > 0
		) {
			/**
			 * Backwards compatibility.
			 *
			 * Before Views 2.3.0 and Types 2.2.8, the Beaver Builder integration with Views 
			 * provided a mchanism to insert Fields and Views shortcodes into any text input 
			 * that has been replaced by the 'appent' action of this GUI.
			 * By then, the current action was 'create' as it pretended to be an admin bar 
			 * shortcodes generator clone.
			 * Given that Beaver Builder integrated its frontend editor and the admin bar 
			 * shortcodes generator is only available in the backend, pretend that 'append' 
			 * is 'create'  should not cause any harm by now.
			 *
			 * @since 2.3.0
			 * @until 2.5.0
			 */
			self.set_shortcode_gui_action( 'create' );
			//self.set_shortcode_gui_action( 'append' );
			self.shortcode_to_append_selector = thiz.parent( '.js-wpv-fields-and-views-in-input-wrapper' ).find( '.js-wpv-fields-and-views-in-input-target' );
			self.open_fields_and_views_dialog();
		}
		return false;
	});

	/**
	 * Open the Fields and Views dialog, depending on the current target.
	 * Also, close it when pressing ESC or when clicking on any of its field items.
	 *
	 * @since unknown
	 */
	self.open_fields_and_views_dialog = function() {
		if ( _.has( self.shortcodes_wrapper_dialogs, self.shortcodes_set ) ) {
			self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog( 'open' );
		}
		// Bind Escape
		$( document ).bind( 'keyup', function( e ) {
			if ( e.keyCode == 27 ) {
				self.maybe_close_fields_and_views_dialog();
				$( this ).unbind( e );
			}
		});
	};
	
	/**
	 * Close the Fields and Views dialog, if it is opened.
	 *
	 * @since m2m
	 */
	self.maybe_close_fields_and_views_dialog = function() {
		if ( 
			_.has( self.shortcodes_wrapper_dialogs, self.shortcodes_set ) 
			&& self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog( "isOpen" ) 
		) {
			self.shortcodes_wrapper_dialogs[ self.shortcodes_set ].dialog('close');
		}
	};
	
	$( document ).on( 'click', '.js-wpv-fields-views-dialog-content .item', function( event, data ) {
		self.maybe_close_fields_and_views_dialog();
	});
	
	/**
	* Scroll the Fields and Views dialog when clicking on a header menu item
	*
	* @since 2.2.0
	*/
	
	$( document ).on( 'click','.editor-addon-top-link', function() {
		
        var thiz	= $( this ),
		scrolling	= thiz.closest('.wpv-fields-and-views-dialog'),
        scrollingto	= scrolling.find( '.' + thiz.data('editor_addon_target' )+'-target' ),
        position	= scrollingto.position(),
        scrollto	= position.top;

        scrolling.animate({
            scrollTop: Math.round( scrollto ) - 25
        }, 'fast');

    });

	//-----------------------------------------
	// Views wpv-view and wpv-form-view shortcodes dialog management
	//-----------------------------------------
	
	/**
	 * Open the dialog to insert a wpv-view or wpv-form-view Views shortcode.
	 *
	 * Since 2.3.0 we will use self.wpv_insert_view_shortcode_dialog_open instead, because it has some benefits:
	 * - Takes a single object as attribute, hence it extendable without refactoring.
	 * - Gets rid of the obsolete 'nonce' attribute as we use a single, unique one from localization.
	 *
	 * @param view_id		integer	The View ID
	 * @param view_title	string	The View title
	 * @param view_name		string	The View slug
	 * @param orig_id		integer	The object ID where this dialog is opened, if any
	 * @param nonce			string	A nonce for secuting the AJAX call. Deprecated, to remove.
	 *
	 * @since unknown
	 * @deprecated 2.3.0
	 * @until 2.5.0
	 */

	self.wpv_insert_view_shortcode_dialog = function( view_id, view_title, view_name, orig_id, nonce ) {
		
		var dialog_data = {
			view_id:	view_id,
			view_title:	view_title,
			view_name:	view_name,
			params:		{},
			overrides:	{}
		};
		
		self.wpv_insert_view_shortcode_dialog_open( dialog_data );
		
	};
	
	/**
	 * Display a dialog for inserting a wpv-view or wpr-form-view shortcode.
	 *
	 * @param object dialog_data 
	 *     shortcode	string	Shortcode name
	 *     title 		string	Dialog title, also the View title.
	 *     name			string	View name.
	 *     params		object	Optional. Hidden parameters to enforce as attributes for the resulting shortcode.
	 *     overrides	object	Optional. Attribute values to override/enforce, mainly when editing a shortcode.
	 *
	 * @since 2.3.0
	 * @todo Attributes params and overrides have no meaning now, but wpv-view and wpv-form-view shortcodes will be editable soon too.
	 */
	self.wpv_insert_view_shortcode_dialog_open = function( dialog_data ) {
		
		_.defaults( dialog_data, { params: {}, overrides: {} } );
		
		var view_id		= dialog_data.view_id,
			view_title	= dialog_data.view_title,
			view_name	= dialog_data.view_name,
			params		= dialog_data.params,
			overrides	= dialog_data.overrides,
			data_view = {
				action:		'wpv_view_form_popup',
				_wpnonce:	wpv_shortcodes_gui_texts.wpv_editor_callback_nonce,
				view_id:	view_id,
				orig_id:	self.ps_orig_id,
				view_title:	view_title,
				view_name:	view_name
			},
			data_for_shortcode_wpv_view_dialog_requested_opened = {
				shortcode:	'wpv-views',
				title:		view_title,
				params:		{},
				overrides:	{},
				nonce:		wpv_shortcodes_gui_texts.wpv_editor_callback_nonce,
				dialog:		self.dialog_insert_view
			};
		
		self.ps_view_id = view_id;
		
		Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-shortcode-dialog-requested', data_for_shortcode_wpv_view_dialog_requested_opened );
		Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-shortcode-wpv-view-dialog-requested', data_for_shortcode_wpv_view_dialog_requested_opened );
		// Legacy, leave for backwards compatibility
		$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_triggered', [ data_for_shortcode_wpv_view_dialog_requested_opened ] );

		self.dialog_insert_view.dialog( 'open' ).dialog({
			title: view_title
		});

		Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-shortcode-dialog-preloaded', data_for_shortcode_wpv_view_dialog_requested_opened );
		Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-shortcode-wpv-view-dialog-preloaded', data_for_shortcode_wpv_view_dialog_requested_opened );
		
		self.dialog_insert_view.html( self.shortcodeDialogSpinnerContent );
		
		$.ajax({
			url:		wpv_shortcodes_gui_texts.ajaxurl,
			data:		data_view,
			type:		"GET",
			success:	function( response ) {
				// Ensure the body has this modal-open class, 
				// as another prior dialog closing while opening this one might remove it
				$( 'body' ).addClass( 'modal-open' );
				self.dialog_insert_view.html( response );
				$( '.js-wpv-insert-view-form-action' )
					.addClass( 'button-primary' )
					.removeClass( 'button-secondary' )
					.prop( 'disabled', false );
				self.dialog_insert_view.find( '.js-wpv-shortcode-gui-tabs' )
					.tabs({
						beforeActivate: function( event, ui ) {
							if (
								ui.oldPanel.attr( 'id' ) == 'js-wpv-insert-view-parametric-search-container'
								&& self.dialog_insert_view_locked
							) {
								event.preventDefault();
								ui.oldTab.focus().addClass( 'wpv-shortcode-gui-tabs-incomplete' );
								$( '.wpv-advanced-setting', ui.oldPanel ).addClass( 'wpv-advanced-setting-incomplete' );
								setTimeout( function() {
									ui.oldTab.removeClass( 'wpv-shortcode-gui-tabs-incomplete' );
									$( '.wpv-advanced-setting', ui.oldPanel ).removeClass( 'wpv-advanced-setting-incomplete' );
								}, 1000 );
							}
						}
					})
					.addClass('ui-tabs-vertical ui-helper-clearfix')
					.removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');
				$('#js-wpv-shortcode-gui-dialog-tabs ul, #js-wpv-shortcode-gui-dialog-tabs li').removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');

				Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-shortcode-dialog-opened', data_for_shortcode_wpv_view_dialog_requested_opened );
				Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-shortcode-wpv-view-dialog-opened', data_for_shortcode_wpv_view_dialog_requested_opened );
				// Legacy, leave for backwards compatibility
				$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_opened', [ data_for_shortcode_wpv_view_dialog_requested_opened ] );
			}
		});
		
	};
	
	$( document ).on( 'change input cut paste', '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-orderby', function() {
		var orderby_value = $( this ).val();
		
		if (
			self.orderby_postfield_pattern.test( orderby_value ) 
			|| self.orderby_termmeta_field_pattern.test( orderby_value )
			|| self.orderby_usermeta_field_pattern.test( orderby_value )
		) {
			$( '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-orderby_as-setting' ).fadeIn( 'fast' );
		} else {
			$( '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-orderby_as-setting' ).hide();
		}
	});
	
	$( document ).on( 'change input cut paste', '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-orderby_second', function() {
		var orderby_second_value = $( this ).val();
		
		if ( orderby_second_value == '' ) {
			$( '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-order_second' ).prop( 'disabled', true );
		} else {
			$( '#js-wpv-insert-view-override-container .js-wpv-insert-view-shortcode-order_second' ).prop( 'disabled', false );
		}
	});

	self.wpv_get_view_override_values = function() {
		var override_container = $( '#js-wpv-insert-view-override-container' ),
			override_values = {};
			
		if ( $( '.js-wpv-insert-view-shortcode-limit', override_container ).val() != '' ) {
			override_values['limit'] = $( '.js-wpv-insert-view-shortcode-limit', override_container ).val();
		}
		if ( $( '.js-wpv-insert-view-shortcode-offset', override_container ).val() != '' ) {
			override_values['offset'] = $( '.js-wpv-insert-view-shortcode-offset', override_container ).val();
		}
		if ( $( '.js-wpv-insert-view-shortcode-orderby', override_container ).val() != '' ) {
			override_values['orderby'] = $( '.js-wpv-insert-view-shortcode-orderby', override_container ).val();
			if ( 
				$( '.js-wpv-insert-view-shortcode-orderby_as', override_container ).length > 0
				&& $( '.js-wpv-insert-view-shortcode-orderby_as', override_container ).val() != '' 
			) {
				if (
					self.orderby_postfield_pattern.test( override_values['orderby'] ) 
					|| self.orderby_termmeta_field_pattern.test( override_values['orderby'] )
					|| self.orderby_usermeta_field_pattern.test( override_values['orderby'] )
				) {
					override_values['orderby_as'] = $( '.js-wpv-insert-view-shortcode-orderby_as', override_container ).val();
				}
			}
		}
		if ( $( '.js-wpv-insert-view-shortcode-order', override_container ).val() != '' ) {
			override_values['order'] = $( '.js-wpv-insert-view-shortcode-order', override_container ).val();
		}
		// Secondary sorting
		if ( 
			$( '.js-wpv-insert-view-shortcode-orderby_second', override_container ).length > 0 
			&& $( '.js-wpv-insert-view-shortcode-orderby_second', override_container ).val() != '' 
		) {
			override_values['orderby_second'] = $( '.js-wpv-insert-view-shortcode-orderby_second', override_container ).val();
		}
		if ( 
			$( '.js-wpv-insert-view-shortcode-order_second', override_container ).length > 0 
			&& $( '.js-wpv-insert-view-shortcode-order_second', override_container ).val() != '' 
		) {
			override_values['order_second'] = $( '.js-wpv-insert-view-shortcode-order_second', override_container ).val();
		}
		return override_values;
	};

	self.wpv_get_view_extra_values = function() {
		var extra_container = $( '#js-wpv-insert-view-extra-attributes-container' ),
			extra_values = {};
		if ( extra_container.length > 0 ) {
			$( '.js-wpv-insert-view-shortcode-extra-attribute', extra_container ).each( function() {
				var thiz = $( this );
				if ( thiz.val() != '' ) {
					extra_values[ thiz.data( 'attribute' ) ] = thiz.val();
				}
			});
		}
		return extra_values;
	};

	self.wpv_get_view_cache_values = function() {
		var cache_container = $( '#js-wpv-insert-view-cache-attributes-container' ),
			cache_values = {};
		if ( cache_container.length > 0 ) {
			var use_cache = $( '.js-wpv-insert-view-shortcode-cache:checked', cache_container ).val();
			if ( 'off' == use_cache ) {
				cache_values['cached'] = 'off';
			}
		}
		return cache_values;
	};

	self.dialog_insert_view_locked_check = function() {
		var container = $( '#js-wpv-insert-view-parametric-search-container' );
		if ( $( '.js-wpv-insert-view-form-display:checked', container ).val() == 'form' ) {
			var target = $( '.js-wpv-insert-view-form-target:checked', container ).val(),
				set_target = $( '.js-wpv-insert-view-form-target-set:checked', container ).val(),
				set_target_id = $( '.js-wpv-insert-view-form-target-set-existing-id', container ).val();
			if ( target == 'self' ) {
				$( '.js-wpv-insert-view-form-action' ).addClass( 'button-primary' ).removeClass( 'button-secondary' ).prop( 'disabled', false );
				self.dialog_insert_view_locked = false;
			} else {
				if ( set_target == 'existing' && set_target_id != '' ) {
					$( '.js-wpv-insert-view-form-target-set-actions' ).show();
				}
				$( '.js-wpv-insert-view-form-action' ).removeClass( 'button-primary' ).addClass( 'button-secondary' ).prop( 'disabled', true );
				self.dialog_insert_view_locked = true;
			}
		} else {
			self.dialog_insert_view_locked = false;
		}
	};

	self.wpv_insert_view_shortcode_to_editor = function() {
		var form_name = $( '#js-wpv-view-shortcode-gui-dialog-view-title' ).val(),
			override_values = self.wpv_get_view_override_values(),
			override_values_string = '',
			extra_values = self.wpv_get_view_extra_values(),
			extra_values_string = '',
			cache_values = self.wpv_get_view_cache_values(),
			cache_values_string = '',
			valid = self.validate_shortcode_attributes( $( '#js-wpv-view-shortcode-gui-dialog-container' ), $( '#js-wpv-view-shortcode-gui-dialog-container' ), $( '#js-wpv-view-shortcode-gui-dialog-container' ).find( '.js-wpv-filter-toolset-messages' ) ),
			shortcode_to_insert = '',
			shortcode_attribute_values = {};

		if ( ! valid ) {
			return;
		}

		shortcode_attribute_values['name'] = form_name;
		_.map( override_values, function( over_val, over_key ) {
			shortcode_attribute_values[ over_key ] = over_val;
			override_values_string += ' ' + over_key + '="' + over_val + '"';
		});
		_.map( extra_values, function( extra_val, extra_key ) {
			shortcode_attribute_values[ extra_key ] = extra_val;
			extra_values_string += ' ' + extra_key + '="' + extra_val + '"';
		});
		_.each( cache_values, function( cache_val, cache_key ) {
			shortcode_attribute_values[ cache_key ] = cache_val;
			cache_values_string += ' ' + cache_key + '="' + cache_val + '"';
		});

		if ( $( '#js-wpv-insert-view-parametric-search-container' ).length > 0 ) {

			var display = $( '.js-wpv-insert-view-form-display:checked' ).val(),
				target = $( '.js-wpv-insert-view-form-target:checked' ).val(),
				set_target = $( '.js-wpv-insert-view-form-target-set:checked' ).val(),
				set_target_id = $( '.js-wpv-insert-view-form-target-set-existing-id' ).val(),
				results_helper_container = $( '.js-wpv-insert-form-workflow-help-box' ),
				results_helper_container_after = $( '.js-wpv-insert-form-workflow-help-box-after' );

			if ( display == 'both' ) {
				shortcode_to_insert = '[wpv-view name="' + form_name + '"' + override_values_string + extra_values_string + cache_values_string + ']';
				self.wpv_insert_view_shortcode_to_editor_helper( 'wpv-view', shortcode_attribute_values, shortcode_to_insert );
				if (
					results_helper_container.length > 0
					&& results_helper_container.hasClass( 'js-wpv-insert-form-workflow-help-box-for-' + self.ps_view_id )
				) {
					results_helper_container.fadeOut( 'fast' );
				}
				if (
					results_helper_container_after.length > 0
					&& results_helper_container_after.hasClass( 'js-wpv-insert-form-workflow-help-box-for-after-' + self.ps_view_id )
				) {
					results_helper_container_after.show();
				}
			} else if ( display == 'results' ) {
				shortcode_to_insert = '[wpv-view name="' + form_name + '" view_display="layout"' + override_values_string + extra_values_string + cache_values_string + ']';
				self.wpv_insert_view_shortcode_to_editor_helper( 'wpv-view', shortcode_attribute_values, shortcode_to_insert );
				if (
					results_helper_container.length > 0
					&& results_helper_container.hasClass( 'js-wpv-insert-form-workflow-help-box-for-' + self.ps_view_id )
				) {
					results_helper_container.fadeOut( 'fast' );
				}
				if (
					results_helper_container_after.length > 0
					&& results_helper_container_after.hasClass( 'js-wpv-insert-form-workflow-help-box-for-after-' + self.ps_view_id )
				) {
					results_helper_container_after.show();
				}
			} else if ( display == 'form' ) {
				if ( target == 'self' ) {
					shortcode_to_insert = '[wpv-form-view name="' + form_name + '" target_id="self"' + override_values_string + extra_values_string + cache_values_string + ']';
					self.wpv_insert_view_shortcode_to_editor_helper( 'wpv-form-view', shortcode_attribute_values, shortcode_to_insert );
					if ( results_helper_container.length > 0 ) {
						var results_shortcode = '<code>[wpv-view name="' + form_name + '" view_display="layout"' + override_values_string + extra_values_string + cache_values_string + ']</code>';
						results_helper_container.find( '.js-wpv-insert-view-form-results-helper-name' ).html( form_name );
						results_helper_container.find( '.js-wpv-insert-view-form-results-helper-shortcode' ).html( results_shortcode );
						results_helper_container.addClass( 'js-wpv-insert-form-workflow-help-box-for-' + self.ps_view_id ).fadeIn( 'fast' );
					}
				} else {
					shortcode_to_insert = '[wpv-form-view name="' + form_name + '" target_id="' + set_target_id + '"' + override_values_string + extra_values_string + cache_values_string + ']';
					self.wpv_insert_view_shortcode_to_editor_helper( 'wpv-form-view', shortcode_attribute_values, shortcode_to_insert );
				}
			}

		} else {
			shortcode_to_insert = '[wpv-view name="' + form_name + '"' + override_values_string + extra_values_string + cache_values_string + ']';
			self.wpv_insert_view_shortcode_to_editor_helper( 'wpv-view', shortcode_attribute_values, shortcode_to_insert );

		}
	};

	/**
	 * Helpr method to act over a crafted a wpv-view or wpv-form-view shortcode.
	 *
	 * @since unknown
	 * @todo Turn arguments into a single object
	 */
	self.wpv_insert_view_shortcode_to_editor_helper = function( shortcode_name, shortcode_attribute_values, shortcode_to_insert ) {
		
		self.dialog_insert_view.dialog( 'close' );
		
		var shortcode_data = {
			shortcode:		shortcode_to_insert,
			name:			shortcode_name,
			attributes:		shortcode_attribute_values,
			raw_attributes:	shortcode_attribute_values,
			content:		''
		};
		
		self.do_shortcode_gui_action( shortcode_data );
		
	};


	/**
	 * Suggest for parametric search target
	 */

	$( document ).on( 'focus', '.js-wpv-insert-view-form-target-set-existing-title:not(.js-wpv-shortcode-gui-suggest-inited)', function() {
		var thiz = $( this );
		thiz
			.addClass( 'js-wpv-shortcode-gui-suggest-inited' )
			.suggest( wpv_shortcodes_gui_texts.ajaxurl + '&action=wpv_suggest_form_targets', {
				resultsClass: 'ac_results wpv-suggest-results',
				onSelect: function() {
					var t_value = this.value,
						t_split_point = t_value.lastIndexOf(' ['),
						t_title = t_value.substr( 0, t_split_point ),
						t_extra = t_value.substr( t_split_point ).split('#'),
						t_id = t_extra[1].replace(']', '');
					$( '.js-wpv-filter-form-help' ).hide();
					$('.js-wpv-insert-view-form-target-set-existing-title').val( t_title );
					t_edit_link = $('.js-wpv-insert-view-form-target-set-existing-link').data( 'editurl' );
					t_view_id = $('.js-wpv-insert-view-form-target-set-existing-link').data( 'viewid' );
					t_orig_id = $('.js-wpv-insert-view-form-target-set-existing-link').data('origid');
					$( '.js-wpv-insert-view-form-target-set-existing-link' ).attr( 'href', t_edit_link + t_id + '&action=edit&completeview=' + t_view_id + '&origid=' + t_orig_id );
					$( '.js-wpv-insert-view-form-target-set-existing-id' ).val( t_id ).trigger( 'change' );
					$( '.js-wpv-insert-view-form-target-set-actions' ).show();
				}
			});
	});

	/*
	 * Adjust the action button text copy based on the action to perform
	 */

	$( document ).on( 'change', '.js-wpv-insert-view-form-display', function() {
		var container = $( '#js-wpv-insert-view-parametric-search-container' ),
			display_container = $( '.js-wpv-insert-view-form-display-container', container ),
			display = $( '.js-wpv-insert-view-form-display:checked', container ).val(),
			target_container = $( '.js-wpv-insert-view-form-target-container', container ),
			target = $( '.js-wpv-insert-view-form-target:checked', container ).val(),
			set_target = $( '.js-wpv-insert-view-form-target-set:checked', container ).val(),
			set_target_id = $( '.js-wpv-insert-view-form-target-set-existing-id', container ).val(),
			results_helper_container = $( '.js-wpv-insert-form-workflow-help-box', container ),
			results_helper_container_after = $( '.js-wpv-insert-form-workflow-help-box-after', container );
		if ( display == 'form' ) {
			target_container.fadeIn();
		} else {
			target_container.fadeOut();
		}
		self.dialog_insert_view_locked_check();
	});

	/*
	 * Adjust the GUI when inserting just the form, based on the target options - target this or other page
	 */

	$( document ).on( 'change', '.js-wpv-insert-view-form-target', function() {
		var target = $( '.js-wpv-insert-view-form-target:checked' ).val(),
			set_target = $( '.js-wpv-insert-view-form-target-set:checked' ).val();
		if ( target == 'self' ) {
			$( '.js-wpv-insert-view-form-target-set-container' ).hide();
		} else if ( target == 'other' ) {
			$( '.js-wpv-insert-view-form-target-set-container' ).fadeIn( 'fast' );
		}
		self.dialog_insert_view_locked_check();
	});

	$( document ).on( 'click', '.js-wpv-insert-view-form-target-set-discard', function( e ) {
		e.preventDefault();
		self.dialog_insert_view_locked = false;
		$( '.js-wpv-insert-view-form-action' )
			.addClass( 'button-primary' )
			.removeClass( 'button-secondary' )
			.prop( 'disabled', false );
		$( '.js-wpv-insert-view-form-target-set-actions' ).hide();
	});

	$( document ).on( 'click', '.js-wpv-insert-view-form-target-set-existing-link', function() {
		self.dialog_insert_view_locked = false;
		$( '.js-wpv-insert-view-form-action' )
			.addClass( 'button-primary' )
			.removeClass( 'button-secondary' )
			.prop( 'disabled', false );
		$( '.js-wpv-insert-view-form-target-set-actions' ).hide();
	});

	/*
	 * Adjust the GUI when inserting just the form and targeting another page, based on the target options - target existing or new page
	 */

	$( document ).on( 'change', '.js-wpv-insert-view-form-target-set', function() {
		var set_target = $( '.js-wpv-insert-view-form-target-set:checked' ).val();
		if ( set_target == 'create' ) {
			$( '.js-wpv-insert-view-form-target-set-existing-extra' ).hide();
			$( '.js-wpv-insert-view-form-target-set-create-extra' ).fadeIn( 'fast' );
			$( '.js-wpv-insert-view-form-action' )
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary' )
				.prop( 'disabled', true );
		} else if ( set_target == 'existing' ) {
			$( '.js-wpv-insert-view-form-target-set-create-extra' ).hide();
			$( '.js-wpv-insert-view-form-target-set-existing-extra' ).fadeIn( 'fast' );
			$( '.js-wpv-insert-view-form-action' )
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary' )
				.prop( 'disabled', true );
			if ( $( '.js-wpv-insert-view-form-target-set-existing-id' ).val() != '' ) {
				$( '.js-wpv-insert-view-form-target-set-actions' ).show();
			}
		}
		self.dialog_insert_view_locked_check();
	});

	/*
	 * Adjust values when editing the target page title - clean data and mark this as unfinished
	 */

	$( document ).on('change input cut paste', '.js-wpv-insert-view-form-target-set-existing-title', function() {
		$( '.js-wpv-insert-view-form-target-set-actions' ).hide();
		$( '.js-wpv-insert-view-form-target-set-existing-link' ).attr( 'data-targetid', '' );
		$('.js-wpv-insert-view-form-target-set-existing-id')
			.val( '' )
			.trigger( 'manchange' );
	});

	/*
	 * Disable the insert button when doing any change in the existing title textfield
	 *
	 * We use a custom event 'manchange' as in "manual change"
	 */

	$( document ).on( 'manchange', '.js-wpv-insert-view-form-target-set-existing-id', function() {
		$( '.js-wpv-insert-view-form-action' )
			.removeClass( 'button-primary' )
			.addClass( 'button-secondary' )
			.prop( 'disabled', true );
		self.dialog_insert_view_locked_check();
	});

	/*
	 * Adjust GUI when creating a target page, based on the title value
	 */

	$( document ).on( 'change input cut paste', '.js-wpv-insert-view-form-target-set-create-title', function() {
		if ( $( '.js-wpv-insert-view-form-target-set-create-title' ).val() == '' ) {
			$( '.js-wpv-insert-view-form-target-set-create-action' )
				.prop( 'disabled', true )
				.addClass( 'button-secondary' )
				.removeClass( 'button-primary' );
		} else {
			$( '.js-wpv-insert-view-form-target-set-create-action' )
				.prop( 'disabled', false )
				.addClass( 'button-primary' )
				.removeClass( 'button-secondary' );
		}
	});

	/*
	 * AJAX action to create a new target page
	 */

	$( document ).on( 'click', '.js-wpv-insert-view-form-target-set-create-action', function() {
		var thiz = $( this ),
			thiz_existing_radio = $( '.js-wpv-insert-view-form-target-set[value="existing"]' ),
			spinnerContainer = $('<div class="wpv-spinner ajax-loader">').insertAfter( thiz ).show();
		data = {
			action: 'wpv_create_form_target_page',
			post_title: $( '.js-wpv-insert-view-form-target-set-create-title' ).val(),
			_wpnonce: thiz.data( 'nonce' )
		};
		$.ajax({
			url: wpv_shortcodes_gui_texts.ajaxurl,
			data: data,
			success: function( response ) {
				decoded_response = $.parseJSON( response );
				if ( decoded_response.result == 'error' ) {

				} else {
					$( '.js-wpv-insert-view-form-target-set-existing-title' ).val( decoded_response.page_title );
					$( '.js-wpv-insert-view-form-target-set-existing-id' ).val( decoded_response.page_id );
					t_edit_link = $('.js-wpv-insert-view-form-target-set-existing-link').data( 'editurl' );
					$('.js-wpv-insert-view-form-target-set-existing-link')
						.attr( 'href', t_edit_link + decoded_response.page_id + '&action=edit&completeview=' + self.ps_view_id + '&origid=' + self.ps_orig_id );
					thiz_existing_radio
						.prop( 'checked', true )
						.trigger( 'change' );
					$( '.js-wpv-insert-view-form-target-set-actions' ).show();
				}
			},
			error: function ( ajaxContext ) {

			},
			complete: function() {
				spinnerContainer.remove();
			}
		});
	});

	// Close the finished help boxes

	$( document ).on( 'click', '.js-wpv-insert-form-workflow-help-box-close', function( e ) {
		e.preventDefault();
		$( this ).closest( '.js-wpv-insert-form-workflow-help-box, .js-wpv-insert-form-workflow-help-box-after' ).hide();
	});
	
	// Toggle advanced settings on the dialog to insert a View
	
	$( document ).on( 'click', '.js-wpv-insert-views-shortcode-advanced-toggler', function( e ) {
		e.preventDefault();
		$( this )
			.find( 'i' )
				.toggleClass( 'fa-caret-down fa-caret-up' );
		$( '.js-wpv-insert-views-shortcode-advanced-wrapper' ).fadeToggle( 'fast' );
	});
	
	//-----------------------------------------
	// Views wpv-conditional shortcode dialog management
	//-----------------------------------------

	/**
	 * wpv_insert_popup_conditional
	 *
	 * @since 1.9.0
	 * @since 2.3.0 object.post_id is deprecated, we use wpv_shortcodes_gui_texts.post_id instead
	 * @since 2.3.0 Proper JSON rsponse management.
	 */

	self.wpv_insert_popup_conditional = function( shortcode, title, params, nonce, object ) {
		
		var data = {
			action:		'wpv_shortcode_gui_dialog_conditional_create',
			_wpnonce:	wpv_shortcodes_gui_texts.wpv_editor_callback_nonce,
			post_id:	parseInt( wpv_shortcodes_gui_texts.post_id )
		},
		data_for_shortcode_dialog_requested_opened = {
			shortcode:	shortcode,
			title:		title,
			params:		params,
			overrides:	{},
			nonce:		nonce,
			dialog:		self.dialog_insert_views_conditional
		};
		
		Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-shortcode-dialog-requested', data_for_shortcode_dialog_requested_opened );
		Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-shortcode-' + shortcode + '-dialog-requested', data_for_shortcode_dialog_requested_opened );
		// Legacy, leave for backwards compatibility
		$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_triggered', [ data_for_shortcode_dialog_requested_opened ] );

		self.dialog_insert_views_conditional.dialog( 'open' ).dialog({
			title: title
		});

		self.dialog_insert_views_conditional.html( self.shortcodeDialogSpinnerContent );
		
		self.wpv_conditional_editor = object.codemirror;
		self.wpv_conditional_object = object;
		
		$.ajax({
			type:		"GET",
			dataType: 	"json",
			url:		wpv_shortcodes_gui_texts.ajaxurl,
			data:		data,
			success:	function( response ) {
				if ( response.success ) {
					
					// Ensure the body has this modal-open class, 
					// as another prior dialog closing while opening this one might remove it
					$( 'body' ).addClass( 'modal-open' );
					
					self.dialog_insert_views_conditional.html( response.data.dialog ).dialog( 'open' );
					
					$( '.js-wpv-shortcode-gui-insert' )
						.addClass( 'button-primary' )
						.removeClass( 'button-secondary' )
						.prop( 'disabled', false );

					$('.js-wpv-conditional-shortcode-gui-tabs')
						.tabs()
						.addClass('ui-tabs-vertical ui-helper-clearfix')
						.removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');
					$('.js-wpv-conditional-shortcode-gui-tabs, .js-wpv-conditional-shortcode-gui-tabs li').removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');
					
					if ( object.codemirror == '' ) {
						if ( typeof object.ed.canvas !== 'undefined' ) {
							self.wpv_conditional_text = object.ed.canvas.value.substring(object.ed.canvas.selectionStart, object.ed.canvas.selectionEnd);
						} else {
							self.wpv_conditional_text = object.ed.selection.getContent();
						}
					} else {
						self.wpv_conditional_text = WPV_Toolset.CodeMirror_instance[object.codemirror].getSelection();
					}
					
					self.wpv_conditional_close = object.close_tag;
					
					self.wpv_conditional_add_row( $( '#js-wpv-conditionals' ) );
					
					Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-after-open-shortcode-dialog', data_for_shortcode_dialog_requested_opened );
					Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-after-open-' + shortcode + '-shortcode-dialog', data_for_shortcode_dialog_requested_opened );
					// Legacy, leave for backwards compatibility
					$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_opened', [ data_for_shortcode_dialog_requested_opened ] );
					
				} else {
					self.dialog_insert_views_conditional.html( self.shortcodeDialogNonceError );
					$( '.js-wpv-shortcode-gui-button-insert' ).hide();
				}
			},
			error:		function( ajaxContext ) {
				
			},
			complete:	function() {
				
			}
		});

	};
	
	/**
	 * Create the if attribut for a wpv-conditional shortcode.
	 *
	 * @since 1.9.0
	 */
	self.wpv_conditional_create_if_attribute = function( mode ) {
		var value = '';
		$('.js-wpv-views-condtional-item').each( function() {
			var tr = $(this),
                parent_selected_value = '';
			if ( $('.js-wpv-views-condtional-field :selected', tr).val() ) {
				if ( value ) {
					if ( 'multiline' == mode ) {
						value += "\n";
					}
					value += ' '+$('.js-wpv-views-condtional-connect :checked', tr).val()+' ';
					if ( 'multiline' == mode ) {
						value += "\n";
					}
				}
				value += '( ';
				value += $('.js-wpv-views-condtional-field :selected', tr ).val();
				parent_selected_value = $('.js-wpv-views-condtional-parents :selected', tr ).val();
				if ( typeof ( parent_selected_value ) !== 'undefined' && parent_selected_value !== '' ){
					value += '.id(' + parent_selected_value + ')';
				}
				value += ' ';
				value += $('.js-wpv-views-condtional-operator :selected', tr).val();
				value += ' \'';
				value += $('.js-wpv-views-condtional-value input', tr).val();
				value += '\' ';
				value += ')';
			}
		});
		return value;
	}

	$(document).on('click', '.js-wpv-views-conditional-add-term', function(e) {
		self.wpv_conditional_add_row($('#js-wpv-conditionals'));
	});

	/**
	 * bind type
	 */
	$( document ).on( 'click', '#js-wpv-views-conditional-shortcode-gui-dialog-container .js-wpv-shortcode-expression-switcher', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
			thiz_container = thiz.closest( '.js-wpv-shortcode-gui-attribute-wrapper-for-if' ),
			thiz_container_gui = $( '.js-wpv-conditionals-set-with-gui', thiz_container ),
			thiz_container_manual = $( '.js-wpv-conditionals-set-manual', thiz_container ),
			thiz_add_condition_button = $( '.js-wpv-views-conditional-add-term', thiz_container )
		if ( self.views_conditional_use_gui ) {
			thiz.fadeOut( 400 );
			thiz_add_condition_button.fadeOut( 400 );
			thiz_container_gui.fadeOut( 400, function() {
				self.views_conditional_use_gui = false;
				$('#wpv-conditional-custom-expressions')
					.val( self.wpv_conditional_create_if_attribute('multiline') )
					.data( 'edited', false );
				thiz.html( wpv_shortcodes_gui_texts.conditional_enter_conditions_gui ).fadeIn( 400 );
				thiz_container_manual.fadeIn( 400, function() {

				});
			});
		} else {
			/**
			 * check editor if was edited, ask user
			 */
			if ( $('#wpv-conditional-custom-expressions').data( 'edited' ) ) {
				if ( ! confirm( wpv_shortcodes_gui_texts.conditional_switch_alert ) ) {
					return;
				}
			}
			thiz.fadeOut( 400 );
			thiz_container_manual.fadeOut( 400, function() {
				self.views_conditional_use_gui = true;
				thiz.html( wpv_shortcodes_gui_texts.conditional_enter_conditions_manually ).fadeIn( 400 );
				thiz_add_condition_button.fadeIn( 400 );
				thiz_container_gui.fadeIn( 400, function() {

				});
			});
		}
	});

	/**
	 * add wpv-conditional-custom-expressions
	 */
	$(document).on('keyup', '#wpv-conditional-custom-expressions', function() {
		if ( !$(this).data('edited') ) {
			$(this).data('edited', true);
		}
	});


	self.wpv_conditional_add_row = function ( container ) {
		if ( 'unfinished' == typeof WPViews.wpv_conditional_data ) {
			return false;
		}
		html = '<tr class="js-wpv-views-condtional-item">';
		/**
		 * type
		 */
		html += '<td class="js-wpv-views-conditional-origin">';
		html += '<span class="js-wpv-views-condtional-type" style="display:inline-block;width:150px;"><select><option value="">' + WPViews.wpv_conditional_data.labels.select_choose + '</option>';

		$.each( WPViews.wpv_conditional_data.fields, function( key, field ) {
			if ( ! $.isEmptyObject( field.fields ) ) {
				html += '<option value="' + field.slug + '">' + field.label + '</option>';
			}
		});
		html += '</select></span>';
		/**
		 * field
		 */
		html += '<span class="js-wpv-views-condtional-field" style="display:inline-block; width:150px; padding-left:5px; "><select disabled="disabled">';
		html += '</select></span>';
		/**
		 * Parents dropdown, only for Types
		 */
		html += '<span class="js-wpv-views-condtional-parents" style="width:150px;display:none; padding-left:5px;"> <select>';
		if ( typeof WPViews.wpv_conditional_data.fields.types.parents !== 'undefined'){
			html += '<option value="">' + WPViews.wpv_conditional_data.labels.no_parent + '</option>';
			html += '<optgroup label="' + WPViews.wpv_conditional_data.labels.select_parent + '">';
			$.each( WPViews.wpv_conditional_data.fields.types.parents, function( key, field ) {
				html += '<option value="'+ key +'">' + field + '</option>';
			});
			html += '</optiongroup>';
		}
		html += '</select></span>';
		html += '</td>';

		/**
		 * operator
		 */
		html += '<td class="js-wpv-views-condtional-operator">';
		html += '<select>';
		html += '<option value="eq">=</option>';
		html += '<option value="ne">!=</option>';
		html += '<option value="gt">&gt;</option>';
		html += '<option value="lt">&lt;</option>';
		html += '<option value="gte">&gt;=</option>';
		html += '<option value="lte">&lt;=</option>';
		html += '</select>';
		html += '</td>';
		html += '</select></td>';
		/**
		 * value
		 */
		html += '<td class="js-wpv-views-condtional-value">';
		html += '<input type="text">';
		html += '</td>';
		html += '<td class="js-wpv-views-condtional-connect">';
		html += '<select>';
		html += '<option value="AND">AND</option>';
		html += '<option value="OR">OR</option>';
		html += '</select>';
		html += '</td>';
		html += '</select></td>';
		/**
		 * action
		 */
		html += '<td>';
		html += '<i class="icon-remove fa fa-times wpv-views-condtional-remove js-wpv-views-condtional-remove"></i>';
		html += '</td></tr>';
		$('.js-wpv-views-conditional-body').append(html);
		/**
		 * remove operator for first row
		 */
		self.wpv_conditional_row_remove_trash_from_first();


		return false;
	}

	/**
	 * bind remove
	 */
	$(document).on('click', '.js-wpv-views-condtional-remove', function() {
		var row = $(this).closest('tr');
		$( '.js-wpv-views-condtional-remove', '#js-wpv-conditionals' ).prop( 'disabled', true );
		row.addClass( 'wpv-condition-deleted' );
		row.fadeOut( 400, function() {
			row.remove();
			self.wpv_conditional_row_remove_trash_from_first();
			$( '.js-wpv-views-condtional-remove', '#js-wpv-conditionals' ).prop( 'disabled', false );
		});
	});

	/**
	 * bind type
	 */
	$( document ).on( 'change', '.js-wpv-views-condtional-type select', function() {
		$(this).closest('tr').find('.js-wpv-views-condtional-parents').css('display','none');
		var wpv_type = $( ':selected', $( this ) ).val();
		if ( wpv_type === '' ) {
			$( '.js-wpv-views-condtional-field select', $( this ).closest( 'tr' ) ).html( '' ).prop( 'disabled', true );
			return;
		}
		var html = '';
		html += '<option value="">'+ WPViews.wpv_conditional_data.labels.select_field +'</option>';
		$.each( WPViews.wpv_conditional_data.fields[wpv_type].fields, function( key, field ) {
			html += '<option value="' + field.slug + '" ';
			html += 'data-field-type="' + field.type + '" ';
			html += 'data-view-type="' + wpv_type + '" ';
			html += '>' + field.label + '</option>';
		});

		if ( wpv_type == 'types' || wpv_type == 'custom-fields' ){
			$(this).closest('tr').find('.js-wpv-views-condtional-parents').css('display','inline-block');
		} else {
            $(this).closest('tr').find('.js-wpv-views-condtional-parents select').val('');
		}
		
		$( '.js-wpv-views-condtional-field select', $( this ).closest('tr') ).html( html ).prop( 'disabled', false );
	});

	/**
	 * remove operator for first row
	 */
	self.wpv_conditional_row_remove_trash_from_first = function(container) {
		if ( $('.js-wpv-views-condtional-item').length == 1) {
			$('.js-wpv-views-condtional-remove').hide();
		} else {
			$('.js-wpv-views-condtional-remove').show();
		}
		$('.js-wpv-views-conditional-body .js-wpv-views-condtional-item:first-child .js-wpv-views-condtional-connect', container).html('&nbsp;');
	}

	$( document ).on( 'change input cut paste', '#wpv-conditional-extra-settings .js-wpv-add-item-settings-form-newname', function() {
		var thiz = $( this ),
			thiz_form = thiz.closest( 'form' ),
			thiz_button = thiz_form.find( '.js-wpv-add-item-settings-form-button' );
		$( '.js-wpv-cs-error, .js-wpv-cs-dup, .js-wpv-cs-ajaxfail', thiz_form ).hide();
		if ( thiz.val() == '' ) {
			thiz_button.prop( 'disabled', true );
		} else {
			thiz_button.prop( 'disabled', false );
		}
	});

	$( document ).on( 'click', '.js-wpv-add-item-settings-form-button', function( e ) {
		e.preventDefault();
		var thiz = $( this ),
			shortcode_pattern,
			thiz_append,
			thiz_kind,
			parent_form = thiz.closest( '.js-wpv-add-item-settings-form' ),
			parent_container = thiz.closest( '.wpv-shortcode-gui-attribute-wrapper' ),
			newitem = $( '.js-wpv-add-item-settings-form-newname', parent_form ),
			spinnerContainer = $('<div class="wpv-spinner ajax-loader">'),
			data = {
				csaction: 'add',
				cstarget: newitem.val(),
				wpnonce: $( '#wpv_custom_conditional_extra_settings' ).val()
			};
		if ( thiz.hasClass( 'js-wpv-custom-inner-shortcodes-add' ) ) {
			shortcode_pattern = /^[a-z0-9\-\_]+$/;
			data.action = 'wpv_update_custom_inner_shortcodes';
			thiz_append = '<li class="js-' + newitem.val() + '-item"><span class="">[' + newitem.val() + ']</span></li>';
			thiz_kind = 'custom-shortcodes';
		} else if ( thiz.hasClass( 'js-wpv-custom-conditional-functions-add' ) ) {
			shortcode_pattern = /^[a-zA-Z0-9\:\-\_]+$/;
			data.action = 'wpv_update_custom_conditional_functions';
			thiz_append = '<li class="js-' + newitem.val() + '-item"><span class="">' + newitem.val() + '</span></li>';
			thiz_kind = 'custom-functions';
		} else {
			return;
		}
		$( '.js-wpv-cs-error, .js-wpv-cs-dup, .js-wpv-cs-ajaxfail', parent_form ).hide();
		if ( shortcode_pattern.test( newitem.val() ) == false ) {
			$( '.js-wpv-cs-error', parent_form ).show();
		} else if ( $( '.js-' + newitem.val() + '-item', parent_container ).length > 0 ) {
			$( '.js-wpv-cs-dup', parent_form ).show();
		} else {
			spinnerContainer.insertAfter( thiz ).show();
			thiz
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary' )
				.prop( 'disabled', true );

			$.ajax({
				async: false,
				dataType: "json",
				type: "POST",
				url: wpv_shortcodes_gui_texts.ajaxurl,
				data: data,
				success: function( response ) {
					if ( response.success ) {
						$( '.js-wpv-add-item-settings-list', parent_container )
							.append( thiz_append );
						$( document ).trigger( 'js_event_wpv_extra_conditional_registered', [ { kind: thiz_kind, value: newitem.val() } ] );
						newitem.val('');
					} else {
						$( '.js-wpv-cs-ajaxfail', parent_form ).show();
						console.log( "Error: AJAX returned ", response );
					}
				},
				error: function (ajaxContext) {
					$( '.js-wpv-cs-ajaxfail', parent_form ).show();
					console.log( "Error: ", ajaxContext.responseText );
				},
				complete: function() {
					spinnerContainer.remove();
				}
			});
		}
		return false;
	});

	$( document ).on( 'submit', '.js-wpv-add-item-settings-form' , function( e ) {
		e.preventDefault();
		var thiz = $( this );
		$( '.js-wpv-add-item-settings-form-button', thiz ).click();
		return false;
	});

	$( document ).on( 'js_event_wpv_extra_conditional_registered', function( event, data ) {
		var html = '',
			type_select = $( '.js-wpv-views-condtional-type select' );
		switch ( data.kind ) {
			case 'custom-shortcodes':
				if ( $.isEmptyObject( WPViews.wpv_conditional_data.fields['custom-shortcodes'].fields ) ) {
					WPViews.wpv_conditional_data.fields['custom-shortcodes'].fields = {};
				}
				WPViews.wpv_conditional_data.fields['custom-shortcodes'].fields[data.value] = {
					label: data.value,
					slug: '\'[' + data.value + ']\'',
					type: 'text'
				};
				break;
			case 'custom-functions':
				if ( $.isEmptyObject( WPViews.wpv_conditional_data.fields['custom-functions'].fields ) ) {
					WPViews.wpv_conditional_data.fields['custom-functions'].fields = {};
				}
				WPViews.wpv_conditional_data.fields['custom-functions'].fields[data.value] = {
					label: data.value,
					slug: data.value + '()',
					type: 'text'
				};
				break;
		}
		html += '<option value="">' + WPViews.wpv_conditional_data.labels.select_choose + '</option>';
		$.each( WPViews.wpv_conditional_data.fields, function( key, field ) {
			if ( ! $.isEmptyObject( field.fields ) ) {
				html += '<option value="' + field.slug + '">' + field.label + '</option>';
			}
		});
		type_select.html( html );
		type_select.trigger( 'change' );
	});
	
	/**
	 * Crft a Views conditional shortcode and send it to self.do_shortcode_gui_action
	 *
	 * @since 1.10.0
	 */

	self.wpv_insert_view_conditional_shortcode = function() {
		var shortcode_name = $('.js-wpv-views-conditional-shortcode-gui-dialog-name').val(),
			shortcode_attribute_key,
			shortcode_attribute_value,
			shortcode_attribute_default_value,
			shortcode_attribute_string = '',
			shortcode_attribute_values = {},
			shortcode_raw_attribute_values = {},
			shortcode_content = '',
			shortcode_to_insert = '',
			shortcode_data = {},
			shortcode_valid = self.validate_shortcode_attributes( $( '#js-wpv-views-conditional-shortcode-gui-dialog-container' ), $( '#js-wpv-views-conditional-shortcode-gui-dialog-container' ), $( '#js-wpv-views-conditional-shortcode-gui-dialog-container' ).find( '.js-wpv-filter-toolset-messages' ) );
		if ( ! shortcode_valid ) {
			return;
		}
		$( '.js-wpv-shortcode-gui-attribute-wrapper', '#js-wpv-views-conditional-shortcode-gui-dialog-container' ).each( function() {
			var thiz_attribute_wrapper = $( this ),
				shortcode_attribute_key = thiz_attribute_wrapper.data('attribute');
			switch ( thiz_attribute_wrapper.data('type') ) {
				case 'radio':
					shortcode_attribute_value = $('input:checked', thiz_attribute_wrapper ).val();
					if ( 'custom-combo' == shortcode_attribute_value ) {
						shortcode_attribute_value = $('.js-wpv-shortcode-gui-attribute-custom-combo-target', $('input:checked', thiz_attribute_wrapper ).closest('.js-wpv-shortcode-gui-attribute-custom-combo')).val();
					}
					break;
				default:
					shortcode_attribute_value = $('input', thiz_attribute_wrapper ).val();
			}

			shortcode_attribute_default_value = thiz_attribute_wrapper.data('default');
			/**
			 * Fix true/false from data attribute for shortcode_attribute_default_value
			 */
			if ( 'boolean' == typeof shortcode_attribute_default_value ) {
				shortcode_attribute_default_value = shortcode_attribute_default_value ? 'true' :'false';
			}
			shortcode_raw_attribute_values[ shortcode_attribute_key ] = shortcode_attribute_value;
			/**
			 * Filter value
			 */
			shortcode_attribute_value = self.filter_computed_attribute_value( shortcode_name, shortcode_attribute_key, shortcode_attribute_value );
			/**
			 * Add to the shortcode_attribute_string string
			 */
			if (
				shortcode_attribute_value
				&& shortcode_attribute_value != shortcode_attribute_default_value
			) {
				shortcode_attribute_string += ' ' + shortcode_attribute_key + '="' + shortcode_attribute_value + '"';
				shortcode_attribute_values[shortcode_attribute_key] = shortcode_attribute_value;
			}
		});

		shortcode_to_insert = '[' + shortcode_name + shortcode_attribute_string + ']';
		/**
		 * Shortcodes with content
		 */
		if ( $( '.js-wpv-shortcode-conditional-gui-content' ).length > 0 ) {
			shortcode_content = $( '.js-wpv-shortcode-conditional-gui-content' ).val();
			/**
			 * Filter shortcode content
			 */
			shortcode_content = self.filter_computed_content( shortcode_name, shortcode_content, shortcode_attribute_values );
			var selected_text = self.wpv_conditional_text;
			if ( self.wpv_conditional_close ) {
				shortcode_to_insert += selected_text;
				shortcode_to_insert += '[/' + shortcode_name + ']';
				self.views_conditional_qtags_opened = false;
			} else {
				self.views_conditional_qtags_opened = true;
			}
		}
		/**
		 * Close, insert if needed and fire custom event
		 */
		self.dialog_insert_views_conditional.dialog( 'close' );
		
		shortcode_data = {
			shortcode:		shortcode_to_insert,
			name:			shortcode_name,
			attributes:		shortcode_attribute_values,
			raw_attributes: shortcode_raw_attribute_values,
			content:		shortcode_content
		};
		
		self.do_shortcode_gui_action( shortcode_data );
		
	};

	//-----------------------------------------
	// Generic shortcodes API GUI dialog management
	//-----------------------------------------

	/**
	 * Act over a shortcode when there are no attributes to set.
	 *
	 * Used in Basic taxonomy shortcodes, for example.
	 *
	 * @param shortcode_name		string	Shortcode tag.
	 * @param shortcode_to_insert	stringActual shortcode to insert.
	 *
	 * @since 1.12.0
	 * @todo Transform arguments into a single object.
	 * @todo rename for  more accurate method name.
	 */
	self.insert_shortcode_with_no_attributes = function( shortcode_name, shortcode_to_insert ) {
		
		self.maybe_close_fields_and_views_dialog();
		
		var shortcode_data = {
			shortcode:		shortcode_to_insert,
			name:			shortcode_name,
			attributes:		{},
			raw_attributes:	{},
			content:		''
		};
		
		self.do_shortcode_gui_action( shortcode_data );
		
	};

	/**
	 * Display a dialog for inserting a specific Views shortcode.
	 *
	 * This is the original method used to generate the shortcodes dialog, and uses an obsolete 'nonce' attribute.
	 * Since 2.3.0 we will use self.wpv_insert_shortcode_dialog_open instead, because it has some benefits:
	 * - Takes a single object as attribute, hence it extendable without refactoring.
	 * - Gets rid of the obsolete 'nonce' attribute as we use a single, unique one from localization.
	 * - Gets rid of the obxolete 'object' attribute as it was used only to get the current post data, 
	 *   and now we get it from localization.
	 *
	 * @param shortcode
	 * @param {string} title Dialog title.
	 * @param params
	 * @param nonce Deprecated, to remove
	 * @param object Deprecated, to remove
	 *
	 * @uses self.wpv_insert_shortcode_dialog_open
	 *
	 * @since 1.9.0
	 * @deprecated 2.3.0
	 * @until 2.5.0
	 */
	self.wpv_insert_popup = function( shortcode, title, params, nonce, object ) {
		var dialog_data = {
			shortcode:	shortcode,
			title:		title,
			params:		params,
			overrides:	{}
		};
		self.wpv_insert_shortcode_dialog_open( dialog_data );
		
	};
	
	/**
	 * Display a dialog for inserting a specific Views shortcode.
	 *
	 * @param object dialog_data 
	 *     shortcode	string	Shortcode name
	 *     title 		string	Dialog title.
	 *     params		object	Optional. Hidden parameters to enforce as attributes for the resulting shortcode.
	 *     overrides	object	Optional. Attribute values to override/enforce, mainly when editing a shortcode.
	 *
	 * @since 2.3.0
	 */
	self.wpv_insert_shortcode_dialog_open = function( dialog_data ) {
		
		_.defaults( dialog_data, { params: {}, overrides: {} } );
		
		var shortcode			= dialog_data.shortcode,
			title				= dialog_data.title,// This might not be neded at all :-)
			params				= dialog_data.params,
			overrides			= dialog_data.overrides,
			url					= wpv_shortcodes_gui_texts.ajaxurl,
			data_for_ajax_call	= {
				_wpnonce:	wpv_shortcodes_gui_texts.wpv_editor_callback_nonce,
				gui_action:	self.get_shortcode_gui_action(),
				action:		'wpv_shortcode_gui_dialog_create',
				shortcode:	shortcode,
				post_id:	parseInt( wpv_shortcodes_gui_texts.post_id ),
				get_page:   self.page,
				parameters:	params,
				overrides:	overrides
			},
			data_for_shortcode_dialog_requested_opened = {
				shortcode:	shortcode,
				title:		title,
				params:		params,
				overrides:	overrides,
				nonce:		wpv_shortcodes_gui_texts.wpv_editor_callback_nonce,
				dialog:		self.dialog_insert_shortcode
			};
		
		data_for_ajax_call = Toolset.hooks.applyFilters( 'wpv-filter-wpv-shortcodes-gui-extend-shortcode-dialog-data', data_for_ajax_call, data_for_shortcode_dialog_requested_opened );
		
		Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-shortcode-dialog-requested', data_for_shortcode_dialog_requested_opened );
		Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-shortcode-' + shortcode + '-dialog-requested', data_for_shortcode_dialog_requested_opened );
		// Legacy, leave for backwards compatibility
		$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_triggered', [ data_for_shortcode_dialog_requested_opened ] );
		
		// Show the "empty" dialog with a spinner while loading dialog content
		self.dialog_insert_shortcode.dialog( 'open' ).dialog({
			title: title
		});

		Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-shortcode-dialog-preloaded', data_for_shortcode_dialog_requested_opened );
		Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-shortcode-' + shortcode + '-dialog-preloaded', data_for_shortcode_dialog_requested_opened );
		
		self.dialog_insert_shortcode.html( self.shortcodeDialogSpinnerContent );
		
		$.ajax({
			type:		"GET",
			dataType: 	"json",
			url:		url,
			data:		data_for_ajax_call,
			success:	function( response ) {
				if ( response.success ) {
					
					// Ensure the body has this modal-open class, 
					// as another prior dialog closing while opening this one might remove it
					$( 'body' ).addClass( 'modal-open' );
					
					self.dialog_insert_shortcode
						.html( response.data.dialog )
						.dialog({
							title: response.data.title
						});
					$( '.js-wpv-shortcode-gui-insert' )
						.addClass( 'button-primary' )
						.removeClass( 'button-secondary' )
						.prop( 'disabled', false );
					if ( self.dialog_insert_shortcode.find( '.js-wpv-shortcode-gui-tabs-list > li' ).length > 1 ) {
						self.dialog_insert_shortcode.find( '.js-wpv-shortcode-gui-tabs' )
							.tabs({
								beforeActivate: function( event, ui ) {
									var valid = self.validate_shortcode_attributes( $( '#js-wpv-shortcode-gui-dialog-container' ), ui.oldPanel, $( '#js-wpv-shortcode-gui-dialog-container' ).find( '.js-wpv-filter-toolset-messages' ) );
									if ( ! valid ) {
										event.preventDefault();
										ui.oldTab.focus().addClass( 'wpv-shortcode-gui-tabs-incomplete' );
										setTimeout( function() {
											ui.oldTab.removeClass( 'wpv-shortcode-gui-tabs-incomplete' );
										}, 1000 );
									}
								}
							})
							.addClass('ui-tabs-vertical ui-helper-clearfix')
							.removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');
						$('#js-wpv-shortcode-gui-dialog-tabs ul, #js-wpv-shortcode-gui-dialog-tabs li').removeClass('ui-corner-top ui-corner-right ui-corner-bottom ui-corner-left ui-corner-all');
					} else {
						self.dialog_insert_shortcode.find( '.js-wpv-shortcode-gui-tabs-list' ).remove();
					}
					
					Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-after-open-shortcode-dialog', data_for_shortcode_dialog_requested_opened );
					Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-after-open-' + shortcode + '-shortcode-dialog', data_for_shortcode_dialog_requested_opened );
					// Legacy, leave for backwards compatibility
					$( document ).trigger( 'js_event_wpv_shortcode_gui_dialog_opened', [ data_for_shortcode_dialog_requested_opened ] );
				
				} else {
					self.dialog_insert_shortcode.html( self.shortcodeDialogNonceError );
					$( '.js-wpv-shortcode-gui-button-insert' ).hide();
				}
			}
		});
	};
	
	self.after_preload_shortcode_dialog = function( data ) {
		self.manage_shortcodes_dialog_buttonpane( data );
	};
	
	/**
	 * after_open_dialog
	 *
	 * @since 1.9.0
	 * @deprecated 2.3.0 See wpv-action-wpv-shortcodes-gui-after-open-shortcode-dialog. 
	 *     Use self.after_open_shortcode_dialog instead.
	 */
	self.after_open_dialog = function( shortcode, title, params, nonce, object ) {
		
	};
	
	/**
	 * after_open_shortcode_dialog
	 *
	 * Executes the following actions:
	 *     manage_fixed_initial_params	Manages the fixed initial parameters passed to the dialog, by creating hidden inputs for them.
	 *     manage_editing_overrides		Manages the parameters to override, by setting the attribute values and maybe shortcode content passed to the dialog.
	 * 	   manage_special_cases			Manages the special cases we have for some selections that show or hide other options.
	 * 	   manage_suggest_cache			Manages the suggest caching on suggest fields.
	 *     custom_combo_management		Manages custom combos, meaning dummy options that enable other options.
	 *
	 * @param data object The data we have about this scenario.
	 *     shortcode	string	The shortcode tag.
	 *     title		string	The dialog title.
	 *     params		object	The initial fixed attributes for this shortcode.
	 *     overrides	object	The attributes key->value pairs to enforce when editing a shortcode.
	 *     nonce		string	The nonce used to populate the dialog.
	 *     dialog		object	The jQuery UI dialog that was just opened.
	 *
	 * @see wpv-action-wpv-shortcodes-gui-after-open-shortcode-dialog.
	 *
	 * @since 2.3.0
	 */
	
	self.after_open_shortcode_dialog = function( data ) {
		self.manage_fixed_initial_params( data );
		self.manage_editing_overrides( data );
		self.manage_special_cases( data );
		self.manage_suggest_cache();
		self.custom_combo_management( data );
		self.initPostSelector();
	};
	
	/**
	 * Manage initial parameters that are to be forced when creating or editing a shortcode.
	 *
	 * @param data object The data we have about this scenario.
	 *     shortcode	string	The shortcode tag.
	 *     title		string	The dialog title.
	 *     params		object	The initial fixed attributes for this shortcode.
	 *     overrides	object	The attributes key->value pairs to enforce when editing a shortcode.
	 *     nonce		string	The nonce used to populate the dialog.
	 *     dialog		object	The jQuery UI dialog that was just opened.
	 *
	 * @since 1.9.0
	 * @since 2.3.0 When editing a shortcode, initial params are in data.override.attributes and need to be 
	 *     pushed to data.params.attributes to be enforced. That means we need to identify them by hand.
	 *
	 * @todo Try to set an automatic method for knowing which shortcode has which fixed initial parameters, 
	 *     maybe as a wpv_shortcodes_gui_texts entry.
	 */

	self.manage_fixed_initial_params = function( data ) {
		if ( ! _.has( data, 'params' ) ) {
			data.params = {};
		}
		if ( ! _.has( data.params, 'attributes' ) ) {
			data.params.attributes = {};
		}
		switch ( data.shortcode ) {
			case 'wpv-post-taxonomy':
				if ( 
					_.has( data.overrides, 'attributes' ) 
					&& _.has( data.overrides.attributes, 'type' ) 
				) {
					data.params.attributes.type = data.overrides.attributes.type;
				}
				break;
			case 'wpv-user':
				if ( 
					_.has( data.overrides, 'attributes' ) 
					&& _.has( data.overrides.attributes, 'field' ) 
				) {
					data.params.attributes.field = data.overrides.attributes.field;
				}
				break;
			case 'wpv-control-post-taxonomy':
				if ( 
					_.has( data.overrides, 'attributes' ) 
					&& _.has( data.overrides.attributes, 'taxonomy' ) 
				) {
					data.params.attributes.taxonomy = data.overrides.attributes.taxonomy;
				}
				break;
			case 'wpv-control-postmeta':
				if ( 
					_.has( data.overrides, 'attributes' ) 
					&& _.has( data.overrides.attributes, 'field' ) 
				) {
					data.params.attributes.field = data.overrides.attributes.field;
				}
				break;
		}
		_.each( data.params.attributes, function( value, key, list ) {
			data.dialog.find( '.wpv-dialog' ).prepend( '<span class="wpv-shortcode-gui-attribute-wrapper js-wpv-shortcode-gui-attribute-wrapper js-wpv-shortcode-gui-attribute-wrapper-for-' + key + '" data-attribute="' + key + '" data-type="param"><input type="hidden" name="' + key + '" value="' + value + '" disabled="disabled" /></span>' );
		});
		if ( 
			_.has( data.params, 'content' )
			&& data.params.content !== undefined
		) {
			data.dialog.find( '.wpv-dialog .js-wpv-shortcode-gui-content' ).val( data.params.content );
		}
	};
	
	/**
	 * Force attribut values when editing a shortcode.
	 *
	 * @param data object The data we have about this scenario.
	 *     shortcode	string	The shortcode tag.
	 *     title		string	The dialog title.
	 *     params		object	The initial fixed attributes for this shortcode.
	 *     overrides	object	The attributes key->value pairs to enforce when editing a shortcode.
	 *     nonce		string	The nonce used to populate the dialog.
	 *     dialog		object	The jQuery UI dialog that was just opened.
	 *
	 * @since 2.3.0
	 */
	
	self.manage_editing_overrides = function( data ) {
		if ( _.has( data.overrides, 'attributes' ) ) {
			_.each( data.overrides.attributes, function( value, key, list ) {
				if ( data.dialog.find( '.wpv-dialog .js-wpv-shortcode-gui-attribute-wrapper-for-' + key ).length > 0 ) {
					var attribute_wrapper = data.dialog.find( '.wpv-dialog .js-wpv-shortcode-gui-attribute-wrapper-for-' + key ),
					attribute_type = attribute_wrapper.data( 'type' );
					switch ( attribute_type ) {
						case 'select':
							if ( attribute_wrapper.find( '.js-shortcode-gui-field option[value="' + value + '"]' ).length != 0 ) {
								attribute_wrapper.find( '.js-shortcode-gui-field' ).val( value );
							}
							break;
						case 'radio':
						case 'radiohtml':
							if ( attribute_wrapper.find( '.js-shortcode-gui-field[value="' + value + '"]' ).length != 0 ) {
								attribute_wrapper.find( '.js-shortcode-gui-field[value="' +  value + '"]' ).prop( 'checked', true );
							}
							break;
						case 'number':
						case 'text':
						case 'url':
						case 'fixed':
							attribute_wrapper.find( '.js-shortcode-gui-field' ).val( value );
							break;
						case 'textarea':
							// @todo check this
							attribute_wrapper.find( '.js-shortcode-gui-field' ).val( value );
							break;
						case 'suggest':
							if ( attribute_wrapper.find( 'select.js-shortcode-gui-field option[value="' + value + '"]' ).length != 0 ) {
								attribute_wrapper.find( 'select.js-shortcode-gui-field[value="' +  value + '"]' ).prop( 'checked', true );
							} else if ( attribute_wrapper.find( 'input.js-shortcode-gui-field' ).length != 0 ) {
								attribute_wrapper.find( 'input.js-shortcode-gui-field' ).val( value );
							}
							break;
						case 'post':
							if ( '$parent' == value ) {
								$( '#wpv-shortcode-gui-item-selector-post-id-parent' )
									.prop( 'checked', true )
									.trigger( 'change' );
							} else if ( '$' == value.substring( 0, 1 ) ) {
								var parent_post_type = value.substring( 1 );
								if ( $( '#wpv-shortcode-gui-item-selector-post-relationship-id-' + parent_post_type ).length > 0 ) {
									$( '#wpv-shortcode-gui-item-selector-post-id-related' )
										.prop( 'checked', true )
										.trigger( 'change' );
									$( '#wpv-shortcode-gui-item-selector-post-relationship-id-' + parent_post_type )
										.prop( 'checked', true )
										.trigger( 'change' );
								}
							} else if ( self.numeric_natural_pattern.test( value ) ) {
								$( '#wpv-shortcode-gui-item-selector-post-id' )
									.prop( 'checked', true )
									.trigger( 'change' );
								$( '[name="specific_object_id"]', attribute_wrapper ).val( value );
							}

							break;
						case 'user':
							if ( self.numeric_natural_pattern.test( value ) ) {
								$( '#wpv-shortcode-gui-item-selector-user-id' )
									.prop( 'checked', true )
									.trigger( 'change' );
								$( '[name="specific_object_id"]', attribute_wrapper ).val( value );
							}
							break;
						case 'callback':
							// @todo
							break;
					}
				} else {
					data.dialog.find( '.wpv-dialog' ).prepend( '<span class="wpv-shortcode-gui-attribute-wrapper js-wpv-shortcode-gui-attribute-wrapper js-wpv-shortcode-gui-attribute-wrapper-for-' + key + '" data-attribute="' + key + '" data-type="param"><input type="hidden" name="' + key + '" value="' + value + '" disabled="disabled" /></span>' );
				}				
			});
		}
		if ( 
			_.has( data.overrides, 'content' )
			&& data.overrides.content !== undefined
		) {
			data.dialog.find( '.wpv-dialog .js-wpv-shortcode-gui-content' ).val( data.overrides.content );
		}
	};
	
	/**
	 * Manage special interactions on the dialog to craft a shortcode, for specific shortcodes.
	 *
	 * @param data object The data we have about this scenario.
	 *     shortcode	string	The shortcode tag.
	 *     title		string	The dialog title.
	 *     params		object	The initial fixed attributes for this shortcode.
	 *     overrides	object	The attributes key->value pairs to enforce when editing a shortcode.
	 *     nonce		string	The nonce used to populate the dialog.
	 *     dialog		object	The jQuery UI dialog that was just opened.
	 *
	 * @since 1.9.0
	 * @since 2.3.2 Added support for the [wpv-current-user] shortcode that took profile picture functionality.
	 * @since 2.3.2 Added support for the [wpv-login-form] shortcode.
	 */

	self.manage_special_cases = function( data ) {
		switch ( data.shortcode ) {
			case 'wpv-post-author':
				self.manage_wpv_post_author_format_show_relation();
				break;
			case 'wpv-post-excerpt':
				self.manage_wpv_post_excerpt_output_show_relation();
				break;
			case 'wpv-post-taxonomy':
				self.manage_wpv_post_taxonomy_format_show_relation();
				break;
			case 'wpv-post-featured-image':
				self.manage_wpv_post_featured_image_output_show_class();
				self.manage_wpv_post_featured_image_resize_show_relation();
				self.manage_wpv_post_featured_image_crop_show_relation();
				break;
            case 'wpv-current-user':
                self.manage_wpv_current_user_info_show_relation();
                break;
			case 'wpv-login-form':
				self.manage_wpv_login_form_show_remember_me_state();
				break;
		}
	};
	
	/**
	 * Preload a cached value on a shortcode attribute of type 'suggest', if available.
	 *
	 *
	 * @since 1.9.0
	 */

	self.manage_suggest_cache = function() {
		$( '.js-wpv-shortcode-gui-suggest' ).each( function() {
			var thiz_inner = $( this ),
				action_inner = '';
			if ( thiz_inner.data('action') != '' ) {
				action_inner = thiz_inner.data('action');
				if ( self.suggest_cache.hasOwnProperty( action_inner ) ) {
					thiz_inner
						.val( self.suggest_cache[action_inner] )
						.trigger( 'change' );
				}
			}
		});
	};

	/**
	 * Bind interaction of custom combo value, meaning dummy values that enable hidden attributes.
	 *
	 * @param data object The data we have about this scenario.
	 *     shortcode	string	The shortcode tag.
	 *     title		string	The dialog title.
	 *     params		object	The initial fixed attributes for this shortcode.
	 *     overrides	object	The attributes key->value pairs to enforce when editing a shortcode.
	 *     nonce		string	The nonce used to populate the dialog.
	 *     dialog		object	The jQuery UI dialog that was just opened.
	 *
	 * @since 2.9.0
	 */
	self.custom_combo_management = function ( data ) {
		switch ( data.shortcode ) {
			case 'wpv-post-body':
				if ( 
					_.has( data.overrides, 'attributes' ) 
					&& _.has( data.overrides.attributes, 'view_template' ) 
					&& data.overrides.attributes.view_template != 'None'
				) {
					$( '#wpv-post-body-view_template-value' ).val( data.overrides.attributes.view_template );
					$( '#wpv-post-body-view_template .js-wpv-shortcode-gui-attribute-custom-combo-pointer' ).prop( 'checked', true );
				}
				break;
			case 'wpv-post-date':
				if ( 
					_.has( data.overrides, 'attributes' ) 
					&& _.has( data.overrides.attributes, 'format' ) 
					&& ! _.contains( [ 'F j, Y', 'F j, Y g:i a', 'd/m/y' ], data.overrides.attributes.format )
				) {
					$( '#wpv-post-date-format-value' ).val( data.overrides.attributes.format );
					$( '#wpv-post-date-format .js-wpv-shortcode-gui-attribute-custom-combo-pointer' ).prop( 'checked', true );
				}
				break;
		}
		$( '.js-wpv-shortcode-gui-attribute-custom-combo').each( function() {
			var combo_parent = $( this ).closest( '.js-wpv-shortcode-gui-attribute-wrapper' ),
				combo_target = $( '.js-wpv-shortcode-gui-attribute-custom-combo-target', combo_parent );
			if ( $( '[value=custom-combo]:checked', combo_parent ).length ) {
				combo_target.show();
			}
			$( '[type=radio]', combo_parent ).on( 'change', function() {
				var thiz_radio = $( this );
				if (
					thiz_radio.is( ':checked' )
					&& 'custom-combo' == thiz_radio.val()
				) {
					combo_target.slideDown( 'fast' );
				} else {
					combo_target.slideUp( 'fast' );
				}
			});
		});
	};
	
	
	/**
	 * Set the first post selector and post reference selector as checked, if any.
	 *
	 * @since m2m
	 */
	self.initPostSelector = function() {
		$( 'input[name="related_object"]:not(:disabled)', '#js-wpv-shortcode-gui-dialog-container' )
			.first()
				.prop( 'checked', true );
		
		$( 'input[name="referenced_object"]:not(:disabled)', '#js-wpv-shortcode-gui-dialog-container' )
			.first()
				.prop( 'checked', true );
	};

	/**
	 * filter_dialog_ajax_data
	 *
	 * Filter the empty extra string added to the request to create the dialog GUI, so we can pass additional parameters for some shortcodes.
	 *
	 * @param shortcode The shortcode to which the dialog is being created.
	 *
	 * @return ajax_extra_data
	 *
	 * @since 1.9
	 * @deprecated 2.3.0 See wpv-filter-wpv-shortcodes-gui-extend-shortcode-dialog-data.
	 */

	self.filter_dialog_ajax_data = function( shortcode ) {
		return;
	};
	
	/**
	 * Filter the data passed to the request to create the dialog GUI, so we can pass additional parameters for some shortcodes.
	 *
	 * @param data_to_send	The data to be sent.
	 * @param data			The data we have about this scenario.
	 *
	 * @return data_to_send
	 *
	 * @see wpv-filter-wpv-shortcodes-gui-extend-shortcode-dialog-data
	 *
	 * @since 2.3.0
	 */
	
	self.extend_shortcode_dialog_data = function( data_to_send, data ) {
		switch( data.shortcode ) {
			case 'wpv-post-body':
				// Check for excluded content templates list via the filter.
				var excluded_cts = [];
				excluded_cts = Toolset.hooks.applyFilters( 'wpv-filter-wpv-shortcodes-gui-wpv_post_body-exclude-content-template', excluded_cts );
				if ( 
					$.isArray( excluded_cts ) 
					&& excluded_cts.length > 0 
				) {
					data_to_send['wpv_suggest_wpv_post_body_view_template_exclude'] = excluded_cts;
				}

				break;
		}
		return data_to_send;
	};

	/**
	 * Adjust the dialog buttons depending on the current GUI action.
	 *
	 * @since 1.9.0
	 * @since 2.3.0 Hide the "Back" button when the current GUI action is 'save' or 'edit'.
	 * @since 2.3.1 Transform into a WP Hooks action callback which gets passed the shortcode data,
	 *     because other shortcodes should not provide this "Back" button even when 'insert'-ing.
	 */

	self.manage_shortcodes_dialog_buttonpane = function( data ) {
		var shortcodes_gui_action = self.get_shortcode_gui_action();
		$( '.js-wpv-shortcode-gui-button-insert' ).show();
		switch ( shortcodes_gui_action ) {
			case 'save':
				$( '.js-wpv-shortcode-gui-button-back' ).hide();
				$( '.js-wpv-shortcode-gui-button-close .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_cancel );
				$( '.js-wpv-shortcode-gui-button-insert .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_save_settings );
				break;
			case 'create':
			case 'append':
				$( '.js-wpv-shortcode-gui-button-back' ).show();
				$( '.js-wpv-shortcode-gui-button-close .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_cancel );
				$( '.js-wpv-shortcode-gui-button-insert .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_create_shortcode );
				break;
			case 'edit':
				$( '.js-wpv-shortcode-gui-button-back' ).hide();
				$( '.js-wpv-shortcode-gui-button-close .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_cancel );
				$( '.js-wpv-shortcode-gui-button-insert .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_update_shortcode );
				break;
			case 'insert':
			default:
				if ( 
					data.shortcode.substr( 0, 11 ) == 'wpv-control' 
					|| data.shortcode.substr( 0, 10 ) == 'wpv-filter'
				) {
					$( '.js-wpv-shortcode-gui-button-back' ).hide();
				} else {
					$( '.js-wpv-shortcode-gui-button-back' ).show();
				}
				$( '.js-wpv-shortcode-gui-button-close .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_cancel );
				$( '.js-wpv-shortcode-gui-button-insert .ui-button-text' ).html( wpv_shortcodes_gui_texts.wpv_insert_shortcode );
				break;
		}
	};

	/**
	 * Init suggest on suggest attributes
	 *
	 * @since 1.9.0
	 */

	$( document ).on( 'focus', '.js-wpv-shortcode-gui-suggest:not(.js-wpv-shortcode-gui-suggest-inited)', function() {
		var thiz = $( this ),
			action = '';
		if ( thiz.data('action') != '' ) {
			action = thiz.data('action');
			ajax_extra_data = self.filter_suggest_ajax_data( action );
			thiz
				.addClass( 'js-wpv-shortcode-gui-suggest-inited' )
				.suggest( wpv_shortcodes_gui_texts.ajaxurl + '&action=' + action + ajax_extra_data, {
					resultsClass: 'ac_results wpv-suggest-results',
					onSelect: function() {
						self.suggest_cache[action] = this.value;
					}
				});
		}
	});

	/**
	 * Filter the empty extra string added to the suggest request, so we can pass additional parameters for some shortcodes.
	 *
	 * @param action The suggest action to perform.
	 *
	 * @return ajax_extra_data
	 *
	 * @since 1.9.0
	 * @todo This should use a filter instead of this check against undefined.
	 */

	self.filter_suggest_ajax_data = function( action ) {
		var ajax_extra_data = '';
		switch( action ) {
			case 'wpv_suggest_wpv_post_body_view_template':
				if (
					typeof WPViews.ct_edit_screen != 'undefined'
					&& typeof WPViews.ct_edit_screen.ct_data != 'undefined'
					&& typeof WPViews.ct_edit_screen.ct_data.id != 'undefined'
				) {
					ajax_extra_data = '&wpv_suggest_wpv_post_body_view_template_exclude=' + WPViews.ct_edit_screen.ct_data.id;
				}
				break;
			case 'wpv_suggest_postmeta_default_label':
				ajax_extra_data = '&field=' + $( '.js-wpv-shortcode-gui-attribute-wrapper-for-field > input' ).val();
				ajax_extra_data += '&type=' + $( '#wpv-control-postmeta-type' ).val();
				break;
		}
		return ajax_extra_data;
	};

	/**
	 * Manage the item selector GUI that lets you craft shortcodes for related or specific posts and users.
	 *
	 * This behaves like a customized version of a custom combo.
	 *
	 * @since 1.9.0
	 */

	$( document ).on( 'change', 'input.js-wpv-shortcode-gui-item-selector', function() {
		var thiz = $( this ),
			checked = thiz.val();
		$('.js-wpv-shortcode-gui-item-selector-has-related').each( function() {
			var thiz_inner = $( this );
			if ( $( 'input.js-wpv-shortcode-gui-item-selector:checked', thiz_inner ).val() == checked ) {
				$( '.js-wpv-shortcode-gui-item-selector-is-related', thiz_inner ).slideDown( 'fast' );
			} else {
				$( '.js-wpv-shortcode-gui-item-selector-is-related', thiz_inner ).slideUp( 'fast' );
			}
		});
	});
	
	/**
	 * Init select2 attributes controls.
	 *
	 * @since 2.6.0
	 */
	self.initSelect2Attributes = function() {
		$( '.js-wpv-shortcode-gui-dialog-container .js-wpv-shortcode-gui-field-select2:not(.js-wpv-shortcode-gui-field-select2-inited)' ).each( function() {
			var selector = $( this ),
				selectorParent = selector.closest( '.js-wpv-shortcode-gui-dialog-container' );
			
			selector
				.addClass( 'js-wpv-shortcode-gui-field-select2-inited' )
				.css( { width: '100%' } )
				.toolset_select2(
					{ 
						width:				'resolve',
						dropdownAutoWidth:	true, 
						dropdownParent:		selectorParent,
						placeholder:		selector.data( 'placeholder' )
					}
				)
				.data( 'toolset_select2' )
					.$dropdown
						.addClass( 'toolset_select2-dropdown-in-dialog' );
		});
	};
	
	/**
	 * Init the ajaxSelect2 attributes action.
	 *
	 * @since 2.6.0
	 */
	self.initSelect2AjaxAction = function( selector ) {
		var selectorParent = selector.closest( '.js-wpv-shortcode-gui-dialog-container' );
		selector
				.addClass( 'js-wpv-shortcode-gui-field-select2-inited' )
				.css( { width: '100%' } )
				.toolset_select2(
					{ 
						width:				'resolve',
						dropdownAutoWidth:	true, 
						dropdownParent:		selectorParent,
						placeholder:		selector.data( 'placeholder' ),
						minimumInputLength:	2,
						ajax: {
							url: toolset_shortcode_i18n.ajaxurl,
							dataType: 'json',
							delay: 250,
							type: 'post',
							data: function( params ) {
								return {
									action:  selector.data( 'action' ),
									s:       params.term,
									page:    params.page,
									wpnonce: selector.data( 'nonce' )
								};
							},
							processResults: function( originalResponse, params ) {
								var response = WPV_Toolset.Utils.Ajax.parseResponse( originalResponse );
								params.page = params.page || 1;
								if ( response.success ) {
									return {
										results: response.data,
									};
								}
								return {
									results: [],
								};
							},
							cache: false
						}
					}
				)
				.data( 'toolset_select2' )
					.$dropdown
						.addClass( 'toolset_select2-dropdown-in-dialog' );
	};
	
	/**
	 * Init ajaxSelect2 attributes controls.
	 * Get the prefill label for any existing value.
	 *
	 * @since 2.6.0
	 */
	self.initSelect2AjaxAttributes = function() {
		$( '.js-wpv-shortcode-gui-dialog-container .js-wpv-shortcode-gui-field-ajax-select2:not(.js-wpv-shortcode-gui-field-select2-inited)' ).each( function() {
			var selector = $( this );
			
			if ( 
				selector.val() 
				&& selector.data( 'prefill' )
			) {
				var prefillData = {
					action:  selector.data( 'prefill' ),
					wpnonce: selector.data( 'prefill-nonce' ),
					s:       selector.val()
				};
				$.ajax({
					url:     toolset_shortcode_i18n.ajaxurl,
					data:    prefillData,
					type:    "post",
					success: function( originalResponse ) {
						var response = WPV_Toolset.Utils.Ajax.parseResponse( originalResponse );
						if ( response.success ) {
							selector
								.find( 'option:selected' )
									.html( response.data.label );
						} else {
							selector
								.find( 'option:selected' )
									.remove();
						}
						self.initSelect2AjaxAction( selector );
					},
					error: function ( ajaxContext ) {
						selector
							.find( 'option:selected' )
								.remove();
						self.initSelect2AjaxAction( selector );
					}
				});
			} else {
				self.initSelect2AjaxAction( selector );
			}
			
		});
	};
	
	/**
	 * Init select2 and ajaxSelect2 attributes controls.
	 *
	 * @since 2.6.0
	 */
	self.initSelect2 = function() {
		self.initSelect2Attributes();
		self.initSelect2AjaxAttributes();
	};

	/**
	 * Helper callbacks to manage placeholders: should be removed when focusing on a textfield, added back on blur.
	 *
	 * @since 1.9.0
	 */

	$( document )
		.on( 'focus', '.js-wpv-shortcode-gui-attribute-has-placeholder, .js-wpv-has-placeholder', function() {
			var thiz = $( this );
			thiz.attr( 'placeholder', '' );
		})
		.on( 'blur', '.js-wpv-shortcode-gui-attribute-has-placeholder, .js-wpv-has-placeholder', function() {
			var thiz = $( this );
			if ( thiz.data( 'placeholder' ) ) {
				thiz.attr( 'placeholder', thiz.data( 'placeholder' ) );
			}
		});

	/**
	 * Validate shortcode attributes depending on their type.
	 *
	 * @since 1.9.0
	 */

	self.validate_shortcode_attributes = function( container, evaluate_container, error_container ) {
		self.clear_validate_messages( container );
		var valid = true;
		valid = self.manage_required_attributes( evaluate_container, error_container );
		evaluate_container.find( 'input:text' ).each( function() {
			var thiz = $( this ),
				thiz_val = thiz.val(),
				thiz_type = thiz.data( 'type' ),
				thiz_message = '',
				thiz_valid = true;
			if ( ! thiz.hasClass( 'js-toolset-shortcode-gui-invalid-attr' ) ) {
				switch ( thiz_type ) {
					case 'number':
						if (
							self.numeric_natural_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_number_invalid;
						}
						break;
					case 'numberextended':
						if (
							self.numeric_natural_extended_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_number_invalid;
						}
						break;
					case 'numberlist':
						if (
							self.numeric_natural_list_pattern.test( thiz_val.replace(/\s+/g, '') ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_numberlist_invalid;
						}
						break;
					case 'year':
						if (
							self.year_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_year_invalid;
						}
						break;
					case 'month':
						if (
							self.month_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_month_invalid;
						}
						break;
					case 'week':
						if (
							self.week_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_week_invalid;
						}
						break;
					case 'day':
						if (
							self.day_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_day_invalid;
						}
						break;
					case 'hour':
						if (
							self.hour_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_hour_invalid;
						}
						break;
					case 'minute':
						if (
							self.minute_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_minute_invalid;
						}
						break;
					case 'second':
						if (
							self.second_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_second_invalid;
						}
						break;
					case 'dayofyear':
						if (
							self.dayofyear_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_dayofyear_invalid;
						}
						break;
					case 'dayofweek':
						if (
							self.dayofweek_pattern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_dayofweek_invalid;
						}
						break;
					case 'url':
						if (
							self.url_patern.test( thiz_val ) == false
							&& thiz_val != ''
						) {
							thiz_valid = false;
							thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
							thiz_message = wpv_shortcodes_gui_texts.attr_url_invalid;
						}
						break;
				}
				if ( ! thiz_valid ) {
					valid = false;
					error_container
						.wpvToolsetMessage({
							text: thiz_message,
							type: 'error',
							inline: false,
							stay: true
						});
					// Hack to allow more than one error message per filter
					error_container
						.data( 'message-box', null )
						.data( 'has_message', false );
				}
			}
		});
		// Special case: item selector tab
        var $itemSelector = $( '.js-wpv-shortcode-gui-item-selector:checked', evaluate_container );
		if (
            $itemSelector.length > 0
			&& (
				'object_id' == $itemSelector.val()
				|| 'object_id_raw' == $itemSelector.val()
			)
		) {

            var item_selection = ( 'object_id' == $itemSelector.val() )
				? $( '[name="specific_object_id"]', evaluate_container )
				: $( '[name="specific_object_id_raw"]', evaluate_container );


            var item_selection_id = item_selection.val(),
                item_selection_valid = true,
                item_selection_message = '';

			
			if ( 
				'' == item_selection_id 
				|| null == item_selection_id
			) {
				item_selection_valid = false;
				item_selection.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
				if ( item_selection.hasClass( 'toolset_select2-hidden-accessible' ) ) {
					item_selection
						.toolset_select2()
							.data( 'toolset_select2' )
								.$selection
									.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
				}
				item_selection_message = wpv_shortcodes_gui_texts.attr_empty;
			} else if ( self.numeric_natural_pattern.test( item_selection_id ) == false ) {
				item_selection_valid = false;
				item_selection.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
				if ( item_selection.hasClass( 'toolset_select2-hidden-accessible' ) ) {
					item_selection
						.toolset_select2()
							.data( 'toolset_select2' )
								.$selection
									.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
				}
				item_selection_message = wpv_shortcodes_gui_texts.attr_number_invalid;
			}
			if ( ! item_selection_valid ) {
				valid = false;
				error_container
					.wpvToolsetMessage({
						text: item_selection_message,
						type: 'error',
						inline: false,
						stay: true
					});
				// Hack to allow more than one error message per filter
				error_container
					.data( 'message-box', null )
					.data( 'has_message', false );
			}
		}
		return valid;
	};

	/**
	 * Clean validation errors on input chnage.
	 *
	 * @since unknown
	 */
	$( document ).on( 'change keyup input cut paste', '.js-wpv-shortcode-gui-dialog-container input, .js-wpv-shortcode-gui-dialog-container select', function() {
		var thiz = $( this );
		thiz.removeClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
		if ( thiz.hasClass( 'toolset_select2-hidden-accessible' ) ) {
			thiz
				.toolset_select2()
					.data( 'toolset_select2' )
						.$selection
							.removeClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
		}
		thiz
			.closest( '.js-wpv-shortcode-gui-dialog-container' )
			.find('.toolset-alert-error').not( '.js-wpv-permanent-alert-error' )
			.each( function() {
				$( this ).remove();
			});
	});

	/**
	 * Helper method to remove validation messages from a container.
	 *
	 * @since unknown
	 */
	self.clear_validate_messages = function( container ) {
		container
			.find('.toolset-alert-error').not( '.js-wpv-permanent-alert-error' )
			.each( function() {
				$( this ).remove();
			});
		container
			.find( '.js-toolset-shortcode-gui-invalid-attr' )
			.removeClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
	};

	/**
	 * Make sure that required attributs are filled before crafting the shortcode.
	 *
	 * @since 1.9.0
	 */

	self.manage_required_attributes = function( evaluate_container, error_container ) {
		var valid = true,
			error_container = $( '#js-wpv-shortcode-gui-dialog-container' ).find( '.js-wpv-filter-toolset-messages' );
		evaluate_container.find( '.js-shortcode-gui-field.js-wpv-shortcode-gui-required' ).each( function() {
			var thiz = $( this ),
				thiz_valid = true,
				thiz_parent = thiz.closest('.js-wpv-shortcode-gui-attribute-custom-combo');
			if ( thiz_parent.length ) {
				if (
					$( '[value=custom-combo]:checked', thiz_parent ).length
					&& thiz.val() == ''
				) {
					thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
					thiz_valid = false;
				}
			} else {
				// Here we are checking for empty text inputs and selects with the default empty option selected.
				if ( null === thiz.val() || '' == thiz.val() ) {
					thiz.addClass( 'toolset-shortcode-gui-invalid-attr js-toolset-shortcode-gui-invalid-attr' );
					thiz_valid = false;
				}
			}
			if ( ! thiz_valid ) {
				valid = false;
				error_container
					.wpvToolsetMessage({
						text: wpv_shortcodes_gui_texts.attr_empty,
						type: 'error',
						inline: false,
						stay: true
					});
				// Hack to allow more than one error message per filter
				error_container
					.data( 'message-box', null )
					.data( 'has_message', false );
			}
		});
		return valid;
	};

	/**
	 * Craft a shortcode based on its attributes settings selection.
	 *
	 * @since 1.9.0
	 * @since 2.3.0 Use the proper self.do_shortcode_gui_action method.
	 * @todo Rename for a proper method naming.
	 * @todo Add proper Toolset.hooks filters to filter both each attribute key->value pair and the whole as an object.
	 *     It should replace self.filter_computed_attribute_value, self.filter_computed_attribute_pairs, self.filter_computed_content
	 */

	self.wpv_insert_shortcode = function() {

		var shortcode_name = $('.js-wpv-shortcode-gui-shortcode-name').val(),
			shortcode_attribute_key,
			shortcode_attribute_value,
			shortcode_attribute_default_value,
			shortcode_attribute_string = '',
			shortcode_attribute_values = {},
			shortcode_raw_attribute_values = {},
			shortcode_content = '',
			shortcode_to_insert = '',
			shortcode_data = {},
			shortcode_valid = self.validate_shortcode_attributes( $( '#js-wpv-shortcode-gui-dialog-container' ), $( '#js-wpv-shortcode-gui-dialog-container' ), $( '#js-wpv-shortcode-gui-dialog-container' ).find( '.js-wpv-filter-toolset-messages' ) );
		
		shortcode_valid = Toolset.hooks.applyFilters(
			'wpv-filter-wpv-shortcodes-gui-validate-shortcode',
			shortcode_valid, 
			shortcode_name
		);
		shortcode_valid = Toolset.hooks.applyFilters(
			'wpv-filter-wpv-shortcodes-gui-' + shortcode_name + '-validate-shortcode',
			shortcode_valid
		);
		
		if ( ! shortcode_valid ) {
			return;
		}
		$( '.js-wpv-shortcode-gui-attribute-wrapper', '#js-wpv-shortcode-gui-dialog-container' ).each( function() {
			var thiz_attribute_wrapper = $( this ),
				shortcode_attribute_key = thiz_attribute_wrapper.data('attribute');
			switch ( thiz_attribute_wrapper.data('type') ) {
				case 'post':
				case 'user':
					shortcode_attribute_value = $( '.js-wpv-shortcode-gui-item-selector:checked', thiz_attribute_wrapper ).val();
					switch( shortcode_attribute_value ) {
						case 'current':
							shortcode_attribute_value = false;
							break;
						case 'parent':
							// The value is correct out of the box
							break;
						case 'related':
							shortcode_attribute_value = $( '[name="related_object"]:checked', thiz_attribute_wrapper ).val();
							break;
						case 'referenced':
							shortcode_attribute_value = $( '[name="referenced_object"]:checked', thiz_attribute_wrapper ).val();
							break;
                        case 'object_id_raw':
                            shortcode_attribute_value = $( '[name="specific_object_id_raw"]', thiz_attribute_wrapper ).val();
                            break;
						case 'object_id':
							shortcode_attribute_value = $( '[name="specific_object_id"]', thiz_attribute_wrapper ).val();
						default:
					}
					break;
				case 'select':
					shortcode_attribute_value = $('option:checked', thiz_attribute_wrapper ).val();
					break;
				case 'radio':
				case 'radiohtml':
					shortcode_attribute_value = $('input:checked', thiz_attribute_wrapper ).val();
					if ( 'custom-combo' == shortcode_attribute_value ) {
						shortcode_attribute_value = $('.js-wpv-shortcode-gui-attribute-custom-combo-target', $('input:checked', thiz_attribute_wrapper ).closest('.js-wpv-shortcode-gui-attribute-custom-combo')).val();
					}
					break;
				case 'checkbox':
					shortcode_attribute_value = $('input:checked', thiz_attribute_wrapper ).val();
					break;
				default:
					shortcode_attribute_value = $('input', thiz_attribute_wrapper ).val();
			}

			shortcode_attribute_default_value = thiz_attribute_wrapper.data('default');
			/**
			 * Fix true/false from data attribute for shortcode_attribute_default_value
			 */
			if ( 'boolean' == typeof shortcode_attribute_default_value ) {
				shortcode_attribute_default_value = shortcode_attribute_default_value ? 'true' :'false';
			}
			
			shortcode_raw_attribute_values[ shortcode_attribute_key ] = shortcode_attribute_value;
			/**
			 * Filter value
			 */
			shortcode_attribute_value = self.filter_computed_attribute_value( shortcode_name, shortcode_attribute_key, shortcode_attribute_value );
			/**
			 * Add to the shortcode_attribute_values object
			 */
			if (
				shortcode_attribute_value
				&& shortcode_attribute_value != shortcode_attribute_default_value
			) {
				shortcode_attribute_values[shortcode_attribute_key] = shortcode_attribute_value;
			}
		});
		// Filter pairs key => value
		shortcode_attribute_values = self.filter_computed_attribute_pairs( shortcode_name, shortcode_attribute_values );
		
		shortcode_attribute_values = Toolset.hooks.applyFilters( 'wpv-filter-wpv-shortcodes-gui-computed-attributes-pairs', shortcode_attribute_values, shortcode_name );
		shortcode_attribute_values = Toolset.hooks.applyFilters( 'wpv-filter-wpv-shortcodes-gui-' + shortcode_name + '-computed-attributes-pairs', shortcode_attribute_values );
		
		// Compose the shortcode_attribute_string string
		_.each( shortcode_attribute_values, function( value, key ) {
			if ( value ) {
				shortcode_attribute_string += " " + key + '="' + value + '"';
			}
		});
		shortcode_to_insert = '[' + shortcode_name + shortcode_attribute_string + ']';
		/**
		 * Shortcodes with content
		 */
		if ( $( '.js-wpv-shortcode-gui-content' ).length > 0 ) {
			shortcode_content = $( '.js-wpv-shortcode-gui-content' ).val();
			/**
			 * Filter shortcode content
			 */
			shortcode_content = self.filter_computed_content( shortcode_name, shortcode_content, shortcode_attribute_values );
			shortcode_to_insert += shortcode_content;
			shortcode_to_insert += '[/' + shortcode_name + ']';
		}
		/**
		 * Close, insert if needed and fire custom event
		 */
		self.dialog_insert_shortcode.dialog( 'close' );
		
		shortcode_data = {
			shortcode:		shortcode_to_insert,
			name:			shortcode_name,
			attributes:		shortcode_attribute_values,
			raw_attributes:	shortcode_raw_attribute_values,
			content:		shortcode_content
		};
		
		self.do_shortcode_gui_action( shortcode_data );

	};

    //--------------------------------
    // Compatibility
    //--------------------------------

    /**
     * Handle the event that is triggered by Fusion Builder when creating the WP editor instance.
	 *
	 * The event was added as per our request because Fusion Builder does not load the WP editor using
	 * the native PHP function "wp_editor". It creates the WP editor instance on JS, so no PHP actions
	 * to add custom media buttons like ours are available. It generates the media button plus the toolbar that
	 * contains it as javascript objects that it appends to its own template. It offers no way of adding our custom
	 * buttons to it.
	 *
	 * @param event			The actual event.
	 * @param editorId		The id of the editor that is being created.
     *
     * @since 2.4.0
     */
    $( document ).on( 'fusionButtons', function( event, editorId ) {
		self.add_fields_and_views_button_to_dynamic_editor( editorId );
    });
	
	/**
     * Handle the event that is triggered by Toolset Types when creating a WP editor instance.
	 *
	 * The event is fired when a WYSIWYG field is dynamically initialized in the backend.
	 *
	 * @param event			The actual event.
	 * @param editorId		The id of the editor that is being created.
     *
     * @since 2.0
     */
	$( document ).on( 'toolset:types:wysiwygFieldInited', function( event, editorId ) {
		self.add_fields_and_views_button_to_dynamic_editor( editorId );
    });

    /**
     * add_fields_and_views_button_to_dynamic_editor
     *
	 * Add a "Fields and Views" button dynamically to any native editor that contains a media toolbar, given its editor ID.
     *
     * @since 2.4.0
     */

    self.add_fields_and_views_button_to_dynamic_editor = function( editor_id ) {
        var media_buttons = $( '#wp-' + editor_id + '-media-buttons' ),
            button = '<span'
                + ' class="button js-wpv-fields-and-views-in-toolbar"'
                + ' data-editor="' + editor_id + '">'
                + '<i class="icon-views-logo fa fa-wpv-custom ont-icon-18 ont-color-gray"></i>'
                + '<span class="button-label">' + wpv_shortcodes_gui_texts.wpv_fields_and_views_button_title + '</span>'
                + '</span>',
            fields_and_views_button = $( button );
		
		if ( media_buttons.find( '.js-wpv-fields-and-views-in-toolbar' ).length == 0 ) {
			fields_and_views_button.appendTo( media_buttons );
		}
    };

	//--------------------------------
	// Special cases
	//--------------------------------

    /**
     * wpv-current-user management
     * Handle the change in format that shows/hides the show attribute
     *
     * @since 2.4.0
     */
    $( document ).on( 'change', '#wpv-current-user-info .js-shortcode-gui-field', function() {
        self.manage_wpv_current_user_info_show_relation();
    });

    self.manage_wpv_current_user_info_show_relation = function() {
        if ( $( '#wpv-current-user-info' ).length ) {
            if ( 'profile_picture' == $( '.js-shortcode-gui-field:checked', '#wpv-current-user-info' ).val() ) {
                $( '[class*="js-wpv-shortcode-gui-attribute-wrapper-for-profile-picture"]', '#wpv-current-user-display-options' ).slideDown( 'fast' );
            } else {
                $( '[class*="js-wpv-shortcode-gui-attribute-wrapper-for-profile-picture"]', '#wpv-current-user-display-options' ).slideUp( 'fast' );
            }
        }
    };
	
	$( document ).on( 'change', '#wpv-login-form-allow_remember .js-shortcode-gui-field', function() {
        self.manage_wpv_login_form_show_remember_me_state();
    });
	
	self.manage_wpv_login_form_show_remember_me_state = function() {
		if ( $( '#wpv-login-form-allow_remember' ).length ) {
			if ( 'true' == $( '.js-shortcode-gui-field:checked', '#wpv-login-form-allow_remember' ).val() ) {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-remember_default', '.js-wpv-shortcode-gui-attribute-group-for-remember_me_combo' ).slideDown( 'fast' );
			} else {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-remember_default', '.js-wpv-shortcode-gui-attribute-group-for-remember_me_combo' ).slideUp( 'fast' );
			}
		}
	}

	/**
	 * wpv-post-author management
	 * Handle the change in format that shows/hides the show attribute
	 *
	 * @since 1.9.0
	 * @since 2.4.0 Added support to handle the appearance of the profile picture functionality options.
	 */
	$( document ).on( 'change', '#wpv-post-author-format .js-shortcode-gui-field', function() {
		self.manage_wpv_post_author_format_show_relation();
	});

	self.manage_wpv_post_author_format_show_relation = function() {
		if ( $( '#wpv-post-author-format' ).length ) {
			if ( 'meta' == $( '.js-shortcode-gui-field:checked', '#wpv-post-author-format' ).val() ) {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-meta', '#wpv-post-author-display-options' ).slideDown( 'fast' );
                $( '[class*="js-wpv-shortcode-gui-attribute-wrapper-for-profile-picture"]', '#wpv-post-author-display-options' ).hide();
			} else if ( 'profile_picture' == $( '.js-shortcode-gui-field:checked', '#wpv-post-author-format' ).val() ) {
                $( '[class*="js-wpv-shortcode-gui-attribute-wrapper-for-profile-picture"]', '#wpv-post-author-display-options' ).slideDown( 'fast' );
                $( '.js-wpv-shortcode-gui-attribute-wrapper-for-meta', '#wpv-post-author-display-options' ).hide();
            } else {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-meta', '#wpv-post-author-display-options' ).hide();
                $( '[class*="js-wpv-shortcode-gui-attribute-wrapper-for-profile-picture"]', '#wpv-post-author-display-options' ).hide();
			}
		}
	};
	
	/**
     * wpv-post-excerpt management
     * Handle the change in output that shows/hides the output formatting attributes
     *
     * @since 2.3.0
     */
    $( document ).on( 'change', '#wpv-post-excerpt-output .js-shortcode-gui-field', function() {
        self.manage_wpv_post_excerpt_output_show_relation();
    });

    self.manage_wpv_post_excerpt_output_show_relation = function() {
        if ( $( '#wpv-post-excerpt-output' ).length ) {
            if ( 'formatted' == $( '.js-shortcode-gui-field:checked', '#wpv-post-excerpt-output' ).val() ) {
                $( '.js-wpv-shortcode-gui-attribute-group-for-length_combo', '#wpv-post-excerpt-display-options' ).slideDown( 'fast' );
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-more', '#wpv-post-excerpt-display-options' ).slideDown( 'fast' );
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-format', '#wpv-post-excerpt-display-options' ).slideDown( 'fast' );
            } else {
                $( '.js-wpv-shortcode-gui-attribute-group-for-length_combo, .js-wpv-shortcode-gui-attribute-wrapper-for-more, .js-wpv-shortcode-gui-attribute-wrapper-for-format', '#wpv-post-excerpt-display-options' ).slideUp( 'fast' );
            }
        }
    };

	/**
	 * wpv-post-taxonomy management
	 * Handle the change in format that shows/hides the show attribute
	 *
	 * @since 1.9.0
	 */
	$( document ).on( 'change', '#wpv-post-taxonomy-format .js-shortcode-gui-field', function() {
		self.manage_wpv_post_taxonomy_format_show_relation();
	});

	self.manage_wpv_post_taxonomy_format_show_relation = function() {
		if ( $( '#wpv-post-taxonomy-format' ).length ) {
			if ( 'link' == $( '.js-shortcode-gui-field:checked', '#wpv-post-taxonomy-format' ).val() ) {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-show', '#wpv-post-taxonomy-display-options' ).slideDown( 'fast' );
			} else {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-show', '#wpv-post-taxonomy-display-options' ).slideUp( 'fast' );
			}
		}
	};

	/**
	 * wpv-post-featured-image management
	 * Handle the change in output that shows/hides the class attribute
	 *
	 * @since 1.9.0
	 */
	$( document ).on( 'change', '#wpv-post-featured-image-output.js-shortcode-gui-field', function() {
		self.manage_wpv_post_featured_image_output_show_class();
	});

	self.manage_wpv_post_featured_image_output_show_class = function() {
		if ( $( '#wpv-post-featured-image-output' ).length ) {
			if ( 'img' == $( '#wpv-post-featured-image-output.js-shortcode-gui-field' ).val() ) {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-class', '#wpv-post-featured-image-display-options' ).slideDown( 'fast' );
			} else {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-class', '#wpv-post-featured-image-display-options' ).slideUp( 'fast' );
			}
		}
	};

	/**
	 * wpv-post-featured-image management
	 * Handle the change in UI to show/hide attributes for custom image resizing and cropping
	 *
	 * @since 2.2.0
	 */
	$( document ).on( 'change', '#wpv-post-featured-image-size.js-shortcode-gui-field', function() {
		self.manage_wpv_post_featured_image_resize_show_relation();
	});

	self.manage_wpv_post_featured_image_resize_show_relation = function() {
		if( 'custom' == $( '#wpv-post-featured-image-size.js-shortcode-gui-field' ).val() ) {
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-width' ).slideDown( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-height' ).slideDown( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop' ).slideDown( 'fast' );

			self.manage_wpv_post_featured_image_crop_show_relation();
		} else {
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-width' ).slideUp( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-height' ).slideUp( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop' ).slideUp( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_horizontal' ).slideUp( 'fast' );
			$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_vertical' ).slideUp( 'fast' );
		}
	};

	/**
	 * wpv-post-featured-image management
	 * Handle the change in UI to show/hide attributes for crop positions
	 *
	 * @since 2.2
	 */
	$( document ).on( 'change', '#wpv-post-featured-image-crop .js-shortcode-gui-field', function() {
		self.manage_wpv_post_featured_image_crop_show_relation();
	});

	self.manage_wpv_post_featured_image_crop_show_relation = function() {
		if ( $( '#wpv-post-featured-image-crop' ).length ) {
			if( 'true' == $( '.js-shortcode-gui-field:checked', '#wpv-post-featured-image-crop' ).val() ) {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_horizontal' ).slideDown( 'fast' );
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_vertical' ).slideDown( 'fast' );
			} else {
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_horizontal' ).slideUp( 'fast' );
				$( '.js-wpv-shortcode-gui-attribute-wrapper-for-crop_vertical' ).slideUp( 'fast' );
			}
		}
	};


	/**
	 * Filter a computed value for a shortcode attribute.
	 *
	 * @param shortcode	string	The shortcode tag.
	 * @param attribute	string	The shorcode attribute which value to filter.
	 * @param value		string	Th shortcode attribut computed value to filter.
	 *
	 * @since 1.9.0
	 * @todo Deprecate and use a proper Toolset.hooks filter.
	 */
	self.filter_computed_attribute_value = function( shortcode, attribute, value ) {
		switch ( shortcode ) {
			case 'wpv-post-author':
				if (
					'meta' == attribute
					&& 'meta' != $( '.js-shortcode-gui-field:checked', '#wpv-post-author-format' ).val()
				) {
					value = false;
				}
				break;
			case 'wpv-post-taxonomy':
				if (
					'show' == attribute
					&& 'link' != $( '.js-shortcode-gui-field:checked', '#wpv-post-taxonomy-format' ).val()
				) {
					value = false;
				}
				break;
			case 'wpv-post-featured-image':
				if (
					'class' == attribute
					&& 'img' != $( '#wpv-post-featured-image-output.js-shortcode-gui-field' ).val()
				) {
					value = false;
				}
				break;
			case 'wpv-post-excerpt':
				if (
					'output' != attribute
					&& 'raw' == $( '.js-shortcode-gui-field:checked', '#wpv-post-excerpt-output' ).val()
				) {
					value = false;
				}
				break;
			case 'wpv-conditional':
				switch( attribute ) {
					case 'if':
						if ( self.views_conditional_use_gui ) {
							value = self.wpv_conditional_create_if_attribute( 'singleline' );
						} else {
							value = $('#wpv-conditional-custom-expressions').val();
						}
						if ( value == '' ) {
							value = "('1' eq '1')";
						}
						break;
					/*
					 case 'custom-expressions':
					 value = false;
					 */
				}
				break;
			case 'wpv-for-each':
				if (
					'parse_shortcodes' == attribute
				) {
					value = false;
				}
				break;
		}
		return value;
	};

	/**
	 * Filter the computed values for a shortcode attributes.
	 *
	 * @param shortcode		string	The shortcode tag.
	 * @param attributes	object	The shorcode attributes which values to filter.
	 *
	 * @since 1.9.0
	 * @todo Deprecate and use a proper Toolset.hooks filter.
	 */
	self.filter_computed_attribute_pairs = function( shortcode, attributes ) {
		if ( shortcode in self.shortcode_gui_computed_attribute_pairs_filters ) {
			var filter_callback_func = self.shortcode_gui_computed_attribute_pairs_filters[ shortcode ];
			if ( typeof filter_callback_func == "function" ) {
				attributes = filter_callback_func( attributes );
			}
		}
		return attributes;
	};

	/**
	 * Filter the computed content for a shortcode.
	 *
	 * @param shortcode		string	The shortcode tag.
	 * @param content		string	The content of the shortcode.
	 * @param attributes	object	The shorcode attribute values.
	 *
	 * @since 1.9.0
	 * @todo Deprecate and use a proper Toolset.hooks filter.
	 */
	self.filter_computed_content = function( shortcode, content, values ) {
		switch ( shortcode ) {
			case 'wpv-for-each':
				if ( values.hasOwnProperty( 'field' ) ) {
					var parse_shortcodes = '';
					if ( $( '.js-shortcode-gui-field:checked', '#wpv-for-each-parse_shortcodes' ).val() == 'true' ){
						parse_shortcodes = ' parse_shortcodes="true"';
					}
					content = '[wpv-post-field name="' + values.field + '"'+ parse_shortcodes +']';
				}
				break;
		}
		return content;
	};

	/**
	 * Load the Post field section on the shortcodes GUI on demand.
	 * Used to load non-Types custom fields only when needed.
	 *
	 * @since 1.10.0
	 * @since 2.3.0 Improved UX.
	 */

	self.load_post_field_section_on_demand = function( event, object ) {
		event.stopPropagation();
		var thiz = $( object ),
			thiz_group_list = thiz.closest( '.js-wpv-shortcode-gui-group-list' ),
			spinnerContainer = $( '<span class="wpv-spinner ajax-loader">' ).insertAfter( thiz ).show(),
			post_fields_section = '';
		thiz.prop( 'disabled', true );
		if ( self.post_fields_section ) {
			thiz_group_list
				.fadeOut( 'fast', function() {
					thiz_group_list
						.html( response.data.section )
						.fadeIn( 'fast' );
				});
		} else {
			var url = wpv_shortcodes_gui_texts.ajaxurl + '&action=wpv_shortcodes_gui_load_post_fields_on_demand';
			$.ajax({
				url: url,
				success: function( response ) {
					self.post_fields_list = response.data.fields;
					_.each( self.post_fields_list, function( element, index, list ) {
						post_fields_section += '<li class="item">';
						post_fields_section += '<button class="button button-secondary button-small js-wpv-shortcode-gui-post-field-section-item" data-fieldkey="' + element + '">';
						post_fields_section += element;
						post_fields_section += '</button>';
						post_fields_section += '</li>';
					});
					self.post_fields_section = post_fields_section;
					thiz_group_list
						.fadeOut( 'fast', function() {
							thiz_group_list
								.html( self.post_fields_section )
								.fadeIn( 'fast' );
						});
				}
			});
		}
	};

	/**
	 * Insert wpv-post-field shortcodes after generating the section on the GUI on demand
	 *
	 * @since 1.10.0
	 */

	$( document ).on( 'click', '.js-wpv-shortcode-gui-post-field-section-item', function() {
		var thiz = $( this ),
			thiz_fieldkey = thiz.data( 'fieldkey' ),
			thiz_shortcode = "[wpv-post-field name='" + thiz_fieldkey + "']";
		self.insert_shortcode_with_no_attributes( 'wpv-post-field', thiz_shortcode );
	});
	
	/**
	 * Init main method:
	 * - Init API hooks.
	 * - Init dialogs.
	 * - Init the Admin Bar button, if needed.
	 * - Init registered inputs buttons eligible for Fields and Views shortcodes appending, if any.
	 *
	 * @since unknown
	 */
	self.init = function() {
		self.init_hooks();
		self.init_dialogs();
		self.init_admin_bar_button();
		self.init_input_button();
	};

	self.init();

};

jQuery( document ).ready( function( $ ) {
	WPViews.shortcodes_gui = new WPViews.ShortcodesGUI( $ );
});

var wpcfFieldsEditorCallback_redirect = null;

function wpcfFieldsEditorCallback_set_redirect(function_name, params) {
	wpcfFieldsEditorCallback_redirect = {'function' : function_name, 'params' : params};
}

/*
 * wpv-conditional shortcode QTags callback
 */
function wpv_add_conditional_quicktag_function(e, c, ed) {
	var  t = this;
	/*
	 !Important fix. If shortcode added from quicktags and not closed and we chage mode from text to visual, JS will generate error that closeTag = undefined.
	 */
	t.closeTag = function(el, event) {
		var ret = false, i = 0;
		while ( i < event.openTags.length ) {
			ret = event.openTags[i] == this.id ? i : false;
			el.value = this.display;
			i ++;
		}
		ed.openTags.splice(ret, 1);
	};
	window.wpcfActiveEditor = ed.id;
	var current_editor_object = {};
	if ( ed.canvas.selectionStart !== ed.canvas.selectionEnd ) {
		//When texty selected
		current_editor_object = {'e' : e, 'c' : c, 'ed' : ed, 't' : t, 'post_id' : '', 'close_tag' : true, 'codemirror' : ''};
		WPViews.shortcodes_gui.wpv_insert_popup_conditional('wpv-conditional', icl_editor_localization_texts.wpv_insert_conditional_shortcode, {}, icl_editor_localization_texts.wpv_editor_callback_nonce, current_editor_object );
	} else if ( ed.openTags ) {
		// if we have an open tag, see if it's ours
		var ret = false, i = 0, t = this;
		while ( i < ed.openTags.length ) {
			ret = ed.openTags[i] == t.id ? i : false;
			i ++;
		}
		if ( ret === false ) {
			t.tagStart = '';
			t.tagEnd = false;
			if ( ! ed.openTags ) {
				ed.openTags = [];
			}
			ed.openTags.push(t.id);
			e.value = '/' + e.value;
			current_editor_object = {'e' : e, 'c' : c, 'ed' : ed, 't' : t, 'post_id' : '', 'close_tag' : false, 'codemirror' : ''};
			WPViews.shortcodes_gui.wpv_insert_popup_conditional('wpv-conditional', icl_editor_localization_texts.wpv_insert_conditional_shortcode, {}, icl_editor_localization_texts.wpv_editor_callback_nonce, current_editor_object );
		} else {
			// close tag
			ed.openTags.splice(ret, 1);
			WPViews.shortcodes_gui.views_conditional_qtags_opened = false;
			var tagStart = '[/wpv-conditional]';
			t.tagStart = Toolset.hooks.applyFilters( 'wpv-filter-wpv-shortcodes-transform-format', tagStart );
			e.value = t.display;
			QTags.TagButton.prototype.callback.call(t, e, c, ed);
		}
	} else {
		// last resort, no selection and no open tags
		// so prompt for input and just open the tag
		t.tagStart = '';
		t.tagEnd = false;
		if ( ! ed.openTags ) {
			ed.openTags = [];
		}
		ed.openTags.push(t.id);
		e.value = '/' + e.value;
		current_editor_object = {'e' : e, 'c' : c, 'ed' : ed, 't' : t, 'post_id' : '', 'close_tag' : false, 'codemirror' : ''};
		WPViews.shortcodes_gui.wpv_insert_popup_conditional('wpv-conditional', icl_editor_localization_texts.wpv_insert_conditional_shortcode, {}, icl_editor_localization_texts.wpv_editor_callback_nonce, current_editor_object );
	}
}