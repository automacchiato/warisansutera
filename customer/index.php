<?php
$servername = "127.0.0.1:3306";
$username = "u929965336_wssb";
$password = "Sutera@23";
$database = "u929965336_warisansutera";
$conn = new mysqli($servername, $username, $password, $database);

$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT i.invoice_number, i.order_date, c.name, c.phone, c.address, i.total_amount 
        FROM invoices i 
        JOIN customers c ON i.customer_id=c.customer_id 
        WHERE c.name LIKE '%$search%' OR i.invoice_number LIKE '%$search%'
        ORDER BY i.order_date DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Orders Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">
    <h2>Orders Dashboard</h2>
    <form method="GET" class="mb-3">
        <input type="text" name="search" placeholder="Search by name/invoice" value="<?= $search ?>" class="form-control">
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Invoice No</th>
                <th>Order Date</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Total (RM)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['invoice_number'] ?></td>
                    <td><?= $row['order_date'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['phone'] ?></td>
                    <td><?= number_format($row['total_amount'], 2) ?></td>
                    <td>
                        <a href="view_invoice.php?invoice=<?= $row['invoice_number'] ?>" class="btn btn-sm btn-info">View</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

</html>