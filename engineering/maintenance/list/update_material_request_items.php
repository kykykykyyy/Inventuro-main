<?php
session_start();
header('Content-Type: application/json');
include_once '../../../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $items = $data['items'] ?? [];

    try {
        $conn->beginTransaction();

        foreach ($items as $item) {
            $stmt = $conn->prepare("
                UPDATE material_request_items 
                SET quantity = ? 
                WHERE item_id = ?
            ");
            $stmt->execute([$item['quantity'], $item['item_id']]);
        }

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . htmlspecialchars($e->getMessage())]);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
?>