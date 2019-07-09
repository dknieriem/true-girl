<?php

/**
 * Class WPV_Shortcode_Post_Body
 *
 * @since 2.5.0
 */
class WPV_Shortcode_Post_Body implements WPV_Shortcode_Interface {

	const SHORTCODE_NAME = 'wpv-post-body';

	/**
	 * @var array
	 */
	private $shortcode_atts = array(
		'item'         => null, // post
		'id'           => null, // synonym for 'item'
		'post_id'      => null, // synonym for 'item'
		'view_template'    => 'None',
		'output'       => 'normal',
		'suppress_filters' => 'false'
	);
	
	/**
	 * @var array
	 */
	private $infinite_loop_keys = array();

	/**
	 * @var string|null
	 */
	private $user_content;
	
	/**
	 * @var array
	 */
	private $user_atts;


	/**
	 * @var Toolset_Shortcode_Attr_Interface
	 */
	private $item;

	/**
	 * WPV_Shortcode_Post_Body constructor.
	 *
	 * @param Toolset_Shortcode_Attr_Interface $item
	 */
	public function __construct(
		Toolset_Shortcode_Attr_Interface $item
	) {
		$this->item  = $item;
	}

	/**
	* Get the shortcode output value.
	*
	* @param $atts
	* @param $content
	*
	* @return string
	*
	* @since 2.5.0
	*/
	public function get_value( $atts, $content = null ) {
		$this->user_atts    = shortcode_atts( $this->shortcode_atts, $atts );
		$this->user_content = $content;

		if ( ! $item_id = $this->item->get( $this->user_atts ) ) {
			// no valid item
			throw new WPV_Exception_Invalid_Shortcode_Attr_Item();
		}
		
		$out = '';
		
		$item = get_post( $item_id );

		// Adjust for WPML support
		// If WPML is enabled, $item_id should contain the right ID for the current post in the current language
		// However, if using the id attribute, we might need to adjust it to the translated post for the given ID
		$item_id = apply_filters( 'translate_object_id', $item_id, $item->post_type, true, null );
		
		global $post;
		$post_switched = false;
		$post_cloned = null;
		
		if ( $post->ID != $item->ID ) {
			$post_switched = true;
			$post_cloned = $post;
			$post = $item;
		}


		if ( post_password_required( $post ) ) {
			$post_protected_password_form = get_the_password_form( $post );

			/**
			* Filter wpv_filter_post_protected_body
			*
			* @param (string) $post_protected_password_form The default WordPress password form
			* @param (object) $post The post object to which this shortcode is related to
			* @param (array) $atts The array of attributes passed to this shortcode
			*
			* @return (string)
			*
			* @since 1.7.0
			*/

			return apply_filters( 'wpv_filter_post_protected_body', $post_protected_password_form, $post, $atts );
		}

		do_action( 'wpv_before_shortcode_post_body' );
		
		global $WP_Views, $WPV_templates, $WPVDebug;
		$id = '';
		$old_override = null;

		if ( $this->user_atts['suppress_filters'] == 'true' ) {
			$suppress_filters = true;
		} else {
			$suppress_filters = false;
		}

		if ( isset( $atts['view_template'] ) ) {
			if ( 
				isset( $post->view_template_override ) 
				&& $post->view_template_override != '' 
			) {
				$old_override = $post->view_template_override;
			}
			$post->view_template_override = $atts['view_template'];
			$id = $post->view_template_override;
		}
		if ( strtolower( $id ) == 'none' ) {
			$ct_id = $id;
			$output_mode = $this->user_atts['output'];
		} else {
			$ct_id = $WPV_templates->get_template_id( $id );
			$output_mode = 'normal';
		}
		
		// If the view_template value is not "None" and does not match a Content Template, restore and return
		// Remember that we must support no view_template attribute too! Backwards compatibility.
		if ( 
			$id != '' 
			&& $ct_id === 0 
		) {
			if ( isset( $post->view_template_override ) ) {
				if ( $old_override ) {
					$post->view_template_override = $old_override;
				} else {
					unset( $post->view_template_override );
				}
			}

			do_action( 'wpv_after_shortcode_post_body' );
			return;
		}
		
		$view_settings = apply_filters( 'wpv_filter_wpv_get_view_settings', array() );
		$current_item_type = apply_filters( 'wpv_filter_wpv_get_query_type', 'posts' );
		$current_stop_infinite_loop_key = $current_item_type . '-';
		
		$WPVDebug->wpv_debug_start( $ct_id, $atts, 'content-template' );
		$WPVDebug->set_index();
		
		if ( $WPVDebug->user_can_debug() ) {
			switch( $current_item_type ) {
				case 'posts':
					$WPVDebug->add_log( 'content-template', $post );
					break;
				case 'taxonomy':
					$WPVDebug->add_log( 'content-template', $WP_Views->taxonomy_data['term'] );
					break;
				case 'users':
					$WPVDebug->add_log( 'content-template', $WP_Views->users_data['term'] );
					break;
			}
		}
		
		// Here
		
		
		if ( 
			$post->post_type != 'view' 
			&& $post->post_type != 'view-template' 
		) {

			// Set the output mode for this shortcode (based on the "output" attribute if the "view_template" attribute is set to None, the selected Template output mode will override this otherwise)
			// normal (default) - restore wpautop, only needed if has been previously removed
			// raw - remove wpautop and set the $wpautop_was_active to true
			// inherit - when used inside a Content Template, inherit its wpautop setting; when used outside a Template, inherit from the post itself (so add format, just like "normal")
			// NOTE BUG: we need to first remove_wpautop because for some reason not doing so switches the global $post to the top_current_page one
			$wpautop_was_removed = $WPV_templates->is_wpautop_removed();
			$wpautop_was_active = false;
			$WPV_templates->remove_wpautop();

			if ( $wpautop_was_removed ) { // if we had disabled wpautop, we only need to enable it again for mode "normal" in view_template="None" (will be overriden by Template settings if needed)
				if ( $output_mode == 'normal' ) {
					$WPV_templates->restore_wpautop('');
				}
			} else { // if wpautop was not disabled, we need to revert its state, but just for modes "normal" and "inherit"; we will enable it globally again after the main procedure
				$wpautop_was_active = true;
				if ( $output_mode == 'normal' || $output_mode == 'inherit' ) {
					$WPV_templates->restore_wpautop('');
				}
			}

			// Remove the icl language switcher to stop WPML from add the
			// "This post is avaiable in XXXX" twice.
			// Before WPML 3.6.0
			add_filter( 'icl_post_alternative_languages', '__return_empty_string', 999 );
			// After WPML 3.6.0
			add_filter( 'wpml_ls_post_alternative_languages', '__return_empty_string', 999 );

			// Check for infinite loops where a View template contains a
			// wpv-post-body shortcode without a View template specified
			// or a View template refers to itself directly or indirectly.
			switch( $current_item_type ) {
				case 'posts':
					$current_stop_infinite_loop_key .= (string) $post->ID . '-';
					break;
				case 'taxonomy':
					$current_stop_infinite_loop_key .= (string) $WP_Views->taxonomy_data['term']->term_id . '-';
					break;
				case 'users':
					$current_stop_infinite_loop_key .= (string) $WP_Views->users_data['term']->ID . '-';
					break;
			}
			if ( isset( $post->view_template_override ) ) {
				$current_stop_infinite_loop_key .= $post->view_template_override;
			} else {
				// This only hapens in the unsupported scenario of no view_template attribute
				$current_stop_infinite_loop_key .= '##no#view_template#attribute##';
			}
			
			if ( ! isset( $this->infinite_loop_keys[ $current_stop_infinite_loop_key ] ) ) {
				
				/**
				 * Prevent infinite loops: check the looped object ID and view_template_override
				 * which is based on the view_template attribute 
				 * so we can not pass the same object and same CT/None more than once.
				 */
				$this->infinite_loop_keys[ $current_stop_infinite_loop_key ] = 1;

				if ( $suppress_filters ) {

					/**
					* wpv_filter_wpv_the_content_suppressed
					*
					* Mimics the the_content filter on wpv-post-body shortcodes with attribute suppress_filters="true"
					* Check WPV_template::init()
					*
					* Since 1.8.0
					*/

					$out .= apply_filters( 'wpv_filter_wpv_the_content_suppressed', $post->post_content );

				} else {
					$filter_state = new WPV_WP_filter_state( 'the_content' );
					$out .= apply_filters('the_content', $post->post_content);
					$filter_state->restore( );
				}

				unset( $this->infinite_loop_keys[ $current_stop_infinite_loop_key ] );
				
			} else {
				
				/**
				 * We are inside an infinite loop: 
				 * break early and add a debug message. 
				 * add some backtrace to the console log.
				 *
				 * Note that we do not return the native post contnt either.
				 */
				 
				if ( current_user_can( 'manage_options' ) ) {
				 
					$infinite_loop_debug = '';
					$infinite_loop_debug .= '<p style="font-weight:bold !important;color: red !important;">'
						. __( 'Content not displayed because it produces an infinite loop.', 'wpv-views' )
						. '<br />';
					
					$infinite_loop_debug .= isset( $atts['view_template'] ) ? 
						sprintf(
							__( 'The wpv-post-body shortcode was called more than once with the attribute view_template="%1$s" over the post "%2$s", triggering an infinite loop.', 'wpv-views' ),
							$atts['view_template'],
							$post->post_title
						) : 
						sprintf(
							__( 'The wpv-post-body shortcode was called more than once over the post "%1$s" and without a \'view_template\' attribute, triggering an infinite loop.', 'wpv-views' ),
							$post->post_title
						);
					
					$infinite_loop_debug .= '</p>';
					
					$out .= $infinite_loop_debug;
					
				}
				
			}
			
			// Before WPML 3.6.0
			remove_filter( 'icl_post_alternative_languages', '__return_empty_string', 999 );
			// After WPML 3.6.0
			remove_filter( 'wpml_ls_post_alternative_languages', '__return_empty_string', 999 );

			// Restore the wpautop configuration only if is has been changed
			if ( $wpautop_was_removed ) {
				$WPV_templates->remove_wpautop();
			} else if ( $wpautop_was_active ) {
				$WPV_templates->restore_wpautop('');
			}
		}
		
		
		
		// Here end
		
		
		if ( isset( $post->view_template_override ) ) {
			if ( $old_override ) {
				$post->view_template_override = $old_override;
			} else {
				unset( $post->view_template_override );
			}
		}

		$WPVDebug->add_log_item( 'output', $out );
		$WPVDebug->wpv_debug_end();

		apply_filters( 'wpv_shortcode_debug', 'wpv-post-body', json_encode( $this->user_atts ), '', 'Output shown in the Nested elements section' );
		
		do_action( 'wpv_after_shortcode_post_body' );
		
		if ( $post_switched ) {
			$post = $post_cloned;
		}

		return $out;
	}
	
	
}