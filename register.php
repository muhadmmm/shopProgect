<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT * FROM owners WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo "Error: Email already registered!";
        exit;
    }

    // Hash the password before storing
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert new owner
    $stmt = $pdo->prepare("INSERT INTO owners (name, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $email, $hashedPassword])) {
        echo "Registration successful! <a href='login.php'>Login here</a>";
    } else {
        echo "Error: Registration failed!";
    }
}
?>
