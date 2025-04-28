<?php
// delete-machine.php

// Include the database connection file
include_once '../../../connect.php';

// Ensure the database connection is established
if (!$conn) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed.']);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the posted machine_id
    $machine_id = $_POST['machine_id'] ?? '';

    // Basic validation
    if (empty($machine_id)) {
        http_response_code(400);
        echo json_encode(['message' => 'Machine ID is required.']);
        exit();
    }

    // Start a transaction
    $conn->beginTransaction();

    try {
        // Prepare the SQL statement to delete the machine
        $stmt = $conn->prepare("DELETE FROM machine WHERE machine_id = :machine_id");
        $stmt->bindParam(':machine_id', $machine_id);

        // Execute the delete query and check if a row was affected
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            throw new Exception("Machine with ID $machine_id not found.");
        }

        // Commit the transaction
        $conn->commit();
        http_response_code(200);
        echo json_encode(['message' => 'Machine deleted successfully.']);
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error deleting machine: ' . $e->getMessage()]);
    }

} else {
    // If not a POST request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
