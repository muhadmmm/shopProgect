
<!-- login.php -->
<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check credentials
    $stmt = $pdo->prepare("SELECT * FROM owners WHERE email = ? AND password = ?");
    $stmt->execute([$email, $password]);
    $owner = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($owner) {
        $_SESSION['owner_id'] = $owner['owner_id'];
        header('Location: dashboard.php');
    } else {
        echo "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form action="login.php" method="POST">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>


