<?php
session_start();

// If not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: /myshop/login.php");
    exit;
}





$servername = "localhost";
$username = "root";
$password = "";
$database = "myshop";

// Create connection
$connection = new mysqli($servername, $username, $password, $database);

$name = "";
$type = "";
$amount = "";

$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST["name"]);
    $type = trim($_POST["type"]);
    $amount = trim($_POST["amount"]);

    do {
        if (empty($name) || empty($type) || empty($amount)) {
            $errorMessage = "All fields are required.";
            break;
        }

        // Insert new product into database
        $sql = "INSERT INTO products (name, type, amount) VALUES (?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("sss", $name, $type, $amount);

        if ($stmt->execute()) {
            $successMessage = "Product added successfully!";
            $name = $type = $amount = "";
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }

        // Redirect after success
        header("location: /myshop/index.php");
        exit;
    } while (false);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Shop - Add Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #e3f2fd; /* Light Blue Background */
        }
        .container {
            max-width: 600px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        h2 {
            color: #0277bd; /* Deep blue title */
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">Add New Product</h2>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><?php echo $errorMessage; ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($name); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Type</label>
            <input type="text" class="form-control" name="type" value="<?php echo htmlspecialchars($type); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Amount</label>
            <input type="text" class="form-control" name="amount" value="<?php echo htmlspecialchars($amount); ?>">
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><?php echo $successMessage; ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Submit</button>
            <a class="btn btn-outline-secondary" href="/myshop/index.php">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>
