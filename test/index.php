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

        .pagination {
            justify-content: center;
        }
    </style>
</head>

<body class="p-4">
    <div class="container">
        <h2 class="mb-4">Invoices</h2>

        <!-- Search Box -->
        <div class="mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Search invoices...">
        </div>

        <div class="table-responsive">
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

        <!-- Pagination -->
        <nav>
            <ul id="pagination" class="pagination"></ul>
        </nav>
    </div>

    <script>
        const rowsPerPage = 10;
        let currentPage = 1;

        const table = document.getElementById('invoiceTable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const pagination = document.getElementById('pagination');
        const searchInput = document.getElementById('searchInput');

        // ---- Pagination Function ----
        function displayTable() {
            const filteredRows = rows.filter(row => row.style.display !== 'none');
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;

            // Hide all rows
            rows.forEach(row => row.style.display = 'none');

            // Show current page rows
            filteredRows.slice(start, end).forEach(row => row.style.display = '');

            // Update pagination
            pagination.innerHTML = '';
            if (totalPages > 1) {
                const createPageItem = (page) => {
                    const li = document.createElement('li');
                    li.classList.add('page-item', page === currentPage ? 'active' : '');
                    li.innerHTML = `<a class="page-link" href="#">${page}</a>`;
                    li.addEventListener('click', e => {
                        e.preventDefault();
                        currentPage = page;
                        displayTable();
                    });
                    return li;
                };

                // Prev Button
                const prev = document.createElement('li');
                prev.classList.add('page-item', currentPage === 1 ? 'disabled' : '');
                prev.innerHTML = `<a class="page-link" href="#">Previous</a>`;
                prev.addEventListener('click', e => {
                    e.preventDefault();
                    if (currentPage > 1) currentPage--;
                    displayTable();
                });
                pagination.appendChild(prev);

                for (let i = 1; i <= totalPages; i++) {
                    pagination.appendChild(createPageItem(i));
                }

                // Next Button
                const next = document.createElement('li');
                next.classList.add('page-item', currentPage === totalPages ? 'disabled' : '');
                next.innerHTML = `<a class="page-link" href="#">Next</a>`;
                next.addEventListener('click', e => {
                    e.preventDefault();
                    if (currentPage < totalPages) currentPage++;
                    displayTable();
                });
                pagination.appendChild(next);
            }
        }

        // ---- Search Function ----
        searchInput.addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
            currentPage = 1;
            displayTable();
        });

        // ---- Sort Function ----
        document.querySelectorAll('#invoiceTable th[data-column]').forEach(th => {
            th.addEventListener('click', function() {
                let index = Array.from(th.parentNode.children).indexOf(th);
                let ascending = !th.classList.contains('sort-asc');

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
                currentPage = 1;
                displayTable();
            });
        });

        // Initial display
        displayTable();
    </script>
</body>

</html>