<!--

Checkout button target (new tab or current tab)

-->
<div class="wps-form-group wps-form-group-tight">

  <table class="form-table">
    <tbody>

      <tr valign="top" class="wps-select-row">

        <th scope="row" class="titledesc wps-checkbox-row-label">
          <?php esc_attr_e( 'Checkout button target', WPS_PLUGIN_TEXT_DOMAIN ); ?>
          <span class="wps-help-tip" title="<?php esc_attr_e( 'Determines whether the checkout button will open a new tab or not. Only applicable to desktop. Mobile will always open checkout page in the current tab.', WPS_PLUGIN_TEXT_DOMAIN ); ?>"></span>
        </th>

        <td class="wps-input wps-input-select">
          <div id="wps-settings-checkout-button-target">
            <span class="wps-placeholder wps-placeholder-input"></span>
          </div>
        </td>

      </tr>

    </tbody>
  </table>

</div>
