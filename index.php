<?php
session_start();
include('config.php');

// Fetch stats for cards if user is logged in
if (isset($_SESSION["id"])) {
    $user_id = intval($_SESSION["id"]);
    $total_orders = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE buyer_id = $user_id")->fetch_assoc()['total'];
    $total_complaints = $conn->query("SELECT COUNT(*) AS total FROM complaints WHERE user_id = $user_id")->fetch_assoc()['total'];
    $recent_order = $conn->query("SELECT order_status FROM orders WHERE buyer_id = $user_id ORDER BY order_date DESC LIMIT 1")->fetch_assoc();
    $recent_order_status = $recent_order ? ucfirst($recent_order['order_status']) : 'No orders yet';
}

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
    <title>FurnitureHub - Furniture Marketplace</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --primary: #007bff;
            --secondary: #6c757d;
            --success: #28a745;
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --accent: #007bff;
            --highlight: #28a745;
        }

        .hero-section {
            height: 600px;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('./images/hotel.jpg') center/cover no-repeat;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border-radius: 0 0 30px 30px;
            animation: fadeIn 1.5s ease-in-out;

        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .hero-section .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
            padding: 12px 30px;
            font-size: 1.2rem;
            transition: transform 0.3s, background-color 0.3s;
            border-radius: 20px;
        }
        .hero-section .btn-primary:hover {
            background-color: var(--highlight);
            border-color: var(--highlight);
            transform: scale(1.05);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            transition: transform 0.3s, box-shadow 0.3s;
            background-color: white;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .furniture-img {
            height: 220px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
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
            background: linear-gradient(135deg, #007bff, #28a745);
            border: none;
            border-radius: 20px;
            padding: 10px 30px;
            transition: transform 0.3s ease;
        }
        .btn-search:hover {
            transform: scale(1.05);
        }
        .section {
            padding: 80px 0;
        }
        .section-title {
            color: var(--highlight);
            margin-bottom: 40px;
            font-weight: 700;
            position: relative;
        }
        .section-title::after {
            content: '';
            width: 60px;
            height: 4px;
            background-color: var(--accent);
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }
        .feature-icon {
            font-size: 3em;
            color: var(--accent);
            margin-bottom: 20px;
            transition: color 0.3s;
        }
        .card:hover .feature-icon {
            color: var(--highlight);
        }
        footer {
            background-color: var(--dark);
            color: white;
            padding: 30px 0;
            border-top: 4px solid var(--accent);
        }
        .btn-primary, .btn-secondary {
            border-radius: 20px;
        }
        .alert {
            margin-bottom: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            border-left: 5px solid var(--accent);
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="display-4 fw-bold text-white">FurnitureHub</h1>
        <p class="lead mb-4">Your One-Stop Marketplace for Quality Furniture</p>
        <?php if (!isset($_SESSION['id'])): ?>
            <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt mr-2"></i>Login to Explore</a>
        <?php else: ?>
            <a href="dashboard.php" class="btn btn-primary"><i class="fas fa-tachometer-alt mr-2"></i>Go to Dashboard</a>
        <?php endif; ?>
    </div>

    <!-- About Section -->
    <div class="section bg-light">
        <div class="container">
            <h2 class="text-center section-title">About FurnitureHub</h2>
            <div class="row align-items-center">
                <div class="col-md-12">
                    <p class="lead">FurnitureHub is a premier platform connecting buyers and sellers in the furniture industry. Browse a wide range of furniture, place orders, and discover quality pieces for your home or office.</p>
                    <p>Our mission is to make furniture shopping easy, affordable, and reliable, ensuring seamless transactions and high-quality products for all users.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards (Visible only if logged in) -->
    <?php if (isset($_SESSION['id'])): ?>
    <div class="section">
        <div class="container">
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
        </div>
    </div>
    <?php endif; ?>

    <!-- Furniture Section -->
    <div class="section bg-light">
        <div class="container">
            <h2 class="text-center section-title">Available Furniture</h2>
            <!-- Search Form -->
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
                            <div class="card h-100">
                                <?php if ($f['image_path']): ?>
                                    <img src="./Uploads/<?php echo htmlspecialchars($f['image_path']); ?>" class="furniture-img" alt="<?php echo htmlspecialchars($f['title']); ?>">
                                <?php else: ?>
                                    <img src="./Uploads/no_image.png" class="furniture-img" alt="No Image">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($f['title']); ?></h5>
                                    <p class="card-text">
                                        <strong>Price:</strong> PKR <?php echo number_format($f['price'], 2); ?><br>
                                        <strong>Condition:</strong> <?php echo ucfirst($f['condition']); ?><br>
                                        <strong>Location:</strong> <?php echo htmlspecialchars($f['location']); ?><br>
                                        <strong>Status:</strong> 
                                        <span class="badge badge-<?php echo $f['status'] == 'available' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($f['status']); ?>
                                        </span>
                                    </p>
                                    
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
    </div>

    <!-- Features Section -->
    <div class="section">
        <div class="container">
            <h2 class="text-center section-title">Key Features</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-couch feature-icon"></i>
                            <h5 class="card-title">Browse Furniture</h5>
                            <p class="card-text">Explore a wide variety of furniture from trusted sellers.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-shopping-cart feature-icon"></i>
                            <h5 class="card-title">Easy Ordering</h5>
                            <p class="card-text">Place orders and communicate with sellers seamlessly.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-star feature-icon"></i>
                            <h5 class="card-title">Verified Reviews</h5>
                            <p class="card-text">Read and leave reviews to ensure trust and quality.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-0">© 2025 FurnitureHub. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>