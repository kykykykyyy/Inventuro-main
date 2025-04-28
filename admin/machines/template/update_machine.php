<?php
// update-machine.php

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
    $machine_id = $_POST['machine_id'] ?? '';
    $machine_name = $_POST['machine_name'] ?? '';
    $department_id = $_POST['department_id'] ?? '';
    $description = $_POST['description'] ?? '';

    // Handle the image if provided
    $image = $_POST['image'] ?? null;
    $itemImageBinary = $image ? base64_decode($image) : null;

    // Basic validation
    if (empty($machine_name) || empty($department_id) || empty($description)) {
        http_response_code(400);
        echo json_encode(['message' => 'All fields must be filled out.']);
        exit();
    }

    // Start a transaction (recommended for ensuring data integrity)
    $conn->beginTransaction();

    try {
        // Prepare the SQL statement to update the `machine` table
        $stmt = $conn->prepare("
            UPDATE machine 
            SET 
                machine_name = :machine_name,
                machine_department_id = :machine_department_id,
                machine_description = :machine_description,
                image = :image
            WHERE machine_id = :machine_id
        ");

        // Bind parameters
        $stmt->bindParam(':machine_name', $machine_name);
        $stmt->bindParam(':machine_department_id', $department_id, PDO::PARAM_INT);
        $stmt->bindParam(':machine_description', $description);
        $stmt->bindParam(':image', $itemImageBinary, PDO::PARAM_LOB); // Bind binary data for image
        $stmt->bindParam(':machine_id', $machine_id);

        // Execute the update query
        if (!$stmt->execute()) {
            throw new Exception("Failed to update machine.");
        }

        // Commit the transaction
        $conn->commit();
        http_response_code(200);
        echo json_encode(['message' => 'Machine updated successfully!']);
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
