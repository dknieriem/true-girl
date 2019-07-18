<!--

Tab Content: Updates

-->
<div class="tab-content <?php echo $active_tab === 'tab-license' ? 'tab-content-active' : ''; ?>" data-tab-content="tab-license">

  <h3 class="wps-admin-section-heading">
    <span class="dashicons dashicons-download"></span>
    <?php esc_html_e('License', WPS_PLUGIN_TEXT_DOMAIN) ?>
  </h3>

  <div id="post-body" class="metabox-holder columns-2">

    <div id="post-body-content">

      <div class="meta-box-sortables ui-sortable">

        <?php

        /* @if NODE_ENV='pro' */
        require_once plugin_dir_path( __FILE__ ) . 'wps-tab-content-updates-license-activation.php';
        require_once plugin_dir_path( __FILE__ ) . 'wps-tab-content-updates-license-info.php';
        require_once plugin_dir_path( __FILE__ ) . 'wps-tab-content-updates-plugin-info.php';
        /* @endif */

        /* @if NODE_ENV='free' */
        require_once plugin_dir_path( __FILE__ ) . 'wps-tab-content-updates-pro.php';
        /* @endif */

        ?>

      </div>

    </div>

  </div>

</div>
