<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include your database connection file
include_once '../../../connect.php';

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
        // Prepare the SQL statement to retrieve data from the item table
        $stmt = $conn->prepare('
            SELECT * FROM item
        ');

        // Execute the query
        if (!$stmt->execute()) {
            throw new Exception("Failed to get items.");
        }

        // Fetch the results
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($result)) {
            http_response_code(404);
            echo json_encode(['message' => 'No items found.']);
            exit();
        }

        // Encode image data to base64 if needed (optional)
        foreach ($result as &$item) {
            if (isset($item['image'])) {
                // Detect the MIME type of image using finfo
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->buffer($item['image']);

                // Convert BLOB to base64
                $item['image'] = 'data:' . $mimeType . ';base64,' . base64_encode($item['image']);
            }
        }

        // Return the result as JSON
        http_response_code(200);
        echo json_encode($result);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error retrieving items: ' . $e->getMessage()]);
    }

} else {
    // If not a GET request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed. Allowed methods: GET.']);
}
