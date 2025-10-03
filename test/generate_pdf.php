<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php
require('fpdf186/fpdf.php');

// Database connection
$host = "127.0.0.1:3306";
$user = "u929965336_wssb";
$pass = "Sutera@23";
$dbname = "u929965336_warisansutera";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = intval($_GET['invoice_id']); // always sanitize input

// Fetch invoice
$invoice = $conn->query("SELECT * FROM invoices WHERE invoice_id=$id")->fetch_assoc();

// Fetch customer via JOIN
$customer = $conn->query("
    SELECT c.* 
    FROM customers c 
    JOIN invoices i ON c.customer_id = i.customer_id 
    WHERE i.invoice_id=$id
")->fetch_assoc();

// Fetch items
$items = $conn->query("SELECT * FROM invoice_items WHERE invoice_id=$id");

class PDF extends FPDF
{
    function Header()
    {
        // Company Logo
        $this->Image('logo.jpg', 10, 10, 30);
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY(45, 10);
        $this->MultiCell(80, 5, "Lot C31, Aras 2, Majma Mall,\nKuching, Sarawak", 0, 'L');
        $this->Ln(10);
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// --- Invoice details (top right) ---
$pdf->SetXY(150, 10);
$pdf->Cell(50, 5, "Invoice No: " . $invoice['invoice_number'], 0, 1, "R");
$pdf->Ln();
$pdf->SetX(150);
$pdf->Cell(50, 5, "Order Date: " . $invoice['order_date'], 1, 1, "R");
$pdf->SetX(150);
$pdf->Cell(50, 5, "Fitting Date: " . $invoice['fitting_date'], 1, 1, "R");
$pdf->SetX(150);
$pdf->Cell(50, 5, "Delivery Date: " . $invoice['delivery_date'], 1, 1, "R");

$pdf->Ln(20);

// --- Customer Table ---
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, "Customer Details", 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(50, 7, "Name", 1);
$pdf->Cell(140, 7, $customer['customer_name'], 1, 1);

$pdf->Cell(50, 7, "Address", 1);
$pdf->Cell(140, 7, $customer['customer_address'], 1, 1);

$pdf->Cell(50, 7, "Telephone", 1);
$pdf->Cell(140, 7, $customer['customer_phone'], 1, 1);

$pdf->Ln(10);

// --- Items Table ---
$pdf->SetFont('Arial', 'B', 10);
$headers = ["Qty", "Item Type", "Fabric Code", "Fabric Name", "Fabric Color", "Usage (m)"];
$widths  = [15, 30, 25, 35, 35, 25];

foreach ($headers as $i => $col) {
    $pdf->Cell($widths[$i], 7, $col, 1, 0, 'C');
}
$pdf->Ln();

$pdf->SetFont('Arial', '', 9);
while ($row = $items->fetch_assoc()) {
    $pdf->Cell($widths[0], 7, $row['quantity'], 1);
    $pdf->Cell($widths[1], 7, $row['item_type'], 1);
    $pdf->Cell($widths[2], 7, $row['fabric_code'], 1);
    $pdf->Cell($widths[3], 7, $row['fabric_name'], 1);
    $pdf->Cell($widths[4], 7, $row['fabric_color'], 1);
    $pdf->Cell($widths[5], 7, $row['fabric_usage'], 1);
    $pdf->Ln();
}

$pdf->Ln(10);

// --- Totals from invoices ---
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60, 7, "Total Amount", 1);
$pdf->Cell(60, 7, $invoice['total_amount'], 1, 1);

$pdf->Cell(60, 7, "Deposit", 1);
$pdf->Cell(60, 7, $invoice['deposit_amount'], 1, 1);

$pdf->Cell(60, 7, "Balance", 1);
$pdf->Cell(60, 7, $invoice['balance_amount'], 1, 1);

$pdf->Cell(60, 7, "Additional Deposit", 1);
$pdf->Cell(60, 7, $invoice['additional_deposit'], 1, 1);

$pdf->Cell(60, 7, "Final Balance", 1);
$pdf->Cell(60, 7, $invoice['additional_amount'], 1, 1);

$pdf->Output("I", "Invoice_" . $invoice['invoice_number'] . ".pdf");
