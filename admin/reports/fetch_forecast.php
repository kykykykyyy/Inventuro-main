<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Manila');

// Include your database connection file
include_once '../../connect.php';

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

        // Get the time filter from the query string (e.g., 'month', 'quarter', 'year')
        $timePeriod = isset($_GET['period']) ? $_GET['period'] : 'month';

        // Determine the forecast multiplier based on the time period
        $forecastMultiplier = 1; // Default: next month
        if ($timePeriod === 'quarter') {
            $forecastMultiplier = 3; // Next quarter
        } elseif ($timePeriod === 'year') {
            $forecastMultiplier = 12; // Next year
        }

        // Calculate the start date dynamically
        $today = new DateTime();
        $forecastDates = [];

        // Generate forecast dates dynamically
        for ($i = 1; $i <= $forecastMultiplier; $i++) {
            $forecastDates[] = $today->modify("+1 month")->format('Y-m');
        }

        // Query to fetch historical data (monthly adjustments)
        $stmt = $conn->prepare("
            SELECT 
                DATE_FORMAT(ia.entry_date, '%Y-%m') AS period,
                GREATEST(SUM(ial.quantity_adjusted), 0) AS total_adjustment
            FROM item_adjustment_list ial
            JOIN item_adjustment ia ON ial.adjustment_id = ia.adjustment_id
            GROUP BY period
            ORDER BY period ASC
        ");
        $stmt->execute();
        $historicalData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Optional: Further filter historical data in PHP to remove negatives (extra safety)
        $filteredHistoricalData = array_filter($historicalData, function ($item) {
            return isset($item['total_adjustment']) && $item['total_adjustment'] >= 0;
        });

        // Query to fetch forecasted data based on historical averages
        $stmt = $conn->prepare("
            SELECT 
                i.item_name,
                GREATEST((SUM(ial.quantity_adjusted) / COUNT(DISTINCT MONTH(ia.entry_date))) * :forecastMultiplier, 0) AS forecasted_usage
            FROM item_adjustment_list ial
            JOIN item_adjustment ia ON ial.adjustment_id = ia.adjustment_id
            JOIN item i ON ial.item_id = i.item_code
            GROUP BY i.item_name
        ");
        $stmt->bindParam(':forecastMultiplier', $forecastMultiplier, PDO::PARAM_INT);
        $stmt->execute();
        $forecastData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filter out any forecasted values that are negative or null (for additional safety)
        $filteredForecastData = array_filter($forecastData, function ($item) {
            return isset($item['forecasted_usage']) && $item['forecasted_usage'] >= 0;
        });

        // Return all results in JSON format
        http_response_code(200);
        echo json_encode([
            'historical' => array_values($filteredHistoricalData), // Reset array keys
            'forecast' => array_values($filteredForecastData), // Reset array keys
            'forecastDates' => $forecastDates, // Include dynamically calculated dates
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