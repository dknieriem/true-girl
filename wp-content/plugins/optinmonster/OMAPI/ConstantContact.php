<?php
/**
 * Review class.
 *
 * @since 1.6.0
 *
 * @package OMAPI
 * @author  Devin Vinson
 */
class OMAPI_ConstantContact {

	/**
	 * Holds the class object.
	 *
	 * @since 1.6.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.6.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the review slug.
	 *
	 * @since 1.6.0
	 *
	 * @var string
	 */
	public $hook;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.6.0
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Sign up link.
	 *
	 * @since 1.6.0
	 * @var string
	 */
	public $sign_up = 'https://optinmonster.com/refer/constant-contact/';

	/**
	 * Primary class constructor.
	 *
	 * @since 1.6.0
	 */
	public function __construct() {

		// Set our object.
		$this->set();

		// Pages
		add_action( 'admin_menu', array( $this, 'register_cc_page' ) );
		add_action( 'admin_notices', array( $this, 'constant_contact_cta_notice' ) );
		add_action( 'wp_ajax_om_constant_contact_dismiss', array( $this, 'constant_contact_dismiss' ) );
	}

	/**
	 * Sets our object instance and base class instance.
	 *
	 * @since 1.6.0
	 */
	public function set() {

		self::$instance = $this;
		$this->base     = OMAPI::get_instance();

	}

	/**
	 * Loads the OptinMonster admin menu.
	 *
	 * @since 1.6.0
	 */
	public function register_cc_page() {

		$this->hook = add_submenu_page(
			'optin-monster-api-settings-no-menu', // parent slug
			__( 'OptinMonster with Constant Contact', 'optin-monster-api' ), // page title,
			__( 'OptinMonster with Constant Contact', 'optin-monster-api' ),
			apply_filters( 'optin_monster_api_menu_cap', 'manage_options' ), // cap
			'optin-monster-constant-contact', // slug
			array( $this, 'display_page' ) // callback
		);

		// Load settings page assets.
		if ( $this->hook ) {
			add_action( 'load-' . $this->hook, array( $this, 'assets' ) );
		}

	}

	/**
	 * Add admin notices to connect to Constant Contact.
	 *
	 * @since 1.6.0
	 */
	public function constant_contact_cta_notice() {

		// Only consider showing the review request to admin users.
		if ( ! is_super_admin() ) {
			return;
		}

		// Only display the notice if it has not been dismissed.
		$dismissed = get_option( 'optinmonster_constant_contact_dismiss', false );

		if ( $dismissed ) {
			return;
		}

		// Only show on the main dashboard page (wp-admin/index.php)
		// or any OptinMonster plugin-specific screen.
		$can_show = $is_om_page = isset( $_GET['page'] ) && 'optin-monster-api-settings' === $_GET['page'];
		if ( ! $can_show ) {
			$can_show = function_exists( 'get_current_screen' ) && 'dashboard' === get_current_screen()->id;
		}

		if ( ! $can_show ) {
			return;
		}

		$connect    = admin_url( 'admin.php?page=optin-monster-api-settings' );
		$learn_more = admin_url( 'admin.php?page=optin-monster-constant-contact' );

		// Output the notice message.
		?>
		<div class="notice notice-info is-dismissible om-constant-contact-notice">
			<p>
				<?php
				echo wp_kses(
					__( 'Get the most out of the <strong>OptinMonster</strong> plugin &mdash; use it with an active Constant Contact account.', 'optin-monster-api' ),
					array(
						'strong' => array(),
					)
				);
				?>
			</p>
			<p>
				<a href="<?php echo esc_url( $this->sign_up ); ?>" class="button-primary" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Try Constant Contact for Free', 'optin-monster-api' ); ?>
				</a>
				<?php if ( ! $is_om_page ) { ?>
					<a href="<?php echo esc_url( $connect ); ?>" class="button-secondary">
						<?php esc_html_e( 'Connect your existing account', 'optin-monster-api' ); ?>
					</a>
				<?php } ?>
				<?php
				printf(
					wp_kses(
						/* translators: %s - OptinMonster Constant Contact internal URL. */
						__( 'Learn More about the <a href="%s">power of email marketing</a>', 'optin-monster-api' ),
						array(
							'a' => array(
								'href' => array(),
							),
						)
					),
					esc_url( $learn_more )
				);
				?>
			</p>
		</div>
		<style type="text/css">
			.om-constant-contact-notice {
				/*border-left-color: #1a5285;*/
			}

			.om-constant-contact-notice p:first-of-type {
				margin: 16px 0 8px;
			}

			.om-constant-contact-notice p:last-of-type {
				margin: 8px 0 16px;
			}

			.om-constant-contact-notice .button-primary,
			.om-constant-contact-notice .button-secondary {
				display: inline-block;
				margin: 0 10px 0 0;
			}
		</style>
		<script type="text/javascript">
			jQuery( function ( $ ) {
				$( document ).on( 'click', '.om-constant-contact-notice button', function ( event ) {
					event.preventDefault();
					$.post( ajaxurl, { action: 'om_constant_contact_dismiss' } );
					$( '.om-constant-contact-notice' ).remove();
				} );
			} );
		</script>
		<?php
	}


	/**
	 * Dismiss the Constant Contact admin notice.
	 *
	 * @since 1.6.0
	 */
	public function constant_contact_dismiss() {

		update_option( 'optinmonster_constant_contact_dismiss', 1, false );
		wp_send_json_success();
	}

	/**
	 * Loads assets for the settings page.
	 *
	 * @since 1.6.0
	 */
	public function assets() {
		add_action( 'admin_enqueue_scripts', array( $this, 'styles' ) );
		add_filter( 'admin_footer_text', array( $this, 'footer' ) );
		add_action( 'in_admin_header', array( $this->base->menu, 'output_plugin_screen_banner' ) );
	}

	/**
	 * Register and enqueue settings page specific CSS.
	 *
	 * @since 1.6.0
	 */
	public function styles() {
		wp_enqueue_style( $this->base->plugin_slug . '-settings', plugins_url( '/assets/css/settings.css', OMAPI_FILE ), array(), $this->base->version );
	}


	/**
	 * Customizes the footer text on the OptinMonster settings page.
	 *
	 * @since 1.6.0
	 *
	 * @param string $text  The default admin footer text.
	 * @return string $text Amended admin footer text.
	 */
	public function footer( $text ) {

		$url  = 'https://wordpress.org/support/plugin/optinmonster/reviews?filter=5#new-post';
		$text = sprintf( __( 'Please rate <strong>OptinMonster</strong> <a href="%1$s" target="_blank" rel="noopener">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%1$s" target="_blank" rel="noopener noreferrer">WordPress.org</a> to help us spread the word. Thank you from the OptinMonster team!', 'optin-monster-api' ), $url );
		return $text;

	}

	/**
	 * Outputs the Review Page.
	 *
	 * TODO: Update the copy for OM
	 *
	 * @since 1.6.0
	 */
	public function display_page() {
		$images_url = esc_url( plugins_url( '/assets/css/images/', OMAPI_FILE ) );
		?>
		<style type="text/css">
			.admin_page_optin-monster-constant-contact #wpbody {
				background: #fff;
			}

			.notice {
				display: none;
			}

			.om-cc-wrap {
				max-width: 970px;
			}

			.om-cc-wrap h1 {
				/*color: #1a5285;*/
				font-size: 30px;
				margin: 0 0 15px 0;
			}

			.om-cc-wrap h2 {
				/*color: #1a5285;*/
				font-size: 26px;
				margin: 0 0 15px 0;
				text-align: left;
			}

			.om-cc-wrap p {
				font-size: 16px;
				font-weight: 300;
				color: #333;
				margin: 1.2em 0;
			}

			.om-cc-wrap ul,
			.om-cc-wrap ol {
				margin: 1.6em 2.5em 2em;
				line-height: 1.5;
				font-size: 16px;
				font-weight: 300;
			}

			.om-cc-wrap ul {
				list-style: disc;
			}

			.om-cc-wrap li {
				margin-bottom: 0.8em;
			}

			.om-cc-wrap hr {
				margin: 2.2em 0;
			}

			.om-cc-wrap .logo {
				float: right;
				margin-top: 0.8em;
				border: 1px solid #ddd;
			}

			.om-cc-wrap .reasons {
				margin: 2.2em 400px 2.2em 2em;
			}

			.om-cc-wrap .reasons li {
				margin-bottom: 1.4em;
			}

			.om-cc-wrap .steps {
				clear: both;
				display: -webkit-box;
				display: -ms-flexbox;
				display: flex;
				-ms-flex-wrap: wrap;
				    flex-wrap: wrap;
			}

			.om-cc-wrap .step {
				-webkit-box-flex: 1;
				    -ms-flex-positive: 1;
				        flex-grow: 1;
				-ms-flex-negative: 1;
				    flex-shrink: 1;
				margin-bottom: 1.4em;
				padding: 0 1em 0 0;
				-ms-flex-preferred-size: 50%;
				    flex-basis: 50%;
				-webkit-box-sizing: border-box;
				box-sizing: border-box;
			}

			.om-cc-wrap .step a {
				-webkit-box-shadow: rgba(0, 35, 60, 0.1) 3px 7px 13px 0px;
				box-shadow: rgba(0, 35, 60, 0.1) 3px 7px 13px 0px;
				border: 1px solid #efefef;
				display: block;
			}

			.om-cc-wrap .step a:hover {
				border-color: #d9d9d9;
			}

			.om-cc-wrap .step img {
				max-width: 100%;
				height: auto;
				display: block;
			}

			.om-cc-wrap .dashicons-yes {
				color: #19BE19;
				font-size: 26px;
			}

			.om-cc-wrap .button {
				background-color: #0078C3;
				border: 1px solid #005990;
				border-radius: 4px;
				color: #fff;
				font-size: 16px;
				font-weight: 600;
				height: auto;
				line-height: 1;
				margin-bottom: 10px;
				padding: 14px 30px;
				text-align: center;
			}

			.om-cc-wrap .button:hover,
			.om-cc-wrap .button:focus {
				background-color: #005990;
				color: #fff
			}

			@media only screen and (max-width: 767px) {
				.om-cc-wrap h1 {
					font-size: 26px;
				}

				.om-cc-wrap h2 {
					font-size: 22px;
				}

				.om-cc-wrap p {
					font-size: 14px;
				}

				.om-cc-wrap ul,
				.om-cc-wrap ol {
					font-size: 14px;
				}

				.om-cc-wrap .logo {
					width: 120px;
				}

				.om-cc-wrap .reasons {
					margin-right: 150px;
				}
			}
		</style>
		<div class="wrap omapi-page om-cc-wrap">
			<h1><?php esc_html_e( 'Grow Your Website with OptinMonster + Email Marketing', 'optin-monster-api' ); ?></h1>
			<p><?php esc_html_e( 'Wondering if email marketing is really worth your time?', 'optin-monster-api' ); ?></p>
			<p><?php echo wp_kses( __( 'Email is hands-down the most effective way to nurture leads and turn them into customers, with a return on investment (ROI) of <strong>$44 back for every $1 spent</strong> according to the Direct Marketing Association.', 'optin-monster-api' ), array( 'strong' => array() ) ); ?></p>
			<p><?php esc_html_e( 'Here are 3 big reasons why every smart business in the world has an email list:', 'optin-monster-api' ); ?></p>
			<a href="<?php echo esc_url( $this->sign_up ); ?>" target="_blank" rel="noopener noreferrer">
				<img width="350" class="logo" src="<?php echo $images_url .'constant_OM.png'; ?>" alt="<?php esc_attr_e( 'OptinMonster with Constant Contact - Try us free', 'optin-monster-api' ); ?>"/>
			</a>
			<ol class="reasons">
				<li><?php echo wp_kses( __( '<strong>Email is still #1</strong> - At least 91% of consumers check their email on a daily basis. You get direct access to your subscribers, without having to play by social media&#39;s rules and algorithms.', 'optin-monster-api' ), array( 'strong' => array() ) ); ?></li>
				<li><?php echo wp_kses( __( '<strong>You own your email list</strong> - Unlike with social media, your list is your property and no one can revoke your access to it.', 'optin-monster-api' ), array( 'strong' => array() ) ); ?></li>
				<li><?php echo wp_kses( __( '<strong>Email converts</strong> - People who buy products marketed through email spend 138% more than those who don&#39;t receive email offers.', 'optin-monster-api' ), array( 'strong' => array() ) ); ?></li>
			</ol>
			<p><?php esc_html_e( 'That&#39;s why it&#39;s crucial to start collecting email addresses and building your list as soon as possible.', 'optin-monster-api' ); ?></p>
			<p>
				<?php
				printf(
					wp_kses(
						/* translators: %s - WPBeginners.com Guide to Email Lists URL. */
						__( 'For more details, see this guide on <a href="%s" target="_blank" rel="noopener noreferrer">why building your email list is so important</a>.', 'optin-monster-api' ),
						array(
							'a' => array(
								'href'   => array(),
								'target' => array(),
								'rel'    => array(),
							),
						)
					),
					'https://optinmonster.com/beginners-guide-to-email-marketing/'
				);
				?>
			</p>
			<hr/>
			<h2><?php esc_html_e( 'You&#39;ve Already Started - Here&#39;s the Next Step (It&#39;s Easy)', 'optin-monster-api' ); ?></h2>
			<p><?php esc_html_e( 'Here are the 3 things you need to build an email list:', 'optin-monster-api' ); ?></p>
			<ol>
				<li><?php esc_html_e( 'A Website or Blog', 'optin-monster-api' ); ?> <span class="dashicons dashicons-yes"></span></li>
				<?php // TODO: update the following line ?>
				<li><?php esc_html_e( 'High-Converting Form Builder', 'optin-monster-api' ); ?> <span class="dashicons dashicons-yes"></span></li>
				<li><strong><?php esc_html_e( 'The Best Email Marketing Service', 'optin-monster-api' ); ?></strong></li>
			</ol>
			<p><?php esc_html_e( 'With a powerful email marketing service like Constant Contact, you can instantly send out mass notifications and beautifully designed newsletters to engage your subscribers.', 'optin-monster-api' ); ?></p>
			<p>
				<a href="<?php echo esc_url( $this->sign_up ); ?>" class="button" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Get Started with Constant Contact for Free', 'optin-monster-api' ); ?>
				</a>
			</p>
			<p><?php esc_html_e( 'OptinMonster plugin makes it fast and easy to capture all kinds of visitor information right from your WordPress site - even if you don&#39;t have a Constant Contact account.', 'optin-monster-api' ); ?></p>
			<p><?php esc_html_e( 'But when you combine OptinMonster with Constant Contact, you can nurture your contacts and engage with them even after they leave your website. When you use Constant Contact + OptinMonster together, you can:', 'optin-monster-api' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'Seamlessly add new contacts to your email list', 'optin-monster-api' ); ?></li>
				<li><?php esc_html_e( 'Create and send professional email newsletters', 'optin-monster-api' ); ?></li>
				<li><?php esc_html_e( 'Get expert marketing and support', 'optin-monster-api' ); ?></li>
			</ul>
			<p>
				<a href="<?php echo esc_url( $this->sign_up ); ?>" target="_blank" rel="noopener noreferrer">
					<strong><?php esc_html_e( 'Try Constant Contact Today', 'optin-monster-api' ); ?></strong>
				</a>
			</p>
			<hr/>
			<h2><?php esc_html_e( 'OptinMonster Makes List Building Easy', 'optin-monster-api' ); ?></h2>
			<p><?php esc_html_e( 'When creating OptinMonster, our goal was to make a conversion optimization tool that was both EASY and POWERFUL.', 'optin-monster-api' ); ?></p>
			<p><?php esc_html_e( 'Here&#39;s how it works.', 'optin-monster-api' ); ?></p>
			<div class="steps">
				<div class="step1 step">
					<a href="<?php echo $images_url . 'om-step-1.png'; ?>"><img src="<?php echo $images_url . 'om-step-1-sm.png'; ?>"></a>
					<p><?php esc_html_e( '1. Select a design from our beautiful, high-converting template library.', 'optin-monster-api' ); ?></p>
				</div>
				<div class="step2 step">
					<a href="<?php echo $images_url . 'om-step-2.png'; ?>"><img src="<?php echo $images_url . 'om-step-2-sm.png'; ?>"></a>
					<p><?php esc_html_e( '2. Drag and drop elements to completely customize the look and feel of your campaign.', 'optin-monster-api' ); ?></p>
				</div>
				<div class="step3 step">
					<a href="<?php echo $images_url . 'om-step-3.png'; ?>"><img src="<?php echo $images_url . 'om-step-3-sm.png'; ?>"></a>
					<p><?php esc_html_e( '3. Connect your Constant Contact email list.', 'optin-monster-api' ); ?></p>
				</div>
				<div class="step4 step">
					<a href="<?php echo $images_url . 'om-step-4.png'; ?>"><img src="<?php echo $images_url . 'om-step-4-sm.png'; ?>"></a>
					<p><?php esc_html_e( '4. Sync your campaign to your WordPress site, then hit Go Live.', 'optin-monster-api' ); ?></p>
				</div>
			</div>
			<p><?php esc_html_e( 'It doesn&#39;t matter what kind of business you run, what kind of website you have, or what industry you are in - you need to start building your email list today.', 'optin-monster-api' ); ?></p>
			<p><?php esc_html_e( 'With Constant Contact + OptinMonster, growing your list is easy.', 'optin-monster-api' ); ?></p>
			<p>
				<a href="<?php echo esc_url( $this->sign_up ); ?>" target="_blank" rel="noopener noreferrer">
					<strong><?php esc_html_e( 'Try Constant Contact Today', 'optin-monster-api' ); ?></strong>
				</a>
			</p>
		</div>
		<?php
	}

}
