<?php
// Include your database connection file if not already included
require_once('header.php');

// Get the database connection
$db = getDBConnection();

$endDate = date('Y-m-d');

if (isset($_GET['endDate'])) {
    // Extract the value of startDate
    $endDate = $_GET['endDate'];
    
    // Validate and format startDate
    if (!empty($endDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        // Split the date string into year, month, and day
        // Proceed with further processing
    } else {
        $endDate = date('Y-m-d');
    }
}


// Prepare and execute a query to retrieve data from the database for the specified date range and systemId
$sql = "SELECT bookings.BookingId, bookings.BookingDate, bookings.BookingFrom, bookings.BookingTo, customers.FullName
        FROM bookings
        INNER JOIN customers ON bookings.CustomerId = customers.CustomerId
        WHERE bookings.SystemId = ? AND DATE(bookings.BookingDate) BETWEEN ? AND ?";

// Prepare the statement
$stmt = $db->prepare($sql);

// Bind parameters
$stmt->bind_param("iss", $systemId, $startDate, $endDate);

// Execute the statement
$stmt->execute();

// Get the result set
$result = $stmt->get_result();

// Fetch the results
$bookings = $result->fetch_all(MYSQLI_ASSOC);

// Initialize an array to store the booking information
$bookingInfo = [];

// Store the booking information in an array indexed by date and time slot
foreach ($bookings as $booking) {
    $bookingDate = $booking['BookingDate'];
    $bookingFrom = $booking['BookingFrom'];
    $bookingTo = $booking['BookingTo'];
    $fullName = $booking['FullName'];

    // Format the booking time slot
    $bookingTimeSlot = $bookingFrom . '-' . $bookingTo;

    // Store the booking information for the corresponding date and time slot
    $bookingInfo[$bookingDate][$bookingTimeSlot] = $fullName;
}

// Prepare and execute a query to retrieve available time slots for the specified systemId
$sql = "SELECT weekday, FromInMinutes, ToInMinutes, isAvailable
        FROM setting_bookingperiods
        WHERE SystemId = IFNULL(
                    (SELECT SystemId 
                    FROM setting_weekdays 
                    WHERE SystemId = ?
                    LIMIT 1),
                    0)";

$stmt = $db->prepare($sql);
$stmt->bind_param("i", $systemId);
$stmt->execute();
$result = $stmt->get_result();

// Initialize an array to store the available time slots
$availableSlots = [];

// Fetch the booking periods
while ($row = $result->fetch_assoc()) {
    $weekday = $row['weekday'];
    $fromMinutes = $row['FromInMinutes'];
    $toMinutes = $row['ToInMinutes'];
    $isAvailable = $row['isAvailable'];

    // Store the available time slot information
    $availableSlots[$weekday][] = [
        'FromInMinutes' => $fromMinutes,
        'ToInMinutes' => $toMinutes,
        'isAvailable' => $isAvailable
    ];
}

// Get the earliest available time slot for Monday (weekday 1)
$earliestTimeSlot = PHP_INT_MAX;
foreach ($availableSlots[1] as $slot) {
    if ($slot['FromInMinutes'] < $earliestTimeSlot) {
        $earliestTimeSlot = $slot['FromInMinutes'];
    }
}
// Iterate over each date in the date range
$currentDate = strtotime($startDate);
$endDate = strtotime($endDate);

while ($currentDate <= $endDate) {
    $weekday = date('N', $currentDate) % 7; // Get the weekday (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
    echo '<tr id="trHeader"><td colspan="2">' . date('l, F j, Y', $currentDate) . '</td></tr>';
    if (isset($availableSlots[$weekday])) {
        
        echo '<tr>';
        echo '<td width="50%" colspan="1" bgcolor="#FFFFFF" valign="top" >';
        echo '<font face="arial" size="2">';
        $index = 0;
        // Time slots are available for this weekday
        $availableSlotCount = count($availableSlots[$weekday]);
        foreach ($availableSlots[$weekday] as $slot) {
            $index += 1;
            if ($index == $availableSlotCount/2+1) {
                echo '</font>';
                echo '</td>';
                echo '<td width="50%" bgcolor="#FFFFFF" valign="top" >';
                echo '<font face="arial" size="2">';
            } 
            $fromMinutes = $slot['FromInMinutes'];
            $toMinutes = $slot['ToInMinutes'];
            $isAvailable = $slot['isAvailable'];

            $timeSlot = "$fromMinutes-$toMinutes";

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


            $background_color = "FFFFFF"; // White for available
            $fullName = "";
            // Check if the time slot is booked
            if (isset($bookingInfo[date('Y-m-d', $currentDate)][$timeSlot])) {
                $background_color = "CCFFCC";
                // Time slot is booked
                $fullName = $bookingInfo[date('Y-m-d', $currentDate)][$timeSlot];
                
            }
            echo '&nbsp;<input type="checkbox" name="timeslot" style="margin-top: 5px"value="'.$timeRender.'">&nbsp;<span style="background-color: #'.$background_color.'">'.$timeRender.'</span>&nbsp;'.$fullName.'&nbsp;<br/>';
        }
        echo '</font>';
        echo '</td>';
        echo '</tr>';
    } else {
        // No time slots available for this weekday
        echo '<tr>';
        echo '<td width="50%" colspan="1" bgcolor="#FFFFFF" valign="top" >';
        echo '<font face="arial" size="2">';
        $index = 0; 
        // Render the entire day as unavailable in 15-minute intervals
        for ($minutes = 480; $minutes < 1080; $minutes += 15) {
            $index += 1;
            if ($index == 21) {
                echo '</font>';
                echo '</td>';
                echo '<td width="50%" bgcolor="#FFFFFF" valign="top" >';
                echo '<font face="arial" size="2">';
            } 
            $background_color = "FFE2A6"; // Light yellow for unavailable

            // Calculate start and end times
            $startTimeHour = floor($minutes / 60);
            $startTimeMinute = ($minutes % 60);
            $endTimeHour = floor(($minutes + 15) / 60);
            $endTimeMinute = (($minutes + 15) % 60);

            // Format start time
            $startTime = sprintf('%d:%02d', $startTimeHour, $startTimeMinute);

            // Format end time
            $endTime = sprintf('%d:%02d', $endTimeHour, $endTimeMinute);
            // Combine start and end times
            $timeRender = date('g:i A', strtotime($startTime)) . ' - ' . date('g:i A', strtotime($endTime));
            echo '&nbsp;<input type="checkbox" name="timeslot" style="margin-top: 5px"value="'.$startTime.'-'.$endTime.'">&nbsp;<span style="background-color: #'.$background_color.'">'.$timeRender.'</span>&nbsp;&nbsp;<br/>';;
        }
    }
    // Move to the next date
    $currentDate = strtotime('+1 day', $currentDate);
}

?>
