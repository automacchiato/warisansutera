<?php
require("/Users/ramadani/Documents/warisansutera/warisansutera/test/fpdf186/fpdf.php"); // Make sure you download FPDF and place in project folder

$host = "127.0.0.1:3306";
$user = "u929965336_wssb";
$pass = "Sutera@23";
$dbname = "u929965336_warisansutera";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, item_name, category, size, color, price FROM clothing";
$result = $conn->query($sql);

// PDF setup
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont("Arial", "B", 16);
$pdf->Cell(190, 10, "Clothing Inventory Report", 1, 1, "C");
$pdf->Ln(5);

$pdf->SetFont("Arial", "B", 12);
$pdf->Cell(10, 10, "ID", 1);
$pdf->Cell(40, 10, "Item Name", 1);
$pdf->Cell(40, 10, "Category", 1);
$pdf->Cell(20, 10, "Size", 1);
$pdf->Cell(30, 10, "Color", 1);
$pdf->Cell(30, 10, "Price (RM)", 1);
$pdf->Ln();

$pdf->SetFont("Arial", "", 12);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(10, 10, $row['id'], 1);
        $pdf->Cell(40, 10, $row['item_name'], 1);
        $pdf->Cell(40, 10, $row['category'], 1);
        $pdf->Cell(20, 10, $row['size'], 1);
        $pdf->Cell(30, 10, $row['color'], 1);
        $pdf->Cell(30, 10, number_format($row['price'], 2), 1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(190, 10, "No data available", 1, 1, "C");
}

$pdf->Output("D", "clothing_report.pdf"); // Force download
