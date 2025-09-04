<?php

session_start();

// If not logged in, redirect
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// Now you can use:
$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $customer_name; ?></title>
</head>
<body>
    <div class="logout">
        <a href="logout.php">
            <button>Logout</button>
        </a>
    </div>
    <h2>Welcome, <?php echo $customer_name; ?>!</h2>
    <p>Your Customer ID: <?php echo $customer_id; ?></p>

</body>
</html>