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
    $middle_name = $user['middle_name'];
    $last_name = $user['last_name'];
    $employee_id = $user['employee_id'];
    $department = $user['department'];
    $role = $user['role'];
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

    $department = $departmentName['department_name'];
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

    <title>Maintenance</title>
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
                    <a href="../../repair/request_new/index.php" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#repair" aria-expanded="false" aria-controls="repair">
                        <i class="bi bi-tools"></i>
                        <span>Repair</span>
                    </a>
                    <ul id="repair" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="../../repair/request_new/index.php" class="sidebar-link">Claim a Repair</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="../../repair/claimed/index.php" class="sidebar-link">Your Claimed Repairs</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="../../maintenance/list/index.php" class="sidebar-link collapsed has-dropdown active" data-bs-toggle="collapse"
                        data-bs-target="#maintenance" aria-expanded="false" aria-controls="maintenance">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#224d7a"><path d="M159-120v-120h124L181-574q-27-15-44.5-44T119-680q0-50 35-85t85-35q39 0 69.5 22.5T351-720h128v-40q0-17 11.5-28.5T519-800q9 0 17.5 4t14.5 12l68-64q9-9 21.5-11.5T665-856l156 72q12 6 16.5 17.5T837-744q-6 12-17.5 15.5T797-730l-144-66-94 88v56l94 86 144-66q11-5 23-1t17 15q6 12 1 23t-17 17l-156 74q-12 6-24.5 3.5T619-512l-68-64q-6 6-14.5 11t-17.5 5q-17 0-28.5-11.5T479-600v-40H351q-3 8-6.5 15t-9.5 15l200 370h144v120H159Zm80-520q17 0 28.5-11.5T279-680q0-17-11.5-28.5T239-720q-17 0-28.5 11.5T199-680q0 17 11.5 28.5T239-640Zm126 400h78L271-560h-4l98 320Zm78 0Z"/></svg>
                        <span>Maintenance</span>
                    </a>
                    <ul id="maintenance" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="index.php" class="sidebar-link">List</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="index.php" class="sidebar-link">Request Material</a>
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
                                <p><strong>Department: </strong><?=$department?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="main-content-links">
                    <a id="request-link" class="link-hover-effect text-primary active" href="#">List</a>
                    <a id="materials-link" class="link-hover-effect text-primary" href="#">Materials</a>
                </div>
            </div>
            <div id="main-content">
                <!-- Request Content Section -->
                <div id="request-content" class="content-section active"> <!-- Ensure this is set as active -->
                    <div class="m-4 ml-5">
                        <h1><strong>Machine Maintenance List</strong></h1>
                        <p>Select a machine maintenance to claim.</p>
                    </div>
                    <!-- History Table -->
                    <table id="historyTable" class="table table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th class="text-start">Date</th>
                                <th class="text-start">Maintenance No.</th>
                                <th class="text-start">Machine</th>
                                <th class="text-start">Department</th>
                                <th class="text-start">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                include '../../../connect.php'; // Include database connection
                                $sql = "SELECT 
                                        maintenance.maintenance_id, 
                                        maintenance.machine_parts_id, 
                                        maintenance.maintenance_scheduled_date, 
                                        maintenance.maintenance_status, 
                                        machine.machine_id,
                                        machine.machine_name,
                                        machine.machine_department_id,
                                        department.department_name,
                                        warranty.warranty_status
                                    FROM 
                                        maintenance
                                    LEFT JOIN
                                        machine_parts ON maintenance.machine_parts_id = machine_parts.machine_parts_id
                                    LEFT JOIN 
                                        machine ON machine.machine_id = machine_parts.machine_id
                                    LEFT JOIN
                                        department ON department.department_id = machine.machine_department_id
                                    LEFT JOIN
                                        warranty ON warranty.machine_id = machine.machine_id";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute();

                                // Check if any rows were returned
                                if ($stmt->rowCount() > 0) {
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        // Define status and urgency classes
                                        $statusClass = $row['maintenance_status'] === 'Scheduled' ? 'text-secondary' : ($row['maintenance_status'] === 'Done' ? 'text-success' : 'text-warning');

                                        echo "<tr 
                                        data-maintenance-id='" . htmlspecialchars($row['maintenance_id']) . "' 
                                        data-date-requested='" . htmlspecialchars($row['maintenance_scheduled_date']) . "'
                                        data-machine-name='" . htmlspecialchars($row['machine_name']) . "'
                                        data-department='" . htmlspecialchars($row['department_name']) . "'
                                        data-status='" . htmlspecialchars($row['maintenance_status']) . "'
                                        data-warranty-status='" . htmlspecialchars($row['warranty_status'] ?? 'No warranty') . "'>
                                        
                                        <td class='text-center'><input type='checkbox' class='row-checkbox'></td>
                                        <td>" . htmlspecialchars(date("d M Y g:i A", strtotime($row['maintenance_scheduled_date'] ?? ''))) . "</td>
                                        <td>" . htmlspecialchars($row['maintenance_id'] ?? '') . "</td>
                                        <td>" . htmlspecialchars($row['machine_name'] ?? '') . "</td>
                                        <td>" . htmlspecialchars($row['department_name'] ?? '') . "</td>
                                        <td class='$statusClass'>" . htmlspecialchars($row['maintenance_status'] ?? '') . "</td>
                                        <td class='text-center'>";
                                        
                                    // Always show "View" button
                                    echo "<button class='btn btn-sm btn-primary' id='view-details-btn' data-maintenance-id='" . htmlspecialchars($row['maintenance_id']) . "'>View</button> ";

                                    // Show "Done" button only if status is "Assigned"
                                    if ($row['maintenance_status'] == 'Assigned') {
                                        echo "<button class='btn btn-sm btn-success' id='done-btn' data-maintenance-id='" . htmlspecialchars($row['maintenance_id']) . "'>Done</button>";
                                    }

                                    echo "</td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8'>No maintenance found.</td></tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='8'>Error fetching data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <!-- Materials Content Section -->
                <div id="materials-content" class="content-section">
                    <div class="m-4 ml-5">
                        <h1><strong>Your Material Requests</strong></h1>
                        <p>Click a material request to see info.</p>
                    </div>
                    <!-- Material Requests Table -->
                    <table id="materialRequestTable" class="table table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th class="text-start">Date Requested</th>
                                <th class="text-start">Material Request No.</th>
                                <th class="text-start">Status</th>
                                <th class="text-start">Machine</th>
                                <th class="text-start">Department</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        try {
                            include '../../../connect.php'; // Include database connection
                            
                            $sql = "SELECT 
                                    material_request.material_request_id AS m_material_request_id,
                                    material_request.maintenance_id AS m_maintenance_id,
                                    material_request.timestamp AS m_date_requested,
                                    material_request.status AS m_status,
                                    material_request.requested_by AS m_requested_by,
                                    material_request.approved_by AS m_approved_by,
                                    machine.*,
                                    department.*
                                    FROM material_request
                                    LEFT JOIN material_request_items ON material_request.material_request_id = material_request_items.material_request_id
                                    LEFT JOIN machine_parts ON material_request_items.item_id = machine_parts.machine_parts_id
                                    LEFT JOIN maintenance ON maintenance.maintenance_id = material_request.maintenance_id
                                    LEFT JOIN machine ON machine_parts.machine_id = machine.machine_id
                                    LEFT JOIN department ON machine.machine_department_id = department.department_id
                                    WHERE material_request.requested_by = ? AND material_request.maintenance_id IS NOT NULL
                                    ORDER BY m_date_requested DESC"; // Make sure to refer to the correct alias
                            
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$employee_id]); // Bind the parameter correctly as an array

                            // Check if any rows were returned
                            if ($stmt->rowCount() > 0) {
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    // Define status and urgency classes
                                    $statusClass = isset($row['m_status']) ? ($row['m_status'] === 'Not Started' ? 'text-secondary' : ($row['m_status'] === 'Started' ? 'text-warning' : 'text-success')) : '';

                                    echo "<tr 
                                        data-material-request-id='" . htmlspecialchars($row['m_material_request_id']) . "' 
                                        data-date-requested='" . htmlspecialchars(date("d M Y g:i A", strtotime($row['m_date_requested'] ?? ''))) . "'
                                        data-status='" . htmlspecialchars($row['m_status'] ?? '') . "'
                                        data-machine-name='" . htmlspecialchars($row['machine_name'] ?? '') . "'
                                        data-item-code='" . htmlspecialchars($row['item_code'] ?? '') . "'
                                        data-department='" . htmlspecialchars($row['department_name'] ?? '') . "'>
                                        <td class='text-center'><input type='checkbox' class='row-checkbox'></td>
                                        <td>" . htmlspecialchars(date("d M Y g:i A", strtotime($row['m_date_requested'] ?? ''))) . "</td>
                                        <td>" . htmlspecialchars($row['m_material_request_id'] ?? '') . "</td>
                                        <td class='$statusClass'>" . htmlspecialchars($row['m_status'] ?? '') . "</td>
                                        <td>" . htmlspecialchars($row['machine_name'] ?? '') . "</td>
                                        <td>" . htmlspecialchars($row['department_name'] ?? '') . "</td>
                                        <td class='text-center'>
                                            <button class='btn btn-sm btn-primary' onclick='viewDetails(\"" . htmlspecialchars($row['m_maintenance_id']) . "\")'>View</button>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8'>No material requests found.</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='7'>Error fetching data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
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
    <!-- Offcanvas Modal for Repair Request Details -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="repairRequestModal" aria-labelledby="repairRequestModalLabel" style="width: 40%; padding: 20px;">
        <div class="offcanvas-header">
            <h5 id="repairRequestModalLabel">Maintenance Schedule Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        
        <div class="offcanvas-body">
            <p><strong>Maintenance Date:</strong> <span id="modalDateRequested"></span></p>
            <p><strong>Maintenance No.:</strong> <span id="repairRequestIdLabel"></span></p>
            <p><strong>Machine:</strong> <span id="modalMachineName"></span></p>
            <p><strong>Department:</strong> <span id="modalDepartment"></span></p>
            <p><strong>Status:</strong> <span id="modalStatus"></span></p>
            <p style="display: none; font-size: 15px; max-width: 43%; padding: 10px;" class="badge bg-warning text-center">
                <i class="bi bi-info-circle"></i>
                <strong>Warranty Status:</strong>
                <span id="modalWarranty"></span>
            </p>

            <div class="d-flex justify-content-start" style="padding-top: 20px">
                <button id="requestMaterialBtn" class="btn btn-primary me-2">Claim</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Cart Modal -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">Shopping Cart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-hover" id="cartTable">
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Available Quantity</th>
                                <th>Requested Quantity</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody id="cartModalBody">
                            <!-- Cart items will be injected here by JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="completeOrderBtn" class="btn btn-primary">Complete Order</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Offcanvas Modal for Material Request Details -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="materialRequestModal" aria-labelledby="materialRequestModalLabel" style="width: 40%; padding: 20px;">
        <div class="offcanvas-header">
            <div class="col-10">
                <h5 id="materialRequestModalLabel" class="mb-0">Material Request Details</h5>
                <p class="text-muted mb-0"><strong>Request No.:</strong> <span id="modalMaterialRequestId"></span></p>
            </div>
            <button class="btn btn-danger" id="deleteMaterialRequestBtn"><i class="bi bi-trash"></i></button>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body p-4">
            <p><strong>Date Requested:</strong> <span id="materialRequestDate"></span></p>
            <p><strong>Status:</strong> <span id="materialRequestStatus"></span></p>
            <p><strong>Machine:</strong> <span id="materialRequestMachine"></span></p>
            <p><strong>Department:</strong> <span id="materialRequestDepartment"></span></p>

            <div id="modalItemList" class="mt-3">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity Needed</th>
                        </tr>
                    </thead>
                    <tbody id="itemListBody">
                        <!-- Material request items will be populated here -->
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-start" style="padding-top: 20px">
                <button id="saveMaterialRequestBtn" class="btn btn-primary me-2">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Cancel</button>
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