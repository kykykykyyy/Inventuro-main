<?php
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
    $adjustment_id = isset($_POST['adjustment_id']) ? $_POST['adjustment_id'] : '';

    // Basic validation
    if (empty($adjustment_id)) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid adjustment ID.']);
        exit();
    }

    $conn->beginTransaction();

    try {
        // Prepare the SQL statement to delete from the item_adjustment_list table
        $stmtAdjustmentList = $conn->prepare('
            DELETE FROM item_adjustment_list
            WHERE adjustment_id = :adjustment_id');

        // Prepare the SQL statement to delete from the item_adjustment table
        $stmtAdjustment = $conn->prepare('
            DELETE FROM item_adjustment
            WHERE adjustment_id = :adjustment_id');

        // Bind parameters
        $stmtAdjustmentList->bindParam(':adjustment_id', $adjustment_id);
        $stmtAdjustment->bindParam(':adjustment_id', $adjustment_id);

        // Execute the delete queries
        if (!$stmtAdjustmentList->execute()) {
            throw new Exception("Failed to delete adjustment details.");
        }
        if (!$stmtAdjustment->execute()) {
            throw new Exception("Failed to delete adjustment.");
        }

        // Commit the transaction
        $conn->commit();
        http_response_code(200);
        echo json_encode(['message' => 'Adjustment deleted successfully!']);
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error deleting adjustment: ' . $e->getMessage()]);
    }

} else {
    // If not a POST request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed. Allowed methods: POST.']);
}