<?php

// Include the database connection file
include_once '../../../connect.php';

// Ensure the database connection is initialized
if (!$conn) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed.']);
    exit();
}

// Only proceed if the request is a GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    try {
        // Array to hold the response data
        $response = [];

        // Query to get average daily demand and lead time for each item
        $query = "
            SELECT item_id AS item_code, 
                   AVG(quantity_adjusted) AS average_daily_demand, 
                   MAX(DATEDIFF(NOW(), entry_date)) AS lead_time
            FROM item_adjustment_list AS ial
            JOIN item_adjustment AS ia ON ia.adjustment_id = ial.adjustment_id
            WHERE ia.entry_date BETWEEN NOW() - INTERVAL 30 DAY AND NOW()
            GROUP BY item_id";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Start transaction to ensure data consistency
        $conn->beginTransaction();

        foreach ($results as $row) {
            $item_code = $row['item_code'];
            $average_daily_demand = max(0, $row['average_daily_demand']); // Ensure non-negative
            $lead_time = max(10, $row['lead_time']);                       // Ensure non-negative
        
            // Calculate safety stock (adjust this as per your business logic)
            $safety_stock = 0.15 * $average_daily_demand * $lead_time;
        
            // Calculate reorder point, ensuring it is non-negative
            $reorder_point = max(10, ($average_daily_demand * $lead_time) + $safety_stock);
        
            // Prepare the SQL to update reorder points
            $stmtUpdate = $conn->prepare("
                UPDATE item_reorder_point_fa 
                SET reorder_point = :reorder_point, date = NOW()
                WHERE item_code = :item_code
            ");
        
            // Bind parameters
            $stmtUpdate->bindParam(':item_code', $item_code, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':reorder_point', $reorder_point, PDO::PARAM_INT);
        
            // Execute the update
            if (!$stmtUpdate->execute()) {
                throw new Exception("Failed to update reorder point for item_code: $item_code");
            }
        
            // Append the item to the response
            $response[] = [
                'item_code' => $item_code,
                'reorder_point' => $reorder_point,
            ];
        }

        // Commit the transaction
        $conn->commit();

        // Respond with success
        http_response_code(200);
        echo json_encode(['message' => 'Reorder points updated successfully!', 'data' => $response]);

    } catch (Exception $e) {
        // Rollback transaction if any error occurs
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error updating reorder points: ' . $e->getMessage()]);
    }

} else {
    // If not a GET request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
?>