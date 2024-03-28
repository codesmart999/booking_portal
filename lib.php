<?php
	include("functions.php");
	include("en.php");

    $link = getDBConnection();

	// Initialize Array Session
	$arrAppData = [
		"location" => "",
		"service" => "",
		"system" => "",
		"date_appointment" => "",
		"five_days" => "",
		"booking_time" => "",
		"profile_name" => "",
		"business_name" => "",
		"street" => "",
		"city" => "",
		"state" => "",
		"postcode" => "",
		"email_addr" => "",
		"phone_number" => "",
		"patient_name" => "",
		"chromis_staff" => "",
		"purchase_order" => "",
		"comment" => ""
	];

	// Manage session data
	if (isset($_POST['Submit'])) {
		$_SESSION['appointment_data'] = array_merge( $_SESSION['appointment_data'], $_POST );
	}

	// restore Session data to Array
	if (isset($_SESSION['appointment_data'])) {
		foreach( $_SESSION['appointment_data'] as $key => $val ) {
			$arrAppData[$key] = $val;
		}
	}

	// check user login for other pages
	if (!isset( $_SESSION['User'] ) && $_SERVER['REQUEST_URI'] != "/") {
		header('Location: '. SECURE_URL . LOGIN_PAGE, true, 301);
		exit(0);
	}

	// Login Process
	if ( isset($_POST['login']) && $_POST['login'] == "Submit") {
		// check Login
		$user = array();
	    session_unset();
	    $username = $_POST['username'];
	    $password = $_POST['password'];

	    $stmt = $link->prepare("SELECT UserId, Username, FirstName, LastName, Email, UserType FROM `users` WHERE `Username`=? and `Password`=MD5(?) and `Active`='Y'");
	    $stmt->bind_param('ss', $username, $password);
	    $stmt->execute();
	    $stmt->bind_result($user['UserId'], $user['Username'], $user['Firstname'], $user['Lastname'], $user['Email'], $user['UserType']);
	    $stmt->fetch();
	    $stmt->close();

    	$_SESSION['User'] = $user;
	    if ($user['UserId'] == 0){
	    	header('Location: '. SECURE_URL . LOGIN_PAGE, true, 301);
		   	exit(0);
	    } else {
	    	if( $user['UserType'] == 'A' ){
	    		header('Location: '. SECURE_URL . ADMIN_INDEX, true, 301);
		   		exit(0);
	    	} else {
		   		header('Location: '. SECURE_URL . START_PAGE, true, 301);
		   		exit(0);
		   	}
	   	}
	}

	// Store Location and Service Data
	if( empty( $arrLocations ) ) {
	    $stmt = $link->prepare("SELECT * FROM `locations` WHERE `deleted`=0");
	    $stmt->execute();
	    $stmt->bind_result($id, $name, $address, $suburb, $state, $postcode, $phone, $deleted);
	    while($stmt->fetch()) {
	        $arrLocations[$id] = array(
	        	"name"		=> $name,
	        	"address" 	=> $address,
	        	"suburb" 	=> $suburb,
	        	"state" 	=> $state,
	        	"postcode" 	=> $postcode,
	        	"phone" 	=> $phone
	        );
	    }
	    $stmt->close();
	}

    // Store Service and Service Data
    if( empty( $arrServices ) ) {
	    $stmt = $link->prepare("SELECT ServiceId, ServiceName, FullName, active FROM `services` WHERE `active`=1");
	    $stmt->execute();
	    $stmt->bind_result($id, $name, $fullname, $active);
	    while($stmt->fetch()) {
	        $arrServices[$id] = array(
	        	"id" 	=> $id,
	        	"name"	=> $name,
	        	"fullname"	=> $fullname
	        );
	    }

		$stmt->close();
	}

    $link->close();
?>