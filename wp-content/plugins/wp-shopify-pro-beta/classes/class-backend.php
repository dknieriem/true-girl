<?php

namespace WPS;

if (!defined('ABSPATH')) {
    exit();
}

use WPS\Options;

class Backend
{
    private $DB_Settings_General;
    private $DB_Settings_Connection;

    public function __construct($DB_Settings_General, $DB_Settings_Connection)
    {
        $this->DB_Settings_General = $DB_Settings_General;
        $this->DB_Settings_Connection = $DB_Settings_Connection;
    }

    /*

     Checks for a valid admin page

     */
    public function is_valid_admin_page()
    {
        $screen = get_current_screen();

        if (empty($screen)) {
            return false;
        }

        if (!is_admin()) {
            return false;
        }

        return $screen;
    }

    /*

     Checks for a valid admin page

     */
    public function get_screen_id()
    {
        $screen = $this->is_valid_admin_page();

        if (empty($screen)) {
            return false;
        }

        return $screen->id;
    }

    /*

     Checks for the correct admin page to load CSS

     */
    public function should_load_css()
    {
        if (!$this->is_valid_admin_page()) {
            return;
        }

        $screen_id = $this->get_screen_id();

        if ($this->is_admin_settings_page($screen_id) || $this->is_admin_posts_page($screen_id) || $this->is_admin_plugins_page($screen_id)) {
            return true;
        }

        return false;
    }

    /*

     Checks for the correct admin page to load JS

     */
    public function should_load_js()
    {
        if (!$this->is_valid_admin_page()) {
            return;
        }

        $screen_id = $this->get_screen_id();

        // Might want to check these eventually
        // || $this->is_admin_posts_page($screen_id)

        if ($this->is_admin_settings_page($screen_id)) {
            return true;
        }

        return false;
    }

    /*

     Is wp posts page

     */
    public function is_admin_posts_page($current_admin_screen_id)
    {
        if ($current_admin_screen_id === WPS_COLLECTIONS_POST_TYPE_SLUG || $current_admin_screen_id === WPS_PRODUCTS_POST_TYPE_SLUG) {
            return true;
        }
    }

    /*

     Is wp nav menus page

     */
    public function is_admin_nav_page($current_admin_screen_id)
    {
        if ($current_admin_screen_id === 'nav-menus') {
            return true;
        }
    }

    /*

     Is wp plugins page

     */
    public function is_admin_plugins_page($current_admin_screen_id)
    {
        if ($current_admin_screen_id === 'plugins') {
            return true;
        }
    }

    /*

     Is plugin settings page

     */
    public function is_admin_settings_page($current_admin_screen_id = false)
    {
        if (strpos($current_admin_screen_id, 'wp-shopify') !== false) {
            return true;
        }
    }

    /*

     Admin styles

     */
    public function admin_styles()
    {
        if ($this->should_load_css()) {
            wp_enqueue_style('wp-color-picker');

            //Enqueue the jQuery UI theme css file from google:
            wp_enqueue_style('jquery-ui-css', '//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css', false, '1.9.0', false);

            wp_enqueue_style('animate-css', WPS_PLUGIN_URL . 'admin/css/vendor/animate.min.css', [], filemtime(WPS_PLUGIN_DIR_PATH . 'admin/css/vendor/animate.min.css'));

            wp_enqueue_style('tooltipster-css', WPS_PLUGIN_URL . 'admin/css/vendor/tooltipster.min.css', [], filemtime(WPS_PLUGIN_DIR_PATH . 'admin/css/vendor/tooltipster.min.css'));

            wp_enqueue_style('chosen-css', WPS_PLUGIN_URL . 'admin/css/vendor/chosen.min.css', [], filemtime(WPS_PLUGIN_DIR_PATH . 'admin/css/vendor/chosen.min.css'));

            wp_enqueue_style('gutenberg-components-css', WPS_PLUGIN_URL . 'dist/gutenberg-components.min.css', [], filemtime(WPS_PLUGIN_DIR_PATH . 'dist/gutenberg-components.min.css'));

            wp_enqueue_style(
                WPS_PLUGIN_TEXT_DOMAIN . '-styles-backend',
                WPS_PLUGIN_URL . 'dist/admin.min.css',
                ['wp-color-picker', 'animate-css', 'tooltipster-css', 'chosen-css'],
                filemtime(WPS_PLUGIN_DIR_PATH . 'dist/admin.min.css')
         );
        }
    }

    /*

     Admin scripts

     */
    public function admin_scripts()
    {
        if ($this->should_load_js()) {
            wp_enqueue_script('jquery-ui-slider');

            wp_enqueue_script('promise-polyfill', WPS_PLUGIN_URL . 'admin/js/vendor/es6-promise.auto.min.js', ['jquery'], filemtime(WPS_PLUGIN_DIR_PATH . 'admin/js/vendor/es6-promise.auto.min.js'));

            wp_enqueue_script('tooltipster-js', WPS_PLUGIN_URL . 'admin/js/vendor/jquery.tooltipster.min.js', ['jquery'], filemtime(WPS_PLUGIN_DIR_PATH . 'admin/js/vendor/jquery.tooltipster.min.js'));

            wp_enqueue_script('validate-js', WPS_PLUGIN_URL . 'admin/js/vendor/jquery.validate.min.js', ['jquery'], filemtime(WPS_PLUGIN_DIR_PATH . 'admin/js/vendor/jquery.validate.min.js'));

            wp_enqueue_script('chosen-js', WPS_PLUGIN_URL . 'admin/js/vendor/chosen.jquery.min.js', ['jquery'], filemtime(WPS_PLUGIN_DIR_PATH . 'admin/js/vendor/chosen.jquery.min.js'));

            wp_enqueue_script('anime-js', WPS_PLUGIN_URL . 'admin/js/vendor/anime.min.js', [], filemtime(WPS_PLUGIN_DIR_PATH . 'admin/js/vendor/anime.min.js'));

            // Third-party libs first ...
            wp_enqueue_script(WPS_PLUGIN_TEXT_DOMAIN . '-scripts-vendors-admin', WPS_PLUGIN_URL . 'dist/vendors-admin.min.js', [], filemtime(WPS_PLUGIN_DIR_PATH . 'dist/vendors-admin.min.js'));

            // Commonly shared third-party libs second ...
            wp_enqueue_script(
                WPS_PLUGIN_TEXT_DOMAIN . '-scripts-vendors-common',
                WPS_PLUGIN_URL . 'dist/vendors-admin-public.min.js',
                [],
                filemtime(WPS_PLUGIN_DIR_PATH . 'dist/vendors-admin-public.min.js')
         );

            // Commonly shared pub / admin code ...
            // wp_enqueue_script(
            // 	WPS_PLUGIN_TEXT_DOMAIN . '-scripts-admin-public-common',
            // 	WPS_PLUGIN_URL . 'dist/admin-public.min.js',
            // 	[],
            // 	filemtime( WPS_PLUGIN_DIR_PATH . 'dist/admin-public.min.js' )
            // );

            wp_enqueue_script(
                WPS_PLUGIN_TEXT_DOMAIN . '-scripts-backend',
                WPS_PLUGIN_URL . 'dist/admin.min.js',
                ['jquery', 'promise-polyfill', 'tooltipster-js', 'validate-js', 'chosen-js', WPS_PLUGIN_TEXT_DOMAIN . '-scripts-vendors-admin', WPS_PLUGIN_TEXT_DOMAIN . '-scripts-vendors-common'],
                filemtime(WPS_PLUGIN_DIR_PATH . 'dist/admin.min.js')
         );

            wp_localize_script(WPS_PLUGIN_TEXT_DOMAIN . '-scripts-backend', WPS_PLUGIN_NAME_JS, [
            'ajax' => __(admin_url('admin-ajax.php')),
            'pluginsPath' => __(plugins_url()),
            'siteUrl' => site_url(),
            'pluginsDirURL' => plugin_dir_url(dirname(__FILE__)),
            'nonce' => wp_create_nonce(WPS_BACKEND_NONCE_ACTION),
            'nonce_api' => wp_create_nonce('wp_rest'),
            'selective_sync' => $this->DB_Settings_General->selective_sync_status(),
            'reconnectingWebhooks' => false,
            'hasConnection' => $this->DB_Settings_Connection->has_connection(),
            'isSyncing' => false,
            'manuallyCanceled' => false,
            'isClearing' => false,
            'isDisconnecting' => false,
            'isConnecting' => false,
            'latestVersion' => WPS_NEW_PLUGIN_VERSION,
            'latestVersionCombined' => str_replace('.', '', WPS_NEW_PLUGIN_VERSION),
            'migrationNeeded' => Options::get('wp_shopify_migration_needed'),
            'itemsPerRequest' => $this->DB_Settings_General->get_items_per_request(),
            'maxItemsPerRequest' => WPS_MAX_ITEMS_PER_REQUEST,
            'settings' => [
               'layoutAlignHeight' => $this->DB_Settings_General->get_col_value('align_height', 'bool'),
               'colorAddToCart' => $this->DB_Settings_General->get_add_to_cart_color(),
               'colorVariant' => $this->DB_Settings_General->get_variant_color(),
               'colorCheckout' => $this->DB_Settings_General->get_checkout_color(),
               'colorCartCounter' => $this->DB_Settings_General->get_cart_counter_color(),
               'colorCartIcon' => $this->DB_Settings_General->get_cart_icon_color(),
               'colorCartIconFixed' => $this->DB_Settings_General->get_col_value('cart_icon_fixed_color', 'string'),
               'productsHeading' => $this->DB_Settings_General->get_products_heading(),
               'collectionsHeading' => $this->DB_Settings_General->get_collections_heading(),
               'relatedProductsHeading' => $this->DB_Settings_General->get_related_products_heading(),
               'productsHeadingToggle' => $this->DB_Settings_General->get_products_heading_toggle(),
               'collectionsHeadingToggle' => $this->DB_Settings_General->get_collections_heading_toggle(),
               'relatedProductsHeadingToggle' => $this->DB_Settings_General->get_related_products_heading_toggle(),
               'productsImagesSizingToggle' => $this->DB_Settings_General->get_products_images_sizing_toggle(),
               'productsImagesSizingWidth' => $this->DB_Settings_General->get_products_images_sizing_width(),
               'productsImagesSizingHeight' => $this->DB_Settings_General->get_products_images_sizing_height(),
               'productsImagesSizingCrop' => $this->DB_Settings_General->get_products_images_sizing_crop(),
               'productsImagesSizingScale' => $this->DB_Settings_General->get_products_images_sizing_scale(),

               'productsThumbnailImagesSizingToggle' => $this->DB_Settings_General->get_col_value('products_thumbnail_images_sizing_toggle', 'bool'),
               'productsThumbnailImagesSizingWidth' => $this->DB_Settings_General->get_col_value('products_thumbnail_images_sizing_width', 'int'),
               'productsThumbnailImagesSizingHeight' => $this->DB_Settings_General->get_col_value('products_thumbnail_images_sizing_height', 'int'),
               'productsThumbnailImagesSizingCrop' => $this->DB_Settings_General->get_col_value('products_thumbnail_images_sizing_crop', 'string'),
               'productsThumbnailImagesSizingScale' => $this->DB_Settings_General->get_col_value('products_thumbnail_images_sizing_scale', 'int'),

               'productsImagesShowZoom' => $this->DB_Settings_General->get_col_value('products_images_show_zoom', 'bool'),
               'collectionsImagesSizingToggle' => $this->DB_Settings_General->get_collections_images_sizing_toggle(),
               'collectionsImagesSizingWidth' => $this->DB_Settings_General->get_collections_images_sizing_width(),
               'collectionsImagesSizingHeight' => $this->DB_Settings_General->get_collections_images_sizing_height(),
               'collectionsImagesSizingCrop' => $this->DB_Settings_General->get_collections_images_sizing_crop(),
               'collectionsImagesSizingScale' => $this->DB_Settings_General->get_collections_images_sizing_scale(),
               'relatedProductsImagesSizingToggle' => $this->DB_Settings_General->get_related_products_images_sizing_toggle(),
               'relatedProductsImagesSizingWidth' => $this->DB_Settings_General->get_related_products_images_sizing_width(),
               'relatedProductsImagesSizingHeight' => $this->DB_Settings_General->get_related_products_images_sizing_height(),
               'relatedProductsImagesSizingCrop' => $this->DB_Settings_General->get_related_products_images_sizing_crop(),
               'relatedProductsImagesSizingScale' => $this->DB_Settings_General->get_related_products_images_sizing_scale(),
               'enableCustomCheckoutDomain' => $this->DB_Settings_General->get_enable_custom_checkout_domain(),
               'pricingCompareAt' => $this->DB_Settings_General->get_products_compare_at(),
               'enableCartNotes' => $this->DB_Settings_General->get_col_value('enable_cart_notes', 'bool'),
               'cartNotesPlaceholder' => $this->DB_Settings_General->get_col_value('cart_notes_placeholder', 'string'),
               'enableCartTerms' => $this->DB_Settings_General->get_col_value('enable_cart_terms', 'bool'),
               'cartTerms' => $this->DB_Settings_General->get_col_value('cart_terms_content', 'string'),
               'pricingShowPriceRange' => $this->DB_Settings_General->get_col_value('products_show_price_range', 'bool'),
               'pricingCurrencyDisplayStyle' => $this->DB_Settings_General->get_col_value('currency_display_style', 'string'),
               'checkoutButtonTarget' => $this->DB_Settings_General->get_col_value('checkout_button_target', 'string'),
               'cartShowFixedCartTab' => $this->DB_Settings_General->get_col_value('show_fixed_cart_tab', 'bool'),
               'cartIconFixedColor' => $this->DB_Settings_General->get_col_value('cart_icon_fixed_color', 'string'),
               'cartCounterFixedColor' => $this->DB_Settings_General->get_col_value('cart_counter_fixed_color', 'string'),
               'cartFixedBackgroundColor' => $this->DB_Settings_General->get_col_value('cart_fixed_background_color', 'string'),
               'pricingLocalCurrencyToggle' => $this->DB_Settings_General->get_col_value('pricing_local_currency_toggle', 'bool'),
               'pricingLocalCurrencyWithBase' => $this->DB_Settings_General->get_col_value('pricing_local_currency_with_base', 'bool'),
               'synchronousSync' => $this->DB_Settings_General->get_col_value('synchronous_sync', 'bool'),
               'isLiteSync' => $this->DB_Settings_General->is_lite_sync(),
               'isSyncingPosts' => $this->DB_Settings_General->is_syncing_posts(),
               'selectiveSyncAll' => $this->DB_Settings_General->get_col_value('selective_sync_all', 'bool'),
               'selectiveSyncProducts' => $this->DB_Settings_General->get_col_value('selective_sync_products', 'bool'),
               'selectiveSyncCollections' => $this->DB_Settings_General->get_col_value('selective_sync_collections', 'bool'),
               'selectiveSyncCustomers' => $this->DB_Settings_General->get_col_value('selective_sync_customers', 'bool'),
               'selectiveSyncOrders' => $this->DB_Settings_General->get_col_value('selective_sync_orders', 'bool'),
               'disableDefaultPages' => $this->DB_Settings_General->get_col_value('disable_default_pages', 'bool'),
               'searchBy' => $this->DB_Settings_General->get_col_value('search_by', 'string'),
               'searchExactMatch' => $this->DB_Settings_General->get_col_value('search_exact_match', 'bool'),
               'connection' => [
                  'saveConnectionOnly' => $this->DB_Settings_General->get_col_value('save_connection_only', 'bool')
               ]
            ],
            'API' => [
               'namespace' => WPS_SHOPIFY_API_NAMESPACE,
               'baseUrl' => site_url(),
               'urlPrefix' => rest_get_url_prefix(),
               'restUrl' => get_rest_url(),
               'nonce' => wp_create_nonce('wp_rest')
            ],
            'timers' => [
               'syncing' => false
            ]
         ]);
        }
    }

    /*

     Registering the admin menu into the WordPress Dashboard menu.
     Adding a settings page to the Settings menu.

     */
    public function add_dashboard_menus()
    {
        if (current_user_can('manage_options')) {
            $plugin_name = $this->DB_Settings_General->plugin_nice_name();

            global $submenu;

            $icon_svg =
            'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDIzLjAuNCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHZpZXdCb3g9IjAgMCAxMDAgMTAwIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCAxMDAgMTAwOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxnPgoJPHBhdGggZD0iTTE4LjksMjYuOGM1LjIsMCw5LjksMi45LDEyLjMsNy42bDEwLDE5LjljMCwwLDQuMy02LjksOC40LTEzLjFsMC44LTEuMmwtNS43LTEyLjVjLTAuMi0wLjQsMC4xLTAuOCwwLjUtMC44aDEzCgkJYzUuNSwwLDEwLjQsMy4yLDEyLjYsOC4ybDguNSwxOS4ybDMuOC02LjFjMi40LTQsNS41LTkuMSw4LjEtMTIuOGwyLjItMy41Qzg2LjIsMTUsNjkuNSwzLjMsNTAuMiwzLjNjLTE3LjQsMC0zMi42LDkuNS00MC43LDIzLjUKCQlIMTguOXoiLz4KCTxwYXRoIGQ9Ik05NC42LDM1bC0yLjMsMy43bDAuMSwwbC0yNSw0MC4xYy0wLjUsMC42LTEuMywwLjgtMiwwLjRjLTAuNi0wLjQtMC44LTEuMy0wLjQtMS45bDQuNS03LjNjLTIuOSwwLjMtNS45LTEtNy4yLTRMNTEuOCw0MwoJCUwyOSw3OC43Yy0wLjIsMC4zLTAuNywwLjQtMSwwLjJsLTEtMC42Yy0wLjMtMC4yLTAuNC0wLjctMC4yLTFsNC41LTcuMmMtMi44LDAuMy01LjgtMS4xLTcuMS00bC0xNy0zNC44Yy0yLjYsNS44LTQsMTIuMi00LDE5CgkJYzAsMjYsMjEsNDcsNDcsNDdzNDctMjEsNDctNDdDOTcuMiw0NC45LDk2LjMsMzkuOCw5NC42LDM1eiIvPgo8L2c+Cjwvc3ZnPgo=';

            // Main menu
            add_menu_page(
                __($plugin_name, WPS_PLUGIN_TEXT_DOMAIN),
                __($plugin_name, WPS_PLUGIN_TEXT_DOMAIN),
                'manage_options',
                'wpshopify',
                [$this, 'plugin_admin_page'],
                $icon_svg,
                null
         );

            // Submenu: Settings
            add_submenu_page('wpshopify', __('Settings', WPS_PLUGIN_TEXT_DOMAIN), __('Settings', WPS_PLUGIN_TEXT_DOMAIN), 'manage_options', 'wps-settings', [$this, 'plugin_admin_page']);

            // Submenu: Tools
            add_submenu_page('wpshopify', __('Tools', WPS_PLUGIN_TEXT_DOMAIN), __('Tools', WPS_PLUGIN_TEXT_DOMAIN), 'manage_options', 'wps-tools', [$this, 'plugin_admin_page']);

            // Submenu: Products
            add_submenu_page('wpshopify', __('Products', WPS_PLUGIN_TEXT_DOMAIN), __('Products', WPS_PLUGIN_TEXT_DOMAIN), 'manage_options', 'edit.php?post_type=' . WPS_PRODUCTS_POST_TYPE_SLUG, null);

            // Submenu: Collections
            add_submenu_page('wpshopify', __('Collections', WPS_PLUGIN_TEXT_DOMAIN), __('Collections', WPS_PLUGIN_TEXT_DOMAIN), 'manage_options', 'edit.php?post_type=' . WPS_COLLECTIONS_POST_TYPE_SLUG, null);

            //
            // // Submenu: Tags
            // add_submenu_page(
            // 	'wpshopify',
            // 	__('Tags', WPS_PLUGIN_TEXT_DOMAIN),
            // 	__('Tags', WPS_PLUGIN_TEXT_DOMAIN),
            // 	'manage_options',
            // 	'edit-tags.php?taxonomy=wps_tags&post_type=' . WPS_PRODUCTS_POST_TYPE_SLUG,
            // 	null
            // );

            remove_submenu_page('wpshopify', 'wpshopify');
        }
    }

    /*

     Add settings action link to the plugins page.

     */
    public function add_action_links($links)
    {
        $settings_link = ['<a href="' . esc_url(admin_url('/admin.php?page=' . WPS_PLUGIN_NAME) . '-settings') . '">' . esc_html__('Settings', WPS_PLUGIN_TEXT_DOMAIN) . '</a>'];

        return array_merge($settings_link, $links);
    }

    /*

     Render the settings page for this plugin.

     */
    public function plugin_admin_page()
    {
        include_once WPS_PLUGIN_DIR_PATH . 'admin/partials/wps-admin-display.php';
    }

    /*

     Register / Update plugin options
     Currently only updating connection form

     */
    public function on_options_update()
    {
        register_setting(WPS_SETTINGS_CONNECTION_OPTION_NAME, WPS_SETTINGS_CONNECTION_OPTION_NAME, [$this, 'connection_form_validate']);
    }

    /*

     Validate connection form settings

     */
    public function connection_form_validate($input)
    {
        $valid = [];

        // Nonce
        $valid['nonce'] = isset($input['nonce']) && !empty($input['nonce']) ? sanitize_text_field($input['nonce']) : '';

        return $valid;
    }

    public function get_active_tab($GET)
    {
        if (isset($GET['activetab']) && $GET['activetab']) {
            $active_tab = $GET['activetab'];
        } else {
            $active_tab = 'tab-connect';
        }

        if ($GET['page'] === 'wps-tools') {
            $active_tab = 'tab-tools';
        }

        return $active_tab;
    }

    public function get_active_sub_tab($GET)
    {
        if (isset($GET['activesubnav']) && $GET['activesubnav']) {
            $active_sub_nav = $GET['activesubnav'];
        } else {
            $active_sub_nav = 'wps-admin-section-general'; // default sub nav
        }

        return $active_sub_nav;
    }
    

    /*

     Hooks

     */
    public function hooks()
    {
      add_action('admin_menu', [$this, 'add_dashboard_menus']);
      add_action('admin_enqueue_scripts', [$this, 'admin_styles']);
      add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
      add_filter('plugin_action_links_' . WPS_PLUGIN_BASENAME, [$this, 'add_action_links']);
      add_action('admin_init', [$this, 'on_options_update']);
     
    }

    /*

     Init

     */
    public function init()
    {
        $this->hooks();
    }
}