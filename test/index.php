<?php
$host = "127.0.0.1:3306";
$user = "u929965336_wssb";
$pass = "Sutera@23";
$dbname = "u929965336_warisansutera";

$conn = new mysqli($host, $user, $pass, $dbname);
$result = $conn->query("SELECT i.invoice_id, i.invoice_number, i.order_date, i.delivery_date,
c.customer_name, c.customer_phone
FROM invoices i
JOIN customers c ON i.customer_id = c.customer_id
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">
    <div class="container">
        <h2 class="mb-4">Invoices</h2>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Invoice No</th>
                    <th>Customer</th>
                    <th>Order Date</th>
                    <th>Delivery Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['invoice_number'] ?></td>
                        <td><?= $row['customer_name'] ?></td>
                        <td><?= $row['order_date'] ?></td>
                        <td><?= $row['delivery_date'] ?></td>
                        <td>
                            <a href="generate_pdf.php?invoice_id=<?= $row['invoice_id'] ?>" class="btn btn-danger btn-sm">Download PDF</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>

</html>