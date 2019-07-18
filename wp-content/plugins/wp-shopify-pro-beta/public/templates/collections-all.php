<?php

defined('ABSPATH') ?: die;

get_header('wps');

$Collections = WPS\Factories\Render\Collections\Collections_Factory::build();
$Settings = WPS\Factories\DB\Settings_General_Factory::build();

?>

<section class="wps-container">
   <?= do_action('wps_breadcrumbs') ?>

   <div class="wps-collections-all">
      
      <?php if ($Settings->get_col_value('collections_heading_toggle')) { ?>
         <h1 class="wps-heading"><?= $Settings->get_col_value('collections_heading'); ?></h1>
      <?php }

      $Collections->collections(); ?>

   </div>

</section>


<?php

get_footer('wps');