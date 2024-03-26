<?php 

$menu = "calendar_page";
require_once('header.php');

$array_months = [
    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
];

$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$day = isset($_GET['day']) ? $_GET['day'] : date('d');

$current_month = date('n'); // Get the current month without leading zeros
$current_year = date('Y'); // Get the current year

$array_weeks_in_month = cal_weeks_in_month($year, $month);
?>

<div class="container-fluid">
    <div class="row">
        <!-- Left Sidebar (Calendar) -->
        <div class="col-md-4 col-sm-12 calendar-sidebar demo-calendar">
            <div class="row">
                <div class="col-md-12">
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
                                alert(e.date.toDateString());
                            });         
                        </script>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
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
                <div class="col-md-12">
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
                <div class="col-md-12">
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

        <!-- Main Content (Time Slots) -->
        <div class="col-md-8 col-sm-12 time-slots-sidebar">
            <h3 class="mt-3 mb-3">Time Slots</h3>
            <!-- Time slots with checkboxes -->
            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="timeSlot1">
                    <label class="form-check-label" for="timeSlot1">8:00am - 8:15am</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="timeSlot2">
                    <label class="form-check-label" for="timeSlot2">8:15am - 8:30am</label>
                </div>
                <!-- Add more time slots as needed -->
            </div>
        </div>
    </div>
</div>

<script>
    const weekCells = document.querySelectorAll('.week-number');
    const monthCells = document.querySelectorAll('.month-name');
    const yearCells = document.querySelectorAll('.year-number');

    function updateCalendar(year, month) {
        // Logic to update the calendar based on year and month
        console.log('Updating calendar to ' + month + ' ' + year);
    }

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
            var newUrl = window.location.origin + window.location.pathname + "?year=" + year + "&month=" + month + "&day=1";
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
            var newUrl = window.location.origin + window.location.pathname + "?year=" + year + "&month=1&day=1";
            window.location.href = newUrl;
        });
    });
</script>

<?php
require_once('footer.php');
?>

