<?php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['employee_id']) && $_SESSION['user_type'] === "admin") {

// Include the database connection file
include '../../../connect.php';
date_default_timezone_set('Asia/Manila');

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

            <title>Machines | List</title>
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
                                <a href="index.php" class="sidebar-link">List</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="../maintenance/index.php" class="sidebar-link">Maintenance</a>
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
                <!-- Default Look -->
                <div class="default-view">
                    <div class="row">
                        <div class="col">
                            <div class="d-flex justify-content-between align-items-center" style="padding: 20px 0 20px 0;">
                                <!-- Left: Heading -->
                                <h1 class="title" style="padding-left: 20px;">Machines</h1>
                                
                                <!-- Right: Buttons and Dropdowns -->
                                <div class="d-flex justify-content-end align-items-center gap-2" style="padding-right: 20px;">
                                    <button type="button" id="addNewMachineBtn" class="btn btn-outline-primary">Add a machine</button>
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
                    <table id="itemTable" class="table table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;"><input type="checkbox" id="selectAll"></th>
                                <th class="text-start" style="padding-left: 13px;">Machine</th>
                                <th class="text-start" style="padding-left: 13px;">Department</th>
                                <th class="text-start" style="padding-left: 13px;">Under Repair?</th>
                                <th class="text-start" style="padding-left: 13px;">Warranty Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            try {
                                $sql = "SELECT 
                                            machine.machine_id AS official_machine_id,
                                            machine.*, 
                                            warranty.*, 
                                            department.*,
                                            employee.*, 
                                            repair_request.*
                                        FROM machine 
                                        LEFT JOIN department ON machine.machine_department_id = department.department_id 
                                        LEFT JOIN employee ON machine.machine_created_by = employee.employee_id
                                        -- Join with the latest warranty for each machine
                                        LEFT JOIN warranty ON machine.machine_id = warranty.machine_id 
                                                            AND warranty.warranty_end_date = (
                                                                SELECT MAX(warranty_end_date) 
                                                                FROM warranty 
                                                                WHERE warranty.machine_id = machine.machine_id
                                                            )
                                        LEFT JOIN repair_request ON machine.machine_id = repair_request.machine_id
                                                                AND repair_request.date_repaired IS NULL
                                                                AND repair_request.date_requested = (
                                                                    SELECT MAX(repair_request.date_requested) 
                                                                    FROM repair_request 
                                                                    WHERE repair_request.machine_id = machine.machine_id
                                                                );";

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
                                    data-warranty-company-name='" . htmlspecialchars($row['warranty_provider'] ?? 'Not available') . "'
                                    data-warranty-start-date='" . htmlspecialchars($row['warranty_start_date'] ? (new DateTime($row['warranty_start_date']))->format('d M Y') : 'Not available') . "'
                                    data-warranty-end-date='" . htmlspecialchars($row['warranty_end_date'] ? (new DateTime($row['warranty_end_date']))->format('d M Y') : 'Not available') . "'
                                    data-warranty-coverage-details='" . htmlspecialchars($row['warranty_coverage_details'] ?? 'Not available') . "'
                                    data-warranty-contact-info='" . htmlspecialchars($row['warranty_contact_info'] ?? 'Not available') . "'
                                    data-year-of-manifacture='" . htmlspecialchars($row['machine_year_of_manufacture'] ?? '0') . "'>

                                    <td class='text-center align-middle'><input type='checkbox' class='row-checkbox'></td>

                                    <td class='text-start'>
                                        <img src='" . htmlspecialchars($imageData) . "' 
                                            alt='Profile Picture' class='me-2 align-middle' style='width: 40px; object-fit: cover;'>
                                        <span>" . htmlspecialchars($row['machine_name'] ?? '') . "</span>
                                    </td>

                                    <td class='text-start align-middle'>" . htmlspecialchars($row['department_name'] ?? '') . "</td>
                                    <td class='text-start align-middle'>" . 
                                        htmlspecialchars($row['date_repaired'] === null ? 'No' : 'Yes') . 
                                    "</td>";
                                        // Check if the warranty is active, expired, or not applicable
                                        if($row['warranty_status'] === 'Active') {
                                            echo "<td class='text-start align-middle text-success'>" . htmlspecialchars($row['warranty_status'] ?? '') . "</td> </tr>";
                                        }
                                        else if($row['warranty_status'] === 'Expired') {
                                            echo "<td class='text-start align-middle text-danger'>" . htmlspecialchars($row['warranty_status'] ?? '') . "</td> </tr>";
                                        }
                                        else {
                                            echo "<td class='text-start align-middle'>" . htmlspecialchars($row['warranty_status'] ?? 'No warranty') . "</td> </tr>";
                                        }
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='5'>Error fetching data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
                <!-- Add Machine View -->
                <div class="add-machine-view p-2">
                    <div class="text-end">
                        <button class="btn btn-outline-secondary" id="closeAddMachineBtn" style="margin-right: 200px; margin-top: 20px;">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="container mt-4 w-100">
                        <div class="row">
                            <div class="col-md-8 offset-md-2">
                                <!-- Stepper -->
                                <div class="steps d-flex justify-content-between mb-4">
                                    <button class="btn btn-primary" id="step1-button"> 1</button>
                                    <button class="btn btn-secondary" id="step2-button"> 2</button>
                                    <button class="btn btn-secondary" id="step3-button"> 3</button>
                                    <button class="btn btn-secondary" id="step4-button"> 4</button>
                                </div>

                                <!-- Step 1 -->
                                <div id="step1" class="step p-4">
                                    <h3 class="pt-4">Step 1: Basic Details<span class="text-danger">*</span></h3>
                                    <p>Add the new machine's basic details</p>

                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <strong>Machine Name:</strong>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" id="machineName">
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <strong>Serial Number:</strong>
                                            <span 
                                                class="text-muted" 
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="right" 
                                                title="12 or 16-character alphanumeric code on the machine">
                                                <i class="bi bi-question-circle"></i>
                                            </span>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" id="serialNumber">
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <strong>Description:</strong>
                                        </div>
                                        <div class="col-md-8">
                                            <textarea class="form-control" id="machineDescription" style="height: 100px;"></textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <strong>Department:</strong>
                                        </div>
                                        <div class="col-md-8">
                                            <select class="form-select" id="department">
                                                <option value="" disabled selected>Select a department</option>
                                                <?php
                                                    try {
                                                        $sql = "SELECT * FROM department";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->execute();
                                                        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                        foreach ($departments as $department) {
                                                            echo "<option value='" . htmlspecialchars($department['department_id']) . "'>" . htmlspecialchars($department['department_name']) . "</option>";
                                                        }
                                                    } catch (PDOException $e) {
                                                        echo "Error fetching data: " . htmlspecialchars($e->getMessage());
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <strong>Manufacturer:</strong>
                                        </div>
                                        <div class="col-md-8">
                                            <select class="form-select" id="manufacturer">
                                                <option value="" disabled selected>Select or insert a manufacturer</option>
                                                <?php
                                                    try {
                                                        $sql = "SELECT * FROM manufacturer";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->execute();
                                                        $manufacturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                        foreach ($manufacturers as $manufacturer) {
                                                            echo "<option value='" . htmlspecialchars($manufacturer['manufacturer_id']) . "'>" . htmlspecialchars($manufacturer['manufacturer_name']) . "</option>";
                                                        }
                                                    } catch (PDOException $e) {
                                                        echo "Error fetching data: " . htmlspecialchars($e->getMessage());
                                                    }
                                                ?>
                                                <option value="other">Other</option>
                                            </select>
                                            <input 
                                                type="text" 
                                                class="form-control mt-2 d-none" 
                                                id="newManufacturer" 
                                                placeholder="Enter new manufacturer name">
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <strong>Manufactured Date:</strong>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="date" class="form-control" id="manufacturedDate">
                                        </div>
                                    </div>
                                    <hr>
                                    <p class="mb-4">Upload machine image and manual (Optional)</p>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <strong>Machine Image:</strong>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="file" class="form-control" id="machineImage" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <strong>Machine Manual:</strong>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="file" class="form-control" id="machineManual" accept="application/pdf">
                                        </div>
                                    </div>
                                    <button class="btn btn-primary" onclick="nextStep(2)">Continue</button>
                                </div>

                                <!-- Step 2 -->
                                <div id="step2" class="step d-none">
                                    <h3>Step 2: Machine Parts<span class="text-danger">*</span></h3>
                                    <p>Select a template to specify machine parts</p>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <strong>Machine Template:</strong>
                                        </div>
                                        <div class="col-md-8">
                                            <select class="form-select" id="template">
                                                <option value="" disabled selected>Select a template</option>
                                                <?php
                                                    try {
                                                        $sql = "SELECT * FROM machine_type";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->execute();
                                                        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                        foreach ($templates as $template) {
                                                            echo "<option value='" . htmlspecialchars($template['machine_type_id']) . "'>" . htmlspecialchars($template['machine_type_name']) . "</option>";
                                                        }
                                                    } catch (PDOException $e) {
                                                        echo "Error fetching data: " . htmlspecialchars($e->getMessage());
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div id="machinePartsList" class="row" style="margin-left: 4px;"></div>
                                        <button id="addPartButton" class="btn btn-primary mt-3 d-none text-center" style="margin-left: 10px; width: 98%; position: relative;">Add Part</button>
                                        <div id="addPartForm" class="mt-4 d-none">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Add New Machine Part</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Part Name:</label>
                                                        <input type="text" class="form-control" id="newPartName" placeholder="Enter part name">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Description:</label>
                                                        <textarea class="form-control" id="newPartDescription" placeholder="Enter part description"></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Quantity:</label>
                                                        <input type="number" class="form-control" id="newPartQuantity" value="1" min="1">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Maintenance Interval (operating hours): 
                                                            <span 
                                                                class="text-muted" 
                                                                data-bs-toggle="tooltip" 
                                                                data-bs-placement="right" 
                                                                title="How many hours before the part needs to be maintained/cleaned?">
                                                                <i class="bi bi-question-circle"></i>
                                                            </span>
                                                        </label>
                                                        <input type="number" class="form-control" id="newPartMaintenanceInterval" value="100">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Replacement Lifespan (operating hours):
                                                            <span 
                                                                class="text-muted" 
                                                                data-bs-toggle="tooltip" 
                                                                data-bs-placement="right" 
                                                                title="How many hours before the part needs to be replaced?">
                                                                <i class="bi bi-question-circle"></i>
                                                            </span>
                                                        </label>
                                                        <input type="number" class="form-control" id="newPartReplacementLifespan" value="1000">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Criticality Level:</label>
                                                        <select class="form-select" id="newPartCriticalityLevel">
                                                            <option value="Low">Low</option>
                                                            <option value="Medium">Medium</option>
                                                            <option value="High">High</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Maintenance Instructions:</label>
                                                        <textarea class="form-control" id="newPartInstructions" placeholder="Enter maintenance instructions"></textarea>
                                                    </div>
                                                    <button id="savePartButton" class="btn btn-success">Save Part</button>
                                                    <button id="cancelPartButton" class="btn btn-secondary">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button class="btn btn-secondary" onclick="prevStep(1)">Previous</button>
                                    <button class="btn btn-primary" onclick="nextStep(3)" disabled id="nextStepButton3">Continue</button>
                                </div>

                                <!-- Step 3 -->
                                <div id="step3" class="step d-none">
                                    <h3>Step 3: Warranty (Optional)</h3>
                                    <p>If applicable, add the warranty details of the machine.</p>
                                    <!-- Warranty Checkbox -->
                                    <div class="row mb-4">
                                        <label>
                                            <input type="checkbox" id="warrantyToggle"> This machine is under a warranty
                                        </label>
                                    </div>

                                    <!-- Warranty Details Section -->
                                    <div id="warrantyDetails" style="display:none; margin-top: 20px; margin-bottom: 20px;">
                                        <div class="card p-2">
                                            <div class="card-header">
                                                <h5 class="card-title">Warranty Details<span class="text-danger">*</span></h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group mb-3">
                                                    <label for="providerName">Provider Name:</label>
                                                    <input type="text" id="providerName" class="form-control" placeholder="Enter Provider Name">
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label for="coverageType">Coverage Type:</label>
                                                    <select id="coverageType" class="form-control">
                                                        <option value="fullMachine">Full Machine</option>
                                                        <option value="specificParts">Specific Parts</option>
                                                        <option value="otherServices">Other Services</option>
                                                    </select>
                                                </div>

                                                <!-- Container for specific parts (checklist) -->
                                                <div id="specificPartsContainer" class="d-none mb-3">
                                                    <label>Select parts under warranty (at least 1):</label>
                                                    <div id="specificPartsList"></div>
                                                </div>

                                                <!-- Container for other services (text input) -->
                                                <div id="otherServicesContainer" class="d-none mb-3">
                                                    <label for="otherServiceTitle">Service Title:</label>
                                                    <input type="text" id="otherServiceTitle" class="form-control" placeholder="Enter service title (e.g., Free 1-time deep clean)">
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label for="startDate">Start Date:</label>
                                                    <input type="date" id="startDate" class="form-control">
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label for="expirationDate">Expiration Date:</label>
                                                    <input type="date" id="expirationDate" class="form-control">
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label for="termsConditions">Terms and Conditions:</label>
                                                    <textarea id="termsConditions" class="form-control" placeholder="Add important warranty terms"></textarea>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label for="warrantyDocument">Add warranty document: 
                                                        <span 
                                                            class="text-muted" 
                                                            data-bs-toggle="tooltip" 
                                                            data-bs-placement="right" 
                                                            title="Receipts, contracts, etc.">
                                                            <i class="bi bi-question-circle"></i>
                                                        </span>
                                                    </label>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-grow-1">
                                                            <input type="file" id="warrantyDocument" class="form-control" accept=".pdf,.png,.jpg">
                                                            <small class="form-text text-muted">Only PDF, PNG, or JPG files are allowed. Maximum size: 25MB.</small>
                                                        </div>
                                                        <button type="button" id="clearFile" class="btn btn-danger ms-3 align-self-start">
                                                            <i class="bi bi-trash3-fill"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <h5 class="card-title" style="margin-top: 35px;">Contact Details<span class="text-danger">*</span></h5>
                                                <hr>
                                                <div class="form-group mb-3">
                                                    <label for="contactName">Contact Person:</label>
                                                    <input type="text" id="contactName" class="form-control" placeholder="Enter Contact Person">
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label for="contactNumber">Contact Number:</label>
                                                    <input type="text" id="contactNumber" class="form-control" placeholder="Enter Contact Number">
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label for="contactEmail">Contact Email:</label>
                                                    <input type="email" id="contactEmail" class="form-control" placeholder="Enter Contact Email">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button class="btn btn-secondary" onclick="prevStep(2)">Previous</button>
                                    <button class="btn btn-primary" onclick="nextStep(4)" id="nextStepButton4">Continue</button>
                                </div>

                                <!-- Step 4 -->
                                <div id="step4" class="step d-none">
                                    <h3>Step 4: Review and Submit</h3>
                                    <p>Set notification preferences and check the machine details.</p>
                                    <div id="notificationDetails" style="margin-top: 20px; margin-bottom: 20px;">
                                        <div class="card p-2">
                                            <div class="card-header">
                                                <h5 class="card-title">Notification Preferences<span class="text-danger">*</span></h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group mb-3">
                                                    <label for="notificationEmail">Notification Email:</label>
                                                    <input type="email" id="notificationEmail" class="form-control" placeholder="Enter email to receive notification">
                                                </div>
                                                <!-- <div class="form-group mb-3">
                                                    <label for="notificationPhone">Notification Phone (optional):</label>
                                                    <input type="text" id="notificationPhone" class="form-control" placeholder="Enter phone number to receive notification">
                                                </div> -->
                                                <div class="form-group mb-3">
                                                    <label>
                                                        Notify me 
                                                        <input type="number" class="form-control d-inline-block mx-2" id="maintenanceNotifyDays" style="width: 80px;" placeholder="7" min="7" step="1">
                                                        days before the part requires maintenance or replacement
                                                    </label>
                                                </div>
                                                <div id="notifyWarranty" class="form-group mb-3" style="display: none;">
                                                    <label>
                                                        Notify me 
                                                        <input type="number" class="form-control d-inline-block mx-2" id="warrantyNotifyWeeks" style="width: 80px;" placeholder="1" min="1" step="1">
                                                        week(s) before the warranty expires
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="machineDetails" class="card p-2" style="margin-top: 20px; margin-bottom: 20px;">
                                        <div class="card-header">
                                            <h5 class="card-title">Machine Details<span class="text-danger">*</span></h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-hover mb-3">
                                                <thead>
                                                    <tr>
                                                        <th>Basic Information</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Machine Name</td>
                                                        <td id="machineNameText">[Machine Name]</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Machine Serial Number</td>
                                                        <td id="machineSerialNumberText">[Machine Serial Number]</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Machine Description</td>
                                                        <td id="machineDescriptionText">[Machine Description]</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Machine Department</td>
                                                        <td id="machineDepartmentText">[Machine Department]</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Machine Manufacturer</td>
                                                        <td id="machineManufacturerText">[Machine Manufacturer]</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Machine Manufactured Date</td>
                                                        <td id="machineManufacturedDateText">[Machine Manufactured Date]</td>
                                                    </tr>
                                                    <!-- <tr>
                                                        <td>Machine Image</td>
                                                        <td id="machineImageText">[Machine Image]</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Machine Manual</td>
                                                        <td id="machineManualText">[Machine Manual]</td>
                                                    </tr> -->
                                                </tbody>
                                            </table>

                                            <table class="table table-hover mb-3" id="machinePartsTable">
                                                <thead>
                                                    <tr>
                                                        <th>Part Name</th>
                                                        <th>Description</th>
                                                        <th>Quantity</th>
                                                        <th>M.I. 
                                                            <span 
                                                                class="text-muted" 
                                                                data-bs-toggle="tooltip" 
                                                                data-bs-placement="right" 
                                                                title="Maintenance Interval Hours">
                                                                <i class="bi bi-question-circle"></i>
                                                            </span>
                                                        </th>
                                                        <th>R.L.
                                                            <span 
                                                                class="text-muted" 
                                                                data-bs-toggle="tooltip" 
                                                                data-bs-placement="right" 
                                                                title="Replacement Lifespan Hours">
                                                                <i class="bi bi-question-circle"></i>
                                                            </span>
                                                        </th>
                                                        <th>C.L.
                                                            <span 
                                                                class="text-muted" 
                                                                data-bs-toggle="tooltip" 
                                                                data-bs-placement="right" 
                                                                title="Critical Level">
                                                                <i class="bi bi-question-circle"></i>
                                                            </span></th>
                                                        <th>Maintenance Instructions</th>
                                                        <th>Under Warranty?</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <!-- Part details will be populated here -->
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <table class="table table-hover mb-3 d-none" id="warrantyTable">
                                                <thead>
                                                    <tr>
                                                        <th>Warranty Details</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Provider Name</td>
                                                        <td id="warrantyProviderNameText">[Warranty Provider Name]</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Coverage Type</td>
                                                        <td id="warrantyCoverageTypeText">[Warranty Coverage Type]</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Start Date</td>
                                                        <td id="warrantyStartDateText">[Warranty Start Date]</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Expiration Date</td>
                                                        <td id="warrantyExpirationDateText">[Warranty Expiration Date]</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Terms and Conditions</td>
                                                        <td id="warrantyTermsAndConditionsText">[Warranty Terms and Conditions]</td>
                                                    </tr>
                                                    <!-- <tr>
                                                        <td>Warranty Document</td>
                                                        <td id="warrantyDocumentText">[Warranty Document]</td>
                                                    </tr> -->
                                                    <tr>
                                                        <td>Contact Person</td>
                                                        <td id="warrantyContactPersonText">[Warranty Contact Person]</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Contact Number</td>
                                                        <td id="warrantyContactNumberText">[Warranty Contact Number]</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Contact Email</td>
                                                        <td id="warrantyContactEmailText">[Warranty Contact Email]</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <button class="btn btn-secondary" onclick="prevStep(3)">Previous</button>
                                    <button class="btn btn-success" id="submitRepairUpdate">Submit</button>
                                </div>
                            </div>
                        </div>
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

        <!-- Machine Info Modal -->
        <div id="machineInfoModal" class="offcanvas offcanvas-end" tabindex="-1" style="padding: 20px;" data-bs-scroll="true">
            <div class="offcanvas-header d-flex justify-content-between">
                <!-- Left Side Top: Machine Name -->
                <div>
                    <h5 class="offcanvas-title" id="modalMachineNameText">[Machine Name]</h5>
                    <p class="mb-0 text-muted">Machine Code: <span id="modalMachineCodeText">[Machine Code]</span></p>
                </div>

                <!-- Right Side Top: Edit, Delete, and Close Buttons -->
                <div>
                    <button id="editMachineBtn" class="btn btn-outline-primary me-2" title="Edit">Edit</button>
                    <button id="deleteMachineBtn" class="btn btn-outline-danger me-2" title="Delete">
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
                            <label for="modalMachineName" class="form-label"><strong>Machine Name:</strong></label>
                            <input type="text" id="modalMachineName" class="form-control" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="modalDepartmentName" class="form-label"><strong>Department:</strong></label>
                            <select id="modalDepartmentName" class="form-select" disabled>
                                <option value="1">Engineering</option>
                                <option value="2">Warehouse</option>
                                <option value="4">Motorpool</option>
                                <option value="7">Logistics</option>
                                <option value="8">HR & Admin</option>
                                <option value="9">Production & Packaging</option>
                                <option value="10">Production & Packaging</option>
                                <option value="11">Production & Packaging</option>
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
                        <div id="underWarrantySection" class="mt-3 d-none">
                            <button id="warrantyDescription" type="button" class="btn btn-warning"><i class="bi bi-info-circle"></i> Under Warranty</button>
                        </div>
                        <div class="text-start bg-light p-3 rounded border" style="padding: 10px; margin-top: 20px;">
                            <p><strong>Upcoming Maintenance(s):</strong></p>
                            <p><strong><span id="modalMaintenanceDateText" class="text-muted"></span></strong></p>
                            <p><span id="modalMaintenancePartText" class="text-muted"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Warranty Modal -->
        <div class="modal fade" id="warrantyModal" tabindex="-1" role="dialog" aria-labelledby="warrantyModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document" style="top: 10%; right: 10%;">
                <div class="modal-content" style="max-height: 70vh; max-width: 70vh; overflow-y: auto;">
                    <div class="modal-header">
                        <h5 class="modal-title" id="warrantyModalLabel">Warranty Details</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <strong>Company Name:</strong>
                            <p id="companyName" class="text-muted">Example Company</p>
                        </div>
                        <div class="mb-3">
                            <strong>Start Date:</strong>
                            <p id="startDate" class="text-muted">01 Jan 2023</p>
                        </div>
                        <div class="mb-3">
                            <strong>End Date:</strong>
                            <p id="endDate" class="text-muted">01 Jan 2025</p>
                        </div>
                        <div class="mb-3">
                            <strong>Details:</strong>
                            <p id="details" class="text-muted">This warranty covers all mechanical failures.</p>
                        </div>
                        <div class="mb-3">
                            <strong>Contact Number:</strong>
                            <p id="contactNumber" class="text-muted">+123456789</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Machine Maintenance Interval-->
        <div class="modal fade" id="machineIntervalModal" tabindex="-1">
            <div class="modal-dialog" style="top: 10%; right: 10%;">
                <div class="modal-content" style="max-height: 40vh; overflow-y: auto;">
                <div class="modal-header">
                    <h5 class="modal-title">Maintenance Interval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Set it in days:</strong></p>
                    <input type="number" id="machineMaintenanceIntervalInput" min="1" max="100" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="updateMachineMaintenanceIntervalBtn" class="btn btn-primary">Update</button>
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
        <!-- Confirmation Modal for Deletion of Machine -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" style="top: 10%; right: 10%;">
                <div class="modal-content" style="max-height: 50vh; max-width: 50vh; overflow-y: auto;">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>To confirm deletion, please type the machine name:</p>
                        <div id="itemNameDisplay" style="color: gray; opacity: 0.6; margin-bottom: 10px;">
                            <span id="displayMachineName"></span>
                        </div>
                        <input type="text" id="confirmMachineName" class="form-control" placeholder="Machine Name" required>
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

        <!-- Add Item Offcanvas Modal -->
        <div id="addMachineModal" class="offcanvas offcanvas-end" tabindex="-1" style="padding: 20px; width: 45%;" data-bs-scroll="true" aria-labelledby="addItemModalLabel" aria-modal="true" role="dialog">
            <div class="offcanvas-header d-flex justify-content-between">
                <h5 class="offcanvas-title" id="addItemModalLabel">New Machine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <hr>
            <div class="offcanvas-body">
                <form id="addUserForm">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="addMachineName" class="form-label"><strong>Machine Name:</strong></label>
                            <input type="text" class="form-control" id="addMachineName" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="addManufacturer" class="form-label"><strong>Manufacturer:</strong></label>
                            <input type="text" id="addManufacturer" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4 mb-4">
                            <label for="addType" class="form-label"><strong>Type:</strong></label>
                            <input type="text" id="addType" class="form-control" required>
                        </div>
                        <div class="col-4 mb-4">
                            <label for="addModel" class="form-label"><strong>Model:</strong></label>
                            <input type="text" class="form-control" id="addModel" required>
                        </div>
                        <div class="col-4 mb-4">
                            <label for="addManufactureYear" class="form-label"><strong>Manufactured Year:</strong></label>
                            <input type="number" id="addManufactureYear" class="form-control" min="1950" max="2024" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="addDepartmentID" class="form-label"><strong>Department:</strong></label>
                            <select id="addDepartmentID" class="form-select">
                                <option value="1">Engineering</option>
                                <option value="2">Warehouse</option>
                                <option value="3">Motorpool</option>
                                <option value="4">Logistics</option>
                                <option value="5">HR & Admin</option>
                                <option value="6">Production & Packaging</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="addMaintenanceInterval" class="form-label"><strong>Maintenance Interval:</strong></label>
                            <input type="number" id="addMaintenanceInterval" min="1" max="30" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-6">
                            <label for="addItemImage" class="form-label"><strong>Machine Image</strong></label>
                            <br>
                            <img id="previewItemImage" src="../../../images/gallery.png" alt="Profile Preview" class="img-thumbnail text-center" style="width: 120px; height: 120px;">
                            <input type="file" class="form-control" id="addItemImage" accept="image/*">
                        </div>
                        <div class="mb-3 col-6">
                            <label for="addMachineDescription" class="form-label"><strong>Description:</strong></label>
                            <textarea id="addMachineDescription" class="form-control" required style="height: 150px;"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-6"></div>
                        <div class="mb-3 col-6 align-items-center">
                            <button type="button" id="addWarrantyBtn" class="btn btn-outline-warning">Add warranty</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="offcanvas-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Close</button>
                <button type="button" class="btn btn-primary" id="saveItemBtn">Add Machine</button>
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
    header(header: "Location: ../../../login.php");
    exit();
}
?>