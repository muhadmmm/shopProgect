<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];  // Don't hash the password here

    // Check if the email exists
    $query = "SELECT * FROM owners WHERE email='$email'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the plain password with the hashed password in the database
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user'] = $user['name'];
            $_SESSION['owner_id'] = $user['owner_id'];
            echo "<script>alert('Login successful!'); window.location.href='dashboard.php';</script>";
        } else {
            echo "<script>alert('Incorrect password!'); window.location.href='login.html';</script>";
        }
    } else {
        echo "<script>alert('Email not found! Please register.'); window.location.href='login.html';</script>";
    }
}
$conn->close();
?>
