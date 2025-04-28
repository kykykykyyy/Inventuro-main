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
    $item_code = isset($_GET['item_code']) ? $_GET['item_code'] : '';

    try {
        // Prepare the SQL statement to retrieve data from the item table
        $stmt = $conn->prepare('
            SELECT reorder_point FROM item_reorder_point_fa
            WHERE item_code = :item_code
        ');

        // Bind the parameter correctly to the prepared statement
        $stmt->bindParam(':item_code', $item_code, PDO::PARAM_STR);

        // Execute the query
        if (!$stmt->execute()) {
            throw new Exception("Failed to get reorder point.");
        }

        // Fetch the result
        $result = $stmt->fetch(PDO::FETCH_ASSOC); // Use fetch for a single row
        if (empty($result)) {
            http_response_code(404);
            echo json_encode(['message' => 'No reorder point found.']);
            exit();
        }

        // Return the reorder point as a JSON response
        echo json_encode(['reorder_point' => $result['reorder_point']]); // Send the reorder point back

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error retrieving reorder point: ' . $e->getMessage()]);
    }

} else {
    // If not a GET request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed. Allowed methods: GET.']);
}
?>