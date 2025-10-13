<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "127.0.0.1:3306";
$user = "u929965336_wssb";
$pass = "Sutera@23";
$dbname = "u929965336_warisansutera";

$conn = new mysqli($host, $user, $pass, $dbname);

$result = $conn->query("
    SELECT i.invoice_id, i.invoice_number, i.order_date, i.delivery_date,
           c.customer_name, c.customer_phone
    FROM invoices i
    JOIN customers c ON i.customer_id = c.customer_id
    ORDER BY i.invoice_id DESC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ✅ Bootstrap Table CSS -->
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.css">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">CMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link disabled" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php">Overview</a></li>
                    <li class="nav-item"><a class="nav-link" href="../customer/add_order.php">Add Order</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container p-4">
        <h2 class="text-center mb-4">Invoices</h2>

        <!-- ✅ Bootstrap Table -->
        <table
            id="invoiceTable"
            class="table table-striped table-bordered"
            data-toggle="table"
            data-search="true"
            data-pagination="true"
            data-page-size="10"
            data-page-list="[5, 10, 20, 50, 100, all]"
            data-sortable="true">
            <thead class="table-dark">
                <tr>
                    <th data-field="invoice_number" data-sortable="true">Invoice No</th>
                    <th data-field="customer_name" data-sortable="true">Customer</th>
                    <th data-field="order_date" data-sortable="true">Order Date</th>
                    <th data-field="delivery_date" data-sortable="true">Delivery Date</th>
                    <th data-field="action">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['invoice_number']) ?></td>
                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td><?= htmlspecialchars($row['order_date']) ?></td>
                        <td><?= htmlspecialchars($row['delivery_date']) ?></td>
                        <td>
                            <a href="generate_pdf.php?invoice_id=<?= $row['invoice_id'] ?>" class="btn btn-primary btn-sm">Invoice</a>
                            <a href="workslip_pdf.php?invoice_id=<?= $row['invoice_id'] ?>" class="btn btn-warning btn-sm">Workslip</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- ✅ Bootstrap & Bootstrap Table JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.js"></script>
</body>

</html>