<?php

/**
 * Class Thrive_Clever_Widgets_Manager
 */
class Thrive_Clever_Widgets_Manager
{
    /* @var $loader Thrive_Clever_Widgets_Manager_Loader */
    protected $loader;
    protected $version;
    /**
     * @var Thrive_Clever_Widgets_Database_Manager
     */
    protected $dbManager;
    protected $admin;

    public function __construct()
    {
        $this->version = '1.0';
        $this->load_dependencies();
        if (is_admin()) {
            $this->define_admin_hooks();
        }
        $this->define_frontend_hooks();
    }

    /**
     * Load dependencies for the manager
     */
    private function load_dependencies()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-thrive-clever-widgets-manager-admin.php';
        require_once plugin_dir_path(__FILE__) . 'class-thrive-clever-widgets-manager-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'database/class-thrive-clever-widgets-database-manager.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Option.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Widget_Options.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Action.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Tab_Interface.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Tab.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Tab_Factory.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Posts_Tab.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Post_Types_Tab.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Direct_Urls_Tab.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Other_Screens_Tab.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Others_Tab.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Pages_Tab.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Page_Templates_Tab.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Taxonomy_Archives_Tab.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Taxonomy_Terms_Tab.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/classes/Thrive_Clever_Widgets_Visitors_Status_Tab.php';

        $this->loader = new Thrive_Clever_Widgets_Manager_Loader();
        $this->dbManager = new Thrive_Clever_Widgets_Database_Manager($this->getVersion());
    }

    /**
     * Define admin hooks for the admin manager
     */
    public function define_admin_hooks()
    {
        $admin = new Thrive_Clever_Widgets_Manager_Admin($this->getVersion());
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_scripts');
        $this->loader->add_action('wp_ajax_tcw_widget_options', $admin, 'display_widget_popup');
        $this->loader->add_action('wp_ajax_tcw_widget_save_options', $admin, 'save_options');
        $this->loader->add_action('wp_ajax_tcw_widget_save_template', $admin, 'save_template');
        $this->loader->add_action('admin_menu', $admin, 'add_settings_menu');
        $this->loader->add_action('sidebar_admin_setup', $admin, 'sidebar_admin_setup');
        $this->loader->add_action('init', $admin, 'load_plugin_textdomain');
        $this->loader->add_filter('tve_dash_installed_products', $admin, 'add_to_dashboard');
        $this->loader->add_action('init', $admin, 'checkForPluginUpdates');
        $this->loader->add_action('init', $admin, 'load_plugin_textdomain');

        $this->admin = $admin;
    }

    public function define_frontend_hooks()
    {
        $this->loader->add_filter('sidebars_widgets', $this, 'sidebars_widgets');
        $this->loader->add_action('plugins_loaded', $this, 'load_dash_version');
    }

    /**
     * Loads Thrive Dashboard version file and set it to $GLOBALS
     */
    public function load_dash_version()
    {
        $tve_dash_path = dirname(dirname(__FILE__)) . '/thrive-dashboard';
        $tve_dash_file_path = $tve_dash_path . '/version.php';

        if (is_file($tve_dash_file_path)) {
            $version = require_once($tve_dash_file_path);
            $GLOBALS['tve_dash_versions'][$version] = array(
                'path' => $tve_dash_path . '/thrive-dashboard.php',
                'folder' => '/thrive-clever-widgets',
                'from' => 'plugins'
            );
        }
    }

    function sidebars_widgets($sidebars)
    {

        if (is_admin()) {
            return $sidebars;
        }

        foreach ($sidebars as $sidebarName => &$widgetIds) {
            if ($sidebarName === 'wp_inactive_widgets' || !is_array($widgetIds)) {
                continue;
            }
            foreach ($widgetIds as $key => $widget) {
                $savedOptions = new Thrive_Clever_Widgets_Widget_Options($widget);
                $savedOptions->initOptions();
                if (!$savedOptions->displayWidget()) {
                    unset($widgetIds[$key]);
                }
            }
        }

        return $sidebars;
    }

    /**
     * Run the plugin
     */
    public function run()
    {
        try {
            $this->dbManager->check(THRIVE_CLEVER_WIDGETS_VERSION);
            $this->loader->run();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getLoader()
    {
        return $this->loader;
    }

    public function getAdminManager()
    {
        return $this->admin;
    }
}
