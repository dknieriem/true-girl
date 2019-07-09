<?php

WPV_Editor_Title::on_load();

class WPV_Editor_Title{
	
	static function on_load() {
		// Register the section in the editor pages
		add_action( 'wpv_action_view_editor_section_title',		array( 'WPV_Editor_Title', 'wpv_editor_section_title' ), 10, 4 );
		add_action( 'wpv_action_wpa_editor_section_title',		array( 'WPV_Editor_Title', 'wpv_editor_section_title' ), 10, 4 );
		// AJAX management
		add_action( 'wp_ajax_wpv_update_title',					array( 'WPV_Editor_Title', 'wpv_update_title_callback' ) );
		add_action( 'wp_ajax_wpv_update_description',			array( 'WPV_Editor_Title', 'wpv_update_description_callback' ) );
        // Render the trash button
		add_filter( 'wpv_filter_wpv_admin_add_editor_trash_button', array( 'WPV_Editor_Title', 'wpv_maybe_add_trash_button' ) );
	}
	
	static function wpv_editor_section_title( $view_settings, $view_id, $user_id, $view ) {
		?>
		<div class="wpv-setting-container wpv-settings-title-and-desc js-wpv-settings-title-and-desc wpv-setting-container-no-title">
			<div class="wpv-setting">
				<div id="titlediv">
					<div id="titlewrap" class="wpv-titlewrap js-wpv-titlewrap">
						<label class="screen-reader-text js-title-reader" id="title-prompt-text" for="title"><?php _e('Enter title here','wpv-views'); ?></label>
						<input id="title" class="wpv-title js-title" type="text" name="post_title" size="30" value="<?php echo esc_attr( $view->post_title ); ?>" id="title" autocomplete="off">
						<span class="update-button-wrap js-wpv-update-button-wrap">
							<button
								class="button js-wpv-title-update button-secondary"
								data-nonce="<?php echo wp_create_nonce( 'wpv_view_title_nonce' ); ?>"
								data-success="<?php echo esc_attr( __('Title updated', 'wpv-views') ); ?>"
								data-unsaved="<?php echo esc_attr( __('Title not saved', 'wpv-views') ); ?>"
								disabled="disabled"
								style="height:38px;line-height:36px;"
							><?php _e('Update', 'wpv-views'); ?></button>
						</span>
					</div>
					<span class="wpv-message-container js-wpv-message-container-title"></span>
				</div>

				<div id="edit-slug-box" class="wpv-slug-container js-wpv-slug-container">
					<label for="wpv-slug"><?php _e('Slug:', 'wpv-views'); ?>
					<?php
					if (
						isset( $view_settings['view-query-mode'] ) 
						&& ( 'normal' ==  $view_settings['view-query-mode'] )
					) {
						?>
						<span id="editable-post-name" title="<?php _e('Click to edit the View slug', 'wpv-views'); ?>" class="js-wpv-edit-slug-toggle"><?php echo esc_attr( $view->post_name ); ?></span>
						<?php
					} else {
						?>
						<span id="editable-post-name" title="<?php _e('Click to edit the WordPress Archive slug', 'wpv-views'); ?>" class="js-wpv-edit-slug-toggle"><?php echo esc_attr( $view->post_name ); ?></span>
						<?php
					}
					?>
					<input id="wpv-slug" class="js-wpv-edit-slug-toggle js-wpv-slug" type="text" style="display:none" value="<?php echo esc_attr( $view->post_name ); ?>" />
					<span class="js-wpv-inline-edit">
						<span class="js-wpv-message-container-slug"></span>
						<button class="button-secondary js-wpv-edit-slug js-wpv-edit-slug-toggle"><?php _e('Edit', 'wpv-views'); ?></button>
						<button class="button-secondary js-wpv-edit-slug-update js-wpv-edit-slug-toggle"
								data-nonce="<?php echo wp_create_nonce( 'wpv_view_change_post_name' ); ?>"
								data-success="<?php echo esc_attr( __('Slug changed', 'wpv-views') ); ?>"
								data-unsaved="<?php echo esc_attr( __('Slug not changed', 'wpv-views') ); ?>"
								data-state="edit"
						style="display:none">
						<?php _e('OK', 'wpv-views'); ?>
						</button>
						<a href="#" class="js-wpv-edit-slug-cancel js-wpv-edit-slug-toggle" style="display:none"><?php _e('Cancel', 'wpv-views'); ?></a>
					</span>
					<?php
					if (
						isset( $view_settings['view-query-mode'] ) 
						&& ( 'normal' ==  $view_settings['view-query-mode'] )
					) {
						$native_redirect = admin_url( 'admin.php?page=views');
					} else {
						$native_redirect = admin_url( 'admin.php?page=view-archives');
					}
					?>
					<span class="wpv-action-secondary">
                        <?php
                        $add_trash_button = apply_filters( 'wpv_filter_wpv_admin_add_editor_trash_button', true );
                        if ( $add_trash_button ) {
                        ?>
                            <button class="button-secondary js-wpv-change-view-status"
                                    data-statusto="trash"
                                    data-success="<?php echo esc_attr( __('WPA moved to trash', 'wpv-views') ); ?>"
                                    data-unsaved="<?php echo esc_attr( __('WPA not moved to trash', 'wpv-views') ); ?>"
                                    data-redirect="<?php echo isset($_GET['ref']) && $_GET['ref'] === 'dashboard' ? admin_url( 'admin.php?page=toolset-dashboard' ) : $native_redirect; ?>"
                                    data-nonce="<?php echo wp_create_nonce( 'wpv_view_change_status' ); ?>">
							<i class="icon-trash fa fa-trash"></i> <?php _e('Move to trash', 'wpv-views'); ?>
						</button>
                        <?php
                        }
                        ?>

					</span>
					<?php
					$view_description = get_post_meta( $view_id, '_wpv_description', true );
					if (
						! isset( $view_description )
						|| empty( $view_description )
					) {
					?>
					<span class="wpv-action-secondary">
						<button class="js-wpv-description-toggle button-secondary" >
							<i class="icon-align-left fa fa-align-left"></i> <?php _e('Add description', 'wpv-views'); ?>
						</button>
					</span>
					<?php
					}
					?>
				</div>
				<div class="js-wpv-description-container wpv-description-container<?php echo ( isset( $view_description ) && !empty( $view_description ) ) ? '' : ' hidden'; ?>">
					<p>
						<label for="wpv-description">
						<?php
						if (
							isset( $view_settings['view-query-mode'] ) 
							&& ( 'normal' ==  $view_settings['view-query-mode'] )
						) {
							_e('Describe this View', 'wpv-views' );
						} else {
							_e('Describe this WordPress Archive', 'wpv-views' );
						}
						?>
						</label>
					</p>
					<p>
						<textarea id="wpv-description" class="js-wpv-description" name="_wpv_settings[view_description]" cols="72" rows="4" autocomplete="off"><?php echo ( isset( $view_description ) ) ? esc_html( $view_description ) : ''; ?></textarea>
					</p>
					<p class="update-button-wrap js-wpv-update-button-wrap">
						<span class="js-wpv-message-container-description"></span>
						<button
							class="button button-secondary js-wpv-description-update"
							data-nonce="<?php echo wp_create_nonce( 'wpv_view_description_nonce' ); ?>"
							data-success="<?php echo esc_attr( __('Title and description updated', 'wpv-views') ); ?>"
							data-unsaved="<?php echo esc_attr( __('Title and description not saved', 'wpv-views') ); ?>"
							disabled="disabled"
						><?php _e('Update', 'wpv-views'); ?></button>
					</p>
				</div>

			</div> <!-- .wpv-setting -->
			<div class="toolset-video-box-wrap"></div>
		</div> <!-- .wpv-setting-container -->
		<?php
	}

	/**
	 * Filter the state of appearance of the trash button based on the 'in-iframe-for-layout' URL parameter that comes from Layouts.
	 *
	 * @param $state    bool    Views or WPA editor is loaded inside an iframe. This occurs when adding a Views or a WPA cell inside a Layout
	 *
	 * @return bool
	 *
	 * @since 2.3.0
	 */
	static function wpv_maybe_add_trash_button( $state ) {
		if ( isset( $_GET['in-iframe-for-layout'] ) ) {
			$state = false;
		}
		return $state;
	}
	
	/**
	* Save Views and WPA title and description section.
	*
	* Expects following $_POST variables:
	* - wpnonce
	* - id
	* - title
	* - slug
	* - description
	* - is_title_escaped
	*
	* @since unknown
	* @since 2.1		Moved to a static method
	*/
	
	static function wpv_update_title_callback() {
		wpv_ajax_authenticate( 'wpv_view_title_nonce', array( 'type_of_death' => 'data' ) );

		$view_id = intval( wpv_getpost( 'id', 0 ) );

		// This is full Views, so we will allways get WPV_View, WPV_WordPress_Archive or null.
		$view = WPV_View_Base::get_instance( $view_id );

		// Fail if the View/WPA doesn't exist.
		if ( null == $view ) {
			wp_send_json_error( array(
				'type' => 'id',
				'message' => __( 'Wrong or missing ID.', 'wpv-views' )
			) );
		}

		// Try to update all three properties at once.
		$transaction_result = $view->update_transaction( array(
			'title' => wpv_getpost( 'title' ),
	//        'slug' => wpv_getpost( 'slug' ),
	//        'description' => wpv_getpost( 'description' )
		) );

		// On failure, return the first available error message (there should be only one anyway).
		if ( ! $transaction_result['success'] ) {
			$error_message = wpv_getarr( $transaction_result, 'first_error_message', __( 'An unexpected error happened.', 'wpv-views' ) );
			wp_send_json_error( array( 'type' => 'update', 'message' => $error_message ) );
		}

		// Success.

		// Use special success message if title was changed by escaping in JS.
		$is_title_escaped = intval( wpv_getpost( 'is_title_escaped', 0 ) );
		if ( $is_title_escaped ) {
			$success_message = __( 'We escaped the title before saving.', 'wpv-views' );
			wp_send_json_success( array( 'id' => $view_id, 'message' => $success_message ) );
		}

		wp_send_json_success( array( 'id' => $view_id ) );
	}
	
	/**
	* Save Views and WPA title and description section.
	*
	* Expects following $_POST variables:
	* - wpnonce
	* - id
	* - title
	* - slug
	* - description
	* - is_description_escaped
	*
	* @since unknown
	* @since 2.1		Moved to a static method
	*/
	
	static function wpv_update_description_callback() {

		wpv_ajax_authenticate( 'wpv_view_description_nonce', array( 'type_of_death' => 'data' ) );

		$view_id = intval( wpv_getpost( 'id', 0 ) );

		// This is full Views, so we will allways get WPV_View, WPV_WordPress_Archive or null.
		$view = WPV_View_Base::get_instance( $view_id );

		// Fail if the View/WPA doesn't exist.
		if ( null == $view ) {
			wp_send_json_error( array(
				'type' => 'id',
				'message' => __( 'Wrong or missing ID.', 'wpv-views' )
			) );
		}

		$transaction_result = $view->update_transaction( array(
			'description' => wpv_getpost( 'description' )
		) );

		// On failure, return the first available error message (there should be only one anyway).
		if ( ! $transaction_result['success'] ) {
			$error_message = wpv_getarr( $transaction_result, 'first_error_message', __( 'An unexpected error happened.', 'wpv-views' ) );
			wp_send_json_error( array( 'type' => 'update', 'message' => $error_message ) );
		}

		// Success.
		wp_send_json_success( array( 'id' => $view_id ) );
	}
	
}