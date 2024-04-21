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

    $stmt = $db->prepare("SELECT * FROM services WHERE `active`=1");
	$stmt->execute();
    $stmt->store_result();

    $total_records = $stmt->num_rows;
    $number_of_page = ceil( $total_records / $limit );
?>

<h4 class="page-title">Manage Services<a href="#" data-toggle="modal" data-target="#saveModal" id="addService" class="add_link">Add New</a></h4>

<div class="table-responsive">
    <table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
    	<thead>
	        <tr>
	            <td width="150" nowrap>Name</td>
	            <td width="100" nowrap>Price</td>
	            <td width="100" nowrap>Duration (Doctor)</td>
				<td width="100" nowrap>Duration (Nurse)</td>
	            <td width="100" nowrap>Charge</td>
	            <td width="10" nowrap>Active</td>
	            <td width="10" nowrap></td>
	        </tr>
	    </thead>
	    <tbody>
		<?php
			$page_start = ($page - 1) * $limit;
		    $stmt = $db->prepare("SELECT ServiceId, ServiceName, FullName, Price, DurationInMins_Doctor, DurationInMins_Nurse, IsCharge, active FROM services LIMIT ?,?");
	        $stmt->bind_param( 'ii', $page_start, $limit );
		    $stmt->execute();
		    $stmt->bind_result($serviceId, $servicename, $fullname, $price, $duration_in_mins1, $duration_in_mins2, $charge, $active);
		    $stmt->store_result();

		    if ($stmt->num_rows > 0) {
			    while ($stmt->fetch()) {
					$arrDurationInfo1 = convertDurationToHoursMinutes($duration_in_mins1); // Doctor
					$arrDurationInfo2 = convertDurationToHoursMinutes($duration_in_mins2); // Nurse
		?>
        <tr>
            <td><a href="#" class="serviceEdit" data-service_id=<?php echo $serviceId ?>><?php echo $fullname ?></a></td>
            <td><?php echo displayPrice($price) ?></td>
            <td><?php echo $arrDurationInfo1['formatted_text']; ?></td>
			<td><?php echo $arrDurationInfo2['formatted_text']; ?></td>
            <td><?php echo displayYN($charge) ?></td>
            <td><?php echo displayYN($active) ?></td>
            <td>
            	<a href="#" title="Delete Service" data-toggle="modal" data-target="#deleteModal" class="serviceDelete" data-service_id=<?php echo $serviceId ?>><i class="fa fa-trash fa-lg"></i></a>
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
    	<input type="hidden" name="serviceId" id="serviceId" value="" />
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saveModalLabel">Add Service</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
					<p class="Message fst-italic fw-bold p-0"></p>
                    <div class="form-group">
                        <label for="servicename">Short Name *</label>
                        <input type="input" class="form-control required" required="required" id="servicename" placeholder="Service Slug" name="servicename"/>
                    </div>
                    <div class="form-group">
                        <label for="fullname">Full Name *</label>
                        <input type="input" class="form-control required" id="fullname" placeholder="Full Name" name="fullname" required="required"/>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" placeholder="Description Description" name="description" rows="5"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="number" class="form-control" id="price" placeholder="Price" name="price"/>
                    </div>
                    <div class="form-group">
                        <label for="duration_minutes_doctor">Duration ( Hours, Minutes ) - Doctor</label>
                        <div class="row mb-2">
                        	<div class="col-md-6">
		                    	<select name="duration_hours_doctor" id="duration_hours_doctor" class="form-select form-select-sm">
		                    		<option value="">Select</option>
		                    		<?php
		                    		for( $i = 0; $i < 24; $i++)
		                    			echo '<option value="'.$i.'">'.$i.'</option>';
		                    		?>
		                    	</select>
	                    	</div>
	                    	<div class="col-md-6">
		                    	<select name="duration_minutes_doctor" id="duration_minutes_doctor" class="form-select form-select-sm">
									<option value="">Select</option>
		                    		<?php
		                    		for( $i = 0; $i < 60; $i += 5)
		                    			echo '<option value="'.$i.'">'.sprintf("%02d", $i).'</option>';
		                    		?>
		                    	</select>
		                    </div>
		                </div>
		            </div>
					<div class="form-group">
                        <label for="duration_minutes_nurse">Duration ( Hours, Minutes ) - Nurse</label>
                        <div class="row mb-2">
                        	<div class="col-md-6">
		                    	<select name="duration_hours_nurse" id="duration_hours_nurse" class="form-select form-select-sm">
		                    		<option value="">Select</option>
		                    		<?php
		                    		for( $i = 0; $i < 24; $i++)
		                    			echo '<option value="'.$i.'">'.$i.'</option>';
		                    		?>
		                    	</select>
	                    	</div>
	                    	<div class="col-md-6">
		                    	<select name="duration_minutes_nurse" id="duration_minutes_nurse" class="form-select form-select-sm">
									<option value="">Select</option>
		                    		<?php
		                    		for( $i = 0; $i < 60; $i += 15)
		                    			echo '<option value="'.$i.'">'.sprintf("%02d", $i).'</option>';
		                    		?>
		                    	</select>
		                    </div>
		                </div>
		            </div>

                    <div class="form-group">
                        <label for="IsCharge">Service is a Booking Charge</label>
                        <input class="" type="checkbox" id="IsCharge" name="charge"/>
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
                    <h5 class="modal-title">Delete Service</h5>
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
		var apiUri = "/api/services.php";
		function clearForm() {
			$('input[name="serviceId"]').val( "" );
			$('input[name="servicename"]').val( "" );
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

			if( $("#serviceId").val() )
				formData.push({ name: "action", value: "edit_service" });
			else
				formData.push({ name: "action", value: "new_service" });

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

		$('.serviceEdit').click( function(e){
			var formData = [];
			formData.push({ name: "action", value: "get_service" });
			formData.push({ name: "serviceId", value: $(this).data("service_id") });

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);
				data = res.data;

				$('input[name="serviceId"]').val( data.ServiceId );
				$('input[name="servicename"]').val( data.ServiceName );
				$('input[name="fullname"]').val( data.FullName );
				$('#description').val( data.Description );
				$('input[name="price"]').val( data.Price );
				$('select[name="duration_hours_doctor"]').val( data.Duration_Hours_Doctor );
				$('select[name="duration_minutes_doctor"]').val( data.Duration_Minutes_Doctor );
				$('select[name="duration_hours_nurse"]').val( data.Duration_Hours_Nurse );
				$('select[name="duration_minutes_nurse"]').val( data.Duration_Minutes_Nurse );

				if ( data.IsCharge == "Y" ) {
					$('input[name="charge"]').prop( "checked", true );
				} else {
					$('input[name="charge"]').prop( "checked", false );
				}
				if ( data.Active) {
					$('input[name="active"]').prop( "checked", true );
				} else {
					$('input[name="active"]').prop( "checked", false );
				}
				$("#saveModalLabel").html("Edit Service");
				$("#saveModal").modal("show");
	        });
		});

		$('#addService').on("click", function(e) {
			$("#saveModalLabel").html("Add Service");
			$(".Message").html();
			clearForm();
		})

		$('.serviceDelete').click( function(e){
			$(".Message").html();
			$('input[name="deleteId"]').val( $(this).data("service_id") );
		});

		$('#btnDelete').click(function(e){
			e.preventDefault();

			var formData = $("#DELETE_FORM").serializeArray();

			formData.push({ name: "action", value: "delete_service" });

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