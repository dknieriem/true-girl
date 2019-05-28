<?php 

global $wpdb;
$path =  plugin_dir_url(__FILE__); 
//echo $path;die;
$filename = 'upc_stats.xls';
$ultimate_promohits =$wpdb->prefix."ultimate_promo_hits";
$ultimate_promocode =$wpdb->prefix."ultimate_promo_code";

// Write column names
if(isset($_POST['code_name']) && $_POST['code_name']!="")
{
	$qry = "SELECT * FROM $ultimate_promohits where promo_code ='".$_POST['code_name']."'";
}
else
{
	$qry = "SELECT * FROM $ultimate_promohits";
}
$reg = $wpdb->get_results($qry);

$table = '<table class="wp-list-table widefat fixed Ulitmate_promo_codes" cellspacing="0">
  <thead>
    <tr>
      <th scope="col"  style="width:5%">Id</th>
      <th scope="col"  style="">Promo Code</th>
      <th scope="col"  style="">Email</th>
      <th scope="col"  style=" width:10%;">Date</th>
    </tr>
  </thead>
  <tfoot>
     
  </tfoot>
  <tbody id="the-list">';
 
  $i=1;
  foreach($reg as $row)
  {
	if($i%2==0)
	{
		$class="";
	}
	else
	{
		$class="alternate";
	}

     $table .= '<tr class="'.$class.'">
     <th scope="row" style="width:5%">'.$i.'</th>
      <th scope="row" style="">'.$row->promo_code.'</th>
      <th scope="row" style="">'.$row->email.'</th>
      <th scope="row" style="">'.$row->created_date.'</th>
    </tr>';
$i++;
} 
   
    
$table .='  </tbody>
</table>';

file_put_contents($filename,$table);
wp_redirect($filename);
?>