<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header('Location: login.html');
    exit();
}
require 'db.php';
$owner_id = $_SESSION['owner_id'];
$stmt = $conn->prepare("SELECT name FROM owners WHERE owner_id = ?");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
$owner = $result->fetch_assoc();
$products_result = $conn->query("SELECT * FROM products");
$products = $products_result->fetch_all(MYSQLI_ASSOC);
$bill_items = [];
$total_amount = 0;
$discounted_total = 0;
$customer_discount = 0;
$customer_name = '';
$customer_phone = '';
$customer_id = NULL;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_customer'])) {
    $customer_name = $_POST['customer_name'];
    $customer_phone = $_POST['customer_phone'];
    $stmt = $conn->prepare("SELECT phone FROM customers WHERE phone = ? AND owner_id = ?");
    $stmt->bind_param("si", $customer_phone, $owner_id);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
    if (!$customer) {
        $stmt = $conn->prepare("INSERT INTO customers (name, phone, discount, owner_id) VALUES (?, ?, 1, ?)");
        $stmt->bind_param("ssi", $customer_name, $customer_phone, $owner_id);
        $stmt->execute();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bill'])) {
    $customer_name = $_POST['customer_name'];
    $customer_phone = $_POST['customer_phone'];
    $stmt = $conn->prepare("SELECT discount FROM customers WHERE phone = ? AND owner_id = ?");
    $stmt->bind_param("si", $customer_phone, $owner_id);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
    if ($customer) {
        $customer_discount = ($customer['discount'] == 1) ? 0.05 : 0;
    }
    if (isset($_POST['selected_products'])) {
        foreach ($_POST['selected_products'] as $product_id) {
            if (!empty($_POST['products'][$product_id])) {
                $quantity = (int)$_POST['products'][$product_id];
                $stmt = $conn->prepare("SELECT name, price, quantity FROM products WHERE product_id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $product = $stmt->get_result()->fetch_assoc();
                if ($product && $product['quantity'] >= $quantity) {
                    $total_price = $product['price'] * $quantity;
                    $total_amount += $total_price;
                    $bill_items[] = [
                        'product_name' => $product['name'],
                        'quantity' => $quantity,
                        'price' => $product['price'],
                        'total' => $total_price
                    ];
                    $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
                    $stmt->bind_param("ii", $quantity, $product_id);
                    $stmt->execute();
                }
            }
        }
    }
    $discounted_total = $total_amount - ($total_amount * $customer_discount);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bill'])) {
    $customer_name = $_POST['customer_name'];
    $customer_phone = $_POST['customer_phone'];
    $sale_date = date('Y-m-d H:i:s'); // Capture current date & time
    
    if (isset($_POST['selected_products'])) {
        foreach ($_POST['selected_products'] as $product_id) {
            if (!empty($_POST['products'][$product_id])) {
                $quantity = (int)$_POST['products'][$product_id];

                // Fetch product price
                $stmt = $conn->prepare("SELECT price, quantity FROM products WHERE product_id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $product = $stmt->get_result()->fetch_assoc();

                if ($product && $product['quantity'] >= $quantity) {
                    $total_price = $product['price'] * $quantity;

                    // Insert sale record into `sales` table
                    $stmt = $conn->prepare("INSERT INTO sales (product_id, quantity_sold, total_price, sale_date) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iids", $product_id, $quantity, $total_price, $sale_date);
                    $stmt->execute();

                    // Reduce stock
                    $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ? AND owner_id = ?");
                    $stmt->bind_param("iii", $quantity, $product_id , $owner_id);
                    $stmt->execute();
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="header">
        <h1>Billing</h1>
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
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
        <main class="main">
            <form method="POST" class="form-container">
                <h3>Customer Details</h3>
                <input type="text" name="customer_name" placeholder="Customer Name" value="<?= htmlspecialchars($customer_name) ?>" required>
                <input type="text" name="customer_phone" placeholder="Customer Phone" value="<?= htmlspecialchars($customer_phone) ?>" required>
                <button type="submit" name="save_customer">Save Customer</button>
            </form>
            <form method="POST" class="product-container">
                <input type="hidden" name="customer_name" value="<?= htmlspecialchars($customer_name) ?>">
                <input type="hidden" name="customer_phone" value="<?= htmlspecialchars($customer_phone) ?>">
                <input class="search-input" type="text" id="product-search" placeholder="Search Products" oninput="filterProducts()">
                <table>
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr class="product-row">
                                <td><input type="checkbox" name="selected_products[]" value="<?= $product['product_id'] ?>"></td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><input type="number" name="products[<?= $product['product_id'] ?>]" min="1" max="<?= $product['quantity'] ?>"></td>
                                <td><?= htmlspecialchars($product['price']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button class="print-button" type="submit" name="bill" onclick="document.getElementById('download-button').style.display = 'block'">Generate Bill</button>
            </form>
            <div class="bill-section" id="bill-section">
    <?php if (!empty($bill_items)): ?>
        <h3>Bill Receipt</h3>
        <p><strong>Biller:</strong> <?php echo htmlspecialchars($owner['name']); ?></p>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bill_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= $item['price'] ?></td>
                        <td><?= $item['total'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3"><strong>Total Amount:</strong></td>
                    <td><strong><?= number_format($total_amount, 2) ?></strong></td>
                </tr>
                
                <?php if ($customer_discount > 0): ?>
                    <tr>
                        <td colspan="3"><strong>Discount Applied:</strong></td>
                        <td><strong><?= ($customer_discount * 100) ?>%</strong></td>
                    </tr>
                    <tr>
                        <td colspan="3"><strong>Discounted Total:</strong></td>
                        <td><strong><?= number_format($discounted_total, 2) ?></strong></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="4"><strong>No Discount Applied</strong></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <p><strong>Customer Name:</strong> <?= htmlspecialchars($customer_name) ?></p>
        <span><b>Thank you! Visit Again.</b></span>
    <?php endif; ?>
</div>

            <button class="print-button" id="download-button" onclick="downloadBillImage()">Download Bill</button>
        </main>
    </div>
    <script>
        function downloadBillImage() {
           // document.getElementById("download-button").style.display = "none";
            const billSection = document.getElementById("bill-section");
            html2canvas(billSection).then(canvas => {
                let imgURL = canvas.toDataURL("image/png"); // Convert to image format
                let link = document.createElement("a");
                link.href = imgURL;
                link.download = "Bill_Receipt.png"; // File name
                link.click(); // Trigger download
            });
        }
        function filterProducts() {
        const searchTerm = document.getElementById("product-search").value.toLowerCase();
        const rows = document.querySelectorAll(".product-row");

        rows.forEach(row => {
            const productName = row.cells[1].textContent.toLowerCase();
            if (productName.includes(searchTerm)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }
    function filterProducts() {
        const searchTerm = document.getElementById("product-search").value.toLowerCase();
        const rows = document.querySelectorAll(".product-row");

        rows.forEach(row => {
            const productName = row.querySelector("td:nth-child(2)").textContent.toLowerCase();
            row.style.display = productName.includes(searchTerm) ? "" : "none";
        });
    }

    </script>
</body>
</html>
