<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$seller_id = $_SESSION["id"];
$msg = "";

// Validate Order ID
if (!isset($_GET['order_id'])) {
    echo "<script>alert('Order ID is missing'); window.location.href='seller_orders.php';</script>";
    exit;
}

$order_id = intval($_GET['order_id']);

// Fetch Order Details
$sql = "
    SELECT 
        o.*, 
        b.full_name AS buyer_name,
        b.email AS buyer_email,
        b.phone AS buyer_phone,
        f.title AS furniture_title,
        f.price AS furniture_price,
        f.location AS furniture_location
    FROM orders o
    JOIN users b ON o.buyer_id = b.id
    JOIN furniture f ON o.furniture_id = f.furniture_id
    WHERE o.order_id = ? AND o.seller_id = ?
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $seller_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($order_result) === 0) {
    echo "<script>alert('Order not found or unauthorized'); window.location.href='seller_orders.php';</script>";
    exit;
}

$order = mysqli_fetch_assoc($order_result);
mysqli_stmt_close($stmt);

// Fetch Payment Details (if any)
$pay_sql = "SELECT * FROM payments WHERE order_id = ?";
$stmt_pay = mysqli_prepare($conn, $pay_sql);
mysqli_stmt_bind_param($stmt_pay, "i", $order_id);
mysqli_stmt_execute($stmt_pay);
$payment_result = mysqli_stmt_get_result($stmt_pay);
$payment = mysqli_fetch_assoc($payment_result);
mysqli_stmt_close($stmt_pay);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Details</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3 class="text-center mb-4">Order Details (ID: <?php echo $order_id; ?>)</h3>

    <div class="card mb-4">
        <div class="card-header bg-dark text-white">Furniture Details</div>
        <div class="card-body">
            <p><strong>Title:</strong> <?php echo htmlspecialchars($order['furniture_title']); ?></p>
            <p><strong>Price:</strong> PKR <?php echo htmlspecialchars($order['furniture_price']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($order['furniture_location']); ?></p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-dark text-white">Buyer Details</div>
        <div class="card-body">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['buyer_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['buyer_email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['buyer_phone']); ?></p>
            <p><strong>Delivery Address:</strong> <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-dark text-white">Order Status & Payment</div>
        <div class="card-body">
            <p><strong>Order Status:</strong> <?php echo ucfirst($order['order_status']); ?></p>
            <p><strong>Payment Method:</strong> <?php echo strtoupper($order['payment_method']); ?></p>
            <p><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
            <p><strong>Order Date:</strong> <?php echo $order['order_date']; ?></p>
        </div>
    </div>

    <?php if ($payment): ?>
        <div class="card mb-4">
            <div class="card-header bg-success text-white">Payment Details</div>
            <div class="card-body">
                <p><strong>Amount:</strong> PKR <?php echo $payment['amount']; ?></p>
                <p><strong>Payment Date:</strong> <?php echo $payment['payment_date']; ?></p>
                <p><strong>Receipt:</strong></p>
                <?php if (!empty($payment['receipt'])): ?>
                    <a href="../uploads/<?php echo htmlspecialchars($payment['receipt']); ?>" target="_blank">View Receipt</a>
                <?php else: ?>
                    No Receipt
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">
            No payment uploaded yet.
        </div>
    <?php endif; ?>

    <div class="text-center">
        <a href="seller_orders.php" class="btn btn-secondary">Back to Orders</a>
    </div>
</div>

</body>
</html>
