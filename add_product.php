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
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);

    // Check for empty fields
    if (empty($name) || empty($category) || $price <= 0 || $quantity < 0) {
        echo "Invalid input. Please fill all fields correctly.";
        exit();
    }

    // Insert the product into the database
    $stmt = $conn->prepare("INSERT INTO products (name, category, price, quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $name, $category, $price, $quantity);

    if ($stmt->execute()) {
        echo "Product added successfully!";
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
