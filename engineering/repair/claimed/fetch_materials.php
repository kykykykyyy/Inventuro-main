<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../../../connect.php");

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the machine serial number and selected parts data from the POST request
    if (isset($_POST["machine_serial_number"]) && isset($_POST["selected_parts"])) {
        $machine_serial_number = $_POST["machine_serial_number"];
        $selected_parts = $_POST["selected_parts"];
    } else {
        echo json_encode(['error' => 'Required POST data missing']);
        exit; // Stop script execution if necessary data is missing
    }

    try {
        // Prepare the SQL query to fetch machine parts associated with the machine serial number
        if (!empty($selected_parts)) {
            // If selected parts are provided, use the IN clause with machine_serial_number
            $placeholders = implode(',', array_fill(0, count($selected_parts), '?'));
            $sql = "
                SELECT * 
                FROM machine_parts
                LEFT JOIN item ON machine_parts.machine_part_item_code = item.item_code
                LEFT JOIN machine ON machine_parts.machine_id = machine.machine_id
                WHERE
                    machine.machine_serial_number = ? 
                    AND machine_parts.machine_parts_id IN ($placeholders)
                    AND machine_parts.machine_part_item_code IS NOT NULL;";
            
            // Prepare the statement
            $stmt = $conn->prepare($sql); // Prepare the SQL statement
            
            // Bind the machine serial number
            $stmt->bindParam(1, $machine_serial_number, PDO::PARAM_STR);

            // Bind the selected parts' machine_parts_id values dynamically
            foreach ($selected_parts as $index => $part) {
                // Bind each part's id starting from position 2 in the prepared statement
                $stmt->bindValue($index + 2, $part['part_id'], PDO::PARAM_INT);  // Start binding from index 2
            }
            
            // Execute the statement
            $stmt->execute();

            $items = [];
            
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Check if item_code is not NULL before adding to the $items array
                    if ($row['item_code'] !== null) {
                        $items[] = [
                            'item_code' => $row['item_code'],
                            'item_name' => $row['item_name'],
                            'max_count' => $row['machine_parts_count'],
                            'item_quantity' => $row['item_quantity'],
                            'size_per_unit' => $row['size_per_unit'],
                            'unit' => $row['unit'],
                            'description' => $row['description'],
                            'machine_parts_id' => $row['machine_parts_id']
                        ];
                    }
                }
            }

            // Return the items as JSON
            echo json_encode($items);
        }

    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error fetching materials: ' . $e->getMessage()]);
    }
}
?>