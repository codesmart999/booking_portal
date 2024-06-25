<?php 
    require_once('../config.php');
    require_once('../lib.php');

    $booking_code = -1;

    //get User id and full name from users DB
    $userInfo = getUserInfo();

    if ($booking_code == -1) {
        if (isset($_GET['booking_code'])) {
            // Extract the value of startDate
            $booking_code = $_GET['booking_code'];
        }else {
            header('Location: '. SECURE_URL . ADMIN_INDEX, true, 301);
            exit; // Make sure to exit after redirection to prevent further script execution
        }
    }
    

    $booking_info = getBookedInfoForPrintingByBookingcode($booking_code);

    if (!isset($booking_info["businessName"]) || !isset($booking_info["systemFullName"]) || !isset($booking_info["bookingDate"]) || !isset($booking_info["startTime"]) || !isset($booking_info["endTime"])) {//exception handling
        header('Location: '. SECURE_URL . ADMIN_INDEX, true, 301);
        exit; // Make sure to exit after redirection to prevent further script execution
    }
    
    $bookingDate = date('l, F jS, Y', strtotime($booking_info["bookingDate"]));

    $newStartTime = date('g:i A', strtotime("today +{$booking_info['startTime']} minutes"));
    $newEndTime = date('g:i A', strtotime("today +{$booking_info['endTime']} minutes"));

    $comments_array = json_decode($booking_info["comments"], true);
    $messages_array = json_decode($booking_info["messages"], true);

    $bookingTime = "$newStartTime - $newEndTime";

    
    $customerCommnetsShow = false;
    $systemCommnetsShow = false;
    if (is_array($comments_array) && count($comments_array) > 0) {
        foreach ($comments_array as $comment){
            if (isset($comment["type"]))
                $systemCommnetsShow = true;
            else
                $customerCommnetsShow = true;
        }
    }
    
    $messagesShow = false;
    if (is_array($messages_array) && count($messages_array) > 0) {
        $messagesShow = true;
    }
?>
<style>
    @media print {
        .noPrint {
            display: none;
        }

        .onlyPrint {
            display: block;
        }
    }
</style>
<table border="0" cellpadding="3" width="100%" cellspacing="1" bgcolor="navy">
    <tbody>
        <tr class="noPrint">
            <td width="75%" bgcolor="C5D4F0" align="left" valign="middle">
                <font face="Arial" size="2" color="#000000"><b>View/Print Bookings</b></font>
            </td>
        </tr>
        <tr>
            <td width="75%" bgcolor="#FFFFFF" align="center" valign="top">
                <table border="0" cellpadding="3" width="100%" cellspacing="0" bgcolor="navy">
                    <form method="post"></form>
                    <tbody>
                        <tr class="noPrint">
                            <td bgcolor="#FFFFFF" align="left" colspan="4">

                                <input type="button" value="Print"
                                    onclick="javascript:printPage()">&nbsp;&nbsp;&nbsp;<input type="button"
                                    value="Close" onclick="javascript:self.close()">&nbsp;&nbsp;&nbsp;

                                <!-- <font face="Arial" size="2" color="#000000"><a
                                        href="/apd/options/options_print.asp?win=1">
                                        Select Display Preferences here</a></font> -->
                                <p></p>

                                <input type="hidden" name="c" value="2024-4-25">
                                <input type="hidden" name="todate" value="2024-4-25">
                                <input type="hidden" name="printall" value="">
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#FFFFFF" width="100%" valign="top" align="left" colspan="4">
                                <font size="2" face="Arial"><b>
                                        <?php echo $booking_info["serviceName"]?>
                                    </b><br>
                                    <?php echo $booking_info["systemStreet"]?>
                                    <?php echo $booking_info["systemCity"]?>
                                    <?php echo $booking_info["systemState"]?>
                                    <?php echo $booking_info["systemPostcode"]?></font>
                            </td>
                        </tr>



                        <tr>
                            <td bgcolor="#FFFFFF" width="100%" valign="top" colspan="4" align="left">
                                <font size="2" face="Arial"><b><?php echo $bookingDate;?></b><br>
                                </font>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#FFFFFF" width="100%" valign="top" align="left" colspan="4">
                                <table border="1" cellpadding="6" width="100%" cellspacing="0" bgcolor="#808080">
                                    <tbody>
                                        <tr>
                                            <td bgcolor="#FFFFFF" valign="top" width="150" nowrap="">
                                                <font face="arial" size="2"><b><?php echo $bookingTime; ?></b>
                                                </font>
                                            </td>
                                            <td bgcolor="#FFFFFF">
                                                <font face="arial" size="2">
                                                    Business Name : <?php echo $booking_info["businessName"]; ?>
                                                    <br/>
                                                    Doctor/Nurse Details : <?php echo $booking_info["systemFullName"]?>
                                                    <br/>
                                                    Patient Name : <?php echo $booking_info["patientName"]; ?>
                                                    <br/>
                                                    Chromis staff making the booking : Loren<br>
                                                    <?php 
                                          
                                                    if ($customerCommnetsShow) {
                                                        echo "<br><b>Comments</b> : <br>";
                                                        foreach ($comments_array as $comment): 
                                                            if(isset($comment["type"]))
                                                                continue;
                                                            $converted_date_time = date('l, F j, Y g:i:s A', strtotime($comment["datetime"]));
                                                            echo "* $converted_date_time"; 
                                                            echo " : ";
                                                            echo $comment["content"].'<br>';
                                                        endforeach;
                                                    }

                                                    if ($messagesShow) {
                                                        echo "<br><b>Customer Message</b> : <br>";
                                                        foreach ($messages_array as $message): 
                                                            $converted_date_time = date('l, F j, Y g:i:s A', strtotime($message["datetime"]));
                                                            echo "* $converted_date_time"; 
                                                            echo " : ";
                                                            echo $message["content"].'<br>';
                                                        endforeach;
                                                    }

                                                    if ($systemCommnetsShow) {
                                                        echo "<br><b>System Comments</b> : <br>";
                                                        foreach ($comments_array as $comment): 
                                                            if(!isset($comment["type"]))
                                                                continue;
                                                            $converted_date_time = date('l, F j, Y g:i:s A', strtotime($comment["datetime"]));
                                                            echo "* $converted_date_time"; 
                                                            echo " : ";
                                                            echo getSystemCommentStringFromComment($comment);
                                                            echo ' by '.$userInfo[$comment["user_id"]].'<br>';
                                                        endforeach;
                                                    }
                                                    ?>
                                                </font>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>


                    </tbody>
                </table>
            </td>
        </tr>
        <tr class="noPrint">
            <td bgcolor="#FFFFFF" align="left" colspan="4">
                <form>
                    <p>
                        <input type="button" value="Print" onclick="javascript:printPage()">&nbsp;&nbsp;&nbsp;

                        <input type="button" value="Close" onclick="javascript:self.close()">&nbsp;&nbsp;&nbsp;

                    </p>
                </form>
            </td>
        </tr>
    </tbody>
</table>
<script>
    // Refresh parent window
// window.opener.location.href = window.opener.location.href;

        $(document).ready(function () {
            $('#emailtimeslots').chosen({ width: '350px' }).change(
            function (e, p) {
                var chn = p.selected || p.deselected, val = $(this).val();
                if (chn == 'all' || val == null) {
                    val = 'all';
                    $(this).val(val).trigger('liszt:updated');
                } else {
                    val = $('#emailtimeslots').chosen().val() + '';
                    if (val.indexOf('all,') > -1) {
                        val = val.replace('all,', '');
                        $(this).val(val).trigger('liszt:updated');
                    }
                }
            });

        });

        function printPage() { if (window.print) { window.print(); } else { alert('Sorry, your browser doesn\'t support this feature. Use File/Print instead.'); } }
    

</script>