<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Manila');

// Include your database connection file
include_once '../connect.php';

// Ensure the database connection is initialized
if (!$conn) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed.']);
    exit();
}

// Set content type to application/json
header('Content-Type: application/json');

// Check if the request is a GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Set MySQL timezone (optional)
        $stmt = $conn->prepare("SET time_zone = '+08:00'");
        $stmt->execute();
        
        // Query to sum item_quantity from the item table
        $stmt = $conn->prepare('SELECT SUM(item_quantity) AS total_quantity FROM item');
        $stmt->execute();
        $totalQuantity = $stmt->fetch(PDO::FETCH_ASSOC)['total_quantity'];

        // Query to count rows where reorder_point > item_quantity in item table
        $stmt = $conn->prepare('
            SELECT COUNT(*) AS low_stock_count 
            FROM item 
            WHERE reorder_point > item_quantity
        ');
        $stmt->execute();
        $lowStockCount = $stmt->fetch(PDO::FETCH_ASSOC)['low_stock_count'];

        // Query to count repair requests with date_requested this month
        $stmt = $conn->prepare('
            SELECT COUNT(*) AS this_month_count 
            FROM repair_request 
            WHERE MONTH(date_requested) = MONTH(CURRENT_DATE) 
            AND YEAR(date_requested) = YEAR(CURRENT_DATE)
        ');
        $stmt->execute();
        $thisMonthCount = $stmt->fetch(PDO::FETCH_ASSOC)['this_month_count'];

        // Query to count repair requests with date_requested last month
        $stmt = $conn->prepare('
            SELECT COUNT(*) AS last_month_count 
            FROM repair_request 
            WHERE MONTH(date_requested) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) 
            AND YEAR(date_requested) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
        ');
        $stmt->execute();
        $lastMonthCount = $stmt->fetch(PDO::FETCH_ASSOC)['last_month_count'];

        // Calculate the percentage change
        if ($lastMonthCount > 0) {
            $percentageChange = (($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100;
        } else {
            $percentageChange = ($thisMonthCount > 0) ? 100 : 0;
        }

        // Return all results in JSON format
        http_response_code(200);
        echo json_encode([
            'totalQuantity' => $totalQuantity,
            'lowStockCount' => $lowStockCount,
            'thisMonthCount' => $thisMonthCount,
            'lastMonthCount' => $lastMonthCount,
            'percentageChange' => $percentageChange,
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error retrieving data: ' . $e->getMessage()]);
    }
} else {
    // If not a GET request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed. Allowed methods: GET.']);
}
?>
