<?php
session_start();
header('Content-Type: application/json');
include_once '../../../connect.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    // Get JSON data from request body
    $data = json_decode(file_get_contents('php://input'), true);

    $repairRequestId = $data['repair_request_id'] ?? null;
    $requestedBy = $data['requested_by'] ?? null;
    $items = $data['items'] ?? [];

    if (empty($repairRequestId) || empty($items)) {
        http_response_code(400);
        echo json_encode(['message' => 'Repair request ID and items are required.']);
        exit();
    }

    // Check if the requestedBy exists in the employee table
    $checkEmployeeStmt = $conn->prepare("SELECT COUNT(*) FROM employee WHERE employee_id = ?");
    $checkEmployeeStmt->execute([$requestedBy]);
    $employeeExists = $checkEmployeeStmt->fetchColumn();

    if (!$employeeExists) {
        http_response_code(400);
        echo json_encode(['message' => 'Requested by ID does not exist in employee table.']);
        exit();
    }

    try {
        $conn->beginTransaction();

        // Insert into material_request table
        $stmt = $conn->prepare("
            INSERT INTO material_request (repair_request_id, requested_by, status) 
            VALUES (?, ?, 'Not Started')
        ");
        $stmt->bindParam(1, $repairRequestId, PDO::PARAM_INT);
        $stmt->bindParam(2, $requestedBy, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert material request.");
        }

        // Get the last inserted material_request_id
        $materialRequestId = $conn->lastInsertId();

        // Insert into material_request_items table
        $stmtItem = $conn->prepare("
            INSERT INTO material_request_items (material_request_id, item_id, quantity) 
            VALUES (?, ?, ?)
        ");
        foreach ($items as $item) {
            if (!isset($item['id'], $item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                continue; // Skip if invalid item data
            }
            $stmtItem->execute([$materialRequestId, $item['id'], $item['quantity']]);
        }

        // Update the repair table
        $stmtRepairUpdate = $conn->prepare("
            UPDATE repair 
            SET handled_by = ? 
            WHERE repair_request_id = ?
        ");
        $stmtRepairUpdate->bindParam(1, $requestedBy, PDO::PARAM_STR);
        $stmtRepairUpdate->bindParam(2, $repairRequestId, PDO::PARAM_INT);

        if (!$stmtRepairUpdate->execute()) {
            throw new Exception("Failed to update repair details.");
        }

        // Update the repair request table
        $stmtRepairUpdate = $conn->prepare("
            UPDATE repair_request 
            SET status = 'Started'
            WHERE repair_request_id = ?
        ");

        $stmtRepairUpdate->bindParam(1, $repairRequestId, PDO::PARAM_INT);

        if (!$stmtRepairUpdate->execute()) {
            throw new Exception("Failed to update repair request status.");
        }
        // Commit transaction
        $conn->commit();

        // Log activity
        $activity = "Processed order for repair request ID: $repairRequestId";
        logActivity($conn, $userId, $activity, $ipAddress);

        echo json_encode(['success' => true, 'message' => 'Order processed successfully.']);
    } catch (Exception $e) {
        $conn->rollBack();
        $activity = "Failed to process order for repair request ID: $repairRequestId";
        logActivity($conn, $userId, $activity, $ipAddress);
        
        http_response_code(500);
        echo json_encode(['message' => 'Error processing order: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
?>