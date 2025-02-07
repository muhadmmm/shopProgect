<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grocery Shop Manager Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f8f9fa;
            color: #333;
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            background-color: #4caf50;
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        header .logo {
            display: flex;
            align-items: center;
        }

        header .logo img {
            height: 40px;
            margin-right: 10px;
            border-radius: 20px;
        }

        header .profile-info {
            font-size: 18px;
        }

        .container {
            display: flex;
        }

        .sidebar {
            width: 250px;
            background-color: #e8f5e9;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            height: 100vh;
        }

        .sidebar h2 {
            margin-top: 0;
            color: #4caf50;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #333;
            font-size: 16px;
        }

        .sidebar ul li a:hover {
            color: #4caf50;
        }

        .main {
            flex: 1;
            padding: 20px;
        }

        .main h1 {
            margin-bottom: 20px;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .actions button {
            padding: 10px 15px;
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .actions button:hover {
            background-color: #45a049;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }

        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #4caf50;
            color: white;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .form-container {
            display: none;
            margin-bottom: 20px;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-container input[type="text"], 
        .form-container input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-container button {
            background-color: #4caf50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    require 'db.php';

    // Check if owner is logged in
    if (!isset($_SESSION['owner_id'])) {
        header('Location: login.php');
        exit();
    }

    // Fetch owner info
    $owner_id = $_SESSION['owner_id'];
    $stmt = $pdo->prepare("SELECT name FROM owners WHERE owner_id = ?");
    $stmt->execute([$owner_id]);
    $owner = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch products
    $products = $pdo->query("SELECT * FROM products")->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <header>
        <div class="logo">
            <img src="logo.jpg" alt="Logo">
            <h1>Grocery Shop Manager</h1>
        </div>
        <div class="profile-info">
            Welcome, <?php echo htmlspecialchars($owner['name']); ?>
        </div>
    </header>

    <div class="container">
        <nav class="sidebar">
            <h2>Menu</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="billing.php">Billing</a></li>
                <li><a href="customer.php">Customer Management</a></li>
                <li><a href="report.php">Reports</a></li>
            </ul>
        </nav>

        <main class="main">
            <h1>Stock Management</h1>
            <div class="actions">
                <button onclick="showForm('addForm')">Add Product</button>
                <button onclick="showForm('restockForm')">Restock</button>
            </div>

            <!-- Add Product Form -->
            <div id="addForm" class="form-container">
                <h2>Add Product</h2>
                <form action="add_product.php" method="POST">
                    <input type="text" name="name" placeholder="Product Name" required>
                    <input type="text" name="category" placeholder="Category" required>
                    <input type="number" name="price" placeholder="Price" step="0.01" required>
                    <input type="number" name="quantity" placeholder="Quantity" required>
                    <button type="submit">Save</button>
                </form>
            </div>

            <!-- Restock Form -->
            <div id="restockForm" class="form-container">
                <h2>Restock Product</h2>
                <form action="restock_product.php" method="POST">
                    <select name="product_id" required>
                        <option value="">Select Product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['product_id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="quantity" placeholder="Quantity to Add" required>
                    <button type="submit">Restock</button>
                </form>
            </div>

            <!-- Products Table -->
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['product_id']; ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                <td><?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo $product['quantity']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">No products available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <script>
        function showForm(formId) {
            document.getElementById('addForm').style.display = 'none';
            document.getElementById('restockForm').style.display = 'none';
            document.getElementById(formId).style.display = 'block';
        }
    </script>
</body>
</html>
