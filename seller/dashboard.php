<?php
session_start();
include('config.php'); 

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Fetch stats
$total_furniture = $conn->query("SELECT COUNT(*) AS total FROM furniture WHERE id = $user_id")->fetch_assoc()['total'];
$total_orders = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE seller_id = $user_id")->fetch_assoc()['total'];
$total_reviews = $conn->query("SELECT COUNT(*) AS total FROM reviews WHERE seller_id = $user_id")->fetch_assoc()['total'];
$total_complaints = $conn->query("SELECT COUNT(*) AS total FROM complaints WHERE user_id = $user_id")->fetch_assoc()['total'];
$completed_payments = $conn->query("SELECT COUNT(*) AS total FROM payments p JOIN orders o ON p.order_id = o.order_id WHERE o.seller_id = $user_id")->fetch_assoc()['total'];

// Fetch data for graphs
$orders_by_status = $conn->query("SELECT order_status, COUNT(*) as count FROM orders WHERE seller_id = $user_id GROUP BY order_status");
$orders_status_data = [];
$orders_status_labels = [];
while ($row = $orders_by_status->fetch_assoc()) {
    $orders_status_labels[] = ucfirst($row['order_status']);
    $orders_status_data[] = $row['count'];
}

$reviews_by_rating = $conn->query("SELECT rating, COUNT(*) as count FROM reviews WHERE seller_id = $user_id GROUP BY rating");
$reviews_rating_data = array_fill(1, 5, 0);
while ($row = $reviews_by_rating->fetch_assoc()) {
    $reviews_rating_data[$row['rating']] = $row['count'];
}

// Recent actions
$recent_orders = $conn->query("
    SELECT o.order_id, o.order_status, o.order_date, f.title 
    FROM orders o
    JOIN furniture f ON o.furniture_id = f.furniture_id
    WHERE o.seller_id = $user_id
    ORDER BY o.order_date DESC
    LIMIT 5
");

$recent_reviews = $conn->query("
    SELECT r.rating, r.review_text, r.created_at, u.full_name
    FROM reviews r
    JOIN users u ON r.buyer_id = u.id
    WHERE r.seller_id = $user_id
    ORDER BY r.created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .card-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); }
        .recent-actions { max-height: 300px; overflow-y: auto; }
        .chart-container { position: relative; height: 300px; }
        .gradient-bg { background: linear-gradient(135deg, #6B7280, #1F2937); }
    </style>
</head>
<body class="bg-light font-sans">
<?php include('navbar.php'); ?>

<div class="container py-5">
    <!-- Welcome Card -->
    <div class="card text-white bg-primary mb-4 shadow-lg">
        <div class="card-body text-center">
            <h1 class="card-title h3 mb-2">Welcome, <?php echo htmlspecialchars($_SESSION["name"] ?? 'User'); ?>!</h1>
            <p class="card-text">Manage your furniture listings, orders, and more from your Seller Dashboard.</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-hover shadow">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-couch text-primary fa-2x mr-3"></i>
                    <div>
                        <h5 class="card-title text-muted small font-weight-bold">Total Furniture</h5>
                        <h3 class="card-text font-weight-bold"><?php echo $total_furniture; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-hover shadow">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-shopping-cart text-success fa-2x mr-3"></i>
                    <div>
                        <h5 class="card-title text-muted small font-weight-bold">Total Orders</h5>
                        <h3 class="card-text font-weight-bold"><?php echo $total_orders; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-hover shadow">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-star text-warning fa-2x mr-3"></i>
                    <div>
                        <h5 class="card-title text-muted small font-weight-bold">Total Reviews</h5>
                        <h3 class="card-text font-weight-bold"><?php echo $total_reviews; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-hover shadow">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-exclamation-circle text-danger fa-2x mr-3"></i>
                    <div>
                        <h5 class="card-title text-muted small font-weight-bold">Total Complaints</h5>
                        <h3 class="card-text font-weight-bold"><?php echo $total_complaints; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-hover shadow">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-money-check-alt text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="card-title text-muted small font-weight-bold">Completed Payments</h5>
                        <h3 class="card-text font-weight-bold"><?php echo $completed_payments; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphs -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">Orders by Status</h5>
                    <div class="chart-container">
                        <canvas id="ordersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">Reviews by Rating</h5>
                    <div class="chart-container">
                        <canvas id="reviewsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Actions -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">Recent Orders</h5>
                    <div class="recent-actions">
                        <?php if ($recent_orders->num_rows > 0): ?>
                            <ul class="list-unstyled">
                                <?php while ($row = $recent_orders->fetch_assoc()): ?>
                                    <li class="border-left border-primary pl-3 mb-3">
                                        <p class="font-weight-bold mb-1">Order #<?php echo $row['order_id']; ?></p>
                                        <p class="small text-muted mb-1"><?php echo htmlspecialchars($row['title']); ?></p>
                                        <p class="small text-muted">Status: <span class="font-weight-medium"><?php echo ucfirst($row['order_status']); ?></span> | <?php echo date('M d, Y H:i', strtotime($row['order_date'])); ?></p>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No recent orders.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">Recent Reviews</h5>
                    <div class="recent-actions">
                        <?php if ($recent_reviews->num_rows > 0): ?>
                            <ul class="list-unstyled">
                                <?php while ($row = $recent_reviews->fetch_assoc()): ?>
                                    <li class="border-left border-warning pl-3 mb-3">
                                        <p class="font-weight-bold mb-1"><?php echo htmlspecialchars($row['full_name']); ?></p>
                                        <p class="small text-muted mb-1">Rating: <?php echo str_repeat('★', $row['rating']) . str_repeat('☆', 5 - $row['rating']); ?></p>
                                        <p class="small text-muted mb-1"><?php echo htmlspecialchars($row['review_text'] ?? 'No comment'); ?></p>
                                        <p class="small text-muted"><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></p>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No recent reviews.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Orders by Status Chart
    const ordersChart = document.getElementById('ordersChart').getContext('2d');
    new Chart(ordersChart, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($orders_status_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($orders_status_data); ?>,
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 12 } } }
            }
        }
    });

    // Reviews by Rating Chart
    const reviewsChart = document.getElementById('reviewsChart').getContext('2d');
    new Chart(reviewsChart, {
        type: 'pie',
        data: {
            labels: ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
            datasets: [{
                label: 'Reviews',
                data: <?php echo json_encode(array_values($reviews_rating_data)); ?>,
                backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#28a745', '#007bff'],
                borderColor: '#000000ff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 12 } } }
            }
        }
    });
</script>
</body>
</html>