<?php
include './public/header.php';

// Assuming you have already established a MongoDB connection
$mongoClient = new MongoDB\Client("mongodb+srv://myAtlasDBUser:Enockay23@myatlasclusteredu.bfx6ekr.mongodb.net/");
$database = $mongoClient->selectDatabase('Blackie-Networks');
$collection = $database->selectCollection('purchases');

function isValidJson($data)
{
    return json_decode($data) !== null && json_last_error() === JSON_ERROR_NONE;
}

function findTransactionById($collection, $transactionId)
{
    return $collection->findOne(['transactionId' => $transactionId]);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Read the raw input data
        $postData = file_get_contents("php://input");

        // Check if the input data is valid JSON
        if (!isValidJson($postData)) {
            throw new InvalidArgumentException("Invalid JSON data.");
        }

        // Decode JSON data
        $data = json_decode($postData);

        // Check if the transaction ID exists in the MongoDB collection
        $document = findTransactionById($collection, $data->transactionId);

        if ($document) {
            // Transaction ID exists in the database
            header('Content-Type: application/json');
            echo json_encode(['ResultCode' => '0']);
            exit();
        } else {
            // Transaction ID not found in the database
            header('Content-Type: application/json');
            echo json_encode(['ResultCode' => '1']);
            exit();
        }
    } else {
        // Invalid request method
        header('Content-Type: application/json');
        echo json_encode(['ResultCode' => '2']);
        exit();
    }
} catch (Exception $e) {
    // Log the exception for debugging
    error_log("Exception: " . $e->getMessage());

    // Handle the exception gracefully
    header('Content-Type: application/json');
    echo json_encode(['ResultCode' => '4', 'ErrorMessage' => 'An error occurred.']);
    exit();
}
?>
