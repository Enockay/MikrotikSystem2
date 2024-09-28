<?php
// Ensure these variables are set before assigning them to session
//require './public/header.php';

$mac = $_POST['mac'];
$ip = $_POST['ip'];
$linkLogin = $_POST['link-login'];
$linkLoginOnly = $_POST['link-login-only'];
$profile = $_POST['profile'];

$_SESSION["mac"] = $mac;
$_SESSION["ip"] = $ip;
$_SESSION["link-login"] = $linkLogin;
$_SESSION["link-login-only"] = $linkLoginOnly;
$_SESSION["profile"] = $profile;

$_SESSION["user_type"] = "new";

$data = [
    'mac' => $mac,
    'ip' => $ip,
    //'link-login' => $linkLogin,
    //'link-login-only' => $linkLoginOnly,
];

// Log the details to Heroku logs
$logMessage = "Received details:\n" .
              "MAC: $mac\n" .
              "IP: $ip\n" .
              "Link Login: $linkLogin\n" .
              "Link Login Only: $linkLoginOnly\n" .
              "Profile: $profile\n";

error_log($logMessage);
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>blackie.purchase-package.com</title>
    <link rel="stylesheet" href="./public/assets/styles/pkg.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <p class="text-header">BlACKIE NETWORKS</p>
            <div class="header-advert">
                <div class="image">
                    <img src="./public/assets/images/logo.jpeg" alt="logo" height="60px" width="auto">
                </div>
                <div class="information">
                    <p>
                    <h5></h5>Strong && Fast 20Mbps network</p>
                </div>
                <div class="internet-speed">
                    <span>Contact us</span>
                    <p>0797763718</p>
                    <p>0796869402</p>
                </div>
                <div class="info2">
                <p>
                    20Mbps UNLIMIN</p>
                </div>
            </div>
        </div>
        <div class="main">
            <div class="sub-heading">
                <h4> Login Here </h4>

                <div class="form">
                    <label>PhoneNumber</label>
                    <input type="number" maximum=10 placeholder="phone.." class="phone" >
                </div>
                <!-- <h4 style="color: red; font-weight: bold;">System is under maintenance. Please contact the administrator for package reactivation once system is up. sorry for any inconvinencies</h4>!-->
                <button class="view">how to purchase</button>
                <div class="instruction">
                    <h5>How to purchase a package </h5>
                    <p>**if you have an active package just login above</p>
                    <p>1.Choose your package </p>
                    <p>2.Click buy </p>
                    <p>3.Click on Green connect button & wait form internet connection </p>
                    <p>Thank you for choosing blackie networks </p>
                </div>
            </div>
            <div class="item-content" id="item-content">
                <div class="package">
                    <p class="pkg-head">Hourly packages</p>
                    <div class="items">
                        <p class="item1"><span>30-mins package ksh=5</span> <button class="btn" onclick="purchaseItem('30-mins package ksh=5')">Buy</button></p>
                        <p class="item1">1-hour package ksh=10<button class="btn" onclick="purchaseItem('1-hour package ksh=10')">Buy</button></p>
                        <p class="item1">4-hours package ksh=15<button class="btn" onclick="purchaseItem('4-hours package ksh=15')">Buy</button>
                        <p>
                        <p class="item1">6-hours package ksh=20<button class="btn" onclick="purchaseItem('6-hours package ksh=20')">Buy</button></p>
                    </div>
                </div>

                <div class="package">
                    <p class="pkg-head">Weekly packages</p>
                    <div class="items">
                        <p class="item1">1-day package ksh=30<button class="btn" onclick="purchaseItem('1-day package ksh=30')">Buy</button></p>
                        <p class="item1">2-days package ksh=50<button class="btn" onclick="purchaseItem('2-days package ksh=50')">Buy</button></p>
                        <p class="item1">5-days package ksh=120<button class="btn" onclick="purchaseItem('5-days package ksh=120')">Buy</button>
                        <p>
                        <p class="item1">7-days package ksh=160<button class="btn" onclick="purchaseItem('7-days package ksh=160')">Buy</button></p>
                    </div>
                </div>

                <div class="package">
                    <p class="pkg-head">Monthly packages</p>
                    <p class="item1">14-days package ksh=230<button class="btn" onclick="purchaseItem('14-days package ksh=230')">Buy</button></p>
                    <p class="item1">21-days package ksh=320<button class="btn" onclick="purchaseItem('21-days package ksh=320')">Buy</button></p>
                    <p class="item1">28-days package ksh=400<button class="btn" onclick="purchaseItem('28-days package ksh=400')">Buy</button>
                    <p>
                    <p class="item1">31-days package ksh=430<button class="btn" onclick="purchaseItem('31-days package ksh=430')">Buy</button></p>
                </div>
            </div>
            <div class="prompt">
                <div class="loading">please wait..</div>
            </div>
        </div>

    </div>
    <footer>
    <div class="footer-content">
        <p>&copy; 2023 Enockay FSSD. All rights reserved.</p>
    </div>
   </footer>
    </div>
    <script src="./public/pkgs-src/pkg.js"></script>
</body>

</html>