<?php
include 'config.php';
session_start();

// Only allow if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Drop the view if it exists
$drop_sql = "DROP VIEW IF EXISTS view_products";
if ($conn->query($drop_sql)) {
    // Optional: success message in session
    $_SESSION['message'] = "View deleted successfully.";
} else {
    $_SESSION['message'] = "Error deleting view: " . $conn->error;
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit();
?>