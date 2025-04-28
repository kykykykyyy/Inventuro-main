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
    $reorder_point = isset($_POST['reorder_point']) ? $_POST['reorder_point'] : '';

    // Basic validation
    if (empty($item_code) || empty($reorder_point)) {
        http_response_code(400);
        echo json_encode(['message' => 'Make sure to fill the reorder point.']);
        exit();
    }

    // Start a transaction (optional but recommended to ensure both updates succeed or fail together)
    $conn->beginTransaction();

    try {
        // Prepare the SQL statement to update the `employee` table
        $stmtItem = $conn->prepare("
            UPDATE item 
            SET reorder_point = :reorder_point
            WHERE item_code = :item_code
        ");

        // Bind parameters to the item table update
        $stmtItem->bindParam(':reorder_point', $reorder_point);
        $stmtItem->bindParam(':item_code', $item_code);

        // Execute the employee update query
        if (!$stmtItem->execute()) {
            throw new Exception("Failed to update item's reorder point.");
        }

        // Commit the transaction
        $conn->commit();
        http_response_code(200);
        echo json_encode(['message' => 'Item reorder point updated successfully!']);
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error updating reorder point: ' . $e->getMessage()]);
    }

} else {
    // If not a POST request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
