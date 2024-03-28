<?php

$menu = "confirm";
require_once('header.php');

if ( empty($arrAppData['business_name']) ) {
	header('Location: '. SECURE_URL . PROFILE_PAGE, true, 301);
	exit(0);
}

$format_date = format_date( $arrAppData['date_appointment'] );
$booking_code = $arrAppData['booking_code'];
$arrSystems = $_SESSION['arrAvailableSystems'];
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
				<p><?php echo $format_date; ?></p>
				<p><?php echo $arrServices[$arrAppData['service']]['fullname']; ?></p>
				<p><b><?php echo getLocationNameById($arrAppData['location']); ?></b> - <?php echo getLocationAddressById($arrAppData['location']); ?></p>
				<p><?php echo $arrSystems[$arrAppData['system']]['fullname']; ?></p>
				<p>Reference: <?php echo $booking_code; ?></p>
				<?php
					foreach ( $arrAppData['booking_time'] as $time ) {
						list($from_in_mins, $to_in_mins) = explode('-', $time);

						echo '<p>' . get_display_text_from_minutes($from_in_mins, $to_in_mins) . '</p>';
					}
				?>
				<p>Business Name: <?php echo $arrAppData['business_name']; ?></p>
				<p>Patient Name: <?php echo $arrAppData['patient_name']; ?></p>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="text-center">
				<a class="btn btn-primary btn-sm m-2" href="<?php echo SECURE_URL . START_PAGE;?>">Make Another Booking</a>
			</td>
		</tr>
	</table>
</div>

<?php
require_once('footer.php');
?>