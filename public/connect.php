<?php
require './routeros_api.class.php';
require './header.php';

// Variables
$mac = "1D:2D:3F:00:9D";
$ip = "192.168.100.1";
$server = 'server1';
$link_login = "http://blackieNetworks.com/login";
$link_login_only = "http://blackieNetworks.com/login";
$linkorig = "https://www.google.com";

$phoneNumber = isset($_SESSION['phoneNumber']) ? $_SESSION['phoneNumber'] : "";
$remainingTime = isset($_SESSION['remainingTime']) ? $_SESSION['remainingTime'] : 0;
$identity =  isset($_SESSION['routername']) ? $_SESSION['routername'] : "";
$TransactionCode =  isset($_SESSION['TransactionCode']) ? $_SESSION['TransactionCode'] : "";

// The rest of your connect.php code remains unchanged
$routers = [
    'piusMikrotik' => [
        'ip' => 'app.vexifi.com:558',
        'username' => 'api',
        'password' => 'enock'
    ],
    'enockMikrotik' => [
        'ip' => 'app.vexifi.com:2159',
        'username' => 'api',
        'password' => 'enock'
    ],
    // Add more routers here as needed
];

// Check if remaining time is negative
if ($remainingTime < 0) {
    header("Location: http://blackieNetworks.com/login");
    exit();
}

if (array_key_exists($identity, $routers)) {
    $router_ip = $routers[$identity]['ip'];
    $router_username = $routers[$identity]['username'];
    $router_password = $routers[$identity]['password'];
} else {
    // Redirect or handle error if the identity is not recognized
    echo '
    <!DOCTYPE HTML>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="./assets/styles/tailwind.min.css">
        <title>Error</title>
    </head>
    <body class="bg-blue-500 flex items-center justify-center min-h-screen">
        <div class="bg-white shadow-md rounded px-8 py-6 max-w-lg text-center">
            <h1 class="text-2xl font-semibold text-red-500 mb-4">Unknown Router Identity</h1>
            <p class="text-gray-600">The identity you provided does not match any recognized router.</p>
            <a href="http://blackieNetworks.com/login" class="inline-block mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                Go Back
            </a>
        </div>
    </body>
    </html>';
    exit();
}

$timeRanges = [
    [0, 300, "5min"],
    [300, 600, "10min"],
    [600, 900, "15min"],
    [900, 1200, "20min"],
    [1200, 1500, "25min"],
    [1500, 1800, "30min"],
    [1800, 2100, "35min"],
    [2100, 2400, "40min"],
    [2400, 2700, "45min"],
    [2700, 3000, "50min"],
    [3000, 3300, "55min"],
    [3300, 3600, "1hr"],
    [3600, 7200, "2hr"],
    [7200, 10800, "3hr"],
    [10800, 14400, "4hr"],
    [14400, 18000, "5hr"],
    [18000, 21600, "6hr"],
    [21600, 25200, "7hr"],
    [25200, 28800, "8hr"],
    [28800, 32400, "9hr"],
    [32400, 36000, "10hr"],
    [36000, 39600, "11hr"],
    [39600, 43200, "12hr"],
    [43200, 46800, "13hr"],
    [46800, 50400, "14hr"],
    [50400, 54000, "15hr"],
    [54000, 57600, "16hr"],
    [57600, 61200, "17hr"],
    [61200, 64800, "18hr"],
    [64800, 68400, "19hr"],
    [68400, 72000, "20hr"],
    [72000, 75600, "21hr"],
    [75600, 79200, "22hr"],
    [79200, 82800, "23hr"],
    [82800, 86400, "24hr"],
    [86400, 172800, "24hr"]  // Duplicate entry for 24hr to handle extended time
];

// Default username if no range is matched
$username = "24hr";

// Determine the user profile based on the remaining time range
foreach ($timeRanges as $range) {
    $startRange = $range[0];
    $endRange = $range[1];
    $profileName = $range[2];

    if ($remainingTime >= $startRange && $remainingTime < $endRange) {
        $username = $profileName;
        break;
    }
}
$_SESSION['username'] = $username;

// Connect to MikroTik router via API
$API = new RouterosAPI();
$API->debug = false;

if ($API->connect($router_ip, $router_username, $router_password)) {

    // Check for active devices using the phone number
    $API->write('/ip/hotspot/active/print', false);
    $API->write('?user=' . $phoneNumber, true);
    $READ = $API->read(false);
    $activeDevices = $API->parseResponse($READ);


    // If the user has more than one device connected, find the oldest session and remove it
    if (count($activeDevices) >= 2) {
?>
       <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Device Limit Reached</title>
    <link rel="stylesheet" href="../public/assets/styles/tailwind.min.css">
</head>
<body class="bg-red-400 flex items-center justify-center min-h-screen">

    <div class="bg-white p-10 rounded shadow-md text-center h-80">
        <h4 class="text-2xl font-bold mb-4 text-red-600">Maximum Devices Connected for <?php echo $phoneNumber; ?></h4>
        <p class="text-lg font-semibold mb-6">You have reached the maximum number of connected devices per package.</p>

        <ul class="mb-4">
            <?php foreach ($activeDevices as $device) { ?>
                <li class="text-2xl flex justify-between bg-gray-100 p-2 rounded my-2">
                    <span>Device IP: <?php echo $device['address']; ?></span>
                    <button onclick="openModal('<?php echo $device['address']; ?>')" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Disconnect</button>
                </li>
            <?php } ?>
        </ul>
    </div>

    <!-- Modal -->
    <div id="transactionModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded shadow-md text-center w-80">
            <h3 class="text-lg font-bold mb-4 text-red-600">Enter Mpesa Transaction Code For This Package</h3>
            <form id="disconnectForm" method="post" action="disconnect.php">
                <input type="hidden" name="device_ip" id="deviceIp">
                <input type="text" name="transaction_code" id="transactionCode" placeholder="Transaction Code" required class="border p-2 rounded w-full mb-4" aria-placeholder="STVXXXXXR">
                <button type="button" onclick="validateAndSubmit()" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Submit</button>
                <button type="button" onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mt-2">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(deviceIp) {
            document.getElementById('deviceIp').value = deviceIp;
            document.getElementById('transactionModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('transactionModal').classList.add('hidden');
            document.getElementById('transactionCode').value = ''; // Clear the input field
        }

        function validateAndSubmit() {
            const transactionCode = document.getElementById('transactionCode').value;

            if (transactionCode === "<?php echo $TransactionCode; ?>") { // Validate transaction code here
                document.getElementById('disconnectForm').submit();
            } else {
                alert("Invalid transaction code. Please try again.");
            }
        }
    </script>

</body>
</html>


    <?php
    }

    // Proceed with the login or account creation as before
    $API->write('/ip/hotspot/user/print', false);
    $API->write('?name=' . $phoneNumber, true);
    $READ = $API->read(false);
    $ARRAY = $API->parseResponse($READ);

    if (empty($ARRAY)) {
        $API->write('/ip/hotspot/user/add', false);
        $API->write('=name=' . $phoneNumber, false);
        $API->write('=password=', false);
        $API->write('=profile=' . $username, false);
        $API->write('=comment=' . $phoneNumber, true);
        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);

        if (isset($ARRAY['!trap'])) {
            // Error in creating an account
            echo '
            <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Blackie Networks</title>
            <link rel="stylesheet" href="./assets/styles/tailwind.min.css">
        </head>
        <body>
            <div class="w-full max-w-md mx-auto mt-6">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Account Creation Error!</strong>
                    <span class="block sm:inline">There was an issue creating an account for you. Please try again later.</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 5.652a1 1 0 00-1.414 0L10 8.586 7.066 5.652a1 1 0 10-1.414 1.414L8.586 10l-2.934 2.934a1 1 0 101.414 1.414L10 11.414l2.934 2.934a1 1 0 101.414-1.414L11.414 10l2.934-2.934a1 1 0 000-1.414z"/></svg>
                    </span>
                </div>
                <div class="mt-4">
                    <button onclick="window.location.href=\'http://blackieNetworks.com/login\'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Retry Account Creation
                    </button>
                </div>
            </div>
            </body>
            </html>';
            exit();
        }
    } else {
        if ($ARRAY[0]['profile'] != $username) {
            $API->write('/ip/hotspot/user/set', false);
            $API->write('=.id=' . $ARRAY[0]['.id'], false);
            $API->write('=profile=' . $username, true);
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);

            if (isset($ARRAY['!trap'])) {
                // Error in setting up the account
                echo '
                <div class="w-full max-w-md mx-auto mt-6">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Account Setup Failed!</strong>
                        <span class="block sm:inline">There was an error setting up your account. Please contact the admin.</span>
                        <span class="block sm:inline mt-2 text-sm">
                            Error Details: ' . htmlspecialchars($ARRAY['!trap'][0]['message']) . '
                        </span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                            <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 5.652a1 1 0 00-1.414 0L10 8.586 7.066 5.652a1 1 0 10-1.414 1.414L8.586 10l-2.934 2.934a1 1 0 101.414 1.414L10 11.414l2.934 2.934a1 1 0 101.414-1.414L11.414 10l2.934-2.934a1 1 0 000-1.414z"/></svg>
                        </span>
                    </div>
                </div>';
                exit();
            }
        }
    }

    $API->disconnect()

    // Proceed with HTML login form as usual
    ?>
    <!DOCTYPE HTML>
    <html>

    <head>
        <link rel="stylesheet" href="../public/assets/styles/tailwind.min.css">
        <style>
            @keyframes loading {
                0% {
                    left: -100%;
                }

                100% {
                    left: 100%;
                }
            }
        </style>
    </head>

    <body class="bg-blue-50 flex items-center justify-center min-h-screen">
        <div id="details" class="bg-white p-10 rounded shadow-md text-center">
            <h4 class="text-2xl font-bold mb-4">Details Verification Ongoing</h4>
            <p class="text-lg font-semibold mb-6"><em>Please Wait</em></p>
            <div class="loading-container flex justify-center items-center">
                <div class="loading-bar w-48 h-5 bg-gray-200 relative overflow-hidden">
                    <div class="loading-animation w-full h-full bg-blue-600 absolute left-0 animate-loading"></div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            function formAutoSubmit() {
                var frm = document.getElementById("login");
                frm.submit();
            }
            window.onload = setTimeout(formAutoSubmit, 2500);
        </script>
        <form id="login" method="post" action="<?php echo $link_login_only; ?>" onSubmit="return doLogin()">
            <input name="dst" type="hidden" value="<?php echo $linkorig; ?>" />
            <input name="popup" type="hidden" value="false" />
            <input name="username" type="hidden" value="<?php echo $phoneNumber; ?>" />
            <input name="domain" type="hidden" value="" />
            <input name="password" type="hidden" />
        </form>
    </body>

    </html>
<?php
} else {
?>
    <!DOCTYPE HTML>
    <html>

    <head>
        <link rel="stylesheet" href="../public/assets/styles/tailwind.min.css">
    </head>

    <body class="flex items-center justify-center min-h-screen bg-blue-50">
        <div class="text-center">
            <h3 class="text-2xl text-blue-600 font-bold">
                System starting Error, please wait for a few minutes and try again.
            </h3>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = 'http://blackieNetworks.com/login';
            }, 1000);
        </script>
    </body>

    </html>
<?php
}
?>