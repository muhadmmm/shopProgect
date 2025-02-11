<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if the email already exists
    $checkQuery = "SELECT * FROM owners WHERE email='$email'";
    $result = $conn->query($checkQuery);

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already registered!'); window.location.href='login.html';</script>";
    } else {
        // Insert new user into database
        $insertQuery = "INSERT INTO owners (name, email, password) VALUES ('$name', '$email', '$password')";
        if ($conn->query($insertQuery) === TRUE) {
            echo "<script>alert('Registration successful! Please login.'); window.location.href='login.html';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
$conn->close();
?>