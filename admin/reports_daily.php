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

	$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d');


    $startDateTimestamp = strtotime($startDate);

    // Format the date using the desired format
    $formattedDate = date('l, F j, Y', $startDateTimestamp);

    // Create the desired string
    $resultString = "List of bookings created on " . $formattedDate . ".";

	$pagenationLink = '?&startDate='.$startDate.'&endDate';
    $total_records = count(getReportByDate($startDate, NULL, NULL));
   
    $number_of_page = ceil( $total_records / $limit );
?>

<h4 class="page-title">Daily Bookings List</h4>
<div>
    <div class="row">
        <div class="col-sm-12">
            <label for="startDate"><?php echo "List of bookings created on " . $formattedDate . ".";?></label>
            <input id="startDate" type="date" value="<?php echo $startDate; ?>" />
            <span id="startDateSelected"></span>
        </div>
    </div>
</div>
<div class="table-responsive">
    <table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
        <thead>
            <tr>
                <td width="150" nowrap>Service Name</td>
                <td width="150" nowrap>System Name</td>
                <td width="100" nowrap>Customer Name (Email)</td>
                <td width="100" nowrap>Patient Name</td>
                <td width="100" nowrap>Booking Code</td>
                <td width="100" nowrap>Booking Date</td>
                <td width="100" nowrap>Booking Period</td>
                <td width="100" nowrap>Status</td>
            </tr>
        </thead>
        <tbody>
            <?php
			
			$bookings = getReportByDate($startDate, $page_start, $limit);
			if (!empty($bookings)) {
				foreach ($bookings as $booking) {
                    $bookingForDate = date('D, M j Y', strtotime($booking['bookingForDate']));
					echo '<tr>
                        <td>' . $booking['serviceName'] . '</td>
						<td><a href="booking_access.php?SystemId=' . $booking['systemId'] . '&startDate=' . $startDate . '&endDate=' . $startDate . '">' . $booking['systemName'] . '</a></td>
                        <td>' . $booking['businessName'] . ' (' . $booking['email'] . ')</td>
                        <td>' . $booking['patientName'] . '</td>
                        <td style="color: ' . generateTextColor($booking['bookingCode']) .'">' . $booking['bookingCode'] . '</td>
                        <td>' . $bookingForDate . '</td>
						<td>' . $booking['displayText'] . '</td>
						<td></td>
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
                                    <?php if(isset($_SESSION['records-limit']) && $_SESSION['records-limit'] == $limit) echo 'selected'; ?>
                                    value="<?= $limit; ?>">
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
                                <a class="page-link"
                                    href="<?php if($page == $i){ echo '#'; } else {echo $pagenationLink."&page=". $i; } ?>">
                                    <?= $i; ?> </a>
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
        var url = window.location.pathname + '?customerId=' + customerId + '&startDate=' + startDate +
            '&endDate=' + endDate;
        window.location.href = url;
    });

    $('#records-limit').change(function() {
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