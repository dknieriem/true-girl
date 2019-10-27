<?php

use WPS\Factories;

$Backend                  = Factories\Backend_Factory::build();
$DB_Settings_Connection   = Factories\DB\Settings_Connection_Factory::build();
$DB_Settings_License      = Factories\DB\Settings_License_Factory::build();
$DB_Settings_General      = Factories\DB\Settings_General_Factory::build();
$DB_Shop                  = Factories\DB\Shop_Factory::build();

$connection               = $DB_Settings_Connection->get();
$license                  = $DB_Settings_License->get();
$general                  = $DB_Settings_General->get();

$has_connection           = $DB_Settings_Connection->has_connection();

if ( $Backend->is_admin_settings_page( $Backend->is_valid_admin_page()->id ) ) {

  $active_tab       = $Backend->get_active_tab($_GET);
  $active_sub_nav   = $Backend->get_active_sub_tab($_GET);

}

?>

<div class="wrap wps-admin-wrap">

   <h2>
      <img style="width: 28px;height: 28px;position: relative;top: 7px;margin-right: 3px;" src='data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDIzLjAuNCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHZpZXdCb3g9IjAgMCAxMDAgMTAwIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCAxMDAgMTAwOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxnPgoJPHBhdGggZD0iTTE4LjksMjYuOGM1LjIsMCw5LjksMi45LDEyLjMsNy42bDEwLDE5LjljMCwwLDQuMy02LjksOC40LTEzLjFsMC44LTEuMmwtNS43LTEyLjVjLTAuMi0wLjQsMC4xLTAuOCwwLjUtMC44aDEzCgkJYzUuNSwwLDEwLjQsMy4yLDEyLjYsOC4ybDguNSwxOS4ybDMuOC02LjFjMi40LTQsNS41LTkuMSw4LjEtMTIuOGwyLjItMy41Qzg2LjIsMTUsNjkuNSwzLjMsNTAuMiwzLjNjLTE3LjQsMC0zMi42LDkuNS00MC43LDIzLjUKCQlIMTguOXoiLz4KCTxwYXRoIGQ9Ik05NC42LDM1bC0yLjMsMy43bDAuMSwwbC0yNSw0MC4xYy0wLjUsMC42LTEuMywwLjgtMiwwLjRjLTAuNi0wLjQtMC44LTEuMy0wLjQtMS45bDQuNS03LjNjLTIuOSwwLjMtNS45LTEtNy4yLTRMNTEuOCw0MwoJCUwyOSw3OC43Yy0wLjIsMC4zLTAuNywwLjQtMSwwLjJsLTEtMC42Yy0wLjMtMC4yLTAuNC0wLjctMC4yLTFsNC41LTcuMmMtMi44LDAuMy01LjgtMS4xLTcuMS00bC0xNy0zNC44Yy0yLjYsNS44LTQsMTIuMi00LDE5CgkJYzAsMjYsMjEsNDcsNDcsNDdzNDctMjEsNDctNDdDOTcuMiw0NC45LDk2LjMsMzkuOCw5NC42LDM1eiIvPgo8L2c+Cjwvc3ZnPgo=' /> <?php esc_attr_e( $DB_Settings_General->plugin_nice_name(), WPS_PLUGIN_TEXT_DOMAIN); ?>
      <sup class="wps-version-pill wps-version-pill-sm"><?= WPS_NEW_PLUGIN_VERSION; ?></sup>
   </h2>

   <?php

  require plugin_dir_path( __FILE__ ) . 'wps-tabs.php';
  require plugin_dir_path( __FILE__ ) . 'wps-admin-notices.php';
  require plugin_dir_path( __FILE__ ) . 'wps-tab-content-connect.php';
  require plugin_dir_path( __FILE__ ) . 'wps-tab-content-settings.php';
  require plugin_dir_path( __FILE__ ) . 'wps-tab-content-tools.php';
  require plugin_dir_path( __FILE__ ) . 'wps-tab-content-license.php';
  require plugin_dir_path( __FILE__ ) . 'wps-tab-content-help.php';
  // require plugin_dir_path( __FILE__ ) . 'wps-tab-content-misc.php';

  ?>

</div>

<div id="wps-admin-app"></div>