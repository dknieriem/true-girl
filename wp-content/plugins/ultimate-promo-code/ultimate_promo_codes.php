<?php

global $wpdb;

$path =  plugin_dir_url(__FILE__); 

$ultimate_promocode =$wpdb->prefix."ultimate_promo_code";

  $qry = "select * from $ultimate_promocode";
  //$ssql = $wpdb->prepare($qry);
  $reg = $wpdb->get_results($qry);

$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;

$limit = 10; // number of rows in page
$offset = ( $pagenum - 1 ) * $limit;
$total = $wpdb->get_var("select count(*) from $ultimate_promocode");
$num_of_pages = ceil( $total / $limit );

$entries = $wpdb->get_results( $wpdb->prepare("select * from $ultimate_promocode LIMIT %d, %d",array($offset, $limit)) );

?>

<div class="wrap"><img src="<?php echo $path.'banner.png'  ?>">

<h2>Promo Codes <a href="admin.php?page=Ultimate_Promo_Code&id=0&action=new" class="add-new-h2">Add New</a></h2>

<table class="wp-list-table widefat fixed Ulitmate_promo_codes" cellspacing="0">

  <thead>

    <tr>

      <th scope="col"  style="width:5%">#</th>

      <th scope="col"  style="width:15%"">Promo Code</th>

      <th scope="col"  style="width:15%"">Short Code</th>

      <th scope="col"  style="width:5%""> Hits</th>

    <!--  <th scope="col"  style="">Email</th>-->

      <th scope="col"  style="width:10%">Maximum Uses</th>

      <th scope="col"  style="width:10%">Start Date</th>

      <th scope="col"  style=" width:10%">Expiry Date</th>

      <th scope="col"  style=" width:10%;">Status</th>

    </tr>

  </thead>

  <tfoot>

  </tfoot>

  <tbody id="the-list">

    <?php

  $i= 1 + $offset;

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

	$splitter = " ";

	$start_date = explode($splitter, $row->start_date);

	$end_date = explode($splitter, $row->end_date);

  ?>

    <tr class="<?php echo $class;?>">

      <th scope="row" style="width:5%"><?php echo $i;?></th>

     

      <th scope="row" style="width:15%"><strong><?php echo $row->name;?></strong>

        <div class="row-actions">

        <span class="edit"><a href="admin.php?page=Ultimate_Promo_Code&id=<?php echo $row->id;?>&action=edit" title="Edit this item">Edit</a> | </span><span class="inline hide-if-no-js"><a href="admin.php?page=Ultimate_Promo_Code&id=<?php echo $row->id;?>&action=clear" class="editinline" title="Clear this code download hits">Clear Hits</a> | </span><span class="trash"><a class="submitdelete" title="Delete this code" href="admin.php?page=Ultimate_Promo_Code&id=<?php echo $row->id;?>&action=delete">Delete</a></span></div></th>

       <th scope="row" style="width:15%"><?php echo '[UlitmatePromo id="'.$row->id.'"]';?></th>

      <th scope="row" style="width:5%"><?php echo  Ultimate_promo_stats($row->name);?></th>

      <!--<th scope="row" style=""><?php //echo $row->email;?></th>-->

      <th scope="row" style="width:5%"><?php echo $row->limit_uses;?></th>

      <th scope="row" style="width:10%"><?php echo $start_date[0];?></th>

      <th scope="row" style="width:10%"><?php echo $end_date[0];?></th>

      <th scope="row" style="width:10%;"><?php if($row->status==1)echo 'Active'; else echo 'Disabled';;?></th>

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

