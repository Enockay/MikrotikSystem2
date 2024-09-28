<?php
include './public/header.php';

// Function to handle JSON decoding and validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');

    // Decode JSON data
    $phone = json_decode($json_data, true);

    $_SESSION['phoneNumber'] = $phone;
    $mac = "00:01:4B:7B:27";//$_SESSION['mac'];
    $ip = "192.168.100.1";//$_SESSION['ip'];
    
    $phoneNumber = $phone['UserPhoneNumber']; // Corrected

    $data = [
        'mac' => $mac,
        'phoneNumber' => $phoneNumber, // Corrected
    ];

    $nodeServerUrl = 'https://node-blackie-networks.fly.dev/api/jwt';

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
        echo json_encode(['ResultCode' => '1']);
    } else {
        // Attempt to decode the JSON response
        $decodedResult = json_decode($result, true);

        if ($decodedResult !== null) {
            // Check the value of responseState
            $responseState = $decodedResult['ResultCode']; // Corrected
            $remainingTime = isset($decodedResult['RemainingTime']) ? $decodedResult['RemainingTime'] : null;

            // Handle the response based on the value of responseState
            switch ($responseState) {
                case 0:
                    header('Content-Type: application/json');
                    echo json_encode(['ResultCode' => 0, 'RemainingTime' => $remainingTime]);
                    break;
                case 1:
                    header('Content-Type: application/json');
                    echo json_encode(['ResultCode' => 1, 'RemainingTime' => $remainingTime]);
                    break;
                case 2:
                    header('Content-Type: application/json');
                    echo json_encode(['ResultCode' => 2, 'RemainingTime' => $remainingTime]);
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
?>
