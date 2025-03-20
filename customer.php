<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header('Location: login.html');
    exit();
}

require 'db.php';

$owner_id = $_SESSION['owner_id'];

// Fetch owner details
$stmt = $conn->prepare("SELECT name FROM owners WHERE owner_id = ?");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
$owner = $result->fetch_assoc();
// Fetch all customers
$stmt = $conn->prepare("SELECT * FROM customers WHERE owner_id = ?");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Add New Customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_customer'])) {
    $customer_name = trim($_POST['customer_name']);
    $customer_phone = trim($_POST['customer_phone']);

    // Check if the customer already exists
    $stmt = $conn->prepare("SELECT * FROM customers WHERE phone = ? AND owner_id = ?");
    $stmt->bind_param("si", $customer_phone, $owner_id);
    $stmt->execute();
    $existing_customer = $stmt->get_result()->fetch_assoc();

    if (!$existing_customer) {
        // Insert new customer with a discount eligibility
        $stmt = $conn->prepare("INSERT INTO customers (name, phone, discount, owner_id) VALUES (?, ?, 1, ?)");
        $stmt->bind_param("ssi", $customer_name, $customer_phone, $owner_id);
        $stmt->execute();
        header("Location: customer.php");
        exit();
    } else {
        $error_message = "Customer with this phone number already exists!";
    }
}

// Delete Customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_customer'])) {
    $customer_id = $_POST['customer_id'];
    $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ? AND owner_id = ?");
    $stmt->bind_param("ii", $customer_id, $owner_id);
    $stmt->execute();
    header("Location: customer.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <link rel="stylesheet" <?php echo "href='styles.css?v=" . time() . "'"; ?>>
</head>
<body>
    <header class="header">
        <h1>Customer Management</h1>
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
            <h2>Add Customer</h2>
            <?php if (isset($error_message)): ?>
                <p class="error"><?= htmlspecialchars($error_message) ?></p>
            <?php endif; ?>
            <form method="POST" class="form-container">
                <input type="text" name="customer_name" placeholder="Customer Name" required>
                <input type="text" name="customer_phone" placeholder="Customer Phone" required>
                <button type="submit" name="add_customer">Add Customer</button>
            </form>

            <h2>Customer List</h2>
            <input class="search-input" type="text" id="customer-search" placeholder="Search Customers" oninput="filterCustomers()">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Discount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr class="customer-row">
                            <td><?= htmlspecialchars($customer['name']) ?></td>
                            <td><?= htmlspecialchars($customer['phone']) ?></td>
                            <td><?= ($customer['discount'] == 1) ? "5% Discount" : "No Discount" ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="customer_id" value="<?= $customer['customer_id'] ?>">
                                    <button type="submit" name="delete_customer" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

    <script>
        function filterCustomers() {
            const searchTerm = document.getElementById("customer-search").value.toLowerCase();
            const rows = document.querySelectorAll(".customer-row");

            rows.forEach(row => {
                const customerName = row.children[0].textContent.toLowerCase();
                const customerPhone = row.children[1].textContent.toLowerCase();
                if (customerName.includes(searchTerm) || customerPhone.includes(searchTerm)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }
    </script>
</body>
</html>
