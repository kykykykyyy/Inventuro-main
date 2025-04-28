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
    $item_name = isset($_POST['item_name']) ? $_POST['item_name'] : '';
    $item_quantity = isset($_POST['item_quantity']) ? $_POST['item_quantity'] : '';
    $size_per_unit = isset($_POST['size_per_unit']) ? $_POST['size_per_unit'] : '';
    $unit = isset($_POST['unit']) ? $_POST['unit'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';

    $image = isset($_POST['image']) ? $_POST['image'] : null;
    $itemImageBinary = base64_decode($image);

    $created_by = isset($_POST['created_by']) ? $_POST['created_by'] : '';

    // Basic validation
    if (empty($item_name) || empty($item_quantity) || empty($size_per_unit) || empty($unit) || empty($description)) {
        http_response_code(400);
        echo json_encode(['message' => 'Make sure to fill all fields.']);
        exit();
    }
    // Start a transaction
    $conn->beginTransaction();

    try {
        // Insert into the item table
        $stmtItem = $conn->prepare("
            INSERT INTO item (item_name, item_quantity, size_per_unit, unit, description, image, created_by)
            VALUES (:item_name, :item_quantity, :size_per_unit, :unit, :description, :image, :created_by)
        ");

        $stmtItem->bindParam(':item_name', $item_name);
        $stmtItem->bindParam(':item_quantity', $item_quantity);
        $stmtItem->bindParam(':size_per_unit', $size_per_unit);
        $stmtItem->bindParam(':unit', $unit);
        $stmtItem->bindParam(':description', $description);
        $stmtItem->bindParam(':image', $itemImageBinary, PDO::PARAM_LOB);
        $stmtItem->bindParam(':created_by', $created_by);

        if (!$stmtItem->execute()) {
            throw new Exception("Failed to add item data.");
        }

        // Commit the transaction
        $conn->commit();
        http_response_code(201);
        // Send JSON response
        echo json_encode([
            'message' => 'Item added successfully!',
        ]);
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error adding item: ' . $e->getMessage()]);
    }
    
} else {
    // If not a POST request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
?>
