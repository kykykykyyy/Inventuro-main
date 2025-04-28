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
    $reason_name = isset($_POST['reason_name']) ? $_POST['reason_name'] : '';

    // Basic validation: Ensure the reason name is not empty
    if (empty(trim($reason_name))) {
        http_response_code(400);
        echo json_encode(['message' => 'Input a reason name.']);
        exit();
    }

    // Start a transaction
    $conn->beginTransaction();

    try {
        // Prepare the SQL statement to insert into the item_adjustment_reason table
        $stmtAddReason = $conn->prepare("
            INSERT INTO item_adjustment_reason (reason)
            VALUES (:reason)
        ");

        // Bind the parameter with explicit type
        $stmtAddReason->bindParam(':reason', $reason_name, PDO::PARAM_STR);

        // Execute the query and check for success
        if (!$stmtAddReason->execute()) {
            // Log SQL error for debugging
            $errorInfo = $stmtAddReason->errorInfo();
            throw new Exception("Failed to add reason. SQLSTATE: {$errorInfo[0]}, Error: {$errorInfo[2]}");
        }

        // Commit the transaction
        $conn->commit();
        http_response_code(200);
        echo json_encode(['message' => 'Reason added successfully!']);
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error adding reason: ' . $e->getMessage()]);
    }
    
} else {
    // If not a POST request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
?>
