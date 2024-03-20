<?php
	function __debug( $var ) {
		echo "<pre>";
		print_r( $var );
		echo "</pre>";
	}

	function getDBConnection() {
        //Connect to Database Link
        $link = new mysqli('localhost',
            DB_USER,
            DB_PASS,
            DB_NAME
        ) or die('There was a problem connecting to the database.');

        if (!$link) exit("Error: Couldn't connect to MySQL server.");

        return $link;
    }

	// manage date format for all places
	function format_date( $date ) {
		$date = str_replace('/', '-', $date);
		return date("l, d F Y", strtotime($date));
	}

	// display format Price
	function displayPrice( $price ) {
		return "AUD $".number_format($price, 2, '.', '');
	}

	// display yes/no 
	function displayYN( $val ) {
		if( $val > 0 || $val == "Y" )
			return "Yes";
		else
			return "No";
	}

	// get location name from location Id
	function getLocationName( $locationId) {
		global $arrLocations;

		foreach( $arrLocations as $name => $arrInfo ) {
			if( $arrInfo['id'] == $locationId )
				return $name;
		}

		return "";
	}

    // Check Availability by Selected Date
    function checkAvailability( ) {
    	global $arrAppData;

    	$selectedDate = str_replace('/', '-', $arrAppData['date_appointment']);
    	$selectedDate = date("Y-m-d", strtotime($selectedDate));

    	// if selected date is passed, then return FALSE
		if( strtotime( $selectedDate ) < strtotime(date("Y-m-d")) )
			return false;

    	if( $arrAppData['five_days'] ) {
    		for( $i = 0; $i < 5; $i++ ) {
		    	$arrTimes = getAvailableTimes( $selectedDate );

				if( !empty( $arrTimes ))
		    		return true;

    			$selectedDate = date('Y-m-d', strtotime('+1 day', strtotime($selectedDate)));
    		}
    	} else {
    		$arrTimes = getAvailableTimes( $selectedDate );
    		if( !empty( $arrTimes )) return true;
    	}

    	return false;
    }

    // get Available times by Selected Date 
    // it needs to check by Date, System ( from Location ) and Service
    function getAvailableTimes( $date ) {
    	global $arrAppData, $arrLocations;

    	$day = date("D", strtotime($date));

    	$link = getDBConnection();
    	$arrRes = array(); $arrSystems = array();

    	$locationId = $arrLocations[$arrAppData['location']]['id'];
    	
    	// Get System list by Location
		$stmt = $link->prepare("SELECT SystemId, UserId, Access FROM `systems` WHERE `LocationId`=$locationId");
	    $stmt->execute();
	    $stmt->bind_result($systemId, $userId, $access);
	    while($stmt->fetch()) {
	        $arrSystems[$systemId] = array(
	        	"systemId" 	=> $systemId,
	        	"userId"	=> $userId,
	        	"access"	=> $access
	        );
	    }

    	// check by Default Time Setting
    	// Regular( available ) Booking Period
		$stmt = $link->prepare("SELECT value FROM `settings` WHERE `name`='DEFAULT_REGULAR_TIME'");
	    $stmt->execute();
	    $stmt->bind_result($regular_time);
	    while($stmt->fetch())
	    	$regular_time = json_decode($regular_time);

	    // get Irregular(Unavailable) Booking Period
	    $stmt = $link->prepare("SELECT value FROM `settings` WHERE `name`='DEFAULT_IRREGULAR_TIME'");
	    $stmt->execute();
	    $stmt->bind_result($irregular_time);
	    while($stmt->fetch())
	    	$irregular_time = json_decode($irregular_time);

    	// get Special Availability by System
    	foreach( $arrSystems as $system ){
			$stmt = $link->prepare("SELECT RuleId, Available FROM `availability` WHERE `SystemId`=$system AND `SetDate`=$date");
		    $stmt->execute();
		    $stmt->bind_result($availablility);
		    while($stmt->fetch())
		    	$arrRules[] = json_decode($availablility);
    	}

        $stmt->close();

	    $link->close();

    	return $arrRes;
    }

    // get TimeSheet Array By start/end/period
    function getTimeSheetsArray( $start, $end, $period ) {
    	$arrTimeSheet = array();

    	$startStamp = strtotime($start);
    	$endStamp = strtotime($end);
    	$diff = $period * 60;

    	for( $i = $startStamp; $i < $endStamp; $i = $i + $diff ) {
    		$startText 	= date('H:i A', $i);
    		$endText 	= date('H:i A', $i + $diff);
    		$label 		= $startText . " To " . $endText;
    		$arrTimeSheet[] = array(
    			"start" => $startText,
    			"end"	=> $endText,
    			"value"	=> $i,
    			"label"	=> $label
    		);
    	}

    	return $arrTimeSheet;
    }

    function getDefaultTimeSetting ( $key ) {
    	$arrDefault = array( 
    		"start" 	=> '08:00',
    		"end"		=> '18:00',
    		"period"	=> 15
    	);

    	return $arrDefault[$key];
    } 
?>