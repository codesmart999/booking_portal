<?php
	include("../config.php");
	require_once('../lib.php');

	$res = [
		"status" 	=> "success",
		"message"	=> ""
	];

    $db = getDBConnection();
    if( $_POST['action'] == "get_irregular" ) {
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