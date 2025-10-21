<?php
session_start();
include './config.php';

// Only admin can access
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";

// Initialize query tracking array
$executed_queries = [];

// 1. Minimum product price
$sql_min_price = "SELECT MIN(price) AS cheapest_product_price, name FROM Products GROUP BY name ORDER BY cheapest_product_price ASC LIMIT 1";
$result_min_price = mysqli_query($conn, $sql_min_price);
if (!$result_min_price) {
    $executed_queries[] = ['query' => $sql_min_price, 'type' => 'SELECT', 'status' => 'error'];
} else {
    $executed_queries[] = ['query' => $sql_min_price, 'type' => 'SELECT', 'status' => 'success'];
}

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
if (!$result_max_order) {
    $executed_queries[] = ['query' => $sql_max_order, 'type' => 'SELECT', 'status' => 'error'];
} else {
    $executed_queries[] = ['query' => $sql_max_order, 'type' => 'SELECT', 'status' => 'success'];
}

// 3. Average order total
$sql_avg_order = "
SELECT AVG(total_amount) AS avg_order_amount
FROM Orders
WHERE payment_status = 'Done'
";
$result_avg_order = mysqli_query($conn, $sql_avg_order);
if (!$result_avg_order) {
    $executed_queries[] = ['query' => $sql_avg_order, 'type' => 'SELECT', 'status' => 'error'];
} else {
    $executed_queries[] = ['query' => $sql_avg_order, 'type' => 'SELECT', 'status' => 'success'];
}

// 4. Total number of customers
$sql_count_customers = "SELECT COUNT(*) AS total_customers FROM Customers";
$result_count_customers = mysqli_query($conn, $sql_count_customers);
if (!$result_count_customers) {
    $executed_queries[] = ['query' => $sql_count_customers, 'type' => 'SELECT', 'status' => 'error'];
} else {
    $executed_queries[] = ['query' => $sql_count_customers, 'type' => 'SELECT', 'status' => 'success'];
}

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
if (!$result_products_per_category) {
    $executed_queries[] = ['query' => $sql_products_per_category, 'type' => 'SELECT', 'status' => 'error'];
} else {
    $executed_queries[] = ['query' => $sql_products_per_category, 'type' => 'SELECT', 'status' => 'success'];
}

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
if (!$result_order_stats) {
    $executed_queries[] = ['query' => $sql_order_stats, 'type' => 'SELECT', 'status' => 'error'];
} else {
    $executed_queries[] = ['query' => $sql_order_stats, 'type' => 'SELECT', 'status' => 'success'];
}

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
if (!$result_avg_orders_per_customer) {
    $executed_queries[] = ['query' => $sql_avg_orders_per_customer, 'type' => 'SELECT', 'status' => 'error'];
} else {
    $executed_queries[] = ['query' => $sql_avg_orders_per_customer, 'type' => 'SELECT', 'status' => 'success'];
}

if (!$result_min_price || !$result_max_order || !$result_avg_order || !$result_count_customers || !$result_products_per_category || !$result_order_stats || !$result_avg_orders_per_customer) {
    $error = "Database error: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Aggregates</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            color: #667eea;
            font-size: 28px;
            font-weight: 700;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .header-actions a {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .header-actions a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .main-layout {
            display: flex;
            gap: 20px;
            max-width: 1800px;
            margin: 0 auto;
        }

        .content-area {
            flex: 1;
            order: 1;
        }

        .error-message {
            background-color: #fee;
            color: #c33;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #f44336;
            margin-bottom: 20px;
        }

        .stats-section {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .stats-section h2 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 20px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #667eea;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e8f4fd;
        }

        td {
            color: #2d3748;
            font-size: 14px;
        }

        .sql-sidebar {
            width: 450px;
            order: 2;
            background-color: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 15px;
            position: sticky;
            top: 20px;
            height: fit-content;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .sql-sidebar h3 {
            color: #4ec9b0;
            margin-top: 0;
            border-bottom: 2px solid #4ec9b0;
            padding-bottom: 10px;
            font-size: 18px;
        }

        .sql-query-item {
            background-color: #2d2d2d;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #569cd6;
        }

        .sql-query-item.success {
            border-left-color: #4caf50;
        }

        .sql-query-item.error {
            border-left-color: #f44336;
        }

        .sql-query-item h4 {
            color: #ce9178;
            margin: 0 0 10px 0;
            font-size: 13px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sql-query-item pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.5;
            color: #d4d4d4;
        }

        .query-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            background-color: #2196f3;
            color: white;
        }

        .status-icon {
            font-size: 16px;
        }

        @media (max-width: 1400px) {
            .main-layout {
                flex-direction: column;
            }

            .sql-sidebar {
                width: 100%;
                order: 2;
                position: relative;
                max-height: 500px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>📊 Admin Dashboard - Aggregates & Statistics</h1>
        <div class="header-actions">
            <a href="dashboard.php">← Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="main-layout">
        <div class="content-area">
            <?php if ($error != ""): ?>
                <div class="error-message">
                    ⚠️ <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Minimum Product Price -->
            <div class="stats-section">
                <h2>💰 Minimum Product Price</h2>
                <table>
                    <tr>
                        <th>Cheapest Product Price</th>
                        <th>Product Name</th>
                    </tr>
                    <?php if ($row = mysqli_fetch_assoc($result_min_price)): ?>
                        <tr>
                            <td>$<?php echo number_format($row['cheapest_product_price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Maximum Single Order Value -->
            <div class="stats-section">
                <h2>🏆 Maximum Single Order Value Per Customer</h2>
                <table>
                    <tr>
                        <th>Customer ID</th>
                        <th>Max Order Value</th>
                    </tr>
                    <?php if ($row = mysqli_fetch_assoc($result_max_order)): ?>
                        <tr>
                            <td><?php echo $row['customer_id']; ?></td>
                            <td>$<?php echo number_format($row['max_order_value'], 2); ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Average Order Total -->
            <div class="stats-section">
                <h2>📈 Average Order Total</h2>
                <table>
                    <tr>
                        <th>Average Order Amount</th>
                    </tr>
                    <?php if ($row = mysqli_fetch_assoc($result_avg_order)): ?>
                        <tr>
                            <td>$<?php echo number_format($row['avg_order_amount'], 2); ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Total Customers -->
            <div class="stats-section">
                <h2>👥 Total Customers</h2>
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
            </div>

            <!-- Products Sold Per Category -->
            <div class="stats-section">
                <h2>📦 Products Sold Per Category</h2>
                <table>
                    <tr>
                        <th>Category Name</th>
                        <th>Total Products Sold</th>
                    </tr>
                    <?php if ($result_products_per_category && mysqli_num_rows($result_products_per_category) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result_products_per_category)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                <td><?php echo $row['total_products_sold']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Order Stats Per Customer -->
            <div class="stats-section">
                <h2>📊 Order Statistics Per Customer</h2>
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
                                <td>$<?php echo number_format($row['min_order_value'], 2); ?></td>
                                <td>$<?php echo number_format($row['max_order_value'], 2); ?></td>
                                <td>$<?php echo number_format($row['avg_order_value'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Average Orders Per Customer -->
            <div class="stats-section">
                <h2>🔢 Average Orders Per Customer</h2>
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
        </div>

        <!-- SQL Sidebar -->
        <div class="sql-sidebar">
            <h3>📊 Executed SQL Queries (<?php echo count($executed_queries); ?>)</h3>

            <?php foreach ($executed_queries as $index => $query_data): ?>
                <div class="sql-query-item <?php echo $query_data['status']; ?>">
                    <h4>
                        <span>
                            Query #<?php echo $index + 1; ?>
                            <span class="query-badge"><?php echo $query_data['type']; ?></span>
                        </span>
                        <span class="status-icon">
                            <?php echo $query_data['status'] === 'success' ? '✓' : '✗'; ?>
                        </span>
                    </h4>
                    <pre><?php echo htmlspecialchars($query_data['query']); ?></pre>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>