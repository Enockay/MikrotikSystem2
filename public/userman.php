<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MikroTik User Profile Update</title>
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

    <?php
    require "./header.php";
    require './routeros_api.class.php';

    $link_login = "http://blackieNetworks.com/login";
    $link_login_only = "http://blackieNetworks.com/login";
    $linkorig = "https://www.google.com";

    $phoneNumber = isset($_SESSION['phoneNumber']) ? $_SESSION['phoneNumber'] : "";
    $identity =  isset($_SESSION['routername'])? $_SESSION['routername']:"";
    $amount = isset($_SESSION['amount']) ? $_SESSION['amount'] : "";
    $TransactionCode =  isset($_SESSION['TransactionCode']) ? $_SESSION['TransactionCode'] : "";
    $group = "primary";

    $routers = [
        'piusMikrotik' => ['ip' => 'app.vexifi.com:558', 'username' => 'api', 'password' => 'enock'],
        'enockMikrotik' => ['ip' => 'app.vexifi.com:2159', 'username' => 'api', 'password' => 'enock'],
    ];


    if (!array_key_exists($identity, $routers)) {
        $errorMsg = "Error: Unknown Router Identity - {$identity}";
        logMessage($errorMsg);
        sendJsUpdate($errorMsg);
        exit();
    }

    $routerDetails = $routers[$identity];
    $API = new RouterosAPI();

    $profiles = [
        ['id' => '*5', 'name' => '1week', 'price' => 180],
        ['id' => '*6', 'name' => '2days', 'price' => 60],
        ['id' => '*7', 'name' => '5days', 'price' => 140],
        ['id' => '*9', 'name' => '2weeks', 'price' => 300],
        ['id' => '*A', 'name' => '21days', 'price' => 400],
        ['id' => '*B', 'name' => '28days', 'price' => 450],
        ['id' => '*C', 'name' => '1day', 'price' => 35],
        ['id' => '*D', 'name' => '12hours', 'price' => 25],
        ['id' => '*E', 'name' => '6hours', 'price' => 20],
        ['id' => '*F', 'name' => '1hour', 'price' => 10],
        ['id' => '*11', 'name' => '30min', 'price' => 5],
        ['id' => '*12', 'name' => '2min', 'price' => 100],
        ['id' => '*13', 'name' => '31day', 'price' => 500],
    ];

    logMessage("searching for a matching profile amount: {$amount}");
    $matched_profile = null;

    foreach ($profiles as $profile) {
        if ($profile['price'] == $amount) {
            $matched_profile = $profile['name'];
            break;
        }
    }

    if ($API->connect($routerDetails['ip'], $routerDetails['username'], $routerDetails['password'])) {
        sendJsUpdate("Connected to MikroTik API successfully.");
        logMessage("Connected to MikroTik API: {$routerDetails['ip']}");

        try {
            sendJsUpdate("Checking if user exists...");
            $API->write('/user-manager/user/print', false);
            $API->write('?name=' . $phoneNumber, true);
            $userResponse = $API->read();

            if (!empty($userResponse) && isset($userResponse[0]['.id'])) {
                $userId = $userResponse[0]['.id'];
                sendJsUpdate("User exists with ID: {$userId}");
                logMessage("User exists: ID {$userId}");
            } else {
                sendJsUpdate("User not found. Creating user...");
                $API->write('/user-manager/user/add', false);
                $API->write('=name=' . $phoneNumber, false);
                $API->write('=password=' . $TransactionCode, false);
                $API->write('=group='.$group,false);
                $API->write('=shared-users=2', true);
                logMessage("Created user: {$phoneNumber}");
            }

            sendJsUpdate("Searching for profile ID...");
            $API->write('/user-manager/profile/print', false);
            $API->write('?name=' . $matched_profile, true);
            $profileResponse = $API->read();

            if (!empty($profileResponse) && isset($profileResponse[0]['.id'])) {
                $profileId = $profileResponse[0]['.id'];
                sendJsUpdate("Profile found with ID: {$profileId}");
                logMessage("Profile found: ID {$profileId}");

                sendJsUpdate("Checking if user is assigned to any profile...");
                // Fetch existing profiles for the user
                $API->write('/user-manager/user-profile/print', false);
                $API->write('?user=' . $phoneNumber, true);
                $userProfiles = $API->read();

                if (!empty($userProfiles)) {
                    foreach ($userProfiles as $userProfile) {
                        if (isset($userProfile['.id'])) {
                            // Remove each profile associated with the user
                            $API->write('/user-manager/user-profile/remove', false);
                            $API->write('=.id=' . $userProfile['.id'], true);
                            $API->read();
                            logMessage("Removed existing profile with ID {$userProfile['.id']} for user {$phoneNumber}");
                            sendJsUpdate("Removed existing profile: {$userProfile['.id']}");
                        }
                    }
                } else {
                    sendJsUpdate("No existing profiles found for user.");
                    logMessage("No existing profiles for user {$phoneNumber}");
                }
                $API->write('/user-manager/user-profile/add', false);
                $API->write('=user=' . $phoneNumber, false);
                $API->write('=profile=' . $profileId, true);
                $API->read();
    ?>
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
                sendJsUpdate("Profile assigned successfully.");
                logMessage("Assigned profile ID {$profileId} to user {$phoneNumber}");
            } else {
                sendJsUpdate("Profile not found: {$matched_profile}");
                logMessage("Profile not found: {$matched_profile}");
            }
        } catch (Exception $e) {
            sendJsUpdate("Exception: " . $e->getMessage());
            logMessage("Exception: {$e->getMessage()}");
        } finally {

            $API->disconnect();
            sendJsUpdate("Disconnected from MikroTik API.");
        }
    } else {
        sendJsUpdate("Failed to connect to MikroTik API.");
        logMessage("Failed to connect to MikroTik API.");
    }
    ?>
</body>

</html>