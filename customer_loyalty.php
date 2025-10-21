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

// Query: total spent + MOD-based loyalty tiers
$sql = "
SELECT 
    c.customer_id,
    c.name AS customer_name,
    c.email,
    COALESCE(SUM(oi.quantity * oi.price),0) AS total_spent,
    MOD(c.customer_id, 3) AS loyalty_tier_num,
    CASE MOD(c.customer_id, 3)
        WHEN 0 THEN 'Gold'
        WHEN 1 THEN 'Silver'
        ELSE 'Bronze'
    END AS loyalty_tier
FROM Customers c
LEFT JOIN Orders o ON c.customer_id = o.customer_id AND o.payment_status='Done'
LEFT JOIN Order_Items oi ON o.order_id = oi.order_id
GROUP BY c.customer_id, c.name, c.email
ORDER BY total_spent DESC
";

$result = mysqli_query($conn, $sql);
if (!$result) {
    $error = "Database error: " . mysqli_error($conn);
    $executed_queries[] = ['query' => $sql, 'type' => 'SELECT', 'status' => 'error'];
} else {
    $executed_queries[] = ['query' => $sql, 'type' => 'SELECT', 'status' => 'success'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Loyalty Tiers</title>
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
            max-width: 1600px;
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

        .explanation {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            color: #2d3748;
            line-height: 1.6;
        }

        .explanation strong {
            color: #667eea;
        }

        .table-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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

        .tier-gold {
            background-color: #ffd700 !important;
            color: #000 !important;
            font-weight: bold;
        }

        .tier-silver {
            background-color: #c0c0c0 !important;
            color: #000 !important;
            font-weight: bold;
        }

        .tier-bronze {
            background-color: #cd7f32 !important;
            color: #fff !important;
            font-weight: bold;
        }

        .sql-sidebar {
            width: 400px;
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
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sql-query-item pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.5;
            color: #d4d4d4;
        }

        .query-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            background-color: #2196f3;
            color: white;
        }

        .status-icon {
            font-size: 16px;
        }

        @media (max-width: 1200px) {
            .main-layout {
                flex-direction: column;
            }

            .sql-sidebar {
                width: 100%;
                order: 2;
                position: relative;
                max-height: 400px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>🎖️ Customer Loyalty Tiers</h1>
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

            <div class="explanation">
                <strong>ℹ️ Loyalty Tier Assignment Logic:</strong><br>
                Loyalty tiers are assigned using the <strong>MOD function</strong> on customer IDs.<br>
                <strong>Tier Mapping:</strong> 
                <span style="background: #ffd700; padding: 2px 8px; border-radius: 3px; color: #000;">0 → Gold</span>
                <span style="background: #c0c0c0; padding: 2px 8px; border-radius: 3px; color: #000;">1 → Silver</span>
                <span style="background: #cd7f32; padding: 2px 8px; border-radius: 3px; color: #fff;">2 → Bronze</span>
                <br>
                Total spent is calculated from completed (paid) orders.
            </div>

            <div class="table-container">
                <h3 style="color: #2d3748; margin-bottom: 15px;">🏅 Customer Loyalty Status</h3>
                <table>
            <tr>
                <th>Customer ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Total Spent</th>
                <th>Tier Number</th>
                <th>Tier Name</th>
            </tr>

            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['customer_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>$<?php echo number_format($row['total_spent'], 2); ?></td>
                        <td><?php echo $row['loyalty_tier_num']; ?></td>
                        <td class="tier-<?php echo strtolower($row['loyalty_tier']); ?>">
                            <?php 
                            $tier = $row['loyalty_tier'];
                            if ($tier == 'Gold') echo '🥇 ';
                            elseif ($tier == 'Silver') echo '🥈 ';
                            else echo '🥉 ';
                            echo $tier; 
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; color: #718096; padding: 30px;">No customers found.</td>
                </tr>
            <?php endif; ?>
        </table>
            </div>
        </div>

        <!-- SQL Sidebar -->
        <div class="sql-sidebar">
            <h3>📊 Executed SQL Queries</h3>

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