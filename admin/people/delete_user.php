<?php
// delete_user.php

// Include your database connection file
include_once '../../connect.php';

// Ensure the database connection is initialized
if (!$conn) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed.']);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the posted data (in this case, we need the employee ID)
    $employeeId = isset($_POST['employee_id']) ? $_POST['employee_id'] : '';

    // Basic validation
    if (empty($employeeId)) {
        http_response_code(400);
        echo json_encode(['message' => 'Employee ID is required.']);
        exit();
    }

    // Start a transaction to ensure both deletions succeed or fail together
    $conn->beginTransaction();

    try {
        // Prepare the SQL statement to delete the record from the `users` table
        $stmtUser = $conn->prepare("
            DELETE FROM users 
            WHERE employee_id = :employee_id
        ");
        $stmtUser->bindParam(':employee_id', $employeeId);

        // Execute the delete query for the `users` table
        if (!$stmtUser->execute()) {
            throw new Exception("Failed to delete user from users table.");
        }

        // Prepare the SQL statement to delete the record from the `employee` table
        $stmtEmployee = $conn->prepare("
            DELETE FROM employee 
            WHERE employee_id = :employee_id
        ");
        $stmtEmployee->bindParam(':employee_id', $employeeId);

        // Execute the delete query for the `employee` table
        if (!$stmtEmployee->execute()) {
            throw new Exception("Failed to delete user from employee table.");
        }

        // Commit the transaction
        $conn->commit();
        http_response_code(200);
        echo json_encode(['message' => 'User deleted successfully.']);
        
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error deleting user: ' . $e->getMessage()]);
    }

} else {
    // If not a POST request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
