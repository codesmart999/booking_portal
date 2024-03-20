<?php
	require_once('header.php');

	session_destroy();
	header('Location: '. SECURE_URL . SELECT_PAGE, true, 301);
	
	exit(0);
?>