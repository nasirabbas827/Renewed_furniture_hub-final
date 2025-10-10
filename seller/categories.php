<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$msg = "";

// Handle Add Category
if (isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    if (!empty($category_name)) {
        $sql = "INSERT INTO categories (category_name) VALUES (?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $category_name);
        if (mysqli_stmt_execute($stmt)) {
            $msg = '<div class="alert alert-success text-center">Category added successfully.</div>';
        } else {
            $msg = '<div class="alert alert-danger text-center">Category already exists or error occurred.</div>';
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle Update Category
if (isset($_POST['update_category'])) {
    $category_id = intval($_POST['edit_category_id']);
    $category_name = trim($_POST['edit_category_name']);
    if (!empty($category_name)) {
        $sql = "UPDATE categories SET category_name=? WHERE category_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $category_name, $category_id);
        if (mysqli_stmt_execute($stmt)) {
            $msg = '<div class="alert alert-success text-center">Category updated successfully.</div>';
        } else {
            $msg = '<div class="alert alert-danger text-center">Error updating category.</div>';
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $sql = "DELETE FROM categories WHERE category_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    if (mysqli_stmt_execute($stmt)) {
        $msg = '<div class="alert alert-success text-center">Category deleted successfully.</div>';
    } else {
        $msg = '<div class="alert alert-danger text-center">Error deleting category.</div>';
    }
    mysqli_stmt_close($stmt);
}

// Fetch All Categories
$result = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <?php echo $msg; ?>

    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
            <h4 class="text-center mb-3">Add New Category</h4>
            <form action="" method="POST">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="category_name" class="form-control" required>
                </div>
                <div class="text-center">
                    <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>

    <hr>

            <h4 class="mb-3 text-center">Manage Categories</h4>
            <table class="table table-bordered table-striped text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['category_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning editBtn"
                                data-id="<?php echo $row['category_id']; ?>"
                                data-name="<?php echo htmlspecialchars($row['category_name']); ?>"
                                data-toggle="modal" data-target="#editModal">Edit</button>
                            <a href="?delete=<?php echo $row['category_id']; ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Edit Category</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="edit_category_id" id="edit_category_id">
            <div class="form-group">
                <label>Category Name</label>
                <input type="text" name="edit_category_name" id="edit_category_name" class="form-control" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update_category" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).on("click", ".editBtn", function () {
    var category_id = $(this).data("id");
    var category_name = $(this).data("name");
    $("#edit_category_id").val(category_id);
    $("#edit_category_name").val(category_name);
});
</script>
</body>
</html>
