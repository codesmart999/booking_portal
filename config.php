<?php

define ('TESTING', true);

if( TESTING ){
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

switch( $_SERVER['SERVER_NAME'] ){
	case 'www.chromis.local.net':
	case 'chromis.local.net':
	case 'secure.chromis.local.net':
	default:
		define('ROOT_FOLDER',  '/public_html/chromis_booking/');
		define('SITE_URL',     'http://cb.moneyballdev.com');
		define('SECURE_URL',   'https://cb.moneyballdev.com');

		define ('DB_USER',     'virtua21_chromis');
		define ('DB_PASS',     'pDg&8IrXgY8[');
		define ('DB_NAME',     'virtua21_chromis_booking');
	break;
}

define('DEFAULT_PAGE_NUM', 10);
$arrPageLimits = [2,5,7,10,15,20,50];
// Client Links
define('LOGIN_PAGE',  '/');
define('START_PAGE',  '/start');
define('SELECT_PAGE', '/select_time');
define('PROFILE_PAGE', '/profile');
define('CONFIRM_PAGE', '/confirm');

// adminLinks
define('ADMIN_INDEX',  '/admin/');

$arrAdminMenu = [
	"index" 			=> "Access an Individual System",
	"systems"			=> "Individual System Controls",
	"services" 			=> "Manage Services",
	"directory" 		=> "Directory Settings",
	"settings" 			=> [ 	"title" => "System Settings",
								"sub" => [
									"settings_booking" 	=> "Booking Settings",
									"settings_times" 	=> "Time Settings",
									"settings_email"		=> "Email Settings"
								]
							],
	"sub_admin" 		=> "Sub-Administrators",
	"reports" 			=> "Reports",
	"integration" 		=> "Data Integration",
	"logout" 			=> "Logout"
];

// Load Data from database on lib.php
$arrServices = [];
$arrLocations = [];

// TODO: manage timesheet via API from DB
$arrTimeSheets = [
	"09:00:00 to 09:15:00,09:15:00 to 09:30:00" => "09:00:00-09:30:00",
	"09:15:00 to 09:30:00,09:30:00 to 09:45:00" => "09:15:00-09:45:00",
	"09:30:00 to 09:45:00,09:45:00 to 10:00:00" => "09:30:00-10:00:00",
	"09:45:00 to 10:00:00,10:00:00 to 10:15:00" => "09:45:00-10:15:00",
	"10:00:00 to 10:15:00,10:15:00 to 10:30:00" => "10:00:00-10:30:00",
	"10:15:00 to 10:30:00,10:30:00 to 10:45:00" => "10:15:00-10:45:00",
	"10:30:00 to 10:45:00,10:45:00 to 11:00:00" => "10:30:00-11:00:00",
	"10:45:00 to 11:00:00,11:00:00 to 11:15:00" => "10:45:00-11:15:00",
	"11:00:00 to 11:15:00,11:15:00 to 11:30:00" => "11:00:00-11:30:00",
	"11:15:00 to 11:30:00,11:30:00 to 11:45:00" => "11:15:00-11:45:00",
	"11:30:00 to 11:45:00,11:45:00 to 12:00:00" => "11:30:00-12:00:00",
	"11:45:00 to 12:00:00,12:00:00 to 12:15:00" => "11:45:00-12:15:00",
	"12:00:00 to 12:15:00,12:15:00 to 12:30:00" => "12:00:00-12:30:00",
	"12:15:00 to 12:30:00,12:30:00 to 12:45:00" => "12:15:00-12:45:00",
	"12:30:00 to 12:45:00,12:45:00 to 13:00:00" => "12:30:00-13:00:00",
	"14:00:00 to 14:15:00,14:15:00 to 14:30:00" => "14:00:00-14:30:00",
	"14:15:00 to 14:30:00,14:30:00 to 14:45:00" => "14:15:00-14:45:00",
	"14:30:00 to 14:45:00,14:45:00 to 15:00:00" => "14:30:00-15:00:00",
	"14:45:00 to 15:00:00,15:00:00 to 15:15:00" => "14:45:00-15:15:00",
	"15:00:00 to 15:15:00,15:15:00 to 15:30:00" => "15:00:00-15:30:00",
	"15:15:00 to 15:30:00,15:30:00 to 15:45:00" => "15:15:00-15:45:00",
	"15:30:00 to 15:45:00,15:45:00 to 16:00:00" => "15:30:00-16:00:00",
	"15:45:00 to 16:00:00,16:00:00 to 16:15:00" => "15:45:00-16:15:00",
	"16:00:00 to 16:15:00,16:15:00 to 16:30:00" => "16:00:00-16:30:00",
	"16:15:00 to 16:30:00,16:30:00 to 16:45:00" => "16:15:00-16:45:00",
	"16:30:00 to 16:45:00,16:45:00 to 17:00:00" => "16:30:00-17:00:00"
];

session_start();
?>