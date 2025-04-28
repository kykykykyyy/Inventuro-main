<?php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['employee_id']) && $_SESSION['user_type'] === "non-engineering") {

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

    <link rel="stylesheet" href="style.css">
    <title>Machines List</title>
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
                    <a href="#" class="sidebar-link collapsed has-dropdown active" data-bs-toggle="collapse"
                        data-bs-target="#machines" aria-expanded="false" aria-controls="machines">
                        <i class="bi bi-tools"></i>
                        <span>Repair</span>
                    </a>
                    <ul id="machines" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="index.php" class="sidebar-link">View Machines List</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="../request/index.php" class="sidebar-link">Create a Request</a>
                        </li>
                    </ul>
                </li>
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
            </div>
            <div id="main-content">
                <!-- Repair Request Section -->
                <div id="request-content" class="content-section active p-5">
                    <h1><strong>View Machine List</strong></h1>
                    <p>Check if a machine in your department is under repair.</p>

                    <table id="machinesTable" class="table table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th class="text-start" style="padding-left: 13px;">Machine Name</th>
                                <th class="text-start" style="padding-left: 13px;">Serial Number</th>
                                <th class="text-start" style="padding-left: 13px;">Under Repair?</th>
                                <th class="text-start" style="padding-left: 13px;">Urgency</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            try {
                                $sql = "SELECT * FROM machine
                                LEFT JOIN repair_request ON repair_request.machine_id = machine.machine_id
                                AND repair_request.date_requested = (
                                    SELECT MAX(repair_request.date_requested)
                                    FROM repair_request
                                    WHERE repair_request.machine_id = machine.machine_id
                                )
                                LEFT JOIN department ON machine.machine_department_id = department.department_id
                                LEFT JOIN urgency ON machine.machine_urgency = urgency.id
                                WHERE department.department_id = ?
                                ORDER BY repair_request.date_requested ASC";

                                // Prepare the statement
                                $stmt = $conn->prepare($sql);

                                // Bind the parameter
                                $stmt->bindParam(1, $department, PDO::PARAM_INT);

                                // Execute the query
                                $stmt->execute();
                                
                                // Fetch data and display
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $imageData = isset($row['image']) && !empty($row['image'])
                                        ? "data:" . (new finfo(FILEINFO_MIME_TYPE))->buffer($row['image']) . ";base64," . base64_encode($row['image'])
                                        : "../../../images/gallery.png";
                                        $urgencyClass = $row['name'] === 'High' ? 'text-danger' : ($row['name'] === 'Medium' ? 'text-warning' : 'text-success');
                                    // Construct the table row
                                    echo "<tr>
                                        <td class='text-start align-middle'>" . "<img src='" . htmlspecialchars($imageData) . "' class='img-fluid' alt='Machine Image' style='width: 40px; height: 40px; object-fit: cover;'>" . " " . htmlspecialchars($row['machine_name']) . "</td>
                                        <td class='text-start align-middle'>" . htmlspecialchars($row['machine_serial_number']) . "</td>
                                        <td class='text-start align-middle'>" . htmlspecialchars($row['status'] === "Done" ? "No" : "Yes") . "</td>
                                        <td class='text-start align-middle $urgencyClass'>" . htmlspecialchars($row['name']) . "</td>
                                    </tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='4'>Error fetching data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
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

    <script src="script.js"></script>
</body>
</html>
<?php
} else {
    header(header: "Location: ../../../login.php");}
?>