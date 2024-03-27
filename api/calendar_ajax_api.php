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

    // Bind the result variables
    $statement->bind_result($weekday, $isAvailable);

    $arrayWeek = [];

    // Fetch the availability data
    while ($statement->fetch()) {
        // Store each row in $arrayWeek using the weekday as the key
        $arrayWeek[$weekday] = $isAvailable;
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

    // Bind the result variables
    $statement->bind_result($bookingDate, $numBookings);

    $bookedDates = [];

    // Fetch the booking data
    while ($statement->fetch()) {
        $bookedDates[$bookingDate] = $numBookings;
    }

    $data = [];

    // Initialize the date to the first day of the month
    $date = new DateTime("$year-$month-01");

    // Loop through each day of the month
    while ($date->format('Y-m') === sprintf('%04d-%02d', $year, $month)) {
        // Fetch the weekday of the current date (0 for Sunday, 1 for Monday, etc.)
        $weekday = $date->format('w');

        // Determine the date in YYYY-MM-DD format
        $currentDate = $date->format('Y-m-d');

        $className = isset($arrayWeek[$weekday]) && $arrayWeek[$weekday] == 1 ? 'grade-available' : 'grade-unavailable';

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
	exit();

} 
if( $_POST['action'] == "change_availability" ) {
	$date = isset($_POST['date']) ? $_POST['date'] : "";
	$value = isset($_POST['value']) ?  $_POST['value'] : [];

	if ($date == "" || empty($value)) return;

	foreach ($value as $item) {
		$timeSlot = $item['timeSlot']; // Assuming the key is 'timeSlot'
		$status = $item['status']; // Assuming the key is 'status'
		print_r($timeSlot);
		// Now you can use $timeSlot and $status as needed
	}
	
	// $stmt = $db->prepare("SELECT services.ServiceId, ServiceName, FullName, Price, Duration, IsCharge, active FROM services LEFT JOIN system_services as Sys on services.ServiceId = Sys.ServiceId 
	// 	WHERE Sys.systemId=?");
	// $stmt->bind_param( 'i', $systemId );
	// $stmt->execute();
	// $stmt->bind_result($serviceId, $servicename, $fullname,  $price, $duration, $charge, $active);
	// $stmt->store_result();

	// $arrServices = array();
	// if ($stmt->num_rows > 0) {
	// 	while ($stmt->fetch()) {
	// 		$arrServices[] = [
	// 			"name"		=> $fullname,
	// 			"price"		=> displayPrice($price),
	// 			"duration"	=> $duration,
	// 			"charge"	=> displayYN($charge),
	// 			"active"	=> displayYN($active)
	// 		];
	// 	}
	// }

	// $res["status"] = "success";
	// $res['data'] = $arrServices;

	// echo json_encode( $res );
	// exit;
	exit();
}


?>
