<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/** @noinspection PhpIncludeInspection */
require_once PYS_FREE_PATH . '/modules/google_analytics/function-helpers.php';

class GA extends Settings implements Pixel {
	
	private static $_instance;
	
	private $configured;
	
	public static function instance() {
		
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		
		return self::$_instance;
		
	}
	
    public function __construct() {
		
        parent::__construct( 'ga' );
        
	    $this->locateOptions(
		    PYS_FREE_PATH . '/modules/google_analytics/options_fields.json',
		    PYS_FREE_PATH . '/modules/google_analytics/options_defaults.json'
	    );
	    
	    add_action( 'pys_register_pixels', function( $core ) {
		    /** @var PYS $core */
		    $core->registerPixel( $this );
	    } );
    }
	
	public function enabled() {
		return $this->getOption( 'enabled' );
	}
	
	public function configured() {
		
		if ( $this->configured === null ) {
			
			$tracking_id = $this->getOption( 'tracking_id' );
			$this->configured = $this->enabled()
			                    && ! empty( $tracking_id )
			                    && ! apply_filters( 'pys_pixel_disabled', false, $this->getSlug() );
			
		}
		
		return $this->configured;
		
	}
	
	public function getPixelIDs() {

		$ids = (array) $this->getOption( 'tracking_id' );
		
		return (array) reset( $ids ); // return first id only
		
	}

    public function getPixelOptions()
    {

        return array(
            'trackingIds' => $this->getPixelIDs(),
            'enhanceLinkAttr' => $this->getOption('enhance_link_attribution'),
            'anonimizeIP' => $this->getOption('anonimize_ip'),
            'commentEventEnabled' => $this->getOption('comment_event_enabled'),
            'commentEventNonInteractive' => $this->getOption('comment_event_non_interactive'),
            'downloadEnabled' => $this->getOption('download_event_enabled'),
            'downloadEventNonInteractive' => $this->getOption('download_event_non_interactive'),
            'formEventEnabled' => $this->getOption('form_event_enabled'),
            'crossDomainEnabled' => $this->getOption('cross_domain_enabled'),
            'crossDomainAcceptIncoming' => $this->getOption('cross_domain_accept_incoming'),
            'crossDomainDomains' => $this->getOption('cross_domain_domains'),
        );

    }

	public function getEventData( $eventType, $args = null ) {
		
		if ( ! $this->configured() ) {
			return false;
		}

		switch ( $eventType ) {
			case 'init_event':
				return $this->getPageViewEventParams();

			case 'search_event':
				return $this->getSearchEventData();

			case 'custom_event':
				return $this->getCustomEventData( $args );

			case 'woo_view_content':
				return $this->getWooViewContentEventParams();

			case 'woo_add_to_cart_on_button_click':
				return $this->getWooAddToCartOnButtonClickEventParams( $args );

			case 'woo_add_to_cart_on_cart_page':
			case 'woo_add_to_cart_on_checkout_page':
				return $this->getWooAddToCartOnCartEventParams();

			case 'woo_remove_from_cart':
				return $this->getWooRemoveFromCartParams( $args );

			case 'woo_view_category':
				return $this->getWooViewCategoryEventParams();

			case 'woo_initiate_checkout':
				return $this->getWooInitiateCheckoutEventParams();

			case 'woo_purchase':
				return $this->getWooPurchaseEventParams();

			case 'edd_view_content':
				return $this->getEddViewContentEventParams();

			case 'edd_add_to_cart_on_button_click':
				return $this->getEddAddToCartOnButtonClickEventParams( $args );

			case 'edd_add_to_cart_on_checkout_page':
				return $this->getEddCartEventParams( 'add_to_cart' );

			case 'edd_remove_from_cart':
				return $this->getEddRemoveFromCartParams( $args );

			case 'edd_view_category':
				return $this->getEddViewCategoryEventParams();

			case 'edd_initiate_checkout':
				return $this->getEddCartEventParams( 'begin_checkout' );

			case 'edd_purchase':
				return $this->getEddCartEventParams( 'purchase' );

			default:
				return false;   // event does not supported
		}

	}
	
	public function outputNoScriptEvents() {
	 
		if ( ! $this->configured() ) {
			return;
		}

		$eventsManager = PYS()->getEventsManager();

		foreach ( $eventsManager->getStaticEvents( 'ga' ) as $eventName => $events ) {
			foreach ( $events as $event ) {
				foreach ( $this->getPixelIDs() as $pixelID ) {

					$args = array(
						'v'   => 1,
						'tid' => $pixelID,
						't'   => 'event',
						'aip' => $this->getOption( 'anonimize_ip' ),
					);

					//@see: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#ec
					if ( isset( $event['params']['event_category'] ) ) {
						$args['ec'] = urlencode( $event['params']['event_category'] );
					}

					if ( isset( $event['params']['event_action'] ) ) {
						$args['ea'] = urlencode( $event['params']['event_action'] );
					}

					if ( isset( $event['params']['event_label'] ) ) {
						$args['el'] = urlencode( $event['params']['event_label'] );
					}

					if ( isset( $event['params']['value'] ) ) {
						$args['ev'] = urlencode( $event['params']['value'] );
					}

					if ( isset( $event['params']['items'] ) ) {

						foreach ( $event['params']['items'] as $key => $item ) {

							@$args["pr{$key}id" ] = urlencode( $item['id'] );
							@$args["pr{$key}nm"] = urlencode( $item['name'] );
							@$args["pr{$key}ca"] = urlencode( $item['category'] );
							//@$args["pr{$key}va"] = urlencode( $item['id'] ); // variant
							@$args["pr{$key}pr"] = urlencode( $item['price'] );
							@$args["pr{$key}qt"] = urlencode( $item['quantity'] );

						}
						
						//https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#pa
						$args["pa"] = 'detail'; // required

					}

					// ALT tag used to pass ADA compliance
					printf( '<noscript><img height="1" width="1" style="display: none;" src="%s" alt="google_analytics"></noscript>',
						add_query_arg( $args, 'https://www.google-analytics.com/collect' ) );

					echo "\r\n";

				}
			}
		}
		
	}
	
	private function getPageViewEventParams() {

		if ( PYS()->getEventsManager()->doingAMP ) {

			return array(
				'name' => 'PageView',
				'data' => array(),
			);

		} else {
			return false; // PageView is fired by tag itself
		}

	}

	private function getSearchEventData() {

		if ( ! $this->getOption( 'search_event_enabled' ) ) {
			return false;
		}

		$params['event_category'] = 'WordPress Search';
		$params['search_term']    = empty( $_GET['s'] ) ? null : $_GET['s'];

		if ( isWooCommerceActive() && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product' ) {
			$params['event_category'] = 'WooCommerce Search';
		}

		$params['non_interaction'] = $this->getOption( 'search_event_non_interactive' );

		return array(
			'name'  => 'search',
			'data'  => $params,
		);

	}

	/**
	 * @param CustomEvent $event
	 *
	 * @return array|bool
	 */
	private function getCustomEventData( $event ) {

		$ga_action = $event->getGoogleAnalyticsAction();

		if ( ! $event->isGoogleAnalyticsEnabled() || empty( $ga_action ) ) {
			return false;
		}

		$params = array(
			'event_category'  => $event->ga_event_category,
			'event_label'     => $event->ga_event_label,
			'value'           => $event->ga_event_value,
			'non_interaction' => $event->ga_non_interactive,
		);

		return array(
			'name'  => $event->getGoogleAnalyticsAction(),
			'data'  => $params,
			'delay' => $event->getDelay(),
		);

	}

	private function getWooViewCategoryEventParams() {
		global $posts;

		if ( ! $this->getOption( 'woo_view_category_enabled' ) ) {
			return false;
		}
        
        $product_categories = array();
        $term = get_term_by( 'slug', get_query_var( 'term' ), 'product_cat' );
        
        if ( $term ) {
            $parent_ids = get_ancestors( $term->term_id, 'product_cat', 'taxonomy' );
            $product_categories[] = $term->name;
            
            foreach ( $parent_ids as $term_id ) {
                $parent_term = get_term_by( 'id', $term_id, 'product_cat' );
                $product_categories[] = $parent_term->name;
            }
        }

		$list_name = implode( '/', array_reverse( $product_categories ) );

		$items = array();
		$product_ids = array();
		$total_value = 0;

		for ( $i = 0; $i < count( $posts ); $i ++ ) {

			if ( $posts[ $i ]->post_type !== 'product' ) {
				continue;
			}

			$item = array(
				'id'            => $posts[ $i ]->ID,
				'name'          => $posts[ $i ]->post_title,
				'category'      => implode( '/', getObjectTerms( 'product_cat', $posts[ $i ]->ID ) ),
				'quantity'      => 1,
				'price'         => getWooProductPriceToDisplay( $posts[ $i ]->ID ),
				'list_position' => $i + 1,
				'list'          => $list_name,
			);

			$items[] = $item;
			$product_ids[] = $item['id'];
			$total_value += $item['price'];

		}

		$params = array(
			'event_category'  => 'ecommerce',
			'event_label'     => $list_name,
			'items'           => $items,
			'non_interaction' => $this->getOption( 'woo_view_category_non_interactive' ),
		);
		
		return array(
			'name'  => 'view_item_list',
			'data'  => $params,
		);

	}

	private function getWooViewContentEventParams() {
		global $post;

		if ( ! $this->getOption( 'woo_view_content_enabled' ) ) {
			return false;
		}

		$params = array(
			'event_category'  => 'ecommerce',
			'items'           => array(
				array(
					'id'       => $post->ID,
					'name'     => $post->post_title,
					'category' => implode( '/', getObjectTerms( 'product_cat', $post->ID ) ),
					'quantity' => 1,
					'price'    => getWooProductPriceToDisplay( $post->ID ),
				),
			),
			'non_interaction' => $this->getOption( 'woo_view_content_non_interactive' ),
		);

		return array(
			'name'  => 'view_item',
			'data'  => $params,
			'delay' => (int) PYS()->getOption( 'woo_view_content_delay' ),
		);

	}

	private function getWooAddToCartOnButtonClickEventParams( $product_id ) {

		if ( ! $this->getOption( 'woo_add_to_cart_enabled' )  || ! PYS()->getOption( 'woo_add_to_cart_on_button_click' ) ) {
			return false;
		}

		$product = get_post( $product_id );
		$price = getWooProductPriceToDisplay( $product_id, 1 );

		$params = array(
			'event_category'  => 'ecommerce',
			'items'           => array(
				array(
					'id'       => $product_id,
					'name'     => $product->post_title,
					'category' => implode( '/', getObjectTerms( 'product_cat', $product_id ) ),
					'quantity' => 1,
					'price'    => $price,
				),
			),
			'non_interaction' => $this->getOption( 'woo_add_to_cart_non_interactive' ),
		);

		return array(
			'data'  => $params,
		);

	}

	private function getWooAddToCartOnCartEventParams() {

		if ( ! $this->getOption( 'woo_add_to_cart_enabled' ) ) {
			return false;
		}

		$params = $this->getWooCartParams();
		$params['non_interaction'] = true;

		return array(
			'name' => 'add_to_cart',
			'data' => $params
		);

	}

	private function getWooRemoveFromCartParams( $cart_item ) {

		if ( ! $this->getOption( 'woo_remove_from_cart_enabled' ) ) {
			return false;
		}

		$product_id = $cart_item['product_id'];

		$product = get_post( $product_id );

		if ( ! empty( $cart_item['variation_id'] ) ) {
			$variation = get_post( (int) $cart_item['variation_id'] );
			$variation_name = $variation->post_title;
		} else {
			$variation_name = null;
		}

		return array(
			'data' => array(
				'event_category'  => 'ecommerce',
				'currency'        => get_woocommerce_currency(),
				'items'           => array(
					array(
						'id'       => $product_id,
						'name'     => $product->post_title,
						'category' => implode( '/', getObjectTerms( 'product_cat', $product_id ) ),
						'quantity' => $cart_item['quantity'],
						'price'    => getWooProductPriceToDisplay( $product_id, $cart_item['quantity'] ),
						'variant'  => $variation_name,
					),
				),
				'non_interaction' => $this->getOption( 'woo_remove_from_cart_non_interactive' ),
			),
		);

	}

	private function getWooInitiateCheckoutEventParams() {

		if ( ! $this->getOption( 'woo_initiate_checkout_enabled' ) ) {
			return false;
		}

		$params = $this->getWooCartParams();
		$params['non_interaction'] = $this->getOption( 'woo_initiate_checkout_non_interactive' );

		return array(
			'name'  => 'begin_checkout',
			'data'  => $params
		);

	}
	
	private function getWooPurchaseEventParams() {

		if ( ! $this->getOption( 'woo_purchase_enabled' ) ) {
			return false;
		}

		$order_id = (int) wc_get_order_id_by_order_key( $_REQUEST['key'] );

		$order = new \WC_Order( $order_id );
		$items = array();
		$product_ids = array();
		$total_value = 0;

		foreach ( $order->get_items( 'line_item' ) as $line_item ) {

			$post    = get_post( $line_item['product_id'] );
			$product = wc_get_product( $line_item['product_id'] );

			if ( $line_item['variation_id'] ) {
				$variation      = get_post( $line_item['variation_id'] );
				$variation_name = $variation->post_title;
			} else {
				$variation_name = null;
			}

			/**
			 * Discounted price used instead of price as is on Purchase event only to avoid wrong numbers in
			 * Analytic's Product Performance report.
			 */
			if ( isWooCommerceVersionGte( '3.0' ) ) {
				$price = $line_item['total'] + $line_item['total_tax'];
			} else {
				$price = $line_item['line_total'] + $line_item['line_tax'];
			}

			$qty = $line_item['qty'];
			$price = $price / $qty;

			if ( isWooCommerceVersionGte( '3.0' ) ) {

				if ( 'yes' === get_option( 'woocommerce_prices_include_tax' ) ) {
					$price = wc_get_price_including_tax( $product, array( 'qty' => 1, 'price' => $price ) );
				} else {
					$price = wc_get_price_excluding_tax( $product, array( 'qty' => 1, 'price' => $price ) );
				}

			} else {

				if ( 'yes' === get_option( 'woocommerce_prices_include_tax' ) ) {
					$price = $product->get_price_including_tax( 1, $price );
				} else {
					$price = $product->get_price_excluding_tax( 1, $price );
				}

			}

			$item = array(
				'id'       => $post->ID,
				'name'     => $post->post_title,
				'category' => implode( '/', getObjectTerms( 'product_cat', $post->ID ) ),
				'quantity' => $qty,
				'price'    => $price,
				'variant'  => $variation_name,
			);

			$items[] = $item;
			$product_ids[] = $item['id'];
			$total_value   += $item['price'];

		}
		
		$params = array(
			'event_category'  => 'ecommerce',
            'transaction_id'  => $order_id,
			'value'           => $order->get_total(),
			'currency'        => get_woocommerce_currency(),
			'items'           => $items,
			'non_interaction' => $this->getOption( 'woo_purchase_non_interactive' ),
		);

		return array(
			'name' => 'purchase',
			'data' => $params
		);

	}

	private function getWooCartParams() {

		$items = array();
		$product_ids = array();
		$total_value = 0;

		foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {

			$product = get_post( $cart_item['product_id'] );

			if ( $cart_item['variation_id'] ) {
				$variation = get_post( $cart_item['variation_id'] );
				$variation_name = $variation->post_title;
			} else {
				$variation_name = null;
			}

			$item = array(
				'id'       => $product->ID,
				'name'     => $product->post_title,
				'category' => implode( '/', getObjectTerms( 'product_cat', $product->ID ) ),
				'quantity' => $cart_item['quantity'],
				'price'    => getWooProductPriceToDisplay( $product->ID ),
				'variant'  => $variation_name,
			);

			$items[] = $item;
			$product_ids[] = $item['id'];
			$total_value += $item['price'];

		}
		
		$params = array(
			'event_category' => 'ecommerce',
			'items' => $items,
		);

		return $params;

	}
	
	private function getEddViewContentEventParams() {
		global $post;

		if ( ! $this->getOption( 'edd_view_content_enabled' ) ) {
			return false;
		}

		$params = array(
			'event_category'  => 'ecommerce',
			'items'           => array(
				array(
					'id'       => $post->ID,
					'name'     => $post->post_title,
					'category' => implode( '/', getObjectTerms( 'download_category', $post->ID ) ),
					'quantity' => 1,
					'price'    => getEddDownloadPriceToDisplay( $post->ID ),
				),
			),
			'non_interaction' => $this->getOption( 'edd_view_content_non_interactive' ),
		);

		return array(
			'name'  => 'view_item',
			'data'  => $params,
			'delay' => (int) PYS()->getOption( 'edd_view_content_delay' ),
		);

	}

	private function getEddAddToCartOnButtonClickEventParams( $download_id ) {

		if ( ! $this->getOption( 'edd_add_to_cart_enabled' ) || ! PYS()->getOption( 'edd_add_to_cart_on_button_click' ) ) {
			return false;
		}

		// maybe extract download price id
		if ( strpos( $download_id, '_') !== false ) {
			list( $download_id, $price_index ) = explode( '_', $download_id );
		} else {
			$price_index = null;
		}

		$download_post = get_post( $download_id );

		$params = array(
			'event_category'  => 'ecommerce',
			'items'           => array(
				array(
					'id'       => $download_id,
					'name'     => $download_post->post_title,
					'category' => implode( '/', getObjectTerms( 'download_category', $download_id ) ),
					'quantity' => 1,
					'price'    => getEddDownloadPriceToDisplay( $download_id, $price_index ),
				),
			),
			'non_interaction' => $this->getOption( 'edd_add_to_cart_non_interactive' ),
		);
		
		return array(
			'data' => $params,
		);

	}

	private function getEddCartEventParams( $context = 'add_to_cart' ) {

		if ( $context == 'add_to_cart' && ! $this->getOption( 'edd_add_to_cart_enabled' ) ) {
			return false;
		} elseif ( $context == 'begin_checkout' && ! $this->getOption( 'edd_initiate_checkout_enabled' ) ) {
			return false;
		} elseif ( $context == 'purchase' && ! $this->getOption( 'edd_purchase_enabled' ) ) {
			return false;
		}

		if ( $context == 'add_to_cart' || $context == 'begin_checkout' ) {
			$cart = edd_get_cart_contents();
		} else {
			$cart = edd_get_payment_meta_cart_details( edd_get_purchase_id_by_key( getEddPaymentKey() ), true );
		}

		$items = array();
		$product_ids = array();
		$total_value = 0;

		foreach ( $cart as $cart_item_key => $cart_item ) {

			$download_id   = (int) $cart_item['id'];
			$download_post = get_post( $download_id );

			if ( in_array( $context, array( 'purchase', 'FrequentShopper', 'VipClient', 'BigWhale' ) ) ) {
				$item_options = $cart_item['item_number']['options'];
			} else {
				$item_options = $cart_item['options'];
			}

			if ( ! empty( $item_options ) && $item_options['price_id'] !== 0 ) {
				$price_index = $item_options['price_id'];
			} else {
				$price_index = null;
			}

			/**
			 * Price as is used for all events except Purchase to avoid wrong values in Product Performance report.
			 */
			if ( $context == 'purchase' ) {
			 
				$price = $cart_item['item_price'] - $cart_item['discount'];

				if ( edd_prices_include_tax() ) {
					$price -= $cart_item['tax'];
				} else {
					$price += $cart_item['tax'];
				}

			} else {
				$price = getEddDownloadPriceToDisplay( $download_id, $price_index );
			}

			$item = array(
				'id'       => $download_id,
				'name'     => $download_post->post_title,
				'category' => implode( '/', getObjectTerms( 'download_category', $download_id ) ),
				'quantity' => $cart_item['quantity'],
				'price'    => $price
//				'variant'  => $variation_name,
			);

			$items[] = $item;
			$product_ids[] = (int) $cart_item['id'];
			$total_value += $price;

		}

		$params = array(
			'event_category' => 'ecommerce',
			'items' => $items,
		);

		if ( $context == 'add_to_cart' ) {
			$params['non_interaction'] = true;
		} elseif ( $context == 'begin_checkout' ) {
			$params['non_interaction'] = $this->getOption( 'edd_initiate_checkout_non_interactive' );
		} elseif ( $context == 'purchase' ) {
			$params['non_interaction'] = $this->getOption( 'edd_purchase_non_interactive' );
		}

		if ( $context == 'purchase' ) {

			$payment_key = getEddPaymentKey();
			$payment_id = (int) edd_get_purchase_id_by_key( $payment_key );

            $params['transaction_id'] = $payment_id;
			$params['currency'] = edd_get_currency();
            $params['value'] = edd_get_payment_amount( $payment_id );

		}
		
		return array(
			'name' => $context,
			'data' => $params,
		);

	}

	private function getEddRemoveFromCartParams( $cart_item ) {

		if ( ! $this->getOption( 'edd_remove_from_cart_enabled' ) ) {
			return false;
		}

		$download_id = $cart_item['id'];
		$download_post = get_post( $download_id );

		$price_index = ! empty( $cart_item['options'] ) ? $cart_item['options']['price_id'] : null;

		return array(
			'data' => array(
				'event_category'  => 'ecommerce',
				'currency'        => edd_get_currency(),
				'items'           => array(
					array(
						'id'       => $download_id,
						'name'     => $download_post->post_title,
						'category' => implode( '/', getObjectTerms( 'download_category', $download_id ) ),
						'quantity' => $cart_item['quantity'],
						'price'    => getEddDownloadPriceToDisplay( $download_id, $price_index ),
//						'variant'  => $variation_name,
					),
				),
				'non_interaction' => $this->getOption( 'edd_remove_from_cart_non_interactive' ),
			),
		);

	}

	private function getEddViewCategoryEventParams() {
		global $posts;

		if ( ! $this->getOption( 'edd_view_category_enabled' ) ) {
			return false;
		}

		$term = get_term_by( 'slug', get_query_var( 'term' ), 'download_category' );
		$parent_ids = get_ancestors( $term->term_id, 'download_category', 'taxonomy' );

		$download_categories = array();
		$download_categories[] = $term->name;

		foreach ( $parent_ids as $term_id ) {
			$parent_term = get_term_by( 'id', $term_id, 'download_category' );
			$download_categories[] = $parent_term->name;
		}

		$list_name = implode( '/', array_reverse( $download_categories ) );

		$items = array();
		$product_ids = array();
		$total_value = 0;

		for ( $i = 0; $i < count( $posts ); $i ++ ) {

			$item = array(
				'id'            => $posts[ $i ]->ID,
				'name'          => $posts[ $i ]->post_title,
				'category'      => implode( '/', getObjectTerms( 'download_category', $posts[ $i ]->ID ) ),
				'quantity'      => 1,
				'price'         => getEddDownloadPriceToDisplay( $posts[ $i ]->ID ),
				'list_position' => $i + 1,
				'list'          => $list_name,
			);

			$items[] = $item;
			$product_ids[] = $item['id'];
			$total_value += $item['price'];

		}

		$params = array(
			'event_category'  => 'ecommerce',
			'event_label'     => $list_name,
			'items'           => $items,
			'non_interaction' => $this->getOption( 'edd_view_category_non_interactive' ),
		);

		return array(
			'name'  => 'view_item_list',
			'data'  => $params,
		);

	}

}

/**
 * @return GA
 */
function GA() {
	return GA::instance();
}

GA();