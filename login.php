<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Fetch user data
    $stmt = $pdo->prepare("SELECT * FROM owners WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        // Set session and redirect
        $_SESSION["owner_id"] = $user["owner_id"];
        $_SESSION["name"] = $user["name"];
        header("Location: dashboard.php");  // Redirect to dashboard after login
        exit;
    } else {
        echo "Error: Invalid email or password!";
    }
}
?>