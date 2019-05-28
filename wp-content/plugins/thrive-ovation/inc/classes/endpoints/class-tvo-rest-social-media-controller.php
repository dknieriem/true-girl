<?php

/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/26/2016
 * Time: 12:36 PM
 */
class TVO_REST_Social_Media_Controller extends TVO_REST_Controller {
	public $base = 'socialmedia';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		parent::register_routes();

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/import_testimonial', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'import_testimonial' ),
				'permission_callback' => array( $this, 'import_testimonial_permissions_check' ),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/gravatar', array(
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_gravatar' ),
			),
		) );
	}

	/**
	 * Get gravatar picture
	 *
	 * @param $request
	 *
	 * @return string $picture
	 */
	public function get_gravatar( $request ) {
		$email = $request->get_param( 'email' );

		$picture = tvo_validate_gravatar( $email );

		return $picture;

	}

	/**
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return bool
	 */
	public function import_testimonial_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function import_testimonial( $request ) {
		$social_media_url = $request->get_param( 'social_media_url' );

		$response = array(
			'code'    => 0,
			'message' => __( 'Please enter a valid Facebook comment or Twitter comment URL', TVO_TRANSLATE_DOMAIN ),
		);

		if ( strpos( $social_media_url, 'facebook.com' ) !== false ) {
			$parts = parse_url( $social_media_url );
			if ( empty( $parts['query'] ) ) {
				return new WP_REST_Response( $response, 200 );
			}
			parse_str( $parts['query'], $query );
			/*Parsing the facebook URL and getting the facebook ID and the comment ID*/
			if ( empty( $query['fbid'] ) && empty( $query['story_fbid'] ) ) {
				$social_media_url_arr = explode( '/', tvo_get_string_between( $social_media_url, '/', '?' ) );
				$query['fbid']        = $social_media_url_arr[ count( $social_media_url_arr ) - 1 ];
				if ( empty( $query['fbid'] ) || ! is_numeric( $query['fbid'] ) ) {
					$query['fbid'] = $social_media_url_arr[ count( $social_media_url_arr ) - 2 ];
				}
			} elseif ( empty( $query['fbid'] ) && ! empty( $query['story_fbid'] ) ) {
				$query['fbid'] = $query['story_fbid'];
			}

			if ( ! empty( $query['reply_comment_id'] ) && is_numeric( $query['reply_comment_id'] ) ) {
				$query['comment_id'] = $query['reply_comment_id'];
			}

			if ( ! empty( $query['fbid'] ) && is_numeric( $query['fbid'] ) && ! empty( $query['comment_id'] ) && is_numeric( $query['comment_id'] ) ) {
				$response['result'] = $this->get_facebook_testimonial( $query['fbid'], $query['comment_id'] );
				if ( empty( $response['result']['error'] ) ) {
					$response['code']             = 1;
					$response['message']          = __( 'The data was fetched successfully', TVO_TRANSLATE_DOMAIN );
					$response['result']['source'] = 'facebook';
				} else {
					$response['code']    = 0;
					$response['message'] = $response['result']['error_message'];
				}
			}
		} elseif ( strpos( $social_media_url, 'twitter.com' ) !== false ) {
			$array_var = explode( '/', $social_media_url );
			$id        = end( $array_var );

			if ( is_numeric( $id ) && ! empty( $id ) ) {
				$response['result'] = $this->get_twitter_testimonial( $id );
				if ( empty( $response['result']['error'] ) ) {
					$response['code']             = 1;
					$response['message']          = __( 'The data was fetched successfully', TVO_TRANSLATE_DOMAIN );
					$response['result']['source'] = 'twitter';
				} else {
					$response['code']    = 0;
					$response['message'] = $response['result']['error_message'];
				}
			}
		}

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Fetch facebook comment and make a testimonial out of it
	 *
	 * @param $fbid
	 * @param $comment_id
	 *
	 * @return array
	 */
	private function get_facebook_testimonial( $fbid, $comment_id ) {

		$facebook = new Thrive_Dash_List_Connection_Facebook();

		$testimonial = array(
			'error'         => 0,
			'error_message' => '',
		);

		if ( $facebook->isConnected() == false ) {
			$testimonial['error']         = 1;
			$testimonial['error_message'] = __( 'Your Facebook connection is not active.', TVO_TRANSLATE_DOMAIN );

			return $testimonial;
		} else {
			$comment = $facebook->get_comment( $fbid, $comment_id );
		}

		if ( is_array( $comment ) ) {
			$attachment_url = tvo_upload_image_to_media_library( $comment['picture'], tvo_construct_social_image_name( $comment['name'] ) );

			$testimonial = array(
				'testimonial'       => $comment['message'],
				'name'              => $comment['name'],
				'profile_image_url' => $attachment_url,
				'email'             => $comment['id'],
				'website_url'       => '',
			);
		} elseif ( is_string( $comment ) ) {
			$testimonial['error']         = 1;
			$testimonial['error_message'] = $comment;
		}

		return $testimonial;
	}

	/**
	 * Get Twitter comment and convert it into testimonial
	 *
	 * @param $id
	 *
	 * @return array
	 */
	private function get_twitter_testimonial( $id ) {

		$twitter = new Thrive_Dash_List_Connection_Twitter();

		$testimonial = array(
			'error'         => 0,
			'error_message' => '',
		);

		if ( $twitter->isConnected() == false ) {
			$testimonial['error']         = 1;
			$testimonial['error_message'] = __( 'Your Twitter connection is not active.', TVO_TRANSLATE_DOMAIN );

			return $testimonial;
		} else {
			$comment = $twitter->get_comment( $id );
		}

		if ( is_array( $comment ) ) {
			$attachment_url = tvo_upload_image_to_media_library( $comment['picture'], tvo_construct_social_image_name( $comment['name'] ) );

			$testimonial = array(
				'testimonial'       => $comment['text'],
				'name'              => $comment['name'],
				'profile_image_url' => $attachment_url,
				'email'             => $comment['screen_name'],
				'website_url'       => $comment['url'],
			);
		} elseif ( is_string( $comment ) ) {
			$testimonial['error']         = 1;
			$testimonial['error_message'] = $comment;
		}

		return $testimonial;
	}
}
