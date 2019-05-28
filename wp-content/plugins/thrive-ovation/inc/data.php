<?php
/**
 * Functions that work with the DB
 *
 * e.g.: CRUD functions
 */

/**
 * Get testimonial with activity log
 *
 * @param $id
 *
 * @return array
 */
function tvo_get_testimonial( $id ) {
	$testimonial = tvo_get_testimonial_data( $id );
	if ( ! $testimonial ) {
		return array( 'status' => 'error', 'message' => __( 'Invalid ID', TVO_TRANSLATE_DOMAIN ) );
	}

	$activity_log                    = tvo_get_testimonial_activity_log( $testimonial['id'] );
	$testimonial['sent_emails']      = tvo_get_emails_from_activity_log( $testimonial['id'] );
	$testimonial['activityLog']      = ! empty( $activity_log['activity_log'] ) ? $activity_log['activity_log'] : array();
	$testimonial['activityLogCount'] = ! empty( $activity_log['total_count'] ) ? $activity_log['total_count'] : 0;

	return array( 'status' => 'ok', 'testimonial' => $testimonial );
}

/**
 * Get testimonial data
 *
 * @param $id
 *
 * @return array|bool
 */
function tvo_get_testimonial_data( $id ) {
	/** @var WP_Post $testimonial */

	$testimonial = tvo_get_testimonial_post( $id );

	if ( $testimonial ) {

		$tvo_testimonial_meta = get_post_meta( $testimonial->ID, TVO_POST_META_KEY, true );
		$tvo_status_meta      = get_post_meta( $testimonial->ID, TVO_STATUS_META_KEY, true );
		$tvo_source_meta      = get_post_meta( $testimonial->ID, TVO_SOURCE_META_KEY, true );
		$data                 = array(
			'id'           => $testimonial->ID,
			'title'        => $testimonial->post_title,
			'date'         => date_i18n( 'jS F, Y', strtotime( $testimonial->post_date ) ),
			'name'         => $tvo_testimonial_meta['name'],
			'content'      => $testimonial->post_content,
			'summary'      => wp_trim_words( wp_strip_all_tags( $testimonial->post_content ), TVO_TESTIMONIAL_CONTENT_WORDS_LIMIT ),
			'role'         => $tvo_testimonial_meta['role'],
			'email'        => $tvo_testimonial_meta['email'],
			'website_url'  => ! empty( $tvo_testimonial_meta['website_url'] ) ? $tvo_testimonial_meta['website_url'] : '',
			'picture_url'  => empty( $tvo_testimonial_meta['picture_url'] ) || strpos( $tvo_testimonial_meta['picture_url'], 'no-photo' ) !== false ? tvo_get_default_image_placeholder() : $tvo_testimonial_meta['picture_url'],
			'has_picture'  => ! empty( $tvo_testimonial_meta['picture_url'] ) ? 1 : 0,
			'tags'         => tvo_get_testimonial_tags( $testimonial->ID ),
			'status'       => $tvo_status_meta,
			'source'       => tvo_get_testimonial_source_text( $tvo_source_meta ),
			'media_source' => empty( $tvo_testimonial_meta['media_url'] ) ? '' : tvo_get_media_source( $tvo_testimonial_meta['media_url'] ),
			'comment_url'  => empty( $tvo_testimonial_meta['media_url'] ) ? '' : $tvo_testimonial_meta['media_url'],
		);

		return $data;
	}

	return false;
}

/**
 * Update testimonial
 *
 * @param $params array
 *
 * @return array
 */
function tvo_update_testimonial( $params ) {
	/** @var WP_Post $testimonial */
	$testimonial = tvo_get_testimonial_post( $params['id'] );

	if ( $testimonial ) {
		/*Updates testimonial field changes -> activity log*/
		do_action( 'tvo_log_testimonial_activity', $params );

		$testimonial->post_title   = $params['title'];
		$testimonial->post_content = $params['content'];

		$testimonial_id = wp_update_post( $testimonial );
		if ( ! empty( $testimonial_id ) ) {
			update_post_meta( $testimonial_id, TVO_POST_META_KEY, tvo_construct_testimonial_meta( $testimonial_id, $params ) );
		}
		//handle tags
		$tags = $params['tags'];
		if ( ! empty( $tags[0] ) && is_array( $tags[0] ) ) {
			foreach ( $params['tags'] as $tag ) {
				$tags['tags'][] = $tag['id'];
			}
		}

		tvo_update_testimonial_tags( $testimonial->ID, $tags );
		$saved_tags   = tvo_get_testimonial_tags( $testimonial->ID );
		$activity_log = tvo_get_testimonial_activity_log( $testimonial->ID );

		$data = array(
			'testimonial'      => $testimonial,
			'tags'             => $saved_tags,
			'activityLog'      => ! empty( $activity_log['activity_log'] ) ? $activity_log['activity_log'] : array(),
			'activityLogCount' => ! empty( $activity_log['total_count'] ) ? $activity_log['total_count'] : array(),
		);

		return array( 'status' => 'ok', 'testimonial' => $data );
	}

	return array( 'status' => 'error', 'message' => __( 'Invalid ID', TVO_TRANSLATE_DOMAIN ) );
}

/**
 * Create testimonial
 *
 * @param $params array
 *
 * @return array
 */
function tvo_create_testimonial( $params ) {


	$testimonial = array(
		'post_title'   => $params['title'],
		'post_content' => $params['content'],
		'post_status'  => 'publish',
		'post_type'    => TVO_TESTIMONIAL_POST_TYPE,
	);
	if ( ! empty( $params ) ) {
		$testimonial_id = wp_insert_post( $testimonial );

		tvo_update_testimonial_tags( $testimonial_id, $params['tags'] );
		if ( ! empty( $testimonial_id ) ) {
			if ( $params['status'] < 0 ) {
				$params['status'] = TVO_STATUS_READY_FOR_DISPLAY;
			}
			if ( empty( $params['source'] ) && ! is_numeric( $params['source'] ) ) {
				$params['source'] = TVO_SOURCE_PLUGIN;
			}
			if ( $params['source'] == 'copy' ) {
				$params['source'] = TVO_SOURCE_COPY;
			}

			$source_activity = array(
				'id'          => $testimonial_id,
				'source_type' => $params['source'],
			);

			if ( $params['source'] == TVO_SOURCE_SOCIAL_MEDIA || ( isset($params['is_media_source']) && $params['is_media_source'] )  ) {
				$source_activity = array_merge( $source_activity, array( 'comment_url' => $params['comment_url'] ) );
			}
			add_post_meta( $testimonial_id, TVO_POST_META_KEY, tvo_construct_testimonial_meta( $testimonial_id, $params ), true ) or update_post_meta( $testimonial_id, TVO_POST_META_KEY, tvo_construct_testimonial_meta( $testimonial_id, $params ) );
			add_post_meta( $testimonial_id, TVO_STATUS_META_KEY, $params['status'], true ) or update_post_meta( $testimonial_id, TVO_STATUS_META_KEY, $params['status'] );
			add_post_meta( $testimonial_id, TVO_SOURCE_META_KEY, $params['source'], true ) or update_post_meta( $testimonial_id, TVO_SOURCE_META_KEY, $params['source'] );

			$testimonial = tvo_get_testimonial( $testimonial_id );
			do_action( 'tvo_log_testimonial_source_activity', $source_activity );
			return $testimonial;
		}

		return array( 'status' => 'error', 'message' => __( 'Invalid ID', TVO_TRANSLATE_DOMAIN ) );
	}

	return array( 'status' => 'error', 'message' => __( 'Missing parameters', TVO_TRANSLATE_DOMAIN ) );
}

/**
 * Delete testimonial
 *
 * @param $id int
 *
 * @return array
 */
function tvo_delete_testimonial( $id ) {

	if ( get_post_status( $id ) ) {
		if ( wp_trash_post( $id ) != false ) {
			return array( 'status' => 'ok', 'message' => __( 'Success', TVO_TRANSLATE_DOMAIN ) );
		}
	}

	return array( 'status' => 'error', 'message' => __( 'Invalid ID', TVO_TRANSLATE_DOMAIN ) );
}

/**
 * Update post tag list
 *
 * @param $post_id int
 * @param $new_tags array
 *
 * @return bool
 */
function tvo_update_testimonial_tags( $post_id, $new_tags ) {

	if ( ! is_array( $new_tags ) ) {
		$new_tags = array();
	}

	$old_tags     = tvo_get_testimonial_tags( $post_id );
	$old_tag_keys = array();
	foreach ( $old_tags as $tag ) {
		$old_tag_keys[] = $tag['id'];
	}

	//delete old tags
	$diff = array_diff( $old_tag_keys, $new_tags );
	tvo_delete_testimonial_tag( $post_id, $diff );

	//add new tags
	$diff = array_diff( $new_tags, $old_tag_keys );
	tvo_attach_tags_to_testimonial( $post_id, $diff );

	return true;

}

/**
 * Returns all tags ids of a given testimonial
 *
 * @param $id
 *
 * @return array
 */
function tvo_get_testimonial_tags_ids( $id ) {

	if ( ! get_post_status( $id ) ) {
		return array( 'status' => 'error', 'message' => __( 'Post id is invalid', TVO_TRANSLATE_DOMAIN ) );
	}

	$tags   = wp_get_post_terms( $id, TVO_TESTIMONIAL_TAG_TAXONOMY, array() );
	$return = array();
	foreach ( $tags as $key => $tag ) {
		$return[] = $tag->term_id;
	}

	return $return;
}


/**
 * Get testimonial post
 *
 * @param $id int
 *
 * @return array
 */
function tvo_get_testimonial_post( $id ) {

	$testimonial = get_post( $id );

	if ( empty( $testimonial ) || $testimonial->post_type != TVO_TESTIMONIAL_POST_TYPE || $testimonial->post_status !== 'publish' ) {
		return false;
	}

	return $testimonial;
}

/**
 * Get post tags
 *
 * @param $id int
 *
 * @return array
 */
function tvo_get_testimonial_tags( $id ) {

	if ( ! get_post_status( $id ) ) {
		return array( 'status' => 'error', 'message' => __( 'Post id is invalid', TVO_TRANSLATE_DOMAIN ) );
	}

	$tags = wp_get_post_terms( $id, TVO_TESTIMONIAL_TAG_TAXONOMY, array() );
	$data = array();
	foreach ( $tags as $key => $tag ) {
		$data[ $key ]['id']      = $tag->term_id;
		$data[ $key ]['post_id'] = $id;
		$data[ $key ]['text']    = $tag->name;
	}

	return $data;
}

/**
 * Get all available testimonial tags
 *
 * @return array
 */
function tvo_get_all_tags() {

	$tags = get_terms( array( 'taxonomy' => TVO_TESTIMONIAL_TAG_TAXONOMY, 'hide_empty' => 0 ) );

	$data = array();

	foreach ( $tags as $key => $tag ) {
		$data[ $key ]['id']   = $tag->term_id;
		$data[ $key ]['text'] = $tag->name;
	}

	return $data;
}

/**
 * Create tag if does not exists
 *
 * @param $tag_args array
 *
 * @return array
 */
function tvo_save_testimonial_tag( $tag_args ) {

	if ( empty( $tag_args['term_id'] ) ) {
		$result = wp_insert_term( $tag_args['name'], TVO_TESTIMONIAL_TAG_TAXONOMY, array() );
		if ( is_array( $result ) ) {
			$tag_args['term_id'] = $result['term_id'];
		} else {
			return array( 'status' => 'error', 'message' => __( 'Error', TVO_TRANSLATE_DOMAIN ) );
		}
	}

	return array( 'status' => 'ok', 'tag' => $tag_args );
}

/**
 * Process approval email content
 *
 * @param $template string
 * @param $data array|bool
 *
 * @return string|bool
 */
function tvo_process_approval_email_content( $template, $data = false ) {

	if ( empty( $template ) ) {
		return false;
	}
	if ( ! empty( $data['name'] ) && ! empty( $data['content'] ) ) {
		$template = str_replace( '[tvo_full_name]', $data['name'], $template );
		$template = str_replace( '[tvo_testimonial_text]', $data['content'], $template );
	} else {
		$template = str_replace( '[tvo_full_name]', TVO_DEFAULT_EMAIL_TEMPLATE_NAME, $template );
		$template = str_replace( '[tvo_testimonial_text]', TVO_DEFAULT_EMAIL_TEMPLATE_TEXT, $template );
	}

	$yes_str = tvo_get_string_between( $template, 'yes="', '" ' );
	$no_str  = tvo_get_string_between( $template, 'no="', '"' );

	if ( $yes_str && $no_str ) {
		$links      = tvo_construct_yes_no_email_links( $data );
		$yes_str    = '<a href=\'' . $links['yes'] . '\'  style="background-color: #4bb35e;border-radius: 50px;color:#fff;padding: 10px 20px;text-decoration: none;">' . $yes_str . '</a>';
		$no_str     = '<a href=\'' . $links['no'] . '\' style="background: #a9a9a9;border-radius: 50px;color: #fff; margin-right: 10px;padding: 10px 20px;text-decoration: none;">' . $no_str . '</a>';
		$button_tag = tvo_get_string_between( $template, '[tvo_approval_buttons', ']' );
		$button_tag = '[tvo_approval_buttons' . $button_tag . ']';
		$template   = str_replace( $button_tag, $no_str . $yes_str, $template );
	}

	return $template;
}

/**
 * Process approval email subject
 *
 * @param $subject string
 * @param $data array|bool
 *
 * @return string|bool
 */
function tvo_process_approval_email_subject( $subject, $data = false ) {

	if ( empty( $subject ) ) {
		return false;
	}
	if ( ! empty( $data['name'] ) && ! empty( $data['content'] ) ) {
		$subject = str_replace( '[tvo_full_name]', $data['name'], $subject );
		$subject = str_replace( '[tvo_testimonial_text]', strip_tags( $data['content'] ), $subject );
	} else {
		$subject = str_replace( '[tvo_full_name]', TVO_DEFAULT_EMAIL_TEMPLATE_NAME, $subject );
		$subject = str_replace( '[tvo_testimonial_text]', TVO_DEFAULT_EMAIL_TEMPLATE_TEXT, $subject );
	}
	$button_tag = tvo_get_string_between( $subject, '[tvo_approval_buttons', ']' );
	$button_tag = '[tvo_approval_buttons' . $button_tag . ']';
	$subject    = str_replace( $button_tag, '', $subject );

	return $subject;
}

/**
 * Attach multiple tags to post
 *
 * @param $post_id int
 * @param $tag_ids array
 *
 * @return bool
 */
function tvo_attach_tags_to_testimonial( $post_id, $tag_ids ) {
	$tag_ids = array_map( 'intval', $tag_ids );
	wp_set_object_terms( $post_id, $tag_ids, TVO_TESTIMONIAL_TAG_TAXONOMY, true );

	return true;
}

/**
 * Attach tag to post
 * Create tag if doesn't exists
 *
 * @param $post_id int
 * @param $tag_id int
 *
 * @return array
 */
function tvo_attach_tag_to_testimonial( $post_id, $tag_id ) {

	if ( ! get_post_status( $post_id ) ) {
		return array( 'status' => 'error', 'message' => __( 'Post id is invalid', TVO_TRANSLATE_DOMAIN ) );
	}
	if ( empty( $tag_id ) ) {
		return array( 'status' => 'error', 'message' => __( 'Error', TVO_TRANSLATE_DOMAIN ) );
	}

	$tag = get_term( $tag_id, TVO_TESTIMONIAL_TAG_TAXONOMY );
	if ( empty( $tag ) ) {
		return array( 'status' => 'error', 'message' => __( 'Tag id is invalid', TVO_TRANSLATE_DOMAIN ) );
	}

	wp_set_object_terms( $post_id, intval( $tag_id ), TVO_TESTIMONIAL_TAG_TAXONOMY, true );

	return array( 'status' => 'ok', 'tag' => $tag );
}

/**
 * Detach tag from post
 *
 * @param $post_id int
 * @param $tag_id int/array
 *
 * @return array
 */
function tvo_delete_testimonial_tag( $post_id, $tag_id ) {

	if ( ! get_post_status( $post_id ) ) {
		return array( 'status' => 'error', 'message' => __( 'Post id is invalid', TVO_TRANSLATE_DOMAIN ) );
	}
	if ( ! is_array( $tag_id ) ) {
		$tag_id = intval( $tag_id );

		$tag = get_term( $tag_id, TVO_TESTIMONIAL_TAG_TAXONOMY );
		if ( empty( $tag ) ) {
			return array( 'status' => 'error', 'message' => __( 'Tag id is invalid', TVO_TRANSLATE_DOMAIN ) );
		}
	}
	wp_remove_object_terms( $post_id, $tag_id, TVO_TESTIMONIAL_TAG_TAXONOMY );

	return array( 'status' => 'ok' );

}

/**
 * Get count of sent emails
 *
 * @param $post_id int
 *
 * @return array
 */
function tvo_get_emails_from_activity_log( $post_id ) {
	global $tvodb;
	$email_activity_log_count = $tvodb->get_send_email_count( $post_id );

	return $email_activity_log_count;
}

/**
 * Get array of activity log entries
 *
 * @param $post_id int
 * @param $offset int
 *
 * @return array
 */
function tvo_get_testimonial_activity_log( $post_id, $offset = 0 ) {
	/** @var WP_Post $testimonial */
	$testimonial = tvo_get_testimonial_post( $post_id );

	if ( ! $testimonial ) {
		return array( 'status' => 'error', 'message' => __( 'Post id is invalid', TVO_TRANSLATE_DOMAIN ) );
	}

	global $tvodb;
	$activity_log = $tvodb->get_activity_log( $testimonial->ID, $offset );
	if ( empty( $activity_log ) ) {
		return false;
	}
	foreach ( $activity_log as $key => $entry ) {
		$activity_log[ $key ]['date'] = date_i18n( 'jS M, Y H:i', strtotime( $entry['date'] ) );

		$activity_data = maybe_unserialize( $activity_log[ $key ]['activity_data'] );
		if ( ! empty( $activity_data ) ) {
			$user_info = get_userdata( $activity_data['user_id'] );
			if ( $user_info ) {
				$user_nice_name_with_link = '<a href="' . get_edit_user_link( $activity_data['user_id'] ) . '" target="_blank">' . ucfirst( $user_info->data->display_name ) . '</a>';
			} else {
				$user_nice_name_with_link = '';
			}

			switch ( $entry['activity_type'] ) {
				case TVO_LOG_SOURCE_CAPTURE_FORM:
					$shortcode = tvo_get_testimonial_shortcode_source( $post_id );
					$activity_log[ $key ]['text']  = __( 'Testimonial added through ', TVO_TRANSLATE_DOMAIN ) . '<a href="' . $shortcode['url'] . '">' . $shortcode['name'] . '</a>';
					$activity_log[ $key ]['class'] = 'tvo-log-source-capture-form';
					break;
				case TVO_LOG_SOURCE_WORDPRESS_COMMENTS:
					$activity_log[ $key ]['text']  = $user_nice_name_with_link . __( ' converted ', TVO_TRANSLATE_DOMAIN ).'<a target="_blank" href="' . get_edit_comment_link( $activity_data['comment_id'] ) . '">comment</a> ' . __( ' into testimonial ', TVO_TRANSLATE_DOMAIN );
					$activity_log[ $key ]['class'] = 'tvo-log-source-wordpress-comments';
					break;
				case TVO_LOG_SOURCE_IMPORT_SOCIAL_MEDIA:
					preg_match( '/[^\.\/]+\.[^\.\/]+$/', $activity_data['network'], $matches );
					$activity_log[ $key ]['text']  = $user_nice_name_with_link . __( ' added testimonial from ', TVO_TRANSLATE_DOMAIN ) . ' <a href="' . $activity_data['comment_url'] . '">' . ucfirst( substr( $matches[0], 0, strpos( $matches[0], '.' ) ) ) . '</a>';
					$activity_log[ $key ]['class'] = 'tvo-log-source-import-social-media';
					break;
				case TVO_LOG_SOURCE_PLUGIN:
					$activity_log[ $key ]['text']  = $user_nice_name_with_link . __( ' added this testimonial manually ', TVO_TRANSLATE_DOMAIN );
					$activity_log[ $key ]['class'] = 'tvo-log-source-tvo-ovation';
					break;
				case TVO_LOG_SOURCE_COPY:
					$activity_log[ $key ]['text']  = $user_nice_name_with_link . __( ' added this testimonial through copy', TVO_TRANSLATE_DOMAIN );
					$activity_log[ $key ]['class'] = 'tvo-log-copy';
					break;
				case TVO_LOG_CONTENT_CHANGED_BY_STAFF:
					$activity_log[ $key ]['text']  = $user_nice_name_with_link . __( ' updated ', TVO_TRANSLATE_DOMAIN ) . $activity_data['fields'];
					$activity_log[ $key ]['class'] = 'tvo-log-content-changed-by-staff';
					break;
				case TVO_LOG_EMAIL_SENT:
					$activity_log[ $key ]['text']  = __( ' Approval email send to ', TVO_TRANSLATE_DOMAIN ) . $activity_data['email_address'];
					$activity_log[ $key ]['class'] = 'tvo-log-email-sent-to-author';
					break;
				case TVO_LOG_CHANGED_STATUS:
					if ( $user_nice_name_with_link ) {
						$activity_log[ $key ]['text'] = $user_nice_name_with_link . __( ' changed the status of the testimonial form ', TVO_TRANSLATE_DOMAIN ) . tvo_get_testimonial_status_text( $activity_data['previous_status'] ) . ' to ' . tvo_get_testimonial_status_text( $activity_data['current_status'] );
					} else {
						$activity_log[ $key ]['text'] = __( ' Customer changed the status of the testimonial form ', TVO_TRANSLATE_DOMAIN ) . tvo_get_testimonial_status_text( $activity_data['previous_status'] ) . ' to ' . tvo_get_testimonial_status_text( $activity_data['current_status'] );
					}

					$activity_log[ $key ]['class'] = 'tvo-log-changed-status';
					break;
				case TVO_LOG_CHANGED_PICTURE:
					$activity_log[ $key ]['text']  = $user_nice_name_with_link . __( ' modified the testimonial ', TVO_TRANSLATE_DOMAIN ) . ' <a href="' . $activity_data['picture'] . '" target="_blank">picture</a>';
					$activity_log[ $key ]['class'] = 'tvo-log-changed-picture';
					break;
				case TVO_LOG_EMAIL_CONFIRMED:
					$activity_log[ $key ]['text']  = '[email_address] approved usage';
					$activity_log[ $key ]['class'] = 'tvo-log-changed-picture';
					break;
			}
		}
	}
	$count                = $tvodb->count_logs( $testimonial->ID );
	$data['activity_log'] = $activity_log;
	$data['total_count']  = $count[0]['entry_count'];

	return $data;

}


/**
 * Get the testimonials together with the post meta depending on the filters set
 *
 * @param array $filters
 *
 * @return array
 */
function tvo_get_testimonials( $filters = array() ) {
	$defaults = array(
		'post_type'      => TVO_TESTIMONIAL_POST_TYPE,
		'order'          => 'ASC',
		'posts_per_page' => '-1',
	);
	$filters  = array_merge( $defaults, $filters );

	$testimonials = get_posts( $filters );
	foreach ( $testimonials as $key => $value ) {
		$testimonials[ $key ]->_tvo_testimonial_attributes = get_post_meta( $value->ID, TVO_POST_META_KEY, true );
		$testimonials[ $key ]->status                      = get_post_meta( $value->ID, TVO_STATUS_META_KEY, true );
	}

	return $testimonials;
}

/**
 * Return formatted tags for front end
 * @return array
 */
function tvo_get_formatted_tags() {

	$tags = array();

	$terms = get_terms( TVO_TESTIMONIAL_TAG_TAXONOMY, array( 'hide_empty' => false ) );
	foreach ( $terms as $t ) {
		if ( ! empty( $t->count ) ) {
			$tags[] = array(
				'id'   => $t->term_id,
				'name' => $t->name,
			);
		}
	}

	return $tags;
}

/**
 * Return formatted testimonials for front end
 * @return array
 */
function tvo_get_formatted_testimonials() {
	$testimonials_temp = tvo_get_testimonials();
	$testimonials      = array();

	foreach ( $testimonials_temp as $t ) {

		if ( $t->status != TVO_STATUS_READY_FOR_DISPLAY ) {
			continue;
		}

		$testimonial = array(
			'id'      => $t->ID,
			'author'  => $t->post_title,
			'content' => $t->post_content,
			'tags'    => array(),
		);

		$testimonial = array_merge( $testimonial, $t->_tvo_testimonial_attributes );

		$testimonial['picture_url'] = ! empty( $testimonial['picture_url'] ) ? $testimonial['picture_url'] : tvo_get_default_image_placeholder();

		$terms = wp_get_post_terms( $t->ID, TVO_TESTIMONIAL_TAG_TAXONOMY );

		foreach ( $terms as $term ) {
			$testimonial['tags'][] = $term->term_id;
		}

		$testimonials[] = $testimonial;
	}

	return $testimonials;
}

/**
 * Logs testimonial fields activity
 *
 * @param array $testimonial_params
 */
function tvo_log_testimonial_activity( $testimonial_params = array() ) {
	global $tvodb;

	$testimonial      = get_post( $testimonial_params['id'] );
	$testimonial_meta = get_post_meta( $testimonial->ID, TVO_POST_META_KEY, true );
	$fields           = array();

	/*Testimonial Title*/
	if ( ! tvo_are_strings_equal( $testimonial->post_title, $testimonial_params['title'] ) ) {
		$fields[] = __( 'title', TVO_TRANSLATE_DOMAIN );
	}

	/*Author name*/
	if ( ! tvo_are_strings_equal( $testimonial_meta['name'], $testimonial_params['name'] ) ) {
		$fields[] = __( 'author name', TVO_TRANSLATE_DOMAIN );
	}

	/*Testimonial text*/
	if ( ! tvo_are_strings_equal( $testimonial->post_content, $testimonial_params['content'] ) ) {
		$fields[] = __( 'testimonial text', TVO_TRANSLATE_DOMAIN );
	}

	/*Email*/
	if ( ! tvo_are_strings_equal( $testimonial_meta['email'], $testimonial_params['email'] ) ) {
		if ( ! empty( $testimonial_meta['media_url'] ) ) {
			if ( strpos( $testimonial_meta['media_url'], 'facebook.com' ) !== false ) {
				$fields[] = __( 'Facebook ID', TVO_TRANSLATE_DOMAIN );
			} elseif ( strpos( $testimonial_meta['media_url'], 'twitter.com' ) !== false ) {
				$fields[] = __( 'Twitter handle', TVO_TRANSLATE_DOMAIN );
			}
		} else {
			$fields[] = __( 'email address', TVO_TRANSLATE_DOMAIN );
		}
	}

	/*Role*/
	if ( ! tvo_are_strings_equal( $testimonial_meta['role'], $testimonial_params['role'] ) ) {
		$fields[] = __( 'role/occupation', TVO_TRANSLATE_DOMAIN );
	}

	/*Website*/
	if ( ! tvo_are_strings_equal( $testimonial_meta['website_url'], $testimonial_params['website_url'] ) ) {
		$fields[] = __( 'web site URL', TVO_TRANSLATE_DOMAIN );
	}

	/*Tags*/
	$tags = tvo_get_testimonial_tags_ids( $testimonial_params['id'] );
	if ( empty( $testimonial_params['tags'] ) ) {
		$testimonial_params['tags'] = array();
	}
	$diff_1 = array_diff( $testimonial_params['tags'], $tags );
	$diff_2 = array_diff( $tags, $testimonial_params['tags'] );
	if ( ! empty( $diff_1 ) || ! empty( $diff_2 ) ) {
		$fields[] = __( 'testimonial tags', TVO_TRANSLATE_DOMAIN );
	}

	if ( ! empty( $fields ) ) {
		$tvodb->populate_activity_log( $testimonial_params['id'], TVO_LOG_CONTENT_CHANGED_BY_STAFF, array( 'fields' => implode( ', ', $fields ) ) );
	}

	/*Picture*/
	if ( ! tvo_are_strings_equal( $testimonial_meta['picture_url'], $testimonial_params['picture_url'] ) ) {
		$tvodb->populate_activity_log( $testimonial_params['id'], TVO_LOG_CHANGED_PICTURE, array( 'picture' => $testimonial_params['picture_url'] ) );
	}
}

/**
 * Logs testimonial status activity
 *
 * @param array $testimonial_params
 */
function tvo_log_testimonial_status_activity( $testimonial_params = array() ) {
	global $tvodb;

	$testimonial_status_meta = get_post_meta( $testimonial_params['id'], TVO_STATUS_META_KEY, true );
	if ( ! tvo_are_strings_equal( $testimonial_status_meta, $testimonial_params['status'] ) ) {
		$tvodb->populate_activity_log( $testimonial_params['id'], TVO_LOG_CHANGED_STATUS, array(
			'previous_status' => $testimonial_status_meta,
			'current_status'  => $testimonial_params['status'],
		) );
	}
}

/**
 * Logs testimonial email activity
 *
 * @param array $testimonial_params
 */
function tvo_log_testimonial_email_activity( $testimonial_params = array() ) {
	global $tvodb;
	$tvodb->populate_activity_log( $testimonial_params['id'], TVO_LOG_EMAIL_SENT, array( 'email_address' => $testimonial_params['email_address'] ) );
}

/**
 * Logs testimonial source activity
 *
 * @param array $testimonial_params
 */
function tvo_log_testimonial_source_activity( $testimonial_params = array() ) {
	global $tvodb;

	switch ( $testimonial_params['source_type'] ) {
		case TVO_SOURCE_COMMENTS:
			$log_type      = TVO_LOG_SOURCE_WORDPRESS_COMMENTS;
			$activity_data = array( 'comment_id' => $testimonial_params['comment_id'] );
			break;
		case TVO_SOURCE_SOCIAL_MEDIA:
			$log_type      = TVO_LOG_SOURCE_IMPORT_SOCIAL_MEDIA;
			$activity_data = array(
				'network'     => parse_url( $testimonial_params['comment_url'], PHP_URL_HOST ),
				'comment_url' => $testimonial_params['comment_url'],
			);
			break;
		case TVO_SOURCE_DIRECT_CAPTURE:
			$log_type      = TVO_LOG_SOURCE_CAPTURE_FORM;
			$activity_data = array();
			break;
		case TVO_SOURCE_COPY:
			$log_type      = TVO_LOG_SOURCE_COPY;
			$activity_data = array();
			break;
		case TVO_SOURCE_PLUGIN:
			$log_type      = TVO_LOG_SOURCE_PLUGIN;
			$activity_data = array();
			break;
		default:
			break;
	}

	$tvodb->populate_activity_log( $testimonial_params['id'], $log_type, $activity_data );
}

/**
 * Return shortcode posts
 *
 * @param $type
 *
 * @return array|boolean
 */
function tvo_get_shortcodes( $type ) {

	if ( ! empty( $type ) ) {
		$args = array(
			'post_status'    => 'draft',
			'post_type'      => TVO_SHORTCODE_POST_TYPE,
			'posts_per_page' => - 1,
			'meta_query'     => array(
				array(
					'key'   => 'tvo_shortcode_type',
					'value' => $type,
				),
			),
		);

		$items = get_posts( $args );

		$data = array();
		foreach ( $items as $item ) {
			$data[] = array(
				'id'      => $item->ID,
				'url'     => get_permalink( $item->ID ),
				'name'    => $item->post_title,
				'content' => $item->post_content,
				'config'  => tvo_get_shortcode_config( $item->ID, $type ),
			);
		}

		return $data;
	}

	return false;
}

/**
 * Return set of needed settings to make email sending available
 * @return boolean
 */
function tvo_get_needed_email_options() {
	$options             = array();
	$options['template'] = get_option( TVO_EMAIL_TEMPLATE_OPTION, false );
	$options['subject']  = get_option( TVO_EMAIL_TEMPLATE_SUBJECT_OPTION, false );
	$options['landing']  = get_option( TVO_LANDING_PAGE_SETTINGS_OPTION, false );

	return ( ! empty( $options['template'] ) && ! empty( $options['subject'] ) && ! empty( $options['landing'] ) );
}

/**
 * Return capture shortcode config
 *
 * @param $id int
 * @param $type string
 *
 * @return array
 */
function tvo_get_shortcode_config( $id, $type = '' ) {

	if ( empty( $type ) ) {
		$type = get_post_meta( $id, 'tvo_shortcode_type', true );
	}

	$config = get_post_meta( $id, 'tvo_shortcode_config', true );

	if ( empty( $config ) ) {
		$config = tvo_get_default_shortcode_config( $type );
	}

	if ( empty( $config['tags'] ) ) {
		$config['tags'] = array();
	}

	if ( empty( $config['testimonials'] ) ) {
		$config['testimonials'] = array();
	}

	if ( $type == 'capture' ) {
		/* backwards compatibility */
		if ( ! isset( $config['image_display'] ) ) {
			$config['image_display'] = 1;
		}

		if ( ! isset( $config['on_success_option'] ) ) {
			$config['on_success_option'] = 'message';
		}

		if ( ! isset( $config['placeholders'] ) ) {
			$config['placeholders'] = array_fill( 0, count( $config['questions'] ), '' );
		}
	}

	$config['id']   = $id;
	$config['type'] = $type;

	return $config;
}

/**
 * Get testimonials from shortcode config
 *
 * @param $config
 *
 * @return array
 */
function tvo_get_testimonials_from_config( $config ) {

	$testimonials = array();

	if ( ! empty( $config['testimonials'] ) ) {
		foreach ( $config['testimonials'] as $testimonial_id ) {
			$t = tvo_get_testimonial_data( $testimonial_id );

			//check if the testimonial still exists
			if ( $t ) {
				/* some weird spaces that we need to remove */
				$t['content'] = str_replace( ' ', ' ', $t['content'] );
				/* some editors save the content with div  */
				$t['content']   = str_replace( '<div', '<p', $t['content'] );
				$t['content']   = str_replace( '</div', '</p', $t['content'] );
				$t['content']   = strpos( $t['content'], '<p' ) !== false ? $t['content'] : '<p>' . $t['content'] . '</p>';
				$testimonials[] = $t;
			}
		}
	} elseif ( ! empty( $config['tags'] ) ) {
		$args  = array(
			'posts_per_page' => - 1,
			'post_type'      => TVO_TESTIMONIAL_POST_TYPE,
			'tax_query'      => array(
				array(
					'taxonomy' => TVO_TESTIMONIAL_TAG_TAXONOMY,
					'field'    => 'term_id',
					'terms'    => $config['tags'],
				),
			),
			'meta_query'     => array(
				array(
					'key'     => TVO_STATUS_META_KEY,
					'value'   => TVO_STATUS_READY_FOR_DISPLAY,
					'compare' => '=',
				),
			),
		);
		$query = new WP_Query( $args );

		foreach ( $query->get_posts() as $t ) {
			$t = tvo_get_testimonial_data( $t->ID );
			/* some weird spaces that we need to remove */
			$t['content'] = str_replace( ' ', ' ', $t['content'] );
			/* some editors save the content with div  */
			$t['content']   = str_replace( '<div', '<p', $t['content'] );
			$t['content']   = str_replace( '</div', '</p', $t['content'] );
			$t['content']   = strpos( $t['content'], '<p>' ) !== false ? $t['content'] : '<p>' . $t['content'] . '</p>';
			$testimonials[] = $t;
		}
		shuffle( $testimonials );

		if ( ! empty( $config['max_testimonials'] ) ) {
			$testimonials = array_slice( $testimonials, 0, $config['max_testimonials'] );
		}
	}

	return $testimonials;
}
