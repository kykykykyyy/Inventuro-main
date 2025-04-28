<?php
session_start();
include '../connect.php';

$response = ["success" => false];

// Check if required fields are set
if (isset($_POST['title'], $_POST['content'], $_POST['status'], $_SESSION['user_id'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $status = $_POST['status'];
    $created_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO announcements (title, content, created_by, status) VALUES (?, ?, ?, ?)");
    $success = $stmt->execute([$title, $content, $created_by, $status]);

    if ($success) {
        $response["success"] = true;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
