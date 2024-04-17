<?php
	include("../config.php");
	require_once('../lib.php');

	$res = [
		"status" 	=> "success",
		"message"	=> ""
	];

    $db = getDBConnection();

	// New Service
	if( $_POST['action'] == "new_service" ) {
		// GET POST data
		$servicename = isset($_POST['servicename']) ? $_POST['servicename'] : "";
		$fullname = isset($_POST['fullname']) ? $_POST['fullname'] : "";
		$description = isset($_POST['description']) ? $_POST['description'] : "";
		$price = isset($_POST['price']) ? $_POST['price'] : 0;
		$duration_hours = isset($_POST['duration_hours']) ? $_POST['duration_hours'] : 0;
		$duration_minutes = isset($_POST['duration_minutes']) ? $_POST['duration_minutes'] : 0;
		$duration_in_mins = $duration_hours * 60 + $duration_minutes;
		$charge = isset($_POST['charge']) ? "Y" : "N";
		$active = isset($_POST['active']) ? 1 : 0;

		// check servicename
	    $stmt = $db->prepare("SELECT ServiceId FROM services WHERE `ServiceName`=? ");
	    $stmt->bind_param('s', $servicename);
	    $stmt->execute();
	    $stmt->bind_result($serviceId);
	    $stmt->store_result();
	    if ($stmt->num_rows > 0) {
	    	$res["status"] = "error";
	    	$res["message"] = _lang("name_exists");

	    	$stmt->close();
	    	echo json_encode( $res );
			exit;
	    }

	    // Save Service 
		$stmt = $db->prepare(
            'INSERT INTO services (
                ServiceName,
                FullName,
                Description,
                Price,
                DurationInMins,
                IsCharge,
                active
            ) VALUES (?,?,?,?,?,?,?)');
        $stmt->bind_param( 'sssdiss',
            $servicename,
			$fullname,
			$description,
			$price, 
			$duration_in_mins,
			$charge,
			$active );
        $stmt->execute() or die($stmt->error);

    	$res["status"] = "success";
    	$res["message"] = _lang("success_register");

		echo json_encode( $res );
		exit;
	}

	// Edit Service
	if( $_POST['action'] == "edit_service" ) {
		// GET POST data
		$serviceId = isset($_POST['serviceId']) ? $_POST['serviceId'] : "";
		$servicename = isset($_POST['servicename']) ? $_POST['servicename'] : "";
		$fullname = isset($_POST['fullname']) ? $_POST['fullname'] : "";
		$description = isset($_POST['description']) ? $_POST['description'] : "";
		$price = isset($_POST['price']) ? $_POST['price'] : 0;
		$duration_hours = isset($_POST['duration_hours']) ? $_POST['duration_hours'] : 0;
		$duration_minutes = isset($_POST['duration_minutes']) ? $_POST['duration_minutes'] : 0;
		$duration_in_mins = $duration_hours * 60 + $duration_minutes;
		$charge = isset($_POST['charge']) ? "Y" : "N";
		$active = isset($_POST['active']) ? 1 : 0;

		// check ServiceName
	    $stmt = $db->prepare("SELECT ServiceId FROM services WHERE `ServiceName`=? AND `ServiceID`!=$serviceId");
	    $stmt->bind_param('s', $servicename);
	    $stmt->execute();
	    $stmt->bind_result($serviceId);
	    $stmt->store_result();
	    if ($stmt->num_rows > 0) {
	    	$res["status"] = "error";
	    	$res["message"] = _lang("name_exists");

	    	$stmt->close();
	    	echo json_encode( $res );
			exit;
	    }

	    // Save Service 
		$stmt = $db->prepare(
			'UPDATE services
                SET ServiceName = ?,
                    FullName = ?,
                    Description = ?,
                    Price = ?,
                    DurationInMins = ?,
                    IsCharge = ?,
                    active = ?
                WHERE ServiceId=?' );

        $stmt->bind_param( 'sssdissi',
			$servicename,
			$fullname,
			$description,
			$price,
			$duration_in_mins,
			$charge,
			$active,
			$serviceId
        );
        $stmt->execute() or die($stmt->error);
        $stmt->close();

    	$res["status"] = "success";
    	$res["message"] = _lang("success_update");

		echo json_encode( $res );
		exit;
	}

	// Delete Service
	if( $_POST['action'] == "delete_service" ) {
		$deleteId = isset($_POST['deleteId']) ? $_POST['deleteId'] : "";
		$stmt = $db->prepare( 'DELETE FROM services WHERE ServiceId=?' );
        $stmt->bind_param( 'i', $deleteId);
        $stmt->execute() or die($stmt->error);
        $stmt->close();

		$res["status"] = "success";
    	$res["message"] = _lang("success_delete");
		echo json_encode( $res );
		exit;
	}

	// Get a Service Info by Id
	if( $_POST['action'] == "get_service" ) {
		$serviceId = isset($_POST['serviceId']) ? $_POST['serviceId'] : "";
		$stmt = $db->prepare("SELECT ServiceId, ServiceName, FullName, Description, Price, DurationInMins, IsCharge, active
			FROM services
			WHERE ServiceId=?");
        $stmt->bind_param( 'i', $serviceId);
		$stmt->execute();
		$stmt->bind_result($serviceId, $servicename, $fullname, $description, $price, $duration_in_mins, $charge, $active);
		$stmt->store_result();
		$stmt->fetch();

		$arrDurationInfo = convertDurationToHoursMinutes($duration_in_mins);

		$res["status"] = "success";
		$res['data'] = [
			"ServiceId" 		=> $serviceId,
			"ServiceName" 		=> $servicename,
			"FullName" 			=> $fullname,
			"Description" 		=> $description,
			"Price" 			=> $price,
			"Duration_Hours" 	=> $arrDurationInfo['hours'],
			"Duration_Minutes"	=> $arrDurationInfo['minutes'],
			"IsCharge"			=> $charge,
			"Active"			=> $active
		];
		
		echo json_encode( $res );
		exit;
	}

	// get  All Service Info

	if( $_POST['action'] == "get_all_services" ) {
		$input_system_id = $_POST['SystemId'];
		
		$arrResult = array();
		$stmt = $db->prepare("
			SELECT 
			s.ServiceId,
			s.ServiceName,
			s.FullName,
			s.DurationInMins,
			s.IsCharge,
			s.Price,
			m.SystemId
			FROM 
				services s
			LEFT JOIN
				system_services m ON s.ServiceId = m.ServiceId AND m.SystemId = ?
			ORDER BY s.ServiceId ASC;
		");
		$stmt->bind_param('i', $input_system_id);
		
		$stmt->execute();
		$stmt->bind_result($ServiceId, $ServiceName, $FullName, $DurationInMins, $IsCharge, $Price, $SystemId);
		$stmt->store_result();

		while($stmt->fetch()) {
			$arrDurationInfo = convertDurationToHoursMinutes($DurationInMins);

			$arrResult[] = array(
				"ServiceId" 		=> $ServiceId,
				"ServiceName" 		=> $ServiceName,
				"FullName" 			=> $FullName,
				"DurationInMins"	=> $DurationInMins,
				"Formatted_Duration" 	=> $arrDurationInfo['formatted_text'],
				"IsCharge"			=> $IsCharge,
				"Price" 			=> $Price,
				"isSystemService"   => $SystemId == $input_system_id
            );
	    }

		$res["status"] = "success";
		$res['data'] = $arrResult;
		
		echo json_encode( $res );
		exit;
	}



    $db->close();
?>
