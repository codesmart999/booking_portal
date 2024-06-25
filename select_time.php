<?php

$menu = "select";
require_once('header.php');

if ( !isset($_SESSION['arrAvailableSystems']) || !isset($_SESSION['arrSystemBookingPeriodsByDaysDiff']) ){
	header('Location: '. SECURE_URL . START_PAGE, true, 301);
	exit(0);
}

$arrAvailableSystems = $_SESSION['arrAvailableSystems'];
$arrSystemBookingPeriodsByDaysDiff = $_SESSION['arrSystemBookingPeriodsByDaysDiff'];
$objSelectedService = $arrServices[$arrAppData['service']];

$err_consecutive_time = 'd-none';
$err_duration_match = 'd-none';

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
	
	$arrAppData['date_appointment_final'] = date('d/m/Y', strtotime($booking_date));;

	if ($prev_ending == -1) {
		// Time slots are inconsecutive!!!
		$err_consecutive_time = '';
	} else if ($objSelectedService['duration_in_mins_doctor'] + $objSelectedService['duration_in_mins_nurse'] != $total_duration) {
		// Time slots do not match the total duration!!!
		$err_duration_match = '';
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
					<input type="hidden" id="duration_in_mins_doctor" value="<?php echo $objSelectedService['duration_in_mins_doctor'];?>" />
					<input type="hidden" id="duration_in_mins_nurse" value="<?php echo $objSelectedService['duration_in_mins_nurse'];?>" />
					<p><b><?php echo $objSelectedService['fullname']; ?></b> - Duration <?php echo $objSelectedService['formatted_duration']; ?></p>
					<p><b><?php echo getLocationNameById($arrAppData['location']); ?></b> - <?php echo getLocationAddressById($arrAppData['location']); ?></p>
					<span id="err_consecutive_time" class="ErrorMessage fst-italic fw-bold <?php echo $err_consecutive_time;?>"><?php echo _lang('err_consecutive_time'); ?></span>
					<span id="err_duration_match" class="ErrorMessage fst-italic fw-bold <?php echo $err_duration_match;?>"><?php echo _lang('err_duration_match'); ?></span>
				</td>
			</tr>
			<tr>
				<td class="form-label">Select Time:</td>
				<td class="app_desc">
					<input type="hidden" id="booking_date" name="booking_date"/>
					<?php
						foreach ($arrAvailableSystems as $systemId => $objSystemInfo) {
							$systemType = $objSystemInfo['system_type'];
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
							<select class="system_bookingperiods" name="booking_time[]" multiple="multiple" style="height:150px;" data-system-id="<?php echo $systemId;?>" data-system-type="<?php echo $systemType;?>" data-days-diff="<?php echo $days_diff;?>">
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
					<button id="btnNext" type="submit" class="btn btn-primary btn-sm m-2" name="Submit" value="Save" disabled>Next >></button>
				</td>
			</tr>
		</table>
	</div>
</form>

<script>
	$(document).ready(function() { 
		$('.system_bookingperiods').on('change', function() {
			var system_id = $(this).attr('data-system-id');
			var system_type = $(this).attr('data-system-type'); // either D or N
			var days_diff = $(this).attr('data-days-diff');

			var duration_in_mins_doctor = $("#duration_in_mins_doctor").val();
			var duration_in_mins_nurse = $("#duration_in_mins_nurse").val();
			var duration_in_mins = system_type == 'D' ? duration_in_mins_doctor : duration_in_mins_nurse;
			
			var arr_selected = $(this).val();
			if (!!arr_selected && arr_selected.length && !arr_selected[0]) {
				// If Deselect is chosen.
				$(this).prop('selectedIndex', -1);
				$('#lbl_system_' + system_id).removeClass('focus');
				$('#lbl_system_bookingperiods_' + system_id + '_' + days_diff).removeClass('focus');
			} else {
				// 1) Check if timeslots are consecutive.
				let prevEnding = -1;
				let errConsecutiveTime = 'd-none';

				arr_selected.forEach(value => {
					const [fromInMins, toInMins] = value.split('-').map(Number);
					
					if (prevEnding !== -1 && prevEnding !== fromInMins) {
						prevEnding = -1; // Time slots are not consecutive.
						return;
					}
					prevEnding = toInMins;
				});

				if (prevEnding === -1) {
					// Time slots are inconsecutive!!!
					$("#err_consecutive_time").removeClass("d-none");
				} else if (!$("#err_consecutive_time").hasClass("d-none")) {
					$("#err_consecutive_time").addClass("d-none");
				}

				// 2) Select next timeslots automatically based on the duration_in_mins
				let selectedOption = $(this).find('option:selected');

				let [fromInMins, toInMins] = selectedOption.val().split('-').map(Number);
				let firstFromInMins = fromInMins, lastToInMins = toInMins;
				arr_selected = [];

				while (toInMins - fromInMins <= duration_in_mins) {
					arr_selected.push(selectedOption.val());
					lastToInMins = toInMins;

					duration_in_mins -= toInMins - fromInMins;
					
					selectedOption = selectedOption.next();
					if (!selectedOption || !selectedOption.length)
						break;
					[fromInMins, toInMins] = selectedOption.val().split('-').map(Number);
				}

				$(this).val(arr_selected);

				$('#lbl_system_' + system_id).addClass('focus');
				$('#lbl_system_bookingperiods_' + system_id + '_' + days_diff).addClass('focus');

				$('.system_bookingperiods').each(function(index) {
					var _system_id = $(this).attr('data-system-id');
					var _system_type = $(this).attr('data-system-type');
					var _days_diff = $(this).attr('data-days-diff');
					var _arr_selected = $(this).val();

					// Deselect timeslots on other date
					if (days_diff != _days_diff && _arr_selected && _arr_selected.length) {
						$(this).prop('selectedIndex', -1);
						if (system_id != _system_id)
							$('#lbl_system_' + _system_id).removeClass('focus');
						$('#lbl_system_bookingperiods_' + _system_id + '_' + _days_diff).removeClass('focus');
					} else if (days_diff == _days_diff && system_id != _system_id) {
						// Deselect other individual system of the same System Type
						if (system_type == _system_type) {
							$(this).prop('selectedIndex', -1);
							$('#lbl_system_' + _system_id).removeClass('focus');
							$('#lbl_system_bookingperiods_' + _system_id + '_' + _days_diff).removeClass('focus');
						} else if (system_type == 'N') {
							$(this).prop('selectedIndex', -1);
							$('#lbl_system_' + _system_id).removeClass('focus');
							$('#lbl_system_bookingperiods_' + _system_id + '_' + _days_diff).removeClass('focus');

							if (duration_in_mins_doctor > 0) {
								// Nurse is selected. Now automatically select timeslots for the doctor.
								let arr_options = $(this).find('option');
								let selectedOption = false;

								arr_options.each(function() {
									let fromInMins, toInMins;
									
									// Extract values from each option
									[fromInMins, toInMins] = $(this).val().split('-').map(Number);
									
									// Check if condition is met
									if (fromInMins === lastToInMins) {
										selectedOption = $(this);
										return false; // Break the loop
									}
								});

								if (selectedOption) {
									[fromInMins, toInMins] = selectedOption.val().split('-').map(Number);
									arr_selected = [];

									while (toInMins - fromInMins <= duration_in_mins_doctor) {
										arr_selected.push(selectedOption.val());

										duration_in_mins_doctor -= toInMins - fromInMins;
										
										selectedOption = selectedOption.next();
										if (!selectedOption || !selectedOption.length)
											break;
										[fromInMins, toInMins] = selectedOption.val().split('-').map(Number);
									}

									$(this).val(arr_selected);

									if (arr_selected && arr_selected.length) {
										$('#lbl_system_' + _system_id).addClass('focus');
										$('#lbl_system_bookingperiods_' + _system_id + '_' + _days_diff).addClass('focus');
									}
								}
							}
						} else if (system_type == 'D') {
							$(this).prop('selectedIndex', -1);
							$('#lbl_system_' + _system_id).removeClass('focus');
							$('#lbl_system_bookingperiods_' + _system_id + '_' + _days_diff).removeClass('focus');

							if (duration_in_mins_nurse > 0) {
								// Doctor is selected. Now automatically select timeslots for the nurse.
								let arr_options = $(this).find('option');
								let selectedOption = false;

								arr_options.each(function() {
									let fromInMins, toInMins;
									
									// Extract values from each option
									[fromInMins, toInMins] = $(this).val().split('-').map(Number);
									
									// Check if condition is met
									if (toInMins === firstFromInMins) {
										selectedOption = $(this);
										return false; // Break the loop
									}
								});

								if (selectedOption) {
									[fromInMins, toInMins] = selectedOption.val().split('-').map(Number);
									arr_selected = [];

									while (toInMins - fromInMins <= duration_in_mins_nurse) {
										arr_selected.push(selectedOption.val());

										duration_in_mins_nurse -= toInMins - fromInMins;
										
										selectedOption = selectedOption.prev();
										[fromInMins, toInMins] = selectedOption.val().split('-').map(Number);
									}

									$(this).val(arr_selected);

									if (arr_selected && arr_selected.length) {
										$('#lbl_system_' + _system_id).addClass('focus');
										$('#lbl_system_bookingperiods_' + _system_id + '_' + _days_diff).addClass('focus');
									}
								}
							}
						}
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