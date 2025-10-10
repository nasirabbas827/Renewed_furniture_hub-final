<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$user_id = $_SESSION["id"];
$msg = "";

// Get furniture belonging to this user
$furniture_sql = "SELECT furniture_id, title FROM furniture WHERE id=?";
$stmt = mysqli_prepare($conn, $furniture_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);  
$furniture_result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);


if (isset($_POST['upload_image'])) {
    $furniture_id = intval($_POST['furniture_id']);
    
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = time() . "_" . basename($_FILES['image']['name']);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $sql = "INSERT INTO furniture_images (furniture_id, image_path) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "is", $furniture_id, $file_name);
            
            if (mysqli_stmt_execute($stmt)) {
                $msg = '<div class="alert alert-success text-center">Image uploaded successfully.</div>';
            } else {
                $msg = '<div class="alert alert-danger text-center">Database error.</div>';
            }
            mysqli_stmt_close($stmt);
        } else {
            $msg = '<div class="alert alert-danger text-center">Failed to upload file.</div>';
        }
    } else {
        $msg = '<div class="alert alert-warning text-center">Please select an image.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Furniture Image</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <?php echo $msg; ?>
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
            <h4 class="text-center mb-3">Add Furniture Image</h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Select Furniture</label>
                    <select name="furniture_id" class="form-control" required>
                        <option value="">Select...</option>
                        <?php while ($row = mysqli_fetch_assoc($furniture_result)): ?>
                            <option value="<?php echo $row['furniture_id']; ?>">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Choose Image</label>
                    <input type="file" name="image" class="form-control-file" required>
                </div>
                <div class="text-center">
                    <button type="submit" name="upload_image" class="btn btn-primary">Upload</button>
                    <a href="view_furniture_images.php" class="btn btn-secondary">View Images</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
