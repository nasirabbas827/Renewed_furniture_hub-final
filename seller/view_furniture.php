<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$user_id = $_SESSION["id"];
$msg = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $sql = "DELETE FROM furniture WHERE furniture_id=? AND id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $delete_id, $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $msg = '<div class="alert alert-success text-center">Furniture deleted successfully.</div>';
    } else {
        $msg = '<div class="alert alert-danger text-center">Error deleting furniture.</div>';
    }
    mysqli_stmt_close($stmt);
}

$result = mysqli_query($conn, "SELECT f.*, c.category_name FROM furniture f JOIN categories c ON f.category_id = c.category_id WHERE f.id = $user_id ORDER BY f.furniture_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Furniture Listings</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <?php echo $msg; ?>
    <h4 class="mb-3 text-center">My Furniture Listings</h4>
    <div class="text-right mb-3">
        <a href="add_furniture.php" class="btn btn-success">Add New Furniture</a>
    </div>

    <table class="table table-bordered table-striped text-center">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Category</th>
                <th>Title</th>
                <th>Price</th>
                <th>Condition</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $row['furniture_id']; ?></td>
                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo $row['price']; ?></td>
                <td><?php echo ucfirst($row['condition']); ?></td>
                <td><?php echo ucfirst($row['status']); ?></td>
                <td><?php echo $row['created_at']; ?></td>
                <td>
                    <a href="edit_furniture.php?id=<?php echo $row['furniture_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="?delete=<?php echo $row['furniture_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this furniture?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
