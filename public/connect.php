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

$remainingTime = isset($_POST['remainingTime']) ? intval($_POST['remainingTime']) : 0;
$phoneNumber = isset($_POST['phoneNumber']) ? $_POST['phoneNumber'] : "";
$identity = isset($_POST['routername']) ? $_POST['routername'] : "";

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

// Check if the identity exists in the routers array
if (array_key_exists($identity, $routers)) {
    $router_ip = $routers[$identity]['ip'];
    $router_username = $routers[$identity]['username'];
    $router_password = $routers[$identity]['password'];
} else {
    // Redirect or handle error if the identity is not recognized
    echo "Unknown router identity.";
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

// Connect to MikroTik router via API
$API = new RouterosAPI();
$API->debug = false;

if ($API->connect($router_ip, $router_username, $router_password)) {

    // Check for active devices using the phone number
    $API->write('/ip/hotspot/active/print', false);
    $API->write('?user=' . $phoneNumber, true);
    $READ = $API->read(false);
    $activeDevices = $API->parseResponse($READ);

    
    // If the user has more than two devices connected, display the form to disconnect
    if (count($activeDevices) >= 2) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Device Limit Reached</title>
            <link rel="stylesheet" href="../public/assets/styles/tailwind.min.css"> 
        </head>
        <body class="bg-red-400 flex items-center justify-center min-h-screen">
            <div class="bg-white p-10 rounded shadow-md text-center h-80">
                <h4 class="text-2xl font-bold mb-4 text-red-600">Maximum Devices Connected for <?php echo $phoneNumber; ?></h4>
                <p class="text-lg font-semibold mb-6">You have reached the maximum number of connected devices per package.</p>
    
                <!-- Form for disconnecting devices -->
                <form method="post" action="">
                    <span class="text-gray-900">Contact this <>0796869402/0791218989<> to disconnect one device for you </span>
                    <ul class="mb-4">
                        <?php foreach ($activeDevices as $device) { ?>
                            <li class="text-2xl flex justify-between bg-gray-100 p-2 rounded my-2">
                                <span>Device IP: <?php echo $device['address']; ?></span>
                                <!-- Disconnect button -->
                                <button name="disconnect" value="<?php echo $device['.id']; ?>" class="text-red-500 hover:text-red-700">
                                    Disconnect
                                </button>
                            </li>
                        <?php } ?>
                    </ul>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
    
    // If disconnect button is clicked, remove the selected device
    if (isset($_POST['disconnect'])) {
        $deviceId = $_POST['disconnect'];
    
        // Remove the device from the active hotspot users
        $API->write('/ip/hotspot/active/remove', false);
        $API->write('=.id=' . $deviceId, true);
        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);
    
        // Handle success or error
        if (isset($ARRAY['!trap'])) {
            echo "Error in disconnecting the device.";
        } else {
            echo "Device disconnected successfully.";
            // You can refresh the page or redirect after the successful disconnection
            header("Refresh:0");
        }
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
            echo "Error in creating an account for you";
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
                echo "Error in setting up your account. Please contact admin. Error: " . $ARRAY['!trap'][0]['message'];
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
                0% { left: -100%; }
                100% { left: 100%; }
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