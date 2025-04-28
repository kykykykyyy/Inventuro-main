<?php
header('Content-Type: application/json');

require '../../../connect.php'; // Replace with your database connection script

if (isset($_GET['template_id'])) {
    $templateId = intval($_GET['template_id']);

    try {
        $sql = "SELECT 
                    machine_type_parts_id, 
                    machine_type_parts_name, 
                    machine_type_parts_description, 
                    machine_type_parts_quantity, 
                    machine_type_parts_maintenance_interval, 
                    machine_type_parts_replacement_lifespan, 
                    machine_type_parts_criticality_level,
                    machine_type_parts_instructions
                FROM machine_type_parts 
                WHERE machine_type_id = :templateId";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':templateId', $templateId, PDO::PARAM_INT);
        $stmt->execute();
        $parts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($parts);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Template ID not provided.']);
}
?>