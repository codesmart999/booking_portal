<?php

    require_once('header.php');
	require_once('utils.php');

    header('Location: '. SECURE_URL . START_PAGE, true, 301);
    exit(0);
?>