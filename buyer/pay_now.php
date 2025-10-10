<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$buyer_id = $_SESSION["id"];

if (!isset($_GET['order_id'])) {
    die("Order not specified.");
}

$order_id = intval($_GET['order_id']);

// Fetch order info with furniture price
$sql = "
    SELECT o.*, f.title AS furniture_title, f.price AS furniture_price
    FROM orders o
    JOIN furniture f ON o.furniture_id = f.furniture_id
    WHERE o.order_id = ? AND o.buyer_id = ? 
      AND o.order_status = 'pending' 
      AND o.payment_status = 'pending' 
      AND o.payment_method = 'online'
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $buyer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    die("Invalid or unauthorized order access.");
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
        $uploads_dir = '../uploads';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES['receipt']['name']);
        $target_path = $uploads_dir . "/" . $filename;

        if (move_uploaded_file($_FILES['receipt']['tmp_name'], $target_path)) {

            $amount = $order['furniture_price']; 
            $insert_sql = "
                INSERT INTO payments (order_id, buyer_id, amount, receipt)
                VALUES (?, ?, ?, ?)
            ";

            $stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($stmt, "iids", $order_id, $buyer_id, $amount, $filename);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $update_order_sql = "
                UPDATE orders
                SET payment_status = 'completed'
                WHERE order_id = ?
            ";

            $stmt = mysqli_prepare($conn, $update_order_sql);
            mysqli_stmt_bind_param($stmt, "i", $order_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $msg = "Payment receipt uploaded successfully.";
        } else {
            $msg = "Failed to upload receipt.";
        }
    } else {
        $msg = "Please upload a valid receipt file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pay Now</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h2 class="text-center mb-4">Pay Now - <?php echo htmlspecialchars($order['furniture_title']); ?></h2>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?php echo $msg; ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Total Amount:</label>
            <input type="text" class="form-control" value="<?php echo $order['furniture_price']; ?>" disabled>
        </div>

        <div class="form-group">
            <label>Upload Payment Receipt:</label>
            <input type="file" name="receipt" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Submit Payment</button>
        <a href="my_orders.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
