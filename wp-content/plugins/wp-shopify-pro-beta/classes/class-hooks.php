<?php

namespace WPS;

use WPS\Utils;
use WPS\Transients;
use WPS\Options;
use WPS\Utils\Data;

if (!defined('ABSPATH')) {
    exit();
}

class Hooks
{
    private $Utils;
    private $Templates;
    private $Async_Processing_Database;
    private $Pagination;
    private $Activator;
    private $Render_Data;


    /*

     Initialize the class and set its properties.

     */
    public function __construct($Utils, $Templates, $Async_Processing_Database, $Pagination, $Activator, $Render_Data, $plugin_settings, $DB_Settings_General)
    {
        $this->Utils = $Utils;
        $this->Templates = $Templates;
        $this->Async_Processing_Database = $Async_Processing_Database;
        $this->Pagination = $Pagination;
        $this->Activator = $Activator;
        $this->Render_Data = $Render_Data;
        $this->plugin_settings = $plugin_settings;
        $this->DB_Settings_General = $DB_Settings_General;
    }


    /*

     wps_products_custom_args

     */
    public function wps_products_custom_args($args)
    {
        return array(
         'items_per_row' => apply_filters('wps_products_custom_args_items_per_row', 3)
      );
    }

    public function wps_collections_custom_args()
    {
        return array(
         'items_per_row' => apply_filters('wps_collections_custom_args_items_per_row', 3)
      );
    }


    public function wps_products_custom_args_items_per_row($items_per_row)
    {
        return 3;
    }

    public function wps_collections_custom_args_items_per_row($items_per_row)
    {
        return 4;
    }
    public function wps_products_pagination_first_page_text()
    {
        return 'First';
    }

    public function wps_products_pagination_next_link_text()
    {
        return '';
    }

    public function wps_products_pagination_prev_link_text()
    {
        return '';
    }

    public function wps_products_pagination_prev_page_text()
    {
        return '<<';
    }

    public function wps_products_pagination_next_page_text()
    {
        return '>>';
    }

    public function wps_products_pagination_show_as_prev_next()
    {
        return false;
    }

    public function wps_products_pagination_range()
    {
        return 5;
    }

    public function wps_collection_single_heading_before($collection)
    {
        echo '';
    }

    public function wps_collection_single_heading_after($collection)
    {
        echo '';
    }

    public function wps_product_single_thumbs_class()
    {
        return;
    }

    public function wps_products_related_before()
    {
        echo '';
    }

    public function wps_products_related_after()
    {
        echo '';
    }

    public function wps_products_related_heading_end_after()
    {
        echo '';
    }

    public function wps_collection_single_products_heading_class()
    {
        return '';
    }

    public function wps_collections_heading_class($collections)
    {
        return '';
    }

    public function wps_collections_heading($collections)
    {
        return '';
    }

    // public function wps_products_title_class()
    // {
    //    return '';
    // }

    public function wps_collections_title_class()
    {
        return '';
    }
    public function wps_collections_img_class()
    {
        return '';
    }

    public function wps_products_img_class()
    {
        return '';
    }

    public function wps_collections_link_class()
    {
        return '';
    }

    public function wps_products_link_class()
    {
        return '';
    }

    public function wps_product_class()
    {
        return '';
    }
    public function wps_products_class()
    {
        return '';
    }

    public function wps_collections_class()
    {
        return '';
    }

    public function wps_collection_class()
    {
        return '';
    }

    public function wps_products_heading_class()
    {
        return '';
    }

    public function wps_collection_single_products_heading()
    {
        return 'Products';
    }

    public function wps_cart_before()
    {
        echo '';
    }

    public function wps_cart_after()
    {
        echo '';
    }

    public function wps_cart_title_text()
    {
        return 'Shopping cart';
    }

    public function wps_cart_close_icon()
    {
        return '&times;';
    }

    public function wps_cart_total_text()
    {
        return 'Total';
    }

    public function wps_cart_checkout_text()
    {
        return 'Checkout';
    }

    public function wps_syncing_settings_timeout()
    {

        if (Data::coerce($this->plugin_settings['general']->synchronous_sync, 'bool')) {
            return 99999;
        }

        return 0.01;
    }

    public function wps_syncing_settings_blocking()
    {
        if (Data::coerce($this->plugin_settings['general']->synchronous_sync, 'bool')) {
            return true;
        }

        return false;
    }

    /*

     Products heading show

     */
    public function wps_products_heading_show($show)
    {
        $related_products_show = $this->DB_Settings_General->get_products_heading_toggle();

        if (isset($related_products_show)) {
            return $related_products_show;
        } else {
            return $show;
        }
    }

    /*

     Products heading show

     */
    public function wps_collections_heading_show($show)
    {
        $related_collections_show = $this->DB_Settings_General->get_collections_heading_toggle();

        if (isset($related_collections_show)) {
            return $related_collections_show;
        } else {
            return $show;
        }
    }

    /*

     Products heading show

     */
    public function wps_related_products_heading_show($show)
    {
        $related_related_products_show = $this->DB_Settings_General->get_related_products_heading_toggle();

        if (isset($related_related_products_show)) {
            return $related_related_products_show;
        } else {
            return $show;
        }
    }

    public function wps_products_price($defaultPrice, $data)
    {
        return $defaultPrice;
    }

    public function wps_product_single_price_multi($defaultPrice, $priceFirst, $priceLast, $product)
    {
        return $defaultPrice;
    }

    public function wps_product_single_price_one($defaultPrice, $finalPrice, $product)
    {
        return $defaultPrice;
    }

    public function wps_products_args_posts_per_page($posts_per_page)
    {
        return $posts_per_page;
    }

    public function wps_products_args_orderby($orderby)
    {
        return $orderby;
    }

    public function wps_products_args_paged($paged)
    {
        return $paged;
    }

    /*

     Setting: Products link to Shopify

     */
    public function wps_products_link($wp_shopify_link, $product)
    {
        if ($this->DB_Settings_General->products_link_to_shopify()) {
            return 'https://' . $this->DB_Shop->domain() . '/products/' . $product->handle;
        } else {
            return $wp_shopify_link;
        }
    }

    /*

     Sidebar: Collections Single

     */
    public function wps_collection_single_sidebar()
    {
        if (apply_filters('wps_collection_single_show_sidebar', false)) {
            get_sidebar('wps');
        }
    }

    /*

     Sidebar: Collections

     */
    public function wps_collections_sidebar()
    {
        if (apply_filters('wps_collections_show_sidebar', false)) {
            get_sidebar('wps');
        }
    }

    /*

     Sidebar: Products Single

     */
    public function wps_product_single_sidebar()
    {
        if (apply_filters('wps_product_single_show_sidebar', false)) {
            get_sidebar('wps');
        }
    }

    /*

     Sidebar: Products

     */
    public function wps_products_sidebar()
    {
        if (apply_filters('wps_products_show_sidebar', false)) {
            get_sidebar('wps');
        }
    }


    /*

     Main Collections
     TODO: Think about combining with wps_products_args

     */
    public function wps_collections_args($shortcodeData)
    {
        $settingsNumPosts = $this->DB_Settings_General->get_num_posts();

        $paged = get_query_var('paged') ? get_query_var('paged') : 1;

        if (empty($shortcodeData->shortcodeArgs)) {
            return [
            'post_type' => WPS_COLLECTIONS_POST_TYPE_SLUG,
            'post_status' => 'publish',
            'posts_per_page' => apply_filters('wps_collections_args_posts_per_page', $settingsNumPosts),
            'orderby' => apply_filters('wps_collections_args_orderby', 'desc'),
            'paged' => apply_filters('wps_collections_args_paged', $paged)
         ];
        } else {
            $shortcodeData->shortcodeArgs['paged'] = $paged;
            return $shortcodeData->shortcodeArgs;
        }
    }

    /*

     Main Products

     */
    public function wps_products_args($shortcodeData)
    {
        $settingsNumPosts = $this->DB_Settings_General->get_num_posts();

        $paged = get_query_var('paged') ? get_query_var('paged') : 1;

        if (empty($shortcodeData->shortcodeArgs)) {
            return [
            'post_type' => WPS_PRODUCTS_POST_TYPE_SLUG,
            'post_status' => 'publish',
            'posts_per_page' => apply_filters('wps_products_args_posts_per_page', $settingsNumPosts),
            'orderby' => apply_filters('wps_products_args_orderby', 'desc'),
            'paged' => apply_filters('wps_products_args_paged', $paged)
         ];
        } else {
            $shortcodeData->shortcodeArgs['paged'] = $paged;
            return $shortcodeData->shortcodeArgs;
        }
    }

    /*

     Product single price

     */
    public function wps_product_single_price($default, $priceFirst, $priceLast, $product)
    {
        $finalPrice = '';

        if ($priceFirst !== $priceLast) {
            $defaultPrice =
            apply_filters('wps_product_single_price_multi_from', '<small class="wps-product-from-price">From: </small>') .
            apply_filters('wps_product_single_price_multi_first', $priceFirst) .
            apply_filters('wps_product_single_price_multi_separator', ' <span class="wps-product-from-price-separator">-</span> ') .
            apply_filters('wps_product_single_price_multi_last', $priceLast);

            $finalPrice = apply_filters('wps_product_single_price_multi', $defaultPrice, $priceFirst, $priceLast, $product);
        } else {
            $finalPrice = apply_filters('wps_product_single_price_one', $priceFirst, $priceFirst, $product);
        }

        return $finalPrice;
    }

    /*

     Table doesnt exist, need to notify user of that

     */
    public function show_missing_tables_notice($error)
    {
        return add_action('admin_notices', function () use ($error) {
            ?>

<div class="notice wps-notice notice-warning is-dismissible">
   <p><?= Utils::filter_error_messages($error) ?>
   </p>
</div>

<?php
        });
    }

    /*

     Runs when the plugin updates.

     Will only run once since we're updating the plugin verison after everything gets executed.

     TODO: This functions gets executed many times. Even though most of the time it will return
     immeditately, it will still make an unnesssary call to get_current_plugin_version() which
     actually gets the DB. We should figure out a way to avoid this.

     */

    public function on_plugin_load()
    {
        $new_version_number = WPS_NEW_PLUGIN_VERSION;
        $current_version_number = $this->plugin_settings['general']->plugin_version;

        //$new_version_number = '196.44';

        // If current version is behind new version
        if (version_compare($current_version_number, $new_version_number, '<')) {
            
            $this->Async_Processing_Database->sync_table_deltas();
            $this->DB_Settings_General->update_plugin_version($new_version_number);

            Transients::delete_all_cache();
            Options::delete('wp_shopify_migration_needed');

        }
    }

    /*

     For later use ... after plugin updates.

     */
    // public function after_plugin_update($upgrader_object, $options ) {
    //
    // }

    /*

     Hooks

     */
    public function hooks()
    {
        // add_action('upgrader_process_complete', [$this, 'after_plugin_update'], 10, 2 );
        add_action('plugins_loaded', [$this, 'on_plugin_load']);
        
        add_action('wps_products_sidebar', [$this, 'wps_products_sidebar']);
        add_action('wps_product_single_sidebar', [$this, 'wps_product_single_sidebar']);
        add_action('wps_collections_sidebar', [$this, 'wps_collections_sidebar']);
        add_action('wps_collection_single_sidebar', [$this, 'wps_collection_single_sidebar']);


        add_filter('wps_collections_args', [$this, 'wps_collections_args']);
        add_filter('wps_collections_custom_args', [$this, 'wps_collections_custom_args']);
        add_filter('wps_collections_custom_args_items_per_row', [$this, 'wps_collections_custom_args_items_per_row']);
        add_filter('wps_collection_single_products_heading_class', [$this, 'wps_collection_single_products_heading_class']);
        
        add_filter('wps_products_args', [$this, 'wps_products_args']);
        add_filter('wps_products_args_posts_per_page', [$this, 'wps_products_args_posts_per_page']);
        add_filter('wps_products_args_orderby', [$this, 'wps_products_args_orderby']);
        add_filter('wps_products_args_paged', [$this, 'wps_products_args_paged']);
        add_filter('wps_products_custom_args', [$this, 'wps_products_custom_args']);
        add_filter('wps_products_custom_args_items_per_row', [$this, 'wps_products_custom_args_items_per_row']);
        add_filter('wps_products_price', [$this, 'wps_products_price'], 10, 2);
   

        add_filter('wps_products_heading_show', [$this, 'wps_products_heading_show']);
        add_filter('wps_collections_heading_show', [$this, 'wps_collections_heading_show']);
        add_filter('wps_related_products_heading_show', [$this, 'wps_related_products_heading_show']);

        add_filter('wps_product_single_thumbs_class', [$this, 'wps_product_single_thumbs_class'], 10, 2);
        add_filter('wps_product_single_price', [$this, 'wps_product_single_price'], 10, 4);
        add_filter('wps_product_single_price_multi', [$this, 'wps_product_single_price_multi'], 10, 4);
        add_filter('wps_product_single_price_one', [$this, 'wps_product_single_price_one'], 10, 3);
        add_filter('wps_products_link', [$this, 'wps_products_link'], 10, 3);

        add_filter('wps_syncing_settings_timeout', [$this, 'wps_syncing_settings_timeout']);
        add_filter('wps_syncing_settings_blocking', [$this, 'wps_syncing_settings_blocking']);
    }

    /*

     Init

     */
    public function init()
    {
        $this->hooks();
    }
}