<?php
include 'db.php';

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Join customers + invoices + items
$query = "
    SELECT 
        i.invoice_id, i.invoice_number, i.invoice_details, i.order_date, i.fitting_date, i.delivery_date,
        i.total_amount, i.deposit_amount, i.balance_amount,
        c.customer_name, c.customer_phone,
        it.item_id, it.item_type, it.quantity, it.fabric_name, it.fabric_color, it.amount
    FROM invoices i
    JOIN customers c ON i.customer_id = c.customer_id
    JOIN invoice_items it ON i.invoice_id = it.invoice_id
    ORDER BY i.invoice_id DESC
";

$result = $conn->query($query);

// Check query execution
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Invoice Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">CMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
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

    <div class="container-fluid">
        <h2 class="mb-4 mt-2">Invoice Dashboard</h2>
        <table id="invoiceTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Invoice Details</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Order Date</th>
                    <th>Delivery Date</th>
                    <th>Item</th>
                    <th>Fabric</th>
                    <th>Qty</th>
                    <th>Amount (RM)</th>
                    <th>Workslip</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['invoice_number']) ?></td>
                            <td><?= htmlspecialchars($row['invoice_details']) ?></td>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><?= htmlspecialchars($row['customer_phone']) ?></td>
                            <td><?= htmlspecialchars($row['order_date']) ?></td>
                            <td><?= htmlspecialchars($row['delivery_date']) ?></td>
                            <td><?= htmlspecialchars($row['item_type']) ?></td>
                            <td><?= htmlspecialchars($row['fabric_name']) ?></td>
                            <td><?= htmlspecialchars($row['quantity']) ?></td>
                            <td><?= number_format($row['amount'], 2) ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary viewWorkslipBtn"
                                    data-invoice="<?= (int)$row['invoice_id'] ?>"
                                    data-item-id="<?= (int)$row['item_id'] ?>"
                                    data-item-type="<?= htmlspecialchars($row['item_type']) ?>">
                                    View Workslip
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="15" class="text-center">No invoices found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Workslip Modal -->
    <div class="modal fade" id="workslipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Workslip Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="workslipContent">
                    <!-- Workslip details will be loaded here -->
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#invoiceTable').DataTable({
                "pageLength": 10,
                "order": [
                    [0, "desc"]
                ], // sort by invoice number by default
                "responsive": true
            });

            $(".viewWorkslipBtn").on("click", function() {
                let invoiceId = parseInt($(this).data("invoice"));
                let itemId = parseInt($(this).data("item-id"));
                let itemType = $(this).data("item-type");

                // Validate parameters
                if (!invoiceId || invoiceId <= 0 || !itemId || itemId <= 0 || !itemType) {
                    alert("Invalid parameters");
                    return;
                }

                $("#workslipContent").html('<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>');

                let modal = new bootstrap.Modal(document.getElementById('workslipModal'));
                modal.show();

                $.ajax({
                    url: "get_workslip.php",
                    type: "GET",
                    data: {
                        invoice_id: invoiceId,
                        item_id: itemId,
                        item_type: itemType
                    },
                    success: function(data) {
                        $("#workslipContent").html(data);
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);
                        $("#workslipContent").html("<p class='text-danger'>Failed to load workslip details. Please try again.</p>");
                    }
                });
            });
        });
    </script>

</body>

</html>