<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';

if (isset($_GET['invoice_id'])) {
    $invoice_id = $_GET['invoice_id'];

    // Start a transaction for safety
    $conn->begin_transaction();

    try {
        // 1. Get all item_ids under this invoice
        $stmt = $conn->prepare("SELECT item_id FROM invoice_items WHERE invoice_id = ?");
        $stmt->bind_param("s", $invoice_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $item_ids = [];
        while ($row = $result->fetch_assoc()) {
            $item_ids[] = $row['item_id'];
        }

        // If there are related items, delete their workslips
        if (!empty($item_ids)) {
            $placeholders = implode(',', array_fill(0, count($item_ids), '?'));
            $types = str_repeat('s', count($item_ids));

            // Prepare deletion for each workslip table
            $tables = ['workslip_baju_melayu', 'workslip_jacket', 'workslip_shirts', 'workslip_trousers'];

            foreach ($tables as $table) {
                $sql = "DELETE FROM $table WHERE item_id IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$item_ids);
                $stmt->execute();
            }
        }

        // 2. Delete items
        $stmt = $conn->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
        $stmt->bind_param("s", $invoice_id);
        $stmt->execute();

        // 3. Delete invoice
        $stmt = $conn->prepare("DELETE FROM invoices WHERE invoice_id = ?");
        $stmt->bind_param("s", $invoice_id);
        $stmt->execute();

        // Commit all
        $conn->commit();

        header("Location: invoices_list.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error deleting invoice: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
