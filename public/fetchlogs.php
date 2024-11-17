<?php

// File: fetch_logs.php

header('Content-Type: text/plain');

$logFile = __DIR__ . '/mikrotik_api.log';

// Check if the log file exists
if (file_exists($logFile)) {
    // Read and output the log file contents
    echo file_get_contents($logFile);
} else {
    // If the log file doesn't exist, return an appropriate message
    echo "Log file not found.";
}
