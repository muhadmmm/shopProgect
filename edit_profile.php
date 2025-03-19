<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header('Location: login.html');
    exit();
}

require 'db.php';
$owner_id = $_SESSION['owner_id'];

// Fetch current owner details
$stmt = $conn->prepare("SELECT name, email, phone FROM owners WHERE owner_id = ?");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
$owner = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if (!empty($name) && !empty($email) && !empty($phone)) {
        $stmt = $conn->prepare("UPDATE owners SET name = ?, email = ?, phone = ? WHERE owner_id = ?");
        $stmt->bind_param("sssi", $name, $email, $phone, $owner_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Profile updated successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $error = "Failed to update profile. Try again.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
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

        .edit-container {
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

    <div class="edit-container">
        <h2>Edit Profile</h2>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($owner['name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($owner['email']); ?>" required>
            </div>

            <div class="form-group">
                <label>Phone:</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($owner['phone']); ?>" required>
            </div>

            <button type="submit" class="btn">Save Changes</button>
            <a href="profile.php" class="btn cancel-btn">Cancel</a>
        </form>
    </div>

</body>
</html>
