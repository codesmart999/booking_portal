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

    $stmt = $db->prepare("SELECT * FROM customers WHERE `active`=1");
	$stmt->execute();
    $stmt->store_result();

    $total_records = $stmt->num_rows;
    $number_of_page = ceil( $total_records / $limit );
?>

<h4 class="page-title">Manage Customers<a href="#" data-toggle="modal" data-target="#saveModal" id="addcustomer" class="add_link">Add New</a></h4>

<div class="table-responsive">
    <table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
    	<thead>
	        <tr>
	            <td width="150" nowrap>FullName</td>
	            <td width="100" nowrap>Email</td>
	            <td width="100" nowrap>Address</td>
	            <td width="100" nowrap>Phone</td>
				<td width="100" nowrap>Active</td>
	            <td width="10" nowrap></td>
	        </tr>
	    </thead>
	    <tbody>
		<?php
			$page_start = ($page - 1) * $limit;
		    $stmt = $db->prepare("SELECT CustomerId, FullName, Email, PostalAddr, Phone, active FROM customers LIMIT ?,?");
	        $stmt->bind_param( 'ii', $page_start, $limit );
		    $stmt->execute();
		    $stmt->bind_result($customerId, $businessName, $email, $address, $phone, $active);
		    $stmt->store_result();
		    if ($stmt->num_rows > 0) {
			    while ($stmt->fetch()) {
					$addressArray = json_decode($address, true);
					if ($addressArray === null) {
						$street = "";
						$city = "";
						$state = "";
						$postcode = "";
					}
					$street = $addressArray['street'];
					$city = $addressArray['city'];
					$state = $addressArray['state'];
					$postcode = $addressArray['postcode'];
		?>
        <tr>
            <td><a href="#" class="customerEdit" data-customer_id=<?php echo $customerId ?>><?php echo $businessName ?></a></td>
            <td><?php echo $email ?></td>
            <td><?php echo $street; ?> <?php echo $city; ?> <?php echo $state; ?> <?php echo $postcode; ?></td>
            <td><?php echo $phone ?></td>
			<td><?php echo displayYN($active) ?></td>
            <td>
            	<a href="/admin/view_booking_history?id=<?php echo $customerId ?>" title="View Booking History"><i class="fa fa-eye fa-lg"></i></a>
				<a href="#" title="Delete Customer" data-toggle="modal" data-target="#deleteModal" class="customerDelete" data-customer_id=<?php echo $customerId ?>><i class="fa fa-trash fa-lg"></i></a>
            </td>
        </tr>
		<?php
	    	}
   	    } else {
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
		function clearForm() {
			$('input[name="customerId"]').val( "" );
			$('input[name="customername"]').val( "" );
			$('input[name="fullname"]').val( "" );
			$('input[name="description"]').val( "" );
			$('input[name="price"]').val( "" );
			$('select[name="duration_hours"]').val( "" );
			$('select[name="duration_minutes"]').val( "" );
			$('input[name="charge"]').prop( "checked", false );
			$('input[name="active"]').prop( "checked", false );
		}

		$('#btnSave').click(function(e){
			e.preventDefault();

			var formData = $("#APP_FORM").serializeArray();

			if( $("#customerId").val() )
				formData.push({ name: "action", value: "edit_customer" });
			else
				formData.push({ name: "action", value: "new_customer" });

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);
				if (res.status == "error") {
					$(".Message").removeClass("text-success");
					$(".Message").addClass("text-danger");
				} else {
					location.reload();
				}
				$(".Message").html( res.message );
	        });
		});

		$('.customerEdit').click( function(e){
			var formData = [];
			formData.push({ name: "action", value: "get_customer" });
			formData.push({ name: "customerId", value: $(this).data("customer_id") });

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);
				data = res.data;

				$('input[name="customerId"]').val( data.customerId );
				$('input[name="businessName"]').val( data.businessName );
				$('input[name="email"]').val( data.email );
				$('input[name="phone"]').val( data.phone );

				var addressArray = JSON.parse(data.address);

				// Initialize variables
				var street = "";
				var city = "";
				var state = "";
				var postcode = "";

				// Check if JSON parsing was successful
				if (addressArray !== null) {
					// Access properties from the parsed object
					street = addressArray.street;
					city = addressArray.city;
					state = addressArray.state;
					postcode = addressArray.postcode;
				}
				$('input[name="street"]').val( street );
				$('input[name="city"]').val( city );
				$('input[name="state"]').val( state );
				$('input[name="postcode"]').val( postcode );
				
				if ( data.active) {
					$('input[name="active"]').prop( "checked", true );
				} else {
					$('input[name="active"]').prop( "checked", false );
				}
				$("#saveModalLabel").html("Edit Customer");
				$("#saveModal").modal("show");
	        });
		});

		$('#addcustomer').on("click", function(e) {
			$("#saveModalLabel").html("Add Customer");
			$(".Message").html();
			clearForm();
		})

		$('.customerDelete').click( function(e){
			$(".Message").html();
			$('input[name="deleteId"]').val( $(this).data("customer_id") );
		});

		$('#btnDelete').click(function(e){
			e.preventDefault();

			var formData = $("#DELETE_FORM").serializeArray();

			formData.push({ name: "action", value: "delete_customer" });

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

				$(".Message").html( res.message );
	        });
		});

		$('#records-limit').change(function () {
            $('.pagination-form').submit();
        })
	});
</script>