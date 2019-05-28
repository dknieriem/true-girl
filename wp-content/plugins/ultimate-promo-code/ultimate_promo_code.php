<?php

global $wpdb;

$ultimate_promocode =$wpdb->prefix."ultimate_promo_code";

$ultimate_promohits =$wpdb->prefix."ultimate_promo_hits";

$path =  plugin_dir_url(__FILE__); 

if($_REQUEST['action']=='delete')

{

	$qry = "delete from $ultimate_promocode where id= %d";
	$wpdb->query($wpdb->prepare($qry,$_REQUEST['id']));	

	//wp_redirect("location:admin.php?page=Ultimate_Promo_codes");

	header("location:admin.php?page=Ultimate_Promo_codes");

	//exit();

	//header();

}



if($_REQUEST['action']=='clear')

{

	$qry = "delete from $ultimate_promohits where binary promo_code=(select name from $ultimate_promocode where id=%d)";

	//echo $qry;die;

	$wpdb->query($wpdb->prepare($qry,$_REQUEST['id']));	

	header("location:admin.php?page=Ultimate_Promo_codes");

}



if(isset($_POST['field_submit']) && $_POST['action']=='edit')

{

	$qry = "UPDATE $ultimate_promocode SET `name` = %s, `limit_uses` = %d, `download_url` = %s, `email` = %s, `status` = %d , `start_date` = %s , `end_date` = %s WHERE id = %d";

	

$wpdb->query($wpdb->prepare($qry,array($_POST['field_name'],$_POST['field_download_limit'],$_POST['field_download_url'],$_POST['field_email'],$_POST['field_status'],$_POST['field_publish_date'],$_POST['field_end_date'],$_POST['code_id'])));

header("location:admin.php?page=Ultimate_Promo_codes");



}





if(isset($_POST['field_submit']) && $_POST['action']=='new')

{

//print_r($_POST);die;

$qry = "insert into $ultimate_promocode values('',%s,%d,%s,%s,%s,%s,%d)";
$qry_pre = $wpdb->prepare($qry,array(trim($_POST['field_name']),$_POST['field_download_limit'],$_POST['field_download_url'],$_POST['field_email'],$_POST['field_publish_date'],$_POST['field_end_date'],$_POST['field_status']));
//echo $qry_pre;die;
$wpdb->query($qry_pre);

header("location:admin.php?page=Ultimate_Promo_codes");

}



if(empty($_REQUEST['id']))$id=0;else $id=$_REQUEST['id'];

$qry = "select * from $ultimate_promocode where id=%d";

$reg = $wpdb->get_results($wpdb->prepare($qry,$id));

?>

<style>

label{ float:left; width:10%;}

</style>

<div class="wrap"><img src="<?php echo $path.'banner.png'  ?>">

<form method="post" action="">
  <div class="form_field">

    <p id="namefield">

      <label for="field_name">Promo Code</label>

      <input type="text" name="field_name" id="field_name" onBlur="check()" value="<?php if(!empty($reg)) echo $reg[0]->name; ?>" required>

      <div id="user-result" style=" color:red"></div>

    </p>

    <p id="downloadlimitfield">

      <label for="field_download_limit">Maximum Uses</label>

      <input type="text" name="field_download_limit" id="field_download_limit" value="<?php if(!empty($reg)) echo $reg[0]->limit_uses; ?>" required>

      <div id="downlod_error" style=" color:red"></div>

    </p>

    <p id="downloadurlfield">

      <label for="field_download_url">File Path</label>

      <input type="text" name="field_download_url" id="field_download_url" value="<?php if(!empty($reg)) echo $reg[0]->download_url; ?>" required>
<div id="download_url_error" style=" color:red"></div>
    </p>

    <p id="emailfield" style="display:none;">

      <label for="field_email">Email</label>

      <input type="text" name="field_email" id="field_email" value="<?php if(!empty($reg)) echo $reg[0]->email; ?>">

    </p>

    <p id="publishdatefield">

      <label for="field_publish_date">Start Date</label>

      <input type="text" class="MyDate" name="field_publish_date" id="field_publish_date" value="<?php if(!empty($reg)) echo $reg[0]->start_date; ?>" required />
<div id="publish_error" style=" color:red"></div>
    </p>

    <p id="enddatefield">

      <label for="field_end_date">Expiry Date</label>

      <input type="text" class="MyDate" name="field_end_date" id="field_end_date" value="<?php if(!empty($reg)) echo $reg[0]->end_date; ?>" required />
<div id="end_error" style=" color:red"></div>
    </p>

    

    <p id="statusfield">

      <label for="field_status">Status</label>

      <select name="field_status" id="field_status">

      <option value="1" <?php if(!empty($reg)) if($reg[0]->status==1)echo 'selected'; ?>>Active</option>

      <option value="0" <?php if(!empty($reg)) if($reg[0]->status==0)echo 'selected'; ?>>Disabled</option>

      </select>

    </p>

 	<p id="submit_field">

    <input type="submit" id="field_submit" name="field_submit" class="button-primary" value="Save" style="width:auto;" onClick="return validation()" />
    <input type="button" id="field_cancel" name="field_cancel" class="button-primary" value="Cancel" style="width:auto;" onClick="window.location='admin.php?page=Ultimate_Promo_codes'"/>

    <input type="hidden" id="code_id" name="code_id" value="<?php echo $_REQUEST['id']?>" />

    <input type="hidden" id="action" name="action" value="<?php echo $_REQUEST['action']?>" />

    </p>

  </div>

</form>

</div>

<script type="text/javascript">



jQuery(document).ready(function() {

    jQuery('.MyDate').datepicker({

        dateFormat : 'yy-mm-dd'

    });

});



function check() { //user types username on inputfiled

  	 //get the string typed by user

	 name = jQuery("#field_name").val();



   jQuery.post('<?php echo get_option('siteurl').'/wp-admin/admin-ajax.php';?>?action=check_code&cookie=encodeURIComponent(document.cookie)', {'name':name,'prevalue':'<?php if(!empty($reg)) echo $reg[0]->name;?>'}, function(data) { 

   if(data=="" || data.trim()=="")

   {

	 //alert('success');
	  jQuery("#user-result").html('');

	  jQuery("#submit_field").show();

	  jQuery("#submit_field").css('display','block');

   }

   else

   {
	 //alert('failed');
   jQuery("#user-result").html(data);

   jQuery("#submit_field").hide();

   }

    //dump the data received from PHP page



   });



}
function validation()
{
	var code = jQuery('#field_name').val();
	var limit = jQuery('#field_download_limit').val();
	var url = jQuery('#field_download_url').val();
	var pdate = jQuery('#field_publish_date').val();
	var edate = jQuery('#field_end_date').val();
	if(code=="" || limit=="" || url=="" || pdate=="" || edate=="" || jQuery.trim(code)=="")
	{
	  if(jQuery.trim(code)=="")
	  {
		  jQuery('#user-result').html('Warning! Promo Code required.');
		  return false;
	  }
	  else
	  {
		  jQuery('#user-result').html('');	  
	  }
	  
	  if(limit =="")
	  {
		  jQuery('#downlod_error').html('Warning! Maximum Uses required.');
		  return false;
	  }
	  else
	  {
		  jQuery('#downlod_error').html('');
	  }
	  if(url =="")
	  {
		  jQuery('#download_url_error').html('Warning! File Path required.');
		  return false;
	  }
	  else
	  {
		  jQuery('#download_url_error').html('');  
	  }
	  if(pdate =="")
	  {
		  jQuery('#publish_error').html('Warning! Start date required.');
		  return false;
	  }
	  else
	  {
		  jQuery('#publish_error').html('');
	  }
	  if(edate =="")
	  {
		  jQuery('#end_error').html('Warning! Expiry date required.');
		  return false;
	  }
	  else
	  {
		  jQuery('#end_error').html('');
	  }
	}
	else
	{
		return true;	
	}
	
}


</script>