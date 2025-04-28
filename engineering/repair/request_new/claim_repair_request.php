<?php
session_start();
header('Content-Type: application/json');
include_once '../../../connect.php';

if (!$conn) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed.']);
    exit();
}

// Log activity function
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

// Validate session and input
$userId = $_SESSION['user_id'] ?? null;
$handledBy = $_SESSION['employee_id'] ?? null;
$ipAddress = $_SERVER['REMOTE_ADDR'];

$data = json_decode(file_get_contents('php://input'), true);
$repairRequestId = $data['repair_request_id'] ?? null;

if (empty($userId) || empty($handledBy) || empty($repairRequestId)) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid input data.']);
    exit();
}

try {
    $conn->beginTransaction();

    // Update repair table
    $stmtRepairUpdate = $conn->prepare("
        UPDATE repair 
        SET handled_by = ? 
        WHERE repair_request_id = ?
    ");
    $stmtRepairUpdate->execute([$handledBy, $repairRequestId]);

    // Check affected rows
    if ($stmtRepairUpdate->rowCount() == 0) {
        throw new Exception("No rows updated in repair table.");
    }

    // Update repair_request table
    $stmtRequestUpdate = $conn->prepare("
        UPDATE repair_request 
        SET status = 'Started'
        WHERE repair_request_id = ?
    ");
    $stmtRequestUpdate->execute([$repairRequestId]);

    if ($stmtRequestUpdate->rowCount() == 0) {
        throw new Exception("No rows updated in repair_request table.");
    }

    // Update repair_request table
    $stmtMachinePartUpdate = $conn->prepare("
        UPDATE machine_parts
        JOIN repair_request ON machine_parts.machine_id = repair_request.machine_id
        SET machine_parts.machine_parts_status = 'Under repair'
        WHERE repair_request.repair_request_id = ?");

    $stmtMachinePartUpdate->execute([$repairRequestId]);

    if ($stmtMachinePartUpdate->rowCount() == 0) {
        throw new Exception("No rows updated in machine parts table.");
    }

    $conn->commit();

    // Log activity
    $activity = "Claimed repair request ID: $repairRequestId";
    logActivity($conn, $userId, $activity, $ipAddress);

    echo json_encode(['success' => true, 'message' => 'Repair claimed successfully.']);
} catch (Exception $e) {
    $conn->rollBack();
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Error claiming repair: ' . $e->getMessage()]);
}
?>