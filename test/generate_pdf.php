<?php
require('fpdf186/fpdf.php');
$host = "127.0.0.1:3306";
$user = "u929965336_wssb";
$pass = "Sutera@23";
$dbname = "u929965336_warisansutera";

$conn = new mysqli($host, $user, $pass, $dbname);
$id = $_GET['id'];

// Fetch invoice
$invoice = $conn->query("SELECT * FROM invoices WHERE id=$id")->fetch_assoc();

//Fetch customers
$customer = $conn->query("SELECT * FROM customers WHERE invoice_id=$id");

// Fetch items
$items = $conn->query("SELECT * FROM invoice_items WHERE invoice_id=$id");

class PDF extends FPDF
{
    function Header()
    {
        // Company Logo
        $this->Image('logo.png', 10, 10, 30); // replace logo.png with your logo file
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
$pdf->SetXY(140, 10);
$pdf->Cell(50, 5, "Invoice No: " . $invoice['invoice_number'], 0, 1);
$pdf->SetX(140);
$pdf->Cell(50, 5, "Order Date: " . $invoice['order_date'], 0, 1);
$pdf->SetX(140);
$pdf->Cell(50, 5, "Fitting Date: " . $invoice['fitting_date'], 0, 1);
$pdf->SetX(140);
$pdf->Cell(50, 5, "Delivery Date: " . $invoice['delivery_date'], 0, 1);

$pdf->Ln(20);

// --- Customer Table ---
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, "Customer Details", 1, 1, 'C');
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
$headers = ["Qty", "Item Type", "Fabric Code", "Fabric Name", "Fabric Color", "Usage (m)", "Amount", "Total", "Deposit", "Balance", "Add Deposit", "Final Balance"];
$widths = [10, 25, 20, 25, 25, 20, 20, 20, 20, 20, 20, 20];

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
    $pdf->Cell($widths[6], 7, $row['amount'], 1);
    $pdf->Cell($widths[7], 7, $row['total'], 1);
    $pdf->Cell($widths[8], 7, $invoice['deposit'], 1);
    $pdf->Cell($widths[9], 7, $row['balance'], 1);
    $pdf->Cell($widths[10], 7, $invoice['add_deposit'], 1);
    $pdf->Cell($widths[11], 7, $invoice['final_balance'], 1);
    $pdf->Ln();
}

$pdf->Output("I", "Invoice_" . $invoice['invoice_no'] . ".pdf");
