<?php
session_start();
include './config.php';

// Only admin can access
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";

// 1. Minimum product price
$sql_min_price = "SELECT MIN(price) AS cheapest_product_price, name FROM Products GROUP BY name ORDER BY cheapest_product_price ASC LIMIT 1";
$result_min_price = mysqli_query($conn, $sql_min_price);

// 2. Maximum single order value 
$sql_max_order = "
SELECT o.customer_id, MAX(oi.quantity * oi.price) AS max_order_value
FROM Orders o
JOIN Order_Items oi ON o.order_id = oi.order_id
WHERE o.payment_status = 'Done'
GROUP BY o.customer_id
ORDER BY max_order_value DESC
LIMIT 1
";
$result_max_order = mysqli_query($conn, $sql_max_order);

// 3. Average order total
$sql_avg_order = "
SELECT AVG(total_amount) AS avg_order_amount
FROM Orders
WHERE payment_status = 'Done'
";
$result_avg_order = mysqli_query($conn, $sql_avg_order);

// 4. Total number of customers
$sql_count_customers = "SELECT COUNT(*) AS total_customers FROM Customers";
$result_count_customers = mysqli_query($conn, $sql_count_customers);

// 5. Products sold per category
$sql_products_per_category = "
SELECT c.name AS category_name, COUNT(oi.product_id) AS total_products_sold
FROM Categories c
JOIN Products p ON c.category_id = p.category_id
JOIN Order_Items oi ON p.product_id = oi.product_id
JOIN Orders o ON oi.order_id = o.order_id
WHERE o.payment_status = 'Done'
GROUP BY c.category_id, c.name
ORDER BY total_products_sold DESC
";
$result_products_per_category = mysqli_query($conn, $sql_products_per_category);

// 6. Order stats per customer
$sql_order_stats = "
SELECT 
    o.customer_id,
    MIN(oi.quantity * oi.price) AS min_order_value,
    MAX(oi.quantity * oi.price) AS max_order_value,
    AVG(oi.quantity * oi.price) AS avg_order_value
FROM Orders o
JOIN Order_Items oi ON o.order_id = oi.order_id
WHERE o.payment_status = 'Done'
GROUP BY o.customer_id
ORDER BY avg_order_value DESC
";
$result_order_stats = mysqli_query($conn, $sql_order_stats);

// 7. Average orders per customer
$sql_avg_orders_per_customer = "
SELECT AVG(order_count) AS avg_orders_per_customer
FROM (
    SELECT customer_id, COUNT(*) AS order_count
    FROM Orders
    WHERE payment_status = 'Done'
    GROUP BY customer_id
) AS sub
";
$result_avg_orders_per_customer = mysqli_query($conn, $sql_avg_orders_per_customer);

if (!$result_min_price || !$result_max_order || !$result_avg_order || !$result_count_customers || !$result_products_per_category || !$result_order_stats || !$result_avg_orders_per_customer) {
    $error = "Database error: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Aggregates</title>
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
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        h2 {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Admin Dashboard - Aggregates</h1>
        <div class="user-info">
            <a href="dashboard.php" class="logout-btn">Back to Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($error != ""): ?>
            <p style="color:red; text-align:center;"><?php echo $error; ?></p>
        <?php endif; ?>

        <!-- Minimum Product Price -->
        <h2>Minimum Product Price</h2>
        <table>
            <tr>
                <th>Cheapest Product Price</th>
                <th>Product Name</th>
            </tr>
            <?php if ($row = mysqli_fetch_assoc($result_min_price)): ?>
                <tr>
                    <td><?php echo number_format($row['cheapest_product_price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                </tr>
            <?php endif; ?>
        </table>

        <!-- Maximum Single Order Value -->
        <h2>Maximum Single Order Value Per Customer</h2>
        <table>
            <tr>
                <th>Customer ID</th>
                <th>Max Order Value</th>
            </tr>
            <?php if ($row = mysqli_fetch_assoc($result_max_order)): ?>
                <tr>
                    <td><?php echo $row['customer_id']; ?></td>
                    <td><?php echo number_format($row['max_order_value'], 2); ?></td>
                </tr>
            <?php endif; ?>
        </table>

        <!-- Average Order Total -->
        <h2>Average Order Total</h2>
        <table>
            <tr>
                <th>Average Order Amount</th>
            </tr>
            <?php if ($row = mysqli_fetch_assoc($result_avg_order)): ?>
                <tr>
                    <td><?php echo number_format($row['avg_order_amount'], 2); ?></td>
                </tr>
            <?php endif; ?>
        </table>

        <!-- Total Customers -->
        <h2>Total Customers</h2>
        <table>
            <tr>
                <th>Total Customers</th>
            </tr>
            <?php if ($row = mysqli_fetch_assoc($result_count_customers)): ?>
                <tr>
                    <td><?php echo $row['total_customers']; ?></td>
                </tr>
            <?php endif; ?>
        </table>

        <!-- Products Sold Per Category -->
        <h2>Products Sold Per Category</h2>
        <table>
            <tr>
                <th>Category Name</th>
                <th>Total Products Sold</th>
            </tr>
            <?php if ($result_products_per_category && mysqli_num_rows($result_products_per_category) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result_products_per_category)): ?>
                    <tr>
                        <td><?php echo $row['category_name']; ?></td>
                        <td><?php echo $row['total_products_sold']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </table>

        <!-- Order Stats Per Customer -->
        <h2>Order Statistics Per Customer</h2>
        <table>
            <tr>
                <th>Customer ID</th>
                <th>Min Order Value</th>
                <th>Max Order Value</th>
                <th>Avg Order Value</th>
            </tr>
            <?php if ($result_order_stats && mysqli_num_rows($result_order_stats) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result_order_stats)): ?>
                    <tr>
                        <td><?php echo $row['customer_id']; ?></td>
                        <td><?php echo number_format($row['min_order_value'], 2); ?></td>
                        <td><?php echo number_format($row['max_order_value'], 2); ?></td>
                        <td><?php echo number_format($row['avg_order_value'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </table>

        <!-- Average Orders Per Customer -->
        <h2>Average Orders Per Customer</h2>
        <table>
            <tr>
                <th>Average Orders</th>
            </tr>
            <?php if ($row = mysqli_fetch_assoc($result_avg_orders_per_customer)): ?>
                <tr>
                    <td><?php echo number_format($row['avg_orders_per_customer'], 2); ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>

</html>