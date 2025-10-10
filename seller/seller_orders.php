<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$seller_id = $_SESSION["id"];
$msg = "";

// Update Order Status
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['order_status'];

    $update_sql = "UPDATE orders SET order_status = ? WHERE order_id = ? AND seller_id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "sii", $new_status, $order_id, $seller_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $msg = '<div class="alert alert-success text-center">Order status updated successfully.</div>';
}

// Date filter
$where = "";
$from_date = $to_date = "";
if (isset($_GET['from_date']) && isset($_GET['to_date']) && $_GET['from_date'] != "" && $_GET['to_date'] != "") {
    $from_date = $_GET['from_date'];
    $to_date = $_GET['to_date'];
    $where = "AND DATE(o.order_date) BETWEEN '$from_date' AND '$to_date'";
}

// Fetch Orders for Seller
$sql = "
    SELECT o.*, 
           u.full_name AS buyer_name, 
           u.email AS buyer_email, 
           u.phone AS buyer_phone,
           f.title AS furniture_title
    FROM orders o
    JOIN users u ON o.buyer_id = u.id
    JOIN furniture f ON o.furniture_id = f.furniture_id
    WHERE o.seller_id = ? $where
    ORDER BY o.order_id DESC
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $seller_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Export CSV
if (isset($_GET['export_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="seller_orders.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order ID', 'Furniture', 'Buyer', 'Buyer Email', 'Buyer Phone', 'Delivery Address', 'Payment Method', 'Payment Status', 'Order Status', 'Order Date']);

    $res = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($res, "i", $seller_id);
    mysqli_stmt_execute($res);
    $csv_result = mysqli_stmt_get_result($res);

    while ($row = mysqli_fetch_assoc($csv_result)) {
        fputcsv($output, [
            $row['order_id'],
            $row['furniture_title'],
            $row['buyer_name'],
            $row['buyer_email'],
            $row['buyer_phone'],
            $row['delivery_address'],
            $row['payment_method'],
            $row['payment_status'],
            $row['order_status'],
            $row['order_date']
        ]);
    }

    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Orders (Seller)</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3 class="text-center mb-4">Orders for Your Furniture</h3>
    <?php echo $msg; ?>

    <form method="GET" class="form-inline mb-3">
        <label class="mr-2">From:</label>
        <input type="date" name="from_date" class="form-control mr-3" value="<?= htmlspecialchars($from_date) ?>">
        <label class="mr-2">To:</label>
        <input type="date" name="to_date" class="form-control mr-3" value="<?= htmlspecialchars($to_date) ?>">
        <button type="submit" class="btn btn-primary mr-2">Search</button>
        <a href="seller_orders.php?export_csv=1<?= $from_date && $to_date ? "&from_date=$from_date&to_date=$to_date" : '' ?>" class="btn btn-success">Export CSV</a>
        <a href="seller_orders.php" class="btn btn-secondary ml-2">Reset</a>
    </form>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Furniture</th>
                    <th>Buyer</th>
                    <th>Delivery Address</th>
                    <th>Payment</th>
                    <th>Order Status</th>
                    <th>View</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['order_id'] ?></td>
                    <td><?= htmlspecialchars($row['furniture_title']) ?></td>
                    <td>
                        <?= htmlspecialchars($row['buyer_name']) ?><br>
                        <small><?= htmlspecialchars($row['buyer_email']) ?></small><br>
                        <small><?= htmlspecialchars($row['buyer_phone']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($row['delivery_address']) ?></td>
                    <td>
                        <?= strtoupper($row['payment_method']) ?><br>
                        <small><?= ucfirst($row['payment_status']) ?></small>
                    </td>
                    <td>
                        <form method="POST" class="form-inline">
                            <select name="order_status" class="m-2 form-control form-control-sm mr-2">
                                <option value="pending" <?= $row['order_status']=='pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="processing" <?= $row['order_status']=='processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="shipped" <?= $row['order_status']=='shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="completed" <?= $row['order_status']=='completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $row['order_status']=='cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                            <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
                        </form>
                    </td>
                    <td>
                        <a href="order_details.php?order_id=<?= $row['order_id'] ?>" class="btn btn-info btn-sm">View</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning text-center">No orders found.</div>
    <?php endif; ?>
</div>
</body>
</html>
