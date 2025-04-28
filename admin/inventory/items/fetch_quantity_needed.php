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
    $item_id = isset($_GET['item_code']) ? $_GET['item_code'] : '';

    // Validate if the item_id is a valid integer and greater than or equal to 1
    if (!filter_var($item_id, FILTER_VALIDATE_INT) || $item_id < 1) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid item_id. It must be an integer greater than or equal to 1.']);
        exit();
    }

    try {
        // Prepare the SQL statement to retrieve the sum of quantity for the item
        $stmt = $conn->prepare("
            SELECT SUM(quantity) AS total_quantity
            FROM material_request_items 
            JOIN material_request
            WHERE item_id = :item_id AND status != 'Done';
        ");

        // Bind the parameter correctly to the prepared statement
        $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();

        // Fetch the result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if total_quantity is null, indicating no quantity records were found
        $totalQuantity = $result['total_quantity'] ?? 0; // Default to 0 if null

        // Return the total quantity as a JSON response
        echo json_encode(['total_quantity' => $totalQuantity]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error retrieving total quantity needed: ' . $e->getMessage()]);
    }

} else {
    // If not a GET request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed. Allowed methods: GET.']);
}
?>