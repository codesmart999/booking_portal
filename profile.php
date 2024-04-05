<?php

$menu = "profile";
require_once('header.php');

$db = getDBConnection();

$objCurUser = $_SESSION['User'];

if ( isset($_POST['Submit'])){
	
	$business_name = $arrAppData['business_name'];
	$email_addr = $arrAppData['email_addr'];

	$postAddress = json_encode(array (
		'street' => $arrAppData['street'],
		'city' => $arrAppData['city'],
		'state' => $arrAppData['state'],
		'postcode' => $arrAppData['postcode']
	));

	$phone_number = $arrAppData['phone_number'];
	$comments = '';
	if (!empty($arrAppData['comment'])) {
		$arr_comments = array();
		$arr_comments[] = [
			'id' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
			'user_id' => $objCurUser['UserId'],
			'datetime' => date('Y-m-d H:i:s'),
			'content' => $arrAppData['comment']
		];
		$comments = json_encode($arr_comments);;
	}

	// Check Email
	$customerId = 0;
	$stmt = $db->prepare("SELECT CustomerId FROM `customers` WHERE Email = ?");
	$stmt->bind_param('s', $email_addr);
	$stmt->execute();
	$stmt->bind_result($customerId);
	$stmt->store_result();
	
	// If the user does not exist, insert the new record
	if (!$stmt->fetch() || empty($customerId)) {
		$stmt = $db->prepare("INSERT INTO `customers` (FullName, Email, PostalAddr, Phone) VALUES (?, ?, ?, ?)");
		$stmt->bind_param('ssss', $business_name, $email_addr, $postAddress, $phone_number);
		$stmt->execute() or die($stmt->error);
		$customerId = $db->insert_id;
	} else {
		$stmt = $db->prepare("UPDATE `customers` SET FullName = ?, PostalAddr = ?, Phone = ? WHERE Email = ?");
		$stmt->bind_param('ssss', $business_name, $postAddress, $phone_number, $email_addr);
		$stmt->execute() or die($stmt->error);
	}
	
	$service_id = $arrAppData['service'];
	$system_id = $arrAppData['system'];
	$patient_name = $arrAppData['patient_name'];
	$staff_name = $arrAppData['chromis_staff'];
	$date_appointment = DateTime::createFromFormat('d/m/Y', $arrAppData['date_appointment_final']);
	$booking_date = $date_appointment->format('Y-m-d');
	
	list($booking_from, $booking_to) = extractStartAndEndTime($arrAppData['booking_time']);

	$booking_code = generateRandomCode($customerId . $system_id . $arrAppData['date_appointment'] . $booking_from . $booking_to);
	
	$stmt = $db->prepare("INSERT INTO `bookings` (ServiceId, SystemId, CustomerId, PatientName, StaffName, BookingDate, BookingFrom, BookingTo, BookingCode, Comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	foreach ($arrAppData['booking_time'] as $time) {
		list($booking_from, $booking_to) = explode('-', $time);

		$stmt->bind_param('iiisssssss', $service_id, $system_id, $customerId, $patient_name, $staff_name, $booking_date, $booking_from, $booking_to, $booking_code, $comments);
		$stmt->execute() or die($stmt->error);
	}
	$stmt->close();

	$bookID = $db->insert_id;

	if ($bookID != 0) {
		$_SESSION['appointment_data']['booking_code'] = $booking_code;
		header('Location: '. SECURE_URL . CONFIRM_PAGE, true, 301);
	}
	
	exit(0);
}

if ( empty($arrAppData['booking_time']) ){
	header('Location: '. SECURE_URL . SELECT_PAGE, true, 301);
	exit(0);
}

$format_date = format_date( $arrAppData['date_appointment_final'] );
list($from_in_mins, $to_in_mins) = extractStartAndEndTime($arrAppData['booking_time']);
?>

<h4 class="page-name">Profile ></h4>
<h1 class="fw-bold">Chromis Medical Appointments</h1>

<form method="post" class="form-horizontal" id="APP_FORM">
	<input type="hidden" name="profile_id" id="profile_id" value=""/>
	<h6>&nbsp;</h6>
	<div class="table-responsive">
		<table class="appForm table">
			<tr>
				<td colspan = "2" class="text-center app_desc fst-italic">
					<p><?php echo get_display_text_from_minutes($from_in_mins, $to_in_mins) . ', ' . $format_date; ?></p>
					<p><?php echo getLocationNameById($arrAppData['location']) . ' - ' . getLocationAddressById($arrAppData['location']); ?></p>
					<p><?php echo $arrServices[$arrAppData['service']]['fullname']; ?></p>
				</td>
			</tr>
			<tr>
				<td class="form-label">Search Profile:</td>
				<td>
					<div class="input-action">
						<input class="form-control form-control-sm" name="profile_name" type="text" id="inputString" autocomplete="off" onkeyup="lookup(this.value);" />
						<button type="submit" class="btn btn-primary btn-sm" name="search" id="search" value="Search">Search</button>
					</div>
					<div class="suggestionsBox" id="suggestions" style="display: none;">
                        <img src="./images/upArrow.png" style="position: relative; top: -12px; left: 10px;" alt="upArrow" />
                        <div class="suggestionList" id="autoSuggestionsList" style="padding:1em 0;"></div>
                    </div>
				</td>
			</tr>
			<tr class="new_profile">
				<td class="form-label profile_label">
					OR Add a New Profile:
				</td>
				<td>
					<div class="form-group">
						<label class="form-check-label" for="business_name">Business Name *</label>
						<input type="text" name="business_name" value="" required="" class="required valid form-control form-control-sm" placeholder="" id="business_name" />
					</div>
					<div class="form-group">
						<label class="form-check-label" for="postal_address">Postal address *</label>
						<div class="d-flex gap-1">
							<div class="addr_items">
								<input type="text" name="street" value="" required="" class="required valid form-control form-control-sm" placeholder="Street" id="street" />
							</div>
							<div class="addr_items">
								<input type="text" name="city" value="" required="" class="required valid form-control form-control-sm" placeholder="City" id="city" />
							</div>
							<div class="addr_items">
								<input type="text" name="state" value="" required="" class="required valid form-control form-control-sm" placeholder="State" id="state" />
							</div>
							<div class="addr_items">
								<input type="text" name="postcode" value="" required="" class="required valid form-control form-control-sm" placeholder="PostCode" id="postcode" />
							</div>							
						</div>
					</div>
					<div class="form-group">
						<label class="form-check-label" for="email_addr">Email address *</label>
						<input type="text" name="email_addr" value="" required="" class="required valid form-control form-control-sm" placeholder="" id="email_addr" />
						<div class="form-check">
							<label>Check Box to send Email</label>
							<input type="checkbox" class="form-check-input" name="send_email">
						</div>
					</div>
					<div class="form-group">
						<label class="form-check-label" for="phone_number">Phone *</label>
						<input type="text" name="phone_number" value="" required="" class="required valid form-control form-control-sm" placeholder="" id="phone_number" />
					</div>
					<div class="form-group">
						<label class="form-check-label" for="patient_name">Patient Name *</label>
						<input type="text" name="patient_name" value="" required="" class="required valid form-control form-control-sm" placeholder="" id="patient_name" />
					</div>
					<div class="form-group">
						<label class="form-check-label" for="chromis_staff">Chromis Staff making the booking *</label>
						<input type="text" name="chromis_staff" value="" required="" class="required valid form-control form-control-sm" placeholder="" id="chromis_staff" />
					</div>
					<div class="form-group">
						<label class="form-check-label" for="purchase_order">Purchase Order</label>
						<input type="text" name="purchase_order" value="" class="valid form-control form-control-sm" placeholder="" id="purchase_order" />
					</div>
					<div class="form-group">
						<label class="form-check-label" for="comment">Comment</label>
						<textarea type="text" name="comment" value="" class="valid form-control form-control-sm" placeholder="" id="comment" rows=3></textarea>
					</div>
					<div class="form-group">
						<input type="checkbox" class="form-check-input required" name="agreed_tos" id="agreed_tos" required="required">
						<label class="form-check-label" for="agreed_tos">I have Read and Agreed to the Terms of Use</label>
					</div>
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