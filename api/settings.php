<?php
	include("../config.php");
	require_once('../lib.php');
	require_once('../admin/utils.php');

	$res = [
		"status" 	=> "success",
		"message"	=> ""
	];

    $db = getDBConnection();
	// Added by Hennadii (2024-03-26)
	if ( $_POST['action'] == 'get_bookingperiods_by_weekday') {
		$arr_bookingperiod_list = array();
		$weekday = $_POST['weekday'];

		$stmt = $db->prepare("SELECT id, FromInMinutes, ToInMinutes, isRegular, isAvailable FROM setting_bookingperiods WHERE SystemId = 0 AND weekday = ? ORDER BY FromInMinutes ASC");
		$stmt->bind_param('i', $weekday);
		$stmt->execute();
		$stmt->bind_result($id, $from_in_mins, $to_in_mins, $isRegular, $isAvailable);
		$stmt->store_result();

		while ($stmt->fetch()) {
			if (empty($arr_bookingperiod_list)) {
				$arr_bookingperiod_list = array();
			}
			
			// keep adding to the list
			$arr_bookingperiod_list[] = [
				'id' => $id,
				'FromInMinutes' => $from_in_mins,
				'ToInMinutes' => $to_in_mins,
				'DisplayText' => get_display_text_from_minutes($from_in_mins, $to_in_mins),
				'isRegular' => $isRegular,
				'isAvailable' => $isAvailable,
			];
		}

		$stmt->close();

		$res["status"] = "success";
		$res["data"] = $arr_bookingperiod_list;
		echo json_encode( $res );
		exit;
	}
	
	// Added by Awesome (2024-03-27)
	if ($_POST['action'] == "save_booking_periods_availability") {
		$weekday = $_POST['weekday'];
		$arr_unavailable_bookingperiods = isset($_POST['unavailable_bookingperiod']) ? $_POST['unavailable_bookingperiod'] : array();
		$arr_bookingperiod_ids = array_keys($arr_unavailable_bookingperiods);

		$stmt = $db->prepare( 'UPDATE setting_bookingperiods SET isAvailable = 1 WHERE SystemId = 0 AND weekday = ?' );
		$stmt->bind_param('i', $weekday);
		$stmt->execute() or die($stmt->error);

		if (!empty($arr_bookingperiod_ids)) {
			$str_bookingperiod_ids = implode(',', $arr_bookingperiod_ids);
			$stmt = $db->prepare( 'UPDATE setting_bookingperiods SET isAvailable = 0 WHERE id IN (' . $str_bookingperiod_ids . ')' );
			$stmt->execute() or die($stmt->error);
		}
		
		$stmt->close();

		$res["status"] = "success";
    	$res["message"] = _lang("success_update");
		echo json_encode( $res );
		exit;
	}

	// Added by Hennadii (2024-03-26)
	if ($_POST['action'] == "save_booking_periods") {
		$weekday = $_POST['weekday'];
		$isAvailable = $_POST['isWeekdayAvailable'];

		$arr_bookingperiods = isset($_POST['list_bookingperiods']) ? $_POST['list_bookingperiods'] : array();
		$stmt = $db->prepare( 'DELETE FROM setting_bookingperiods WHERE SystemId = 0 AND weekday = ?' );
		$stmt->bind_param('i', $weekday);
		$stmt->execute() or die($stmt->error);

		$stmt = $db->prepare("INSERT INTO `setting_bookingperiods` (weekday, FromInMinutes, ToInMinutes, isRegular, isAvailable) VALUES (?, ?, ?, ?, ?)");
		foreach ($arr_bookingperiods as $booking_period) {
			$arr_params = explode('-', $booking_period);
			list($from_in_mins, $to_in_mins) = $arr_params;

			$isRegular = 1;
			if (count($arr_params) === 3)
				$isRegular = 0;
			
			$stmt->bind_param('iiiii', $weekday, $from_in_mins, $to_in_mins, $isRegular, $isAvailable);
			$stmt->execute() or die($stmt->error);
		}

		$stmt->close();

		$res["status"] = "success";
    	$res["message"] = _lang("success_update");
		echo json_encode( $res );
		exit;
	}

	// Added by Awesome (2024-03-26)
	if( $_POST['action'] == "get_all_systems" ) {
		$stmt = $db->prepare("SELECT * FROM systems");
	    $stmt->execute();
		$stmt->bind_result($SystemId, $UserId, $LocationId, $FullName, $ReferenceId, $Access, $InternalId, $BusinessName, $Steet, $City, $State,
			$PostCode, $Country, $PStreet, $PCity, $PState, $PPostCode, $Timezone, $Latitue, $Longitude, $SecondEmail, $ThirdEmail, $Phone, $Mobile, $Fax, $Website,
			$LastAccess, $RegDate);
		$stmt->store_result();

	    $arrSystems = array();
		if ($stmt->num_rows > 0) {
			while ($stmt->fetch()) {
				$arrSystems[] = [
					"SystemId"		=> $SystemId,
					"UserId"		=> $UserId,
					"FullName"	    => $FullName,
					"Country"	    => $Country,
					"LocationId"	=> $LocationId
				];
			}
		}
		

		$res["status"] = "success";
		$res['data'] = $arrSystems;
		echo json_encode( $res );
		exit;
	}

	// Not using anymore by Hennadii (2024-03-26)
    if ( $_POST['action'] == "get_irregular" ) {
    	$day = $_POST['irregularDay'];

	    $stmt = $db->prepare("SELECT * FROM settings WHERE `name`='DEFAULT_IRREGULAR_TIME'");
	    $stmt->execute();
   		$stmt->bind_result($id, $name, $value, $category, $description);
   		$stmt->store_result();
	   	$stmt->fetch(); 

	   	$arrValue = json_decode( $value, true );

	   	// Init time sheets
	   	$arrInitSheets = [];
	   	$start 	= strtotime(getDefaultTimeSetting('start'));
		$end 	= strtotime(getDefaultTimeSetting('end'));
		$period = getDefaultTimeSetting('period');

		$startTime = date('H:i A', $start);
		for ($i=$start;$i<$end;$i = $i + $period * 60) {
			$endTime = date('H:i A',$i + $period * 60);
			$arrInitSheets[] = $startTime . " To " . $endTime;			
			$startTime = $endTime;
		}

    	$arrIrTime = isset($arrValue[$day] ) ? $arrValue[$day] : $arrInitSheets;
		$res["status"] = "success";
		$res["data"] = $arrIrTime;
		echo json_encode( $res );
		exit;
    }

	// Not using anymore by Hennadii (2024-03-26)
    if( $_POST['action'] == "save_irregular" ) {
    	$day = $_POST['irregularDay'];
    	$arrSheets = isset($_POST['irregularTimes']) ? $_POST['irregularTimes'] : [];

	    $stmt = $db->prepare("SELECT * FROM settings WHERE `name`='DEFAULT_IRREGULAR_TIME'");
	    $stmt->execute();
   		$stmt->bind_result($id, $name, $value, $category, $description);
   		$stmt->store_result();
	   	$stmt->fetch(); 

	   	$arrValue = json_decode( $value, true );
	   	$arrValue[$day] = $arrSheets;
	   	$newValue = json_encode( $arrValue );

	   	$stmt = $db->prepare( "UPDATE settings SET `value`=? WHERE `name`='DEFAULT_IRREGULAR_TIME'" );
        $stmt->bind_param( 's', $newValue );
        $stmt->execute() or die($stmt->error);
        $stmt->close();

		$res["status"] = "success";
    	$res["message"] = _lang("success_update");
		echo json_encode( $res );
		exit;
    }
    if( $_POST['action'] == "get_available" ) {
    	$day = $_POST['availableDay'];

	    $stmt = $db->prepare("SELECT * FROM settings WHERE `name`='AVAILABLE_BOOKING_PERIOD'");
	    $stmt->execute();
   		$stmt->bind_result($id, $name, $value, $category, $description);
   		$stmt->store_result();
	   	$stmt->fetch(); 

	   	$arrValue = json_decode( $value, true );

	   	// Init time sheets
	   	$arrInitSheets = [];
	   	$start 	= strtotime(getDefaultTimeSetting('start'));
		$end 	= strtotime(getDefaultTimeSetting('end'));
		$period = getDefaultTimeSetting('period');

		$startTime = date('H:i A', $start);
		for ($i=$start;$i<$end;$i = $i + $period * 60) {
			$endTime = date('H:i A',$i + $period * 60);
			$arrInitSheets[] = [
				"from" 		=> $startTime,
				"to"		=> $endTime,
				"status"	=> 'A'
			];
			$startTime = $endTime;
		}

    	$arrIrTime = isset($arrValue[$day] ) ? $arrValue[$day] : $arrInitSheets;
		$res["status"] = "success";
		$res["data"] = $arrIrTime;
		echo json_encode( $res );
		exit;
    }

    if( $_POST['action'] == "save_available" ) {
       	$day = $_POST['availableDay'];
    	$arrFrom = isset($_POST['avaialble_from']) ? $_POST['avaialble_from'] : [];
    	$arrTo = isset($_POST['avaialble_to']) ? $_POST['avaialble_to'] : [];
    	$arrStatus = isset($_POST['avaialble_status']) ? $_POST['avaialble_status'] : [];

	    $stmt = $db->prepare("SELECT * FROM settings WHERE `name`='AVAILABLE_BOOKING_PERIOD'");
	    $stmt->execute();
   		$stmt->bind_result($id, $name, $value, $category, $description);
   		$stmt->store_result();
	   	$stmt->fetch(); 

	   	$arrValue = json_decode( $value, true );
	   	$arrValue[$day] = [];
	   	foreach( $arrFrom as $index => $val ) {
	   		$arrValue[$day][] = [
	   			"from" 	=> $arrFrom[$index],
	   			"to" 	=> $arrTo[$index],
	   			"status" 	=> $arrStatus[$index],
	   		];
	   	}

	   	$newValue = json_encode( $arrValue );

	   	$stmt = $db->prepare( "UPDATE settings SET `value`=? WHERE `name`='AVAILABLE_BOOKING_PERIOD'" );
        $stmt->bind_param( 's', $newValue );
        $stmt->execute() or die($stmt->error);
        $stmt->close();

		$res["status"] = "success";
    	$res["message"] = _lang("success_update");
		echo json_encode( $res );
		exit;
    }
?>