<div class="wrap">
  <h2>Head & Footer Code</h2>
  <div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">
      <div id="post-body-content">
  <form action="options.php" method="post" id="<?php echo $plugin_id; ?>_options_form" name="<?php echo $plugin_id; ?>_options_form">
  <?php settings_fields($plugin_id.'_options'); ?>
    <div>
      <label for="aFhfc_site_wide_head_code">
        <h3 class="title">Site-wide Head Code</h3>
        <p><textarea name="aFhfc_site_wide_head_code" rows="10" cols="50" id="aFhfc_site_wide_head_code" class="large-text code"><?php echo get_option('aFhfc_site_wide_head_code'); ?></textarea></p>
      </label>
      <label class="aFtext" for="aFhfc_head_code_priority"><strong>Head code Priority:</strong></label> <input id="aFhfc_head_code_priority" class="small-text" type="number" min="0" step="1" name="aFhfc_head_code_priority" value="<?php echo get_option('aFhfc_head_code_priority'); ?>" />
      <span class="description">Higher number = ealier output | Lower number = later output | Default = 10</span>
      <p>&nbsp;</p>
    </div>
    <div>
      <label for="aFhfc_site_wide_body_code">
        <h3 class="title">Site-wide Body Start Code <span class="dashicons dashicons-info" title="Inserts immediately after the opening body tag."></span></h3>
        <p><textarea name="aFhfc_site_wide_body_code" rows="10" cols="50" id="aFhfc_site_wide_body_code" class="large-text code"><?php echo get_option('aFhfc_site_wide_body_code'); ?></textarea></p>
      </label>
      <p>&nbsp;</p>
    </div>
    <div>
      <label for="aFhfc_site_wide_footer_code">
        <h3 class="title">Site-wide Footer Code</h3>
        <p><textarea name="aFhfc_site_wide_footer_code" rows="10" cols="50" id="aFhfc_site_wide_footer_code" class="large-text code"><?php echo get_option('aFhfc_site_wide_footer_code'); ?></textarea></p>
      </label>
      <label class="aFtext" for="aFhfc_footer_code_priority"><strong>Footer code Priority:</strong></label> <input id="aFhfc_footer_code_priority" class="small-text" type="number" min="0" step="1" name="aFhfc_footer_code_priority" value="<?php echo get_option('aFhfc_footer_code_priority'); ?>" />
      <span class="description">Higher number = ealier output | Lower number = later output | Default = 10</span>
    </div>
<?php submit_button(); ?>
  </form>
      </div> <!-- post-body-content -->
      <!-- sidebar -->
      <div id="postbox-container-1" class="postbox-container">
      </div> <!-- #postbox-container-1 .postbox-container -->
    </div> <!-- #post-body .metabox-holder .columns-2 -->
    <br class="clear">
  </div> <!-- #poststuff -->
</div>
