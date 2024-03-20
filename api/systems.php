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
	if( $_POST['action'] == "new_user" ) {
		// GET POST data
		$username 		= isset($_POST['username']) ? $_POST['username'] : "";
		$first_name 	= isset($_POST['first_name']) ? $_POST['first_name'] : "";
		$last_name 		= isset($_POST['last_name']) ? $_POST['last_name'] : "";
		$fullname 		= isset($_POST['fullname']) ? $_POST['fullname'] : "";
		$businessname 	= isset($_POST['businessname']) ? $_POST['businessname'] : "";
		$locationId 	= isset($_POST['location']) ? $_POST['location'] : 1;
		$email_addr 	= isset($_POST['email_addr']) ? $_POST['email_addr'] : "";
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

		$email_addr1	= isset($_POST['email_addr1']) ? $_POST['email_addr1'] : ""; 
		$email_addr2	= isset($_POST['email_addr2']) ? $_POST['email_addr2'] : ""; 
		$phone_number 	= isset($_POST['phone_number']) ? $_POST['phone_number'] : "";
		$mobile 		= isset($_POST['mobile']) ? $_POST['mobile'] : "";
		$fax 			= isset($_POST['fax']) ? $_POST['fax'] : "";

		// Generate PWD for new
		$password = "";
		$refId = generateReferenceId();

		// check username and email is available
	    $stmt = $db->prepare("SELECT UserId FROM users WHERE `Username`=?");
	    $stmt->bind_param('s', $username);
	    $stmt->execute();
	    $stmt->bind_result($userId);
	    $stmt->store_result();
	    if ($stmt->num_rows > 0) {
	    	$res["status"] = "error";
	    	$res["message"] = _lang("username_exists");

	    	$stmt->close();
	    	echo json_encode( $res );
			exit;
	    }

	    // Save User 
		$stmt = $db->prepare(
            'INSERT INTO users (
                Username,
                Firstname,
                Lastname,
                Password,
                Email,
                UserType,
                Active
            ) VALUES (?,?,?,?,?,?,?)');
        $stmt->bind_param( 'sssssss',
            $username,
			$first_name,
			$last_name,
			$password, 
			$email_addr,
			$user_type,
			$active );
        $stmt->execute() or die($stmt->error);
        $insertId = $stmt->insert_id;

        // save to System Table
		$regDate = date("Y-m-d");

        $stmt = $db->prepare(
            'INSERT INTO systems (
                UserId,
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
                RegDate
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');

        $stmt->bind_param( 'iisssssssssssssddssssss',
            $insertId,
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
			$regDate);
        $stmt->execute() or die($stmt->error);
        $stmt->close();

    	$res["status"] = "success";
    	$res["message"] = _lang("success_register");

		echo json_encode( $res );
		exit;
	}

	// Edit User
	if( $_POST['action'] == "edit_user" ) {

		// GET POST data
		$userId 		= isset($_POST['userId']) ? $_POST['userId'] : "";
		$username 		= isset($_POST['username']) ? $_POST['username'] : "";
		$first_name 	= isset($_POST['first_name']) ? $_POST['first_name'] : "";
		$last_name 		= isset($_POST['last_name']) ? $_POST['last_name'] : "";
		$fullname 		= isset($_POST['fullname']) ? $_POST['fullname'] : "";
		$businessname 	= isset($_POST['businessname']) ? $_POST['businessname'] : "";
		$locationId 	= isset($_POST['location']) ? $_POST['location'] : 1;
		$email_addr 	= isset($_POST['email_addr']) ? $_POST['email_addr'] : "";
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

		$email_addr1	= isset($_POST['email_addr1']) ? $_POST['email_addr1'] : ""; 
		$email_addr2	= isset($_POST['email_addr2']) ? $_POST['email_addr2'] : ""; 
		$phone_number 	= isset($_POST['phone_number']) ? $_POST['phone_number'] : "";
		$mobile 		= isset($_POST['mobile']) ? $_POST['mobile'] : "";
		$fax 			= isset($_POST['fax']) ? $_POST['fax'] : "";

		// Avoid Admin Edit
		if( $userId == 1 ) {
			$res["status"] = "error";
	    	$res["message"] = _lang("impossible_admin_edit");

	    	echo json_encode( $res );
			exit;
		}

		// check username and email is available
	    $stmt = $db->prepare("SELECT UserId FROM users WHERE (`Username`=? OR `Email`=?) AND `UserID`!=$userId");
	    $stmt->bind_param('ss', $username, $email_addr);
	    $stmt->execute();
	    $stmt->bind_result($user_id);
	    $stmt->store_result();
	    if ($stmt->num_rows > 0) {
	    	$res["status"] = "error";
	    	$res["message"] = _lang("username_exists");

	    	$stmt->close();
	    	echo json_encode( $res );
			exit;
	    }

	    // Save User 
		$stmt = $db->prepare(
			'UPDATE users
                SET Username = ?,
                    Firstname = ?,
                    Lastname = ?,
                    Email = ?,
                    UserType = ?,
                    Active = ?
                WHERE UserId=?' );

        $stmt->bind_param( 'ssssssi',
			$username,
			$first_name,
			$last_name,
			$email_addr,
			$user_type,
			$active,
			$userId
        );
        $stmt->execute() or die($stmt->error);
        $stmt->close();

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
                Fax = ?
			WHERE UserId=?' );

        $stmt->bind_param( 'isssssssssssssddsssssi',
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
			$userId
        );

        $stmt->execute() or die($stmt->error);
        $stmt->close();

    	$res["status"] = "success";
    	$res["message"] = _lang("success_update");

		echo json_encode( $res );
		exit;
	}

	// Delete User
	if( $_POST['action'] == "delete_user" ) {
		// Avoid Admin Edit
		$deleteId = isset($_POST['deleteId']) ? $_POST['deleteId'] : "";

		if( $deleteId == 1 ) {
			$res["status"] = "error";
	    	$res["message"] = _lang("impossible_admin_delete");

	    	echo json_encode( $res );
			exit;
		}

    	// Delete User
		$stmt = $db->prepare( 'DELETE FROM users WHERE UserId=?' );
        $stmt->bind_param( 'i', $deleteId);
        $stmt->execute() or die($stmt->error);
        $stmt->close();

		$stmt = $db->prepare( 'DELETE FROM systems WHERE UserId=?' );
        $stmt->bind_param( 'i', $deleteId);
        $stmt->execute() or die($stmt->error);
        $stmt->close();

		$res["status"] = "success";
    	$res["message"] = _lang("success_delete");
		echo json_encode( $res );
		exit;
	}

	// Change Password
	if( $_POST['action'] == "change_pass" ) {
		$passId = isset($_POST['passId']) ? $_POST['passId'] : "";
		$password = isset($_POST['password']) ? $_POST['password'] : "";

		// Change Password
		$stmt = $db->prepare( 'UPDATE users SET `Password`=MD5(?) WHERE UserId=?' );
        $stmt->bind_param( 'si', $password, $passId );
        $stmt->execute() or die($stmt->error);
        $stmt->close();

    	$res["status"] = "success";
    	$res["message"] = _lang("success_update");

		echo json_encode( $res );
		exit;
	}

	if( $_POST['action'] == "get_user" ) {
		$userId = isset($_POST['userId']) ? $_POST['userId'] : "";
		$stmt = $db->prepare("SELECT U.UserId, U.Username, U.Firstname, U.Lastname, U.Email, U.UserType, U.Active, S.LocationId, S.FullName, S.ReferenceId, S.BusinessName, S.Street, S.City, S.State, S.PostCode, S.Country, S.Timezone, S.PStreet, S.PCity, S.PState, S.PPostCode, S.Latitude, S.Longitude, S.SecondEmail, S.ThirdEmail, S.Phone, S.Mobile, S.Fax, S.RegDate
			FROM users as U
			LEFT JOIN systems as S on U.UserId = S.UserId
			WHERE U.UserId=?");
        $stmt->bind_param( 'i', $userId);
		$stmt->execute();
		$stmt->bind_result($userId, $username, $firstname, $lastname, $email, $usertype, $active, $locationId, $fullname, $referenceId, $businessname, $lstreet, $lcity, $lstate, $lzipcode, $lcountry, $timezone, $pstreet, $pcity, $pstate, $pzipcode, $latitude, $longitude, $email_addr1, $email_addr2, $phone_number, $mobile, $fax, $regdate );
		$stmt->store_result();
		$stmt->fetch();
		$res["status"] = "success";
		$res['data'] = [
			"UserId" 	=> $userId,
			"Username" 	=> $username,
			"Firstname" => $firstname,
			"Lastname" 	=> $lastname,
			"Email" 	=> $email,
			"UserType" 	=> $usertype,
			"Active"	=> $active,
			"LocationId" => $locationId,
			"FullName" => $fullname,
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
			"SecondEmail" => $email_addr1,
			"ThirdEmail" => $email_addr2,
			"Phone" => $phone_number,
			"Mobile" => $mobile,
			"Fax" => $fax,
			"RegDate" => $regdate
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