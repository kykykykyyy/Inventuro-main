<?php

// Include your database connection file
include_once '../../../connect.php';

// Ensure the database connection is initialized
if (!$conn) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed.']);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the posted data
    $machine_id = isset($_POST['machine_id']) ? $_POST['machine_id'] : '';
    $machine_maintenance_interval_days = isset($_POST['machine_maintenance_interval_days']) ? $_POST['machine_maintenance_interval_days'] : '';

    // Basic validation
    if (empty($machine_id) || empty($machine_maintenance_interval_days)) {
        http_response_code(400);
        echo json_encode(['message' => 'Make sure to fill the interval day.']);
        exit();
    }

    if ($machine_maintenance_interval_days <= 0) {
        http_response_code(400);
        echo json_encode(['message' => 'Interval day must be greater than 0.']);
        exit();
    }

    // Start a transaction (optional)
    $conn->beginTransaction();

    try {
        // Prepare the SQL statement to update the `machine` table
        $stmt = $conn->prepare("
            UPDATE machine 
            SET machine_maintenance_interval_days = :machine_maintenance_interval_days
            WHERE machine_id = :machine_id
        ");

        // Bind parameters to the machine table update
        $stmt->bindParam(':machine_maintenance_interval_days', $machine_maintenance_interval_days, PDO::PARAM_INT);
        $stmt->bindParam(':machine_id', $machine_id, PDO::PARAM_STR);

        // Execute the update query
        if (!$stmt->execute()) {
            throw new Exception("Failed to update machine's maintenance interval days.");
        }

        // Commit the transaction
        $conn->commit();
        http_response_code(200);
        echo json_encode(['message' => 'Machine interval updated successfully!']);
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error updating machine: ' . $e->getMessage()]);
    }

} else {
    // If not a POST request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}