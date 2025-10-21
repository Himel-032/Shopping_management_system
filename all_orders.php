<?php
session_start();
include './config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";

// ------------------ Detailed Orders ------------------
$sql = "
SELECT 
    o.order_id,
    o.order_date,
    o.payment_status AS status,
    c.name AS customer_name,
    c.email AS customer_email,
    c.phone AS customer_phone,
    c.address AS customer_address,
    oi.product_id,
    p.name AS product_name,
    oi.quantity,
    oi.price,
    (oi.quantity * oi.price) AS total_price,
    (SELECT SUM(quantity * price) FROM Order_Items WHERE order_id = o.order_id) AS total_order_amount
FROM Orders o
JOIN Customers c ON o.customer_id = c.customer_id
JOIN Order_Items oi ON o.order_id = oi.order_id
JOIN Products p ON oi.product_id = p.product_id
ORDER BY o.order_date DESC, o.order_id DESC
";

$result = mysqli_query($conn, $sql);
if (!$result) {
    $error = "Database error: " . mysqli_error($conn);
}

$last_order_id = 0;

//  Orders Grouped by Product ------------------
// $sql_grouped = "
// SELECT 
//     p.name AS product_name,
//     SUM(oi.quantity) AS total_quantity_ordered,
//     SUM(oi.quantity * oi.price) AS total_revenue
// FROM Order_Items oi
// JOIN Products p ON oi.product_id = p.product_id
// GROUP BY p.product_id, p.name
// ORDER BY total_quantity_ordered DESC
// ";
// $sql_grouped = "
// SELECT 
//     p.name AS product_name,
//     SUM(oi.quantity) AS total_quantity_ordered,
//     SUM(oi.quantity * oi.price) AS total_revenue
// FROM Order_Items oi
// INNER JOIN Products p ON oi.product_id = p.product_id
// GROUP BY p.product_id, p.name
// ORDER BY total_quantity_ordered DESC
// ";

$sql_grouped = "
SELECT 
    p.name AS product_name,
    SUM(oi.quantity) AS total_quantity_ordered,
    SUM(oi.quantity * oi.price) AS total_revenue
FROM Order_Items oi
LEFT JOIN Products p ON oi.product_id = p.product_id
GROUP BY p.product_id, p.name

UNION

SELECT 
    p.name AS product_name,
    SUM(oi.quantity) AS total_quantity_ordered,
    SUM(oi.quantity * oi.price) AS total_revenue
FROM Order_Items oi
RIGHT JOIN Products p ON oi.product_id = p.product_id
GROUP BY p.product_id, p.name

ORDER BY total_quantity_ordered DESC
";



$result_grouped = mysqli_query($conn, $sql_grouped);
if (!$result_grouped) {
    $error .= "<br>Grouped query error: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>All Orders - Admin</title>
    <link rel="stylesheet" href="css/dashboard.css">
    
        
    <link rel="stylesheet" href="css/all_orders.css">
    <style>
        .main-layout {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        
        .content-area {
            flex: 1;
            min-width: 0;
        }
        
        .sql-panel {
            width: 400px;
            background-color: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 8px;
            position: sticky;
            top: 20px;
            height: fit-content;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .sql-panel h3 {
            color: #4ec9b0;
            margin-top: 0;
            border-bottom: 2px solid #4ec9b0;
            padding-bottom: 10px;
        }
        
        .sql-query {
            background-color: #2d2d2d;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #569cd6;
        }
        
        .sql-query h4 {
            color: #ce9178;
            margin: 0 0 8px 0;
            font-size: 14px;
        }
        
        .sql-query pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.5;
        }
        
        .sql-keyword {
            color: #569cd6;
            font-weight: bold;
        }
        
        .sql-string {
            color: #ce9178;
        }
        
        .sql-number {
            color: #b5cea8;
        }
        
        .sql-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            margin-left: 10px;
        }
        
        .status-success {
            background-color: #4caf50;
            color: white;
        }
        
        .status-error {
            background-color: #f44336;
            color: white;
        }
        
        @media (max-width: 1200px) {
            .main-layout {
                flex-direction: column;
            }
            
            .sql-panel {
                width: 100%;
                position: relative;
                max-height: 400px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Shopping Management System - All Orders</h1>
        <div class="user-info">
            <a href="dashboard.php" class="logout-btn">Back to Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="main-layout">
        <div class="content-area">
            <div class="container">
                <?php if ($error != ""): ?>
                    <p style="color:red;"><?php echo $error; ?></p>
                <?php endif; ?>

                <!-- Detailed Orders Table -->
                <h2>All Orders</h2>
                <table>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Customer Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total Order Amount</th>
                        <th>Payment Status</th>
                    </tr>

                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <?php if ($row['order_id'] != $last_order_id): ?>
                                    <td><?php echo $row['order_id']; ?></td>
                                    <td><?php echo $row['order_date']; ?></td>
                                    <td><?php echo $row['customer_name']; ?></td>
                                    <td><?php echo $row['customer_email']; ?></td>
                                    <td><?php echo $row['customer_phone']; ?></td>
                                    <td><?php echo $row['customer_address']; ?></td>
                                    <td><?php echo $row['product_name']; ?></td>
                                    <td><?php echo $row['quantity']; ?></td>
                                    <td><?php echo $row['price']; ?></td>
                                    <td><?php echo $row['total_order_amount']; ?></td>
                                    <td><?php echo $row['status']; ?></td>
                                    <?php $last_order_id = $row['order_id']; ?>
                                <?php else: ?>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><?php echo $row['product_name']; ?></td>
                                    <td><?php echo $row['quantity']; ?></td>
                                    <td><?php echo $row['price']; ?></td>
                                    <td><?php echo $row['total_price']; ?></td>
                                    <td></td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </table>

                <!-- Summary Table: Orders Grouped by Product -->
                <h2>Orders Summary by Product</h2>
                <table>
                    <tr>
                        <th>Product Name</th>
                        <th>Total Quantity Ordered</th>
                        <th>Total Expected Revenue</th>
                    </tr>

                    <?php if ($result_grouped && mysqli_num_rows($result_grouped) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result_grouped)): ?>
                            <tr>
                                <td><?php echo $row['product_name']; ?></td>
                                <td><?php echo $row['total_quantity_ordered']; ?></td>
                                <td><?php echo $row['total_revenue']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- SQL Query Display Panel -->
        <div class="sql-panel">
            <h3>📊 Executed SQL Queries</h3>
            
            <div class="sql-query">
                <h4>Query 1: Detailed Orders 
                    <span class="sql-status <?php echo $result ? 'status-success' : 'status-error'; ?>">
                        <?php echo $result ? '✓ Success' : '✗ Error'; ?>
                    </span>
                </h4>
                <pre><?php echo htmlspecialchars($sql); ?></pre>
            </div>

            <div class="sql-query">
                <h4>Query 2: Orders Grouped by Product
                    <span class="sql-status <?php echo $result_grouped ? 'status-success' : 'status-error'; ?>">
                        <?php echo $result_grouped ? '✓ Success' : '✗ Error'; ?>
                    </span>
                </h4>
                <pre><?php echo htmlspecialchars($sql_grouped); ?></pre>
            </div>

            <div style="margin-top: 20px; padding: 10px; background-color: #2d2d2d; border-radius: 5px; font-size: 11px;">
                <strong style="color: #4ec9b0;">Query Statistics:</strong><br>
                <span style="color: #d4d4d4;">
                    • Total Queries Executed: 2<br>
                    • Successful: <?php echo ($result ? 1 : 0) + ($result_grouped ? 1 : 0); ?><br>
                    • Failed: <?php echo ($result ? 0 : 1) + ($result_grouped ? 0 : 1); ?><br>
                    • Orders Retrieved: <?php echo $result ? mysqli_num_rows($result) : 0; ?><br>
                    • Product Summary Rows: <?php echo $result_grouped ? mysqli_num_rows($result_grouped) : 0; ?>
                </span>
            </div>
        </div>
    </div>
</body>

</html>