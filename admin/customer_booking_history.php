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

	$page_start = ($page - 1) * $limit;

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

	$pagenationLink = '?customerId='.$_customerId.'&startDate='.$startDate.'&endDate'.$endDate;

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
					$stmt = $db->prepare("SELECT CustomerId, FullName FROM customers");
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
			<label for="startDate">Start Date:</label>
			<input id="startDate" type="date" value="<?php echo $startDate; ?>" />
			<span id="startDateSelected"></span>
		</div>
		<div class="col-lg-3 col-sm-4">
			<label for="endDate">End Date:</label>
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
			
			$bookings = getCustomerBookings($_customerId, $startDate, $endDate, $page_start, $limit);
			if (!empty($bookings)) {
				foreach ($bookings as $booking) {
					echo '<tr>
						<td>' . $booking['BookingDate'] . '</td>
						<td>' . convertDurationToHoursMinutes($booking['BookingFrom'])["formatted_text_type1"] . '</td>
						<td>' . convertDurationToHoursMinutes($booking['BookingTo'])["formatted_text_type1"] . '</td>
						<td>' . $booking['BookingCode'] . '</td>
						<td>' . ($booking['BookingTo'] - $booking['BookingFrom']) . '</td>
						<td>' . displayYN($booking['Attended']) . '</td>
						<td><a target="_self" href="#" onclick="viewBookings(&quot;' . $booking['BookingCode'] . '&quot;);">' . $booking['Comments'] . '</a></td>
						<td>' . displayYN($booking['Messages']) . '</td>
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
					                href="<?php if($page <= 1){ echo '#'; } else { echo $pagenationLink."&page=" . $prev; } ?>">Previous</a>
					        </li>
					        <?php if( $page > 3 ) {?>
				        	<li class="page-item disabled">
				            	<a class="page-link" href="#">...</a>
					        </li>
					        <?php } ?>
					        <?php for($i = max($page-2, 1); $i <= min($number_of_page, $page+2); $i++ ): ?>
					        <li class="page-item <?php if($page == $i) {echo 'active'; } ?>">
					            <a class="page-link" href="<?php if($page == $i){ echo '#'; } else {echo $pagenationLink."&page=". $i; } ?>"> <?= $i; ?> </a>
					        </li>
					        <?php endfor; ?>
					        <?php if( $number_of_page > $page+2 ) {?>
				        	<li class="page-item disabled">
				            	<a class="page-link" href="#">...</a>
					        </li>
					        <?php } ?>
					        <li class="page-item <?php if($page >= $number_of_page) { echo 'disabled'; } ?>">
					            <a class="page-link"
					                href="<?php if($page >= $number_of_page){ echo '#'; } else {echo $pagenationLink."&page=". $next; } ?>">Next</a>
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
<script>
	$(document).ready(function() { 


		var apiUri = "/api/customers.php";
		$('#startDate, #endDate, #customer').on('change', function() {
            var startDate = $('#startDate').val();
            var endDate = $('#endDate').val();
			var customerId = $('#customer').val();
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

		$('#records-limit').change(function () {
            $('.pagination-form').submit();
        })
	});

	function viewBookings(booingCode) {
		// Specify the URL you want to open in the small window

		// Define the width and height of the small window
		var width = 800;
		var height = 600;
		// Calculate the left and top position to center the window
		var left = (window.innerWidth - width) / 2;
		var top = (window.innerHeight - height) / 2;
		// Open the small window with specified parameters
		window.open(
		window.location.origin +
			"/admin/options_comments.php?booking_code=" +
			booingCode,
		"_blank",
		"width=" + width + ",height=" + height + ",left=" + left + ",top=" + top
		);	
	}
</script>