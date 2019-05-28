<?php
/**
 * Content class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */
class OMAPI_Content {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $base;

	/**
	 * The current view slug
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $view;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Set our object.
		$this->set();

		// Load actions and filters.
		add_action( 'optin_monster_api_content_before', array( $this, 'form_start' ), 0, 2 );
		add_action( 'optin_monster_api_content_after', array( $this, 'form_end' ), 9999 );
		add_action( 'optin_monster_api_content_api', array( $this, 'api' ), 10, 2 );
		add_action( 'optin_monster_api_content_optins', array( $this, 'optins' ), 10, 2 );
		add_action( 'optin_monster_api_content_settings', array( $this, 'settings' ), 10, 2 );
		add_action( 'optin_monster_api_content_support', array( $this, 'support' ), 10, 2 );
		add_action( 'optin_monster_api_content_migrate', array( $this, 'migrate' ), 10, 2 );

	}

	/**
	 * Sets our object instance and base class instance.
	 *
	 * @since 1.0.0
	 */
	public function set() {

		self::$instance = $this;
		$this->base     = OMAPI::get_instance();
		$this->view     = isset( $_GET['optin_monster_api_view'] ) ? stripslashes( $_GET['optin_monster_api_view'] ) : $this->base->get_view();
		$this->optin    = isset( $_GET['optin_monster_api_id'] ) ? $this->base->get_optin( absint( $_GET['optin_monster_api_id'] ) ) : false;

	}

	/**
	 * Loads the starting form HTML for the panel content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id    The panel ID we are targeting.
	 * @param string $panel The panel name we are targeting.
	 */
	public function form_start( $id, $panel ) {

		if ( $this->view == 'support' ) :
		?>
			<h3><?php echo esc_html( $panel ); ?></h3>
		<?php
		else:
		?>
		<form id="omapi-form-<?php echo sanitize_html_class( $id ); ?>" class="omapi-form" method="post" action="<?php echo esc_attr( stripslashes( $_SERVER['REQUEST_URI'] ) ); ?>">
			<?php wp_nonce_field( 'omapi_nonce_' . $id, 'omapi_nonce_' . $id ); ?>
			<input type="hidden" name="omapi_panel" value="<?php echo $id; ?>" />
			<input type="hidden" name="omapi_save" value="true" />
			<?php if ( 'settings' == $this->view ) : ?>
			<input type="hidden" name="omapi[<?php echo esc_attr( $this->view ); ?>][wpform]" value="true" />
			<?php endif; ?>
			<h3>
				<?php if ( isset( $_GET['optin_monster_api_action'] ) && 'edit' == $_GET['optin_monster_api_action'] ) : ?>
				<?php printf( __( 'Output Settings for %s', 'optin-monster-api' ), esc_html( $this->optin->post_title ) ); ?>
				<span class="omapi-back"><a class="button button-secondary button-small" href="<?php echo esc_url_raw( add_query_arg( array( 'optin_monster_api_view' => 'optins' ), admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ); ?>" title="<?php esc_attr_e( 'Back to campaign overview', 'optin-monster-api' ); ?>"><?php _e( 'Back to Overview', 'optin-monster-api' ); ?></a></span>
				<?php else : ?>
				<?php echo esc_html( $panel ); ?>
				<?php endif; ?>
			</h3>
		<?php
		endif;

		// Action to load success/reset messages.
		do_action( 'optin_monster_api_messages_' . $id );

	}

	/**
	 * Loads the ending form HTML for the panel content.
	 *
	 * @since 1.0.0
	 */
	public function form_end() {

		// Load different form buttons based on if credentials have been supplied or not.
		if ( ! $this->base->get_api_credentials() && 'support' !== $this->view ) :
		?>
			<p class="submit">
				<input class="button button-primary" type="submit" name="omapi_submit" value="<?php esc_attr_e( 'Connect to OptinMonster', 'optin-monster-api' ); ?>" tabindex="749" />
			</p>
		</form>
		<?php
		elseif ( 'optins' == $this->view ) :
			if ( isset( $_GET['optin_monster_api_action'] ) && 'edit' == $_GET['optin_monster_api_action'] ) :
			?>
				<p class="submit">
					<input class="button button-primary" type="submit" name="submit" value="<?php esc_attr_e( 'Save Settings', 'optin-monster-api' ); ?>" tabindex="749" />
				</p>
			</form>
			<?php
			else :
			?>
				<p class="submit">
					<input class="button button-primary" type="submit" name="omapi_refresh" value="<?php esc_attr_e( 'Refresh Campaigns', 'optin-monster-api' ); ?>" tabindex="749" />
					<a class="button button-secondary" href="<?php echo wp_nonce_url( esc_url_raw( add_query_arg( array( 'optin_monster_api_view' => $this->view, 'optin_monster_api_action' => 'cookies' ), admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ), 'omapi-action' ); ?>" title="<?php esc_attr_e( 'Clear Local Cookies', 'optin-monster-api' ); ?>"><?php _e( 'Clear Local Cookies', 'optin-monster-api' ); ?></a>
				</p>
			</form>
			<?php
			endif;
		elseif ( 'migrate' == $this->view ) :
			?>
		</form>
		<?php
		elseif ( 'support' == $this->view ) :

			//you get nothing

		else :
		?>
			<p class="submit">
				<input class="button button-primary" type="submit" name="submit" value="<?php esc_attr_e( 'Save Settings', 'optin-monster-api' ); ?>" tabindex="749" />
			</p>
		</form>
		<?php
		endif;

	}

	/**
	 * Loads the content output for the API panel.
	 *
	 * @since 1.0.0
	 *
	 * @param string $panel  The panel name we are targeting.
	 * @param object $object The menu object (useful for settings helpers).
	 */
	public function api( $panel, $object ) {

		$link = $this->base->menu->get_action_link();
		$text = $this->base->menu->has_trial_link() ? 'Click here to start your free 30-day trial!' : 'Click here to learn more about OptinMonster!';

		$credentials = $this->base->get_api_credentials();

		if ( ! $credentials ) : ?>
		<p class="omapi-red"><strong><?php _e( 'You must authenticate your OptinMonster account before you can use OptinMonster on this site.', 'optin-monster-api' ); ?></strong></p>
		<p><em><?php printf( __( 'Need an OptinMonster account? <a href="%s" title="Click here to learn more about OptinMonster" target="_blank">%s</a>', 'optin-monster-api' ), $link, $text ); ?></em></p>
		<?php endif; ?>

		<?php echo $object->get_setting_ui( 'api', 'apikey' ); ?>

		<?php // If we have credentials only show the old stuff if it is saved ?>
		<?php if ( $credentials ) : ?>
			<?php if ( isset( $credentials['api'] ) && '' != $credentials['api'] || isset( $credentials['key'] ) && '' != $credentials['key'] ) : ?>
				<p>The Legacy API Username and Key below will be deprecated soon. Please <a href="<?php echo OPTINMONSTER_APP_URL; ?>/account/api/" target="_blank">generate a new API key</a> and paste it above to authenticate using our new and improved REST API.</p>
				<?php echo $object->get_setting_ui( 'api', 'user' ); ?>
				<?php echo $object->get_setting_ui( 'api', 'key' ); ?>
			<?php endif; ?>
		<?php endif; ?>

		<?php

	}

	/**
	 * Loads the content output for the Database panel.
	 *
	 * @since 1.0.0
	 *
	 * @param string $panel  The panel name we are targeting.
	 * @param object $object The menu object (useful for settings helpers).
	 */
	public function optins( $panel, $object ) {

		$optin_view = isset( $_GET['optin_monster_api_action'] ) && 'edit' == $_GET['optin_monster_api_action'] ? 'edit' : 'overview';
		if ( 'edit' == $optin_view ) {
			$this->optin_edit( $object );
		} else {
			$this->optin_overview( $object );
		}

	}

	/**
	 * Shows the optins loaded on the site.
	 *
	 * @since 1.0.0
	 *
	 * @param object $object The menu object (useful for settings helpers).
	 */
	public function optin_overview( $object ) {

		$optins = $this->base->get_optins();
		$i      = 0;
		if ( $optins ) :
		?>
		<?php foreach ( $optins as $optin ) : $class = 0 == $i ? ' omapi-optin-first' : '';
			if ( (bool) get_post_meta( $optin->ID, '_omapi_enabled', true ) ) {
				$status = '<span class="omapi-green">' . __( 'Live', 'optin-monster-api' ) . '</span>';
				$status_tooltip = __('This campaign is embedded on your site based on your output settings and will load subject to the display rules configured in the campaign builder.', 'optin-monster-api');
			} else {
				$status = '<span class="omapi-red">' . __( 'Disabled', 'optin-monster-api' ) . '</span>';
				$status_tooltip = __('This campaign is not embedded by the plugin anywhere on this site.', 'optin-monster-api');
			}
		?>
		<p class="omapi-optin<?php echo $class; ?>">
			<a href="<?php echo esc_url_raw( add_query_arg( array( 'optin_monster_api_view' => $this->view, 'optin_monster_api_action' => 'edit', 'optin_monster_api_id' => $optin->ID ), admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ); ?>" title="<?php printf( esc_attr__( 'Manage output settings for %s', 'optin-monster-api' ), $optin->post_title ); ?>"><?php echo $optin->post_title; ?></a>
			<span class="omapi-status omapi-has-tooltip" data-toggle="tooltip" data-placement="bottom" title="<?php echo $status_tooltip; ?>"><?php echo $status; ?></span><br>
			<span class="omapi-slug omapi-has-tooltip" data-toggle="tooltip" data-placement="bottom" title="<?php _e('The unique slug of this campaign. Used for shortcodes and embed scripts.', 'optin-monster-api'); ?>"><?php echo $optin->post_name; ?></span>
			<span class="omapi-links"><?php echo $this->get_optin_links( $optin->ID ); ?></span>
		</p>
		<?php $i++; endforeach; ?>
		<?php else : ?>
		<p><strong><?php _e( 'No campaigns could be retrieved for this site.', 'optin-monster-api' ); ?></strong></p>
		<?php
		endif;

	}

	/**
	 * Loads the content output for the Support panel.
	 *
	 * @since 1.0.0
	 *
	 * @param string $panel  The panel name we are targeting.
	 * @param object $object The menu object (useful for settings helpers).
	 */
	public function settings( $panel, $object ) {

		echo $object->get_setting_ui( 'settings', 'cookies' );

	}

	public function support( $panel, $object ) {

		echo $object->get_setting_ui( 'support', 'video' );
		echo $object->get_setting_ui( 'support', 'links' );
		echo $object->get_setting_ui( 'support', 'server-report' );

	}

	/**
	 * Shows the editing interface for optins.
	 *
	 * @since 1.0.0
	 *
	 * @param object $object The menu object (useful for settings helpers).
	 */
	public function optin_edit( $object ) {

		//Check for existing optins
		if ( $this->optin ) {
			$type = get_post_meta( $this->optin->ID, '_omapi_type', true );
			echo $object->get_setting_ui( 'optins', 'enabled' );

			if ( 'sidebar' !== $type ) {
				if ( OMAPI_Utils::is_inline_type( $type ) ) {
					echo $object->get_setting_ui( 'optins', 'automatic' );
					echo $object->get_setting_ui( 'optins', 'automatic_shortcode');
				} else {
					echo $object->get_setting_ui( 'optins', 'global' );
				}
				echo $object->get_setting_ui( 'optins', 'users' );
			}

			echo $object->get_setting_ui( 'optins', 'shortcode' );
			echo $object->get_setting_ui( 'optins', 'shortcode_output' );

			// Add support for MailPoet if the plugin is active.
			if ( $this->base->is_mailpoet_active() ) {
				echo $object->get_setting_ui( 'optins', 'mailpoet' );
				echo $object->get_setting_ui( 'optins', 'mailpoet_list' );
			}
			if ( 'sidebar' !== $type ) {

				// Add WooCommerce Toggle
				if ( $this->base->is_woocommerce_active() ) {
					echo $object->get_setting_ui( 'toggle', 'woocommerce-start');

					echo $object->get_setting_ui( 'optins', 'show_on_woocommerce');
					// Don't show if output can't use the_content filter
					if ( ! OMAPI_Utils::is_inline_type( $type ) ) {
						echo $object->get_setting_ui( 'optins', 'is_wc_shop' );
					}
					echo $object->get_setting_ui( 'optins', 'is_wc_product');
					echo $object->get_setting_ui( 'optins', 'is_wc_cart');
					echo $object->get_setting_ui( 'optins', 'is_wc_checkout');
					echo $object->get_setting_ui( 'optins', 'is_wc_account');
					echo $object->get_setting_ui( 'optins', 'is_wc_endpoint');
					echo $object->get_setting_ui( 'optins', 'is_wc_endpoint_order_pay');
					echo $object->get_setting_ui( 'optins', 'is_wc_endpoint_order_received');
					echo $object->get_setting_ui( 'optins', 'is_wc_endpoint_view_order');
					echo $object->get_setting_ui( 'optins', 'is_wc_endpoint_edit_account');
					echo $object->get_setting_ui( 'optins', 'is_wc_endpoint_edit_address');
					echo $object->get_setting_ui( 'optins', 'is_wc_endpoint_lost_password');
					echo $object->get_setting_ui( 'optins', 'is_wc_endpoint_customer_logout');
					echo $object->get_setting_ui( 'optins', 'is_wc_endpoint_add_payment_method');
					echo $object->get_setting_ui( 'optins', 'is_wc_product_category' );
					echo $object->get_setting_ui( 'optins', 'is_wc_product_tag' );
					echo $object->get_setting_ui( 'toggle', 'woocommerce-end');
				}


				// Advanced Settings
				echo $object->get_setting_ui( 'toggle', 'advanced-start' );
				echo $object->get_setting_ui( 'optins', 'never' );
				echo $object->get_setting_ui( 'optins', 'only' );
				echo $object->get_setting_ui( 'optins', 'categories' );
				echo $object->get_setting_ui( 'optins', 'taxonomies' );
				echo $object->get_setting_ui( 'optins', 'show' );
				echo $object->get_setting_ui( 'toggle', 'advanced-end' );
			}

			if ( 'sidebar' == $type || 'inline' == $type ) {
				echo $object->get_setting_ui('note', 'sidebar_widget_notice');
			}

		} else {
			?>
			<p><strong><?php _e( 'No campaign could be retrieved for the ID specified.', 'optin-monster-api' ); ?></strong></p>
			<?php
		}

	}

	/**
	 * Returns the action links for the optin.
	 *
	 * @since 1.0.0
	 *
	 * @param int $optin_id  The optin ID to target.
	 * @return string $links HTML string of action links.
	 */
	public function get_optin_links( $optin_id ) {

		$optin       = get_post( $optin_id );
		$slug        = $optin->post_name;
		$status      = (bool) get_post_meta( $optin_id, '_omapi_enabled', true );
		$status_link = $status ? __( 'Disable', 'optin-monster-api' ) : __( 'Go Live', 'optin-monster-api' );
		$status_desc = $status ? esc_attr__( 'Disable this campaign', 'optin-monster-api' ) : esc_attr__( 'Go live with this campaign', 'optin-monster-api' );
		$links       = array();
		$links['editd']  = '<a href="' . esc_url_raw( OPTINMONSTER_APP_URL . '/campaigns/' . $slug . '/edit/' ) . '" title="' . esc_attr__( 'Edit this campaign on the OptinMonster App', 'optin-monster-api' ) . '" target="_blank">Edit Design</a>';
		$links['edito']  = '<a href="' . esc_url_raw( add_query_arg( array( 'optin_monster_api_view' => $this->view, 'optin_monster_api_action' => 'edit', 'optin_monster_api_id' => $optin_id ), admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ) . '" title="' . esc_attr__( 'Edit the output settings for this campaign', 'optin-monster-api' ) . '">Edit Output Settings</a>';
		$links['status'] = '<a href="' . wp_nonce_url( esc_url_raw( add_query_arg( array( 'optin_monster_api_view' => $this->view, 'optin_monster_api_action' => 'status', 'optin_monster_api_id' => $optin_id ), admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ), 'omapi-action' ) . '" title="' . $status_desc . '">' . $status_link . '</a>';

		$links = apply_filters( 'optin_monster_api_action_links', $links, $optin_id );
		return implode( ' | ', (array) $links );

	}

	public function migrate() {
		?>
		<p><?php _e( 'Your campaigns created within WordPress using the original OptinMonster plugin can be recreated manually in your OptinMonster account.', 'optin-monster-api' ); ?></p>
		<p><a href="https://optinmonster.com/docs/old-wordpress-customers-migrating-to-the-new-optinmonster-app/"><?php _e( 'Read the full post about the changes.')?></a></p>
		<?php
	}

}
