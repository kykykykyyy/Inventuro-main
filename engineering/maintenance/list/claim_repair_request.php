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
    try {
        $stmtLog = $conn->prepare("
            INSERT INTO activity_log (user_id, timestamp, activity, ip_address) 
            VALUES (:user_id, NOW(), :activity, :ip_address)
        ");
        $stmtLog->bindParam(':user_id', $userId);
        $stmtLog->bindParam(':activity', $activity);
        $stmtLog->bindParam(':ip_address', $ipAddress);
        $stmtLog->execute();
    } catch (Exception $e) {
        // Log error if logging fails (Optional: create an error log table)
        error_log('Error logging activity: ' . $e->getMessage());
    }
}

// Ensure session is active and input is valid
$userId = $_SESSION['user_id'] ?? null;
$handledBy = $_SESSION['employee_id'] ?? null;
$ipAddress = $_SERVER['REMOTE_ADDR'];

$data = json_decode(file_get_contents('php://input'), true);
$maintenanceId = $data['maintenance_id'] ?? null;

if (empty($userId) || empty($handledBy) || empty($maintenanceId)) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid input data.']);
    exit();
}

try {
    // Begin transaction
    $conn->beginTransaction();

    // Check if maintenance ID exists
    $stmtCheckMaintenance = $conn->prepare("SELECT * FROM maintenance WHERE maintenance_id = ?");
    $stmtCheckMaintenance->execute([$maintenanceId]);
    if ($stmtCheckMaintenance->rowCount() == 0) {
        throw new Exception("Maintenance ID does not exist.");
    }

    // Update maintenance status
    $stmtRepairUpdate = $conn->prepare("
        UPDATE maintenance 
        SET maintenance_status = 'Assigned', handled_by = ?
        WHERE maintenance_id = ?
    ");
    $stmtRepairUpdate->execute([$handledBy, $maintenanceId]);

    // Check if update was successful
    if ($stmtRepairUpdate->rowCount() == 0) {
        throw new Exception("No rows updated in maintenance table.");
    }

    // Log the activity
    logActivity($conn, $userId, "Assigned maintenance (ID: $maintenanceId)", $ipAddress);

    // If maintenance exists, proceed with material request creation
    $stmtCheckMaterial = $conn->prepare("
        SELECT item.item_code, item.item_quantity
        FROM item
        LEFT JOIN maintenance ON maintenance.machine_parts_id = item.item_code
        WHERE maintenance.maintenance_id = ?
    ");
    $stmtCheckMaterial->execute([$maintenanceId]);
    $result = $stmtCheckMaterial->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $conn->commit();
        echo json_encode(['message' => 'Maintenance assigned; no materials needed.']);
    }
    else if ($result['item_quantity'] <= 0) {
        $conn->commit();
        echo json_encode(['message' => 'Maintenance assigned; no materials needed.']);
    }
    else {
        // Insert into material_request table
        $stmtMaterialUpdate = $conn->prepare("
        INSERT INTO material_request (maintenance_id, requested_by, status, approved_by, timestamp) 
        VALUES (?, ?, 'Not Started', NULL, NOW())
        ");
        $stmtMaterialUpdate->execute([$maintenanceId, $handledBy]);

        // Get the last inserted material_request_id
        $materialRequestId = $conn->lastInsertId();

        $stmtMaterialRequestItem = $conn->prepare("
                INSERT INTO material_request_items (material_request_id, item_id, quantity) 
                VALUES (?, ?, 1)
            ");

        $stmtMaterialRequestItem->execute([$materialRequestId, $result['item_code']]);

        // Commit transaction if all operations are successful
        $conn->commit();
        echo json_encode(['message' => 'Maintenance assigned and material request created successfully.']);
    }
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();

    // Log error
    error_log('Error processing maintenance assignment: ' . $e->getMessage());

    // Return error response
    http_response_code(500);
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
}