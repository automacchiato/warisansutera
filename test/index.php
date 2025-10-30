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

    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
                    <li class="nav-item"><a class="nav-link disabled">Disabled</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container p-4">
        <h2 class="text-center mb-4">Invoices</h2>

        <table id="invoiceTable" class="table table-bordered table-striped align-middle">
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
                        <td><?= htmlspecialchars($row['invoice_number']) ?></td>
                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td><?= htmlspecialchars($row['order_date']) ?></td>
                        <td><?= htmlspecialchars($row['delivery_date']) ?></td>
                        <td class="text-center">
                            <a href="workslip_pdf.php?invoice_id=<?= $row['invoice_id'] ?>" class="btn btn-primary btn-sm" target="_blank">Show Workslip</a>
                            <a href="deleteworkslip.php?invoice_id=<?= $row['invoice_id'] ?>" class="btn btn-danger btn-sm"">Delete Workslip</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- jQuery + Bootstrap + DataTables JS -->
    <script src=" https://code.jquery.com/jquery-3.7.0.min.js"></script>
                                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                                <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
                                <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

                                <script>
                                    $(document).ready(function() {
                                        $('#invoiceTable').DataTable({
                                            "order": [
                                                [0, "desc"]
                                            ],
                                            "pageLength": 10,
                                            "lengthMenu": [5, 10, 25, 50, 100],
                                            "language": {
                                                "search": "Search invoices:",
                                                "lengthMenu": "Show _MENU_ entries per page",
                                                "info": "Showing _START_ to _END_ of _TOTAL_ invoices",
                                                "paginate": {
                                                    "first": "First",
                                                    "last": "Last",
                                                    "next": "Next",
                                                    "previous": "Prev"
                                                }
                                            }
                                        });
                                    });
                                </script>

</body>

</html>