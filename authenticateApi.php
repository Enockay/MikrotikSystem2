<?php
require './public/header.php';
require './public/routeros_api.class.php';

// Variables
$mac = "1D:2D:3F:00:9D";
$ip = "192.168.100.1";
$server = 'server1';
$link_login = "http://blackieNetworks.com/login";
$link_login_only = "http://blackieNetworks.com/login";
$linkorig = "https://www.google.com";

$phoneNumber = isset($_SESSION['phoneNumber']) ? $_SESSION['phoneNumber'] : "No phone number set";
$identity =  isset($_SESSION['routername'])? $_SESSION['routername']:"";
$amount = isset($_SESSION['amount']) ? $_SESSION['amount'] : "No amount set";
//error_log($remainingTime , $phoneNumber,$identity);

$routers = [
    'piusMikrotik' => ['ip' => 'id-40.hostddns.us:14182', 'username' => 'api', 'password' => 'enock'],
    'enockMikrotik' => ['ip' => 'app.vexifi.com:2159', 'username' => 'api', 'password' => 'enock']
];

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
        <link rel="stylesheet" href="./public/assets/styles/tailwind.min.css">
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
// Determine username based on amount
$usernames = [
    5 => "30min",
    10 => "1hr",
    20 => "5hr",
    25 => "12hr",
    35 => "1day",
    
];
$username = $usernames[$amount] ?? "1hr";

// Connect to MikroTik router via API
$API = new RouterosAPI();
$API->debug = false;

if ($API->connect($router_ip, $router_username, $router_password)) {
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

    $API->write('/ip/hotspot/active/print', false);
    $API->write('?user=' . $phoneNumber, true);
    $READ = $API->read(false);
    $ACTIVE_ARRAY = $API->parseResponse($READ);

    if (!empty($ACTIVE_ARRAY)) {
        ?>
        <!DOCTYPE HTML>
        <html>
        <head>
           <link rel="stylesheet" href="./public/assets/styles/tailwind.min.css">
        </head>
        <body class="flex items-center justify-center min-h-screen bg-blue-50">
            <div class="text-center">
                <h4 class="text-2xl text-blue-600 font-bold">
                    This account has an active session. Contact admin or disconnect the device and try again.
                </h4>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = 'http://blackieNetworks.com/login';
                }, 10000);
            </script>
        </body>
        </html>
        <?php
    } else {
        $API->disconnect();
        ?>
        <!DOCTYPE HTML>
        <html>
        <head>
            <link rel="stylesheet" href="./public/assets/styles/tailwind.min.css">
        </head>
        <body class="bg-blue-50 flex items-center justify-center min-h-screen">
            <div id="details" class="bg-white p-10 rounded shadow-md text-center">
                <h4 class="text-xl font-bold mb-4">Details Verification Ongoing</h4>
                <p class="text-lg font-semibold mb-6"><em>Please Wait</em></p>
                <div class="loading-container flex justify-center items-center">
                    <div class="loading-bar w-48 h-5 bg-gray-200 relative overflow-hidden">
                        <div class="loading-animation w-full h-full bg-blue-600 absolute left-0 animate-loading"></div>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
                function doLogin() {
                    document.sendin.username.value = document.login.username.value;
                    document.sendin.password.value = hexMD5('\011\373\054\364\002\233\266\263\270\373\173\323\234\313\365\337\356');
                    document.sendin.submit();
                    return false;
                }
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
    }
} else {
    ?>
    <!DOCTYPE HTML>
    <html>
    <head>
    <link rel="stylesheet" href="./public/assets/styles/tailwind.min.css">
    </head>
    <body class="flex items-center justify-center min-h-screen bg-blue-50">
        <div class="text-center">
            <h3 class="text-xl text-blue-600 font-bold">
                System starting Error, please wait for a few minutes and try again.
            </h3>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = 'http://blackieNetworks.com/login';
            }, 10000);
        </script>
    </body>
    </html>
    <?php
}
?>
