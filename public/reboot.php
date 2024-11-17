<?php
// Include the RouterOS PHP API library
require('routeros_api.class.php');

// Function to log messages
function logMessage(string $message): void
{
    $logFile = __DIR__ . '/mikrotik_api.log';
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($logFile, "{$timestamp} {$message}\n", FILE_APPEND);
}

// Router connection details
$routerIp = 'app.vexifi.com:2159'; // Replace with your router's IP
$username = 'api';        // Replace with your API username
$password = 'enock';     // Replace with your API password
             // Default API port

// Create RouterOS API instance
$api = new RouterosAPI();
$api->debug = true;

try {
    // Connect to the router
    if ($api->connect($routerIp, $username, $password, $port)) {
        logMessage("Connected to MikroTik router at {$routerIp}.");

        // Query all user profiles in User Manager
        $api->write('/user-manager/profile/print', true);;
        $profiles = $api->read();

        // Display the results
        echo "<h1>User Manager Profiles</h1>";
        echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Validity</th><th>Owner</th></tr>";
        
        foreach ($profiles as $profile) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($profile['.id'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($profile['name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($profile['validity'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($profile['owner'] ?? '') . "</td>";
            echo "</tr>";
        }

        echo "</table>";

        // Log success
        logMessage("Queried " . count($profiles) . " user profiles successfully.");
    } else {
        throw new Exception("Failed to connect to MikroTik router.");
    }
} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage());
    echo "Error: " . htmlspecialchars($e->getMessage());
} finally {
    // Disconnect from the router
    $api->disconnect();
    logMessage("Disconnected from MikroTik router.");
}
?>
