<?php
include("../../../connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $machine_serial_number = $_POST["machine_serial_number"];

    try {
        // Prepare and execute the query
        $sql = "SELECT * 
                FROM machine_parts 
                LEFT JOIN machine ON machine_parts.machine_id = machine.machine_id
                LEFT JOIN item ON machine_parts.machine_part_item_code = item.item_code
                WHERE machine.machine_serial_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(1, $machine_serial_number, PDO::PARAM_STR);
        $stmt->execute();

        // Check if any rows were returned and generate HTML
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Determine the text and color based on the disposable value
                $replaceableText = $row['disposable'] == 1 ? "REPLACEABLE" : "NOT REPLACEABLE";
                $replaceableColor = $row['disposable'] == 1 ? "green" : "black";

                echo "<div class='col-12 mb-4'>
                        <div class='card'>
                            <div class='card-body'>
                                <!-- Checkbox for selecting the part -->
                                <input type='checkbox' class='part-checkbox' id='part-{$row['machine_parts_id']}' name='{$row['machine_parts_name']}' value='{$row['machine_parts_id']}'>
                                <label for='part-{$row['machine_parts_id']}' class='ml-2'>Select</label>

                                <!-- Machine part details -->
                                <h5 class='card-title mt-3'>" . htmlspecialchars($row['machine_parts_name']) . "</h5>
                                <p class='card-text'>
                                    <strong style='color: {$replaceableColor};'>" . htmlspecialchars($replaceableText) . "</strong> | " . htmlspecialchars($row['machine_parts_description']) . "
                                </p>

                                <!-- Issue Dropdown -->
                                <div class='form-group'>
                                    <label for='issue-{$row['machine_parts_id']}'>Issue:</label>
                                    <select class='form-control issue-dropdown' id='issue-{$row['machine_parts_id']}' name='issue[{$row['machine_parts_id']}]' required='required' disabled>
                                        <option selected value='Manufacturing Defect'>Manufacturing Defect</option>
                                        <option value='Environmental Damage'>Environmental Damage</option>
                                        <option value='Operational Error'>Operational Error</option>
                                        <option value='Impact Damage'>Impact Damage</option>
                                        <option value='Other'>Other</option>
                                    </select>
                                </div>

                                <!-- Optional Description Text Area -->
                                <div class='form-group'>
                                    <label for='description-{$row['machine_parts_id']}'>Description (Optional):</label>
                                    <textarea class='form-control description-textarea' id='description-{$row['machine_parts_id']}' name='description[{$row['machine_parts_id']}]' rows='3' disabled></textarea>
                                </div>
                            </div>
                        </div>
                      </div>";
            }
        } else {
            echo "<div class='col-12 mb-4'><div class='alert alert-warning'>No machine parts found for the provided machine serial number.</div></div>";
        }

    } catch (PDOException $e) {
        echo "<div class='col-12 mb-4'><div class='alert alert-danger'>Error fetching machine parts: " . htmlspecialchars($e->getMessage()) . "</div></div>";
    }
}
?>