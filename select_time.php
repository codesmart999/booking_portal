<?php

$menu = "select";
require_once('header.php');

if ( !isset($_SESSION['arrAvailableSystems']) || !isset($_SESSION['arrSystemBookingPeriodsByDaysDiff']) ){
	header('Location: '. SECURE_URL . START_PAGE, true, 301);
	exit(0);
}

$arrAvailableSystems = $_SESSION['arrAvailableSystems'];
$arrSystemBookingPeriodsByDaysDiff = $_SESSION['arrSystemBookingPeriodsByDaysDiff'];
$message = '';

if ( isset($_POST['Submit'])){
	$system_id = $_POST['system_id'];
	$booking_date = $_POST['booking_date'];
	$arr_booking_times = $_POST['booking_time'];

	usort($arr_booking_times, 'sortRanges'); // custom-defined sorting function (in functions.php)

	$prev_ending = -1;
	foreach ($arr_booking_times as $value) {
		list($from_in_mins, $to_in_mins) = explode('-', $value);
		if ($prev_ending != -1 && $prev_ending != $from_in_mins) {
			$prev_ending = -1; // Time slots are not consecutive.
			break;
		}
		$prev_ending = $to_in_mins;
	}
	
	// Are time slots consecutive?
	if ($prev_ending != -1) {
		$_SESSION['appointment_data']['date_appointment_final'] = date('d/m/Y', strtotime($booking_date));
		$_SESSION['appointment_data']['booking_time'] = $arr_booking_times;
		$_SESSION['appointment_data']['system'] = $system_id;

		header('Location: '. SECURE_URL . PROFILE_PAGE, true, 301);
		exit(0);
	}

	$message = '<span class="ErrorMessage fst-italic fw-bold show">'._lang('err_consecutive_time').'</span>';
}

if ( $arrAppData['location'] == ""){
	header('Location: '. SECURE_URL . START_PAGE, true, 301);
	exit(0);
}
?>

<h4 class="page-name">Select Times ></h4>
<h1 class="fw-bold">Chromis Medical Appointments</h1>

<form method="post" class="form-horizontal" id="APP_FORM">
	<h6>&nbsp;</h6>
	<div class="table-responsive">
		<table class="appForm table">
			<tr>
				<td colspan = "2" class="text-center app_desc fst-italic">
					<p><?php echo $arrServices[$arrAppData['service']]['fullname']; ?></p>
					<p><b><?php echo getLocationNameById($arrAppData['location']); ?></b> - <?php echo getLocationAddressById($arrAppData['location']); ?></p>
					<?php echo $message; ?>
				</td>
			</tr>
			<tr>
				<td class="form-label">Select Time:</td>
				<td class="app_desc">
					<input type="hidden" id="system_id" name="system_id"/>
					<input type="hidden" id="booking_date" name="booking_date"/>
					<?php
						foreach ($arrAvailableSystems as $systemId => $objSystemInfo) {
							if (!empty($arrSystemBookingPeriodsByDaysDiff[$systemId]))
								$arrBookingPeriodsByDaysDiff = $arrSystemBookingPeriodsByDaysDiff[$systemId];
							else
								$arrBookingPeriodsByDaysDiff = $arrSystemBookingPeriodsByDaysDiff[0];
					?>
					<div class="row">
						<div class="col-md-12">
							<p class="lbl_system" id="lbl_system_<?php echo $systemId;?>"><?php echo $objSystemInfo['fullname']; ?></p>
						</div>
					</div>
					<div class="row">
					<?php
						foreach ($arrBookingPeriodsByDaysDiff as $days_diff => $arrBookingPeriods) {
							if (empty($arrBookingPeriods)) continue;

							$date = date('d/m/Y', strtotime('+' . $days_diff . ' day', strtotime(str_replace('/', '-', $arrAppData['date_appointment']))));
					?>
						<div class="col-md-2">
							<p class="lbl_system_bookingperiods" id="lbl_system_bookingperiods_<?php echo $systemId . '_' . $days_diff;?>"><?php echo format_date($date); ?></p>
							<select class="system_bookingperiods" name="booking_time[]" multiple="multiple" style="height:150px;" data-system-id="<?php echo $systemId;?>" data-days-diff="<?php echo $days_diff;?>">
								<option value="">Deselect</option>
								<?php
									foreach( $arrBookingPeriods as $objBookingPeriod ) {
										$key = $objBookingPeriod['FromInMinutes'] . '-' . $objBookingPeriod['ToInMinutes'];
										$val = get_display_text_from_minutes($objBookingPeriod['FromInMinutes'], $objBookingPeriod['ToInMinutes']);
										
										$selected = "";
										if ($arrAppData['system'] == $systemId) {
											foreach( $arrAppData['booking_time'] as $book_time ){
												if ( $key == $book_time && $arrAppData['date_appointment'] == $date )
													$selected = "selected";
											}
										}
										
										echo '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>';
									}
								?>
							</select>
						</div>	
					<?php 
						}
					?>
					</div>
					<?php
					}
					?>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="text-center">
					<a class="btn btn-primary btn-sm m-2" href="<?php echo SECURE_URL . START_PAGE;?>"><< Previous</a>
					<button type="submit" class="btn btn-primary btn-sm m-2" name="Submit" value="Save" disabled>Next >></button>
				</td>
			</tr>
		</table>
	</div>
</form>

<script>
	$(document).ready(function() { 
		$('.system_bookingperiods').on('change', function() {
			system_id = $(this).attr('data-system-id');
			days_diff = $(this).attr('data-days-diff');
			$('.lbl_system').removeClass('focus');
			$('#lbl_system_' + system_id).addClass('focus');
			$('.lbl_system_bookingperiods').removeClass('focus');
			$('#lbl_system_bookingperiods_' + system_id + '_' + days_diff).addClass('focus');

			$("#system_id").val(system_id);
			$("#booking_date").val($('#lbl_system_bookingperiods_' + system_id + '_' + days_diff).html());
		});
	});
</script>

<?php
require_once('footer.php');
?>