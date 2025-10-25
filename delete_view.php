<?php
include 'config.php';
session_start();


if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$drop_sql = "DROP VIEW IF EXISTS view_products";
if ($conn->query($drop_sql)) {
    $_SESSION['message'] = "View deleted successfully.";
} else {
    $_SESSION['message'] = "Error deleting view: " . $conn->error;
}


header("Location: dashboard.php");
exit();
?>