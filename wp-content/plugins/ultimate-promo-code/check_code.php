<?php
global $wpdb;
$name = trim($_POST['name']);
if(isset($_POST['prevalue']) && $_POST['prevalue']!="")
{
$prename = trim($_POST['prevalue']);
}
$ultimate_promocode =$wpdb->prefix."ultimate_promo_code";
if(isset($_POST['prevalue']) && $_POST['prevalue']!="")
{
	$qry = "select count(*) from $ultimate_promocode where binary name = %s and binary name != %s";
	$result = $wpdb->get_var($wpdb->prepare($qry,array($name,$prename)));

	if($result!=0)
	{
		echo '<div style=" color:red">Warning! Promo Code already exists. Please choose a unique code.</div>';	
	}
}
else
{

	$qry = "select * from $ultimate_promocode where binary name = %s";

	$result = $wpdb->get_var($wpdb->prepare($qry,$name));

	if($result!=0)
	{
		echo '<div style=" color:red">Warning! Promo Code already exists. Please choose a unique code.</div>';
	}

}
?>