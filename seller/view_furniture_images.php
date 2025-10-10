<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$user_id = $_SESSION["id"];
$msg = "";

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    $img_sql = "SELECT fi.image_id, fi.image_path 
                FROM furniture_images fi
                JOIN furniture f ON fi.furniture_id = f.furniture_id
                WHERE fi.image_id=? AND f.id=?";
    $stmt = mysqli_prepare($conn, $img_sql);
    mysqli_stmt_bind_param($stmt, "ii", $delete_id, $user_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $img = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if ($img) {
        $file_path = "../uploads/" . $img['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $del = "DELETE FROM furniture_images WHERE image_id=?";
        $stmt2 = mysqli_prepare($conn, $del);
        mysqli_stmt_bind_param($stmt2, "i", $delete_id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
        $msg = '<div class="alert alert-success text-center">Image deleted.</div>';
    } else {
        $msg = '<div class="alert alert-danger text-center">Image not found or unauthorized.</div>';
    }
}

$sql = "SELECT fi.image_id, fi.image_path, f.title
        FROM furniture_images fi
        JOIN furniture f ON fi.furniture_id = f.furniture_id
        WHERE f.id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$images = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Furniture Images</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <?php echo $msg; ?>
    <h3 class="mb-3 text-center">My Furniture Images</h3>
    <a href="add_furniture_image.php" class="btn btn-primary float-right m-3">Add New Image</a>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Furniture</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($images)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><img src="../uploads/<?php echo $row['image_path']; ?>" width="80"></td>
                <td>
                    <a href="edit_furniture_image.php?id=<?php echo $row['image_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a onclick="return confirm('Delete this image?');"
                       href="?delete_id=<?php echo $row['image_id']; ?>"
                       class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
