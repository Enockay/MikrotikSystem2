<?php
// This must be the very first thing in the file, before any HTML output
header("Access-Control-Allow-Origin: http://blackienetworks.com");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Preflight request, return the headers and exit
    exit(0);
}

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if it's a POST request and store the session variables
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mac'])) {
        $_SESSION['mac'] = $_POST['mac'];
    }

    if (isset($_POST['phoneNumber'])) {
        $_SESSION['phoneNumber'] = $_POST['phoneNumber'];
    }

    if (isset($_POST['remainingTime'])) {
        $_SESSION['remainingTime'] = intval($_POST['remainingTime']);
    }

    if (isset($_POST['routername'])) {
        $_SESSION['routername'] = $_POST['routername'];
    }

    if (isset($_POST['amount'])) {
        $_SESSION['amount'] = $_POST['amount'];
    }
    if (isset($_POST['TransactionCode'])) {
        $_SESSION['TransactionCode'] = $_POST['TransactionCode'];
    }
    // Add more session variables as needed
}

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$business_name = "blackie-networks";
?>