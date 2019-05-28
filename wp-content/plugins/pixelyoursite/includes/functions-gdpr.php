<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * @link https://wordpress.org/plugins/ginger/
 */
function isGingerPluginActivated() {
	
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	return is_plugin_active( 'ginger/ginger-eu-cookie-law.php' );
	
}

/**
 * @link https://wordpress.org/plugins/cookiebot/
 * @link https://www.cookiebot.com/en/developer/
 */
function isCookiebotPluginActivated() {
	
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	return is_plugin_active( 'cookiebot/cookiebot.php' );
	
}

/**
 * @link https://wordpress.org/plugins/cookie-notice/
 */
function isCookieNoticePluginActivated() {
	
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	return is_plugin_active( 'cookie-notice/cookie-notice.php' );
	
}

/**
 * GDPR Cookie Consent
 *
 * @link https://wordpress.org/plugins/cookie-law-info/
 */
function isCookieLawInfoPluginActivated() {
	
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	return is_plugin_active( 'cookie-law-info/cookie-law-info.php' )
	       || is_plugin_active( 'webtoffee-gdpr-cookie-consent/cookie-law-info.php' ) ;
	
}

function adminGdprAjaxNotEnabledNotice() {
    
    $url = buildAdminUrl( 'pixelyoursite', 'gdpr', false, array(
        '_wpnonce' => wp_create_nonce( 'pys_enable_gdpr_ajax' ),
        'pys'      => array(
            'enable_gdpr_ajax' => true,
        ),
    ) );
    
    ?>
    
    <div class="notice notice-error pys_core_gdpr_ajax_notice">
        <p>You use the <strong>GDPR Cookie Consent</strong> and <strong>PixelYourSite</strong> plugins. You
            must turn on "Enable AJAX filter values update" option to avoid problems with cache plugins.
            <a href="<?php echo esc_url( $url ); ?>"><strong>CLICK HERE TO
                    ENABLE</strong></a>.</p>
    </div>
    
    <?php
}

function adminGdprAjaxEnabledNotice() {
    ?>
    
    <div class="notice notice-success">
        <p>All good :)</p>
    </div>
    
    <?php
}