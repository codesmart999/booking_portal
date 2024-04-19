<?php
    require_once('header.php');
    $db = getDBConnection();
	if (isset($_POST['records-limit'])) {
		$page = 1;
		$_SESSION['records-limit'] = $_POST['records-limit'];
	}

	$page = isset($_GET['page']) ? $_GET['page'] : 1;
	$limit = isset($_SESSION['records-limit']) ? $_SESSION['records-limit'] : DEFAULT_PAGE_NUM;

	$prev = $page - 1;
	$next = $page + 1;

	$_customerId = isset($_GET['customerId']) ? intval($_GET['customerId']) : null;

	if (!isset($_customerId)) {
		// Redirect the user to the desired location
		header('Location: '. SECURE_URL . ADMIN_INDEX . "customers", true, 301);
		exit; // Make sure to exit after redirection to prevent further script execution
	}
	
	$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d');
	$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');
	

    $stmt = $db->prepare("SELECT * FROM customers WHERE `active`=1");
	$stmt->execute();
    $stmt->store_result();

    $total_records = $stmt->num_rows;
    $number_of_page = ceil( $total_records / $limit );
?>

<h4 class="page-title">Customer Booking History</h4>
<div>
	<div class="row">
		<div class="col-lg-3 col-sm-4">
			<label for="customer">Customer:</label>
			<select id="customer" name="customer">
				<?php
					$stmt = $db->prepare("SELECT CustomerId, FullName FROM customers WHERE `active`=1");
					$stmt->execute();
					$stmt->store_result();
					$stmt->bind_result($customerId, $businessName); // Assuming $customerId and $customerName are the columns you want to fetch
					
					while ($stmt->fetch()) {
						// Check if the current customer is the selected one
						$selected = ($customerId == $_customerId) ? 'selected' : '';
						// Output the option
						echo "<option value='$customerId' $selected>$businessName</option>";
					}
				?>
			</select>
		</div>
		<div class="col-lg-3 col-sm-4">
			<label for="startDate">Start</label>
			<input id="startDate" type="date" value="<?php echo $startDate; ?>" />
			<span id="startDateSelected"></span>
		</div>
		<div class="col-lg-3 col-sm-4">
			<label for="endDate">End</label>
			<input id="endDate" type="date" value="<?php echo $endDate; ?>" />
			<span id="endDateSelected"></span>
		</div>
	</div>
</div>
<div class="table-responsive">
    <table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
    	<thead>
	        <tr>
	            <td width="150" nowrap>Date</td>
	            <td width="100" nowrap>From</td>
				<td width="100" nowrap>To</td>
	            <td width="100" nowrap>Booking Code</td>
	            <td width="100" nowrap>Minutes</td>
				<td width="100" nowrap>Attended</td>
				<td width="100" nowrap>Comments</td>
				<td width="100" nowrap>Message</td>
	        </tr>
	    </thead>
	    <tbody>
		<?php
			
			$bookings = getCustomerBookings($_customerId, $startDate, $endDate);
			if (!empty($bookings)) {
				foreach ($bookings as $booking) {
					echo '<tr>
					<td>' . $booking['BookingDate'] . '</td>
					<td>' . convertDurationToHoursMinutes($booking['BookingFrom'])["formatted_text_type1"] . '</td>
					<td>' . convertDurationToHoursMinutes($booking['BookingTo'])["formatted_text_type1"] . '</td>
					<td>' . $booking['BookingCode'] . '</td>
					<td>' . $booking['BookingTo'] - $booking['BookingFrom'] . '</td>
					<td>' . $booking['Attended'] . '</td>
					<td>' . $booking['Comments'] . '</td>
					<td>' . $booking['Messages'] . '</td>
				</tr>';
				}
			}
   	     else {
   	    ?>
   	    <tr>
	        <td align="right" colspan="5" class="text-center">No Result.</td>
	    </tr>
   	    <?php
   	    }
	    ?>
	    </tbody>
	    <tfoot>
		    <tr>
		        <td align="center" colspan="7">
		        	<div class="limit-selector">
						<form class="pagination-form" method="post">
							<select name="records-limit" id="records-limit" class="custom-select">
								<option disabled selected>Records Limit</option>
								<?php foreach($arrPageLimits as $limit) : ?>
								<option
								<?php if(isset($_SESSION['records-limit']) && $_SESSION['records-limit'] == $limit) echo 'selected'; ?> value="<?= $limit; ?>">
								<?= $limit; ?>
								</option>
								<?php endforeach; ?>
							</select>
						</form>
		        	</div>
					<nav aria-label="Page navigation example mt-5" class="pagination-wrapper">
					    <ul class="pagination justify-content-center">
					        <li class="page-item <?php if($page <= 1){ echo 'disabled'; } ?>">
					            <a class="page-link"
					                href="<?php if($page <= 1){ echo '#'; } else { echo "?page=" . $prev; } ?>">Previous</a>
					        </li>
					        <?php if( $page > 3 ) {?>
				        	<li class="page-item disabled">
				            	<a class="page-link" href="#">...</a>
					        </li>
					        <?php } ?>
					        <?php for($i = max($page-2, 1); $i <= min($number_of_page, $page+2); $i++ ): ?>
					        <li class="page-item <?php if($page == $i) {echo 'active'; } ?>">
					            <a class="page-link" href="<?php if($page == $i){ echo '#'; } else {echo "?page=". $i; } ?>"> <?= $i; ?> </a>
					        </li>
					        <?php endfor; ?>
					        <?php if( $number_of_page > $page+2 ) {?>
				        	<li class="page-item disabled">
				            	<a class="page-link" href="#">...</a>
					        </li>
					        <?php } ?>
					        <li class="page-item <?php if($page >= $number_of_page) { echo 'disabled'; } ?>">
					            <a class="page-link"
					                href="<?php if($page >= $number_of_page){ echo '#'; } else {echo "?page=". $next; } ?>">Next</a>
					        </li>
					    </ul>
					</nav>
		        </td>
		    </tr>
	    </tfoot>
    </table>
</div>
    <?php
    $stmt->close();
    $db->close();
    
    require_once('footer.php');
?>

<div class="modal fade" id="saveModal" tabindex="-1" role="dialog" aria-labelledby="saveModalLabel" aria-hidden="true">
    <form method="post" class="form-horizontal" id="APP_FORM">
    	<input type="hidden" name="customerId" id="customerId" value="" />
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saveModalLabel">Add Customer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
					<p class="Message fst-italic fw-bold p-0"></p>
                    <div class="form-group">
                        <label for="businessName">Business Name *</label>
                        <input type="input" class="form-control required" required="required" id="businessName" placeholder="Business Name" name="businessName"/>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="input" class="form-control required" id="email" placeholder="Email Address" name="email" required="required"/>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
						<div class="addr_items">
							Street:<input type="text" name="street" value="" required="" class="required valid form-control form-control-sm" placeholder="Street" id="street" />
						</div>
						<div class="addr_items">
							City:<input type="text" name="city" value="" required="" class="required valid form-control form-control-sm" placeholder="City" id="city" />
						</div>
						<div class="addr_items">
							State:<input type="text" name="state" value="" required="" class="required valid form-control form-control-sm" placeholder="State" id="state" />
						</div>
						<div class="addr_items">
							PostCode:<input type="text" name="postcode" value="" required="" class="required valid form-control form-control-sm" placeholder="PostCode" id="postcode" />
						</div>		
                    </div>
					<div class="form-group">
                        <label for="phoneNumber">Phone *</label>
						<input type="input" class="form-control required form-control-sm" id="phoneNumber" placeholder="PhoneNumber" name="phone" required="required"/>
                    </div>
                    <div class="form-group">
                        <label for="IsActive">Active</label>
                        <input class="" type="checkbox" id="IsActive" name="active"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm" name="Save" value="Save" id="btnSave" disabled>Save</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form method="post" class="form-horizontal" id="DELETE_FORM">
    	<input type="hidden" name="deleteId" id="deleteId" value="" />
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Customer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                	<p class="Message fst-italic fw-bold p-0"></p>
                	<div class="form-group">
						Are you sure?
					</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm" name="Save" value="Save" id="btnDelete">DELETE</button>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
	$(document).ready(function() { 


		var apiUri = "/api/customers.php";
		$('#startDate, #endDate').on('change', function() {
            var startDate = $('#startDate').val();
            var endDate = $('#endDate').val();
			var customerId = '<?php echo $customerId; ?>';
            // Check if startDate is after endDate
            if (startDate > endDate) {
                alert("Start date cannot be after end date.");
                $('#startDate').val('');
                return;
            }

            // Redirect with updated startDate and endDate
            var url = window.location.pathname + '?customerId=' + customerId + '&startDate=' + startDate + '&endDate=' + endDate;
            window.location.href = url;
        });
	});
</script>