<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$buyer_id = $_SESSION["id"];
$msg = "";

/* DELETE ORDER */
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    $check_sql = "
        SELECT * FROM orders
        WHERE order_id = ? AND buyer_id = ? 
        AND order_status = 'pending' 
        AND payment_status = 'pending'
    ";
    $stmt_check = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt_check, "ii", $delete_id, $buyer_id);
    mysqli_stmt_execute($stmt_check);
    $check_result = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($check_result) > 0) {
        $delete_sql = "DELETE FROM orders WHERE order_id = ?";
        $stmt_delete = mysqli_prepare($conn, $delete_sql);
        mysqli_stmt_bind_param($stmt_delete, "i", $delete_id);
        if (mysqli_stmt_execute($stmt_delete)) {
            $msg = "<div class='alert alert-success'>Order deleted successfully.</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Failed to delete order.</div>";
        }
        mysqli_stmt_close($stmt_delete);
    } else {
        $msg = "<div class='alert alert-warning'>You cannot delete this order.</div>";
    }

    mysqli_stmt_close($stmt_check);
}

/* FETCH ORDERS */
$sql = "
    SELECT o.*, 
           f.title AS furniture_title,
           f.price AS furniture_price,
           u.full_name AS seller_name
    FROM orders o
    JOIN furniture f ON o.furniture_id = f.furniture_id
    JOIN users u ON o.seller_id = u.id
    WHERE o.buyer_id = ?
    ORDER BY o.order_date DESC
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $buyer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">My Orders</h2>

    <?php if ($msg) echo $msg; ?>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table class="table  table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Furniture</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Payment Status</th>
                    <th>Order Status</th>
                    <th>Order Date</th>
                    <th>Delivery Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $counter = 1; ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($row['furniture_title']); ?></td>
                        <td><?php echo number_format($row['furniture_price'], 2); ?></td>
                        <td><?php echo strtoupper($row['payment_method']); ?></td>
                        <td><?php echo ucfirst($row['payment_status']); ?></td>
                        <td><?php echo ucfirst($row['order_status']); ?></td>
                        <td><?php echo $row['order_date']; ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['delivery_address'])); ?></td>
                        <td>
                            <?php if ($row['order_status'] === 'pending' && $row['payment_status'] === 'pending'): ?>
                                <a href="my_orders.php?delete_id=<?php echo $row['order_id']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure you want to delete this order?');">
                                   Delete
                                </a>
                            <?php endif; ?>

                            <?php if ($row['payment_method'] === 'online' && $row['payment_status'] === 'pending'): ?>
                                <a href="pay_now.php?order_id=<?php echo $row['order_id']; ?>" 
                                   class="m-2 btn btn-success btn-sm">Pay Now</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info text-center">
            You have not placed any orders yet.
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
