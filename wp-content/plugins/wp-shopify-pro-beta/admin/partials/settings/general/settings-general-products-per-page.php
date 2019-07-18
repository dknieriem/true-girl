<div class="wps-form-group wps-form-group-tight">

  <table class="form-table">
    <tbody>
      <tr valign="top">

        <th scope="row" class="titledesc">
          <?php esc_html_e( 'Products per page', WPS_PLUGIN_TEXT_DOMAIN ); ?>
          <span class="wps-help-tip" title="<?php esc_attr_e( 'Determines how many products to show per page. Defaults to the standard WordPress post count set within Settings - Reading. You can also override this value within each shortcode.', WPS_PLUGIN_TEXT_DOMAIN ); ?>"></span>
        </th>

        <td class="forminp forminp-text">
          <input type="number" class="small-text" id="<?= WPS_SETTINGS_GENERAL_OPTION_NAME; ?>_num_posts" name="<?= WPS_SETTINGS_GENERAL_OPTION_NAME; ?>[wps_general_num_posts]" value="<?php echo $general->num_posts ?>" placeholder="">
        </td>

      </tr>

    </tbody>
  </table>

</div>
