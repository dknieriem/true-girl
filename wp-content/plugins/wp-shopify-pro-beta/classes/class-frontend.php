<?php

namespace WPS;

use WPS\Utils;
use WPS\Options;
use WPS\Utils\Data;
use WPS\Utils\Server;

if (!defined('ABSPATH')) {
   exit();
}

class Frontend
{
   private $plugin_settings;

   /*

	Initialize the class and set its properties.

	*/
   public function __construct($plugin_settings)
   {
      $this->plugin_settings = $plugin_settings;
   }

   /*

	Public styles

	*/
   public function public_styles()
   {
      if (!is_admin()) {
         $styles_all = $this->plugin_settings['general']->styles_all;
         $styles_core = $this->plugin_settings['general']->styles_core;
         $styles_grid = $this->plugin_settings['general']->styles_grid;

         if ($styles_all) {
            wp_enqueue_style(WPS_PLUGIN_TEXT_DOMAIN . '-styles-frontend-all', WPS_PLUGIN_URL . 'dist/public.min.css', [], filemtime(WPS_PLUGIN_DIR_PATH . 'dist/public.min.css'), 'all');
         } else {

            if ($styles_core) {
               wp_enqueue_style(WPS_PLUGIN_TEXT_DOMAIN . '-styles-frontend-core', WPS_PLUGIN_URL . 'dist/core.min.css', [], filemtime(WPS_PLUGIN_DIR_PATH . 'dist/core.min.css'), 'all');
            }

            if ($styles_grid) {
               wp_enqueue_style(WPS_PLUGIN_TEXT_DOMAIN . '-styles-frontend-grid', WPS_PLUGIN_URL . 'dist/grid.min.css', [], filemtime(WPS_PLUGIN_DIR_PATH . 'dist/grid.min.css'), 'all');
            }
         }

      }
   }

   public function has_version_5() {
      global $wp_version;

      if ( version_compare($wp_version, '5.0', '<' )) {
         return false;
      }

      return true;

   }

   public function scripts_deps() {

      $deps = ['jquery', 'promise-polyfill', 'fetch-polyfill', WPS_PLUGIN_TEXT_DOMAIN . '-scripts-vendors-common', WPS_PLUGIN_TEXT_DOMAIN . '-scripts-vendors-public'];

      if ($this->has_version_5()) {
         array_push($deps, 'wp-hooks');
      }

      return $deps;

   }


   /*

	Public scripts

	*/
   public function public_scripts()
   {
      if (!is_admin()) {

         global $post;
         global $wp_version;

         // wp_enqueue_script('anime-js', WPS_PLUGIN_URL . 'public/js/vendor/anime.min.js', [], filemtime(WPS_PLUGIN_DIR_PATH . 'public/js/vendor/anime.min.js'));

         wp_enqueue_script('promise-polyfill', WPS_PLUGIN_URL . 'public/js/vendor/es6-promise.auto.min.js', ['jquery'], filemtime(WPS_PLUGIN_DIR_PATH . 'public/js/vendor/es6-promise.auto.min.js'), true);

         wp_enqueue_script('fetch-polyfill', WPS_PLUGIN_URL . 'public/js/vendor/fetch.umd.js', ['promise-polyfill'], filemtime(WPS_PLUGIN_DIR_PATH . 'public/js/vendor/fetch.umd.js'), true);

         // Commonly shared third-party libs first ...
         wp_enqueue_script(
            WPS_PLUGIN_TEXT_DOMAIN . '-scripts-vendors-common',
            WPS_PLUGIN_URL . 'dist/vendors-admin-public.min.js',
            ['promise-polyfill', 'fetch-polyfill'],
            filemtime(WPS_PLUGIN_DIR_PATH . 'dist/vendors-admin-public.min.js'),
            true
         );

         // Public third-party libs second ...
         wp_enqueue_script(
            WPS_PLUGIN_TEXT_DOMAIN . '-scripts-vendors-public',
            WPS_PLUGIN_URL . 'dist/vendors-public.min.js',
            ['promise-polyfill', 'fetch-polyfill'],
            filemtime(WPS_PLUGIN_DIR_PATH . 'dist/vendors-public.min.js'),
            true
         );

         wp_enqueue_script(
            WPS_PLUGIN_TEXT_DOMAIN . '-scripts-frontend',
            WPS_PLUGIN_URL . 'dist/public.min.js',
            $this->scripts_deps(),
            filemtime(WPS_PLUGIN_DIR_PATH . 'dist/public.min.js'),
            true
         );

         wp_localize_script(WPS_PLUGIN_TEXT_DOMAIN . '-scripts-frontend', WPS_PLUGIN_NAME_JS, [
            'ajax' => apply_filters('wps_admin_ajax_url', esc_url(Utils::convert_to_relative_url(admin_url('admin-ajax.php')))),
            'pluginsPath' => esc_url(plugins_url()),
            'pluginsDirURL' => plugin_dir_url(dirname(__FILE__)),
            'pluginsDistURL' => plugin_dir_url(dirname(__FILE__)) . 'dist/',
            'productsSlug' => $this->plugin_settings['general']->url_products,
            'is_connected' => empty($this->plugin_settings['connection']->storefront_access_token) ? false : true,
            'post_id' => is_object($post) ? $post->ID : false,
            'nonce' => wp_create_nonce(WPS_FRONTEND_NONCE_ACTION),
            'note_attributes' => '',
            'nonce_api' => wp_create_nonce('wp_rest'),
            'checkoutAttributes' => [],
            'hasCartTerms' => $this->plugin_settings['general']->enable_cart_terms,
            'misc' => [
               'cache_cleared' => Data::coerce(Options::get('wp_shopify_cache_cleared'), 'bool'),
               'wp_version' => $wp_version,
               'is_mobile' => wp_is_mobile()
            ],
            'settings' => [
               'shop' => [],
               'cart' => [
                  'showFixedCartIcon' => Data::coerce($this->plugin_settings['general']->show_fixed_cart_tab, 'bool'),
                  'cartLoaded' => Data::coerce($this->plugin_settings['general']->cart_loaded, 'bool'),
                  'enableCartTerms' => Data::coerce($this->plugin_settings['general']->enable_cart_terms, 'bool'),
                  'cartTermsContent' => Data::coerce($this->plugin_settings['general']->cart_terms_content, 'string'),
                  'enableCartNotes' => Data::coerce($this->plugin_settings['general']->enable_cart_notes, 'bool'),
                  'cartNotesPlaceholder' => Data::coerce($this->plugin_settings['general']->cart_notes_placeholder, 'string'),
                  'checkoutButtonColor' => Data::coerce($this->plugin_settings['general']->checkout_color, 'string'),
                  'cartIconColor' => Data::coerce($this->plugin_settings['general']->cart_icon_color, 'string'),
                  'colorCounter' => Data::coerce($this->plugin_settings['general']->cart_counter_color, 'string'),
                  'colorCartIconFixed' => Data::coerce($this->plugin_settings['general']->cart_icon_fixed_color, 'string'),
                  'colorCartCounterFixed' => Data::coerce($this->plugin_settings['general']->cart_counter_fixed_color, 'string'),
                  'colorCartBackgroundFixed' => Data::coerce($this->plugin_settings['general']->cart_fixed_background_color, 'string')
               ],
               'products' => [],
               'collections' => [],
               'hasCurrencyCode' => $this->plugin_settings['general']->price_with_currency,
               'enableCustomCheckoutDomain' => $this->plugin_settings['general']->enable_custom_checkout_domain,
               'myShopifyDomain' => $this->plugin_settings['connection']->domain,
               'urlProducts' => Utils::get_site_url() . '/' . Data::coerce($this->plugin_settings['general']->url_products, 'string'),
               'urlCollections' => Utils::get_site_url() . '/' . Data::coerce($this->plugin_settings['general']->url_collections, 'string'),
               'checkoutButtonTarget' => Data::coerce($this->plugin_settings['general']->checkout_button_target, 'string'),
               'itemsLinkToShopify' => Data::coerce($this->plugin_settings['general']->products_link_to_shopify, 'bool'),
               'isLiteSync' => Data::coerce($this->plugin_settings['general']->is_lite_sync, 'bool'),
               'isSyncingPosts' => Data::coerce($this->plugin_settings['general']->is_syncing_posts, 'bool'),
               'searchBy' => Data::coerce($this->plugin_settings['general']->search_by, 'string'),
               'searchExactMatch' => Data::coerce($this->plugin_settings['general']->search_exact_match, 'bool'),
               'pricing' => [
                  'baseCurrencyCode' => Data::coerce($this->plugin_settings['shop']->currency, 'string'),
                  'enableLocalCurrency' => Data::coerce($this->plugin_settings['general']->pricing_local_currency_toggle, 'bool')
               ],
               'currentLocale' => Server::get_current_locale(),
               'hidePagination' => Data::coerce($this->plugin_settings['general']->hide_pagination, 'bool'),
               'layout' => [
                  'alignHeight' => Data::coerce($this->plugin_settings['general']->align_height, 'bool'),
                  'globalNoticesDropzone' => apply_filters('wps_global_notices_dropzone', false)
               ],
               'pricingCurrencyDisplayStyle' => Data::coerce($this->plugin_settings['general']->currency_display_style, 'string'),
               'productsImagesSizingToggle' => Data::coerce($this->plugin_settings['general']->products_images_sizing_toggle, 'bool'),
               'productsImagesSizingWidth' => Data::coerce($this->plugin_settings['general']->products_images_sizing_width, 'int'),
               'productsImagesSizingHeight' => Data::coerce($this->plugin_settings['general']->products_images_sizing_height, 'int'),
               'productsImagesSizingCrop' => Data::coerce($this->plugin_settings['general']->products_images_sizing_crop, 'string'),
               'productsImagesSizingScale' => Data::coerce($this->plugin_settings['general']->products_images_sizing_scale, 'int'),

               'productsThumbnailImagesSizingToggle' => Data::coerce($this->plugin_settings['general']->products_thumbnail_images_sizing_toggle, 'bool'),
               'productsThumbnailImagesSizingWidth' => Data::coerce($this->plugin_settings['general']->products_thumbnail_images_sizing_width, 'int'),
               'productsThumbnailImagesSizingHeight' => Data::coerce($this->plugin_settings['general']->products_thumbnail_images_sizing_height, 'int'),
               'productsThumbnailImagesSizingCrop' => Data::coerce($this->plugin_settings['general']->products_thumbnail_images_sizing_crop, 'string'),
               'productsThumbnailImagesSizingScale' => Data::coerce($this->plugin_settings['general']->products_thumbnail_images_sizing_scale, 'int'),

               'productsImagesShowZoom' => Data::coerce($this->plugin_settings['general']->products_images_show_zoom, 'bool'),
               'collectionsImagesSizingToggle' => Data::coerce($this->plugin_settings['general']->collections_images_sizing_toggle, 'bool'),
               'collectionsImagesSizingWidth' => Data::coerce($this->plugin_settings['general']->collections_images_sizing_width, 'int'),
               'collectionsImagesSizingHeight' => Data::coerce($this->plugin_settings['general']->collections_images_sizing_height, 'int'),
               'collectionsImagesSizingCrop' => Data::coerce($this->plugin_settings['general']->collections_images_sizing_crop, 'string'),
               'collectionsImagesSizingScale' => Data::coerce($this->plugin_settings['general']->collections_images_sizing_scale, 'int'),
               'relatedProductsImagesSizingToggle' => Data::coerce($this->plugin_settings['general']->related_products_images_sizing_toggle, 'bool'),
               'relatedProductsImagesSizingWidth' => Data::coerce($this->plugin_settings['general']->related_products_images_sizing_width, 'int'),
               'relatedProductsImagesSizingHeight' => Data::coerce($this->plugin_settings['general']->related_products_images_sizing_height, 'int'),
               'relatedProductsImagesSizingCrop' => Data::coerce($this->plugin_settings['general']->related_products_images_sizing_crop, 'string'),
               'relatedProductsImagesSizingScale' => Data::coerce($this->plugin_settings['general']->related_products_images_sizing_scale, 'int'),
               'textdomain' => WPS_PLUGIN_TEXT_DOMAIN
            ],
            'API' => [
               'namespace' => WPS_SHOPIFY_API_NAMESPACE,
               'baseUrl' => site_url(),
               'urlPrefix' => rest_get_url_prefix(),
               'restUrl' => get_rest_url(),
               'nonce' => wp_create_nonce('wp_rest')
            ],
            'storefront' => [
               'domain' => Data::coerce($this->plugin_settings['connection']->domain, 'string'),
               'storefrontAccessToken' => Data::coerce($this->plugin_settings['connection']->storefront_access_token, 'string')
            ]
         ]);

      }
   }

   public function css_body_class($classes)
   {
      $classes[] = 'wpshopify';

      return $classes;
   }

   /*

	Only hooks not meant for public consumption

	*/
   public function hooks()
   {
      add_action('wp_enqueue_scripts', [$this, 'public_styles']);
      add_action('wp_enqueue_scripts', [$this, 'public_scripts'], 1);
      add_filter('body_class', [$this, 'css_body_class']);

   }

   /*

	Init

	*/
   public function init()
   {
      $this->hooks();
   }
}
