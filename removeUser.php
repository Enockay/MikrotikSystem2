<?php
header("Access-Control-Allow-Origin: https://admin-blackie-y3kg.vercel.app");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");


require './public/routeros_api.class.php';

$router_ip = 'app.vexifi.com:558'; 
$router_username = 'api'; 
$router_password = 'enock'; 

// Check if the request method is POST
    // Assuming the request contains appropriate authentication and authorization checks
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    // Extract the username from the POST data
    $username_to_remove = '0796869402';

    // Connect to MikroTik router via API
    $API = new RouterosAPI();
    $API->debug = false;

    if ($API->connect($router_ip, $router_username, $router_password)) {
        // Get list of active users
        $API->write('/ip/hotspot/active/print', false);
        $API->write('?');
        $API->write('=.proplist=.id,user'); // Retrieve only necessary properties
        $API->write('=stats=!cookies'); // Exclude statistics cookies
        $API->write('=count-only=');

        $READ = $API->read(false);
        $count = $API->parseResponse($READ);
        $total = $count[0]['ret'];

        $API->write('/ip/hotspot/active/print', false);
        $API->write('?');
        $API->write('=.proplist=.id,user');
        $API->write('=stats=!cookies');

        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);

        // Iterate through active users
        foreach ($ARRAY as $user) {
            // Check if user has the specified username
            if ($user['user'] === $username_to_remove) {
                // Disconnect user
                $API->write('/ip/hotspot/active/remove', false);
                $API->write('=.id=' . $user['.id'], true);

                $READ = $API->read(false);
                $ARRAY = $API->parseResponse($READ);

                if (isset($ARRAY['!trap'])) {
                    // Error occurred while disconnecting user
                    echo "Error in disconnecting user. Error: " . $ARRAY['!trap'][0]['message'];
                } else {
                    echo "User " . $username_to_remove . " disconnected.";
                }
            }
        }

        $API->disconnect();
    } else {
        echo "Failed to connect to MikroTik router.";
    }

?>
