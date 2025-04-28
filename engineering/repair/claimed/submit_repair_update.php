<?php
session_start();
include_once '../../../connect.php'; // Ensure this file sets up the database connection

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
    // Decode JSON payload
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data) {
        $repairNo = isset($data['repair_no']) ? $data['repair_no'] : null;
        $machineName = isset($data['machine_name']) ? $data['machine_name'] : null;
        $machineSerialNumber = isset($data['machine_serial_number']) ? $data['machine_serial_number'] : null;
        $mainDescription = isset($data['main_description']) ? $data['main_description'] : null;
        $selectedParts = isset($data['selected_parts']) ? $data['selected_parts'] : null;
        $selectedMaterials = isset($data['selected_materials']) ? $data['selected_materials'] : null;

        try {
            // // Step 1: Update repair table with main_description and calculated repair_date based on machine_urgency
            // // First, get the urgency value to calculate the repair date
            // $stmt = $conn->prepare("
            //     SELECT machine_urgency FROM repair
            //     LEFT JOIN repair_request ON repair.repair_request_id = repair_request.repair_request_id
            //     LEFT JOIN machine ON repair_request.machine_id = machine.machine_id
            //     WHERE repair.repair_id = ?
            // ");

            // $stmt->bindParam(1, $repairNo, PDO::PARAM_INT);
            // $stmt->execute();
            // $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // $machineUrgency = $result['machine_urgency'];

            // // Calculate the repair date based on urgency
            // $repairDate = null;
            // if ($machineUrgency == 1) {
            //     $repairDate = date('Y-m-d', strtotime("+2 days"));
            // } elseif ($machineUrgency == 2) {
            //     $repairDate = date('Y-m-d', strtotime("+4 days"));
            // } elseif ($machineUrgency == 3) {
            //     $repairDate = date('Y-m-d', strtotime("+7 days"));
            // }

            $repairDate = date('Y-m-d', strtotime("+2 days"));
            
            // Update the repair table with new description and calculated repair date
            $stmtUpdate = $conn->prepare("
                UPDATE repair
                SET description = ?, repair_date = ?
                WHERE repair_id = ?
            ");
            $stmtUpdate->bindParam(1, $mainDescription);
            $stmtUpdate->bindParam(2, $repairDate);
            $stmtUpdate->bindParam(3, $repairNo);
            
            $stmtUpdate->execute();

            // Step 2: If selected materials exist, insert into material_request and material_request_items tables
            if (!empty($selectedMaterials)) {
                // Insert into material_request table
                $stmtMaterialRequest = $conn->prepare("
                    INSERT INTO material_request(repair_request_id, requested_by, status, approved_by, timestamp) 
                    VALUES (?, ?, 'Not Started', NULL, NOW())
                ");
                $stmtMaterialRequest->bindParam(1, $repairNo, PDO::PARAM_INT);
                $stmtMaterialRequest->bindParam(2, $_SESSION['employee_id'], PDO::PARAM_STR);
                $stmtMaterialRequest->execute();

                // Get the last inserted material_request_id
                $materialRequestId = $conn->lastInsertId();
                
                // Insert into material_request_items table for each selected material
                foreach ($selectedMaterials as $material) {
                    // Use the correct keys: 'item_code' and 'quantity' from the payload
                    $stmtMaterialRequestItem = $conn->prepare("
                        INSERT INTO material_request_items(material_request_id, item_id, quantity) 
                        VALUES (?, ?, ?)
                    ");
                    $stmtMaterialRequestItem->execute([$materialRequestId, $material['item_code'], $material['quantity']]);
                }
            }

            // Step 3: Update affected parts
            if (!empty($selectedParts)) {
                $stmtParts = $conn->prepare("
                    UPDATE machine_parts
                    SET machine_parts_status = 'Under repair'
                    WHERE machine_parts_id = ?
                ");

                foreach ($selectedParts as $part) {
                    $stmtParts->execute( $part['part_id']);
                }
            }
            
            // Log the activity (success)
            $activity = "Updated repair for repair_no: $repairNo, description updated and repair_date set.";
            logActivity($conn, $userId, $activity, $ipAddress);

            echo json_encode(['status' => 'success', 'message' => 'Repair updated successfully.']);
        } catch (Exception $e) {
            // Log the activity (failure)
            $activity = "Error updating repair_no: $repairNo - " . $e->getMessage();
            logActivity($conn, $userId, $activity, $ipAddress);

            // Handle any exceptions
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>