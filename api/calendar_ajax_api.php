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

    // Get the first and last day of the current month
    $firstDayOfMonth = date('Y-m-01', strtotime("$year-$month-01"));
    $lastDayOfMonth = date('Y-m-t', strtotime("$year-$month-01"));

    // Added by Hennadii(20204-04-26)
    $arrMonthlySummaryBySystems = getMonthlySummary(array($systemId), $firstDayOfMonth, $lastDayOfMonth);
    $arrMonthlySummary = $arrMonthlySummaryBySystems[$systemId];

    //Sample Return Result 
    // [2025-10-10] => Array
    //     (
    //         [available_slots] => 7
    //         [unavailable_slots] => 0
    //         [single_bookings] => 5
    //         [group_bookings] => 2
    //     )

    // [2025-10-13] => Array
    //     (
    //         [available_slots] => 7
    //         [unavailable_slots] => 0
    //         [single_bookings] => 5
    //         [group_bookings] => 2
    //     )
    //__debug($arrMonthlySummary);

    $data = [];

    // Initialize the date to the first day of the month
    $date = new DateTime("$year-$month-01");
    $days_diff = 0;

    // Loop through each day of the month
    while ($date->format('Y-m') === sprintf('%04d-%02d', $year, $month)) {
        // Fetch the weekday of the current date (0 for Sunday, 1 for Monday, etc.)
        $weekday = $date->format('w');

        $className = 'grade-available';

        if (!empty($arrMonthlySummary[$days_diff]['unavailable_slots'])){
            $className = 'grade-unavailable';
        }
            
        if (!empty($arrMonthlySummary[$days_diff]['available_slots'])) {
            $className = 'grade-available';
        }

        // Check if the current date is booked and count the number of bookings
        if ($arrMonthlySummary[$days_diff]['group_bookings'] > 1) {
            $className = 'grade-booked-many';
        } elseif ($arrMonthlySummary[$days_diff]['single_bookings'] > 0) {
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
        $days_diff++;
    }

    // Set the content type to JSON
    header('Content-Type: application/json');

    // Output the data as JSON
    echo json_encode($data);
	exit();

} 

//GET DATA FROM DB FOR CHAGNE_AVIALITY ACTION
if (!empty($_REQUEST['action']) && $_POST['action'] == "change_availability") {
    $date = isset($_POST['date']) ? intval($_POST['date']) : 0;
    $value = isset($_POST['value']) ? $_POST['value'] : [];
    $systemId = isset($_POST['systemId']) ? $_POST['systemId'] : [];

    // Validate input
    if ($date == 0 || empty($value) || empty($systemId)) {
        // Handle invalid input, maybe return an error response
        return;
    }

    // Loop through each value in the 'value' array
    foreach ($value as $item) {
        $timeSlot = $item['timeSlot'];
        $status = $item['status'];
        $invertedStatus = ($status == "1") ? "0" : "1";
        // Extract hours and minutes from the time slot
        list($fromInMinutes, $toInMinutes) = explode('-', $timeSlot);
        insertIntoUnavailableBookingPeriods($systemId, $date, $fromInMinutes, $toInMinutes, $invertedStatus);
        
        // Output status for debugging
        
    }

    // Exit after processing
    exit();
}

if (!empty($_REQUEST['action']) && $_POST['action'] == "change_availability_date_range") {
    $date = isset($_POST['date']) ? intval($_POST['date']) : 0;
    $flag = isset($_POST['flag']) ? intval($_POST['flag']) : [];
    $systemId = isset($_POST['systemId']) ? $_POST['systemId'] : [];
    
    // Validate input
    if ($date == 0 || empty($systemId)) {
        // Handle invalid input, maybe return an error response
        return;
    }
    $formattedDate = date('Y-m-d', $date);
     
    // Get the day of the week (0 for Sunday, 1 for Monday, ..., 6 for Saturday)
    $dayOfWeek = date('w', $date);
    $availableTimeslot = getAvailableInfoInOneWeekRange($systemId, $formattedDate, $formattedDate);
    
    //$availablesInfo = getTimePeriodsByDay($systemId, $formattedDate, $formattedDate);
    //print_r($formattedDate);
    foreach ($availableTimeslot[$dayOfWeek]["timeslot"] as $slot) {
        if ($slot['isAvailable'] == $flag){
            insertIntoUnavailableBookingPeriods($systemId, $date, $slot['FromInMinutes'], $slot['ToInMinutes'], !$flag);
        }
    }
    // if (isset($availablesInfo[$formattedDate])){
    //     foreach ($availablesInfo[$formattedDate] as $key => $value) {
    //         // Check if the value is 1
    //         $time_slot_parts = explode('-', $key);
    //         if ($value == $flag) {
    //             insertIntoUnavailableBookingPeriods($systemId, $date, $time_slot_parts[0], $time_slot_parts[1], !$flag);
    //         }
    //     }
    // }

    // Exit after processing
    echo json_encode(1);
    exit();
}

//MUTIL BOOKING
if (!empty($_REQUEST['action']) && $_POST['action'] === 'multi_bookings') {

    $availableInfo = isset($_POST['availableInfo']) ? $_POST['availableInfo'] : 0;
    $timeSlot = isset($_POST['slot']) ? $_POST['slot'] : 0;
    $date = isset($_POST['date']) ? $_POST['date'] : 0;
    $multiple_booking = isset($_POST['multiple_booking']) ? $_POST['multiple_booking'] : '';
    $max_multiple_bookings = isset($_POST['max_multiple_bookings']) ? $_POST['max_multiple_bookings'] : '';
    $systemId = isset($_POST['systemId']) ? $_POST['systemId'] : [];
    
    // Validate input
    if ($date == 0 || empty($systemId)) {
        // Handle invalid input, maybe return an error response
        return;
    }
    $formattedDate = date('Y-m-d', $date);
    list($fromInMinutes, $toInMinutes) = explode('-', $timeSlot);
    insertIntoUnavailableBookingPeriods($systemId, $date, $fromInMinutes, $toInMinutes, $availableInfo, $max_multiple_bookings, 1); //final param 1 means updating multiplebookings

    echo json_encode(1);
    exit();
}

//API for action "Make All Available/ Make All Unavialable" Button on Montly Show Table
if (!empty($_REQUEST['action']) && $_POST['action'] == "change_availability_month") {
    $data = isset($_POST['data']) ? json_decode($_POST['data']) : [];
    $flag = isset($_POST['flag']) ? intval($_POST['flag']) : [];
    $systemId = isset($_POST['systemId']) ? $_POST['systemId'] : [];
    $startDateTime = isset($_POST['startDate']) ? $_POST['startDate'] : 0;
    // Validate input
    if (empty($data) || empty($systemId) || !is_object($data) || $startDateTime == 0) {
        // Handle invalid input, maybe return an error response
        return;
    }

    $year = date('Y', $startDateTime);
    $month = date('m', $startDateTime);
    $formattedDate = date('Y-m-d', $startDateTime);
    // Get the first day of the month
    $firstDayOfMonth = strtotime('first day of ' . $year . '-' . $month);

    // Get the last day of the month
    $lastDayOfMonth = strtotime('last day of ' . $year . '-' . $month);

    // Format the dates as YYYY-MM-DD
    $firstDayOfMonthFormatted = date('Y-m-d', $firstDayOfMonth);
    $lastDayOfMonthFormatted = date('Y-m-d', $lastDayOfMonth);

    $availableSlots = getAvailabilityData($systemId, $firstDayOfMonthFormatted, $lastDayOfMonthFormatted);
    OnDeleteSpecialTimePeriods($systemId, $firstDayOfMonthFormatted, $lastDayOfMonthFormatted);
     
    $strInsertValues = "";
    $avaialable = $flag == 0 ? 1 : 0; 
    //SystemId, SetDate, FromInMinutes, ToInMinutes, isAvailable
    foreach ($data->dates as $date){
        if (isset($availableSlots[$date])){
            foreach ($availableSlots[$date]["timeslot"] as $slot) {
                if ($slot['isAvailable'] == $flag){
                    $strInsertValues = $strInsertValues."(".$systemId.",'".$date."',".$slot['FromInMinutes'].",".$slot['ToInMinutes'].",".$avaialable."),";
                    //insertIntoUnavailableBookingPeriods($systemId, $date, $slot['FromInMinutes'], $slot['ToInMinutes'], !$flag);
                }
            }
        }
    }
    $str = substr($strInsertValues, 0, -1);
    InsertManyInSpecialTimePeriods($str);
    // $formattedDate = date('Y-m-d', $date);
     
    // // Get the day of the week (0 for Sunday, 1 for Monday, ..., 6 for Saturday)
    // $dayOfWeek = date('w', $date);
    // $availableTimeslot = getAvailableInfoInOneWeekRange($systemId, $formattedDate, $formattedDate);
    // //$availablesInfo = getTimePeriodsByDay($systemId, $formattedDate, $formattedDate);
    // //print_r($formattedDate);
    // foreach ($availableTimeslot[$dayOfWeek]["timeslot"] as $slot) {
    //     if ($slot['isAvailable'] == $flag){
    //         insertIntoUnavailableBookingPeriods($systemId, $date, $slot['FromInMinutes'], $slot['ToInMinutes'], !$flag);
    //     }
    // }
    echo json_encode(1);
    // Exit after processing
    exit();
}


?>