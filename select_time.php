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

if ( isset($_POST['Submit']) && isset($_POST['booking_time']) ) {
	$booking_date = $_POST['booking_date'];
	$arr_booking_times = $_POST['booking_time']; // ['FromInMins-ToInMins-SystemId']

	usort($arr_booking_times, 'sortRanges'); // custom-defined sorting function (in functions.php)

	$prev_ending = -1;
	$total_duration = 0;
	$arr_system_ids = array();
	foreach ($arr_booking_times as $value) {
		list($from_in_mins, $to_in_mins, $system_id) = explode('-', $value);
		
		if ($prev_ending != -1 && $prev_ending != $from_in_mins) {
			$prev_ending = -1; // Time slots are not consecutive.
			break;
		}
		$prev_ending = $to_in_mins;

		$total_duration += $to_in_mins - $from_in_mins;
		if (!in_array($system_id, $arr_system_ids))
			$arr_system_ids[] = $system_id;
	}
	
	// Are time slots inconsecutive?
	$arrAppData['date_appointment_final'] = date('d/m/Y', strtotime($booking_date));;

	if ($prev_ending == -1) {
		$message = '<span class="ErrorMessage fst-italic fw-bold show">'._lang('err_consecutive_time').'</span>';
	} else if ($arrServices[$arrAppData['service']]['duration_in_mins'] != $total_duration) {
		$message = '<span class="ErrorMessage fst-italic fw-bold show">'._lang('err_duration_match').'</span>';
	} else {
		$_SESSION['appointment_data']['date_appointment_final'] = $arrAppData['date_appointment_final'];
		$_SESSION['appointment_data']['booking_time'] = $arr_booking_times;
		$_SESSION['appointment_data']['booked_systems'] = $arr_system_ids;

		header('Location: '. SECURE_URL . PROFILE_PAGE, true, 301);
		exit(0);
	}
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
					<p><b><?php echo $arrServices[$arrAppData['service']]['fullname']; ?></b> - Duration <?php echo $arrServices[$arrAppData['service']]['formatted_duration']; ?></p>
					<p><b><?php echo getLocationNameById($arrAppData['location']); ?></b> - <?php echo getLocationAddressById($arrAppData['location']); ?></p>
					<?php echo $message; ?>
				</td>
			</tr>
			<tr>
				<td class="form-label">Select Time:</td>
				<td class="app_desc">
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
										$key = $objBookingPeriod['FromInMinutes'] . '-' . $objBookingPeriod['ToInMinutes'] . '-' . $systemId;
										$val = get_display_text_from_minutes($objBookingPeriod['FromInMinutes'], $objBookingPeriod['ToInMinutes']);
										
										$selected = "";
										if ($arrAppData['date_appointment_final'] == $date) {
											foreach( $arrAppData['booking_time'] as $book_time ){
												if ( $key == $book_time )
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
			var system_id = $(this).attr('data-system-id');
			var days_diff = $(this).attr('data-days-diff');
			
			var arr_selected = $(this).val();
			if (!!arr_selected && arr_selected.length && !arr_selected[0]) {
				// If Deselect is chosen.
				$(this).prop('selectedIndex', -1);
				$('#lbl_system_' + system_id).removeClass('focus');
				$('#lbl_system_bookingperiods_' + system_id + '_' + days_diff).removeClass('focus');
			} else {
				$('#lbl_system_' + system_id).addClass('focus');
				$('#lbl_system_bookingperiods_' + system_id + '_' + days_diff).addClass('focus');

				// Deselect time slots on other date
				$('.system_bookingperiods').each(function(index) {
					var _system_id = $(this).attr('data-system-id');
					var _days_diff = $(this).attr('data-days-diff');
					var _arr_selected = $(this).val();
					if (days_diff != _days_diff && _arr_selected && _arr_selected.length) {
						$(this).prop('selectedIndex', -1);
						if (system_id != _system_id)
							$('#lbl_system_' + _system_id).removeClass('focus');
						$('#lbl_system_bookingperiods_' + _system_id + '_' + _days_diff).removeClass('focus');
					}
				});

				$("#booking_date").val($('#lbl_system_bookingperiods_' + system_id + '_' + days_diff).html());
			}
		});
	});
</script>

<?php
require_once('footer.php');
?>