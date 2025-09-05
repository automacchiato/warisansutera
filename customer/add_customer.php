<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Add Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">
    <h2>Add Customer</h2>
    <form method="POST">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" required>
        </div>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
</body>

</html>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("INSERT INTO customers (name, address, phone) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['name'], $_POST['address'], $_POST['phone']);
    $stmt->execute();
    echo "<div class='alert alert-success mt-3'>Customer Added!</div>";
}
?>