<?php
include 'db.php'; // your DB connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Insert customer
    $stmt = $conn->prepare("INSERT INTO customers (customer_name, customer_address, customer_phone) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['customer_name'], $_POST['customer_address'], $_POST['customer_phone']);
    $stmt->execute();
    $customer_id = $stmt->insert_id;

    // Insert invoice
    $stmt = $conn->prepare("INSERT INTO invoices 
        (invoice_number, customer_id, order_date, fitting_date, delivery_date, total_amount, deposit_amount, balance_amount, additional_deposit, additional_amount)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sisssddddd",
        $_POST['invoice_number'],
        $customer_id,
        $_POST['order_date'],
        $_POST['fitting_date'],
        $_POST['delivery_date'],
        $_POST['total_amount'],
        $_POST['deposit_amount'],
        $_POST['balance_amount'],
        $_POST['additional_deposit'],
        $_POST['additional_amount']
    );
    $stmt->execute();
    $invoice_id = $stmt->insert_id;

    // Insert items
    foreach ($_POST['item_name'] as $key => $item) {
        $stmt = $conn->prepare("INSERT INTO invoice_items 
            (invoice_id, item_type, quantity, fabric_code, fabric_name, fabric_color, fabric_usage, amount) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "isisssdd",
            $invoice_id,
            $_POST['item_type'][$key],
            $_POST['quantity'][$key],
            $_POST['fabric_code'][$key],
            $_POST['fabric_name'][$key],
            $_POST['fabric_color'][$key],
            $_POST['fabric_usage'][$key],
            $_POST['amount'][$key]
        );
        $stmt->execute();
    }

    echo "<div class='alert alert-success'>Invoice Saved!</div>";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Create Invoice</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="p-4">
    <div class="container">
        <h2>Create Invoice</h2>
        <form method="post">
            <h4>Customer Details</h4>
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="customer_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Address</label>
                <textarea name="customer_address" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label>Phone</label>
                <input type="text" name="customer_phone" class="form-control">
            </div>

            <h4>Invoice Details</h4>
            <div class="mb-3">
                <label>Invoice Number</label>
                <input type="text" name="invoice_number" class="form-control" required>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label>Order Date</label>
                    <input type="date" name="order_date" class="form-control">
                </div>
                <div class="col">
                    <label>Fitting Date</label>
                    <input type="date" name="fitting_date" class="form-control">
                </div>
                <div class="col">
                    <label>Delivery Date</label>
                    <input type="date" name="delivery_date" class="form-control">
                </div>
            </div>

            <h4>Items</h4>
            <div id="items">
                <div class="item-block border rounded p-2 mb-3">
                    <div class="row g-2 mb-2">
                        <div class="col">
                            <select name="item_type[]" class="form-control item-type" required onchange="showWorkslip(this)">
                                <option value="">Select Apparel</option>
                                <option value="shirt">Shirt</option>
                                <option value="trousers">Trousers</option>
                                <option value="jacket">Jacket</option>
                                <option value="baju_melayu">Baju Melayu</option>
                            </select>
                        </div>
                        <div class="col"><input type="number" name="quantity[]" class="form-control" placeholder="Qty" required></div>
                        <div class="col"><input type="text" name="fabric_code[]" class="form-control" placeholder="Fabric Code"></div>
                        <div class="col"><input type="text" name="fabric_name[]" class="form-control" placeholder="Fabric Name"></div>
                        <div class="col"><input type="text" name="fabric_color[]" class="form-control" placeholder="Color"></div>
                        <div class="col"><input type="number" step="0.01" name="fabric_usage[]" class="form-control" placeholder="Usage (m)"></div>
                        <div class="col"><input type="number" step="0.01" name="amount[]" class="form-control" placeholder="Amount"></div>
                    </div>

                    <!-- Workslip section (hidden by default) -->
                    <div class="workslip mt-2" style="display:none;">
                        <h6>Workslip</h6>
                        <div class="workslip-fields"></div>
                        <div class="mb-2">
                            <label>Upload Drawing:</label>
                            <input type="file" name="drawing[]" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary mb-3" onclick="addItem()">+ Add Item</button>

            <h4>Payments</h4>
            <div class="row mb-3">
                <div class="col"><input type="number" step="0.01" name="total_amount" class="form-control" placeholder="Total Amount"></div>
                <div class="col"><input type="number" step="0.01" name="deposit_amount" class="form-control" placeholder="Deposit"></div>
                <div class="col"><input type="number" step="0.01" name="balance_amount" class="form-control" placeholder="Balance"></div>
                <div class="col"><input type="number" step="0.01" name="additional_deposit" class="form-control" placeholder="Additional Deposit"></div>
                <div class="col"><input type="number" step="0.01" name="additional_amount" class="form-control" placeholder="Additional Amount"></div>
            </div>

            <button type="submit" class="btn btn-primary">Save Invoice?</button>
        </form>
    </div>

    <script>
        function addItem() {
            let template = document.querySelector(".item-block").outerHTML;
            document.getElementById("items").insertAdjacentHTML("beforeend", template);
        }

        function showWorkslip(select) {
            let workslip = select.closest(".item-block").querySelector(".workslip");
            let fields = workslip.querySelector(".workslip-fields");
            workslip.style.display = "block";

            let type = select.value;
            let html = "";

            switch (type) {
                case "shirt":
                    html = `
                        <div class="row mb-2">
                            <div class="col">
                            <select name="shirt_neck[]" class="form-control" required">
                                <option value="">Select Manufacturer</option>
                                <option value="In-House Factory">In-House Factory</option>
                                <option value="Fabrica">Fabrica</option>
                            </select>
                            </div>
                            <div class="col"><input type="text" name="shirt_chest[]" class="form-control" placeholder="Chest"></div>
                            <div class="col"><input type="text" name="shirt_sleeve[]" class="form-control" placeholder="Sleeve"></div>
                        </div>`;
                    break;
                case "trousers":
                    html = `
                        <div class="row mb-2">
                            <div class="col"><input type="text" name="trouser_waist[]" class="form-control" placeholder="Waist"></div>
                            <div class="col"><input type="text" name="trouser_hip[]" class="form-control" placeholder="Hip"></div>
                            <div class="col"><input type="text" name="trouser_length[]" class="form-control" placeholder="Length"></div>
                        </div>`;
                    break;
                case "jacket":
                    html = `
                        <div class="row mb-2">
                            <div class="col"><input type="text" name="jacket_chest[]" class="form-control" placeholder="Chest"></div>
                            <div class="col"><input type="text" name="jacket_length[]" class="form-control" placeholder="Length"></div>
                            <div class="col"><input type="text" name="jacket_sleeve[]" class="form-control" placeholder="Sleeve"></div>
                        </div>`;
                    break;
                case "baju_melayu":
                    html = `
                        <div class="row mb-2">
                            <div class="col"><input type="text" name="bm_ksleeve[]" class="form-control" placeholder="Kain Sleeve"></div>
                            <div class="col"><input type="text" name="bm_panjang[]" class="form-control" placeholder="Panjang Baju"></div>
                            <div class="col"><input type="text" name="bm_pinggang[]" class="form-control" placeholder="Pinggang"></div>
                        </div>`;
                    break;
            }
            fields.innerHTML = html;
        }
    </script>
</body>

</html>