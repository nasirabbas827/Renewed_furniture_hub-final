<?php
session_start();
include('config.php');

if (!isset($_GET['furniture_id']) || empty($_GET['furniture_id'])) {
    header("location: index.php");
    exit;
}

$furniture_id = intval($_GET['furniture_id']);

// Fetch furniture details with seller info
$sql = "
    SELECT f.*, c.category_name, u.full_name AS seller_name, u.email AS seller_email, u.phone AS seller_phone
    FROM furniture f
    JOIN categories c ON f.category_id = c.category_id
    JOIN users u ON f.id = u.id
    WHERE f.furniture_id = ?
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $furniture_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo "<h3 class='text-center mt-5'>Furniture not found!</h3>";
    exit;
}

$furniture = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Fetch images
$images_sql = "SELECT image_path FROM furniture_images WHERE furniture_id = ?";
$stmt2 = mysqli_prepare($conn, $images_sql);
mysqli_stmt_bind_param($stmt2, "i", $furniture_id);
mysqli_stmt_execute($stmt2);
$images_result = mysqli_stmt_get_result($stmt2);
$images = mysqli_fetch_all($images_result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Furniture Details</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="./css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <h2 class="text-center mb-4"><?php echo htmlspecialchars($furniture['title']); ?></h2>

        <!-- Image Slider -->
        <?php if (!empty($images)): ?>
        <div id="furnitureCarousel" class="carousel slide mb-4" data-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($images as $index => $img): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="uploads/<?php echo $img['image_path']; ?>" class="d-block w-100" style="height: 400px; object-fit: cover;">
                    </div>
                <?php endforeach; ?>
            </div>
            <a class="carousel-control-prev" href="#furnitureCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </a>
            <a class="carousel-control-next" href="#furnitureCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon"></span>
            </a>
        </div>
        <?php else: ?>
            <p class="text-center">No images available.</p>
        <?php endif; ?>

        <!-- Furniture Details -->
        <h4 class="mb-3">Furniture Information</h4>
        <table class="table table-bordered">
            <tr>
                <th>Category</th>
                <td><?php echo htmlspecialchars($furniture['category_name']); ?></td>
            </tr>
            <tr>
                <th>Description</th>
                <td><?php echo nl2br(htmlspecialchars($furniture['description'])); ?></td>
            </tr>
            <tr>
                <th>Price</th>
                <td>PKR <?php echo $furniture['price']; ?></td>
            </tr>
            <tr>
                <th>Condition</th>
                <td><?php echo ucfirst($furniture['condition']); ?></td>
            </tr>
            <tr>
                <th>Location</th>
                <td><?php echo htmlspecialchars($furniture['location']); ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?php echo ucfirst($furniture['status']); ?></td>
            </tr>
        </table>

        <!-- Seller Information -->
        <h4 class="mt-4 mb-3">Seller Information</h4>
        <table class="table table-bordered">
            <tr>
                <th>Name</th>
                <td><?php echo htmlspecialchars($furniture['seller_name']); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo htmlspecialchars($furniture['seller_email']); ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?php echo htmlspecialchars($furniture['seller_phone']); ?></td>
            </tr>
        </table>

        <!-- Order Button -->
        <div class="text-center mt-4">
            <?php if ($furniture['status'] === 'available'): ?>
                <a href="login.php" class="btn btn-success px-4">Order Now</a>
            <?php else: ?>
                <button class="btn btn-secondary px-4" disabled>Not Available</button>
            <?php endif; ?>
            <a href="index.php" class="btn btn-primary px-4">Back</a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
