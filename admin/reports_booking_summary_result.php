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

$numberminutes = isset($_GET['numberminutes']) ? intval($_GET['numberminutes']) : 60;

$prev = $page - 1;
$next = $page + 1;

$page_start = ($page - 1) * $limit;

// Variable to store the report option
$reportOption = '';

$criteriaType = 2; //default all search
$locationID = null;
$systemID = null;
// Determine the report option based on the value of searchchoice
if ($searchChoice === 'bsall') {
    $reportOption = "Report Option 3 - All Booking Systems";
} elseif ($searchChoice === 'dp') {

     // Extract the value of searchbydp1 from $_GET
     $searchByDP1 = isset($_GET['searchbydp1']) ? intval($_GET['searchbydp1']) : -1;

     if ( $searchByDP1 == -1) { //exception
        // Redirect back to the previous page
        header("Location: /admin/reports_booking_summary.php");
        exit(); // Make sure to exit after redirecting to prevent further execution
    }

    $locationID = $searchByDP1;
    $criteriaType = 0; //search with location

     // Get the name corresponding to the selected location
     foreach ($arrLocations as $key => $values) {
         if ($key == $searchByDP1) {
             $reportOption = "Report Option 1 - " . $values['name'];
             break;
         }
     }
} elseif ($searchChoice === 'bs') {
    $searchByBS = isset($_GET['searchbybs']) ? intval($_GET['searchbybs']) : -1;

    if ( $searchByBS == -1) { //exception handling
        // Redirect back to the previous page
        header("Location: /admin/reports_all_customize.php");
        exit(); // Make sure to exit after redirecting to prevent further execution
    }

    $criteriaType = 1; //systemID
    $systemID = $searchByBS;

    // Get the name corresponding to the selected service
    foreach ($arrServices as $key => $objService) {
        if ($key == $searchByBS) {
            $reportOption = "Report Option 2 - Booking System : " . $objService['fullname'];
            break;
        }
    }
}

if ($_GET['output'] === 'csv') {
    // Generate CSV content
    $csvContent = '';

    // Header row
    $csvContent .= "BSN and Date, Total, Booked, Available, Utilisation\n";

    $globalTotal = 0;
    $globalBooked = 0;
    $globalAvailable = 0;
    
    foreach ($arrSystems as $key => $objSystem) {
        if ($criteriaType == 0) {
            if ($objSystem["locationId"] != $locationID) {
                continue;
            }
        }

        if ($criteriaType == 1) {
            if ($objSystem["id"] != $systemID) {
                continue;
            }
        }

        $csvContent .= $objSystem["fullname"] . ",,,,\n";

        $availableInfo = getAvailabilityDataRange($objSystem["id"], $startDate, $endDate);
        $bookedInfo = getBookedInfoForSummary($objSystem["id"], $startDate, $endDate);

        if ($bookedInfo > $availableInfo) $bookedInfo = $availableInfo; //exception for multi bookingc case

        $sumTotal = 0;
        $sumBooked = 0;
        $sumAvailable = 0;

        foreach ($availableInfo as $key => $info) {
            if ($info["durationAvailable"] > 0) {
                $keyFormatted = date('l F j Y', strtotime($key));
                $totalDuration = $info["durationAvailable"];
                $bookedDuration = isset($bookedInfo[$key]) ? intval($bookedInfo[$key]["duration"]) : 0;
                $availableDuration = $totalDuration - $bookedDuration;

                $sumTotal += $totalDuration;
                $sumBooked += $bookedDuration;
                $sumAvailable += $availableDuration;

                $percentage = $bookedDuration * 100 / $totalDuration;

                $csvContent .= $keyFormatted . "," .
                    number_format($totalDuration / $numberminutes, 1) . "," .
                    number_format($bookedDuration / $numberminutes, 1) . "," .
                    number_format($availableDuration / $numberminutes, 1) . "," .
                    number_format($percentage, 1) . "\n";
            }
        }

        $globalTotal += $sumTotal;
        $globalBooked += $sumBooked;
        $globalAvailable += $sumAvailable;

        $csvContent .= "," . number_format($sumTotal / $numberminutes, 1) . "," . number_format($sumBooked / $numberminutes, 1) . "," . number_format($sumAvailable / $numberminutes, 1) . "," . number_format($sumBooked * 100 / $sumTotal / $numberminutes, 2) . "\n";
    }

    $csvContent .= "TOTALS," . number_format($globalTotal / $numberminutes, 1) . "," . number_format($globalBooked / $numberminutes, 1) . "," . number_format($globalAvailable / $numberminutes, 1) . "," . number_format($globalBooked * 100 / $globalTotal / $numberminutes, 2) . "\n";

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="export.csv"');

    // Output CSV content
    echo $csvContent;

    // Stop further execution
    exit;
}


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

                <br>
                <br>
                <table border="0" cellpadding="2" cellspacing="0" width="100%" bgcolor="#FFFFFF" bordercolor="gray"
                    style="border: 1 solid #ffffff">
                    <tbody>
                        <tr>
                            <td bgcolor="#F3F3F3" valign="top" align="left">
                                <font face="Arial" size="2" color="navy"><b>BSN and Date</b></font>
                            </td>
                            <td bgcolor="#F3F3F3" valign="top" align="left">
                                <font face="Arial" size="2" color="navy"><b></b></font>&nbsp;
                            </td>
                            <td bgcolor="#F3F3F3" align="right">
                                <font face="Arial" size="2" color="navy"><b>Total</b></font>
                            </td>
                            <td bgcolor="#F3F3F3" align="right">
                                <font face="Arial" size="2" color="navy"><b>Booked</b></font>
                            </td>
                            <td bgcolor="#F3F3F3" align="right">
                                <font face="Arial" size="2" color="navy"><b>Available</b></font>
                            </td>
                            <td bgcolor="#F3F3F3" align="right">
                                <font face="Arial" size="2" color="navy"><b>Utilisation</b></font>
                            </td>
                            <td bgcolor="#F3F3F3" valign="top" align="left">
                                <font face="Arial" size="2" color="navy"><b></b></font>
                            </td>
                        </tr>
                        <?php

$globalTotal = 0;
$globalBooked = 0;
$globalAvailable = 0;
foreach ($arrSystems as $key => $objSystem): 
    if($criteriaType == 0){
        if ($objSystem["locationId"] != $locationID)
            continue;
    }

    if($criteriaType == 1){//
        if ($objSystem["id"] != $systemID)
            continue;
    }

    $availableInfo = getAvailabilityDataRange($objSystem["id"], $startDate, $endDate);
    $bookedInfo = getBookedInfoForSummary($objSystem["id"], $startDate, $endDate);

    echo '<tr>
        <td>
            <font face="arial" size="2"><b>'.$objSystem["fullname"].'</b></font>
        </td>
        <td></td>
        <td>
            <font face="arial" size="2"></font>
        </td>
        <td>
            <font face="arial" size="2"></font>
        </td>
        <td>
            <font face="arial" size="2"></font>
        </td>
        <td>
            <font face="arial" size="2"></font>
        </td>
        <td>
            <font face="arial" size="2"></font>
        </td>
    </tr>';
    $sumTotal = 0;
    $sumBooked = 0;
    $sumAvailable = 0;
    foreach ($availableInfo as $key => $info): 
        if ($info["durationAvailable"] > 0){
            $keyFormatted = date('l, F j, Y', strtotime($key));
            $totalDuration = $info["durationAvailable"];
            $bookedDuration = isset($bookedInfo[$key]) ?  intval($bookedInfo[$key]["duration"]) : 0;
            $availableDuratoin = $totalDuration - $bookedDuration;
            
            $sumTotal += $totalDuration;
            $sumBooked += $bookedDuration;
            $sumAvailable += $availableDuratoin;
            
            $percentage = $bookedDuration * 100 / $totalDuration;


            echo '
                <tr>
                    <td>
                        <font face="arial" size="2"><a
                                href="booking_access.php?SystemId='.$objSystem["id"].'&startDate='.$key.'&endDate='.$key.'"
                                target="_blank">'.$keyFormatted.'</a></font>
                    </td>

                    <td>&nbsp;</td>
                    <td align="right">
                        <font face="arial" size="2">'. number_format($totalDuration / $numberminutes, 1) .'</font>
                    </td>
                    <td align="right">
                        <font face="arial" size="2">'. number_format($bookedDuration / $numberminutes, 1) .'</font>
                    </td>

                    <td align="right">
                        <font face="arial" size="2">'. number_format($availableDuratoin / $numberminutes, 1) .'</font>
                    </td>
                    <td align="right">
                        <font face="arial" size="2">'. number_format($percentage, 1).'%</font>
                    </td>
                    <td>
                        <font face="arial" size="2"></font>
                    </td>
                        </tr>';
        }
    endforeach;

    $globalTotal += $sumTotal;
    $globalBooked += $sumBooked;
    $globalAvailable += $sumAvailable;
    echo '<tr>
            <td>
            <b><font face="Arial" size="2" color="navy"></font></b>
            </td>
            <td></td>
            <td align="right">
            <font face="arial" size="2"><b>'. number_format($sumTotal / $numberminutes, 1) .'</b></font>
            </td>
            <td align="right">
            <font face="arial" size="2"><b>'. number_format($sumBooked / $numberminutes, 1) .'</b></font>
            </td>
            <td align="right">
            <font face="arial" size="2"><b>'. number_format($sumAvailable / $numberminutes, 1) .'</b></font>
            </td>
            <td align="right">
            <font face="arial" size="2"><b>'. number_format($sumBooked * 100 / $sumTotal / $numberminutes, 2) .'%</b></font>
            </td>
            <td></td>
    </tr>
    ';
    
endforeach;

echo '<tr>
            <td><b>
                    <font face="Arial" size="2">TOTALS</font>
                </b></td>
            <td></td>
            <td align="right">
            <font face="arial" size="2"><b>'. number_format($globalTotal / $numberminutes, 1) .'</b></font>
            </td>
            <td align="right">
            <font face="arial" size="2"><b>'. number_format($globalBooked / $numberminutes, 1) .'</b></font>
            </td>
            <td align="right">
            <font face="arial" size="2"><b>'. number_format($globalAvailable / $numberminutes, 1) .'</b></font>
            </td>
            <td align="right">
            <font face="arial" size="2"><b>'. number_format($globalBooked * 100 / $globalTotal / $numberminutes, 2) .'%</b></font>
            </td>
            <td></td>
    </tr>
    ';
?>
                    </tbody>
                </table>
                <br>

            </td>
        </tr>
    </tbody>
</table>
