<?php

global $wpdb;

$path =  plugin_dir_url(__FILE__); 

$ultimate_promohits =$wpdb->prefix."ultimate_promo_hits";

$ultimate_promocode =$wpdb->prefix."ultimate_promo_code";

$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;

$limit = 10; // number of rows in page
$offset = ( $pagenum - 1 ) * $limit;


if(isset($_POST['export']))
{
	//header("location:admin.php?page=Ultimate_Promo_Hits_export");	
	include 'ultimate_promo_hits_export.php';
}

if(isset($_POST['promo_hits']))

{
	
$total = $wpdb->get_var( $wpdb->prepare("SELECT count(*) FROM $ultimate_promohits where binary promo_code =%s",$_POST['promo_hits']) );
$num_of_pages = ceil( $total / $limit );

$entries = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $ultimate_promohits where binary promo_code =%s LIMIT %d, %d",array($_POST['promo_hits'],$offset,$limit)) );

}

else

{

	$qry = "SELECT * FROM $ultimate_promohits";
	
	$total = $wpdb->get_var("SELECT count(*) FROM $ultimate_promohits");
    $num_of_pages = ceil( $total / $limit );

    $entries = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $ultimate_promohits LIMIT %d, %d",array($offset,$limit)) );

}

?>

<div class="wrap"><img src="<?php echo $path.'banner.png'  ?>">

<p></p>

<h2 style="float:left;">Promo Hits</h2>

<div style="float:right;"><form method="post">

 Filter Promo Code:<select name="promo_hits" onChange="submit()">

<option value="">Select a code</option>



<?php

$qrycodes = "select * from $ultimate_promocode";

$regcodes = $wpdb->get_results($wpdb->prepare($qrycodes));

foreach($regcodes as $rowcodes)

{?>

<option value="<?php echo $rowcodes->name;?>" <?php if(isset($_POST['promo_hits']) && $_POST['promo_hits']== $rowcodes->name) echo 'selected'?>><?php echo $rowcodes->name;?></option>

<?php }

?>

</select></form></div>
<form name="hits_export" method="post">
<input type="hidden" name="code_name" value="<?php echo $_POST['promo_hits'];?>">
<input type="submit" name="export" value="Export" id="export" >
</form>

<table class="wp-list-table widefat fixed Ulitmate_promo_codes" cellspacing="0">

  <thead>

    <tr>

      <th scope="col"  style="width:5%">#</th>

      <th scope="col"  style="">Promo Code</th>

      <th scope="col"  style="">user email</th>

      <th scope="col"  style=" width:10%;">USed On</th>

    </tr>

  </thead>

  <tfoot>

     

  </tfoot>

  <tbody id="the-list">

  <?php

  $i=1 + $offset;

  foreach($entries as $row)

  {

	if($i%2==0)

	{

		$class="";

	}

	else

	{

		$class="alternate";

	}

  ?>

     <tr class="<?php echo $class;?>">

     <th scope="row" style="width:5%"><?php echo $i;?></th>

      <th scope="row" style=""><?php echo $row->promo_code;?></th>

      <th scope="row" style=""><?php echo $row->email;?></th>

      <th scope="row" style=""><?php echo $row->created_date;?></th>

    </tr>

<?php 

$i++;

} 



?>    

    

  </tbody>

</table>
<?php
$page_links = paginate_links( array(
    'base' => add_query_arg( 'pagenum', '%#%' ),
    'format' => '',
    'prev_text' => __( '&laquo;', 'text-domain' ),
    'next_text' => __( '&raquo;', 'text-domain' ),
    'total' => $num_of_pages,
    'current' => $pagenum
) );

if ( $page_links ) {
    echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
}
?>
</div>