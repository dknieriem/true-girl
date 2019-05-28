<?php

global $wpdb;

$ultimate_promocode =$wpdb->prefix."ultimate_promo_code";

extract( shortcode_atts( array('id' => '', 

        'promolabel' => '', 

        'emaillabel' => ''), $atts ) );

    if(!$promolabel)$promolabel = "Please enter the promo code";

	if(!$emaillabel)$emaillabel = "Please enter your email address";

	

if(isset($_POST['Ultimate_promo_submit']))

{

        $input_code  = trim($_POST['promo_code']);

		$ids = $_POST['promo_ids'];

		$qry = "select * from $ultimate_promocode where id in(".$ids.") and BINARY name=%s and status=1";

		$reg = $wpdb->get_row($wpdb->prepare($qry,$input_code));

		if(empty($reg))

		{

			echo '<div class="error">Sorry! This promo code is invalid.</div>';

		}

		else

		{

			$today = date('Y-m-d');

			$sdate = $reg->start_date;

			$splitter = " ";

			$start_date = explode($splitter, $sdate);

			$edate = $reg->end_date;

			$splitter = " ";

			$end_date = explode($splitter, $edate);

			$startdate = $start_date[0];

			$enddate = $end_date[0];

			$today_time = strtotime($today);

			$start_time = strtotime($startdate);

			$expire_time = strtotime($enddate);

			if($start_time > $today_time)

			{

				echo '<div class="error">Sorry! This promo code is not active yet.</div>';	

			}

			else if($expire_time < $today_time)

			{

				echo '<div class="error">Sorry! This promo code has expired.</div>';

			}

			else

			{

				if(Ultimate_promo_validEmail($_POST['email'])==true)

				{

				   $n = Ultimate_promo_stats($reg->name);

				   if($n < $reg->limit_uses)	

				   {

					   Ultimate_promo_hit($reg->name, $reg->name, $_POST['email']);

					   wp_redirect($reg->download_url);

					   exit;

				   }

				   else

				   {

						echo '<div class="error">Sorry! This promo code has reached its maximum limit.</div>';   

				   }

				}

				else

				{

					echo '<div class="error">Sorry! this Email address is invalid.</div>';	

				}

			}

			

		}

}

    

?>

<style>

.promo_email { width: 100%;}

#Ultimate_promo_submit {margin-top:10px;}

.promo-enter input {margin-top:10px;}

.promo-enter{width:100%;}

.promo_submit {width:100%;} 

.error{padding:10px; padding-left:15px; background-color: #FFEBE8;
border-color: #CC0000;}

</style>

<form id="promo_form" action="" method="POST" >

     <div class="promo-enter"> <label for="promo_code" style="display:none;"><?php echo $promolabel; ?></label><br>

      <input type="text" value="Enter Code Here..." name="promo_code" id="promo_code" /></div>

      

      <div class="promo-enter"><label class="promo_email" for="email" style="display:none;"><?php echo $emaillabel; ?></label>

      <input type="text" value="Your Email Address" name="email" id="email" /><br></div>

      <input type="hidden" value="<?php echo $id;?>" name="promo_ids">

      <div class="promo_submit"><input type="submit" value="Enter Your Secret Word"  name="Ultimate_promo_submit" id="Ultimate_promo_submit" /></div>

</form>

