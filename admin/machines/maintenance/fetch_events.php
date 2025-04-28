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
$sql = "SELECT machine_id, maintenance_scheduled_date, machine_description 
        FROM maintenance";
$stmt = $conn->prepare($sql);
$stmt->execute();

// Fetch the results
$events = [];
$query = "SELECT maintenance.*, 
       GROUP_CONCAT(machine.machine_id) AS machine_ids
FROM maintenance 
LEFT JOIN machine ON maintenance.machine_id = machine.machine_id 
GROUP BY maintenance.maintenance_id;";

$result = $conn->query($query);

date_default_timezone_set('Asia/Manila'); // Change this to your local timezone

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $events[] = [
        'machine_id' => $row['machine_id'],
        'machine_description' => $row['machine_description'],
        'maintenance_scheduled_date' => date('Y-m-d H:i:s', strtotime($row['maintenance_scheduled_date'])), // Convert to local time
        'machine_ids' => explode(',', $row['machine_ids']) // Convert comma-separated string to array
    ];
}

echo json_encode($events);
?>
