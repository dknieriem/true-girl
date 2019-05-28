var ThriveOvation = ThriveOvation || {};
ThriveOvation.views = ThriveOvation.views || {};
ThriveOvation.objects = ThriveOvation.objects || {};

(function ( $ ) {
	$( function () {

		$( document ).keypress( function ( ev ) {
			if ( ev.which == 13 || ev.keyCode == 13 ) {
				if ( ! jQuery( 'a' ).is( ':focus' ) ) {
					if ( jQuery( '#tvo-delete-testimonial-modal' ).is( ':visible' ) ) {
						jQuery( '.tvo-detele-testimonial-action' ).focus().click();
					}
					if ( jQuery( '#tvo-delete-multiple-testimonials-modal' ).is( ':visible' ) ) {
						jQuery( '.tvo-detele-multiple-testimonials-action' ).focus().click();
					}
				}
			}
		} );

		ThriveOvation.views.Base = Backbone.View.extend( {
			/**
			 * instantiate and open a new modal which has the view constructor assigned and send params further along
			 *
			 * @param ViewConstructor View constructor
			 * @param params
			 */
			modal: function ( ViewConstructor, params ) {
				return TVE_Dash.modal( ViewConstructor, params );
			}
		} );

		ThriveOvation.views.Breadcrumbs = ThriveOvation.views.Base.extend( {
			render: function ( items ) {
				var self = this;
				this.$el.empty();

				_.each( items, function ( item ) {
					var v = new ThriveOvation.views.BreadcrumbsItem();
					self.$el.append( v.render( item ).el );
				} );

				return this;
			}
		} );

		ThriveOvation.views.BreadcrumbsItem = ThriveOvation.views.Base.extend( {
			template: TVE_Dash.tpl( 'dashboard/breadcrumb-item' ),
			className: 'tvd-breadcrumb',
			tagName: 'li',
			render: function ( item ) {
				this.$el.html( this.template( {
					url: item.url,
					title: item.title
				} ) );
				return this;
			}
		} );


		/**
		 * Social Media Import
		 */
		ThriveOvation.views.SocialMediaImport = ThriveOvation.views.Base.extend( {
			template: TVE_Dash.tpl( 'social-media/social-media-import' ),
			events: {
				'click .tvo-import': 'importTestimonial',
				'click #tvo-upload-testimonial-image': 'openUploadImage',
				'keydown .tvo-url': 'captureKeyImport',
				'keydown .tvo-social-field': 'captureKeySave',
				'click .tvo-save-testimonial': 'saveTestimonial'
			},
			render: function () {
				var tpl = this.template;

				if ( ! ThriveOvation.social_connections.facebook && ! ThriveOvation.social_connections.twitter ) {
					tpl = TVE_Dash.tpl( 'social-media/no-social-media' );
				}

				this.$el.html( tpl() );
				this.renderTestimonialEditor();

				return this;
			},
			populateFieldWithValues: function ( obj ) {

				this.$el.find( '.tvo-testimonial-area' ).removeClass( 'tvd-hide' );
				this.$el.find( '#tvo-author-name' ).val( obj.name );
				this.$el.find( '#tvo-author-email' ).val( obj.email );
				this.$el.find( '#tvo-author-website' ).val( obj.website_url );
				this.$el.find( '.tvo-testimonial-author-image img' ).attr( 'src', obj.profile_image_url );
				TVE_Dash.materialize( this.$el );
				if ( ! ThriveOvation.util.getTestimonialContent( this.$el ) ) {
					this.renderTestimonialEditor();
				}

				if ( ThriveOvation.util.hasTinymce() ) {
					tinyMCE.get( 'tvo-testimonial-content-tinymce' ).setContent( obj.testimonial );
				} else {
					this.$el.find( '#tvo-testimonial-content-tinymce' ).val( obj.testimonial );
				}

				if ( obj.source ) {
					var email_label = this.$el.find( '.tvo-author-email label' );
					if ( obj.source == 'twitter' ) {
						email_label.attr( 'data-error', email_label.attr( 'data-error-twitter' ) );
						email_label.html( email_label.attr( 'data-content-twitter' ) );
					} else {
						if ( obj.source == 'facebook' ) {
							email_label.attr( 'data-error', email_label.attr( 'data-error-facebook' ) );
							email_label.html( email_label.attr( 'data-content-facebook' ) );
						}
					}

				}
				this.renderSelectTags();
			},
			renderTestimonialEditor: function () {
				var mce_reinit = ThriveOvation.util.build_mce_init( {
					mce: window.tinyMCEPreInit.mceInit['tvo-tinymce-tpl'],
					qt: window.tinyMCEPreInit.qtInit['tvo-tinymce-tpl']
				}, 'tvo-testimonial-content-tinymce' );
				if ( mce_reinit ) {
					tinyMCEPreInit.mceInit = $.extend( tinyMCEPreInit.mceInit, mce_reinit.mce_init );
					tinyMCEPreInit.mceInit['tvo-testimonial-content-tinymce'].setup = function ( editor ) {
						editor.on( 'change', function () {
							editor.save();
						} );
					};
					tinyMCE.init( tinyMCEPreInit.mceInit['tvo-testimonial-content-tinymce'] );
					window.wpActiveEditor = 'tvo-testimonial-content-tinymce';
				}
			},
			renderSelectTags: function () {
				var select = this.$el.find( '#tvo-social-media-new-tag-modal' ),
					self = this;
				if ( select.data( 'select2' ) ) {
					select.select2( 'destroy' );
					select.val( null );
					select.unbind( 'select2:select' );
				}

				select.select2( {
					tags: true,
					multiple: true,
					data: ThriveOvation.availableTags,
					placeholder: ThriveOvation.translations.tags_select2_placeholder,
				} ).on( "select2:select", function ( e ) {
					ThriveOvation.util.addNewTagInTheSystem( e.params.data, self );
				} ).on( "select2:unselect", function ( evt ) {
					if ( ! evt.params.originalEvent ) {
						return;
					}
					evt.params.originalEvent.stopPropagation();
				} );
			},
			openUploadImage: function () {
				if ( wp_file_frame ) {
					wp_file_frame.open();
					return;
				}

				var wp_file_frame = wp.media( {
						title: ThriveOvation.translations.choose_testimonial_image,
						button: {
							text: ThriveOvation.translations.choose_testimonial_image_button
						},
						library: {
							type: 'image'
						},
						multiple: false,
						frame: 'select'
					} ),
					self = this;
				wp_file_frame.on( 'select', function () {
					var attachment = wp_file_frame.state().get( 'selection' ).first().toJSON();
					self.$el.find( '.tvo-testimonial-author-image img' ).attr( 'src', attachment.url );
				} );
				wp_file_frame.open();
			},
			captureKeyImport: function ( ev ) {
				if ( ev.which == 13 || ev.keyCode == 13 ) {
					this.$el.find( '.tvo-import' ).click();
				}
			},
			captureKeySave: function ( ev ) {
				if ( ev.which == 13 || ev.keyCode == 13 ) {
					this.$el.find( '.tvo-save-testimonial' ).click();
				}
			},
			importTestimonial: function ( ev ) {

				var isValid = ThriveOvation.util.validateURL( this.$el.find( '.tvo-url' ) ),
					social_media_url = this.$el.find( '.tvo-url' ).val(),
					elem = jQuery( ev.currentTarget ),
					self = this;

				if ( isValid ) {

					if ( elem.hasClass( 'tvd-disabled' ) ) {
						return;
					} else {
						ThriveOvation.util.setLoading( elem );
					}

					$.ajax( {
						headers: {
							'X-WP-Nonce': ThriveOvation.nonce
						},
						cache: false,
						url: ThriveOvation.routes.socialmedia + '/import_testimonial',
						type: 'POST',
						data: {'social_media_url': social_media_url}
					} ).done( function ( response ) {

						switch ( response.code ) {
							case 0: // Error
								TVE_Dash.err( response.message );
								break;
							case 1: // Success
								TVE_Dash.success( response.message );
								self.populateFieldWithValues( response.result );
								break;
							default:
								break;
						}
					} ).error( function () {
						TVE_Dash.err( ThriveOvation.translations.invalid_social_request );
					} ).always( function () {
						ThriveOvation.util.removeLoading( elem );
						TVE_Dash.hideLoader();
					} );
				} else {
					TVE_Dash.err( ThriveOvation.translations.invalid_url );
				}

			},
			validateModel: function () {
				var valid = true;
				if ( this.$el.find( '#tvo-author-website' ).val() && ! ThriveOvation.util.validateURL( this.$el.find( '#tvo-author-website' ) ) ) {
					valid = false;
				}

				if ( ! ThriveOvation.util.validateInput( this.$el.find( '#tvo-author-name' ), ThriveOvation.translations.isRequired ) ) {
					valid = false;
				}

				if ( ! ThriveOvation.util.validateInput( ThriveOvation.util.getTestimonialContent( this.$el ), ThriveOvation.translations.testimonial_content_missing, true ) ) {
					valid = false;
					this.$el.find( '.tvo-testimonial-content' ).addClass( 'tvo-tiny-mce-error' );
				} else {
					this.$el.find( '.tvo-testimonial-content' ).removeClass( 'tvo-tiny-mce-error' );
				}
				return valid;
			},
			clearAreaAfterSave: function () {
				this.$el.find( '.tvo-url' ).val( '' );
				this.$el.find( '.tvo-testimonial-area' ).addClass( 'tvd-hide' );
			},
			saveTestimonial: function ( ev ) {
				var elem = jQuery( ev.currentTarget ),
					testimonial = new ThriveOvation.models.Testimonial(),
					testimonialObj = {},
					self = this;

				if ( this.validateModel() ) {

					if ( elem.hasClass( 'tvd-disabled' ) ) {
						return;
					} else {
						ThriveOvation.util.setLoading( elem );
					}

					testimonialObj = {
						'title': this.$el.find( '#tvo-title' ).val(),
						'name': this.$el.find( '#tvo-author-name' ).val(),
						'email': this.$el.find( '#tvo-author-email' ).val(),
						'role': this.$el.find( '#tvo-author-role' ).val(),
						'website_url': this.$el.find( '#tvo-author-website' ).val(),
						'content': ThriveOvation.util.getTestimonialContent( this.$el ),
						'tags': this.$el.find( '#tvo-social-media-new-tag-modal' ).val(),
						'source': ThriveOvation.const.source.social_media,
						'picture_url': this.$el.find( '.tvo-testimonial-author-image img' ).prop( 'src' ),
						'comment_url': this.$el.find( '.tvo-url' ).val()
					};

					testimonial.save( testimonialObj, {
						success: function ( model, response ) {
							TVE_Dash.success( ThriveOvation.translations.testimonial_saved_success_toast );
							ThriveOvation.util.removeLoading( elem );
							self.clearAreaAfterSave();
						},
						error: function ( model, response ) {
							TVE_Dash.err( ThriveOvation.translations.testimonial_saved_error_toast );
							ThriveOvation.util.removeLoading( elem );
						}
					} );
				}
			}
		} );

		/**
		 * Dashboard view
		 */
		ThriveOvation.views.Testimonials = ThriveOvation.views.Base.extend( {
			template: TVE_Dash.tpl( 'testimonials/list' ),
			events: {
				'change .tvo-testimonials-select-all': 'checkUncheckTestimonials',
				'change .tvo-checkbox-testimonial-item': 'showHideCheckboxActions',
				'click .tvo-filter': 'applyFilters',
				'change .tvo-dropdown-filter': 'applyDropDownFilters',
				'change .tvo-filter-content': 'filterContent',
				'click .tvo-detele-testimonial-action': 'deleteTestimonial',
				'click .tvo-delete-multiple-testimonials-modal': 'deleteTestimonialsModal',
				'click .tvo-detele-multiple-testimonials-action': 'deleteMultipleTestimonials',
				'click .tvo-add-new-testimonial': 'newTestimonialModal',
				'click .tvo-show-hide': 'showHideElements',
				'click .tvo-add-tags-to-multiple-testimonials': 'addTagsToMultipleTestimonials',
				'click .tvo-new-dropdown-button': 'showTagDropdown',
				'keyup .tvo-multiple-tag-search': 'searchMultipleTags',
				'change .tvo-add-multiple-tags-checkbox': 'checkMultipleTestimonialsCheckboxForParticularClass',
				'keydown .tvo-delete-testimonial-modal': 'capturekey',
				'keydown .tvo-delete-multiple-testimonials-modal': 'capturekey',
				'click .tvo-testimonial-duplicate': 'duplicate'
			},
			testimonialsPagination: null,
			filter_status: [],
			filter_untagged: 0,
			filter_nopicture: 0,
			initialize: function () {
				var self = this,
					$document = jQuery( document );
				this.render();
				$document.off( 'filterReset' );
				$document.on( 'filterReset', function () {
					self.resetFilters();
				} )
			},
			showHideElements: function ( ev ) {
				var elem = jQuery( ev.currentTarget ),
					id = elem.attr( 'id' );

				if ( ! elem.is( ':checked' ) ) {
					jQuery( '.' + id + '-container' ).addClass( 'tvd-hide' );
					ThriveOvation.util.incrementDecrementShowHideSpace( id, 1 );
				} else {
					jQuery( '.' + id + '-container' ).removeClass( 'tvd-hide' );
					ThriveOvation.util.incrementDecrementShowHideSpace( id, - 1 );
				}
				this.saveFilters();
			},
			capturekey: function ( ev ) {
				if ( ev.which == 13 || ev.keyCode == 13 ) {
					if ( this.$el.find( '#tvo-delete-testimonial-modal' ).is( ':visible' ) ) {
						this.$el.find( '.tvo-detele-testimonial-action' ).focus().click();
					}
					if ( this.$el.find( '#tvo-delete-multiple-testimonials-modal' ).is( ':visible' ) ) {
						this.$el.find( '.tvo-detele-multiple-testimonials-action' ).focus().click();
					}
				}
			},
			showHideMassActions: function () {
				var $all = this.$( '.tvo-checkbox-testimonial-item' );
				$all.filter( ':checkbox:checked' ).length ?
					this.$( '.tvo-testimonials-select-all-actions' ).removeClass( 'tvd-hide' ) :
					this.$( '.tvo-testimonials-select-all-actions' ).addClass( 'tvd-hide' );

				this.$( '.tvo-testimonials-select-all' ).prop( 'checked', ! $all.not( ':checked' ).length );
			},
			resetTheParticularTags: function () {
				this.$el.find( '.tvo-multiple-tag-search' ).val( '' ).trigger( 'change' );
				this.$el.find( '.tvo_multiple_tag_container' ).removeClass( 'tvd-hide' );
				this.$el.find( '.tvo-add-multiple-tags-checkbox' ).removeAttr( 'checked' ); //.removeClass( 'tvo-multiple-tags-particular' )
			},
			resetView: function () {
				this.$el.find( '.tvo-testimonials-select-all-actions' ).addClass( 'tvd-hide' );
				this.$el.find( '.tvo-testimonials-select-all' ).prop( 'checked', false ).trigger( 'change' );
				this.resetTheParticularTags();
			},
			showHideCheckboxActions: function ( e ) { //individual
				var $checkbox = $( e.currentTarget );

				this.adjustTagsCheckboxes();

				if ( $checkbox.is( ':checked' ) ) {
					$checkbox.parents( '.tvo-gray-box' ).addClass( 'tvo-highlight-row' );
				} else {
					$checkbox.parents( '.tvo-gray-box' ).removeClass( 'tvo-highlight-row' );
				}
				this.showHideMassActions();
			},
			checkUncheckTestimonials: function ( ev ) {
				var elem = jQuery( ev.currentTarget );
				this.$( '.tvo-checkbox-testimonial-item' ).prop( 'checked', $( elem ).is( ':checked' ) ).trigger( 'change' );
			},
			adjustTagsCheckboxes: function () {
				var uniqueTags = [];
				this.resetTheParticularTags();
				jQuery( '.tvo-checkbox-testimonial-item:checked' ).each( function () {
					var id = jQuery( this ).attr( 'data-id' ),
						model = ThriveOvation.objects.Testimonials.get( id ),
						tags = model.get( 'tags' );

					for ( var i = 0; i < tags.length; i ++ ) {
						if ( jQuery.inArray( tags[i].id, uniqueTags ) === - 1 ) {
							uniqueTags.push( tags[i].id );
						}
					}
				} );

				this.$el.find( '.tvo-add-multiple-tags-checkbox' ).each( function () {
					var tag_id = jQuery( this ).attr( 'data-id' ),
						self = this;
					jQuery( self ).removeClass( 'tvo-multiple-tags-particular' );
					if ( jQuery.inArray( parseInt( tag_id ), uniqueTags ) > - 1 ) {
						jQuery( self ).addClass( 'tvo-multiple-tags-particular' );
					}
				} );
			},
			searchMultipleTags: function ( ev ) {
				var elem = jQuery( ev.currentTarget ),
					text = jQuery( elem ).val().toLowerCase();

				this.$el.find( '.tvo_multiple_tag_container' ).each( function () {
					var label = jQuery( this ).attr( 'data-label' );

					if ( label.indexOf( text ) === - 1 ) {
						jQuery( this ).addClass( 'tvd-hide' );
					} else {
						jQuery( this ).removeClass( 'tvd-hide' );
					}
				} );

				var lengthVisibleTags = this.$el.find( '.tvo_multiple_tag_container' ).not( '.tvd-hide' ).length;
				if ( lengthVisibleTags == 0 ) {
					this.$el.find( '.tvo-no-tags-to-display' ).removeClass( 'tvd-hide' );
					this.$el.find( '.tvo-add-tags-to-multiple-testimonials' ).attr( 'disabled', 'disabled' );
				} else {
					this.$el.find( '.tvo-no-tags-to-display' ).addClass( 'tvd-hide' );
					this.$el.find( '.tvo-add-tags-to-multiple-testimonials' ).removeAttr( 'disabled' );
				}
			},
			filterContent: function ( ev ) {
				var elem = jQuery( ev.currentTarget );
				if ( jQuery( elem ).val() == 'summary' ) {
					jQuery( '.tvo-testimonial-content-full' ).addClass( 'tvd-hide' );
				} else {
					jQuery( '.tvo-testimonial-content-summary' ).addClass( 'tvd-hide' );
				}
				jQuery( '.tvo-testimonial-content-' + jQuery( elem ).val() ).removeClass( 'tvd-hide' );
				this.saveFilters();
			},
			saveFilters: function () {
				var filtersModel = new ThriveOvation.models.Filters( {
					'show_hide_tags': Number( this.$el.find( '#tvo-show-hide-tags' )[0].checked ),
					'show_hide_type': Number( this.$el.find( '#tvo-show-hide-type' )[0].checked ),
					'show_hide_status': Number( this.$el.find( '#tvo-show-hide-status' )[0].checked ),
					'testimonial_content': this.$el.find( '.tvo-filter-content' ).val()
				} );

				filtersModel.save( null, {
					success: function ( model, response ) {

					},
					error: function ( model, response ) {

					}
				} );
			},
			deleteTestimonial: function ( ev ) {
				var elem = jQuery( ev.currentTarget ),
					id = jQuery( elem ).attr( 'data-id' ),
					model = ThriveOvation.objects.Testimonials.get( id ),
					self = this;
				model.destroy( {
					success: (function ( model, response ) {
						jQuery( '.testimonial-row-' + response ).slideUp( 'normal', function () {
							jQuery( this ).remove();
						} );
						ThriveOvation.util.decrementIncrementListCounters( model, - 1 );
						self.resetView();
						self.testimonialsPagination.changePage();
						TVE_Dash.success( ThriveOvation.translations.testimonial_deleted_success_toast );
					}),
					error: (function ( model, response ) {
						TVE_Dash.err( ThriveOvation.translations.testimonial_deleted_fail_toast );
					})
				} );
			},
			duplicate: function ( ev ) {
				var elem = jQuery( ev.currentTarget ),
					id = jQuery( elem ).attr( 'data-id' ),
					model = ThriveOvation.objects.Testimonials.get( id ),
					self = this,
					model_object = model;

				model_object = new ThriveOvation.models.copyTestimonial(
					model.toJSON()
				);

				model_object.save().done( function ( response ) {
					TVE_Dash.success( response.message );
					var model = new ThriveOvation.models.Testimonial( response ),
						view = new ThriveOvation.views.TestimonialsItem( {
							model: model
						} ),
						el = view.render().$el;
					ThriveOvation.util.decrementIncrementListCounters( model, 1 );
					ThriveOvation.objects.Testimonials.push( model );
					ThriveOvation.objects.TestimonialsList.$el.prepend( el );


					            //!*Change page after a new testimonial is created so the counters of the pagination can update*!/
					            var testimonialsPagination = new ThriveOvation.views.TestimonialPagination( {
						            collection: ThriveOvation.objects.Testimonials,
						            view: ThriveOvation.objects.TestimonialsList,
						            el: jQuery( '.tvo-top-pagination' ),
						            type: 'static'
					            } );
					            testimonialsPagination.changePage();

					$( '.tvd-material-tooltip' ).hide();
				} ).error( function ( response ) {
					response = JSON.parse( response.responseText );
					TVE_Dash.err( response.message + ': ' + response.code );
				} );
			},
			newTestimonialModal: function () {
				this.modal( ThriveOvation.views.ModalNewTestimonial, {
					model: new ThriveOvation.models.Testimonial()
				} );
				return this;
			},
			checkMultipleTestimonialsCheckboxForParticularClass: function ( ev ) {
				var elem = jQuery( ev.currentTarget );
				elem.removeClass( 'tvo-multiple-tags-particular' );
			},
			addTagsToMultipleTestimonials: function ( ev ) {
				var testimonialsIdsArr = [],
					tagsIdsArr = [],
					deleteTagsIdsArr = [],
					self = this;

				TVE_Dash.showLoader();

				/* Construct the tags array */
				this.$el.find( '.tvo-add-multiple-tags-checkbox' ).each( function () {
					var $this = jQuery( this );
					if ( $this.attr( 'checked' ) ) {
						var tag_id = $this.attr( 'data-id' );
						tagsIdsArr.push( tag_id );
					} else {
						if ( ! $this.hasClass( 'tvo-multiple-tags-particular' ) ) {
							var id = $this.attr( 'data-id' );
							deleteTagsIdsArr.push( id );
						}
					}
				} );

				/* Construct the testimonials array */
				this.$el.find( '.tvo-checkbox-testimonial-item:checked' ).each( function () {
					var id = jQuery( this ).attr( 'data-id' );
					testimonialsIdsArr.push( id );
				} );

				$.ajax( {
					headers: {
						'X-WP-Nonce': ThriveOvation.nonce
					},
					cache: false,
					url: ThriveOvation.routes.tags + '/add_multiple_tags_to_multiple_testimonials',
					type: 'POST',
					data: {
						'tvo_testimonial_ids': testimonialsIdsArr,
						'tvo_tags_ids': tagsIdsArr,
						'tvo_delete_tags_ids': deleteTagsIdsArr
					}
				} ).done( function ( response ) {

					if ( response.length > 0 ) {
						self.resetView();
						self.fetchTestimonialListData();
					}
					self.$el.find( '.tvo-close-modal-add-tags-to-multiple-testimonials' ).click();

				} ).always( function () {
					setTimeout( function () {
						TVE_Dash.hideLoader();
					}, 500 );
				} );

			},
			deleteTestimonialsModal: function ( ev ) {
				var modal = jQuery( '#tvo-delete-multiple-testimonials-modal' );
				modal.openModal( {} );
			},
			deleteMultipleTestimonials: function ( ev ) {
				var idsArr = [],
					modelArr = [],
					self = this;

				this.$el.find( '.tvo-checkbox-testimonial-item:checked' ).each( function () {
					var id = jQuery( this ).attr( 'data-id' ),
						model = ThriveOvation.objects.Testimonials.get( id );
					idsArr.push( id );
					modelArr.push( model );
				} );

				$.ajax( {
					headers: {
						'X-WP-Nonce': ThriveOvation.nonce
					},
					cache: false,
					url: ThriveOvation.objects.Testimonials.url(),
					type: 'DELETE',
					data: {'tvo_testimonial_elements': idsArr}
				} ).done( function ( response ) {
					for ( var i = 0; i < response.length; i ++ ) {
						jQuery( '.testimonial-row-' + response[i] ).remove();
					}

					jQuery( modelArr ).each( function ( index, entry ) {
						ThriveOvation.util.decrementIncrementListCounters( entry, - 1 );
						ThriveOvation.objects.Testimonials.remove( entry );
					} );
					self.resetView();
					self.testimonialsPagination.changePage();
					TVE_Dash.success( ThriveOvation.translations.multiple_testimonials_deleted_success_toast );
				} ).error( function () {
					TVE_Dash.err( ThriveOvation.translations.multiple_testimonials_deleted_fail_toast );
				} ).always( function () {
					TVE_Dash.hideLoader();
				} );

			},
			applyFilters: function ( ev ) {
				var elem = jQuery( ev.currentTarget ),
					self = this;
				TVE_Dash.showLoader();
				/*Uncheck the select all checkbox*/
				this.$el.find( '.tvo-testimonials-select-all' ).prop( 'checked', false ).trigger( 'change' );
				this.adjustTagsCheckboxes();

				if ( elem.hasClass( 'tvo-filter-status' ) ) {
					self.filter_status = [];
					jQuery( 'input:checkbox.tvo-filter-status' ).each( function () {
						if ( this.checked ) {
							self.filter_status.push( jQuery( this ).val() );
						}
					} );
				} else {
					if ( elem.hasClass( 'tvo-filter-tag' ) ) {
						if ( elem.is( ':checked' ) ) {
							self.filter_untagged = 1;
						} else {
							self.filter_untagged = 0;
						}
					} else {
						if ( elem.hasClass( 'tvo-filter-picture' ) ) {
							if ( elem.is( ':checked' ) ) {
								self.filter_nopicture = 1;
							} else {
								self.filter_nopicture = 0;
							}
						}
					}
				}

				this.testimonialsPagination.changePage( null, {
					status: self.filter_status,
					untagged: self.filter_untagged,
					nopicture: self.filter_nopicture
				} );
			},
			applyDropDownFilters: function ( ev ) {
				var elem = jQuery( ev.currentTarget ),
					self = this,
					filterClass = elem.attr( 'class' ).split( ' ' ).shift();

				TVE_Dash.showLoader();

				/*Uncheck the select all checkbox*/
				this.$el.find( '.tvo-testimonials-select-all' ).prop( 'checked', false ).trigger( 'change' );
				this.adjustTagsCheckboxes();


				switch ( filterClass ) {
					case "tvo-image-filter":
						self.image_filter = elem.val();
						if ( elem.val() == 'any' ) {
							elem.val( 'select-title' );
						}
						break;
					case "tvo-title-filter":
						self.title_filter = elem.val();
						if ( elem.val() == 'any' ) {
							elem.val( 'select-image' );
						}
						break;
					default:
						break;
				}

				this.testimonialsPagination.changePage( null, {
					titleFilter: self.title_filter,
					imageFilter: self.image_filter,
				} );

			},
			resetFilters: function () {
				var filterPicture = this.$el.find( '.tvo-image-filter' ),
					filterTitle = this.$el.find( '.tvo-title-filter' ),
					filterTags = this.$el.find( '#tvo-dashboard-filter-tags' );

				filterTags.select2( "val", "" );
				this.testimonialsPagination.changePage( null, {
					tagsFilter: ""
				} );
				filterTitle.val( 'select-title' ).trigger( 'change' );
				filterPicture.val( 'select-image' ).trigger( 'change' );
			},
			showTagDropdown: function ( e ) {
				var self = this.$el,
					selfAbs = this,
					target = jQuery( e.target ),
					$dropdown = target.parents( '.tvo-new-dropdown' ),
					$dropdownContent = $dropdown.find( '.tvo-new-dropdown-content' );
				$dropdown.toggleClass( 'tvd-active' );
				$dropdownContent.slideToggle();
				e.stopPropagation();

				self.parents( 'body' ).off( 'click.clearDropdown' ).on( 'click.clearDropdown', function ( evt ) {
					if ( ! $( evt.target ).parents().is( $dropdown ) ) {
						var $allDropdowns = self.find( '.tvo-new-dropdown' ),
							$allDropdownsContent = self.find( '.tvo-new-dropdown-content' );
						if ( $allDropdowns.hasClass( 'tvd-active' ) ) {
							$allDropdownsContent.slideUp();
							$allDropdowns.removeClass( 'tvd-active' );
							selfAbs.resetTheParticularTags();
						}
					}
				} );
			},
			render: function () {
				this.$el.html( this.template() );

				TVE_Dash.materialize( this.$el );

				ThriveOvation.objects.Testimonials = new ThriveOvation.collections.Testimonials();
				ThriveOvation.objects.TestimonialsList = new ThriveOvation.views.TestimonialsList( {
					collection: ThriveOvation.objects.Testimonials,
					el: this.$el.find( '#tvo-testimonials-list' )
				} );

				this.testimonialsPagination = new ThriveOvation.views.TestimonialPagination( {
					collection: ThriveOvation.objects.Testimonials,
					view: ThriveOvation.objects.TestimonialsList,
					el: this.$el.find( '.tvo-top-pagination' ),
					type: 'static'
				} );

				this.renderFilterTags();
				this.fetchTestimonialListData();


				setTimeout( function () {
					jQuery( '.tvd-dropdown-button' ).tvd_dropdown();
				}, 500 );
				TVE_Dash.hideLoader();

				return this;
			},
			fetchTestimonialListData: function () {
				var self = this;
				ThriveOvation.objects.Testimonials.fetch( {
						success: function () {
							self.testimonialsPagination.changePage();
						}
					}
				);
			},
			renderFilterTags: function () {
				var self = this,
					select = this.$el.find( '#tvo-dashboard-filter-tags' );

				if ( select.data( 'select2' ) ) {
					select.select2( 'destroy' );
				}

				select.select2( {
					tags: true,
					multiple: true,
					data: ThriveOvation.availableTags,
					placeholder: ThriveOvation.translations.select_filter_tags,
				} ).on( "select2:select", function ( e ) {
					self.testimonialsPagination.changePage( null, {
						tagsFilter: select.val()
					} );
				} ).on( "select2:unselect", function ( evt ) {
					if ( ! evt.params.originalEvent ) {
						return;
					}
					self.testimonialsPagination.changePage( null, {
						tagsFilter: select.val()
					} );
					evt.params.originalEvent.stopPropagation();
				} );
			}


		} );

		/**
		 * Testimonial List
		 */

		ThriveOvation.views.TestimonialsList = ThriveOvation.views.Base.extend( {
			events: {},
			initialize: function () {
				/*use this with ajax*/
				//this.listenTo( this.collection, 'sync', this.render );
			},
			renderOne: function ( item ) {
				var view = new ThriveOvation.views.TestimonialsItem( {
						model: item
					} ),
					el = view.render().$el;
				this.$el.append( el );
			},
			render: function ( collection ) {
				this.$el.empty();
				var c = this.collection;
				if ( typeof collection !== 'undefined' ) {
					c = new ThriveOvation.collections.Testimonials( collection );
				}

				c.each( this.renderOne, this );
				return this;
			}
		} );

		/**
		 * Testimonial Item
		 */
		ThriveOvation.views.TestimonialsItem = ThriveOvation.views.Base.extend( {
			template: TVE_Dash.tpl( 'testimonials/item' ),
			events: {
				'change .tvo-testimonial-status-change': 'updateTestimonialStatus',
				'click .tvo-delete-testimonial-modal': 'deleteTestimonialModal',
				'click .tvo-remove-testimonial-tag': 'deleteTestimonialTag',
				'click .tvo-add-new-tag': 'addNewTag',
				'click .tvo-add-new-tag-action': 'addNewTagAction'
			},
			attributes: function () {
				return {
					class: 'tvd-row tvo-gray-box testimonial-row-' + this.model.get( 'id' )
				};
			},
			initialize: function ( options ) {
			},
			addNewTag: function ( ev ) {
				this.$el.find( '.tvo-testimonial-' + this.model.get( 'id' ) + '-tag-display-area' ).addClass( 'tvd-hide' );
				this.$el.find( '.tvo-testimonial-' + this.model.get( 'id' ) + '-tag-add-area' ).removeClass( 'tvd-hide' );
				this.$el.find( '#tvo-testimonial-' + this.model.get( 'id' ) + '-new-tag' ).select2( 'open' );
			},
			addNewTagAction: function ( ev ) {
				var elem = jQuery( ev.currentTarget ),
					data = this.$el.find( '#tvo-testimonial-' + this.model.get( 'id' ) + '-new-tag' ).select2( 'data' ),
					self = this,
					prev_nr_of_tags = this.model.get( 'tags' ).length;

				if ( elem.hasClass( 'tvd-disabled' ) ) {
					return;
				} else {
					ThriveOvation.util.setLoading( elem );
				}

				this.model.set( 'tags', this.$el.find( '#tvo-testimonial-' + this.model.get( 'id' ) + '-new-tag' ).val() );

				this.model.save( null, {
					success: function ( model, response ) {
						var HTML = '';
						if ( data.length > 0 ) {
							jQuery.each( data, function ( index, value ) {
								var object = {
									'id': value.id,
									'text': value.text,
									'model_id': model.get( 'id' )
								};
								HTML += TVE_Dash.tpl( 'testimonials/tag-item', {object: object} );
							} );
						} else {
							HTML += '<i class="tvd-small-text tvo-gray-text">' + ThriveOvation.translations.untagged_testimonial + '</i>'
						}
						self.$el.find( '.tvo-testimonial-' + model.get( 'id' ) + '-tag-display-area > div.tvd-clearfix' ).html( HTML );
						self.$el.find( '.tvo-testimonial-' + model.get( 'id' ) + '-tag-display-area' ).removeClass( 'tvd-hide' );
						self.$el.find( '.tvo-testimonial-' + model.get( 'id' ) + '-tag-add-area' ).addClass( 'tvd-hide' );

						if ( prev_nr_of_tags === 0 && model.get( 'tags' ).length > 0 ) {
							ThriveOvation.util.incrementDecrementTagCounters( - 1 );
						} else {
							if ( prev_nr_of_tags > 0 && model.get( 'tags' ).length === 0 ) {
								ThriveOvation.util.incrementDecrementTagCounters( 1 );
							}
						}

						TVE_Dash.success( ThriveOvation.translations.testimonial_tag_added_success_toast );
					},
					error: function ( model, response ) {
						TVE_Dash.err( ThriveOvation.translations.testimonial_tag_added_fail_toast );
					},
					complete: function () {
						ThriveOvation.util.removeLoading( elem );
					}
				} );
			},
			renderSelectTags: function () {
				var self = this,
					select = this.$el.find( '#tvo-testimonial-' + this.model.get( 'id' ) + '-new-tag' );
				if ( select.data( 'select2' ) ) {
					select.select2( 'destroy' );
				}
				if ( self.$el.find( '#tvo-testimonial-' + self.model.get( 'id' ) + '-new-tag' ).val() == null ) {
					self.$el.find( '.tvo-add-new-tag-action' ).html( ThriveOvation.translations.update_tags );
				}
				select.select2( {
					tags: true,
					multiple: true,
					placeholder: ThriveOvation.translations.tags_select2_placeholder
				} ).on( 'select2:select', function ( e ) {
					self.$el.find( '.tvo-add-new-tag-action' ).html( ThriveOvation.translations.save );
					ThriveOvation.util.addNewTagInTheSystem( e.params.data, self );
				} ).on( "select2:unselect", function ( evt ) {
					if ( ! evt.params.originalEvent ) {
						return;
					}
					if ( self.$el.find( '#tvo-testimonial-' + self.model.get( 'id' ) + '-new-tag' ).val() == null ) {
						self.$el.find( '.tvo-add-new-tag-action' ).html( ThriveOvation.translations.update_tags );
					}
					evt.params.originalEvent.stopPropagation();
				} ).on( "select2:open", function ( evt ) {
					var search_input = self.$el.find( '.tvo-testimonial-' + self.model.get( 'id' ) + '-tag-add-area' ).find( '.select2-search__field' ),
						select = self.$el.find( '#tvo-testimonial-' + self.model.get( 'id' ) + '-new-tag' );
					search_input.unbind();
					search_input.keyup( function ( event ) {
						if ( event.which === 13 && search_input.val() == '' ) {
							select.select2( 'close' );
							self.$el.find( '.tvo-testimonial-' + self.model.get( 'id' ) + '-tag-add-area' ).find( '.tvo-add-new-tag-action' ).click();

						}
					} );
				} );
			},
			renderTags: function ( tags ) {
				var self = this;
				self.$el.find( "#tvo-testimonial-" + self.model.get( 'id' ) + '-new-tag' ).empty();
				ThriveOvation.availableTags.forEach( function ( entry ) {
					var selected = '';
					if ( ThriveOvation.util.containsObject( entry, tags ) ) {
						selected = 'selected="selected"';
					}
					var html = '<option value="' + entry.id + '" ' + selected + '>' + entry.text + '</option>';
					self.$el.find( "#tvo-testimonial-" + self.model.get( 'id' ) + '-new-tag' ).append( html );
				} );
				self.renderSelectTags();
			},
			deleteTestimonialModal: function ( ev ) {
				var modal = jQuery( '#tvo-delete-testimonial-modal' ),
					id = ev.currentTarget.attributes['data-id'].value,
					title = ev.currentTarget.attributes['data-title'].value;

				modal.find( '.tvo-delete-content-title' ).html( title );

				modal.openModal( {
					ready: function () {
						modal.find( '.tvo-detele-testimonial-action' ).attr( 'data-id', id );
					}
				} );
			},
			updateTestimonialStatus: function ( ev ) {
				var elem = jQuery( ev.currentTarget ),
					value = jQuery( elem ).val(),
					initialValue = jQuery( elem ).attr( 'data-value' );
				if ( value != initialValue ) {
					this.model.set( {'status': value} );
					var postMetaModel = new ThriveOvation.models.PostMeta( {
						'key': this.model.get( 'id' ),
						'meta_key': ThriveOvation.const.meta_key.status,
						'meta_value': value
					} );

					postMetaModel.save( null, {
						success: function ( model, response ) {
							jQuery( elem ).attr( 'data-value', value ).removeClass( 'tvo-testimonial-status-' + initialValue ).addClass( 'tvo-testimonial-status-' + value );
							ThriveOvation.util.incrementDecrementStatusCounters( initialValue, value );
							TVE_Dash.success( ThriveOvation.translations.status_changed_success_toast );
						},
						error: function ( model, response ) {
							TVE_Dash.err( ThriveOvation.translations.status_changed_fail_toast );
						}
					} );
				}
			},
			deleteTestimonialTag: function ( ev ) {
				var elem = jQuery( ev.currentTarget ),
					testimonialId = elem.attr( 'data-testimonial-id' ),
					tagId = elem.attr( 'data-tag-id' ),
					testimonialTags = this.model.get( 'tags' ),
					tagsArr = [],
					templateElem = this.$el,
					self = this;

				for ( var i = 0; i < testimonialTags.length; i ++ ) {
					if ( testimonialTags[i].id == tagId ) {
						tagsArr['name'] = testimonialTags[i].text;
						tagsArr['post_id'] = testimonialTags[i].post_id;
						tagsArr['term_id'] = testimonialTags[i].id;
						break;
					}
				}

				var tagModel = new ThriveOvation.models.Tag( tagsArr );

				tagModel.destroy( {
					data: {
						'post_id': tagsArr['post_id'],
						'id': tagsArr['term_id']
					},
					processData: true,
					success: (function ( model, response ) {
						templateElem.find( '.tvo-testimonial-custom-tag-' + testimonialId + '-' + tagId ).remove();
						if ( templateElem.find( '.tvo-testimonial-custom-tag' ).length === 0 ) {
							var HTML = '<i class="tvd-small-text tvo-gray-text">' + ThriveOvation.translations.untagged_testimonial + '</i>';
							templateElem.find( 'div.tvo-testimonial-' + testimonialId + '-tag-display-area > div.tvd-clearfix' ).html( HTML );
							ThriveOvation.util.incrementDecrementTagCounters( 1 )
						}
						TVE_Dash.success( ThriveOvation.translations.testimonial_tag_removed_success_toast );
						templateElem.find( 'div.tvo-testimonial-' + testimonialId + '-tag-display-area > div.tvd-clearfix' ).html( HTML );
						self.deleteTagFromSelect( tagsArr['term_id'] );
					}),
					error: (function ( model, response ) {
						TVE_Dash.err( ThriveOvation.translations.testimonial_tag_removed_fail_toast );
					})
				} );
			},
			deleteTagFromSelect: function ( id_to_remove ) {

				var tags = this.model.get( 'tags' );
				var newtags = $.grep( tags, function ( value ) {
					return value.id != id_to_remove;
				} );
				this.model.set( 'tags', newtags );
				this.renderTags( newtags );
			},
			render: function () {
				this.$el.html( this.template( {testimonial: this.model} ) );
				TVE_Dash.materialize( this.$el );

				var tags = this.model.get( 'tags' );
				this.renderTags( tags );

				return this;
			}
		} );

		/**
		 * Pagination View
		 */

		ThriveOvation.views.TestimonialPagination = ThriveOvation.views.Base.extend( {
			template: TVE_Dash.tpl( 'pagination/post-view' ),
			events: {
				'click a.page': 'setLoaderBeforeChangingThePage',
				'change .tvo-items-per-page': 'changeItemPerPage'
			},
			currentPage: 1,
			pageCount: 1,
			itemsPerPage: 10,
			total_items: 0,
			collection: null,
			params: null,
			type: '',
			view: null,
			initialize: function ( options ) {
				this.collection = options.collection;
				this.view = options.view;
				this.type = options.type;
			},
			changeItemPerPage: function ( event ) {
				var self = this;
				TVE_Dash.showLoader();
				this.itemsPerPage = jQuery( event.target ).val();
				/*This is for loader spinner to appear*/
				setTimeout( function () {
					self.changePage( null, {page: 1} );
				}, 10 );
			},
			setLoaderBeforeChangingThePage: function ( event, args ) {
				/*This is for loader spinner to appear*/
				var self = this;
				TVE_Dash.showLoader();
				setTimeout( function () {
					self.changePage( event, args );
				}, 10 );
			},
			changePage: function ( event, args ) {
				TVE_Dash.showLoader();
				var self = this,
					data = {
						itemsPerPage: this.itemsPerPage
					},
					counter = {
						readyForDisplay: 0,
						awaitingReview: 0,
						awaitingApproval: 0,
						rejected: 0,
						untagged: 0,
						noPicture: 0
					};

				/* Set the current page of the pagination. This can be changed by clicking on a page or by just calling this method with params */
				if ( event && typeof event.currentTarget !== 'undefined' ) {
					data.page = jQuery( event.currentTarget ).attr( 'value' );
				} else if ( args && typeof args.page !== 'undefined' ) {
					data.page = parseInt( args.page );
				} else {
					data.page = this.currentPage;
				}

				/* just to make sure */
				if ( data.page < 1 ) {
					data.page = 1;
				}

				/* Parse args sent to pagination */
				if ( typeof args !== 'undefined' ) {

					if ( typeof args.search_by !== 'undefined' ) {
						this.search_by = args.search_by;
					}

					if ( typeof args.status !== 'undefined' ) {
						this.status = args.status;
					}

					if ( typeof args.untagged !== 'undefined' ) {
						this.untagged = args.untagged;
					}

					if ( typeof args.nopicture !== 'undefined' ) {
						this.nopicture = args.nopicture;
					}

					if ( typeof args.titleFilter !== 'undefined' ) {
						this.titleFilter = args.titleFilter;
					}

					if ( typeof args.imageFilter !== 'undefined' ) {
						this.imageFilter = args.imageFilter;
					}

					if ( typeof args.tagsFilter !== 'undefined' ) {
						this.tagsFilter = args.tagsFilter;
					}
				}

				/* In case we've saved this before */
				data.search_by = this.search_by ? this.search_by : '';
				data.exclude = this.exclude ? this.exclude : [];

				data.status = this.status ? this.status : [];
				data.untagged = this.untagged ? this.untagged : 0;
				data.nopicture = this.nopicture ? this.nopicture : 0;

				data.titleFilter = this.titleFilter ? this.titleFilter : '';
				data.imageFilter = this.imageFilter ? this.imageFilter : '';
				data.tagsFilter = this.tagsFilter ? this.tagsFilter : [];


				/* A dynamic pagination, on search, gets data with an AJAX request */
				if ( this.type == 'dynamic' ) {
					this.collection.fetch( {
						reset: true,
						data: $.param( data ),
						success: function () {

							/* When we're on the last page and there are no elements to display,  */
							if ( self.collection.length == 0 && self.collection.total_count > 0 && self.currentPage != 1 ) {
								self.changePage( null, {page: self.currentPage - 1} );
								return;
							}

							self.updateParams( data.page, self.collection.total_count );
							self.render();
						}
					} );

					/* A static pagination, on search, gets data from within the collection, without any other calls */
				} else if ( this.type == 'static' && typeof this.view != 'undefined' && this.view != null ) {

					/* Prepare params for pagination render */
					this.updateParams( data.page, this.collection.length );

					var currentCollection = this.collection.clone(),
						from = (
							       this.currentPage - 1
						       ) * this.itemsPerPage,
						collectionSlice,
						removeIds = [];

					if ( typeof currentCollection.comparator !== 'undefined' ) {
						currentCollection.sort();
					}

					currentCollection.each( function ( model ) {

						switch ( model.get( 'status' ) ) {
							case ThriveOvation.const.status.ready_for_display:
								counter.readyForDisplay ++;
								break;
							case ThriveOvation.const.status.awaiting_approval:
								counter.awaitingApproval ++;
								break;
							case ThriveOvation.const.status.awaiting_review:
								counter.awaitingReview ++;
								break;
							case ThriveOvation.const.status.rejected:
								counter.rejected ++;
								break;
						}

						/*Status filters*/
						if ( data.status.length > 0 ) {
							if ( jQuery.inArray( model.get( 'status' ), data.status ) === - 1 ) {
								removeIds.push( model );
							}
						}

						if ( model.get( 'tags' ).length > 0 ) {
							if ( data.untagged != 0 ) {
								removeIds.push( model );
							}
						} else {
							counter.untagged ++;
						}

						if ( model.get( 'picture_url' ).indexOf( ThriveOvation.testimonial_image_placeholder ) == - 1 ) {
							if ( data.nopicture != 0 ) {
								removeIds.push( model );
							}
						} else {
							counter.noPicture ++;
						}

						/*** Dropdown filters ***/
						//filter by tags
						if ( data.tagsFilter.length > 0 ) {
							var removeModel = true,
								testimonial_tags = model.get( 'tags' ).map( function ( tag ) {
									return tag.id;
								} );

							for ( var i = 0; i < data.tagsFilter.length; i ++ ) {
								if ( testimonial_tags.indexOf( parseInt( data.tagsFilter[i] ) ) !== - 1 ) {
									removeModel = false;
								}
							}
							if ( removeModel ) {
								removeIds.push( model );
							}
						}

						//filter by title
						if ( data.titleFilter != '' ) {
							var title = data.titleFilter,
								modelTitle = model.get( 'title' ) || '';

							switch ( title ) {
								case 'with-title':
									if ( modelTitle == '' ) {
										removeIds.push( model );
									}
									break;
								case 'without-title':
									if ( modelTitle != '' ) {
										removeIds.push( model );
									}
									break;
								default:
									break;
							}
						}

						//filter by image
						if ( data.imageFilter != '' ) {
							var image = data.imageFilter;

							switch ( image ) {
								case 'with-image':
									if ( model.get( 'has_picture' ) == 0 ) {
										removeIds.push( model );
									}
									break;
								case 'without-image':
									if ( model.get( 'has_picture' ) == 1 ) {
										removeIds.push( model );
									}
									break;
								default:
									break;
							}
						}

					} );

					/* set the counters to labels */
					self.setCountersToLabels( counter );

					for ( var i in removeIds ) {
						currentCollection.remove( removeIds[i] );
					}

					/*Update params one more time after the colection has been modified*/
					this.updateParams( data.page, currentCollection.length );

					collectionSlice = currentCollection.chain().rest( from ).first( this.itemsPerPage ).value();

					/* render sliced view collection */
					this.view.render( collectionSlice );
					if ( collectionSlice.length == 0 ) {
						/* When we're on the last page and there are no elements to display,  */
						if ( self.collection.length > 0 && self.currentPage != 1 ) {
							self.changePage( null, {page: self.currentPage - 1} );
							return;
						}
						jQuery( '#tvo-testimonials-list' ).html( TVE_Dash.tpl( 'pagination/no-results' ) );
					}
					this.checkFieldsDisplay();
					/* render pagination */
					this.render();
				}

				return false;
			},
			setCountersToLabels: function ( counter ) {
				jQuery( '.tvo-ready-for-display-c' ).html( counter.readyForDisplay );
				jQuery( '.tvo-awaiting-approval-c' ).html( counter.awaitingApproval );
				jQuery( '.tvo-awaiting-review-c' ).html( counter.awaitingReview );
				jQuery( '.tvo-rejected-c' ).html( counter.rejected );
				jQuery( '.tvo-untagged-c' ).html( counter.untagged );
				jQuery( '.tvo-no-picture-c' ).html( counter.noPicture );
			},
			checkFieldsDisplay: function () {
				if ( jQuery( '#tvo-show-hide-tags' ).is( ':checked' ) ) {
					jQuery( '.tvo-show-hide-tags-container' ).removeClass( 'tvd-hide' ).trigger( 'change' );
					ThriveOvation.util.incrementDecrementShowHideSpace( 'tvo-show-hide-tags', - 1 );
				} else {
					jQuery( '.tvo-show-hide-tags-container' ).addClass( 'tvd-hide' ).trigger( 'change' );
					ThriveOvation.util.incrementDecrementShowHideSpace( 'tvo-show-hide-tags', 1 );
				}

				if ( jQuery( '#tvo-show-hide-status' ).is( ':checked' ) ) {
					jQuery( '.tvo-show-hide-status-container' ).removeClass( 'tvd-hide' ).trigger( 'change' );
					ThriveOvation.util.incrementDecrementShowHideSpace( 'tvo-show-hide-status', - 1 );
				} else {
					jQuery( '.tvo-show-hide-status-container' ).addClass( 'tvd-hide' ).trigger( 'change' );
					ThriveOvation.util.incrementDecrementShowHideSpace( 'tvo-show-hide-status', 1 );
				}

				if ( jQuery( '#tvo-show-hide-type' ).is( ':checked' ) ) {
					jQuery( '.tvo-show-hide-type-container' ).removeClass( 'tvd-hide' ).trigger( 'change' );
				} else {
					jQuery( '.tvo-show-hide-type-container' ).addClass( 'tvd-hide' ).trigger( 'change' );
				}

				if ( jQuery( '.tvo-filter-content' ).val() == 'full' ) {
					jQuery( '.tvo-testimonial-content-summary' ).addClass( 'tvd-hide' );
					jQuery( '.tvo-testimonial-content-full' ).removeClass( 'tvd-hide' );
				} else {
					jQuery( '.tvo-testimonial-content-summary' ).removeClass( 'tvd-hide' );
					jQuery( '.tvo-testimonial-content-full' ).addClass( 'tvd-hide' );
				}

			},
			updateParams: function ( page, total ) {
				this.currentPage = page;
				this.total_items = total;
				this.pageCount = Math.ceil( this.total_items / this.itemsPerPage );
			},
			setupParams: function ( page ) {
				this.currentPage = page;
				this.total_items = this.collection.length;
				this.pageCount = Math.ceil( this.total_items / this.itemsPerPage );
			},
			render: function () {
				this.$el.html( this.template( {
					currentPage: parseInt( this.currentPage ),
					pageCount: parseInt( this.pageCount ),
					total_items: parseInt( this.total_items ),
					itemsPerPage: parseInt( this.itemsPerPage )
				} ) );
				TVE_Dash.hideLoader();
				TVE_Dash.materialize( this.$el );
				return this;
			}
		} );

		ThriveOvation.views.ModalNewTestimonial = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'testimonials/testimonial/modal' ),
			type: '',
			events: {
				'click .tvo-save-new-testimonial': 'save',
				'click .tvo-upload-testimonial-image': 'openUploadImage',
				'click #tvo-remove-testimonial-image': 'removeImage',
				'keydown .tvo-testimonial-input': 'enterPress',
			},
			afterRender: function () {
				this.$el.html( this.template( {testimonial: this.model} ) );
				this.renderTestimonialEditor();
				this.$el.addClass( 'tvo-big-modal' );
				return this;
			},
			afterMaterialize: function () {
				this.renderSelectTags();
				return this;
			},
			enterPress: function ( ev ) {
				if ( ev.which == 13 || ev.keyCode == 13 ) {
					this.save();
				}
			},
			renderSelectTags: function () {
				var select = this.$el.find( '#tvo-author-new-tag-modal' ),
					self = this;
				if ( select.data( 'select2' ) ) {
					select.select2( 'destroy' );
				}
				select.select2( {
					tags: true,
					multiple: true,
					data: ThriveOvation.availableTags,
					placeholder: ThriveOvation.translations.tags_select2_placeholder,
				} ).on( "select2:select", function ( e ) {
					ThriveOvation.util.addNewTagInTheSystem( e.params.data, self );
				} ).on( "select2:unselect", function ( evt ) {
					if ( ! evt.params.originalEvent ) {
						return;
					}
					evt.params.originalEvent.stopPropagation();
				} );
			},
			renderTestimonialEditor: function () {
				var mce_reinit = ThriveOvation.util.build_mce_init( {
					mce: window.tinyMCEPreInit.mceInit['tvo-tinymce-tpl'],
					qt: window.tinyMCEPreInit.qtInit['tvo-tinymce-tpl']
				}, 'tvo-testimonial-content-tinymce' );
				if ( mce_reinit ) {
					tinyMCEPreInit.mceInit = $.extend( tinyMCEPreInit.mceInit, mce_reinit.mce_init );
					tinyMCEPreInit.mceInit['tvo-testimonial-content-tinymce'].setup = function ( editor ) {
						editor.on( 'change', function () {
							editor.save();
						} );
					};
					tinyMCE.init( tinyMCEPreInit.mceInit['tvo-testimonial-content-tinymce'] );
					window.wpActiveEditor = 'tvo-testimonial-content-tinymce';
				}
			},
			validateModel: function () {
				var valid = true;
				if ( ! ThriveOvation.util.validateInput( this.$el.find( '#tvo-author-name' ), ThriveOvation.translations.author_name_required, true ) ) {
					valid = false;
				}
				if ( this.$el.find( '#tvo-author-email' ).val() && ! ThriveOvation.util.validateEmail( this.$el.find( '#tvo-author-email' ) ) ) {
					valid = false;
				}
				if ( this.$el.find( '#tvo-author-website' ).val() && ! ThriveOvation.util.validateURL( this.$el.find( '#tvo-author-website' ) ) ) {
					valid = false;
				}
				if ( ! ThriveOvation.util.validateInput( ThriveOvation.util.getTestimonialContent( this.$el ), ThriveOvation.translations.testimonial_content_missing, true ) ) {
					valid = false;
					this.$el.find( '.tvo-testimonial-content' ).addClass( 'tvo-tiny-mce-error' );
				} else {
					this.$el.find( '.tvo-testimonial-content' ).removeClass( 'tvo-tiny-mce-error' );
				}
				return valid;
			},
			save: function () {
				TVE_Dash.showLoader();
				var self = this,
					model_object = {
						'title': this.$el.find( '#tvo-title' ).val(),
						'name': this.$el.find( '#tvo-author-name' ).val(),
						'email': this.$el.find( '#tvo-author-email' ).val(),
						'role': this.$el.find( '#tvo-author-role' ).val(),
						'website_url': this.$el.find( '#tvo-author-website' ).val(),
						'content': ThriveOvation.util.getTestimonialContent( this.$el ),
						'tags': this.$el.find( '#tvo-author-new-tag-modal' ).val(),
						'picture_url': ( this.$el.find( "input[name='tvo-is-placeholder']" ).val() == 1 ) ? "" : this.$el.find( '.tvo-testimonial-author-image img' ).attr( 'src' ),
						'source': ThriveOvation.const.source.plugin
					},
					$elem = this.$el.find( '.tvo-save-new-testimonial' );
				if ( this.validateModel() ) {
					if ( $elem.hasClass( 'tvd-disabled' ) ) {
						return;
					} else {
						ThriveOvation.util.setLoading( $elem );
					}

					this.model.save( model_object ).done( function ( response ) {
						TVE_Dash.success( response.message );
						var model = new ThriveOvation.models.Testimonial( response );
						var view = new ThriveOvation.views.TestimonialsItem( {
								model: model
							} ),
							el = view.render().$el;
						ThriveOvation.util.decrementIncrementListCounters( model, 1 );
						ThriveOvation.objects.Testimonials.push( model );
						ThriveOvation.objects.TestimonialsList.$el.prepend( el );


						/*Change page after a new testimonial is created so the counters of the pagination can update*/
						var testimonialsPagination = new ThriveOvation.views.TestimonialPagination( {
							collection: ThriveOvation.objects.Testimonials,
							view: ThriveOvation.objects.TestimonialsList,
							el: jQuery( '.tvo-top-pagination' ),
							type: 'static'
						} );
						testimonialsPagination.changePage();
						jQuery( "body" ).trigger( 'filterReset' );

						self.close();
					} ).error( function ( response ) {
						response = JSON.parse( response.responseText );
						TVE_Dash.err( response.message + ': ' + response.code );
					} ).always( function () {
						ThriveOvation.util.removeLoading( $elem );
						TVE_Dash.hideLoader();
					} );
				} else {
					TVE_Dash.hideLoader();
				}
				return true;
			},
			openUploadImage: function () {
				if ( wp_file_frame ) {
					wp_file_frame.open();
					return;
				}

				var wp_file_frame = wp.media( {
					title: ThriveOvation.translations.choose_testimonial_image,
					button: {
						text: ThriveOvation.translations.choose_testimonial_image_button
					},
					library: {
						type: 'image'
					},
					multiple: false,
					frame: 'select'
				} );
				var self = this;
				wp_file_frame.on( 'select', function () {
					var attachment = wp_file_frame.state().get( 'selection' ).first().toJSON();


					self.$el.find( "input[name='tvo-is-placeholder']" ).val( 0 );
					self.$el.find( '.tvo-testimonial-author-image img' ).attr( 'src', attachment.url );
					self.$el.find( '#tvo-upload-testimonial-image' ).hide();
					self.$el.find( '.tvo-image-uploaded' ).show();
				} );
				wp_file_frame.open();
			},
			removeImage: function () {
				var default_image = this.$el.find( '#tvo-remove-testimonial-image' ).attr( 'data-default' );
				this.$el.find( '.tvo-testimonial-author-image img' ).attr( 'src', default_image );
				this.$el.find( '#tvo-upload-testimonial-image' ).show();
				this.$el.find( '.tvo-image-uploaded' ).hide();
			},
			beforeClose: function () {
				ThriveOvation.util.clearMCEEditor();
			}
		} );

		/**
		 * Testimonial page view
		 */

		ThriveOvation.views.Testimonial = ThriveOvation.views.Base.extend( {
			template: TVE_Dash.tpl( 'testimonials/testimonial' ),
			events: {
				'click .tvo-update-testimonial': 'updateTestimonial',
				'change #tvo-testimonial-status-change': 'updateTestimonialStatus',
				'click .tvo-upload-testimonial-image': 'openUploadImage',
				'click #tvo-remove-testimonial-image': 'removeImage',
				'click .tvo-send-approval-email': 'openEmailModal',
				'keydown .tvo-testimonial-input': 'enterPress',
				'keyup .tvo-testimonial-input': 'updateTestimonialModel',
				'click .tvo-open-testimonial-webpage': function () {
					if ( ThriveOvation.util.validateInput( this.$el.find( '#tvo-author-website' ) ) && ThriveOvation.util.validateURL( this.$el.find( '#tvo-author-website' ) ) ) {
						var link = this.$el.find( '#tvo-author-website' ).val();
						if ( link.substring( 0, 4 ) !== "http" ) {
							link = '//' + link;
						}
						window.open( link, '_blank' );
					}
				},
				'click .tvo-open-testimonial-email': function () {
					window.location.href = 'mailto:' + this.$el.find( '#tvo-author-email' ).val();
				}
			},
			className: 'tvd-testimonial-page',
			tagName: 'div',

			initialize: function () {
				this.listenTo( this.model, 'sync', this.render );
				ThriveOvation.objects.EmailConfig = new ThriveOvation.models.EmailConfig;
				return this;
			},
			render: function () {
				this.$el.html( this.template( {testimonial: this.model} ) );
				this.renderTestimonialEditor();
				this.renderAcivitylog();
				var tags = this.model.get( 'tags' );
				TVE_Dash.materialize( this.$el );
				this.renderTags( tags );
				return this;
			},
			renderAcivitylog: function () {
				var collection = this.model.get( 'activityLog' );
				ThriveOvation.objects.ActivityLogEntriesCollection = new ThriveOvation.collections.ActivityLog( collection );
				if ( ThriveOvation.objects.ActivityLogViews instanceof ThriveOvation.views.ActivityLogView ) {
					ThriveOvation.objects.ActivityLogViews.undelegateEvents()
				}
				ThriveOvation.objects.ActivityLogViews = new ThriveOvation.views.ActivityLogView( {
					collection: ThriveOvation.objects.ActivityLogEntriesCollection,
					el: this.$el.find( '#tvo-activity-log-entries' )
				} );
				ThriveOvation.objects.Testimonial.set( 'activityLogCount', parseInt( this.model.get( 'activityLogCount' ) ) );
				ThriveOvation.objects.ActivityLogViews.render();
			},
			renderSelectTags: function () {
				var self = this,
					select = this.$el.find( "#tvo-author-new-tag-" + this.model.get( 'id' ) );
				select.select2( {
					tags: true,
					multiple: true,
					placeholder: ThriveOvation.translations.tags_select2_placeholder,
				} ).on( "select2:select", function ( e ) {
					ThriveOvation.util.addNewTagInTheSystem( e.params.data, self );
				} ).on( "select2:unselect", function ( evt ) {
					if ( ! evt.params.originalEvent ) {
						return;
					}
					evt.params.originalEvent.stopPropagation();
				} );
			},
			enterPress: function ( ev ) {
				if ( ev.which == 13 || ev.keyCode == 13 ) {
					this.updateTestimonial();
				}
			},
			renderTags: function ( tags ) {
				var self = this,
					selected = '';
				ThriveOvation.availableTags.forEach( function ( entry ) {
					if ( ThriveOvation.util.containsObject( entry, tags ) ) {
						selected = 'selected="selected"';
					} else {
						selected = '';
					}
					var html = '<option value="' + entry.id + '" ' + selected + '>' + entry.text + '</option>';
					self.$el.find( "#tvo-author-new-tag-" + self.model.get( 'id' ) ).append( html );
				} );
				this.renderSelectTags();
			},
			renderTestimonialEditor: function () {
				var mce_reinit = ThriveOvation.util.build_mce_init( {
					mce: window.tinyMCEPreInit.mceInit['tvo-tinymce-tpl'],
					qt: window.tinyMCEPreInit.qtInit['tvo-tinymce-tpl']
				}, 'tvo-testimonial-content-tinymce' );

				if ( mce_reinit ) {
					tinyMCEPreInit.mceInit = $.extend( tinyMCEPreInit.mceInit, mce_reinit.mce_init );
					tinyMCEPreInit.mceInit['tvo-testimonial-content-tinymce'].setup = function ( editor ) {
						editor.on( 'change', function () {
							editor.save();
						} );
					};
					tinyMCE.init( tinyMCEPreInit.mceInit['tvo-testimonial-content-tinymce'] );
					window.wpActiveEditor = 'tvo-testimonial-content-tinymce';
					tinyMCE.get( 'tvo-testimonial-content-tinymce' ).setContent( this.model.get( 'content' ) );
				}
			},
			validateModel: function () {
				var valid = true,
					$email = this.$el.find( '#tvo-author-email' );
				if ( ! ThriveOvation.util.validateInput( this.$el.find( '#tvo-author-name' ), ThriveOvation.translations.isRequired ) ) {
					valid = false;
				}
				if ( this.model.get( 'media_source' ) !== 'twitter' && this.model.get( 'media_source' ) !== 'facebook' && $email.val() != '' ) {
					if ( ! ThriveOvation.util.validateEmail( $email ) ) {
						valid = false;
					}
				}
				if ( this.$el.find( '#tvo-author-website' ).val() && ! ThriveOvation.util.validateURL( this.$el.find( '#tvo-author-website' ) ) ) {
					valid = false;
				}
				if ( ! ThriveOvation.util.validateInput( ThriveOvation.util.getTestimonialContent( this.$el ), ThriveOvation.translations.testimonial_content_missing, true ) ) {
					valid = false;
					this.$el.find( '.tvo-testimonial-content' ).addClass( 'tvo-tiny-mce-error' );
				} else {
					this.$el.find( '.tvo-testimonial-content' ).removeClass( 'tvo-tiny-mce-error' );
				}

				return valid;
			},
			updateTestimonialModel: function ( ev ) {
				var elem = jQuery( ev.currentTarget );
				ThriveOvation.util.setModelWithKeyValue( elem, this.model );
			},
			updateTestimonial: function () {
				TVE_Dash.showLoader();
				var self = this;

				this.model.set( 'tags', self.$el.find( '#tvo-author-new-tag-' + self.model.get( 'id' ) ).val() );
				this.model.set( 'content', ThriveOvation.util.getTestimonialContent( this.$el ) );

				if ( this.validateModel() ) {
					ThriveOvation.util.clearMCEEditor();
					this.model.save().done( function ( response ) {

						self.model.set( 'tags', response.tags );
						self.model.set( 'activityLog', response.activityLog );
						self.model.set( 'activityLogCount', response.activityLogCount );
						self.renderAcivitylog();
						TVE_Dash.success( ThriveOvation.translations.testimonial_successfully_saved );
					} ).error( function ( response ) {
						TVE_Dash.err( JSON.parse( response.responseText ).message );
					} ).always( function () {
						TVE_Dash.hideLoader();
					} );
				} else {
					TVE_Dash.hideLoader();
				}
				return true;
			},
			updateTestimonialStatus: function ( ev ) {
				var elem = jQuery( ev.currentTarget ),
					value = jQuery( elem ).val(),
					initialValue = jQuery( elem ).attr( 'data-value' ),
					self = this;
				if ( value != initialValue ) {
					this.model.set( {'status': this.$el.find( '#tvo-testimonial-status-change' ).val()} );
					var postMetaModel = new ThriveOvation.models.PostMeta( {
						'key': this.model.get( 'id' ),
						'meta_key': ThriveOvation.const.meta_key.status,
						'meta_value': this.$el.find( '#tvo-testimonial-status-change' ).val()
					} );

					postMetaModel.save( null, {
						success: function ( model, response ) {
							jQuery( elem ).attr( 'data-value', value ).removeClass( 'tvo-testimonial-status-' + initialValue ).addClass( 'tvo-testimonial-status-' + value );
							TVE_Dash.success( ThriveOvation.translations.status_changed_success_toast );
							self.model.set( 'activityLog', response.activityLog );
							self.model.set( 'activityLogCount', response.activityLogCount );

							self.renderAcivitylog();
						},
						error: function ( model, response ) {
							TVE_Dash.err( ThriveOvation.translations.status_changed_fail_toast );
						}
					} );
				}

			},
			openEmailModal: function () {
				this.modal( ThriveOvation.views.ConfigureEmailModal, {
					model: ThriveOvation.objects.EmailConfig,
					testimonial: ThriveOvation.objects.Testimonial,
					preview: true
				} );
			},
			openConfirmModal: function () {
				this.modal( ThriveOvation.views.ConfirmSendingEmailModal, {
					model: new ThriveOvation.models.ConfirmEmailSend
				} );
			},
			sendApprovalEmail: function () {
				TVE_Dash.showLoader();
				var testimonial = this.model.get( 'id' ),
					self = this;
				jQuery.ajax( {
					headers: {
						'X-WP-Nonce': ThriveOvation.nonce
					},
					url: ThriveOvation.routes.testimonials + '/email/approval',
					type: 'POST',
					data: {
						testimonial: testimonial
					}
				} ).done( function ( response ) {
					self.model.set( 'activityLog', response.activityLog );
					self.model.set( 'activityLogCount', response.activityLogCount );
					self.model.set( 'status', response.status );
					self.model.set( 'sent_emails', response.sent_emails );
					self.$el.find( '#tvo-testimonial-status-change' ).val( response.status ).trigger( 'change' );
					self.renderAcivitylog();
					TVE_Dash.success( ThriveOvation.translations.confirmation_email_sent );

				} ).error( function ( response ) {
					TVE_Dash.err( JSON.parse( response.responseText ).message );
				} ).always( function () {
					TVE_Dash.hideLoader();
				} );

			},
			openUploadImage: function () {
				if ( wp_file_frame ) {
					wp_file_frame.open();
					return;
				}

				var wp_file_frame = wp.media( {
					title: ThriveOvation.translations.choose_testimonial_image,
					button: {
						text: ThriveOvation.translations.choose_testimonial_image_button
					},
					library: {
						type: 'image'
					},
					multiple: false,
					frame: 'select'
				} );
				var self = this;
				wp_file_frame.on( 'select', function () {
					var attachment = wp_file_frame.state().get( 'selection' ).first().toJSON();
					self.model.set( 'picture_url', attachment.url );
					self.$el.find( '.tvo-profile-picture' ).css( 'background-image', 'url(' + attachment.url + ')' );
					self.$el.find( '#tvo-upload-testimonial-image' ).hide();
					self.$el.find( '.tvo-image-uploaded' ).show();
				} );
				wp_file_frame.open();
			},
			removeImage: function () {
				var default_image = this.$el.find( '#tvo-remove-testimonial-image' ).attr( 'data-default' );
				this.model.set( 'picture_url', default_image );

				this.$el.find( '.tvo-profile-picture' ).css( 'background-image', 'url(' + default_image + ')' );
				this.$el.find( '#tvo-upload-testimonial-image' ).show();
				this.$el.find( '.tvo-image-uploaded' ).hide();
			}
		} );

		/**
		 * Setting view
		 */
		ThriveOvation.views.Setting = ThriveOvation.views.Base.extend( {
			template: TVE_Dash.tpl( 'settings' ),
			events: {
				'click .tvo-save-settings': 'saveSettings',
				'click .tvo-add-new-connection': 'openConnectionModal',
				'click .tvo-configure-email-template': 'openEmailConfigureModal',
				'click .tvo-configure-landing-page': 'openLandingPageConfigureModal',
				'click .tvo-new-image': 'selectDefaultImage',
				'click .tvo-default-image': 'setDefaultImage'
			},
			initialize: function () {
				this.listenTo( this.model, 'sync', this.render );
			},
			init: function () {
				/*Setting the select values*/
				var model = this.model;
				jQuery( '.tvo-setting-input-select' ).each( function () {
					var data_key = jQuery( this ).data( 'key' );
					jQuery( this ).val( model.get( data_key ) );
				} );
				ThriveOvation.objects.EmailConfig = new ThriveOvation.models.EmailConfig;
			},
			render: function () {
				this.$el.html( this.template( {settings: this.model} ) );
				ThriveOvation.util.bind_wistia();
				this.init();
				this.renderApiConnections();
				TVE_Dash.materialize( this.$el );
				TVE_Dash.hideLoader();
				return this;
			},
			/*Social media ENDS*/
			renderApiConnections: function () {
				var v = new ThriveOvation.views.AssetConnections( {
					collection: ThriveOvation.objects.AssetConnection,
					el: this.$el.find( '#tvo-email-connection-wrap' )
				} );
				v.render();
				return this;
			},
			openConnectionModal: function () {
				TVE_Dash.modal( ThriveOvation.views.ConnectionModal, {
					'max-width': '35%',
					model: new ThriveOvation.models.NewConnection( {connected_apis: ThriveOvation.objects.AssetConnection} )
				} );
			},
			openLandingPageConfigureModal: function () {
				this.modal( ThriveOvation.views.ConfigureLandingPageModal, {
					model: new ThriveOvation.models.LandingPageConfig,
					'max-width': '45%'
				} );
			},
			openEmailConfigureModal: function () {
				this.modal( ThriveOvation.views.ConfigureEmailModal, {
					model: ThriveOvation.objects.EmailConfig
				} );
			},
			saveSettings: function ( ev ) {
				var elem = jQuery( ev.currentTarget ),
					model = this.model;

				if ( elem.hasClass( 'tvd-disabled' ) ) {
					return;
				} else {
					ThriveOvation.util.setLoading( elem );
				}

				jQuery( '.tvo-setting-input-checkbox' ).each( function () {
					var $this = jQuery( this ),
						data_key = $this.data( 'key' ),
						data_value = Number( $this[0].checked ),
						obj = {};
					obj[data_key] = data_value;
					model.set( obj );
				} );

				model.save( null, {
					success: function ( model, response ) {
						TVE_Dash.success( ThriveOvation.translations.settings_saved_success_toast );
						ThriveOvation.util.removeLoading( elem );
					},
					error: function ( model, response ) {
						TVE_Dash.err( ThriveOvation.translations.settings_saved_fail_toast );
						ThriveOvation.util.removeLoading( elem );
					}
				} );
			},
			selectDefaultImage: function () {
				if ( wp_file_frame ) {
					wp_file_frame.open();
					return;
				}

				var wp_file_frame = wp.media( {
						title: ThriveOvation.translations.choose_default_image,
						button: {
							text: ThriveOvation.translations.choose_testimonial_image_button
						},
						library: {
							type: 'image'
						},
						multiple: false,
						frame: 'select'
					} ),
					self = this;
				wp_file_frame.on( 'select', function () {
					var attachment = wp_file_frame.state().get( 'selection' ).first().toJSON();
					self.$el.find( '.tvo-default-picture' ).css( 'background-image', 'url(' + attachment.url + ')' );
					self.$el.find( '.tvo-default-image' ).attr( 'disabled', false );
					self.saveDefaultImage( attachment.url )
				} );
				wp_file_frame.open();
			},
			setDefaultImage: function ( e ) {
				var url = e.currentTarget.getAttribute( 'data-default' );
				e.currentTarget.setAttribute( 'disabled', true );
				this.$el.find( '.tvo-default-picture' ).css( 'background-image', 'url(' + url + ')' );
				this.saveDefaultImage( url );
			},
			saveDefaultImage: function ( url ) {
				if ( ! url ) {
					url = this.$el.find( '.tvo-default-picture' ).css( 'background-image' );
				}

				TVE_Dash.showLoader();

				jQuery.ajax( {
					headers: {
						'X-WP-Nonce': ThriveOvation.nonce
					},
					type: 'POST',
					url: ThriveOvation.routes.settings + '/default-placeholder',
					data: {image: url}
				} ).done( function () {
					TVE_Dash.success( ThriveOvation.translations.success_image_set );
				} ).fail( function () {
					TVE_Dash.error( ThriveOvation.translations.error_image_set );
				} ).always( function () {
					TVE_Dash.hideLoader();
				} );
			}
		} );


		/**
		 * Activity Log Entry
		 */
		ThriveOvation.views.ActivityLogEntryView = ThriveOvation.views.Base.extend( {
			template: TVE_Dash.tpl( 'testimonials/activity/log/entry' ),
			tagName: "li",

			className: "tvd-collection-item tvo-activity-log-entry",
			events: {},
			initialize: function ( options ) {

			},
			render: function () {
				this.$el.html( this.template( {logEntry: this.model} ) );
				return this;
			}
		} );

		/**
		 * Activity Log
		 */
		ThriveOvation.views.ActivityLogView = ThriveOvation.views.Base.extend( {
			events: {
				'click .tvo-extend-activity-log': 'extend'
			},
			initialize: function () {
			},
			renderOne: function ( item ) {
				var view = new ThriveOvation.views.ActivityLogEntryView( {
						model: item
					} ),
					el = view.render().$el;
				this.$el.append( el );
			},
			render: function ( collection ) {
				this.$el.empty();
				var c = this.collection;
				if ( typeof collection !== 'undefined' ) {
					c = new ThriveOvation.collections.ActivityLog( collection );
				}
				c.each( this.renderOne, this );
				this.removeExtension();
				this.renderExtension();
				return this;
			},
			removeExtension: function () {
				this.$el.find( '.tvo-activity-log-extension' ).remove();
			},
			extend: function () {
				var self = this;
				TVE_Dash.showLoader();
				$.ajax( {
					headers: {
						'X-WP-Nonce': ThriveOvation.nonce
					},
					url: self.collection.url() + ThriveOvation.objects.Testimonial.get( 'id' ) + '/' + self.collection.length,
					type: 'GET'
				} ).done( function ( response ) {
					if ( response.activity_log.length ) {
						self.removeExtension();
						response.activity_log.forEach( function ( item ) {
							var model = new ThriveOvation.models.ActivityLogEntry( item );
							self.collection.push( model );
							self.renderOne( model );
						} );
						self.renderExtension();
					}
				} ).error( function () {
				} ).always( function () {
					TVE_Dash.hideLoader();
				} );
			},
			renderExtension: function () {
				if ( this.collection.length < ThriveOvation.objects.Testimonial.get( 'activityLogCount' ) ) {

					var html = TVE_Dash.tpl( 'testimonials/activity/log/extension' );
					this.$el.append( html );
				}
			}
		} );

		/**
		 * Shortcodes dashboard view
		 */
		ThriveOvation.views.Shortcodes = ThriveOvation.views.Base.extend( {
			template: TVE_Dash.tpl( 'shortcodes/list' ),
			type: '',
			events: {
				'click .tvo-add-new-shortcode': 'new'
			},
			initialize: function ( options ) {
				this.type = options.type;
			},
			render: function () {
				this.$el.html( this.template( {type: this.type} ) );

				if ( this.collection.length === 0 ) {
					this.$el.find( '.tvo-shortcodes-notice' ).show();
				} else {
					this.collection.each( this.renderShortcode, this );
				}

				TVE_Dash.materialize( this.$el );

				return this;
			},
			renderShortcode: function ( item ) {
				var view = new ThriveOvation.views.Shortcode( {
					model: item
				} );
				this.$el.find( '.tvo-shortcodes-container .tvo-add-new-shortcode' ).before( view.render().$el );
			},
			new: function () {
				this.modal( ThriveOvation.views.ModalNewShortcode, {
					model: new ThriveOvation.models.Shortcode(),
					view: this
				} );
			}
		} );

		/**
		 * Shortcode individual view
		 */
		ThriveOvation.views.Shortcode = ThriveOvation.views.Base.extend( {
			className: 'tvd-col tvd-s6 tvd-ms6 tvd-m4 tvd-l3 tvo-shortcode-item',
			template: TVE_Dash.tpl( 'shortcodes/shortcode' ),
			events: {
				'click .tvo-edit-shortcode-title': 'editTitle',
				'click .tvo-shortcode-icon-delete': 'deleteShortcode'
			},
			initialize: function () {
				this.listenTo( this.model, 'destroy', this.remove );
				this.listenTo( this.model, 'change:state', this.renderState );
				this.renderState();
			},
			renderState: function () {
				var el;
				if ( this.model.get( 'state' ) == 'delete' ) {
					var deleteView = new ThriveOvation.views.ShortcodeDeleteState( {
						model: this.model
					} );
					el = deleteView.render().$el;
					this.$el.html( el );
				} else {
					el = this.template( {shortcode: this.model} );
					this.$el.html( el );
					this.$shortcodeName = this.$el.find( '.tvo-shortcode-title' );
					ThriveOvation.bindZClip( this.$el.find( 'a.tve-copy-to-clipboard' ) );
					TVE_Dash.materialize( this.$el );
				}
				return this;
			},
			deleteShortcode: function () {
				this.model.set( 'state', 'delete' );
				this.renderState();
				return this;
			},
			editTitle: function () {
				var self = this,
					edit_btn = this.$el.find( '.tvo-edit-shortcode-title' ),
					edit_model = new Backbone.Model( {
						value: this.model.get( 'title' ),
						label: ThriveOvation.translations.shortcode_name,
						required: true
					} );
				edit_model.on( 'change:value', function () {
					self.model.set( 'name', arguments[1] );
					self.model.save();
					self.$shortcodeName.html( self.model.get( 'name' ) ).show();
					textEdit.remove();
				} );
				edit_model.on( 'tvo_no_change', function () {
					self.$shortcodeName.html( self.model.get( 'name' ) ).show();
					textEdit.remove();
				} );

				var textEdit = new ThriveOvation.views.TextEdit( {
					model: edit_model,
					tagName: 'div'
				} );

				this.$shortcodeName.hide().after( textEdit.render().$el );
				textEdit.focus();
			}
		} );

		/**
		 * Shortcode Delete State View
		 */
		ThriveOvation.views.ShortcodeDeleteState = ThriveOvation.views.Base.extend( {
			template: TVE_Dash.tpl( 'shortcodes/delete/shortcode' ),
			events: {
				'click .tvo-delete-no': function () {
					this.model.set( 'state', 'normal' );
				},
				'click .tvo-delete-yes': 'yes',
				'keydown': 'keyAction'
			},
			initialize: function () {
				this.listenTo( this.collection, 'remove', this.remove );
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				var _this = this;
				_.defer( function () {
					_this.$( '.tvo-delete-shortcode-card' ).focus();
				} );
				return this;
			},
			keyAction: function ( e ) {
				var code = e.which;
				if ( code == 13 ) {
					this.yes();
				} else if ( code == 27 ) {
					this.model.set( 'state', 'normal' );
				}
			},
			yes: function () {
				TVE_Dash.cardLoader( this.$el );
				this.model.destroy( {
					wait: true,
					success: function () {
						TVE_Dash.hideLoader();
						TVE_Dash.success( ThriveOvation.translations.delete_shortcode );
					},
					error: function ( model, response ) {
						response = JSON.parse( response.responseText );
						TVE_Dash.err( response.message + ': ' + response.code );
						TVE_Dash.hideLoader();
					}
				} );
			}
		} );

		ThriveOvation.views.ModalNewShortcode = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'shortcodes/shortcode/modal' ),
			events: {
				'click .tvo-save-new-shortcode': 'save',
				'keydown #tvo-shortcode-name': 'toSave',
			},
			afterRender: function () {
				this.$el.html( this.template() );

				return this;
			},
			validateModel: function () {
				return ThriveOvation.util.validateInput( this.$el.find( '#tvo-shortcode-name' ), ThriveOvation.translations.isRequired );
			},
			toSave: function ( event ) {
				var code = event.keyCode || event.which;
				if ( code == 13 ) {
					this.save();
				}
			},
			save: function () {
				if ( this.validateModel() ) {
					var self = this,
						model = {
							'name': this.$el.find( '#tvo-shortcode-name' ).val(),
							'type': this.view.type,
						};

					TVE_Dash.showLoader();
					this.model.save( model, {
						success: function ( model, response ) {
							model.set( {'url': response.url, 'id': response.id} );
							self.view.collection.add( model );
							self.view.collection.sort();
							self.view.render();
							self.close();
							TVE_Dash.hideLoader();
						}
					} );
				}
				return true;
			}
		} );

		ThriveOvation.views.TextEdit = ThriveOvation.views.Base.extend( {
			className: 'tvd-input-field tvo-inline-edit',
			template: TVE_Dash.tpl( 'textedit' ),
			events: {
				'keyup input': 'keyup',
				'change input': function ( e ) {
					if ( ! $.trim( this.input.val() ) ) {
						this.input.addClass( 'tvd-invalid' );
						return false;
					}
					this.model.set( 'value', this.input.val() );
					return false;
				},
				'blur input': function () {
					this.model.trigger( 'tvo_no_change' );
				}
			},
			keyup: function ( event ) {
				if ( event.which === 27 ) {
					this.model.trigger( 'tvo_no_change' );
				}
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				this.input = this.$el.find( 'input' );

				return this;
			},
			focus: function () {
				this.input.focus().select();
			}
		} );

		ThriveOvation.views.AssetConnections = ThriveOvation.views.Base.extend( {
			events: {
				'click .tvo-connection-setting-change': 'saveConnection'
			},
			initialize: function () {
				this.listenTo( this.collection, 'change', this.render );
			},
			render: function () {
				this.$el.find( '#tvo-email-api-connections' ).empty();
				this.collection.each( this.renderOne, this );
				return this;
			},
			renderOne: function ( item ) {
				var v = new ThriveOvation.views.AssetConnection( {
					model: item,
					wrapper: this.$el
				} );
				var html = v.render().$el;
				this.$el.find( '#tvo-email-api-connections' ).append( html );
			},
			saveConnection: function ( event ) {
				var connection = jQuery( event.currentTarget ).attr( 'data' ),
					self = this;
				TVE_Dash.showLoader();
				jQuery.ajax( {
					headers: {
						'X-WP-Nonce': ThriveOvation.nonce
					},
					type: 'POST',
					url: ThriveOvation.routes.settings + '/api/activate',
					data: {connection: connection}
				} ).done( function () {
					self.collection.each( function ( model, index ) {
						model.set( 'active', connection );
					} );
					TVE_Dash.hideLoader();
				} );
			}
		} );

		ThriveOvation.views.AssetConnection = ThriveOvation.views.Base.extend( {
			tagName: 'li',
			template: TVE_Dash.tpl( 'api/connections/connections' ),
			events: {
				'click .tve-asset-group-test': 'testConnection',
				'click .tvo-connection-edit': 'openEditModal',
			},
			initialize: function ( options ) {
				_.extend( this, _.pick( options, 'wrapper' ) );
			},
			render: function () {
				if ( this.model.get( 'connection' ) ) {
					this.$el.html( this.template( this.model.toJSON() ) );
					this.wrapper.find( '.tvo-no-email-connection-setup' ).hide();
					this.wrapper.find( '#tvo-add-new-connection-upper' ).show();
				} else {
					this.wrapper.find( '.tvo-no-email-connection-setup' ).show();
					this.wrapper.find( '#tvo-add-new-connection-upper' ).hide();
				}
				return this;
			},
			openEditModal: function ( e ) {
				var item_id = $( e.currentTarget ).attr( 'id' ).replace( 'tvo-delivery-', '' ).replace( '-edit', '' );

				this.modal( ThriveOvation.views.ConnectionModal, {
					model: new ThriveOvation.models.NewConnection( {connected_apis: ThriveOvation.objects.AssetConnection} ),
					edit: item_id
				} );
			},
			testConnection: function ( event ) {
				var connection = jQuery( event.currentTarget ).attr( 'data' ),
					response_icon = this.$el.find( '.tvo-test-connection-' + connection + ' .tve-asset-group-test-result' ),
					response_wrapper = this.$el.find( '.tvo-test-response' );
				TVE_Dash.showLoader();
				jQuery.ajax( {
					headers: {
						'X-WP-Nonce': ThriveOvation.nonce
					},
					type: 'POST',
					url: ThriveOvation.routes.settings + '/api/testconnection',
					data: {connection: connection}
				} ).success( function ( response ) {
					response_wrapper.empty();
					var clean = JSON.parse( response );
					response_wrapper.prepend( clean );
					response_icon.empty();
					if ( clean.indexOf( "updated" ) > - 1 ) {
						response_icon.append( '<span class="tvd-text-green"><i class="tvd-icon-check"></i>Success</span>' );
					} else {
						response_icon.append( '<span class="tvd-text-red"><i class="tvd-icon-remove"></i>Error</span>' );
					}
					TVE_Dash.hideLoader();
				} )
			}
		} );

		ThriveOvation.views.ConfigureLandingPageModal = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'email/configure-landing-page' ),
			events: {
				'click .tvo-save-landing-page-settings': 'save',
				'change .tvo-change-landing-setting': 'changeSettings',
			},
			getPostNameById: function( postId, classType ) {
				$('#tvu-leads-posts').val('');
				$('#tvu-leads-posts-not').val('');
				var getPostByIdUrl = ThriveOvation.routes.settings + '/get_post_name_by_id';

				jQuery.ajax( {
					headers: {
						'X-WP-Nonce': ThriveOvation.nonce
					},
					url: getPostByIdUrl,
					type: 'POST',
					data: {
						postId: postId
					}
				} ).done( function ( response ) {
					if( classType == 'approve') {
						$('#tvu-leads-posts').val(response);
						$('#tvu-leads-posts').removeClass("ui-autocomplete-loading");
					}

					if( classType == 'not_approve') {
						$('#tvu-leads-posts-not').val(response);
						$('#tvu-leads-posts-not').removeClass("ui-autocomplete-loading");
					}
				} ).error( function ( response ) {
					TVE_Dash.err( JSON.parse( response.responseText ).message );
				} ).always( function () {
					TVE_Dash.hideLoader();
				} );
			},
			testimonialAutocomplete: function( classType ) {
				var self = this;
				var ajaxUrl = ThriveOvation.routes.settings + '/landing_testimonial_autocomplete';

				$(".tvu-leads-autocomplete").autocomplete({
					delay: 50,
					select: function (event, ui) {
						self.model.set({'approve_post_id': ui.item.id});
						self.model.set({'approve_post_val': ui.item.value});
					},
					source: function (request, response) {
						$.ajax({
							headers: {
								'X-WP-Nonce': ThriveOvation.nonce
							},
							url: ajaxUrl,
							dataType: 'json',
							data: {
								q: request.term,
							},
							type: 'POST',
						}).done(function(data){
							response(data);
						}).error( function ( response ) {
							TVE_Dash.err( JSON.parse( response.responseText ).message );
						});
					}
				});

				$(".tvu-not-leads-autocomplete").autocomplete({
					delay: 50,
					select: function (event, ui) {
						self.model.set({'not_approve_post_id': ui.item.id});
						self.model.set({'not_approve_post_val': ui.item.value});
					},
					source: function (request, response) {
						$.ajax({
							headers: {
								'X-WP-Nonce': ThriveOvation.nonce
							},
							url: ajaxUrl,
							dataType: 'json',
							data: {
								q: request.term,
							},
							type: 'POST',
						}).done(function(data){
							response(data);
						}).error( function ( response ) {
							TVE_Dash.err( JSON.parse( response.responseText ).message );
						});
					}
				});
			},
			afterRender: function () {
				var self = this;
				console.log(this.model.get( 'approve' ));
				this.model.fetch( {
					success: function ( model, response, options ) {
						if(model.get( 'approve' )) {
							self.$el.find( '#approve' ).val( model.get( 'approve' ) ).trigger( 'change' );
						}
						if(model.get( 'not_approve' )) {
							self.$el.find( '#not_approve' ).val( model.get( 'not_approve' ) ).trigger( 'change' );
						}

						if ( model.get( 'approve' ) == 'tvo_custom_url' ) {
							self.$el.find( '#approve_url' ).val( model.get( 'approve_url' ) );
						}

						if ( model.get( 'not_approve' ) == 'tvo_existing_content' ) {
							self.$el.find( '#not_approve_post_id' ).val( model.get( 'not_approve_post_id' ) ).trigger( 'change' );
						} else {
							if ( model.get( 'not_approve' ) == 'tvo_custom_url' ) {
								self.$el.find( '#not_approve_url' ).val( model.get( 'not_approve_url' ) );
							}
						}

						if( self.model.get( 'approve_post_val' ) ){
							$('#tvu-leads-posts').val(self.model.get( 'approve_post_val' ));
						}
						if( self.model.get( 'approve_post_val' ) ){
							$('#tvu-leads-posts-not').val(self.model.get( 'not_approve_post_val' ));
						}

						TVE_Dash.materialize( self.$el );

						self.testimonialAutocomplete();
						self.getPostNameById( model.get( 'approve_post_id' ), 'approve' );
						self.getPostNameById( model.get( 'not_approve_post_id' ), 'not_approve' );

					}
				} );
			},
			changeSettings: function ( ev ) {
				var elem = jQuery( ev.currentTarget ),
					action = elem.attr( 'data-action' ),
					value = elem.val();

				this.$el.find( '.' + action ).addClass( 'tvd-hide' );
				this.$el.find( '.' + action + '.' + value ).removeClass( 'tvd-hide' );
				this.$el.find( '.' + action + '_tvo_existing_content' ).val( null ).trigger( 'change' );
				this.$el.find( '.' + action + '_tvo_custom_url' ).val( null ).trigger( 'change' );
			},
			save: function ( ev ) {

				var elem = jQuery( ev.currentTarget ),
					self = this,
					approve = this.$el.find( '#approve' ).val(),
					approve_url = this.$el.find( '#approve_url' ).val(),
					approve_post_id = this.model.get('approve_post_id'),
					approve_post_val = this.model.get('approve_post_val'),
					not_approve = this.$el.find( '#not_approve' ).val(),
					not_approve_url = this.$el.find( '#not_approve_url' ).val(),
					not_approve_post_id = this.model.get('not_approve_post_id'),
					not_approve_post_val = this.model.get('not_approve_post_val');

				if ( elem.hasClass( 'tvd-disabled' ) ) {
					return;
				} else {
					ThriveOvation.util.setLoading( elem );
				}

				if ( approve != '' && (approve_url != '' || approve_post_id != '') && not_approve != '' && (not_approve_url != '' || not_approve_post_id != '') ) {

					if ( ( approve_url != '' && ! ThriveOvation.util.validateURL( this.$el.find( '#approve_url' ) ) ) || ( not_approve_url != '' && ! ThriveOvation.util.validateURL( this.$el.find( '#not_approve_url' ) ) ) ) {
						TVE_Dash.err( ThriveOvation.translations.invalid_url );
						ThriveOvation.util.removeLoading( elem );
						return;
					}

					var obj = {
						'approve': approve,
						'approve_url': approve_url,
						'approve_post_id': approve_post_id,
						'approve_post_val': approve_post_val,
						'not_approve': not_approve,
						'not_approve_url': not_approve_url,
						'not_approve_post_id': not_approve_post_id,
						'not_approve_post_val': not_approve_post_val
					};

					this.model.save( obj, {
						success: function ( model, response ) {
							TVE_Dash.success( ThriveOvation.translations.landing_settings_success_toast );
							jQuery( '.tvo_icon_landing_page' ).addClass( 'tvo-settings-step-success' );
							jQuery( '.tvo-configure-landing-page' ).text( ThriveOvation.translations.settings_step_completed );
							self.close();
						},
						error: function ( model, response ) {
							TVE_Dash.err( ThriveOvation.translations.landing_settings_fail_toast );
						},
						complete: function () {
							ThriveOvation.util.removeLoading( elem );
						}
					} );

				} else {
					TVE_Dash.err( ThriveOvation.translations.complete_all_the_fields );
					ThriveOvation.util.removeLoading( elem );
					return;
				}
			}
		} );

		ThriveOvation.views.ConfirmSendingEmailModal = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'email/confirm/send/email' ),
			events: {
				'click .tvo-send-approval-email': 'sendEmail'
			},
			afterRender: function () {
				this.$el.html( this.template( {testimonial: this.model} ) );
			},
			sendEmail: function () {
				ThriveOvation.objects.TestimonialView.sendApprovalEmail();
				this.close();
			}
		} );

		/**
		 *  Email template modal configuration page
		 */

		ThriveOvation.views.ConfigureEmailModal = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'email/configure/email' ),
			events: {
				'click .tvo-save-email-template': 'saveEmailTemplate',
				'click .tvo-preview-email': 'previewEmail',
				'click .tvo-back-to-email-template': 'configEmail',
				'click .tvo-test-approval-email': 'sendTestEmail',
				'click .tvo-send-email': 'checkIfAlreadySent',
				'click .tl-toggle-tab-display': 'toggleTabDisplay',
				'keydown .tvo-email-test': 'enterPressOnTest',
			},
			afterRender: function () {
				this.$el.addClass( 'tve-assed-email-modal' );
				ThriveOvation.bindZClip( this.$el.find( 'a.tve-copy-to-clipboard' ) );
				if ( ThriveOvation.objects.EmailConfig.get( 'template' ) && ThriveOvation.objects.EmailConfig.get( 'subject' ) ) {
					this.$el.find( '#tvo-email-template' ).val( ThriveOvation.objects.EmailConfig.get( 'template' ) );
					this.$el.find( '#tvo-email-subject' ).val( ThriveOvation.objects.EmailConfig.get( 'subject' ) );
				}
				this.renderTestimonialEditor();
				if ( this.preview ) {
					this.previewEmail();
					this.$el.find( '.tvo-settings-footer-buttons' ).hide();
					this.$el.find( '.tvo-preview-footer-buttons' ).show();
				}
				return this;
			},
			configEmail: function () {
				this.$el.find( '.tvo-preview-email-content' ).hide();
				this.$el.find( '.tvo-config-email-content' ).show();
			},
			renderTestimonialEditor: function () {
				var mce_reinit = ThriveOvation.util.build_mce_init( {
					mce: window.tinyMCEPreInit.mceInit['tvo-tinymce-tpl'],
					qt: window.tinyMCEPreInit.qtInit['tvo-tinymce-tpl']
				}, 'tvo-email-template' );

				if ( mce_reinit ) {
					tinyMCEPreInit.mceInit = $.extend( tinyMCEPreInit.mceInit, mce_reinit.mce_init );
					tinyMCEPreInit.mceInit['tvo-email-template'].setup = function ( editor ) {
						editor.on( 'change', function () {
							editor.save();
						} );
					};
					tinyMCE.init( tinyMCEPreInit.mceInit['tvo-email-template'] );
					window.wpActiveEditor = 'tvo-email-template';
				}
			},
			sendTestEmail: function () {
				if ( ThriveOvation.util.validateInput( this.$el.find( '.tvo-email-test' ) ) && ThriveOvation.util.validateEmail( this.$el.find( '.tvo-email-test' ) ) ) {
					var email = this.$el.find( '.tvo-email-test' ).val();
					TVE_Dash.showLoader();
					var template = this.$el.find( '.tvo-preview-email-text' ).html(),
						subject = this.$el.find( '.tvo-preview-email-subject' ).html();
					jQuery.ajax( {
						headers: {
							'X-WP-Nonce': ThriveOvation.nonce
						},
						url: ThriveOvation.routes.settings + '/email/test',
						type: 'POST',
						data: {
							template: template,
							subject: subject,
							email: email
						}
					} ).done( function ( response ) {
						TVE_Dash.success( ThriveOvation.translations.confirmation_test_email_sent );
					} ).error( function ( response ) {
						TVE_Dash.err( JSON.parse( response.responseText ).message );
					} ).always( function () {
						TVE_Dash.hideLoader();
					} );
				}
			},
			checkIfAlreadySent: function () {
				if ( parseInt( ThriveOvation.objects.Testimonial.get( 'sent_emails' ) ) > 0 ) {
					this.close();
					ThriveOvation.objects.TestimonialView.openConfirmModal();
				} else {
					this.sendApprovalEmail();
				}
			},
			sendApprovalEmail: function () {
				ThriveOvation.objects.TestimonialView.sendApprovalEmail();
				this.close();
			},
			previewEmail: function () {
				var self = this;
				TVE_Dash.showLoader();
				var testimonial_info = {};
				if ( this.preview ) {
					testimonial_info.name = this.testimonial.get( 'name' );
					testimonial_info.content = this.testimonial.get( 'content' );
				}
				var template = tinyMCE.get( 'tvo-email-template' ).getContent(),
					subject = this.$el.find( '#tvo-email-subject' ).val();
				jQuery.ajax( {
					headers: {
						'X-WP-Nonce': ThriveOvation.nonce
					},
					url: ThriveOvation.routes.settings + '/email/process',
					type: 'POST',
					data: {
						template: template,
						subject: subject,
						data: testimonial_info
					}
				} ).done( function ( response ) {
					self.$el.find( '.tvo-preview-email-text' ).html( response.template );
					self.$el.find( '.tvo-preview-email-subject' ).html( response.subject );
					self.$el.find( '.tvo-config-email-content' ).hide();
					self.$el.find( '.tvo-preview-email-content' ).show();
				} ).error( function ( response ) {
					TVE_Dash.err( JSON.parse( response.responseText ).message );
				} ).always( function () {
					TVE_Dash.hideLoader();
				} );

			},
			saveEmailTemplate: function () {
				var template = ( ! ThriveOvation.util.hasTinymce() ) ? this.$el.find( "#tvo-email-template" ).val() : tinyMCE.get( 'tvo-email-template' ).getContent(),
					subject = this.$el.find( '.tvo-email-subject' ).val(),
					self = this,
					old_template = this.model.get( 'template' ),
					$elem = this.$el.find( '.tvo-save-email-template' );

				this.model.set( {'template': template, 'subject': subject} );
				TVE_Dash.showLoader();
				if ( $elem.hasClass( 'tvd-disabled' ) ) {
					return;
				} else {
					ThriveOvation.util.setLoading( $elem );
				}
				this.model.save().done( function () {
					jQuery( '.tvo_icon_email_config' ).addClass( 'tvo-settings-step-success' );
					jQuery( '.tvo-configure-email-template' ).text( ThriveOvation.translations.settings_step_completed );
					TVE_Dash.success( ThriveOvation.translations.settings_saved_success_toast );
					self.close();
				} ).error( function ( response ) {
					self.$el.find( '.tvo-email-template' ).val( old_template );
					TVE_Dash.err( JSON.parse( response.responseText ).message );
				} ).always( function () {
					TVE_Dash.hideLoader();
					ThriveOvation.util.removeLoading( $elem );
				} );
			},
			toggleTabDisplay: function ( e ) {
				var $elem = jQuery( e.currentTarget ), collapsed = $elem.hasClass( 'collapsed' ), $target = jQuery( $elem.data( 'target' ) );

				if ( collapsed ) {
					$target.hide( 0 ).removeClass( 'tvd-not-visible' ).slideDown( 200 );
				} else {
					$target.slideUp( 200, function () {
						$target.addClass( 'tvd-not-visible' );
					} );
				}

				$elem.toggleClass( 'collapsed' );
				$elem.toggleClass( 'hover' );
			},
			enterPressOnTest: function ( ev ) {
				if ( ev.which == 13 || ev.keyCode == 13 ) {
					this.sendTestEmail();
				}
			},
			beforeClose: function () {
				ThriveOvation.util.clearMCEEditor( 'tvo-testimonial-content-tinymce' );
			}
		} );

		ThriveOvation.views.ConnectionModal = TVE_Dash.views.ModalSteps.extend( {
			template: TVE_Dash.tpl( 'api/connections/add-api-connection' ),
			events: {
				'click .tvd-modal-next-step': "next",
				'click .tvd-modal-prev-step': "prev",
				'click .tve-asset-delivery-conection': 'openConnection',
				'click .tve-leads-connection-edit': 'openConnection',
				'click .tvd-api-connect': "submitConnection",
				'click .tvd-api-cancel': "closeConnection",
				'click .tvo-asset-close': function () {
					this.close();
				}
			},
			onOpen: function () {
				ThriveOvation.objects.Apis.each( this.populateFields, this );
			},
			afterRender: function () {
				var self = this;
				ThriveOvation.objects.AssetConnection.forEach( function ( item ) {
					var elem = self.$el.find( '.tvo-' + item.get( 'connection' ) + '-api' );
					elem.addClass( 'tvo-api-selected' ).next( '.tvo-connection-name' ).addClass( 'tvo-connected-message' ).append( '<span class="tvo-asset-connected">(connected)</span>' );
					//fill in existing credentials
					var credentials = item.get( 'connection_instance' );
					for ( var propertyName in credentials ) {
						if ( credentials[propertyName] ) {
							self.$el.find( '#tvo-' + propertyName + '-' + item.get( 'connection' ) ).val( credentials[propertyName] );
						}
					}
				} );

				ThriveOvation.objects.Apis.each( this.renderSelector, this );

				this.steps = this.$el.find( this.stepClass ).hide();
				this.gotoStep( 0 );

				if ( this.edit ) {
					var form = this.$el.find( '.tvo-connection-form[data-key=' + this.edit + ']' );
					form.find( '.tvd-api-cancel' ).addClass( 'tvo-close-connection' );

					this.$el.find( '.tvo-connection-form' ).hide();
					form.show();
					this.gotoStep( 1 );

				}

				return this;
			},

			populateFields: function ( item ) {
				var connection = item.get( 'connection' ),
					instance = item.get( 'connection_instance' ),
					$form = this.$el.find( '.tvo-connection-form[data-key=' + connection + '] form' );
				if ( instance ) {
					_.each( instance, function ( v, k ) {
						var element = $form.find( ':input[name="connection[' + k + ']"]' );
						/**
						 * if we're adding checkboxes or radio buttons we should cover these cases too here
						 */
						if ( element.is( 'input' ) || element.is( 'textarea' ) || element.is( 'select' ) ) {
							element.val( v );
						}
					} );
				}
			},
			renderSelector: function ( item ) {
				var v = new ThriveOvation.views.AssetSelector( {
					model: item
				} );
				var html = v.render().$el;
				this.$el.find( '#tvo-connections-list' ).append( html );
			},

			openConnection: function ( e ) {
				e.stopPropagation();
				var $target = $( e.target ),
					data = $target.attr( 'data-key' ),
					form = this.$el.find( '.tvo-connection-form[data-key=' + data + ']' );

				this.$el.find( '.tvo-connection-form' ).hide();
				form.show();

				this.gotoStep( 1 );
			},
			closeConnection: function ( e ) {
				if ( $( e.target ).hasClass( 'tvo-close-connection' ) ) {
					$( e.target ).removeClass( 'tvo-close-connection' );
					this.close();

					return;
				}

				this.gotoStep( 0 );
			},
			submitConnection: function ( event ) {
				var self = this,
					model = this.model,
					$form = $( event.target ).closest( '.tvo-connection-form' ).find( 'form' ),
					data = $form.serializeArray(),
					connection = this.mapProprieties( data ),
					valid = false;

				_.each( connection.connection_instance, function ( v, k ) {
					valid = ThriveOvation.util.validateInput( $form.find( ':input[name="connection[' + k + ']"]' ) );
				} );

				if ( valid ) {
					TVE_Dash.showLoader();
					jQuery.ajax( {
						headers: {
							'X-WP-Nonce': ThriveOvation.nonce
						},
						url: ThriveOvation.routes.settings + '/api',
						type: 'post',
						data: data
					} ).done( function ( result ) {
						TVE_Dash.hideLoader();
						if ( result === "true" ) {
							var api = new ThriveOvation.models.AssetWizardConnection();
							api.set( connection );

							var apis = ThriveOvation.objects.Apis.findWhere( {connection: connection.connection} );

							if ( ! apis.get( 'connected' ) ) {
								apis.set( {connected: true} );

								ThriveOvation.objects.AssetConnection.add( api );
							}

							apis.set( {connection_instance: connection.connection_instance} );

							if ( ! ThriveOvation.objects.AssetConnection.find( function ( model ) {
									return model.get( 'connection' ) === connection.connection;
								} ) ) {
								ThriveOvation.objects.AssetConnection.add( api );
							}
							ThriveOvation.objects.AssetConnection.each( function ( model, index ) {
								model.set( 'active', connection.connection );
							} );
							var v = new ThriveOvation.views.AssetConnections( {
								collection: ThriveOvation.objects.AssetConnection,
								el: $( '#tvo-email-connection-wrap' )
							} );
							v.render();

							self.$el.find( '.tvo-connection-name-success' ).text( connection.connection );

							self.gotoStep( 2 );

						} else {
							$( '.tvo-delivery-connection' ).find( '.error' ).remove();
							TVE_Dash.err( JSON.parse( result ), 2000 );
						}

					} );
				}


			},
			mapProprieties: function ( data ) {
				var value = {connection: {}, connection_instance: {}};
				data.map( function ( obj ) {
					if ( obj.name == 'api' ) {
						value.connection = obj.value;
					} else {
						var name = obj.name.match( /\[(.*?)\]/ )[1];

						value.connection_instance[name] = obj.value
					}
				} );

				return value;
			}
		} );

		ThriveOvation.views.AssetSelector = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'api/connections/selector' ),
			className: 'tvd-col tvd-s3 tvd-center-align',
			render: function () {
				this.$el.html( this.template( {item: this.model.toJSON()} ) );
				return this;
			}
		} );

	} );
})
( jQuery );