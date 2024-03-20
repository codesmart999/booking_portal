<?php

$menu = false;
require_once('header.php');

if(isset($_GET['logout'])) {
	session_destroy();
}

if( isset( $_SESSION['User'] ) ){
	if($_SESSION['User']['UserId']){
		header('Location: '. SECURE_URL . START_PAGE, true, 301);
		exit(0);
	}
}

?>

<form method="post" id="APP_FORM">
	<div class="row">
		<div class="col-md-4 text-center">
			<h1>Login</h1>
			<!--a href="#" title="Retrieve password for your system if you have forgotten it">Retrieve Password?</a-->
		</div>
		<div class="col-md-8">
			<div class="table-responsive">
				<table class="table">
					<tr>
						<td style="width: 40%">Username</td>
						<td style="width: 60%"><label>
							<input class="form-control form-control-sm required" type="text" name="username" id="username" required=""/>
						</label></td>
					</tr>
					<tr>
						<td>Password</td>
						<td><label>
							<input class="form-control form-control-sm required" type="password" name="password" id="password" required=""/>
						</label></td>
					</tr>
					<tr>
						<td class="text-center" colspan="2"><label>
							<button type="submit" class="btn btn-primary btn-sm" name="login" id="Login" value="Submit">Login</button>
						</label></td>
					</tr>
				</table>
			</div>
		</div>
		
	</div>
</form>


<?php
require_once('footer.php');
?>