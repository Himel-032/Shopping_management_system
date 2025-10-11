<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Shopping Management System</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <div class="header">
        <h1>Shopping Management System</h1>
        <div class="user-info">
            <a href="showuser.php" class="logout-btn">All Customers</a>
        </div>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_email']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    <div class="navbar">
        <div class="nav-item">
            <a href="products.php">
                <button type="button">Manage Products</button>
            </a>
        </div>
        <div class="nav-item">
            <a href="suppliers.php">
                <button type="button">Manage Suppliers</button>
            </a>
        </div>
        <div class="nav-item">
            <a href="categories.php">
                <button type="button">Manage Categories</button>
            </a>
        </div>
        <div class="nav-item">
            <a href="all_orders.php">
                <button type="button">All Orders</button>
            </a>
        </div>
        <div class="nav-item">
            <a href="all_reviews.php">
                <button type="button">Customer Reviews</button>
            </a>
        </div>
        <div class="nav-item">
            <a href="top_customer.php">
                <button type="button">Top customer</button>
            </a>
        </div>
        <div class="nav-item">
            <a href="top_order.php">
                <button type="button">Top orders</button>
            </a>
        </div>

     </div>
    <div class="container">
        <div class="welcome-card">
            <h2>Welcome to Admin Dashboard</h2>
            <p>You are successfully logged in as an administrator.</p>
        </div>
    </div>
</body>

</html>