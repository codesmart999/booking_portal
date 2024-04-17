<?php

$menu = "confirm";
require_once('header.php');

if ( empty($arrAppData['business_name']) ) {
	header('Location: '. SECURE_URL . PROFILE_PAGE, true, 301);
	exit(0);
}

$objUser = $_SESSION['User'];

$format_date = format_date( $arrAppData['date_appointment_final'] );
$booking_code = $arrAppData['booking_code'];
$arrSystems = $_SESSION['arrAvailableSystems'];
list($from_in_mins, $to_in_mins) = extractStartAndEndTime($arrAppData['booking_time']);
// TODO: uncomment after testing
unset($_SESSION['appointment_data']);
?>

<h4 class="page-name">Confirmation ></h4>
<h1 class="fw-bold">Chromis Medical Appointments</h1>

<h6>&nbsp;</h6>
<div class="table-responsive">
	<table class="appForm table">
		<tr>
			<td colspan = "2" class="text-center app_desc fst-italic">
				<p><?php echo get_display_text_from_minutes($from_in_mins, $to_in_mins) . ', ' . $format_date; ?></p>
				<p>by <?php echo $objUser['Username']; ?></p>
				<p><?php echo $arrServices[$arrAppData['service']]['fullname']; ?></p>
				<p><b><?php echo getLocationNameById($arrAppData['location']); ?></b> - <?php echo getLocationAddressById($arrAppData['location']); ?></p>
				<p><?php echo getSystemNames($arrSystems, $arrAppData['booked_systems']); ?></p>
				<p>Reference: <?php echo $booking_code; ?></p>
				<p>Business Name: <?php echo $arrAppData['business_name']; ?></p>
				<p>Patient Name: <?php echo $arrAppData['patient_name']; ?></p>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="text-center">
				<a class="btn btn-primary btn-sm m-2" href="<?php echo SECURE_URL . START_PAGE;?>">Make Another Booking</a>
				<button class="btn btn-secondary btn-sm m-2" id="btnPrint">Print</button>
			</td>
		</tr>
	</table>
</div>

<div class="print-container d-none">
    <h2>Booking Confirmation</h2>
    <p>Thank you for your booking. Your booking details are as follows:</p>
    
    <div class="print-details">
        <table>
            <tr>
                <th>Booking Date:</th>
                <td><?php echo get_display_text_from_minutes($from_in_mins, $to_in_mins) . ', ' . $format_date; ?></td>
            </tr>
            <tr>
                <th>Booked By:</th>
                <td><?php echo $objUser['Username']; ?></td>
            </tr>
            <tr>
                <th>Service Name:</th>
                <td><?php echo $arrServices[$arrAppData['service']]['fullname']; ?></td>
            </tr>
            <tr>
                <th>Location:</th>
                <td><b><?php echo getLocationNameById($arrAppData['location']); ?></b> - <?php echo getLocationAddressById($arrAppData['location']); ?></td>
            </tr>
            <tr>
                <th>Individual System:</th>
                <td><?php echo getSystemNames($arrSystems, $arrAppData['booked_systems']); ?></td>
            </tr>
            <tr>
                <th>Reference Code:</th>
                <td><?php echo $booking_code; ?></td>
            </tr>
			<tr>
                <th>Business Name:</th>
                <td><?php echo $arrAppData['business_name']; ?></td>
            </tr>
			<tr>
                <th>Patient Name:</th>
                <td><?php echo $arrAppData['patient_name']; ?></td>
            </tr>
        </table>
    </div>
</div>

<?php
require_once('footer.php');
?>

