<?php
session_start();
include 'connect.php';

if(isset($_POST['employee_id']) && isset($_POST['password'])) {
    $employee_id = $_POST['employee_id'];
    $password = $_POST['password'];

    // Log IP Address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    if(empty($employee_id)) {
        header(header: "Location: login.php?error=Employee ID is required");
    }else if(empty($password)) {
        header(header: "Location: login.php?error=Password is required");
    }else {
        
        $stmt = $conn->prepare("SELECT * FROM users
        JOIN employee ON users.employee_id = employee.employee_id 
        WHERE employee.employee_id =?;");
        $stmt->execute([$employee_id]);

        if($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $user_id = $user['user_id'];
            $user_employee_id = $user['employee_id'];
            $user_password = $user['password'];
            $user_type = $user['user_type'];
            $user_first_name = $user['first_name'];
            $user_last_name = $user['last_name'];
            $profile_picture = $user['image'];

            // Detect the MIME type of image using finfo
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($profile_picture);

            if($employee_id === $user_employee_id) {
                if(password_verify(password: $password, hash: $user_password)) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['employee_id'] = $user_employee_id;
                    $_SESSION['user_type'] = $user_type;
                    $_SESSION['user_first_name'] = $user_first_name;
                    $_SESSION['user_last_name'] = $user_last_name;

                    $_SESSION['profile_picture'] = base64_encode($profile_picture);
                    $_SESSION['profile_picture_type'] = $mimeType;
                    
                    // Log successful login activity with IP address
                    $activity = 'User logged in successfully';
                    $logStmt = $conn->prepare("INSERT INTO activity_log (user_id, timestamp, activity, ip_address) VALUES (?, NOW(), ?, ?)");
                    $logStmt->execute([$user_id, $activity, $ip_address]);

                    // Redirect based on user type
                    if($user_type === "admin") {
                        header(header: "Location: admin/index.php");
                    }
                    else if($user_type === "engineering") {
                        header(header: "Location: engineering/index.php");
                    }
                    else { //non-engineering
                        header(header: "Location: non-engineering/index.php");
                    }
                }
                else {
                    // Log unsuccessful login attempt with IP address
                    $activity = 'Failed login attempt: Invalid password';
                    $logStmt = $conn->prepare("INSERT INTO activity_log (user_id, timestamp, activity, ip_address) VALUES (?, NOW(), ?, ?)");
                    $logStmt->execute([$user_id, $activity, $ip_address]);

                    header("Location: login.php?error=Employee ID or Password is invalid");
                }
            }
            else{
                // Log unsuccessful login attempt with IP address
                $activity = 'Failed login attempt: Invalid Employee ID';
                $logStmt = $conn->prepare("INSERT INTO activity_log (user_id, timestamp, activity, ip_address) VALUES (?, NOW(), ?, ?)");
                $logStmt->execute([$user_id, $activity, $ip_address]);

                header("Location: login.php?error=Employee ID or Password is invalid");
            }
        }
        else {
            // Log unsuccessful login attempt with IP address
            $activity = 'Failed login attempt: User does not exist';
            $logStmt = $conn->prepare("INSERT INTO activity_log (user_id, timestamp, activity, ip_address) VALUES (?, NOW(), ?, ?)");
            // Use null for user_id if the user doesn't exist
            $logStmt->execute([null, $activity, $ip_address]);

            header("Location: login.php?error=Employee ID or Password is invalid");
        }
    }
}
else {
    header("Location: login.php?error=Invalid request");
    exit();
}
?>