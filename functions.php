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
			$systemId = $system["systemId"];
			$stmt = $link->prepare("SELECT RuleId, Available FROM `availability` WHERE `SystemId`= $systemId AND `SetDate`=$date");
		    $stmt->execute();
		    $stmt->bind_result($ruleId, $availablility);
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

	//ramdom code generator for booking_code
	function generateRandomCode($seed) {
		// Set the seed for the random number generator
		mt_srand(crc32($seed));

		// Define characters to be used in the code
		$characters = 'ABCDEFGHIJKLMNPQRSTUVWXYZ0123456789';

		// Get the length of the character string
		$char_length = strlen($characters);

		// Initialize an empty string to store the code
		$code = '';

		// Generate 8 random characters
		for ($i = 0; $i < 8; $i++) {
			// Generate a random index within the range of character string length
			$index = mt_rand(0, $char_length - 1);

			// Append the randomly selected character to the code
			$code .= $characters[$index];
		}

		// Return the generated code
		return $code;
	}

	//extract start and end time from input
	function extractStartAndEndTime($input) {
		// Split the string by comma to separate the time ranges
		$time_ranges = explode(',', $input);

		// Extract start and end times from the first and last time ranges respectively
		$start_time = trim(explode(' to ', $time_ranges[0])[0]);
		$end_time = trim(explode(' to ', $time_ranges[count($time_ranges) - 1])[1]);

		// Return start and end times as an associative array
		return array(
			"start_time" => $start_time,
			"end_time" => $end_time
		);
	}

	function cal_weeks_in_month($year, $month){
		// Get the number of days in the current month
		if (!isset($year))
		    $year = date('Y');
		if (!isset($month))
		    $month = date('m');
		$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

		// Get the first day of the month
		$firstDayOfMonth = strtotime($year . '-' . $month . '-01');
		// Determine the day of the week for the first day of the month
		$dayOfWeek = date('N', $firstDayOfMonth);

		// Calculate the date of the first Monday of the month
		$firstMonday = $firstDayOfMonth - (($dayOfWeek - 1) * 24 * 60 * 60);
		$weeks = array();

		// Calculate the start and end date for each week
		for ($i = $firstMonday; $i < $firstDayOfMonth + ($daysInMonth * 24 * 60 * 60); $i += (7 * 24 * 60 * 60)) {
			$weekStart = date('Y-m-d', $i);
			$weekEnd = date('Y-m-d', $i + (6 * 24 * 60 * 60));
			
			// Add to list of weeks
			$weeks[] = array('start' => $weekStart, 'end' => $weekEnd);
		}

		if (count($weeks) > 5) {//ignore when 1st day of month is sunday
        	array_shift($weeks); // Remove the first element
    	}
		return $weeks;
	}

?>