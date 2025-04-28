<?php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

// Include your database connection script
require '../../../connect.php';

try {
    // Decode JSON data if provided
    $data = isset($_POST['data']) ? json_decode($_POST['data'], true) : [];

    $conn->beginTransaction();

    // Handle manufacturer
    $manufacturerName = $data['manufacturerName'];
    $manufacturerId = null;

    $stmt = $conn->prepare("SELECT manufacturer_id FROM manufacturer WHERE manufacturer_name = :name");
    $stmt->execute([':name' => $manufacturerName]);
    $existingManufacturer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingManufacturer) {
        $manufacturerId = $existingManufacturer['manufacturer_id'];
    } else {
        $stmt = $conn->prepare("INSERT INTO manufacturer (manufacturer_name) VALUES (:name)");
        $stmt->execute([':name' => $manufacturerName]);
        $manufacturerId = $conn->lastInsertId();
    }

    // Handle department
    $departmentName = $data['departmentName'];
    $departmentId = null;

    $stmt = $conn->prepare("SELECT department_id FROM department WHERE department_name = :name");
    $stmt->execute([':name' => $departmentName]);
    $existingDepartment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingDepartment) {
        $departmentId = $existingDepartment['department_id'];
    } else {
        $stmt = $conn->prepare("INSERT INTO department (department_name) VALUES (:name)");
        $stmt->execute([':name' => $departmentName]);
        $departmentId = $conn->lastInsertId();
    }

    // Handle file uploads
    $uploadDir = '../../../uploads/'; // Set your upload directory here
    $machineManualPath = null;
    $warrantyDocumentPath = null;

    // Handle machine manual upload
    if (isset($_FILES['machineManual']) && $_FILES['machineManual']['error'] === UPLOAD_ERR_OK) {
        $machineManualPath = $uploadDir . basename($_FILES['machineManual']['name']);
        if (!move_uploaded_file($_FILES['machineManual']['tmp_name'], $machineManualPath)) {
            throw new Exception("Failed to upload machine manual.");
        }
    }

    // Handle warranty document upload
    if (isset($_FILES['warrantyDocument']) && $_FILES['warrantyDocument']['error'] === UPLOAD_ERR_OK) {
        $warrantyDocumentPath = $uploadDir . basename($_FILES['warrantyDocument']['name']);
        if (!move_uploaded_file($_FILES['warrantyDocument']['tmp_name'], $warrantyDocumentPath)) {
            throw new Exception("Failed to upload warranty document.");
        }
    }

    // Step 1: Insert machine information
    $stmt = $conn->prepare("
        INSERT INTO machine (
            machine_name,
            machine_serial_number,
            machine_type,
            machine_manufacturer,
            machine_department_id,
            machine_manufactured_date,
            image,
            machine_manual,
            machine_created_by,
            machine_created_at,
            machine_description
        ) 
        VALUES (
            :machine_name,
            :machine_serial_number,
            :machine_type,
            :machine_manufacturer,
            :machine_department_id,
            :machine_manufactured_date,
            :image,
            :machine_manual,
            :machine_created_by,
            :machine_created_at,
            :machine_description
        )
    ");
    $stmt->execute([
        ':machine_name' => $data['machineName'],
        ':machine_serial_number' => $data['serialNumber'],
        ':machine_type' => $data['machineType'],
        ':machine_manufacturer' => $manufacturerId,
        ':machine_department_id' => $departmentId,
        ':machine_manufactured_date' => $data['manufacturedDate'],
        ':image' => null, // Skip image for now
        ':machine_manual' => $machineManualPath, // File path for machine manual
        ':machine_created_by' => $data['created_by'],
        ':machine_created_at' => date('Y-m-d H:i:s'),
        ':machine_description' => $data['machineDescription']
    ]);

    $machineId = $conn->lastInsertId();

    // Step 2: Insert parts
    foreach ($data['selectedParts'] as $part) {
        $installationDate = date('Y-m-d H:i:s');
        // Calculate lifespan in operational days
        $lifespanInOperationalDays = floor($part['replacementLifespan'] / 12); // Or ceil() / round()

        // Debug operational days
        error_log("Lifespan in Operational Days (Rounded): $lifespanInOperationalDays");

        // Calculate replacement date
        $replacementDate = date('Y-m-d H:i:s', strtotime("+$lifespanInOperationalDays days", strtotime($installationDate)));

        // Debug replacement date
        error_log("Replacement Date (Rounded): $replacementDate");

        $stmt = $conn->prepare("
            INSERT INTO machine_parts (
                machine_id,
                machine_parts_name,
                machine_parts_count,
                machine_parts_description,
                machine_part_criticality_level,
                machine_part_maintenance_interval,
                machine_part_replacement_lifespan,
                machine_part_maintenance_instructions,
                machine_parts_status,
                machine_parts_installation_date,
                machine_parts_replacement_date,
                machine_parts_replacement_reason,
                machine_parts_replacement_description,
                machine_parts_replacement_maintenance_hours,
                current_operating_hours,
                disposable,
                warranty_covered
            ) 
            VALUES (
                :machine_id,
                :machine_parts_name,
                :machine_parts_count,
                :machine_parts_description,
                :machine_part_criticality_level,
                :machine_part_maintenance_interval,
                :machine_part_replacement_lifespan,
                :machine_part_maintenance_instructions,
                :machine_parts_status,
                :machine_parts_installation_date,
                :machine_parts_replacement_date,
                :machine_parts_replacement_reason,
                :machine_parts_replacement_description,
                :machine_parts_replacement_maintenance_hours,
                :current_operating_hours,
                :disposable,
                :warranty_covered
            )
        ");
        $stmt->execute([
            ':machine_id' => $machineId,
            ':machine_parts_name' => $part['name'],
            ':machine_parts_count' => $part['quantity'],
            ':machine_parts_description' => $part['description'],
            ':machine_part_criticality_level' => $part['criticalityLevel'],
            ':machine_part_maintenance_interval' => $part['maintenanceInterval'],
            ':machine_part_replacement_lifespan' => $part['replacementLifespan'],
            ':machine_part_maintenance_instructions' => $part['instructions'],
            ':machine_parts_status' => "In use",
            ':machine_parts_installation_date' => $installationDate,
            ':machine_parts_replacement_date' => $replacementDate,
            ':machine_parts_replacement_reason' => "New",
            ':machine_parts_replacement_description' => "Working",
            ':machine_parts_replacement_maintenance_hours' => $part['maintenanceInterval'],
            ':current_operating_hours' => 0,
            ':disposable' => 1,
            ':warranty_covered' => $part['warrantyCovered'] === 'True' ? 1 : 0
        ]);
    }

    // Step 3: Insert warranty if enabled
    if ($data['warrantyEnabled'] && isset($data['warranty'])) {
        $stmt = $conn->prepare("
            INSERT INTO warranty (
                machine_id,
                warranty_start_date,
                warranty_end_date,
                warranty_provider,
                warranty_contact_info,
                contact_person,
                contact_email,
                warranty_terms_and_conditions,
                warranty_status,
                warranty_other_services,
                warranty_document,
                warranty_notification_email,
                warranty_notification_phone,
                warranty_notification_maintenance,
                warranty_notification_expiration
            ) VALUES (
                :machine_id,
                :warranty_start_date,
                :warranty_end_date,
                :warranty_provider,
                :warranty_contact_info,
                :contact_person,
                :contact_email,
                :warranty_terms_and_conditions,
                :warranty_status,
                :warranty_other_services,
                :warranty_document,
                :warranty_notification_email,
                :warranty_notification_phone,
                :warranty_notification_maintenance,
                :warranty_notification_expiration
            )
        ");
        $stmt->execute([
            ':machine_id' => $machineId,
            ':warranty_start_date' => $data['warranty']['startDate'],
            ':warranty_end_date' => $data['warranty']['expirationDate'],
            ':warranty_provider' => $manufacturerId,
            ':warranty_contact_info' => $data['warranty']['contactNumber'],
            ':contact_person' => $data['warranty']['contactPerson'],
            ':contact_email' => $data['warranty']['contactEmail'],
            ':warranty_terms_and_conditions' => $data['warranty']['termsConditions'],
            ':warranty_status' => "Active",
            ':warranty_other_services' => $data['warranty']['otherServices'] ?? null,
            ':warranty_document' => $warrantyDocumentPath, // File path for warranty document
            ':warranty_notification_email' => $data['notifications']['email'] ?? null,
            ':warranty_notification_phone' => null,
            ':warranty_notification_maintenance' => $data['notifications']['notifyDays'] ?? null,
            ':warranty_notification_expiration' => $data['notifications']['notifyWeeks'] ?? null
        ]);
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
