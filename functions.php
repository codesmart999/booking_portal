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
		return date("D, d F Y", strtotime($date));
		// return date("l, d F Y", strtotime($date));
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
	function getLocationNameById( $locationId) {
		global $arrLocations;

		if (empty($arrLocations[$locationId])) {
			return "";
		}
		
		return $arrLocations[$locationId]['name'];
	}

	function getLocationAddressById( $locationId) {
		global $arrLocations;

		if (empty($arrLocations[$locationId])) {
			return "";
		}
		
		return $arrLocations[$locationId]['address'];
	}

    // Check Availability by Selected Date
    function checkAvailability( ) {
    	global $arrAppData;

    	$selectedDate = str_replace('/', '-', $arrAppData['date_appointment']);
    	$selectedDate = date("Y-m-d", strtotime($selectedDate));

    	// if selected date is passed, then return FALSE
		if( strtotime( $selectedDate ) < strtotime(date("Y-m-d")) ) {
			return false;
		}
    	
		$result = getAvailableSystems( $selectedDate );

		unset($_SESSION['arrAvailableSystems']);
		unset($_SESSION['arrSystemBookingPeriodsByDaysDiff']);
		unset($_SESSION['appointment_data']['booking_time']);

		if (empty($result))
			return false;

		$_SESSION['arrAvailableSystems'] = $result['arrSystems'];
		$_SESSION['arrSystemBookingPeriodsByDaysDiff'] = $result['arrBookingPeriodsByDaysDiff'];

		return true;
    }

    // get Available times by Selected Date 
    // it needs to check by Date, System ( from Location ) and Service
    function getAvailableSystems( $date ) {
    	global $arrAppData;

    	$day = date("D", strtotime($date));

    	$link = getDBConnection();
		
		$arrSystems = array(); //RESULT
		$arrBookingPeriodsByDaysDiff = array(); //RESULT

    	$locationId = $arrAppData['location'];
		$serviceId = $arrAppData['service'];
		$bLookInFiveDays = !empty($arrAppData['five_days']);

		// Get System list by LocationId && ServiceId
		$stmt = $link->prepare('SELECT sys.SystemId, sys.UserId, sys.Access, sys.FullName FROM systems sys'
			. ' JOIN system_services serv ON sys.SystemId = serv.SystemId'
			. "	WHERE sys.LocationId = $locationId AND serv.ServiceId = $serviceId");
	    $stmt->execute();
	    $stmt->bind_result($systemId, $userId, $access, $fullname);
	    while ($stmt->fetch()) {
	        $arrSystems[$systemId] = array(
	        	"fullname" 	=> $fullname,
	        	"userId"	=> $userId,
	        	"access"	=> $access
	        );
	    }

		if (empty($arrSystems))
			return null;

		$arrSystemIds = array_keys($arrSystems);
		$arrSystemIds[] = 0; // Add Default System Id
		$strSystemIds = implode(',', $arrSystemIds);
		
		// Get Available Booking Periods
		$strQuery = 'SELECT 
						sbp.weekday,
						sbp.SystemId AS SystemId,
						sbp.FromInMinutes AS FromInMinutes,
						sbp.ToInMinutes AS ToInMinutes,
						CASE
							WHEN sbp.weekday - DAYOFWEEK(?) + 1 < 0 THEN sbp.weekday - DAYOFWEEK(?) + 1 + 7
							ELSE sbp.weekday - DAYOFWEEK(?) + 1
						END AS days_diff
					FROM 
						setting_bookingperiods sbp
					JOIN 
						setting_weekdays sw ON sbp.weekday = sw.weekday AND sbp.SystemId = sw.SystemId
					WHERE 
						sbp.SystemId IN (' . $strSystemIds . ')
						AND sw.isAvailable = 1
						AND sbp.isAvailable = 1' .
					($bLookInFiveDays ? '
						AND sw.weekday IN (
							DAYOFWEEK(?) - 1, -- Current day
							DAYOFWEEK(?) % 7, -- Next day
							(DAYOFWEEK(?) + 1) % 7, -- Next next day
							(DAYOFWEEK(?) + 2) % 7, -- Next next next day
							(DAYOFWEEK(?) + 3) % 7 -- Next next next next day
						)
					' : '
						AND sw.weekday = DAYOFWEEK(?) - 1
					');
		$stmt = $link->prepare($strQuery);
		if ($bLookInFiveDays) {
			$stmt->bind_param('ssssssss', $date, $date, $date, $date, $date, $date, $date, $date);
		} else {
			$stmt->bind_param('ssss', $date, $date, $date, $date);
		}
		
	    $stmt->execute();
	    $stmt->bind_result($weekday, $SystemId, $FromInMinutes, $ToInMinutes, $days_diff);
	    
		while($stmt->fetch()) {
			if (!isset($arrBookingPeriodsByDaysDiff[$SystemId])) {
				$arrBookingPeriodsByDaysDiff[$SystemId] = array();
			}
			if (!isset($arrBookingPeriodsByDaysDiff[$SystemId][$days_diff])) {
				$arrBookingPeriodsByDaysDiff[$SystemId][$days_diff] = array();
			}

			$arrBookingPeriodsByDaysDiff[$SystemId][$days_diff][] = array(
				'weekday' => $weekday,
				'SystemId' => $SystemId,
				'FromInMinutes' => $FromInMinutes,
				'ToInMinutes' => $ToInMinutes,
			);
		}

        $stmt->close();

	    $link->close();

		foreach ($arrBookingPeriodsByDaysDiff as $values) {
			ksort($values); //Sorty by key (i.e. days_diff)
		}

    	return compact('arrSystems', 'arrBookingPeriodsByDaysDiff');
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
	function extractStartAndEndTime($booking_periods) {
		$first_slot = $booking_periods[0];
		$end_slot = end($booking_periods);
		
		$from_in_mins = explode('-', $first_slot)[0];
		$to_in_mins = explode('-', $end_slot)[1];

		return array($from_in_mins, $to_in_mins);
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

		
	function formatDateRange($startDate, $endDate, $showFlag) {
		if ($showFlag == MONTHLY_SHOWING_MODE){
			return date('F, Y', strtotime($startDate));
		}

		$start = date('l, F j, Y', strtotime($startDate));
		$end = date('l, F j, Y', strtotime($endDate));

		if ($startDate === $endDate) {
			return $start;
		} else {
			return $start . ' to ' . $end;
		}
	}

	// Custom sorting function by Hennadii
	// Used to sort the date range. e.g. array( '1080-1100', '660-675')
	function sortRanges($a, $b) {
		// Extract starting values from each range
		$startA = explode('-', $a)[0];
		$startB = explode('-', $b)[0];
		// Convert starting values to integers for comparison
		$startA = intval($startA);
		$startB = intval($startB);

		// Compare starting values
		if ($startA == $startB) {
			return 0;
		}
		return ($startA < $startB) ? -1 : 1;
	}

	function getNextDayFormatted($currentDate) {
		// Get the timestamp for the next day
		$nextDayTimestamp = strtotime('+1 day', $currentDate);

		// Extract day, month, and year from the next day's timestamp
		$dayOfWeek = date('l', $nextDayTimestamp);
		$monthName = date('F', $nextDayTimestamp);
		$dayOfMonth = date('j', $nextDayTimestamp);
		$year = date('Y', $nextDayTimestamp);

		// Return the formatted next day string
		return $dayOfWeek . ", " . $monthName . " " . $dayOfMonth . ", " . $year;
	}

	function getWeekDates($inputDate) {
		// Convert input date to timestamp
		$inputTimestamp = strtotime($inputDate);

		// Get the day of the week (0 for Sunday, 1 for Monday, ..., 6 for Saturday)
		$dayOfWeek = date('w', $inputTimestamp);

		// Calculate the start of the current week (Monday)
		$startOfWeekTimestamp = strtotime('last Monday', $inputTimestamp);

		// Calculate the end of the current week (Sunday)
		$endOfWeekTimestamp = strtotime('this Sunday', $inputTimestamp);

		// Calculate the start of the previous week (Monday)
		$startOfPrevWeekTimestamp = strtotime('-1 week', $startOfWeekTimestamp);

		// Calculate the end of the previous week (Sunday)
		$endOfPrevWeekTimestamp = strtotime('-1 day', $startOfWeekTimestamp);

		// Calculate the start of the next week (Monday)
		$startOfNextWeekTimestamp = strtotime('+1 day', $endOfWeekTimestamp);

		// Calculate the end of the next week (Sunday)
		$endOfNextWeekTimestamp = strtotime('+1 week', $endOfWeekTimestamp);

		// Format the dates
		$startOfWeek = date('Y-m-d', $startOfWeekTimestamp);
		$endOfWeek = date('Y-m-d', $endOfWeekTimestamp);
		$startOfPrevWeek = date('Y-m-d', $startOfPrevWeekTimestamp);
		$endOfPrevWeek = date('Y-m-d', $endOfPrevWeekTimestamp);
		$startOfNextWeek = date('Y-m-d', $startOfNextWeekTimestamp);
		$endOfNextWeek = date('Y-m-d', $endOfNextWeekTimestamp);

		// Return the date ranges
		return array(
			'prevWeek' => array(
				'start' => $startOfPrevWeek,
				'end' => $endOfPrevWeek
			),
			'nextWeek' => array(
				'start' => $startOfNextWeek,
				'end' => $endOfNextWeek
			)
		);
	}
	
	//get available/unvavailable time periods from setting_bookingperiods DB
	function getTimePeriodsByWeek($systemId){

		$db = getDBConnection();
		// Prepare and execute a query to retrieve available time slots for the specified systemId
		$sql = "SELECT weekday, FromInMinutes, ToInMinutes, isAvailable
				FROM setting_bookingperiods
				WHERE SystemId = IFNULL(
							(SELECT SystemId 
							FROM setting_bookingperiods 
							WHERE SystemId = ?
							LIMIT 1),
							0)";

		$stmt = $db->prepare($sql);
		$stmt->bind_param("i", $systemId);
		$stmt->execute();

		// Bind the result variables
		$stmt->bind_result($weekday, $fromMinutes, $toMinutes, $isAvailable);

		// Initialize an array to store the available time slots
		$result = [];
		// Fetch the booking periods

		$isAvailableCountByWeekday = []; //temp variable to check $weekday is new.
		while ($stmt->fetch()) {
			// Increment the corresponding counter based on isAvailable value and weekday
			if (!isset($isAvailableCountByWeekday[$weekday])) {
				$result[$weekday] = [
					0 => 0,
					1 => 0
				];
				$isAvailableCountByWeekday[$weekday] = 1; //temp
			}
			
			// Store the available time slot information
			$result[$weekday]["timeslot"][] = [
				'FromInMinutes' => $fromMinutes,
				'ToInMinutes' => $toMinutes,
				'isAvailable' => $isAvailable
			];
			$result[$weekday][$isAvailable]+=1;
		}
		return $result;
	}

	//
	function getWeeklyTimePeriodsByDateRange($systemId, $startDate, $endDate){
		$db = getDBConnection();
		// Prepare and execute a query to retrieve available time slots for the specified systemId
		$sql = "SELECT
				Week_TB.weekday, 
				Week_TB.FromInMinutes, 
				Week_TB.ToInMinutes, 
				COALESCE(SP_TB.isAvailable, Week_TB.isAvailable) AS isAvailable
			FROM
				(
					SELECT
						setting_bookingperiods.*
					FROM
						setting_bookingperiods
				) AS Week_TB
				LEFT JOIN
				(
					SELECT
						id,FromInMinutes, ToInMinutes, isAvailable,
						MOD(DAYOFWEEK(SetDate) + 6, 7) AS weekday_calc
					FROM
						setting_bookingperiods_special
					WHERE
						systemId = ? AND
						DATE(SetDate) BETWEEN ? AND ?
				) AS SP_TB
				ON 
					Week_TB.FromInMinutes = SP_TB.FromInMinutes AND
					Week_TB.ToInMinutes = SP_TB.ToInMinutes AND
					Week_TB.weekday = SP_TB.weekday_calc
			ORDER BY
				Week_TB.weekday ASC,
				Week_TB.FromInMinutes ASC
				";

		$stmt = $db->prepare($sql);
		$stmt->bind_param("iss", $systemId, $startDate, $endDate);
		$stmt->execute();
		$stmt->bind_result($weekday, $fromMinutes, $toMinutes, $isAvailable);


		// Initialize an array to store the available time slots
		$result = [];
		// Fetch the booking periods

		$isAvailableCountByWeekday = []; //temp variable to check $weekday is new.
		while ($stmt->fetch()) {
			// Increment the corresponding counter based on isAvailable value and weekday
			if (!isset($isAvailableCountByWeekday[$weekday])) {
				$result[$weekday] = [
					0 => 0,
					1 => 0
				];
				$isAvailableCountByWeekday[$weekday] = 1; //temp
			}

			$time_slot = $fromMinutes . '-' . $toMinutes;
			// Store the available time slot information
			$result[$weekday]["timeslot"][] = [
				'FromInMinutes' => $fromMinutes,
				'ToInMinutes' => $toMinutes,
				'isAvailable' => $isAvailable
			];
			$result[$weekday][$time_slot] = $isAvailable;
			$result[$weekday][$isAvailable]+=1;
		}
		return $result;
		
	}

	//get available/unvavailable time periods from setting_bookingperiods DB BY weekid
	function getOneDayTimePeriodByWeekDay($systemId, $weekday){

		$db = getDBConnection();
		// Prepare and execute a query to retrieve available time slots for the specified systemId
		$sql = "SELECT weekday, FromInMinutes, ToInMinutes, isAvailable
				FROM setting_bookingperiods
				WHERE SystemId = IFNULL(
							(SELECT SystemId 
							FROM setting_bookingperiods 
							WHERE SystemId = ?
							LIMIT 1),
							0) AND weekday = ?";

		$stmt = $db->prepare($sql);
		$stmt->bind_param("ii", $systemId, $weekday);
		$stmt->execute();

		// Bind the result variables
		$stmt->bind_result($weekday, $fromMinutes, $toMinutes, $isAvailable);

		// Initialize an array to store the available time slots
		$result = [];
		// Fetch the booking periods

		$isAvailableCountByWeekday = []; //temp variable to check $weekday is new.
		while ($stmt->fetch()) {
			// Increment the corresponding counter based on isAvailable value and weekday
			if (!isset($isAvailableCountByWeekday[$weekday])) {
				$result[$weekday] = [
					0 => 0,
					1 => 0
				];
				$isAvailableCountByWeekday[$weekday] = 1; //temp
			}
			
			// Store the available time slot information
			$result[$weekday]["timeslot"][] = [
				'FromInMinutes' => $fromMinutes,
				'ToInMinutes' => $toMinutes,
				'isAvailable' => $isAvailable
			];
			$result[$weekday][$isAvailable]+=1;
		}
		return $result;
	}

	//Get available/unavailable time periods from setting_bookingperiods_special DB
	function getTimePeriodsByDay($systemId, $startDate, $endDate){
		
		$db = getDBConnection();
		//Get unavailable time period with SytemId and Data Range
		$stmt = $db->prepare("SELECT SetDate, FromInMinutes, ToInMinutes, isAvailable FROM setting_bookingperiods_special WHERE SystemId = ? AND SetDate >= ? AND SetDate <= ?");

		$stmt->bind_param("iss", $systemId, $startDate, $endDate);
		$stmt->execute();
		$stmt->bind_result($date, $fromInMinutes, $toInMinutes, $isAvailable);

		// Initialize an array to store the booking information
		$availablesInfo = [];

		// Fetch the results
		while ($stmt->fetch()) {
			$time_slot = $fromInMinutes . '-' . $toInMinutes;
			$availablesInfo[$date][$time_slot] = $isAvailable;
		}
		return $availablesInfo;
	}

	//getBookedInfo
	function getBookedInfo($systemId, $startDate, $endDate)
	{
		$db = getDBConnection();
		// Prepare and execute a query to retrieve data from the database for the specified date range and systemId
		$sql = "SELECT bookings.BookingId, bookings.BookingDate, bookings.BookingFrom, bookings.BookingTo, customers.FullName
				FROM bookings
				INNER JOIN customers ON bookings.CustomerId = customers.CustomerId
				WHERE bookings.SystemId = ? AND DATE(bookings.BookingDate) BETWEEN ? AND ?";

		// Prepare the statement
		$stmt = $db->prepare($sql);
		$stmt->bind_param("iss", $systemId, $startDate, $endDate);
		$stmt->execute();
		$stmt->bind_result($bookingId, $bookingDate, $bookingFrom, $bookingTo, $fullName);

		// Initialize an array to store the booking information
		$bookingInfo = [];

		// Fetch the results
		while ($stmt->fetch()) {

			$bookingTimeSlot = $bookingFrom . '-' . $bookingTo;
			// Store the booking information for the corresponding date and time slot
			$bookingInfo[$bookingDate][$bookingTimeSlot] = $fullName;
		}
		return $bookingInfo;
	}

	// Function to insert into unavailable_bookingperiods table
	function insertIntoUnavailableBookingPeriods($systemId, $date, $fromInMinutes, $toInMinutes, $status) {
		// Get database connection
		$db = getDBConnection();

		// Format date
		$formattedDate = date('Y-m-d', $date);

		// Check if the record already exists
		$stmt = $db->prepare("SELECT COUNT(*) FROM setting_bookingperiods_special WHERE SystemId = ? AND SetDate = ? AND FromInMinutes = ? AND ToInMinutes = ?");
		$stmt->execute([$systemId, $formattedDate, $fromInMinutes, $toInMinutes]);
		$stmt->bind_result($existingRecordsCount);
		$stmt->fetch();
		$stmt->close();

		/// If no matching record found, insert the new record
		if ($existingRecordsCount == 0) {
			// Prepare and execute SQL statement to insert the record
			$insertStmt = $db->prepare("INSERT INTO setting_bookingperiods_special (SystemId, SetDate, FromInMinutes, ToInMinutes, isAvailable) VALUES (?, ?, ?, ?, ?)");
			if (!$insertStmt) {
				die('Error in preparing insert SQL statement: ' . $db->error);
			}

			$insertStmt->bind_param('isiii', $systemId, $formattedDate, $fromInMinutes, $toInMinutes, $status);
			if (!$insertStmt->execute()) {
				die('Error executing insert SQL statement: ' . $insertStmt->error);
			}

			$insertStmt->close();
		}else {
			$updateStmt = $db->prepare("UPDATE setting_bookingperiods_special SET isAvailable = ? WHERE SystemId=? AND SetDate = ? AND FromInMinutes = ? AND ToInMinutes = ?");
			if (!$updateStmt) {
				die('Error in preparing insert SQL statement: ' . $db->error);
			}

			$updateStmt->bind_param('iisii', $status, $systemId, $formattedDate, $fromInMinutes, $toInMinutes);
			if (!$updateStmt->execute()) {
				die('Error executing insert SQL statement: ' . $updateStmt->error);
			}

			$updateStmt->close();
    	}
	}
?>