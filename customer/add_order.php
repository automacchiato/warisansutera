<?php
include 'db.php'; // your DB connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Insert customer
    $stmt = $conn->prepare("INSERT INTO customers (customer_name, customer_address, customer_email, customer_phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $_POST['customer_name'], $_POST['customer_address'], $_POST['customer_email'], $_POST['customer_phone']);
    $stmt->execute();
    $customer_id = $stmt->insert_id;

    // Insert invoice
    $stmt = $conn->prepare("INSERT INTO invoices 
        (invoice_number, invoice_details, customer_id, order_date, fitting_date, delivery_date, total_amount, deposit_amount, balance_amount, additional_deposit, additional_amount)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssisssddddd",
        $_POST['invoice_number'],
        $_POST['invoice_details'],
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
    foreach ($_POST['item_type'] as $key => $item) {
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
        $invoice_item_id = $stmt->insert_id;

        //Workslip handling
        if ($item === 'SHIRT') {
            // SHIRT WORKSLIP
            $stmt = $conn->prepare("INSERT INTO workslip_shirts
                (item_id, manufacturer, salesman_name, cutter_name, tailor_name, shirt_type, gender, special_instructions, previous_invoice_number, fabric_direction, collar_design, collar_height, collar_width, collar_gap, collar_meet, collar_length, back_length, front_length, chest_fit, chest_loose, waist_fit, waist_loose, hip_fit, hip_loose, shoulder, sleeve_length, elbow_length, cuff_type, cuff_length, cuff_width, armhole_length, erect, hunch, shoulder_type, corpulent, front_cutting, placket_type, top_initial, bottom_initial, cleaning_type)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param(
                "isssssssssssssssssssssssssssssssssssssss",
                $invoice_item_id,
                $_POST['manufacturer'][$key],
                $_POST['salesman_name'][$key],
                $_POST['cutter_name'][$key],
                $_POST['tailor_name'][$key],
                $_POST['shirt_type'][$key],
                $_POST['gender'][$key],
                $_POST['special_instructions'][$key],
                $_POST['previous_invoice_number'][$key],
                $_POST['fabric_direction'][$key],
                $_POST['collar_design'][$key],
                $_POST['collar_height'][$key],
                $_POST['collar_width'][$key],
                $_POST['collar_gap'][$key],
                $_POST['collar_meet'][$key],
                $_POST['collar_length'][$key],
                $_POST['back_length'][$key],
                $_POST['front_length'][$key],
                $_POST['chest_fit'][$key],
                $_POST['chest_loose'][$key],
                $_POST['waist_fit'][$key],
                $_POST['waist_loose'][$key],
                $_POST['hip_fit'][$key],
                $_POST['hip_loose'][$key],
                $_POST['shoulder'][$key],
                $_POST['sleeve_length'][$key],
                $_POST['elbow_length'][$key],
                $_POST['cuff_type'][$key],
                $_POST['cuff_length'][$key],
                $_POST['cuff_width'][$key],
                $_POST['armhole'][$key],
                $_POST['erect'][$key],
                $_POST['hunch'][$key],
                $_POST['shoulder_type'][$key],
                $_POST['corpulent'][$key],
                $_POST['front_cutting'][$key],
                $_POST['placket_type'][$key],
                $_POST['top_initial'][$key],
                $_POST['bottom_initial'][$key],
                $_POST['cleaning_type'][$key],
            );
            $stmt->execute();
        } elseif ($item === 'TROUSERS') {
            // TROUSERS WORKSLIP (mapping only)
            // INSERT INTO trousers_workslip(invoice_item_id, manufacturer, salesman_name, cutter_name, tailor_name, item, gender, ...)
            // bind_param with trousers fields
        } elseif ($item === 'JACKETS') {
            // JACKET WORKSLIP (mapping only)
        } elseif ($item === 'BAJU MELAYU') {
            // BAJU MELAYU WORKSLIP (mapping only)
        }
    }

    //Insert workslip


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
        <h2 class="text-center">Create Invoice</h2>
        <form method="post">
            <h4>Customer Details</h4>
            <div class="mb-3">
                <label class="fw-bold">Name</label>
                <input type="text" name="customer_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold">Address</label>
                <textarea name="customer_address" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
                <label class="fw-bold">Email</label>
                <input type="text" name="customer_email" class="form-control">
            </div>
            <div class="mb-3">
                <label class="fw-bold">Phone</label>
                <input type="text" name="customer_phone" class="form-control" required>
            </div>

            <h4>Invoice Details</h4>
            <div class="mb-3">
                <label class="fw-bold">Invoice Number</label>
                <input type="text" name="invoice_number" class="form-control" required>
                <label class="fw-bold">Invoice Description</label>
                <input type="text" name="invoice_details" class="form-control" required>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label class="fw-bold">Order Date</label>
                    <input type="date" name="order_date" class="form-control">
                </div>
                <div class="col">
                    <label class="fw-bold">Fitting Date</label>
                    <input type="date" name="fitting_date" class="form-control">
                </div>
                <div class="col">
                    <label class="fw-bold">Delivery Date</label>
                    <input type="date" name="delivery_date" class="form-control">
                </div>
            </div>

            <h4>Items</h4>
            <div id="items">
                <div class="item-block border rounded p-2 mb-3">
                    <div class="row g-2 mb-2">
                        <div class="col">
                            <label class="fw-bold">Apparel Type</label>
                            <select name="item_type[]" class="form-control item-type" required onchange="showWorkslip(this)">
                                <option value="">Select Apparel</option>
                                <option value="SHIRT">Shirt</option>
                                <option value="TROUSER">Trousers</option>
                                <option value="JACKET">Jacket</option>
                                <option value="BAJU MELAYU">Baju Melayu</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="fw-bold">Quantity</label>
                            <input type="number" name="quantity[]" class="form-control" required>
                        </div>
                        <div class="col">
                            <label class="fw-bold">Fabric Code</label>
                            <input type="text" name="fabric_code[]" class="form-control">
                        </div>
                        <div class="col">
                            <label class="fw-bold">Fabric Name</label>
                            <input type="text" name="fabric_name[]" class="form-control">
                        </div>
                        <div class="col">
                            <label class="fw-bold">Fabric Color</label>
                            <input type="text" name="fabric_color[]" class="form-control">
                        </div>
                        <div class="col">
                            <label class="fw-bold">Fabric Usage</label>
                            <input type="number" step="0.01" name="fabric_usage[]" class="form-control">
                        </div>
                        <div class="col">
                            <label class="fw-bold">Amount</label>
                            <input type="number" step="0.01" name="amount[]" class="form-control">
                        </div>
                    </div>

                    <!-- Workslip section (hidden by default) -->
                    <div class="workslip mt-2" style="display:none;">
                        <h6>Workslip</h6>
                        <div class="workslip-fields"></div>
                        <div class="mb-2">
                            <label class="fw-bold">Upload Drawing:</label>
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
                case "SHIRT":
                    html = `
                        <div class="row mb-2">
                            <div class="col">
                            <label class="fw-bold">Manufacturer</label>
                            <select name="manufacturer[]" class="form-control" required>
                                <option value="" disabled selected>Select Manufacturer</option>
                                <option value="In-House Factory">In-House Factory</option>
                                <option value="Fabrica">Fabrica</option>
                            </select>
                            </div>
                            <div class="col">
                                <label class="fw-bold">Salesman</label>
                                <input type="text" name="salesman_name[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Cutter Name</label>
                                <input type="text" name="cutter_name[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Tailor Name</label>
                                <input type="text" name="tailor_name[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Shirt Type</label>
                                <select name="shirt_type[]" class="form-control" required>
                                    <option value="" disabled selected >Select Shirt Type</option>
                                    <option value="SH/S">Shirt (Short Sleeve)</option>
                                    <option value="SH/L">Shirt (Long Sleeve)</option>
                                    <option value="BSH/S">Batik Shirt (Short Sleeve)</option>
                                    <option value="BSH/S">Batik Shirt (Long Sleeve)</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="fw-bold">Gender</label>
                                <select name="gender[]" class="form-control" required>
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>   
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Special Instructions</label>
                                <input type="text" name="special_instructions[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Previous Invoice No.</label>
                                <input type="text" name="previous_invoice_number[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Fabric Direction</label>
                                <select name="fabric_direction[]" class="form-control" required>
                                    <option value="" disabled selected>Select Fabric Direction</option>
                                    <option value="Vertical">Vertical</option>
                                    <option value="Horizontal">Horizontal</option>
                                </select>   
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Collar Design</label>
                                <select name="collar_design[]" class="form-control" required>
                                    <option value="" disabled selected>Select Collar Design</option>
                                    <option value="Button Down (C1)">Button Down (C1)</option>
                                    <option value="Classic (C2)">Classic (C2)</option>
                                    <option value="Cutaway (C3)">Cutaway (C3)</option>
                                    <option value="Wing (C4)">Wing (C4)</option>
                                    <option value="Wing (Narrow) (C5)">Wing (Narrow) (C5)</option>
                                    <option value="Wing (Round Tip) (C6)">Wing (Round Tip) (C6)</option>
                                    <option value="Tab Collar (C7)">Tab Collar (C7)</option>
                                    <option value="Button Loop (C8)">Button Loop (C8)</option>
                                    <option value="BDI (C9)">BDI (C9)</option>
                                    <option value="NAP (C10)">NAP (C10)</option>
                                    <option value="MAPS (C11)">MAPS (C11)</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Collar Height</label>
                                <input type="text" name="collar_height[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Collar Width</label>
                                <input type="text" name="collar_width[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Collar Gap</label>
                                <input type="text" name="collar_gap[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Collar Meet</label>
                                <input type="text" name="collar_meet[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Collar Length</label>
                                <input type="text" name="collar_length[]" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Back Length</label>
                                <input type="text" name="back_length[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Front Length</label>
                                <input type="text" name="front_length[]" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Chest (Fit)</label>
                                <input type="text" name="chest_fit[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Chest (Loose)</label>
                                <input type="text" name="chest_loose[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Waist (Fit)</label>
                                <input type="text" name="waist_fit[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Waist (Loose)</label>
                                <input type="text" name="waist_loose[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Hip (Fit)</label>
                                <input type="text" name="hip_fit[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Hip (Loose)</label>
                                <input type="text" name="hip_loose[]" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Shoulder Type</label>
                                <select name="shoulder_type[]" class="form-control">
                                    <option value="Square">Square</option>
                                    <option value="Drop" selected>Drop</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Shoulder Length</label>
                                <input type="text" name="shoulder[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Sleeve Length</label>
                                <input type="text" name="sleeve_length[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Elbow</label>
                                <input type="text" name="elbow_length[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Cuff Type</label>
                                <select name="cuff_type[]" class="form-control" required>
                                    <option value="" disabled selected>Select Cuff Type</option>
                                    <option value="Single Cuff">Single Cuff</option>
                                    <option value="Double Cuff">Double Cuff</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Cuff Length</label>
                                <input type="text" name="cuff_length[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Cuff Width</label>
                                <input type="text" name="cuff_width[]" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Armhole</label>
                                <input type="text" name="armhole[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Erect</label>
                                <input type="text" name="erect[]" class="form-control" disabled>
                            </div>
                            <div class="col">
                                <label class="fw-bold">Hunch</label>
                                <input type="text" name="hunch[]" class="form-control" disabled>
                            </div>
                            <div class="col">
                                <label class="fw-bold">Corpulent</label>
                                <select name="corpulent[]" class="form-control" disabled>
                                    <option value="" disabled selected>Have corpulent?</option>
                                    <option value="Yes">Yes</option>
                                    <option value="">No</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Front Cutting</label>
                                <select name="front_cutting[]" class="form-control" required>
                                    <option value="" disabled selected>Select Front Cutting</option>
                                    <option value="Straight">Straight</option>
                                    <option value="Rounded">Rounded</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Placket Type</label>
                                <select name="placket_type[]" class="form-control" required>
                                    <option value="" disabled selected>Select Placket Type</option>
                                    <option value="Hidden Button">Hidden Button</option>
                                    <option value="Live Placket">Live Placket</option>
                                    <option value="Front Placket">Front Placket</option>
                                </select>   
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Top Initial</label>
                                <input type="text" name="top_initial[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Bottom Initial</label>
                                <input type="text" name="bottom_initial[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Cleaning Type</label>
                                <select name="cleaning_type[]" class="form-control">
                                    <option value="" disabled selected>Select Cleaning Type</option>
                                    <option value="No Restriction">No Restriction</option>
                                    <option value="Dry Clean Only">Dry Clean Only</option>
                                    <option value="Hand Wash Only">Hand Wash Only</option>
                                </select>   
                            </div>
                        </div>
                        `;
                    break;
                case "TROUSER":
                    html = `
                    <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Manufacturer</label>
                                <select name="manufacturer[]" class="form-control" required>
                                    <option value="" disabled selected>Select Manufacturer</option>
                                    <option value="In-House Factory">In-House Factory</option>
                                    <option value="Fabrica">Fabrica</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="fw-bold">Salesman</label>
                                <input type="text" name="salesman_name[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Cutter Name</label>
                                <input type="text" name="cutter_name[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Tailor Name</label>
                                <input type="text" name="tailor_name[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Gender</label>
                                <select name="gender[]" class="form-control" required>
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>   
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Special Instructions</label>
                                <input type="text" name="special_instructions[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Previous Invoice No.</label>
                                <input type="text" name="previous_invoice_number[]" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Fly Hand Stitch?</label>
                                <select name="fly_hs[]" class="form-control" required>
                                    <option value="1">Yes</option>
                                    <option value="0" selected>No</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Side Pocket Stitch?</label>
                                <select name="side_pocket_hs[]" class="form-control" required>
                                    <option value="1">Yes</option>
                                    <option value="0" selected>No</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Side Seams Stitch?</label>
                                <select name="side_seams_hs[]" class="form-control" required>
                                    <option value="1">Yes</option>
                                    <option value="0" selected>No</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Pocket Pull Stitch?</label>
                                <select name="pocket_pull[]" class="form-control" required>
                                    <option value="1">Yes</option>
                                    <option value="0" selected>No</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Pleat Number</label>
                                <select name="pleat_num[]" class="form-control" required>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>   
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Waist (Fit)</label>
                                <input type="text" name="waist_fit[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Waist (Loose)</label>
                                <input type="text" name="waist_loose[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Hip (Fit)</label>
                                <input type="text" name="hip_fit[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Hip (Loose)</label>
                                <input type="text" name="hip_loose[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Top Hip (Fit)</label>
                                <input type="text" name="top_hip_fit[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Top Hip (Loose)</label>
                                <input type="text" name="top_hip_loose[]" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Length</label>
                                <input type="text" name="length[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Thigh</label>
                                <input type="text" name="thigh[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Knee</label>
                                <input type="text" name="knee[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Bottom</label>
                                <input type="text" name="bottom[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Crotch</label>
                                <input type="text" name="crotch[]" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Position on Waist</label>
                                <select name="position_on_waist[]" class="form-control">
                                    <option value="" disabled selected>Select Position</option>
                                    <option value="Front High">Front High</option>
                                    <option value="Front Cut Low">Front Cut Low</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Corpulent</label>
                                <select name="corpulent[]" class="form-control" disabled>
                                    <option value="" disabled selected>Have corpulent?</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Seating Type</label>
                                <select name="seating_type[]" class="form-control" disabled>
                                    <option value="" disabled selected>Select Seating Type</option>
                                    <option value="Prom Seat (Hollow Back Waist)">Prom Seat (Hollow Back Waist)</option>
                                    <option value="Flat Seat">Flat Seat</option>
                                </select>   
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Turn Up</label>
                                <select name="turn_up[]" class="form-control" disabled>
                                    <option value="" disabled selected>Select Option</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Turn Up Length</label>
                                <input type="number" name="turn_up_length" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Right Pocket</label>
                                <select name="right_pocket[]" class="form-control" disabled>
                                    <option value="" disabled selected>Select Option</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Left Pocket</label>
                                <select name="left_pocket[]" class="form-control" disabled>
                                    <option value="" disabled selected>Select Option</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>   
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Inside Pocket Number</label>
                                <input type="text" name="inside_pocket_num[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Loop Number</label>
                                <input type="text" name="loop_num[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Loop Width</label>
                                <input type="number" name="loop_width" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Loop Length</label>
                                <input type="number" name="loop_length" class="form-control" step="0.01" max="999.99">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Lining Type</label>
                                <select name="lining_type[]" class="form-control">
                                    <option value="" disabled selected>Select Lining Type</option>
                                    <option value="Half lined front only">Half lined front only</option>
                                    <option value="Front Back 1/2 lining">Front Back 1/2 lining</option>
                                    <option value="Front Full Length">Front Full Length</option>
                                    <option value="Trousers full lined">Trousers full lined</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Bottom Initial</label>
                                <input type="text" name="bottom_initial[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Cleaning Type</label>
                                <select name="cleaning_type[]" class="form-control">
                                    <option value="" disabled selected>Select Cleaning Type</option>
                                    <option value="No Restriction">No Restriction</option>
                                    <option value="Dry Clean Only">Dry Clean Only</option>
                                    <option value="Hand Wash Only">Hand Wash Only</option>
                                </select>   
                            </div>
                        </div>
                        `;
                    break;
                case "JACKET":
                    html = `
                        <div class="row mb-2">
                            <div class="col"><input type="text" name="jacket_chest[]" class="form-control" placeholder="Chest"></div>
                            <div class="col"><input type="text" name="jacket_length[]" class="form-control" placeholder="Length"></div>
                            <div class="col"><input type="text" name="jacket_sleeve[]" class="form-control" placeholder="Sleeve"></div>
                        </div>`;
                    break;
                case "BAJU MELAYU":
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