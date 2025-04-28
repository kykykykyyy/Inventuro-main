<?php
session_start();
include_once '../../../connect.php';

if (!$conn) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed.']);
    exit();
}

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

$userId = $_SESSION['user_id'];
$ipAddress = $_SERVER['REMOTE_ADDR'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $repairRequestId = $_POST['repair_request_id'];
    $details = $_POST['details'];

    if (empty($repairRequestId) || empty($details)) {
        http_response_code(400);
        echo json_encode(['message' => 'All fields are required.']);
        exit();
    }

    try {
        $conn->beginTransaction();

        $stmtUpdate = $conn->prepare("
            UPDATE repair_request
            SET details = :details
            WHERE repair_request_id = :repair_request_id
        ");
        $stmtUpdate->bindParam(':details', $details);
        $stmtUpdate->bindParam(':repair_request_id', $repairRequestId);

        if ($stmtUpdate->execute()) {
            $conn->commit();

            $activity = "Updated repair request ID: $repairRequestId";
            logActivity($conn, $userId, $activity, $ipAddress);

            http_response_code(200);
            echo json_encode(['message' => 'Repair request updated successfully.']);
        } else {
            throw new Exception("Failed to update repair request.");
        }
        
    } catch (Exception $e) {
        $conn->rollBack();

        $activity = "Failed to update repair request ID: $repairRequestId - " . $e->getMessage();
        logActivity($conn, $userId, $activity, $ipAddress);

        http_response_code(500);
        echo json_encode(['message' => 'Error updating repair request: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
