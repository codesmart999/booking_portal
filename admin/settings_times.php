<?php 
	require_once('header.php');
	require_once('utils.php');

	$message = "";
    $db = getDBConnection();

	// Get setting_weekdays
    $arr_availability_by_weekday = array(); // if set TRUE, it's available on that day
    $stmt = $db->prepare("SELECT weekday, isAvailable FROM setting_weekdays WHERE `SystemId`=0");
	$stmt->execute();
    $stmt->bind_result($weekday, $isAvailable);
    $stmt->store_result();
    while ($stmt->fetch()) {
    	$arr_availability_by_weekday[$weekday] = $isAvailable;
    }

    // Save Settings
    if ( isset($_POST['Save']) ) {
   	    $key = $_POST['settingKey'];

		switch ($key) {
			case 'AVAILABLE_WEEK_DAYS':
				$arr_unavailable_weekdays = array_values($_POST['weekday_availability']);
				$arr_systems = $_POST['Systems'];

				$stmt = $db->prepare( 'TRUNCATE TABLE setting_weekdays' );
				$stmt->execute() or die($stmt->error);

				$stmt = $db->prepare("INSERT INTO `setting_weekdays` (SystemId, weekday, isAvailable) VALUES (?, ?, ?)");
				for ($day = 0; $day < 7; $day++) {
					$system_id = 0;
					$isAvailable = !in_array($day, $arr_unavailable_weekdays);
					$stmt->bind_param('iii', $system_id, $day, $isAvailable);
					$stmt->execute() or die($stmt->error);

					foreach ($arr_systems as $system_id) {
						$stmt->bind_param('iii', $system_id, $day, $isAvailable);
						$stmt->execute() or die($stmt->error);
					}
				}

				break;
			case 'DEFAULT_REGULAR_TIME':
				$arr_bookingperiod_summary_by_weekday = summarize_bookingperiod_details($_POST);
				
				$stmt = $db->prepare( 'DELETE FROM setting_bookingperiods WHERE SystemId = 0 AND isRegular = 1' );
				$stmt->execute() or die($stmt->error);

				if (!empty($arr_bookingperiod_summary_by_weekday)) {
					$stmt = $db->prepare("INSERT INTO `setting_bookingperiods` (weekday, FromInMinutes, ToInMinutes, isAvailable) VALUES (?, ?, ?, ?)");
					
					foreach ($arr_bookingperiod_summary_by_weekday as $weekday => $arr_workhours) {
						$isAvailable = $arr_availability_by_weekday[$weekday];

						for ($i = $arr_workhours['FromInMinutes']; $i < $arr_workhours['ToInMinutes']; $i += $arr_workhours['DurationInMinutes']) {
							$to_in_mins = $i + $arr_workhours['DurationInMinutes'];
							$stmt->bind_param('iiii', $weekday, $i, $to_in_mins, $isAvailable);
							$stmt->execute() or die($stmt->error);
						}
					}
				}

				break;
			default:
				$tmpValues = $_POST;
				unset( $tmpValues["settingKey"] );
				unset( $tmpValues["Save"] );
	
				$stmt = $db->prepare(
					'UPDATE settings
						SET value = ?
						WHERE name=?' );
				$value = json_encode( $tmpValues );
				$stmt->bind_param( 'ss',
					$value,
					$key
				);
				$stmt->execute() or die($stmt->error);
				$stmt->close();
				break;
		}

		$message = '<p class="Message fst-italic fw-bold text-success show">'._lang('success_update').'</p>';
    }

    // Get Existing Settings
    $arrSettings = array();
    $stmt = $db->prepare("SELECT * FROM settings WHERE `category`='time'");
	$stmt->execute();
    $stmt->bind_result($id, $name, $value, $category, $description);
    $stmt->store_result();
    while ($stmt->fetch()) {
    	$arrSettings[$name] = [
    		'id' => $id,
    		'name'	=> $name,
    		'value'	=> $value,
    		'cat'	=> $category,
    		'desc'	=> $description
    	];
    }

	// Get setting_bookingperiods
	// 
	// Default Values, just in case the table is empty
	// Each weekday has a summary in the following format.
	// array(
	//	'FromInMinutes' => 8 * 60, // 8:00 AM
	//  'ToInMinutes' => 18 * 60, // 6:00 PM
	//  'DurationInMinutes' => 15
	// );
	$arr_bookingperiod_summary_by_weekday = array(
		'0' => array(),
		'1' => array(),
		'2' => array(),
		'3' => array(),
		'4' => array(),
		'5' => array(),
		'6' => array()
	);

	$stmt = $db->prepare("SELECT weekday, FromInMinutes, ToInMinutes, isRegular, isAvailable FROM setting_bookingperiods WHERE `SystemId`= 0 ORDER BY weekday ASC, FromInMinutes ASC");
	$stmt->execute();
    $stmt->bind_result($weekday, $from_in_mins, $to_in_mins, $isRegular, $isAvailable);
    $stmt->store_result();

    while ($stmt->fetch()) {
		if ($isRegular) {
			if (!isset($arr_bookingperiod_summary_by_weekday[$weekday]['FromInMinutes'])
			 || $from_in_mins < $arr_bookingperiod_summary_by_weekday[$weekday]['FromInMinutes']) {
				$arr_bookingperiod_summary_by_weekday[$weekday]['FromInMinutes'] = $from_in_mins;
			}
			if (!isset($arr_bookingperiod_summary_by_weekday[$weekday]['ToInMinutes'])
			 || $to_in_mins > $arr_bookingperiod_summary_by_weekday[$weekday]['ToInMinutes']) {
				$arr_bookingperiod_summary_by_weekday[$weekday]['ToInMinutes'] = $to_in_mins;
			}
			if (!isset($arr_bookingperiod_summary_by_weekday[$weekday]['DurationInMinutes'])) {
				$arr_bookingperiod_summary_by_weekday[$weekday]['DurationInMinutes'] = $to_in_mins - $from_in_mins;
			}
		}
    }

	$result = convert_bookingperiod_summary_into_details($arr_bookingperiod_summary_by_weekday);

	list($regular_weekday_start_hour, $regular_weekday_start_minutes, $regular_weekday_start_AP,
		$regular_weekday_end_hour, $regular_weekday_end_minutes, $regular_weekday_end_AP,
		$regular_weekday_duration_hours, $regular_weekday_duration_minutes) = array_values($result);
?>

<h4 class="page-title">Manage Default Time Settings</h4>
<p>Denotes option which can be applied across all or individual existing systems.</p>
<?php echo $message ?>
<div class="table-responsive">
	<table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
    	<thead>
	        <tr>
	            <td width="10" nowrap>Name</td>
	            <td width="90" nowrap>Description</td>
	        </tr>
	    </thead>
	    <tbody>
	    	<tr>
	    		<td><a href="#" class="btn-show-modal" data-toggle="modal" data-target="#regularModal">Regular Booking Periods</a></td>
	    		<td>Set Regular Booking Periods in the day, e.g.,10.00 AM to 10.30 AM.</td>
	    	</tr>
	    	<tr>
	    		<td><a href="#" class="btn-show-modal" data-toggle="modal" data-target="#irregularModal">Irregular Booking Periods</a></td>
	    		<td>Set Irregular Booking Periods in the day, e.g.,10.20 AM to 11.00 AM.</td>
	    	</tr>
	    	<tr>
	    		<td><a href="#" class="btn-show-modal" data-toggle="modal" data-target="#bookingPeriodModal">Unavailable/Available Booking Periods</a></td>
	    		<td>Set Booking Periods in either Regular or Irregular Booking Periods as Unavailable. <br>Bookings Periods set as Unavailable can be reset at any time to Available.</td>
	    	</tr>
	    	<tr>
	    		<td><a href="#" class="btn-show-modal" data-toggle="modal" data-target="#bookingDaysModal">Unavailable/Available Week Days</a></td>
	    		<td>Set Week Days, e.g., Friday, Saturday, Sunday, etc., as Unavailable. <br>Week Days set as Unavailable can be reset at any time to Available.</td>
	    	</tr>
	    </tbody>
	</table>
</div>

<?php

$stmt->close();
$db->close();

require_once('footer.php');
?>
<?php
	$settingKey = "DEFAULT_REGULAR_TIME";
	$settingValue = json_decode($arrSettings[$settingKey]['value'], true);
?>
<div class="modal fade" id="regularModal" tabindex="-1" role="dialog" aria-labelledby="saveModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<form method="post" class="form-horizontal setting_form" id="">
			<input type="hidden" name="settingKey" id="settingKey" value="<?php echo $settingKey;?>" />
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="saveModalLabel">Regular Booking Period</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<?php 
						for( $day = 0; $day < 7; $day++ ) { 
							$dayLabel = date('l', strtotime("Sunday +{$day} days"));
					?>
					<h6 class="day-title">
						<?php
							echo $dayLabel;
							if (!$arr_availability_by_weekday[$day]) {
								echo ' (Unavailable)';
							}
						?>
					</h6>
					<div class="form-group">
						<table style="width:100%; margin-bottom:0">
							<tr>
								<td width="33%">Start Time</td>
								<td width="33%">End Time</td>
								<td width="33%">Period Duration</td>
							</tr>
							<tr>
								<td width="33%">
									<?php $optionKey = "weekday_start_hour[" . $day . "]"; ?>
									<select name="<?php echo $optionKey; ?>">
										<?php
										for ( $i = 1; $i <= 12; $i++) {
											$selected = "";
											if ( $regular_weekday_start_hour[$day] == $i )
												$selected = "selected";
											echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
										}
										?>
									</select>
									<?php $optionKey = "weekday_start_minutes[" . $day . "]"; ?>
									<select name="<?php echo $optionKey; ?>">
										<option value="00">00</option>
										<?php
										for ( $i = 5; $i < 60; $i += 1) {
											$selected = "";
											if ( $regular_weekday_start_minutes[$day] == $i )
												$selected = "selected";
											echo '<option value="' . $i . '" ' . $selected . '>' . sprintf("%02d", $i) . '</option>';
										}
										?>
									</select>
									<?php $optionKey = "weekday_start_AP[" . $day . "]"; ?>
									<select name="<?php echo $optionKey; ?>">
										<option value="AM" <?php if (strtolower($regular_weekday_start_AP[$day]) == "am") echo 'selected';?>>AM</option>
										<option value="PM" <?php if (strtolower($regular_weekday_start_AP[$day]) == "pm") echo 'selected';?>>PM</option>
									</select>
								</td>
								<td width="33%">
									<?php $optionKey = "weekday_end_hour[" . $day . "]"; ?>
									<select name="<?php echo $optionKey; ?>">
										<?php
										for ( $i = 1; $i <= 12; $i++) {
											$selected = "";
											if ( $regular_weekday_end_hour[$day] == $i )
												$selected = "selected";
											echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
										}
										?>
									</select>
									<?php $optionKey = "weekday_end_minutes[" . $day . "]"; ?>
									<select name="<?php echo $optionKey; ?>">
										<option value="00">00</option>
										<?php
										for ( $i = 5; $i < 60; $i += 1) {
											$selected = "";
											if ( $regular_weekday_end_minutes[$day] == $i )
												$selected = "selected";
											echo '<option value="' . $i . '" ' . $selected . '>' . sprintf("%02d", $i) . '</option>';
										}
										?>
									</select>
									<?php $optionKey = "weekday_end_AP[" . $day . "]"; ?>
									<select name="<?php echo $optionKey; ?>">
										<option value="AM" <?php if ($regular_weekday_end_AP[$day] == "AM") echo 'selected';?>>AM</option>
										<option value="PM" <?php if ($regular_weekday_end_AP[$day] == "PM") echo 'selected';?>>PM</option>
									</select>
								</td>
								<td width="33%">
									<?php $optionKey = "weekday_duration_hours[" . $day . "]"; ?>
									<select class="d-none" name="<?php echo $optionKey; ?>">
										<option value="0" selected>0</option>
										<?php
										// for ( $i = 0; $i < 24; $i++) {
										// 	$selected = "";
										// 	if ( $regular_weekday_duration_hours[$day] == $i )
										// 		$selected = "selected";
										// 	echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
										// }
										?>
									</select>
									<?php $optionKey = "weekday_duration_minutes[" . $day . "]"; ?>
									<select name="<?php echo $optionKey; ?>">
										<!-- <option value="00">00</option> -->
										<option value="15" selected>15</option>
										<?php
										// for ( $i = 5; $i < 60; $i += 1) {
										// 	$selected = "";
										// 	if ( $regular_weekday_duration_minutes[$day] == $i )
										// 		$selected = "selected";
										// 	echo '<option value="' . $i . '" ' . $selected . '>' . sprintf("%02d", $i) . '</option>';
										// }
										?>
									</select>
									&nbsp;minutes
								</td>
							</tr>
						</table>
					</div>
					<?php } ?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary btn-sm" name="Save" value="Save" id="btnSave">Save</button>
				</div>
			</div>
		</form>
	</div>
</div>

<?php
	$settingKey = "DEFAULT_IRREGULAR_TIME";
	$settingValue = json_decode($arrSettings[$settingKey]['value'], true);
?>
<div class="modal fade" id="irregularModal" tabindex="-1" role="dialog" aria-labelledby="saveModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<form method="post" class="form-horizontal setting_form" id="IRREGULAR_TIME_FORM">
			<input type="hidden" name="settingKey" id="settingKey" value="<?php echo $settingKey;?>" />
			<input type="hidden" name="isWeekdayAvailable" id="isWeekdayAvailable" />
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title step1" id="saveModalLabel">Select Individual Day to Assign Irregular Booking Periods</h5>
					<h5 class="modal-title step2">Set Irregular Booking Periods (<span class="span_cur_weekday"></span>)</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="step1" >
					<?php 
						for ( $day = 0; $day < 7; $day++ ) { 
							$dayLabel = date('l', strtotime("Sunday +{$day} days"));
							// $dayKey = date('D', strtotime("Sunday +{$day} days"));

							$optionKey = "choose_ir_day_" . $day;
							$isAvailable = 1;
							if ( empty($arr_availability_by_weekday[$day]) ) {
								$dayLabel .= ' (Unavailable)';
								$isAvailable = 0;
							}
					?>
						<div class="form-group">
							<input class="choose_ir_day" type="radio" id="<?php echo $optionKey?>" name="weekday" value="<?php echo $day?>" data-availability="<?php echo $isAvailable; ?>"/>
							<label for="<?php echo $optionKey?>"><?php echo $dayLabel; ?></label>
						</div>
						<?php } ?>
					</div>
					<div class="step2">
						<table style="width:100%; margin-bottom:0">
							<tr>
								<td width="33%">Start Time</td>
								<td width="33%">End Time</td>
								<td width="33%"></td>
							</tr>
							<tr>
								<td>
									<select id="irregular_start_hour">
										<?php
										for( $i = 1; $i <= 12; $i++){
											echo '<option value="'.$i.'">'.$i.'</option>';
										}
										?>
									</select>
									<select id="irregular_start_minutes">
										<option value="00">00</option>
										<?php
										for( $i = 5; $i < 60; $i += 1){
											echo '<option value="'.$i.'">'.sprintf("%02d", $i).'</option>';
										}
										?>
									</select>
									<select id="irregular_start_AP">
										<option value="AM">AM</option>
										<option value="PM">PM</option>
									</select>
								</td>
								<td>
									<select id="irregular_end_hour">
										<?php
										for( $i = 1; $i <= 12; $i++){
											echo '<option value="'.$i.'">'.$i.'</option>';
										}
										?>
									</select>
									<select id="irregular_end_minutes">
										<option value="00">00</option>
										<?php
										for( $i = 5; $i < 60; $i += 1){
											echo '<option value="'.$i.'">'.sprintf("%02d", $i).'</option>';
										}
										?>
									</select>
									<select id="irregular_end_AP">
										<option value="AM">AM</option>
										<option value="PM">PM</option>
									</select>
								</td>
								<td>
									<button type="button" id="btn_add_irregular_bookingperiods" class="btn btn-primary btn-sm">Include</button>
								</td>
							</tr>
						</table>
						<div class="form-group text-center mt-2 flex">
							<select name="list_bookingperiods[]" id="list_bookingperiods" multiple="multiple" style="height:400px; width: 200px;">
							</select>
							<div class="mt-2">
								<button type="button" class="btn btn-primary btn-sm" id="delIrTimes">Delete</button>
								<button type="button" class="btn btn-primary btn-sm" id="delIrAll">Delete All</button>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary btn-sm step1" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary btn-sm step1 btn-next" disabled>Next</button>
					<button type="button" class="btn btn-secondary btn-sm step2 btn-previous">Previous</button>
					<button type="button" class="btn btn-primary btn-sm step2" name="Save" value="Save" id="btnIrSave">Save</button>
				</div>
			</div>
		</form>
	</div>
</div>

<?php
	$settingKey = "AVAILABLE_BOOKING_PERIOD";
	$settingValue = json_decode($arrSettings[$settingKey]['value'], true);
?>
<div class="modal fade" id="bookingPeriodModal" tabindex="-1" role="dialog" aria-labelledby="saveModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document"  style="max-width: 800px; margin: 1.75rem auto;">
		<form method="post" class="form-horizontal setting_form" id="AVAILABLE_PERIOD_FORM">
			<input type="hidden" name="settingKey" id="settingKey" value="<?php echo $settingKey;?>" />
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title step1" id="saveModalLabel">Unavailable/Available Booking Periods</h5>
					<h5 class="modal-title step2">Unavailable/Available Booking Periods (<span class="span_cur_weekday"></span>)</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="step1">
					<?php 
						for ( $day = 0; $day < 7; $day++ ) { 
							$dayLabel = date('l', strtotime("Sunday +{$day} days"));
							// $dayKey = date('D', strtotime("Sunday +{$day} days"));

							$optionKey = "choose_bp_day_" . $day;
							if ( empty($arr_availability_by_weekday[$day]) ) {
								$dayLabel .= ' (Unavailable)';
							}
					?> 
						<div class="form-group">
						<input class="choose_bp_day" type="radio" id="<?php echo $optionKey?>" name="weekday" value="<?php echo $day?>" />
							<label for="<?php echo $optionKey?>"><?php echo $dayLabel; ?></label>
						</div>
					<?php } ?>
					</div>
					<div class="step2">
						<div class="form-group text-center mt-2 flex">
						<table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
							<thead>
							<tr>
								<td width="40" nowrap>Select</td>
								<td width="40" nowrap>Time</td>
								<td width="40" nowrap>Status</td>
							</tr>
							</thead>
							<tbody id="tbl_bookingperiods">
							</tbody>
						</table>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary btn-sm step1" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary btn-sm step1 btn-next" disabled>Next</button>
					<button type="button" class="btn btn-secondary btn-sm step2 d-none btn-previous">Previous</button>
					<button type="button" class="btn btn-primary btn-sm step2 d-none" name="Save" value="Save" id="btnBpSave">Save</button>
				</div>
			</div>
		</form>
	</div>
</div>
<?php
	$settingKey = "AVAILABLE_WEEK_DAYS";
?>
<div class="modal fade" id="bookingDaysModal" tabindex="-1" role="dialog" aria-labelledby="saveModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<form method="post" class="form-horizontal setting_form" id="">
			<input type="hidden" name="settingKey" id="settingKey" value="<?php echo $settingKey;?>" />
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="saveModalLabel">Unavailable/Available Week Days</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body table-responsive">
					<table border="0" cellspacing="0" cellpadding="5" width="100%" class="table step1">
						<thead>
							<tr>
								<td width="20" nowrap>Select</td>
								<td width="40" nowrap>Week Day</td>
								<td width="40" nowrap>Status</td>
							</tr>
						</thead>
						<tbody>
							<?php 
								for( $day = 0; $day < 7; $day++ ) { 
									$dayLabel = date('l', strtotime("Sunday +{$day} days"));
									
									$optionKey = 'weekday_' . $day;
									$checked = "";
									$status = "";
									if ( empty($arr_availability_by_weekday[$day] )) {
										$checked = "checked";
										$status = "Unavailable";
									}
							?>
							<tr>
								<td width="20" nowrap>
									<input class="chk_availability" type="checkbox" id="<?php echo $optionKey?>" name="weekday_availability[]" value="<?php echo $day;?>" <?php echo $checked?>/>
								</td>
								<td width="40" nowrap>
									<label for="<?php echo $optionKey?>"><?php echo $dayLabel; ?></label>
								</td>
								<td width="40" nowrap>
									<span id="<?php echo $optionKey?>_status"><?php echo $status; ?></span>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<h6 class="step2">Select individual Systems that you want the changes applied to</p>
					<table border="0" cellspacing="0" cellpadding="5" width="100%" class="table step2">
						<thead>
						<tr>
							<td width="40" nowrap>Select</td>
							<td width="40" nowrap>Name</td>
						</tr>
						</thead>
						<tbody id="tbl_weekday_systems">

						</tbody>
					</table>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary btn-sm step1" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary btn-sm step1 btn-next" data-modal-name="available_weekdays">Next</button>
					<button type="button" class="btn btn-secondary btn-sm step2 btn-previous" >Previous</button>
					<button type="submit" class="btn btn-primary btn-sm step2" name="Save" value="Save" id="btnSave">Save</button>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
	$(document).ready(function() { 
		var apiUri = "/api/settings.php";

		// Irregular setting actions
		$('#btn_add_irregular_bookingperiods').on('click', function() {
			var start_hour = parseInt($("#irregular_start_hour").val());
			var start_minutes = parseInt($("#irregular_start_minutes").val());
			var start_AP = $("#irregular_start_AP").val();
			var end_hour = parseInt($("#irregular_end_hour").val());
			var end_minutes = parseInt($("#irregular_end_minutes").val());
			var end_AP = $("#irregular_end_AP").val();

			start_hour += (start_AP.toLowerCase() === 'pm' && start_hour < 12) ? 12 : 0;
        	end_hour += (end_AP.toLowerCase() === 'pm' && end_hour < 12) ? 12 : 0;

			var from_in_mins = start_hour * 60 + start_minutes;
			var to_in_mins = end_hour * 60 + end_minutes;

			if (from_in_mins >= to_in_mins) {
				alert("Please select a valid booking period.");
				return;
			}

			var bFoundAnyOverlap = false;
			var indexToInsert = -1; //
			$("#list_bookingperiods option").each(function(index) {
				// Access each option using $(this)
				var optionValue = $(this).val(); // Get the value of the current option
				var arr_parts = optionValue.split("-"); //[0]: FromInMins, [1]: ToInMins
				arr_parts[0] = parseInt(arr_parts[0]);
				arr_parts[1] = parseInt(arr_parts[1]);
				
				if ((from_in_mins >= arr_parts[0] && from_in_mins < arr_parts[1]) || (to_in_mins > arr_parts[0] && to_in_mins <= arr_parts[1])) {
					bFoundAnyOverlap = true;
					return false;
				}

				if (from_in_mins > arr_parts[0])
					indexToInsert = index;
			});

			if (bFoundAnyOverlap) {
				alert("Time slots can not overlap.");
				return;
			}

			// Create a new option element
			var newOption = $('<option>').val(from_in_mins + "-" + to_in_mins + "-irregular").text(getDisplayTextFromMinutes(from_in_mins, to_in_mins));
			var targetOption = $("#list_bookingperiods option").eq(indexToInsert + 1);
			if (targetOption.length)
				newOption.insertBefore(targetOption);
			else
				$("#list_bookingperiods").append(newOption);
		});

		// Added by Hennadii (2024-03-26)
		$('.btn-show-modal').on('click', function() {
			$('.step1').removeClass('d-none');
			$('.step2').addClass('d-none');
		});

		// Updated by Hennadii (2024-03-26)
		$('#delIrTimes').on('click', function() {
			$("#list_bookingperiods option:selected").remove();
		});

		// Updated by Hennadii (2024-03-26)
		$('#delIrAll').on('click', function() {
			$("#list_bookingperiods option").remove();
		});

		// Updated by Hennadii (2024-03-26)
		$('.choose_ir_day').click(function(e){
			$("#IRREGULAR_TIME_FORM .btn-next").removeAttr("disabled");
			$("#isWeekdayAvailable").val($(this).attr('data-availability'));
			
			var weekday = $(this).val();
			var formData = [];
			formData.push({ name: "action", value: "get_bookingperiods_by_weekday" });
			formData.push({ name: "weekday", value: $(this).val() });

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);

				$("#list_bookingperiods").find('option').remove();
				
				if ( res.data.length ) {
					for ( var i = 0; i < res.data.length; i++ ) {
						$('#list_bookingperiods').append("<option value=\"" + res.data[i].FromInMinutes + "-" + res.data[i].ToInMinutes + "\">" + res.data[i].DisplayText + "</option>");
					}
				}
	        });
		});

		// Updated by Awesome (2024-03-26)
		$('.choose_bp_day').click(function(e){
			$("#AVAILABLE_PERIOD_FORM .btn-next").removeAttr("disabled");

			$('#tbl_bookingperiods').html("");

			var weekday = $(this).val();
			var formData = [];
			formData.push({ name: "action", value: "get_bookingperiods_by_weekday" });
			formData.push({ name: "weekday", value: $(this).val() });

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);
				
				if (res.data.length) {
					for (var i = 0; i < res.data.length; i++) {
						var bookingperiod_id = res.data[i].id;
						var isAvailable = res.data[i].isAvailable;

						var html = `<tr>
							<td width="20" nowrap>
								<input class="chk_availability" type="checkbox" id="unavailable_bookingperiod_${i}" name="unavailable_bookingperiod[${bookingperiod_id}]" ${isAvailable ? "" : "checked"} />
							</td>
							<td width="40" nowrap>
								<label for="unavailable_bookingperiod_${i}">${res.data[i].DisplayText}</label>
							</td>
							<td width="40" nowrap>
								<span id="unavailable_bookingperiod_${i}_status">${isAvailable ? "" : "Unavailable"}</span>
							</td>
						</tr>`;
						
						$('#tbl_bookingperiods').append(html);
					}

					$('.chk_availability').change(function() {
						var str_optionKey = $(this).attr('id');
						var isChecked = $(this).prop("checked");
						$('#' + str_optionKey + "_status").html(isChecked ? "Unavailable" : "");
					})
				}
			});
		});

		// Added by Hennadii (2024-03-26)
		$('.btn-next').click(function(e) {
			e.preventDefault();
			$('.step2').removeClass('d-none');
			$('.step1').addClass('d-none');

			var parentForm = $(this).closest('form');
			var weekday = parentForm.find('input[type="radio"]:checked').val();
			
			const getDayName = dayNumber => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][dayNumber];
			parentForm.find('.span_cur_weekday').html(getDayName(weekday));

			if ($(this).attr('data-modal-name') == 'available_weekdays') {
				var formData = [];
				formData.push({ name: "action", value: "get_all_systems" });

				$.post(apiUri, formData, function(data) {
					var res = JSON.parse(data);

					$("#tbl_weekday_systems").empty();

					if (res.status != "success")
						return;
					
					var systemsData = res.data;

					for (var i = 0; i < systemsData.length; i++) {
						var system = systemsData[i];
						var html = `<tr>
							<td>
								<div class="form-group">
									<input name="Systems[]" value="${system.SystemId}" type="checkbox" checked>
								</div>
							</td>
							<td>${system.FullName}</td>
						</tr>`;
						
						$("#tbl_weekday_systems").append(html);
					}
				});
			}
		});

		// Added by Hennadii (2024-03-26)
		$('.btn-previous').click(function(e) {
			e.preventDefault();
			$('.step1').removeClass('d-none');
			$('.step2').addClass('d-none');
		})

		$('#btnIrSave').click(function(e){
			e.preventDefault();
			$("#list_bookingperiods option").prop('selected', true);
      
			var formData = $("#IRREGULAR_TIME_FORM").serializeArray();
			formData.push({ name: "action", value: "save_booking_periods" });

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);
				
				if (res.status == "error"){
					$(".Message").removeClass("text-success");
					$(".Message").addClass("text-danger");
				} else {
					$(".Message").removeClass("text-danger");
					$(".Message").addClass("text-success");
					location.reload();
				}
	        });
		});

    	$('#btnBpSave').click(function(e){
			e.preventDefault();

			var formData = $("#AVAILABLE_PERIOD_FORM").serializeArray();

			formData.push({ name: "action", value: "save_booking_periods_availability" });

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);
				
				if (res.status == "error"){
					$(".Message").removeClass("text-success");
					$(".Message").addClass("text-danger");
				} else {
					$(".Message").removeClass("text-danger");
					$(".Message").addClass("text-success");
					location.reload();
				}
	        });
		});

		$('body').on( 'click', '.changeAvailable', function() { 
			parent = $(this).closest('.available_row')
			if( $(this).prop("checked") ){
				parent.find(".status_label").html("Unavailable")
				parent.find(".status_value").val("U")
			} else {
				parent.find(".status_label").html("Available")
				parent.find(".status_value").val("A")
			}
		})

		$('#btnAvailableSave').click(function(e){
			e.preventDefault();

			var formData = $("#AVAILABLE_PERIOD_FORM").serializeArray();
			formData.push({ name: "action", value: "save_available" });

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);
				if(res.status == "error"){
					$(".Message").removeClass("text-success");
					$(".Message").addClass("text-danger");
				} else {
					$(".Message").removeClass("text-danger");
					$(".Message").addClass("text-success");
					location.reload();
				}
	        });
		});

		// Added by Hennadii
		$('.chk_availability').change(function() {
			var str_optionKey = $(this).attr('id');
			var isChecked = $(this).prop("checked");
			$('#' + str_optionKey + "_status").html(isChecked ? "Unavailable" : "");
		})

	});
</script>