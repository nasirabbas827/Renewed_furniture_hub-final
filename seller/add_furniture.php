<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$msg = "";
$user_id = $_SESSION["id"];

// CHECK USER UPLOAD STATUS
$check_sql = "SELECT upload_status FROM users WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "i", $user_id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_bind_result($check_stmt, $upload_status);
mysqli_stmt_fetch($check_stmt);
mysqli_stmt_close($check_stmt);

// If blocked, stop access
if ($upload_status === 'blocked') {
    echo '<script>alert("Your upload access is blocked. Please contact support."); window.location.href="view_furniture.php";</script>';
    exit;
}

if (isset($_POST['add_furniture'])) {
    $category_id = intval($_POST['category_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $condition = $_POST['condition'];
    $location = trim($_POST['location']);

    $sql = "INSERT INTO furniture (id, category_id, title, description, price, `condition`, location) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iissdss", $user_id, $category_id, $title, $description, $price, $condition, $location);

    if (mysqli_stmt_execute($stmt)) {
        $msg = '<div class="alert alert-success text-center">Furniture added successfully.</div>';
    } else {
        $msg = '<div class="alert alert-danger text-center">Error adding furniture.</div>';
    }

    mysqli_stmt_close($stmt);
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Furniture</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <?php echo $msg; ?>
    <div class="card mx-auto shadow" style="max-width: 700px;">
        <div class="card-body">
            <h4 class="text-center mb-4">Add New Furniture</h4>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php while ($row = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?php echo $row['category_id']; ?>">
                                    <?php echo htmlspecialchars($row['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Condition</label>
                        <select name="condition" class="form-control" required>
                            <option value="new">New</option>
                            <option value="like_new">Like New</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Price (PKR)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Location</label>
                        <input type="text" name="location" class="form-control" required>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" name="add_furniture" class="btn btn-primary px-4">Add Furniture</button>
                    <a href="view_furniture.php" class="btn btn-secondary px-4">View Furniture</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
