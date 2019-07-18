<?php

defined('ABSPATH') ?: die;

get_header('wps');

$Products = WPS\Factories\Render\Products\Products_Factory::build();
$Settings = WPS\Factories\DB\Settings_General_Factory::build();

?>

<section class="wps-container">
   <?= do_action('wps_breadcrumbs') ?>

   <div class="wps-products-all">
      
      <?php if ($Settings->get_col_value('products_heading_toggle')) { ?>
         <h1 class="wps-heading"><?= $Settings->get_col_value('products_heading'); ?></h1>
      <?php }

      $Products->products(); ?>

   </div>

</section>

<?php

get_footer('wps');