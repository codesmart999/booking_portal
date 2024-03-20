<?php 
	require_once('header.php');

	$message = "";
    $db = getDBConnection();
    // Save Settings
    if( isset($_POST['Save']) ) {	
   	    $key = $_POST['settingKey'];
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
		$message = '<p class="Message fst-italic fw-bold text-success show">'._lang('success_update').'</p>';
		unset($_POST['Save']);
    }

    // Get Existing Settings
    $arrSettings = array();
    $stmt = $db->prepare("SELECT * FROM settings WHERE `category`='booking'");
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
?>
<h4 class="page-title">Manage Default Booking Settings</h4>
<p>Denotes option which can be applied across all or individual existing systems.</p>
<p>Default Booking Settings are automatically applied when a new Individual System is added. This saves entering the settings manually each time. The settings can, of course, be modified at the Individual System Level.</p>
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
	    		<td><a href="#" data-toggle="modal" data-target="#statusModal">Status</a></td>
	    		<td>Set Calendar booking status indicators, e.g., Interim, Final.
		    		<?php
						$settingKey = "BOOKING_STATUS";
						$settingValue = json_decode($arrSettings[$settingKey]['value'], true);
					?>
					<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="saveModalLabel" aria-hidden="true">
					    <form method="post" class="form-horizontal setting_form" id="">
					    	<input type="hidden" name="settingKey" value="<?php echo $settingKey;?>" />
					        <div class="modal-dialog" role="document">
					            <div class="modal-content">
					                <div class="modal-header">
					                    <h5 class="modal-title" id="saveModalLabel">Default Booking Status</h5>
					                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					                        <span aria-hidden="true">&times;</span>
					                    </button>
					                </div>
					                <div class="modal-body">
					                    <div class="form-group">
					                    	<label for="">1. Active Status?</label>
					                    	<?php $optionKey = "booking_status"; 
					                    		if( !isset($settingValue[$optionKey]) )
					                    			$settingValue[$optionKey] = "no"; 
					                    	?>
					                    	<div class="display-flex">
						                    	<div class="form-check">
						                        	<input class="" type="radio" id="status_yes" name="<?php echo $optionKey?>" value="yes" <?php if($settingValue[$optionKey]=="yes") echo "checked"?>/>
						                        	<label class="form-check-label" for="status_yes">Yes</label>
						                        </div>
						                    	<div class="form-check">
						                        	<input class="" type="radio" id="status_no" name="<?php echo $optionKey?>" value="no" <?php if($settingValue[$optionKey]=="no") echo "checked"?>/>
						                        	<label class="form-check-label" for="status_no">No</label>
						                        </div>
						                    </div>
					                    </div>
					                    <div class="form-group">
					                    	<label for="">2. Highlight Booking with Status Colour?</label>
					                    	<?php $optionKey = "highlight_status_color"; 
					                    		if( !isset($settingValue[$optionKey]) )
					                    			$settingValue[$optionKey] = "no"; 
					                    	?>
					                    	<div class="display-flex">
						                    	<div class="form-check">
						                        	<input class="" type="radio" id="status_yes" name="<?php echo $optionKey?>" value="yes" <?php if($settingValue[$optionKey]=="yes") echo "checked"?>/>
						                        	<label class="form-check-label" for="status_yes">Yes</label>
						                        </div>
						                    	<div class="form-check">
						                        	<input class="" type="radio" id="status_no" name="<?php echo $optionKey?>" value="no" <?php if($settingValue[$optionKey]=="no") echo "checked"?>/>
						                        	<label class="form-check-label" for="status_no">No</label>
						                        </div>
						                    </div>
					                    </div>
					                    <div class="form-group">
					                    	<label for="">3. Send Customers Email advising of Status Change by Default.</label>
					                    	<?php $optionKey = "status_email_advising"; 
					                    		if( !isset($settingValue[$optionKey]) )
					                    			$settingValue[$optionKey] = "no"; 
					                    	?>
					                    	<div class="display-flex">
						                    	<div class="form-check">
						                        	<input class="" type="radio" id="status_yes" name="<?php echo $optionKey?>" value="yes" <?php if($settingValue[$optionKey]=="yes") echo "checked"?>/>
						                        	<label class="form-check-label" for="status_yes">Yes</label>
						                        </div>
						                    	<div class="form-check">
						                        	<input class="" type="radio" id="status_no" name="<?php echo $optionKey?>" value="no" <?php if($settingValue[$optionKey]=="no") echo "checked"?>/>
						                        	<label class="form-check-label" for="status_no">No</label>
						                        </div>
						                    </div>
					                    </div>
					                </div>
					                <div class="modal-footer">
					                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
					                    <button type="submit" class="btn btn-primary btn-sm" name="Save" value="Save" id="btnSave">Save</button>
					                </div>
					            </div>
					        </div>
					    </form>
					</div>
	    		</td>
	    	</tr>
			<tr>
	    		<td><a href="#" data-toggle="modal" data-target="#attendanceModal">Attendance</a></td>
	    		<td>Set Calendar default attendance flag.
		    		<?php
						$settingKey = "BOOKING_ATTENDANCE";
						$settingValue = json_decode($arrSettings[$settingKey]['value'], true);
					?>
					<div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="saveModalLabel" aria-hidden="true">
					    <form method="post" class="form-horizontal setting_form" id="">
					    	<input type="hidden" name="settingKey" value="<?php echo $settingKey;?>" />
					        <div class="modal-dialog" role="document">
					            <div class="modal-content">
					                <div class="modal-header">
					                    <h5 class="modal-title" id="saveModalLabel">Default Booking Attendance</h5>
					                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					                        <span aria-hidden="true">&times;</span>
					                    </button>
					                </div>
					                <div class="modal-body">
					                    <div class="form-group">
					                    	<label for="">1. Default Attendance flag?</label>
					                    	<?php $optionKey = "booking_attendance"; 
					                    		if( !isset($settingValue[$optionKey]) )
					                    			$settingValue[$optionKey] = "no"; 
					                    	?>
					                    	<div class="display-flex">
						                    	<div class="form-check">
						                        	<input class="" type="radio" id="status_yes" name="<?php echo $optionKey?>" value="yes" <?php if($settingValue[$optionKey]=="yes") echo "checked"?>/>
						                        	<label class="form-check-label" for="status_yes">Yes</label>
						                        </div>
						                    	<div class="form-check">
						                        	<input class="" type="radio" id="status_no" name="<?php echo $optionKey?>" value="no" <?php if($settingValue[$optionKey]=="no") echo "checked"?>/>
						                        	<label class="form-check-label" for="status_no">No</label>
						                        </div>
						                    </div>
					                    </div>
					                    <div class="form-group">
					                    	<label for="">2. Show Attendance Option?</label>
					                    	<?php $optionKey = "show_attendance_option"; 
					                    		if( !isset($settingValue[$optionKey]) )
					                    			$settingValue[$optionKey] = "no"; 
					                    	?>
					                    	<div class="display-flex">
						                    	<div class="form-check">
						                        	<input class="" type="radio" id="status_yes" name="<?php echo $optionKey?>" value="yes" <?php if($settingValue[$optionKey]=="yes") echo "checked"?>/>
						                        	<label class="form-check-label" for="status_yes">Yes</label>
						                        </div>
						                    	<div class="form-check">
						                        	<input class="" type="radio" id="status_no" name="<?php echo $optionKey?>" value="no" <?php if($settingValue[$optionKey]=="no") echo "checked"?>/>
						                        	<label class="form-check-label" for="status_no">No</label>
						                        </div>
						                    </div>
					                    </div>
					                </div>
					                <div class="modal-footer">
					                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
					                    <button type="submit" class="btn btn-primary btn-sm" name="Save" value="Save" id="btnSave">Save</button>
					                </div>
					            </div>
					        </div>
					    </form>
					</div>
	    		</td>
	    	</tr>
	    </tbody>
	</table>
</div>
<?php
require_once('footer.php');
?>