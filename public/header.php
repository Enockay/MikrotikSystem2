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

// Logging function
function logMessage(string $message): void
{
    $logFile = __DIR__ . '/mikrotik_api.log';
    $timestamp = date('[Y-m-d H:i:s]');
    
    // Get debug backtrace to include file and line number
    $debugTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
    $file = $debugTrace[0]['file'] ?? 'unknown file';
    $line = $debugTrace[0]['line'] ?? 'unknown line';

    // Get the request method, if available (e.g., in a web server environment)
    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'CLI';

    // Combine all details into the log message
    $formattedMessage = "{$timestamp} [{$requestMethod}] {$message} in {$file} on line {$line}";
    
    file_put_contents($logFile, "{$formattedMessage}\n", FILE_APPEND);
}
// Function to send JavaScript updates to the client
function sendJsUpdate(string $message): void
{
    echo "<script>updateStatus('" . addslashes($message) . "');</script>";
}

// Check if it's a POST request and store the session variables
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    logMessage("Received POST request");

    if (isset($_POST['mac'])) {
        $_SESSION['mac'] = $_POST['mac'];
        logMessage("MAC Address set: " . $_POST['mac']);
        sendJsUpdate("MAC Address saved.");
    }

    if (isset($_POST['phoneNumber'])) {
        $_SESSION['phoneNumber'] = $_POST['phoneNumber'];
        logMessage("Phone Number set: " . $_POST['phoneNumber']);
        sendJsUpdate("Phone Number saved.");
    }

    if (isset($_POST['remainingTime'])) {
        $_SESSION['remainingTime'] = intval($_POST['remainingTime']);
        logMessage("Remaining Time set: " . $_POST['remainingTime']);
        sendJsUpdate("Remaining time saved.");
    }

    if (isset($_POST['routername'])) {
        $_SESSION['routername'] = $_POST['routername'];
        logMessage("Router Name set: " . $_POST['routername']);
        sendJsUpdate("Router name saved.");
    }

    if (isset($_POST['amount'])) {
        $_SESSION['amount'] = $_POST['amount'];
        logMessage("Amount set: " . $_POST['amount']);
        sendJsUpdate("Amount saved.");
    }

    if (isset($_POST['TransactionCode'])) {
        $_SESSION['TransactionCode'] = $_POST['TransactionCode'];
        logMessage("Transaction Code set: " . $_POST['TransactionCode']);
        sendJsUpdate("Transaction code saved.");
    }

    // Log all session variables for debugging purposes
    logMessage("Session Variables: " . json_encode($_SESSION));
    sendJsUpdate("Session variables successfully logged.");
}

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$business_name = "blackie-networks";
logMessage("Business Name: $business_name");
?>
