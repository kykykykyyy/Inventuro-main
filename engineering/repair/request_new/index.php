<?php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['employee_id']) && $_SESSION['user_type'] === "engineering") {

// Include the database connection file
include '../../../connect.php';

// SQL query to get the most recent BLOB from the image column
$stmt = $conn->prepare("
    SELECT *
    FROM users 
    JOIN employee
    ON users.employee_id = employee.employee_id
    WHERE users.user_id = ?
    LIMIT 1
");

$stmt->execute([$_SESSION['user_id']]);

// Fetch the result
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if a result was found and store the image data
if ($user) {
    $profileImage = $user['image'];

    // Detect MIME type of the image
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->buffer($profileImage);

    // Convert BLOB to base64
    $base64Image = base64_encode($profileImage);

    $first_name = $user['first_name'];
    $last_name = $user['last_name'];
    $employee_id = $user['employee_id'];
    $department = $user['department'];
    $date_created = $user['date_created'];

    $departmentStmt = $conn->prepare("
    SELECT department_name
    FROM department
    WHERE department_id=?
    LIMIT 1
    ");

    $departmentStmt->execute([$department]);

    // Fetch the result
    $departmentName = $departmentStmt->fetch(PDO::FETCH_ASSOC);

    $department_name = $departmentName['department_name'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="style.css">

    <title>Repair</title>
</head>
<body>
    <div class="wrapper">
        <aside id="sidebar" class="expand">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <i class="bi bi-box-seam-fill"></i>
                </button>
                <div class="sidebar-logo">
                    <a href="../../index.php">Inventuro</a>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="../../index.php" class="sidebar-link">
                    <i class="bi bi-house-door"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="index.php" class="sidebar-link collapsed has-dropdown active" data-bs-toggle="collapse"
                        data-bs-target="#repair" aria-expanded="false" aria-controls="repair">
                        <i class="bi bi-tools"></i>
                        <span>Repair</span>
                    </a>
                    <ul id="repair" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="index.php" class="sidebar-link">Claim a Repair</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="../claimed/index.php" class="sidebar-link">Your Claimed Repairs</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="../../maintenance/list/index.php" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#maintenance" aria-expanded="false" aria-controls="maintenance">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#224d7a"><path d="M159-120v-120h124L181-574q-27-15-44.5-44T119-680q0-50 35-85t85-35q39 0 69.5 22.5T351-720h128v-40q0-17 11.5-28.5T519-800q9 0 17.5 4t14.5 12l68-64q9-9 21.5-11.5T665-856l156 72q12 6 16.5 17.5T837-744q-6 12-17.5 15.5T797-730l-144-66-94 88v56l94 86 144-66q11-5 23-1t17 15q6 12 1 23t-17 17l-156 74q-12 6-24.5 3.5T619-512l-68-64q-6 6-14.5 11t-17.5 5q-17 0-28.5-11.5T479-600v-40H351q-3 8-6.5 15t-9.5 15l200 370h144v120H159Zm80-520q17 0 28.5-11.5T279-680q0-17-11.5-28.5T239-720q-17 0-28.5 11.5T199-680q0 17 11.5 28.5T239-640Zm126 400h78L271-560h-4l98 320Zm78 0Z"/></svg>
                        <span>Maintenance</span>
                    </a>
                    <ul id="maintenance" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="../../maintenance/list/index.php" class="sidebar-link">List</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="../../maintenance/request_material/index.php" class="sidebar-link">Request Material</a>
                        </li>
                    </ul>
                </li>
                <!-- <li class="sidebar-item">
                    <a href="../../history/index.php" class="sidebar-link">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#224d7a"><path d="M13 3a9 9 0 0 0-9 9H2l3.89 3.89.07.14L10 12H7a7 7 0 1 1 7 7 7.07 7.07 0 0 1-6-3H6.26a8.99 8.99 0 0 0 7.74 5 9 9 0 1 0 0-18Zm-1 5v6h6v-2h-4V8Z"/></svg>
                        <span>History</span>
                    </a>
                </li> -->
                <li class="sidebar-item">
                    <a href="../../profile/index.php" class="sidebar-link">
                    <i class="bi bi-person"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="../../../logout.php" class="sidebar-link">
                    <i class="lni lni-exit"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>
        <div class="main">
            <nav class="navbar navbar-expand-lg navbar-custom px-4">
                <div class="d-flex flex-grow-1 align-items-center p-2">
                    <form class="d-flex flex-grow-1" role="search">
                    <input id="search-bar" class="form-control me-2" type="search" placeholder="Search..." aria-label="Search" style="width: 600px; height: 30px; font-size: 16px;">
                    <button type="button" class="icon-btn" title="Search">
                        <i class="bi bi-search"></i>
                    </button>
                    </form>
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <button class="icon-btn" title="Notifications">
                                <i class="bi bi-bell"></i>
                            </button>
                        </li>
                        <li class="nav-item mx-3">
                            <button class="icon-btn" title="Settings">
                                <i class="bi bi-gear"></i>
                            </button>
                        </li>
                        <button class="icon-btn" title="Profile" style="border: none; background: none; padding: 0;">
                            <?php if (isset($base64Image)): ?>
                                <img src="data:<?=$mimeType?>;base64,<?=$base64Image?>"
                                    alt="Profile Picture" 
                                    class="profile-icon" style="height: 1.7rem; width: 1.7rem">
                            <?php else: ?>
                                <img src="../../../images/person-circle.png"
                                    alt="Profile Picture" 
                                    class="profile-icon">
                            <?php endif; ?>
                        </button>
                    </ul>
                </div>
            </nav>
            <div class="second-nav">
                <div class="container p-2">
                    <div class="box left-box">
                        <img src="../../../images/ksk-logo.png" alt="KSK Logo" style="max-width: 100%; height: auto;">
                    </div>
                    <div class="box right-box">
                        <div class="row">
                            <div class="col-8">
                                <h1 class="name-display">Hello, <?=$first_name?> <?=$last_name?></h1>
                                <h5>KSK Food Products</h5>
                            </div>
                            <div class="col-4">
                                <p><strong>Employee ID:</strong> <span id="employee-id-text"><?= $employee_id ?></span></p>
                                <p><strong>Department: </strong><?=$department_name?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="main-content-links">
                    <a id="unclaimed-link" class="link-hover-effect text-primary active" href="#" style="text-decoration: none;">Unclaimed</a>
                    <a id="claimed-link" class="link-hover-effect text-primary" href="#" style="text-decoration: none;">Claimed</a>
                    <a id="under-warranty-link" class="link-hover-effect text-primary" href="#" style="text-decoration: none;">Under Warranty</a>
                </div>
            </div>
            <div id="main-content">
                <!-- Active Repair Requests Content Section -->
                <div id="unclaimed-content" class="content-section active"> <!-- Ensure this is set as active -->
                    <div class="m-4 ml-5 mb-0">
                        <h1><strong>Unclaimed Repair Requests</strong></h1>
                        <p>Select an open repair request to claim.</p>
                    </div>
                    <table id="unclaimedRepairs" class="table table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">
                                    <input type="checkbox" id="selectAllClaimed">
                                </th>
                                <th class="text-start">Date Requested</th>
                                <th class="text-start">Repair Request No.</th>
                                <th class="text-start">Machine</th>
                                <th class="text-start">Serial No.</th>
                                <th class="text-start">Department</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                include '../../../connect.php'; // Include database connection
                                $sql = "SELECT
                                    repair_request.*,
                                    repair_request.repair_request_id AS r_repair_request_id,
                                    employee.*, 
                                    machine.*,
                                    department.*,
                                    warranty.*
                                FROM repair_request
                                JOIN employee ON repair_request.requested_by = employee.employee_id
                                JOIN machine ON repair_request.machine_id = machine.machine_id
                                JOIN department ON machine.machine_department_id = department.department_id
                                LEFT JOIN warranty ON machine.machine_id = warranty.machine_id
                                WHERE 
                                repair_request.status = 'Not Started'
                                AND warranty.warranty_status IS NULL
                                AND repair_request.date_repaired IS NULL
                                ORDER BY 
                                    repair_request.date_requested ASC";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute();

                                // Check if any rows were returned
                                if ($stmt->rowCount() > 0) {
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        // Define status and urgency classes
                                        $statusClass = $row['status'] === 'Not Started' ? 'text-secondary' : ($row['status'] === 'Started' ? 'text-warning' : 'text-success');

                                        echo "<tr 
                                            data-repair-id='" . htmlspecialchars($row['repair_id'] ?? '') . "' 
                                            data-repair-request-id='" . htmlspecialchars($row['r_repair_request_id']) . "' 
                                            data-date-requested='" . htmlspecialchars($row['date_requested']) . "'
                                            data-machine-name='" . htmlspecialchars($row['machine_name']) . "'
                                            data-machine-serial-number='" . htmlspecialchars($row['machine_serial_number']) . "'
                                            data-department='" . htmlspecialchars($row['department_name']) . "'
                                            data-status='" . htmlspecialchars($row['status']) . "'
                                            data-requested-by='" .  htmlspecialchars($row['requested_by']) ."'
                                            data-requested-by-name='" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ."'
                                            data-warranty-status='" . htmlspecialchars($row['warranty_status'] ?? 'No warranty') . "'
                                            data-details='" . htmlspecialchars($row['details']) . "'
                                            data-repaired-by='" .  htmlspecialchars($row['repaired_by'] ?? $employee_id) ."'>
                                            <td class='text-center'><input type='checkbox' class='row-checkbox'></td>
                                            <td>" . htmlspecialchars(date("d M Y g:i A", strtotime($row['date_requested'] ?? ''))) . "</td>
                                            <td>" . htmlspecialchars($row['r_repair_request_id'] ?? '') . "</td>
                                            <td>" . htmlspecialchars($row['machine_name'] ?? '') . "</td>
                                            <td>" . htmlspecialchars($row['machine_serial_number'] ?? '') . "</td>
                                            <td>" . htmlspecialchars($row['department_name'] ?? '') . "</td>
                                            <td class='text-start'>
                                                <button class='btn btn-sm btn-primary' id='viewRepairRequest'>View</button>
                                            </td>
                                        </tr>";
                                    }
                                } else {
                                    // Placeholder row with data-dt-no-data attribute
                                    echo "<tr data-dt-no-data><td class='text-center' colspan='8'>No repair requests found.</td></tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='8'>Error fetching data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <!-- Diagnosis Repair Section -->
                <div id="claimed-content" class="content-section">
                    <div class="m-4 ml-5 mb-0">
                        <h1><strong>Claimed Repair Requests</strong></h1>
                        <p>View an active repair request that has been claimed.</p>
                    </div>
                    <table id="claimedRepairs" class="table table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">
                                    <input type="checkbox" id="selectAllClaimed">
                                </th>
                                <th class="text-start">Date Requested</th>
                                <th class="text-start">Repair Request No.</th>
                                <th class="text-start">Machine</th>
                                <th class="text-start">Serial No.</th>
                                <th class="text-start">Department</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                include '../../../connect.php'; // Include database connection
                                $sql = "SELECT 
                                    repair_request.*, 
                                    repair_request.repair_request_id AS r_repair_request_id, 
                                    repair.*,
                                    employee.*, 
                                    machine.*, 
                                    department.*, 
                                    warranty.*
                                FROM 
                                    repair_request 
                                LEFT JOIN 
                                    repair ON repair_request.repair_request_id = repair.repair_request_id
                                LEFT JOIN 
                                    employee ON repair_request.requested_by = employee.employee_id
                                LEFT JOIN 
                                    machine ON repair_request.machine_id = machine.machine_id
                                LEFT JOIN 
                                    department ON machine.machine_department_id = department.department_id
                                LEFT JOIN 
                                    warranty ON machine.machine_id = warranty.machine_id
                                WHERE 
                                    repair_request.status = 'Started'
                                ORDER BY 
                                    repair_request.date_requested ASC";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute();

                                // Check if any rows were returned
                                if ($stmt->rowCount() > 0) {
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        // Define status and urgency classes
                                        $statusClass = $row['status'] === 'Not Started' ? 'text-secondary' : ($row['status'] === 'Started' ? 'text-warning' : 'text-success');

                                        // Check if repair_date is not empty or null
                                        $repairDate = htmlspecialchars($row['repair_date'] ?? '');
                                        $unclaimButtonVisibility = !empty($repairDate) ? 'style="display:none;"' : '';
                                        $completeButtonVisbility = empty($repairDate) ? 'style="display:none;"' : '';

                                        echo "<tr 
                                            data-repair-id='" . htmlspecialchars($row['repair_id']) . "' 
                                            data-date-requested='" . htmlspecialchars($row['date_requested']) . "'
                                            data-machine-name='" . htmlspecialchars($row['machine_name']) . "'
                                            data-machine-serial-number='" . htmlspecialchars($row['machine_serial_number']) . "'
                                            data-department='" . htmlspecialchars($row['department_name']) . "'
                                            data-status='" . htmlspecialchars($row['status']) . "'
                                            data-repair-date='" . htmlspecialchars($row['repair_date'] ?? '') . "'
                                            data-requested-by='" .  htmlspecialchars($row['requested_by']) ."'
                                            data-requested-by-name='" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ."'
                                            data-warranty-status='" . htmlspecialchars($row['warranty_status'] ?? 'No warranty') . "'
                                            data-details='" . htmlspecialchars($row['details']) . "'
                                            data-repaired-by='" .  htmlspecialchars($row['repaired_by'] ?? $employee_id) ."'>
                                            <td class='text-center'><input type='checkbox' class='row-checkbox'></td>
                                            <td>" . htmlspecialchars(date("d M Y g:i A", strtotime($row['date_requested'] ?? ''))) . "</td>
                                            <td>" . htmlspecialchars($row['repair_id'] ?? '') . "</td>
                                            <td>" . htmlspecialchars($row['machine_name'] ?? '') . "</td>
                                            <td>" . htmlspecialchars($row['machine_serial_number'] ?? '') . "</td>
                                            <td>" . htmlspecialchars($row['department_name'] ?? '') . "</td>
                                        </tr>";
                                    }
                                } else {
                                    // Placeholder row with data-dt-no-data attribute
                                    echo "<tr data-dt-no-data><td class='text-center' colspan='8'>No repair requests found.</td></tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='8'>Error fetching data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    
                </div>
                <!-- Inactive Repairs Content Section -->
                <div id="under-warranty-content" class="content-section">
                    <div class="m-4 ml-5">
                        <h1><strong>Under Warranty Repair Request</strong></h1>
                        <p>Click on a row to view its details.</p>
                    </div>
                    <table id="warrantyRepairs" class="table table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">
                                    <input type="checkbox" id="selectAllClaimed">
                                </th>
                                <th class="text-start">Date Requested</th>
                                <th class="text-start">Repair Request No.</th>
                                <th class="text-start">Machine</th>
                                <th class="text-start">Serial Number</th>
                                <th class="text-start">Department</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                include '../../../connect.php'; // Include database connection
                                $sql = "SELECT
                                repair_request.*,
                                repair_request.repair_request_id AS r_repair_request_id,
                                employee.*, 
                                machine.*,
                                department.*,
                                warranty.*
                                FROM repair_request
                                JOIN employee ON repair_request.requested_by = employee.employee_id
                                JOIN machine ON repair_request.machine_id = machine.machine_id
                                JOIN department ON machine.machine_department_id = department.department_id
                                JOIN warranty ON machine.machine_id = warranty.machine_id
                                WHERE 
                                repair_request.status != 'Done'
                                AND repair_request.date_repaired IS NULL
                                AND warranty.warranty_status IS NOT NULL
                                ORDER BY 
                                    repair_request.date_requested ASC";

                                $stmt = $conn->prepare($sql);
                                $stmt->execute();

                                // Check if any rows were returned
                                if ($stmt->rowCount() > 0) {
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                                        echo "<tr 
                                            data-repair-id='" . htmlspecialchars($row['r_repair_request_id']) . "' 
                                            data-date-requested='" . htmlspecialchars($row['date_requested']) . "'
                                            data-machine-name='" . htmlspecialchars($row['machine_name']) . "'
                                            data-machine-serial-number='" . htmlspecialchars($row['machine_serial_number']) . "'
                                            data-department='" . htmlspecialchars($row['department_name']) . "'
                                            data-status='" . htmlspecialchars($row['status']) . "'
                                            data-requested-by='" .  htmlspecialchars($row['requested_by']) ."'
                                            data-requested-by-name='" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ."'
                                            data-warranty-status='" . htmlspecialchars($row['warranty_status'] ?? 'No warranty') . "'
                                            data-details='" . htmlspecialchars($row['details']) . "'>
                                            <td class='text-center'><input type='checkbox' class='row-checkbox'></td>
                                            <td>" . htmlspecialchars(date("d M Y g:i A", strtotime($row['date_repaired'] ?? ''))) . "</td>
                                            <td>" . htmlspecialchars($row['r_repair_request_id'] ?? '') . "</td>
                                            <td>" . htmlspecialchars($row['machine_name'] ?? '') . "</td>
                                            <td>" . htmlspecialchars($row['machine_serial_number'] ?? '') . "</td>
                                            <td>" . htmlspecialchars($row['department_name'] ?? '') . "</td>
                                        </tr>";
                                    }
                                } else {
                                    // Placeholder row with data-dt-no-data attribute
                                    echo "<tr data-dt-no-data><td class='text-center' colspan='8'>No repair requests found.</td></tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='8'>Error fetching data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!--Info Modal -->
    <div class="modal fade" tabindex="-1" role="dialog" id="infoModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modal title</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Modal body text goes here.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="confirmationModalLabel">Modal title</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <p id="confirmationModalBody">Modal body text goes here.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
            <button type="button" class="btn btn-primary" id="confirmButton">Yes</button>
        </div>
        </div>
    </div>
    </div>

    <!-- Offcanvas Modal for Repair Request Details -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="repairRequestModal" aria-labelledby="repairRequestModalLabel" style="width: 40%; padding: 20px;">
        <div class="offcanvas-header">
            <h5 id="repairRequestModalLabel">Repair Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        
        <div class="offcanvas-body">
            <p><strong>Date Requested:</strong> <span id="modalDateRequested"></span></p>
            <p><strong>Repair Request No.:</strong> <span id="repairRequestIdLabel"></span></p>
            <p><strong>Machine:</strong> <span id="modalMachineName"></span></p>
            <p><strong>Serial Number:</strong> <span id="modalMachineSerialNumber"></span></p>
            <p><strong>Department:</strong> <span id="modalDepartment"></span></p>
            <p><strong>Status:</strong> <span id="modalStatus"></span></p>
            <p><strong>Requested By:</strong> <span id="modalRequestedBy"></span></p>
            <p><strong>Details:</strong> <span id="modalDetails"></span></p>
            <p style="display: none; font-size: 15px; max-width: 43%; padding: 10px;" class="badge bg-warning text-center">
                <i class="bi bi-info-circle"></i>
                <strong>Warranty Status:</strong>
                <span id="modalWarranty"></span>
            </p>

            <div class="d-flex justify-content-start" style="padding-top: 20px">
                <button id="claimButton" class="btn btn-primary me-2">Claim</button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>

    <!-- Include jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

    <script>
    function previewProfilePicture(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profile-picture').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
    </script>
    
    <script src="script.js"></script>
</body>
</html>
<?php
} else {
    header(header: "Location: ../../../login.php");}
?>