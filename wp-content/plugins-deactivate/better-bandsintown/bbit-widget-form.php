<p>
    <label for="<?php echo $this->get_field_id('bandname'); ?>"><?php _e('Band name', 'bbit-widget'); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id('bandname'); ?>" name="<?php echo $this->get_field_name('bandname'); ?>" type="text" value="<?php echo $bandname; ?>" />
</p>

<p>
    <label for="<?php echo $this->get_field_id('theme'); ?>"><?php _e('Theme', 'bbit-widget'); ?></label>
    <select name="<?php echo $this->get_field_name('theme'); ?>" id="<?php echo $this->get_field_id('theme'); ?>" class="widefat">
    <?php
        $options = array('Dark', 'Light');
        foreach ($options as $option) {
            echo '<option value="' . $option . '" id="' . $option . '"', $theme == $option ? ' selected="selected"' : '', '>', $option, '</option>';
        }
    ?>
    </select>
</p>