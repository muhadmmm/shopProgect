<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header('Location: login.html');
    exit();
}

require 'db.php';
$owner_id = $_SESSION['owner_id'];

// Fetch owner details
$stmt = $conn->prepare("SELECT name, email, phone FROM owners WHERE owner_id = ?");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
$owner = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background: #f4f4f4;
            background-image: url('images/shop-bg.jpg');
            background-blend-mode: darken;
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background: #4CAF50;
            height: 100vh;
            color: white;
            padding: 20px;
            position: fixed;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
           
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar ul li a {
            text-decoration: none;
            color: white;
            display: block;
            padding: 15px;
        }

        .sidebar ul li:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .main-content {
            margin-left: 270px;
            padding: 20px;
            flex-grow: 1;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background-color: #4caf50;
            color: white;
            border-radius: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .logo {
            display: flex;
            align-items: center;
        }
    
        .logo img {
            height: 40px;
            margin-right: 10px;
            border-radius: 20px;
        }

        .profile-container {
            background-color:rgba(255, 255, 255, 0.74);
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            display: flex;
            align-items: center;
        }

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 2px solid #4CAF50;
            object-fit: cover;
            margin-right: 20px;
        }

        .profile-details {
            flex-grow: 1;
        }

        .profile-details h2 {
            margin-bottom: 10px;
        }

        .profile-details p {
            font-size: 16px;
            margin: 5px 0;
        }

        .settings {
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.74);
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .settings h3 {
            margin-bottom: 15px;
        }

        .btn {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
            margin-right: 10px;
        }

        .btn:hover {
            background: #388E3C;
        }

        .logout-btn {
            background: #d9534f;
        }

        .logout-btn:hover {
            background: #c9302c;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Menu</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="stocks.php">Stocks</a></li>
            <li><a href="billing.php">Billing</a></li>
            <li><a href="customer.php">Customers</a></li>
            <li><a href="report.php">Reports</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
        <div class="logo">
            <img src="images/logo.jpg" alt="Groches Logo">
            <h1>Groches</h1>
        </div>
            <h1>Profile</h1>
            Welcome, <?php echo htmlspecialchars($owner['name']); ?>
        </div>

        <div class="profile-container">
            <img src="images/profile.png" alt="Profile Picture" class="profile-image">
            <div class="profile-details">
                <h2><?php echo htmlspecialchars($owner['name']); ?></h2>
                <p><strong>Email:</strong><strong> <?php echo htmlspecialchars($owner['email']); ?></strong></p>
                <p><strong>Phone:</strong><strong> <?php echo htmlspecialchars($owner['phone']); ?></strong></p>
                <a href="edit_profile.php" class="btn"><b>Edit Profile </b> </a>
            </div>
        </div>

        <div class="settings">
            <h3>Settings</h3>
            <p><strong>Manage your account settings and preferences.</strong></p>
            <a href="change_password.php" class="btn"><b> Change Password</b></a>
            <a href="logout.php" class="btn logout-btn"><b>Logout</b></a>
        </div>
    </div>

</body>
</html>
