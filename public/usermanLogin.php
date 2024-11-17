<?php
require "./header.php";
require './routeros_api.class.php';

// Configuration
$link_login = "http://blackieNetworks.com/login";
$link_login_only = "http://blackieNetworks.com/login";
$linkorig = "https://www.google.com";
$phoneNumber = isset($_SESSION['phoneNumber']) ? $_SESSION['phoneNumber'] : "";
$identity =  isset($_SESSION['routername']) ? $_SESSION['routername'] : "";
$amount = $_SESSION['amount'] ?? "10";
$TransactionCode =  isset($_SESSION['TransactionCode']) ? $_SESSION['TransactionCode'] : "";
$group = "primary";
$remainingTime = isset($_SESSION['remainingTime']) ? $_SESSION['remainingTime'] :0;

$routers = [
    'piusMikrotik' => ['ip' => 'app.vexifi.com:558', 'username' => 'api', 'password' => 'enock'],
    'enockMikrotik' => ['ip' => 'app.vexifi.com:2159', 'username' => 'api', 'password' => 'enock'],
];

// Define profiles with time ranges in seconds
$profiles = [
    ['id' => '*5', 'name' => '1week', 'price' => 180, 'min_seconds' => 604800, 'max_seconds' => 604800],     // Exactly 7 days
    ['id' => '*6', 'name' => '2days', 'price' => 60, 'min_seconds' => 172800, 'max_seconds' => 431999],      // 2 days to less than 5 days
    ['id' => '*7', 'name' => '5days', 'price' => 140, 'min_seconds' => 432000, 'max_seconds' => 604799],     // 5 days to less than 7 days
    ['id' => '*9', 'name' => '2weeks', 'price' => 300, 'min_seconds' => 1209600, 'max_seconds' => 1209600],  // Exactly 2 weeks
    ['id' => '*A', 'name' => '21days', 'price' => 400, 'min_seconds' => 1814400, 'max_seconds' => 2419199],  // 21 days to less than 28 days
    ['id' => '*B', 'name' => '28days', 'price' => 450, 'min_seconds' => 2419200, 'max_seconds' => 2678399],  // 28 days to less than 31 days
    ['id' => '*C', 'name' => '1day', 'price' => 35, 'min_seconds' => 86400, 'max_seconds' => 172799],        // 1 day to less than 2 days
    ['id' => '*D', 'name' => '12hours', 'price' => 25, 'min_seconds' => 43200, 'max_seconds' => 86399],      // 12 hours to less than 1 day
    ['id' => '*E', 'name' => '6hours', 'price' => 20, 'min_seconds' => 21600, 'max_seconds' => 43199],       // 6 hours to less than 12 hours
    ['id' => '*F', 'name' => '1hour', 'price' => 10, 'min_seconds' => 3600, 'max_seconds' => 21599],         // 1 hour to less than 6 hours
    ['id' => '*11', 'name' => '30min', 'price' => 5, 'min_seconds' => 1800, 'max_seconds' => 3599],          // 30 minutes to less than 1 hour
    ['id' => '*12', 'name' => '2min', 'price' => 100, 'min_seconds' => 120, 'max_seconds' => 1799],          // 2 minutes to less than 30 minutes
    ['id' => '*13', 'name' => '31days', 'price' => 500, 'min_seconds' => 2678400, 'max_seconds' => INF],     // 31 days and beyond
];


logMessage("Searching for a matching profile with remaining time: {$remainingTime} seconds");

// Filter to find a profile where the remaining time is within its defined range
$matchedProfile = array_filter($profiles, fn($profile) => 
    $remainingTime >= $profile['min_seconds'] && $remainingTime <= $profile['max_seconds']
);

// Extract profile details if a match is found
$Profile = reset($matchedProfile)['name'] ?? null;
$matchedProfileID = reset($matchedProfile)['id'] ?? null;

if ($Profile && $matchedProfileID) {
    logMessage("Matched Profile: {$Profile} with ID: {$matchedProfileID}");
} else {
    logMessage("No profile matched for the remaining time: {$remainingTime}");
}


// Validate router identity
if (!isset($routers[$identity])) {
    $errorMsg = "Error: Unknown Router Identity - {$identity}";
    logMessage($errorMsg);
    sendJsUpdate($errorMsg);
    exit();
}

$routerDetails = $routers[$identity];
$API = new RouterosAPI();


// Connect to MikroTik API
if (!$API->connect($routerDetails['ip'], $routerDetails['username'], $routerDetails['password'])) {
    sendJsUpdate("Failed to connect to MikroTik API.");
    exit();
}

sendJsUpdate("Connected to MikroTik API successfully.");
logMessage("Connected to MikroTik API: {$routerDetails['ip']}");

try {
    // Check if the user exists
    $API->write('/user-manager/user/print', false);
    $API->write('?name=' . $phoneNumber, true);
    $userResponse = $API->read();

    if (!empty($userResponse) && isset($userResponse[0]['.id'])) {
        $userId = $userResponse[0]['.id'];
        sendJsUpdate("User exists with ID: {$userId}");
        logMessage("User exists with ID: {$userId}");

        // Manage user profiles
        manageUserProfiles($API, $phoneNumber, $TransactionCode);
    } else {
        // Create a new user if none exists
        createUser($API, $phoneNumber, $TransactionCode);
    }
} catch (Exception $e) {
    sendJsUpdate("Exception: " . $e->getMessage());
    logMessage("Error occurred: " . $e->getMessage());
} finally {
    $API->disconnect();
    sendJsUpdate("Disconnected from MikroTik API.");
}

/**
 * Manage active sessions and profiles for an existing user
 */
function manageUserProfiles($API, $phoneNumber, $TransactionCode) {
    // Fetch active sessions for the user
    $API->write('/user-manager/session/print', false);
    $API->write('?user=' . $phoneNumber, true);
    $activeSessions = $API->read();

    $sessionCount = count($activeSessions);

    if ($sessionCount >= 2) {
        // Sort sessions by start time (newest first)
        usort($activeSessions, function ($a, $b) {
            return strtotime($b['started']) <=> strtotime($a['started']);
        });

        // Remove the most recent session
        $mostRecentSession = $activeSessions[0];
        $API->write('/user-manager/session/remove', false);
        $API->write('=.id=' . $mostRecentSession['.id'], true);
        $API->read();

        sendJsUpdate("Removed most recent session with ID: {$mostRecentSession['.id']}");
        logMessage("Removed most recent session for user: {$phoneNumber} with ID: {$mostRecentSession['.id']}");
    }

    // Automatically submit the login form
    autoSubmitLoginForm($phoneNumber, $TransactionCode);
}

/**
 * Create a new user and assign a profile
 */
function createUser($API, $phoneNumber, $TransactionCode) {
    global $matchedProfileID;

    sendJsUpdate("User not found. Creating user...");
    logMessage("User not found. Creating user...");

    // Add the user
    $API->write('/user-manager/user/add', false);
    $API->write('=name=' . $phoneNumber, false);
    $API->write('=password=' . $TransactionCode, false);
    $API->write('=group=primary', false);
    $API->write('=shared-users=2', true);
    $API->read();

    sendJsUpdate("User created in primary group.");
    logMessage("User created in primary group.");

    // Remove any existing profiles for the user
    $API->write('/user-manager/user-profile/print', false);
    $API->write('?user=' . $phoneNumber, true);
    $userProfiles = $API->read();

    if (!empty($userProfiles)) {
        foreach ($userProfiles as $userProfile) {
            if (isset($userProfile['.id'])) {
                $API->write('/user-manager/user-profile/remove', false);
                $API->write('=.id=' . $userProfile['.id'], true);
                $API->read();

                sendJsUpdate("Removed existing profile with ID: {$userProfile['.id']}");
                logMessage("Removed existing profile with ID {$userProfile['.id']} for user {$phoneNumber}");
            }
        }
    } else {
        sendJsUpdate("No existing profiles found for user.");
        logMessage("No existing profiles found for user: {$phoneNumber}");
    }

    // Assign a new profile to the user
    $API->write('/user-manager/user-profile/add', false);
    $API->write('=user=' . $phoneNumber, false);
    $API->write('=profile=' . $matchedProfileID, true);
    $API->read();

    sendJsUpdate("Assigned profile to new user: {$phoneNumber}");
    logMessage("Assigned profile to new user: {$phoneNumber}");

    // Automatically submit the login form
    autoSubmitLoginForm($phoneNumber, $TransactionCode);
}

/**
 * Function to auto-submit login form
 */
function autoSubmitLoginForm($phoneNumber, $TransactionCode)
{
    global $link_login_only, $linkorig;
    ?>
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account creation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
   </head>

   <body class="bg-gray-100 min-h-screen flex items-center justify-center">
     <div id="details" class="bg-white p-10 rounded shadow-md text-center">
            <h4 class="text-2xl font-bold mb-4">Creating and activating Account</h4>
            <p class="text-lg font-semibold mb-6"><em>Please Wait...</em></p>
            <div class="loading-container flex justify-center items-center">
                <div class="loading-bar w-48 h-5 bg-gray-200 relative overflow-hidden">
                    <div class="loading-animation w-full h-full bg-blue-600 absolute left-0 animate-loading"></div>
                </div>
            </div>
        </div>
   </body>
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
        <input name="password" type="hidden" value="<?php echo $TransactionCode; ?>" />
    </form>
    <?php
}

?>
