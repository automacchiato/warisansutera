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
        (invoice_number, invoice_details, customer_id, order_date, fitting_date, delivery_date, total_amount, deposit_amount, balance_amount, additional_deposit, additional_balance)
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
        $_POST['additional_balance']
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

            $drawingFile = null; // default if no file uploaded
            if (isset($_FILES['drawing']['name'][$key]) && $_FILES['drawing']['error'][$key] == 0) {
                $targetDir = "uploads/drawings/";
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $fileName = time() . "_" . basename($_FILES['drawing']['name'][$key]);
                $targetFile = $targetDir . $fileName;

                if (move_uploaded_file($_FILES['drawing']['tmp_name'][$key], $targetFile)) {
                    $drawingFile = $fileName;
                }
            }

            $stmt = $conn->prepare("INSERT INTO workslip_shirts
                (item_id, manufacturer, salesman_name, cutter_name, tailor_name, shirt_type, gender, special_instructions, previous_invoice_number, fabric_direction, collar_design, collar_height, collar_width, collar_gap, collar_meet, collar_length, back_length, front_length, chest_fit, chest_loose, waist_fit, waist_loose, hip_fit, hip_loose, shoulder, sleeve_length, arm_length, elbow_length, cuff_type, cuff_length, cuff_width, armhole_length, erect, hunch, shoulder_type, corpulent, front_cutting, placket_type, top_initial, bottom_initial, cleaning_type, drawing)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param(
                "issssssssssdddddddddddddddddsdddddsdssssss",
                $invoice_item_id,
                $_POST['manufacturer'][$key], //s
                $_POST['salesman_name'][$key], //s
                $_POST['cutter_name'][$key], //s
                $_POST['tailor_name'][$key], //s
                $_POST['shirt_type'][$key], //s
                $_POST['gender'][$key], //s
                $_POST['special_instructions'][$key], //s
                $_POST['previous_invoice_number'][$key], //s
                $_POST['fabric_direction'][$key], //s
                $_POST['collar_design'][$key], //s
                $_POST['collar_height'][$key], //d
                $_POST['collar_width'][$key], //d
                $_POST['collar_gap'][$key], //d
                $_POST['collar_meet'][$key], //d
                $_POST['collar_length'][$key], //d
                $_POST['back_length'][$key], //d
                $_POST['front_length'][$key], ///d
                $_POST['chest_fit'][$key], //d
                $_POST['chest_loose'][$key], //d
                $_POST['waist_fit'][$key], //d
                $_POST['waist_loose'][$key], //d
                $_POST['hip_fit'][$key], //d
                $_POST['hip_loose'][$key], //d
                $_POST['shoulder'][$key], //d
                $_POST['sleeve_length'][$key], //d
                $_POST['arm_length'][$key], //d
                $_POST['elbow_length'][$key], //d
                $_POST['cuff_type'][$key], //s
                $_POST['cuff_length'][$key], //d
                $_POST['cuff_width'][$key], //d
                $_POST['armhole_length'][$key], //d
                $_POST['erect'][$key], //double
                $_POST['hunch'][$key], //double
                $_POST['shoulder_type'][$key], //s
                $_POST['corpulent'][$key], // double
                $_POST['front_cutting'][$key], //s
                $_POST['placket_type'][$key], //s
                $_POST['top_initial'][$key], //s
                $_POST['bottom_initial'][$key], //s
                $_POST['cleaning_type'][$key], //s
                $drawingFile
            );
            $stmt->execute();
        } elseif ($item === 'TROUSERS') {

            $drawingFile = null; // default if no file uploaded
            if (isset($_FILES['drawing']['name'][$key]) && $_FILES['drawing']['error'][$key] == 0) {
                $targetDir = "uploads/drawings/";
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $fileName = time() . "_" . basename($_FILES['drawing']['name'][$key]);
                $targetFile = $targetDir . $fileName;

                if (move_uploaded_file($_FILES['drawing']['tmp_name'][$key], $targetFile)) {
                    $drawingFile = $fileName;
                }
            }

            $stmt = $conn->prepare("INSERT INTO workslip_trousers
                (item_id, manufacturer, salesman_name, cutter_name, tailor_name, gender, special_instructions, previous_invoice_number, fly_hs, side_pocket_hs, side_seams_hs, pocket_pull, pleat_num, waist_fit, waist_loose, hip_fit, hip_loose, top_hip_fit, top_hip_loose, length, thigh, knee, bottom, crotch, position_on_waist, corpulent, seating_type, turn_up, turn_up_length, inside_pocket_num, inside_pocket_width, inside_pocket_length, loop_num, loop_width, loop_length, right_pocket, left_pocket, lining_type, bottom_initial, cleaning_type, drawing)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param(
                "issssssssssssdddddddddddsdssdsddsddssssss",
                $invoice_item_id, //i
                $_POST['manufacturer'][$key], //s
                $_POST['salesman_name'][$key], //s
                $_POST['cutter_name'][$key], //s
                $_POST['tailor_name'][$key], //s
                $_POST['gender'][$key], //s
                $_POST['special_instructions'][$key], //s
                $_POST['previous_invoice_number'][$key], //s
                $_POST['fly_hs'][$key], //s
                $_POST['side_pocket_hs'][$key], //s
                $_POST['side_seams_hs'][$key], //s
                $_POST['pocket_pull'][$key], ///s
                $_POST['pleat_num'][$key], //s
                $_POST['waist_fit'][$key], //d
                $_POST['waist_loose'][$key], //d
                $_POST['hip_fit'][$key], //d
                $_POST['hip_loose'][$key], //d
                $_POST['top_hip_fit'][$key], //d
                $_POST['top_hip_loose'][$key], //d
                $_POST['length'][$key], //d
                $_POST['thigh'][$key], //d
                $_POST['knee'][$key], //d
                $_POST['bottom'][$key], //d
                $_POST['crotch'][$key], //d
                $_POST['position_on_waist'][$key], //s
                $_POST['corpulent'][$key], //double
                $_POST['seating_type'][$key], //s
                $_POST['turn_up'][$key], //s
                $_POST['turn_up_length'][$key], //d
                $_POST['inside_pocket_num'][$key], //s
                $_POST['inside_pocket_width'][$key], //d
                $_POST['inside_pocket_length'][$key], //d
                $_POST['loop_num'][$key], //s
                $_POST['loop_width'][$key], //d
                $_POST['loop_length'][$key], //d
                $_POST['right_pocket'][$key], //s
                $_POST['left_pocket'][$key], //s
                $_POST['lining_type'][$key], //s
                $_POST['bottom_initial'][$key], //s
                $_POST['cleaning_type'][$key], //s
                $drawingFile
            );
            $stmt->execute();
        } elseif ($item === 'JACKET') {

            $drawingFile = null; // default if no file uploaded
            if (isset($_FILES['drawing']['name'][$key]) && $_FILES['drawing']['error'][$key] == 0) {
                $targetDir = "uploads/drawings/";
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $fileName = time() . "_" . basename($_FILES['drawing']['name'][$key]);
                $targetFile = $targetDir . $fileName;

                if (move_uploaded_file($_FILES['drawing']['tmp_name'][$key], $targetFile)) {
                    $drawingFile = $fileName;
                }
            }

            $stmt = $conn->prepare("INSERT INTO workslip_jacket
                (item_id, manufacturer, salesman_name, cutter_name, tailor_name, gender, special_instructions, previous_invoice_number, back_length, front_length, chest_fit, chest_loose, waist_fit, waist_loose, hip_fit, hip_loose, shoulder, sleeve_length, cuff_length, cross_back, cross_front, vest_length, back_neck_to_waist, back_neck_to_front_waist, sleeve_button, top_initial, bottom_initial, cleaning_type, drawing)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param(
                "isssssssddddddddddddddddissss",
                $invoice_item_id, //i
                $_POST['manufacturer'][$key], //s
                $_POST['salesman_name'][$key], //s
                $_POST['cutter_name'][$key], //s
                $_POST['tailor_name'][$key], //s
                $_POST['gender'][$key], //s
                $_POST['special_instructions'][$key], //s
                $_POST['previous_invoice_number'][$key], //s
                $_POST['back_length'][$key], //d
                $_POST['front_length'][$key], ///d
                $_POST['chest_fit'][$key], //d
                $_POST['chest_loose'][$key], //d
                $_POST['waist_fit'][$key], //d
                $_POST['waist_loose'][$key], //d
                $_POST['hip_fit'][$key], //d
                $_POST['hip_loose'][$key], //d
                $_POST['shoulder'][$key], //d
                $_POST['sleeve_length'][$key], //d
                $_POST['cuff_length'][$key], //d
                $_POST['cross_back'][$key], //d
                $_POST['cross_front'][$key], //d
                $_POST['vest_length'][$key], //d
                $_POST['back_neck_to_waist'][$key], //d
                $_POST['back_neck_to_front_waist'][$key], //d
                $_POST['sleeve_button'][$key], //i
                $_POST['top_initial'][$key], //s
                $_POST['bottom_initial'][$key], //s
                $_POST['cleaning_type'][$key], //s
                $drawingFile //s
            );

            if ($stmt->execute()) {
                echo "Insert successful!";
            } else {
                echo "Error: " . $stmt->error;
            }
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
    <style>
        .canvas-container {
            position: relative;
            display: inline-block;
            border: 1px solid #ccc;
        }

        #baseImage {
            display: block;
            max-width: 100%;
        }

        #designCanvas {
            position: absolute;
            top: 0;
            left: 0;
            cursor: crosshair;
        }

        .controls {
            margin-top: 10px;
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
                        <a class="nav-link" href="overview.php">Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="add_order.php">Add Order</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" aria-disabled="true">Disabled</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="text-center mt-2">Create Invoice</h2>
        <form method="post" enctype="multipart/form-data">
            <h4>Customer Details</h4>
            <div class="mb-3">
                <label class="fw-bold">Name*</label>
                <input type="text" name="customer_name" class="form-control" autocomplete="on" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold">Address*</label>
                <textarea name="customer_address" class="form-control" autocomplete="on" required></textarea>
            </div>
            <div class="mb-3">
                <label class="fw-bold">Email</label>
                <input type="text" name="customer_email" autocomplete="on" class="form-control">
            </div>
            <div class="mb-3">
                <label class="fw-bold">Phone*</label>
                <input type="text" name="customer_phone" autocomplete="on" class="form-control" required>
            </div>

            <h4>Invoice Details</h4>
            <div class="mb-3">
                <label class="fw-bold">Invoice Number*</label>
                <input type="text" name="invoice_number" class="form-control" autocomplete="on" value="MK" required>
                <label class="fw-bold">Invoice Description</label>
                <input type="text" name="invoice_details" class="form-control">
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label class="fw-bold">Order Date*</label>
                    <input type="date" name="order_date" class="form-control" required>
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
                            <label class="fw-bold">Apparel Type*</label>
                            <select id="item_type" name="item_type[]" class="form-control item-type" required onchange="showWorkslip(this)">
                                <option value="">Select Apparel</option>
                                <option value="SHIRT">Shirt</option>
                                <option value="TROUSER">Trousers</option>
                                <option value="JACKET">Jacket</option>
                                <option value="BAJU MELAYU" disabled>Baju Melayu</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="fw-bold">Quantity*</label>
                            <input type="number" name="quantity[]" class="form-control" autocomplete="on" required>
                        </div>
                        <div class="col">
                            <label class="fw-bold">Fabric Code</label>
                            <input type="text" name="fabric_code[]" class="form-control" autocomplete="on">
                        </div>
                        <div class="col">
                            <label class="fw-bold">Fabric Name*</label>
                            <input type="text" name="fabric_name[]" class="form-control" required>
                        </div>
                        <div class="col">
                            <label class="fw-bold">Fabric Color</label>
                            <input type="text" name="fabric_color[]" autocomplete="on" class="form-control">
                        </div>
                        <div class="col">
                            <label class="fw-bold">Fabric Usage</label>
                            <input type="number" step="0.01" name="fabric_usage[]" autocomplete="on" class="form-control">
                        </div>
                        <div class="col">
                            <label class="fw-bold">Amount</label>
                            <input type="number" step="0.01" name="amount[]" autocomplete="on" class="form-control">
                        </div>
                    </div>

                    <!-- Workslip section (hidden by default) -->
                    <div class="workslip mt-2" style="display:none;">
                        <h6>Workslip</h6>
                        <div class="workslip-fields"></div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary mb-3" onclick="addItem()">+ Add Item</button>

            <h4>Payments</h4>
            <div class="row mb-3">
                <div class="col">
                    <label class="fw-bold">Total</label>
                    <input type="number" step="0.01" id="total_amount" name="total_amount" class="form-control" placeholder="Total Amount" autocomplete="on" value="0.00">
                </div>
                <div class="col">
                    <label class="fw-bold">Deposit</label>
                    <input type="number" step="0.01" id="deposit_amount" name="deposit_amount" class="form-control" placeholder="Deposit" autocomplete="on" value="0.00">
                </div>
                <div class="col">
                    <label class="fw-bold">Balance</label>
                    <input type="number" step="0.01" id="balance_amount" name="balance_amount" class="form-control" placeholder="Balance" autocomplete="on" value="0.00">
                </div>
                <div class="col">
                    <label class="fw-bold">Additional Deposit</label>
                    <input type="number" step="0.01" id="additional_deposit" name="additional_deposit" class="form-control" placeholder="Additional Deposit" autocomplete="on" value="0.00">
                </div>
                <div class="col">
                    <label class="fw-bold">Final Balance</label>
                    <input type="number" step="0.01" id="additional_balance" name="additional_balance" class="form-control" placeholder="Additional Balance" autocomplete="on" value="0.00">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Invoice?</button>
        </form>
    </div>

    <script>
        //Add item
        function addItem() {
            let template = document.querySelector(".item-block").outerHTML;
            document.getElementById("items").insertAdjacentHTML("beforeend", template);
        }

        //Show workslip
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
                                <select id="shirt_type" name="shirt_type[]" class="form-control" required>
                                    <option value="" disabled selected >Select Shirt Type</option>
                                    <option value="SH/S">Shirt (Short Sleeve)</option>
                                    <option value="SH/L">Shirt (Long Sleeve)</option>
                                    <option value="BSH/S">Batik Shirt (Short Sleeve)</option>
                                    <option value="BSH/L">Batik Shirt (Long Sleeve)</option>
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
                                <select name="fabric_direction[]" class="form-control">
                                    <option value="No Direction" selected>No Direction</option>
                                    <option value="Vertical">Vertical</option>
                                    <option value="Horizontal">Horizontal</option>
                                </select>   
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Collar Design</label>
                                <select name="collar_design[]" class="form-control">
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
                                <input type="number" name="collar_height[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Collar Width</label>
                                <input type="number" name="collar_width[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Collar Gap</label>
                                <input type="number" name="collar_gap[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Collar Meet</label>
                                <input type="number" name="collar_meet[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Collar Length</label>
                                <input type="number" name="collar_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Back Length</label>
                                <input type="number" name="back_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Front Length</label>
                                <input type="number" name="front_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Chest (Fit)</label>
                                <input type="number" name="chest_fit[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Chest (Loose)</label>
                                <input type="number" name="chest_loose[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Waist (Fit)</label>
                                <input type="number" name="waist_fit[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Waist (Loose)</label>
                                <input type="number" name="waist_loose[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Hip (Fit)</label>
                                <input type="number" name="hip_fit[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Hip (Loose)</label>
                                <input type="number" name="hip_loose[]" class="form-control" step="0.01" max="999.99">
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
                                <input type="number" name="shoulder[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Sleeve Length</label>
                                <input type="number" name="sleeve_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Arm Length</label>
                                <input type="number" name="arm_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Elbow</label>
                                <input type="number" name="elbow_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Cuff Type</label>
                                <select name="cuff_type[]" class="form-control" required>
                                    <option value="No Cuff" selected>No Cuff</option>
                                    <option value="Single Cuff">Single Cuff</option>
                                    <option value="Double Cuff">Double Cuff</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Cuff Length</label>
                                <input type="number" name="cuff_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Cuff Width</label>
                                <input type="number" name="cuff_width[]" class="form-control" step="0.01" max="999.99">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Armhole</label>
                                <input type="number" name="armhole_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Erect</label>
                                <input type="number" name="erect[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Hunch</label>
                                <input type="number" name="hunch[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Corpulent</label>
                                <input type="number" name="corpulent[]" class="form-control" step="0.01" max="999.99">  
                            </div>
                            <div class="col">
                                <label class="fw-bold">Front Cutting</label>
                                <select name="front_cutting[]" class="form-control">
                                    <option value="" disabled selected>Select Front Cutting</option>
                                    <option value="Straight">Straight</option>
                                    <option value="Rounded">Rounded</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Placket Type</label>
                                <select name="placket_type[]" class="form-control">
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
                            <div class="row mb-2">
                                <div class="col">
                                    <label class="fw-bold">Design Option</label>
                                    <select name="design_option[]" class="form-control design-option" required>
                                        <option value="" disabled selected>Select Design Option</option>
                                        <option value="default">Use Default Design</option>
                                        <option value="upload">Upload Own Design</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="fw-bold">Upload Drawing</label>
                                    <input type="file" name="drawing[]" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                    <small class="text-muted">Accepted formats: JPG, PNG, or PDF (max size 5MB)</small>
                                </div>
                            </div>
                            <div class="row mb-3 default-design-preview">
                                <div class="col">
                                <label class="fw-bold">Default Design Preview</label>
                                    <div class="border rounded p-2 text-center bg-light">
                                        <img src="" alt="Default Design Preview" class="img-fluid default-design-img" style="max-height: 250px;">
                                    </div>
                                </div>
                            </div>
                        </div>    
                        `;
                    break;
                case "TROUSERS":
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
                                    <option value="Yes">Yes</option>
                                    <option value="No" selected>No</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Side Pocket Stitch?</label>
                                <select name="side_pocket_hs[]" class="form-control" required>
                                    <option value="Yes">Yes</option>
                                    <option value="No" selected>No</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Side Seams Stitch?</label>
                                <select name="side_seams_hs[]" class="form-control" required>
                                    <option value="Yes">Yes</option>
                                    <option value="No" selected>No</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Pocket Pull Stitch?</label>
                                <select name="pocket_pull[]" class="form-control" required>
                                    <option value="Yes">Yes</option>
                                    <option value="No" selected>No</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Pleat Number</label>
                                <select name="pleat_num[]" class="form-control" required>
                                    <option value="0" selected>0</option>
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
                                <input type="number" name="waist_fit[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Waist (Loose)</label>
                                <input type="number" name="waist_loose[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Hip (Fit)</label>
                                <input type="number" name="hip_fit[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Hip (Loose)</label>
                                <input type="number" name="hip_loose[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Top Hip (Fit)</label>
                                <input type="number" name="top_hip_fit[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Top Hip (Loose)</label>
                                <input type="number" name="top_hip_loose[]" class="form-control" step="0.01" max="999.99">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Length</label>
                                <input type="number" name="length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Thigh</label>
                                <input type="number" name="thigh[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Knee</label>
                                <input type="number" name="knee[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Bottom</label>
                                <input type="number" name="bottom[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Crotch</label>
                                <input type="number" name="crotch[]" class="form-control" step="0.01" max="999.99">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Position on Waist</label>
                                <select name="position_on_waist[]" class="form-control">
                                    <option value="Not Stated" disabled selected>Not Stated</option>
                                    <option value="Front High">Front High</option>
                                    <option value="Front Cut Low">Front Cut Low</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Corpulent</label>
                                <input type="number" name="corpulent[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Seating Type</label>
                                <select name="seating_type[]" class="form-control">
                                    <option value="Not Stated" disabled selected>Not Stated</option>
                                    <option value="Prom Seat (Hollow Back Waist)">Prom Seat (Hollow Back Waist)</option>
                                    <option value="Flat Seat">Flat Seat</option>
                                </select>   
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Turn Up</label>
                                <select name="turn_up[]" class="form-control">
                                    <option value="Yes">Yes</option>
                                    <option value="No" selected>No</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Turn Up Length</label>
                                <input type="number" name="turn_up_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Right Pocket</label>
                                <select name="right_pocket[]" class="form-control">
                                    <option value="Yes" selected>Yes</option>
                                    <option value="No">No</option>
                                </select>  
                            </div>
                            <div class="col">
                                <label class="fw-bold">Left Pocket</label>
                                <select name="left_pocket[]" class="form-control">
                                    <option value="Yes" selected>Yes</option>
                                    <option value="No">No</option>
                                </select>  
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Inside Pocket Number</label>
                                <input type="text" name="inside_pocket_num[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Inside Pocket Width</label>
                                <input type="number" name="inside_pocket_width[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Inside Pocket Length</label>
                                <input type="number" name="inside_pocket_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Loop Number</label>
                                <input type="text" name="loop_num[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Loop Width</label>
                                <input type="number" name="loop_width[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Loop Length</label>
                                <input type="number" name="loop_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Lining Type</label>
                                <select name="lining_type[]" class="form-control">
                                    <option value="Not Stated" disabled selected>Not Stated</option>
                                    <option value="Half Lined Front Only">Half Lined Front Only</option>
                                    <option value="Front Back 1/2 Lining">Front Back 1/2 Lining</option>
                                    <option value="Front Full Length Lined">Front Full Length Lined</option>
                                    <option value="Trousers Full Lined">Trousers Full Lined</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Bottom Initial</label>
                                <input type="text" name="bottom_initial[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Cleaning Type</label>
                                <select name="cleaning_type[]" class="form-control">
                                    <option value="No Restriction" selected>No Restriction</option>
                                    <option value="Dry Clean Only">Dry Clean Only</option>
                                    <option value="Hand Wash Only">Hand Wash Only</option>
                                </select>   
                            </div>
                            <div class="form-group">
                                <label for="drawing">Upload Drawing (PDF/JPG/PNG):</label>
                                <input type="file" name="drawing[]" id="drawing" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        `;
                    break;
                case "JACKET":
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
                            <div class="col">
                                <label class="fw-bold">Previous Invoice No.</label>
                                <input type="text" name="previous_invoice_number[]" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Special Instructions</label>
                                <textarea name="special_instructions[]" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Back Length</label>
                                <input type="number" name="back_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Front Length</label>
                                <input type="number" name="front_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Chest (Fit)</label>
                                <input type="number" name="chest_fit[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Chest (Loose)</label>
                                <input type="number" name="chest_loose[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Waist (Fit)</label>
                                <input type="number" name="waist_fit[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Waist (Loose)</label>
                                <input type="number" name="waist_loose[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Hip (Fit)</label>
                                <input type="number" name="hip_fit[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Hip (Loose)</label>
                                <input type="number" name="hip_loose[]" class="form-control" step="0.01" max="999.99">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Shoulder Length</label>
                                <input type="number" name="shoulder[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Sleeve Length</label>
                                <input type="number" name="sleeve_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Cuff Length</label>
                                <input type="number" name="cuff_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Cross Back</label>
                                <input type="number" name="cross_back[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Cross Front</label>
                                <input type="number" name="cross_front[]" class="form-control" step="0.01" max="999.99">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Vest/Elbow Length</label>
                                <input type="number" name="vest_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Back Neck to Waist</label>
                                <input type="number" name="back_neck_to_waist[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Back Neck to Front Waist</label>
                                <input type="number" name="back_neck_to_front_waist[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Sleeve Button</label>
                                <input type="number" name="sleeve_button[]" class="form-control" step="1" max="5">
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
                                    <option value="No Restriction" selected>No Restriction</option>
                                    <option value="Dry Clean Only">Dry Clean Only</option>
                                    <option value="Hand Wash Only">Hand Wash Only</option>
                                </select>   
                            </div>
                            <div class="form-group col">
                                <label for="drawing">Upload Drawing (PDF/JPG/PNG):</label>
                                <input type="file" name="drawing[]" id="drawing" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        `;
                    break;
                case "BAJU MELAYU":
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
                            <div class="col">
                                <label class="fw-bold">Fabric Direction</label>
                                <select name="fabric_direction[]" class="form-control">
                                    <option value="No Direction" selected>No Direction</option>
                                    <option value="Vertical">Vertical</option>
                                    <option value="Horizontal">Horizontal</option>
                                </select>   
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Collar Type</label>
                                <select name="collar_type[]" class="form-control" required>
                                    <option value="Teluk Belanga">Teluk Belanga</option>
                                    <option value="Cekak Musang">Cekak Musang</option>
                                    <option value="Mandarin">Mandarin</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Collar Height</label>
                                <input type="number" name="collar_height[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Collar Width</label>
                                <input type="number" name="collar_width[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Collar Gap</label>
                                <input type="number" name="collar_gap[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Collar Meet</label>
                                <input type="number" name="collar_meet[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Collar Length</label>
                                <input type="number" name="collar_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Chest (Fit)</label>
                                <input type="number" name="chest_fit[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Chest (Loose)</label>
                                <input type="number" name="chest_loose[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Waist (Fit)</label>
                                <input type="number" name="waist_fit[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Waist (Loose)</label>
                                <input type="number" name="waist_loose[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Hip (Fit)</label>
                                <input type="number" name="hip_fit[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Hip (Loose)</label>
                                <input type="number" name="hip_loose[]" class="form-control" step="0.01" max="999.99">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Shoulder Length</label>
                                <input type="number" name="shoulder[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Sleeve Length</label>
                                <input type="number" name="sleeve_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Arm Length</label>
                                <input type="number" name="arm_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label class="fw-bold">Armhole</label>
                                <input type="number" name="armhole_length[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Erect</label>
                                <input type="number" name="erect[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Hunch</label>
                                <input type="number" name="hunch[]" class="form-control" step="0.01" max="999.99">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Corpulent</label>
                                <input type="number" name="corpulent[]" class="form-control" step="0.01" max="999.99">  
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
                        `;
                    break;
            }
            fields.innerHTML = html;
        }

        //Cuff Type
        document.querySelectorAll('.cuff_type').forEach(function(select) {
            select.addEventListener('change', function() {
                const cuffLength = this.closest('.col').parentElement.querySelector('.cuff_length');
                const cuffWidth = this.closest('.col').parentElement.querySelector('.cuff_width');
                if (this.value === 'No Cuff') {
                    cuffLength.disabled = true;
                    cuffLength.value = '';
                    cuffWidth.disabled = true;
                    cuffWidth.value = '';
                } else {
                    cuffLength.disabled = false;
                    cuffWidth.disabled = false;
                }
            });
            // Trigger change event on page load to set initial state
            select.dispatchEvent(new Event('change'));
        });

        //Calculate total
        document.addEventListener('input', function(e) {
            if (e.target.name === 'amount[]' ||
                e.target.id === 'deposit_amount' ||
                e.target.id === 'additional_deposit') {
                calculateTotals();
            }
        });

        //Calculate total
        function calculateTotals() {
            // --- 1. Calculate total amount ---
            let total = 0;
            document.querySelectorAll('input[name="amount[]"]').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById('total_amount').value = total.toFixed(2);

            // --- 2. Calculate balance amount ---
            const deposit = parseFloat(document.getElementById('deposit_amount').value) || 0;
            const balance = total - deposit;
            document.getElementById('balance_amount').value = balance.toFixed(2);

            // --- 3. Calculate additional amount ---
            const additionalDeposit = parseFloat(document.getElementById('additional_deposit').value) || 0;
            const additionalAmount = balance - additionalDeposit;
            document.getElementById('additional_amount').value = additionalAmount.toFixed(2);
        }
    </script>
</body>

</html>