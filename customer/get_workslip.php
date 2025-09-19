<?php
include 'db.php';

if (isset($_GET['invoice_id']) && isset($_GET['item_id']) && isset($_GET['item_type'])) {
    $invoice_id = intval($_GET['invoice_id']);
    $item_id = intval($_GET['item_id']);
    $item_type = strtolower(trim($_GET['item_type']));

    // Get basic item and customer info
    $query = "
        SELECT i.invoice_number, it.item_type, it.fabric_name, it.fabric_color, it.quantity,
               c.customer_name, c.customer_phone
        FROM invoices i
        JOIN invoice_items it ON i.invoice_id = it.invoice_id
        JOIN customers c ON i.customer_id = c.customer_id
        WHERE i.invoice_id = ? AND it.item_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $invoice_id, $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $itemInfo = $result->fetch_assoc();

        // Display header info
        echo "<div class='row mb-4'>";
        echo "<h3>" . htmlspecialchars($itemInfo['customer_name']) . "</h3>";
        echo "<div class='col-md-6'>";
        echo "<p><strong>Invoice:</strong> " . htmlspecialchars($itemInfo['invoice_number']) . "</p>";
        echo "<p><strong>Phone:</strong> " . htmlspecialchars($itemInfo['customer_phone']) . "</p>";
        echo "<p><strong>Item Type:</strong> " . htmlspecialchars($itemInfo['item_type']) . "</p>";
        echo "</div>";
        echo "<div class='col-md-6'>";
        echo "<p><strong>Fabric:</strong> " . htmlspecialchars($itemInfo['fabric_name']) . " - " . htmlspecialchars($itemInfo['fabric_color']) . "</p>";
        echo "<p><strong>Quantity:</strong> " . htmlspecialchars($itemInfo['quantity']) . "</p>";
        echo "</div>";
        echo "</div>";

        // Get workslip data based on item type
        displayWorkslipByType($conn, $item_id, $item_type);
    } else {
        echo "<p class='text-warning'>No item information found.</p>";
    }
} else {
    echo "<p class='text-danger'>Missing required parameters.</p>";
}

function displayWorkslipByType($conn, $item_id, $item_type)
{
    // Map item types to table names
    $tableMap = [
        'baju melayu' => 'workslip_baju_melayu',
        'baju_melayu' => 'workslip_baju_melayu',
        'jacket' => 'workslip_jacket',
        'shirt' => 'workslip_shirts',
        'shirts' => 'workslip_shirts',
        'trouser' => 'workslip_trousers',
        'trousers' => 'workslip_trousers',
        'pants' => 'workslip_trousers'
    ];

    // Get the correct table name
    $tableName = $tableMap[$item_type] ?? null;

    if (!$tableName) {
        echo "<p class='text-danger'>Unknown item type: " . htmlspecialchars($item_type) . "</p>";
        return;
    }

    // Check if table exists (optional safety check)
    $checkTable = "SHOW TABLES LIKE '$tableName'";
    $tableExists = $conn->query($checkTable);

    if ($tableExists->num_rows == 0) {
        echo "<p class='text-danger'>Workslip table not found for item type: " . htmlspecialchars($item_type) . "</p>";
        return;
    }

    // Get workslip data
    $query = "SELECT * FROM $tableName WHERE item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $workslip = $result->fetch_assoc();

        echo "<div class='mt-4'>";
        echo "<h6 class='mb-3 text-primary'>Measurements for " . htmlspecialchars(ucwords(str_replace('_', ' ', $item_type))) . "</h6>";

        // Display measurements based on item type
        switch ($item_type) {
            case 'baju melayu':
            case 'baju_melayu':
                displayBajuMelayuMeasurements($workslip);
                break;
            case 'jacket':
                displayJacketMeasurements($workslip);
                break;
            case 'shirt':
            case 'shirts':
                displayShirtMeasurements($workslip);
                break;
            case 'trouser':
            case 'trousers':
            case 'pants':
                displayTrouserMeasurements($workslip);
                break;
            default:
                displayGenericMeasurements($workslip);
        }

        echo "</div>";
    } else {
        echo "<div class='mt-4'>";
        echo "<p class='text-info'>No workslip measurements found for this item.</p>";
        echo "<p class='text-muted'>Item ID: $item_id | Table: $tableName</p>";
        echo "</div>";
    }
}

function displayBajuMelayuMeasurements($workslip)
{
    $measurements = [
        'chest_fit' => 'Chest (Fit)',
        'waist' => 'Waist',
        'shoulder' => 'Shoulder',
        'sleeve_length' => 'Sleeve Length',
        'baju_length' => 'Baju Length',
        'collar' => 'Collar',
        'cuff' => 'Cuff',
        'trouser_waist' => 'Trouser Waist',
        'trouser_length' => 'Trouser Length',
        'trouser_bottom' => 'Trouser Bottom'
    ];

    displayMeasurementsTable($workslip, $measurements);
}

function displayJacketMeasurements($workslip)
{
    $measurements = [
        'chest_fit' => 'Chest (Fit)',
        'waist' => 'Waist',
        'shoulder' => 'Shoulder',
        'sleeve_length' => 'Sleeve Length',
        'jacket_length' => 'Jacket Length',
        'collar' => 'Collar',
        'cuff' => 'Cuff',
        'lapel' => 'Lapel',
        'back_length' => 'Back Length'
    ];

    displayMeasurementsTable($workslip, $measurements);
}

function displayShirtMeasurements($workslip)
{
    $measurements = [
        'collar_design' => 'Collar Design',
        'collar_height' => 'Collar Height',
        'collar_width' => 'Collar Width',
        'collar_gap' => 'Collar Gap',
        'collar_meet' => 'Collar Meet',
        'collar_length' => 'Collar Length',
        'back_length' => 'Back Length',
        'front_length' => 'Front Length',
        'chest_fit' => 'Chest (Fit)',
        'chest_loose' => 'Chest (Loose)',
        'waist_fit' => 'Waist (Fit)',
        'waist_loose' => 'Waist (Loose)',
        'hip_fit' => 'Hip (Fit)',
        'hip_loose' => 'Hip (Loose)',
        'shoulder' => 'Shoulder',
        'sleeve_length' => 'Sleeve Length',
        'arm_length' => 'Arm Length',
        'elbow_length' => 'Elbow Length',
        'cuff_length' => 'Cuff Length',
        'armhole_length' => 'Armhole Length',
        'erect' => 'Erect',
        'hunch' => 'Hunch',
        'shoulder_type' => 'Shoulder Type',
        'corpulent' => 'Corpulent',
        'cuff_type' => 'Cuff Type',
        'cuff_width' => 'Cuff Width',
        'front_cutting' => 'Front Cutting',
        'placket_type' => 'Placket Type',
        'top_initial' => 'Top Initial',
        'bottom_initial' => 'Bottom Initial',
        'cleaning_type' => 'Cleaning Type',
    ];

    displayMeasurementsTable($workslip, $measurements);
}

function displayTrouserMeasurements($workslip)
{
    $measurements = [
        'waist' => 'Waist',
        'hip' => 'Hip',
        'thigh' => 'Thigh',
        'inseam' => 'Inseam',
        'outseam' => 'Outseam',
        'bottom' => 'Bottom Opening',
        'rise' => 'Rise',
        'trouser_length' => 'Trouser Length'
    ];

    displayMeasurementsTable($workslip, $measurements);
}

function displayGenericMeasurements($workslip)
{
    // Display all non-system fields
    $skipFields = ['id', 'item_id', 'workslip_id', 'created_at', 'updated_at', 'notes', 'special_instructions', 'manufacturer'];

    echo "<div class='table-responsive'>";
    echo "<table class='table table-bordered table-sm'>";
    echo "<thead class='table-dark'>";
    echo "<tr><th>Measurement</th><th>Value</th></tr>";
    echo "</thead><tbody>";

    foreach ($workslip as $field => $value) {
        if (!in_array($field, $skipFields) && !empty($value)) {
            $label = ucwords(str_replace('_', ' ', $field));
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($label) . "</strong></td>";
            echo "<td>" . htmlspecialchars($value) . "</td>";
            echo "</tr>";
        }
    }

    echo "</tbody></table>";
    echo "</div>";
}

function displayMeasurementsTable($workslip, $measurements)
{
    echo '<div class="container">';
    echo '<div class="row justify-content-start">';

    //LEFT COLUMN: Measurements
    echo '<div class="col">';
    echo "<div class='table-responsive'>";
    echo "<table class='table table-bordered table-sm'>";
    echo "<thead class='table-dark'>";
    echo "<tr><th>Measurement</th><th>Value (inches)</th></tr>";
    echo "</thead><tbody>";

    foreach ($measurements as $field => $label) {
        $value = $workslip[$field] ?? '';

        if (!empty($value)) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($label) . "</strong></td>";
            echo "<td class='text-end'>" . htmlspecialchars($value) . "</td>";
            echo "</tr>";
        }
    }

    echo "</tbody></table>";
    echo "</div>";
    echo '</div>';

    //RIGHT COLUMN: Photo
    echo '<div class="col">';

    if (!empty($workslip['drawing'])) {
        // If stored as path/filename
        echo "<img src='uploads/drawings/" . htmlspecialchars($workslip['drawing']) . "' 
                 class='img-fluid rounded border' 
                 alt='Workslip Photo'>";
    } else {
        echo "<p class='text-muted fst-italic'>No photo available</p>";
    }
    echo '</div>';

    echo '</div>';
    echo '</div>';


    // Display notes if available
    if (!empty($workslip['notes'])) {
        echo "<div class='mt-3'>";
        echo "<strong>Notes:</strong><br>";
        echo "<div class='p-2 bg-light rounded'>" . nl2br(htmlspecialchars($workslip['notes'])) . "</div>";
        echo "</div>";
    }

    // Display special instructions if available
    if (!empty($workslip['special_instructions'])) {
        echo "<div class='mt-3'>";
        echo "<strong>Special Instructions:</strong><br>";
        echo "<div class='p-2 bg-warning bg-opacity-25 rounded'>" . nl2br(htmlspecialchars($workslip['special_instructions'])) . "</div>";
        echo "</div>";
    }
}
