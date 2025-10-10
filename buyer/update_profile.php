<?php
session_start();
include('config.php'); 

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$user_id = $_SESSION["id"];
$update_msg = "";

// Fetch current user data
$sql = "SELECT full_name, email, phone, address, city, state, country, profile_image FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $full_name, $email, $phone, $address, $city, $state, $country, $profile_image);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);
    $city = trim($_POST["city"]);
    $state = trim($_POST["state"]);
    $country = trim($_POST["country"]);
    $password = trim($_POST["password"]);

    $upload_dir = "../uploads/";
    $upload_file = $profile_image; // keep old if not updated

    // Handle profile image upload
    if (!empty($_FILES["profile_image"]["name"])) {
        $target_file = $upload_dir . basename($_FILES["profile_image"]["name"]);
        $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_exts = ["jpg", "jpeg", "png", "gif"];

        if (in_array($file_ext, $allowed_exts)) {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $upload_file = basename($_FILES["profile_image"]["name"]);
            } else {
                $update_msg = '<div class="alert alert-danger text-center">Error uploading image.</div>';
            }
        } else {
            $update_msg = '<div class="alert alert-danger text-center">Invalid file type. Only JPG, PNG, GIF allowed.</div>';
        }
    }

    // Update with or without password
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET full_name=?, email=?, phone=?, address=?, city=?, state=?, country=?, password=?, profile_image=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "sssssssssi", $full_name, $email, $phone, $address, $city, $state, $country, $hashed_password, $upload_file, $user_id);
    } else {
        $update_sql = "UPDATE users SET full_name=?, email=?, phone=?, address=?, city=?, state=?, country=?, profile_image=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "ssssssssi", $full_name, $email, $phone, $address, $city, $state, $country, $upload_file, $user_id);
    }

    if (mysqli_stmt_execute($stmt)) {
        $update_msg = '<div class="alert alert-success text-center">Profile updated successfully.</div>';
    } else {
        $update_msg = '<div class="alert alert-danger text-center">Error updating profile. Please try again.</div>';
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Profile</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 700px;">
        <div class="card-body">
            <h3 class="text-center mb-4">Update Profile</h3>
            <?php echo $update_msg; ?>

            <div class="text-center mb-3">
                <img src="../uploads/<?php echo htmlspecialchars($profile_image ?: 'default.png'); ?>" 
                     alt="Profile Image" 
                     class="rounded-circle" 
                     width="120" height="120">
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($full_name); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($address); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>City</label>
                        <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($city); ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>State</label>
                        <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($state); ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Country</label>
                        <input type="text" name="country" class="form-control" value="<?php echo htmlspecialchars($country); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Profile Image</label>
                    <input type="file" name="profile_image" class="form-control">
                </div>

                <div class="form-group">
                    <label>New Password (optional)</label>
                    <input type="password" name="password" class="form-control">
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>

        </div>
    </div>
</div>
</body>
</html>
