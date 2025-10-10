<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Delete complaint
if (isset($_GET['delete'])) {
    $complaint_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM complaints WHERE complaint_id = $complaint_id");
    header("Location: view_complaints.php?msg=deleted");
    exit;
}

// Update complaint status or admin reply
if (isset($_POST['update_complaint'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $status = $_POST['status'];
    $reply = $_POST['admin_reply'];

    $stmt = $conn->prepare("UPDATE complaints SET status=?, admin_reply=? WHERE complaint_id=?");
    $stmt->bind_param("ssi", $status, $reply, $complaint_id);
    $stmt->execute();
    $stmt->close();

    header("Location: view_complaints.php?msg=updated");
    exit;
}

// Fetch all complaints with user info
$query = "
    SELECT c.*, u.full_name AS user_name, u.email AS user_email
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    ORDER BY c.complaint_id DESC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Complaints - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this complaint?");
        }
    </script>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">All Complaints</h2>

    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Complaint deleted successfully.</div>
        <?php elseif ($_GET['msg'] == 'updated'): ?>
            <div class="alert alert-success">Complaint updated successfully.</div>
        <?php endif; ?>
    <?php endif; ?>

    <table class="table table-bordered table-striped table-responsive-sm">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Description</th>
                <th>Admin Reply</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['complaint_id'] ?></td>
                    <td>
                        <?= htmlspecialchars($row['user_name']) ?><br>
                        <small><?= htmlspecialchars($row['user_email']) ?></small>
                    </td>
                    <td><?= nl2br(htmlspecialchars($row['description'])) ?></td>
                    <td>
                        <form method="POST" class="form-inline">
                            <textarea name="admin_reply" class="m-2 form-control form-control-sm mb-1" rows="2" placeholder="Reply..."><?= htmlspecialchars($row['admin_reply']) ?></textarea>
                            <input type="hidden" name="complaint_id" value="<?= $row['complaint_id'] ?>">
                            <select name="status" class="form-control form-control-sm mr-2">
                                <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="resolved" <?= $row['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                <option value="dismissed" <?= $row['status'] === 'dismissed' ? 'selected' : '' ?>>Dismissed</option>
                            </select>
                            <button type="submit" name="update_complaint" class="btn btn-sm btn-primary">Update</button>
                        </form>
                    </td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
                    <td>
                        <a href="?delete=<?= $row['complaint_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete()">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-center text-muted">No complaints found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
