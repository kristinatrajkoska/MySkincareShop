<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "myshop";

// Create connection
$connection = new mysqli($servername, $username, $password, $database);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Check if ID is provided
if (isset($_GET["id"])) {
    $id = $_GET["id"];

    // Delete product from database
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: /myshop/index.php");
        exit;
    } else {
        echo "Error deleting product: " . $connection->error;
    }
} else {
    echo "Invalid product ID.";
}
?>
