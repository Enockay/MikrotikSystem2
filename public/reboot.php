<?php
header("Access-Control-Allow-Origin: https://admin-blackie-y3kg.vercel.app/");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
require './public/header.php';
require './public/routeros_api.class.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming the request contains appropriate authentication and authorization checks

    $router_ip = 'app.vexifi.com:558'; // IP address of your MikroTik router
    $router_username = 'enock'; // RouterOS username
    $router_password = 'enockay'; // RouterOS password

    // Connect to MikroTik router via API
    $API = new RouterosAPI();
    $API->debug = false;

    if ($API->connect($router_ip, $router_username, $router_password)) {
        // Send command to reboot router
        $API->write('/system/reboot', true);
        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);

        if (isset($ARRAY['!trap'])) {
            // Error occurred while rebooting router
            echo "Error in rebooting router. Error: " . $ARRAY['!trap'][0]['message'];
        } else {
            echo "Router reboot was successfully done.";
        }

        $API->disconnect();
    } else {
        echo "Failed to connect to MikroTik router.";
    }
} else {
    // If request method is not POST, show error message
    echo "Invalid request method. Only POST requests are allowed.";
}
?>

