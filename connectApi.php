<?php
require './public/routeros_api.class.php';
require './public/header.php';

// Set your MikroTik router credentials
$router_ip = 'app.vexifi.com:788'; //getenv('ROUTER_IP');
$router_username = 'admin'; //getenv('ROUTER_USERNAME');
$router_password = 'enock'; //getenv('ROUTER_PASSWORD');

// Retrieve posted data
$device_phone_number = isset($_POST['phoneNumber']) ? $_POST['phoneNumber'] : '';

$expiry_seconds = isset($_POST['expiry']) ? intval($_POST['expiry']) : 3600; // Default expiry time of 1 hour if not provided
$profile = isset($_POST['profile']) ? $_POST['profile'] : '';

// Connect to MikroTik router via API
$API = new RouterosAPI();
$API->debug = false;

if ($API->connect($router_ip, $router_username, $router_password)) {
    // Calculate expiry date based on current time + expiry seconds
    $expiry_date = date('Y-m-d H:i:s', time() + $expiry_seconds);

    // Check if the user already exists
    $API->write('/ip/hotspot/user/print', false);
    $API->write('?name=' . $device_phone_number, true);
    $READ = $API->read(false);
    $ARRAY = $API->parseResponse($READ);

    if (empty($ARRAY)) {
        // User does not exist, create a new one
        $API->write('/ip/hotspot/user/add', false);
        $API->write('=name=' . $device_phone_number, false); // Use phone number as username
        $API->write('=password=', false); // Blank password
        $API->write('=profile=' . $profile, false); // Specify Hotspot profile (replace 'your_profile_name_here' with your actual profile name)
        $API->write('=comment=' . $device_phone_number . '; expiry=' . $expiry_date, true); // Store phone number as comment with expiry date

        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);

        if (isset($ARRAY['!trap'])) {
            // Error occurred while adding user
            echo "Failed to add user. Error: " . $ARRAY['!trap'][0]['message'];
        } else {
            header("Location: ./authenticateApi.php");
            exit; 
        }
    } else {
        // User already exists
        header("Location: ./authenticateApi.php");
            exit; 
    }

    $API->disconnect();
} else {
    // Failed to connect to MikroTik router
    echo "system error! contact System admin 0796869402 urgently .";
}
?>
