<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$msg = "";
$user_id = $_SESSION["id"];

if (!isset($_GET['id'])) {
    die("Furniture ID missing.");
}
$furniture_id = intval($_GET['id']);

$sql = "SELECT * FROM furniture WHERE furniture_id=? AND id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $furniture_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$furniture = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$furniture) {
    die("Furniture not found or unauthorized.");
}

if (isset($_POST['update_furniture'])) {
    $category_id = intval($_POST['category_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $condition = $_POST['condition'];
    $location = trim($_POST['location']);

    // FIXED: backticks added for `condition`
    $sql = "UPDATE furniture 
            SET category_id=?, title=?, description=?, price=?, `condition`=?, location=? 
            WHERE furniture_id=? AND id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issdssii", $category_id, $title, $description, $price, $condition, $location, $furniture_id, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $msg = '<div class="alert alert-success text-center">Furniture updated successfully.</div>';
    } else {
        $msg = '<div class="alert alert-danger text-center">Error updating furniture.</div>';
    }
    mysqli_stmt_close($stmt);
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Furniture</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <?php echo $msg; ?>
    <div class="card mx-auto shadow" style="max-width: 700px;">
        <div class="card-body">
            <h4 class="text-center mb-4">Edit Furniture</h4>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>
                            <?php while ($row = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?php echo $row['category_id']; ?>" 
                                    <?php if ($row['category_id'] == $furniture['category_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Condition</label>
                        <select name="condition" class="form-control" required>
                            <?php
                            $conditions = ['new', 'like_new', 'good', 'fair', 'poor'];
                            foreach ($conditions as $cond) {
                                $sel = ($furniture['condition'] == $cond) ? 'selected' : '';
                                echo "<option value='$cond' $sel>" . ucfirst($cond) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($furniture['title']); ?>" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($furniture['description']); ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Price (PKR)</label>
                        <input type="number" step="0.01" name="price" value="<?php echo $furniture['price']; ?>" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Location</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($furniture['location']); ?>" class="form-control" required>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" name="update_furniture" class="btn btn-primary px-4">Update Furniture</button>
                    <a href="view_furniture.php" class="btn btn-secondary px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
