<?php
/**
 * Keep this file only for helpers
 */

/**
 * Thrive Ovation URL
 *
 * @param $file
 *
 * @return string TVO URL
 */
function tvo_plugin_url( $file = '' ) {
	return plugin_dir_url( dirname( __FILE__ ) ) . ltrim( $file, ' /' );
}

/**
 * wrapper over the wp_enqueue_style function
 * it will add the plugin version to the style link if no version is specified
 *
 * @param $handle
 * @param string|bool $src
 * @param array $deps
 * @param bool|string $ver
 * @param string $media
 */
function tvo_enqueue_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
	if ( $ver === false ) {
		$ver = TVO_VERSION;
	}
	wp_enqueue_style( $handle, $src, $deps, $ver, $media );
}

/**
 * wrapper over the wp_enqueue_script function
 * it will add the plugin version to the script source if no version is specified
 *
 * @param $handle
 * @param string $src
 * @param array $deps
 * @param bool $ver
 * @param bool $in_footer
 */
function tvo_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
	if ( $ver === false ) {
		$ver = TVO_VERSION;
	}

	wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
}

/**
 * Return complete url for route endpoint
 *
 * @param $endpoint string
 * @param $id int
 * @param $args array
 *
 * @return string
 */
function tvo_get_route_url( $endpoint, $id = 0, $args = array() ) {

	$url = get_rest_url() . TVO_REST_NAMESPACE . '/' . $endpoint;

	if ( ! empty( $id ) && is_numeric( $id ) ) {
		$url .= '/' . $id;
	}

	if ( ! empty( $args ) ) {
		add_query_arg( $args, $url );
	}

	return $url;
}

/**
 * @param $option
 *
 * @return array
 */
function tvo_get_default_values( $option ) {

	switch ( $option ) {
		case TVO_SETTINGS_OPTION:
			return array();
		case TVO_FILTERS_OPTION:
			return array(
				'show_hide_tags'      => 1,
				'show_hide_type'      => 1,
				'show_hide_status'    => 1,
				'testimonial_content' => 'summary',
			);
		default:
			return array();
	}
}

/**
 * @param $option_name
 * @param array $default_values
 *
 * @return array|mixed
 */
function tvo_get_option( $option_name, $default_values = array() ) {

	$option = maybe_unserialize( get_option( $option_name ) );

	if ( empty( $option ) ) {

		add_option( $option_name, $default_values );

		$option = $default_values;
	}

	return $option;

}

/**
 * Wrapper over the update option
 *
 * @param $option_name
 * @param array|object $value
 * @param boolean $serialize
 *
 * @return array|mixed
 */
function tvo_update_option( $option_name, $value ) {

	if ( empty( $option_name ) || empty( $value ) ) {
		return false;
	}

	$old_value = tvo_get_option( $option_name );

	/* Check to see if the old value is the same as the new one */
	if ( is_array( $old_value ) && is_array( $value ) ) {
		$diff = array_diff_assoc( $old_value, $value ) + array_diff_assoc( $value, $old_value );
	} elseif ( is_object( $old_value ) && is_object( $value ) ) {
		$diff = array_diff_assoc( get_object_vars( $old_value ), get_object_vars( $value ) ) + array_diff_assoc( get_object_vars( $value ), get_object_vars( $old_value ) );
	} else {
		$diff = ! ( $old_value == $value );
	}

	/* If the new value is the same with the old one, return true and don't update */
	if ( empty( $diff ) ) {
		return true;
	}

	return update_option( $option_name, $value );

}

/**
 * Returns all the statuses that a testimonial can have
 *
 * @return array
 */
function tvo_testimonial_statuses() {
	return array(
		TVO_STATUS_READY_FOR_DISPLAY,
		TVO_STATUS_AWAITING_APPROVAL,
		TVO_STATUS_AWAITING_REVIEW,
		TVO_STATUS_REJECTED,
	);
}

/**
 * Returns the status text depending on status id
 *
 * @param int $status_id
 *
 * @return bool|string|void
 */
function tvo_get_testimonial_status_text( $status_id = 0 ) {

	switch ( $status_id ) {
		case TVO_STATUS_READY_FOR_DISPLAY:
			return __( 'Ready for Display', TVO_TRANSLATE_DOMAIN );
			break;
		case TVO_STATUS_AWAITING_APPROVAL:
			return __( 'Awaiting Approval', TVO_TRANSLATE_DOMAIN );
			break;
		case TVO_STATUS_AWAITING_REVIEW:
			return __( 'Awaiting Review', TVO_TRANSLATE_DOMAIN );
			break;
		case TVO_STATUS_REJECTED:
			return __( 'Rejected', TVO_TRANSLATE_DOMAIN );
			break;
		default:
			break;
	}

	return false;
}

/**
 * Returns the source text depending on the source id
 *
 * @param string $source
 *
 * @return string|void
 */
function tvo_get_testimonial_source_text( $source = TVO_SOURCE_PLUGIN ) {

	$return = __( 'via ', TVO_TRANSLATE_DOMAIN );

	switch ( $source ) {
		case TVO_SOURCE_COMMENTS:
			$return .= __( 'Wordpress comments', TVO_TRANSLATE_DOMAIN );
			break;
		case TVO_SOURCE_SOCIAL_MEDIA:
			$return .= __( 'social media', TVO_TRANSLATE_DOMAIN );
			break;
		case TVO_SOURCE_DIRECT_CAPTURE:
			$return .= __( 'direct capture', TVO_TRANSLATE_DOMAIN );
			break;
		case TVO_SOURCE_COPY:
			$return .= __( 'copy', TVO_TRANSLATE_DOMAIN );
			break;
		case TVO_SOURCE_PLUGIN:
		default:
			$return = __( 'manually added', TVO_TRANSLATE_DOMAIN );
			break;
	}

	return $return;
}


/**
 * Returns an array of comment IDs from testimonials
 *
 * @param array $testimonials
 *
 * @return array
 */
function tvo_comment_check_testimonial( $testimonials = array() ) {

	$return = array();

	foreach ( $testimonials as $testimonial ) {
		if ( ! empty( $testimonial->_tvo_testimonial_attributes['comment_id'] ) ) {
			$return[] = $testimonial->_tvo_testimonial_attributes['comment_id'];
		}
	}

	return $return;
}


/**
 * appends the WordPress tables prefix and the default TVO_DB_PREFIX to the table name
 *
 * @param string $table
 *
 * @return string the modified table name
 */
function tvo_table_name( $table ) {
	global $wpdb;

	return $wpdb->prefix . TVO_DB_PREFIX . $table;
}

/**
 * gets the email template for confirmation mail *
 * returns default email template if no email template has been set. *
 * @return mixed
 */
function tvo_get_email_template() {
	$template = get_option( TVO_EMAIL_TEMPLATE_OPTION, TVO_DEFAULT_EMAIL_TEMPLATE );
	if ( is_array( $template ) ) {
		return '';
	}

	return $template;
}

/**
 * gets the email subject for confirmation mail
 * returns default email subject if no email subject has been set.
 * @return mixed
 */
function tvo_get_email_template_subject() {
	$subject = get_option( TVO_EMAIL_TEMPLATE_SUBJECT_OPTION, TVO_DEFAULT_EMAIL_TEMPLATE_SUBJECT );
	if ( is_array( $subject ) ) {
		return '';
	}

	return $subject;
}

/**
 * get substring between two strings
 *
 * @return string
 */
function tvo_get_string_between( $string, $start, $end ) {
	$string = ' ' . $string;
	$ini    = strpos( $string, $start );
	if ( $ini == 0 ) {
		return '';
	}
	$ini += strlen( $start );
	$len = strpos( $string, $end, $ini ) - $ini;

	return substr( $string, $ini, $len );
}

/**
 * Checks if two string are equal
 *
 * @param string $string1
 * @param string $string2
 *
 * @return bool
 */
function tvo_are_strings_equal( $string1 = '', $string2 = '' ) {
	/*Note that this comparison is case sensitive.*/
	if ( strcmp( $string1, $string2 ) === 0 ) {
		return true;
	}

	return false;
}

/**
 * Prepares the testimonial meta before updating
 *
 * @param $testimonial_id
 * @param array $testimonial_values
 *
 * @return mixed
 */
function tvo_construct_testimonial_meta( $testimonial_id, $testimonial_values = array() ) {
	$tvo_testimonial_meta = get_post_meta( $testimonial_id, TVO_POST_META_KEY, true );

	if ( empty( $tvo_testimonial_meta ) ) {
		$tvo_testimonial_meta = array();
	}

	$tvo_testimonial_meta['email']       = $testimonial_values['email'];
	$tvo_testimonial_meta['role']        = $testimonial_values['role'];
	$tvo_testimonial_meta['website_url'] = $testimonial_values['website_url'];
	$tvo_testimonial_meta['picture_url'] = $testimonial_values['picture_url'];
	$tvo_testimonial_meta['name']        = $testimonial_values['name'];
	if ( ($testimonial_values['source'] == TVO_SOURCE_COPY && $testimonial_values['is_media_source'] ) || ($testimonial_values['source'] == TVO_SOURCE_SOCIAL_MEDIA &&  ! empty( $testimonial_values['comment_url'] ) ) ) {
		$tvo_testimonial_meta['media_url'] = $testimonial_values['comment_url'];
	}
	return $tvo_testimonial_meta;
}
/**
 * Return a structure of breadcrumbs containing title, url and descendants
 * @return array
 */
function tvo_get_default_breadcrumbs() {

	$plugin_url = menu_page_url( 'tvo_admin_dashboard', false );

	return array(
		array(
			'key'   => 'base',
			'title' => __( 'Thrive Dashboard', TVO_TRANSLATE_DOMAIN ),
			'url'   => menu_page_url( 'tve_dash_section', false ),
			'kids'  => array(
				array(
					'key'   => 'testimonials',
					'title' => __( 'Thrive Ovation', TVO_TRANSLATE_DOMAIN ),
					'url'   => $plugin_url,
					'kids'  => array(
						array(
							'key'   => 'settings',
							'title' => __( 'Settings', TVO_TRANSLATE_DOMAIN ),
							'url'   => $plugin_url . '#settings',
							'kids'  => false,
						),
						array(
							'key'   => 'testimonial',
							'title' => __( 'Edit Testimonial', TVO_TRANSLATE_DOMAIN ),
							'url'   => $plugin_url . '#testimonial',
							'kids'  => false,
						),
						array(
							'key'   => 'capture-shortcodes',
							'title' => __( 'Capture shortcodes', TVO_TRANSLATE_DOMAIN ),
							'url'   => $plugin_url . '#shortcodes/capture',
							'kids'  => false,
						),
						array(
							'key'   => 'display-shortcodes',
							'title' => __( 'Display shortcodes', TVO_TRANSLATE_DOMAIN ),
							'url'   => $plugin_url . '#shortcodes/display',
							'kids'  => false,
						),
						array(
							'key'   => 'socialimport',
							'title' => __( 'Social Media Import', TVO_TRANSLATE_DOMAIN ),
							'url'   => $plugin_url . '#socialimport',
							'kids'  => false,
						),
					),
				),
			),
		),
	);
}

/**
 * Saves an image from the URL to the system
 *
 * @param string $url
 * @param string $filename
 *
 * @return mixed
 */
function tvo_upload_image_to_media_library( $url = '', $filename = '' ) {
	/* get the image, and store it in your upload-directory */
	$uploaddir  = wp_upload_dir();
	$uploadfile = $uploaddir['path'] . '/' . $filename;

	$contents = wp_remote_fopen( $url );  // wp_remote_get
	$savefile = fopen( $uploadfile, 'w' );
	fwrite( $savefile, $contents );
	fclose( $savefile );

	/* insert the image into the media library */
	$wp_filetype = wp_check_filetype( basename( $filename ), null );

	$attachment = array(
		'post_mime_type' => $wp_filetype['type'],
		'post_title'     => $filename,
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	$attach_id = wp_insert_attachment( $attachment, $uploadfile );

	$attachment_url = wp_get_attachment_url( $attach_id );

	return $attachment_url;
}

/**
 * Constructs the image name
 *
 * @param string $user_name
 *
 * @return string
 */
function tvo_construct_social_image_name( $user_name = '' ) {
	$image_extension         = '.jpg';
	$six_digit_random_number = mt_rand( 100000, 999999 );

	return iconv( 'UTF-8', 'ASCII//IGNORE', str_replace( ' ', '_', strtolower( $user_name ) ) ) . '_' . $six_digit_random_number . $image_extension;
}

/**
 * Return default capture shortcode config
 * @return array
 */
function tvo_get_default_shortcode_config( $type ) {
	switch ( $type ) {
		case 'capture':
			$defaults = array(
				'template'             => 0,
				'name_label'           => __( 'Full Name', TVO_TRANSLATE_DOMAIN ),
				'title_label'          => __( 'Testimonial Title', TVO_TRANSLATE_DOMAIN ),
				'email_label'          => __( 'Email', TVO_TRANSLATE_DOMAIN ),
				'role_label'           => __( 'Role', TVO_TRANSLATE_DOMAIN ),
				'website_url_label'    => __( 'Website URL', TVO_TRANSLATE_DOMAIN ),
				'name_required'        => 1,
				'title_required'       => 0,
				'email_required'       => 0,
				'role_required'        => 0,
				'website_url_required' => 0,
				'website_url_display'  => 0,
				'role_display'         => 0,
				'title_display'        => 0,
				'image_display'        => 1,
				'questions'            => array(
					__( 'What was your experience with our product like?', TVO_TRANSLATE_DOMAIN )
				),
				'placeholders'         => array(),
				'questions_required'   => array( 1 ),
				'button_text'          => __( 'Submit', TVO_TRANSLATE_DOMAIN ),
				'on_success_option'    => 'message',
				'on_success'           => __( 'Thanks for submitting your testimonial.', TVO_TRANSLATE_DOMAIN ),
				'tags'                 => array(),
			);
			break;
		case 'display':
			$defaults = array(
				'template'         => 0,
				'tags'             => array(),
				'testimonials'     => array(),
				'show_role'        => 1,
				'show_site'        => 0,
				'show_title'       => 1,
				'type'             => 'display',
				'max_testimonials' => 0,
			);
			break;
		default:
			$defaults = array();
	}

	return $defaults;
}

/**
 * Gets the settings
 *
 * @return array|mixed
 */
function tvo_get_settings() {
	$default_settings = tvo_get_default_values( TVO_SETTINGS_OPTION );
	$settings         = tvo_get_option( TVO_SETTINGS_OPTION, $default_settings );

	return $settings;
}

/**
 * @param $permission
 * @param $delivery_service
 * @param array $email_data
 *
 * @return array
 */
function tvo_get_ask_permission_email_response( $delivery_service, $email_data = array(), $permission = 1 ) {
	$response = array( 'html' => '', 'button_text' => __( 'Add testimonial', TVO_TRANSLATE_DOMAIN ) );

	$landing_page_options = tvo_get_option( TVO_LANDING_PAGE_SETTINGS_OPTION );
	$email_template       = get_option( TVO_EMAIL_TEMPLATE_OPTION, false );
	$email_subject        = get_option( TVO_EMAIL_TEMPLATE_SUBJECT_OPTION, false );

	if ( $permission ) {
		if ( $delivery_service != false && ! empty( $landing_page_options ) && $email_template != false && $email_subject != false ) {
			$response['button_text'] = __( 'Send email to customer', TVO_TRANSLATE_DOMAIN );
			$response['html']        = __( 'Email preview', TVO_TRANSLATE_DOMAIN );
			$response['html'] .= '<a href="javascript:void(0);" onclick="tvo_refresh_preview_email();" class="tvd-margin-left"><span class="tvd-icon-loop2 tvd-margin-right-small"></span>' . __( 'Refresh', TVO_TRANSLATE_DOMAIN ) . '</a>';
			$response['html'] .= '<div class="tvo-preview-email-wrapper">' . tvo_process_approval_email_content( $email_template, $email_data ) . '</div>';
		} else {
			$response['html'] = '<span>' . __( "We can't send an approval email because you haven't configured your email settings. ", TVO_TRANSLATE_DOMAIN ) . '<a href="' . admin_url( 'admin.php?page=tvo_admin_dashboard#settings' ) . '" target="_blank">' . __( 'Click here to enable sending of email', TVO_TRANSLATE_DOMAIN ) . '</a>' . __( ' and after you are done ', TVO_TRANSLATE_DOMAIN ) . '<a href="javascript:void(0);" onclick="tvo_refresh_preview_email()">' . __( '  click here to refresh.', TVO_TRANSLATE_DOMAIN ) . '</a></span>';
		}
	}

	return $response;
}

/**
 * Get testimonial capture shortcode source id and name
 *
 * @param $testimonial_id
 *
 * @return array|null
 */
function tvo_get_testimonial_shortcode_source( $testimonial_id ) {
	$source = get_post_meta( $testimonial_id, 'tvo_shortcode_source', true );

	if ( ! empty( $source ) ) {
		$shortcode = get_post( $source );
		if ( ! empty( $shortcode ) ) {
			return array(
				'name' => $shortcode->post_title,
				'url'  => get_permalink( $shortcode->ID ),
			);
		}
	}

	return array(
		'name' => __( 'capture form', TVO_TRANSLATE_DOMAIN ),
		'url'  => '',
	);
}

/**
 * Construct customer email yes and no links
 *
 * @param array $data
 *
 * @return array
 */
function tvo_construct_yes_no_email_links( $data = array() ) {
	$landing_page_options = tvo_get_option( TVO_LANDING_PAGE_SETTINGS_OPTION );
	$links                = array( 'yes' => 'javascript:void(0);', 'no' => 'javascript:void(0);' );

	if ( ! empty( $data['id'] ) ) {
		if ( ! empty( $landing_page_options['approve'] ) ) {
			$links['yes'] = get_site_url() . '?tvo_status=approve&tvo_testimonial=' . $data['id'];
		}

		if ( ! empty( $landing_page_options['not_approve'] ) ) {
			$links['no'] = get_site_url() . '?tvo_status=not_approve&tvo_testimonial=' . $data['id'];
		}
	}

	return $links;
}

/**
 * Construct customer email yes and no links
 *
 * @param array $url
 *
 * @return array
 */
function tvo_get_media_source( $url ) {
	if ( strpos( $url, 'facebook.com' ) !== false ) {
		return 'facebook';
	} elseif ( strpos( $url, 'twitter.com' ) !== false ) {
		return 'twitter';
	}

	return '';
}

/**
 * Check if an email has gravatar and return if true
 *
 * @param $email
 *
 * @return bool|string
 */
function tvo_validate_gravatar( $email ) {
	// Craft a potential url and test its headers
	$hash        = md5( strtolower( trim( $email ) ) );
	$uri         = 'http://www.gravatar.com/avatar/' . $hash . '?s=512&d=404';
	$response    = tve_dash_api_remote_get( $uri );
	$header_type = wp_remote_retrieve_header( $response, 'content-type' );

	if ( ! $header_type || strpos( $header_type, 'image' ) === false ) {
		$valid_avatar = false;
	} else {
		$valid_avatar = $uri;
	}

	return $valid_avatar;
}

/**
 * Get facebook id in case we have one
 * @return mixed
 */
function tvo_get_facebook_app_id() {

	$facebook = new Thrive_Dash_List_Connection_Facebook();

	$app_id = '';
	if ( $facebook->isConnected() ) {
		$app_id = $facebook->param( 'app_id', '' );
	}

	return $app_id;
}

/**
 * Get google client id in case we have one
 * @return mixed
 */
function tvo_get_google_client_id() {

	$google = new Thrive_Dash_List_Connection_Google();

	$client_id = '';
	if ( $google->isConnected() ) {
		$client_id = $google->param( 'client_id', '' );
	}

	return $client_id;
}

/**
 * Get default picture placeholder when the testimonial doesn't have picture
 * @return array|mixed
 */
function tvo_get_default_image_placeholder() {
	$default_image = TVO_ADMIN_URL . 'img/tvo-no-photo.png';

	return tvo_get_option( TVO_DEFAULT_PLACEHOLDER, $default_image );
}
