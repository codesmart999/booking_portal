<?php
// Include your database connection file if not already included
require_once('header.php');
// Get the database connection
$db = getDBConnection();

if (!isset($systemId) || !isset($startDate)){ //exception
    header('Location: '. SECURE_URL . ADMIN_INDEX, true, 301);
    exit; // Make sure to exit after redirection to prevent further script execution
}

$filterFlag = 0x0; //Show Whole Day.
if (isset($_GET['filter'])) {
    $filterFlag = $_GET['filter'];
}
$filter_array = getFilterArray($filterFlag);

$isGroupBooking = 0;
if (isset($_GET['groupBooking'])) {
    $isGroupBooking = $_GET['groupBooking'];
}

//get BookedInfo
$arrBookingsOfSystem = getBookedInfo($systemId, $startDate, $endDate);

//count bookings
$bookCount = getNumberOfBookings($arrBookingsOfSystem);

//Get available/unavailable timesolt by week
$availableSlots =  ($showFlag == MONTHLY_SHOWING_MODE) ? getAvailabilityDataWithCounts($systemId, $startDate, $endDate) : getAvailableInfoInOneWeekRange($systemId, $startDate, $endDate);

//__debug($availableSlots);
// Iterate over each date in the date range
$currentDateTime = strtotime(date('Y-m-d'));
$startDateTime = strtotime($startDate);
$tmpStartDateTime = $startDateTime;
$endDateTime = strtotime($endDate);

$tableTitle = formatDateRange($startDate, $endDate, $showFlag);

$weekDates = getWeekDates($endDate);

//building TABLE HEADER part
if ($showFlag == MONTHLY_SHOWING_MODE){
    //For Make All Available/Make All Unavaiable Button Event on Monthly show list 
    $availableDatedInMonth = (isset($availableSlots[1])) ? json_encode($availableSlots[1]) : json_encode(array());
    $unavailableDatedInMonth = (isset($availableSlots[0])) ? json_encode($availableSlots[0]) : json_encode(array());
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
                    <a href="javascript:changeAvailabilityMonth(0)" target="_self" onclick="return confirm('This will make ALL future booking periods available in this month view.\nNo change will be made to periods already booked.\nAre you sure you want to proceed?')" class="link">Make All Available</a>&nbsp;&nbsp;
                    <a href="javascript:changeAvailabilityMonth(1)" target="_self" onclick="return confirm('This will make ALL future booking periods unavailable in this month view.\nNo change will be made to periods already booked.\nAre you sure you want to proceed?')" class="link">Make All Unavailable</a>&nbsp;&nbsp;
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
                    <a class="image-links" href="javascript:onClickFilter(0)" target="_self"><img title="Show Whole Day" border="0" src="/images/day_blue<?php if (in_array(0, $filter_array)) echo "_tick2";?>.jpg"></a>
                    <a class="image-links" href="javascript:onClickFilter(1)" target="_self"><img title="Show/Hide Past Times" border="0" src="/images/day_yellow<?php if (in_array(1, $filter_array)) echo "_tick1";?>.jpg"></a>
                    <a class="image-links" href="javascript:onClickFilter(2)" target="_self"><img title="Show/Hide Unavailable Times" border="0" src="/images/day_orange<?php if (in_array(2, $filter_array)) echo "_tick1";?>.jpg"></a>
                    <a class="image-links" href="javascript:onClickFilter(4)" target="_self"><img title="Show/Hide Default Unavailable Times" border="0" src="/images/day_pink<?php if (in_array(4, $filter_array)) echo "_tick1";?>.jpg"></a>
                    <a class="image-links" href="javascript:onClickFilter(8)" target="_self"><img title="Show/Hide Bookings" border="0" src="/images/day_green<?php if (in_array(8, $filter_array)) echo "_tick1";?>.jpg"</a>
                    <a class="image-links" href="javascript:onClickFilter(16)" target="_self"><img title="Show/Hide Available Times" border="0" src="/images/day_white<?php if (in_array(16, $filter_array)) echo "_tick1";?>.jpg"></a>
                </span>
                <span style="float: right;" >
                    &nbsp;&nbsp;<font size="2" face="Arial" color="#0000FF">&nbsp; •
                    </font><font size="2" face="Arial" color="#FFFFFF"> <a href="#" target="_self" onclick = "onGroupBooking()"><?php if($isGroupBooking) echo "Single&nbsp;Bookings"; else echo "Group&nbsp;Bookings";?></a></font><font size="2" face="Arial" color="#0000FF">&nbsp; &nbsp;
                    
                    <!-- </font><font size="2" face="Arial" color="#FFFFFF"> <a href="#">Change&nbsp;Display</a></font><font size="2" face="Arial" color="#0000FF">&nbsp; &nbsp; -->
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

    $dateYMD = date('Y-m-d', $startDateTime); //change 17000000 -> 2024-03-22
    //$dateYMDString = json_encode($dateYMD);

    if ($showFlag == MONTHLY_SHOWING_MODE){//if you click on Month(JAN -DEC) or Year calendar
        $i += 1;
        $dayOfWeek = date("D", $startDateTime); //This will output the day of the week (e.g., "Sat")
        $weekday = date('N', $startDateTime) % 7;

        $availableSlotCount = 0;
        $unavailableSlotCount = 0;

        $bookedCount = isset($arrBookingsOfSystem[$dateYMD]) ? count($arrBookingsOfSystem[$dateYMD]) : 0;
        
        if (isset($availableSlots[$dateYMD])) {
            $availableSlotCount = $availableSlots[$dateYMD]["nAvailable"];
            $unavailableSlotCount = $availableSlots[$dateYMD]["nUnavailable"];
        }

        $availableSlotCount -= $bookedCount;

        //some logic to calc because there is special booking periods

        $availableSlotCount = ($availableSlotCount < 0) ? 0 : $availableSlotCount; //exception handling;
        $unavailableSlotCount = ($unavailableSlotCount < 0) ? 0 : $unavailableSlotCount; //exception handling;

        $dateTdStr = ($dateYMD == $todayDate)? '<td width="8%" bgcolor="yellow" valign="top" align="left"><font face="Arial" size="2">&nbsp;<a target="_self" href="#" onclick="redirectToSelectedDate(&quot;'.$dateYMD.'&quot;)">' . $i . ' ' . $dayOfWeek . '</a></font></td>' :  '<td width="8%" bgcolor="FFFFFF" valign="top" align="left"><font face="Arial" size="2">&nbsp;<a target="_self" href="#" onclick="redirectToSelectedDate(&quot;'.$dateYMD.'&quot;)">' . $i . ' ' . $dayOfWeek . '</a></font></td>';
        $availableTdStr = $availableSlotCount > 0 ? '<td width="12%" bgcolor="FFFFFF" valign="top" align="left"><font face="Arial" size="2">&nbsp;' . $availableSlotCount . '&nbsp;<a target="_self" href="#" onclick="changeAvailabilityDateRange(&quot;'.$startDateTime.'&quot;,1)">Change</a></font></td>' : '<td width="12%" bgcolor="FFFFFF" valign="top" align="left"><font face="Arial" size="2">&nbsp;&nbsp;</font></td>';
        $unavailableTdStr = $unavailableSlotCount > 0 ? '<td width="12%" bgcolor="FFE2A6" valign="top" align="left"><font face="Arial" size="2">&nbsp;' . $unavailableSlotCount . '&nbsp;<a target="_self" href="#" onclick="changeAvailabilityDateRange(&quot;'.$startDateTime.'&quot;,0)">Change</a></font></td>' : '<td width="12%" bgcolor="FFFFFF" valign="top" align="left"><font face="Arial" size="2">&nbsp;&nbsp;</font></td>'; 
        $bookTdStr = $bookedCount > 0 ? '<td width="39%" bgcolor="CCFFCC" valign="top" align="left"><font face="Arial" size="2">&nbsp;'.$bookedCount.'&nbsp;&nbsp;<a target="_self" href="#" onclick="redirectToSelectedDate(&quot;'.$dateYMD.'&quot;)">Book/Cancel</a>&nbsp;</font></td>' : '<td width="39%" bgcolor="FFFFFF" valign="top" align="left"><font face="Arial" size="2">&nbsp;&nbsp;&nbsp;&nbsp;<a target="_self" href="#" onclick="redirectToSelectedDate(&quot;'.$dateYMD.'&quot;)">Book</a>&nbsp;</font></td>';
        echo '<tr id ="monthly_show_body_tr">' . $dateTdStr . $bookTdStr . $availableTdStr . $unavailableTdStr . '</tr>';

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
       ?>
            <tr id="<?php echo $startDateTime?>">
                <td width="50%" colspan="1" bgcolor="#FFFFFF" valign="top">
                    <font face="arial" size="2">
<?php       
            $index = 0;
            // Time slots are available for this weekday
            //[0] => Array (
            // [0] => 1
            // [1] => 0
            // ["timeslot"] => Array (
            //     [FromInMinutes] => 900
            //     [ToInMinutes] => 960
            //     [isAvailable] => 0
            // )
            // )
            $availableSlotCount = isset($availableSlots[$weekday][1]) ? $availableSlots[$weekday][1] : 0;
            $unavailableSlotCount = isset($availableSlots[$weekday][0]) ? $availableSlots[$weekday][0] : 0;
            $halfIndex = ceil(($availableSlotCount + $unavailableSlotCount) / 2) + 1;
            if (isset($availableSlots[$weekday]["timeslot"])){//exception handling
                
                $bookedInfo = [];
                foreach ($availableSlots[$weekday]["timeslot"] as $slot) {
                    $index += 1;

                    if ($index == $halfIndex) {
                        echo '</font></td>';
                        echo '<td width="50%" bgcolor="#FFFFFF" valign="top"><font face="arial" size="2">';
                    } 
                    $fromMinutes = $slot['FromInMinutes'];
                    $toMinutes = $slot['ToInMinutes'];
                    $isAvailable = $slot['isAvailable'];

                    $timeSlot = "$fromMinutes-$toMinutes";
                    $timeRender = formatTimeRange($fromMinutes, $toMinutes);
                    
                    $background_color = "FFFFFF"; // White for available
                    $fullName = "";
                    $available = 1; //available
                    // Check if the time slot is booked
                    if (isset($arrBookingsOfSystem[$dateYMD][$timeSlot])) { //booked case

                        if (in_array(8, $filter_array)) //if hide bookings filter case 
                            continue;

                        if (in_array(1, $filter_array)){ //if hide past time filter case 
                            $currentTime = time();
                            if ($startDateTime < $currentTime) 
                                continue;
                        }

                        foreach ($arrBookingsOfSystem[$dateYMD][$timeSlot] as $bookings) {
                            $bookingCode = $bookings["booking_code"];
                            $background_color = "CCFFCC"; //booked color
                            // Time slot is booked
                            $businessName = $bookings["business_name"];
                            $booking_id = $bookings["booking_id"];
                            $customer_id = $bookings["customer_id"];
                            $available = 2; //booked
                            $hasComment = 0;

                            $comments_array = json_decode($bookings["booking_comments"], true);

                            $$hasComment = false;
                            if (is_array($comments_array) && count($comments_array) > 0) {
                                $hasComment = true;
                            }

                            if ($isGroupBooking){
                                if ($arrBookingsOfSystem[$dateYMD][$bookingCode][1] == $toMinutes){
                                
                                    $timeSlot = $arrBookingsOfSystem[$dateYMD][$bookingCode][0]."-".$arrBookingsOfSystem[$dateYMD][$bookingCode][1];
                                    $timeRender = formatTimeRange($arrBookingsOfSystem[$dateYMD][$bookingCode][0], $arrBookingsOfSystem[$dateYMD][$bookingCode][1]);
                                }
                                else{
                                    continue;
                                }
                            }
                        
                            echo '<input type="checkbox" 
                                    name="timeslot" 
                                    date = "'.$startDateTime.'" 
                                    status = "'.$available.'" 
                                    style="margin-top: 5px; margin-left: 3px;" 
                                    value="'.$timeSlot.'">&nbsp;
                                <span 
                                    style="color: ' . generateTextColor($bookingCode) . '">'
                                    .$timeRender.'
                                </span>';
                            echo '<a target="_self" href="#" onclick="bookedClientView('.$customer_id.');"><span class="CustName" style="color: ' . generateTextColor($bookingCode) .'"><i class="fa fa-user"></i>'
                                . $businessName . ' (' . $bookingCode . ')</span><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                            echo '<a target="_self" href="#" onclick="bookedAddComments(&quot;'.$bookingCode.'&quot;);">';
                            if ($hasComment) {
                                echo '<img title="Comment Added" border="0" src="/images/comment.png">
                                    </a>';
                            } else {
                                echo '<img title="Add Comments" border="0" src="/images/nocomment.png">
                                    </a>';
                            }
                            // echo '<a target="_self" href="#" onclick="bookedQA('.$booking_id.');">
                            //         <img title="Questions and Answers" border="0" src="/images/i1.png">
                            //      </a>';
                            echo '<a target="_self" href="#" onclick="bookedViewBookingDetails(&quot;'.$bookingCode.'&quot;);">
                                        <img title="View Booking Details" border="0" src="/images/bookdetail.png">
                                    </a>
                                <br>';
                        }
                    } else {

                        if (empty($isAvailable)) { //unavailable case
                            if (in_array(2, $filter_array)) //if hide unavailable time filter case 
                                continue;
                            if (in_array(1, $filter_array)){ //if hide past time filter case 
                                $currentTime = time();
                                if ($startDateTime < $currentTime) 
                                continue;
                            }
                            if (in_array(4, $filter_array)){ //if hide regular unavaialable time filter case 
                                if ($slot["isAvailable"] == 0) {
                                    if (!isset($slot["isSpecailAvailable"]))
                                        continue;
                                }
                                    
                            }
                            $background_color = "FFE2A6"; //unavailable
                            $available = 0; //unavailable
                            echo '&nbsp;<input type="checkbox" name="timeslot" date = "'.$startDateTime.'" status = "'.$available.'" style="margin-top: 5px" value="'.$fromMinutes.'-'.$toMinutes.'">&nbsp;<span style="background-color: #'.$background_color.'">'.$timeRender.'</span>&nbsp;&nbsp;<br/>';
                        }
                        else {
                            if (in_array(16, $filter_array)) //if hide available time filter case 
                                continue;
                            if (in_array(1, $filter_array)){ //if hide past time filter case 
                                $currentTime = time();
                                if ($startDateTime < $currentTime) 
                                continue;
                            }
                            echo '&nbsp;<input type="checkbox" name="timeslot" date = "'.$startDateTime.'" status = "'.$available.'" style="margin-top: 5px" value="'.$fromMinutes.'-'.$toMinutes.'">&nbsp;<span style="background-color: #'.$background_color.'">'.$timeRender.'</span>&nbsp;&nbsp;<br/>';
                        }
                        
                        
                    }

                    
                }
            }
            echo '</font>';
            echo '</td>';
            echo '</tr>';
        
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
        const newUrl = `${window.location.origin}${window.location.pathname}?SystemId=${<?php echo $systemId; ?>}&startDate=<?php echo $nextDay?>&endDate=<?php echo $nextDay;?>`;
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

    function redirectToSelectedDate(selectedDate){
        
        const newUrl = `${window.location.origin}${window.location.pathname}?SystemId=${<?php echo $systemId; ?>}&startDate=`+selectedDate+`&endDate=`+selectedDate;
        window.location.href = newUrl;
    }

    //flag 'N' ==> make unavailable, 'Y' ==> make available
    function changeAvailabilityDateRange(selectedDate, flag){
        console.log(selectedDate);
        // Send AJAX request only if data is not empty
        $.ajax({
            url: '/api/calendar_ajax_api.php', // URL to your PHP script that handles saving the value
            method: 'POST',
            data: {
                action: 'change_availability_date_range', // Action parameter
                date: selectedDate,
                flag: flag,
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
    }

    function changeAvailabilityMonth(flag){
        // Send AJAX request only if data is not empty
        $.ajax({
            url: '/api/calendar_ajax_api.php', // URL to your PHP script that handles saving the value
            method: 'POST',
            data: {
                action: 'change_availability_month', // Action parameter
                data: flag == 1 ? JSON.stringify({ dates: <?php echo $availableDatedInMonth; ?> }) : JSON.stringify({ dates: <?php echo $unavailableDatedInMonth; ?> }),
                flag: flag,
                startDate: <?php echo $tmpStartDateTime;?>,
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
    }

    function onGroupBooking(){
        const newUrl = `${window.location.origin}${window.location.pathname}?SystemId=${<?php echo $systemId; ?>}&startDate=<?php echo $startDate;?>&endDate=<?php echo $endDate;?>&groupBooking=<?php echo !$isGroupBooking;?>`;
        window.location.href = newUrl;
    }


    function getFilter(inputValue) {
        if (inputValue === 0) {
            return [0];
        }

        const consts = [1, 2, 4, 8, 16];
        const result = [];

        for (let i = consts.length - 1; i >= 0; i--) {
            if (inputValue & consts[i]) {
                result.push(consts[i]);
                inputValue -= consts[i];
            }
        }

        return result;
    }


    function getNextFilterValue(current, input) {
         if (input == 0)
            return 0;
        // Calculate the sum of current and input
        var filter = getFilter(current);

         if (filter.includes(input)) {
            // If 0 is already in the list, exclude it
            const index = filter.indexOf(input);
            filter.splice(index, 1);
        } else {
            // If 0 is not in the list, include it
            filter.push(input);
        }
        console.log( filter.reduce((acc, val) => acc + val, 0));
        return filter.reduce((acc, val) => acc + val, 0);
    }

    function onClickFilter(type) {
        var filter = <?php echo $filterFlag?>;
        const nextFilter = getNextFilterValue(filter, type);
        const newUrl = `${window.location.origin}${window.location.pathname}?SystemId=${<?php echo $systemId; ?>}&startDate=<?php echo $startDate;?>&endDate=<?php echo $endDate;?>&groupBooking=<?php echo $isGroupBooking;?>&filterFlag=?>` + '&filter=' + nextFilter;
        window.location.href = newUrl;
      
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
                var currentTimeMillis = new Date().getTime() / 1000;
                if (dateAttribute <= currentTimeMillis) {
                   
                    // Show alert if the date is in the past
                    alert('This Timeslot is in the past');
                    // Uncheck the checkbox
                    this.checked = false;
                }
            }
        });
    });

</script>