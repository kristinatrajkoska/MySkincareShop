<?php
session_start();

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$database = "myshop";

$connection = new mysqli($servername, $username, $password, $database);

// Check if the connection is successful
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_logged_in = true;
    $username = $_SESSION['username'];
} else {
    $user_logged_in = false;
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    // Validate inputs
    if (empty($input_username) || empty($input_password)) {
        $errorMessage = "Both fields are required.";
    } else {
        // Check if the user exists
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("s", $input_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($input_password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: /myshop/index.php");
                exit;
            } else {
                $errorMessage = "Invalid username or password.";
            }
        } else {
            $errorMessage = "Invalid username or password.";
        }
    }
}

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $register_username = $_POST['register_username'];
    $register_password = $_POST['register_password'];
    $register_confirm_password = $_POST['register_confirm_password'];

    if (empty($register_username) || empty($register_password) || empty($register_confirm_password)) {
        $register_errorMessage = "All fields are required.";
    } elseif ($register_password !== $register_confirm_password) {
        $register_errorMessage = "Passwords do not match.";
    } else {
        // Check if the username already exists
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("s", $register_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $register_errorMessage = "Username already exists.";
        } else {
            // Insert new user into the database
            $hashed_password = password_hash($register_password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ss", $register_username, $hashed_password);
            $stmt->execute();
            $register_successMessage = "Registration successful! You can now log in.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Shop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        /* Background image settings */
        body {
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
        }

        .container {
            margin-top: 50px;
        }

        /* White box for better readability */
        .table-container {
            background: rgba(255, 255, 255, 0.9); /* Semi-transparent white */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #0277bd;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">List of Products</h2>

    <?php if ($user_logged_in): ?>
        <div class="text-center">
            <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <!-- Product List -->
        <div class="table-container">
            <a class="btn btn-primary mb-3" href="/myshop/create.php">New Product</a>
            <table class="table table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Read all rows from database table
                    $sql = "SELECT * FROM products";
                    $result = $connection->query($sql);

                    if (!$result) {
                        die("Invalid query: " . $connection->error);
                    }

                    // Read data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['type']}</td>
                            <td>{$row['amount']}</td>
                            <td>{$row['created_at']}</td>
                            <td>
                                <a class='btn btn-primary btn-sm' href='/myshop/edit.php?id={$row['id']}'>Edit</a>
                                <a class='btn btn-danger btn-sm' href='/myshop/delete.php?id={$row['id']}'>Delete</a>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <!-- Login Form -->
        <div class="table-container">
            <h4 class="text-center">Login</h4>
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary">Login</button>
            </form>

            <hr>

            <!-- Registration Form -->
            <h4 class="text-center">Register</h4>
            <?php if (!empty($register_errorMessage)): ?>
                <div class="alert alert-danger"><?php echo $register_errorMessage; ?></div>
            <?php endif; ?>
            <?php if (!empty($register_successMessage)): ?>
                <div class="alert alert-success"><?php echo $register_successMessage; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="register_username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="register_password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" name="register_confirm_password" required>
                </div>
                <button type="submit" name="register" class="btn btn-success">Register</button>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
