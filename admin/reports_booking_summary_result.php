<?php
// CREATED BY CodeMAX 2024-04-22;

require_once('../config.php');
require_once('../lib.php');

$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';


if (!isset($startDate) || empty($startDate) || !isset($endDate) || empty($endDate)) {
    // Redirect back to the previous page
    header("Location: /admin/reports_booking_summary.php");
    exit(); // Make sure to exit after redirecting to prevent further execution
}

// Format the start and end dates as desired
$formattedStartDate = date('l, F j, Y', strtotime($startDate));
$formattedEndDate = date('l, F j, Y', strtotime($endDate));

$searchChoice = isset($_GET['searchchoice']) ? $_GET['searchchoice'] : '';

$acrosspage = isset($_GET['acrosspage']) ? $_GET['acrosspage'] == 'Y' : false; //flag to show screen mode / export CSV mode

$numberminutes = isset($_GET['numberminutes']) ? intval($_GET['numberminutes']) : 60;

// Variable to store the report option
$reportOption = '';

$criteriaType = 2; // Default all search
$locationID = null;
$systemID = null;

// Determine the report option based on the value of searchchoice
if ($searchChoice === 'bsall') {
    $reportOption = "Report Option 3 - All Booking Systems";
} elseif ($searchChoice === 'dp') {
    // Extract the value of searchbydp1 from $_GET
    $searchByDP1 = isset($_GET['searchbydp1']) ? intval($_GET['searchbydp1']) : -1;

    if ($searchByDP1 == -1) { // Exception
        // Redirect back to the previous page
        header("Location: /admin/reports_booking_summary.php");
        exit();
    }

    $locationID = $searchByDP1;
    $criteriaType = 0; // Search with location

    // Get the name corresponding to the selected location
    foreach ($arrLocations as $key => $values) {
        if ($key == $searchByDP1) {
            $reportOption = "Report Option 1 - " . $values['name'];
            break;
        }
    }
} elseif ($searchChoice === 'bs') {
    $searchByBS = isset($_GET['searchbybs']) ? intval($_GET['searchbybs']) : -1;

    if ($searchByBS == -1) { // Exception handling
        // Redirect back to the previous page
        header("Location: /admin/reports_booking_summary.php");
        exit();
    }

    $criteriaType = 1; // SystemID
    $systemID = $searchByBS;

    // Get the name corresponding to the selected service
    foreach ($arrSystems as $key => $objSystem) {
        if ($key == $searchByBS) {
            $reportOption = "Report Option 2 - Booking System : " . $objSystem['fullname'];
            break;
        }
    }
}


//export CSV mode
if ($_GET['output'] === 'csv' && !$acrosspage) {
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

        $csvContent .= '"' . $objSystem["fullname"] . '","","","",""' . "\n";

        $availableInfo = getAvailabilityDataRange($objSystem["id"], $startDate, $endDate);
        $bookedInfo = getBookedInfoForSummary($objSystem["id"], $startDate, $endDate);

        if ($bookedInfo > $availableInfo) {
            $bookedInfo = $availableInfo; // Exception for multi-booking case
        }

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

                $csvContent .= '"' . $keyFormatted . '",' .
                '"' . number_format($totalDuration / $numberminutes, 1) . '",' .
                '"' . number_format($bookedDuration / $numberminutes, 1) . '",' .
                '"' . number_format($availableDuration / $numberminutes, 1) . '",' .
                '"' . number_format($percentage, 1) . '"' . "\n";

            }
        }

        $globalTotal += $sumTotal;
        $globalBooked += $sumBooked;
        $globalAvailable += $sumAvailable;

        $csvContent .= ',"' . number_format($sumTotal / $numberminutes, 1) . '","' . number_format($sumBooked / $numberminutes, 1) . '","' . number_format($sumAvailable / $numberminutes, 1) . '","' . number_format($sumBooked * 100 / $sumTotal / $numberminutes, 2) . '%"' . "\n";
    }

    $csvContent .= '"TOTALS",' . 
    '"' . number_format($globalTotal / $numberminutes, 1) . '",' . 
    '"' . number_format($globalBooked / $numberminutes, 1) . '",' . 
    '"' . number_format($globalAvailable / $numberminutes, 1) . '",' . 
    '"' . number_format($globalBooked * 100 / $globalTotal / $numberminutes, 2) . '%"' . "\n";


    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="bookingsummary.csv"');

    // Output CSV content
    echo $csvContent;

    // Stop further execution
    exit;
}

if ($_GET['output'] === 'csv' && $acrosspage) {
    // Generate CSV content
    $csvContent = 'BSN, Type';
    $currentDate = strtotime($startDate);
    $lastDate = strtotime($endDate);

    $sumAvailable = 0;

    $colspan = 2;
    while ($currentDate <= $lastDate) {
        $currentDay = date('Y-m-d', $currentDate);
        $formatedCurrentDay = date('m/d/Y', $currentDate);

        $csvContent .= ",". $formatedCurrentDay ;
        $currentDate = strtotime('+1 day', $currentDate);
    }

    $csvContent .= ",Total\n";

    foreach ($arrSystems as $key => $objSystem) :
        if ($criteriaType == 0) {
            if ($objSystem["locationId"] != $locationID)
                continue;
        }

        if ($criteriaType == 1) { //
            if ($objSystem["id"] != $systemID)
                continue;
        }

        $availableInfo = getAvailabilityDataRange($objSystem["id"], $startDate, $endDate);
        $bookedInfo = getBookedInfoForSummary($objSystem["id"], $startDate, $endDate);

        //show Available Info
        $csvContent .= $objSystem["fullname"].",Available";

        $currentDate = strtotime($startDate);
        $lastDate = strtotime($endDate);

        $sumAvailable = 0;

        while ($currentDate <= $lastDate) {
            $currentDay = date('Y-m-d', $currentDate);

            $durationAvailable = isset($availableInfo[$currentDay]) ? $availableInfo[$currentDay]['durationAvailable'] : 0;
            $sumAvailable += $durationAvailable;

            $csvContent .= ",".number_format($durationAvailable / $numberminutes, 1);
           
            $currentDate = strtotime('+1 day', $currentDate);

            $colspan += 1;
        }

        $csvContent .= ',"'.number_format($sumAvailable / $numberminutes, 1). '"' . "\n";
       
        $csvContent .= $objSystem["fullname"].",Booked";
       
        $currentDate = strtotime($startDate);
        $lastDate = strtotime($endDate);

        $sumBooked = 0;
        while ($currentDate <= $lastDate) {
            $currentDay = date('Y-m-d', $currentDate);

            $duratoinBooked = isset($bookedInfo[$currentDay]) ? $bookedInfo[$currentDay]['duration'] : 0;
            $sumBooked += $duratoinBooked;

            $csvContent .= ",".number_format($duratoinBooked / $numberminutes, 1);

            $currentDate = strtotime('+1 day', $currentDate);
        }
        $csvContent .= ",".number_format($sumBooked / $numberminutes, 1)."\n";


        $csvContent .= $objSystem["fullname"].",Utilisation";
        $currentDate = strtotime($startDate);
        $lastDate = strtotime($endDate);

        $sumBooked = 0;
        while ($currentDate <= $lastDate) {
            $currentDay = date('Y-m-d', $currentDate);

            $duratoinBooked = isset($bookedInfo[$currentDay]) ? $bookedInfo[$currentDay]['duration'] : 0;

            $durationAvailable = isset($availableInfo[$currentDay]) ? $availableInfo[$currentDay]['durationAvailable'] : 0;
            
            $percentage = $duratoinBooked + $durationAvailable == 0 ? 0 : number_format($duratoinBooked * 100/ ($duratoinBooked + $durationAvailable), 2);
            
            $csvContent .= ",".$percentage.'%';

            $currentDate = strtotime('+1 day', $currentDate);
        }
        $sumPercentage = $sumBooked + $sumAvailable == 0 ? 0 : number_format($sumBooked * 100/ ($sumBooked + $sumAvailable), 2);
        $csvContent .= ",".$sumPercentage."%\n";

    endforeach;

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="bookingsummary.csv"');

    echo $csvContent;
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
                                <?php echo $reportOption; ?><br>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php
    if (!$acrosspage) {
    ?>

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
                        foreach ($arrSystems as $key => $objSystem) :
                            if ($criteriaType == 0) {
                                if ($objSystem["locationId"] != $locationID)
                                    continue;
                            }

                            if ($criteriaType == 1) { //
                                if ($objSystem["id"] != $systemID)
                                    continue;
                            }

                            $availableInfo = getAvailabilityDataRange($objSystem["id"], $startDate, $endDate);
                            $bookedInfo = getBookedInfoForSummary($objSystem["id"], $startDate, $endDate);

                            echo '<tr>
                                <td>
                                    <font face="arial" size="2"><b>' . $objSystem["fullname"] . '</b></font>
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

                            //for calc total 
                            $sumTotal = 0;
                            $sumBooked = 0;
                            $sumAvailable = 0;

                            foreach ($availableInfo as $key => $info) :
                                if ($info["durationAvailable"] > 0) {
                                    $keyFormatted = date('l, F j, Y', strtotime($key));
                                    $totalDuration = $info["durationAvailable"];
                                    $bookedDuration = isset($bookedInfo[$key]) ? intval($bookedInfo[$key]["duration"]) : 0;
                                    $availableDuratoin = $totalDuration - $bookedDuration;

                                    $sumTotal += $totalDuration;
                                    $sumBooked += $bookedDuration;
                                    $sumAvailable += $availableDuratoin;

                                    $percentage = $bookedDuration * 100 / $totalDuration;
                                    echo '
                                    <tr>
                                        <td>
                                            <font face="arial" size="2"><a
                                                    href="booking_access.php?SystemId=' . $objSystem["id"] . '&startDate=' . $key . '&endDate=' . $key . '"
                                                    target="_blank">' . $keyFormatted . '</a></font>
                                        </td>
                                        <td>&nbsp;</td>
                                        <td align="right">
                                            <font face="arial" size="2">' . number_format($totalDuration / $numberminutes, 1) . '</font>
                                        </td>
                                        <td align="right">
                                            <font face="arial" size="2">' . number_format($bookedDuration / $numberminutes, 1) . '</font>
                                        </td>

                                        <td align="right">
                                            <font face="arial" size="2">' . number_format($availableDuratoin / $numberminutes, 1) . '</font>
                                        </td>
                                        <td align="right">
                                            <font face="arial" size="2">' . number_format($percentage, 1) . '%</font>
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
                                        <font face="arial" size="2"><b>' . number_format($sumTotal / $numberminutes, 1) . '</b></font>
                                        </td>
                                        <td align="right">
                                        <font face="arial" size="2"><b>' . number_format($sumBooked / $numberminutes, 1) . '</b></font>
                                        </td>
                                        <td align="right">
                                        <font face="arial" size="2"><b>' . number_format($sumAvailable / $numberminutes, 1) . '</b></font>
                                        </td>
                                        <td align="right">
                                        <font face="arial" size="2"><b>' . number_format($sumBooked * 100 / $sumTotal / $numberminutes, 2) . '%</b></font>
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
                                        <font face="arial" size="2"><b>' . number_format($globalTotal / $numberminutes, 1) . '</b></font>
                                        </td>
                                        <td align="right">
                                        <font face="arial" size="2"><b>' . number_format($globalBooked / $numberminutes, 1) . '</b></font>
                                        </td>
                                        <td align="right">
                                        <font face="arial" size="2"><b>' . number_format($globalAvailable / $numberminutes, 1) . '</b></font>
                                        </td>
                                        <td align="right">
                                        <font face="arial" size="2"><b>' . number_format($globalBooked * 100 / $globalTotal / $numberminutes, 2) . '%</b></font>
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
<?php
    } else {
?>
<br>
<br>
<table border="0" cellpadding="2" cellspacing="0" width="100%" bgcolor="#FFFFFF" bordercolor="gray" style="border: 1 solid #ffffff">
    <tbody>
        <tr>
            <td bgcolor="#F3F3F3" valign="top" align="left">
                <font face="Arial" size="2" color="navy"><b>BSN</b></font>
            </td>
            <td bgcolor="#F3F3F3" valign="top" align="left">
                <font face="Arial" size="2" color="navy"><b>Type</b></font>
            </td>
            <?php 
                $currentDate = strtotime($startDate);
                $lastDate = strtotime($endDate);
                while ($currentDate <= $lastDate) {
                    $currentDay = date('d', $currentDate);
                    echo '<td bgcolor="#F3F3F3" valign="top" align="left">
                            <font face="Arial" size="2" color="navy"><b>' . $currentDay . '</b></font>
                          </td>';
                    $currentDate = strtotime('+1 day', $currentDate);
                }
            ?>
            <td bgcolor="#F3F3F3" valign="top" align="left">
                <font face="Arial" size="2" color="navy"><b>Total</b></font>
            </td>
        </tr>
        <?php
            
            foreach ($arrSystems as $key => $objSystem) :
                if ($criteriaType == 0) {
                    if ($objSystem["locationId"] != $locationID)
                        continue;
                }

                if ($criteriaType == 1) { //
                    if ($objSystem["id"] != $systemID)
                        continue;
                }

                $availableInfo = getAvailabilityDataRange($objSystem["id"], $startDate, $endDate);
                $bookedInfo = getBookedInfoForSummary($objSystem["id"], $startDate, $endDate);

                //show Available Info
                echo '<tr style="background-color: #ffffff">
                            <td style="font-size: 10pt">'.$objSystem["fullname"].'</td>
                            <td style="font-size: 10pt">Available</td>';
                $currentDate = strtotime($startDate);
                $lastDate = strtotime($endDate);

                $sumAvailable = 0;

                $colspan = 2;
                while ($currentDate <= $lastDate) {
                    $currentDay = date('Y-m-d', $currentDate);

                    $durationAvailable = isset($availableInfo[$currentDay]) ? $availableInfo[$currentDay]['durationAvailable'] : 0;
                    $sumAvailable += $durationAvailable;

                    echo '<td style="font-size: 10pt">'.number_format($durationAvailable / $numberminutes, 1).'</td>';

                    $currentDate = strtotime('+1 day', $currentDate);

                    $colspan += 1;
                }
                echo '<td style="font-size: 10pt">'.number_format($sumAvailable / $numberminutes, 1).'</td>';


                //show Booked Info
                echo '<tr style="background-color: #ffffff">
                            <td style="font-size: 10pt">'.$objSystem["fullname"].'</td>
                            <td style="font-size: 10pt">Booked</td>';
                $currentDate = strtotime($startDate);
                $lastDate = strtotime($endDate);

                $sumBooked = 0;
                while ($currentDate <= $lastDate) {
                    $currentDay = date('Y-m-d', $currentDate);

                    $duratoinBooked = isset($bookedInfo[$currentDay]) ? $bookedInfo[$currentDay]['duration'] : 0;
                    $sumBooked += $duratoinBooked;

                    echo '<td style="font-size: 10pt">'.number_format($duratoinBooked / $numberminutes, 1).'</td>';

                    $currentDate = strtotime('+1 day', $currentDate);
                }
                echo '<td style="font-size: 10pt">'.number_format($sumBooked / $numberminutes, 1).'</td>';

                //show Utilisation Info
                echo '<tr style="background-color: #ffffff">
                            <td style="font-size: 10pt">'.$objSystem["fullname"].'</td>
                            <td style="font-size: 10pt">Utilisation</td>';
                $currentDate = strtotime($startDate);
                $lastDate = strtotime($endDate);

                $sumBooked = 0;
                while ($currentDate <= $lastDate) {
                    $currentDay = date('Y-m-d', $currentDate);

                    $duratoinBooked = isset($bookedInfo[$currentDay]) ? $bookedInfo[$currentDay]['duration'] : 0;

                    $durationAvailable = isset($availableInfo[$currentDay]) ? $availableInfo[$currentDay]['durationAvailable'] : 0;
                    
                    $percentage = $duratoinBooked + $durationAvailable == 0 ? 0 : number_format($duratoinBooked * 100/ ($duratoinBooked + $durationAvailable), 2);
                    

                    echo '<td style="font-size: 10pt">'.$percentage.'%</td>';

                    $currentDate = strtotime('+1 day', $currentDate);
                }
                $sumPercentage = $sumBooked + $sumAvailable == 0 ? 0 : number_format($sumBooked * 100/ ($sumBooked + $sumAvailable), 2);
                echo '<td style="font-size: 10pt">'.$sumPercentage.'%</td>';
                echo '<tr><td colspan="'.$colspan.'">&nbsp;</td></tr>';
                

            endforeach;
        ?>
    </tbody>
</table>
<br>
<?php
    }
?>
