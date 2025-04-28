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
    $reason_id = isset($_POST['reason_id']) ? $_POST['reason_id'] : '';

    // Basic validation
    if (empty($reason_id)) {  // Fix the variable name
        http_response_code(400);
        echo json_encode(['message' => 'Invalid reason ID.']);
        exit();
    }

    // Begin transaction
    $conn->beginTransaction();

    try {
        // Prepare the SQL statement to delete from the item_adjustment_reason table
        $stmtAdjustmentReason = $conn->prepare('
            DELETE FROM item_adjustment_reason
            WHERE reason_id = :reason_id');  // Corrected parameter

        // Bind parameters
        $stmtAdjustmentReason->bindParam(':reason_id', $reason_id, PDO::PARAM_INT);

        // Execute the delete query
        if (!$stmtAdjustmentReason->execute()) {
            // Log the SQL error
            $errorInfo = $stmtAdjustmentReason->errorInfo();
            throw new Exception("Failed to delete adjustment reason. SQLSTATE: {$errorInfo[0]}, Error: {$errorInfo[2]}");
        }        

        // Commit the transaction
        $conn->commit();
        http_response_code(200);
        echo json_encode(['message' => 'Reason deleted successfully!']);
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error deleting reason: ' . $e->getMessage()]);
    }

} else {
    // If not a POST request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed. Allowed methods: POST.']);
}
?>