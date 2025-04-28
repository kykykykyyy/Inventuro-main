<?php
// fetch_announcements.php
// Enable error reporting for debugging (only use in development)
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

        // Fetch active announcements, ordered by created_at descending
        $stmt = $conn->prepare('
            SELECT announcement_id, title, content, created_at, created_by, status
            FROM announcements
            WHERE status = "active"
            ORDER BY created_at DESC
        ');
        $stmt->execute();
        
        // Fetch all active announcements
        $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return announcements in JSON format
        http_response_code(200);
        echo json_encode($announcements ?: []); // Always return an array
        
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