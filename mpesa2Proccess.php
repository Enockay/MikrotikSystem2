<?php
include './accessToken.php';
include './public/header.php';

$transactionId = uniqid();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve form data if set
    $phoneNumber = isset($_POST['phoneNumber']) ? $_POST['phoneNumber'] : null;
    $amount = isset($_POST['amount']) ? $_POST['amount'] : null;
    $timeUnit = $_POST['timeUnit'] ;
    $time = $data = json_decode($timeUnit);

    if ($phoneNumber && $amount) {
        $money = $amount;

        try {
              
              date_default_timezone_set('Africa/Nairobi');
              $processrequestUrl = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
              $callbackurl = 'https://mikrotik-server-2e924a061565.herokuapp.com/callback.php';
              $passkey = "4b55ed5145cd2f614dbdf71743f5c5f84ca6574a824f1b7291f5e4b8983941e0";
              $BusinessShortCode = '6696654';
              $Timestamp = date('YmdHis');

              // ENCRIPT  DATA TO GET PASSWORD
              $Password = base64_encode($BusinessShortCode . $passkey . $Timestamp);
              $PartyA = $phoneNumber;
              $PartyB = '4086382';
              $AccountReference = 12345;
              $TransactionDesc = 'pay for your order';
              $stkpushheader = ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token];
            //INITIATE CURL

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $processrequestUrl);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $stkpushheader); 
            
            $curl_post_data = array(
                 //Fill in the request parameters with valid values
                 'BusinessShortCode' => $BusinessShortCode,
                 'Password' => $Password,
                 'Timestamp' => $Timestamp,
                 'TransactionType'=>'CustomerBuyGoodsOnline',
                 'Amount' => $money,
                 'PartyA' => $PartyA,
                 'PartyB' => $PartyB,
                 'PhoneNumber' => $PartyA,
                 'CallBackURL' => $callbackurl,
                 'AccountReference' => $AccountReference,
                 'TransactionDesc' => $TransactionDesc
           );

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $curl_response = curl_exec($curl);

       //ECHO  RESPONSE
       $data = json_decode($curl_response);
       $CheckoutRequestID = $data->CheckoutRequestID;
       $ResponseCode = $data->ResponseCode;

    if ($ResponseCode == "0") {
      header('Content-Type: application/json');
      echo json_encode(['transactionId' => $transactionId]);
      error_log("successfully responded");
     
      }else{
        header('Content-Type: application/json');
        echo json_encode(['ResultCode' => '1']);
        error_log("transaction cancelled");
      }

    } catch (Exception $error) {
    echo 'Error: ' . $error->getMessage();
  }
}

function convertToSeconds($time) {
    // Define a regular expression pattern to extract numeric value and unit
    $pattern = '/^(\d+)-(hour|day|min)$/i';

    // Check if the time unit matches the pattern
    if (preg_match($pattern, $time, $matches)) {
        $value = $matches[1];
        $unit = strtolower($matches[2]);

        // Convert the time unit value to seconds
        switch ($unit) {
            case 'min':
                error_log("time was minutes");
                return $value * 60;
            case 'hour':
                error_log("time was hours");
                return $value * 3600;
            case 'day':
                error_log("time was days");
                return $value * 86400;
            default:
                // Handle unknown time units or set a default timeout
                return 1800; // Set a default value (30 minutes) or adjust as needed
        }
    } else {
        // Return a default value if the time unit doesn't match the expected format
        error_log("time not correctly defined");
        return 1800;
    }
}    
    // Use the function to get the timeout in seconds
    $timeoutSeconds = convertToSeconds($time);
    $_SESSION['timeout'] = $timeoutSeconds;
    
}
?>