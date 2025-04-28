<?php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['employee_id']) && $_SESSION['user_type'] === "non-engineering") {

// Include the database connection file
include '../../connect.php';

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

    <link rel="stylesheet" href="style.css">

    <title>Profile</title>
</head>
<body>
    <div class="wrapper">
        <aside id="sidebar" class="expand">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <i class="bi bi-box-seam-fill"></i>
                </button>
                <div class="sidebar-logo">
                    <a href="../index.php">Inventuro</a>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="../index.php" class="sidebar-link">
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
                            <a href="../repair/list/index.php" class="sidebar-link">View Machines List</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="../repair/request/index.php" class="sidebar-link">Create a Request</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="index.php" class="sidebar-link active">
                    <i class="bi bi-person"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="../../logout.php" class="sidebar-link">
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
                        <img src="../../images/ksk-logo.png" alt="KSK Logo" style="max-width: 100%; height: auto;">
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
                    <a id="settings-link" class="link-hover-effect text-primary active" href="#">Settings</a>
                    <a id="activity-log-link" class="link-hover-effect text-primary" href="#">Activity Log</a>
                </div>
            </div>
            <div id="main-content" class="p-4">
                <!-- Settings Content Section -->
                <div id="settings-content" class="content-section active">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-4 col-sm-auto mb-3">
                                    <div class="mx-auto" style="width: 140px;">
                                        <?php if (isset($base64Image)): ?>
                                            <img id="profile-picture" src="data:<?=$mimeType?>;base64,<?=$base64Image?>"
                                                alt="Profile Picture" 
                                                class="profile-icon" style="height: 140px; width: 140px;">
                                        <?php else: ?>
                                            <img id="profile-picture" src="../../images/person-circle.png"
                                                alt="Profile Picture" 
                                                class="profile-icon" style="height: 140px; width: 140px;">
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-8 d-flex flex-column flex-sm-row justify-content-between mb-3">
                                    <div class="text-center text-sm-left mb-2 mb-sm-0">
                                        <h4 class="pt-sm-2 pb-1 mb-0 text-nowrap"><?=$first_name?> <?=$last_name?> <small><span class="badge badge-secondary"><?=$role?> of <?=$department?></span></small></h4>
                                        <p class="mb-0"><?=$employee_id?></p>
                                        <div class="text-muted"><small><?= date("d M Y", strtotime($date_created)) ?></small></div>
                                        <div class="mt-2">
                                            <button class="btn btn-primary" type="button" onclick="document.getElementById('profile-picture-input').click()">
                                                <i class="fa fa-fw fa-camera"></i>
                                                <span>Change Photo</span>
                                            </button>
                                            <input type="file" id="profile-picture-input" accept="image/*" style="display: none;" onchange="previewProfilePicture(event)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Settings Tab -->
                            <ul class="nav nav-tabs">
                                <li class="nav-item"><a href="" class="active nav-link">Settings</a></li>
                            </ul>
                            <div class="tab-content pt-3">
                                <div class="tab-pane active">
                                <form id="updateProfileForm" class="form" method="POST" novalidate="">
                                    <div class="row">
                                        <div class="col">
                                            <div class="row">
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label>First Name</label>
                                                        <input class="form-control" type="text" name="first_name" placeholder="Juan" value="<?= htmlspecialchars($first_name ?? '') ?>">
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label>Middle Name</label>
                                                        <input class="form-control" type="text" name="middle_name" placeholder="Apolinario" value="<?= htmlspecialchars($middle_name ?? '') ?>">
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label>Last Name</label>
                                                        <input class="form-control" type="text" name="last_name" placeholder="Dela Cruz" value="<?= htmlspecialchars($last_name ?? '') ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <div class="mb-2"><b>Change Password</b></div>
                                            <div class="form-group">
                                                <label>Current Password</label>
                                                <input id="current-password" class="form-control" type="password" name="current_password" placeholder="••••••">
                                            </div>
                                            <div class="form-group">
                                                <label>New Password</label>
                                                <input id="new-password" class="form-control" type="password" name="new_password" placeholder="••••••">
                                            </div>
                                            <div class="form-group">
                                                <label>Confirm Password</label>
                                                <input id="confirm-password" class="form-control" type="password" name="confirm_password" placeholder="••••••">
                                            </div>
                                            <div class="form-check mt-2">
                                                <input type="checkbox" id="toggle-password-visibility" class="form-check-input">
                                                <label class="form-check-label" for="toggle-password-visibility">Show Password</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col">
                                            <div class="form-group">
                                                <input type="file" id="profile-picture-input" accept="image/*" onchange="previewProfilePicture(event)" style="display: none;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col d-flex justify-content-start">
                                            <button class="btn btn-primary" type="submit">Save Changes</button>
                                        </div>
                                    </div>
                                </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Activity Log Content Section -->
                <div id="activity-log-content" class="content-section">
                    <div class="p-3">
                        <h1><strong>Your Activity Log</strong></h1>
                    </div>
                    <!-- Table -->
                    <table id="activityLogTable" class="table table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th class="text-start" style="padding-left: 13px;">Date</th>
                                <th class="text-start" style="padding-left: 13px;">Activity</th>
                                <th class="text-start" style="padding-left: 13px;">IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            try {
                                // Get the user ID from the session
                                $user_id = $_SESSION['user_id'];

                                // SQL query to fetch activity logs for the logged-in user
                                $sql = "SELECT * FROM activity_log WHERE user_id = ? ORDER BY timestamp DESC";
                                
                                // Prepare the statement
                                $stmt = $conn->prepare($sql);

                                // Bind the user_id parameter
                                $stmt->bindParam(1, $user_id, PDO::PARAM_INT);

                                // Execute the query
                                $stmt->execute();
                                
                                // Fetch and display each activity log
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>
                                        <td class='text-start align-middle'>" . htmlspecialchars(date("d M Y g:i A", strtotime($row['timestamp']))) . "</td>
                                        <td class='text-start align-middle'>" . htmlspecialchars($row['activity']) . "</td>
                                        <td class='text-start align-middle'>" . htmlspecialchars($row['ip_address'] ?? '') . "</td>
                                    </tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='3'>Error fetching data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!--Info Modal -->
    <div class="modal" tabindex="-1" role="dialog" id="infoModal">
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
    header(header: "Location: ../../login.php");}
?>