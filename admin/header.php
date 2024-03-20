<?php
require_once('../config.php');
require_once('../lib.php');

// Check Admin Login
if( $_SESSION['User']['UserType'] != "A" && $_SERVER['REQUEST_URI'] != ADMIN_INDEX."/" ) {
    header('Location: '. SECURE_URL . LOGIN_PAGE, true, 301);
    exit(0);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" class="admin_page">
<head>
    <link href="../scripts/bootstrap/bootstrap.min.css" rel="stylesheet">
    <!--[if lt IE 9]>
        <script src="../scripts/html5.js"></script>
    <![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <title>CHROMIS Booking Administrator</title>

        <link href="../css/chromisWCstyle.css" rel="stylesheet" type="text/css" />
        <link href="../css/chromisGED.css" rel="stylesheet" type="text/css" />
        <link href="../scripts/jquery-ui-1.10.0/css/ui-lightness/jquery-ui-1.10.0.custom.css" rel="stylesheet">
        <link href="../css/booking.css" rel="stylesheet" type="text/css" />
        <link href="./css/sidebar.css" rel="stylesheet" type="text/css" />
        <link href="./css/style.css" rel="stylesheet" type="text/css" />
    </head>

    <body>
        <div id="page-wrap">
            <div id="line"></div>
            <div id="inside">
                <div id="container">
                    <div id="header">
                        <div id="logo">
                            <img src="../images/logo.png" width="277" height="126" alt="Chromis Medical Health Services" />
                        </div>
                    </div>
                    <?php 
                        include("./sidebar.php");
                    ?>