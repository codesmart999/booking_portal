<?php 
    require_once('../config.php');
    require_once('../lib.php');

    $systemId = -1;

    //get User id and full name from users DB
    $userInfo = getUserInfo();

    
    if (isset($_GET['systemId'])) {
        // Extract the value of startDate
        $systemId = $_GET['systemId'];
    }
    if ($systemId == -1) {
        header('Location: '. SECURE_URL . ADMIN_INDEX, true, 301);
        exit; // Make sure to exit after redirection to prevent further script execution
    }
            
    //$serviceInfo = getServiceBySystemId($systemId);

    
    $currentDateTime = date('l, F j, Y g:i:s A');
    // if (!isset($booking_info["businessName"]) || !isset($booking_info["systemFullName"]) || !isset($booking_info["bookingDate"]) || !isset($booking_info["startTime"]) || !isset($booking_info["endTime"])) {//exception handling
    //     header('Location: '. SECURE_URL . ADMIN_INDEX, true, 301);
    //     exit; // Make sure to exit after redirection to prevent further script execution
    // }
 
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
<table border="0" width="1000" cellspacing="1" cellpadding="3" bgcolor="#000080">

    <tbody>
        <tr>
            <td width="100%" valign="middle" align="center" bgcolor="E8EEF7">
                <table border="0" cellpadding="3" width="100%" bgcolor="#E8EEF7" cellspacing="0">
                    <tbody>
                        <tr>
                            <td width="65%" valign="middle" align="left" bgcolor="E8EEF7">
                                <b>
                                    <font face="Arial" size="2" color="#000000">
                                        &nbsp;Nurse Maitland : Services</font>
                                </b>
                            </td>
                            <td width="35%" valign="middle" align="right" bgcolor="E8EEF7">
                                <font face="Arial" size="2" color="#000000">
                                    <?php echo $currentDateTime;?>
                                </font>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>

        <tr>
            <td width="100%" valign="top" align="center" bgcolor="#FFFFFF">

                <table border="0" cellpadding="4" width="96%" bgcolor="#FFFFFF" cellspacing="0">

                    <tbody>
                        <tr>
                            <td width="100%" valign="top" align="left" bgcolor="#FFFFFF">
                                <font face="Arial" size="2" color="#000000">
                                    <br>
                                    <b>Services</b>

                                    <table border="0" cellpadding="3" width="100%" bgcolor="#C0C0C0" cellspacing="1">
                                        <tbody>
                                            <tr>
                                                <td width="55%" valign="top" align="left" bgcolor="f9f9f9">
                                                    <font face="Arial" size="2" color="#000000">
                                                        Display Status
                                                    </font>
                                                </td>
                                                <td width="15%" valign="top" align="center" bgcolor="f9f9f9">
                                                    <font face="Arial" size="2" color="#000000">
                                                        Customers
                                                    </font>
                                                </td>
                                                <td width="15%" valign="top" align="center" bgcolor="f9f9f9">
                                                    <font face="Arial" size="2" color="#000000">
                                                        Agents/Operators
                                                    </font>
                                                </td>
                                                <td width="15%" valign="top" align="center" bgcolor="f9f9f9">
                                                    <font face="Arial" size="2" color="#000000">
                                                        Owners
                                                    </font>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td width="55%" valign="top" align="left" bgcolor="#FFFFFF">
                                                    <font face="Arial" size="2" color="#000000">
                                                        General Description Displays for
                                                    </font>
                                                </td>
                                                <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                    <font face="Arial" size="2" color="#000000">
                                                        No
                                                    </font>
                                                </td>
                                                <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                    <font face="Arial" size="2" color="#000000">
                                                        No
                                                    </font>
                                                </td>
                                                <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                    <font face="Arial" size="2" color="#000000">
                                                        No
                                                    </font>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td width="55%" valign="top" align="left" bgcolor="#FFFFFF">
                                                    <font face="Arial" size="2" color="#000000">
                                                        Services Display under Services Menu Button
                                                    </font>
                                                </td>
                                                <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                    <font face="Arial" size="2" color="#000000">
                                                        No
                                                    </font>
                                                </td>
                                                <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                    <font face="Arial" size="2" color="#000000">
                                                        No
                                                    </font>
                                                </td>
                                                <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                    <font face="Arial" size="2" color="#000000">
                                                        Yes
                                                    </font>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </font>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </td>
        </tr>
        <tr>
            <td width="100%" valign="top" align="center" bgcolor="#FFFFFF">
                <table border="0" cellpadding="3" width="100%" bgcolor="#000080" cellspacing="1">
                    <tbody>
                        <tr>
                            <td width="100%" valign="top" align="center" bgcolor="E8EEF7">
                                <font face="Arial" size="2" color="#000000">
                                    <b>Services</b>
                                </font>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>


        <tr>
            <td width="100%" valign="top" align="center" bgcolor="#FFFFFF">
                <table border="0" cellpadding="3" width="96%" bgcolor="#C0C0C0" cellspacing="0">
                    <tbody>
                        <tr>
                            <td width="100%" valign="top" align="left" bgcolor="#FFFFFF">
                                <font face="Arial" size="2" color="#000000">
                                    <b>Jill Maitland Travel</b>
                                    <br>

                                    <br>
                                </font>

                                <table border="0" cellpadding="3" width="100%" bgcolor="#C0C0C0" cellspacing="1">
                                    <tbody>
                                        <tr>
                                            <td width="15%" valign="top" align="right" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Price
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="right" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Duration
                                                </font>
                                            </td>
                                            <td width="25%" valign="top" align="center" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Service is a Booking Charge
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Customers
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Agents/Operators
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Owners
                                                </font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td width="15%" valign="top" align="right" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    AUD$0.00
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="right" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    1:00
                                                </font>
                                            </td>
                                            <td width="25%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    No
                                                </font>
                                            </td>
                                            <td width="45%" valign="top" align="center" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    &nbsp;
                                                </font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td width="55%" valign="top" align="left" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    Service is Active for
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td width="55%" valign="top" align="left" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    Service Displays under Services Menu Button
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td width="55%" valign="top" align="left" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    Services displays Email Address under Services Menu Button
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    No
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    No
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    No
                                                </font>
                                            </td>
                                        </tr>



                                        <tr>
                                            <td width="55%" valign="top" align="left" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    Service can be Changed by
                                                </font>
                                            </td>
                                            <td width="45%" valign="top" align="left" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    Nurse Maitland
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

        <tr>
            <td width="100%" valign="top" align="center" bgcolor="#FFFFFF">
                <table border="0" cellpadding="3" width="96%" bgcolor="#C0C0C0" cellspacing="0">
                    <tbody>
                        <tr>
                            <td width="100%" valign="top" align="left" bgcolor="#FFFFFF">
                                <font face="Arial" size="2" color="#000000">
                                    <b>Nurse Only 15 Minutes</b>
                                    <br>

                                    <br>
                                </font>

                                <table border="0" cellpadding="3" width="100%" bgcolor="#C0C0C0" cellspacing="1">
                                    <tbody>
                                        <tr>
                                            <td width="15%" valign="top" align="right" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Price
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="right" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Duration
                                                </font>
                                            </td>
                                            <td width="25%" valign="top" align="center" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Service is a Booking Charge
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Customers
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Agents/Operators
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Owners
                                                </font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td width="15%" valign="top" align="right" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    AUD$0.00
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="right" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    0:15
                                                </font>
                                            </td>
                                            <td width="25%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                            <td width="45%" valign="top" align="center" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    &nbsp;
                                                </font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td width="55%" valign="top" align="left" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    Service is Active for
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td width="55%" valign="top" align="left" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    Service Displays under Services Menu Button
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td width="55%" valign="top" align="left" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    Services displays Email Address under Services Menu Button
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    No
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    No
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    No
                                                </font>
                                            </td>
                                        </tr>



                                        <tr>
                                            <td width="55%" valign="top" align="left" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    Service can be Changed by
                                                </font>
                                            </td>
                                            <td width="45%" valign="top" align="left" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    Nurse Maitland
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

        <tr>
            <td width="100%" valign="top" align="center" bgcolor="#FFFFFF">
                <table border="0" cellpadding="3" width="96%" bgcolor="#C0C0C0" cellspacing="0">
                    <tbody>
                        <tr>
                            <td width="100%" valign="top" align="left" bgcolor="#FFFFFF">
                                <font face="Arial" size="2" color="#000000">
                                    <b>Nurse Only 30 Minutes</b>
                                    <br>

                                    <br>
                                </font>

                                <table border="0" cellpadding="3" width="100%" bgcolor="#C0C0C0" cellspacing="1">
                                    <tbody>
                                        <tr>
                                            <td width="15%" valign="top" align="right" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Price
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="right" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Duration
                                                </font>
                                            </td>
                                            <td width="25%" valign="top" align="center" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Service is a Booking Charge
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Customers
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Agents/Operators
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="f9f9f9">
                                                <font face="Arial" size="2" color="#000000">
                                                    Owners
                                                </font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td width="15%" valign="top" align="right" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    AUD$0.00
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="right" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    0:30
                                                </font>
                                            </td>
                                            <td width="25%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                            <td width="45%" valign="top" align="center" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    &nbsp;
                                                </font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td width="55%" valign="top" align="left" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    Service is Active for
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td width="55%" valign="top" align="left" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    Service Displays under Services Menu Button
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    Yes
                                                </font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td width="55%" valign="top" align="left" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    Services displays Email Address under Services Menu Button
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    No
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    No
                                                </font>
                                            </td>
                                            <td width="15%" valign="top" align="center" bgcolor="#FFFFFF">
                                                <font face="Arial" size="2" color="#000000">
                                                    No
                                                </font>
                                            </td>
                                        </tr>



                                        <tr>
                                            <td width="55%" valign="top" align="left" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    Service can be Changed by
                                                </font>
                                            </td>
                                            <td width="45%" valign="top" align="left" bgcolor="#FFFFFF" colspan="3">
                                                <font face="Arial" size="2" color="#000000">
                                                    Nurse Maitland
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

        <tr>
            <td width="100%" valign="top" align="center" bgcolor="#FFFFFF">
                <font face="Arial" size="2" color="#000000">
                    <br>
                    <input type="button" value="Print" onclick="javascript:printPage()">&nbsp;&nbsp;<input type="button"
                        value="Close" onclick="javascript:self.close()">
                    <br><br>
                </font>
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