<?php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['employee_id']) && $_SESSION['user_type'] === "admin") {

// Include the database connection file
include '../../connect.php';

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

            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

            <!-- Bootstrap Icons -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

            <!-- Line Icons -->
            <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />

            <!-- DataTables CSS -->
            <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

            <!-- Your Custom Stylesheet -->
            <link href="style.css" rel="stylesheet">

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
                            data-bs-target="#inventory" aria-expanded="false" aria-controls="inventory">
                            <i class="bi bi-basket3"></i>
                            <span>Inventory</span>
                        </a>
                        <ul id="inventory" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li class="sidebar-item">
                                <a href="../inventory/items/index.php" class="sidebar-link">Items</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="../inventory/adjustments/index.php" class="sidebar-link">Adjustments</a>
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
                                <a href="../requests/repair/index.php" class="sidebar-link">Repair</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="../requests/materials/index.php" class="sidebar-link">Material</a>
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
                                <a href="../machines/list/index.php" class="sidebar-link">List</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="../machines/maintenance/index.php" class="sidebar-link">Maintenance</a>
                            </li>
                        </ul>
                    </li>
                    <li class="sidebar-item">
                        <a href="index.php" class="sidebar-link">
                        <i class="bi bi-people"></i>
                            <span>People</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link">
                        <i class="bi bi-file-earmark-text"></i>
                            <span>Reports</span>
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
                <nav class="navbar navbar-expand-lg navbar-custom px-4" style="border-bottom: 1px solid #dee2e6">
                    <div class="d-flex justify-content-end align-items-center flex-grow-1">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <button class="icon-btn" title="Notifications">
                                    <i class="bi bi-bell" style="font-size: 1.5rem;"></i>
                                </button>
                            </li>
                            <li class="nav-item mx-3">
                                <button class="icon-btn" title="Settings">
                                    <i class="bi bi-gear" style="font-size: 1.5rem;"></i>
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
                <div class="row">
                    <div class="col">
                    <div class="d-flex justify-content-between align-items-center" style="padding: 20px 0 20px 0;">
                            <!-- Left: Heading -->
                            <h1 class="title" style="padding-left: 20px;">Users</h1>
                            
                            <!-- Right: Buttons and Dropdowns -->
                            <div class="d-flex justify-content-end align-items-center gap-2" style="padding-right: 20px;">
                                <button type="button" id="addUserBtn" class="btn btn-outline-primary">Add a User</button>
                                <div class="dropdown">
                                    <a class="btn btn-secondary" href="#" role="button" id="dropDownMore" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="dropDownMore">
                                        <a class="dropdown-item" href="#">Delete</a>
                                        <a class="dropdown-item" href="#">Filter</a>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <a class="btn btn-warning" href="#" role="button" id="dropDownQuestion" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-question-lg"></i>
                                    </a>
                                    <div aria-labelledby="dropDownQuestion"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Table -->
                <table id="userTable" class="table table-striped table-hover w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 5%;"><input type="checkbox" id="selectAll"></th>
                            <th class="text-start" style="padding-left: 13px;">Name</th>
                            <th class="text-start" style="padding-left: 13px;">Role</th>
                            <th class="text-start" style="padding-left: 13px;">Department</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        try {
                            $sql = "SELECT * FROM users 
                            JOIN employee ON users.employee_id = employee.employee_id
                            JOIN department on employee.department = department.department_id";
                            $result = $conn->query($sql);

                            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                // Initialize image-related variables
                                $mimeType = null;
                                $base64Image = null;
                                $imageData = '';

                                // Check if the user has an image
                                if (isset($row['image']) && !empty($row['image'])) {
                                    // Detect MIME type and convert BLOB to base64
                                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                                    $mimeType = $finfo->buffer($row['image']);
                                    $base64Image = base64_encode($row['image']);
                                    $imageData = "data:$mimeType;base64,$base64Image";  // Store the base64 image
                                }
                                else {
                                    $imageData = "../../images/person-circle.png";
                                }

                                // Construct the table row
                                echo "<tr
                                    data-employee-id='" . htmlspecialchars($row['employee_id']) . "' 
                                    data-date-created='" . htmlspecialchars($row['date_created']) . "' 
                                    data-first-name='" . htmlspecialchars($row['first_name']) . "' 
                                    data-middle-name='" . htmlspecialchars($row['middle_name']) . "' 
                                    data-last-name='" . htmlspecialchars($row['last_name']) . "' 
                                    data-image='" . htmlspecialchars($imageData) . "'>
                                    <td class='text-center align-middle'><input type='checkbox' class='row-checkbox'></td>
                                    <td class='text-start'><img src='" . htmlspecialchars($imageData) . "' 
                                        alt='Profile Picture' class='profile-icon me-2 align-middle' style='width: 40px; height: 40px; object-fit: cover;'>
                                        <span>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "</span></td>
                                    <td class='text-start align-middle'>" . htmlspecialchars($row['role']) . "</td>
                                    <td class='text-start align-middle'>" . htmlspecialchars($row['department_name']) . "</td>
                                </tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='5'>Error fetching data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- User Info Modal -->
        <div id="userInfoModal" class="offcanvas offcanvas-end" tabindex="-1" style="padding: 20px;" data-bs-scroll="true">
            <div class="offcanvas-header d-flex justify-content-between">
                <!-- Left Side Top: User Name and Employee ID -->
                <div>
                    <h5 class="offcanvas-title" id="modalUserNameText">[User Name]</h5>
                    <p class="mb-0 text-muted" id="modalEmployeeIdText"></p>
                </div>

                <!-- Right Side Top: Edit, Delete, and Close Buttons -->
                <div>
                    <button id="editUserBtn" class="btn btn-outline-primary me-2" title="Edit">Edit</button>
                    <button id="deleteUserBtn" class="btn btn-outline-danger me-2" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" title="Close"></button>
                </div>
            </div>
            <hr>
            <div class="offcanvas-body">
                <div class="row">
                    <!-- Left Side Bottom: User Information -->
                    <div class="col-md-8 user-details">
                        <div class="mb-3">
                            <label for="modalFirstName" class="form-label"><strong>First Name:</strong></label>
                            <input type="text" id="modalFirstName" class="form-control" disabled>
                        </div>

                        <div class="mb-3">
                            <label for="modalMiddleName" class="form-label"><strong>Middle Name:</strong></label>
                            <input type="text" id="modalMiddleName" class="form-control" disabled>
                        </div>

                        <div class="mb-3">
                            <label for="modalLastName" class="form-label"><strong>Last Name:</strong></label>
                            <input type="text" id="modalLastName" class="form-control" disabled>
                        </div>

                        <div class="mb-3">
                            <label for="modalRole" class="form-label"><strong>Role:</strong></label>
                            <select id="modalRole" class="form-select" disabled>
                                <option value="Employee">Employee</option>
                                <option value="Head">Head</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="modalDepartment" class="form-label"><strong>Department:</strong></label>
                            <select id="modalDepartment" class="form-select" disabled>
                                <option default value="1">Engineering</option>
                                <option value="2">Warehouse</option>
                                <option value="4">Logistics</option>
                                <option value="7">Pre-production</option>
                                <option value="8">Manufacturing</option>
                                <option value="9">Flavoring</option>
                                <option value="10">Packaging</option>
                                <option value="11">IT</option>
                            </select>
                        </div>

                        <p id="modalDateCreated" class="mt-3"></p>
                    </div>

                    <!-- Right Side Bottom: User Picture with Edit/Remove Buttons -->
                    <div class="col-md-4 text-center user-image-section">
                        <div>
                            <img id="userProfileImage" src="" alt="Profile Picture" 
                                class="img-thumbnail mb-2" 
                                style="width: 120px; height: 120px; border-radius: 50%;">
                        </div>
                        <input type="file" id="uploadImageInput" accept="image/*" style="display: none;"> <!-- Hidden input for file upload -->
                        <div class="btn-group">
                            <button id="editImageBtn" class="btn btn-outline-secondary" disabled>Upload</button>
                            <button id="removeImageBtn" class="btn btn-outline-secondary" disabled>Remove</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal for confirming image removal -->
        <div class="modal fade" id="removeImageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" style="top: 10%; right: 10%;">
                <div class="modal-content" style="max-height: 40vh; overflow-y: auto;">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Remove Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to remove this picture?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmRemoveImageBtn" class="btn btn-danger">Remove</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Confirmation Modal for Deletion of User -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" style="top: 10%; right: 10%;">
                <div class="modal-content" style="max-height: 60vh; max-width: 60vh; overflow-y: auto;">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>To confirm deletion, please type the user's first and last name:</p>
                        <div id="userNameDisplay" style="color: gray; opacity: 0.6; margin-bottom: 10px;">
                            <!-- This will show the user's name in faded format -->
                            <span id="displayFirstName"></span> <span id="displayLastName"></span>
                        </div>
                        <input type="text" id="confirmFirstName" class="form-control" placeholder="First Name" required>
                        <input type="text" id="confirmLastName" class="form-control mt-2" placeholder="Last Name" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Modal -->
        <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" style="top: 10%; right: 10%;">
                <div class="modal-content" style="max-height: 30vh; max-width: 60vh; overflow-y: auto;">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="modal-text"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Okay</button>
                </div>
                </div>
            </div>
        </div>
        <!-- Add User Offcanvas Modal -->
        <div id="addUserModal" class="offcanvas offcanvas-end" tabindex="-1" style="padding: 20px; width: 45%;" data-bs-scroll="true" aria-labelledby="addUserModalLabel" aria-modal="true" role="dialog">
            <div class="offcanvas-header d-flex justify-content-between">
                <h5 class="offcanvas-title" id="addUserModalLabel">Add a User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <hr>
            <div class="offcanvas-body">
                <form id="addUserForm">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="addEmployeeId" class="form-label">Employee ID</label>
                            <input type="text" class="form-control" id="addEmployeeId" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="addFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="addFirstName" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="addMiddleName" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="addMiddleName">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="addLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="addLastName" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="addRole" class="form-label">Role</label>
                            <select class="form-select" id="addRole" required>
                                <option value="Employee">Employee</option>
                                <option value="Head">Head</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="addDepartment" class="form-label">Department</label>
                            <select id="addDepartment" class="form-select">
                                <option value="1">Engineering</option>
                                <option value="2">Warehouse</option>
                                <option value="4">Logistics</option>
                                <option value="7">Pre-production</option>
                                <option value="8">Manufacturing</option>
                                <option value="9">Flavoring</option>
                                <option value="10">Packaging</option>
                                <option value="11">IT</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="addUserImage" class="form-label">Profile Image</label>
                        <input type="file" class="form-control" id="addUserImage" accept="image/*">
                    </div>
                    <div class="mb-3 text-center">
                        <img id="previewUserImage" src="../../images/person-circle.png" alt="Profile Preview" class="img-thumbnail" style="width: 120px; height: 120px; border-radius: 50%;">
                    </div>
                </form>
            </div>
            <div class="offcanvas-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Close</button>
                <button type="button" class="btn btn-primary" id="saveUserBtn">Add User</button>
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
        
        <!-- Include jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- Include DataTables JS -->
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

        <!-- Bootstrap JS Bundle -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>

        <!-- Your Custom Script -->
        <script src="script.js"></script>

        <script>
            const loggedInEmployeeId = "<?php echo $_SESSION['employee_id']; ?>"; // Logged In User's Employee ID
        </script>
    </body>
</html>
<?php
} else {
    header(header: "Location: ../../login.php");
    exit();
}
?>