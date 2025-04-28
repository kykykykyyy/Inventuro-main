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

    $adjustment_id = isset($_GET['adjustment_id']) ? $_GET['adjustment_id'] : '';

    // Basic validation
    if (empty($adjustment_id)) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid adjustment ID.']);
        exit();
    }

    try {
        // Prepare the SQL statement to retrieve from the item_adjustment_list table
        $stmtAdjustmentList = $conn->prepare('
            SELECT 
                item.item_name, 
                item.item_quantity, 
                item.image,
                item_adjustment_list.quantity_adjusted,
                item_adjustment_list.previous_quantity
            FROM 
                item_adjustment_list 
            JOIN 
                item ON item_adjustment_list.item_id = item.item_code
            WHERE 
                item_adjustment_list.adjustment_id = :adjustment_id');

        // Bind parameters
        $stmtAdjustmentList->bindParam(':adjustment_id', $adjustment_id, PDO::PARAM_STR);

        // Execute the query
        if (!$stmtAdjustmentList->execute()) {
            throw new Exception("Failed to get adjustment details.");
        }

        // Fetch the results
        $result = $stmtAdjustmentList->fetchAll(PDO::FETCH_ASSOC);
        if (empty($result)) {
            http_response_code(404);
            echo json_encode(['message' => 'No adjustment details found.']);
            exit();
        }

        // Encode image data to base64
        foreach ($result as &$item) {
            if (isset($item['image'])) {
                $item['image'] = base64_encode($item['image']); // Encode the image data
            }
            else {
                $item['image'] = base64_encode(file_get_contents('../../../images/gallery.png'));
            }
        }

        // Return the result as JSON
        http_response_code(200);
        echo json_encode($result);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error retrieving adjustment: ' . $e->getMessage()]);
    }

} else {
    // If not a GET request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed. Allowed methods: GET.']);
}
