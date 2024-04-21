<?php 
	require_once('header.php');
?>

<h4 class="page-title">Reports</h4>
<div class="table-responsive">
    <table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
        <tbody>
            <tr>
                <td width="30%" bgcolor="#FFFFFF" align="left" valign="top">
                    <font size="2" face="Arial">
                        <a href="/admin/reports_all_customize">
                            <font size="2">Report on All Bookings</font>
                        </a>
                    </font>
                </td>
                <td width="70%" bgcolor="#FFFFFF" valign="top">
                    <font size="2" face="Arial" color="#000000">
                        Generate report from 1 to 31 days with selected details of bookings.
                    </font>
                </td>
            </tr>

            <tr>
                <td width="30%" bgcolor="#FFFFFF" align="left" valign="top">
                    <font size="2" face="Arial">
                        <a href="/admin/reports_daily">
                            <font size="2">Daily Bookings List (by date)</font>
                        </a>
                    </font>
                </td>
                <td width="70%" bgcolor="#FFFFFF" valign="top">
                    <font size="2" face="Arial">
                        List of all bookings created on specific date.
                    </font>
                </td>
            </tr>

            <tr>
                <td width="30%" bgcolor="#FFFFFF" align="left" valign="top">
                    <font size="2" face="Arial">
                        <a href="/admin/reports_booking_summary">
                            <font size="2">Booking Summary Report</font>
                        </a>
                    </font>
                </td>
                <td width="70%" bgcolor="#FFFFFF" valign="top">
                    <font size="2" face="Arial">
                        Generate report showing summary of booked time
                    </font>
                </td>
            </tr>
        </tbody>

    </table>
</div>
<?php
    require_once('footer.php');
?>