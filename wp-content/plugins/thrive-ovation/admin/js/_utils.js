/**
 * Created by Ovidiu on 4/14/2016.
 */
var ThriveOvation = ThriveOvation || {};
ThriveOvation.util = ThriveOvation.util || {};

_.templateSettings = {
	evaluate: /<#([\s\S]+?)#>/g,
	interpolate: /<#=([\s\S]+?)#>/g,
	escape: /<#-([\s\S]+?)#>/g
};
(function ( $ ) {
	/**
	 * Add loading spinner to a specified element
	 * @param $elem
	 */
	ThriveOvation.util.setLoading = function ( $elem ) {
		$elem.addClass( 'tvd-disabled' );
		$elem.prepend( '<span class="tvd-icon-spinner mdi-pulse tvo-small-icon tvd-margin-right-small"></span>' );
	};

	/**
	 * Remove loading spinner from element
	 * @param $elem
	 */
	ThriveOvation.util.removeLoading = function ( $elem ) {
		$elem.removeClass( 'tvd-disabled' );
		$elem.find( '.tvd-icon-spinner' ).remove();
	};

	ThriveOvation.util.validateInput = function ( $input, message, with_toast ) {
		var _value = ($input instanceof jQuery) ? $input.val() : $input,
			_response = true;
		if ( _value == '' ) {
			if ( ! message ) {
				message = ThriveOvation.translations.isRequired;
			}
			_response = false;
			if ( $input instanceof jQuery ) {
				$input.removeClass( "tvd-valid" );
				$input.addClass( "tvd-invalid" );
			}
		}
		if ( message && message.length > 0 && _response === false && with_toast ) {
			TVE_Dash.err( message, ThriveOvation.const.toast_timeout );
		}

		return _response;
	};

	ThriveOvation.util.containsObject = function ( obj, list ) {
		var i;
		for ( i = 0; i < list.length; i ++ ) {
			if ( list[i].id == obj.id ) {
				return true;
			}
		}

		return false;
	};

	ThriveOvation.util.build_mce_init = function ( defaults, _id ) {
		var mce = {}, qt = {};

		if ( ! this.hasTinymce() ) {
			return false;
		}

		mce[_id] = $.extend( true, {}, defaults.mce );
		qt[_id] = $.extend( true, {}, defaults.qt );

		qt[_id].id = _id;

		mce[_id].selector = '#' + _id;
		mce[_id].body_class = mce[_id].body_class.replace( 'tvo-tinymce-tpl', _id );

		return {
			'mce_init': mce,
			'qt_init': qt
		};
	};

	ThriveOvation.util.clearMCEEditor = function ( ignore ) {
		var _current_ids = ['tvo-testimonial-content-tinymce', 'tvo-email-template'];
		_current_ids.forEach( function ( element ) {
			if ( ignore != element ) {
				if ( typeof tinymce !== 'undefined' ) {
					var _current = tinymce.get( element );
					if ( _current ) {
						_current.remove();
					}
				}
			}
		} );
	};
	/**
	 * Return testimonial content based on the fact that tinymce is loaded or not in the page
	 * @param $element
	 * @returns {*}
	 */
	ThriveOvation.util.getTestimonialContent = function ( $element ) {
		if ( this.hasTinymce() ) {
			return tinyMCE.get( 'tvo-testimonial-content-tinymce' ).getContent()
		}

		return $element.find( '#tvo-testimonial-content-tinymce' ).val();
	};

	/**
	 * Checks if tinymce is loaded into the page
	 *
	 * @returns {*}
	 */
	ThriveOvation.util.hasTinymce = function () {
		return (window.tinymce) ? true : false;
	};


	ThriveOvation.util.validateURL = function ( $input ) {

		var _value = ($input instanceof jQuery) ? $input.val() : $input,
			pattern = new RegExp( '^(?!mailto:)(?:(?:http|https|ftp)://)(?:\\S+(?::\\S*)?@)?(?:(?:(?:[1-9]\\d?|1\\d\\d|2[01]\\d|22[0-3])(?:\\.(?:1?\\d{1,2}|2[0-4]\\d|25[0-5])){2}(?:\\.(?:[0-9]\\d?|1\\d\\d|2[0-4]\\d|25[0-4]))|(?:(?:[a-z\\u00a1-\\uffff0-9]+-?)*[a-z\\u00a1-\\uffff0-9]+)(?:\\.(?:[a-z\\u00a1-\\uffff0-9]+-?)*[a-z\\u00a1-\\uffff0-9]+)*(?:\\.(?:[a-z\\u00a1-\\uffff]{2,})))|localhost)(?::\\d{2,5})?(?:(/|\\?|#)[^\\s]*)?$' );
		if ( ! pattern.test( _value ) ) {
			$input.removeClass( "tvd-valid" );
			$input.addClass( "tvd-invalid" );
			return false;
		}
		return true;
	};

	ThriveOvation.util.validateEmail = function ( $input ) {

		var _value = ($input instanceof jQuery) ? $input.val() : $input,
			pattern = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

		if ( ! pattern.test( _value ) ) {
			if ( $input instanceof jQuery ) {
				$input.removeClass( "tvd-valid" );
				$input.addClass( "tvd-invalid" );
			}
			return false;
		} else {
			return true;
		}
	};

	ThriveOvation.util.bind_wistia = function () {
		if ( window.rebindWistiaFancyBoxes ) {
			window.rebindWistiaFancyBoxes();
		}
	}

	/**
	 *  Increment / Decrement the testimonial filter counters in the list view
	 * @param model
	 * @param operation
	 */
	ThriveOvation.util.decrementIncrementListCounters = function ( model, operation ) {
		var $input;
		switch ( model.get( 'status' ) ) {
			case ThriveOvation.const.status.ready_for_display:
				$input = jQuery( '.tvo-ready-for-display-c' );
				$input.html( parseInt( $input.text() ) + operation );
				break;
			case ThriveOvation.const.status.awaiting_approval:
				$input = jQuery( '.tvo-awaiting-approval-c' );
				$input.html( parseInt( $input.text() ) + operation );
				break;
			case ThriveOvation.const.status.awaiting_review:
				$input = jQuery( '.tvo-awaiting-review-c' );
				$input.html( parseInt( $input.text() ) + operation );
				break;
			case ThriveOvation.const.status.rejected:
				$input = jQuery( '.tvo-rejected-c' );
				$input.html( parseInt( $input.text() ) + operation );
				break;
		}

		if ( model.get( 'tags' ).length == 0 ) {
			$input = jQuery( '.tvo-untagged-c' );
			$input.html( parseInt( $input.text() ) + operation );
		}

		if ( model.get( 'picture_url' ).indexOf( ThriveOvation.testimonial_image_placeholder ) != - 1 ) {
			$input = jQuery( '.tvo-no-picture-c' );
			$input.html( parseInt( $input.text() ) + operation );
		}

	};

	/**
	 *
	 * @param previous_status
	 * @param actual_status
	 */
	ThriveOvation.util.incrementDecrementStatusCounters = function ( previous_status, actual_status ) {
		var $input;
		switch ( previous_status ) {
			case ThriveOvation.const.status.ready_for_display:
				$input = jQuery( '.tvo-ready-for-display-c' );
				break;
			case ThriveOvation.const.status.awaiting_approval:
				$input = jQuery( '.tvo-awaiting-approval-c' );
				break;
			case ThriveOvation.const.status.awaiting_review:
				$input = jQuery( '.tvo-awaiting-review-c' );
				break;
			case ThriveOvation.const.status.rejected:
				$input = jQuery( '.tvo-rejected-c' );
				break;
		}
		$input.html( parseInt( $input.text() ) - 1 );

		switch ( actual_status ) {
			case ThriveOvation.const.status.ready_for_display:
				$input = jQuery( '.tvo-ready-for-display-c' );
				break;
			case ThriveOvation.const.status.awaiting_approval:
				$input = jQuery( '.tvo-awaiting-approval-c' );
				break;
			case ThriveOvation.const.status.awaiting_review:
				$input = jQuery( '.tvo-awaiting-review-c' );
				break;
			case ThriveOvation.const.status.rejected:
				$input = jQuery( '.tvo-rejected-c' );
				break;
		}
		$input.html( parseInt( $input.text() ) + 1 );
	};
	/**
	 *  Increment / Decrement tag counters
	 * @param operation
	 */
	ThriveOvation.util.incrementDecrementTagCounters = function ( operation ) {
		var $input = jQuery( '.tvo-untagged-c' );
		$input.html( parseInt( $input.text() ) + operation );
	};

	/**
	 * bind the zclip js lib over a "copy" button
	 *
	 * @param $element jQuery elem
	 */
	ThriveOvation.bindZClip = function ( $element ) {
		function bind_it() {
			//bind zclip on links that copy the shortcode in clipboard
			try {
				$element.closest( '.tvo-copy-input-content' ).find( 'input.tvo-copy' ).on( 'click', function ( e ) {
					this.select();
					e.preventDefault();
					e.stopPropagation();
				} );
				$element.zclip( {
					path: ThriveOvation.const.wp_content + 'plugins/thrive-ovation/admin/js/libs/jquery.zclip.1.1.1/ZeroClipboard.swf',
					copy: function () {
						return jQuery( this ).parents( '.tvo-copy-input-content' ).find( 'input.tvo-copy' ).val();
					},
					afterCopy: function () {
						var $link = jQuery( this );
						$link.prev().select();
						var text = $link.html(),
							color_class;
						if ( $link.hasClass( 'tvd-btn-blue' ) ) {
							color_class = 'tvd-btn-blue';
							$link.removeClass( 'tvd-btn-blue' ).addClass( 'tvd-btn-green' )
						}
						$link.find( '.tvo-copy-text' ).html( '<span class="tvd-icon-check"></span>' );
						setTimeout( function () {
							if ( color_class ) {
								$link.removeClass( 'tvd-btn-green' ).addClass( color_class );
							}
							$link.find( '.tvo-copy-text' ).html( text );
						}, 3000 );
						$link.parent().prev().select();
					}
				} );
			} catch ( e ) {
				console.error && console.error( 'Error embedding zclip - most likely another plugin is messing this up' ) && console.error( e );
			}
		}

		setTimeout( bind_it, 200 );
	};


	/**
	 * Show / hides twitter or facebook
	 * @param event
	 * @param element
	 */
	ThriveOvation.util.CheckCheckboxValue = function ( event, element ) {
		var elem = jQuery( event.currentTarget );
		if ( elem.is( ':checked' ) == true ) {
			element.find( "." + elem.attr( 'data-hide' ) ).slideDown();
		} else {
			element.find( "." + elem.attr( 'data-hide' ) ).slideUp();
		}
	};

	/**
	 * Set a model with key - value
	 *
	 * @param elem
	 */
	ThriveOvation.util.setModelWithKeyValue = function ( elem, model ) {
		var data_key = elem.attr( 'data-key' ),
			data_value = elem.val(),
			obj = {};
		obj[data_key] = data_value;
		model.set( obj );
	};

	/**
	 * Adds new tag in the system
	 *
	 * @param tag
	 * @param self
	 */
	ThriveOvation.util.addNewTagInTheSystem = function ( tag, self ) {
		tag['name'] = tag['text'];

		if ( tag['text'] == tag['id'] ) {
			var tagModel = new ThriveOvation.models.Tag( tag );
			tagModel.set( 'post_id', 0 );
			TVE_Dash.showLoader();

			tagModel.save( null, {
				success: function ( model, response ) {
					self.$el.find( 'option[value="' + tag['id'] + '"]' ).remove();
					self.$el.find( '.tvo-add-tag-autocomplete' ).append( '<option value="' + response.tag.term_id + '" selected>' + response.tag.text + '</option>' ).trigger( 'change' );
					var obj = {id: response.tag.term_id, text: response.tag.text};
					ThriveOvation.availableTags.push( obj );
					TVE_Dash.hideLoader();
				},
				error: function ( model, response ) {
					TVE_Dash.err( ThriveOvation.translations.testimonial_tag_added_fail_toast );
					TVE_Dash.hideLoader();
				}
			} );
		}
	};


	/**
	 * Adds new tag in the system
	 *
	 * @param tag
	 * @param self
	 */
	ThriveOvation.util.addNewTagInTheSystemFromComments = function ( tag ) {
		tag['name'] = tag['text'];

		if ( tag['text'] == tag['id'] ) {
			var tagModel = new ThriveOvation.models.Tag( tag );
			tagModel.set( 'post_id', 0 );
			TVE_Dash.showLoader();

			tagModel.save( null, {
				success: function ( model, response ) {
					jQuery( 'option[value="' + tag['id'] + '"]' ).remove();
					jQuery( '.tvo-add-tag-autocomplete' ).append( '<option value="' + response.tag.term_id + '" selected>' + response.tag.text + '</option>' ).trigger( 'change' );
					var obj = {id: response.tag.term_id, text: response.tag.text};
					ThriveOvation.availableTags.push( obj );
					TVE_Dash.hideLoader();
				},
				error: function ( model, response ) {
					TVE_Dash.err( ThriveOvation.translations.testimonial_tag_added_fail_toast );
					TVE_Dash.hideLoader();
				}
			} );
		}
	};

	/**
	 * Return the path for the breadcrumbs.
	 * @param $base
	 * @param $route
	 * @returns {*} Returns an array of objects containing the title and the link
	 * @constructor
	 */
	ThriveOvation.util.Breadcrumbs = function ( $base, $route, $id ) {

		for ( var i in $base ) {

			var $current = {
				title: $base[i].title,
				url: $base[i].url
			}, $links = [];

			/* If the current route is the one we are looking for, we return it in an array */
			if ( $base[i].key == $route ) {
				$current.url += typeof $id === 'undefined' ? '' : '/' + $id

				return [$current];
			}

			/* Search the route in the descendants  */
			$links = ThriveOvation.util.Breadcrumbs( $base[i].kids, $route, $id );

			/* If something was return, it means that one of the kids has the route => this title is also part of the breadcrumb */
			if ( $links.length > 0 ) {
				/* Push the current link at the beginning of the array */
				$links.unshift( $current );
				return $links;
			}
		}

		return typeof $links === 'undefined' ? [] : $links;

	};

	/**
	 * Increments / Decrements space depending on the show / hide checkboxes checked
	 *
	 * @param cut_space_elem
	 * @param operation
	 */
	ThriveOvation.util.incrementDecrementShowHideSpace = function ( cut_space_elem, operation ) {
		var add_space_elemelem, add_class, remove_class;
		switch ( cut_space_elem ) {
			case 'tvo-show-hide-tags':
				add_space_elemelem = '.tvo-show-hide-testimonial-info-container';
				remove_class = 'tvd-s3';
				add_class = 'tvd-s6';
				break;
			case 'tvo-show-hide-status':
				add_space_elemelem = '.tvo-show-hide-description-container';
				remove_class = 'tvd-s3';
				add_class = 'tvd-s5';
				break;
		}

		if ( operation > 0 ) {
			jQuery( add_space_elemelem ).removeClass( remove_class ).addClass( add_class )
		} else {
			jQuery( add_space_elemelem ).removeClass( add_class ).addClass( remove_class )
		}
	};

})( jQuery );