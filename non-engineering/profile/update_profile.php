<?php
session_start();
include '../../connect.php';

// Prepare a response array
$response = ["success" => false, "message" => ""];

// Ensure the database connection is initialized
if (!$conn) {
    http_response_code(500);
    $response["message"] = 'Database connection failed.';
    echo json_encode($response);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get the posted data
    $firstName = isset($_POST['first_name']) ? $_POST['first_name'] : '';
    $middleName = isset($_POST['middle_name']) ? $_POST['middle_name'] : '';
    $lastName = isset($_POST['last_name']) ? $_POST['last_name'] : '';
    $currentPasswordInput = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $ipAddress = $_SERVER['REMOTE_ADDR']; // Get the IP address for logging
    $profileImage = isset($_POST['image']) ? $_POST['image'] : null; // Base64 image data

    // Basic validation
    if (empty($firstName) || empty($lastName)) {
        http_response_code(400);
        $response["message"] = 'First Name and Last Name are required.';
        echo json_encode($response);
        exit();
    }

    // Start a transaction
    $conn->beginTransaction();

    try {
        // Get the current user's password hash from the database
        $userId = $_SESSION['user_id'];
        $employeeId = $_SESSION['employee_id'];

        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $currentPasswordHash = $user['password'];

            // Check if current password input is provided
            if (!empty($currentPasswordInput)) {
                // Verify current password
                if (password_verify($currentPasswordInput, $currentPasswordHash)) {
                    // Check if new passwords match
                    if ($newPassword === $confirmPassword) {
                        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    } else {
                        $response["message"] = "New Password and Confirm Password do not match.";
                        echo json_encode($response);
                        exit();
                    }
                } else {
                    $response["message"] = "Current Password is incorrect.";
                    echo json_encode($response);
                    exit();
                }
            }
        }
        // Update the employee details
        $updateEmployeeStmt = $conn->prepare("UPDATE employee SET first_name = ?, middle_name = ?, last_name = ? WHERE employee_id = ?");
        $updateEmployeeStmt->execute([$firstName, $middleName, $lastName, $employeeId]);

        // If there's a new password, update it
        if (!empty($newPassword)) {
            $updatePasswordStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $updatePasswordStmt->execute([$newPasswordHash, $userId]);
        }

        // Update profile image if provided
        if ($profileImage) {
            // Prepare the SQL statement to update the `users` table
            $stmtUser = $conn->prepare("UPDATE users SET image = ? WHERE employee_id = ?");
            $profileImageBinary = base64_decode($profileImage);
            $stmtUser->bindParam(1, $profileImageBinary, PDO::PARAM_LOB);
            $stmtUser->bindParam(2, $employeeId);

            // Execute the users update query
            if (!$stmtUser->execute()) {
                throw new Exception("Failed to update user profile image.");
            }
        }

        // Log activity
        $logStmt = $conn->prepare("INSERT INTO activity_log (user_id, timestamp, activity, ip_address) VALUES (?, NOW(), ?, ?)");
        $activity = "Profile picture updated";
        if (!empty($newPassword)) {
            $activity .= " and password updated";
        }
        $logStmt->execute([$userId, $activity, $ipAddress]);

        // Commit the transaction
        $conn->commit();

        // Set success response
        $response["success"] = true;
        $response["message"] = "Profile picture updated successfully!";
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollBack();
        http_response_code(500);
        $response["message"] = "Error updating user: " . $e->getMessage();
    }
} else {
    // If not a POST request
    http_response_code(405);
    $response["message"] = 'Method not allowed.';
}

// Output the response
echo json_encode($response);