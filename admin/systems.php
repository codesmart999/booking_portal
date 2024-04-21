<?php 
	require_once('header.php');

    $db = getDBConnection();
	if(isset($_POST['records-limit'])){
		$_SESSION['records-limit'] = $_POST['records-limit'];
	}

	$page = isset($_GET['page']) ? $_GET['page'] : 1;
	$limit = isset($_SESSION['records-limit']) ? $_SESSION['records-limit'] : DEFAULT_PAGE_NUM;

	$prev = $page - 1;
	$next = $page + 1;

    $stmt = $db->prepare("SELECT * FROM systems");
	  $stmt->execute();
    $stmt->store_result();

    $total_records = $stmt->num_rows;
    $number_of_page = ceil( $total_records / $limit );
?>

<h4 class="page-title">Individual Systems <a href="#" data-toggle="modal" data-target="#saveModal" id="addNew" class="add_link">Add New</a></h4>
<div class="table-responsive">
    <table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
    	<thead>
	        <tr>
	            <td width="150" nowrap>Full Name</td>
	            <td width="150" nowrap>Location</td>
	            <td width="150" nowrap>Reference ID</td>
	            <td width="150" nowrap>Email</td>
	            <td width="100" nowrap>Phone</td>
				<td width="100" nowrap>System Type</td>
				<td width="100" nowrap>Multiple Bookings?</td>
	            <td width="10" nowrap>Reg Date</td>
	            <td width="10" nowrap></td>
	        </tr>
	    </thead>
	    <tbody>
		<?php
			$page_start = ($page - 1) * $limit;
		    $stmt = $db->prepare("SELECT SystemId, FullName, LocationId, ReferenceId, Phone, RegDate, FirstEmail, SystemType, MaxMultipleBookings FROM systems LIMIT ?,?");
	        $stmt->bind_param( 'ii', $page_start, $limit );
		    $stmt->execute();
		    $stmt->bind_result($systemId, $fullname, $locationId, $referenceId, $phone_number, $regdate, $email_addr, $system_type, $max_multiple_bookings);
		    $stmt->store_result();
		    if ($stmt->num_rows > 0) {
			    while ($stmt->fetch()) {
		?>
        <tr>
            <td>
				<a href="#" class="editSystem" data-system_id=<?php echo $systemId ?>><?php echo $fullname ?></a>
			</td>
            <td><?php echo getLocationNameById( $locationId ); ?></td>
            <td><?php echo $referenceId ?></td>
            <td><?php echo $email_addr ?></td>
            <td><?php echo $phone_number ?></td>
			<td><?php echo $arrSystemTypes[$system_type] ?></td>
			<td><?php echo $max_multiple_bookings == 1 ? '-' : $max_multiple_bookings; ?></td>
            <td><?php echo format_date($regdate) ?></td>
            <td>
            	<a href="#" title="Services" data-toggle="modal" data-target="#serviceModal" class="viewService" data-system_id=<?php echo $systemId ?>><i class="fa fa-wrench fa-lg"></i></a> 
            	<a href="#" title="Delete System" data-toggle="modal" data-target="#deleteModal" class="deleteSystem" data-system_id=<?php echo $systemId ?>><i class="fa fa-trash fa-lg"></i></a>
            </td>
        </tr>
        <?php
	    	}
   	    } else {
   	    ?>
   	    <tr>
	        <td align="right" colspan="7" class="text-center">No Result.</td>
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
    	<input type="hidden" name="systemId" id="systemId" value="" />
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saveModalLabel">Add New</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
					<p class="Message fst-italic fw-bold p-0"></p>
                    <div class="form-group">
                        <label for="fullname">Individual System Name *</label>
                        <input type="input" class="form-control required" required="required" id="fullname" placeholder="Full Name" name="fullname"/>
                    </div>
                    <div class="form-group">
                        <label for="FirstName">First Name (Optional)</label>
                        <input type="input" class="form-control" id="FirstName" placeholder="First Name" name="first_name"/>
                    </div>
                    <div class="form-group">
                        <label for="LastName">Last Name (Optional)</label>
                        <input type="input" class="form-control" id="LastName" placeholder="Last Name" name="last_name"/>
                    </div>
                    <div class="form-group">
                        <label for="businessname">Business Name (Optional)</label>
                        <input type="input" class="form-control" id="businessname" placeholder="Business Name" name="businessname"/>
                    </div>
					<div class="form-group">
                        <label for="location">Location</label>
                        <select name="location" id="location" class="form-select form-select-sm">
						<?php
							foreach( $arrLocations as $key => $values){
								echo '<option value="' . $key . '">' . $values['name'] . '</option>';
							}
						?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="LocationAddress">Location Address</label>
                        <div class="row mb-2" id="LocationAddress">
                        	<div class="col-md-12">
		                        <input type="input" class="form-control" id="lstreet" placeholder="Street Address" name="lstreet"/>
		                    </div>
	                    </div>
	                    <div class="row mb-2">
	                    	<div class="col-md-6">
		                    	<input type="input" class="form-control" id="lcity" placeholder="Suburb/Town/City" name="lcity"/>
	                    	</div>
	                    	<div class="col-md-6">
		                    	<input type="input" class="form-control" id="lstate" placeholder="State/Region/County" name="lstate"/>
		                    </div>
	                    </div>
	                    <div class="row mb-2">
	                    	<div class="col-md-6">
		                    	<input type="input" class="form-control" id="lzipcode" placeholder="Zip/Postcode" name="lzipcode"/>
		                    </div>
	                    	<div class="col-md-6">
		                    	<input type="input" class="form-control" name="lcountry" value="Australia" disabled/>
		                    </div>
	                    </div>
                    </div>                    
                    <div class="form-group">
                        <label for="timezone">Time Zone</label>
                        <input type="input" class="form-control" id="timezone" placeholder="Time Zone" name="timezone" value="Sydney Australia" disabled />
                    </div>
                    <div class="form-group">
                        <label for="PostalAddress">Postal Address</label>
                        <div class="row mb-2" id="PostalAddress">
                        	<div class="col-md-12">
		                        <input type="input" class="form-control" id="pstreet" placeholder="Street Address" name="pstreet"/>
		                    </div>
	                    </div>
	                    <div class="row mb-2">
	                    	<div class="col-md-6">
		                    	<input type="input" class="form-control" id="pcity" placeholder="Suburb/Town/City" name="pcity"/>
	                    	</div>
	                    	<div class="col-md-6">
		                    	<input type="input" class="form-control" id="pstate" placeholder="State/Region/County" name="pstate"/>
		                    </div>
	                    </div>
	                    <div class="row mb-2">
	                    	<div class="col-md-6">
		                    	<input type="input" class="form-control" id="pzipcode" placeholder="Zip/Postcode" name="pzipcode"/>
		                    </div>
	                    	<div class="col-md-6">
		                    	<input type="input" class="form-control" name="pcountry" value="Australia" disabled/>
		                    </div>
	                    </div>
                    </div>
                    <div class="form-group">
                        <label for="latitude">Latitude</label>
                        <input type="input" class="form-control" id="latitude" placeholder="Latitude" name="latitude"/>
                    </div>
                    <div class="form-group">
                        <label for="longitude">Longitude</label>
                        <input type="input" class="form-control" id="longitude" placeholder="Longitude" name="longitude"/>
                    </div>
                    <div class="form-group">
                        <label for="email_addr">First Email *</label>
                        <input type="input" class="form-control required" required="required" id="email_addr" placeholder="Email" name="email_addr"/>
                    </div>
                    <div class="form-group">
                        <label for="email_addr1">Second Email</label>
                        <input type="input" class="form-control"id="email_addr1" placeholder="Second Email" name="email_addr1"/>
                    </div>
                    <div class="form-group">
                        <label for="email_addr2">Third Email</label>
                        <input type="input" class="form-control"id="email_addr2" placeholder="Third Email" name="email_addr2"/>
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone</label>
                        <input type="input" class="form-control" id="phone_number" placeholder="Phone" name="phone_number"/>
                    </div>
                    <div class="form-group">
                        <label for="mobile">Cell/Mobile</label>
                        <input type="input" class="form-control" id="mobile" placeholder="Cell/Mobile" name="mobile"/>
                    </div>
                    <div class="form-group">
                        <label for="fax">Fax</label>
                        <input type="input" class="form-control" id="fax" placeholder="Fax" name="fax"/>
                    </div>
					<div class="form-group">
                        <label for="system_type">System Type</label>
                        <select name="system_type" id="system_type" class="form-select form-select-sm">
						<?php
							foreach( $arrSystemTypes as $key => $value){
								echo '<option value="' . $key . '">' . $value . '</option>';
							}
						?>
                        </select>
                    </div>
					<div class="form-group">
						<label for="multiple_booking">Multiple Booking? (Specify the number of multiple bookings)</label>
						<div class="display-flex">
							<div>
								<label class="toggle-switch">
									<input id="multiple_booking" name="multiple_booking" type="checkbox" value="">
									<span class="toggle-slider"></span>
								</label>
							</div>
							<div id="max_multiple_bookings_container" class="d-none">
								<input type="number" class="form-control"name="max_multiple_bookings" value="1"/>
							</div>
						</div>
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
                    <h5 class="modal-title">Delete System</h5>
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
<div class="modal fade" id="serviceModal" tabindex="-1" role="dialog" aria-labelledby="serviceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Services provided by the Individual System</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body table-responsive">
              <form method="post" class="form-horizontal" id="SERVICE_FORM">
                <table border="0" cellspacing="0" cellpadding="5" width="100%" class="table-sm">
                    <thead>
                        <tr>
                            <td width="150" nowrap>Name</td>
                            <td width="100" nowrap>Price</td>
                            <td width="100" nowrap>Duration</td>
                            <td width="100" nowrap>Charge</td>
                            <td width="100" nowrap>Serve</td>
                        </tr>
                    </thead>
                    <tbody id="system_services">

                    </tbody>
                </table>
              </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary btn-sm" name="Save" value="Save" id="btnServiceSave" data-dismiss="modal">Save</button>
            </div>
        </div>
    </div>
</div>
<script>
	$(document).ready(function() { 
		var apiUri = "/api/systems.php";
		var apiUri2 = "/api/services.php";
    	var SystemId = '';

		function clearForm() {
			$('input[name="systemId"]').val( "" );
			$('input[name="fullname"]').val( "" );
			$('input[name="first_name"]').val( "" );
			$('input[name="last_name"]').val( "" );
			$('input[name="businessname"]').val( "" );
			$('select[name="location"]').prop( "" );
			$('input[name="lstreet"]').val( "" );
			$('input[name="lcity"]').val( "" );
			$('input[name="lstate"]').val( "" );
			$('input[name="lzipcode"]').val( "" );
			$('input[name="pstreet"]').val( "" );
			$('input[name="pcity"]').val( "" );
			$('input[name="pstate"]').val( "" );
			$('input[name="pzipcode"]').val( "" );
			$('input[name="latitude"]').val( "" );
			$('input[name="longitude"]').val( "" );
			$('input[name="email_addr"]').val( "" );
			$('input[name="email_addr1"]').val( "" );
			$('input[name="email_addr2"]').val( "" );
			$('input[name="phone_number"]').val( "" );
			$('input[name="mobile"]').val( "" );
			$('input[name="fax"]').val( "" );
			$('select[name="system_type"]').prop( "" );
			$('input[name="multiple_booking"]').value( "1" );
		}
    
    	$('#btnServiceSave').click(function(e) { 
			var formData = $("#SERVICE_FORM").serializeArray();
			formData.push({ name: "action", value: "update_service_items" });
			$.post(apiUri, formData, function (data) {
				// var res = JSON.parse(data);
			});
    	});

		$('#btnSave').click(function(e) {
			e.preventDefault();

			// Flag to track if all required fields are filled
			var allFieldsFilled = true;

			// Loop through each required input field
			$("#APP_FORM input[required]").each(function(){
				// Check if field has a value
				if (!$(this).val()) {
					allFieldsFilled = false;
					// Optionally, you can add visual feedback for the user here
					$(this).css("border-color", "red"); // Example: highlight empty fields in red
				}
			});

			if (!allFieldsFilled)
				return false;

			var formData = $("#APP_FORM").serializeArray();

			if( $("#systemId").val() )
				formData.push({ name: "action", value: "edit_system" });
			else
				formData.push({ name: "action", value: "new_system" });

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);

				if (res.status == "error") {
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

		$("#multiple_booking").change(function(){
			// If checkbox is checked, show the input container
			if($(this).is(":checked")){
				$("#max_multiple_bookings_container").removeClass("d-none");
			} else {
				// If checkbox is unchecked, hide the input container and reset input value
				$("#max_multiple_bookings_container").addClass("d-none");
				$("#max_multiple_bookings").val("1");
			}
		});

		$('.editSystem').click( function(e){
			var formData = [];
			formData.push({ name: "action", value: "get_system" });
			formData.push({ name: "systemId", value: $(this).data("system_id") });

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);
				data = res.data;

				$('input[name="systemId"]').val( data.SystemId );
				$('input[name="first_name"]').val( data.Firstname );
				$('input[name="last_name"]').val( data.Lastname );
				$('input[name="fullname"]').val( data.FullName );
				$('input[name="businessname"]').val( data.BusinessName );
				$("select[name=location]").val(data.LocationId);
				$('input[name="lstreet"]').val( data.Street );
				$('input[name="lcity"]').val( data.City );
				$('input[name="lstate"]').val( data.State );
				$('input[name="lzipcode"]').val( data.PostCode );
				$('input[name="pstreet"]').val( data.PStreet );
				$('input[name="pcity"]').val( data.PCity );
				$('input[name="pstate"]').val( data.PState );
				$('input[name="pzipcode"]').val( data.PPostCode );
				$('input[name="latitude"]').val( data.Latitude );
				$('input[name="longitude"]').val( data.Longitude );
				$('input[name="email_addr"]').val( data.FirstEmail );
				$('input[name="email_addr1"]').val( data.SecondEmail );
				$('input[name="email_addr2"]').val( data.ThirdEmail );

				$('input[name="phone_number"]').val( data.Phone );
				$('input[name="mobile"]').val( data.Mobile );
				$('input[name="fax"]').val( data.Fax );

				$("select[name=system_type]").val(data.SystemType);

				if (data.MaxMultipleBookings > 1) {
					$('input[name="multiple_booking"]').prop("checked", true);
					$("#max_multiple_bookings_container").removeClass("d-none");
				} else {
					$('input[name="multiple_booking"]').prop("checked", false);
					$("#max_multiple_bookings_container").addClass("d-none");
				}
				$('input[name="max_multiple_bookings"]').val( data.MaxMultipleBookings );
				
				$("#saveModalLabel").html("Edit");
				$("#saveModal").modal("show");
	        });
		});

		$('#addNew').on("click", function(e) {
			$("#saveModalLabel").html("Add New");
			$(".Message").html();
			clearForm();
		})

		$('.deleteSystem').click( function(e){
			$(".Message").html();
			$('input[name="deleteId"]').val( $(this).data("system_id") );
		});

		$('#btnDelete').click(function(e){
			e.preventDefault();

			var formData = $("#DELETE_FORM").serializeArray();

			formData.push({ name: "action", value: "delete_system" });

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

    	$('.viewService').click( function(e){
			var formData = [];
			var SystemId = $(this).data("system_id");

			formData.push({ name: "action", value: "get_all_services" });
			formData.push({ name: 'SystemId', value: SystemId});
		
			$.post(apiUri2, formData, function (data) {
				var res = JSON.parse(data);
				data = res.data;

        		$("#system_services").empty();
				
				for( i = 0; i < data.length; i++ ) {
					var service = data[i];
					var html = `<tr>
							<td>${service.ServiceName}</td>
							<td>${service.Price}</td>
							<td>${service.Formatted_Duration}</td>
							<td>${service.IsCharge}</td>
							<td>
								<label class="toggle-switch-sm">
									<input name="Services[]" value="${service.ServiceId}" type="checkbox" ${service.isSystemService ? 'checked' : ''}>
									<span class="toggle-slider"></span>
								</label>
							</td>
						</tr>`;
					$("#system_services").append(html)
				}
        		
				$("#system_services").append('<input type="hidden" name="SystemId" value="' + SystemId + '"/>');
	    	});
		});

    	$('#myToggle').change(function() {
			// Get the new value of the toggle switch (checked or unchecked)
			var newValue = $(this).is(':checked') ? 1 : 0;

			// Make an AJAX request to save the updated value
			$.ajax({
				url: '/api/systems.php', // URL to your PHP script that handles saving the value
				method: 'POST',
				data: { value: newValue }, // Send the new value to the server
				success: function(response) {
					// Handle the server response if needed
					console.log('Toggle value saved successfully.');
				},
				error: function(xhr, status, error) {
					// Handle errors if the AJAX request fails
					console.error('Error saving toggle value:', error);
				}
			});
		});
	});
</script>