<?php
// Enable error reporting for debugging (can be disabled in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include your database connection file
include_once '../../connect.php';

// Function to generate a random password
function generateRandomPassword($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    $charactersLength = strlen($characters);
    $randomPassword = '';
    for ($i = 0; $i < $length; $i++) {
        $randomPassword .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomPassword;
}

// Start output buffering to avoid accidental output
ob_start();

// Ensure the database connection is initialized
if (!$conn) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed.']);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the posted data
    $employee_id = isset($_POST['employee_id']) ? $_POST['employee_id'] : '';
    $first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';
    $middle_name = isset($_POST['middle_name']) ? $_POST['middle_name'] : '';
    $last_name = isset($_POST['last_name']) ? $_POST['last_name'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';
    $department = isset($_POST['department']) ? $_POST['department'] : '';
    $profileImage = isset($_POST['image']) ? $_POST['image'] : null;

    // Basic validation
    if (empty($employee_id) || empty($first_name) || empty($last_name) || empty($role) || empty($department)) {
        http_response_code(400);
        echo json_encode(['message' => 'Make sure to fill all fields.']);
        exit();
    }

    // Determine user_type based on department and role
    $user_type = 'non-engineering';
    if ($department === 'Engineering') {
        $user_type = 'engineering';
    } elseif ($department === 'Warehouse' && $role === 'Admin') {
        $user_type = 'admin';
    }

    // Generate a random password and hash it
    $randomPassword = generateRandomPassword();
    $hashed_password = password_hash($randomPassword, PASSWORD_BCRYPT);

    // Start a transaction
    $conn->beginTransaction();

    try {
        // Insert into the employee table
        $stmtEmployee = $conn->prepare("
            INSERT INTO employee (employee_id, first_name, middle_name, last_name, department, role)
            VALUES (:employee_id, :first_name, :middle_name, :last_name, :department, :role)
        ");
        $stmtEmployee->bindParam(':employee_id', $employee_id);
        $stmtEmployee->bindParam(':first_name', $first_name);
        $stmtEmployee->bindParam(':middle_name', $middle_name);
        $stmtEmployee->bindParam(':last_name', $last_name);
        $stmtEmployee->bindParam(':department', $department);
        $stmtEmployee->bindParam(':role', $role);

        if (!$stmtEmployee->execute()) {
            throw new Exception("Failed to add employee data.");
        }

        // Handle the profile image
        $profileImageBinary = null;
        if ($profileImage) {
            // Decode the base64 image
            $profileImageBinary = base64_decode($profileImage);
            if ($profileImageBinary === false) {
                throw new Exception("Failed to decode the image.");
            }
        }

        // Insert into the users table
        $stmtUser = $conn->prepare("
            INSERT INTO users (employee_id, password, user_type, image)
            VALUES (:employee_id, :hashed_password, :user_type, :profile_image)
        ");
        
        $stmtUser->bindParam(':employee_id', $employee_id);
        $stmtUser->bindParam(':hashed_password', $hashed_password);
        $stmtUser->bindParam(':user_type', $user_type);

        // If no image is provided, bind null
        if ($profileImageBinary) {
            $stmtUser->bindParam(':profile_image', $profileImageBinary, PDO::PARAM_LOB);
        } else {
            $null = null;
            $stmtUser->bindParam(':profile_image', $null, PDO::PARAM_LOB); // Set to NULL if no image
        }
        
        if (!$stmtUser->execute()) {
            throw new Exception("Failed to add user data.");
        }

        // Commit the transaction
        $conn->commit();
        http_response_code(201);
        // Send JSON response
        echo json_encode([
            'message' => 'User added successfully!',
            'password' => $randomPassword // Send plain-text password
        ]);
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error adding user: ' . $e->getMessage()]);
    }
    
} else {
    // If not a POST request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}

// Flush output buffer to ensure clean JSON response
ob_end_flush();
?>
