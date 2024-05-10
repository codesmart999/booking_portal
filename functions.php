<?php
	require_once('admin/utils.php');

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
		return date("D, d F Y", strtotime($date)); // Don't change this format. It'll cause error in parsing!!!
	}

	// display format Price
	function displayPrice( $price ) {
		return "AUD $".number_format($price, 2, '.', '');
	}

	// display yes/no 
	function displayYN( $val ) {
		if ( empty($val) || $val == 'N' )
			return "No";
		else
			return "Yes";
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

		$result = getAvailableSystems( $selectedDate );

		unset($_SESSION['arrAvailableSystems']);
		unset($_SESSION['arrSystemBookingPeriodsByDaysDiff']);
		unset($_SESSION['appointment_data']['booking_time']);

		if (empty($result) || empty($result['arrSystems']) || empty($result['arrBookingPeriodsByDaysDiff']))
			return false;

		$_SESSION['arrAvailableSystems'] = $result['arrSystems'];
		$_SESSION['arrSystemBookingPeriodsByDaysDiff'] = $result['arrBookingPeriodsByDaysDiff'];

		return true;
    }

    // get Available times by Selected Date 
    // it needs to check by Date, System ( from Location ) and Service
    function getAvailableSystems( $date ) {
    	global $arrAppData;
		
		$endDate = $date;
		if (!empty($arrAppData['five_days']))
			$endDate = date('Y-m-d', strtotime($date . ' +4 days'));

    	// if selected date is passed, then return FALSE
		if( strtotime( $endDate ) < strtotime('now') ) {
			return false;
		}

    	$link = getDBConnection();
		
		$arrSystems = array(); //RESULT
		$arrBookingPeriodsByDaysDiff = array(); //RESULT

    	$locationId = $arrAppData['location'];
		$serviceId = $arrAppData['service'];
		$bLookInFiveDays = !empty($arrAppData['five_days']);

		// 1) Get System list by LocationId && ServiceId
		$stmt = $link->prepare('SELECT sys.SystemId, sys.FullName, sys.Access, sys.SystemType, sys.MaxMultipleBookings FROM systems sys'
			. ' JOIN system_services serv ON sys.SystemId = serv.SystemId'
			. "	WHERE sys.LocationId = $locationId AND serv.ServiceId = $serviceId"
			. ' ORDER BY sys.SystemType ASC, sys.SystemId ASC');
	    $stmt->execute();
	    $stmt->bind_result($systemId, $fullname, $access, $system_type, $max_multiple_bookings);
	    while ($stmt->fetch()) {
	        $arrSystems[$systemId] = array(
	        	"fullname" 	=> $fullname,
	        	"access"	=> $access,
				"system_type" => $system_type,
				"max_multiple_bookings" => $max_multiple_bookings
	        );
	    }

		if (empty($arrSystems))
			return null;

		// 2) Get Already-Existing Bookings & Get Explicitly-Set Unavailable Booking Periods
		$arrSystemIds = array_keys($arrSystems);
		$arrBookings = array();
		$arrSpecialBookingPeriods = array();
		foreach ($arrSystemIds as $systemId) {
			$arrBookings[$systemId] = getBookedInfo($systemId, $date, $endDate);
			$arrSpecialBookingPeriods[$systemId] = getBookingPeriodsSpecialByDate($systemId, $date, $endDate);
		}

		// 3) Get Booking Periods Availability
		$arrSystemIds[] = 0; // Add Default System Id
		$strSystemIds = implode(',', $arrSystemIds);

		// 3-1) Get Weekdays Availability
		$stmt = $link->prepare('SELECT SystemId, weekday, isAvailable FROM setting_weekdays'
			. ' WHERE SystemId IN (' . $strSystemIds . ')'
			. ' ORDER BY SystemId ASC');
	    $stmt->execute();
	    $stmt->bind_result($systemId, $weekday, $is_available);
	    while ($stmt->fetch()) {
			if (!isset($arrSystems[$systemId]))
				$arrSystems[$systemId] = array();
	        
			$arrSystems[$systemId][$weekday] = $is_available;
	    }
		unset($arrSystems[0]);

		// 3-2) Load All Booking Periods for the given SystemIDs
		$strQuery = 'SELECT 
						sbp.weekday,
						sbp.SystemId AS SystemId,
						sbp.FromInMinutes AS FromInMinutes,
						sbp.ToInMinutes AS ToInMinutes,
						sbp.isAvailable AS isAvailable,
						CASE
							WHEN sbp.weekday - DAYOFWEEK(?) + 1 < 0 THEN sbp.weekday - DAYOFWEEK(?) + 1 + 7
							ELSE sbp.weekday - DAYOFWEEK(?) + 1
						END AS days_diff
					FROM 
						setting_bookingperiods sbp
					JOIN 
						setting_weekdays sw ON sbp.weekday = sw.weekday AND sbp.SystemId = sw.SystemId
					WHERE 
						sbp.SystemId IN (' . $strSystemIds . ')' .
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
	    $stmt->bind_result($weekday, $systemId, $FromInMinutes, $ToInMinutes, $isAvailable, $days_diff);
	    
		while($stmt->fetch()) {
			if (!isset($arrBookingPeriodsByDaysDiff[$systemId])) {
				$arrBookingPeriodsByDaysDiff[$systemId] = array();
			}
			if (!isset($arrBookingPeriodsByDaysDiff[$systemId][$days_diff])) {
				$arrBookingPeriodsByDaysDiff[$systemId][$days_diff] = array();
			}

			$arrBookingPeriodsByDaysDiff[$systemId][$days_diff][] = array(
				'weekday' => $weekday,
				'SystemId' => $systemId,
				'FromInMinutes' => $FromInMinutes,
				'ToInMinutes' => $ToInMinutes,
				'isAvailable' => $isAvailable,
				'max_multiple_bookings' => 1
			);
		}

		// 3-3) Copy default booking periods & update availability
		foreach ($arrSystems as $systemId => $objSystem) {
			if (!isset($arrBookingPeriodsByDaysDiff[$systemId])) {
				$arrBookingPeriodsByDaysDiff[$systemId] = unserialize(serialize($arrBookingPeriodsByDaysDiff[0]));
			}

			foreach ($arrBookingPeriodsByDaysDiff[$systemId] as $days_diff => $booking_periods) {
				$calculated_date = date('Y-m-d', strtotime($date . ' +' . $days_diff . ' days'));
				$weekday = date('w', strtotime($calculated_date));

				foreach ($booking_periods as $i => $period) {
					$isAvailable = $period['isAvailable'];

					if (isset($objSystem[$weekday])) {
						$isAvailable = $objSystem[$weekday];
					} else {
						$isAvailable = $arrSystems[0][$weekday];
					}

					// Check if it has already passed the current time
					$newDate = date('Y-m-d H:i:s', strtotime($calculated_date . ' +' . $ToInMinutes . ' minutes'));
					if (strtotime($newDate) < strtotime('now')) {
						$isAvailable = false;
					}

					$arrBookingPeriodsByDaysDiff[$systemId][$days_diff][$i]['isAvailable'] = $isAvailable;
					$arrBookingPeriodsByDaysDiff[$systemId][$days_diff][$i]['max_multiple_bookings'] = $arrSystems[$systemId]['max_multiple_bookings'];
				}
			}
		}
		
		unset($arrBookingPeriodsByDaysDiff[0]);

		// 5) Add Explicitly-Set Available Timeslots (in setting_bookingperiods_special)
		foreach ($arrSpecialBookingPeriods as $systemId => $arr_special_bookingperiods_by_date) {
			foreach ($arr_special_bookingperiods_by_date as $special_date => $arr_special_bookingperiods) {
				$weekday = date('w', strtotime($special_date));
				$diffInSeconds = strtotime($special_date) - strtotime($date);
				$days_diff = floor($diffInSeconds / (60 * 60 * 24));

				foreach ($arr_special_bookingperiods as $timeslot => $values) {
					list($FromInMinutes, $ToInMinutes) = explode('-', $timeslot);

					// Create a flag to check if the element already exists
					$elementExists = false;

					// Check if the element already exists in the array
					if (isset($arrBookingPeriodsByDaysDiff[$systemId][$days_diff])) {
						foreach ($arrBookingPeriodsByDaysDiff[$systemId][$days_diff] as $i => $period) {
							if (is_array($period) && $period['weekday'] == $weekday &&
								$period['FromInMinutes'] == $FromInMinutes &&
								$period['ToInMinutes'] == $ToInMinutes) {
								$elementExists = true;
								$arrBookingPeriodsByDaysDiff[$systemId][$days_diff][$i]['isAvailable'] = $values['isAvailable'];

								if (!empty($values['max_multiple_bookings'])) // zero is default value in setting_bookingperiods_special (no influence)
									$arrBookingPeriodsByDaysDiff[$systemId][$days_diff][$i]['max_multiple_bookings'] = $values['max_multiple_bookings'];
								break;
							}
						}
					}

					// If the element does not exist, add it to the array
					if (!$elementExists) {
						$arrBookingPeriodsByDaysDiff[$systemId][$days_diff][] = array(
							'weekday' => $weekday,
							'SystemId' => $systemId,
							'FromInMinutes' => $FromInMinutes,
							'ToInMinutes' => $ToInMinutes,
							'isAvailable' => $values['isAvailable'],
							'max_multiple_bookings' => empty($values['max_multiple_bookings']) ? $arrSystems[$systemId]['max_multiple_bookings'] : $values['max_multiple_bookings'],
						);
					}
				}
			}
		}

        $stmt->close();
	    $link->close();

		// 6) Check Already-Booked Timeslots (in bookings table)
		foreach ($arrSystems as $systemId => $objSystem) {
			foreach ($arrBookingPeriodsByDaysDiff[$systemId] as $days_diff => &$arr_bookingperiods) {
				$calculated_date = date('Y-m-d', strtotime($date . ' +' . $days_diff . ' days'));
				
				foreach ($arr_bookingperiods as $index => $values) {
					$max_multiple_bookings = $values['max_multiple_bookings'];
					if (isset($arrBookings[$systemId]) && isset($arrBookings[$systemId][$calculated_date])
						&& isset($arrBookings[$systemId][$calculated_date][$values['FromInMinutes'] . '-' . $values['ToInMinutes']])
						&& count($arrBookings[$systemId][$calculated_date][$values['FromInMinutes'] . '-' . $values['ToInMinutes']]) >= $max_multiple_bookings) {
						$arr_bookingperiods[$index]['isAvailable'] = false;
					}
				}
			}
		}

		foreach ($arrBookingPeriodsByDaysDiff as $systemId => &$days) {
			foreach ($days as $days_diff => &$periods) {
				foreach ($periods as $i => $period) {
					if (empty($period['isAvailable'])) {
						unset($arrBookingPeriodsByDaysDiff[$systemId][$days_diff][$i]);
					}
				}
			}
		}

    	return compact('arrSystems', 'arrBookingPeriodsByDaysDiff');
    }

	// Inspired by getAvailableSystems()!!!
	// For each system, it returns the following array
	// Array (
	//	'{DAYS_DIFF}' => Array ( [available_slots] => 39 [bookings] => 6)
	// )
	function getMonthlySummary( $arrSystemIds, $date, $endDate = '' ) {
		if (empty($endDate))
			$endDate = date('Y-m-d', strtotime($date . ' +30 days'));

		$total_days_diff = (strtotime($endDate) - strtotime($date)) / 86400;

    	$link = getDBConnection();

		$result = array();
		
		$arrSystems = array();
		$arrBookingPeriodsByDaysDiff = array();

		// 1) Get Systems
		$query = 'SELECT SystemId, MaxMultipleBookings FROM systems WHERE SystemId IN (' . implode(',', $arrSystemIds) . ')';

		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->bind_result($systemId, $max_multiple_bookings);
		$stmt->store_result();
		while ($stmt->fetch()) {
			$arrSystems[$systemId] = array(
				'max_multiple_bookings' => $max_multiple_bookings,
			);
		}

		// 2) Get Already-Existing Bookings & Get Explicitly-Set Unavailable Booking Periods
		$arrBookings = array();
		$arrSpecialBookingPeriods = array();
		foreach ($arrSystemIds as $systemId) {
			if (empty($systemId))
				continue;
			$arrBookings[$systemId] = getBookedInfo($systemId, $date, $endDate);
			$arrSpecialBookingPeriods[$systemId] = getBookingPeriodsSpecialByDate($systemId, $date, $endDate);
		}

		// 3) Get Booking Periods Availability
		$arrSystemIds[] = 0; // Add Default System Id
		$strSystemIds = implode(',', $arrSystemIds);
	
		// 3-1) Get Weekdays Availability
		$stmt = $link->prepare('SELECT SystemId, weekday, isAvailable FROM setting_weekdays'
			. ' WHERE SystemId IN (' . $strSystemIds . ')'
			. ' ORDER BY SystemId ASC'
		);
		$stmt->execute();
		$stmt->bind_result($systemId, $weekday, $is_available);
		while ($stmt->fetch()) {
			if (!isset($arrSystems[$systemId]))
				$arrSystems[$systemId] = array();
			
			$arrSystems[$systemId][$weekday] = $is_available;
		}
		unset($arrSystems[0]);
	
		// 3-2) Load All Booking Periods for the given SystemIDs
		$strQuery = 'SELECT 
				sbp.weekday,
				sbp.SystemId AS SystemId,
				sbp.FromInMinutes AS FromInMinutes,
				sbp.ToInMinutes AS ToInMinutes,
				sbp.isAvailable AS isAvailable,
				CASE
				  WHEN sbp.weekday - DAYOFWEEK(?) + 1 < 0 THEN sbp.weekday - DAYOFWEEK(?) + 1 + 7
				  ELSE sbp.weekday - DAYOFWEEK(?) + 1
				END AS days_diff
			  FROM 
				setting_bookingperiods sbp
			  JOIN 
				setting_weekdays sw ON sbp.weekday = sw.weekday AND sbp.SystemId = sw.SystemId
			  WHERE 
				sbp.SystemId IN (' . $strSystemIds . ')';
		$stmt = $link->prepare($strQuery);
		$stmt->bind_param('sss', $date, $date, $date);
		
		$stmt->execute();
		$stmt->bind_result($weekday, $systemId, $FromInMinutes, $ToInMinutes, $isAvailable, $days_diff);
		  
		while($stmt->fetch()) {
			if (!isset($arrBookingPeriodsByDaysDiff[$systemId])) {
				$arrBookingPeriodsByDaysDiff[$systemId] = array();
			}
			if (!isset($arrBookingPeriodsByDaysDiff[$systemId][$days_diff])) {
				$arrBookingPeriodsByDaysDiff[$systemId][$days_diff] = array();
			}

			$arrBookingPeriodsByDaysDiff[$systemId][$days_diff][] = array(
				'weekday' => $weekday,
				'SystemId' => $systemId,
				'FromInMinutes' => $FromInMinutes,
				'ToInMinutes' => $ToInMinutes,
				'isAvailable' => $isAvailable,
				'max_multiple_bookings' => 1
			);
		}
	
		// 3-3) Copy default booking periods & update availability
		foreach ($arrSystems as $systemId => $objSystem) {
			if (!isset($arrBookingPeriodsByDaysDiff[$systemId])) {
				$arrBookingPeriodsByDaysDiff[$systemId] = unserialize(serialize($arrBookingPeriodsByDaysDiff[0]));
			}

			foreach ($arrBookingPeriodsByDaysDiff[$systemId] as $days_diff => $booking_periods) {
				$calculated_date = date('Y-m-d', strtotime($date . ' +' . $days_diff . ' days'));
				$weekday = date('w', strtotime($calculated_date));

				foreach ($booking_periods as $i => $period) {
					$isAvailable = $period['isAvailable'];

					if (isset($objSystem[$weekday])) {
						$isAvailable = $objSystem[$weekday];
					} else {
						$isAvailable = $arrSystems[0][$weekday];
					}

					$arrBookingPeriodsByDaysDiff[$systemId][$days_diff][$i]['isAvailable'] = $isAvailable;
					$arrBookingPeriodsByDaysDiff[$systemId][$days_diff][$i]['max_multiple_bookings'] = $arrSystems[$systemId]['max_multiple_bookings'];
				}
			}
		}
		
		unset($arrBookingPeriodsByDaysDiff[0]);

		// 4) Expand days  - different from getAvailableSystems()
		for ($i = 7; $i <= $total_days_diff; $i++) {
			foreach ($arrSystemIds as $systemId) {
				if (isset($arrBookingPeriodsByDaysDiff[$systemId][$i % 7])) {
					$arrBookingPeriodsByDaysDiff[$systemId][$i] = $arrBookingPeriodsByDaysDiff[$systemId][$i % 7];
				}
			}
		}

		// 5) Add Explicitly-Set Available/Unavailable Timeslots (in setting_bookingperiods_special)
		foreach ($arrSpecialBookingPeriods as $systemId => $arr_special_bookingperiods_by_date) {
			foreach ($arr_special_bookingperiods_by_date as $special_date => $arr_special_bookingperiods) {
				$weekday = date('w', strtotime($special_date));
				$diffInSeconds = strtotime($special_date) - strtotime($date);
				$days_diff = floor($diffInSeconds / (60 * 60 * 24));

				foreach ($arr_special_bookingperiods as $timeslot => $values) {
					list($FromInMinutes, $ToInMinutes) = explode('-', $timeslot);

					// Create a flag to check if the element already exists
					$elementExists = false;

					// Check if the element already exists in the array
					if (isset($arrBookingPeriodsByDaysDiff[$systemId][$days_diff])) {
						foreach ($arrBookingPeriodsByDaysDiff[$systemId][$days_diff] as $i => $period) {
							if (is_array($period) && $period['weekday'] == $weekday &&
								$period['FromInMinutes'] == $FromInMinutes &&
								$period['ToInMinutes'] == $ToInMinutes) {
								$elementExists = true;
								$arrBookingPeriodsByDaysDiff[$systemId][$days_diff][$i]['isAvailable'] = $values['isAvailable'];
								
								if (!empty($values['max_multiple_bookings'])) // zero is default value in setting_bookingperiods_special (no influence)
									$arrBookingPeriodsByDaysDiff[$systemId][$days_diff][$i]['max_multiple_bookings'] = $values['max_multiple_bookings'];
								break;
							}
						}
					}

					// If the element does not exist, add it to the array
					if (!$elementExists) {
						$arrBookingPeriodsByDaysDiff[$systemId][$days_diff][] = array(
							'weekday' => $weekday,
							'SystemId' => $systemId,
							'FromInMinutes' => $FromInMinutes,
							'ToInMinutes' => $ToInMinutes,
							'isAvailable' => $values['isAvailable'],
							'max_multiple_bookings' => empty($values['max_multiple_bookings']) ? $arrSystems[$systemId]['max_multiple_bookings'] : $values['max_multiple_bookings'],
						);
					}
				}
			}
		}

        $stmt->close();
	    $link->close();

		// 6) Check Already-Booked Timeslots (in bookings table)
		foreach ($arrSystems as $systemId => $objSystem) {
			foreach ($arrBookingPeriodsByDaysDiff[$systemId] as $days_diff => &$arr_bookingperiods) {
				$calculated_date = date('Y-m-d', strtotime($date . ' +' . $days_diff . ' days'));
				
				foreach ($arr_bookingperiods as $index => $values) {
					$max_multiple_bookings = $values['max_multiple_bookings'];
					if (isset($arrBookings[$systemId]) && isset($arrBookings[$systemId][$calculated_date])
						&& isset($arrBookings[$systemId][$calculated_date][$values['FromInMinutes'] . '-' . $values['ToInMinutes']])
						&& count($arrBookings[$systemId][$calculated_date][$values['FromInMinutes'] . '-' . $values['ToInMinutes']]) >= $max_multiple_bookings) {
						$arr_bookingperiods[$index]['isAvailable'] = false;
					}
				}
			}
		}

		unset($arrBookingPeriodsByDaysDiff[0]);

		// 7) Count the number of Available Slots
		foreach ($arrBookingPeriodsByDaysDiff as $systemId => &$arrSystemBookingPeriodsByDaysDiff) {
			ksort($arrSystemBookingPeriodsByDaysDiff); //Sorty by key (i.e. days_diff)

			foreach ($arrSystemBookingPeriodsByDaysDiff as $days_diff => $booking_periods) {
				$calculated_date = date('Y-m-d', strtotime($date . ' +' . $days_diff . ' days'));

				$single_bookings = 0;
				$group_bookings = 0;
				if (isset($arrBookings[$systemId][$calculated_date])) {

					foreach ($arrBookings[$systemId][$calculated_date] as $key => $values) {
						if (strpos($key, '-') !== false) {
							$single_bookings += count($values);
						} else
							$group_bookings ++;
					}
				}

				$result[$systemId][$days_diff] = array(
					'available_slots' => count(array_filter($booking_periods, 'filterAvailable')),
					'unavailable_slots' => count(array_filter($booking_periods, 'filterUnavailable')),
					'single_bookings' => $single_bookings,
					'group_bookings' => $group_bookings,
					'booking_periods' => $booking_periods
				);
			}
		}
		
		ksort($result);

    	return $result;
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
		mt_srand(crc32($seed . strtotime('now')));

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

	function formatTimeRange($fromMinutes, $toMinutes) {
	    // Format the start time
	    $startHour = floor($fromMinutes / 60);
	    $startMinute = $fromMinutes % 60;
	    $startTime = sprintf('%d:%02d', $startHour, $startMinute);
	
	    // Format the end time
	    $endHour = floor($toMinutes / 60);
	    $endMinute = $toMinutes % 60;
	    $endTime = sprintf('%d:%02d', $endHour, $endMinute);
	
	    // Combine start and end times
	    $timeRender = date('g:i A', strtotime($startTime)) . ' - ' . date('g:i A', strtotime($endTime));
	
	    return $timeRender;
	}
	
	//get Number of bookings
	function getNumberOfBookings($bookingData ) {
		// Initialize an empty array to store unique booking codes
		$numUniqueBookings = 0;

		// Iterate through each date
		foreach ($bookingData as $date => $timeSlots) {
			// Iterate through each time slot
			foreach ($timeSlots as $slot => $bookings) {
				if (strpos($slot, '-') === false)
					$numUniqueBookings++;
			}
		}

		return $numUniqueBookings;
	}


	/**
	 * Executes an SQL query to retrieve availability data for a specific date range.
	 *
	 * @param int 		$systemId 	
	 * @param string 	$startDate The start date of the date range (YYYY-MM-DD).
	 * @param string 	$endDate   The end date of the date range (YYYY-MM-DD).
	 *
	 * @return array Associative array containing availability data for each date in the range.
	 */
	function getAvailabilityDataWithCounts($systemId, $startDate, $endDate) {
		$arrMonthlySummaryBySystems = getMonthlySummary(array($systemId), $startDate, $endDate);
    	$arrMonthlySummary = $arrMonthlySummaryBySystems[$systemId];

		$result = [];

		foreach ($arrMonthlySummary as $days_diff => $data) {
			if (isset($data['available_slots']) && isset($data['unavailable_slots'])) {
				$calculated_date = date('Y-m-d', strtotime($startDate . ' +' . $days_diff . ' days'));

				$result[$calculated_date]['nAvailable'] = $data['available_slots'];
				$result[$calculated_date]['nUnavailable'] = $data['unavailable_slots'];

				if (!empty($data['available_slots'])) {
					$result[1][] = $calculated_date;
				}
				if (!empty($data['unavailable_slots'])) {
					$result[0][] = $calculated_date;
				}
			}
		}

		return $result;
	}

	/**
	 * Executes an SQL query to retrieve availability data for a specific date range.
	 *
	 * @param int 		$systemId 	
	 * @param string 	$startDate The start date of the date range (YYYY-MM-DD).
	 * @param string 	$endDate   The end date of the date range (YYYY-MM-DD).
	 *
	 * @return array Associative array containing availability data for each date in the range.
	 */
	function getAvailabilityData($systemId, $startDate, $endDate) {
		
		// SQL query to retrieve availability data
		$db = getDBConnection();

		$sql = "SELECT 
					TB2.SetDate, 
					COALESCE(SP.isAvailable, TB2.isAvailable) AS isAvailable,
					TB2.FromInMinutes,
					TB2.ToInMinutes
				FROM 
					(
						SELECT 
							date_column AS SetDate, 
							D1.weekday, 
							FromInMinutes, 
							ToInMinutes, 
							isRegular, 
							isAvailable 
						FROM 
							(
								SELECT
									date_column,
									MOD(DAYOFWEEK(date_column) + 6, 7) AS weekday
								FROM
									(
										SELECT
											DATE(?) + INTERVAL (t4*10000 + t3*1000 + t2*100 + t1*10 + t0) DAY AS date_column
										FROM
											(SELECT 0 t0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t0,
											(SELECT 0 t1 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t1,
											(SELECT 0 t2 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t2,
											(SELECT 0 t3 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t3,
											(SELECT 0 t4 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t4
									) dates
								WHERE
									date_column BETWEEN ? AND ?
							) AS D1 
						LEFT JOIN 
							(
								SELECT * FROM setting_bookingperiods 
								WHERE 
									SystemId = IFNULL(
										(
											SELECT 
												SystemId 
											FROM 
												setting_bookingperiods 
											WHERE 
												SystemId = ?
											LIMIT 1
										),
										0
									)
							) AS SB
						ON 
							D1.weekday = SB.weekday 
						ORDER BY 
							D1.date_column
					) AS TB2 
				LEFT JOIN 
					setting_bookingperiods_special AS SP 
				ON 
					TB2.SetDate = SP.SetDate 
					AND TB2.FromInMinutes = SP.FromInMinutes 
					AND TB2.ToInMinutes = SP.ToInMinutes 
				ORDER BY 
					TB2.SetDate;
		";

		// Prepare the SQL statement
		$stmt = $db ->prepare($sql);

		// Bind parameters and execute the query
		$stmt->bind_param("sssi", $startDate, $startDate, $endDate, $systemId);
		$stmt->execute();
		$stmt->bind_result($setDate, $isAvailable, $fromMinutes, $toInMinutes);
		
		// Initialize an array to store the available time slots
		$result = [];
		// Fetch the booking periods
		while ($stmt->fetch()) {
			
			$time_slot = $fromMinutes . '-' . $toInMinutes;

			$result[$setDate]["timeslot"][] = [
				'FromInMinutes' => $fromMinutes,
				'ToInMinutes' => $toInMinutes,
				'isAvailable' => $isAvailable
			];
			
		}
		return $result;
	}

	// Added by CodeMAX 2024-04-21
	// return available time only
	function getAvailabilityDataRange($systemId, $startDate, $endDate) {
		
		// SQL query to retrieve availability data
		$db = getDBConnection();

		$sql = "SELECT 
					TB2.SetDate, 
					COALESCE(SP.isAvailable, TB2.isAvailable) AS isAvailable,
					SUM(CASE WHEN TB2.isAvailable = 1 THEN TB2.ToInMinutes - TB2.FromInMinutes ELSE 0 END) AS availableDuration,
    				SUM(CASE WHEN TB2.isAvailable = 0 THEN TB2.ToInMinutes - TB2.FromInMinutes ELSE 0 END) AS unavailableDuration
				FROM 
					(
						SELECT 
							date_column AS SetDate, 
							D1.weekday, 
							FromInMinutes, 
							ToInMinutes, 
							isRegular, 
							isAvailable 
						FROM 
							(
								SELECT
									date_column,
									MOD(DAYOFWEEK(date_column) + 6, 7) AS weekday
								FROM
									(
										SELECT
											DATE(?) + INTERVAL (t4*10000 + t3*1000 + t2*100 + t1*10 + t0) DAY AS date_column
										FROM
											(SELECT 0 t0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t0,
											(SELECT 0 t1 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t1,
											(SELECT 0 t2 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t2,
											(SELECT 0 t3 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t3,
											(SELECT 0 t4 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t4
									) dates
								WHERE
									date_column BETWEEN ? AND ?
							) AS D1 
						LEFT JOIN 
							(
								SELECT * FROM setting_bookingperiods 
								WHERE 
									SystemId = IFNULL(
										(
											SELECT 
												SystemId 
											FROM 
												setting_bookingperiods 
											WHERE 
												SystemId = ?
											LIMIT 1
										),
										0
									)
							) AS SB
						ON 
							D1.weekday = SB.weekday 
						ORDER BY 
							D1.date_column
					) AS TB2 
				LEFT JOIN 
					setting_bookingperiods_special AS SP 
				ON 
					TB2.SetDate = SP.SetDate 
					AND TB2.FromInMinutes = SP.FromInMinutes 
					AND TB2.ToInMinutes = SP.ToInMinutes 
					GROUP BY 
						TB2.SetDate, 
						isAvailable
				ORDER BY 
					TB2.SetDate;
		";

		// Prepare the SQL statement
		$stmt = $db ->prepare($sql);

		// Bind parameters and execute the query
		$stmt->bind_param("sssi", $startDate, $startDate, $endDate, $systemId);
		$stmt->execute();
		$stmt->bind_result($setDate, $isAvailable, $availableDuration, $unavailableDuration);
		
		// Initialize an array to store the available time slots
		$result = [];
		// Fetch the booking periods
		while ($stmt->fetch()) {
			
			$result[$setDate] = [
				'durationAvailable' => $availableDuration,
				'durationUnAvailable' => $unavailableDuration
			];
			
		}
		return $result;
	}

	//Get one Week time periods with DateRange by comibining BookingPeriods DB and Special DB
	function getAvailableInfoInOneWeekRange($systemId, $startDate, $endDate) {
		$arrWeeklySummaryBySystems = getMonthlySummary(array($systemId), $startDate, $endDate);
    	$arrWeeklySummary = $arrWeeklySummaryBySystems[$systemId];

		$result = [];
		foreach ($arrWeeklySummary as $days_diff => $data) {
			if (isset($data['available_slots']) && isset($data['unavailable_slots'])) {
				$weekday = date('w', strtotime($startDate . ' +' . $days_diff . ' days'));

				$result[$weekday]['timeslot'] = $data['booking_periods'];
				$result[$weekday][0] = $data['unavailable_slots'];
				$result[$weekday][1] = $data['available_slots'];
			}
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
		$sql = "SELECT 
					bookings.BookingId, 
					bookings.BookingDate, 
					bookings.BookingFrom, 
					bookings.BookingTo, 
					bookings.BookingCode, 
					bookings.PatientName, 
					bookings.Comments, 
					bookings.Messages, 
					customers.FullName, 
					customers.CustomerId
				FROM bookings
				INNER JOIN customers ON bookings.CustomerId = customers.CustomerId
				WHERE bookings.SystemId = ? AND DATE(bookings.BookingDate) BETWEEN ? AND ?";

		// Prepare the statement
		$stmt = $db->prepare($sql);
		$stmt->bind_param("iss", $systemId, $startDate, $endDate);
		$stmt->execute();
		$stmt->bind_result(
				$bookingId, 
				$bookingDate, 
				$bookingFrom, 
				$bookingTo, 
				$bookingCode,
				$patientName,
				$bookingComments,
				$bookingMessages,
				$customerBusiness, 
				$customerId);

		// Initialize an array to store the booking information
		$bookingInfo = [];

		// Fetch the results
		while ($stmt->fetch()) {
			$bookingTimeSlot = $bookingFrom . '-' . $bookingTo;

			if (!isset($bookingInfo[$bookingDate][$bookingTimeSlot]))
				$bookingInfo[$bookingDate][$bookingTimeSlot] = array();
			// Store the booking information for the corresponding date and time slot
			$bookingInfo[$bookingDate][$bookingTimeSlot][] = array(
				'customer_id' => $customerId,
				'business_name' => $customerBusiness,
				'booking_code' => $bookingCode,
				'patient_name' => $patientName,
				'booking_comments' => $bookingComments,
				'booking_id' => $bookingId
			);
			
			if (isset($bookingInfo[$bookingDate][$bookingCode][0])) {
				if ($bookingInfo[$bookingDate][$bookingCode][1] == $bookingFrom){
					$bookingInfo[$bookingDate][$bookingCode][1] = $bookingTo;
					$bookingInfo[$bookingDate][$bookingCode][2] = get_display_text_from_minutes($bookingInfo[$bookingDate][$bookingCode][0], $bookingTo);
				}
			} else {
				$bookingInfo[$bookingDate][$bookingCode] = [$bookingFrom, $bookingTo, get_display_text_from_minutes($bookingFrom, $bookingTo)];
			}
		}
		return $bookingInfo;
	}

	
	// Added By CodeMAX 2024-040-21   
	// Added for Booking Summary Report
	function getBookedInfoForSummary($systemId, $startDate, $endDate)
	{
		$db = getDBConnection();
		// Prepare and execute a query to retrieve data from the database for the specified date range and systemId
		$sql = "SELECT 
					bookings.BookingDate, 
					SUM(bookings.BookingTo - bookings.BookingFrom)
				FROM bookings
				WHERE bookings.SystemId = ? AND DATE(bookings.BookingDate) BETWEEN ? AND ? GROUP BY bookings.BookingDate";

		// Prepare the statement
		$stmt = $db->prepare($sql);
		$stmt->bind_param("iss", $systemId, $startDate, $endDate);
		$stmt->execute();
		$stmt->bind_result(
				$bookingDate, 
				$duration);

		// Initialize an array to store the booking information
		$bookingInfo = [];

		// Fetch the results
		while ($stmt->fetch()) {
			
			$bookingInfo[$bookingDate] = array(
				
				'duration' => $duration
			);
		}
		return $bookingInfo;
	}

	// Function to insert into unavailable_bookingperiods table
	function insertIntoUnavailableBookingPeriods($systemId, $date, $fromInMinutes, $toInMinutes, $status, $maxMultiBookings = 1, $type = 0) {
		//type == 0 means that updating $status
        //type == 1 means that updating $maxMultiBookings
        
        // Get database connection
		$db = getDBConnection();
		// Format date
		$formattedDate = date('Y-m-d', $date);

		// Check if the record already exists
		$stmt = $db->prepare("SELECT COUNT(*), isAvailable, MaxMultipleBookings  FROM setting_bookingperiods_special WHERE SystemId = ? AND SetDate = ? AND FromInMinutes = ? AND ToInMinutes = ?");
		$stmt->bind_param("isii", $systemId, $formattedDate, $fromInMinutes, $toInMinutes);
		$stmt->execute();
		$stmt->bind_result($existingRecordsCount, $_isAvailable, $_maxMultiBookings);
		$stmt->fetch();
		$stmt->close();

		/// If no matching record found, insert the new record
		if ($existingRecordsCount == 0) {
			// Prepare and execute SQL statement to insert the record
			$insertStmt = $db->prepare("INSERT INTO setting_bookingperiods_special (SystemId, SetDate, FromInMinutes, ToInMinutes, isAvailable, MaxMultipleBookings) VALUES (?, ?, ?, ?, ?, ?)");
			if (!$insertStmt) {
				die('Error in preparing insert SQL statement: ' . $db->error);
			}

			$insertStmt->bind_param('isiiii', $systemId, $formattedDate, $fromInMinutes, $toInMinutes, $status, $maxMultiBookings);
			if (!$insertStmt->execute()) {
				die('Error executing insert SQL statement: ' . $insertStmt->error);
			}

			$insertStmt->close();
		}else {

            if($type == 0){ //update status
                $updateStmt = $db->prepare("UPDATE setting_bookingperiods_special SET isAvailable = ? WHERE SystemId=? AND SetDate = ? AND FromInMinutes = ? AND ToInMinutes = ? ");
                if (!$updateStmt) {
                    die('Error in preparing insert SQL statement: ' . $db->error);
                }

                $updateStmt->bind_param('iisii', $status, $systemId, $formattedDate, $fromInMinutes, $toInMinutes);
                if (!$updateStmt->execute()) {
                    die('Error executing insert SQL statement: ' . $updateStmt->error);
                }

			    $updateStmt->close();

            }else{ //update maxMultipleBookings
                $updateStmt = $db->prepare("UPDATE setting_bookingperiods_special SET isAvailable = ?, MaxMultipleBookings = ? WHERE SystemId=? AND SetDate = ? AND FromInMinutes = ? AND ToInMinutes = ? ");
                if (!$updateStmt) {
                    die('Error in preparing insert SQL statement: ' . $db->error);
                }

                $updateStmt->bind_param('iiisii', $status, $maxMultiBookings, $systemId, $formattedDate, $fromInMinutes, $toInMinutes);
                if (!$updateStmt->execute()) {
                    die('Error executing insert SQL statement: ' . $updateStmt->error);
                }

                $updateStmt->close();
            }
    	}
	}

	function OnDeleteSpecialTimePeriods($systemId, $firstDayOfMonthFormatted, $lastDayOfMonthFormatted){
		$db = getDBConnection();
        $sql = "DELETE FROM setting_bookingperiods_special WHERE SystemId =? AND SetDate BETWEEN? AND?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("iss", $systemId, $firstDayOfMonthFormatted, $lastDayOfMonthFormatted);
        $stmt->execute();
        $stmt->close();
	}

	function InsertManyInSpecialTimePeriods ($values) {
		$db = getDBConnection();
		
		$sql = "INSERT INTO setting_bookingperiods_special (SystemId, SetDate, FromInMinutes, ToInMinutes, isAvailable) VALUES $values";
		
		$stmt = $db->prepare($sql);
		if (!$stmt->execute()) {
			die('Error executing insert SQL statement: ' . $insertStmt->error);
		}

		$stmt->close();
	}

	function getBookedInfoByBookingCode($booking_code, $systemId = 0) {
	    $db = getDBConnection();
		if (empty($systemId)) {
        	$sql = "SELECT FullName, BookingCode, BookingDate, BookingFrom, BookingTo, Attended, Comments FROM (SELECT * FROM bookings WHERE BookingCode = ? ORDER BY BookingFrom) AS T1 JOIN customers ON T1.CustomerId = customers.CustomerId ORDER BY BookingFrom ASC";
			$stmt = $db->prepare($sql);
			$stmt->bind_param("s", $booking_code);
		} else {
			$sql = "SELECT FullName, BookingCode, BookingDate, BookingFrom, BookingTo, Attended, Comments FROM (SELECT * FROM bookings WHERE BookingCode = ? AND SystemId = ? ORDER BY BookingFrom) AS T1 JOIN customers ON T1.CustomerId = customers.CustomerId ORDER BY BookingFrom ASC";
			$stmt = $db->prepare($sql);
			$stmt->bind_param("si", $booking_code, $systemId);
		}
        $stmt->execute();
		$stmt->bind_result($businessName, $bookingCode, $bookingDate, $fromInMinutes, $toInMinutes, $attended, $comments);
		$bookingInfo = [];

		// Fetch the results
		$bookingTimeStart = 100000; // SET MAX VALUE
		$bookingTimeEnd = 0;
		while ($stmt->fetch()) {
			if ($bookingTimeStart > $fromInMinutes)
				$bookingTimeStart= $fromInMinutes;
			if ($bookingTimeEnd < $toInMinutes)
				$bookingTimeEnd = $toInMinutes;
			$bookingInfo["businessName"] = $businessName;
			$bookingInfo["bookingCode"] = $bookingCode;
			$bookingInfo["bookingDate"] = $bookingDate;
			$bookingInfo["attended"] = $attended;
			$bookingInfo["comments"] = $comments;
		}
		$bookingInfo["startTime"] = $bookingTimeStart;
		$bookingInfo["endTime"] = $bookingTimeEnd;
		$bookingInfo["DisplayText"] = get_display_text_from_minutes($bookingTimeStart, $bookingTimeEnd);

		$stmt->close();
		return $bookingInfo;
		
	}


	function addBookingComments($bookingCode, $attended, $new_comment){
		$db = getDBConnection();
		$stmt = $db->prepare("SELECT Comments FROM bookings WHERE BookingCode = ? LIMIT 1");
		$stmt->bind_param("s", $bookingCode);
		$stmt->execute();
		$stmt->bind_result($comments);

		if ($stmt->fetch()) {
			$existing_comments = json_decode($comments, true);
		} else {
			$existing_comments = [];
		}

		if ($existing_comments === null) {
			$existing_comments = [];
		}

		$stmt->close();

		$json_comments = null;

		if (!empty($new_comment))
			$json_comments = json_encode(array_merge($existing_comments, [$new_comment]));
		else 
			$json_comments = json_encode($existing_comments);
		$updateStmt = $db->prepare("UPDATE bookings SET Comments = ?, Attended = ? WHERE BookingCode = ?");
		$updateStmt->bind_param("sis", $json_comments, $attended, $bookingCode);
		$updateStmt->execute();
	}

	function updateBookingComments($bookingCode, $comment_id, $commentDate, $content){
		$db = getDBConnection();
		$stmt = $db->prepare("SELECT Comments FROM bookings WHERE BookingCode = ? LIMIT 1");
		$stmt->bind_param("s", $bookingCode);
		$stmt->execute();
		$stmt->bind_result($comments);

		if ($stmt->fetch()) {
			$existing_comments = json_decode($comments, true);
		} else {
			$existing_comments = [];
		}

		if ($existing_comments === null) {
			$existing_comments = [];
		}

		$stmt->close();
		// Append new comment to existing comments
		$index_to_update = null;
		foreach ($existing_comments as $index => $comment) {
			if ($comment['id'] == $comment_id) {
				$index_to_update = $index;
				break;
			}
		}

		if ($index_to_update !== null) {
			// Update the comment at the found index
			$existing_comments[$index_to_update]["content"] = $content;
			$existing_comments[$index_to_update]["datetime"] = $commentDate;
		}
		
		$json_comments = json_encode($existing_comments);

		$updateStmt = $db->prepare("UPDATE bookings SET Comments = ? WHERE BookingCode = ?");
		$updateStmt->bind_param("ss", $json_comments, $bookingCode);
		$updateStmt->execute();
	}

	function deletBookingCommentsWithCommentId($bookingCode, $comment_id){
		$db = getDBConnection();
		$stmt = $db->prepare("SELECT Comments FROM bookings WHERE BookingCode = ? LIMIT 1");
		$stmt->bind_param("s", $bookingCode);
		$stmt->execute();
		$stmt->bind_result($comments);

		if ($stmt->fetch()) {
			$existing_comments = json_decode($comments, true);
		} else {
			$existing_comments = [];
		}

		if ($existing_comments === null) {
			$existing_comments = [];
		}

		$stmt->close();

		foreach ($existing_comments as $index => $comment) {
			if ($comment['id'] == $comment_id) {
				unset($existing_comments[$index]);
				break;
			}
		}

		$json_comments = json_encode($existing_comments);

		$updateStmt = $db->prepare("UPDATE bookings SET Comments = ? WHERE BookingCode = ?");
		$updateStmt->bind_param("ss", $json_comments, $bookingCode);
		$updateStmt->execute();
	}

	function getUserInfo() {
   
		$db = getDBConnection();
		$stmt = $db->prepare('SELECT UserId, Username FROM users');
		$stmt->execute();
		$stmt->bind_result($userId, $userName);
		$result = [];
		while ($stmt->fetch()) {
			$result[$userId] = $userName;
		}
		return $result;
	}

	// Added by Hennadii (2024-04-01)
	function getBookingPeriodsByWeekday($weekday, $_system_id = 0) {
		$db = getDBConnection();

		$arr_bookingperiod_list = array();
		$arr_systems = array($_system_id);

		if (!empty($_system_id))
			$arr_systems[] = 0;

		foreach ($arr_systems as $systemId) {
			$stmt = $db->prepare("SELECT id, FromInMinutes, ToInMinutes, isRegular, isAvailable FROM setting_bookingperiods WHERE SystemId = ? AND weekday = ? ORDER BY FromInMinutes ASC");
			$stmt->bind_param('ii', $systemId, $weekday);
			$stmt->execute();
			$stmt->bind_result($id, $from_in_mins, $to_in_mins, $isRegular, $isAvailable);
			$stmt->store_result();

			while ($stmt->fetch()) {
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

			if (!empty($arr_bookingperiod_list))
				break;
		}

		$stmt->close();

		return $arr_bookingperiod_list;
	}

	// Added by Hennadii (2024-04-01)
	function getBookingPeriodsSpecialByDate($systemId, $start_date, $end_date = '') {
		$db = getDBConnection();

		$result = array();

		if (empty($end_date))
			$end_date = $start_date;
		
		$stmt = $db->prepare("SELECT SetDate, FromInMinutes, ToInMinutes, isAvailable, MaxMultipleBookings FROM setting_bookingperiods_special WHERE SystemId = ? AND SetDate >= ? AND SetDate <= ? ORDER BY FromInMinutes ASC");
		$stmt->bind_param('iss', $systemId, $start_date, $end_date);
		$stmt->execute();
		$stmt->bind_result( $date, $from_in_mins, $to_in_mins, $isAvailable, $max_multiple_bookings);
		$stmt->store_result();

		while ($stmt->fetch()) {
			if (!isset($result[$date]))
				$result[$date] = array();
			
			$result[$date][$from_in_mins . '-' . $to_in_mins] = array(
				'isAvailable' => $isAvailable,
				'max_multiple_bookings' => $max_multiple_bookings
			);
		}

		$stmt->close();

		return $result;
	}

	// Added by Devmax (2024-04-02)
	function getCutomerInfoById($customer_id){
		$db = getDBConnection();
		$stmt = $db->prepare('SELECT FullName, Email, PostalAddr, Phone, Comment, RegDate FROM customers WHERE CustomerId =?');
		$stmt->bind_param('i', $customer_id);
		$stmt->execute();
		$stmt->bind_result($fullName, $email, $postalAddr, $phone, $comment, $regDate);
		$result = [];
		while ($stmt->fetch()) {
			$result["businessName"] = $fullName;
            $result["email"] = $email;
            $result["postalAddr"] = $postalAddr;
            $result["phone"] = $phone;
            $result["comments"] = $comment;
			$result["regDate"] = $regDate;
		}
		$stmt->close();
		return $result;
	}
	 
	// Added by Devmax (2024-04-02)
	function addCustomerComment($customerId, $new_comment){
		$db = getDBConnection();
		$stmt = $db->prepare("SELECT Comment FROM customers WHERE CustomerId = ?");
		$stmt->bind_param("s", $customerId);
		$stmt->execute();
		$stmt->bind_result($comments);

		if ($stmt->fetch()) {
			$existing_comments = json_decode($comments, true);
		} else {
			$existing_comments = [];
		}

		if ($existing_comments === null) {
			$existing_comments = [];
		}

		$stmt->close();
		// Append new comment to existing comments
		$all_comments = array_merge($existing_comments, [$new_comment]);
		
		// Encode all comments as JSON
		$json_comments = json_encode($all_comments);

		$updateStmt = $db->prepare("UPDATE customers SET Comment = ? WHERE CustomerId = ?");
		$updateStmt->bind_param("ss", $json_comments, $customerId);
		$updateStmt->execute();
	}

	// Added by Devmax (2024-04-02)
	function updateCustomerComment($customerId, $comment_id, $commentDate, $content){
		$db = getDBConnection();
		$stmt = $db->prepare("SELECT Comment FROM customers WHERE CustomerId = ?");
		$stmt->bind_param("s", $customerId);
		$stmt->execute();
		$stmt->bind_result($comments);

		if ($stmt->fetch()) {
			$existing_comments = json_decode($comments, true);
		} else {
			$existing_comments = [];
		}

		if ($existing_comments === null) {
			$existing_comments = [];
		}

		$stmt->close();
		// Append new comment to existing comments
		$index_to_update = null;
		foreach ($existing_comments as $index => $comment) {
			if ($comment['id'] == $comment_id) {
				$index_to_update = $index;
				break;
			}
		}

		if ($index_to_update !== null) {
			// Update the comment at the found index
			$existing_comments[$index_to_update]["content"] = $content;
			$existing_comments[$index_to_update]["datetime"] = $commentDate;
		}
		
		$json_comments = json_encode($existing_comments);

		$updateStmt = $db->prepare("UPDATE customers SET Comment = ? WHERE CustomerId = ?");
		$updateStmt->bind_param("ss", $json_comments, $customerId);
		$updateStmt->execute();
	}

	// Added by Devmax (2024-04-02)
	function deleteCustomerComment($customerId, $comment_id){
		$db = getDBConnection();
		$stmt = $db->prepare("SELECT Comment FROM customers WHERE CustomerId = ?");
		$stmt->bind_param("s", $customerId);
		$stmt->execute();
		$stmt->bind_result($comments);

		if ($stmt->fetch()) {
			$existing_comments = json_decode($comments, true);
		} else {
			$existing_comments = [];
		}

		if ($existing_comments === null) {
			$existing_comments = [];
		}

		$stmt->close();

		foreach ($existing_comments as $index => $comment) {
			if ($comment['id'] == $comment_id) {
				unset($existing_comments[$index]);
				break;
			}
		}

		$json_comments = json_encode($existing_comments);

		$updateStmt = $db->prepare("UPDATE customers SET Comment = ? WHERE CustomerId = ?");
		$updateStmt->bind_param("ss", $json_comments, $customerId);
		$updateStmt->execute();
	}
	
	// Added by Devmax (2024-04-02)
	function getBookedInfoForPrintingByBookingcode($bookingCode){
		$db = getDBConnection();
        $stmt = $db->prepare('SELECT services.FullName as serviceName, systems.FullName, systems.Street, systems.City, systems.State, systems.PostCode, BookingDate, BookingFrom, BookingTo, Comments, Messages, LocationName, customers.FullName as BusinessName  FROM (SELECT * FROM bookings where BookingCode = ?) AS T1 JOIN systems ON T1.SystemId = systems.SystemId JOIN locations on systems.LocationId = locations.LocationId JOIN customers on T1.CustomerId = customers.CustomerId JOIN services ON T1.ServiceId = services.ServiceId');
        $stmt->bind_param('s', $bookingCode);
        $stmt->execute();
        $stmt->bind_result($serviceName,
					$systemFullName, 
					$systemStreet, 
					$systemCity, 
					$systemState, 
					$systemPostcode, 
					$bookingDate, 
					$fromInMinutes, 
					$toInMinutes, 
					$comments, 
					$messages,
					$systemLocation, 
					$businessName);
        $bookingInfo = [];


		$bookingTimeStart = 100000; // SET MAX VALUE
		$bookingTimeEnd = 0;
		while ($stmt->fetch()) {
			if ($bookingTimeStart > $fromInMinutes)
				$bookingTimeStart= $fromInMinutes;
			if ($bookingTimeEnd < $toInMinutes)
				$bookingTimeEnd = $toInMinutes;
			$bookingInfo["serviceName"] = $serviceName;
			$bookingInfo["systemFullName"] = $systemFullName;
			$bookingInfo["systemStreet"] = $systemStreet;
			$bookingInfo["systemCity"] = $systemCity;
			$bookingInfo["systemState"] = $systemState;
			$bookingInfo["systemPostcode"] = $systemPostcode;
			$bookingInfo["bookingDate"] = $bookingDate;
			$bookingInfo["comments"] = $comments;
			$bookingInfo["messages"] = $messages;
			$bookingInfo["businessName"] = $businessName;
		}
		$bookingInfo["startTime"] = $bookingTimeStart;
		$bookingInfo["endTime"] = $bookingTimeEnd;

		return $bookingInfo;
	}

	//get Filter array from input
	//0-show whole day
	//1-show/hide past times
	//2-show/hide unavailable times
	//4-show/hide default unavailable times
	//8-show/hide bookings
	//16-show/hide available times
	function getFilterArray($inputValue) {
		if ($inputValue == 0) {
        	return [0];
    	}	
		$consts = [1, 2, 4, 8, 16];
		$result = [];

		for ($i = count($consts) - 1; $i >= 0; $i--) {
			if ($inputValue & $consts[$i]) {
				$result[] = $consts[$i];
				$inputValue -= $consts[$i];
			}
		}

		return $result;
	}

	function getServiceBySystemId($systemId){
		$db = getDBConnection();
        $stmt = $db->prepare('SELECT * FROM services WHERE SystemId =?');
        $stmt->bind_param('s', $systemId);
        $stmt->execute();
        $stmt->bind_result($serviceId,
					$serviceName, 
					$serviceFullname, 
					$description, 
					$price, 
					$duration, 
					$isCharge, 
					$permission, 
					$active);
        $result = [];


		while ($stmt->fetch()) {
			
			$result["serviceId"] = $serviceId;
			$result["serviceName"] = $serviceName;
			$result["serviceFullname"] = $serviceFullname;
			$result["description"] = $description;
			$result["price"] = $price;
			$result["duration"] = $duration;
			$result["isCharge"] = $isCharge;
			$result["permission"] = $permission;
			$result["active"] = $active;
		}
		
		return $result;
	}

	function getSystemCommentStringFromComment($comment){
		if(isset($comment['type'])){
			if ($comment['type'] == "MoveBooking"){
				$oldBookingDate = date('l, F jS, Y', strtotime($comment["prevDate"]));
				$newBookingDate = date('l, F jS, Y', strtotime($comment["prevDate"]));

				$oldStartTime = date('g:i A', strtotime("today +{$comment['prevFrom']} minutes"));
				$newStartTime = date('g:i A', strtotime("today +{$comment['newFrom']} minutes"));
				return "Rescheduled from " . $oldBookingDate .' '. $oldStartTime .' to '. $newBookingDate .' '. $newStartTime;
			}
		}
	}

	// Added by Hennadii (2024-04-17)
	function convertDurationToHoursMinutes($durationInMinutes) {
		// Calculate hours
		$hours = floor($durationInMinutes / 60);
	
		// Calculate minutes
		$minutes = $durationInMinutes % 60;

		// Format hours and minutes
		$formattedDuration = '-';
		$timeFormatted = "";
		if (!empty($hours) || !empty($minutes)) {
			$formattedDuration = sprintf('%02d:%02d', $hours, $minutes);
			//added by codemax
			$timeFormatted = sprintf('%02d:%02d %s', ($hours % 12 == 0 ? 12 : $hours % 12), $minutes, ($hours < 12 ? 'AM' : 'PM'));
		}
	
		// Return an array containing hours and minutes
		return array('hours' => $hours, 'minutes' => $minutes, 'formatted_text' => $formattedDuration, 'formatted_text_type1' => $timeFormatted);
	}

	// Added by Hennadii (2024-04-17)
	function getSystemNames($arrSystems, $arrBookedSystemIds) {
		$arr_system_fullnames = array();
		foreach ($arrBookedSystemIds as $systemId)
			$arr_system_fullnames[] = $arrSystems[$systemId]['fullname'];

		return implode(',', $arr_system_fullnames);
	}

	// Added by CodeMAX (2024-04-20)
	function getCustomerBookings($customerId, $fromDate, $toDate, $page_start, $limit){
		$db = getDBConnection();
		$sql = "SELECT b.BookingDate, b.BookingCode, b.BookingFrom, b.BookingTo, b.Attended, b.Comments, b.Messages
            FROM bookings b
            JOIN customers c ON b.CustomerId = c.CustomerId
            WHERE b.CustomerId = ? AND b.BookingDate BETWEEN ? AND ? LIMIT ?,?";

		// Prepare the statement
		$stmt = $db->prepare($sql);

		// Bind parameters
		$stmt->bind_param('issii', $customerId, $fromDate, $toDate, $page_start, $limit);

		// Execute the statement
		$stmt->execute();

		// Bind result variables
		$stmt->bind_result($bookingDate, $bookingCode, $bookingFrom, $bookingTo, $attended, $comments, $messages);

		// Initialize an array to store the booking data
		$bookings = array();

		// Fetch the data and store it in the array
		while ($stmt->fetch()) {
			$commentsArray = json_decode($comments, true);
			$commentsStatus = !empty($commentsArray) ? 'Yes' : 'No';
			$bookings[] = array(
				'BookingDate' => $bookingDate,
				'BookingFrom' => $bookingFrom,
				'BookingTo' => $bookingTo,
				'BookingCode' => $bookingCode,
				'Attended' => $attended,
				'Comments' => $commentsStatus,
				'Messages' => $messages
			);
		}

		// Close the statement
		$stmt->close();

		// Return the array of booking data
		return $bookings;
	}

	// Added by Hennadii(2024-04-21)
	function generateBackgroundColor($text) {
		// Convert text into a numeric value
		$hash = 0;
		for ($i = 0; $i < strlen($text); $i++) {
			$hash = ord($text[$i]) + (($hash << 5) - $hash);
		}
	
		// Generate hexadecimal color code with brighter colors
		$color = "#";
		for ($i = 0; $i < 3; $i++) {
			$value = ($hash >> ($i * 8)) & 0xFF;
			$value = max($value, 120); // Ensure minimum RGB component value (adjust as needed)
			$color .= str_pad(dechex($value), 2, "0", STR_PAD_LEFT);
		}
	
		return $color;
	}

	function generateTextColor($text) {
		// Convert text into a numeric value
		$hash = 0;
		for ($i = 0; $i < strlen($text); $i++) {
			$hash = ord($text[$i]) + (($hash << 5) - $hash);
		}
	
		// Generate hexadecimal color code with darker colors
		$color = "#";
		for ($i = 0; $i < 3; $i++) {
			$value = ($hash >> ($i * 8)) & 0xFF;
			$value = max($value - 20, 0); // Decrease brightness by subtracting a value (adjust as needed)
			$color .= str_pad(dechex($value), 2, "0", STR_PAD_LEFT);
		}
	
		return $color;
	}

	// Added by CodeMAX (2024-04-21)
	function getReportAllCustomize($startDate, $endDate, $page_start, $limit, $systemId, $locationId)
	{

		$db = getDBConnection();

		// Base SQL query
		$sql = "SELECT s.FullName,
							l.locationName,
							b.BookingDate,
							b.IsCancelled,
							b.BookingFrom,
							b.BookingTo,
							b.createdAt,
							b.BookingCode,
							b.PatientName,
							b.StaffName,
							c.FullName,
							sv.FullName,
							b.Attended,
							sv.Price,
							sv.DurationInMins_Doctor,
							sv.DurationInMins_Nurse,
							c.Email,
							c.Phone,
							c.PostalAddr
				FROM bookings b
				JOIN customers c ON b.CustomerId = c.CustomerId
				JOIN systems s ON b.SystemId = s.SystemId
				JOIN services sv ON b.ServiceId = sv.ServiceId
				JOIN locations l ON s.LocationId = l.LocationId
				WHERE b.BookingDate BETWEEN ? AND ?";

		// Additional conditions based on optional parameters
		if (is_null($page_start) && is_null($limit)) {
			$sql .= " ORDER BY s.SystemId";
			$stmt = $db->prepare($sql);
			$stmt->bind_param('ss', $startDate, $endDate);
		} else if (!is_null($locationId)) {
			$sql .= " AND s.LocationId = ? ORDER BY s.SystemId LIMIT ?, ? ";
			$stmt = $db->prepare($sql);
			$stmt->bind_param('ssiii', $startDate, $endDate, $locationId, $page_start, $limit);
		} else if (!is_null($systemId)) {
			$sql .= " AND b.SystemId = ?  ORDER BY s.SystemId LIMIT ?, ?";
			$stmt = $db->prepare($sql);
			$stmt->bind_param('ssiii', $startDate, $endDate, $systemId, $page_start, $limit);
		} else if (is_null($systemId) && is_null($locationId)) {
			$sql .= " ORDER BY s.SystemId LIMIT ?, ?";
			$stmt = $db->prepare($sql);
			$stmt->bind_param('ssii', $startDate, $endDate, $page_start, $limit);
		}
		$stmt->execute();
		// Bind result variables
		$stmt->bind_result($systemName,
			$locationName,
			$bookingForDate,
			$isCancelled,
			$bookingFrom,
			$bookingTo,
			$bookingDate,
			$bookingCode,
			$patientName,
			$staffName,
			$businessName,
			$serviceName,
			$attended,
			$servicePrice,
			$durationDoctor,
			$durationNurse,
			$customerEmail,
			$customerPhone,
			$customerAddress
		);

		// Initialize an array to store the booking data
		$bookings = array();

		// Fetch the data and store it in the array
		while ($stmt->fetch()) {
			$bookings[] = array(
				'systemName' => $systemName,
				'systemLocation' => $locationName,
				'bookingForDate' => $bookingForDate,
				'isCancelled' => $isCancelled,
				'bookingFrom' => $bookingFrom,
				'bookingTo' => $bookingTo,
				'bookingDate' => $bookingDate,
				'bookingCode' => $bookingCode,
				'patientName' => $patientName,
				'staffName' => $staffName,
				'businessName' => $businessName,
				'isAttended' => $attended,
				'serviceName' => $serviceName,
				'servicePrice' => $servicePrice,
				'durationDoctor' => $durationDoctor,
				'durationNurse' => $durationNurse,
				"customerEmail" => $customerEmail,
				"customerPhone" => $customerPhone,
				"customerAddress" => $customerAddress,
			);
		}
		// Close the statement
		$stmt->close();

		// Return the array of booking data
		return $bookings;
	}

	// Added by CodeMAX (2024-04-21)
	function getCountReportAllCustomize($startDate, $endDate, $systemId, $locationId){
	
		$db = getDBConnection();
    
		// Base SQL query
		$sql = "SELECT count(*)
			FROM bookings b
			JOIN customers c ON b.CustomerId = c.CustomerId
			JOIN systems s ON b.SystemId = s.SystemId
			WHERE b.BookingDate BETWEEN ? AND ?";
		
		// Additional conditions based on optional parameters
		if (!is_null($locationId)) {
			$sql .= " AND s.LocationId = ?";
			$stmt = $db->prepare($sql);
			$stmt->bind_param('ssi', $startDate, $endDate, $locationId);
		}
		if (!is_null($systemId)) {
			$sql .= " AND b.SystemId = ?";
			$stmt = $db->prepare($sql);
			$stmt->bind_param('ssi', $startDate, $endDate, $systemId);
		}
		if (is_null($systemId) && is_null($locationId)) {
			$stmt = $db->prepare($sql);
			$stmt->bind_param('ss', $startDate, $endDate);
		}
		$stmt->execute();

		// Bind result variables
		$stmt->bind_result($total_rows);
		$stmt->fetch();
		// Close the statement
		$stmt->close();

		// Return the array of booking data
		return $total_rows;
	}

	// Added by CodeMAX (2024-04-21)
	function getReportByDate($startDate, $page_start, $limit){
		$db = getDBConnection();
    
		// Base SQL query
		$sql = "SELECT s.FullName, b.BookingDate, b.BookingFrom, b.BookingTo, c.FullName 
			FROM bookings b
			JOIN customers c ON b.CustomerId = c.CustomerId
			JOIN systems s ON b.SystemId = s.SystemId
			JOIN services v ON b.ServiceId = v.ServiceId
			WHERE b.BookingDate BETWEEN ? AND ?";
		
		// Additional conditions based on optional parameters
		if (is_null($page_start) && is_null($limit)) {
			$stmt = $db->prepare($sql);
			$stmt->bind_param('ss', $startDate, $startDate);
		}else {
			$sql .= " LIMIT ?, ?";
			$stmt = $db->prepare($sql);
			$stmt->bind_param('ssii', $startDate, $startDate, $page_start, $limit);
		}
		
		$stmt->execute();

		// Bind result variables
		$stmt->bind_result($systemName, $bookingForDate, $bookingFrom, $bookingTo, $businessName);

		// Initialize an array to store the booking data
		$bookings = array();

		// Fetch the data and store it in the array
		while ($stmt->fetch()) {
			$bookings[] = array(
				'systemName' => $systemName,
				'bookingForDate' => $bookingForDate,
				'bookingFrom' => $bookingFrom,
				'bookingTo' => $bookingTo,
				'businessName' => $businessName,
			);
		}

		// Close the statement
		$stmt->close();

		// Return the array of booking data
		return $bookings;
	}
	
	// Added by CodeMAX (2024-04-21)
	function getReportBookingSummary($startDate, $endDate, $page_start, $limit, $systemId, $locationId){
	
		$db = getDBConnection();
    
		// Base SQL query
		$sql = "SELECT s.FullName, b.BookingDate, b.IsCancelled, b.BookingFrom, b.BookingTo, b.createdAt, b.BookingCode, c.FullName, b.Attended
			FROM bookings b
			JOIN customers c ON b.CustomerId = c.CustomerId
			JOIN systems s ON b.SystemId = s.SystemId
			WHERE b.BookingDate BETWEEN ? AND ?";
		
		// Additional conditions based on optional parameters
		if (is_null($page_start) && is_null($limit)) {
			$stmt = $db->prepare($sql);
			$stmt->bind_param('ss', $startDate, $endDate);
		}
		else if (!is_null($locationId)) {
			$sql .= " AND s.LocationId = ? LIMIT ?, ?";
			$stmt = $db->prepare($sql);
			$stmt->bind_param('ssiii', $startDate, $endDate, $locationId, $page_start, $limit);
		}
		else if (!is_null($systemId)) {
			$sql .= " AND b.SystemId = ? LIMIT ?, ?";
			$stmt = $db->prepare($sql);
			$stmt->bind_param('ssiii', $startDate, $endDate, $systemId, $page_start, $limit);
		}
		else if (is_null($systemId) && is_null($locationId)) {
			$sql .= " LIMIT ?, ?";
			$stmt = $db->prepare($sql);
			$stmt->bind_param('ssii', $startDate, $endDate, $page_start, $limit);
		}
		$stmt->execute();

		// Bind result variables
		$stmt->bind_result($systemName, $bookingForDate, $isCancelled, $bookingFrom, $bookingTo, $bookingDate, $bookingCode, $businessName, $isAttended);

		// Initialize an array to store the booking data
		$bookings = array();

		// Fetch the data and store it in the array
		while ($stmt->fetch()) {
			$bookings[] = array(
				'systemName' => $systemName,
				'bookingForDate' => $bookingForDate,
				'isCancelled' => $isCancelled,
				'bookingFrom' => $bookingFrom,
				'bookingTo' => $bookingTo,
				'bookingDate' => $bookingDate,
				'bookingCode' => $bookingCode,
				'businessName' => $businessName,
				'isAttended' => $isAttended
			);
		}

		// Close the statement
		$stmt->close();

		// Return the array of booking data
		return $bookings;
	}

	function apply_template($apply_date, $template_systemId, $template_date, $bIncludeBookings = false) {
		$db = getDBConnection();

		$stmt = $db->prepare('DELETE FROM `setting_bookingperiods_special` WHERE `SystemId` = ? AND `SetDate` = ?');
		$stmt->bind_param('is', $template_systemId, $apply_date);
		$stmt->execute();

		$stmt = $db->prepare('INSERT INTO `setting_bookingperiods_special` (`SystemId`, `SetDate`, `FromInMinutes`, `ToInMinutes`, `isAvailable`)
			SELECT 
				`SystemId`,
				? AS `SetDate`,
				`FromInMinutes`,
				`ToInMinutes`,
				`isAvailable`
			FROM 
				`setting_bookingperiods_special`
			WHERE 
				`SystemId` = ? AND
				`SetDate` = ?
			ORDER BY 
				`FromInMinutes` ASC;');
		$stmt->bind_param('sis', $apply_date, $template_systemId, $template_date);
		$stmt->execute();

		if ($bIncludeBookings) {
			$stmt = $db->prepare('DELETE FROM `bookings` WHERE `SystemId` = ? AND `BookingDate` = ?');
			$stmt->bind_param('is', $template_systemId, $apply_date);
			$stmt->execute();

			$stmt = $db->prepare('INSERT INTO `bookings` (`ServiceId`, `SystemId`, `CustomerId`, `StaffName`, `PatientName`, `BookingDate`, `BookingFrom`, `BookingTo`, `BookingCode`, `IsCancelled`, `Attended`, `Comments`, `Messages`)
				SELECT 
					`ServiceId`,
					`SystemId`,
					`CustomerId`,
					`StaffName`,
					`PatientName`,
					? AS `BookingDate`,
					`BookingFrom`,
					`BookingTo`,
					`BookingCode` AS `BookingCode`,
					`IsCancelled`,
					`Attended`,
					`Comments`,
					`Messages`
				FROM 
					`bookings`
				WHERE 
					`SystemId` = ? AND
					`BookingDate` = ?
				ORDER BY 
					`BookingFrom` ASC;');
			$stmt->bind_param('sis', $apply_date, $template_systemId, $template_date);
			$stmt->execute();
		}
	}

	function filterAvailable($item) {
		return !empty($item['isAvailable']);
	}

	function filterUnavailable($item) {
		return empty($item['isAvailable']);
	}
?>