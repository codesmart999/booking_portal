<?php
require_once('config.php');
require_once('lib.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link href="./scripts/bootstrap/bootstrap.min.css" rel="stylesheet">
    <!--[if lt IE 9]>
        <script src="./scripts/html5.js"></script>
    <![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <title>CHROMIS Booking System</title>

        <script src="./scripts/jquery-ui-1.10.0/js/jquery-1.9.0.js"></script>
        <script src="./scripts/jquery-ui-1.10.0/js/jquery-ui-1.10.0.custom.js"></script>
        <script src="./scripts/dates.js"></script>
        <script src="./scripts/bootstrap/bootstrap.bundle.min.js"></script>
        <link href="./css/chromisWCstyle.css" rel="stylesheet" type="text/css" />
        <link href="./css/chromisGED.css" rel="stylesheet" type="text/css" />
        <link href="./scripts/jquery-ui-1.10.0/css/ui-lightness/jquery-ui-1.10.0.custom.css" rel="stylesheet">
        <script src="./scripts/search.js"></script>
        <script src="./scripts/script.js"></script>

        <link href="./css/booking.css" rel="stylesheet" type="text/css" />


        <script src="./scripts/wc-form.js"></script>
        <script src="./scripts/jquery.validate.min.js"></script>
        <script src="./scripts/jqEasyCharCounter/jquery.jqEasyCharCounter.min.js"></script>
    </head>

    <body>
        <div id="line"></div>
        <div id="page-wrap">
            <div id="inside">
                <div id="container">
                    <div id="header">
                        <div id="logo">
                            <img src="./images/logo.png" width="277" height="126" alt="Chromis Medical Health Services" />
                        </div>
                    </div>
                    <?php if( $menu ){ 
                        include("./menu.php");
                    } ?>
                    <div id="content">