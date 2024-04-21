<?php
	include("../config.php");
	require_once('../lib.php');

	// TODO: confirm Rule of RefId
	function generateReferenceId() {
		$refId = "";
		return $refId;
	}
	$res = [
		"status" 	=> "success",
		"message"	=> _lang("success_register")
	];

    $db = getDBConnection();

	// New User
	if ( $_POST['action'] == "new_system" ) {
		// GET POST data
		$fullname 		= isset($_POST['fullname']) ? $_POST['fullname'] : "";
		$first_name 	= isset($_POST['first_name']) ? $_POST['first_name'] : "";
		$last_name 		= isset($_POST['last_name']) ? $_POST['last_name'] : "";
		$businessname 	= isset($_POST['businessname']) ? $_POST['businessname'] : "";
		$locationId 	= isset($_POST['location']) ? $_POST['location'] : 1;
		$lstreet 		= isset($_POST['lstreet']) ? $_POST['lstreet'] : "";
		$lcity 			= isset($_POST['lcity']) ? $_POST['lcity'] : "";
		$lstate 		= isset($_POST['lstate']) ? $_POST['lstate'] : "";
		$lzipcode 		= isset($_POST['lzipcode']) ? $_POST['lzipcode'] : "";
		$lcountry 		= isset($_POST['lcountry']) ? $_POST['lcountry'] : "Australia"; 
		$pstreet 		= isset($_POST['pstreet']) ? $_POST['pstreet'] : "";
		$pcity 			= isset($_POST['pcity']) ? $_POST['pcity'] : "";
		$pstate 		= isset($_POST['pstate']) ? $_POST['pstate'] : "";
		$pzipcode 		= isset($_POST['pzipcode']) ? $_POST['pzipcode'] : "";
		$pcountry 		= isset($_POST['pcountry']) ? $_POST['pcountry'] : "Australia"; 
		
		$timezone 		= isset($_POST['timezone']) ? $_POST['timezone'] : "Sydney Australia"; 
		$latitude 		= isset($_POST['latitude']) ? $_POST['latitude'] : 0; 
		$longitude 		= isset($_POST['longitude']) ? $_POST['longitude'] : 0; 

		$email_addr 	= isset($_POST['email_addr']) ? $_POST['email_addr'] : "";
		$email_addr1	= isset($_POST['email_addr1']) ? $_POST['email_addr1'] : ""; 
		$email_addr2	= isset($_POST['email_addr2']) ? $_POST['email_addr2'] : ""; 
		$phone_number 	= isset($_POST['phone_number']) ? $_POST['phone_number'] : "";
		$mobile 		= isset($_POST['mobile']) ? $_POST['mobile'] : "";
		$fax 			= isset($_POST['fax']) ? $_POST['fax'] : "";

		// Generate PWD for new
		$refId = generateReferenceId();

        // save to System Table
		$regDate = date("Y-m-d");

        $stmt = $db->prepare(
            'INSERT INTO systems (
                LocationId,
                FullName,
                ReferenceId,
                BusinessName,
                Street,
                City,
                State,
                PostCode,
                Country,
                Timezone,
                PStreet,
                PCity,
                PState,
                PPostCode,
                Latitude,
                Longitude,
                SecondEmail,
                ThirdEmail,
                Phone,
                Mobile,
                Fax,
                RegDate,
				FirstName,
				LastName,
				FirstEmail
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');

        $stmt->bind_param( 'isssssssssssssddsssssssss',
            $locationId,
			$fullname,
			$refId,
			$businessname,
			$lstreet,
			$lcity,
			$lstate,
			$lzipcode,
			$lcountry,
			$timezone,
			$pstreet,
			$pcity,
			$pstate,
			$pzipcode,
			$latitude,
			$longitude,
			$email_addr1,
			$email_addr2,
			$phone_number,
			$mobile,
			$fax,
			$regDate,
			$first_name,
			$last_name,
			$email_addr);
        $stmt->execute() or die($stmt->error);
        $stmt->close();

    	$res["status"] = "success";
    	$res["message"] = _lang("success_register");

		echo json_encode( $res );
		exit;
	}

	// Edit User
	if ( $_POST['action'] == "edit_system" ) {

		// GET POST data
		$systemId 		= isset($_POST['systemId']) ? $_POST['systemId'] : "";
		$fullname 		= isset($_POST['fullname']) ? $_POST['fullname'] : "";
		$first_name 	= isset($_POST['first_name']) ? $_POST['first_name'] : "";
		$last_name 		= isset($_POST['last_name']) ? $_POST['last_name'] : "";
		$businessname 	= isset($_POST['businessname']) ? $_POST['businessname'] : "";
		$locationId 	= isset($_POST['location']) ? $_POST['location'] : 1;
		$user_type 		= isset($_POST['user_type']) ? $_POST['user_type'] : "";
		$active 		= isset($_POST['active']) ? "Y" : "N";
		$lstreet 		= isset($_POST['lstreet']) ? $_POST['lstreet'] : "";
		$lcity 			= isset($_POST['lcity']) ? $_POST['lcity'] : "";
		$lstate 		= isset($_POST['lstate']) ? $_POST['lstate'] : "";
		$lzipcode 		= isset($_POST['lzipcode']) ? $_POST['lzipcode'] : "";
		$lcountry 		= isset($_POST['lcountry']) ? $_POST['lcountry'] : "Australia"; 
		$pstreet 		= isset($_POST['pstreet']) ? $_POST['pstreet'] : "";
		$pcity 			= isset($_POST['pcity']) ? $_POST['pcity'] : "";
		$pstate 		= isset($_POST['pstate']) ? $_POST['pstate'] : "";
		$pzipcode 		= isset($_POST['pzipcode']) ? $_POST['pzipcode'] : "";
		$pcountry 		= isset($_POST['pcountry']) ? $_POST['pcountry'] : "Australia"; 
		
		$timezone 		= isset($_POST['timezone']) ? $_POST['timezone'] : "Sydney Australia"; 
		$latitude 		= isset($_POST['latitude']) ? $_POST['latitude'] : 0; 
		$longitude 		= isset($_POST['longitude']) ? $_POST['longitude'] : 0; 

		$email_addr 	= isset($_POST['email_addr']) ? $_POST['email_addr'] : "";
		$email_addr1	= isset($_POST['email_addr1']) ? $_POST['email_addr1'] : ""; 
		$email_addr2	= isset($_POST['email_addr2']) ? $_POST['email_addr2'] : ""; 
		$phone_number 	= isset($_POST['phone_number']) ? $_POST['phone_number'] : "";
		$mobile 		= isset($_POST['mobile']) ? $_POST['mobile'] : "";
		$fax 			= isset($_POST['fax']) ? $_POST['fax'] : "";

		$system_type	= isset($_POST['system_type']) ? $_POST['system_type'] : 'D';
		$max_multiple_bookings	= isset($_POST['max_multiple_bookings']) ? $_POST['max_multiple_bookings'] : 1;

        // Update System Info
		$stmt = $db->prepare(
			'UPDATE systems
                SET LocationId = ?,
                FullName = ?,
                ReferenceId = ?,
                BusinessName = ?,
                Street = ?,
                City = ?,
                State = ?,
                PostCode = ?,
                Country = ?,
                Timezone = ?,
                PStreet = ?,
                PCity = ?,
                PState = ?,
                PPostCode = ?,
                Latitude = ?,
                Longitude = ?,
                SecondEmail = ?,
                ThirdEmail = ?,
                Phone = ?,
                Mobile = ?,
                Fax = ?,
				FirstName = ?,
				LastName = ?,
				FirstEmail = ?,
				SystemType = ?,
				MaxMultipleBookings = ?
			WHERE SystemId=?' );

        $stmt->bind_param( 'isssssssssssssddsssssssssii',
            $locationId,
			$fullname,
			$refId,
			$businessname,
			$lstreet,
			$lcity,
			$lstate,
			$lzipcode,
			$lcountry,
			$timezone,
			$pstreet,
			$pcity,
			$pstate,
			$pzipcode,
			$latitude,
			$longitude,
			$email_addr1,
			$email_addr2,
			$phone_number,
			$mobile,
			$fax,
			$first_name,
			$last_name,
			$email_addr,
			$system_type,
			$max_multiple_bookings,
			$systemId
        );

        $stmt->execute() or die($stmt->error);
        $stmt->close();

    	$res["status"] = "success";
    	$res["message"] = _lang("success_update");

		echo json_encode( $res );
		exit;
	}

	// Delete User
	if ( $_POST['action'] == "delete_system" ) {
		// Avoid Admin Edit
		$deleteId = isset($_POST['deleteId']) ? $_POST['deleteId'] : "";

    	// Delete User
		$stmt = $db->prepare( 'DELETE FROM systems WHERE SystemId=?' );
        $stmt->bind_param( 'i', $deleteId);
        $stmt->execute() or die($stmt->error);
        $stmt->close();

		$res["status"] = "success";
    	$res["message"] = _lang("success_delete");
		echo json_encode( $res );
		exit;
	}

	// Change the Services for SystemId
	if ( $_POST['action'] == "update_service_items" ) {
		$system_id = $_POST['SystemId'];
		$arr_services = $_POST['Services'];
		// print_r($system_id)
		$stmt = $db->prepare("DELETE FROM system_services WHERE SystemId=" . $system_id);
		$stmt->execute() or die($stmt->error);

		$stmt = $db->prepare(
            'INSERT INTO system_services ( SystemId, ServiceId) VALUES (?,?)');

        foreach ($arr_services as $service_id) {
			$stmt->bind_param( 'ii', $system_id, $service_id);
        	$stmt->execute() or die($stmt->error);
		}

		$stmt->close();
		exit;
	}

	if ( $_POST['action'] == "get_system" ) {
		$systemId = isset($_POST['systemId']) ? $_POST['systemId'] : "";
		$stmt = $db->prepare("SELECT S.LocationId, S.FullName, S.ReferenceId, S.BusinessName, S.Street, S.City, S.State, S.PostCode, S.Country, S.Timezone, S.PStreet, S.PCity, S.PState, S.PPostCode, S.Latitude, S.Longitude, S.SecondEmail, S.ThirdEmail, S.Phone, S.Mobile, S.Fax, S.RegDate, S.FirstName, S.LastName, S.FirstEmail, S.SystemType, S.MaxMultipleBookings
			FROM systems as S
			WHERE S.SystemId=?");
        $stmt->bind_param( 'i', $systemId);
		$stmt->execute();
		$stmt->bind_result($locationId, $fullname, $referenceId, $businessname, $lstreet, $lcity, $lstate, $lzipcode, $lcountry, $timezone, $pstreet, $pcity, $pstate, $pzipcode, $latitude, $longitude, $email_addr1, $email_addr2, $phone_number, $mobile, $fax, $regdate, $first_name, $last_name, $email_addr, $system_type, $max_multiple_bookings );
		$stmt->store_result();
		$stmt->fetch();
		$res["status"] = "success";
		$res['data'] = [
			"SystemId" 	=> $systemId,
			"FullName" => $fullname,
			"Firstname" => $first_name,
			"Lastname" 	=> $last_name,
			"LocationId" => $locationId,
			"ReferenceId" => $referenceId,
			"BusinessName" => $businessname,
			"Street" => $lstreet,
			"City" => $lcity,
			"State" => $lstate,
			"PostCode" => $lzipcode,
			"Country" => $lcountry,
			"Timezone" => $timezone,
			"PStreet" => $pstreet,
			"PCity" => $pcity,
			"PState" => $pstate,
			"PPostCode" => $pzipcode,
			"Latitude" => $latitude,
			"Longitude" => $longitude,
			"FirstEmail" 	=> $email_addr,
			"SecondEmail" => $email_addr1,
			"ThirdEmail" => $email_addr2,
			"Phone" => $phone_number,
			"Mobile" => $mobile,
			"Fax" => $fax,
			"RegDate" => $regdate,
			"SystemType" => $system_type,
			"MaxMultipleBookings" => $max_multiple_bookings
		];
		
		echo json_encode( $res );
		exit;
	}

	if( $_POST['action'] == "get_services_by_system" ) {
		$systemId = isset($_POST['system_id']) ? $_POST['system_id'] : "";
		$stmt = $db->prepare("SELECT services.ServiceId, ServiceName, FullName, Price, Duration, IsCharge, active FROM services LEFT JOIN system_services as Sys on services.ServiceId = Sys.ServiceId 
			WHERE Sys.systemId=?");
		$stmt->bind_param( 'i', $systemId );
	    $stmt->execute();
	    $stmt->bind_result($serviceId, $servicename, $fullname,  $price, $duration, $charge, $active);
	    $stmt->store_result();

	    $arrServices = array();
	    if ($stmt->num_rows > 0) {
			while ($stmt->fetch()) {
				$arrServices[] = [
					"name"		=> $fullname,
					"price"		=> displayPrice($price),
					"duration"	=> $duration,
					"charge"	=> displayYN($charge),
					"active"	=> displayYN($active)
				];
			}
		}

		$res["status"] = "success";
		$res['data'] = $arrServices;

		echo json_encode( $res );
		exit;

	}
    $db->close();
?>