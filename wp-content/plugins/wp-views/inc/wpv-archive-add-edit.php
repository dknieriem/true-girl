<?php
/*
* Added extra files to have the old and new editors working together
* Once we are done, those extra files will be merged with the old ones after cleaning no longer needed functions
*/

/* General TODOs
* TODO: Create extra files to make this screen modular. STATUS: 80%
*/

// Loop selection files
require_once WPV_PATH . '/inc/sections/wpv-section-loop-selection.php';
// Layout section files
require_once WPV_PATH . '/inc/sections/wpv-section-layout-template.php';

/**
* WordPress Archives edit screen
*/

function views_archive_redesign_html() {
	global $post;
	
	if ( 
		isset( $_GET['view_id'] ) 
		&& is_numeric( $_GET['view_id'] ) 
	) {
		do_action('views_edit_screen');
		$view_id = (int) $_GET['view_id'];
		$view = get_post( $view_id, OBJECT, 'edit' );
		if ( null == $view ) {
			wpv_die_toolset_alert_error( __( 'You attempted to edit a WordPress Archive that doesn&#8217;t exist. Perhaps it was deleted?', 'wpv-views' ) );
		} elseif ( 'view'!= $view->post_type ) {
			wpv_die_toolset_alert_error( __( 'You attempted to edit a WordPress Archive that doesn&#8217;t exist. Perhaps it was deleted?', 'wpv-views') );
		} else {
			$view_settings_stored = get_post_meta( $view_id, '_wpv_settings', true );
			
			$wpv_filter_wpv_get_view_settings_args = array(
				'override_view_settings' => false, 
				'extend_view_settings' => false, 
				'public_view_settings' => false
			);
			
			$view_settings = apply_filters( 'wpv_filter_wpv_get_view_settings', array(), $view_id, $wpv_filter_wpv_get_view_settings_args );
			
			$view_layout_settings_stored = get_post_meta( $view_id, '_wpv_layout_settings', true );
			
			/**
			* wpv_view_layout_settings
			*
			* Internal filter to set some View layout settings that will overwrite the ones existing in the _wpv_layout_settings postmeta
			* Only used to set default values that need to be there on the returned array,, but may not be there for legacy reasons
			* Use wpv_filter_override_view_layout_settings to override View layout settings
			*
			* @param $view_layout_settings_stored (array) Unserialized array of the _wpv_layout_settings postmeta
			* @param $view_id (integer) The View ID
			*
			* @return $view_layout_settings (array) The View layout settings
			*
			* @since 1.8.0
			*/
			
			$view_layout_settings = apply_filters( 'wpv_view_layout_settings', $view_layout_settings_stored, $view_id );
			
			if ( isset( $view_settings['view-query-mode'] )
				&& (
					'archive' ==  $view_settings['view-query-mode']
					|| 'layouts-loop' ==  $view_settings['view-query-mode'] // For elements coming from the Layouts post loop cell
				) 
			) {
				$post = $view;
				if ( get_post_status( $view_id ) == 'trash' ) {
					wpv_die_toolset_alert_error( __( 'You can’t edit this WordPress Archive because it is in the Trash. Please restore it and try again.', 'wpv-views' ) );
				}
			} else {
				wpv_die_toolset_alert_error( __( 'You attempted to edit a WordPress Archive that doesn&#8217;t exist. Perhaps it was deleted?', 'wpv-views' ) );
			}
		}
	} else {
		wpv_die_toolset_alert_error( __( 'You attempted to edit a WordPress Archive that doesn&#8217;t exist. Perhaps it was deleted?', 'wpv-views' ) );
	}
	$user_id = get_current_user_id();

	

	/**
	* Screen Options tab
	*/
	do_action( 'wpv_action_wpa_editor_screen_options', $view_settings, $view_layout_settings, $view_id, $user_id, $view );
	/**
	* Actual WordPress Archive edit page
	*/
	?>
	<div class="wrap toolset-views">
		<input id="post_ID" class="js-post_ID" type="hidden" value="<?php echo esc_attr( $view_id ); ?>" data-nonce="<?php echo wp_create_nonce( 'wpv_view_edit_general_nonce' ); ?>" />
        <input id="toolset-edit-data" type="hidden" value="<?php echo esc_attr( $view_id ); ?>" data-plugin="views" />
		<h1>
			<?php
			if ('archive' ==  $view_settings['view-query-mode']) {
				echo __('Edit WordPress Archive','wpv-views');
			} else {
				echo __('Edit Layouts Loop View','wpv-views');
			}
			?>
		</h1>
		<?php
		if ( isset( $_GET['in-iframe-for-layout'] ) ) {
			$in_iframe = 'yes';
		} else {
			$in_iframe = '';
		}
		
		?>
		<input type="hidden" class="js-wpv-display-in-iframe" value="<?php echo esc_attr( $in_iframe ); ?>" />
		<div id="js-wpv-general-actions-bar" class="wpv-settings-save-all wpv-general-actions-bar wpv-setting-container js-wpv-no-lock js-wpv-general-actions-bar">
			<p class="update-button-wrap js-wpv-update-button-wrap">
				<button class="button-secondary button button-large js-wpv-view-save-all"
						disabled="disabled"
						data-success="<?php echo esc_attr( __('View saved', 'wpv-views') ); ?>"
						data-unsaved="<?php echo esc_attr( __('View not saved', 'wpv-views') ); ?>">
					<?php _e( 'Save all sections at once', 'wpv-views' ); ?>
				</button>
			</p>
			<span class="wpv-message-container js-wpv-message-container"></span>
			<div class="wpv-view-save-all-progress js-wpv-view-save-all-progress"></div>
		</div>
		<input type="hidden" name="_wpv_settings[view-query-mode]" value="archive" />
		
		<div class="wpv-title-section">
			<?php 
			
			/**
			* wpv_action_wpa_editor_section_title
			*
			* Hook for sections in the Title metasection.
			*
			* @since 2.1
			*/
			do_action( 'wpv_action_wpa_editor_section_title', $view_settings, $view_id, $user_id, $view ); 
			?>


		</div> <!-- .wpv-title-section -->
		
		<div class="wpv-query-section">
			
			<span class="wpv-section-title"><?php _e('The Loops Selection section determines which listing page to customize','wpv-views') ?></span>
			
			<div class="js-wpv-metasection-message-container js-wpv-metasection-message-container-query"></div>
			
			<?php 
			
			/**
			* wpv_action_wpa_editor_section_query
			*
			* Hook for sections in the Query metasection.
			*
			* @since 2.1
			*/
			do_action( 'wpv_action_wpa_editor_section_query', $view_settings, $view_id, $user_id );
			?>
		</div> <!-- .wpv-query-section -->
		
		<div class="wpv-filter-section">
			
			<span class="wpv-section-title"><?php _e('The Filter section lets you set up a custom search, which lets visitors control the WordPress Archive results','wpv-views') ?></span>
			
			<div class="js-wpv-metasection-message-container js-wpv-metasection-message-container-filter"></div>
			
			<?php 
			
			/**
			* wpv_action_wpa_editor_section_filter
			*
			* Hook for sections in the Filter metasection.
			*
			* @since 2.1
			*/
			do_action( 'wpv_action_wpa_editor_section_filter', $view_settings, $view_id, $user_id ); 
			?>
		</div>

		<?php
		/*
		* Loop selection - Priority 10
		*/
		?>

		<div class="wpv-layout-section">
			
			<span class="wpv-section-title"><?php _e( 'The Loop Output section determines how the content displays, including pagination', 'wpv-views' ) ?></span>
			
			<div class="js-wpv-metasection-message-container js-wpv-metasection-message-container-layout"></div>
			
			<?php
			$data = wpv_get_view_layout_introduction_data();
			wpv_toolset_help_box($data);
			?>
			<?php 
			
			/**
			* wpv_action_wpa_editor_section_layout
			*
			* Hook for sections in the first half of the Loop Output metasection.
			*
			* @since 2.1
			*/
			do_action( 'wpv_action_wpa_editor_section_layout', $view_settings, $view_layout_settings, $view_id, $user_id );
			/**
			* The action 'view-editor-section-layout' is now deprecated, leave it for backwards compatibility
			*
			* @deprecated 2.1
			*/
			do_action( 'view-editor-section-layout', $view_settings, $view_layout_settings, $view_id, $user_id );
			
			/**
			* wpv_action_wpa_editor_section_extra
			*
			* Hook for sections in the second half of the Loop Output metasection.
			*
			* @since 2.1
			*/
			do_action( 'wpv_action_wpa_editor_section_extra', $view_settings, $view_id, $user_id ); 
			/**
			* The action 'view-editor-section-extra' is now deprecated, leave it for backwards compatibility
			*
			* @deprecated 2.1
			*/
			do_action( 'view-editor-section-extra', $view_settings, $view_id, $user_id );
			?>
		</div> <!-- .wpv-layout-section -->

	</div><!-- .toolset-views -->
	<?php
		
		/**
		* wpv_action_wpa_editor_section_hidden
		*
		* Show hidden container for dialogs, pointers and messages that need to be taken from an existing HTML element
		*
		* @param $args['settings']					$view_settings
		* @param $args['settings_stored']			$view_settings_stored
		* @param $args['layout_settings']			$view_layout_settings
		* @param $args['layout_settings_stored']	$view_layout_settings_stored
		* @param $args['id']						$view_id
		* @param $args['user_id']					$user_id
		*
		* @note			You can use the .popup-window-container classname to hide the containers added here
		*
		* @since 2.1
		*/
		
		do_action( 'wpv_action_wpa_editor_section_hidden', array(
				'settings'					=> $view_settings,
				'settings_stored'			=> $view_settings_stored,
				'layout_settings'			=> $view_layout_settings,
				'layout_settings_stored'	=> $view_layout_settings_stored,
				'id'						=> $view_id,
				'user_id'					=> $user_id			
			)
		);
		
		/**
		* view-editor-section-hidden
		*
		* Show hidden container for dialogs, pointers and messages that need to be taken from an existing HTML element
		*
		* @param $view_settings
		* @param $view_laqyout_settings
		* @param $view_id
		* @param $user_id
		*
		* @note that you can use the .popup-window-container classname to hide the containers added here
		*
		* @since 1.7
		*
		* @deprecated 2.1	Use wpv_action_wpa_editor_section_hidden instead
		*/
		
		do_action( 'view-editor-section-hidden', $view_settings, $view_layout_settings, $view_id, $user_id );
		
		if ( ! class_exists( '_WP_Editors' ) ) {
			require( ABSPATH . WPINC . '/class-wp-editor.php' );
		}
		_WP_Editors::wp_link_dialog();
		
		/**
		* wpv_action_wpa_editor_after_sections
		*
		* Final action to include additional data.
		* Used to generate the Types post relationship tree reference, as doing it too early fails because post types are managed as "inactive".
		*
		* @todo Move here the generation of filter validation rules.
		*
		* @param $view_settings
		* @param $view_laqyout_settings
		* @param $view_id
		* @param $user_id
		*
		* @since 2.1
		*/
		
		do_action( 'wpv_action_wpa_editor_after_sections', $view_settings, $view_layout_settings, $view_id, $user_id );
	?>
<?php }
