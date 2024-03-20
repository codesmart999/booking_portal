<?php
	$arrMessages = [
		"no_time_available" => "Sorry, there are no times available.",
		"impossible_admin_edit" => "Sorry, You can't change Super Admin Profile.",
		"impossible_admin_delete" => "Sorry, You can't delete Super Admin User.",
		"username_exists" => "Sorry, Email or Username alrady exists.",
		"name_exists" => "Sorry, That name is alrady used.",
		"success_register" => "Registered Successully.",
		"success_update" => "Updated Successully.",
		"success_delete" => "Deleted Successully."
	];

	function _lang( $key ) {
		global $arrMessages;
		
		return $arrMessages[$key];
	}
?>