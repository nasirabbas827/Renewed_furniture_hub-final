<?php
session_start();
include('config.php');

if (!isset($_SESSION['id'])) {
    header("location: ../index.php");
    exit;
}

$user_id = $_SESSION['id'];
$msg = "";

// Delete complaint if status is pending
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    $check_sql = "SELECT * FROM complaints WHERE complaint_id = ? AND user_id = ? AND status = 'pending'";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "ii", $delete_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $del_sql = "DELETE FROM complaints WHERE complaint_id = ?";
        $stmt_del = mysqli_prepare($conn, $del_sql);
        mysqli_stmt_bind_param($stmt_del, "i", $delete_id);
        mysqli_stmt_execute($stmt_del);
        $msg = "Complaint deleted successfully.";
    } else {
        $msg = "Cannot delete this complaint.";
    }
}

// Add complaint
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $description = trim($_POST['description']);

    if (!empty($description)) {
        $insert_sql = "INSERT INTO complaints (user_id, description) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, "is", $user_id, $description);
        mysqli_stmt_execute($stmt);
        $msg = "Complaint submitted successfully.";
    } else {
        $msg = "Description cannot be empty.";
    }
}

// Fetch complaints
$fetch_sql = "SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $fetch_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$complaints = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Complaints</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
        <div class="card mx-auto shadow" style="max-width: 700px;">
        <div class="card-body">
    <h3 class="text-center mb-4">Submit a Complaint</h3>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?php echo $msg; ?></div>
    <?php endif; ?>

    <form action="" method="POST" class="mb-5">
        <div class="form-group">
            <label>Complaint Description:</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Complaint</button>
    </form>
</div>
</div>

    <h3 class="text-center m-4">My Complaints</h3>

    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Description</th>
                <th>Admin Reply</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($complaints)): ?>
                <tr>
                    <td><?php echo $row['complaint_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td><?php echo $row['admin_reply'] ? htmlspecialchars($row['admin_reply']) : 'No Reply Yet'; ?></td>
                    <td><?php echo ucfirst($row['status']); ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td>
                        <?php if ($row['status'] == 'pending'): ?>
                            <a href="?delete_id=<?php echo $row['complaint_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this complaint?')">Delete</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
