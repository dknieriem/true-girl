<?php

class TWBanner {
  public $menu_postfix = ''; // To display on only current plugin pages.
  public $prefix = ''; // Current plugin prefix.
  public $logo = ''; // Current plugin logo relative URL.
  public $plugin_slug = ''; // Current plugin slug.
  public $plugin_url = ''; // Current plugin URL.
  public $plugin_id = ''; // Current plugin id.
  public $text = ''; // Banner text.
  public $slug = ''; // Plugin slug to be installed.
  public $mu_plugin_slug = ''; // Must use plugin slug.
  public $base_php = ''; // Plugin base php filename to be installed.
  public $page_url = ''; // Redirect to URL after activating the plugin.
  public $status_install = 0; // Is plugin installed.
  public $status_active = 0; // Is plugin active.

  /**
   * TW_Banner_Class constructor.
   *
   * @param $opt_banner_param
   */
  public function __construct( $opt_banner_param ) {
    $this->menu_postfix = $opt_banner_param["menu_postfix"];
    $this->prefix = $opt_banner_param["prefix"];
    $this->logo = $opt_banner_param["logo"];
    $this->plugin_slug = $opt_banner_param['plugin_slug'];
    $this->plugin_url = $opt_banner_param["plugin_url"];
    $this->plugin_id = $opt_banner_param['plugin_id'];
    $this->text = $opt_banner_param['text'];
    $this->slug = $opt_banner_param['slug'];
    $this->mu_plugin_slug = $opt_banner_param['mu_plugin_slug'];
    $this->base_php = $opt_banner_param['base_php'];
    $this->page_url = $opt_banner_param['page_url'];
    $this->init();
  }

  /**
   * Add actions.
   */
  public function init() {
    add_action('wp_ajax_wd_tenweb_dismiss', array( $this, 'dismiss' ));
    add_action('wp_ajax_tenweb_status', array( $this, 'change_status' ));
    
    // Check the page to show banner.
    if ( ( !isset($_GET['page']) || ( preg_match("/^$this->menu_postfix/", esc_html( $_GET['page'] )) === 0 && preg_match("/$this->menu_postfix$/", esc_html( $_GET['page'] )) === 0 )) || ( isset($_GET['task']) && !strpos(esc_html($_GET['task']), 'edit') === TRUE && !(strpos(esc_html($_GET['task']), 'display') > -1)) ) {

      return;
    }

    if ( $this->is_plugin_mu($this->mu_plugin_slug) ) {
      $this->status_install = 1;
      $this->status_active = 1;
    }
    else {
      $this->upgrade_install_status();
    }
    if ( !$this->status_active ) {
      add_action('admin_notices', array( $this, 'tenweb_install_notice' ));
    }
  }

  /**
   * Check plugin install status.
   */
  public function upgrade_install_status() {
    if ( !function_exists('is_plugin_active') ) {
      include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    if ( $this->is_plugin_installed($this->slug) ) {
      $this->status_install = 1;
      if ( is_plugin_active($this->slug . '/' . $this->base_php) ) {
        $this->status_active = 1;
      }
    }
  }

  /**
   * Save status.
   */
  public function dismiss() {
    update_option('tenweb_notice_status', '1', 'no');
  }

  /**
   * Plugin install/activate status.
   *
   * @return string
   */
  public function tenweb_install_notice() {
    // Remove old notice.
    if ( get_option('tenweb_notice_status') !== FALSE ) {
      update_option('tenweb_notice_status', '1', 'no');
    }
    $meta_value = get_option('tenweb_notice_status');
    if ( $meta_value === '' || $meta_value === FALSE ) {
      ob_start();
      $dismiss_url = add_query_arg(array( 'action' => 'wd_tenweb_dismiss' ), admin_url('admin-ajax.php'));
      $verify_url = add_query_arg(array( 'action' => 'tenweb_status' ), admin_url('admin-ajax.php'));
      ?>
      <style>
        .hide {
          display: none !important;
        }
        #verifyUrl {
          display: none;
        }
        #loading {
          position: absolute;
          right: 20px;
          top: 50%;
          transform: translateY(-50%);
          margin: 0px;
          background: url("<?php echo $this->plugin_url . '/images/spinner.gif'; ?>") no-repeat;
          background-size: 20px 20px;
          filter: alpha(opacity=70);
        }
        #wd_tenweb_logo_notice {
          height: 32px;
          float: left;
        }
        .error_install,
        .error_activate {
          color: red;
          font-size: 10px;
        }
        #wpbody-content #v2_tenweb_notice_cont {
          display: none;
          flex-wrap: wrap;
          background: #fff;
          box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
          position: relative;
          margin-left: 0px;
          padding: 5px 0;
          overflow: hidden;
          border-left: 4px solid #0073AA;
          font-family: Open Sans, sans-serif;
          height: 40px;
          min-height: 40px;
          box-sizing: initial;
        }
        .v2_logo {
          display: flex;
          flex-direction: column;
          justify-content: center;
          height: inherit;
        }
        #v2_tenweb_notice_cont {
          height: 50px;
          padding: 0px;
        }
        .v2_content {
          flex-grow: 1;
          height: inherit;
          margin-left: 25px;
        }
        .v2_content p {
          margin: 0px;
          padding: 0px;
        }
        .v2_content p > span {
          font-size: 16px;
          color: #333B46;
          font-weight: 600;
          line-height: 40px;
          margin: 0;
        }
        #wd_tenweb_logo_notice {
          margin-left: 25px;
          height: 30px;
          line-height: 100%;
        }
        .v2_button {
          display: flex;
          margin-right: 30px;
          flex-direction: column;
          justify-content: center;
        }
        .v2_button #install_now, #activate_now {
          width: 112px;
          height: 32px;
          line-height: 30px;
          font-size: 14px;
          text-align: center;
          padding: 0;
        }
        #v2_tenweb_notice_cont .wd_tenweb_notice_dissmiss.notice-dismiss {
          top: 3px;
          right: 3px;
          padding: 0px;
        }
        .v2_button .button {
          position: relative;
        }
        .v2_button .button #loading {
          position: absolute;
          right: 10px;
          top: 50%;
          transform: translateY(-50%);
          margin: 0px;
          background-size: 12px 12px;
          filter: alpha(opacity=70);
          width: 12px;
          height: 12px;
        }
        @media only screen and (max-width: 1200px) and (min-width: 821px) {
          #wpbody-content #v2_tenweb_notice_cont {
            height: 50px;
            min-height: 50px;
          }
          #v2_tenweb_notice_cont {
            height: 60px;
          }
          .v2_content {
            margin-left: 25px;
          }
          .v2_content p {
            font-size: 14px;
            color: #333B46;
            font-weight: 600;
            line-height: 20px;
            margin-top: 5px;
          }
          .v2_content p span {
            display: block;
          }
          #wd_tenweb_logo_notice {
            margin-left: 25px;
            height: 30px;
            line-height: 100%;
          }
          .v2_button {
            display: flex;
            margin-right: 30px;
            flex-direction: column;
            justify-content: center;
          }
          .v2_button #install_now {
            width: 112px;
            height: 32px;
            line-height: 30px;
            font-size: 14px;
            text-align: center;
            padding: 0;
          }
          #v2_tenweb_notice_cont .wd_tenweb_notice_dissmiss.notice-dismiss {
            top: 3px;
            right: 3px;
          }
        }
        @media only screen and (max-width: 820px) and (min-width: 781px) {
          #wpbody-content #v2_tenweb_notice_cont {
            height: 50px;
            min-height: 50px;
          }
          #v2_tenweb_notice_cont {
            height: 60px;
          }
          .v2_content {
            margin-left: 25px;
          }
          .v2_content p {
            font-size: 13px;
            color: #333B46;
            font-weight: 600;
            line-height: 20px;
            margin-top: 5px;
          }
          .v2_content p span {
            display: block;
          }
        }
        @media only screen and (max-width: 780px) {
          #wpbody-content #v2_tenweb_notice_cont {
            height: auto;
            min-height: auto;
          }
          #v2_tenweb_notice_cont {
            height: auto;
            padding: 5px;
          }
          .v2_logo {
            display: block;
            height: auto;
            width: 100%;
            margin-top: 5px;
          }
          .v2_content {
            display: block;
            margin-left: 9px;
            margin-top: 10px;
            width: calc(100% - 10px);
          }
          .v2_content p {
            line-height: unset;
            font-size: 15px;
            line-height: 25px;
          }
          .v2_content p span {
            display: block;
          }
          #wd_tenweb_logo_notice {
            margin-left: 9px;
          }
          .v2_button {
            margin-left: 9px;
            margin-top: 10px;
            margin-bottom: 5px;
          }
        }
      </style>
      <script type="text/javascript">
        jQuery(document).ready(function () {
          jQuery('#v2_tenweb_notice_cont').css('display', 'flex');
        });
      </script>
      <div id="v2_tenweb_notice_cont" class="notice wd-notice">
        <div class="v2_logo">
          <img id="wd_tenweb_logo_notice" src="<?php echo $this->plugin_url . $this->logo; ?>" />
        </div>
        <div class="v2_content">
          <p>
            <?php echo $this->text ?>
          </p>
        </div>
        <div class="v2_button">
          <?php $this->tw_install_button(2); ?>
        </div>
        <button type="button" class="wd_tenweb_notice_dissmiss notice-dismiss" onclick="jQuery('#v2_tenweb_notice_cont').attr('style', 'display: none !important;'); jQuery.post('<?php echo $dismiss_url; ?>');">
          <span class="screen-reader-text"></span></button>
        <div id="verifyUrl" data-url="<?php echo $verify_url; ?>"></div>
      </div>
      <?php
      echo ob_get_clean();
    }
  }

  /**
   * Change status.
   */
  public function change_status() {
    $this->upgrade_install_status();
    if ( $this->status_install ) {
      $old_opt_array = array();
      $new_opt_array = array( $this->plugin_slug => $this->plugin_id );
      $key = 'tenweb_manager_installed';
      $option = get_option($key);
      if ( !empty($option) ) {
        $old_opt_array = (array) json_decode($option);
      }
      $array_installed = array_merge($new_opt_array, $old_opt_array);
      update_option($key, json_encode($array_installed));
    }
    $jsondata = array( 'status_install' => $this->status_install, 'status_active' => $this->status_active );
    echo json_encode($jsondata);
    exit;
  }

  /**
   * Install/activate button.
   *
   * @param $v
   */
  public function tw_install_button( $v ) {
    $prefix = $this->prefix;
    $install_url = esc_url(wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $this->slug), 'install-plugin_' . $this->slug));
    $activation_url = $this->na_action_link($this->slug . '/' . $this->base_php, 'activate');
    $tenweb_url = $this->page_url;
    $dismiss_url = add_query_arg(array( 'action' => 'wd_tenweb_dismiss' ), admin_url('admin-ajax.php'));
    $activate = $this->status_install && !$this->status_active ? TRUE : FALSE;
    ?>
    <a class="button<?php echo($v == 2 ? ' button-primary' : ''); ?> tenweb_activaion"
       id="<?php echo $activate ? 'activate_now' : 'install_now'; ?>"
       data-activation="<?php _e("Activation", $prefix); ?>"
       data-tenweb-url="<?php echo $tenweb_url; ?>"
       data-install-url="<?php echo $install_url; ?>"
       data-activate-url="<?php echo $activation_url; ?>">
      <span class="tenweb_activaion_text"><?php echo $activate ? __("Activate", $prefix) : __("Install", $prefix); ?></span>
      <span class="spinner" id="loading"></span>
    </a>
    <span class="hide <?php echo $activate ? 'error_activate' : 'error_install tenweb_active'; ?> ">
        <?php echo $activate ? __("Activation failed, please try again.", $prefix) : __("Installation failed, please try again.", $prefix); ?>
      </span>
    <script>
      var url = jQuery(".tenweb_activaion").attr("data-install-url");
      var activate_url = jQuery(".tenweb_activaion").attr("data-activate-url");

      function install_tenweb_plugin() {
        jQuery("#loading").addClass('is-active');
        jQuery(this).prop('disable', true);
        jQuery.ajax({
          method: "POST",
          url: url,
        }).done(function () {
          /* Check if plugin installed.*/
          jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: jQuery("#verifyUrl").attr('data-url'),
            error: function () {
              jQuery("#loading").removeClass('is-active');
              jQuery(".error_install").show();
            },
            success: function (response) {
              if (response.status_install == 1) {
                jQuery('#install_now .tenweb_activaion_text').text(jQuery("#install_now").data("activation"));
                activate_tenweb_plugin();
              }
              else {
                jQuery("#loading").removeClass('is-active');
                jQuery(".error_install").removeClass('hide');
              }
            }
          });
        }).fail(function () {
          jQuery("#loading").removeClass('is-active');
          jQuery(".error_install").removeClass('hide');
        });
      }

      function activate_tenweb_plugin() {
        jQuery("#activate_now #loading").addClass('is-active');
        jQuery.ajax({
          method: "POST",
          url: activate_url,
        }).done(function () {
          jQuery("#loading").removeClass('is-active');
          var data_tenweb_url = '';
          /* Check if plugin installed.*/
          jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: jQuery("#verifyUrl").attr('data-url'),
            error: function () {
              jQuery("#loading").removeClass('is-active');
              jQuery(".error_activate").removeClass('hide');
            },
            success: function (response) {
              if (response.status_active == 1) {
                //jQuery('#install_now').addClass('hide');
                data_tenweb_url = jQuery('.tenweb_activaion').attr('data-tenweb-url');
                jQuery.post('<?php echo $dismiss_url; ?>');
              }
              else {
                jQuery("#loading").removeClass('is-active');
                jQuery(".error_activate").removeClass('hide');
              }
            },
            complete: function () {
              if (data_tenweb_url != '') {
                window.location.href = data_tenweb_url;
              }
            }
          });
        }).fail(function () {
          jQuery("#loading").removeClass('is-active');
        });
      }

      jQuery("#install_now").on("click", function () {
        install_tenweb_plugin();
      });
      jQuery("#activate_now").on("click", function () {
        activate_tenweb_plugin();
      });
    </script>
    <?php
  }

  /**
   * Check if plugin is installed.
   *
   * @param $plugin_slug
   *
   * @return bool
   */
  public function is_plugin_installed( $plugin_slug ) {
    if ( is_dir(WP_PLUGIN_DIR . '/' . $plugin_slug) ) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Check if plugin is must used.
   *
   * @param $plugin_slug
   *
   * @return bool
   */
  public function is_plugin_mu( $plugin_slug ) {
    if ( $plugin_slug != '' ) {
      if ( is_dir(WPMU_PLUGIN_DIR . '/' . $plugin_slug) ) {
        return TRUE;
      }
    }

    return FALSE;
  }

  public function na_action_link( $plugin, $action = 'activate' ) {
    if ( strpos($plugin, '/') ) {
      $plugin = str_replace('\/', '%2F', $plugin);
    }
    $url = sprintf(admin_url('plugins.php?action=' . $action . '&plugin=%s&plugin_status=all&paged=1&s'), $plugin);
    $_REQUEST['plugin'] = $plugin;
    $url = wp_nonce_url($url, $action . '-plugin_' . $plugin);

    return $url;
  }
}
