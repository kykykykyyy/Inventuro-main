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
    $adjustment_id = isset($_POST['adjustment_id']) ? $_POST['adjustment_id'] : '';

    // Basic validation
    if (empty($adjustment_id)) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid material request ID.']);
        exit();
    }

    // Begin transaction to ensure all queries are executed atomically
    $conn->beginTransaction();

    try {
        // Step 1: Update the material request status to "Done"
        $stmtUpdateMaterialRequest = $conn->prepare('
            UPDATE material_request
            SET status = "Done", approved_by = "123456-ADM"
            WHERE material_request_id = :material_request_id');
        
        // Bind the parameters
        $stmtUpdateMaterialRequest->bindParam(':material_request_id', $adjustment_id, PDO::PARAM_INT);
        
        // Execute the query
        if (!$stmtUpdateMaterialRequest->execute()) {
            throw new Exception("Failed to update material request status.");
        }

        // Step 2: Insert into item_adjustment table
        $stmtInventory = $conn->prepare('
            INSERT INTO item_adjustment (entry_date, reason_id, reference_number, created_by, description)
            VALUES (NOW(), 15, "Not specified", "123456-ADM", "Material is used for machine repair.")');
        
        // Execute the query
        if (!$stmtInventory->execute()) {
            throw new Exception("Failed to insert into item_adjustment.");
        }

        // Step 3: Insert into item_adjustment_list table
        $stmtAdjustmentList = $conn->prepare('
            INSERT INTO item_adjustment_list (adjustment_id, item_id, quantity_adjusted, previous_quantity)
            VALUES (
                (
                    SELECT adjustment_id
                    FROM item_adjustment
                    ORDER BY adjustment_id DESC
                    LIMIT 1 
                ),
                (
                    SELECT item_id
                    FROM material_request_items
                    WHERE material_request_id = :material_request_id
                    LIMIT 1
                ),
                (
                    SELECT quantity
                    FROM material_request_items
                    WHERE material_request_id = :material_request_id
                    AND item_id = (
                        SELECT item_id
                        FROM material_request_items
                        WHERE material_request_id = :material_request_id
                        LIMIT 1
                    )
                ),
                (
                    SELECT item_quantity
                    FROM item
                    WHERE item_code = (
                        SELECT item_id
                        FROM material_request_items
                        WHERE material_request_id = :material_request_id
                        LIMIT 1
                    )
                )
            )');
        
        // Bind the material_request_id
        $stmtAdjustmentList->bindParam(':material_request_id', $adjustment_id, PDO::PARAM_INT);
        $stmtAdjustmentList->bindParam(':material_request_id', $adjustment_id, PDO::PARAM_INT);
        $stmtAdjustmentList->bindParam(':material_request_id', $adjustment_id, PDO::PARAM_INT);
        $stmtAdjustmentList->bindParam(':material_request_id', $adjustment_id, PDO::PARAM_INT);

        // Execute the query
        if (!$stmtAdjustmentList->execute()) {
            throw new Exception("Failed to insert into item_adjustment_list.");
        }

        // Step 4: Update the item quantities in the item table
        $stmtUpdateItems = $conn->prepare('
            UPDATE item
            SET item_quantity = item_quantity - (
                SELECT quantity
                FROM material_request_items
                WHERE material_request_items.material_request_id = :material_request_id
                AND material_request_items.item_id = item.item_code
            )
            WHERE EXISTS (
                SELECT 1
                FROM material_request_items
                WHERE material_request_items.material_request_id = :material_request_id
                AND material_request_items.item_id = item.item_code
            )');
        
        // Bind the material_request_id
        $stmtUpdateItems->bindParam(':material_request_id', $adjustment_id, PDO::PARAM_INT);
        
        // Execute the query
        if (!$stmtUpdateItems->execute()) {
            throw new Exception("Failed to update item quantities.");
        }

        // Commit the transaction
        $conn->commit();

        // Return success response
        echo json_encode(['message' => 'Material request processed successfully.']);
    } catch (Exception $e) {
        // Rollback the transaction on failure
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
    }
}
?>