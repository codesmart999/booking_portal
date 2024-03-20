<?php 
	
	include("config.php");
	
	// TODO: sync with DB
	$arrProfile = [
		"id"			=> 5343,
		"business_name" => "AB1 Directional Drilling",
		"street" 		=> "PO Box 19",
		"city" 			=> "Maitland",
		"state" 		=> "NSW",
		"postcode" 		=> "2320",
		"email_addr" 	=> "office.ab1@bigpond.com",
		"phone_number" 	=> "0412 197 858"
	];

	echo json_encode( $arrProfile );
	exit;
?>