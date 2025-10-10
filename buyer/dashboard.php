<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Fetch stats for cards
$total_orders = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE buyer_id = $user_id")->fetch_assoc()['total'];
$total_complaints = $conn->query("SELECT COUNT(*) AS total FROM complaints WHERE user_id = $user_id")->fetch_assoc()['total'];
$recent_order = $conn->query("SELECT order_status FROM orders WHERE buyer_id = $user_id ORDER BY order_date DESC LIMIT 1")->fetch_assoc();
$recent_order_status = $recent_order ? ucfirst($recent_order['order_status']) : 'No orders yet';

// Fetch categories for filters
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name ASC");

// Search Filters
$where = "WHERE f.status = 'available'";

// Apply filters from form
if (!empty($_GET['category_id'])) {
    $where .= " AND f.category_id = " . intval($_GET['category_id']);
}
if (!empty($_GET['condition'])) {
    $where .= " AND f.condition = '" . mysqli_real_escape_string($conn, $_GET['condition']) . "'";
}
if (!empty($_GET['location'])) {
    $where .= " AND f.location LIKE '%" . mysqli_real_escape_string($conn, $_GET['location']) . "%'";
}
if (!empty($_GET['min_price']) && !empty($_GET['max_price'])) {
    $where .= " AND f.price BETWEEN " . floatval($_GET['min_price']) . " AND " . floatval($_GET['max_price']);
}

// Fetch all furniture with one random image
$furniture_sql = "
    SELECT f.*, 
    (SELECT image_path FROM furniture_images WHERE furniture_id = f.furniture_id ORDER BY RAND() LIMIT 1) AS image_path
    FROM furniture f
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
    <title>Buyer Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .welcome-card {
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: white;
            border: none;
            transition: transform 0.3s ease;
        }
        .welcome-card:hover {
            transform: translateY(-5px);
        }
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .search-card {
            background: #f8f9fa;
            border-radius: 10px;
            border: none;
        }
        .form-control {
            border-radius: 20px;
            border: 1px solid #ced4da;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }
        .btn-search {
            background: linear-gradient(135deg, #007bff, #28a745);
            border: none;
            border-radius: 20px;
            padding: 10px 30px;
            transition: transform 0.3s ease;
        }
        .btn-search:hover {
            transform: scale(1.05);
        }
        .furniture-img {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .btn-primary, .btn-secondary {
            border-radius: 20px;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container-fluid mt-4">
    <!-- Welcome Card -->
    <div class="card welcome-card mb-4 shadow">
        <div class="card-body text-center py-4">
            <h1 class="mb-1 font-weight-bold">Buyer Dashboard</h1>
            <p class="lead mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION["name"] ?? 'User'); ?>! Browse and explore available furniture.</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card card-hover shadow">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-shopping-cart text-primary fa-2x mr-3"></i>
                    <div>
                        <h5 class="card-title text-muted small font-weight-bold">Total Orders</h5>
                        <h3 class="card-text font-weight-bold"><?php echo $total_orders; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
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
        <div class="col-md-4 mb-3">
            <div class="card card-hover shadow">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="card-title text-muted small font-weight-bold">Recent Order Status</h5>
                        <h3 class="card-text font-weight-bold"><?php echo $recent_order_status; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Search -->
    <div class="card search-card p-4 mb-4 shadow">
        <form method="GET">
            <div class="form-row align-items-end">
                <div class="col-md-3 mb-3">
                    <label for="category_id"><i class="fas fa-th-large mr-1"></i> Category</label>
                    <select name="category_id" id="category_id" class="form-control">
                        <option value="">All</option>
                        <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo isset($_GET['category_id']) && $_GET['category_id'] == $cat['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="condition"><i class="fas fa-tags mr-1"></i> Condition</label>
                    <select name="condition" id="condition" class="form-control">
                        <option value="">All</option>
                        <option value="new" <?php echo isset($_GET['condition']) && $_GET['condition'] == 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="like_new" <?php echo isset($_GET['condition']) && $_GET['condition'] == 'like_new' ? 'selected' : ''; ?>>Like New</option>
                        <option value="good" <?php echo isset($_GET['condition']) && $_GET['condition'] == 'good' ? 'selected' : ''; ?>>Good</option>
                        <option value="fair" <?php echo isset($_GET['condition']) && $_GET['condition'] == 'fair' ? 'selected' : ''; ?>>Fair</option>
                        <option value="poor" <?php echo isset($_GET['condition']) && $_GET['condition'] == 'poor' ? 'selected' : ''; ?>>Poor</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="min_price"><i class="fas fa-money-bill-wave mr-1"></i> Min Price</label>
                    <input type="number" class="form-control" name="min_price" id="min_price" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="max_price"><i class="fas fa-money-bill-wave mr-1"></i> Max Price</label>
                    <input type="number" class="form-control" name="max_price" id="max_price" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="location"><i class="fas fa-map-marker-alt mr-1"></i> Location</label>
                    <input type="text" class="form-control" name="location" id="location" placeholder="City or Area" value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>">
                </div>
                <div class="col-md-12 text-right">
                    <button type="submit" class="btn btn-search"><i class="fas fa-search mr-2"></i>Search</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Furniture List -->
    <div class="row">
        <?php if (mysqli_num_rows($furniture_items) > 0): ?>
            <?php while ($f = mysqli_fetch_assoc($furniture_items)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card card-hover shadow">
                        <?php if ($f['image_path']): ?>
                            <img src="../Uploads/<?php echo htmlspecialchars($f['image_path']); ?>" class="furniture-img" alt="<?php echo htmlspecialchars($f['title']); ?>">
                        <?php else: ?>
                            <img src="../Uploads/no_image.png" class="furniture-img" alt="No Image">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($f['title']); ?></h5>
                            <p class="card-text mb-1"><strong>Price:</strong> PKR <?php echo number_format($f['price'], 2); ?></p>
                            <p class="card-text mb-1"><strong>Condition:</strong> <?php echo ucfirst($f['condition']); ?></p>
                            <p class="card-text mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($f['location']); ?></p>
                            <p class="card-text"><strong>Status:</strong> <?php echo ucfirst($f['status']); ?></p>
                            <div class="d-flex">
                                <a href="view_furniture_details.php?furniture_id=<?php echo $f['furniture_id']; ?>" class="btn btn-primary mr-2 flex-fill">View Details</a>
                                <a href="reviews.php?furniture_id=<?php echo $f['furniture_id']; ?>&seller_id=<?php echo $f['id']; ?>" class="btn btn-secondary flex-fill">Reviews</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">No furniture items found matching your criteria.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>