<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header('Location: login.html');
    exit();
}

require 'db.php';
$owner_id = $_SESSION['owner_id'];

// Fetch sales data grouped by date
$stmt = $conn->prepare("SELECT DATE(sale_date) as sale_day, SUM(total_price) as total_sales FROM sales WHERE owner_id = ? GROUP BY sale_day ORDER BY sale_day ASC");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$sales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch most sold products
$stmt = $conn->prepare("SELECT p.name, SUM(s.quantity_sold) as total_sold FROM sales s JOIN products p ON s.product_id = p.product_id WHERE s.owner_id = ? GROUP BY s.product_id ORDER BY total_sold DESC LIMIT 5");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch profit & loss report
$stmt = $conn->prepare("SELECT p.name, (s.total_price - (p.cost_price * s.quantity_sold)) AS profit FROM sales s JOIN products p ON s.product_id = p.product_id WHERE s.owner_id = ?");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$profits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT name FROM owners WHERE owner_id = ?");
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
    <title>Sales Report</title>
    <link rel="stylesheet" <?php echo "href='report.css?v=" . time() . "'"; ?>>
</head>
<body>
    <header class="header">
        <h1>Sales Report</h1>
        <p><strong><?php echo htmlspecialchars($owner['name']); ?></strong></p>
    </header>
    <div class="container">
        <nav class="sidebar">
            <h2>Menu</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="stocks.php">Stocks</a></li>
                <li><a href="billing.php">Billing</a></li>
                <li><a href="customer.php">Customer Management</a></li>
                <li><a href="report.php">Reports</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
        <main class="main">
            <h2>Sales Trend</h2>
            <div class="graph" style="width: 100%; height: <?php echo 45 * count($sales); ?>px; border: 1px solid #ccc; display: flex; flex-direction: column;">
                <?php foreach ($sales as $sale): ?>
                    <div style="width: <?php echo ($sale['total_sales'] / 800) * 100; ?>px; height: 20px; background-color: #4caf50; color: #fff; padding: 10px; margin: 2px;">
                        <?php echo htmlspecialchars($sale['sale_day']) . " - ₹" . number_format($sale['total_sales'], 2); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <h2>Most Sold Products</h2>
            <div class="graph" style="width: 100%; height: <?php echo 45 * count($products); ?>px; border: 1px solid #ccc; display: flex; flex-direction: column;">
                <?php foreach ($products as $product): ?>
                    <div style="width: <?php echo $product['total_sold'] * 10; ?>px; height: 20px; background-color: #ff9800; color: #fff; padding: 10px; margin: 2px;">
                        <?php echo htmlspecialchars($product['name']) . " - " . $product['total_sold']; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <h2>Profit & Loss</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Profit (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($profits as $profit): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($profit['name']); ?></td>
                            <td><?php echo number_format($profit['profit'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
