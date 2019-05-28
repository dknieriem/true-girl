<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function maybeMigrate() {
	
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}
	
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	$pys_free_7_version = get_option( 'pys_core_version', false );
	$v5_free = get_option( 'pixel_your_site' );
	
	if ( ! $pys_free_7_version && is_array( $v5_free ) ) {
		// migrate from FREE 5.x
		
		migrate_v5_free_options();
		migrate_v5_free_events();
	
		update_option( 'pys_core_version', PYS_FREE_VERSION );
		update_option( 'pys_updated_at', time() );
		
	} elseif ( ! $pys_free_7_version ) {
		// first install

		update_option( 'pys_core_version', PYS_FREE_VERSION );
		update_option( 'pys_updated_at', time() );
		
	}
	
}

function migrate_v5_free_options() {
	
	$v5_free = get_option( 'pixel_your_site' );
	
	$v7_core = array(
		'gdpr_facebook_prior_consent_enabled' => isset( $v5_free['gdpr']['enable_before_consent'] ) ? $v5_free['gdpr']['enable_before_consent'] : null,
		'gdpr_cookiebot_integration_enabled'  => isset( $v5_free['gdpr']['cookiebot_enabled'] ) ? $v5_free['gdpr']['cookiebot_enabled'] : null,
		'gdpr_ginger_integration_enabled'     => isset( $v5_free['gdpr']['ginger_enabled'] ) ? $v5_free['gdpr']['ginger_enabled'] : null,

		'general_event_name'             => isset( $v5_free['general']['general_event_name'] ) ? $v5_free['general']['general_event_name'] : null,
		'general_event_delay'            => isset( $v5_free['general']['general_event_delay'] ) ? $v5_free['general']['general_event_delay'] : null,
		'general_event_on_posts_enabled' => isset( $v5_free['general']['general_event_on_posts_enabled'] ) ? $v5_free['general']['general_event_on_posts_enabled'] : null,
		'general_event_on_pages_enabled' => isset( $v5_free['general']['general_event_on_pages_enabled'] ) ? $v5_free['general']['general_event_on_pages_enabled'] : null,
		'general_event_on_tax_enabled'   => isset( $v5_free['general']['general_event_on_tax_enabled'] ) ? $v5_free['general']['general_event_on_tax_enabled'] : null,
		
		'custom_events_enabled'          => isset( $v5_free['std']['enabled'] ) ? $v5_free['std']['enabled'] : null,

		'woo_enabled'                         => isset( $v5_free['woo']['enabled'] ) ? $v5_free['woo']['enabled'] : null,
		'woo_add_to_cart_on_button_click'     => isset( $v5_free['woo']['on_add_to_cart_btn'] ) ? $v5_free['woo']['on_add_to_cart_btn'] : null,
		'woo_add_to_cart_on_cart_page'        => isset( $v5_free['woo']['on_add_to_cart_page'] ) ? $v5_free['woo']['on_add_to_cart_page'] : null,
		'woo_add_to_cart_on_checkout_page'    => isset( $v5_free['woo']['on_add_to_cart_checkout'] ) ? $v5_free['woo']['on_add_to_cart_checkout'] : null,

		'woo_purchase_value_option'           => isset( $v5_free['woo']['purchase_value_option'] ) ? $v5_free['woo']['purchase_value_option'] : null,
		'woo_purchase_value_global'           => isset( $v5_free['woo']['purchase_global_value'] ) ? $v5_free['woo']['purchase_global_value'] : null,
		'woo_initiate_checkout_value_enabled' => isset( $v5_free['woo']['enable_checkout_value'] ) ? $v5_free['woo']['enable_checkout_value'] : null,
		'woo_initiate_checkout_value_option'  => isset( $v5_free['woo']['checkout_value_option'] ) ? $v5_free['woo']['checkout_value_option'] : null,
		'woo_initiate_checkout_value_global'  => isset( $v5_free['woo']['checkout_global_value'] ) ? $v5_free['woo']['checkout_global_value'] : null,
		'woo_add_to_cart_value_enabled'       => isset( $v5_free['woo']['enable_add_to_cart_value'] ) ? $v5_free['woo']['enable_add_to_cart_value'] : null,
		'woo_add_to_cart_value_option'        => isset( $v5_free['woo']['add_to_cart_value_option'] ) ? $v5_free['woo']['add_to_cart_value_option'] : null,
		'woo_add_to_cart_value_global'        => isset( $v5_free['woo']['add_to_cart_global_value'] ) ? $v5_free['woo']['add_to_cart_global_value'] : null,
		'woo_view_content_value_enabled'      => isset( $v5_free['woo']['enable_view_content_value'] ) ? $v5_free['woo']['enable_view_content_value'] : null,
		'woo_view_content_value_option'       => isset( $v5_free['woo']['view_content_value_option'] ) ? $v5_free['woo']['view_content_value_option'] : null,
		'woo_view_content_value_global'       => isset( $v5_free['woo']['view_content_global_value'] ) ? $v5_free['woo']['view_content_global_value'] : null,

		'edd_enabled'                         => isset( $v5_free['edd']['enabled'] ) ? $v5_free['edd']['enabled'] : null,
		'edd_add_to_cart_on_button_click'     => isset( $v5_free['edd']['on_add_to_cart_btn'] ) ? $v5_free['edd']['on_add_to_cart_btn'] : null,
		'edd_add_to_cart_on_checkout_page'    => isset( $v5_free['edd']['on_add_to_cart_checkout'] ) ? $v5_free['edd']['on_add_to_cart_checkout'] : null,
		'edd_purchase_value_option'           => 'price',
		'edd_purchase_value_global'           => isset( $v5_free['edd']['purchase_global_value'] ) ? $v5_free['edd']['purchase_global_value'] : null,
		'edd_initiate_checkout_value_enabled' => isset( $v5_free['edd']['enable_checkout_value'] ) ? $v5_free['edd']['enable_checkout_value'] : null,
		'edd_initiate_checkout_value_option'  => 'price',
		'edd_initiate_checkout_value_global'  => isset( $v5_free['edd']['checkout_global_value'] ) ? $v5_free['edd']['checkout_global_value'] : null,
		'edd_add_to_cart_value_enabled'       => isset( $v5_free['edd']['enable_add_to_cart_value'] ) ? $v5_free['edd']['enable_add_to_cart_value'] : null,
		'edd_add_to_cart_value_option'        => 'price',
		'edd_add_to_cart_value_global'        => isset( $v5_free['edd']['add_to_cart_global_value'] ) ? $v5_free['edd']['add_to_cart_global_value'] : null,
		'edd_view_content_value_enabled'      => isset( $v5_free['edd']['enable_view_content_value'] ) ? $v5_free['edd']['enable_view_content_value'] : null,
		'edd_view_content_value_option'       => 'price',
		'edd_view_content_value_global'       => isset( $v5_free['edd']['view_content_global_value'] ) ? $v5_free['edd']['view_content_global_value'] : null,
        
        'gdpr_ajax_enabled' => isset( $v5_free['gdpr']['gdpr_ajax_enabled'] ) ? $v5_free['gdpr']['gdpr_ajax_enabled']
            : null,
	);
    
    global $wp_roles;
    
    if ( ! isset( $wp_roles ) ) {
        $wp_roles = new \WP_Roles();
    }
    
    // 'do_not_track_user_roles'
    foreach ( $wp_roles->roles as $role => $options ) {
        if ( isset( $v5_free['general'][ 'disable_for_' . $role ] ) && $v5_free['general'][ 'disable_for_' . $role ] ) {
            $v7_core['do_not_track_user_roles'][] = $role;
        }
    }
	
	// update settings
	PYS()->updateOptions( $v7_core );
	PYS()->reloadOptions();
	
	if ( isset( $v5_free['woo']['content_id'] ) ) {
		$woo_content_id = $v5_free['woo']['content_id'] == 'id' ? 'product_id' : 'product_sku';
	} else {
		$woo_content_id = null;
	}
	
	if ( isset( $v5_free['woo']['on_add_to_cart_btn'] ) && $v5_free['woo']['on_add_to_cart_btn'] ) {
		$woo_add_to_cart_enabled = true;
	} elseif ( isset( $v5_free['woo']['on_add_to_cart_page'] ) && $v5_free['woo']['on_add_to_cart_page'] ) {
		$woo_add_to_cart_enabled = true;
	} elseif ( isset( $v5_free['woo']['on_add_to_cart_checkout'] ) && $v5_free['woo']['on_add_to_cart_checkout'] ) {
		$woo_add_to_cart_enabled = true;
	} else {
		$woo_add_to_cart_enabled = false;
	}
	
	if ( isset( $v5_free['edd']['content_id'] ) ) {
		$edd_content_id = $v5_free['edd']['content_id'] == 'id' ? 'download_id' : 'download_sku';
	} else {
		$edd_content_id = null;
	}
	
	if ( isset( $v5_free['edd']['on_add_to_cart_btn'] ) && $v5_free['edd']['on_add_to_cart_btn'] ) {
		$edd_add_to_cart_enabled = true;
	} elseif ( isset( $v5_free['edd']['on_add_to_cart_checkout'] ) && $v5_free['edd']['on_add_to_cart_checkout'] ) {
		$edd_add_to_cart_enabled = true;
	} else {
		$edd_add_to_cart_enabled = false;
	}
	
	$v7_fb = array(
		'enabled'                       => isset( $v5_free['general']['enabled'] ) ? $v5_free['general']['enabled'] : null,
		'pixel_id'                      => isset( $v5_free['general']['pixel_id'] ) ? array( $v5_free['general']['pixel_id'] ) : null,
		'general_event_enabled'         => isset( $v5_free['general']['general_event_enabled'] ) ? $v5_free['general']['general_event_enabled'] : null,
		'search_event_enabled'          => isset( $v5_free['general']['search_event_enabled'] ) ? $v5_free['general']['search_event_enabled'] : null,

		'woo_variable_as_simple'        => isset( $v5_free['woo']['variation_id'] ) && $v5_free['woo']['variation_id'] == 'main',
		'woo_content_id'                => $woo_content_id,
		'woo_purchase_enabled'          => isset( $v5_free['woo']['on_thank_you_page'] ) ? $v5_free['woo']['on_thank_you_page'] : null,
		'woo_initiate_checkout_enabled' => isset( $v5_free['woo']['on_checkout_page'] ) ? $v5_free['woo']['on_checkout_page'] : null,
		'woo_add_to_cart_enabled'       => $woo_add_to_cart_enabled,
		'woo_view_content_enabled'      => isset( $v5_free['woo']['on_view_content'] ) ? $v5_free['woo']['on_view_content'] : null,
		'woo_view_category_enabled'     => isset( $v5_free['woo']['on_view_category'] ) ? $v5_free['woo']['on_view_category'] : null,

		'edd_content_id'                => $edd_content_id,
		'edd_purchase_enabled'          => isset( $v5_free['edd']['on_success_page'] ) ? $v5_free['edd']['on_success_page'] : null,
		'edd_initiate_checkout_enabled' => isset( $v5_free['edd']['on_checkout_page'] ) ? $v5_free['edd']['on_checkout_page'] : null,
		'edd_add_to_cart_enabled'       => $edd_add_to_cart_enabled,
		'edd_view_content_enabled'      => isset( $v5_free['edd']['on_view_content'] ) ? $v5_free['edd']['on_view_content'] : null,
		'edd_view_category_enabled'     => isset( $v5_free['edd']['on_view_category'] ) ? $v5_free['edd']['on_view_category'] : null,
	);
	
	// update settings
	Facebook()->updateOptions( $v7_fb );
	Facebook()->reloadOptions();
	
}

function migrate_v5_free_events() {
	
	$v5_free_events = get_option( 'pixel_your_site_std_events' );
	
	if ( ! is_array( $v5_free_events ) ) {
		return;
	}
	
	foreach ( $v5_free_events as $v5_free_event ) {
		
		if ( empty( $v5_free_event['pageurl'] ) ) {
			continue;
		}
		
		if ( $v5_free_event['eventtype'] == 'CustomCode' ) {
			continue;
		}
		
		$std_events = array(
			'ViewContent',
			'Search',
			'AddToCart',
			'AddToWishlist',
			'InitiateCheckout',
			'AddPaymentInfo',
			'Purchase',
			'Lead',
			'CompleteRegistration',
		);
		
		if ( ! in_array( $v5_free_event['eventtype'], $std_events ) ) {
			$fb_event_type        = 'CustomEvent';
			$fb_custom_event_type = $v5_free_event['custom_name'];
		} else {
			$fb_event_type        = $v5_free_event['eventtype'];
			$fb_custom_event_type = null;
		}
		
		$fb_params = array(
			'value'            => $v5_free_event['value'],
			//			'currency'         => $currency,
			'content_name'     => $v5_free_event['content_name'],
			'content_ids'      => $v5_free_event['content_ids'],
			'content_type'     => $v5_free_event['content_type'],
			'content_category' => $v5_free_event['content_category'],
			'num_items'        => $v5_free_event['num_items'],
			'order_id'         => $v5_free_event['order_id'],
			'search_string'    => $v5_free_event['search_string'],
			'status'           => $v5_free_event['status'],
		);
		
		if ( $v5_free_event['custom_currency'] == true ) {
			$fb_params['currency'] = 'custom';
			$fb_params['custom_currency'] = $v5_free_event['currency'];
		} elseif ( isset( $v5_free_event['currency'] ) ) {
			$fb_params['currency'] = $v5_free_event['currency'];
			$fb_params['custom_currency'] = null;
		}

		$fb_custom_params = array();
		
		foreach ( $v5_free_event as $param => $value ) {
			
			// skip standard params
			if ( array_key_exists( $param, $fb_params ) ) {
				continue;
			}
			
			// skip system params
			if ( in_array( $param, array( 'pageurl', 'eventtype', 'custom_currency', 'code', 'custom_name' ) ) ) {
				continue;
			}
			
			$fb_custom_params[] = array(
				'name' => $param,
				'value' => $value
			);
			
		}
		
        if ( endsWith( $v5_free_event['pageurl'], '*' ) ) {
		    $triggers = array(
                array(
                    'rule'  => 'contains',
                    'value' => rtrim( $v5_free_event['pageurl'], '*' ),
                ),
            );
        } else {
            $triggers = array(
                array(
                    'rule'  => 'match',
                    'value' => $v5_free_event['pageurl'],
                ),
            );
        }
		
		$customEvent = array(
			'title'                      => 'Untitled',
			'enabled'                    => true,
			'delay'                      => null,
			'trigger_type'               => 'page_visit',
			'triggers'                   => array(),
			'url_filters'                => array(),
			'page_visit_triggers'        => $triggers,
			'facebook_enabled'           => true,
			'facebook_event_type'        => $fb_event_type,
			'facebook_custom_event_type' => $fb_custom_event_type,
			'facebook_params_enabled'    => empty( $fb_params ) && empty( $fb_custom_params ) ? false : true,
			'facebook_params'            => $fb_params,
			'facebook_custom_params'     => $fb_custom_params,
		);
		
		CustomEventFactory::create( $customEvent );

	}
	
}