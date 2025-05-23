<?php
include '../../../connect.php';
require('../../../tcpdf/tcpdf.php');

date_default_timezone_set('Asia/Manila');

// Ensure the database connection is initialized
if (!$conn) {
    die("Database connection failed.");
}

// Retrieve parameters
$adjustment_id = isset($_GET['adjustment_id']) ? $_GET['adjustment_id'] : '';
$employee_id = isset($_GET['employee_id']) ? $_GET['employee_id'] : '';
$employee_name = isset($_GET['employee_name']) ? $_GET['employee_name'] : '';

if (empty($adjustment_id) || empty($employee_id) || empty($employee_name)) {
    die('Invalid parameters provided.');
}

// Extend TCPDF to customize watermark and footer
class PDFWithWatermark extends TCPDF {
    private $employee_id;
    private $employee_name;
    private $timestamp;

    // Constructor to receive employee details and timestamp
    public function __construct($employee_id, $employee_name) {
        parent::__construct();
        $this->employee_id = $employee_id;
        $this->employee_name = $employee_name;
        $this->timestamp = date('d M Y, h:i A');
    }

    // Watermark on each page
    public function addWatermark() {
        $this->SetFont('helvetica', 'B', 50);
        $this->SetTextColor(240, 240, 240); // Light gray for watermark

        $this->StartTransform();
        $this->Rotate(45, 105, 105);
        $this->Text(35, 190, $this->employee_id); // Display employee ID as watermark
        $this->StopTransform();
    }

    // Custom footer with employee details and timestamp
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $footer_text = "Generated by: {$this->employee_name} | Employee ID: {$this->employee_id}  | Generated on: {$this->timestamp}";
        $this->Cell(0, 10, $footer_text, 0, 0, 'C');
    }

    // Include watermark on each page by calling it in the Header
    public function Header() {
        parent::Header();
        $this->addWatermark();
    }
}

try {
    // Prepare and execute SQL statement
    $stmtAdjustmentList = $conn->prepare('
        SELECT * FROM item_adjustment_list
        JOIN item ON item_adjustment_list.item_id = item.item_code
        JOIN item_adjustment ON item_adjustment_list.adjustment_id = item_adjustment.adjustment_id
        JOIN item_adjustment_reason ON item_adjustment_reason.reason_id = item_adjustment.reason_id
        WHERE item_adjustment_list.adjustment_id = :adjustment_id
    ');

    $stmtAdjustmentList->bindParam(':adjustment_id', $adjustment_id, PDO::PARAM_STR);
    if (!$stmtAdjustmentList->execute()) {
        throw new Exception("Failed to get adjustment details.");
    }

    $result = $stmtAdjustmentList->fetchAll(PDO::FETCH_ASSOC);
    if (empty($result)) {
        die('No adjustment details found.');
    }

    $firstRow = $result[0]; 

    // Initialize TCPDF with custom watermark and footer
    $pdf = new PDFWithWatermark($employee_id, $employee_name);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle('Inventory Adjustment');
    $pdf->setHeaderFont(array('helvetica', '', 12));
    $pdf->setFooterFont(array('helvetica', '', 10));
    $pdf->SetMargins(15, 27, 15);
    $pdf->SetAutoPageBreak(TRUE, 25);
    $pdf->AddPage();

    // Title and Adjustment Code
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Inventory Adjustment', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 10, 'Inventory Adjustment Code: ' . $firstRow['adjustment_id'], 0, 1, 'C');
    $pdf->Ln(10); // Line break

    // Reset color for details section
    $pdf->SetTextColor(0, 0, 0);

    // Date formatting
    $formattedDate = date('d M Y, h:i A', strtotime($firstRow['entry_date']));

    // Adjustment Details Section
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(50, 10, 'Date:', 0, 0);
    $pdf->Cell(0, 10, $formattedDate, 0, 1);
    $pdf->Cell(50, 10, 'Reason:', 0, 0);
    $pdf->Cell(0, 10, $firstRow['reason'], 0, 1);
    $pdf->Cell(50, 10, 'Adjusted By:', 0, 0);
    $pdf->Cell(0, 10, $firstRow['created_by'], 0, 1);
    $pdf->Cell(50, 10, 'Description:', 0, 0);
    $pdf->Cell(0, 10, $firstRow['description'], 0, 1);
    $pdf->Cell(50, 10, 'Reference Number:', 0, 0);
    $pdf->Cell(0, 10, $firstRow['reference_number'], 0, 1);
    $pdf->Ln(10);

    // Table Header
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(52, 58, 64);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(60, 10, 'Item Details', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'New Quantity', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Quantity Adjusted', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Previous Quantity', 1, 1, 'C', true);

    // Reset font and text color for the table content
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetTextColor(0, 0, 0);

    // Loop through each item in the result and add it to the PDF table
    foreach ($result as $row) {
        $pdf->Cell(60, 20, '', 1, 0); // Reserve space for combined image and name
        $pdf->SetXY($pdf->GetX() - 60, $pdf->GetY());
        if (!empty($row['image'])) {
            $pdf->Image('@' . $row['image'], $pdf->GetX() + 2, $pdf->GetY() + 2, 15, 15);
        } else {
            $pdf->Cell(15, 20, '[No Image]', 0, 0, 'C');
        }

        // Move the cursor to the right side of the image and add the item name
        $pdf->SetXY($pdf->GetX() + 20, $pdf->GetY());
        $pdf->Cell(40, 20, $row['item_name'], 0, 0, 'L');

        // Quantities in separate cells
        $pdf->Cell(40, 20, $row['item_quantity'], 1, 0, 'C');
        $pdf->Cell(40, 20, $row['quantity_adjusted'], 1, 0, 'C');
        $pdf->Cell(40, 20, $row['previous_quantity'], 1, 1, 'C');
    }

    // Output PDF to browser
    $pdf->Output('Inventory_Adjustment_' . $adjustment_id . '.pdf', 'I');

} catch (Exception $e) {
    die('Error retrieving adjustment: ' . $e->getMessage());
}
?>
