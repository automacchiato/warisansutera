<?php
include 'db.php';

// Capture filters
$filter_customer = $_GET['customer'] ?? '';
$filter_invoice  = $_GET['invoice'] ?? '';
$filter_apparel  = $_GET['apparel'] ?? '';

// Base query
$sql = "
    SELECT c.customer_id, c.customer_name, c.customer_phone,
           i.invoice_id, i.invoice_number,
           'SHIRT' AS apparel_type, s.item AS item, s.quantity AS qty, s.delivery_date
    FROM invoices i
    JOIN customers c ON i.customer_id = c.customer_id
    JOIN workslip_shirts s ON i.invoice_id = s.invoice_id

    UNION ALL

    SELECT c.customer_id, c.customer_name, c.customer_phone,
           i.invoice_id, i.invoice_number,
           'TROUSER' AS apparel_type, t.item, t.quantity, t.delivery_date
    FROM invoices i
    JOIN customers c ON i.customer_id = c.customer_id
    JOIN workslip_trousers t ON i.invoice_id = t.invoice_id

    UNION ALL

    SELECT c.customer_id, c.customer_name, c.customer_phone,
           i.invoice_id, i.invoice_number,
           'JACKET' AS apparel_type, j.item, j.quantity, j.delivery_date
    FROM invoices i
    JOIN customers c ON i.customer_id = c.customer_id
    JOIN workslip_jacket j ON i.invoice_id = j.invoice_id

    UNION ALL

    SELECT c.customer_id, c.customer_name, c.customer_phone,
           i.invoice_id, i.invoice_number,
           'BAJU MELAYU' AS apparel_type, b.item, b.quantity, b.delivery_date
    FROM invoices i
    JOIN customers c ON i.customer_id = c.customer_id
    JOIN workslip_baju_melayu b ON i.invoice_id = b.invoice_id
";

// Wrap query so we can filter safely
$sql = "SELECT * FROM ($sql) AS all_orders WHERE 1=1";

// Apply filters
if ($filter_customer !== '') {
    $sql .= " AND customer_name LIKE '%" . $conn->real_escape_string($filter_customer) . "%'";
}
if ($filter_invoice !== '') {
    $sql .= " AND invoice_number LIKE '%" . $conn->real_escape_string($filter_invoice) . "%'";
}
if ($filter_apparel !== '') {
    $sql .= " AND apparel_type = '" . $conn->real_escape_string($filter_apparel) . "'";
}

$sql .= " ORDER BY invoice_number DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Overview - Customers & Invoices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-4">
        <h2 class="mb-4">📋 Overview of Orders</h2>

        <!-- Filter Form -->
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" name="customer" value="<?= htmlspecialchars($filter_customer) ?>" class="form-control" placeholder="Filter by Customer Name">
            </div>
            <div class="col-md-3">
                <input type="text" name="invoice" value="<?= htmlspecialchars($filter_invoice) ?>" class="form-control" placeholder="Filter by Invoice #">
            </div>
            <div class="col-md-3">
                <select name="apparel" class="form-select">
                    <option value="">All Apparel Types</option>
                    <option value="Shirt" <?= $filter_apparel == "Shirt" ? "selected" : "" ?>>Shirt</option>
                    <option value="Trousers" <?= $filter_apparel == "Trousers" ? "selected" : "" ?>>Trousers</option>
                    <option value="Jacket" <?= $filter_apparel == "Jacket" ? "selected" : "" ?>>Jacket</option>
                    <option value="Baju Melayu" <?= $filter_apparel == "Baju Melayu" ? "selected" : "" ?>>Baju Melayu</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Results Table -->
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Invoice #</th>
                    <th>Apparel Type</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Delivery Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><?= htmlspecialchars($row['customer_phone']) ?></td>
                            <td><?= htmlspecialchars($row['invoice_number']) ?></td>
                            <td><?= htmlspecialchars($row['apparel_type']) ?></td>
                            <td><?= htmlspecialchars($row['item']) ?></td>
                            <td><?= htmlspecialchars($row['qty']) ?></td>
                            <td><?= htmlspecialchars($row['delivery_date']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>

</html>