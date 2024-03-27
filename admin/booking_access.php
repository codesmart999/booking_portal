<?php 

$menu = "calendar_page";
require_once('header.php');


if(isset($_SESSION['User'])) {
    // Assuming $user contains the user data retrieved from the session
    $user = $_SESSION['User'];
    
    // Assuming $administrator is a specific field in the $user data
    $administratorName = $user['Username'];
}

// Get the SystemId from the URL parameter
$systemId = isset($_GET['SystemId']) ? $_GET['SystemId'] : null;

if (!isset($systemId) || !isset($administratorName)) {
    // Redirect the user to the desired location
    header('Location: '. SECURE_URL . ADMIN_INDEX, true, 301);
    exit; // Make sure to exit after redirection to prevent further script execution
}


if ($systemId !== null) {
    // Retrieve system name from the database using $systemId
    $systemName = ""; // Initialize variable to store system name
    $db = getDBConnection(); // Assuming you have a function to establish DB connection
    $street = "";
    // Prepare and execute query to fetch system name based on SystemId
    $query = "SELECT FullName, Street FROM systems WHERE SystemId = ?";
    $statement = $db->prepare($query);
    $statement->bind_param("i", $systemId);
    $statement->execute();
    $statement->bind_result($systemName, $street);
    $statement->fetch();
    $statement->close();
}

$array_months = [
    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
];

$year = isset($_GET['year']) ? intval($_GET['year']): date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$day = isset($_GET['day']) ? intval($_GET['day']) : date('d');

if ($year === 0 || $month === 0 || $day ===0 ) {//undefined case: exception handling
    // Set a default value (current year)
    $year = date('Y');
    $month = date('m');
    $day = date('d');
}

$timestamp = mktime(0, 0, 0, $month, $day, $year);

// Get the name of the month (e.g., March)
$monthName = date('F', $timestamp);

// Get the name of the day of the week (e.g., Wednesday)
$dayOfWeek = date('l', $timestamp);

// Get the day of the month without leading zeros (e.g., 27)
$dayOfMonth = date('j', $timestamp);

$current_year = date('Y'); // Get the current year
$current_month = date('n'); // Get the current month without leading zeros
$current_day = date('d'); // Get the current day
$array_weeks_in_month = cal_weeks_in_month($year, $month);
?>
<div class="dropdown" onmouseover="showPopupMenu()" onmouseout="hidePopupMenu()">
  <div class="dropdown-menu" id="popupMenu">
    <a class="dropdown-item" href="#">Manage Time</a>
    <a class="dropdown-item" href="#">Manage Booking</a>
  </div>
</div>
<div class="container-fluid">
    <div class="row">
        <!-- Administrator and System Info -->
        <div class="col-md-12" bgcolor="#FFFFFF" valign="top" align="left">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr bgcolor="#FFFFFF" style="border-size: 0px;">
                    <td bgcolor="#FFFFFF" valign="top" width="20%" align="left" style="white-space: nowrap">
                        <font face="Arial" color="#000000" size="2"><strong>Administrator:</strong> <?php echo $administratorName; ?></font>
                    </td>
                    <td bgcolor="#FFFFFF" valign="top" width="80%" style="text-align: right">
                        <font face="Arial" color="#000000" size="2"><b>
                            <span title="<?php echo $systemName; ?>" style="font-size: 14px;"><?php echo $systemName; ?></span>
                            &nbsp;&nbsp;&nbsp;
                            <a target="main" href="#" onclick="redirectToToday()" style="color: blue;" onmouseover="this.style.color='red';" onmouseout="this.style.color='blue';" onmouseover="window.status='Calendar View for Today';return true" onmouseout="window.status='';return true">Today</a>
                            <a href="javascript:popUp('options/options_services_display.asp');" style="color: blue;" onmouseover="this.style.color='red';" onmouseout="this.style.color='blue';">Services List</a>&nbsp; <!-- Updated link -->
                            <a target="main" href="options/options.asp" style="color: blue;" onmouseover="this.style.color='red';" onmouseout="this.style.color='blue';" onmouseover="window.status='Access Options Menu';return true" onmouseout="window.status='';return true">Options</a>&nbsp; 
                        </font>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- Left Sidebar (Calendar) -->
        <div class="col-md-3 col-sm-12 calendar-sidebar demo-calendar" style="border-color:  white; padding-top:0px; padding: 0px;">
        <div id="locationText">Location Address <span class="location-address"><?php echo $street;?></span></div>
            <div id="calendarWidget">
                
                <div class="row">
                    <div class="col-md-12" style = "padding: 0px 2px;">
                        <div class="" id="demo-calendar-ajax">
                            <script>
                                var $el = $('#demo-calendar-ajax');
                                $(document).ready(function() {
                                    $("#demo-calendar-ajax").zabuto_calendar({
                                        year: <?php echo $year; ?>,
                                        month: <?php echo $month; ?>,
                                        navigation_prev: false,
                                        week_starts: 'sunday',
                                        navigation_next: false,
                                        today_markup: '<span class="month-calendar-today">[day]</span>',
                                        classname: 'table clickable',
                                        ajax: '/api/calendar_api.php'
                                    });
                                    
                                });
                                $el.on('zabuto:calendar:init', function () {
                                //writeToEventLog('zabuto:calendar:init');
                                });

                                $el.on('zabuto:calendar:goto', function (e) {
                                //writeToEventLog('zabuto:calendar:goto' + ' year=' + e.year + ' month=' + e.month);
                                });

                                $el.on('zabuto:calendar:day', function (e) {
                                    console.log(e.value);
                                    const parts = e.value.split("-"); // Split the string by "-"
                                    const year = parseInt(parts[0]); // Extract the year (convert to integer)
                                    const month = parseInt(parts[1]); // Extract the month (convert to integer)
                                    const day = parseInt(parts[2]); // Extract the day (convert to integer)

                                    var newUrl = window.location.origin + window.location.pathname + "?SystemId=<?php echo $systemId?>+&year=" + year + "&month="+month+"&day="+day;
                                    window.location.href = newUrl;
                                    
                                });         
                            </script>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" style = "padding: 0px 2px;">
                        <!-- Table -->
                        <table class="table table-bordered weeks-in-month">
                            <thead>
                                <tr>
                                    <th scope="col" colspan="4" class="table-title">Weeks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                echo '<tr>';
                                foreach ($array_weeks_in_month as $index => $range) {
                                    $startDay = date('d', strtotime($range['start']));
                                    $endDay = date('d', strtotime($range['end']));
                                    $weekStart = strtotime($range['start']);
                                    $weekEnd = strtotime($range['end']);
                                    $currentDate = strtotime(date('Y-m-d'));
                                    $class = '';
                                    if ($currentDate >= $weekStart && $currentDate <= $weekEnd) {
                                        $class = 'highlight-week';
                                    }
                                    if ($index == 4) {
                                        echo '</tr><tr>';
                                        echo '<td class="week-number ' . $class . '" data-week-from="' . $range['start'] . '" data-week-to="' . $range['end'] . '">' . $startDay . '-' . $endDay . '</td><td colspan="3"></td></tr>';
                                    } else {
                                        echo '<td class="week-number ' . $class . '" data-week-from="' . $range['start'] . '" data-week-to="' . $range['end'] . '">' . $startDay . '-' . $endDay . '</td>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" style = "padding: 0px 2px;">
                        <!-- Months Table -->
                        <table class="table table-bordered months-in-year">
                            <thead>
                                <tr>
                                    <th scope="col" colspan="6" class="table-title">Months</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                echo '<tr>';
                                foreach ($array_months as $index => $month) {
                                    $class = '';
                                    if ($current_year == $year && $current_month == $index + 1) {
                                        $class = 'highlight-month'; // Add highlight class if it's the current month and year
                                    }
                                    echo '<td class="month-name ' . $class . '" data-year="' . $year . '" data-month="' . ($index + 1) . '">' . $month . '</td>';
                                    if (($index + 1) % 6 == 0) {
                                        echo '</tr><tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" style = "padding: 0px 2px;">
                        <!-- Years Table -->
                        <table class="table table-bordered years-table">
                            <thead>
                                <tr>
                                    <th scope="col" colspan="4" class="table-title">Years</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php                           
                                    echo '<tr>';
                                    for ($i = $year - 1; $i <= $year + 2; $i++) {
                                        $class = ($i == $current_year) ? 'highlight-year' : ''; // Add highlight class to the current year
                                        echo '<td class="year-number ' . $class . '" data-year="' . $i . '">' . $i . '</td>';
                                    }
                                    echo '</tr>';
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content (Time Slots) -->
        <div class="col-md-9 col-sm-12 time-slots-sidebar">
            <!-- Code from the real site -->
            
             <table id="timeSlotsTable" width="100%" table-bordered cellpadding="2" cellspacing="1" bgcolor="#000080">
                <thead>
                    <tr colspan="2">
                        <td width="100%" bgcolor="#C5D4F0" valign="top" align="center" colspan="1">
                            <font face="Arial" size="2" color="#000000">
                                <span class="big-font">
                                    <b><?php echo $dayOfWeek . ", " . $monthName . " " . $dayOfMonth . ", " . $year; ?></b>
                                </span> - 7 bookings
                            </font>
                            <span style="float: right">
                                <a class="image-links" href="#"><img title="Show Whole Day" border="0" src="/images/day_blue_tick2.jpg"></a>
                                <a class="image-links" href="#"><img title="Show/Hide Past Times" border="0" src="/images/day_yellow.jpg"></a>
                                <a class="image-links" href="#"><img title="Show/Hide Unavailable Times" border="0" src="/images/day_orange.jpg"></a>
                                <a class="image-links" href="#"><img title="Show/Hide Default Unavailable Times" border="0" src="/images/day_pink.jpg"></a>
                                <a class="image-links" href="#"><img title="Show/Hide Bookings" border="0" src="/images/day_green.jpg"></a>
                                <a class="image-links" href="#"><img title="Show/Hide Available Times" border="0" src="/images/day_white.jpg"></a>

                                &nbsp;&nbsp;<font size="2" face="Arial" color="#0000FF">&nbsp; •

                                </font><font size="2" face="Arial" color="#FFFFFF"> <a href="ddaction.asp?action=groupbookings&amp;d=2024-11-11&amp;endate=2024-11-11">Group&nbsp;Bookings</a></font><font size="2" face="Arial" color="#0000FF">&nbsp; &nbsp;• 

                                </font><font size="2" face="Arial" color="#FFFFFF"> <a href="ddaction.asp?action=changedisplay&amp;d=2024-11-11&amp;endate=2024-11-11">Change&nbsp;Display</a></font><font size="2" face="Arial" color="#0000FF">&nbsp; &nbsp;• 
                                </font><font size="2" face="Arial" color="#FFFFFF"> <a href="#" onmouseover="showLocation(true)" onmouseout="showLocation(false)">Show Location</a>

                                </font>
                            </span>
                        </td>
                    </tr>
                </thead>
                <tbody colspan = "2">
                    <?php
                    $startTime = strtotime("8:00 AM");
                    $endTime = strtotime("1:00 PM");
                    $interval = 5 * 60; // 5 minutes in seconds
                    echo '<tr colspan="2">';
                    echo '<td width="50%" colspan="1" bgcolor="#FFFFFF" valign="top" >';
                    echo '<font face="arial" size="2">';
                    while ($startTime <= $endTime) {
                        $timeSlotStart = date("g:i A", $startTime);
                        $startTime += $interval;
                        $timeSlotEnd = date("g:i A", $startTime);

                        // Generate a random number to represent the availability
                        $random = mt_rand(1, 3);
                        $background_color = "";
                        $business_name = "";
                        $link = "";

                        // Set background color and business name based on availability
                        switch ($random) {
                            case 1:
                                $background_color = "FFFFFF"; // White for available
                                break;
                            case 2:
                                $background_color = "FFE2A6"; // Light yellow for unavailable
                                break;
                            case 3:
                                $background_color = "CCFFCC"; // Light green for booked
                                $business_name = " - Business Name"; // Add the business name for booked slots
                                $link = '<a href="#">MB</a>'; // Add the "MB" link for booked slots
                                break;
                        }

                        // Output the time slot item with appropriate background color, checkbox, MB link, and business name
                       
                        echo '&nbsp;<input type="checkbox" name="timeslot" style="margin-top: 5px"value="'.$timeSlotStart.'-'.$timeSlotEnd.'">&nbsp;<span style="background-color: #'.$background_color.'">'.$timeSlotStart.' - '.$timeSlotEnd.'</span>&nbsp;'.$link.$business_name.'&nbsp;<br/>';
                       
                    }
                    echo '</font>';
                    echo '</td>';
                    echo '</tr>';
                    ?>
                </tbody>
                <tfoot colspan="2">
                    <tr>
                        <td valign="middle">
                            <input id="doChangeAvail" data-dochange="false" type="submit" value="Change Availability" name="action" class="buttons" onclick="return changeAvailability()">
                            <span style="float:right; display: flex; align-items: center;">
                                &nbsp;&nbsp;
                                <font face="Arial" size="2">
                                    <span class="big-font">
                                        <b><?php echo $dayOfWeek . ", " . $monthName . " " . $dayOfMonth . ", " . $year; ?></b>
                                    </span>&nbsp;&nbsp;</font>
                                <a href="amenux.asp?ddate=11/10/2024&amp;endate=11/10/2024">
                                    <img border="0" title="Previous Day" src="/images/arrowhead_week_astern.gif" align="middle">
                                </a>
                                <img border="0" src="/images/day.gif" align="middle">
                                <a href="amenux.asp?ddate=11/12/2024&amp;endate=11/12/2024">
                                    <img border="0" title="Next Day" src="/images/arrowhead_week_ahead.gif" align="middle">
                                </a>
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<script>
    
    const weekCells = document.querySelectorAll('.week-number');
    const monthCells = document.querySelectorAll('.month-name');
    const yearCells = document.querySelectorAll('.year-number');

    // Add event listeners to each week cell
    weekCells.forEach(cell => {
        cell.addEventListener('mouseover', () => {
            cell.style.color = 'red'; // Change font color to red on mouseover
        });
        cell.addEventListener('mouseout', () => {
            cell.style.color = 'blue'; // Change font color to blue on mouseout
        });
        cell.addEventListener('click', () => {
            const from = cell.getAttribute('data-week-from');
            const to = cell.getAttribute('data-week-to'); 
            alert(from + ":" + to); // Alert the week number on click
        });
    });

    // Add event listeners to each month cell
    monthCells.forEach(cell => {
        cell.addEventListener('mouseover', () => {
            cell.style.color = 'red'; // Change font color to red on mouseover
        });
        cell.addEventListener('mouseout', () => {
            cell.style.color = 'blue'; // Change font color to blue on mouseout
        });
        cell.addEventListener('click', () => {      
            var year = $(cell).attr('data-year');
            var month = $(cell).attr('data-month');
            var newUrl = window.location.origin + window.location.pathname + "?SystemId=<?php echo $systemId?>+&year=" + year + "&month=" + month + "&day=1";
            window.location.href = newUrl;
        });
    });

    // Add event listeners to each year cell
    yearCells.forEach(cell => {
        cell.addEventListener('mouseover', () => {
            cell.style.color = 'red'; // Change font color to red on mouseover
        });
        cell.addEventListener('mouseout', () => {
            cell.style.color = 'blue'; // Change font color to blue on mouseout
        });
        cell.addEventListener('click', () => {
            var year = $(cell).attr('data-year');
            console.log(year);
            var newUrl = window.location.origin + window.location.pathname + "?SystemId=<?php echo $systemId?>+&year=" + year + "&month=1&day=1";
            window.location.href = newUrl;
        });
    });

    function redirectToToday() {
        window.location.href = window.location.origin + window.location.pathname + "?SystemId=" + <?php echo $systemId; ?> + "&year=" + <?php echo $current_year; ?> + "&month="+ <?php echo $current_month; ?> +"&day="+ <?php echo $current_day; ?>;
    }

    function showLocation(flag) {
        var locationText = document.getElementById("locationText");
        var calendarWidget = document.getElementById("calendarWidget");

        if (flag) {
            locationText.style.display = "block"; // Show the location text
            calendarWidget.style.display = "none"; // Hide the calendar widget
        } else {
            locationText.style.display = "none"; // Hide the location text
            calendarWidget.style.display = "block"; // Show the calendar widget
        }
    }
</script>
<script src="./js/booking_access.js"></script>
<?php
require_once('footer.php');
?>

