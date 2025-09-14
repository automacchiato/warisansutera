<?php
include 'db.php';

// Join customers + invoices + items
$query = "
    SELECT 
        i.invoice_id, i.invoice_number, i.order_date, i.fitting_date, i.delivery_date,
        i.total_amount, i.deposit_amount, i.balance_amount,
        c.customer_name, c.customer_phone,
        it.item_type, it.quantity, it.fabric_name, it.fabric_color, it.amount
    FROM invoices i
    JOIN customers c ON i.customer_id = c.customer_id
    JOIN invoice_items it ON i.invoice_id = it.invoice_id
    ORDER BY i.invoice_id DESC
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Invoice Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>

<body class="p-4">
    <div class="container">
        <h2 class="mb-4">Invoice Dashboard</h2>

        <nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">CMS</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="overview.php">Overview</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add_order.php">Add Order</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link disabled" aria-disabled="true">Disabled</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <table id="invoiceTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Order Date</th>
                    <th>Fitting Date</th>
                    <th>Delivery Date</th>
                    <th>Item</th>
                    <th>Fabric</th>
                    <th>Color</th>
                    <th>Qty</th>
                    <th>Amount (RM)</th>
                    <th>Total</th>
                    <th>Deposit</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['invoice_number']) ?></td>
                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td><?= htmlspecialchars($row['customer_phone']) ?></td>
                        <td><?= htmlspecialchars($row['order_date']) ?></td>
                        <td><?= htmlspecialchars($row['fitting_date']) ?></td>
                        <td><?= htmlspecialchars($row['delivery_date']) ?></td>
                        <td><?= htmlspecialchars($row['item_type']) ?></td>
                        <td><?= htmlspecialchars($row['fabric_name']) ?></td>
                        <td><?= htmlspecialchars($row['fabric_color']) ?></td>
                        <td><?= htmlspecialchars($row['quantity']) ?></td>
                        <td><?= number_format($row['amount'], 2) ?></td>
                        <td><?= number_format($row['total_amount'], 2) ?></td>
                        <td><?= number_format($row['deposit_amount'], 2) ?></td>
                        <td><?= number_format($row['balance_amount'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#invoiceTable').DataTable({
                "pageLength": 10,
                "order": [
                    [0, "desc"]
                ], // sort by invoice number by default
            });
        });
    </script>
</body>

</html>