<?php
include 'db.php';

if (isset($_GET['invoice_id'])) {
    $invoice_id = intval($_GET['invoice_id']);

    $query = "
        SELECT w.workslip_id, w.description, w.created_at, w.drawing_file
        FROM workslips w
        WHERE w.invoice_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table class='table table-bordered'>";
        echo "<thead><tr><th>ID</th><th>Description</th><th>Created</th><th>Drawing</th></tr></thead><tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['workslip_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            if (!empty($row['drawing_file'])) {
                echo "<td><a href='uploads/" . htmlspecialchars($row['drawing_file']) . "' target='_blank'>View Drawing</a></td>";
            } else {
                echo "<td>No Drawing</td>";
            }
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No workslip found for this invoice.</p>";
    }
}
