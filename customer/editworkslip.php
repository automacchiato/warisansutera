<?php
include 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['invoice_id'])) {
    die("Invoice ID not provided.");
}

$invoice_id = $_GET['invoice_id'];

// --- FETCH EXISTING DATA ---
$invoiceQuery = $conn->prepare("SELECT * FROM invoices WHERE invoice_id = ?");
$invoiceQuery->bind_param("i", $invoice_id);
$invoiceQuery->execute();
$invoice = $invoiceQuery->get_result()->fetch_assoc();

$customerQuery = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
$customerQuery->bind_param("i", $invoice['customer_id']);
$customerQuery->execute();
$customer = $customerQuery->get_result()->fetch_assoc();

$itemsQuery = $conn->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$itemsQuery->bind_param("i", $invoice_id);
$itemsQuery->execute();
$invoice_items = $itemsQuery->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch workslip data for each item
foreach ($invoice_items as &$item) {
    $table = '';
    switch ($item['item_type']) {
        case 'SHIRT':
            $table = 'workslip_shirts';
            break;
        case 'TROUSERS':
            $table = 'workslip_trousers';
            break;
        case 'JACKET':
            $table = 'workslip_jacket';
            break;
        case 'BAJU MELAYU':
            $table = 'workslip_baju_melayu';
            break;
    }

    if ($table) {
        $workslipQuery = $conn->prepare("SELECT * FROM $table WHERE item_id = ?");
        $workslipQuery->bind_param("i", $item['item_id']);
        $workslipQuery->execute();
        $item['workslip'] = $workslipQuery->get_result()->fetch_assoc();
    }
}
unset($item); // Break reference

//         $conn->commit();
//         echo "<div class='alert alert-success'>Invoice Updated Successfully!</div>";
//     } catch (Exception $e) {
//         $conn->rollback();
//         echo "<div class='alert alert-danger'>Error updating invoice: " . $e->getMessage() . "</div>";
//     }
// }
?>


<!DOCTYPE html>
<html>

<head>
    <title>Edit Order</title>
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
                        <a class="nav-link disabled" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../test/index.php">Overview</a>
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

    <div class="container p-4">
        <h2 class="text-center mb-4">Edit Order</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="customer_id" value="<?= $customer['customer_id'] ?>">
            <input type="hidden" name="invoice_id" value="<?= $invoice['invoice_id'] ?>">
            <h4>Customer Details</h4>
            <div class="mb-3">
                <label class="fw-bold">Name*</label>
                <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($customer['customer_name'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold">Address*</label>
                <textarea name="customer_address" class="form-control" required><?= htmlspecialchars($customer['customer_address'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="fw-bold">Email</label>
                <input type="text" name="customer_email" class="form-control" value="<?= htmlspecialchars($customer['customer_email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="fw-bold">Phone*</label>
                <input type="text" name="customer_phone" class="form-control" value="<?= htmlspecialchars($customer['customer_phone'] ?? '') ?>" required>
            </div>

            <h4>Invoice Details</h4>
            <div class="mb-3">
                <label class="fw-bold">Invoice Number*</label>
                <input type="text" name="invoice_number" class="form-control" value="<?= htmlspecialchars($invoice['invoice_number'] ?? '') ?>" required>
                <label class="fw-bold">Invoice Description</label>
                <input type="text" name="invoice_details" class="form-control" value="<?= htmlspecialchars($invoice['invoice_details'] ?? '') ?>">
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label class="fw-bold">Order Date*</label>
                    <input type="date" name="order_date" class="form-control" value="<?= htmlspecialchars($invoice['order_date'] ?? '') ?>" required>
                </div>
                <div class="col">
                    <label class="fw-bold">Fitting Date</label>
                    <input type="date" name="fitting_date" class="form-control" value="<?= htmlspecialchars($invoice['fitting_date'] ?? '') ?>">
                </div>
                <div class="col">
                    <label class="fw-bold">Delivery Date</label>
                    <input type="date" name="delivery_date" class="form-control" value="<?= htmlspecialchars($invoice['delivery_date'] ?? '') ?>">
                </div>
            </div>

            <h4>Items</h4>
            <div id="items">
                <?php foreach ($invoice_items as $item): ?>
                    <div class="item-block border rounded p-2 mb-3">
                        <input type="hidden" name="item_id[]" value="<?= $item['item_id'] ?>">
                        <!-- Use drawing from workslip table, not invoice_items -->
                        <input type="hidden" name="existing_drawing[]" value="<?= htmlspecialchars($item['workslip']['drawing'] ?? '') ?>">
                        <div class="row g-2 mb-2">
                            <div class="col">
                                <label class="fw-bold">Apparel Type*</label>
                                <select id="item_type_<?= $item['item_id'] ?>" name="item_type[]" class="form-control item-type" required onchange="showWorkslip(this)">
                                    <option value="SHIRT" <?= $item['item_type'] == 'SHIRT' ? 'selected' : '' ?>>Shirt</option>
                                    <option value="TROUSERS" <?= $item['item_type'] == 'TROUSERS' ? 'selected' : '' ?>>Trousers</option>
                                    <option value="JACKET" <?= $item['item_type'] == 'JACKET' ? 'selected' : '' ?>>Jacket</option>
                                    <option value="BAJU MELAYU" <?= $item['item_type'] == 'BAJU MELAYU' ? 'selected' : '' ?>>Baju Melayu</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="fw-bold">Quantity*</label>
                                <input type="number" name="quantity[]" class="form-control" value="<?= htmlspecialchars($item['quantity'] ?? '') ?>" required>
                            </div>
                            <div class="col">
                                <label class="fw-bold">Fabric Code</label>
                                <input type="text" name="fabric_code[]" class="form-control" value="<?= htmlspecialchars($item['fabric_code'] ?? '') ?>">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Fabric Name*</label>
                                <input type="text" name="fabric_name[]" class="form-control" value="<?= htmlspecialchars($item['fabric_name'] ?? '') ?>" required>
                            </div>
                            <div class="col">
                                <label class="fw-bold">Fabric Color</label>
                                <input type="text" name="fabric_color[]" class="form-control" value="<?= htmlspecialchars($item['fabric_color'] ?? '') ?>">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Fabric Usage</label>
                                <input type="number" step="0.01" name="fabric_usage[]" class="form-control" value="<?= htmlspecialchars($item['fabric_usage'] ?? '') ?>">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Amount</label>
                                <input type="number" step="0.01" name="amount[]" class="form-control" value="<?= htmlspecialchars($item['amount'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Workslip section - Show if data exists -->
                        <div class="workslip mt-2" style="display: <?= isset($item['workslip']) ? 'block' : 'none' ?>;">
                            <h6>Workslip</h6>
                            <div class="workslip-fields" data-item-type="<?= $item['item_type'] ?>" data-workslip='<?= isset($item['workslip']) ? json_encode($item['workslip']) : '{}' ?>'></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-secondary mb-3" onclick="addItem()">+ Add Item</button>

            <h4>Payments</h4>
            <div class="row mb-3">
                <div class="col">
                    <label class="fw-bold">Total</label>
                    <input type="number" step="0.01" id="total_amount" name="total_amount" class="form-control" value="<?= htmlspecialchars($invoice['total_amount'] ?? '0.00') ?>">
                </div>
                <div class="col">
                    <label class="fw-bold">Deposit</label>
                    <input type="number" step="0.01" id="deposit_amount" name="deposit_amount" class="form-control" value="<?= htmlspecialchars($invoice['deposit_amount'] ?? '0.00') ?>">
                </div>
                <div class="col">
                    <label class="fw-bold">Balance</label>
                    <input type="number" step="0.01" id="balance_amount" name="balance_amount" class="form-control" value="<?= htmlspecialchars($invoice['balance_amount'] ?? '0.00') ?>">
                </div>
                <div class="col">
                    <label class="fw-bold">Additional Deposit</label>
                    <input type="number" step="0.01" id="additional_deposit" name="additional_deposit" class="form-control" value="<?= htmlspecialchars($invoice['additional_deposit'] ?? '0.00') ?>">
                </div>
                <div class="col">
                    <label class="fw-bold">Final Balance</label>
                    <input type="number" step="0.01" id="additional_balance" name="additional_balance" class="form-control" value="<?= htmlspecialchars($invoice['additional_balance'] ?? '0.00') ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update Invoice</button>
        </form>
    </div>

    <script>
        //Add item
        function addItem() {
            let template = document.querySelector(".item-block").cloneNode(true);

            // Reset all input values in the cloned template
            template.querySelectorAll('input, select, textarea').forEach(input => {
                if (input.type === 'file') {
                    input.value = '';
                } else if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0;
                } else {
                    input.value = '';
                }
            });

            // Hide workslip in cloned template
            template.querySelector('.workslip').style.display = 'none';
            template.querySelector('.workslip-fields').innerHTML = '';

            document.getElementById("items").appendChild(template);
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
                        </div>
                        <div class="row mb-2">
            <div class="col">
                <label class="fw-bold">Design Option</label>
                <select name="design_option[]" class="form-control design-option" required onchange="updateDesignPreview(this)">
                    <option value="" disabled>Select Design Option</option>
                    <option value="keep_existing">Keep Existing Drawing</option>
                    <option value="default">Use Default Design</option>
                    <option value="upload">Upload New Design</option>
                </select>
            </div>
        </div>
        
        <!-- Existing Drawing Display -->
        <div class="row mb-2 existing-drawing-section d-none">
            <div class="col">
                <label class="fw-bold">Current Drawing</label>
                <div class="existing-drawing-display"></div>
            </div>
        </div>
        
        <div class="row mb-2 upload-design d-none">
            <div class="col">
                <label class="fw-bold">Upload New Drawing</label>
                <input type="file" name="drawing[]" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                <small class="text-muted">Accepted formats: JPG, PNG, or PDF (max size 5MB)</small>
            </div>
        </div>
        
        <div class="row mb-3 default-design-preview d-none">
            <div class="col">
                <label class="fw-bold">Default Design Preview</label>
                <div class="border rounded p-2 text-center bg-light">
                    <img src="" alt="Default Design Preview" class="img-fluid default-design-img" style="max-height: 250px;">
                </div>
                <button type="button" class="btn btn-primary btn-sm mt-2 use-default-btn d-none">
                    Use This Design as My Drawing
                </button>
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
                            <div class="row mb-2">
                        <div class="col">
                            <label class="fw-bold">Design Option</label>
                            <select name="design_option[]" class="form-control design-option" required onchange="updateDesignPreview(this)">
                                <option value="" disabled selected>Select Design Option</option>
                                <option value="default">Use Default Design</option>
                                <option value="upload">Upload Own Design</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2 upload-design d-none">
                        <div class="col">
                            <label class="fw-bold">Upload Drawing</label>
                            <input type="file" name="drawing[]" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">Accepted formats: JPG, PNG, or PDF (max size 5MB)</small>
                        </div>
                    </div>
                    <div class="row mb-3 default-design-preview d-none">
                        <div class="col">
                            <label class="fw-bold">Default Design Preview</label>
                            <div class="border rounded p-2 text-center bg-light">
                                <img src="" alt="Default Design Preview" class="img-fluid default-design-img" style="max-height: 250px;">
                            </div>
                            <!-- 🆕 New Button -->
                            <button type="button" class="btn btn-primary btn-sm mt-2 use-default-btn d-none">
                                Use This Design as My Drawing
                            </button>
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
                            <div class="row mb-2">
                        <div class="col">
                            <label class="fw-bold">Design Option</label>
                            <select name="design_option[]" class="form-control design-option" required onchange="updateDesignPreview(this)">
                                <option value="" disabled selected>Select Design Option</option>
                                <option value="default">Use Default Design</option>
                                <option value="upload">Upload Own Design</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2 upload-design d-none">
                        <div class="col">
                            <label class="fw-bold">Upload Drawing</label>
                            <input type="file" name="drawing[]" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">Accepted formats: JPG, PNG, or PDF (max size 5MB)</small>
                        </div>
                    </div>
                    <div class="row mb-3 default-design-preview d-none">
                        <div class="col">
                            <label class="fw-bold">Default Design Previewss</label>
                            <div class="border rounded p-2 text-center bg-light">
                                <img src="" alt="Default Design Preview" class="img-fluid default-design-img" style="max-height: 250px;">
                            </div>
                            <!-- 🆕 New Button -->
                            <button type="button" class="btn btn-primary btn-sm mt-2 use-default-btn d-none">
                                Use This Design as My Drawing
                            </button>
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
                                <select name="collar_type[]" class="form-control">
                                    <option value="" disabled selected>Select Collar Design</option>
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
                                <select name="cutting_type[]" class="form-control">
                                    <option value="" disabled selected>Select Front Cutting</option>
                                    <option value="Slim Fit">Slim Fit</option>
                                    <option value="Normal Size">Normal Size</option>
                                </select>   
                            </div>
                            <div class="col">
                                <label class="fw-bold">Buttons Type</label>
                                <input type="text" name="buttons_type[]" class="form-control">
                            </div>
                            <div class="col">
                                <label class="fw-bold">Pesak</label>
                                <input type="number" name="pesak[]" class="form-control" step="0.01" max="999.99">  
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
                            <select name="design_option[]" class="form-control design-option" required onchange="updateDesignPreview(this)">
                                <option value="" disabled selected>Select Design Option</option>
                                <option value="default">Use Default Design</option>
                                <option value="upload">Upload Own Design</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2 upload-design d-none">
                        <div class="col">
                            <label class="fw-bold">Upload Drawing</label>
                            <input type="file" name="drawing[]" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">Accepted formats: JPG, PNG, or PDF (max size 5MB)</small>
                        </div>
                    </div>
                    <div class="row mb-3 default-design-preview d-none">
                        <div class="col">
                            <label class="fw-bold">Default Design Previewss</label>
                            <div class="border rounded p-2 text-center bg-light">
                                <img src="" alt="Default Design Preview" class="img-fluid default-design-img" style="max-height: 250px;">
                            </div>
                            <!-- 🆕 New Button -->
                            <button type="button" class="btn btn-primary btn-sm mt-2 use-default-btn d-none">
                                Use This Design as My Drawing
                            </button>
                        </div>
                    </div>
                        `;
                    break;
            }
            fields.innerHTML = html;
        }

        // Default design paths
        const defaultDesignPaths = {
            SHIRT: {
                'SH/S': './customer/defaults/default_shirt_short.png',
                'SH/L': './customer/defaults/default_shirt_long.png',
                'BSH/S': './customer/defaults/default_shirt_short.png',
                'BSH/L': './customer/defaults/default_shirt_long.png',
            },
            TROUSERS: './customer/defaults/default_trousers.png',
            JACKET: './customer/defaults/default_jackets.png',
            'BAJU MELAYU': './customer/defaults/default_bajumelayu.png'
        };

        // Update design preview (works for any item block)
        function updateDesignPreview(selectElement) {
            const itemBlock = selectElement.closest('.item-block');
            const designOption = selectElement.value;
            const apparelType = itemBlock.querySelector('[name="item_type[]"]').value;
            const uploadCol = itemBlock.querySelector('.upload-design');
            const uploadInput = uploadCol.querySelector('input[type="file"]');
            const previewRow = itemBlock.querySelector('.default-design-preview');
            const previewImg = itemBlock.querySelector('.default-design-img');
            const useDefaultBtn = itemBlock.querySelector('.use-default-btn');
            const existingSection = itemBlock.querySelector('.existing-drawing-section');

            // Reset
            uploadCol.classList.add('d-none');
            uploadInput.removeAttribute('required');
            previewRow.classList.add('d-none');
            existingSection.classList.add('d-none');
            previewImg.src = '';

            if (designOption === 'keep_existing') {
                // Show existing drawing
                existingSection.classList.remove('d-none');
                const existingDrawing = itemBlock.querySelector('input[name="existing_drawing[]"]').value;
                if (existingDrawing) {
                    const fieldsDiv = itemBlock.querySelector('.workslip-fields');
                    showExistingDrawing(fieldsDiv, existingDrawing);
                }
            } else if (designOption === 'upload') {
                uploadCol.classList.remove('d-none');
                uploadInput.setAttribute('required', 'required');
            } else if (designOption === 'default') {
                let imagePath = '';

                if (apparelType === 'SHIRT') {
                    const shirtTypeSelect = itemBlock.querySelector('[name="shirt_type[]"]');
                    const shirtType = shirtTypeSelect ? shirtTypeSelect.value : '';
                    imagePath = defaultDesignPaths.SHIRT[shirtType] || '';
                } else {
                    imagePath = defaultDesignPaths[apparelType] || '';
                }

                if (imagePath) {
                    previewImg.src = imagePath;
                    previewRow.classList.remove('d-none');
                    if (useDefaultBtn) useDefaultBtn.classList.remove('d-none');
                }
            }
        }

        // Update shirt design preview when shirt type changes
        function updateShirtDesignPreview(selectElement) {
            const itemBlock = selectElement.closest('.item-block');
            const designSelect = itemBlock.querySelector('.design-option');

            // Only update if default design is selected
            if (designSelect && designSelect.value === 'default') {
                updateDesignPreview(designSelect);
            }
        }

        // 🆕 Allow user to "upload" default design automatically
        document.addEventListener('click', async function(e) {
            if (e.target.classList.contains('use-default-btn')) {
                const itemBlock = e.target.closest('.item-block');
                const previewImg = itemBlock.querySelector('.default-design-img');
                const uploadInput = itemBlock.querySelector('.upload-design input[type="file"]');

                try {
                    const response = await fetch(previewImg.src);
                    const blob = await response.blob();
                    const file = new File([blob], 'default_design.png', {
                        type: blob.type
                    });

                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    uploadInput.files = dataTransfer.files;

                    // Show upload section
                    itemBlock.querySelector('.upload-design').classList.remove('d-none');
                    uploadInput.setAttribute('required', 'required');

                    // Feedback
                    e.target.textContent = 'Design Selected ✓';
                    e.target.classList.replace('btn-primary', 'btn-success');
                    e.target.disabled = true;
                } catch (err) {
                    alert('Error selecting design: ' + err.message);
                }
            }
        });

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
            document.getElementById('additional_balance').value = additionalAmount.toFixed(2);
        }

        // Add this at the end of your script section, before closing
        document.addEventListener('DOMContentLoaded', function() {
            // Populate existing workslips
            document.querySelectorAll('.workslip-fields').forEach(function(fieldsDiv) {
                const itemType = fieldsDiv.getAttribute('data-item-type');
                const workslipData = JSON.parse(fieldsDiv.getAttribute('data-workslip') || '{}');

                if (itemType && Object.keys(workslipData).length > 0) {
                    const itemBlock = fieldsDiv.closest('.item-block');
                    const select = itemBlock.querySelector('.item-type');

                    // Generate the workslip HTML
                    showWorkslip(select);

                    // Populate the fields with existing data
                    setTimeout(() => {
                        populateWorkslipData(fieldsDiv, workslipData);

                        // Show existing drawing if available
                        const existingDrawing = itemBlock.querySelector('input[name="existing_drawing[]"]').value;
                        if (existingDrawing) {
                            showExistingDrawing(fieldsDiv, existingDrawing);
                        }
                    }, 100);
                }
            });
        });

        // Function to populate workslip fields with existing data
        function populateWorkslipData(container, data) {
            Object.keys(data).forEach(key => {
                if (key === 'item_id' || key === 'drawing') return; // Skip these fields

                const input = container.querySelector(`[name="${key}[]"]`);
                if (input && data[key] !== null) {
                    if (input.type === 'checkbox') {
                        input.checked = data[key] == 1;
                    } else {
                        input.value = data[key];
                    }
                }
            });
        }

        // Function to display existing drawing
        function showExistingDrawing(container, drawingFilename) {
            const drawingDisplay = container.querySelector('.existing-drawing-display');
            if (!drawingDisplay || !drawingFilename) return;

            const fileExt = drawingFilename.split('.').pop().toLowerCase();
            const drawingPath = 'uploads/drawings/' + drawingFilename;

            let html = '<div class="border rounded p-2 bg-light">';

            if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
                // Image preview
                html += `
            <img src="${drawingPath}" alt="Current Drawing" class="img-fluid mb-2" style="max-height: 200px;">
            <br>
        `;
            } else if (fileExt === 'pdf') {
                // PDF link
                html += `
            <p class="mb-2">
                <i class="bi bi-file-pdf"></i> PDF Document
            </p>
        `;
            }

            html += `
        <a href="${drawingPath}" target="_blank" class="btn btn-sm btn-info">
            View/Download Current Drawing
        </a>
        <p class="text-muted small mt-2 mb-0">
            Upload a new file below to replace this drawing
        </p>
    </div>`;

            drawingDisplay.innerHTML = html;
        }
    </script>
</body>

</html>