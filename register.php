<?php
include('config.php');

// Initialize variables
$name = $email = $password = $role = $phone = $address = $city = $state = $country = "";
$name_err = $email_err = $password_err = $role_err = $phone_err = $address_err = $city_err = $state_err = $country_err = "";
$registration_msg = "";

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate Name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your full name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate Email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_email);
        $param_email = $email;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $email_err = "This email is already registered.";
        }
        mysqli_stmt_close($stmt);
    }

    // Validate Password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 3) {
        $password_err = "Password must be at least 3 characters long.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate Role
    if (empty($_POST["role"])) {
        $role_err = "Please select a role.";
    } else {
        $role = $_POST["role"];
    }

    // Validate Phone
    if (!empty($_POST["phone"])) {
        $phone = trim($_POST["phone"]);
        $sql = "SELECT id FROM users WHERE phone = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_phone);
        $param_phone = $phone;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $phone_err = "This phone number is already registered.";
        }
        mysqli_stmt_close($stmt);
    }

    // Validate Address Fields
    $address = !empty($_POST["address"]) ? trim($_POST["address"]) : "";
    $city = !empty($_POST["city"]) ? trim($_POST["city"]) : "";
    $state = !empty($_POST["state"]) ? trim($_POST["state"]) : "";
    $country = !empty($_POST["country"]) ? trim($_POST["country"]) : "";

    // Insert if no errors
    if (empty($name_err) && empty($email_err) && empty($password_err) && empty($role_err) && empty($phone_err)) {
        $sql = "INSERT INTO users (full_name, email, password, role, status, upload_status, phone, address, city, state, country) 
                VALUES (?, ?, ?, ?, 'active', 'active', ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssss", 
            $param_name, $param_email, $param_password, $param_role, 
            $param_phone, $param_address, $param_city, $param_state, $param_country
        );

        $param_name = $name;
        $param_email = $email;
        $param_password = password_hash($password, PASSWORD_DEFAULT);
        $param_role = $role;
        $param_phone = $phone;
        $param_address = $address;
        $param_city = $city;
        $param_state = $state;
        $param_country = $country;

        if (mysqli_stmt_execute($stmt)) {
            $registration_msg = '<div class="alert alert-success text-center">Registration successful!</div>';
        } else {
            $registration_msg = '<div class="alert alert-danger text-center">Error: Unable to register. Try again.</div>';
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto shadow-sm" style="max-width: 790px;">
        <div class="card-body">
            <h3 class="text-center mb-4">User Registration</h3>
            <?php echo $registration_msg; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                        <span class="invalid-feedback"><?php echo $name_err; ?></span>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Role</label>
                        <select name="role" class="form-control <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>">
                            <option value="">Select Role</option>
                            <option value="buyer" <?php echo ($role == 'buyer') ? 'selected' : ''; ?>>Buyer</option>
                            <option value="seller" <?php echo ($role == 'seller') ? 'selected' : ''; ?>>Seller</option>
                        </select>
                        <span class="invalid-feedback"><?php echo $role_err; ?></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone; ?>">
                        <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Address</label>
                        <textarea name="address" rows="1" class="form-control"><?php echo $address; ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>City</label>
                        <input type="text" name="city" class="form-control" value="<?php echo $city; ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>State</label>
                        <input type="text" name="state" class="form-control" value="<?php echo $state; ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Country</label>
                        <input type="text" name="country" class="form-control" value="<?php echo $country; ?>">
                    </div>
                </div>

                <div class="form-group text-center mt-4">
                    <input type="submit" class="btn btn-primary px-4" value="Register">
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
