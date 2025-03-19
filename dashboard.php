<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header('Location: login.html');
    exit();
}

require 'db.php';
$owner_id = $_SESSION['owner_id'];

// Fetch sales stats
$sales_query = $conn->query("SELECT COUNT(*) AS total_sales, SUM(total_price) AS revenue FROM sales WHERE owner_id = $owner_id");
$sales_data = $sales_query->fetch_assoc();
$total_sales = $sales_data['total_sales'] ?? 0;
$total_revenue = $sales_data['revenue'] ?? 0;

// Fetch top-selling products
$top_products = $conn->query("SELECT p.name, SUM(s.quantity_sold) as total_sold FROM sales s JOIN products p ON s.product_id = p.product_id WHERE s.owner_id = $owner_id GROUP BY p.product_id ORDER BY total_sold DESC LIMIT 5");

// Fetch stock status
$low_stock = $conn->query("SELECT name, quantity FROM products WHERE quantity < 5 AND owner_id = $owner_id LIMIT 5");

// Fetch customer insights
$customer_query = $conn->query("SELECT COUNT(DISTINCT customer_id) AS total_customers FROM sales WHERE owner_id = $owner_id");
$customer_data = $customer_query->fetch_assoc();
$total_customers = $customer_data['total_customers'] ?? 0;

// Fetch recent transactions
$recent_sales = $conn->query("SELECT sale_date, total_price FROM sales WHERE owner_id = $owner_id ORDER BY sale_date DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }
        .header {
            background: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .container {
            display: flex;
        }
        .sidebar {
            width: 250px;
            background: #e8f5e9;
            padding: 20px;
            height: 100vh;
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
        .main {
            flex-grow: 1;
            padding: 20px;
        }
        .stats, .graph-section {
            display: flex;
            gap: 20px;
        }
        .card {
            background: white;
            padding: 20px;
            flex: 1;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .table-container {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        th {
            background: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard</h1>
    </div>
    <div class="container">
        <nav class="sidebar">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="stocks.php">Stock Management</a></li>
                <li><a href="billing.php">Billing</a></li>
                <li><a href="customer.php">Customers</a></li>
                <li><a href="report.php">Reports</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
        <main class="main">
            <div class="stats">
                <div class="card">
                    <h3>Total Sales</h3>
                    <p><?php echo $total_sales; ?></p>
                </div>
                <div class="card">
                    <h3>Total Revenue</h3>
                    <p>₹<?php echo number_format($total_revenue, 2); ?></p>
                </div>
                <div class="card">
                    <h3>Total Customers</h3>
                    <p><?php echo $total_customers; ?></p>
                </div>
            </div>

            <div class="table-container">
                <h2>Top Selling Products</h2>
                <table>
                    <tr><th>Product</th><th>Units Sold</th></tr>
                    <?php while ($product = $top_products->fetch_assoc()): ?>
                        <tr><td><?php echo htmlspecialchars($product['name']); ?></td><td><?php echo $product['total_sold']; ?></td></tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <div class="table-container">
                <h2>Low Stock Products</h2>
                <table>
                    <tr><th>Product</th><th>Stock Left</th></tr>
                    <?php while ($stock = $low_stock->fetch_assoc()): ?>
                        <tr><td><?php echo htmlspecialchars($stock['name']); ?></td><td><?php echo $stock['quantity']; ?></td></tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <div class="table-container">
                <h2>Recent Transactions</h2>
                <table>
                    <tr><th>Date</th><th>Amount</th></tr>
                    <?php while ($sale = $recent_sales->fetch_assoc()): ?>
                        <tr><td><?php echo $sale['sale_date']; ?></td><td>₹<?php echo number_format($sale['total_price'], 2); ?></td></tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </main>
    </div>
</body>
</html>