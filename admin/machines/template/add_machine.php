<?php
// add_machine.php

// Include your database connection file
include_once '../../../connect.php';

if (!$conn) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the posted data
    $machine_name = $_POST['machine_name'] ?? '';
    $machine_type = $_POST['machine_type'] ?? '';
    $machine_model = $_POST['machine_model'] ?? '';
    $machine_manufacturer = $_POST['machine_manufacturer'] ?? '';
    $machine_year_of_manufacture = $_POST['machine_year_of_manufacture'] ?? '';
    $machine_maintenance_interval_days = $_POST['machine_maintenance_interval_days'] ?? '';
    $machine_description = $_POST['machine_description'] ?? '';
    $department_id = $_POST['department_id'] ?? '';
    $machine_created_by = $_POST['machine_created_by'] ?? '';
    $machine_created_at = $_POST['machine_created_at'] ?? date('Y-m-d H:i:s');
    $machine_location_id = 1;

    $image = $_POST['image'] ?? null;
    $machineImageBinary = $image ? base64_decode($image) : null;

    // Validation
    if (empty($machine_name) || empty($machine_type) || empty($machine_model) || empty($machine_manufacturer) || empty($machine_year_of_manufacture) || empty($machine_maintenance_interval_days) || empty($department_id)) {
        http_response_code(400);
        echo json_encode(['message' => 'All required fields must be filled out.']);
        exit();
    }

    $conn->beginTransaction();

    try {
        // Insert into the machine table
        $stmtMachine = $conn->prepare("
            INSERT INTO machine (
                machine_name, machine_type, machine_model, machine_manufacturer, machine_year_of_manufacture, 
                machine_maintenance_interval_days, machine_description, machine_department_id, machine_location_id, machine_created_by, 
                machine_created_at, image
            ) VALUES (
                :machine_name, :machine_type, :machine_model, :machine_manufacturer, :machine_year_of_manufacture, 
                :machine_maintenance_interval_days, :machine_description, :department_id, :location_id, :machine_created_by, 
                :machine_created_at, :image
            )
        ");

        // Bind parameters
        $stmtMachine->bindParam(':machine_name', $machine_name);
        $stmtMachine->bindParam(':machine_type', $machine_type);
        $stmtMachine->bindParam(':machine_model', $machine_model);
        $stmtMachine->bindParam(':machine_manufacturer', $machine_manufacturer);
        $stmtMachine->bindParam(':machine_year_of_manufacture', $machine_year_of_manufacture, PDO::PARAM_INT);
        $stmtMachine->bindParam(':machine_maintenance_interval_days', $machine_maintenance_interval_days, PDO::PARAM_INT);
        $stmtMachine->bindParam(':machine_description', $machine_description);
        $stmtMachine->bindParam(':department_id', $department_id, PDO::PARAM_INT); // Department ID
        $stmtMachine->bindParam(':location_id', $machine_location_id, PDO::PARAM_INT);
        $stmtMachine->bindParam(':machine_created_by', $machine_created_by);
        $stmtMachine->bindParam(':machine_created_at', $machine_created_at);

        if ($machineImageBinary) {
            $stmtMachine->bindParam(':image', $machineImageBinary, PDO::PARAM_LOB);
        } else {
            $stmtMachine->bindValue(':image', null, PDO::PARAM_NULL);
        }

        
        // Execute the insert query
        $stmtMachine->execute();

        // Commit the transaction
        $conn->commit();
        http_response_code(201);
        echo json_encode(['message' => 'Machine added successfully!']);
    } catch (Exception $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error adding machine: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
?>
