<?php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['employee_id']) && $_SESSION['user_type'] === "admin") {
// Include the database connection file
include '../../../connect.php';

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

            <!-- FullCalendar CSS -->
            <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/index.global.min.css" rel="stylesheet" />
            <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.8/index.global.min.css" rel="stylesheet" />
            <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.8/index.global.min.css" rel="stylesheet" />
            
            <!-- Your Custom Stylesheet -->
            <link href="style.css" rel="stylesheet">

            <title>Items</title>
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
                        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                            data-bs-target="#inventory" aria-expanded="false" aria-controls="inventory">
                            <i class="bi bi-basket3"></i>
                            <span>Inventory</span>
                        </a>
                        <ul id="inventory" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li class="sidebar-item">
                                <a href="../../inventory/items/index.php" class="sidebar-link">Items</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="../../inventory/adjustments/index.php" class="sidebar-link">Adjustments</a>
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
                                <a href="../../requests/repair/index.php" class="sidebar-link">Repair</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="../../requests/materials/index.php" class="sidebar-link">Material</a>
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
                                <a href="../list/index.php" class="sidebar-link">List</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="index.php" class="sidebar-link">Maintenance</a>
                            </li>
                        </ul>
                    </li>
                    <li class="sidebar-item">
                        <a href="../../people/index.php" class="sidebar-link">
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
                    <a href="../../../logout.php" class="sidebar-link">
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
                                    <img src="../../../images/person-circle.png"
                                        alt="Profile Picture" 
                                        class="profile-icon">
                                <?php endif; ?>
                            </button>
                        </ul>
                    </div>
                </nav>
                <!-- Main Content -->
                    <div id="calendar"></div>
            </div>
        </div>
        <!-- Item Info Modal -->
        <div id="itemInfoModal" class="offcanvas offcanvas-end" tabindex="-1" style="padding: 20px;" data-bs-scroll="true">
            <div class="offcanvas-header d-flex justify-content-between">
                <!-- Left Side Top: User Name and Employee ID -->
                <div>
                    <h5 class="offcanvas-title" id="modalItemNameText">[Item Name]</h5>
                    <p class="mb-0 text-muted">Item Code: <span id="modalItemCodeText">[Item Code]</span></p>
                </div>

                <!-- Right Side Top: Edit, Delete, and Close Buttons -->
                <div>
                    <button id="editItemBtn" class="btn btn-outline-primary me-2" title="Edit">Edit</button>
                    <button id="deleteItemBtn" class="btn btn-outline-danger me-2" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" title="Close"></button>
                </div>
            </div>
            <hr>
            <div class="offcanvas-body">
                <div class="row">
                    <!-- Left Side Bottom: Item Information -->
                    <div class="col-md-6 user-details">
                        <div class="mb-3">
                            <label for="modalItemName" class="form-label"><strong>Item Name:</strong></label>
                            <input type="text" id="modalItemName" class="form-control" disabled>
                        </div>
                        <div class="mb-3"> 
                            <label for="modalSizePerUnit" class="form-label"><strong>Size per Unit:</strong></label>
                            <input type="number" id="modalSizePerUnit" min="1" max="100" class="form-control" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="modalUnit" class="form-label"><strong>Unit:</strong></label>
                            <select id="modalUnit" class="form-select" disabled>
                                <option value="pcs (piece/s)">pcs (piece/s)</option>
                                <option value="kg (kilogram/s)">kg (kilogram/s)</option>
                                <option value="L (liter/s)">L (liter/s)</option>
                                <option value="m (meter/s)">m (meter/s)</option>
                                <option value="box">box</option>
                                <option value="set">set</option>
                                <option value="mL (milliliter/s)">mL (milliliter/s)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="modalDescription" class="form-label"><strong>Description:</strong></label>
                            <textarea id="modalDescription" class="form-control" disabled></textarea>
                        </div>
                        <div class="mb-3">
                            <p><strong>Created By: </strong><span id="modalCreatedByNameText">[Item Quantity]</span></p>
                            <p><strong>Created At: </strong><span id="modalCreatedAtText">[Item Quantity]</span></p>
                        </div>
                    </div>

                    <!-- Right Side Bottom: Item Picture with Edit/Remove Buttons -->
                    <div class="col-md-6 text-center user-image-section">
                        <div>
                            <img id="itemProfileImage" src="" alt="Profile Picture" 
                                class="img-thumbnail mb-2" 
                                style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        <input type="file" id="uploadImageInput" accept="image/*" style="display: none;"> <!-- Hidden input for file upload -->
                        <div class="btn-group">
                            <button id="editImageBtn" class="btn btn-outline-secondary" disabled>Upload</button>
                            <button id="removeImageBtn" class="btn btn-outline-secondary" disabled>Remove</button>
                        </div>
                        <div class="text-start bg-light p-3 rounded border" style="padding: 10px; margin-top: 20px;">
                            <strong>Physical Stock</strong>
                            <p class="mb-0 text-muted">Quantity on Hand: <span id="modalQuantityText">[Item Quantity]</span>
                            <span><a href="#" id="editQuantityBtn"><i class="bi bi-pencil"></i></a></span>
                            </p>
                            <p class="mb-0 text-muted">Quantity Needed: <span id="modalNeededQuantityText">[Requested]</span></p>
                            <hr class="my-2"/> <!-- Faded line -->
                            <p>Reorder Point: 
                                <span id="modalReorderPointText" class="mb-0 text-muted">[Reorder Point]</span>
                                <span><a href="#" id="editReorderPointBtn"><i class="bi bi-pencil"></i></a></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal for Reorder Point-->
        <div class="modal fade" id="reorderPointModal" tabindex="-1">
            <div class="modal-dialog" style="top: 10%; right: 10%;">
                <div class="modal-content" style="max-height: 40vh; overflow-y: auto;">
                <div class="modal-header">
                    <h5 class="modal-title">Reorder Point</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Set Reorder Point:</strong></p>
                    <input type="number" id="reorderPointModalInput" min="1" max="100" class="form-control" required>
                    <p class="text-success">System Recommendation: <span id="reorderPointText">[Reorder Point]</span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="updateReorderPointBtn" class="btn btn-primary">Update</button>
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
        <!-- Confirmation Modal for Deletion of Item -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" style="top: 10%; right: 10%;">
                <div class="modal-content" style="max-height: 50vh; max-width: 50vh; overflow-y: auto;">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>To confirm deletion, please type the item name:</p>
                        <div id="itemNameDisplay" style="color: gray; opacity: 0.6; margin-bottom: 10px;">
                            <span id="displayItemName"></span>
                        </div>
                        <input type="text" id="confirmItemName" class="form-control" placeholder="Item Name" required>
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

        <!-- Offcanvas Modal -->
        <div id="addItemModal" class="offcanvas offcanvas-end" tabindex="-1" style="padding: 20px; width: 45%;" data-bs-scroll="true" aria-labelledby="addItemModalLabel" aria-modal="true" role="dialog">
            <div class="offcanvas-header d-flex justify-content-between">
                <h5 class="offcanvas-title" id="addItemModalLabel">Add Maintenance Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <hr>
            <div class="offcanvas-body">
                <form id="addUserForm">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="maintenanceDate" class="form-label"><strong>Maintenance Date:</strong></label>
                            <input type="datetime-local" class="form-control" id="maintenanceDate" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="maintenanceDescription" class="form-label"><strong>Description:</strong></label>
                            <input type="text" class="form-control" id="maintenanceDescription" required>
                        </div>
                    </div>
                    <!-- Maintenance Machines Table -->
                    <h6>Maintenance Machines</h6>
                    <table id="itemTable" class="table table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;"><input type="checkbox" id="selectAll"></th>
                                <th class="text-start">Machine</th>
                                <th class="text-start">Department</th>
                                <th class="text-start">Next Maintenance</th>
                                <th class="text-start">Warranty Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                try {
                                    $sql = "SELECT
                                        machine.machine_parts_id AS official_machine_id,
                                        machine.*, 
                                        warranty.*, 
                                        department.*, 
                                        maintenance.*,
                                        employee.*
                                    FROM machine_parts
                                    LEFT JOIN machine on machine_parts.machine_id = machine.machine_id
                                    LEFT JOIN department ON machine.machine_department_id = department.department_id 
                                    LEFT JOIN employee ON machine.machine_created_by = employee.employee_id
                                    LEFT JOIN warranty ON machine.machine_id = warranty.machine_id 
                                        AND warranty.warranty_end_date = (
                                            SELECT MAX(warranty_end_date) 
                                            FROM warranty 
                                            WHERE warranty.machine_id = machine.machine_id
                                        )
                                    LEFT JOIN maintenance ON machine.machine_parts_id = maintenance.machine_parts_id 
                                        AND maintenance.maintenance_scheduled_date = (
                                            SELECT MAX(maintenance_scheduled_date) 
                                            FROM maintenance 
                                            WHERE maintenance.machine_id = machine.machine_id
                                        );";
                                    $result = $conn->query($sql);

                                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                        // Initialize image-related variables
                                        $mimeType = null;
                                        $base64Image = null;
                                        $imageData = '';

                                        // Check if the user has an image
                                        if (isset($row['image']) && !empty($row['image'])) {
                                            $finfo = new finfo(FILEINFO_MIME_TYPE);
                                            $mimeType = $finfo->buffer($row['image']);
                                            $base64Image = base64_encode($row['image']);
                                            $imageData = "data:$mimeType;base64,$base64Image";
                                        } else {
                                            $imageData = "../../../images/gallery.png";
                                        }

                                        // Construct the table row
                                        echo "<tr data-machine-id='" . htmlspecialchars($row['official_machine_id'] ?? '') . "' 
                                        data-machine-name='" . htmlspecialchars($row['machine_name'] ?? '') . "'
                                        data-type='" . htmlspecialchars($row['machine_type'] ?? '') . "'
                                        data-model='" . htmlspecialchars($row['machine_model'] ?? '') . "'
                                        data-manufacturer='" . htmlspecialchars($row['machine_manufacturer'] ?? '') . "'
                                        data-image='" . htmlspecialchars($imageData) . "'
                                        data-department-id='" . htmlspecialchars($row['department_id'] ?? '') . "'
                                        data-department-name='" . htmlspecialchars($row['department_name'] ?? '') . "'
                                        data-machine-description='" . htmlspecialchars($row['machine_description'] ?? 'Not available') . "'
                                        data-machine-created-by='" . htmlspecialchars($row['first_name'] . " " . $row['last_name'] ?? 'Not available') . "'
                                        data-machine-created-at='" . htmlspecialchars((new DateTime($row['machine_created_at']))->format('d M Y')) . "'
                                        data-warranty-status='" . htmlspecialchars($row['warranty_status'] ?? 'No warranty') . "'
                                        data-machine-maintenance-interval-days='" . htmlspecialchars($row['machine_maintenance_interval_days'] ?? '0') . "'
                                        data-year-of-manifacture='" . htmlspecialchars($row['machine_year_of_manufacture'] ?? '0') . "'>

                                        <td class='text-center align-middle'><input type='checkbox' class='row-checkbox' data-machine-id='" . htmlspecialchars($row['machine_id'] ?? '') . "'></td>

                                        <td class='text-start'>
                                            <img src='" . htmlspecialchars($imageData) . "' 
                                                alt='Profile Picture' class='me-2 align-middle' style='width: 40px; object-fit: cover;'>
                                            <span>" . htmlspecialchars($row['machine_name'] ?? '') . "</span>
                                        </td>

                                        <td class='text-start align-middle'>" . htmlspecialchars($row['department_name'] ?? '') . "</td>
                                        <td class='text-start align-middle'>" . 
                                            (!empty($row['maintenance_scheduled_date']) 
                                                ? htmlspecialchars((new DateTime($row['maintenance_scheduled_date']))->format('d M Y')) 
                                                : '') . 
                                        "</td>";

                                        // Check warranty status
                                        if ($row['warranty_status'] === 'Active') {
                                            echo "<td class='text-start align-middle text-success'>" . htmlspecialchars($row['warranty_status'] ?? '') . "</td> </tr>";
                                        } else if ($row['warranty_status'] === 'Expired') {
                                            echo "<td class='text-start align-middle text-danger'>" . htmlspecialchars($row['warranty_status'] ?? '') . "</td> </tr>";
                                        } else {
                                            echo "<td class='text-start align-middle'>" . htmlspecialchars($row['warranty_status'] ?? '') . "</td> </tr>";
                                        }
                                    }
                                } catch (PDOException $e) {
                                    echo "<tr><td colspan='5'>Error fetching data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </form>
            </div>
            <div class="offcanvas-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Close</button>
                <button type="button" class="btn btn-primary" id="saveMaintenanceBtn">Save Maintenance Schedule</button>
            </div>
        </div>

        <!-- Include jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- Include DataTables JS -->
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

        <!-- Bootstrap JS Bundle -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>

        <!-- Include FullCalendar JS -->
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.8/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.8/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.8/index.global.min.js"></script>

        <!-- Your Custom Script -->
        <script src="script.js"></script>

        <script>
            const loggedInEmployeeId = "<?php echo $_SESSION['employee_id']; ?>"; // Logged In User's Employee ID
        </script>
    </body>
</html>
<?php
} else {
    header(header: "Location: ../../../login.php");
    exit();
}
?>