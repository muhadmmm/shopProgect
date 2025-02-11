<?php
session_start();
require 'db.php';

// Check if owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header('Location: login.html');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity_to_add = intval($_POST['quantity']);

    // Validate input
    if ($product_id <= 0 || $quantity_to_add <= 0) {
        echo "Invalid input. Please select a product and enter a positive quantity.";
        exit();
    }

    // Update the product quantity in the database
    $stmt = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE product_id = ?");
    $stmt->bind_param("ii", $quantity_to_add, $product_id);

    if ($stmt->execute()) {
        echo "Product restocked successfully!";
        header('Location: dashboard.php'); // Redirect back to the dashboard
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request method.";
}

$conn->close();
?>
