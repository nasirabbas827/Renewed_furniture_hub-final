<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Update user status or upload_status
if (isset($_POST['update_status'])) {
    $user_id = intval($_POST['user_id']);
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE users SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $user_id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['update_upload_status'])) {
    $user_id = intval($_POST['user_id']);
    $upload_status = $_POST['upload_status'];
    $stmt = $conn->prepare("UPDATE users SET upload_status=? WHERE id=?");
    $stmt->bind_param("si", $upload_status, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Delete operation
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id = $id");
}

$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Users</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">All Users</h2>
    <table class="table table-bordered table-striped table-responsive-sm">
        <thead class="thead-dark">
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Status</th>
            <th>Upload Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['role']) ?></td>

                <!-- Main Status -->
                <td>
                    <form method="POST" class="form-inline">
                        <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                        <select name="status" class="form-control form-control-sm mr-2"
                                onchange="this.form.submit()">
                            <option value="active"   <?= $row['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="blocked"  <?= $row['status'] === 'blocked' ? 'selected' : '' ?>>Blocked</option>
                            <option value="pending"  <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        </select>
                        <input type="hidden" name="update_status" value="1">
                    </form>
                </td>

                <!-- Upload Status -->
                <td>
                    <form method="POST" class="form-inline">
                        <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                        <select name="upload_status" class="form-control form-control-sm mr-2"
                                onchange="this.form.submit()">
                            <option value="active"  <?= $row['upload_status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="blocked" <?= $row['upload_status'] === 'blocked' ? 'selected' : '' ?>>Blocked</option>
                        </select>
                        <input type="hidden" name="update_upload_status" value="1">
                    </form>
                </td>

                <!-- Delete -->
                <td>
                    <a href="?delete=<?= $row['id'] ?>"
                       onclick="return confirm('Delete user?')"
                       class="btn btn-sm btn-danger mt-1">
                        Delete
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
