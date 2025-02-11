<?php
// db.php
$host = 'localhost';
$db   = 'groceryshop';
$user = 'root'; // Change if you have a different MySQL user
$pass = '';     // Add password if required

// Create a connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
