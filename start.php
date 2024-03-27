<?php

$menu = "start";
require_once('header.php');

$message = "";

if( isset($_POST['Submit'])){
	if( !isset($_POST['five_days']))
		$_SESSION['appointment_data']['five_days'] = "";

	// check Availability for Selected Date
	if( checkAvailability()) {
		header('Location: '. SECURE_URL . SELECT_PAGE, true, 301);
		exit(0);
	} else {
		$message = '<p class="ErrorMessage fst-italic fw-bold show">'._lang('no_time_available').'</p>';
	}
}

// Initialize SESSION
if( !isset( $_SESSION['appointment_data'] ))
	$_SESSION['appointment_data'] = array();
?>

<h4 class="page-name">Start ></h4>
<h1 class="fw-bold">Chromis Medical Appointments</h1>

<form method="post" class="form-horizontal" id="APP_FORM">
	<?php echo $message; ?>
	<h6>&nbsp;</h6>
	<div class="table-responsive">
		<table class="appForm table">
			<tr>
				<td class="form-label">Location:</td>
				<td>
					<?php 
						foreach ( $arrLocations as $key => $objLocation){
							$checked = "";
							
							if( $key == $arrAppData['location'] )
								$checked = "checked";

							echo '<div class="form-check">
									<label class="form-check-label" for="location_' . $key . '">' . $objLocation['name'] .'</label>
									<input type="radio" required class="form-check-input" name="location" id="location_' . $key . '" value="' . $key . '" ' . $checked.'/>
								</div>';
						}
					?>
				</td>
			</tr>
			<tr>
				<td class="form-label">Services:</td>
				<td>
					<?php 
						foreach( $arrServices as $key => $objService){
							$checked = "";
							if( $key == $arrAppData['service'] )
								$checked = "checked";

							echo '<div class="form-check">
									<label class="form-check-label" for="service_'.$key.'">' . $objService['fullname'] . '</label>
									<input type="radio" required class="form-check-input" name="service" id="service_' . $key . '" value="' . $objService['id'].'" ' . $checked . '/>
								</div>';
						}
					?>
				</td>
			</tr>
			<tr>
				<td class="form-label">Date of Appointment:</td>
				<td>
					<input type="text" name="date_appointment" required="" class="required date valid form-control form-control-sm" placeholder="dd/mm/yyyy" id="date_appointment" value="<?php echo $arrAppData['date_appointment'];?>" />
					<div class="form-check mt-2">
						<input type="checkbox" class="form-check-input" name="five_days" id="five_days" <?php if($arrAppData['five_days']) echo "checked";?>/>
						<label for="five_days">Click here for a five day search</label>
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="text-center">
					<button type="submit" class="btn btn-primary btn-sm" name="Submit" value="Save" disabled>Next >></button>
				</td>
			</tr>
		</table>
	</div>
</form>

<?php
require_once('footer.php');
?>