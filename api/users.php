<?php
	include("../config.php");
	require_once('../lib.php');

	$res = [
		"status" 	=> "success",
		"message"	=> ""
	];

    $db = getDBConnection();

	// New User
	if( $_POST['action'] == "new_user" ) {
		// GET POST data
		$username = isset($_POST['username']) ? $_POST['username'] : "";
		$first_name = isset($_POST['first_name']) ? $_POST['first_name'] : "";
		$last_name = isset($_POST['last_name']) ? $_POST['last_name'] : "";
		$email_addr = isset($_POST['email_addr']) ? $_POST['email_addr'] : "";
		$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : "";
		$active = isset($_POST['active']) ? "Y" : "N";

		// Generate PWD for new
		$password = "";

		// check username and email is available
	    $stmt = $db->prepare("SELECT UserId FROM users WHERE `Username`=? OR `Email`=?");
	    $stmt->bind_param('ss', $username, $email_addr);
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

        // save to Sub-Admin Table
   		$position = isset($_POST['position']) ? $_POST['position'] : "";
		$street = isset($_POST['street']) ? $_POST['street'] : "";
		$city = isset($_POST['city']) ? $_POST['city'] : "";
		$state = isset($_POST['state']) ? $_POST['state'] : "";
		$zipcode = isset($_POST['zipcode']) ? $_POST['zipcode'] : "";
		$phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : "";
		$mobile = isset($_POST['mobile']) ? $_POST['mobile'] : "";
		$fax = isset($_POST['fax']) ? $_POST['fax'] : "";

		$country = isset($_POST['country']) ? $_POST['country'] : "Australia"; 
		$regDate = date("Y-m-d");

        $stmt = $db->prepare(
            'INSERT INTO sub_admins (
                UserId,
                Position,
                StreetAddr,
                City,
                State,
                Zip,
                Country,
                ContactPhone,
                MobilePhone,
                Fax,
                RegDate
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->bind_param( 'issssssssss',
            $insertId,
            $position,
			$street,
			$city,
			$state,
			$zipcode,
			$country,
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
		$userId = isset($_POST['userId']) ? $_POST['userId'] : "";
		$username = isset($_POST['username']) ? $_POST['username'] : "";
		$first_name = isset($_POST['first_name']) ? $_POST['first_name'] : "";
		$last_name = isset($_POST['last_name']) ? $_POST['last_name'] : "";
		$email_addr = isset($_POST['email_addr']) ? $_POST['email_addr'] : "";
		$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : "";
		$active = isset($_POST['active']) ? "Y" : "N";

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

        // Update Sub Admin Table
   		$position = isset($_POST['position']) ? $_POST['position'] : "";
		$street = isset($_POST['street']) ? $_POST['street'] : "";
		$city = isset($_POST['city']) ? $_POST['city'] : "";
		$state = isset($_POST['state']) ? $_POST['state'] : "";
		$zipcode = isset($_POST['zipcode']) ? $_POST['zipcode'] : "";
		$phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : "";
		$mobile = isset($_POST['mobile']) ? $_POST['mobile'] : "";
		$fax = isset($_POST['fax']) ? $_POST['fax'] : "";

		$stmt = $db->prepare(
			'UPDATE sub_admins
                SET Position = ?,
                    StreetAddr = ?,
                    City = ?,
                    State = ?,
                    Zip = ?,
                    ContactPhone = ?,
                    MobilePhone = ?,
                    Fax = ?
                WHERE UserId=?' );

        $stmt->bind_param( 'ssssssssi',
			$position,
			$street,
			$city,
			$state,
			$zipcode,
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

		$stmt = $db->prepare( 'DELETE FROM sub_admins WHERE UserId=?' );
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

	// Get a User Profile by Id
	if( $_POST['action'] == "get_user" ) {
		$userId = isset($_POST['userId']) ? $_POST['userId'] : "";
		$stmt = $db->prepare("SELECT U.UserId, U.Username, U.Firstname, U.Lastname, U.Email, U.UserType, U.Active, A.Position, A.StreetAddr, A.City, A.State, A.Zip, A.Country, A.ContactPhone, A.MobilePhone, A.Fax, A.RegDate
			FROM users as U
			LEFT JOIN sub_admins as A on U.UserId = A.UserId
			WHERE U.UserId=?");
        $stmt->bind_param( 'i', $userId);
		$stmt->execute();
		$stmt->bind_result($userId, $username, $firstname, $lastname, $email, $usertype, $active, $position, $street, $city, $state, $zipcode, $country, $phone, $mobilephone, $fax, $regdate);

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
			"Position"	=> $position,
			"Street"	=> $street,
			"City"		=> $city,
			"State"		=> $state,
			"Zipcode"	=> $zipcode,
			"Country"	=> $country,
			"Phone"		=> $phone,
			"Mobile"	=> $mobilephone,
			"Fax"		=> $fax,
			"Regdate"	=> $regdate
		];
		
		echo json_encode( $res );
		exit;
	}

	if( $_POST['action'] == "list_user" ) {
		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$per_page = isset($_POST['per_page']) ? $_POST['per_page'] : DEFAULT_PAGE_NUM;

	    $stmt = $db->prepare("SELECT * FROM users");
		$stmt->execute();
	    $stmt->store_result();

	    $total_records = $stmt->num_rows;
	    $number_of_page = ceil( $total_records / $per_page );

	    $page_start = ($page - 1) * $per_page;
	    $stmt = $db->prepare("SELECT * FROM users LIMIT ?,?");
        $stmt->bind_param( 'ii', $page_start, $per_page );
		$stmt->execute();
	    $stmt->store_result();

	    while ($stmt->fetch()) {
	    	$res['data'][] = [
				"UserId" => $userId,
				"Username" => $username,
				"Firstname" => $firstname,
				"Lastname" => $lastname,
				"Email" => $email,
				"UserType" => $usertype
	    	];
	    }

	   	echo json_encode( $res );
		exit;
	}

    $db->close();
?>