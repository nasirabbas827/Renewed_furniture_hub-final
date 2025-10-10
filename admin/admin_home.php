<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Fetch stats
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role IN ('buyer', 'seller')")->fetch_assoc()['total'];
$total_furniture = $conn->query("SELECT COUNT(*) AS total FROM furniture")->fetch_assoc()['total'];
$total_orders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'];
$total_complaints = $conn->query("SELECT COUNT(*) AS total FROM complaints")->fetch_assoc()['total'];

// Fetch data for charts
$orders_by_status = $conn->query("SELECT order_status, COUNT(*) as count FROM orders GROUP BY order_status");
$orders_status_data = [];
$orders_status_labels = [];
while ($row = $orders_by_status->fetch_assoc()) {
    $orders_status_labels[] = ucfirst($row['order_status']);
    $orders_status_data[] = $row['count'];
}

$reviews_by_rating = $conn->query("SELECT rating, COUNT(*) as count FROM reviews GROUP BY rating");
$reviews_rating_data = array_fill(1, 5, 0);
while ($row = $reviews_by_rating->fetch_assoc()) {
    $reviews_rating_data[$row['rating']] = $row['count'];
}

$orders_last_7_days = $conn->query("SELECT DATE(order_date) as date, COUNT(*) as count FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(order_date)");
$orders_dates = [];
$orders_counts = [];
while ($row = $orders_last_7_days->fetch_assoc()) {
    $orders_dates[] = date('M d', strtotime($row['date']));
    $orders_counts[] = $row['count'];
}

// Fetch recent activities
$recent_users = $conn->query("SELECT id, full_name, email, role, status, created_at FROM users WHERE role IN ('buyer', 'seller') ORDER BY created_at DESC LIMIT 5");
$recent_furniture = $conn->query("SELECT f.furniture_id, f.title, f.status, c.category_name, u.full_name, f.created_at FROM furniture f JOIN categories c ON f.category_id = c.category_id JOIN users u ON f.id = u.id ORDER BY f.created_at DESC LIMIT 5");
$recent_orders = $conn->query("SELECT o.order_id, o.order_status, o.order_date, f.title, u.full_name AS buyer_name FROM orders o JOIN furniture f ON o.furniture_id = f.furniture_id JOIN users u ON o.buyer_id = u.id ORDER BY o.order_date DESC LIMIT 5");
$recent_complaints = $conn->query("SELECT c.complaint_id, c.description, c.status, c.created_at, u.full_name FROM complaints c JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC LIMIT 5");

// Fetch categories for furniture search
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name ASC");

// Furniture Search Filters
$where = "WHERE 1=1";
if (!empty($_GET['category_id'])) {
    $where .= " AND f.category_id = " . intval($_GET['category_id']);
}
if (!empty($_GET['condition'])) {
    $where .= " AND f.condition = '" . mysqli_real_escape_string($conn, $_GET['condition']) . "'";
}
if (!empty($_GET['status'])) {
    $where .= " AND f.status = '" . mysqli_real_escape_string($conn, $_GET['status']) . "'";
}
if (!empty($_GET['min_price']) && !empty($_GET['max_price'])) {
    $where .= " AND f.price BETWEEN " . floatval($_GET['min_price']) . " AND " . floatval($_GET['max_price']);
}

// Fetch filtered furniture
$furniture_sql = "
    SELECT f.*, c.category_name, u.full_name,
    (SELECT image_path FROM furniture_images WHERE furniture_id = f.furniture_id ORDER BY RAND() LIMIT 1) AS image_path
    FROM furniture f
    JOIN categories c ON f.category_id = c.category_id
    JOIN users u ON f.id = u.id
    $where
    ORDER BY f.created_at DESC
";
$furniture_items = mysqli_query($conn, $furniture_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | FurnitureHub</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --bg-light: #f4f6f9;
            --card-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            --accent: #007bff;
            --highlight: #28a745;
        }
        
        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color), #6610f2);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 123, 255, 0.3);
        }

        .card-stats {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border-radius: 12px;
            border: none;
            box-shadow: var(--card-shadow);
        }
        .card-stats:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        .card-stats .card-header-icon {
            background: linear-gradient(45deg, var(--primary-color), #00c4ff);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            position: absolute;
            top: -20px;
            right: 20px;
            box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.14);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-stats .card-body {
            padding-top: 2rem;
        }
        .card-stats .stats-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #343a40;
            line-height: 1;
        }
        .card-stats .stats-title {
            color: var(--secondary-color);
            text-transform: uppercase;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }
        .card-header {
            background-color: white;
            border-bottom: 1px solid #eee;
            border-radius: 12px 12px 0 0;
            padding: 1rem 1.5rem;
        }

        .notification-list {
            max-height: 350px;
            overflow-y: auto;
        }
        .notification-list::-webkit-scrollbar {
            width: 8px;
        }
        .notification-list::-webkit-scrollbar-thumb {
            background-color: rgba(0, 123, 255, 0.5);
            border-radius: 10px;
        }

        .alert {
            margin-bottom: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            border-left: 5px solid;
        }
        .alert-warning { border-left-color: var(--warning-color); }
        .alert-info { border-left-color: var(--primary-color); }
        .alert-success { border-left-color: var(--success-color); }
        .alert-danger { border-left-color: var(--danger-color); }
        .alert small {
            display: block;
            margin-top: 5px;
        }

        .search-card {
            background: #f8f9fa;
            border-radius: 10px;
            border: none;
            border-left: 5px solid var(--accent);
        }
        .form-control {
            border-radius: 20px;
            border: 1px solid #ced4da;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }
        .btn-search {
            background: linear-gradient(135deg, var(--accent), var(--highlight));
            border: none;
            border-radius: 20px;
            padding: 10px 30px;
            transition: transform 0.3s ease;
        }
        .btn-search:hover {
            transform: scale(1.05);
        }
        .btn-primary, .btn-secondary {
            border-radius: 20px;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container-fluid mt-4">
    <!-- Welcome Card -->
    <div class="card welcome-card mb-5 shadow">
        <div class="card-body text-center py-4">
            <h1 class="mb-1 font-weight-bold">Admin Dashboard</h1>
            <p class="lead mb-0">Welcome, <strong><?php echo htmlspecialchars($_SESSION["username"] ?? 'Admin'); ?>!</strong> Monitor and manage system activities.</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon"><i class="fas fa-users fa-2x"></i></div>
                <div class="card-body">
                    <h5 class="stats-title">Total Users</h5>
                    <h3 class="stats-value"><?php echo $total_users; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon"><i class="fas fa-couch fa-2x"></i></div>
                <div class="card-body">
                    <h5 class="stats-title">Total Furniture</h5>
                    <h3 class="stats-value"><?php echo $total_furniture; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon"><i class="fas fa-shopping-cart fa-2x"></i></div>
                <div class="card-body">
                    <h5 class="stats-title">Total Orders</h5>
                    <h3 class="stats-value"><?php echo $total_orders; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon"><i class="fas fa-exclamation-circle fa-2x"></i></div>
                <div class="card-body">
                    <h5 class="stats-title">Total Complaints</h5>
                    <h3 class="stats-value"><?php echo $total_complaints; ?></h3>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Orders by Status</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="ordersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Reviews by Rating</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="reviewsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Orders Last 7 Days</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="ordersTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Management -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Users</h5>
                </div>
                <div class="card-body notification-list">
                    <?php if ($recent_users->num_rows > 0): ?>
                        <ul class="list-unstyled">
                            <?php while ($user = $recent_users->fetch_assoc()): ?>
                                <li class="alert alert-info mb-3">
                                    <p class="mb-1"><strong><?php echo htmlspecialchars($user['full_name']); ?></strong> (<?php echo ucfirst($user['role']); ?>)</p>
                                    <p class="mb-1 small"><?php echo htmlspecialchars($user['email']); ?></p>
                                    <p class="mb-1 small">Status: <span class="badge badge-<?php echo $user['status'] == 'active' ? 'success' : ($user['status'] == 'blocked' ? 'danger' : 'warning'); ?>"><?php echo ucfirst($user['status']); ?></span></p>
                                    <small>Joined: <?php echo date('M d, Y H:i', strtotime($user['created_at'])); ?></small>
                                    <div class="mt-2">
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-secondary text-center">No recent users.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>



    <!-- Orders Management -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Orders</h5>
                </div>
                <div class="card-body notification-list">
                    <?php if ($recent_orders->num_rows > 0): ?>
                        <ul class="list-unstyled">
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <li class="alert alert-success mb-3">
                                    <p class="mb-1"><strong>Order #<?php echo $order['order_id']; ?></strong> - <?php echo htmlspecialchars($order['title']); ?></p>
                                    <p class="mb-1 small">Buyer: <?php echo htmlspecialchars($order['buyer_name']); ?></p>
                                    <p class="mb-1 small">Status: <span class="badge badge-<?php echo $order['order_status'] == 'completed' ? 'success' : ($order['order_status'] == 'cancelled' ? 'danger' : 'warning'); ?>"><?php echo ucfirst($order['order_status']); ?></span></p>
                                    <small>Placed: <?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></small>
                                    <div class="mt-2">
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-secondary text-center">No recent orders.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Complaints Management -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Complaints</h5>
                </div>
                <div class="card-body notification-list">
                    <?php if ($recent_complaints->num_rows > 0): ?>
                        <ul class="list-unstyled">
                            <?php while ($complaint = $recent_complaints->fetch_assoc()): ?>
                                <li class="alert alert-<?php echo $complaint['status'] == 'pending' ? 'warning' : ($complaint['status'] == 'resolved' ? 'success' : 'danger'); ?> mb-3">
                                    <p class="mb-1"><strong>Complaint #<?php echo $complaint['complaint_id']; ?></strong> - <?php echo htmlspecialchars(substr($complaint['description'], 0, 50)) . '...'; ?></p>
                                    <p class="mb-1 small">By: <?php echo htmlspecialchars($complaint['full_name']); ?></p>
                                    <p class="mb-1 small">Status: <span class="badge badge-<?php echo $complaint['status'] == 'pending' ? 'warning' : ($complaint['status'] == 'resolved' ? 'success' : 'danger'); ?>"><?php echo ucfirst($complaint['status']); ?></span></p>
                                    <small>Filed: <?php echo date('M d, Y H:i', strtotime($complaint['created_at'])); ?></small>
                                    <div class="mt-2">
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-secondary text-center">No recent complaints.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

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
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Orders Trend Chart
    const ordersTrendChart = document.getElementById('ordersTrendChart').getContext('2d');
    new Chart(ordersTrendChart, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($orders_dates); ?>,
            datasets: [{
                label: 'Orders',
                data: <?php echo json_encode($orders_counts); ?>,
                borderColor: '#ff0000ff',
                backgroundColor: 'rgba(255, 0, 0, 0.99)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>
</body>
</html>