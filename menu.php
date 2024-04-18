<?php
    $user = null;
    if (isset($_SESSION['User'])) {
        $user = $_SESSION['User'];
    }

    if ($menu == "start"){
    	$start_menu = '<a class="nav-link active" href="' . SECURE_URL . START_PAGE . '">Start</a>';
    } else {
    	$start_menu = '<a class="nav-link" href="' . SECURE_URL . START_PAGE . '">Start</a>';
    }

    if ($menu == "select"){
    	$select_menu = '<a class="nav-link active" href="' . SECURE_URL . SELECT_PAGE . '">Select Times</a>';
    } else {
    	$select_menu = '<a class="nav-link" href="' . SECURE_URL . SELECT_PAGE . '">Select Times</a>';
    }

    if ($menu == "profile"){
    	$profile_menu = '<a class="nav-link active" href="' . SECURE_URL . PROFILE_PAGE . '">Profile</a>';
    } else {
    	$profile_menu = '<a class="nav-link" href="' . SECURE_URL . PROFILE_PAGE . '">Profile</a>';
    }

    if ($menu == "confirm"){
        $select_menu = '<a class="nav-link disabled" href="#">Select Times</a>';
        $profile_menu = '<a class="nav-link disabled" href="#">Profile</a>';
    	$confirm_menu = '<a class="nav-link active disabled" href="#">Confirmation</a>';
    } else {
    	$confirm_menu = '<a class="nav-link disabled" href="#">Confirmation</a>';
    }

    $manage_menu = '';
    if ($user['UserType'] == 'A') {
        $manage_menu = '<li class="navbar-item right"><a class="nav-link" href="' . SECURE_URL . ADMIN_INDEX . '">Manage Bookings</a></li>'
            . '<li class="navbar-item right" style="margin-left: 10px;"><a class="nav-link" href="' . SECURE_URL . LOGIN_PAGE . '?logout=1">Logout</a></li>';
    } else {
        $manage_menu = '<li class="navbar-item right"><a class="nav-link" href="' . SECURE_URL . LOGIN_PAGE . '?logout=1">Logout</a></li>';
    }

    $r =    '<nav id="nav" class="navbar navbar-expand-md navbar-light">
                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="collapsibleNavbar">
                        <ul class="navbar-nav">
                        	<li class="navbar-item">
                        		'.$start_menu.'
                        	</li>
                        	<li class="navbar-item">
                        		'.$select_menu.'
                        	</li>
                        	<li class="navbar-item">
                        		'.$profile_menu.'
                        	</li>
                        	<li class="navbar-item">
                        		'.$confirm_menu.'
                        	</li>'
                            . $manage_menu .
                        '</ul>
                    </div>
                </div>
            </nav>';

    echo $r;
