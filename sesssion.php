<?php
require './public/header.php';
require './public/config.php';

// Check for logout request from MikroTik router with only MAC address
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["logout_mac"])) {
    $mac_address = $_POST["mac_address"];
    // Search through all sessions in the database
    $cursor = $sessionCollection->find(['mac_addresses' => $mac_address]);

    foreach ($cursor as $document) {
        // Remove the MAC address from the session in the database
        $sessionCollection->updateOne(
            ['_id' => $document['_id']],
            ['$pull' => ['mac_addresses' => $mac_address]]
        );
        echo "Logout successful!";
    }
}
