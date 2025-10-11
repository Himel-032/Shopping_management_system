<?php
session_start();
include './config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$min_quantity = 5;

// Query: Top products without JOIN
$sql = "
SELECT 
    product_id,
    (SELECT name FROM Products p WHERE p.product_id = oi.product_id) AS product_name,
    SUM(quantity) AS total_quantity,
    SUM(quantity * price) AS total_revenue
FROM Order_Items oi
WHERE EXISTS (
    SELECT 1 
    FROM Orders o 
    WHERE o.order_id = oi.order_id AND o.payment_status='Done'
)
GROUP BY product_id
HAVING total_quantity > $min_quantity
ORDER BY total_quantity DESC
";

$result = mysqli_query($conn, $sql);
if (!$result) {
    $error = "Database error: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Top Products</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        table {
            border-collapse: collapse;
            width: 90%;
            margin: 20px auto;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e6f7ff;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Top Products (Quantity &gt; <?php echo $min_quantity; ?>)</h1>
        <div class="user-info">
            <a href="dashboard.php" class="logout-btn">Back to Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($error != ""): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <table>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Total Quantity Sold</th>
                <th>Total Revenue</th>
            </tr>
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['product_id']; ?></td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo $row['total_quantity']; ?></td>
                        <td><?php echo number_format($row['total_revenue'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No products found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>

</html>