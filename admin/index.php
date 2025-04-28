<?php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['employee_id']) && $_SESSION['user_type'] === "admin") {

// Include the database connection file
include '../connect.php';

// SQL query to get the most recent BLOB from the image column
$stmt = $conn->prepare("
    SELECT image
    FROM users 
    WHERE employee_id = ?
    LIMIT 1
");

$stmt->execute([$_SESSION['employee_id']]);

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
    <link href="style.css" rel="stylesheet" >
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
                    <a href="index.php" class="sidebar-link">
                    <i class="bi bi-house-door"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#inventory" aria-expanded="false" aria-controls="inventory">
                        <i class="bi bi-basket3"></i>
                        <span>Inventory</span>
                    </a>
                    <ul id="inventory" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="inventory/items/index.php" class="sidebar-link">Items</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="inventory/adjustments/index.php" class="sidebar-link">Adjustments</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#requests" aria-expanded="false" aria-controls="requests">
                        <i class="bi bi-pencil-square"></i>
                        <span>Requests</span>
                    </a>
                    <ul id="requests" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="requests/repair/index.php" class="sidebar-link">Repair</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="requests/materials/index.php" class="sidebar-link">Material</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#machines" aria-expanded="false" aria-controls="machines">
                        <i class="bi bi-tools"></i>
                        <span>Machines</span>
                    </a>
                    <ul id="machines" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="machines/list/index.php" class="sidebar-link">List</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="machines/maintenance/index.php" class="sidebar-link">Maintenance</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="people/index.php" class="sidebar-link">
                    <i class="bi bi-people"></i>
                        <span>People</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="reports/index.php" class="sidebar-link">
                    <i class="bi bi-file-earmark-text"></i>
                        <span>Reports</span>
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
                        <input class="form-control me-2" type="search" placeholder="Search..." aria-label="Search" style="width: 600px; height: 30px; font-size: 16px;">
                        <button class="icon-btn" title="Notifications">
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
                                <img src="../../images/person-circle.png"
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
                        <h1 class="name-display">Hello, <?=$_SESSION['user_first_name']?> <?=$_SESSION['user_last_name']?></h1>
                        <h5>KSK Food Products</h5>
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
                    <div class="row">
                        <div class="col-8">
                            <h2>Inventory Activity</h2>
                            <div class="row">
                                <!-- Card for Items on Hand -->
                                <div class="card col m-2 align-items-center card-hover">
                                    <a href="inventory/items/index.php" class="card-link"> <!-- Redirect link -->
                                        <div class="card-body d-flex align-items-center">
                                            <div id="items-on-hand-container" class="card-icon-container">
                                                <svg id="items-on-hand" xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="card-icon" viewBox="0 0 16 16">
                                                    <path d="M5.071 1.243a.5.5 0 0 1 .858.514L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15.5a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V9H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 6h1.717zM3.5 10.5a.5.5 0 1 0-1 0v3a.5.5 0 0 0 1 0zm2.5 0a.5.5 0 1 0-1 0v3a.5.5 0 0 0 1 0zm2.5 0a.5.5 0 1 0-1 0v3a.5.5 0 0 0 1 0zm2.5 0a.5.5 0 1 0-1 0v3a.5.5 0 0 0 1 0z"/>
                                                </svg>
                                            </div>
                                            <p class="mb-0">Items on Hand</p>
                                        </div>
                                        <p id="items-on-hand-text" class="card-number">[Loading]</p>
                                    </a>
                                </div>

                                <!-- Card for Item Requests -->
                                <div class="card col m-2 align-items-center card-hover">
                                    <a href="requests/materials/index.php" class="card-link">
                                        <div class="card-body d-flex align-items-center">
                                            <div id="repair-request-container" class="card-icon-container">
                                                <svg id="repair-request" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-text-fill" viewBox="0 0 16 16">
                                                    <path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M4.5 9a.5.5 0 0 1 0-1h7a.5.5 0 0 1 0 1zM4 10.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 1 0-1h4a.5.5 0 0 1 0 1z"/>
                                                </svg>
                                            </div>
                                            <p class="mb-0">Repair Request</p>
                                        </div>
                                        <p id="this-month-count-text" class="card-number">[Loading]</p>
                                        <p id="percentage-change" class="percentage-change">
                                            <!-- Will be populated dynamically with JavaScript -->
                                        </p>
                                    </a>
                                </div>

                                <!-- Card for Low Stock Items -->
                                <div class="card col m-2 align-items-center card-hover">
                                    <a href="inventory/items/index.php" class="card-link">
                                        <div class="card-body d-flex align-items-center">
                                            <div id="low-stock-items-container" class="card-icon-container">
                                                <svg id="low-stock-items" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16">
                                                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                                                </svg>
                                            </div>
                                            <p class="mb-0">Low Stock Items</p>
                                        </div>
                                        <p id="low-stock-text" class="card-number">[Loading]</p>
                                    </a>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <h2>Next Maintenance Schedule</h2>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th scope="col">Machine ID</th>
                                                <th scope="col">Machine Name</th>
                                                <th scope="col">Next Maintenance Date</th>
                                                <th scope="col">Maintenance Type</th>
                                                <th scope="col">Assigned Technician</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>001</td>
                                                <td>Hydraulic Press</td>
                                                <td>2025-01-10</td>
                                                <td>Full Service</td>
                                                <td>John Doe</td>
                                            </tr>
                                            <tr>
                                                <td>002</td>
                                                <td>CNC Machine</td>
                                                <td>2025-03-01</td>
                                                <td>Inspection</td>
                                                <td>Jane Smith</td>
                                            </tr>
                                            <tr>
                                                <td>003</td>
                                                <td>Conveyor Belt</td>
                                                <td>2025-02-15</td>
                                                <td>Lubrication</td>
                                                <td>Mike Johnson</td>
                                            </tr>
                                            <tr>
                                                <td>004</td>
                                                <td>Forklift</td>
                                                <td>2025-06-05</td>
                                                <td>Battery Check</td>
                                                <td>Emily Davis</td>
                                            </tr>
                                            <tr>
                                                <td>005</td>
                                                <td>Air Compressor</td>
                                                <td>2025-01-20</td>
                                                <td>Filter Replacement</td>
                                                <td>Chris Wilson</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card col align-items-center card-hover mb-5">
                                <h2 class="mt-2">Analytics Insights</h2>
                                <div class="radial-progress-container m-3">
                                    <svg class="radial-progress" width="120" height="120" viewBox="0 0 120 120">
                                        <circle class="progress-bg" cx="60" cy="60" r="54" />
                                        <circle class="progress" cx="60" cy="60" r="54" transform="rotate(-90, 60, 60)" />
                                        <text x="60" y="65" class="progress-text">45%</text>
                                    </svg>
                                </div>
                            </div>
                            <div class="card col align-items-center card-hover">
                                <h2 class="mt-2">Employee Performance</h2>
                                <div class="radial-progress-container m-3">
                                    <svg class="radial-progress" width="120" height="120" viewBox="0 0 120 120">
                                        <circle class="progress-bg" cx="60" cy="60" r="54" />
                                        <!-- Static progress with pre-calculated stroke-dashoffset -->
                                        <circle class="progress" cx="60" cy="60" r="54" transform="rotate(-90, 60, 60)" 
                                            style="stroke-dasharray: 339.292; stroke-dashoffset: 84.823;" />
                                        <text x="60" y="65" class="progress-text">75%</text>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Announcement Content Section -->
                <div id="announcement-content" class="content-section">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary" id="addAnnouncementBtn"><i class="bi bi-plus"></i> Add announcement</button>
                </div>
                    <!-- Timeline Container for dynamically loaded announcements -->
                    <div class="timeline"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="addAnnouncementModal" tabindex="-1" aria-labelledby="addAnnouncementModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAnnouncementModalLabel">Add Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="announcementForm">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <input type="hidden" id="created_by" name="created_by" value="<?= $_SESSION['user_id'] ?>">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveAnnouncementBtn">Save Announcement</button>
                </div>
            </div>
        </div>
    </div>

    <!--Info Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
</body>
</html>
<?php
} else {
    header(header: "Location: ../login.php");}
?>