<?php
header("Access-Control-Allow-Origin: http://blackienetworks.com");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Preflight request, we just need to return the headers
    exit(0);
}


session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if either session mac is set, POST mac is set, it's a Safaricom request, or it's a POST request
/**if (!(isset($_SESSION['mac']) || isset($_POST['mac']) || ($_SERVER['REQUEST_METHOD'] === 'POST'))) {
    exit('This page cannot be accessed directly. It only works when using a hotspot.');
}**/

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$business_name = "blackie-networks";
?>