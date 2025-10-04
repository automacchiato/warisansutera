<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('fpdf186/fpdf.php');

$host = "127.0.0.1:3306";
$user = "u929965336_wssb";
$pass = "Sutera@23";
$dbname = "u929965336_warisansutera";

$conn = new mysqli($host, $user, $pass, $dbname);

$invoice_id = intval($_GET['invoice_id']);

// Fetch invoice + customer
$invoice_sql = "
    SELECT i.*, c.*
    FROM invoices i
    JOIN customers c ON i.customer_id = c.customer_id
    WHERE i.invoice_id = $invoice_id;
";
$invoice = $conn->query($invoice_sql)->fetch_assoc();

// Fetch items
$item_sql = "
    SELECT it.item_id, it.item_type, it.quantity, it.fabric_code, it.fabric_name, it.fabric_color, it.fabric_usage 
    FROM invoice_items it
    WHERE it.invoice_id = $invoice_id
";
$items = $conn->query($item_sql);

// ---------------- Page 1: Invoice ----------------
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
$pdf->Cell(140, 7, $invoice['customer_name'], 1, 1);

$pdf->Cell(50, 7, "Address", 1);
$pdf->Cell(140, 7, $invoice['customer_address'], 1, 1);

$pdf->Cell(50, 7, "Telephone", 1);
$pdf->Cell(140, 7, $invoice['customer_phone'], 1, 1);

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
$pdf->SetX(110);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60, 7, "Total Amount", 1);
$pdf->Cell(30, 7, $invoice['total_amount'], 1, 1, "R");

$pdf->SetX(110);
$pdf->Cell(60, 7, "Deposit", 1);
$pdf->Cell(30, 7, $invoice['deposit_amount'], 1, 1, "R");

$pdf->SetX(110);
$pdf->Cell(60, 7, "Balance", 1);
$pdf->Cell(30, 7, $invoice['balance_amount'], 1, 1, "R");

$pdf->SetX(110);
$pdf->Cell(60, 7, "Additional Deposit", 1);
$pdf->Cell(30, 7, $invoice['additional_deposit'], 1, 1, "R");

$pdf->SetX(110);
$pdf->Cell(60, 7, "Final Balance", 1);
$pdf->Cell(30, 7, $invoice['additional_amount'], 1, 1, "R");

// ---------------- Page 2: Workslip ----------------
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, "Workslip", 0, 1, 'C');
$pdf->Ln(5);

$items->data_seek(0);
while ($row = $items->fetch_assoc()) {
    // $pdf->SetFont('Arial', 'B', 14);
    // $pdf->Cell(0, 10, $row['item_type'] . " (x" . $row['quantity'] . ")", 0, 1);
    $pdf->SetFont('Arial', '', 12);

    $item_id = $row['item_id'];

    // Fetch details from correct workslip table
    switch (strtoupper($row['item_type'])) {
        case 'SHIRT':
            $sql = "SELECT * FROM workslip_shirts WHERE item_id = $item_id";
            $work = $conn->query($sql)->fetch_assoc();

            //Line 1
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(38, 8, "Invoice No.", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(38, 8, $invoice['invoice_number'], 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(38, 8, "Manufacturer", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(38, 8, $work['manufacturer'], 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(38, 8, "MUST", 1, 1, "C");

            //Line 2
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(31.7, 8, "Salesman", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(31.7, 8, $work['salesman_name'], 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(31.7, 8, "Cutter", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(31.7, 8, $work['cutter_name'], 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(31.7, 8, "Tailor", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(31.6, 8, $work['tailor_name'], 1, 1);

            //Line 3
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(47.5, 8, "Fitting Date", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(47.5, 8, $invoice['fitting_date'], 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(47.5, 8, "Deliver Date", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(47.5, 8, $invoice['delivery_date'], 1, 1);

            //Line 4
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(47.5, 8, "Gender", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(47.5, 8, $work['gender'], 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(47.5, 8, "Fabric Direction", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(47.5, 8, $work['fabric_direction'], 1, 1);

            //Line 5
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Collar", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(60, 8, $work['collar_length'], 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Top Initial", 1, 0, "C");
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(70, 8, $work['top_initial'], 1, 1);

            //Line 6
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Back", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(60, 8, $work['back_length'], 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Collar Design", 1, 0, "C");
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(70, 8, "Collar Specification", 1, 1, "C");

            //Line 7
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Front", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(60, 8, $work['front_length'], 1);
            $pdf->Cell(30, 8, $work['collar_design'], 1, 0, "C");
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(17.5, 8, "Width", 1, 0, "C");
            $pdf->Cell(17.5, 8, "Height", 1, 0, "C");
            $pdf->Cell(17.5, 8, "Gap", 1, 0, "C");
            $pdf->Cell(17.5, 8, "Meet", 1, 1, "C");

            //Line 8
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Chest", 1);
            $pdf->Cell(15, 8, "Fit", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(15, 8, $work['chest_fit'], 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(15, 8, "Loose", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(15, 8, $work['chest_loose'], 1);
            $pdf->Cell(30, 8, "", 1, 0, "C");
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(17.5, 8, $work['collar_width'], 1, 0, "C");
            $pdf->Cell(17.5, 8, $work['collar_height'], 1, 0, "C");
            $pdf->Cell(17.5, 8, $work['collar_gap'], 1, 0, "C");
            $pdf->Cell(17.5, 8, $work['collar_meet'], 1, 1, "C");

            //Line 9
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Waist", 1);
            $pdf->Cell(15, 8, "Fit", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(15, 8, $work['waist_fit'], 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(15, 8, "Loose", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(15, 8, $work['waist_loose'], 1, 1);

            //Line 10
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Hip", 1);
            $pdf->Cell(15, 8, "Fit", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(15, 8, $work['hip_fit'], 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(15, 8, "Loose", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(15, 8, $work['hip_loose'], 1, 1);

            //Line 11
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Shoulder", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(60, 8, $work['shoulder'], 1, 1);

            //Line 12
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Sleeve", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(60, 8, $work['sleeve_length'], 1, 1);

            //Line 13
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Arm", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(60, 8, $work['arm_length'], 1, 1);

            //Line 14
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Elbow", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(60, 8, $work['elbow_length'], 1, 1);

            //Line 15
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Cuff", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(60, 8, $work['cuff_length'], 1, 1);

            //Line 16
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Armhole", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(60, 8, $work['armhole_length'], 1, 1);

            //Line 17
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Erect", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(60, 8, $work['erect'], 1, 1);

            //Line 18
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Hunch", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(60, 8, $work['hunch'], 1, 1);

            //Line 18
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Shoulder Type", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(60, 8, $work['shoulder_type'], 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(25, 8, "Placket Type", 1, 0, "C");
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(25, 8, $work['placket_type'], 1, 0, "C");
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(25, 8, "Cuff Type", 1, 0, "C");
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(25, 8, $work['cuff_type'], 1, 1, "C");

            //Line 19
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Corpulent", 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(60, 8, $work['corpulent'], 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Front Cutting", 1, 0, "C");
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(70, 8, $work['front_cutting'], 1, 1);

            //Line 20
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, "Fabric Code", 1, 0, "C");
            $pdf->Cell(60, 8, "Fabric Name", 1, 0, "C");
            $pdf->Cell(30, 8, "Fabric Color", 1, 0, "C");
            $pdf->Cell(35, 8, "Fabric Usage (m)", 1, 0, "C");
            $pdf->Cell(35, 8, "Cleaning Type", 1, 1, "C");

            //Line 21
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 8, $invoice['fabric_code'], 1, 0, "C");
            $pdf->Cell(60, 8, $invoice['fabric_name'], 1, 0, "C");
            $pdf->Cell(30, 8, $invoice['fabric_color'], 1, 0, "C");
            $pdf->Cell(35, 8, $invoice['fabric_usage'], 1, 0, "C");
            $pdf->Cell(35, 8, $work['cleaning_type'], 1, 1, "C");

            //Line 22
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(80, 8, "Bottom Initial", 1, 0, "C");
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(110, 8, $work['bottom_initial'], 1, 1);

            $pdf->Ln(5);

            // $pdf->Ln(5);
            // $pdf->SetFont('Arial', 'B', 12);
            // $pdf->Cell(0, 8, "Measurements", 0, 1);

            // $pdf->SetFont('Arial', '', 11);

            // // Left column
            // $pdf->Cell(50, 8, "Chest", 1);
            // $pdf->Cell(50, 8, $work['chest_fit'], 1);
            // $pdf->Cell(50, 8, "Length", 1);
            // $pdf->Cell(40, 8, $work['collar_length'], 1, 1);

            // $pdf->Cell(50, 8, "Sleeve", 1);
            // $pdf->Cell(50, 8, $work['sleeve_length'], 1);
            // $pdf->Cell(50, 8, "Collar Design", 1);
            // $pdf->Cell(40, 8, $work['collar_design'], 1, 1);

            // $pdf->Cell(50, 8, "Shoulder Type", 1);
            // $pdf->Cell(50, 8, $work['shoulder_type'], 1);
            // $pdf->Cell(50, 8, "Collar Spec (Width)", 1);
            // $pdf->Cell(40, 8, $work['collar_width'], 1, 1);

            // $pdf->Cell(50, 8, "Armhole", 1);
            // $pdf->Cell(50, 8, $work['armhole_length'], 1);
            // $pdf->Cell(50, 8, "Collar Spec (Height)", 1);
            // $pdf->Cell(40, 8, $work['collar_height'], 1, 1);

            // $pdf->Cell(50, 8, "Waist", 1);
            // $pdf->Cell(50, 8, $work['waist_fit'], 1);
            // $pdf->Cell(50, 8, "Collar Spec (Gap)", 1);
            // $pdf->Cell(40, 8, $work['collar_gap'], 1, 1);

            // $pdf->Cell(50, 8, "Hip", 1);
            // $pdf->Cell(50, 8, $work['hip_fit'], 1);
            // $pdf->Cell(50, 8, "Collar Spec (Meet)", 1);
            // $pdf->Cell(40, 8, $work['collar_meet'], 1, 1);

            // $pdf->Ln(5);

            // // Fabric Info
            // $pdf->SetFont('Arial', 'B', 12);
            // $pdf->Cell(0, 8, "Fabric Details", 0, 1);

            // $pdf->SetFont('Arial', '', 11);
            // $pdf->Cell(40, 8, "Fabric Code", 1);
            // $pdf->Cell(40, 8, $row['fabric_code'], 1);
            // $pdf->Cell(40, 8, "Fabric Name", 1);
            // $pdf->Cell(70, 8, $row['fabric_name'], 1, 1);

            // $pdf->Cell(40, 8, "Fabric Color", 1);
            // $pdf->Cell(40, 8, $row['fabric_color'], 1);
            // $pdf->Cell(40, 8, "Fabric Usage (m)", 1);
            // $pdf->Cell(70, 8, $row['fabric_usage'], 1, 1);

            // $pdf->Ln(8);

            // // Extra notes / signatures
            // $pdf->Cell(0, 8, "Special Instructions: " . ($work['special_instructions'] ?? ""), 0, 1);
            // $pdf->Ln(10);
            // $pdf->Cell(90, 8, "Tailor Signature: ___________________", 0, 0, 'L');
            // $pdf->Cell(90, 8, "Customer Signature: _______________", 0, 1, 'R');
            break;


        // case 'TROUSERS':
        //     $sql = "SELECT waist, hips, inseam, pocket_style, fly_type 
        //             FROM trousers_workslip WHERE invoice_item_id = $item_id";
        //     $work = $conn->query($sql)->fetch_assoc();

        //     $pdf->MultiCell(0,8,
        //         "- Waist: ".$work['waist']."\n".
        //         "- Hips: ".$work['hips']."\n".
        //         "- Inseam: ".$work['inseam']."\n".
        //         "- Pocket Style: ".$work['pocket_style']."\n".
        //         "- Fly: ".$work['fly_type']
        //     );
        //     break;

        // case 'JACKET':
        //     $sql = "SELECT shoulder, chest, sleeve, lining, vent_type 
        //             FROM jacket_workslip WHERE invoice_item_id = $item_id";
        //     $work = $conn->query($sql)->fetch_assoc();

        //     $pdf->MultiCell(0,8,
        //         "- Shoulder: ".$work['shoulder']."\n".
        //         "- Chest: ".$work['chest']."\n".
        //         "- Sleeve: ".$work['sleeve']."\n".
        //         "- Lining: ".$work['lining']."\n".
        //         "- Vent: ".$work['vent_type']
        //     );
        //     break;

        default:
            $pdf->MultiCell(0, 8, "- No specific workslip data available.");
            break;
    }

    $pdf->Ln(5);
}

$pdf->Output();
