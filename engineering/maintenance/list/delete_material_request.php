<?php
session_start();
header('Content-Type: application/json');
include_once '../../../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $materialRequestId = $data['material_request_id'] ?? null;

    if (empty($materialRequestId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Material request ID is required.']);
        exit();
    }

    try {
        $conn->beginTransaction();

        // Update related tables first
        // Set handled_by to NULL in repair table
        $stmtRepairUpdate = $conn->prepare("
            UPDATE maintenance 
            SET maintenance_status = 'Scheduled', handled_by = NULL 
            WHERE maintenance_id = (
                SELECT maintenance_id FROM material_request WHERE material_request_id = ?
            )
        ");
        
        $stmtRepairUpdate->execute([$materialRequestId]);

        // Delete the material request
        $stmtDelete = $conn->prepare("
            DELETE FROM material_request 
            WHERE material_request_id = ?
        ");
        $stmtDelete->execute([$materialRequestId]);

        $conn->commit();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . htmlspecialchars($e->getMessage())]);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
?>