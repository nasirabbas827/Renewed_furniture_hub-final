<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$user_id = $_SESSION["id"];
$msg = "";

if (!isset($_GET['id'])) {
    die("Image ID missing.");
}
$image_id = intval($_GET['id']);

$sql = "SELECT fi.image_id, fi.image_path, fi.furniture_id, f.title
        FROM furniture_images fi
        JOIN furniture f ON fi.furniture_id = f.furniture_id
        WHERE fi.image_id=? AND f.id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $image_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$image = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$image) {
    die("Image not found or unauthorized.");
}

if (isset($_POST['update_image'])) {
    if (!empty($_FILES['new_image']['name'])) {
        $old_path = "../uploads/" . $image['image_path'];
        if (file_exists($old_path)) {
            unlink($old_path);
        }
        
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = time() . "_" . basename($_FILES['new_image']['name']);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES['new_image']['tmp_name'], $target_file)) {
            $upd = "UPDATE furniture_images SET image_path=? WHERE image_id=?";
            $stmt2 = mysqli_prepare($conn, $upd);
            mysqli_stmt_bind_param($stmt2, "si", $file_name, $image_id);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);
            $msg = '<div class="alert alert-success text-center">Image updated successfully.</div>';
        } else {
            $msg = '<div class="alert alert-danger text-center">Failed to upload new image.</div>';
        }
    } else {
        $msg = '<div class="alert alert-warning text-center">Please select a new image.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Furniture Image</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <?php echo $msg; ?>
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
            <h4 class="mb-3 text-center">Edit Image for: <?php echo htmlspecialchars($image['title']); ?></h4>
            <img src="../uploads/<?php echo $image['image_path']; ?>" width="200" class="d-block mx-auto mb-3">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Choose New Image</label>
                    <input type="file" name="new_image" class="form-control-file" required>
                </div>
                <div class="text-center">
                    <button type="submit" name="update_image" class="btn btn-primary">Update Image</button>
                    <a href="view_furniture_images.php" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
