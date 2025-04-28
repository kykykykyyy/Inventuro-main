<?php
session_start();
header('Content-Type: application/json');
include_once '../../../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $repairId = $data['repair_id'] ?? null;

    if (empty($repairId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Repair ID is required.']);
        exit();
    }

    try {
        $conn->beginTransaction();

        // Update related tables first
        // Set handled_by to NULL in repair table
        $stmtRepairUpdate = $conn->prepare("
            UPDATE repair 
            SET handled_by = NULL 
            WHERE repair_request_id = (
                SELECT repair_request_id FROM material_request WHERE material_request_id = ?
            )
        ");
        
        $stmtRepairUpdate->execute([$materialRequestId]);

        // Update status to 'Not Started' in repair_request table
        $stmtRepairRequestUpdate = $conn->prepare("
            UPDATE repair_request 
            SET status = 'Not Started' 
            WHERE repair_request_id = (
                SELECT repair_request_id FROM material_request WHERE material_request_id = ?
            )
        ");
        $stmtRepairRequestUpdate->execute([$materialRequestId]);

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