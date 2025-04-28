<?php
// Include your database connection file
include_once '../../../connect.php';

// Ensure the database connection is initialized
if (!$conn) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed.']);
    exit();
}

// Prepare and execute the SQL query to fetch maintenance records
$sql = "SELECT maintenance_id, maintenance.machine_parts_id, maintenance_scheduled_date
		FROM maintenance
        LEFT JOIN machine_parts ON maintenance.machine_parts_id = machine_parts.machine_parts_id
        LEFT JOIN machine ON machine_parts.machine_id = machine.machine_id
        ORDER BY machine.machine_id, maintenance_scheduled_date ASC;";
$stmt = $conn->prepare($sql);
$stmt->execute();

// Fetch the results
$events = [];

// Set the timezone (Asia/Manila for this example)
date_default_timezone_set('Asia/Manila'); 

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Format the maintenance scheduled date to ISO 8601 format for FullCalendar
    $maintenanceDate = date('Y-m-d\TH:i:s', strtotime($row['maintenance_scheduled_date']));

    // Prepare the event data
    $events[] = [
        'title' => 'Maintenance No. ' . $row['maintenance_id'],  // Customize the title as needed
        'start' => $maintenanceDate,  // FullCalendar expects the start date in ISO 8601 format
        'machine_parts_id' => $row['machine_parts_id']  // You can store this ID for later use if needed
    ];
}

// Output the events in JSON format
echo json_encode($events);
?>