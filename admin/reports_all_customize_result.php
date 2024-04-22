<?php
require_once('../config.php');
require_once('../lib.php');


$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';
if (!isset($startDate) || empty($startDate) || !isset($endDate) || empty($endDate)) {
    // Redirect back to the previous page
    header("Location: /admin/reports_all_customize.php");
    exit(); // Make sure to exit after redirecting to prevent further execution
}

// Format the start and end dates as desired
$formattedStartDate = date('l, F j, Y', strtotime($startDate));
$formattedEndDate = date('l, F j, Y', strtotime($endDate));

$searchChoice = isset($_GET['searchchoice']) ? $_GET['searchchoice'] : '';

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? $_GET['limit'] : DEFAULT_PAGE_NUM;

$prev = $page - 1;
$next = $page + 1;

$page_start = ($page - 1) * $limit;

// Variable to store the report option
$reportOption = '';

$row_data = array(); //set as empty arrary
$total_count = 0;

if ($_GET['output'] === 'csv') {
    $page_start = NULL;
    $limit = NULL;
}


// Determine the report option based on the value of searchchoice
if ($searchChoice === 'bsall') {
    $reportOption = "Report Option 3 - All Booking Systems";
    $row_data = getReportAllCustomize($startDate, $endDate, $page_start, $limit, NULL, NULL);
    $total_count = getCountReportAllCustomize($startDate, $endDate, NULL, NULL);
} elseif ($searchChoice === 'dp') {
    
     // Extract the value of searchbydp1 from $_GET
     $searchByDP1 = isset($_GET['searchbydp1']) ? intval($_GET['searchbydp1']) : -1;

     if ( $searchByDP1 == -1) { //exception
        // Redirect back to the previous page
        header("Location: /admin/reports_all_customize.php");
        exit(); // Make sure to exit after redirecting to prevent further execution
    }

     $row_data = getReportAllCustomize($startDate, $endDate, $page_start, $limit, NULL, $searchByDP1);
     $total_count = getCountReportAllCustomize($startDate, $endDate, NULL, $searchByDP1);
     // Get the name corresponding to the selected location
     foreach ($arrLocations as $key => $values) {
         if ($key == $searchByDP1) {
             $reportOption = "Report Option 1 - " . $values['name'];
             break;
         }
     }
} elseif ($searchChoice === 'bs') {
    $searchByBS = isset($_GET['searchbybs']) ? intval($_GET['searchbybs']) : -1; //systemId

    if ( $searchByBS == -1) { //exception handling
        // Redirect back to the previous page
        header("Location: /admin/reports_all_customize.php");
        exit(); // Make sure to exit after redirecting to prevent further execution
    }
    $row_data = getReportAllCustomize($startDate, $endDate, $page_start, $limit, $searchByBS, NULL);
    $total_count = getCountReportAllCustomize($startDate, $endDate, $searchByBS, NULL);
    // Get the name corresponding to the selected service
    foreach ($arrSystems as $key => $objSystem) {
        if ($key == $searchByBS) {
            $reportOption = "Report Option 2 - Booking System : " . $objSystem['fullname'];
            break;
        }
    }
}
$row_count = count($row_data);
if ($row_count == 0) {
    echo '<table border="0" cellpadding="5" cellspacing="0" width="100%">
    <tbody><tr>
    <td width="100%" bgcolor="#ffffff" valign="top" align="center">
    
    <table border="0" cellpadding="3" cellspacing="1" width="100%" bgcolor="000080">
    
    <tbody><tr>
    <td width="100%" bgcolor="#E8EEF7" align="left" valign="top" colspan="2">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="E8EEF7">
    <tbody><tr>
    <td width="100%" bgcolor="#E8EEF7" align="left" valign="top">
    <!--<font color="C5D4F0" size="2" face="Webdings"><b>/</b> </font>-->
    <font size="2" face="Arial" color="#000000"><b>System Message</b></font>
    </td>
    </tr>
    </tbody></table>
    </td>
    </tr>
    <tr>
    <td width="100%" bgcolor="#ffffff" align="left" valign="top">
    <font face="Arial" color="#000000" size="2">
    <br>
    0-0 Bookings found (Non-Consolidated)
    <br><br>
    </font>
    </td>
    </tr>
    </tbody></table>
    <table border="0" cellpadding="3" cellspacing="0" width="100%" bgcolor="ffffff">
    <tbody><tr>
    <td width="100%" bgcolor="#ffffff" valign="top" align="left">
    <font face="Arial" color="#000000" size="2">
    <br>
    <form><input type="button" onclick="history.go(-1);" value="Previous Page">
    </form>
    </font>
    </td>
    </tr>
    </tbody></table>
    <script language="javascript" type="text/javascript">
        var obj = document.getElementById("loading") 
        if ( obj ) obj.style.display="none";
    </script>
    </td></tr></tbody></table>';
    return;
}

if ($_GET['output'] === 'csv') {
    // Generate CSV content
    $csvContent = '';

    // Header row
    $csvContent .= "Booking System Name, Booking For, Cancelled, From, To, Booking Date, Booking Code\n";

    // Loop through each row of data and append to CSV content
    foreach ($row_data as $row) {
        // Format each column value as needed
        $formattedRow = [
            $row['systemName'],
            date('D M j Y', strtotime($row['bookingForDate'])),
            ($row['isCancelled'] == 1) ? 'Yes' : '',
            convertDurationToHoursMinutes($row['bookingFrom'])["formatted_text_type1"],
            convertDurationToHoursMinutes($row['bookingTo'])["formatted_text_type1"],
            date("D M d Y", strtotime($row['bookingDate'])),
            $row['bookingCode']
            // Add more columns if needed and format them accordingly
        ];
    
        // Append the formatted row to the CSV content
        $csvContent .= implode(",", $formattedRow) . "\n";
    }
    
    // // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="export.csv"');

    // Output CSV content
    echo $csvContent;

    // Stop further execution
    exit;
}

$getParams = http_build_query($_GET);
$pagenationLink = "?" . $getParams;

$number_of_page = ceil( $total_count / $limit );

?>
<style>
td {
    font-size: 10pt;
    white-space: nowrap;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8pt;
}
</style>
<table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF">
    <tbody>
        <tr>
            <td width="100%" bgcolor="#FFFFFF" align="left" valign="top">
                &nbsp;
                <table border="0" cellpadding="1" cellspacing="0" width="1000">
                    <tbody>
                        <tr>
                            <td width="100%" bgcolor="#F3F3F3" colspan="4">
                                <b>
                                    Chromis Occupational Medicine Pty Ltd</b><br>
                                Date Range Selected: <?php echo "$formattedStartDate to $formattedEndDate"; ?>
                            </td>
                        </tr>
                        <tr>
                            <td width="100%" bgcolor="#F3F3F3" colspan="4">
                                <?php echo $reportOption;?><br>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table border="0" cellpadding="3" cellspacing="1" width="1000" bgcolor="#C0C0C0" align="left">
                    <form name="form1"></form>
                    <tbody>
                        <tr>
                            <td width="34%" valign="middle" align="center" bgcolor="#F9F9F9">
                                <?php echo $page_start + 1;?>
                                -
                                <?php echo $page_start + $row_count;?>
                                of
                                <?php echo $total_count;?>
                                Bookings (Non-Consolidated)
                            </td>
                            <td width="33%" valign="middle" align="center" bgcolor="#F9F9F9">
                                <a class="page-link"
                                    href="<?php if($page <= 1){ echo '#'; } else { echo $pagenationLink."&page=" . $prev; } ?>">
                                    <input type="button" value="<< Prev" <?php if($page <= 1){ echo 'disabled'; } ?>>
                                </a>
                                <a class="page-link"
                                    href="<?php if($page >= $number_of_page){ echo '#'; } else {echo $pagenationLink."&page=". $next; } ?>">
                                    <input type="button" value="Next >>"
                                        <?php if($page >= $number_of_page) { echo 'disabled'; } ?>>
                                </a>

                                &nbsp;&nbsp;

                                <input type="button" value="Print"
                                    onclick="javascript:printPage()">&nbsp;&nbsp;&nbsp;<input type="button"
                                    value="Close" onclick="javascript:self.close()">
                            </td>
                            <td width="33%" valign="middle" align="center" bgcolor="#F9F9F9">
                                Names per Page
                                <select name="rpp" onchange="reloadPage()">
                                    <?php
                                    $options = array(10, 20, 30, 40, 50, 100, 150);
                                    foreach ($options as $option) {
                                        if ($option == $limit) {
                                            echo "<option value=\"$option\" selected>$option</option>";
                                        } else {
                                            echo "<option value=\"$option\">$option</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <input type="hidden" name="lettertopass" value="0" size="20">
                            </td>
                        </tr>

                    </tbody>
                </table>
                <br>
                <br>
                <br>
                <table border="0" cellpadding="3" cellspacing="1" width="100%" bgcolor="F3F3F3" bordercolor="gray"
                    style="border: 1 solid #000000">
                    <tbody>
                        <tr>
                            <td><b>Booking System<br>Name</b></td>
                            <td><b>Booking For</b></td>
                            <td><b>Cancelled</b></td>
                            <td><b>From</b></td>
                            <td><b>To</b></td>
                            <td><b>Booking Date</b></td>
                            <td><b>Booking Code</b></td>
                        </tr>
                        <?php
                            // Loop through each row of data
                            foreach ($row_data as $row) {
                                // Start a table row
                                echo "<tr>";

                                // Output each column value
                                echo "<td bgcolor=\"#F8F8F8\">" . $row['systemName'] . "</td>";
                                $bookingForDate = date('D, M j Y', strtotime($row['bookingForDate']));

                                // Output the formatted date
                                echo "<td bgcolor=\"#F8F8F8\">" . $bookingForDate . "</td>";

                                $isCancelled = ($row['isCancelled'] == 1) ? "Yes" : "";

                                // Output the formatted value
                                echo "<td bgcolor=\"#F8F8F8\">" . $isCancelled . "</td>";

                                $bookingFrom = convertDurationToHoursMinutes($row['bookingFrom'])["formatted_text_type1"];
                                $bookingTo = convertDurationToHoursMinutes($row['bookingTo'])["formatted_text_type1"];

                                echo "<td bgcolor=\"#F8F8F8\">" . $bookingFrom . "</td>";
                                echo "<td bgcolor=\"#F8F8F8\">" . $bookingTo . "</td>";

                                $bookingDate = date("D, M d Y", strtotime($row['bookingDate']));

                                // Output the formatted bookingDate
                                echo "<td bgcolor=\"#F8F8F8\">" . $bookingDate . "</td>";

                                echo "<td bgcolor=\"#F8F8F8\">" . $row['bookingCode'] . "</td>";
                                // Similarly, output other columns as needed

                                // End the table row
                                echo "</tr>";
                            }
                        ?>

                    </tbody>
                </table>
                <br>
            </td>
        </tr>
    </tbody>
</table>

<script>
function reloadPage() {
    // Get the selected value from the select element
    var selectedValue = document.getElementsByName("rpp")[0].value;

    // Update the URL with the selected value as a GET parameter
    var currentUrl = window.location.href;
    var urlWithParam = updateQueryStringParameter(currentUrl, 'limit', selectedValue);
    var urlWithParam = updateQueryStringParameter(urlWithParam, 'page', 1);
    // Redirect to the updated URL

    window.location.href = urlWithParam;
}

// Function to update query string parameter in URL
function updateQueryStringParameter(uri, key, value) {
    // Remove hash part before operating on URI
    var i = uri.indexOf('#');
    var hash = i === -1 ? ''  : uri.substr(i);
    uri = i === -1 ? uri : uri.substr(0, i);

    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf('?') !== -1 ? "&" : "?";

    if (uri.match(re)) {
        uri = uri.replace(re, '$1' + key + "=" + value + '$2');
    } else {
        uri = uri + separator + key + "=" + value;
    }
    return uri + hash;  // Append hash as well
}

function printPage() {
    if (window.print)
        window.print()
    else
        alert("Sorry, your browser doesn't support this feature. Use File/Print instead.");
}
</script>