<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$buyer_id = $_SESSION["id"];

if (!isset($_GET['furniture_id']) || empty($_GET['furniture_id'])) {
    header("location: dashboard.php");
    exit;
}

$furniture_id = intval($_GET['furniture_id']);

// Fetch furniture with seller info
$sql = "
    SELECT f.*, u.id AS seller_id, u.full_name AS seller_name, u.email AS seller_email, u.phone AS seller_phone
    FROM furniture f
    JOIN users u ON f.id = u.id
    WHERE f.furniture_id = ?
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $furniture_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo "<h3 class='text-center mt-5'>Invalid Furniture!</h3>";
    exit;
}

$furniture = mysqli_fetch_assoc($result);
$seller_id = $furniture['seller_id'];
$title = $furniture['title'];
mysqli_stmt_close($stmt);

$msg = "";

// Handle Order Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $delivery_address = trim($_POST['delivery_address']);
    $payment_method = trim($_POST['payment_method']);

    if ($delivery_address === "" || $payment_method === "") {
        $msg = "<div class='alert alert-danger'>All fields are required!</div>";
    } else {
        $insert_sql = "
            INSERT INTO orders (buyer_id, furniture_id, seller_id, delivery_address, payment_method)
            VALUES (?, ?, ?, ?, ?)
        ";
        $stmt2 = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt2, "iiiss", $buyer_id, $furniture_id, $seller_id, $delivery_address, $payment_method);

        if (mysqli_stmt_execute($stmt2)) {
            header("Location: my_orders.php?order_success=1");
            exit;
        } else {
            $msg = "<div class='alert alert-danger'>Failed to place order!</div>";
        }

        mysqli_stmt_close($stmt2);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Furniture</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="row">
        <!-- Left Side: Furniture Details -->
        <div class="col-md-6">
            <div class="card shadow p-4 mb-4">
                <h3><?php echo htmlspecialchars($furniture['title']); ?></h3>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($furniture['category_id']); ?></p>
                <p><strong>Price:</strong> PKR <?php echo $furniture['price']; ?></p>
                <p><strong>Condition:</strong> <?php echo ucfirst($furniture['condition']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($furniture['location']); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($furniture['status']); ?></p>
                <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($furniture['description'])); ?></p>

                <h5 class="mt-4">Seller Information</h5>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($furniture['seller_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($furniture['seller_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($furniture['seller_phone']); ?></p>
            </div>
        </div>

        <!-- Right Side: Order Form -->
        <div class="col-md-6">
            <div class="card shadow p-4">
                <h4 class="mb-4">Order Form</h4>
                <?php if ($msg) echo $msg; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="delivery_address">Delivery Address</label>
                        <textarea name="delivery_address" id="delivery_address" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                            <option value="">Select Payment Method</option>
                            <option value="cod">Cash on Delivery (COD)</option>
                            <option value="online">Online</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Place Order</button>
                    <a href="view_furniture_details.php?furniture_id=<?php echo $furniture_id; ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
