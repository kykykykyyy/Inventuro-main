<?php
session_start();
include_once '../../../connect.php';  // Ensure this file sets up the database connection

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['repair_no']) && isset($_GET['machine_id'])) {
    $repairNo = $_GET['repair_no'];  // Get the repair number from the GET request
    $machine_id = $_GET['machine_id'];  // Get the machine_id from the GET request

    try {
        // Fetch the repair details from the database
        $stmt = $conn->prepare("
            SELECT *
            FROM repair
            WHERE repair_id = :repair_id
        ");
        $stmt->bindParam(':repair_id', $repairNo, PDO::PARAM_INT);
        $stmt->execute();
        
        $repairDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$repairDetails) {
            echo json_encode(['message' => 'Repair details not found']);
            exit();
        }

        // Fetch material request info
        $stmtMaterialRequest = $conn->prepare("
            SELECT *
            FROM material_request
            LEFT JOIN repair_request ON material_request.repair_request_id = repair_request.repair_request_id
            LEFT JOIN repair ON repair_request.repair_request_id = repair.repair_request_id
            WHERE repair.repair_id = :repair_id
        ");
        $stmtMaterialRequest->bindParam(':repair_id', $repairNo, PDO::PARAM_INT);
        $stmtMaterialRequest->execute();
        
        $materialRequestDetails = $stmtMaterialRequest->fetchAll(PDO::FETCH_ASSOC);
        if (empty($materialRequestDetails)) {
            echo json_encode(['message' => 'Material request details not found']);
            exit();
        }

        // Fetch machine parts affected
        $stmtMachineParts = $conn->prepare("
            SELECT *
            FROM machine_parts
            WHERE machine_id = :machine_id
            AND machine_parts_status = 'Under repair' AND disposable = 1
        ");
        $stmtMachineParts->bindParam(':machine_id', $machine_id, PDO::PARAM_INT);
        $stmtMachineParts->execute();
        
        $machineParts = $stmtMachineParts->fetchAll(PDO::FETCH_ASSOC);
        if (empty($machineParts)) {
            echo json_encode(['message' => 'No machine parts found under repair']);
            exit();
        }

        // Fetch material request items
        $stmtPartsItems = $conn->prepare("
            SELECT *
            FROM material_request
            JOIN material_request_items ON material_request.material_request_id = material_request_items.material_request_id
            JOIN machine_parts ON material_request_items.item_id = machine_parts.machine_part_item_code
            WHERE repair.repair_id = :repair_id
        ");
        $stmtPartsItems->bindParam(':repair_id', $repairNo, PDO::PARAM_INT);
        $stmtPartsItems->execute();
        
        $materialRequestItems = $stmtPartsItems->fetchAll(PDO::FETCH_ASSOC);
        if (empty($materialRequestItems)) {
            echo json_encode(['message' => 'No material request items found']);
            exit();
        }
        
        // Return all the fetched data
        echo json_encode([
            'repairDetails' => $repairDetails,
            'materialRequestDetails' => $materialRequestDetails,
            'machineParts' => $machineParts,
            'materialRequestItems' => $materialRequestItems
        ]);

    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
