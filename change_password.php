<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header('Location: login.html');
    exit();
}

require 'db.php';
$owner_id = $_SESSION['owner_id'];
$message = "";

// Fetch current hashed password from database
$stmt = $conn->prepare("SELECT password FROM owners WHERE owner_id = ?");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
$owner = $result->fetch_assoc();
$current_hashed_password = $owner['password'];

// Handle password update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!password_verify($old_password, $current_hashed_password)) {
        $message = "<p class='error'>Old password is incorrect.</p>";
    } elseif ($new_password !== $confirm_password) {
        $message = "<p class='error'>New passwords do not match.</p>";
    } elseif (strlen($new_password) < 6) {
        $message = "<p class='error'>New password must be at least 6 characters long.</p>";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE owners SET password = ? WHERE owner_id = ?");
        $stmt->bind_param("si", $hashed_password, $owner_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Password changed successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $message = "<p class='error'>Failed to change password. Try again.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .change-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }

        h2 {
            margin-bottom: 15px;
            color: #4CAF50;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .success {
            color: green;
            margin-bottom: 10px;
        }

        .btn {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #388E3C;
        }

        .cancel-btn {
            background: #d9534f;
        }

        .cancel-btn:hover {
            background: #c9302c;
        }
    </style>
</head>
<body>

    <div class="change-container">
        <h2>Change Password</h2>

        <?php if (!empty($message)): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Old Password:</label>
                <input type="password" name="old_password" required>
            </div>

            <div class="form-group">
                <label>New Password:</label>
                <input type="password" name="new_password" required>
            </div>

            <div class="form-group">
                <label>Confirm New Password:</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn">Change Password</button>
            <a href="profile.php" class="btn cancel-btn">Cancel</a>
        </form>
    </div>

</body>
</html>
