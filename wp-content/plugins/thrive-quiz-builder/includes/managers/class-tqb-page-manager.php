<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Class TQB_Page_Manager
 *
 * Handles Page operations
 */
class TQB_Page_Manager {

	/**
	 * @var TQB_Page_Manager $instance
	 */
	protected $page;

	/**
	 * TQB_Page_Manager constructor.
	 */
	public function __construct( $page_id = null ) {

		$this->page = get_post( $page_id );
	}

	/**
	 *  Get all quiz pages
	 */
	public static function get_quiz_pages( $quiz_id ) {
		$posts = query_posts(
			array(
				'post_type'   => array(
					Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE,
					Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA,
					Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN,
					Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS,
				),
				'post_parent' => $quiz_id,
			)
		);

		return $posts;
	}

	/**
	 *  Get the quiz_id for page
	 */
	public function get_quiz_id() {
		return $this->page->post_parent;
	}

	/**
	 * Delete all quiz pages and the tests that are linked to them
	 *
	 * @param array $posts
	 */
	public static function delete_quiz_pages( $posts = array() ) {
		global $tqbdb;

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );

			//delete event log for splash and result and optin page
			$tqbdb->delete_logs( array( 'page_id' => $post->ID ) );
			//delete event log for Q&A
			$tqbdb->delete_logs( array( 'page_id' => $post->post_parent ) );

			// delete quiz test and test items
			$tests = $tqbdb->get_test( array( 'page_id' => $post->ID ), false );
			if ( empty( $tests ) ) {
				continue;
			}

			foreach ( $tests as $test ) {
				$test_manager = new TQB_Test_Manager( $test['id'] );
				$test_manager->delete_test_items();
				$test_manager->delete_test( array(
					'page_id' => $post->ID,
					'id'      => $test['id'],
				) );
			}
		}
	}

	/**
	 * Gets running test for current page
	 *
	 * @return array|bool|null|WP_Post
	 */
	function get_tests_for_page( $filters, $single = false, $return_type = ARRAY_A ) {
		global $tqbdb;

		if ( empty( $this->page ) ) {
			return false;
		}

		return $tqbdb->get_test( $filters, $single, $return_type );
	}

	/**
	 * Gets a quiz page based on a given id
	 *
	 * @return array|bool|null|WP_Post
	 */
	function get_page( $is_front = false, $viewed = false ) {
		global $tqbdb;

		if ( empty( $this->page ) ) {
			return false;
		}
		if ( $viewed ) {
			$this->update_page_viewed_status( $this->page->post_parent, $this->page->post_type );
		}

		$this->page->variations = $tqbdb->get_page_variations( array(
			'post_id'     => $this->page->ID,
			'post_status' => Thrive_Quiz_Builder::VARIATION_STATUS_PUBLISH,
		), OBJECT );

		foreach ( $this->page->variations as $variation ) {
			$variation->tcb_editor_url = TQB_Variation_Manager::get_editor_url( $this->page->ID, $variation->id );
			$variation->post_type      = $this->page->post_type;
		}
		//get test and its items
		$this->page->running_test = $this->get_tests_for_page( array( 'page_id' => $this->page->ID, 'status' => 1 ), true );

		//get stuff only for backend
		if ( ! $is_front ) {
			$this->page->archived_variations = $tqbdb->get_page_variations( array(
				'post_id'     => $this->page->ID,
				'post_status' => Thrive_Quiz_Builder::VARIATION_STATUS_ARCHIVE,
			), OBJECT );
			foreach ( $this->page->archived_variations as $archived_variation ) {
				$archived_variation->tcb_preview_url = TQB_Variation_Manager::get_preview_url( $this->page->ID, $archived_variation->id );
				$archived_variation->post_type       = $this->page->post_type;
			}

			$this->page->completed_tests = $this->get_tests_for_page( array( 'page_id' => $this->page->ID, 'status' => 0 ), false );

			$this->page->tqb_page_name        = tqb()->get_style_page_name( $this->page->post_type );
			$this->page->tqb_page_description = tqb()->get_style_page_description( $this->page->post_type );
			$this->page->quiz_name            = html_entity_decode( get_the_title( $this->page->post_parent ) );
		}

		return $this->page;
	}

	/**
	 * Gets a quiz page based on a given id
	 *
	 * @return array|bool|null
	 */
	function update_page_viewed_status( $quiz_id, $type ) {

		$type           = tqb()->get_structure_type_name( $type );
		$quiz_structure = new TQB_Structure_Manager( $quiz_id );

		return $quiz_structure->update_quiz_viewed_status( $type, true );
	}

	/**
	 * Saved the page
	 *
	 * @param $type
	 * @param $quiz_id
	 *
	 * @return int|WP_Error
	 */
	function save_page( $type, $quiz_id ) {
		$post_type = tqb()->get_structure_post_type_name( $type );
		$page_name = tqb()->get_style_page_name( $post_type );
		$args      = array(
			'post_type'   => $post_type,
			'post_parent' => $quiz_id,
			'post_title'  => $page_name,
			'post_status' => 'publish',
		);
		$post_id   = wp_insert_post( $args );

		return $post_id;
	}

	/**
	 * Delete the page
	 *
	 * @return int|WP_Error
	 */
	function delete_page() {

		wp_delete_post( $this->page->ID );
		global $tqbdb;
		$tqbdb->delete_tests( array( 'page_id' => $this->page->ID ) );

		return $tqbdb->delete_variations( array( 'page_id' => $this->page->ID ) );
	}

	/**
	 * Get page html to display
	 *
	 * @param $points
	 *
	 * @return int|WP_Error
	 */
	function get_page_display_html( $points ) {
		$page      = $this->get_page( true );
		$page_type = TQB_Post_meta::get_quiz_type_meta( $page->post_parent );
		global $tqbdb;
		$variation_manager = new TQB_Variation_Manager( $page->post_parent, $page->ID );

		if ( ! empty( $page->running_test ) ) {
			$variation = $variation_manager->determine_variation( $page->running_test );
		} else {
			$variation = $variation_manager->get_page_variations( array( 'is_control' => 1, 'post_status' => 'publish' ) );
		}
		$variation['post_type'] = $page->post_type;

		if ( $page->post_type == Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS && $points['user_points'] != null ) {

			$content    = '';
			$variations = $tqbdb->get_page_variations( array( 'parent_id' => $variation['id'] ) );

			$result = $tqbdb->get_explicit_result( $points );

			foreach ( $variations as $child_variation ) {
				switch ( $page_type['type'] ) {
					case Thrive_Quiz_Builder::QUIZ_TYPE_NUMBER:
						if ( $points['user_points'] >= $child_variation['tcb_fields']['min'] && $points['user_points'] <= $child_variation['tcb_fields']['max'] ) {
							$content = explode( Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN, $child_variation['content'] );
						}
						break;
					case Thrive_Quiz_Builder::QUIZ_TYPE_PERCENTAGE:
						if ( $result >= $child_variation['tcb_fields']['min'] && $result <= $child_variation['tcb_fields']['max'] ) {
							$content = explode( Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN, $child_variation['content'] );
						}
						break;
					case Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY:
						if ( $points['result_id'] == $child_variation['tcb_fields']['result_id'] ) {
							$content = explode( Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN, $child_variation['content'] );
						}
						break;
				}
			}

			$m = explode( Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN, $variation['content'] );

			if ( isset( $m[1] ) && isset( $content[1] ) ) {
				$variation['content'] = str_replace( ( Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN . $m[1] . Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN ), $content[1], $variation['content'] );
			}

			// Replace %result shortcode with actual result%

			$variation['content'] = str_replace( Thrive_Quiz_Builder::QUIZ_RESULT_SHORTCODE, $result, $variation['content'] );
		}

		return $variation;
	}

	/**
	 * Update social share badge links
	 *
	 * @param int $quiz_id
	 * @param $social_share_badge_url
	 * @param $social_share_badge_searched_url
	 */
	function update_social_share_links( $quiz_id = 0, $social_share_badge_url, $social_share_badge_searched_url ) {
		global $tqbdb;
		$variations = $tqbdb->get_page_variations( array( 'post_id' => $this->page->ID ) );


		if ( empty( $variations ) ) {
			return;
		}

		foreach ( $variations as $variation ) {
			if ( empty( $variation ['tcb_fields'][ Thrive_Quiz_Builder::FIELD_SOCIAL_SHARE_BADGE ] ) ) {
				continue;
			}

			$new_variation_content = str_replace( $social_share_badge_searched_url, $social_share_badge_url, $variation['content'] );

			$tqbdb->save_variation( array(
				'id'      => $variation['id'],
				'quiz_id' => $quiz_id,
				'page_id' => $this->page->ID,
				'content' => $new_variation_content,
			) );
		}
	}
}
