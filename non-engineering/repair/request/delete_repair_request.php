<?php
session_start();
// Include your database connection file
include_once '../../../connect.php';

// Ensure the database connection is initialized
if (!$conn) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed.']);
    exit();
}

// Function to log activity
function logActivity($conn, $userId, $activity, $ipAddress) {
    $stmtLog = $conn->prepare("
        INSERT INTO activity_log (user_id, timestamp, activity, ip_address) 
        VALUES (:user_id, NOW(), :activity, :ip_address)
    ");
    $stmtLog->bindParam(':user_id', $userId);
    $stmtLog->bindParam(':activity', $activity);
    $stmtLog->bindParam(':ip_address', $ipAddress);
    $stmtLog->execute();
}

// Retrieve user ID and IP address (adjust based on your system's user tracking)
$userId = $_SESSION['user_id'];
$ipAddress = $_SERVER['REMOTE_ADDR'];

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the posted data (in this case, we need the repair request ID)
    $repairRequestId = isset($_POST['repair_request_id']) ? $_POST['repair_request_id'] : null;

    // Basic validation
    if (empty($repairRequestId)) {
        http_response_code(400);
        echo json_encode(['message' => 'Repair Request ID is required.']);
        exit();
    }

    // Start a transaction to ensure the deletion succeeds
    $conn->beginTransaction();

    try {
        // Prepare the SQL statement to delete the record from the `repair_request` table
        $stmtRepairRequest = $conn->prepare("
            DELETE FROM repair_request 
            WHERE repair_request_id = :repair_request_id
        ");
        $stmtRepairRequest->bindParam(':repair_request_id', $repairRequestId);

        // Execute the delete query for the `repair_request` table
        if ($stmtRepairRequest->execute()) {
            // Commit the transaction
            $conn->commit();

            // Log success
            $activity = "Successfully deleted repair request ID: $repairRequestId";
            logActivity($conn, $userId, $activity, $ipAddress);

            http_response_code(200);
            echo json_encode(['message' => 'Repair request deleted successfully.']);
        } else {
            throw new Exception("Failed to delete repair request.");
        }
        
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollBack();

        // Log failure
        $activity = "Failed to delete repair request ID: $repairRequestId: " . $e->getMessage();
        logActivity($conn, $userId, $activity, $ipAddress);

        http_response_code(500);
        echo json_encode(['message' => 'Error deleting repair request: ' . $e->getMessage()]);
    }

} else {
    // If not a POST request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
