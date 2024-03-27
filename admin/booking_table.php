<?php
// Include your database connection file if not already included
require_once('header.php');

// Get the database connection
$db = getDBConnection();

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

// Bind result variables
$stmt->bind_result($bookingId, $bookingDate, $bookingFrom, $bookingTo, $fullName);

// Initialize an array to store the booking information
$bookings = [];

// Fetch the results
while ($stmt->fetch()) {
    $bookings[] = [
        'BookingId' => $bookingId,
        'BookingDate' => $bookingDate,
        'BookingFrom' => $bookingFrom,
        'BookingTo' => $bookingTo,
        'FullName' => $fullName
    ];
}

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
$availableSlots = [];
// Fetch the booking periods
while ($stmt->fetch()) {
    // Store the available time slot information
    $availableSlots[$weekday][] = [
        'FromInMinutes' => $fromMinutes,
        'ToInMinutes' => $toMinutes,
        'isAvailable' => $isAvailable
    ];
}


$bookCount = count($bookingInfo);

// Get the earliest available time slot for Monday (weekday 1)
$earliestTimeSlot = PHP_INT_MAX;
foreach ($availableSlots[1] as $slot) {
    if ($slot['FromInMinutes'] < $earliestTimeSlot) {
        $earliestTimeSlot = $slot['FromInMinutes'];
    }
}
// Iterate over each date in the date range
$currentDate = strtotime($startDate);
$endDateTime = strtotime($endDate);

$tableTitle = formatDateRange($startDate, $endDate);
?>
<thead>
        <tr>
            <td style="width:50%;  border-right: 0 solid black;" bgcolor="#C5D4F0" valign="top" align="center" >
                
                <font face="Arial" size="2" color="#000000">
                    <span class="big-font">
                        <b>
                        <?php echo $tableTitle; ?>
                        </b>
                    </span> - <?php echo $bookCount; ?> bookings
                </font>
            </td>
            <td style="width:50%; border-left: 0 solid black;" bgcolor="#C5D4F0" valign="top" align="center">
                <span style="float: left;">
                    <a class="image-links" href="#"><img title="Show Whole Day" border="0" src="/images/day_blue_tick2.jpg"></a>
                    <a class="image-links" href="#"><img title="Show/Hide Past Times" border="0" src="/images/day_yellow.jpg"></a>
                    <a class="image-links" href="#"><img title="Show/Hide Unavailable Times" border="0" src="/images/day_orange.jpg"></a>
                    <a class="image-links" href="#"><img title="Show/Hide Default Unavailable Times" border="0" src="/images/day_pink.jpg"></a>
                    <a class="image-links" href="#"><img title="Show/Hide Bookings" border="0" src="/images/day_green.jpg"></a>
                    <a class="image-links" href="#"><img title="Show/Hide Available Times" border="0" src="/images/day_white.jpg"></a>
                </span>
                <span style="float: right;">
                    &nbsp;&nbsp;<font size="2" face="Arial" color="#0000FF">&nbsp; •

                    <!-- </font><font size="2" face="Arial" color="#FFFFFF"> <a href="#">Group&nbsp;Bookings</a></font><font size="2" face="Arial" color="#0000FF">&nbsp; &nbsp;• 

                    </font><font size="2" face="Arial" color="#FFFFFF"> <a href="#">Change&nbsp;Display</a></font><font size="2" face="Arial" color="#0000FF">&nbsp; &nbsp;•  -->
                    </font><font size="2" face="Arial" color="#FFFFFF"> <a href="#" onmouseover="showLocation(true)" onmouseout="showLocation(false)">Show Location</a>

                    </font>
                <span>
            </td>
        </tr>
    </thead>
    <tbody>
<?php 
while ($currentDate <= $endDateTime) {
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
        echo '</font>';
        echo '</td>';
        echo '</tr>';
    }
    // Move to the next date
    $currentDate = strtotime('+1 day', $currentDate);
}



?>
