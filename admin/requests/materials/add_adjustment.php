<?php
// Include your database connection file
include_once '../../../connect.php';

// Ensure the database connection is initialized
if (!$conn) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed.']);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the posted data
    $reason_id = $_POST['reason_id'];
    $reference_number = $_POST['reference_number'];
    $entry_date = $_POST['entry_date'];
    $description = $_POST['description'];
    $created_by = $_POST['created_by'];
    $items = $_POST['items'];

    // Start a transaction
    try {
        $conn->beginTransaction();

        // Insert adjustment metadata
        $stmtAdjustment = $conn->prepare('
            INSERT INTO item_adjustment
            (entry_date, reason_id, reference_number, created_by, description)
            VALUES
            (:entry_date, :reason_id, :reference_number, :created_by, :description)
        ');

        $stmtAdjustment->bindParam(':entry_date', $entry_date);
        $stmtAdjustment->bindParam(':reason_id', $reason_id);
        $stmtAdjustment->bindParam(':reference_number', $reference_number);
        $stmtAdjustment->bindParam(':created_by', $created_by);
        $stmtAdjustment->bindParam(':description', $description);

        if (!$stmtAdjustment->execute()) {
            throw new Exception("Failed to insert adjustment.");
        }
        
        $adjustment_id = $conn->lastInsertId();

        // Prepare the statement for item adjustments
        $stmtAdjustmentList = $conn->prepare("INSERT INTO item_adjustment_list (adjustment_id, item_id, quantity_adjusted, previous_quantity) VALUES (?, ?, ?, ?)");
        
        // Prepare statement to update items table
        $stmtUpdateItem = $conn->prepare("UPDATE item SET item_quantity = ? WHERE item_code = ?");

        foreach ($items as $item) {
            // Insert into item_adjustment_list
            $stmtAdjustmentList->execute([
                $adjustment_id,
                $item['item_id'],
                $item['quantity_adjusted'],
                $item['previous_quantity']
            ]);

            // Update the item quantity in the items table
            $stmtUpdateItem->execute([
                $item['new_quantity'], // New quantity to be added
                $item['item_id'] // Assuming item_id exists in the items array
            ]);
        }

        // Commit the transaction
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Adjustment added successfully.']);

    } catch (PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error adding adjustment: ' . $e->getMessage()]);
    }

} else {
    // If not a POST request
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed. Allowed methods: POST.']);
}
?>
