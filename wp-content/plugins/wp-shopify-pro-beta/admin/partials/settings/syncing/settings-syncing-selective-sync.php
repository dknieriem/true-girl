<!--

Load default styles

-->
<div class="wps-form-group wps-form-group-tight wps-form-group-align-top">

  <table class="form-table">
    <tbody>
      <tr valign="top">

        <th scope="row" class="titledesc">
          <?php esc_attr_e( 'Selective Sync', WPS_PLUGIN_TEXT_DOMAIN ); ?>

          <span class="wps-help-tip" title="<?php esc_attr_e('Determines which type of Shopify data to sync.', WPS_PLUGIN_TEXT_DOMAIN ); ?>"></span>

        </th>

        <td class="forminp forminp-text wps-checkbox-wrapper">

            <div class="wps-label-block-wrapper">
            <div id="wps-settings-selective-sync-all"></div>
         </div>

          <div class="wps-label-block-wrapper">
            <div id="wps-settings-selective-sync-products"></div>
          </div>


          <div class="wps-label-block-wrapper">
            <div id="wps-settings-selective-sync-collections"></div>
          </div>


          <div class="wps-label-block-wrapper">
            <div id="wps-settings-selective-sync-customers"></div>
          </div>


          <div class="wps-label-block-wrapper">
            <div id="wps-settings-selective-sync-orders"></div>
          </div>

        </td>

      </tr>

    </tbody>
  </table>

</div>
