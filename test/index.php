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
    <style>
        th {
            cursor: pointer;
        }

        th.sort-asc::after {
            content: " 🔼";
        }

        th.sort-desc::after {
            content: " 🔽";
        }
    </style>
</head>

<body class="bg-light">

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
                        <a class="nav-link active" href="../test/index.php">Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="add_order.php">Add Order</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" aria-disabled="true">Disabled</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container p-4">
        <h2 class="mb-4">Invoices</h2>

        <!-- Search Box -->
        <div class="mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Search invoices...">
        </div>

        <table id="invoiceTable" class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th data-column="invoice_number">Invoice No</th>
                    <th data-column="customer_name">Customer</th>
                    <th data-column="order_date">Order Date</th>
                    <th data-column="delivery_date">Delivery Date</th>
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
                        <td>
                            <a href="generate_pdf.php?invoice_id=<?= $row['invoice_id'] ?>" class="btn btn-primary btn-sm">Invoice</a>
                            <a href="workslip_pdf.php?invoice_id=<?= $row['invoice_id'] ?>" class="btn btn-warning btn-sm">Workslip</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script>
        // --- SEARCH FUNCTION ---
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#invoiceTable tbody tr');

            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });

        // --- SORT FUNCTION ---
        document.querySelectorAll('#invoiceTable th[data-column]').forEach(th => {
            th.addEventListener('click', function() {
                let table = th.closest('table');
                let tbody = table.querySelector('tbody');
                let rows = Array.from(tbody.querySelectorAll('tr'));
                let index = Array.from(th.parentNode.children).indexOf(th);
                let ascending = !th.classList.contains('sort-asc');

                // Reset other column classes
                table.querySelectorAll('th').forEach(header => header.classList.remove('sort-asc', 'sort-desc'));

                th.classList.toggle('sort-asc', ascending);
                th.classList.toggle('sort-desc', !ascending);

                rows.sort((a, b) => {
                    let cellA = a.children[index].innerText.trim().toLowerCase();
                    let cellB = b.children[index].innerText.trim().toLowerCase();

                    if (!isNaN(Date.parse(cellA)) && !isNaN(Date.parse(cellB))) {
                        return ascending ? new Date(cellA) - new Date(cellB) : new Date(cellB) - new Date(cellA);
                    }

                    return ascending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
                });

                rows.forEach(row => tbody.appendChild(row));
            });
        });
    </script>
</body>

</html>