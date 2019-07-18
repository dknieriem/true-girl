<?php

use WPS\Options;

/*

@description   Title template for each product within the main products loop

@version       2.0.0
@since         1.0.49
@path          templates/components/products/title/title.php

@docs          https://wpshop.io/docs/templates/components/products/title

*/

defined('ABSPATH') ?: exit();

?>

<?php 

$options = $data->Templates->get_template_wrapper_options($data);  

?>

<div 
   class="wps-server-component" 
   data-wps-render-from-server="<?= $options['component_render_from_server'] ?>"
   data-wps-sever-component-type="<?= $options['component_type']; ?>"
   data-wps-component-path="<?= $options['component_path'];?>">

   <?php if ($data->Templates->is_combining($data)) {

      if (is_array($options['component_data']) && !isset($options['component_data'][0])) {
         $options['component_data'] = [$options['component_data']];
      }

      if (!empty($options['component_data'])) {
         
         foreach ($options['component_data'] as $item) {
            if ($data->Templates->is_indexed_array($data->params)) {
               $full_params = $data->Templates->collpase_duplicate_items($data->params, $item);

               $data->Templates->load_single_wrapper($full_params);
            } else {
               $data->Templates->load_single_wrapper($data->params);
            }
         }

      }
   } else {


      if (is_array($data->params['data'])) {
         $data->params = $data->Templates->convert_params_data_to_object($data->params);
      }

      if (!is_array($data->Templates->first_item_in_data($data->params))) {
         $full_params = $data->Templates->combine_with_user_params($data->params, $data->user_params, $data->params['data']);

         $data->Templates->load_single_wrapper($full_params);
      } else {

         $full_params = $data->Templates->add_post_meta_to_data($data->params['data'], $data->user_params);
         $data->Templates->load_single_wrapper($full_params);
         
      }
   } ?>

</div>