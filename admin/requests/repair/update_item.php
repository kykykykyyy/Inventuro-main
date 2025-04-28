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
    $item_name = isset($_POST['item_name']) ? $_POST['item_name'] : '';
    $size_per_unit = isset($_POST['size_per_unit']) ? $_POST['size_per_unit'] : '';
    $unit = isset($_POST['unit']) ? $_POST['unit'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $reorder_point = isset($_POST['reorder_point']) ? $_POST['reorder_point'] : '';
    $item_code = isset($_POST['item_code']) ? $_POST['item_code'] : '';

    $image = isset($_POST['image']) ? $_POST['image'] : null;
    $itemImageBinary = base64_decode($image);
            

    // Basic validation
    if (empty($item_code) || empty($item_name) || empty($size_per_unit) || empty($unit) || empty($reorder_point) || empty($description)) {
        http_response_code(400);
        echo json_encode(['message' => 'Make sure to fill all fields.']);
        exit();
    }

    // Start a transaction (optional but recommended to ensure both updates succeed or fail together)
    $conn->beginTransaction();

    try {
        // Prepare the SQL statement to update the `employee` table
        $stmtItem = $conn->prepare("
            UPDATE item 
            SET 
                item_name = :item_name,
                size_per_unit = :size_per_unit,
                unit = :unit,
                reorder_point = :reorder_point,
                image = :image,
                description = :description
            WHERE item_code = :item_code
        ");

        // Bind parameters to the item table update
        $stmtItem->bindParam(':item_name', $item_name);
        $stmtItem->bindParam(':size_per_unit', $size_per_unit);
        $stmtItem->bindParam(':unit', $unit);
        $stmtItem->bindParam(':reorder_point', $reorder_point);
        $stmtItem->bindParam(':image', $itemImageBinary, PDO::PARAM_LOB); // Binary data
        $stmtItem->bindParam(':description', $description);
        $stmtItem->bindParam(':item_code', $item_code);

        // Execute the item update query
        if (!$stmtItem->execute()) {
            throw new Exception("Failed to update item.");
        }

        // Commit the transaction
        $conn->commit();
        http_response_code(200);
        echo json_encode(['message' => 'ITem updated successfully!']);
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error updating item: ' . $e->getMessage()]);
    }

} else {
    // If not a POST request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
