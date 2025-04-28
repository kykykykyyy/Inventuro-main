<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include your database connection file
include_once '../../../connect.php'; // Adjust the path as needed

// Ensure the database connection is initialized
if (!$conn) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed.']);
    exit();
}

// Set content type to application/json
header('Content-Type: application/json');

// Check if the request is a GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Get the machine ID from the query string
    $machine_id = isset($_GET['machine_id']) ? $_GET['machine_id'] : '';

    // Basic validation
    if (empty($machine_id)) {
        http_response_code(400);
        echo json_encode(['message' => 'Machine ID is required.']);
        exit();
    }

    try {
        // Prepare the SQL query to get the parts with the latest replacement date for the given machine_id
        $stmt = $conn->prepare('
            SELECT 
                machine_parts_id, 
                machine_parts_name,
                DATE(machine_parts_replacement_date) AS replacement_date,
                machine_parts_replacement_maintenance_hours AS replacement_hours,
                current_operating_hours
            FROM 
                machine_parts
            WHERE 
                machine_id = :machine_id
                AND DATE(machine_parts_replacement_date) = (
                    SELECT MAX(DATE(machine_parts_replacement_date))
                    FROM machine_parts
                    WHERE machine_id = :machine_id
                );
        ');

        // Bind parameters
        $stmt->bindParam(':machine_id', $machine_id, PDO::PARAM_INT);

        // Execute the query
        if (!$stmt->execute()) {
            throw new Exception("Failed to retrieve machine parts.");
        }

        // Fetch the results
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If no results are found
        if (empty($result)) {
            http_response_code(404);
            echo json_encode(['message' => 'No machine parts found for this machine.']);
            exit();
        }

        // Return the result as JSON
        http_response_code(200);
        echo json_encode($result);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error retrieving machine parts: ' . $e->getMessage()]);
    }
} else {
    // If the request is not GET, return method not allowed
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed.']);
}