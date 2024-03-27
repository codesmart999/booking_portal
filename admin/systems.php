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

<h4 class="page-title">Individual Systems <a href="#" data-toggle="modal" data-target="#saveModal" id="addUser" class="add_link">Add New</a></h4>
<div class="table-responsive">
    <table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
    	<thead>
	        <tr>
	            <td width="150" nowrap>Full Name</td>
	            <td width="150" nowrap>Location</td>
	            <td width="150" nowrap>Reference ID</td>
	            <td width="150" nowrap>Email</td>
	            <td width="100" nowrap>Phone</td>
	            <td width="10" nowrap>Reg Date</td>
	            <td width="10" nowrap></td>
	        </tr>
	    </thead>
	    <tbody>
		<?php
			$page_start = ($page - 1) * $limit;
		    $stmt = $db->prepare("SELECT SystemId, FullName, LocationId, ReferenceId, Phone, RegDate, U.Userid, U.Email FROM systems LEFT JOIN users as U on U.UserId = systems.UserId LIMIT ?,?");
	        $stmt->bind_param( 'ii', $page_start, $limit );
		    $stmt->execute();
		    $stmt->bind_result($systemId, $fullname, $locationId, $referenceId, $phone_number, $regdate, $userId, $email);
		    $stmt->store_result();
		    if ($stmt->num_rows > 0) {
			    while ($stmt->fetch()) {
		?>
        <tr>
            <td><a href="#" class="userEdit" data-user_id=<?php echo $userId ?>><?php echo $fullname ?></a></td>
            <td><?php echo getLocationNameById( $locationId ); ?></td>
            <td><?php echo $referenceId ?></td>
            <td><?php echo $email ?></td>
            <td><?php echo $phone_number ?></td>
            <td><?php echo format_date($regdate) ?></td>
            <td>
            	<a href="#" title="Services" data-toggle="modal" data-target="#serviceModal" class="viewService" data-system_id=<?php echo $systemId ?>><i class="fa fa-wrench fa-lg"></i></a> 
            	<a href="#" title="Change Password" data-toggle="modal" data-target="#passModal" class="updatePass" data-user_id=<?php echo $userId ?>><i class="fa fa-key fa-lg"></i></a> 
            	<a href="#" title="Delete User" data-toggle="modal" data-target="#deleteModal" class="userDelete" data-user_id=<?php echo $userId ?>><i class="fa fa-trash fa-lg"></i></a>
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
    	<input type="hidden" name="userId" id="userId" value="" />
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saveModalLabel">Add Profile</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
					<p class="Message fst-italic fw-bold p-0"></p>
                    <div class="form-group">
                        <label for="Username">Username *</label>
                        <input type="input" class="form-control required" required="required" id="Username" placeholder="Username" name="username"/>
                    </div>
                    <div class="form-group">
                        <label for="FirstName">First Name *</label>
                        <input type="input" class="form-control required" required="required" id="FirstName" placeholder="First Name" name="first_name"/>
                    </div>
                    <div class="form-group">
                        <label for="LastName">Last Name *</label>
                        <input type="input" class="form-control required" required="required" id="LastName" placeholder="Last Name" name="last_name"/>
                    </div>
                    <div class="form-group">
                        <label for="fullname">Full Name *</label>
                        <input type="input" class="form-control required" required="required" id="fullname" placeholder="Full Name" name="fullname"/>
                    </div>
                    <div class="form-group">
                        <label for="businessname">Business Name</label>
                        <input type="input" class="form-control" id="businessname" placeholder="Business Name" name="businessname"/>
                    </div>
					<div class="form-group">
                        <label for="Location">Location</label>
                        <?php 
							foreach( $arrLocations as $key => $desc){
								$checked = "";
								if( $key == $arrAppData['location'] )
									$checked = "checked";

								echo '<div class="form-check">
										<label class="form-check-label" for="'.$key.'">'.$key.'</label>
										<input type="radio" required class="form-check-input" name="location" id="'.$key.'" value="'.$desc['id'].'" '.$checked.'/>
									</div>';
							}
						?>
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
                        <label for="UserType">User Type</label>
                        <select name="user_type" id="UserType" class="form-select form-select-sm">
                            <option value="U">User</option>
                            <option value="A" selected>Administrator</option>
                        </select>
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
<div class="modal fade" id="passModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form method="post" class="form-horizontal" id="PASSWORD_FORM">
    	<input type="hidden" name="passId" id="passId" value="" />
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Password</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
					<p class="Message fst-italic fw-bold p-0"></p>
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" class="form-control required" required="required" id="password" placeholder="Password" name="password"/>
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Confirm Password *</label>
                        <input type="password" class="form-control required" required="required" id="password_confirm" placeholder="Confirm Password" name="password_confirm"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm" name="Save" value="Save" id="btnPass" disabled>Update</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal fade" id="serviceModal" tabindex="-1" role="dialog" aria-labelledby="serviceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Services</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body table-responsive">
              <form method="post" class="form-horizontal" id="SERVICE_FORM">
                <table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
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
		var apiUri = "/api/services.php";
    var SystemId = '';
		function clearForm() {
			$('input[name="userId"]').val( "" );
			$('input[name="username"]').val( "" );
			$('input[name="first_name"]').val( "" );
			$('input[name="last_name"]').val( "" );
			$('input[name="fullname"]').val( "" );
			$('input[name="businessname"]').val( "" );
			$('input[name="location"]').prop( "checked", false );
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
			$('select[name="user_type"]').val( "" );
			$('input[name="active"]').prop( "checked", false );
      
		}
    
    $('#btnServiceSave').click(function(e) { 
      var formData = $("#SERVICE_FORM").serializeArray();
      formData.push({ name: "action", value: "update_service_items" });
      $.post('/api/systems.php', formData, function (data) {
				// var res = JSON.parse(data);
        
	    });
    });

		$('#btnSave').click(function(e) {
			e.preventDefault();

			var formData = $("#APP_FORM").serializeArray();

			if( $("#userId").val() )
				formData.push({ name: "action", value: "edit_user" });
			else
				formData.push({ name: "action", value: "new_user" });

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

		$('.userEdit').click( function(e){
			var formData = [];
			formData.push({ name: "action", value: "get_user" });
			formData.push({ name: "userId", value: $(this).data("user_id") });

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);
				data = res.data;

				$('input[name="userId"]').val( data.UserId );
				$('input[name="username"]').val( data.Username );
				$('input[name="first_name"]').val( data.Firstname );
				$('input[name="last_name"]').val( data.Lastname );
				$('input[name="email_addr"]').val( data.Email );
				$('input[name="fullname"]').val( data.FullName );
				$('input[name="businessname"]').val( data.BusinessName );
				$("input[name=location][value=" + data.LocationId + "]").attr('checked', 'checked');
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
				$('input[name="email_addr1"]').val( data.SecondEmail );
				$('input[name="email_addr2"]').val( data.ThirdEmail );

				$('input[name="phone_number"]').val( data.Phone );
				$('input[name="mobile"]').val( data.Mobile );
				$('input[name="fax"]').val( data.Fax );
				$('select[name="user_type"]').val( data.UserType );

				if( data.Active == "Y" ){
					$('input[name="active"]').prop( "checked", true );
				} else {
					$('input[name="active"]').prop( "checked", false );
				}
				$("#saveModalLabel").html("Edit Profile");
				$("#saveModal").modal("show");
	        });
		});

		$('#addUser').on("click", function(e) {
			$("#saveModalLabel").html("Add Profile");
			$(".Message").html();
			clearForm();
		})

		$('.userDelete').click( function(e){
			$(".Message").html();
			$('input[name="deleteId"]').val( $(this).data("user_id") );
		});

		$('#btnDelete').click(function(e){
			e.preventDefault();

			var formData = $("#DELETE_FORM").serializeArray();

			formData.push({ name: "action", value: "delete_user" });

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

		$('.updatePass').click( function(e){
			$('input[name="passId"]').val( $(this).data("user_id") );
		});

		$('#btnPass').click(function(e){
			e.preventDefault();

			var formData = $("#PASSWORD_FORM").serializeArray();

			formData.push({ name: "action", value: "change_pass" });

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);
				if(res.status == "error"){
					$(".Message").removeClass("text-success");
					$(".Message").addClass("text-danger");
				} else {
					$(".Message").removeClass("text-danger");
					$(".Message").addClass("text-success");
					$("#passModal").modal("hide");
				}

				$(".Message").html( res.message );
	        });
		});

		$('#records-limit').change(function () {
            $('.pagination-form').submit();
        })

    $('.viewService').click( function(e){
			var formData = [];
			formData.push({ name: "action", value: "get_all_services" });
      formData.push({ name: 'SystemId', value: $(this).attr('data-system_id')});
      var SystemId = $(this).attr('data-system_id');
		
      $.post(apiUri, formData, function (data) {
        var res = JSON.parse(data);
				data = res.data;

        $("#system_services").empty();
				for( i = 0; i < data.length; i++ ) {
					var service = data[i];
					var html = `<tr>
              <td>${service.ServiceName}</td>
							<td>${service.Price}</td>
							<td>${service.Duration}</td>
							<td>${service.IsCharge}</td>
              <td>
                  <div class="form-group">
                      <label class="toggle-switch">
                          <input name="Services[]" value="${service.ServiceId}" type="checkbox" ${service.isSystemService ? 'checked' : ''}>
                          <span class="toggle-slider"></span>
                      </label>
                  </div>
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