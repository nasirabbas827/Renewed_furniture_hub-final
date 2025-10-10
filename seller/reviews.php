<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$seller_id = $_SESSION["id"]; // Logged-in seller

// Fetch all reviews for this seller
$sql = "
    SELECT r.review_id, r.rating, r.review_text, r.created_at,
           f.title AS furniture_title,
           u.full_name AS buyer_name
    FROM reviews r
    JOIN furniture f ON r.furniture_id = f.furniture_id
    JOIN users u ON r.buyer_id = u.id
    WHERE r.seller_id = $seller_id
    ORDER BY r.created_at DESC
";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Reviews</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">Reviews Received</h2>

    <?php if (mysqli_num_rows($result) > 0) { ?>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Furniture</th>
                    <th>Buyer</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                        <td>{$i}</td>
                        <td>{$row['furniture_title']}</td>
                        <td>{$row['buyer_name']}</td>
                        <td>{$row['rating']}</td>
                        <td>" . (!empty($row['review_text']) ? $row['review_text'] : 'No Comment') . "</td>
                        <td>{$row['created_at']}</td>
                    </tr>";
                    $i++;
                }
                ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No reviews found.</p>
    <?php } ?>
</div>
</body>
</html>
