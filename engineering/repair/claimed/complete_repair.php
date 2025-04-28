<?php
include '../../../connect.php'; // Database connection
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get the POST data
        $repairNo = $_POST['repair_no'];
        $repairedBy = $_POST['repaired_by'];

        // Validate input
        if (empty($repairNo) || empty($repairedBy)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }

        // Prepare the SQL query
        $sql = "UPDATE repair
        JOIN repair_request ON repair_request.repair_request_id = repair.repair_request_id
        SET repair_request.date_repaired = NOW(), 
            repair_request.repaired_by = :repairedBy, 
            repair_request.status = 'Done', 
            repair.duration_hours = TIMESTAMPDIFF(HOUR, repair_request.date_requested, NOW())
        WHERE repair.repair_id = :repairNo";

        // Prepare the statement
        $stmt = $conn->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':repairedBy', $repairedBy, PDO::PARAM_STR);
        $stmt->bindParam(':repairNo', $repairNo, PDO::PARAM_INT);

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Repair completed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to complete the repair']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>