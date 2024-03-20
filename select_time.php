<?php

$menu = "select";
require_once('header.php');

$format_date = format_date( $arrAppData['date_appointment'] );

if( isset($_POST['Submit'])){
	// adjust booking date
	if( isset($_POST['booking_time0']) ) {
		$_SESSION['appointment_data']['date_appointment'] = date('d/m/Y', strtotime('+1 day', strtotime($format_date)));
		$_SESSION['appointment_data']['booking_time'] = $_POST['booking_time0'];
	}
	if( isset($_POST['booking_time1']) ) {
		$_SESSION['appointment_data']['date_appointment'] = date('d/m/Y', strtotime('+1 day', strtotime($format_date)));
		$_SESSION['appointment_data']['booking_time'] = $_POST['booking_time1'];
	}
	if( isset($_POST['booking_time2']) ) {
		$_SESSION['appointment_data']['date_appointment'] = date('d/m/Y', strtotime('+2 day', strtotime($format_date)));
		$_SESSION['appointment_data']['booking_time'] = $_POST['booking_time2'];
	}
	if( isset($_POST['booking_time3']) ) {
		$_SESSION['appointment_data']['date_appointment'] = date('d/m/Y', strtotime('+3 day', strtotime($format_date)));
		$_SESSION['appointment_data']['booking_time'] = $_POST['booking_time3'];
	}
	if( isset($_POST['booking_time4']) ) {
		$_SESSION['appointment_data']['date_appointment'] = date('d/m/Y', strtotime('+4 day', strtotime($format_date)));
		$_SESSION['appointment_data']['booking_time'] = $_POST['booking_time4'];
	}
	unset($_SESSION['appointment_data']['booking_time0']);
	unset($_SESSION['appointment_data']['booking_time1']);
	unset($_SESSION['appointment_data']['booking_time2']);
	unset($_SESSION['appointment_data']['booking_time3']);
	unset($_SESSION['appointment_data']['booking_time4']);

	header('Location: '. SECURE_URL . PROFILE_PAGE, true, 301);
	exit(0);
}

if( $arrAppData['location'] == ""){
	header('Location: '. SECURE_URL . START_PAGE, true, 301);
	exit(0);
}
?>

<h4 class="page-name">Select Times ></h4>
<h1 class="fw-bold">Chromis Medical Appointments</h1>

<form method="post" class="form-horizontal" id="APP_FORM">
	<p class="ErrorMessage fst-italic fw-bold">Please select Consecutive Times</p>
	<h6>&nbsp;</h6>
	<div class="table-responsive">
		<table class="appForm table">
			<tr>
				<td colspan = "2" class="text-center app_desc fst-italic">
					<p><?php echo $format_date; ?></p>
					<p><?php echo $arrAppData['location']; ?></p>
					<p><?php echo $arrServices[$arrAppData['service']]['fullname']; ?></p>
				</td>
			</tr>
			<tr>
				<td colspan = "2" class="text-center app_desc">
					<p><b><?php echo $arrAppData['location']; ?></b> - <?php echo $arrLocations[$arrAppData['location']]['address']; ?></p>
				</td>
			</tr>
			<tr>
				<td class="form-label">Select Time:</td>
				<td class="app_desc">
					<div class="row">
					<?php 
						if( $arrAppData['five_days'] ) { 
							$date = $arrAppData['date_appointment'];
							for( $i = 0; $i < 5; $i++ ) {
					?>
						<div class="col-md-2 text-center">
							<?php 
								echo "<p>".format_date($date)."</p>";
							?>							
							<select name="booking_time<?php echo $i;?>[]" id="time_selector<?php echo $i;?>" multiple="multiple" style="height:150px;">
								<option value="">Deselect</option>
								<?php
									foreach( $arrTimeSheets as $key => $time ) {
										$selected = "";
										foreach( $arrAppData['booking_time'] as $book_time ){
											if( $key == $book_time && $arrAppData['date_appointment'] == $date )
												$selected = "selected";
										}
										echo '<option value="'.$key.'" '.$selected.'>'.$time.'</option>';
									}
								?>
							</select>
						</div>	
					<?php 
							$date = date('d/m/Y', strtotime('+1 day', strtotime(str_replace('/', '-', $date))));
						}
					} else { ?>
						<div class="col-md-12">
							<select name="booking_time0[]" id="time_selector" multiple="multiple" style="height:150px;">
								<option value="">Deselect</option>
								<?php
									foreach( $arrTimeSheets as $key => $time ) {
										$selected = "";
										foreach( $arrAppData['booking_time'] as $book_time ){
											if( $key == $book_time )
												$selected = "selected";
										}

										echo '<option value="'.$key.'" '.$selected.'>'.$time.'</option>';
									}
								?>
							</select>
						</div>						
					</div>
				<?php } ?>
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

<?php
require_once('footer.php');
?>