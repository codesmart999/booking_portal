<?php 
	require_once('header.php');

	$message = "";
    $db = getDBConnection();
    // Save Settings
    if( isset($_POST['Save']) ) {
   	    $key = $_POST['settingKey'];

		switch ($key) {
			case 'AVAILABLE_WEEK_DAYS':
				$arr_unavailable_weekdays = array_values($_POST['weekday_availability']);

				$stmt = $db->prepare( 'TRUNCATE TABLE setting_weekdays' );
				$stmt->execute() or die($stmt->error);

				$stmt = $db->prepare("INSERT INTO `setting_weekdays` (weekday, isAvailable) VALUES (?, ?)");
				for ($day = 0; $day < 7; $day++) {
					$isAvailable = !in_array($day, $arr_unavailable_weekdays);
					$stmt->bind_param('ii', $day, $isAvailable);
					$stmt->execute() or die($stmt->error);
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

	// Get setting_weekdays
    $arrSettingWeekdays = array();
    $stmt = $db->prepare("SELECT id, weekday, isAvailable FROM setting_weekdays WHERE `SystemId`=0");
	$stmt->execute();
    $stmt->bind_result($id, $weekday, $isAvailable);
    $stmt->store_result();
    while ($stmt->fetch()) {
    	$arrSettingWeekdays[$weekday] = [
    		'id' => $id,
    		'isAvailable' => $isAvailable
    	];
    }

	// Get setting_bookingperiods
	$arrSettingBookingPeriods = array();
    $stmt = $db->prepare("SELECT id, weekday, FromInMinutes, ToInMinutes, isRegular, isAvailable FROM setting_bookingperiods WHERE `SystemId`=0 ORDER BY weekday ASC, FromInMinutes ASC");
	$stmt->execute();
    $stmt->bind_result($id, $weekday, $from_in_mins, $to_in_mins, $isRegular, $isAvailable);
    $stmt->store_result();
    while ($stmt->fetch()) {
    	$arrSettingBookingPeriods[$weekday] = [
    		'id' => $id,
			'FromInMinutes' => $from_in_mins,
			'ToInMinutes' => $to_in_mins,
    		'isRegular' => $isRegular,
			'isAvailable' => $isAvailable,
    	];
    }
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
	    		<td><a href="#" data-toggle="modal" data-target="#regularModal">Regular Booking Periods</a></td>
	    		<td>Set Regular Booking Periods in the day, e.g.,10.00 AM to 10.30 AM.</td>
	    	</tr>
	    	<tr>
	    		<td><a href="#" data-toggle="modal" data-target="#irregularModal">Irregular Booking Periods</a></td>
	    		<td>Set Irregular Booking Periods in the day, e.g.,10.20 AM to 11.00 AM.</td>
	    	</tr>
	    	<tr>
	    		<td><a href="#" data-toggle="modal" data-target="#bookingPeriodModal">Unavailable/Available Booking Periods</a></td>
	    		<td>Set Booking Periods in either Regular or Irregular Booking Periods as Unavailable. <br>Bookings Periods set as Unavailable can be reset at any time to Available.</td>
	    	</tr>
	    	<tr>
	    		<td><a href="#" data-toggle="modal" data-target="#bookingDaysModal">Unavailable/Available Week Days</a></td>
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
    <form method="post" class="form-horizontal setting_form" id="">
    	<input type="hidden" name="settingKey" id="settingKey" value="<?php echo $settingKey;?>" />
        <div class="modal-dialog" role="document">
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
							$dayLable = date('l', strtotime("Sunday +{$day} days"));
							$dayKey = date('D', strtotime("Sunday +{$day} days"));
					?>
					<h6 class="day-title"><?php echo $dayLable;?></h6>
                    <div class="form-group">
                    	<table style="width:100%; margin-bottom:0">
                    		<tr>
                    			<td width="33%">Start Time</td>
                    			<td width="33%">End Time</td>
                    			<td width="33%">Period Duration</td>
                    		</tr>
                    		<tr>
                    			<td width="33%">
                    				<?php $optionKey = $dayKey."_start_hour" ?>
	                    			<select name="<?php echo $optionKey; ?>">
			                        	<?php
			                    		for( $i = 0; $i < 12; $i++){
				                        	$selected = "";
			                    			if( $settingValue[$optionKey] == $i )
			                    				$selected = "selected";
			                    			echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
			                    		}
			                    		?>
			                        </select>
                    				<?php $optionKey = $dayKey."_start_minutes" ?>
	                    			<select name="<?php echo $optionKey; ?>">
			                        	<option value="00">00</option>
			                        	<?php
			                    		for( $i = 5; $i < 60; $i += 1){
				                        	$selected = "";
			                    			if( $settingValue[$optionKey] == $i )
			                    				$selected = "selected";
			                    			echo '<option value="'.$i.'" '.$selected.'>'.sprintf("%02d", $i).'</option>';
			                    		}
			                    		?>
			                        </select>
                    				<?php $optionKey = $dayKey."_start_AP"; 
                    				if( isset($settingValue[$optionKey]))
                    					$startAP = $settingValue[$optionKey];
                    				else
                    					$startAP = "AM";
                    				?>
	                    			<select name="<?php echo $optionKey; ?>">
			                        	<option value="AM" <?php if($startAP=="AM") echo 'selected';?>>AM</option>
			                        	<option value="PM" <?php if($startAP=="PM") echo 'selected';?>>PM</option>
			                        </select>
			                    </td>
                    			<td width="33%">
	                    			<?php $optionKey = $dayKey."_end_hour" ?>
	                    			<select name="<?php echo $optionKey; ?>">
			                        	<?php
			                    		for( $i = 0; $i < 12; $i++){
				                        	$selected = "";
			                    			if( $settingValue[$optionKey] == $i )
			                    				$selected = "selected";
			                    			echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
			                    		}
			                    		?>
			                        </select>
                    				<?php $optionKey = $dayKey."_end_minutes" ?>
	                    			<select name="<?php echo $optionKey; ?>">
			                        	<option value="00">00</option>
			                        	<?php
			                    		for( $i = 5; $i < 60; $i += 1){
				                        	$selected = "";
			                    			if( $settingValue[$optionKey] == $i )
			                    				$selected = "selected";
			                    			echo '<option value="'.$i.'" '.$selected.'>'.sprintf("%02d", $i).'</option>';
			                    		}
			                    		?>
			                        </select>
                    				<?php $optionKey = $dayKey."_end_AP";
                    				if( isset($settingValue[$optionKey]))
                    					$endAP = $settingValue[$optionKey];
                    				else
                    					$endAP = "AM";
                    				?>
	                    			<select name="<?php echo $optionKey; ?>">
			                        	<option value="AM" <?php if($endAP=="AM") echo 'selected';?>>AM</option>
			                        	<option value="PM" <?php if($endAP=="PM") echo 'selected';?>>PM</option>
			                        </select>
			                    </td>
			                    <td width="33%">
                    				<?php $optionKey = $dayKey."_duration_hours" ?>
	                    			<select name="<?php echo $optionKey; ?>">
			                        	<?php
			                    		for( $i = 0; $i < 24; $i++){
			                    			$selected = "";
			                    			if( $settingValue[$optionKey] == $i )
			                    				$selected = "selected";
			                    			echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
			                    		}
			                    		?>
			                        </select>
                    				<?php $optionKey = $dayKey."_duration_minutes" ?>
	                    			<select name="<?php echo $optionKey; ?>">
			                        	<option value="00">00</option>
			                        	<?php
			                    		for( $i = 5; $i < 60; $i += 1){
				                        	$selected = "";
			                    			if( $settingValue[$optionKey] == $i )
			                    				$selected = "selected";
			                    			echo '<option value="'.$i.'" '.$selected.'>'.sprintf("%02d", $i).'</option>';
			                    		}
			                    		?>
			                        </select>
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
        </div>
    </form>
</div>

<?php
	$settingKey = "DEFAULT_IRREGULAR_TIME";
	$settingValue = json_decode($arrSettings[$settingKey]['value'], true);
?>
<div class="modal fade" id="irregularModal" tabindex="-1" role="dialog" aria-labelledby="saveModalLabel" aria-hidden="true">
    <form method="post" class="form-horizontal setting_form" id="IRREGULAR_TIME_FORM">
    	<input type="hidden" name="settingKey" id="settingKey" value="<?php echo $settingKey;?>" />
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saveModalLabel">Irregular Booking Period</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                	<div class="step1" >
						<?php 
							for( $day = -1; $day < 7; $day++ ) { 
								if( $day == -1 ) {
									$dayLable = "All days";
									$dayKey = "All";
								} else {
									$dayLable = date('l', strtotime("Sunday +{$day} days"));
									$dayKey = date('D', strtotime("Sunday +{$day} days"));
								}

								$optionKey = $dayKey . "_ir_day";
								$checked = "";
								if( isset($settingValue[$optionKey]) )
									$checked = "checked";
						?>
	                    <div class="form-group">
	                        <input class="choose_ir_day" type="radio" id="<?php echo $optionKey?>" name="irregularDay" value="<?php echo $dayKey?>"/>
	                    	<label for="<?php echo $optionKey?>"><?php echo $dayLable; ?></label>
	                    </div>
	                    <?php } ?>
	                </div>
	                <div class="step2 hide">
	                	<table style="width:100%; margin-bottom:0">
                    		<tr>
                    			<td width="33%">Start Time</td>
                    			<td width="33%">End Time</td>
                    			<td width="33%"></td>
                    		</tr>
                    		<tr>
                    			<td>
                    				<select id="insertStartHour">
			                        	<?php
			                    		for( $i = 1; $i <= 12; $i++){
			                    			echo '<option value="'.$i.'">'.$i.'</option>';
			                    		}
			                    		?>
			                        </select>
			                      	<select id="insertStartMinutes">
			                        	<option value="00">00</option>
			                        	<?php
			                    		for( $i = 5; $i < 60; $i += 1){
			                    			echo '<option value="'.$i.'">'.sprintf("%02d", $i).'</option>';
			                    		}
			                    		?>
			                        </select>
			                        <select id="insertStartAP">
			                        	<option value="AM">AM</option>
			                        	<option value="PM">PM</option>
			                        </select>
                    			</td>
								<td>
                    				<select id="insertEndHour">
			                        	<?php
			                    		for( $i = 1; $i <= 12; $i++){
			                    			echo '<option value="'.$i.'">'.$i.'</option>';
			                    		}
			                    		?>
			                        </select>
			                      	<select id="insertEndMinutes">
			                        	<option value="00">00</option>
			                        	<?php
			                    		for( $i = 5; $i < 60; $i += 1){
			                    			echo '<option value="'.$i.'">'.sprintf("%02d", $i).'</option>';
			                    		}
			                    		?>
			                        </select>
			                        <select id="insertEndAP">
			                        	<option value="AM">AM</option>
			                        	<option value="PM">PM</option>
			                        </select>
                    			</td>
                    			<td>
                    				<button type="button" id="addIrTimes" class="btn btn-primary btn-sm">Include</button>
                    			</td>
                    		</tr>
                    	</table>
                    	<div class="form-group text-center mt-2 flex">
                    		<select name="irregularTimes[]" id="irregularTimes" multiple="multiple" style="height:400px; width: 200px;">
                    		</select>
                    		<div class="mt-2">
	                    		<button type="button" class="btn btn-primary btn-sm" id="delIrTimes">Delete</button>
	                    		<button type="button" class="btn btn-primary btn-sm" id="delIrAll">Delete All</button>
	                    	</div>
                    	</div>
	                </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm" name="Save" value="Save" id="btnIrSave">Save</button>
                </div>
            </div>
        </div>
    </form>
</div>
<?php
	$settingKey = "AVAILABLE_BOOKING_PERIOD";
	$settingValue = json_decode($arrSettings[$settingKey]['value'], true);
?>
<div class="modal fade" id="bookingPeriodModal" tabindex="-1" role="dialog" aria-labelledby="saveModalLabel" aria-hidden="true">
    <form method="post" class="form-horizontal setting_form" id="AVAILABLE_PERIOD_FORM">
    	<input type="hidden" name="settingKey" id="settingKey" value="<?php echo $settingKey;?>" />
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saveModalLabel">Unavailable/Available Booking Periods</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                	<div class="step1">
						<?php 
							for( $day = -1; $day < 7; $day++ ) { 
								if( $day == -1 ) {
									$dayLable = "All days";
									$dayKey = "All";
								} else {
									$dayLable = date('l', strtotime("Sunday +{$day} days"));
									$dayKey = date('D', strtotime("Sunday +{$day} days"));
								}

								$optionKey = $dayKey . "_period";
								$checked = "";
								if( isset($settingValue[$optionKey]) )
									$checked = "checked";
						?>
	                    <div class="form-group">
	                        <input class="choose_available_day" type="radio" id="<?php echo $optionKey?>" name="availableDay" value="<?php echo $dayKey?>"/>
	                    	<label for="<?php echo $optionKey?>"><?php echo $dayLable; ?></label>
	                    </div>
	                    <?php } ?>
	                </div>
	                <div class="step2 hide">
	                	<table style="width:100%; margin-bottom:0">
	                		<thead>
	                    		<tr>
	                    			<td width="10%">Select</td>
	                    			<td width="25%">Start Time</td>
	                    			<td width="25%">End Time</td>
	                    			<td width="25%">Status</td>
	                    		</tr>
	                    	</thead>
	                    	<tbody id="availablePeriodStatus">
                    		</tbody>
                    	</table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm" name="Submit" value="Submit" id="btnAvailableSave">Save</button>
                </div>
            </div>
        </div>
    </form>
</div>
<?php
	$settingKey = "AVAILABLE_WEEK_DAYS";
?>
<div class="modal fade" id="bookingDaysModal" tabindex="-1" role="dialog" aria-labelledby="saveModalLabel" aria-hidden="true">
    <form method="post" class="form-horizontal setting_form" id="">
    	<input type="hidden" name="settingKey" id="settingKey" value="<?php echo $settingKey;?>" />
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saveModalLabel">Unavailable/Available Week Days</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body table-responsive">
					<table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
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
									if ( isset($arrSettingWeekdays[$day]) && empty($arrSettingWeekdays[$day]['isAvailable'] )) {
										$checked = "checked";
										$status = "Unavailable";
									}
							?>
							<tr>
								<td width="20" nowrap>
									<input class="chk_weekday_available" type="checkbox" id="<?php echo $optionKey?>" name="weekday_availability[]" value="<?php echo $day;?>" <?php echo $checked?>/>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm" name="Save" value="Save" id="btnSave">Save</button>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
	$(document).ready(function() { 
		var apiUri = "/api/settings.php";

		// Irregular setting actions
		$('#addIrTimes').on('click', function() {

		});

		$('#delIrTimes').on('click', function() {
			$("#irregularTimes").find('option:selected').remove();
		});
		$('#delIrAll').on('click', function() {
			$("#irregularTimes").find('option').remove();
		});

		$('.choose_ir_day').click(function(e){
			var formData = [];
			formData.push({ name: "action", value: "get_irregular" });
			formData.push({ name: "irregularDay", value: $(this).val() });

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);
				$("#IRREGULAR_TIME_FORM .step2").show();

				if( res.data.length ) {
					$("#irregularTimes").find('option').remove();
					for( var i=0; i<res.data.length; i++ ) {
						$('#irregularTimes').append("<option value=\"" + res.data[i] + "\">" + res.data[i] + "</option>");
					}
				}
	        });
		});

		$('#btnIrSave').click(function(e){
			e.preventDefault();
			$("#irregularTimes").find('option').attr('selected', 'selected');

			var formData = $("#IRREGULAR_TIME_FORM").serializeArray();
			formData.push({ name: "action", value: "save_irregular" });

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

		// Available / Unavailable Booking Period
		$('.choose_available_day').click(function(e){
			var formData = [];
			formData.push({ name: "action", value: "get_available" });
			formData.push({ name: "availableDay", value: $(this).val() });

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);
				$("#AVAILABLE_PERIOD_FORM .step2").show();
				if( res.data.length ) {
					$("#availablePeriodStatus").empty();
					for( var i=0; i<res.data.length; i++ ) {
						item = res.data[i];
						status = "Available", checked = "";
						if( res.data[i].status == 'U') {
							status = "Unavailable";
							checked = "checked"
						}

						html = `<tr class="available_row">
						<td><input type='checkbox' class='changeAvailable' ${checked}/></td>
						<td>${res.data[i].from}
						<input type='hidden' name='avaialble_from[]' value="${res.data[i].from}"/></td>
						<td>${res.data[i].to}
						<input type='hidden' name='avaialble_to[]' value="${res.data[i].to}"/></td>
						<td><span class="status_label">${status}</span>
						<input class="status_value" type='hidden' name='avaialble_status[]' value="${res.data[i].status}"/>
						</td>
						</tr>`;

						$('#availablePeriodStatus').append( html );
					}
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

		$('.chk_weekday_available').change(function() {
			var str_optionKey = $(this).attr('id');
			var isChecked = $(this).prop("checked");
			$('#' + str_optionKey + "_status").html(isChecked ? "Unavailable" : "");
		})

	});
</script>