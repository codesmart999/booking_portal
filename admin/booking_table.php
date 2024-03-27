<?php
// Include your database connection file if not already included
require_once('header.php');
// Get the database connection
$db = getDBConnection();

$showFlag = WEEKLY_SHOWING_MODE;

if (!isset($_GET['endDate'])) {
    $showFlag = MONTHLY_SHOWING_MODE;

    // Convert the start date to the first day of the month
    $startDate = date('Y-m-01', strtotime($startDate));

    // Convert the start date to the last day of the month
    $endDate = date('Y-m-t', strtotime($startDate));
}

//if request url is ?systemId=5 
if (!isset($_GET['startDate'])) {
    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d');
}

if ($startDate === $endDate) {
    $showFlag = DAYILY_SHOWING_MODE;
}

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

//count bookings
$bookCount = count($bookingInfo);

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

// Get the earliest available time slot for Monday (weekday 1)
$earliestTimeSlot = PHP_INT_MAX;
foreach ($availableSlots[1] as $slot) {
    if ($slot['FromInMinutes'] < $earliestTimeSlot) {
        $earliestTimeSlot = $slot['FromInMinutes'];
    }
}

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

// Iterate over each date in the date range
$currentDateTime = strtotime(date('Y-m-d'));
$startDateTime = strtotime($startDate);
$endDateTime = strtotime($endDate);

$tableTitle = formatDateRange($startDate, $endDate, $showFlag);


$weekDates = getWeekDates($endDate);
//building TABLE HEADER part
if ($showFlag == MONTHLY_SHOWING_MODE){
?>
    <thead>
        <tr>
            <td style="width:50%; border-right: 0 solid black;" bgcolor="#C5D4F0" valign="top" align="center" colspan="4">
                
                <font face="Arial" size="2" color="#000000">
                    <span class="big-font">
                        <b>
                            <?php echo $tableTitle; ?>
                        </b>
                    </span> - <?php echo ($bookCount == 0) ? 'No' : $bookCount; ?> bookings
                </font>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr id="monthly_show_header">
            <td bgcolor="FFFFFF" colspan="4"> 
                <font face="Arial" size="2" color="#000000">
                    <a href="javascript:changeAvailabilityMonth()" target="main" onclick="return confirm('This will make ALL future booking periods available in this month view.\nNo change will be made to periods already booked.\nAre you sure you want to proceed?')" class="link">Make All Available</a>&nbsp;&nbsp;
                    <a href="javascript:changeAvailabilityMonth()" target="main" onclick="return confirm('This will make ALL future booking periods unavailable in this month view.\nNo change will be made to periods already booked.\nAre you sure you want to proceed?')" class="link">Make All Unavailable</a>&nbsp;&nbsp;
                </font>
            </td>
        </tr>
        <tr>
            <td width="8%" bgcolor="#C5D4F0" valign="top" align="left">
                <font face="Arial" color="#000000" size="2">&nbsp;Date</font>
            </td>
            <td width="39%" bgcolor="#C5D4F0" valign="top" align="left">
                <font face="Arial" color="#000000" size="2">&nbsp;Bookings</font>
            </td>
            <td width="12%" bgcolor="#C5D4F0" valign="top" align="left">
                <font face="Arial" color="#000000" size="2">&nbsp;Available</font>
            </td>
            <td width="12%" bgcolor="#C5D4F0" valign="top" align="left">
                <font face="Arial" color="#000000" size="2">&nbsp;Unavailable</font>
            </td>
        </tr>
    </tbody>
<?php 
} else { //Daily or Weekly showing case
    ?>
    <thead>
        <tr>
            <td style="width:50%; border-right: 0 solid black;" bgcolor="#C5D4F0" valign="top" align="center">
                <font face="Arial" size="2" color="#000000">
                    <span class="big-font">
                        <b>
                            <?php echo $tableTitle; ?>
                        </b>
                    </span> - <?php echo ($bookCount == 0) ? 'No' : $bookCount; ?> bookings
                </font>
            </td>
            <td style="width:50%; border-left: 0 solid black;" bgcolor="#C5D4F0" valign="top" align="center">
                <span style="float: left;" >
                    <a class="image-links" href="#"><img title="Show Whole Day" border="0" src="/images/day_blue_tick2.jpg"></a>
                    <a class="image-links" href="#"><img title="Show/Hide Past Times" border="0" src="/images/day_yellow.jpg"></a>
                    <a class="image-links" href="#"><img title="Show/Hide Unavailable Times" border="0" src="/images/day_orange.jpg"></a>
                    <a class="image-links" href="#"><img title="Show/Hide Default Unavailable Times" border="0" src="/images/day_pink.jpg"></a>
                    <a class="image-links" href="#"><img title="Show/Hide Bookings" border="0" src="/images/day_green.jpg"></a>
                    <a class="image-links" href="#"><img title="Show/Hide Available Times" border="0" src="/images/day_white.jpg"></a>
                </span>
                <span style="float: right;" >
                    &nbsp;&nbsp;<font size="2" face="Arial" color="#0000FF">&nbsp; •
                    <!-- </font><font size="2" face="Arial" color="#FFFFFF"> <a href="#">Group&nbsp;Bookings</a></font><font size="2" face="Arial" color="#0000FF">&nbsp; &nbsp;• 
                    </font><font size="2" face="Arial" color="#FFFFFF"> <a href="#">Change&nbsp;Display</a></font><font size="2" face="Arial" color="#0000FF">&nbsp; &nbsp;•  -->
                    </font><font size="2" face="Arial" color="#FFFFFF"> <a href="#" onmouseover="showLocation(true)" onmouseout="showLocation(false)">Show Location</a>
                    </font>
                </span>
            </td>
        </tr>
    </thead>
    <tbody>
<?php 
}
$i = 0;
while ($startDateTime <= $endDateTime) {
    
    if ($showFlag == MONTHLY_SHOWING_MODE){//if you click on Month(JAN -DEC) or Year calendar
        $i += 1;
        $dayOfWeek = date("D", $startDateTime); //This will output the day of the week (e.g., "Sat")
        $weekday = date('N', $startDateTime) % 7;

        $availableSlotCount = 0;
        $unavailableSlotCount = 0;
        $bookedCount = 0;

        if (isset($availableSlots[$weekday])) {
            $availableSlotCount = count($availableSlots[$weekday]);
        } else {
            $unavailableSlotCount = 20;
        }

        $availableStr = $availableSlotCount > 0 ? '<td width="12%" bgcolor="FFFFFF" valign="top" align="left"><font face="Arial" size="2">&nbsp;' . $availableSlotCount . '&nbsp;<a target="main" href="javascript:changeAvailabilityDateRange("","N")">Change</a></font></td>' : '<td width="12%" bgcolor="FFFFFF" valign="top" align="left"><font face="Arial" size="2">&nbsp;&nbsp;</font></td>';
        $unavailableStr = $unavailableSlotCount > 0 ? '<td width="12%" bgcolor="FFE2A6" valign="top" align="left"><font face="Arial" size="2">&nbsp;' . $unavailableSlotCount . '&nbsp;<a target="main" href="javascript:changeAvailabilityDateRange("","Y")">Change</a></font></td>' : '<td width="12%" bgcolor="FFFFFF" valign="top" align="left"><font face="Arial" size="2">&nbsp;&nbsp;</font></td>'; 

        echo '<tr id ="monthly_show_body_tr">
                <td width="8%" bgcolor="FFFFFF" valign="top" align="left"><font face="Arial" size="2">&nbsp;<a target="main" href="amenux.asp?dDate=4/30/2024">' . $i . ' ' . $dayOfWeek . '</a></font></td>
                <td width="39%" bgcolor="FFFFFF" valign="top" align="left"><font face="Arial" size="2">&nbsp;&nbsp;&nbsp;&nbsp;<a target="main" href="amenux.asp?dDate=4/30/2024">Book</a>&nbsp;</font></td>' . $availableStr . $unavailableStr . '</tr>';

    } else { //if you click on Weeks calendary / Month calendar
        
        $weekday = date('N', $startDateTime) % 7; // Get the weekday (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
        if ($showFlag == WEEKLY_SHOWING_MODE){
            if ($i == 0)
                echo '<tr id="trHeader"><td colspan="2">' . date('l, F j, Y', $startDateTime) . '</td></tr>';
            else {
?>
                <tr id="trHeader">
                    <td valign="middle" colspan="2">
                        <input id="doChangeAvail" style="float: left;" data-dochange="false" type="submit" value="Change Availability" name="action" class="buttons" onclick="return changeAvailability(<?php echo strtotime('-1 day', $startDateTime);?>)">
                        &nbsp;&nbsp;
                        <span style="text-align: center;">
                            <?php echo date('l, F j, Y', $startDateTime); ?>
                        </span>
                        <span style="float: right; display: flex;">
                            <a href="#" onclick="prevWeek()">
                                <img border="0" title="Previous Week" src="/images/arrowhead_week_astern.gif" align="middle">
                            </a>
                            <img border="0" src="/images/w.gif" align="middle">
                            <a href="#" onclick="nextWeek()">
                                <img border="0" title="Next Week" src="/images/arrowhead_week_ahead.gif" align="middle">
                            </a>
                        </span>
                    </td>
                </tr>
<?php
            }
            $i += 1;
        }
        if (isset($availableSlots[$weekday])) {
?>          
            <tr id="<?php echo $startDateTime?>">
                <td width="50%" colspan="1" bgcolor="#FFFFFF" valign="top">
                    <font face="arial" size="2">
<?php       
            $index = 0;
            // Time slots are available for this weekday
            $availableSlotCount = count($availableSlots[$weekday]);
            foreach ($availableSlots[$weekday] as $slot) {
                $index += 1;
                if ($index == $availableSlotCount/2+1) {
                    echo '</font></td>';
                    echo '<td width="50%" bgcolor="#FFFFFF" valign="top"><font face="arial" size="2">';
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
                $available = 1; //available
                // Check if the time slot is booked
                if (isset($bookingInfo[date('Y-m-d', $startDateTime)][$timeSlot])) { //booked case
                    $background_color = "CCFFCC"; //booked color
                    // Time slot is booked
                    $fullName = $bookingInfo[date('Y-m-d', $startDateTime)][$timeSlot];
                    $available = 2; //booked
                }

                if (isset($availablesInfo[date('Y-m-d', $startDateTime)][$timeSlot]) && $availablesInfo[date('Y-m-d', $startDateTime)][$timeSlot] == 0) { //unavailable case
                    $background_color = "FFE2A6"; //unavailable
                    $available = 0; //unavailable
                }
                echo '&nbsp;<input type="checkbox" name="timeslot" date = "'.$startDateTime.'" status = "'.$available.'" style="margin-top: 5px" value="'.$fromMinutes.'-'.$toMinutes.'">&nbsp;<span style="background-color: #'.$background_color.'">'.$timeRender.'</span>&nbsp;'.$fullName.'&nbsp;<br/>';
            }
            echo '</font>';
            echo '</td>';
            echo '</tr>';
        } else {
            ?>
            <tr id="<?php echo $startDateTime?>">
            <td width="50%" colspan="1" bgcolor="#FFFFFF" valign="top" >
            <font face="arial" size="2">
            <?php
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
                $timeSlot = $minutes.'-'.($minutes+15);

                $startTimeHour = floor($minutes / 60);
                $startTimeMinute = ($minutes % 60);
                $endTimeHour = floor(($minutes + 15) / 60);
                $endTimeMinute = (($minutes + 15) % 60);

                // Format start time
                $startTime = sprintf('%d:%02d', $startTimeHour, $startTimeMinute);

                // Format end time
                $endTime = sprintf('%d:%02d', $endTimeHour, $endTimeMinute);
                // Combine start and end times
                $available = 0;
                
                if (isset($availablesInfo[date('Y-m-d', $startDateTime)][$timeSlot]) && $availablesInfo[date('Y-m-d', $startDateTime)][$timeSlot] == 1) { //available case
                    $background_color = "FFFFFF"; //available
                    $available = 1; //available
                }
                $timeRender = date('g:i A', strtotime($startTime)) . ' - ' . date('g:i A', strtotime($endTime));
                echo '&nbsp;<input type="checkbox" name="timeslot" date = "'.$startDateTime.'" status ="'.$available.'" style="margin-top: 5px"value="'.$minutes.'-'.($minutes + 15).'">&nbsp;<span style="background-color: #'.$background_color.'">'.$timeRender.'</span>&nbsp;&nbsp;<br/>';
            }
            echo '</font>';
            echo '</td>';
            echo '</tr>';
        }
        if ($showFlag == DAYILY_SHOWING_MODE) {
            ?>
            <tr id="trHeader">
                <td valign="middle" colspan="2">
                    <input id="doChangeAvail" style="float: left;" data-dochange="false" type="submit" value="Change Availability" name="action" class="buttons" onclick="return changeAvailability(<?php echo $startDateTime; ?>)">
                    &nbsp;&nbsp;
                    <span style="text-align: center;">
                        <?php echo $dayOfWeek . ", " . $monthName . " " . $dayOfMonth . ", " . $year; ?>
                    </span>
                    <span style="float: right; display: flex;">
                        <a href="#" onclick="prevDay()">
                            <img border="0" title="Previous Day" src="/images/arrowhead_week_astern.gif" align="middle">
                        </a>
                        <img border="0" src="/images/day.gif" align="middle">
                        <a href="#" onclick="nextDay()">
                            <img border="0" title="Next Day" src="/images/arrowhead_week_ahead.gif" align="middle">
                        </a>
                    </span>
                </td>
            </tr>
            <?php
        }
    }
    // Move to the next date
    $startDateTime = strtotime('+1 day', $startDateTime);
}

?>

<script>
    function prevDay() {
        const newUrl = `${window.location.origin}${window.location.pathname}?SystemId=${<?php echo $systemId; ?>}&startDate=<?php echo $prevDay;?>&endDate=<?php echo $prevDay;?>`;
        window.location.href = newUrl;
    }

    function nextDay() {
        const newUrl = `${window.location.origin}${window.location.pathname}?SystemId=${<?php echo $systemId; ?>}&startDate=<?php echo $nextDay?>&endDate=<?php echo $prevDay;?>`;
        window.location.href = newUrl;
    }

    function prevWeek() {
        const newUrl = `${window.location.origin}${window.location.pathname}?SystemId=${<?php echo $systemId; ?>}&startDate=<?php echo  $weekDates['prevWeek']['start']; ?>&endDate=<?php echo $weekDates['prevWeek']['end'];?>`;
        window.location.href = newUrl;
    }
    function nextWeek(){
        const newUrl = `${window.location.origin}${window.location.pathname}?SystemId=${<?php echo $systemId; ?>}&startDate=<?php echo  $weekDates['nextWeek']['start']; ?>&endDate=<?php echo $weekDates['nextWeek']['end'];?>`;
        window.location.href = newUrl;
    }

    function changeAvailability(date) {
        // Select the <tr> element with the specified ID
        var trElement = document.getElementById(date);

        // Check if the <tr> element exists
        if (trElement) {
            // Select all input boxes inside the <tr> element
            var inputBoxes = trElement.querySelectorAll('input[type="checkbox"]:checked');

            // Check if at least one checkbox is checked
            if (inputBoxes.length > 0) {
                // Initialize an array to store data objects
                var data = [];

                // Loop through each checked input box
                inputBoxes.forEach(function(inputBox) {
                    // Get the value, background color, and status attribute value of the checked input box
                    var value = inputBox.value;
                    var backgroundColor = inputBox.nextElementSibling.style.backgroundColor;
                    var status = inputBox.getAttribute('status');

                    // Create a data object with the value, background color, and status
                    var item = { timeSlot: value, status: status };

                    // Push the data object to the array
                    data.push(item);
                });

                // Convert the array to JSON format
                var jsonData = JSON.stringify(data);

                // Print the JSON data to the console
                console.log("JSON Data:", jsonData);

                // Send AJAX request only if data is not empty
                $.ajax({
                    url: '/api/calendar_ajax_api.php', // URL to your PHP script that handles saving the value
                    method: 'POST',
                    data: {
                        action: 'change_availability', // Action parameter
                        date: date,
                        value: data,
                        systemId: <?php echo $systemId; ?>
                    },
                    success: function(response) {
                        // Handle the server response if needed
                        
                        console.log('Toggle value saved successfully.');
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        // Handle errors if the AJAX request fails
                        alert('Error saving value:', error);
                    }
                });
            } else {
                // Alert the user if no booking periods are selected
                alert("Please select at least one Booking Period.");
            }
        } else {
            // Handle the case where the <tr> element with the specified ID does not exist
            console.error("Element with ID " + currentDate + " not found.");
        }

    }

   // Get all checkbox elements
    var checkboxes = document.querySelectorAll('input[type="checkbox"][name="timeslot"]');

    // Attach event listener to each checkbox
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            // Check if checkbox is checked
            if (this.checked) {
                // Get the value of the date attribute
                var dateAttribute = this.getAttribute('date');
                // Compare the dates
                if (dateAttribute <= <?php echo $currentDateTime; ?>) {
                    // Show alert if the date is in the past
                    alert('This Timeslot is in the past');
                    // Uncheck the checkbox
                    this.checked = false;
                }
            }
        });
    });

</script>
