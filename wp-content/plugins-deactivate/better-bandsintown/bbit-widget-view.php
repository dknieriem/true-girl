<div class="bbit-widget_wrapper <?php echo $css_class; ?>">
    <a class="bandsintown-logo-link" href="http://www.bandsintown.com/<?php echo $bandname; ?>">
        <div class="bandsintown-logo-wrapper">
            <img class="bandsintown-logo" src="<?php echo plugins_url('images/bandsintown-logo.png', __FILE__ ) ?>" />
            <div class="bbit-widget-bandname"><?php echo $bandname; ?></div>
            <div class="clear-both"></div>
        </div>
    </a>
    <a href="http://www.bandsintown.com" 
       class="bit-widget-initializer"
       data-artist="<?php echo $bandname; ?>" 
       data-force-narrow-layout="true" 
       data-display-limit="3">
        Bandsintown
    </a>
</div>