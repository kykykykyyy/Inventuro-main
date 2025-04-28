<?php
session_start();
header('Content-Type: application/json');
include_once '../../../connect.php';

// Ensure the database connection exists
if (!$conn) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed.']);
    exit();
}

// Get the input data from the request body
$data = json_decode(file_get_contents('php://input'), true);
$maintenanceId = $data['maintenance_id'] ?? null;
$handledBy = $data['handled_by'] ?? null;

// Validate input
if (empty($maintenanceId) || empty($handledBy)) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid input data.']);
    exit();
}

try {
    // Begin transaction
    $conn->beginTransaction();

    // Update maintenance table
    $stmtUpdateMaintenance = $conn->prepare("
        UPDATE maintenance 
        SET maintenance_status = 'Done', maintenance_completed_date = NOW(), handled_by = ? 
        WHERE maintenance_id = ?
    ");
    $stmtUpdateMaintenance->execute([$handledBy, $maintenanceId]);

    // Check if rows were updated
    if ($stmtUpdateMaintenance->rowCount() == 0) {
        throw new Exception("Maintenance task update failed or already completed.");
    }

    // Commit the transaction
    $conn->commit();

    // Return success message
    echo json_encode(['message' => 'Success']);
} catch (Exception $e) {
    // Rollback the transaction in case of error
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
}
?>