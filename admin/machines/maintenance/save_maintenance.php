<?php
// Enable error reporting for debugging (can be disabled in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    $data = json_decode(file_get_contents('php://input'), true); // Read JSON input
    $maintenance_date = isset($data['maintenance_date']) ? $data['maintenance_date'] : '';
    $maintenance_description = isset($data['maintenance_description']) ? $data['maintenance_description'] : '';
    $machine_ids = isset($data['machine_ids']) ? $data['machine_ids'] : [];

    // Basic validation
    if (empty($maintenance_date) || empty($maintenance_description) || empty($machine_ids)) {
        http_response_code(400);
        echo json_encode(['message' => 'Make sure to fill all fields.']);
        exit();
    }

    // Start a transaction
    $conn->beginTransaction();

    try {
        // Prepare the SQL statement
        $stmt = $conn->prepare("
            INSERT INTO maintenance (machine_id, maintenance_scheduled_date, maintenance_status, machine_description) 
            VALUES (:machine_id, :maintenance_scheduled_date, :maintenance_status, :machine_description)
        ");

        // Loop through machine IDs and insert a record for each
        foreach ($machine_ids as $machine_id) {
            // Bind parameters
            $maintenance_status = 'Scheduled'; // Status is always "Scheduled"
            $stmt->bindParam(':machine_id', $machine_id);
            $stmt->bindParam(':maintenance_scheduled_date', $maintenance_date);
            $stmt->bindParam(':maintenance_status', $maintenance_status);
            $stmt->bindParam(':machine_description', $maintenance_description);

            // Execute the statement
            if (!$stmt->execute()) {
                throw new Exception("Failed to add maintenance data for machine ID: $machine_id.");
            }
        }

        // Commit the transaction
        $conn->commit();
        http_response_code(201);
        // Send JSON response
        echo json_encode([
            'success' => true,
            'message' => 'Maintenance schedule added successfully!',
        ]);
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error adding maintenance schedule: ' . $e->getMessage()]);
    }
} else {
    // If not a POST request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
?>
