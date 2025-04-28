<?php
// update-user.php

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

    // Get the posted data
    $first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';
    $middle_name = isset($_POST['middle_name']) ? $_POST['middle_name'] : '';
    $last_name = isset($_POST['last_name']) ? $_POST['last_name'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';
    $department = isset($_POST['department']) ? $_POST['department'] : '';
    $employeeId = isset($_POST['employeeId']) ? $_POST['employeeId'] : '';
    $profileImage = isset($_POST['image']) ? $_POST['image'] : null;

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($role) || empty($department) || empty($employeeId)) {
        http_response_code(400);
        echo json_encode(['message' => 'Make sure to fill all fields.']);
        exit();
    }

    // Start a transaction (optional but recommended to ensure both updates succeed or fail together)
    $conn->beginTransaction();

    try {
        // Prepare the SQL statement to update the `employee` table
        $stmtEmployee = $conn->prepare("
            UPDATE employee 
            SET first_name = :first_name, 
                middle_name = :middle_name, 
                last_name = :last_name, 
                role = :role, 
                department = :department
            WHERE employee_id = :employee_id
        ");

        // Bind parameters to the employee table update
        $stmtEmployee->bindParam(':first_name', $first_name);
        $stmtEmployee->bindParam(':middle_name', $middle_name);
        $stmtEmployee->bindParam(':last_name', $last_name);
        $stmtEmployee->bindParam(':role', $role);
        $stmtEmployee->bindParam(':department', $department);
        $stmtEmployee->bindParam(':employee_id', $employeeId);

        // Execute the employee update query
        if (!$stmtEmployee->execute()) {
            throw new Exception("Failed to update employee data.");
        }

        // If there's a profile image to update, handle the `users` table update
        if ($profileImage) {
            // Prepare the SQL statement to update the `users` table
            $stmtUser = $conn->prepare("
                UPDATE users 
                SET image = :profile_image
                WHERE employee_id = :employee_id
            ");

            // Decode the base64 image to binary and bind the parameter
            $profileImageBinary = base64_decode($profileImage);
            $stmtUser->bindParam(':profile_image', $profileImageBinary, PDO::PARAM_LOB); // Binary data
            $stmtUser->bindParam(':employee_id', $employeeId);

            // Execute the users update query
            if (!$stmtUser->execute()) {
                throw new Exception("Failed to update user profile image.");
            }
        }

        // Commit the transaction
        $conn->commit();
        http_response_code(200);
        echo json_encode(['message' => 'User and image updated successfully!']);
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error updating user: ' . $e->getMessage()]);
    }

} else {
    // If not a POST request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
