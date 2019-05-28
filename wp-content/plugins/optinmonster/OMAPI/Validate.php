<?php
/**
 * Validate class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */
class OMAPI_Validate {

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
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

	    // Set our object.
	    $this->set();

		// Possibly validate our API credentials.
		$this->maybe_validate();

		// Add validation messages.
		add_action( 'admin_notices', array( $this, 'notices' ) );

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

    }

    /**
     * Maybe validate our API credentials if the transient has expired.
     *
     * @since 1.0.0
     */
    public function maybe_validate() {

	    // Check to see if welcome options have been set. If not, let's delay this check for a day.
	    // Also set a transient so that we know the plugin has been activated.
	    $options = $this->base->get_option();
		if ( ! isset( $options['welcome']['status'] ) || 'welcomed' !== $options['welcome']['status'] ) {
			set_transient( '_omapi_validate', true, DAY_IN_SECONDS );
			return;
		}

	    // Check if the transient has expired.
	    if ( false !== ( $transient = get_transient( '_omapi_validate' ) ) ) {
		    return;
	    }

	    // Validate API.
	    $this->validate();

	    // Provide action to refresh optins.
	    do_action( 'optin_monster_api_validate_api', $this->view );

    }

    /**
     * Validate API credentials.
     *
     * @since 1.0.0
     */
    public function validate() {

		$creds = $this->base->get_api_credentials();

		// Check for new apikey and only use the old user/key if we don't have it
		if ( ! $creds['apikey'] ) {
            $api   = new OMAPI_Api( 'validate', array( 'user' => $creds['user'], 'key' => $creds['key'] ) );
        } else {
            $api   = new OMAPI_Api( 'verify', array( 'apikey' => $creds['apikey'] ) );
        }

		$ret   = $api->request();
		if ( is_wp_error( $ret ) ) {
			$option = $this->base->get_option();
			$type   = $ret->get_error_code();
			switch ( $type ) {
				case 'missing' :
				case 'auth' :
					// Set option values.
					$option['is_invalid']  = true;
					$option['is_expired']  = false;
					$option['is_disabled'] = false;
				break;

				case 'disabled' :
					// Set option values.
					$option['is_invalid']  = false;
					$option['is_expired']  = false;
					$option['is_disabled'] = true;
				break;

				case 'expired' :
					// Set option values.
					$option['is_invalid']  = false;
					$option['is_expired']  = true;
					$option['is_disabled'] = false;
				break;
			}

			// Update option.
			update_option( 'optin_monster_api', $option );

			// Set our transient to run again in an hour.
			set_transient( '_omapi_validate', true, HOUR_IN_SECONDS );
		} else {
			set_transient( '_omapi_validate', true, DAY_IN_SECONDS );
		}

    }

    /**
     * Outputs any validation notices.
     *
     * @since 1.0.0
     */
    public function notices() {

		global $pagenow;
	    $option = $this->base->get_option();
	    if ( isset( $option['is_invalid'] ) && $option['is_invalid'] ) {
		    if ( ! ( isset($_GET['page'] ) && $_GET['page'] == 'optin-monster-api-settings') && ! ( isset($_GET['page'] ) && $_GET['page'] == 'optin-monster-api-welcome') ){
			    if ( ! $this->base->menu->has_trial_link() ) {
				    echo '<div class="error"><p>' . __( 'There was an error verifying your OptinMonster API credentials. They are either missing or they are no longer valid.', 'optin-monster-api' ) . '</p>';
				    echo '<p><a href="' . esc_url_raw( admin_url( 'admin.php?page=optin-monster-api-settings' ) ) . '" class="button button-primary button-large omapi-new-optin" title="View API Settings">View API Settings</a></p></div>';
				}
		    }
	    } elseif ( isset( $option['is_disabled'] ) && $option['is_disabled'] ) {
		    echo '<div class="error"><p>' . __( 'The subscription to this OptinMonster account has been disabled, likely due to a refund or other administrator action. Please contact OptinMonster support to resolve this issue.', 'optin-monster-api' ) . '</p>';
		    echo '<p><a href="' . OPTINMONSTER_APP_URL . '/account/support/?utm_source=orgplugin&utm_medium=link&utm_campaign=wpdashboard" class="button button-primary button-large omapi-new-optin" title="Contact OptinMonster Support" target="_blank">Contact Support</a></p></div>';
	    } elseif ( isset( $option['is_expired'] ) && $option['is_expired'] ) {
		    echo '<div class="error"><p>' . __( 'The subscription to this OptinMonster account has expired. Please renew your subscription to use the OptinMonster API.', 'optin-monster-api' ) . '</p>';
		    echo '<p><a href="' . OPTINMONSTER_APP_URL . '/account/billing/?utm_source=orgplugin&utm_medium=link&utm_campaign=wpdashboard" class="button button-primary button-large omapi-new-optin" title="Renew Subscription" target="_blank">Renew Subscription</a></p></div>';
	    } else {
		    // If user has dismissed before no point going through page checks
		    if ( $this->should_user_see_connect_nag() ) {
			    if ( ! ( isset($_GET['page'] ) && $_GET['page'] == 'optin-monster-api-settings') && ! ( isset($_GET['page'] ) && $_GET['page'] == 'optin-monster-api-welcome') && ! ( 'index.php' == $pagenow ) && ! $this->base->get_api_credentials() ){
				    echo '<div id="omapi-please-connect-notice" class="updated notice is-dismissible"><h3 style="padding:2px;font-weight:normal;margin:.5em 0 0;">' . __( 'Get More Email Subscribers with OptinMonster', 'optin-monster-api' ) . '</h3><p>' . __( 'Please connect to or create an OptinMonster account to start using OptinMonster. This will enable you to start turning website visitors into subscribers & customers.', 'optin-monster-api' ) . '</p>';
				    echo '<p><a href="' . esc_url_raw( $this->base->menu->get_dashboard_link() ) . '" class="button button-primary button-large omapi-new-optin" title="Connect OptinMonster">Connect OptinMonster</a></p></div>';
			    }
		    }

	    }

    }

	/**
	 * Script to hide the please connect nag
	 */
	public function hide_connect_notice_script() {
		?>
		<script type="text/javascript">
			jQuery(document).on('click', '#omapi-please-connect-notice .notice-dismiss', function( event ) {
				event.preventDefault();

				//Set the pointer to be closed for this user
				jQuery.post( ajaxurl, {
					pointer: 'omapi_please_connect_notice',
					action: 'dismiss-wp-pointer'
				});
				jQuery('#omapi-please-connect-notice').fadeTo(100, 0, function() {
					jQuery( this ).slideUp(100, function() {
						jQuery( this ).remove()
					})
				});
			});
		</script>
		<?php
	}

	/**
	 * Check user meta and see if they have previously dismissed the please connect nag
	 *
	 * @return bool default false and true only if the 'omapi_please_connect_notice' is not in the wp dismissed pointers usermeta
	 */
	public function should_user_see_connect_nag() {

		// Assume user has dissmissed
		$show_the_nag = false;

		// Get array list of dismissed pointers for current user and convert it to array
		$dismissed_pointers = explode( ',', get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

		// Check if our pointer is not among dismissed ones and that the user should see this
		if( ! in_array( 'omapi_please_connect_notice', $dismissed_pointers ) && current_user_can('activate_plugins') ) {
			$show_the_nag = true;
			// Add footer script to save when user dismisses
			add_action( 'admin_print_footer_scripts', array( $this, 'hide_connect_notice_script' ) );
		}

		if ( $show_the_nag ) {
			return true;
		}

		return false;

	}


}