<?php
/**
 * Review class.
 *
 * @since 1.1.4.5
 *
 * @package OMAPI
 * @author  Devin Vinson
 */
class OMAPI_Review {

	/**
	 * Holds the class object.
	 *
	 * @since 1.1.4.5
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.1.4.5
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the review slug.
	 *
	 * @since 1.1.4.5
	 *
	 * @var string
	 */
	public $hook;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.1.4.5
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Current API route.
	 *
	 * @since 1.1.4.5
	 *
	 * @var bool|string
	 */
	public $route = 'optinmonster.com/wp-json/optinmonster/v1/pluginreview/';

	/**
	 * API Username.
	 *
	 * @since 1.1.4.5
	 *
	 * @var bool|string
	 */
	public $user = false;

	/**
	 * API Key.
	 *
	 * @since 1.0.0
	 *
	 * @var bool|string
	 */
	public $key = false;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.1.4.5
	 */
	public function __construct() {

		// Set default class properties
		$this->url = 'https://' . $this->route;

		// Set our object.
		$this->set();

		// Pages
		add_action( 'admin_menu', array( $this, 'register_review_page' ) );

		// Action
		add_action( 'admin_post_omapi_send_review', array( $this, 'omapi_send_review' ) );

		// Review Notices
		add_action( 'admin_notices', array( $this, 'review' ) );
		add_action( 'wp_ajax_omapi_dismiss_review', array( $this, 'dismiss_review' ) );

		// Admin Notices
		add_action( 'admin_notices', array( $this, 'notices' ) );

	}

	/**
	 * Sets our object instance and base class instance.
	 *
	 * @since 1.1.4.5
	 */
	public function set() {

		self::$instance = $this;
		$this->base     = OMAPI::get_instance();

	}

	/**
	 * Loads the OptinMonster admin menu.
	 *
	 * @since 1.1.4.5
	 */
	public function register_review_page() {

		$this->hook = add_submenu_page(
			__( 'OptinMonster', 'optin-monster-api' ), //parent slug
			__( 'Review OptinMonster', 'optin-monster-api' ), //page title,
			__( 'Thank you for your Review', 'optin-monster-api'),
			apply_filters( 'optin_monster_api_menu_cap', 'manage_options' ), //cap
			'optin-monster-api-review', //slug
			array($this, 'callback_to_display_page') //callback
		);

		// Load settings page assets.
		if ( $this->hook ) {
			add_action( 'load-' . $this->hook, array( $this, 'assets' ) );
		}

	}

	/**
	 * Loads assets for the settings page.
	 *
	 * @since 1.1.4.2
	 */
	public function assets() {

		add_action( 'admin_enqueue_scripts', array( $this, 'styles' ) );
		add_filter( 'admin_footer_text', array( $this, 'footer' ) );
		add_action( 'in_admin_header', array( $this->base->menu, 'output_plugin_screen_banner') );


	}

	/**
	 * Register and enqueue settings page specific CSS.
	 *
	 * @since 1.1.4.2
	 */
	public function styles() {

		wp_register_style( $this->base->plugin_slug . '-settings', plugins_url( '/assets/css/settings.css', OMAPI_FILE ), array(), $this->base->version );
		wp_enqueue_style( $this->base->plugin_slug . '-settings' );


	}


	/**
	 * Customizes the footer text on the OptinMonster settings page.
	 *
	 * @since 1.1.4.5
	 *
	 * @param string $text  The default admin footer text.
	 * @return string $text Amended admin footer text.
	 */
	public function footer( $text ) {

		$url  = 'https://wordpress.org/support/plugin/optinmonster/reviews?filter=5#new-post';
		$text = sprintf( __( 'Please rate <strong>OptinMonster</strong> <a href="%s" target="_blank" rel="noopener">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%s" target="_blank">WordPress.org</a> to help us spread the word. Thank you from the OptinMonster team!', 'optin-monster-api' ), $url, $url );
		return $text;

	}

	/**
	 * Outputs the Review Page.
	 *
	 * @since 1.1.4.5
	 */
	public function callback_to_display_page() {


		// Get any saved meta
		$review_meta = get_user_meta( get_current_user_id(), 'omapi_review_data', true );

		// Get autofill details
		$current_user           = wp_get_current_user();
		$current_usermail       = isset ( $review_meta['user-email'] ) ? $review_meta['user-email'] : $current_user->user_email;
		$current_userfullname   = isset ( $review_meta['user-name'] ) ? $review_meta['user-name'] : $current_user->user_firstname . ' ' . $current_user->user_lastname;
		$current_credentials    = isset ( $review_meta['user-creds'] ) ? $review_meta['user-creds'] : '';
		$current_source         = isset ( $review_meta['user-source'] ) ? $review_meta['user-source'] : get_site_url();
		$user_review            = isset ( $review_meta['user-review'] ) ? $review_meta['user-review'] : '';
		$review_status          = isset ( $review_meta['status'] ) ? $review_meta['status'] : 'unfinished';

		?>

		<div class="wrap omapi-page">
		<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
		<div class="review-container">

			<div id="review-panel" >
				<div class="omapi-well omapi-mini-well">
				<?php if ($review_status == 'finished') : ?>

					<p><?php _e('Thank you for sending us your review. Please consider leaving us a review on WordPress.org as well. We do really appreciate your time.', 'optin-monster-api'); ?></p>
				<?php else: ?>
					<p><?php _e('Thank you for taking a minute to send us a review.', 'optin-monster-api'); ?></p>

				<?php endif; ?>
				</div>
				<div class="omapi-review-form">
						<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
							<input type="hidden" name="action" value="omapi_send_review">
							<?php wp_nonce_field('omapi-submit-review','omapi-review-nonce') ?>
							<div class="omapi-field-box">
								<div class="omapi-field-wrap">
									<label for="user-review"><?php _e('Review (required)', 'optin-monster-api'); ?></label>
									<textarea id="user-review" tabindex="1" rows="5" name="user-review"><?php echo $user_review; ?></textarea>
									<span class="omapi-field-desc"><?php _e('Be as specific as you would like (140 to 1000 characters).', 'optin-monster-api'); ?></span>
								</div>
							</div>
							<div class="omapi-field-box">
								<div class="omapi-field-wrap">
									<label for="user-email"><?php _e('Email (required)', 'optin-monster-api'); ?></label>
									<input type="text" id="user-email" placeholder="<?php _e('Email', 'optin-monster-api')?>" tabindex="1" name="user-email" value="<?php echo $current_usermail ?>">
								</div>
							</div>
							<div class="omapi-field-box">
								<div class="omapi-field-wrap">
									<label><?php _e('Your Name (required)', 'optin-monster-api'); ?></label>
									<input type="text" id="user-name" placeholder="<?php _e('Your Name', 'optin-monster-api')?>" tabindex="1" name="user-name" value="<?php echo $current_userfullname ?>">
									<span class="omapi-field-desc"><?php _e('The name you would like shown if featured.', 'optin-monster-api'); ?></span>
								</div>
							</div>
							<div class="omapi-field-box">
								<div class="omapi-field-wrap">
									<label><?php _e('Title and Company', 'optin-monster-api'); ?></label>
									<input type="text" id="user-credentials" placeholder="<?php _e('Title, Company Name', 'optin-monster-api')?>" tabindex="1" name="user-credentials" value="<?php echo $current_credentials ?>">
									<span class="omapi-field-desc"><?php _e('Professional Title and Company', 'optin-monster-api'); ?></span>
								</div>
							</div>
							<div class="omapi-field-box">
								<div class="omapi-field-wrap">
									<label><?php _e('Where are you using OptinMonster', 'optin-monster-api'); ?></label>
									<input type="text" id="user-source" placeholder="http://" tabindex="1" name="user-source" value="<?php echo $current_source; ?>">
									<span class="omapi-field-desc"><?php _e('May be shown if featured.', 'optin-monster-api'); ?></span>
								</div>
							</div>
							<p class="submit">
								<?php if ($review_status !== 'finished') : ?>
								<button class="button button-primary" type="submit"><?php _e('Send Review', 'optin-monster-api'); ?></button>
								<?php endif; ?>
							</p>

						</form>
				</div>
			</div>

		</div>
	<?php

	}

	/**
	 * Handle review submission
	 *
	 * This is called via admin_post_{action} when form is submitted.
	 *
	 * @since 1.1.4.5
	 *
	 */
	public function omapi_send_review() {

		// Check our form nonce
		if ( ! wp_verify_nonce( $_POST['omapi-review-nonce'], 'omapi-submit-review' ) ) {
			die( 'Unable to process request');
		}

		$user_id = get_current_user_id();

		// Setup empty defaults
		$user_review    = '';
		$user_email     = '';
		$user_name      = '';
		$user_creds     = '';
		$user_source    = '';
		$user_api       = $this->base->get_api_credentials();

		if ( isset( $_POST['user-review'] ) ) {
			$user_review = sanitize_text_field( $_POST['user-review'] );
		}
		if ( isset( $_POST['user-email'] ) ) {
			$user_email = sanitize_email( $_POST['user-email'] );
		}
		if ( isset( $_POST['user-name'] ) ) {
			$user_name = sanitize_text_field( $_POST['user-name'] );
		}
		if ( isset( $_POST['user-credentials'] ) ) {
			$user_creds = sanitize_text_field( $_POST['user-credentials'] );
		}
		if ( isset( $_POST['user-source'] ) ) {
			$user_source = esc_url( $_POST['user-source'] );
		}

		// Add data into query
		$data_array = array(
			'user-review'   => $user_review,
			'user-email'    => $user_email,
			'user-name'     => $user_name,
			'user-creds'    => $user_creds,
			'user-source'   => $user_source,
			'user-key'      => $user_api['key'],
			'user-id'       => $user_api['user']
		);

		// Save everything passed in to user meta as well
		update_user_meta( $user_id, 'omapi_review_data', $data_array );


		// Check for Name, Review, Email
		if (  $user_name === '' || $user_review === '' || $user_email === '' ) {
			$message = 'required-fields';
			wp_redirect( add_query_arg( 'action', $message , admin_url( 'admin.php?page=optin-monster-api-review' ) ) );
			exit;
		}

		// Build the headers of the request.
		$headers = array(
			'Content-Type'   => 'application/x-www-form-urlencoded',
			'Cache-Control'  => 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0',
			'Pragma'         => 'no-cache',
			'Expires'        => 0,
			'OMAPI-Referer'  => site_url(),
			'OMAPI-Sender'   => 'WordPress'
		);

		// Setup data to be sent to the API.
		$data = array(
			'headers'   => $headers,
			'body'      => $data_array,
			'timeout'   => 3000,
			'sslverify' => false
		);

		// Perform the query and retrieve the response.
		$response      = wp_remote_post( esc_url_raw( $this->url ), $data );
		$response_body = json_decode( wp_remote_retrieve_body( $response ) );

		if ( is_wp_error( $response_body ) ) {
			//$action_query = 'error';
		}

		$message = isset($action_query) ? $action_query : 'success';

		// Update array
		$data_array['status'] = 'finished';

		// Add status to review meta
		update_user_meta( $user_id, 'omapi_review_data', $data_array );

		//reload review page and end things
		wp_redirect( add_query_arg( 'action', $message , admin_url( 'admin.php?page=optin-monster-api-review' ) ) );
		exit;
	}

	/**
	 * Add admin notices as needed for reviews.
	 *
	 * @since 1.1.6.1
	 */
	public function review() {
		// Verify API credentials have been set.
		if ( ! $this->base->get_api_credentials() ) {
			return;
		}

		// Verify that we can do a check for reviews.
		$review = get_option( 'omapi_review' );
		$time   = time();
		$load   = false;

		if ( ! $review ) {
			$review = array(
				'time'      => $time,
				'dismissed' => false
			);
			$load = true;
		} else {
			// Check if it has been dismissed or not.
			if ( (isset( $review['dismissed'] ) && ! $review['dismissed']) && (isset( $review['time'] ) && (($review['time'] + DAY_IN_SECONDS) <= $time)) ) {
				$load = true;
			}
		}

		// If we cannot load, return early.
		if ( ! $load ) {
			return;
		}

		// Update the review option now.
		update_option( 'omapi_review', $review );

		// Run through optins on the site to see if any have been loaded for more than a week.
		$valid  = false;
		$optins = $this->base->get_optins();
		if ( ! $optins ) {
			return;
		}

		foreach ( $optins as $optin ) {
			// Verify the optin has been enabled.
			$enabled = get_post_meta( $optin->ID, '_omapi_enabled', true );
			if ( ! $enabled ) {
				continue;
			}

			// Check the creation date of the local optin. It must be at least one week after.
			$created = isset( $optin->post_date ) ? strtotime( $optin->post_date ) + (7 * DAY_IN_SECONDS) : false;
			if ( ! $created ) {
				continue;
			}

			if ( $created <= $time ) {
				$valid = true;
				break;
			}
		}

		// If we don't have a valid optin yet, return.
		if ( ! $valid ) {
			return;
		}

		// We have a candidate! Output a review message.
		?>
		<div class="notice notice-info is-dismissible om-review-notice">
			<p><?php _e( 'Hey, I noticed you have been using OptinMonster for 7 days now - that\'s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress? This will help us spread the word and boost our motivation - thanks!', 'optin-monster-api' ); ?></p>
			<p><strong><?php _e( '~ Syed Balkhi<br>Co-Founder of OptinMonster', 'optin-monster-api' ); ?></strong></p>
			<p>
				<a href="https://wordpress.org/support/plugin/optinmonster/reviews?filter=5#new-post" class="om-dismiss-review-notice om-review-out" target="_blank" rel="noopener"><?php _e( 'Ok, you deserve it', 'optin-monster-api' ); ?></a><br>
				<a href="#" class="om-dismiss-review-notice" target="_blank" rel="noopener"><?php _e( 'Nope, maybe later', 'optin-monster-api' ); ?></a><br>
				<a href="#" class="om-dismiss-review-notice" target="_blank" rel="noopener"><?php _e( 'I already did', 'optin-monster-api' ); ?></a><br>
			</p>
		</div>
		<script type="text/javascript">
			jQuery(document).ready( function($) {
				$(document).on('click', '.om-dismiss-review-notice, .om-review-notice button', function( event ) {
					if ( ! $(this).hasClass('om-review-out') ) {
						event.preventDefault();
					}

					$.post( ajaxurl, {
						action: 'omapi_dismiss_review'
					});

					$('.om-review-notice').remove();
				});
			});
		</script>
		<?php
	}

	/**
	 * Dismiss the review nag
	 *
	 * @since 1.1.6.1
	 */
	public function dismiss_review() {
		$review = get_option( 'omapi_review' );
		if ( ! $review ) {
			$review = array();
		}

		$review['time']      = time();
		$review['dismissed'] = true;

		update_option( 'omapi_review', $review );
		die;
	}

	/**
	 * Add admin notices as needed for review
	 *
	 * @since 1.1.4.5
	 *
	 */
	public function notices() {

		if ( ! isset ( $_GET['action'] ) ) {
			return;
		}

		if ( 'success' === $_GET['action'] ) {
			echo '<div class="notice notice-success"><p>' . __( 'Review has been sent.', 'optin-monster-api' );
			echo '<a href="' . esc_url_raw( admin_url( 'admin.php?page=optin-monster-api-settings' ) ) . '" class="button button-primary button-large omapi-new-optin" title="Go to OptinMonster overview" style="margin-left: 15px;">Return to OptinMonster</a></p></div>';

		}
		if ( 'required-fields' === $_GET['action'] ) {
			echo '<div class="error is-dismissible"><p>' . __( 'Your Name, Review, and Email address are required to submit your review.', 'optin-monster-api' ) . '</p></div>';
		}

	}

}