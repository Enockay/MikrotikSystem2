<?php
include './public/header.php';

// Function to handle JSON decoding and validation
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $json_data = file_get_contents('php://input');

    // Decode JSON data into a PHP associative array
    $data = json_decode($json_data, true);

    $phoneNumber = $data['phoneNumber'];
    $timeUnit = $data['timeUnit'];

    $mac = "1D:2F:3D:7F:00";//$_SESSION['mac'];
    $ip = "192.168.100";//$_SESSION['ip'];

    
   // error_log($phoneNumber);
    $data = [
        'mac' => $mac,
        'ip' => $ip,
        'phoneNumber' => $phoneNumber,
        'timeunit' => $timeUnit
    ];

    $nodeServerUrl = 'https://node-blackie-networks.fly.dev/session';

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data),
        ],
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($nodeServerUrl, false, $context);
    
    if ($result === FALSE) {
        // Error handling
        header('Content-Type: application/json');
        //echo json_encode(['ResultCode' => '1']);
    } else {
        // Attempt to decode the JSON response
        $decodedResult = json_decode($result, true);
    
        if ($decodedResult !== null) {
            // Check the value of responseState
            $responseState = $decodedResult['ResultCode'];
            error_log(print_r($decodedResult, true));
            // Handle the response based on the value of responseState
            switch ($responseState) {
                case 0:
                    header('Content-Type: application/json');
                    echo json_encode(['ResultCode' => 0]);
                    break;
                case 1:
                    header('Content-Type: application/json');
                    echo json_encode(['ResultCode' => 1]);
                    break;
                case 2:
                    header('Content-Type: application/json');
                    echo json_encode(['ResultCode' => 2]);
                    break;
                default:
                    // Handle other cases if needed
                    break;
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['ResultCode' => 3]);
        }
    }
    
}