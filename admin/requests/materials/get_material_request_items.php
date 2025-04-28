<?php
session_start();
include_once '../../../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materialRequestId = $_POST['material_request_id'] ?? null;

    if ($materialRequestId === null) {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        exit();
    }

    try {
        $stmt = $conn->prepare("
            SELECT material_request_items.item_id, 
                   material_request_items.quantity, 
                   item.item_name, 
                   item.item_quantity 
            FROM material_request_items 
            LEFT JOIN item ON material_request_items.item_id = item.item_code
            WHERE material_request_items.material_request_id = ?
        ");
        $stmt->bindParam(1, $materialRequestId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'items' => $items]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to retrieve items.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
