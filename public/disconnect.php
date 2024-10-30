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
$username =  isset($_SESSION['username']) ? $_SESSION['username'] : "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deviceIp = $_POST['device_ip'] ?? '';
    // Connect to MikroTik router via API
$API = new RouterosAPI();
$API->debug = false;

if ($API->connect($router_ip, $router_username, $router_password)) {
     // Remove the device from the active hotspot users
     $API->write('/ip/hotspot/active/remove', false);
     $API->write('=.id=' . $deviceId, true);
     $READ = $API->read(false);
     $ARRAY = $API->parseResponse($READ);

     // Handle success or error
     if (isset($ARRAY['!trap'])) {
         // Error in disconnecting the device
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
                 <strong class="font-bold">Error!</strong>
                 <span class="block sm:inline">There was an issue disconnecting the device. Please try again.</span>
                 <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                     <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 5.652a1 1 0 00-1.414 0L10 8.586 7.066 5.652a1 1 0 10-1.414 1.414L8.586 10l-2.934 2.934a1 1 0 101.414 1.414L10 11.414l2.934-2.934a1 1 0 101.414-1.414L11.414 10l2.934-2.934a1 1 0 000-1.414z"/></svg>
                 </span>
             </div>
         </div>
     </body>
     </html>';

         // Redirect after showing the error
         header("Refresh: 3; url=http://blackieNetworks.com/login");
         exit();
     } else {
         // Success in disconnecting the device
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
             <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                 <strong class="font-bold">Success!</strong>
                 <span class="block sm:inline">Device disconnected successfully.</span>
                 <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                     <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 5.652a1 1 0 00-1.414 0L10 8.586 7.066 5.652a1 1 0 10-1.414 1.414L8.586 10l-2.934 2.934a1 1 0 101.414 1.414L10 11.414l2.934-2.934a1 1 0 101.414-1.414L11.414 10l2.934-2.934a1 1 0 000-1.414z"/></svg>
                 </span>
             </div>
         </div>
     </body>
     </html>';

         // Redirect after showing the success message
         header("Refresh: 3; url=http://blackieNetworks.com/login");
         exit();
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
}
}
