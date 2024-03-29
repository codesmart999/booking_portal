<?php 
    require_once('../config.php');
    require_once('../lib.php');

    
    $booking_code = -1;
    if (isset($_GET['booking_code'])) {
        // Extract the value of startDate
        $booking_code = $_GET['booking_code'];
    }else {
        header('Location: '. SECURE_URL . ADMIN_INDEX, true, 301);
        exit; // Make sure to exit after redirection to prevent further script execution
    }
    

    $booking_info = getBookedInfoByBookingCode($booking_code);
    

?>
<table border="0" cellpadding="3" width="100%" cellspacing="1" bgcolor="navy">
    
    <tbody>
        <tr>
            <td width="75%" bgcolor="#C5D4F0" align="center" valign="middle"><font face="Arial" size="2" color="#000000"><b>
                Comments
            </b></font></td>
        </tr>
        <tr>
            <td width="100%" bgcolor="#FFFFFF" valign="top" align="left">
                <div align="center">
                    <center>
                        <table border="0" cellpadding="5" cellspacing="0" width="100%">
                            <tbody>
                                <tr>
                                    <td valign="top">
                                        <font face="Arial" size="2" color="#000000">&nbsp;<b>Public Holiday</b>
                                            <br>
                                            &nbsp;Booking Date : </font>
                                        <font face="Arial" size="2" color="#000000"> Friday, March 29, 2024<br>
                                        </font>
                                        <font face="Arial" size="2" color="#000000">&nbsp;Booking Time : </font>
                                        <font face="Arial" size="2" color="#000000"> 8:15 am - 8:30 am <br>
                                            <font face="Arial" size="2" color="#000000">&nbsp;Attended? </font>
                                            <font face="Arial" size="2" color="#000000">
                                                <input type="radio" name="att" value="Y" checked="checked"> Yes &nbsp;&nbsp;
                                                <input type="radio" name="att" value="N"> No </font>
                                        </font>
                                    </td>
                                    <td valign="bottom">
                                        <font face="Arial" size="2" color="#000000">
                                        </font>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </center>
                </div>
            </td>
        </tr>

        <tr>
            <td width="100%" bgcolor="#E8EEF7" align="left" valign="top">
                <font color="navy" size="2" face="arial">Enter New Comments to Attach to Booking</font>
            </td>
        </tr>
        <tr>
            <td width="100%" bgcolor="#FFFFFF" valign="top" align="left">
                <font face="Arial" size="2" color="#000000"></font>
                <textarea rows="8" name="S1" cols="55"></textarea><br>
                <font face="Arial" size="1" color="#000000">Maximum Characters 2000</font>
            </td>
        </tr>

        <tr>

            <td width="100%" align="left" bgcolor="#FFFFFF">
                <font face="Arial" size="2" color="#000000">
                    <br>
                    <input type="submit" value="Save">&nbsp;&nbsp;&nbsp;

                    <input type="button" value="Close to Refresh Calendar"
                        onclick="javascript:self.close();window.opener.location.reload();">&nbsp;&nbsp;&nbsp;

                    <!--//  &nbsp;&nbsp;&nbsp;
                    //-->
                    <br>
                    <br>
                </font>
            </td>

        </tr>

        <tr>
            <td width="100%" bgcolor="#FFFFFF" align="right" valign="top" colspan="3">
                <font face="Arial" size="2" color="#000000"><a
                        href="javascript:popUp('options_comments1b.asp?s=&amp;w=&amp;c=B7CD71CF-D48B-4916-985D-DF98D139EE2F');">
                        Comments Attached to Bookings for
                        Public Holiday</a> </font>
            </td>
        </tr>

    </tbody>
</table>