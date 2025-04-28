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

            <!-- Flatpickr CSS -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

            <!-- Select2 CSS -->
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            
            <!-- Your Custom Stylesheet -->
            <link href="style.css" rel="stylesheet">

            <title>Requested Materials</title>
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
                                <a href="../repair/index.php" class="sidebar-link">Repair</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="index.php" class="sidebar-link">Material</a>
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
                                <a href="../../machines/list/index.php" class="sidebar-link">List</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="../../machines/maintenance/index.php" class="sidebar-link">Maintenance</a>
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
                <div class="row">
                    <div class="col">
                        <div class="d-flex justify-content-between align-items-center" style="padding: 20px 0 20px 0;">
                            <!-- Left: Heading -->
                            <h1 class="title" style="padding-left: 20px;">Request Materials</h1>
                            
                            <!-- Right: Buttons and Dropdowns -->
                            <div class="d-flex justify-content-end align-items-center gap-2" style="padding-right: 20px;">
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
                <table id="adjustmentTable" class="table table-striped table-hover w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 5%;"><input type="checkbox" id="selectAll"></th>
                            <th class="text-start" style="padding-left: 13px;">Date</th>
                            <th class="text-start" style="padding-left: 13px;">Repair / Maintenance No.</th>
                            <th class="text-start" style="padding-left: 10px;">Requested By</th>
                            <th class="text-center" style="padding-right: 13px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        try {
                            $sql = "SELECT 
                                    material_request.*,
                                    material_request_items.*,
                                    employee.*
                                    FROM material_request
                                    LEFT JOIN material_request_items ON material_request.material_request_id = material_request_items.material_request_id
                                    LEFT JOIN employee ON material_request.requested_by = employee.employee_id
                                    LEFT JOIN item ON material_request_items.item_id = item.item_code
                                    LEFT JOIN repair_request ON material_request.repair_request_id = repair_request.repair_request_id
                                    LEFT JOIN maintenance ON material_request.maintenance_id = maintenance.maintenance_id
                                    LEFT JOIN machine ON repair_request.machine_id = machine.machine_id
                                    ORDER BY material_request.timestamp DESC;";

                            $result = $conn->query($sql);

                            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                // Construct the table row
                                echo "<tr
                                    data-material-request-id='" . htmlspecialchars($row['material_request_id']) . "'
                                    data-entry-date='" . htmlspecialchars($row['timestamp']) . "' 
                                    data-reason='" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "'
                                    data-description='" . htmlspecialchars($row['name'] ?? 'Not Specified', ENT_QUOTES, 'UTF-8') . "'
                                    data-reference-number='" . htmlspecialchars($row['status'] ?? 'Not Specified', ENT_QUOTES, 'UTF-8') . "'
                                    data-status='" . htmlspecialchars($row['status']) . "'
                                    data-created-by-name='" . htmlspecialchars($row['repair_request_id'] ?? $row['maintenance_id']). "'>
                                    <td class='text-center align-middle'><input type='checkbox' class='row-checkbox'></td>
                                    <td class='text-start align-middle'>" . htmlspecialchars((new DateTime($row['timestamp']))->format('d M Y')) . "</td>
                                    <td class='text-start align-middle'>" . htmlspecialchars($row['repair_request_id'] ?? $row['maintenance_id']) . "</td>
                                    <td class='text-start align-middle'>" . htmlspecialchars($row['requested_by']) . "</td>
                                    <td class='text-center align-middle'><button class='btn btn-sm btn-primary' id='viewMaterialRequest'><i class='bi bi-eye-fill'></i></button> ";
                                    // Check if the status is NOT "Done"
                                    if ($row['status'] !== 'Done') {
                                        echo "<button class='btn btn-sm btn-success' id='completeMaterialRequest'><i class='bi bi-check-circle-fill'></i></button>
                                        <button class='btn btn-sm btn-danger' id='deleteMaterialRequest'><i class='bi bi-trash3-fill'></i></button>";
                                    }
                                    echo "</td>
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
        <!-- Adjustment Info Modal -->
        <div id="adjustmentInfoModal" class="offcanvas offcanvas-end d-flex" tabindex="-1" style="padding: 20px; width: 60%;" data-bs-scroll="true" aria-labelledby="adjustmentInfoModalLabel">
            <div class="offcanvas-header d-flex justify-content-between">
                <!-- Left Side Top: Adjustment Details -->
                <div>
                    <h5 class="offcanvas-title">Material Request Details</h5>
                    <p class="mb-0 text-muted">Material Request ID: <span id="modalAdjustmentIDText">[Adjustment ID]</span></p>
                </div>

                <!-- Right Side Top: Download, Delete, and Close Buttons -->
                <div>
                    <button id="downloadFileBtn" class="btn btn-outline-primary me-2" title="PDF Download"><i class="bi bi-download"></i></button>
                    <button id="deleteAdjustmentBtn" class="btn btn-outline-danger me-2" title="Delete"><i class="bi bi-trash"></i></button>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" title="Close"></button>
                </div>
            </div>
            <hr>
            <div class="form-check form-switch ms-auto">
                <label class="form-check-label" for="viewCheck"> Show PDF view</label>
                <input class="form-check-input" type="checkbox" role="switch" id="viewCheck" checked>
                </div>
            <div class="offcanvas-body modal-dialog-scrollable">
                <div id="normalView" class="row d-none">
                    <div class="col-4 text-start">
                        <p><strong>Requested Date:</strong></p>
                        <p><strong>Requested By:</strong></p>
                        <p><strong>Repair Request No.:</strong></p>
                        <p><strong>Status:</strong></p>
                    </div>
                    <div class="col-8 text-start">
                        <p id="modalEntryDateText" class="text-muted">[Date]</p>
                        <p id="modalReasonText" class="text-muted">[Reason]</p>
                        <p id="modalCreatedByText" class="text-muted">[Adjusted By]</p>
                        <p id="modalDescriptionText" class="text-muted">[Description]</p>
                        <p id="modalReferenceNumberText" class="text-muted">[Reference Number]</p>
                    </div>
                    <div class="col-12">
                        <table class="table table-striped table-hover w-100" id="itemAdjustmentTable">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col" class="text-center">Item Details</th>
                                    <th scope="col" class="text-center">Quantity Requested</th>
                                    <th scope="col" class="text-center">Current Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="pdfView" class="row">
                    <p>This is the PDF view</p>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal for Completion of Adjustment -->
        <div class="modal fade" id="confirmCompleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="width: 800px; position: absolute; left: 35%; max-height: 50vh;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Completion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to grant this material request?</p>
                        <p>Material Request ID: <span id="modalAdjustmentID"></span></p>

                        <div class="row">
                            <div class="col-6 text-start">
                                <p><strong>Requested Date:</strong></p>
                                <p><strong>Requested By:</strong></p>
                                <p><strong>Repair Request No.:</strong></p>
                                <p><strong>Status:</strong></p>
                            </div>
                            <div class="col-6 text-start">
                                <p id="modalEntryDate" class="text-muted">[Date]</p>
                                <p id="modalReason" class="text-muted">[Reason]</p>
                                <p id="modalCreatedBy" class="text-muted">[Adjusted By]</p>
                                <p id="modalDescription" class="text-muted">[Description]</p>
                                <p id="modalReferenceNumber" class="text-muted">[Reference Number]</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <table class="table table-striped table-hover w-100" id="itemAdjustmentTableSmall">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col" class="text-center">Item Details</th>
                                        <th scope="col" class="text-center">Quantity Requested</th>
                                        <th scope="col" class="text-center">Current Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmCompleteBtn" class="btn btn-success">Complete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal for Deletion of Adjustment -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="width: 500px; position: absolute; left: 35%;">
                <div class="modal-content" style="max-height: 35vh;">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete (or reject) this material request?</p>
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
        <!-- Add Adjustment Offcanvas Modal -->
        <div id="addAdjustmentModal" class="offcanvas offcanvas-end" tabindex="-1" style="padding: 20px; width: 60%;" data-bs-scroll="true" aria-labelledby="addAdjustmentModalLabel" aria-modal="true" role="dialog">
            <div class="offcanvas-header d-flex justify-content-between">
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <hr>
            <div class="offcanvas-body">
                <div class="row">
                    <div class="col-4">
                        <p style="margin-bottom: 35px;"><strong>Reference Number:</strong></p>
                        <p style="margin-bottom: 25px;" class="text-danger"><strong>Date:*</strong></p>
                        <p style="margin-bottom: 35px;" class="text-danger"><strong>Reason:*</strong></p>
                        <p><strong>Description:</strong></p>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control mb-3" id="addReferenceNumber" placeholder="Reference Number">
                        <input type="date" class="form-control mb-3" id="addDate">
                        <?php
                            $stmt = $conn->prepare('SELECT reason_id, reason FROM item_adjustment_reason');
                            $stmt->execute();
                            $reasons = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <select class="form-control form-select" id="addReason" required>
                            <option value="">Select a reason</option>
                            <?php foreach ($reasons as $reason): ?>
                                <option value="<?php echo $reason['reason_id']; ?>"><?php echo $reason['reason']; ?></option>
                            <?php endforeach; ?>
                            <option value="manage" class="manage-reason" data-icon="bi bi-gear-fill">Manage Reason</option>
                        </select>
                        <input type="text-area" class="form-control mt-4" id="addDescription" style="height: 100px;">
                    </div>
                </div>
                <table class="table table-bordered table-hover mt-4 table-sm">
                    <thead>
                        <tr style="font-size: 14px;">
                            <th colspan="3" width="30%">ITEM DETAILS</th>
                            <th width="20%">CURRENT QUANTITY</th>
                            <th>NEW QUANTITY</th>
                            <th>QUANTITY ADJUSTED</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody id="addAdjustmentTableBody">
                        <tr class="template-row">
                            <td><img src="../../../images/gallery.png" alt="Item Picture" width="50" height="50"/></td>
                            <td id="itemCode" class="item-code">#</td>
                            <td>
                                <input type="text" class="form-control item-name" placeholder="Select an item..." list="itemList">
                                <datalist id="itemList"></datalist>
                            </td>
                            <td id="currentQuantity" class="quantity-available">-</td>
                            <td><input id="newQuantity" type="number" class="form-control new-quantity" placeholder="E.g. 10" min="1" disabled/></td>
                            <td><input id="quantityAdjusted" type="number" class="form-control quantity-adjusted" placeholder="E.g. +10 or -10" max="100" disabled/></td>
                            <td><button class="btn btn-outline-danger remove-row"><i class="bi bi-trash3-fill"></i></button></td>
                        </tr>
                    </tbody>
                </table>
                <button class="btn btn-outline-success" id="addRowBtn"><i class="bi bi-plus-circle-fill"></i> Add a Row</button>
            </div>
            <hr>
            <div class="offcanvas-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Close</button>
                <button type="button" class="btn btn-primary" id="saveItemBtn">Save</button>
            </div>
        </div>
        <!-- Manage Reason Modal-->
        <div class="modal fade" id="manageReasonModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-scroll="true">
            <div class="modal-dialog" style="top: 10%; right: 10%; width: 40%; height: 70%;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Manage Reason</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="max-height: 400px; overflow-y: auto;"> <!-- Set max height and enable scroll -->
                        <div class="row">
                            <div class="col-12" id="addReasonSection">
                                <button class="btn btn-success text-start" id="addReasonBtn"><i class="bi bi-plus-circle-fill"></i> Add new reason</button>
                            </div>
                            <div class="col-12 bg-light p-3 rounded border d-none" id="newReasonSection" style="margin: 10px; width: 95%;">
                                <!-- When the addReasonBtn is clicked-->
                                <p class="text-danger"><strong>Reason:*</strong></p>
                                <input type="text" class="form-control mb-4" id="reasonName" placeholder="Reason Name">
                                <button class="btn btn-primary" id="saveNewReasonBtn">Save</button>
                                <button class="btn btn-secondary" id="cancelNewReasonBtn">Cancel</button>
                            </div>
                        </div>
                        <div class="row">
                            <table class="table table-bordered table-hover" style="margin: 10px; width: 95%;">
                                <thead>
                                    <tr>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody id="reasonTableBody">
                                    <?php
                                        // Assuming you have a function to check if a reason is in use
                                        function isReasonUsed($reason_id, $conn) {
                                            $stmt = $conn->prepare('SELECT COUNT(*) FROM item_adjustment WHERE reason_id = :reason_id');
                                            $stmt->bindParam(':reason_id', $reason_id, PDO::PARAM_INT);
                                            $stmt->execute();
                                            return $stmt->fetchColumn() > 0;
                                        }

                                        $stmt = $conn->prepare('SELECT reason_id, reason FROM item_adjustment_reason');
                                        $stmt->execute();
                                        $reasons = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                     
                                    <?php foreach ($reasons as $reason): ?>
                                    <?php $used = isReasonUsed($reason['reason_id'], $conn); // Check if the reason is used ?>
                                    <tr class="reason-row" data-reason-id="<?php echo $reason['reason_id']; ?>" data-used="<?php echo $used ? 'true' : 'false'; ?>">
                                        <td class="text-start">
                                            <?php echo $reason['reason'];?>
                                            <span class="delete-icon" 
                                                style="display: <?php echo $used ? 'none' : 'inline'; ?>; cursor:pointer; color:red; float:right;">
                                                <i class="bi bi-trash-fill"></i>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <input type="hidden" id="loggedInEmployeeId" value="<?php echo $_SESSION['employee_id']; ?>">
        <input type="hidden" id="loggedInName" value="<?php echo $_SESSION['user_first_name'] . " " . $_SESSION['user_last_name']; ?>">
        <input type="hidden" id="hiddenDateInput" value="">
        <!-- Include jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- Include DataTables JS -->
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

        <!-- Bootstrap JS Bundle -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>

        <!-- Flatpickr JS -->
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

        <!-- Select2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <!-- Your Custom Script -->
        <script src="script.js"></script>
    </body>
</html>
<?php
} else {
    header(header: "Location: ../../../login.php");
    exit();
}
?>