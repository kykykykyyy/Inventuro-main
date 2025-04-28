<?php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['employee_id']) && $_SESSION['user_type'] === "non-engineering") {

// Include the database connection file
include '../connect.php';

date_default_timezone_set('Asia/Manila');

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

$stmt_requests = $conn->prepare("SET time_zone = '+08:00'");
$stmt_requests->execute();

// Fetch top 5 most recent requests for the user
$stmt_requests = $conn->prepare("
SELECT * 
FROM repair_request
JOIN machine ON repair_request.machine_id = machine.machine_id
WHERE repair_request.requested_by = ?
ORDER BY repair_request.date_requested DESC
LIMIT 5;
");

$stmt_requests->execute([$employee_id]); // Assuming 'requested_by' is employee_id
$recent_requests = $stmt_requests->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css">
    <title>Home</title>
</head>
<body>
    <div class="wrapper">
        <aside id="sidebar" class="expand">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <i class="bi bi-box-seam-fill"></i>
                </button>
                <div class="sidebar-logo">
                    <a href="index.php">Inventuro</a>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="index.php" class="sidebar-link active">
                    <i class="bi bi-house-door"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#machines" aria-expanded="false" aria-controls="machines">
                        <i class="bi bi-tools"></i>
                        <span>Repair</span>
                    </a>
                    <ul id="machines" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="repair/list/index.php" class="sidebar-link">View Machines List</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="repair/request/index.php" class="sidebar-link">Create a Request</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="profile/index.php" class="sidebar-link">
                    <i class="bi bi-person"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="../logout.php" class="sidebar-link">
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
                        <img src="../images/ksk-logo.png" alt="KSK Logo" style="max-width: 100%; height: auto;">
                    </div>
                    <div class="box right-box">
                        <div class="row">
                            <div class="col-8">
                                <h1 class="name-display">Hello, <?=$first_name?> <?=$last_name?></h1>
                                <h5>KSK Food Products</h5>
                            </div>
                            <div class="col-4">
                                <p><strong>Employee ID: </strong><?=$employee_id?></p>
                                <p><strong>Department: </strong><?=$department_name?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="main-content-links">
                    <a id="dashboard-link" class="link-hover-effect text-primary" href="#">Dashboard</a>
                    <a id="announcements-link" class="link-hover-effect text-primary" href="#">Announcement</a>
                </div>
            </div>
            <div id="main-content" class="p-5">
                <!-- Dashboard Content Section -->
                <div id="dashboard-content" class="content-section active">
                    <h1>Recent Requests</h1>
                    <ul class="list-group">
                        <?php foreach ($recent_requests as $request): 
                            // Determine status color class
                            $statusClass = '';
                            $statusText = htmlspecialchars($request['status']);
                            switch ($statusText) {
                                case 'Not Started':
                                    $statusClass = 'bg-light text-secondary p-1 rounded-pill'; // Gray
                                    break;
                                case 'Started':
                                    $statusClass = 'bg-warning text-dark p-1 rounded-pill'; // Yellow
                                    break;
                                case 'Done':
                                    $statusClass = 'bg-success text-white p-1 rounded-pill'; // Green
                                    break;
                            }

                            // // Determine urgency color class
                            // $urgencyClass = '';
                            // $urgencyText = htmlspecialchars($request['name']);
                            // switch ($urgencyText) {
                            //     case 'Low':
                            //         $urgencyClass = 'text-success'; // Green
                            //         break;
                            //     case 'Medium':
                            //         $urgencyClass = 'text-warning'; // Yellow
                            //         break;
                            //     case 'High':
                            //         $urgencyClass = 'text-danger'; // Red
                            //         break;
                            // }
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-start" 
                                data-request-id="<?= htmlspecialchars($request['repair_request_id']) ?>" 
                                data-machine="<?= htmlspecialchars($request['machine_name']) ?>"
                                data-status="<?= $statusText ?>"
                                data-date="<?= date("d M Y", strtotime($request['date_requested'])) ?>">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">Request ID: <?= htmlspecialchars($request['repair_request_id']) ?></div>
                                    Machine: <?= htmlspecialchars($request['machine_name']) ?> - <?= htmlspecialchars($request['machine_serial_number']) ?> | 
                                    <span class="<?= $statusClass ?>">Status: <?= $statusText ?></span> | 
                                </div>
                                <span class="badge bg-primary rounded-pill">
                                    <?= date("d M Y", strtotime($request['date_requested'])) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <!-- Announcement Content Section -->
                <div id="announcement-content" class="content-section">
                    <!-- Timeline Container for dynamically loaded announcements -->
                    <div class="timeline"></div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="script.js"></script>
</body>
</html>
<?php
} else {
    header(header: "Location: ../login.php");}
?>