<?php

define ('TESTING', true);

if( TESTING ){
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

switch( $_SERVER['SERVER_NAME'] ){
	case 'codesmart.local':
		define('ROOT_FOLDER',  '/public_html/chromis_booking/');
		define('SITE_URL',     'http://codesmart.local:8080');
		define('SECURE_URL',   'https://codesmart.local:8081');

		define ('DB_USER',     'root');
		define ('DB_PASS',     '');
		define ('DB_NAME',     'virtua21_chromis_booking');
		break;
	case 'codemax999.com':
		define('ROOT_FOLDER',  '/public_html/chromis_booking/');
		define('SITE_URL',     'http://codemax999.com:8080');
		define('SECURE_URL',   'https://codemax999.com:8081');

		define ('DB_USER',     'root');
		define ('DB_PASS',     '');
		define ('DB_NAME',     'virtua21_chromis_booking');
		break;
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
	// "directory" 		=> "Directory Settings",
	"settings" 			=> [ 	"title" => "System Default Settings",
								"sub" => [
									"settings_booking" 	=> "Booking Settings",
									"settings_times" 	=> "Time Settings",
									"settings_email"		=> "Email Settings"
								]
							],
	"bookings" 			=> [ 	"title" => "Manage Bookings",
							"sub" => [
								"move_bookings" 	=> "Move Bookings",
								// "search_bookings" 	=> "Search Bookings",
								// "view_history"		=> "View History"
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

session_start();
?>