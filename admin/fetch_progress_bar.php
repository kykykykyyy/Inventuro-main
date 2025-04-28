<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Manila');

// Include your database connection file
include_once '../connect.php';

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
    try {
        // Set MySQL timezone (optional)
        $stmt = $conn->prepare("SET time_zone = '+08:00'");
        $stmt->execute();

        // Count total rows in material_request with a status of "Done" or "Not Done" for the current year
        $stmt = $conn->prepare('
            SELECT COUNT(*) AS total_count 
            FROM material_request
        ');
        $stmt->execute();
        $totalCount = $stmt->fetch(PDO::FETCH_ASSOC)['total_count'];

        // Count rows with status "Done" in material_request for the current year
        $stmt = $conn->prepare('
            SELECT COUNT(*) AS done_count 
            FROM material_request 
            WHERE status = "Done" 
        ');
        $stmt->execute();
        $doneCount = $stmt->fetch(PDO::FETCH_ASSOC)['done_count'];

        // Calculate the percentage of "Done" status
        $donePercentage = ($totalCount > 0) ? ($doneCount / $totalCount) * 100 : 0;

        // Return all results in JSON format
        http_response_code(200);
        echo json_encode([
            'totalCount' => $totalCount,
            'doneCount' => $doneCount,
            'donePercentage' => $donePercentage
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error retrieving data: ' . $e->getMessage()]);
    }
} else {
    // If not a GET request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed. Allowed methods: GET.']);
}
?>