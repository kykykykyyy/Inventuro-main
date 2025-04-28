<?php
session_start();
include_once '../../../connect.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materialRequestId = $_POST['material_request_id'] ?? null;

    if ($materialRequestId === null) {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        exit();
    }

    // Prepare SQL query to fetch items
    $stmt = $conn->prepare("
        SELECT item_id, quantity, item_name, item_quantity, material_request.status
        FROM material_request_items 
        LEFT JOIN item ON material_request_items.item_id = item.item_code
        LEFT JOIN material_request ON material_request.material_request_id = material_request_items.material_request_id
        WHERE material_request_items.material_request_id = ?
    ");
    $stmt->bindParam(1, $materialRequestId, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'items' => $items]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to retrieve items.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>