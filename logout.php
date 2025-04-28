<?php
session_start();
include 'connect.php'; // Ensure this is included to establish DB connection

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Get the user ID from session
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Log successful logout activity with IP address
    $activity = 'User logged out successfully';
    $logStmt = $conn->prepare("INSERT INTO activity_log (user_id, timestamp, activity, ip_address) VALUES (?, NOW(), ?, ?)");
    $logStmt->execute([$user_id, $activity, $ip_address]);

    // Unset and destroy the session
    session_unset();
    session_destroy();
}

// Redirect to login page
header("Location: login.php");
exit();
?>