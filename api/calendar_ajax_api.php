<?php
include("../config.php");
require_once('../lib.php');
//GET DATA FROM DB FOR RENDERING DATA ON CALENDAR WIDGET
if (!empty($_REQUEST['year']) && !empty($_REQUEST['month']) && !empty($_REQUEST['SystemId'])) {

    // Get the database connection
    $db = getDBConnection();

    $systemId = (int)$_REQUEST['SystemId'];
    $year = (int)$_REQUEST['year'];
    $month = (int)$_REQUEST['month'];

    // Fetch availability data from setting_weekdays
    $query = "SELECT weekday, isAvailable 
                FROM setting_weekdays 
                WHERE SystemId = IFNULL(
                    (SELECT SystemId 
                    FROM setting_weekdays 
                    WHERE SystemId = ?
                    LIMIT 1),
                    0
                )";
    $statement = $db->prepare($query);
    $statement->bind_param("i", $systemId);
    $success = $statement->execute();
    if ($success === false) {
        // Handle query execution failure
        header('HTTP/1.1 500 Internal Server Error');
        exit;
    }

    $result = $statement->get_result();
    $data = [];

    // Initialize the date to the first day of the month
    $date = new DateTime("$year-$month-01");

	$arrayWeek = [];

	// Check if there are rows returned from the query
	if ($result->num_rows > 0) {
		// Iterate over the rows fetched from the database
		while ($row = $result->fetch_assoc()) {
			// Store each row in $arrayWeek using the weekday as the key
			$arrayWeek[$row['weekday']] = $row['isAvailable'];
		}
	}

	// Get the first and last day of the current month
	$firstDayOfMonth = date('Y-m-01', strtotime("$year-$month-01"));
	$lastDayOfMonth = date('Y-m-t', strtotime("$year-$month-01"));

	// Fetch booking data for the current month
	$query = "SELECT BookingDate, COUNT(*) AS numBookings 
          FROM bookings 
          WHERE SystemID = ? AND BookingDate BETWEEN ? AND ? 
          GROUP BY BookingDate";
	$statement = $db->prepare($query);
	$statement->bind_param("iss", $systemId, $firstDayOfMonth, $lastDayOfMonth);
	$success = $statement->execute();

	if ($success === false) {
		// Handle query execution failure
		header('HTTP/1.1 500 Internal Server Error');
		exit;
	}

	$result = $statement->get_result();
	$bookedDates = [];

	while ($row = $result->fetch_assoc()) {
		$bookedDates[$row['BookingDate']] = $row['numBookings'];
	}


    // Loop through each day of the month
    while ($date->format('Y-m') === sprintf('%04d-%02d', $year, $month)) {
        // Fetch the weekday of the current date (1 for Monday, 2 for Tuesday, etc.)
        $weekday = $date->format('w');
		
		// Determine the date in YYYY-MM-DD format
    	$currentDate = $date->format('Y-m-d');

		$className = ($arrayWeek[$weekday] == 1) ? 'grade-available' : 'grade-unavailable';

    	// Check if the current date is booked and count the number of bookings
    	$numBookings = isset($bookedDates[$currentDate]) ? $bookedDates[$currentDate] : 0;
		if ($numBookings > 3) {
        	$className = 'grade-booked-many';
		} elseif ($numBookings > 0) {
			$className = 'grade-booked';
		} 
        // Add the availability data for the current day to the array
        $data[] = [
            'date' => $date->format('Y-m-d'),
            'classname' => $className,
            'markup' => null
        ];

        // Move to the next day
        $date->modify('+1 day');
    }

    // Set the content type to JSON
    header('Content-Type: application/json');

    // Output the data as JSON
    echo json_encode($data);

} else {
    header('HTTP/1.1 400 Bad Request');
}
?>
