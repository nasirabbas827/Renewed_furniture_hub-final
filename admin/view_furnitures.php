<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Update furniture status
if (isset($_POST['update_status'])) {
    $furniture_id = intval($_POST['furniture_id']);
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE furniture SET status=? WHERE furniture_id=?");
    $stmt->bind_param("si", $status, $furniture_id);
    $stmt->execute();
    $stmt->close();
}

// Delete operation
if (isset($_GET['delete'])) {
    $furniture_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM furniture WHERE furniture_id = $furniture_id");
}

// Fetch all furniture along with seller name and category name
$query = "
    SELECT f.*, 
           u.full_name AS seller_name, 
           c.category_name 
    FROM furniture f
    LEFT JOIN users u ON f.id = u.id
    LEFT JOIN categories c ON f.category_id = c.category_id
    ORDER BY f.furniture_id DESC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Furniture</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">All Furniture</h2>
    <table class="table table-bordered table-striped table-responsive-sm">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Seller</th>
                <th>Category</th>
                <th>Price</th>
                <th>Condition</th>
                <th>Location</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['furniture_id'] ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['seller_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['category_name'] ?? 'N/A') ?></td>
                <td>Rs. <?= number_format($row['price'], 2) ?></td>
                <td><?= htmlspecialchars($row['condition']) ?></td>
                <td><?= htmlspecialchars($row['location']) ?></td>
                <td>
                    <form method="POST" class="form-inline">
                        <input type="hidden" name="furniture_id" value="<?= $row['furniture_id'] ?>">
                        <select name="status"
                                class="form-control form-control-sm mr-2"
                                onchange="this.form.submit()">
                            <option value="available" <?= $row['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                            <option value="sold" <?= $row['status'] === 'sold' ? 'selected' : '' ?>>Sold</option>
                            <option value="blocked" <?= $row['status'] === 'blocked' ? 'selected' : '' ?>>Blocked</option>
                        </select>
                        <input type="hidden" name="update_status" value="1">
                    </form>
                </td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="?delete=<?= $row['furniture_id'] ?>"
                       onclick="return confirm('Delete this furniture?')"
                       class="btn btn-sm btn-danger mt-1">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
