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
$systemId = isset($_GET['SystemId']) ? intval($_GET['SystemId']) : null;

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



//*************  SET YEAR, MONTH, DAY  ***************** */
$year = date('Y');
$month = date('m');
$day = date('d');

// Initialize startDate with today's date as default
$startDate = date('Y-m-d');

// Check if startDate is set in the GET parameters
if (isset($_GET['startDate'])) {
    // Extract the value of startDate
    $startDate = $_GET['startDate'];
    
    // Validate and format startDate
    if (!empty($startDate) && preg_match('/^\d{4}-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01])$/', $startDate)) {
        // Split the date string into year, month, and day
        list($year, $month, $day) = explode('-', $startDate);
        
        // Convert month and day to integers
        $year = intval($year);
        $month = intval($month);
        $day = intval($day);
        
        // Proceed with further processing
    } else {
        $startDate = date('Y-m-d');
    }
}

$endDate = $startDate;
if (isset($_GET['endDate'])) {
    // Extract the value of startDate
    $endDate = $_GET['endDate'];
    // Validate and format startDate
    if (!empty($endDate) && preg_match('/^\d{4}-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01])$/', $endDate)) {
        // Split the date string into year, month, and day
        // Proceed with further processing
    } else {
        $endDate = date('Y-m-d');
    }
}
// Exception handling: check if any of the values are invalid or undefined
if ($year === 0 || $month === 0 || $day === 0) {
    // Set default values (current date)
    $year = date('Y');
    $month = date('m');
    $day = date('d');
}

$timestamp = mktime(0, 0, 0, $month, $day, $year);

// Get the name of the month (e.g., March)
$monthName = date('M', $timestamp);

// Get the name of the day of the week (e.g., Wed)
$dayOfWeek = date('D', $timestamp);

// Get the day of the month without leading zeros (e.g., 27)
$dayOfMonth = date('j', $timestamp);

$currentDateTime = strtotime(date('Y-m-d'));

$todayDate = date('Y-m-d');

$this_year = date('Y'); // Get the current year
$this_month = date('n'); // Get the current month without leading zeros
$this_day = date('d'); // Get the current day
$array_weeks_in_month = cal_weeks_in_month($year, $month);

$date = new DateTime("$year-$month-$day");
$date->modify('+1 day');// Add one day to the date

$nextDay = $date->format('Y-m-d');

$date->modify('-2 day');// Add prev

$prevDay = $date->format('Y-m-d')

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
                        <font face="Arial" color="#000000" size="2"><strong>Administrator:</strong> <span style ="font-size: 0.9rem; font-weight: bold;"><?php echo $administratorName; ?></span></font>
                    </td>
                    <td bgcolor="#FFFFFF" valign="top" width="80%" style="text-align: right">
                        <font face="Arial" color="#000000" size="2"><b>
                            <span title="<?php echo $systemName; ?>" style="font-size: 14px;"><?php echo $systemName; ?></span>
                            &nbsp;&nbsp;&nbsp;
                            <a target="_self" href="#" onclick="redirectToToday()" style="color: blue;" onmouseover="this.style.color='red';" onmouseout="this.style.color='blue';" onmouseover="window.status='Calendar View for Today';return true" onmouseout="window.status='';return true">Today</a>
                            <a href="javascript:popUp('options/options_services_display.asp');" style="color: blue;" onmouseover="this.style.color='red';" onmouseout="this.style.color='blue';">Services List</a>&nbsp; <!-- Updated link -->
                            <a target="_self" href="options/options.asp" style="color: blue;" onmouseover="this.style.color='red';" onmouseout="this.style.color='blue';" onmouseover="window.status='Access Options Menu';return true" onmouseout="window.status='';return true">Options</a>&nbsp; 
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
                                        ajax: '/api/calendar_ajax_api.php?SystemId=<?php echo $systemId;?>'
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
                                    const startDate = year+'-'+month+'-'+ day; // Format the date as M/D/Y
                                    var newUrl = window.location.origin + window.location.pathname + "?SystemId=<?php echo $systemId?>&startDate="+ startDate + "&endDate=" + startDate;
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
                                    if ($this_year == $year && $this_month == $index + 1) {
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
                                        $class = ($i == $this_year) ? 'highlight-year' : ''; // Add highlight class to the current year
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
             <table id="timeSlotsTable" width="100%" table-bordered cellpadding="2" cellspacing="1" bgcolor="#000080">
                
                    <?php
                    // Include the contents of temp.php here
                        require_once('booking_table.php');
                    ?>
                   
                </tbody>
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
            // Split the dates to get the year, month, and day components
            const fromDateComponents = from.split('-');
            const toDateComponents = to.split('-');

            // Construct the URL with the startDate and endDate parameters
            const url = window.location.origin + window.location.pathname + '?SystemId=<?php echo $systemId?>&startDate=' + fromDateComponents[0] + '-' + fromDateComponents[1] + '-' + fromDateComponents[2] +
                        '&endDate=' + toDateComponents[0] + '-' + toDateComponents[1] + '-' + toDateComponents[2];

            // Redirect to the constructed URL
            window.location.href = url;
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
            const startDate = year +'-'+ month + '-1'; // Format the date as M/D/Y
            var newUrl = window.location.origin + window.location.pathname + "?SystemId=<?php echo $systemId?>&startDate=" + startDate;
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
            const startDate = year + '-1-1'; // Format the date as Y/M/D
            const newUrl = window.location.origin + window.location.pathname + "?SystemId=<?php echo $systemId; ?>&startDate=" + startDate;
            window.location.href = newUrl;
        });
    });

    function redirectToToday() {
        const newUrl = `${window.location.origin}${window.location.pathname}?SystemId=${<?php echo $systemId; ?>}&startDate=<?php echo $this_day;?>&endDate=<?php echo $this_day;?>`;
        window.location.href = newUrl;
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

