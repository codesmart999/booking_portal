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

    $stmt = $db->prepare("SELECT * FROM users WHERE `UserType`='A'");
	$stmt->execute();
    $stmt->store_result();

    $total_records = $stmt->num_rows;
    $number_of_page = ceil( $total_records / $limit );
?>

<h4 class="page-title">Sub-Administrators<a href="#" data-toggle="modal" data-target="#saveModal" id="addUser" class="add_link">Add Profile</a></h4>

<div class="table-responsive">
    <table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
    	<thead>
	        <tr>
	            <td width="150" nowrap>Username</td>
	            <td width="100" nowrap>First name</td>
	            <td width="100" nowrap>Last name</td>
	            <td width="100" nowrap>Email</td>
	            <td width="10" nowrap>Type</td>
	            <td width="10" nowrap>Active</td>
	            <td width="10" nowrap></td>
	        </tr>
	    </thead>
	    <tbody>
		<?php
			$page_start = ($page - 1) * $limit;
		    $stmt = $db->prepare("SELECT UserId, Username, Firstname, Lastname, Email, UserType, Active FROM users WHERE `UserType`='A' LIMIT ?,?");
	        $stmt->bind_param( 'ii', $page_start, $limit );
		    $stmt->execute();
		    $stmt->bind_result($userId, $username, $firstname, $lastname, $email, $usertype, $active);
		    $stmt->store_result();
		    if ($stmt->num_rows > 0) {
			    while ($stmt->fetch()) {
		?>
        <tr>
            <td><a href="#" class="userEdit" data-user_id=<?php echo $userId ?>><?php echo $username ?></a></td>
            <td><?php echo $firstname ?></td>
            <td><?php echo $lastname ?></td>
            <td><?php echo $email ?></td>
            <td><?php echo $usertype ?></td>
            <td><?php echo $active ?></td>
            <td>
            	<a href="#" title="Change Password" data-toggle="modal" data-target="#passModal" class="updatePass" data-user_id=<?php echo $userId ?>><i class="fa fa-key fa-lg"></i></a> 
            	<a href="#" title="Delete User" data-toggle="modal" data-target="#deleteModal" class="userDelete" data-user_id=<?php echo $userId ?>><i class="fa fa-trash fa-lg"></i></a>
            </td>
        </tr>
		<?php
	    	}
   	    } else {
   	    ?>
   	    <tr>
	        <td align="right" colspan="6" class="text-center">No Result.</td>
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
                        <label for="email_addr">Email *</label>
                        <input type="input" class="form-control required" required="required" id="email_addr" placeholder="Email" name="email_addr"/>
                    </div>
                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="input" class="form-control" id="position" placeholder="Position" name="position"/>
                    </div>
                    <div class="form-group">
                        <label for="street">Street Address</label>
                        <input type="input" class="form-control" id="street" placeholder="Street Address" name="street"/>
                    </div>
                    <div class="form-group">
                        <label for="city">Suburb/Town/City</label>
                        <input type="input" class="form-control" id="city" placeholder="Suburb/Town/City" name="city"/>
                    </div>
                    <div class="form-group">
                        <label for="state">State/Region/County</label>
                        <input type="input" class="form-control" id="state" placeholder="State/Region/County" name="state"/>
                    </div>
                    <div class="form-group">
                        <label for="zipcode">Zip/Postcode</label>
                        <input type="input" class="form-control" id="zipcode" placeholder="Zip/Postcode" name="zipcode"/>
                    </div>
                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="input" class="form-control" id="country" placeholder="Country" name="country" value="Australia" disabled/>
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
                            <option value="A">Administrator</option>
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
                    <h5 class="modal-title">Delete User</h5>
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
                    <button type="submit" class="btn btn-primary btn-sm" name="Save" value="Save" id="btnDelete" disabled>DELETE</button>
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
<script>
	$(document).ready(function() { 
		var apiUri = "/api/users.php";
		function clearForm() {
			$('input[name="userId"]').val( "" );
			$('input[name="username"]').val( "" );
			$('input[name="first_name"]').val( "" );
			$('input[name="last_name"]').val( "" );
			$('input[name="email_addr"]').val( "" );
			$('input[name="position"]').val( "" );
			$('input[name="street"]').val( "" );
			$('input[name="city"]').val( "" );
			$('input[name="state"]').val( "" );
			$('input[name="zipcode"]').val( "" );
			$('input[name="phone_number"]').val( "" );
			$('input[name="mobile"]').val( "" );
			$('input[name="fax"]').val( "" );
			$('select[name="user_type"]').val( "" );
			$('input[name="active"]').prop( "checked", false );
		}

		$('#btnSave').click(function(e){
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
				$('input[name="position"]').val( data.Position );
				$('input[name="street"]').val( data.Street );
				$('input[name="city"]').val( data.City );
				$('input[name="state"]').val( data.State );
				$('input[name="zipcode"]').val( data.Zipcode );
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
	});
</script>