<?php if ($this->isLicenseActivated()) : ?>
    <a title="<?php echo $widget->name ?> - <?php echo __("Thrive Clever Widgets Display Options", "thrive-cw") ?>" class="tcw_widget_button"
       href="admin-ajax.php?action=tcw_widget_options&widget=<?php echo $widget->id ?>">
        <?php echo __("Thrive Widget Display Options", "thrive-cw") ?>
    </a>
<?php else : ?>
    <p>
        <?php echo __('Your Thrive Clever Widgets license is not yet activated.', 'thrive-cw') ?>
        <a href="<?php echo admin_url('admin.php?page=tve_dash_license_manager_section&return=' . rawurlencode(admin_url('widgets.php'))) ?>"><?php echo __('Click here to activate your license', 'thrive-cw') ?></a>
    </p>
<?php endif ?>
