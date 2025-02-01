<?php
session_start();

if (!isset($_SESSION["owner_id"])) {
    header("Location: login.html");
    exit;
}

echo "Welcome, " . $_SESSION["name"] . "! <a href='logout.php'>Logout</a>";
?>
