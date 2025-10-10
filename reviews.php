<?php
session_start();
include('config.php');



$furniture_id = isset($_GET['furniture_id']) ? intval($_GET['furniture_id']) : 0;
$seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0;

if ($furniture_id <= 0 || $seller_id <= 0) {
    die("Invalid furniture or seller reference.");
}

$message = "";

// Fetch all reviews for this furniture
$reviews_sql = "
    SELECT r.rating, r.review_text, r.created_at, u.full_name AS buyer_name
    FROM reviews r
    JOIN users u ON r.buyer_id = u.id
    WHERE r.furniture_id = ?
    ORDER BY r.created_at DESC
";
$stmt_reviews = $conn->prepare($reviews_sql);
$stmt_reviews->bind_param("i", $furniture_id);
$stmt_reviews->execute();
$reviews_result = $stmt_reviews->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reviews & Ratings</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container mt-5">
    <h2 class="mb-4">Reviews & Ratings</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>


    <!-- Display Existing Reviews -->
    <h4>Customer Reviews</h4>
    <div class="row">
        <?php if ($reviews_result->num_rows > 0): ?>
            <?php while ($row = $reviews_result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['buyer_name']); ?></h5>
                            <p class="mb-2">
                                <?php
                                $stars = intval($row['rating']);
                                for ($s = 1; $s <= 5; $s++) {
                                    echo $s <= $stars ? "★" : "☆";
                                }
                                ?>
                            </p>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($row['review_text'])); ?></p>
                        </div>
                        <div class="card-footer text-muted">
                            Posted on: <?php echo $row['created_at']; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <p>No reviews available yet.</p>
            </div>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
