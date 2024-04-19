<?php
	include("../config.php");
	require_once('../lib.php');

	$res = [
		"status" 	=> "success",
		"message"	=> ""
	];

    $db = getDBConnection();

	// New customer
	if( $_POST['action'] == "new_customer" ) {
		// GET POST data
		$customerId = isset($_POST['customerId']) ? $_POST['customerId'] : "";
		$businessName = isset($_POST['businessName']) ? $_POST['businessName'] : "";
		$email = isset($_POST['email']) ? $_POST['email'] : "";
		$street = isset($_POST['street']) ? $_POST['street'] : "";
		$city = isset($_POST['city']) ? $_POST['city'] : "";
		$state = isset($_POST['state']) ? $_POST['state'] : "";
		$postcode = isset($_POST['postcode']) ? $_POST['postcode'] : "";
		$data = array(
			"street" => $street,
			"city" => $city,
			"state" => $state,
			"postcode" => $postcode
		);
		
		// Convert the array to a JSON string
		$address = json_encode($data);
		$phone = isset($_POST['phone']) ? $_POST['phone'] : "";
		$active = isset($_POST['active']) ? 1 : 0;

		// check customername
	    $stmt = $db->prepare("SELECT CustomerId FROM customers WHERE `FullName`=? ");
	    $stmt->bind_param('s', $businessName);
	    $stmt->execute();
	    $stmt->bind_result($customerId);
	    $stmt->store_result();
	    if ($stmt->num_rows > 0) {
	    	$res["status"] = "error";
	    	$res["message"] = _lang("name_exists");

	    	$stmt->close();
	    	echo json_encode( $res );
			exit;
	    }

	    // Save customer 
		$stmt = $db->prepare(
            'INSERT INTO customers (
                FullName,
                Email,
                PostalAddr,
                Phone,
                active
            ) VALUES (?,?,?,?,?)');
        $stmt->bind_param( 'ssssi',
            $businessName,
			$email,
			$address,
			$phone, 
			$active );
        $stmt->execute() or die($stmt->error);

    	$res["status"] = "success";
    	$res["message"] = _lang("success_register");

		echo json_encode( $res );
		exit;
	}

	// Edit customer
	if( $_POST['action'] == "edit_customer" ) {
		// GET POST data
		$customerId = isset($_POST['customerId']) ? $_POST['customerId'] : "";
		$businessName = isset($_POST['businessName']) ? $_POST['businessName'] : "";
		$email = isset($_POST['email']) ? $_POST['email'] : "";
		$street = isset($_POST['street']) ? $_POST['street'] : "";
		$city = isset($_POST['city']) ? $_POST['city'] : "";
		$state = isset($_POST['state']) ? $_POST['state'] : "";
		$postcode = isset($_POST['postcode']) ? $_POST['postcode'] : "";
		$data = array(
			"street" => $street,
			"city" => $city,
			"state" => $state,
			"postcode" => $postcode
		);
		
		// Convert the array to a JSON string
		$address = json_encode($data);
		$phone = isset($_POST['phone']) ? $_POST['phone'] : "";
		$active = isset($_POST['active']) ? 1 : 0;

		// check customerName
	    $stmt = $db->prepare("SELECT CustomerId FROM customers WHERE `FullName`=? AND `CustomerID`!=$customerId");
	    $stmt->bind_param('s', $businessName);
	    $stmt->execute();
	    $stmt->bind_result($customerId);
	    $stmt->store_result();
	    if ($stmt->num_rows > 0) {
	    	$res["status"] = "error";
	    	$res["message"] = _lang("name_exists");

	    	$stmt->close();
	    	echo json_encode( $res );
			exit;
	    }

	    // Save customer 
		$stmt = $db->prepare(
			'UPDATE customers
                SET FullName = ?,
                    Email = ?,
                    PostalAddr = ?,
                    Phone = ?,
                    active = ?
                WHERE customerId=?' );

        $stmt->bind_param( 'ssssii',
			$businessName,
			$email,
			$address,
			$phone,
			$active,
			$customerId
        );
        $stmt->execute() or die($stmt->error);
        $stmt->close();

    	$res["status"] = "success";
    	$res["message"] = _lang("success_update");

		echo json_encode( $res );
		exit;
	}

	// Delete customer
	if( $_POST['action'] == "delete_customer" ) {
		$deleteId = isset($_POST['deleteId']) ? $_POST['deleteId'] : "";
		$stmt = $db->prepare( 'DELETE FROM customers WHERE customerId=?' );
        $stmt->bind_param( 'i', $deleteId);
        $stmt->execute() or die($stmt->error);
        $stmt->close();

		$res["status"] = "success";
    	$res["message"] = _lang("success_delete");
		echo json_encode( $res );
		exit;
	}

	// Get a customer Info by Id
	if( $_POST['action'] == "get_customer" ) {
		$customerId = isset($_POST['customerId']) ? $_POST['customerId'] : "";
		$stmt = $db->prepare("SELECT customerId, FullName, Email, PostalAddr, Phone, Comment, active
			FROM customers
			WHERE CustomerId=?");
        $stmt->bind_param( 'i', $customerId);
		$stmt->execute();
		$stmt->bind_result($customerId, $businessName, $email, $address, $phone, $comment, $active);
		$stmt->store_result();
		$stmt->fetch();

		$res["status"] = "success";
		$res['data'] = [
			"customerId" 		=> $customerId,
			"businessName" 		=> $businessName,
			"email" 			=> $email,
			"address" 		=> $address,
			"phone" 			=> $phone,
			"comment" 	=> $comment,
			"active"			=> $active
		];
		
		echo json_encode( $res );
		exit;
	}

	// get  All customer Info

	if( $_POST['action'] == "get_all_customers" ) {
		$input_system_id = $_POST['SystemId'];
		
		$arrResult = array();
		$stmt = $db->prepare("
			SELECT 
			s.customerId,
			s.customerName,
			s.FullName,
			s.DurationInMins,
			s.IsCharge,
			s.Price,
			m.SystemId
			FROM 
				customers s
			LEFT JOIN
				system_customers m ON s.customerId = m.customerId AND m.SystemId = ?
			ORDER BY s.customerId ASC;
		");
		$stmt->bind_param('i', $input_system_id);
		
		$stmt->execute();
		$stmt->bind_result($customerId, $customerName, $FullName, $DurationInMins, $IsCharge, $Price, $SystemId);
		$stmt->store_result();

		while($stmt->fetch()) {
			$arrDurationInfo = convertDurationToHoursMinutes($DurationInMins);

			$arrResult[] = array(
				"customerId" 		=> $customerId,
				"customerName" 		=> $customerName,
				"FullName" 			=> $FullName,
				"DurationInMins"	=> $DurationInMins,
				"Formatted_Duration" 	=> $arrDurationInfo['formatted_text'],
				"IsCharge"			=> $IsCharge,
				"Price" 			=> $Price,
				"isSystemcustomer"   => $SystemId == $input_system_id
            );
	    }

		$res["status"] = "success";
		$res['data'] = $arrResult;
		
		echo json_encode( $res );
		exit;
	}



    $db->close();
?>
