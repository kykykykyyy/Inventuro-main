<?php
// update-user.php

// Include your database connection file
include_once '../../../connect.php';

// Ensure the database connection is initialized
if (!$conn) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed.']);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the posted data
    $item_code = isset($_POST['item_code']) ? $_POST['item_code'] : '';

    // Basic validation
    if (empty($item_code)) {
        http_response_code(400);
        echo json_encode(['message' => 'Make sure to fill all fields.']);
        exit();
    }

    // Start a transaction (optional but recommended to ensure both updates succeed or fail together)
    $conn->beginTransaction();

    try {
        // Prepare the SQL statement to update the `employee` table
        $stmtItem = $conn->prepare("
            DELETE FROM item 
            WHERE item_code = :item_code
        ");

        // Bind parameters to the item table update
        $stmtItem->bindParam(':item_code', $item_code);

        // Execute the item update query
        if (!$stmtItem->execute()) {
            throw new Exception("Failed to delete item.");
        }

        // Commit the transaction
        $conn->commit();
        http_response_code(200);
        echo json_encode(['message' => 'Item deleted successfully!']);
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error deleting item: ' . $e->getMessage()]);
    }

} else {
    // If not a POST request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
