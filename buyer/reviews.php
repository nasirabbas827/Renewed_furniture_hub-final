<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$buyer_id = $_SESSION["id"];

$furniture_id = isset($_GET['furniture_id']) ? intval($_GET['furniture_id']) : 0;
$seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0;

if ($furniture_id <= 0 || $seller_id <= 0) {
    die("Invalid furniture or seller reference.");
}

$message = "";

// Handle Review Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = intval($_POST['rating']);
    $review_text = trim($_POST['review_text']);

    if ($rating < 1 || $rating > 5) {
        $message = "Rating must be between 1 and 5.";
    } else if (empty($review_text)) {
        $message = "Review text cannot be empty.";
    } else {
        // Check if user already reviewed
        $check_sql = "SELECT * FROM reviews WHERE furniture_id = ? AND buyer_id = ?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("ii", $furniture_id, $buyer_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 20) {
            $message = "You have already reviewed this furniture.";
        } else {
            $insert_sql = "INSERT INTO reviews (furniture_id, buyer_id, seller_id, rating, review_text) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("iiiis", $furniture_id, $buyer_id, $seller_id, $rating, $review_text);
            if ($stmt->execute()) {
                $message = "Review submitted successfully.";
            } else {
                $message = "Error submitting review.";
            }
        }
    }
}

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
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container mt-5">
    <h2 class="mb-4">Reviews & Ratings</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Review Form -->
    <div class="card mb-4">
        <div class="card-header">Write a Review</div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Rating (1 to 5):</label>
                    <select name="rating" class="form-control" required>
                        <option value="">Select Rating</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Review:</label>
                    <textarea name="review_text" class="form-control" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Review</button>
            </form>
        </div>
    </div>

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
